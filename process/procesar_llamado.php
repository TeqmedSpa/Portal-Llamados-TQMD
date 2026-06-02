<?php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

// ── Validación ────────────────────────────────────────────────────────────────

$errors = [];

$contactoId  = (int) ($input['contacto_id'] ?? 0);
$titulo      = trim($input['titulo'] ?? '');
$descripcion = trim($input['descripcion'] ?? '');
$prioridad   = $input['prioridad'] ?? 'normal';
$equipos     = $input['equipos'] ?? [];

if ($contactoId <= 0)                       $errors[] = 'Debes identificarte como contacto.';
if (mb_strlen($titulo) < 5)                 $errors[] = 'El título debe tener al menos 5 caracteres.';
if (mb_strlen($descripcion) < 10)           $errors[] = 'La descripción debe tener al menos 10 caracteres.';
if (!in_array($prioridad, ['baja','normal','alta','urgente'])) $errors[] = 'Prioridad inválida.';
if (empty($equipos))                        $errors[] = 'Debes agregar al menos un equipo.';

foreach ($equipos as $i => $eq) {
    $num = $i + 1;
    if (empty($eq['equipo_id']))                           $errors[] = "Equipo #{$num}: selecciona un equipo.";
    if (mb_strlen(trim($eq['descripcion_problema'] ?? '')) < 5) $errors[] = "Equipo #{$num}: describe el problema (mínimo 5 caracteres).";
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// ── Verificar que el contacto existe ─────────────────────────────────────────

try {
    $pdo = Database::get();

    $stmt = $pdo->prepare("SELECT id FROM contactos WHERE id = ? AND activo = 1 LIMIT 1");
    $stmt->execute([$contactoId]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Contacto no encontrado.']);
        exit;
    }

    // ── Generar número de llamado ─────────────────────────────────────────────

    $stmt = $pdo->query("SELECT COALESCE(MAX(id), 0) AS ultimo FROM llamados");
    $ultimo = (int) $stmt->fetchColumn();
    $numero = 'LL-' . str_pad($ultimo + 1, 5, '0', STR_PAD_LEFT);

    // ── Insertar en transacción ───────────────────────────────────────────────

    $pdo->beginTransaction();

    $ahora = date('Y-m-d H:i:s');

    $stmt = $pdo->prepare("
        INSERT INTO llamados
            (contacto_id, numero, titulo, descripcion, prioridad, estado, fecha_apertura, created_at, updated_at)
        VALUES
            (:contacto_id, :numero, :titulo, :descripcion, :prioridad, 'abierto', :fecha, :now, :now)
    ");
    $stmt->execute([
        ':contacto_id' => $contactoId,
        ':numero'      => $numero,
        ':titulo'      => $titulo,
        ':descripcion' => $descripcion,
        ':prioridad'   => $prioridad,
        ':fecha'       => $ahora,
        ':now'         => $ahora,
    ]);

    $llamadoId = (int) $pdo->lastInsertId();

    // Equipos
    $stmtEq = $pdo->prepare("
        INSERT INTO llamado_equipos
            (llamado_id, equipo_id, operativo, descripcion_problema, created_at, updated_at)
        VALUES
            (:llamado_id, :equipo_id, :operativo, :descripcion, :now, :now)
    ");
    foreach ($equipos as $eq) {
        $stmtEq->execute([
            ':llamado_id'  => $llamadoId,
            ':equipo_id'   => (int) $eq['equipo_id'],
            ':operativo'   => $eq['operativo'] ? 1 : 0,
            ':descripcion' => trim($eq['descripcion_problema']),
            ':now'         => $ahora,
        ]);
    }

    // Historial inicial
    $pdo->prepare("
        INSERT INTO llamado_historial
            (llamado_id, usuario_id, tipo_evento, valor_nuevo, created_at)
        VALUES
            (:llamado_id, NULL, 'creacion', 'abierto', :now)
    ")->execute([':llamado_id' => $llamadoId, ':now' => $ahora]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'numero'  => $numero,
        'id'      => $llamadoId,
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno. Intente nuevamente.']);
}

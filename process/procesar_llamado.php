<?php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/rate_limit.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Cache-Control: no-store, no-cache, must-revalidate');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$rawBody = file_get_contents('php://input', false, null, 0, 65536);
if (strlen($rawBody) >= 65536) {
    http_response_code(413);
    echo json_encode(['success' => false, 'message' => 'Solicitud demasiado grande.']);
    exit;
}
$input = json_decode($rawBody, true);

// ── CSRF ─────────────────────────────────────────────────────────────────────
$csrfToken    = $input['csrf_token'] ?? '';
$csrfEsperado = $_SESSION['csrf_token'] ?? '';
if (!$csrfEsperado || !hash_equals($csrfEsperado, $csrfToken)) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    error_log("[TQMD-PORTAL] CSRF inválido desde IP={$ip}");
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Solicitud no válida. Recarga la página e inténtalo de nuevo.']);
    exit;
}

// ── Honeypot ─────────────────────────────────────────────────────────────────
if (!empty($input['hp'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Solicitud rechazada.']);
    exit;
}

// ── Timing anti-bot: rechaza envíos en menos de 3 segundos desde la carga ───
$formReadyAt = (int) ($_SESSION['form_ready_at'] ?? 0);
if ($formReadyAt === 0 || (time() - $formReadyAt) < 3) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Envío demasiado rápido. Por favor recarga la página e inténtalo de nuevo.']);
    exit;
}

// ── Rate limiting ────────────────────────────────────────────────────────────
if (!rateLimitCheck('llamado', 5)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Demasiadas solicitudes. Espera unos minutos e inténtalo de nuevo.']);
    exit;
}

// ── Validación ────────────────────────────────────────────────────────────────

$errors = [];

$encargadoId = (int) ($input['encargado_id'] ?? 0);
$equipos     = $input['equipos'] ?? [];

if ($encargadoId <= 0) $errors[] = 'Debes identificarte como encargado.';
if (empty($equipos))  $errors[] = 'Debes agregar al menos un equipo.';
if (count($equipos) > 10) $errors[] = 'Máximo 10 equipos por llamado.';

foreach ($equipos as $i => $eq) {
    $num  = $i + 1;
    $desc = trim($eq['descripcion_problema'] ?? '');
    if (empty($eq['equipo_id']))                                        $errors[] = "Equipo #{$num}: selecciona un equipo.";
    if (mb_strlen($desc) < 5)                                          $errors[] = "Equipo #{$num}: describe el problema (mínimo 5 caracteres).";
    if (mb_strlen($desc) > 1000)                                       $errors[] = "Equipo #{$num}: descripción demasiado larga (máximo 1000 caracteres).";
    if (!isset($eq['operativo']) || !is_bool($eq['operativo']))         $errors[] = "Equipo #{$num}: indica si el equipo está operativo o no.";
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// ── Verificar que el contacto existe ─────────────────────────────────────────

try {
    $pdo = Database::get();

    $stmt = $pdo->prepare("SELECT id, centro_medico_id FROM encargados WHERE id = ? AND activo = 1 AND deleted_at IS NULL LIMIT 1");
    $stmt->execute([$encargadoId]);
    $encargadoRow = $stmt->fetch();
    if (!$encargadoRow) {
        echo json_encode(['success' => false, 'message' => 'Encargado no encontrado.']);
        exit;
    }
    $centroPropietario = (int) $encargadoRow['centro_medico_id'];

    // ── Verificar que ningún equipo tenga llamado abierto ────────────────────

    $equipoIds   = array_map(fn($eq) => (int) $eq['equipo_id'], $equipos);
    $placeholders = implode(',', array_fill(0, count($equipoIds), '?'));

    $stmt = $pdo->prepare("
        SELECT e.id, e.numero_serie, me.nombre_modelo, ma.nombre AS marca
        FROM llamado_equipos le
        JOIN llamados l  ON l.id = le.llamado_id
        JOIN equipos e   ON e.id = le.equipo_id
        JOIN modelos_equipo me ON me.id = e.modelo_equipo_id
        JOIN marcas ma   ON ma.id = me.marca_id
        WHERE le.equipo_id IN ($placeholders)
          AND l.estado NOT IN ('finalizado', 'cancelado')
          AND l.deleted_at IS NULL
        LIMIT 5
    ");
    $stmt->execute($equipoIds);
    $conflictos = $stmt->fetchAll();

    if (!empty($conflictos)) {
        $nombres = array_map(fn($c) => "{$c['marca']} {$c['nombre_modelo']} (serie {$c['numero_serie']})", $conflictos);
        echo json_encode([
            'success' => false,
            'message' => 'Los siguientes equipos ya tienen un llamado abierto: ' . implode(', ', $nombres) . '. Cierre el llamado existente antes de abrir uno nuevo.',
        ]);
        exit;
    }

    // ── Validar que todos los equipos pertenecen al centro del encargado ─────

    $stmtOwn = $pdo->prepare("
        SELECT COUNT(*) AS total FROM equipos
        WHERE id IN ($placeholders)
          AND centro_medico_id = ?
          AND activo = 1
          AND deleted_at IS NULL
    ");
    $stmtOwn->execute([...$equipoIds, $centroPropietario]);
    $countValidos = (int) $stmtOwn->fetch()['total'];
    if ($countValidos !== count($equipoIds)) {
        error_log("[TQMD-PORTAL] Intento cross-centro: encargado_id={$encargadoId} centro={$centroPropietario} equipos=" . implode(',', $equipoIds));
        echo json_encode(['success' => false, 'message' => 'Uno o más equipos no pertenecen a tu centro médico.']);
        exit;
    }

    // ── Auto-generar título desde el primer equipo ────────────────────────────

    $firstEquipoId = (int) $equipos[0]['equipo_id'];
    $stmtInfo = $pdo->prepare("
        SELECT te.nombre AS tipo, ma.nombre AS marca, me.nombre_modelo AS modelo
        FROM equipos e
        JOIN modelos_equipo me ON me.id = e.modelo_equipo_id
        JOIN marcas ma         ON ma.id = me.marca_id
        JOIN tipos_equipo te   ON te.id = me.tipo_equipo_id
        WHERE e.id = ?
    ");
    $stmtInfo->execute([$firstEquipoId]);
    $equipoInfo = $stmtInfo->fetch();

    if (count($equipos) === 1) {
        $titulo = $equipoInfo
            ? "Falla en {$equipoInfo['tipo']}: {$equipoInfo['marca']} {$equipoInfo['modelo']}"
            : 'Llamado de soporte técnico';
    } else {
        $tipo = $equipoInfo['tipo'] ?? 'equipos';
        $titulo = 'Falla en ' . count($equipos) . ' equipos (' . $tipo . ')';
    }

    // ── Auto-generar descripción ──────────────────────────────────────────────

    $partes = [];
    foreach ($equipos as $i => $eq) {
        $prefix = count($equipos) > 1 ? 'Equipo ' . ($i + 1) . ': ' : '';
        $partes[] = $prefix . trim($eq['descripcion_problema']);
    }
    $descripcion = implode("\n\n", $partes);

    // ── Insertar en transacción ───────────────────────────────────────────────

    $pdo->beginTransaction();

    $ahora = date('Y-m-d H:i:s');

    $stmt = $pdo->prepare("
        INSERT INTO llamados
            (encargado_id, numero, titulo, descripcion, estado, fecha_apertura, created_at, updated_at)
        VALUES
            (:encargado_id, 'TEMP', :titulo, :descripcion, 'abierto', :fecha, :now1, :now2)
    ");
    $stmt->execute([
        ':encargado_id' => $encargadoId,
        ':titulo'      => $titulo,
        ':descripcion' => $descripcion,
        ':fecha'       => $ahora,
        ':now1'        => $ahora,
        ':now2'        => $ahora,
    ]);

    $llamadoId = (int) $pdo->lastInsertId();
    $numero    = 'LL-' . str_pad($llamadoId, 5, '0', STR_PAD_LEFT); // misma lógica que LlamadoService::generarNumero()

    $pdo->prepare("UPDATE llamados SET numero = ? WHERE id = ?")->execute([$numero, $llamadoId]);

    // Equipos
    $stmtEq = $pdo->prepare("
        INSERT INTO llamado_equipos
            (llamado_id, equipo_id, operativo, descripcion_problema, created_at, updated_at)
        VALUES
            (:llamado_id, :equipo_id, :operativo, :descripcion, :now1, :now2)
    ");
    foreach ($equipos as $eq) {
        $stmtEq->execute([
            ':llamado_id'  => $llamadoId,
            ':equipo_id'   => (int) $eq['equipo_id'],
            ':operativo'   => $eq['operativo'] ? 1 : 0,
            ':descripcion' => trim($eq['descripcion_problema']),
            ':now1'        => $ahora,
            ':now2'        => $ahora,
        ]);
    }

    // Historial inicial
    $pdo->prepare("
        INSERT INTO llamado_historial
            (llamado_id, usuario_id, tipo_evento, valor_anterior, valor_nuevo, comentario, created_at)
        VALUES
            (:llamado_id, NULL, 'creacion', '', 'abierto', '', :now)
    ")->execute([':llamado_id' => $llamadoId, ':now' => $ahora]);

    $pdo->commit();

    $_SESSION['csrf_token']  = bin2hex(random_bytes(32));
    $_SESSION['form_ready_at'] = 0; // fuerza recarga de página para envío siguiente

    echo json_encode([
        'success'    => true,
        'numero'     => $numero,
        'id'         => $llamadoId,
        'csrf_token' => $_SESSION['csrf_token'],
    ]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno. Intente nuevamente.']);
}

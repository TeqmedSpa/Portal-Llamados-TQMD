<?php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/rate_limit.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Cache-Control: no-store, no-cache, must-revalidate');

if (!rateLimitCheck('busqueda', 30, 60)) {
    http_response_code(429);
    echo json_encode([]);
    exit;
}

// Solo responde a sesiones que provienen del formulario (tienen token CSRF activo)
if (empty($_SESSION['csrf_token'])) {
    http_response_code(403);
    echo json_encode([]);
    exit;
}

$centroId = (int) ($_GET['centro_id'] ?? 0);

if ($centroId <= 0) {
    echo json_encode([]);
    exit;
}

try {
    $pdo = Database::get();

    $stmt = $pdo->prepare("
        SELECT
            e.id,
            e.numero_serie,
            e.numero_equipo_id,
            ma.nombre  AS marca,
            te.nombre  AS tipo,
            me.nombre_modelo AS modelo
        FROM equipo e
        JOIN modelo_equipo me ON me.id = e.modelo_equipo_id
        JOIN marca ma         ON ma.id = me.marca_id
        JOIN tipo_equipo te   ON te.id = me.tipo_equipo_id
        WHERE e.centro_medico_id = :centro_id
          AND e.activo = 1
          AND e.deleted_at IS NULL
        ORDER BY te.nombre, ma.nombre, me.nombre_modelo
    ");

    $stmt->execute([':centro_id' => $centroId]);
    $equipos = $stmt->fetchAll();

    // Detectar cuáles tienen llamado abierto
    $equipoIds = array_column($equipos, 'id');
    $conLlamadoAbierto = [];
    if (!empty($equipoIds)) {
        $placeholders = implode(',', array_fill(0, count($equipoIds), '?'));
        $stmtAbierto = $pdo->prepare("
            SELECT DISTINCT le.equipo_id
            FROM llamado_equipo le
            JOIN llamado l ON l.id = le.llamado_id
            WHERE le.equipo_id IN ($placeholders)
              AND l.estado NOT IN ('finalizado', 'cancelado')
              AND l.deleted_at IS NULL
        ");
        $stmtAbierto->execute($equipoIds);
        $conLlamadoAbierto = array_column($stmtAbierto->fetchAll(), 'equipo_id');
    }

    // Build display name for each equipo
    foreach ($equipos as &$e) {
        $label = "[{$e['numero_equipo_id']}] {$e['tipo']} {$e['marca']} {$e['modelo']}";
        $label .= " — Serie: {$e['numero_serie']}";
        $e['tiene_llamado_abierto'] = in_array($e['id'], $conLlamadoAbierto);
        if ($e['tiene_llamado_abierto']) {
            $label .= ' ⚠ llamado abierto';
        }
        $e['label'] = $label;
    }

    echo json_encode($equipos);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al buscar equipos']);
}

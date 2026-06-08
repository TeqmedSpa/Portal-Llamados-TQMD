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

$q        = trim($_GET['q'] ?? '');
$centroId = (int) ($_GET['centro_id'] ?? 0);

if (mb_strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

try {
    $pdo = Database::get();

    $centroFiltro = $centroId > 0 ? 'AND e.centro_medico_id = :centro_id' : '';

    $stmt = $pdo->prepare("
        SELECT
            e.id,
            e.primer_nombre,
            e.primer_apellido,
            e.cargo,
            e.centro_medico_id,
            cm.nombre AS centro_nombre
        FROM encargados e
        JOIN centros_medicos cm ON cm.id = e.centro_medico_id
        WHERE e.activo = 1
          AND cm.activo = 1
          AND e.deleted_at IS NULL
          AND cm.deleted_at IS NULL
          {$centroFiltro}
          AND (
              e.primer_nombre LIKE :q1
              OR e.primer_apellido LIKE :q2
              OR e.segundo_nombre LIKE :q3
              OR e.segundo_apellido LIKE :q4
              OR CONCAT(e.primer_nombre, ' ', e.primer_apellido) LIKE :q5
          )
        ORDER BY e.primer_apellido, e.primer_nombre
        LIMIT 8
    ");

    $like = "%{$q}%";
    $params = [':q1' => $like, ':q2' => $like, ':q3' => $like, ':q4' => $like, ':q5' => $like];
    if ($centroId > 0) $params[':centro_id'] = $centroId;
    $stmt->execute($params);
    $resultados = $stmt->fetchAll();

    echo json_encode($resultados);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al buscar encargados']);
}

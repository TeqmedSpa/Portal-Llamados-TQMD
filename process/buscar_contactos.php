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

$q        = trim($_GET['q'] ?? '');
$centroId = (int) ($_GET['centro_id'] ?? 0);

if (mb_strlen($q) < 2 || $centroId <= 0) {
    echo json_encode([]);
    exit;
}

try {
    $pdo = Database::get();

    $stmt = $pdo->prepare("
        SELECT
            e.id,
            e.primer_nombre,
            e.primer_apellido,
            e.cargo,
            e.centro_medico_id
        FROM encargado e
        JOIN centro_medico cm ON cm.id = e.centro_medico_id
        WHERE e.activo = 1
          AND cm.activo = 1
          AND e.deleted_at IS NULL
          AND cm.deleted_at IS NULL
          AND e.centro_medico_id = :centro_id
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
    $params = [':centro_id' => $centroId, ':q1' => $like, ':q2' => $like, ':q3' => $like, ':q4' => $like, ':q5' => $like];
    $stmt->execute($params);
    $resultados = $stmt->fetchAll();

    echo json_encode($resultados);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al buscar encargados']);
}

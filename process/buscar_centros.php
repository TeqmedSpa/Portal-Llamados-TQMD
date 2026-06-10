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

$q = trim($_GET['q'] ?? '');

if (mb_strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

try {
    $pdo = Database::get();

    $stmt = $pdo->prepare("
        SELECT DISTINCT cm.id, cm.nombre
        FROM centro_medico cm
        JOIN encargado c ON c.centro_medico_id = cm.id
            AND c.activo = 1
            AND c.deleted_at IS NULL
        WHERE cm.activo = 1
          AND cm.deleted_at IS NULL
          AND cm.nombre LIKE :q
        ORDER BY cm.nombre
        LIMIT 8
    ");

    $stmt->execute([':q' => "%{$q}%"]);
    echo json_encode($stmt->fetchAll());

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al buscar centros']);
}

<?php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

$q = trim($_GET['q'] ?? '');

if (mb_strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

try {
    $pdo = Database::get();

    $stmt = $pdo->prepare("
        SELECT
            c.id,
            c.nombre,
            c.apellido,
            c.cargo,
            c.telefono,
            c.email,
            c.centro_medico_id,
            cm.nombre AS centro_nombre
        FROM contactos c
        JOIN centros_medicos cm ON cm.id = c.centro_medico_id
        WHERE c.activo = 1
          AND cm.activo = 1
          AND (
              c.nombre LIKE :q
              OR c.apellido LIKE :q
              OR CONCAT(c.nombre, ' ', c.apellido) LIKE :q
          )
        ORDER BY c.nombre, c.apellido
        LIMIT 8
    ");

    $stmt->execute([':q' => "%{$q}%"]);
    $resultados = $stmt->fetchAll();

    echo json_encode($resultados);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al buscar contactos']);
}

<?php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

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
            e.codigo_interno,
            e.codigo_inventario,
            ma.nombre  AS marca,
            te.nombre  AS tipo,
            me.nombre_modelo AS modelo
        FROM equipos e
        JOIN modelos_equipo me ON me.id = e.modelo_equipo_id
        JOIN marcas ma         ON ma.id = me.marca_id
        JOIN tipos_equipo te   ON te.id = me.tipo_equipo_id
        WHERE e.centro_medico_id = :centro_id
          AND e.activo = 1
        ORDER BY te.nombre, ma.nombre, me.nombre_modelo
    ");

    $stmt->execute([':centro_id' => $centroId]);
    $equipos = $stmt->fetchAll();

    // Build display name for each equipo
    foreach ($equipos as &$e) {
        $label = "{$e['tipo']} {$e['marca']} {$e['modelo']}";
        if ($e['codigo_interno']) $label .= " ({$e['codigo_interno']})";
        $label .= " — Serie: {$e['numero_serie']}";
        $e['label'] = $label;
    }

    echo json_encode($equipos);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al buscar equipos']);
}

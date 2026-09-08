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

// ── Leer datos ──────────────────────────────────────────────────────────────
// Soporta FormData (con imágenes) y JSON puro (retrocompatibilidad)
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

if (isset($_POST['data'])) {
    // FormData: los datos vienen como JSON en el campo "data"
    $input = json_decode($_POST['data'], true);
} elseif (stripos($contentType, 'application/json') !== false) {
    // JSON puro (retrocompatibilidad)
    $rawBody = file_get_contents('php://input', false, null, 0, 65536);
    if (strlen($rawBody) >= 65536) {
        http_response_code(413);
        echo json_encode(['success' => false, 'message' => 'Solicitud demasiado grande.']);
        exit;
    }
    $input = json_decode($rawBody, true);
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Formato de solicitud no válido.']);
    exit;
}

if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos no válidos.']);
    exit;
}

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

// Debe coincidir con Modules\Core\Models\Encargado::CARGOS en la intranet
const CARGOS_VALIDOS = [
    'Coordinador/a',
    'Enfermera/o Coordinadora/o',
    'Encargado/a de Equipos',
    'Técnico/a en Enfermería',
    'Administrador/a',
];

$errors = [];

$centroId       = (int) ($input['centro_id'] ?? 0);
$encargadoId    = (int) ($input['encargado_id'] ?? 0);
$nuevoEncargado = $input['encargado_nuevo'] ?? null;
$equipos        = $input['equipos'] ?? [];

// ── Encargado: existente o registro nuevo ────────────────────────────────────
$encNuevoNombre = $encNuevoSegNombre = $encNuevoApellido = $encNuevoSegApellido = '';
$encNuevoCargo  = $encNuevoTelefono  = $encNuevoEmail     = '';

if (is_array($nuevoEncargado)) {
    $encNuevoNombre      = trim($nuevoEncargado['primer_nombre'] ?? '');
    $encNuevoSegNombre   = trim($nuevoEncargado['segundo_nombre'] ?? '');
    $encNuevoApellido    = trim($nuevoEncargado['primer_apellido'] ?? '');
    $encNuevoSegApellido = trim($nuevoEncargado['segundo_apellido'] ?? '');
    $encNuevoCargo       = trim($nuevoEncargado['cargo'] ?? '');
    $encNuevoTelefono    = preg_replace('/\D/', '', (string) ($nuevoEncargado['telefono'] ?? ''));
    $encNuevoEmail       = trim($nuevoEncargado['email'] ?? '');

    if ($centroId <= 0)                                              $errors[] = 'Selecciona tu clínica o centro médico.';
    if ($encNuevoNombre === '' || mb_strlen($encNuevoNombre) > 150)  $errors[] = 'Ingresa un nombre válido.';
    if (mb_strlen($encNuevoSegNombre) > 150)                         $errors[] = 'Segundo nombre demasiado largo.';
    if ($encNuevoApellido === '' || mb_strlen($encNuevoApellido) > 150)    $errors[] = 'Ingresa un apellido válido.';
    if ($encNuevoSegApellido === '' || mb_strlen($encNuevoSegApellido) > 150) $errors[] = 'Ingresa un segundo apellido válido.';
    if (!in_array($encNuevoCargo, CARGOS_VALIDOS, true))             $errors[] = 'Selecciona un cargo válido.';
    if (!preg_match('/^\d{9}$/', $encNuevoTelefono))                 $errors[] = 'Ingresa un teléfono válido de 9 dígitos.';
    if (!filter_var($encNuevoEmail, FILTER_VALIDATE_EMAIL) || mb_strlen($encNuevoEmail) > 255) $errors[] = 'Ingresa un email válido.';
} elseif ($encargadoId <= 0) {
    $errors[] = 'Debes identificarte como encargado.';
}

if (empty($equipos))  $errors[] = 'Debes agregar al menos un equipo.';
if (count($equipos) > 10) $errors[] = 'Máximo 10 equipos por llamado.';

$equipoIdsEnviados = array_map(fn($eq) => (int) ($eq['equipo_id'] ?? 0), $equipos);
if (count($equipoIdsEnviados) !== count(array_unique($equipoIdsEnviados))) {
    $errors[] = 'No puedes agregar el mismo equipo más de una vez en el mismo llamado.';
}

$momentosValidos = [
    'En preparación', 'En diálisis', 'En desinfección', 'Al encender el equipo',
    'Durante uso en paciente', 'Durante procedimiento', 'Durante examen / procedimiento', 'Otros',
];

foreach ($equipos as $i => $eq) {
    $num     = $i + 1;
    $falla   = trim($eq['descripcion_falla'] ?? '');
    $momento = isset($eq['momento']) && $eq['momento'] !== '' ? (string) $eq['momento'] : null;
    if (empty($eq['equipo_id']))                                        $errors[] = "Equipo #{$num}: selecciona un equipo.";
    if (mb_strlen($falla) < 5)                                         $errors[] = "Equipo #{$num}: describe el problema (mínimo 5 caracteres).";
    if (mb_strlen($falla) > 500)                                       $errors[] = "Equipo #{$num}: descripción demasiado larga (máximo 500 caracteres).";
    if (mb_strlen(trim($eq['comentarios_extra'] ?? '')) > 500)         $errors[] = "Equipo #{$num}: comentarios demasiado largos (máximo 500 caracteres).";
    if (!isset($eq['operativo']) || !is_bool($eq['operativo']))        $errors[] = "Equipo #{$num}: indica si el equipo está operativo o no.";
    if ($momento === null)                                              $errors[] = "Equipo #{$num}: indica el momento en que se presentó la falla.";
    elseif (!in_array($momento, $momentosValidos, true))               $errors[] = "Equipo #{$num}: momento no válido.";
}

// ── Validar imágenes ─────────────────────────────────────────────────────────
$imagenesSubidas   = [];
$tiposPermitidos   = ['image/jpeg', 'image/png', 'image/webp'];
$extPermitidas     = ['jpg', 'jpeg', 'png', 'webp'];
$maxTamanoImagen   = 5 * 1024 * 1024; // 5 MB
$maxImagenes       = 3;

if (!empty($_FILES['imagenes']['name'][0])) {
    $totalImagenes = count($_FILES['imagenes']['name']);
    if ($totalImagenes > $maxImagenes) {
        $errors[] = "Máximo {$maxImagenes} imágenes por llamado.";
    } else {
        for ($i = 0; $i < $totalImagenes; $i++) {
            $nombre   = $_FILES['imagenes']['name'][$i];
            $tmpPath  = $_FILES['imagenes']['tmp_name'][$i];
            $tamano   = $_FILES['imagenes']['size'][$i];
            $error    = $_FILES['imagenes']['error'][$i];

            if ($error !== UPLOAD_ERR_OK) {
                $errors[] = "Error al subir la imagen \"{$nombre}\".";
                continue;
            }

            // Validar tamaño
            if ($tamano > $maxTamanoImagen) {
                $errors[] = "\"{$nombre}\" excede los 5 MB.";
                continue;
            }

            // Validar tipo MIME real (no confiar en el header del cliente)
            $finfo    = new finfo(FILEINFO_MIME_TYPE);
            $mimeReal = $finfo->file($tmpPath);
            if (!in_array($mimeReal, $tiposPermitidos, true)) {
                $errors[] = "\"{$nombre}\" no es un formato de imagen válido.";
                continue;
            }

            // Validar extensión
            $ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
            if (!in_array($ext, $extPermitidas, true)) {
                $errors[] = "\"{$nombre}\": extensión no permitida.";
                continue;
            }

            $imagenesSubidas[] = [
                'tmp_path'        => $tmpPath,
                'nombre_original' => mb_substr(basename($nombre), 0, 255),
                'mime_type'       => $mimeReal,
                'tamano_bytes'    => $tamano,
                'ext'             => $ext === 'jpeg' ? 'jpg' : $ext,
            ];
        }
    }
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// ── Verificar que el contacto existe (o que el centro del registro nuevo es válido) ──

try {
    $pdo = Database::get();

    if (is_array($nuevoEncargado)) {
        $stmt = $pdo->prepare("SELECT id FROM centro_medico WHERE id = ? AND activo = 1 AND deleted_at IS NULL LIMIT 1");
        $stmt->execute([$centroId]);
        if (!$stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Centro médico no válido.']);
            exit;
        }
        $centroPropietario = $centroId;
    } else {
        $stmt = $pdo->prepare("SELECT id, centro_medico_id FROM encargado WHERE id = ? AND activo = 1 AND deleted_at IS NULL LIMIT 1");
        $stmt->execute([$encargadoId]);
        $encargadoRow = $stmt->fetch();
        if (!$encargadoRow) {
            echo json_encode(['success' => false, 'message' => 'Encargado no encontrado.']);
            exit;
        }
        $centroPropietario = (int) $encargadoRow['centro_medico_id'];
    }

    // ── Verificar que ningún equipo tenga llamado abierto ────────────────────

    $equipoIds   = array_map(fn($eq) => (int) $eq['equipo_id'], $equipos);
    $placeholders = implode(',', array_fill(0, count($equipoIds), '?'));

    $stmt = $pdo->prepare("
        SELECT e.id, e.numero_serie, me.nombre_modelo, ma.nombre AS marca
        FROM llamado_equipo le
        JOIN llamado l  ON l.id = le.llamado_id
        JOIN equipo e   ON e.id = le.equipo_id
        JOIN modelo_equipo me ON me.id = e.modelo_equipo_id
        JOIN marca ma   ON ma.id = me.marca_id
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
        SELECT COUNT(*) AS total FROM equipo
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

    // ── Insertar en transacción ───────────────────────────────────────────────

    $pdo->beginTransaction();

    $ahora = date('Y-m-d H:i:s');

    if (is_array($nuevoEncargado)) {
        $stmtEnc = $pdo->prepare("
            INSERT INTO encargado
                (centro_medico_id, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, cargo, telefono, email, activo, created_at, updated_at)
            VALUES
                (:centro_id, :pn, :sn, :pa, :sa, :cargo, :tel, :email, 1, :now1, :now2)
        ");
        $stmtEnc->execute([
            ':centro_id' => $centroPropietario,
            ':pn'        => $encNuevoNombre,
            ':sn'        => $encNuevoSegNombre !== '' ? $encNuevoSegNombre : null,
            ':pa'        => $encNuevoApellido,
            ':sa'        => $encNuevoSegApellido,
            ':cargo'     => $encNuevoCargo,
            ':tel'       => $encNuevoTelefono,
            ':email'     => $encNuevoEmail,
            ':now1'      => $ahora,
            ':now2'      => $ahora,
        ]);
        $encargadoId = (int) $pdo->lastInsertId();
    }

    $stmt = $pdo->prepare("
        INSERT INTO llamado
            (encargado_id, numero, estado, fecha_apertura, created_at, updated_at)
        VALUES
            (:encargado_id, 'TEMP', 'abierto', :fecha, :now1, :now2)
    ");
    $stmt->execute([
        ':encargado_id' => $encargadoId,
        ':fecha'        => $ahora,
        ':now1'         => $ahora,
        ':now2'         => $ahora,
    ]);

    $llamadoId = (int) $pdo->lastInsertId();
    $numero    = 'LL-' . str_pad($llamadoId, 5, '0', STR_PAD_LEFT); // misma lógica que LlamadoService::generarNumero()

    $pdo->prepare("UPDATE llamado SET numero = ? WHERE id = ?")->execute([$numero, $llamadoId]);

    // Equipos
    $stmtEq = $pdo->prepare("
        INSERT INTO llamado_equipo
            (llamado_id, equipo_id, operativo, descripcion_falla, momento, comentarios_extra, created_at, updated_at)
        VALUES
            (:llamado_id, :equipo_id, :operativo, :falla, :momento, :comentarios, :now1, :now2)
    ");
    foreach ($equipos as $eq) {
        $stmtEq->execute([
            ':llamado_id'   => $llamadoId,
            ':equipo_id'    => (int) $eq['equipo_id'],
            ':operativo'    => $eq['operativo'] ? 1 : 0,
            ':falla'        => trim($eq['descripcion_falla'] ?? ''),
            ':momento'      => $eq['momento'] ?: null,
            ':comentarios'  => ($eq['comentarios_extra'] ?? '') !== '' ? trim($eq['comentarios_extra']) : null,
            ':now1'         => $ahora,
            ':now2'         => $ahora,
        ]);
    }

    // Historial inicial
    $pdo->prepare("
        INSERT INTO llamado_historial
            (llamado_id, usuario_id, tipo_evento, valor_anterior, valor_nuevo, comentario, created_at)
        VALUES
            (:llamado_id, NULL, 'creacion', '', 'abierto', '', :now)
    ")->execute([':llamado_id' => $llamadoId, ':now' => $ahora]);

    // ── Guardar imágenes ─────────────────────────────────────────────────────
    if (!empty($imagenesSubidas)) {
        $uploadDir = __DIR__ . '/../uploads/llamados/' . $llamadoId;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $stmtImg = $pdo->prepare("
            INSERT INTO llamado_imagen
                (llamado_id, nombre_archivo, nombre_original, mime_type, tamano_bytes, created_at)
            VALUES
                (:llamado_id, :nombre_archivo, :nombre_original, :mime_type, :tamano_bytes, :now)
        ");

        foreach ($imagenesSubidas as $img) {
            // Nombre único basado en uniqid + random
            $nombreUnico = uniqid('img_', true) . '.' . $img['ext'];

            $destino = $uploadDir . '/' . $nombreUnico;
            if (!move_uploaded_file($img['tmp_path'], $destino)) {
                error_log("[TQMD-PORTAL] Error al guardar imagen: {$img['nombre_original']}");
                continue;
            }

            $stmtImg->execute([
                ':llamado_id'      => $llamadoId,
                ':nombre_archivo'  => $nombreUnico,
                ':nombre_original' => $img['nombre_original'],
                ':mime_type'       => $img['mime_type'],
                ':tamano_bytes'    => $img['tamano_bytes'],
                ':now'             => $ahora,
            ]);
        }
    }

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
    // Limpiar archivos subidos si hubo error
    if (!empty($llamadoId) && is_dir(__DIR__ . '/../uploads/llamados/' . $llamadoId)) {
        $dirLimpiar = __DIR__ . '/../uploads/llamados/' . $llamadoId;
        foreach (glob($dirLimpiar . '/*') as $f) @unlink($f);
        @rmdir($dirLimpiar);
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno. Intente nuevamente.']);
}

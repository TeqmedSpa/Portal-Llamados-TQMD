<?php

/**
 * Rate limiter de archivo simple para el portal.
 * Devuelve true si se permite el intento, false si se bloqueó.
 *
 * @param string $accion          Identificador de la acción (ej: 'llamado', 'busqueda')
 * @param int    $maxIntentos     Máximo número de intentos permitidos en la ventana
 * @param int    $ventanaSegundos Tamaño de la ventana de tiempo en segundos
 */
function rateLimitCheck(string $accion, int $maxIntentos = 5, int $ventanaSegundos = 3600): bool
{
    $ip      = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $clave   = preg_replace('/[^a-zA-Z0-9_\-.]/', '_', $accion . '_' . $ip);
    $dir     = __DIR__ . '/../storage/rate_limits';
    $archivo = $dir . '/' . $clave . '.json';
    $ahora   = time();

    if (!is_dir($dir) && !mkdir($dir, 0750, true) && !is_dir($dir)) {
        error_log("[TQMD-PORTAL] rate_limit: no se pudo crear directorio $dir — rate limiting desactivado");
        return true;
    }

    $fh = fopen($archivo, 'c+');
    if (!$fh) {
        error_log("[TQMD-PORTAL] rate_limit: no se pudo abrir $archivo — rate limiting desactivado");
        return true;
    }

    if (!flock($fh, LOCK_EX)) {
        fclose($fh);
        return true;
    }

    $contenido = stream_get_contents($fh);
    $intentos  = json_decode($contenido ?: '[]', true) ?? [];
    $intentos  = array_values(array_filter($intentos, fn($t) => ($ahora - $t) < $ventanaSegundos));

    if (count($intentos) >= $maxIntentos) {
        flock($fh, LOCK_UN);
        fclose($fh);
        return false;
    }

    $intentos[] = $ahora;
    ftruncate($fh, 0);
    rewind($fh);
    fwrite($fh, json_encode($intentos));
    flock($fh, LOCK_UN);
    fclose($fh);
    return true;
}

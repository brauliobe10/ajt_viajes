<?php

declare(strict_types=1);

// Funcion del archivo: Normaliza, optimiza y valida URLs de imagenes externas.
namespace App\Services;

/**
 * Centraliza la validación y normalización de imágenes remotas usadas por paquetes.
 */
class ImageUrlService
{
    /**
     * Optimiza imagen para catálogo (cards pequeñas): 800px de ancho.
     */
    public static function catalogOptimize(string $url): string
    {
        return self::optimize($url, 800, 75);
    }

    /**
     * Ajusta URLs conocidas de imágenes para mantener una carga ligera y consistente.
     */
    public static function optimize(string $url, int $width = 1200, int $quality = 80): string
    {
        $url = trim($url);
        if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        $parts = parse_url($url);
        if (empty($parts['host']) || stripos($parts['host'], 'images.unsplash.com') === false) {
            return $url;
        }

        parse_str($parts['query'] ?? '', $query);
        $query['auto'] = 'format';
        $query['fit'] = 'crop';
        $query['w'] = (string)$width;
        $query['q'] = (string)$quality;

        $scheme = $parts['scheme'] ?? 'https';
        $path = $parts['path'] ?? '';

        return $scheme . '://' . $parts['host'] . $path . '?' . http_build_query($query);
    }

    /**
     * Genera una clave estable de imagen sin parámetros de tamaño/calidad.
     */
    public static function imageKey(string $url): string
    {
        $url = trim($url);
        $parts = parse_url($url);
        if (empty($parts['host']) || empty($parts['path'])) {
            return strtolower($url);
        }

        return strtolower($parts['host'] . $parts['path']);
    }

    /**
     * Valida que la URL sea una imagen remota accesible con respuesta HTTP exitosa.
     */
    public static function isValid(string $url): bool
    {
        $url = trim($url);
        if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $scheme = strtolower((string)parse_url($url, PHP_URL_SCHEME));
        if (!in_array($scheme, ['http', 'https'], true)) {
            return false;
        }

        if (function_exists('curl_init')) {
            return self::validateWithCurl($url);
        }

        return self::validateWithHeaders($url);
    }

    // Funcion: Valida una URL de imagen usando cURL.
    private static function validateWithCurl(string $url): bool
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_NOBODY => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 4,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_TIMEOUT => 4,
            CURLOPT_USERAGENT => 'ViajesAJT/1.0',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $contentType = (string)curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        if ($status >= 200 && $status < 300 && stripos($contentType, 'image/') === 0) {
            return true;
        }

        // Algunos proveedores bloquean HEAD; intentamos una lectura mínima con GET.
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 4,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_USERAGENT => 'ViajesAJT/1.0',
            CURLOPT_RANGE => '0-2048',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $contentType = (string)curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        return $status >= 200 && $status < 300 && stripos($contentType, 'image/') === 0;
    }

    // Funcion: Valida una URL de imagen usando cabeceras HTTP.
    private static function validateWithHeaders(string $url): bool
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'HEAD',
                'timeout' => 3,
                'ignore_errors' => true,
                'header' => "User-Agent: ViajesAJT/1.0\r\n",
            ],
        ]);

        $headers = @get_headers($url, true, $context);
        if (!$headers || empty($headers[0])) {
            return false;
        }

        $statusLine = is_array($headers[0]) ? end($headers[0]) : $headers[0];
        $contentType = $headers['Content-Type'] ?? $headers['content-type'] ?? '';
        if (is_array($contentType)) {
            $contentType = end($contentType);
        }

        return preg_match('/\s2\d\d\s/', (string)$statusLine) === 1
            && stripos((string)$contentType, 'image/') === 0;
    }
}

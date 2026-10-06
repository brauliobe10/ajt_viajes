<?php

declare(strict_types=1);

namespace App\Helper;

/**
 * Clase SecurityHeaders
 * Aplica cabeceras de seguridad HTTP para proteger la aplicación
 * contra XSS, clickjacking, MIME sniffing, y otras vulnerabilidades comunes.
 */
class SecurityHeaders
{
    private static ?string $nonce = null;

    /**
     * Genera o retorna el nonce de la petición actual.
     *
     * @return string
     */
    public static function getNonce(): string
    {
        if (self::$nonce === null) {
            self::$nonce = base64_encode(random_bytes(16));
        }
        return self::$nonce;
    }

    /**
     * Aplica todas las cabeceras de seguridad a la respuesta HTTP.
     * Debe llamarse al inicio de cada petición, antes de enviar cualquier salida.
     *
     * @return void
     */
    public static function apply(): void
    {
        // Evitar que el navegador detecte automáticamente el tipo MIME
        header('X-Content-Type-Options: nosniff');

        // Prevenir clickjacking
        header('X-Frame-Options: SAMEORIGIN');

        // Protección XSS en navegadores antiguos
        header('X-XSS-Protection: 1; mode=block');

        // Política de Referer: solo enviar origen completo para el mismo origen
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // HSTS (HTTP Strict Transport Security) — solo si hay HTTPS
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }

        // Obtener el nonce para la petición actual
        $nonce = self::getNonce();

        // Content Security Policy (CSP) — política restrictiva pero funcional
        // Permite recursos del mismo origen, Google Fonts, Font Awesome, y scripts con nonce
        $csp = [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}' https://code.jquery.com https://cdn.jsdelivr.net https://stackpath.bootstrapcdn.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://stackpath.bootstrapcdn.com",
            "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net",
            "img-src 'self' data: https:",
            "connect-src 'self' https://*.tile.openstreetmap.org",
            "frame-src 'self' https://www.openstreetmap.org",
            "frame-ancestors 'self'",
            "form-action 'self'",
            "base-uri 'self'",
        ];

        header('Content-Security-Policy: ' . implode('; ', $csp));

        // Deshabilitar la capacidad de usar la API de Permissions para evitar fingerprinting
        header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()');
    }

}

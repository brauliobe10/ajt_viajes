<?php

declare(strict_types=1);

namespace App\Helper;

/**
 * Clase Csrf
 * Generación y verificación de tokens CSRF.
 * Utiliza hash_equals para comparación segura de tokens.
 */
class Csrf
{
    /**
     * Genera o recupera el token CSRF de la sesión actual.
     *
     * @return string Token CSRF
     */
    public static function token(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Genera un campo oculto HTML con el token CSRF.
     *
     * @return string HTML del campo oculto
     */
    public static function field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . self::token() . '">';
    }

    /**
     * Verifica que el token CSRF recibido coincida con el de sesión.
     *
     * @param string $token Token recibido del formulario
     * @return bool
     */
    public static function verify(string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $storedToken = $_SESSION['csrf_token'] ?? '';

        if ($storedToken === '') {
            return false;
        }

        return hash_equals($storedToken, $token);
    }

    /**
     * Valida el token CSRF desde POST y redirige si es inválido.
     * Utilizada en controladores para protección en una línea.
     *
     * @param string $redirect Ruta de redirección si falla
     * @return void
     */
    public static function validate(string $redirect = '/'): void
    {
        $token = $_POST['csrf_token'] ?? '';

        if (!self::verify($token)) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['error'] = 'Token CSRF inválido. Por favor, intente nuevamente.';
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . $redirect);
            exit();
        }
    }

    /**
     * @deprecated Usar verify(string $token)
     */
    public static function verifyToken(?string $token): bool
    {
        return self::verify($token ?? '');
    }

    /**
     * @deprecated Usar field()
     */
    public static function insertInput(): string
    {
        return self::field();
    }
}

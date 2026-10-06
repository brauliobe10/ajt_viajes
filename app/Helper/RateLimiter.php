<?php

declare(strict_types=1);

namespace App\Helper;

use App\Config\Database;
use PDOException;

/**
 * Clase RateLimiter
 * Control de acceso por tasa de peticiones (rate limiting) vía MySQL.
 *
 * Previene abusos en login, registro, formularios de contacto,
 * reclamaciones y endpoints Ajax sensibles.
 */
class RateLimiter
{
    private string $identifier;
    private int $maxAttempts;
    private int $windowMinutes;

    /**
     * @param string $identifier Identificador único (ej. "login:127.0.0.1" o "login:user@email.com")
     * @param int $maxAttempts Número máximo de intentos permitidos
     * @param int $windowMinutes Ventana de tiempo en minutos
     */
    public function __construct(string $identifier, int $maxAttempts = 10, int $windowMinutes = 15)
    {
        $this->identifier = $identifier;
        $this->maxAttempts = $maxAttempts;
        $this->windowMinutes = $windowMinutes;
    }

    /**
     * Verifica si se ha excedido el límite de intentos.
     *
     * @return bool true si el límite ha sido excedido
     */
    public function isLimited(): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare(
                "SELECT COUNT(*) FROM rate_limits
                 WHERE identifier = :identifier
                 AND created_at > DATE_SUB(NOW(), INTERVAL :minutes MINUTE)"
            );
            $stmt->execute([
                ':identifier' => $this->identifier,
                ':minutes' => $this->windowMinutes,
            ]);
            $attempts = (int)$stmt->fetchColumn();

            return $attempts >= $this->maxAttempts;
        } catch (PDOException $e) {
            error_log("RateLimiter::isLimited — DB error: " . $e->getMessage());
            // Si la tabla no existe, permitir el acceso (fail open)
            return false;
        }
    }

    /**
     * Registra un intento en la base de datos.
     *
     * @return void
     */
    public function hit(): void
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare(
                "INSERT INTO rate_limits (identifier, ip_address, created_at)
                 VALUES (:identifier, :ip, NOW())"
            );
            $stmt->execute([
                ':identifier' => $this->identifier,
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            ]);
        } catch (PDOException $e) {
            error_log("RateLimiter::hit — DB error: " . $e->getMessage());
        }
    }

    /**
     * Verifica y registra un intento en una sola llamada.
     * Si el límite fue excedido, envía respuesta HTTP 429.
     *
     * @param string $action Nombre descriptivo de la acción para el mensaje
     * @return bool true si puede continuar, false si está limitado (y envió 429)
     */
    public function check(string $action = 'acción', string $redirect = '/auth/login'): bool
    {
        if ($this->isLimited()) {
            http_response_code(429);
            header('Retry-After: ' . ($this->windowMinutes * 60));

            $message = "Demasiados intentos para esta {$action}. Por favor, intente nuevamente en {$this->windowMinutes} minutos.";

            // Verificar si la petición espera JSON
            if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') ||
                str_contains($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '', 'XMLHttpRequest')) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(['error' => $message, 'retry_after' => $this->windowMinutes * 60]);
                error_log("Rate limit excedido: {$this->identifier} — {$action}");
                exit();
            }

            // Para peticiones normales, guardar un mensaje visible y redirigir a la vista adecuada.
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['error'] = $message;
            $_SESSION['toast'] = [
                'type' => 'warning',
                'title' => 'Protección de cuenta',
                'text' => $message,
            ];
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . $redirect);

            error_log("Rate limit excedido: {$this->identifier} — {$action}");
            exit();
        }

        $this->hit();
        return true;
    }

    /**
     * Returns the number of remaining attempts before hitting the limit.
     *
     * @return int Remaining attempts (0 when already limited)
     */
    public function remainingAttempts(): int
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare(
                "SELECT COUNT(*) FROM rate_limits
                 WHERE identifier = :identifier
                 AND created_at > DATE_SUB(NOW(), INTERVAL :minutes MINUTE)"
            );
            $stmt->execute([
                ':identifier' => $this->identifier,
                ':minutes' => $this->windowMinutes,
            ]);
            $attempts = (int)$stmt->fetchColumn();

            return max(0, $this->maxAttempts - $attempts);
        } catch (PDOException $e) {
            error_log("RateLimiter::remainingAttempts — DB error: " . $e->getMessage());
            return $this->maxAttempts;
        }
    }

    /**
     * Limpia los intentos del identificador actual.
     *
     * @return void
     */
    public function clear(): void
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("DELETE FROM rate_limits WHERE identifier = :identifier");
            $stmt->execute([':identifier' => $this->identifier]);
        } catch (PDOException $e) {
            error_log("RateLimiter::clear — DB error: " . $e->getMessage());
        }
    }}

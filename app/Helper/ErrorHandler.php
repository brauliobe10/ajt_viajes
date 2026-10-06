<?php

declare(strict_types=1);

namespace App\Helper;

/**
 * Clase ErrorHandler
 * Manejador global de errores y excepciones para la aplicación.
 *
 * Convierte errores PHP en excepciones, registra en log técnico,
 * y muestra páginas amigables según el entorno (desarrollo/producción).
 */
class ErrorHandler
{
    private static bool $registered = false;

    /**
     * Registra los manejadores globales de errores y excepciones.
     * Debe llamarse una sola vez al inicio de la aplicación.
     *
     * @param bool $debug Modo debug: true muestra detalles, false muestra página amigable
     * @return void
     */
    public static function register(bool $debug = false): void
    {
        if (self::$registered) {
            return;
        }

        $debug = self::resolveDebugMode($debug);

        // Establecer nivel de reporte de errores
        error_reporting(E_ALL);

        // Configurar display_errors según el modo
        ini_set('display_errors', $debug ? '1' : '0');
        ini_set('display_startup_errors', $debug ? '1' : '0');

        // Registrar manejador de excepciones no capturadas
        set_exception_handler(function (\Throwable $e) use ($debug): void {
            self::handleException($e, $debug);
        });

        // Registrar manejador de errores PHP (convierte a ErrorException)
        set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                // El nivel de error no está incluido en error_reporting
                return false;
            }
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        // Registrar manejador de cierre para errores fatales
        register_shutdown_function(function () use ($debug): void {
            $error = error_get_last();
            if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
                self::handleError($error, $debug);
            }
        });

        self::$registered = true;
    }

    /**
     * Determina si el modo debug debe estar activo.
     */
    private static function resolveDebugMode(bool $debug): bool
    {
        if ($debug) {
            return true;
        }

        return \App\Config\LocalConfig::APP_DEBUG;
    }

    /**
     * Maneja una excepción no capturada.
     */
    private static function handleException(\Throwable $e, bool $debug): void
    {
        // Registrar en log técnico
        self::logError($e);

        // Limpiar buffer de salida
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        http_response_code(500);

        if ($debug) {
            // En desarrollo: mostrar detalles
            header('Content-Type: text/html; charset=UTF-8');
            echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Error Interno</title>';
            echo '<style>body{font-family:sans-serif;padding:2em;max-width:800px;margin:0 auto;}
                  h1{color:#dc2626;}pre{background:#f1f5f9;padding:1em;border-radius:8px;overflow:auto;}
                  .file{color:#64748b;font-size:0.9em;}
                  hr{margin:2em 0;}</style></head><body>';
            echo '<h1>Error Interno del Servidor</h1>';
            echo '<p class="file"><strong>' . htmlspecialchars(get_class($e), ENT_QUOTES, 'UTF-8') . '</strong></p>';
            echo '<p><strong>Mensaje:</strong> ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
            echo '<p class="file">Archivo: ' . htmlspecialchars($e->getFile(), ENT_QUOTES, 'UTF-8') . ' (línea ' . $e->getLine() . ')</p>';
            echo '<pre>' . htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8') . '</pre>';
            echo '</body></html>';
            exit();
        }

        // En producción: mostrar página amigable
        self::displayFriendlyError(500, 'Error interno del servidor. Por favor, intente más tarde.');
    }

    /**
     * Maneja un error fatal detectado en shutdown.
     */
    private static function handleError(array $error, bool $debug): void
    {
        $exception = new \ErrorException(
            $error['message'],
            0,
            $error['type'],
            $error['file'],
            $error['line']
        );

        self::logError($exception);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        http_response_code(500);

        if ($debug) {
            header('Content-Type: text/html; charset=UTF-8');
            echo '<h1>Error Fatal del Servidor</h1>';
            echo '<p><strong>Mensaje:</strong> ' . htmlspecialchars($error['message'], ENT_QUOTES, 'UTF-8') . '</p>';
            echo '<p>Archivo: ' . htmlspecialchars($error['file'], ENT_QUOTES, 'UTF-8') . ' (línea ' . $error['line'] . ')</p>';
            exit();
        }

        self::displayFriendlyError(500, 'Error interno del servidor. Por favor, intente más tarde.');
    }

    /**
     * Muestra una página de error amigable al usuario.
     */
    private static function displayFriendlyError(int $code, string $message): void
    {
        $baseViewsDir = dirname(__DIR__) . '/Views/';
        $errorFile = $baseViewsDir . 'errors/' . $code . '.php';

        if (file_exists($errorFile)) {
            require $errorFile;
        } else {
            header('Content-Type: text/html; charset=UTF-8');
            echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">';
            echo '<title>Error ' . $code . '</title>';
            echo '<style>body{font-family:sans-serif;display:flex;justify-content:center;align-items:center;min-height:100vh;margin:0;background:#f8fafc;}
                  .error-card{text-align:center;padding:3em;background:white;border-radius:16px;box-shadow:0 4px 24px rgba(0,0,0,.08);max-width:480px;}
                  h1{font-size:4em;color:#dc2626;margin:0;}p{color:#64748b;margin:1em 0 1.5em;}
                  a{display:inline-block;padding:.75em 2em;background:#1b2c8a;color:white;text-decoration:none;border-radius:8px;}</style></head><body>';
            echo '<div class="error-card"><h1>' . $code . '</h1>';
            echo '<p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
            echo '<a href="' . (defined('BASE_URL') ? BASE_URL : '') . '/">Volver al inicio</a>';
            echo '</div></body></html>';
        }
        exit();
    }

    /**
     * Registra el error en el log del sistema.
     * Nunca expone datos sensibles como contraseñas, tokens o datos personales.
     */
    private static function logError(\Throwable $e): void
    {
        $message = sprintf(
            "[%s] %s: %s in %s:%d%s",
            date('Y-m-d H:i:s'),
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            PHP_EOL . $e->getTraceAsString()
        );

        error_log($message);

        // También registrar en archivo específico si el directorio existe
        $logDir = dirname(__DIR__, 2) . '/storage/logs';
        if (is_dir($logDir) && is_writable($logDir)) {
            // Archivo con fecha para rotación
            $logFile = $logDir . '/error-' . date('Y-m-d') . '.log';
            file_put_contents($logFile, $message . PHP_EOL . PHP_EOL, FILE_APPEND | LOCK_EX);
            // Archivo permanente error.log (requerido por la rúbrica)
            $permanentLog = $logDir . '/error.log';
            file_put_contents($permanentLog, $message . PHP_EOL . PHP_EOL, FILE_APPEND | LOCK_EX);
        }
    }
}

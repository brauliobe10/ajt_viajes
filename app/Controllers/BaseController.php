<?php

declare(strict_types=1);

namespace App\Controllers;

/**
 * Clase BaseController
 * Controlador base que centraliza autenticación, roles, rendering,
 * mensajes flash, respuestas JSON y verificación de método HTTP.
 *
 * Todos los controladores del sistema deberían extender esta clase
 * para evitar duplicación de lógica de seguridad y presentación.
 */
class BaseController
{
    /**
     * Verifica que el método HTTP sea el esperado.
     * Redirige si no coincide.
     *
     * @param string $method Método HTTP esperado (GET, POST, etc.)
     * @param string $redirect Ruta de redirección si falla
     * @return void
     */
    protected function requireMethod(string $method, string $redirect = '/'): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method)) {
            $this->redirect($redirect);
        }
    }

    /**
     * Establece un mensaje flash en sesión.
     *
     * @param string $type Tipo: 'success', 'error', 'warning', 'info'
     * @param string $message Contenido del mensaje
     * @return void
     */
    protected function setFlash(string $type, string $message): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION[$type] = $message;
    }

    /**
     * Redirige a una ruta relativa a BASE_URL.
     *
     * @param string $path Ruta (ej. /admin/dashboard)
     * @return void
     */
    protected function redirect(string $path): never
    {
        $url = (defined('BASE_URL') ? BASE_URL : '') . $path;
        header('Location: ' . $url);
        exit();
    }

    /**
     * Renderiza una vista con layout.
     *
     * @param string $view Ruta relativa a app/Views/ (sin extensión .php)
     * @param array $data Variables para la vista
     * @param string $layout Layout a usar: 'main' o 'admin'
     * @return void
     */
    protected function render(string $view, array $data = [], string $layout = 'main'): void
    {
        $baseViewsDir = dirname(__DIR__) . '/Views/';
        $viewFile = $baseViewsDir . $view . '.php';

        // Extraer variables para la vista
        extract($data);

        if ($layout === 'admin') {
            require_once $baseViewsDir . 'layouts/admin_header.php';
            require_once $viewFile;
            require_once $baseViewsDir . 'layouts/admin_footer.php';
        } else {
            require_once $baseViewsDir . 'layouts/header.php';
            require_once $viewFile;
            require_once $baseViewsDir . 'layouts/footer.php';
        }
    }

    /**
     * Envía una respuesta JSON.
     *
     * @param mixed $data Datos a serializar
     * @param int $statusCode Código HTTP
     * @return void
     */
    protected function json(mixed $data, int $statusCode = 200): never
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit();
    }

    /**
     * Devuelve la ruta del dashboard según el rol del usuario.
     *
     * @param int $roleId ID del rol
     * @return string
     */
    protected function getDashboardPath(int $roleId): string
    {
        return $roleId === 2 ? '/admin/dashboard' : '/profile/mi-perfil';
    }

}

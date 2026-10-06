<?php

declare(strict_types=1);

// Funcion del archivo: Gestiona reclamos del cliente y su atenci?n desde administracion.
namespace App\Controllers;

use App\Models\Reclamacion;
use App\Models\Reserva;
use Exception;

/**
 * Clase ReclamacionController
 * Gestiona el Libro de Reclamaciones (Ley 29571) — público y administración.
 */
class ReclamacionController
{
    /**
     * Muestra la página de reclamaciones con formulario e historial del usuario.
     *
     * @return void
     */
    public function index(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $usuario = $_SESSION['usuario'] ?? null;
        $reclamaciones = [];
        $reservas = [];
        $hasServices = false;

        if ($usuario) {
            $userId = (int)$usuario['id_usuario'];
            $hasServices = Reclamacion::hasActiveServices($userId);
            $reclamaciones = Reclamacion::findByUsuario($userId);
            $reservas = Reserva::findByUsuario($userId);
        }

        $title = "Libro de Reclamaciones — Viajes AJT";
        $description = "Registra tu reclamo o sugerencia en el Libro de Reclamaciones de Viajes AJT. Ley N.° 29571.";
        $pageKey = "reclamaciones";

        $baseViewsDir = dirname(__DIR__) . '/Views/';
        require_once $baseViewsDir . 'layouts/header.php';
        require_once $baseViewsDir . 'reclamaciones.php';
        require_once $baseViewsDir . 'layouts/footer.php';
    }

    /**
     * Procesa el registro de una nueva reclamación (POST).
     *
     * @return void
     */
    public function store(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/reclamaciones');
            exit();
        }

        $usuario = $_SESSION['usuario'] ?? null;
        if (!$usuario) {
            $_SESSION['error'] = "Debe iniciar sesión para registrar una reclamación.";
            header('Location: ' . BASE_URL . '/login');
            exit();
        }

        $userId = (int)$usuario['id_usuario'];

        if (!Reclamacion::hasActiveServices($userId)) {
            $_SESSION['error'] = "Solo los usuarios que hayan realizado una compra, reserva o solicitud de servicio pueden registrar reclamos o sugerencias.";
            header('Location: ' . BASE_URL . '/reclamaciones');
            exit();
        }

        $tipo = trim($_POST['tipo'] ?? '');
        $categoria = trim($_POST['categoria'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $pedidoCliente = trim($_POST['pedido_cliente'] ?? '');
        $medioRespuesta = trim($_POST['medio_respuesta'] ?? 'correo');
        $reservaId = !empty($_POST['reserva_id']) ? (int)$_POST['reserva_id'] : null;

        // Validation
        $errors = [];
        if (!in_array($tipo, ['reclamo', 'sugerencia'])) {
            $errors[] = "Tipo de reclamación no válido.";
        }
        if (empty($categoria)) {
            $errors[] = "La categoría es obligatoria.";
        }
        if (empty($descripcion)) {
            $errors[] = "La descripción es obligatoria.";
        }
        if (!in_array($medioRespuesta, ['whatsapp', 'correo', 'telefono'])) {
            $errors[] = "Medio de respuesta no válido.";
        }

        // Validate reserva belongs to user if provided
        if ($reservaId) {
            $reservas = Reserva::findByUsuario($userId);
            $validReservas = array_column($reservas, 'id_reserva');
            if (!in_array($reservaId, $validReservas)) {
                $errors[] = "La reserva seleccionada no es válida.";
                $reservaId = null;
            }
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode(' ', $errors);
            header('Location: ' . BASE_URL . '/reclamaciones');
            exit();
        }

        $data = [
            'usuario_id' => $userId,
            'tipo' => $tipo,
            'categoria' => $categoria,
            'descripcion' => $descripcion,
            'pedido_cliente' => $pedidoCliente ?: null,
            'medio_respuesta' => $medioRespuesta,
            'reserva_id' => $reservaId,
        ];

        try {
            $id = Reclamacion::create($data);
            if ($id) {
                $_SESSION['success'] = "Reclamación registrada exitosamente. N.° " . str_pad((string)$id, 5, '0', STR_PAD_LEFT);
            } else {
                $_SESSION['error'] = "No se pudo registrar la reclamación. Intente nuevamente.";
            }
        } catch (Exception $e) {
            error_log("Error en ReclamacionController::store: " . $e->getMessage());
            $_SESSION['error'] = "Ocurrió un error inesperado al registrar la reclamación.";
        }

        header('Location: ' . BASE_URL . '/reclamaciones');
        exit();
    }

    /**
     * Panel de administración — listado de reclamaciones (Admin).
     *
     * @return void
     */
    public function adminIndex(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario']) || (int)$_SESSION['usuario']['id_rol'] !== 2) {
            $_SESSION['error'] = "Acceso denegado. Se requieren privilegios de administrador.";
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }

        try {
            $reclamaciones = Reclamacion::findAll();
        } catch (Exception $e) {
            error_log("Error en ReclamacionController::adminIndex: " . $e->getMessage());
            $reclamaciones = [];
        }

        $title = "Reclamaciones · Admin AJT";
        $pageKey = "reclamaciones";

        $baseViewsDir = dirname(__DIR__) . '/Views/';
        require_once $baseViewsDir . 'layouts/admin_header.php';
        require_once $baseViewsDir . 'admin/reclamaciones.php';
        require_once $baseViewsDir . 'layouts/admin_footer.php';
    }

    /**
     * Actualiza el estado de una reclamación (Admin POST).
     *
     * @return void
     */
    public function adminUpdate(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario']) || (int)$_SESSION['usuario']['id_rol'] !== 2) {
            $_SESSION['error'] = "Acceso denegado.";
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/reclamaciones');
            exit();
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $estado = trim($_POST['estado'] ?? '');
        $respuestaAdmin = trim($_POST['respuesta_admin'] ?? '');

        if ($id <= 0 || !in_array($estado, ['nuevo', 'en_revision', 'respondido', 'cerrado'])) {
            $_SESSION['error'] = "Datos no válidos.";
            header('Location: ' . BASE_URL . '/admin/reclamaciones');
            exit();
        }

        try {
            if (Reclamacion::updateEstado($id, $estado, $respuestaAdmin ?: null)) {
                $_SESSION['success'] = "Reclamación #" . str_pad((string)$id, 5, '0', STR_PAD_LEFT) . " actualizada.";
            } else {
                $_SESSION['error'] = "No se pudo actualizar la reclamación.";
            }
        } catch (Exception $e) {
            error_log("Error en ReclamacionController::adminUpdate: " . $e->getMessage());
            $_SESSION['error'] = "Error interno al actualizar la reclamación.";
        }

        header('Location: ' . BASE_URL . '/admin/reclamaciones');
        exit();
    }
}

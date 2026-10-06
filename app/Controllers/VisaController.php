<?php

declare(strict_types=1);

// Funcion del archivo: Gestiona servicios de visa y solicitudes del cliente.
namespace App\Controllers;

use App\Models\Visa;
use Exception;

/**
 * Clase VisaController
 * Gestiona el catálogo de asesorías de visas, el registro de solicitudes y la administración de trámites.
 */
class VisaController
{
    /**
     * Muestra el catálogo de servicios de visa (Público).
     *
     * @return void
     */
    public function index(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        try {
            $servicios = Visa::allServices();
        } catch (Exception $e) {
            error_log("Error en VisaController::index: " . $e->getMessage());
            $servicios = [];
        }

        $title = "Asesoría de visas — Viajes AJT";
        $description = "Asesoría especializada para visas americana, Schengen, Canadá, Reino Unido y más. 95% de aprobación.";
        $pageKey = "visas";

        $baseViewsDir = dirname(__DIR__) . '/Views/';
        require_once $baseViewsDir . 'layouts/header.php';
        require_once $baseViewsDir . 'visas.php';
        require_once $baseViewsDir . 'layouts/footer.php';
    }

    /**
     * Procesa la solicitud de asesoría de visa enviada por el cliente (POST).
     *
     * @return void
     */
    public function procesarSolicitud(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/visas');
            exit();
        }

        $id_servicio = isset($_POST['id_servicio']) ? (int)$_POST['id_servicio'] : 0;
        $fecha_viaje_aprox = isset($_POST['fecha_viaje_aprox']) ? trim($_POST['fecha_viaje_aprox']) : '';

        if ($id_servicio <= 0) {
            $_SESSION['error'] = "Por favor, seleccione un servicio de visa válido.";
            header('Location: ' . BASE_URL . '/visas');
            exit();
        }

        // Verificar si el usuario está autenticado
        if (!isset($_SESSION['usuario'])) {
            $_SESSION['error'] = "Debe iniciar sesión para solicitar una asesoría de visa.";
            $_SESSION['pending_visa'] = [
                'id_servicio' => $id_servicio,
                'fecha_viaje_aprox' => $fecha_viaje_aprox
            ];
            header('Location: ' . BASE_URL . '/login');
            exit();
        }

        $id_usuario = (int)$_SESSION['usuario']['id_usuario'];

        try {
            $data = [
                'id_usuario' => $id_usuario,
                'id_servicio' => $id_servicio,
                'fecha_viaje_aprox' => $fecha_viaje_aprox
            ];

            $codigo = Visa::createSolicitud($data);

            if ($codigo) {
                $_SESSION['success'] = "¡Trámite de visa registrado con éxito! Código de seguimiento: " . $codigo;
                header('Location: ' . BASE_URL . '/profile/mi-perfil#tramites');
            } else {
                $_SESSION['error'] = "No se pudo registrar su solicitud de visa. Intente nuevamente.";
                header('Location: ' . BASE_URL . '/visas');
            }
        } catch (Exception $e) {
            error_log("Error en VisaController::procesarSolicitud: " . $e->getMessage());
            $_SESSION['error'] = "Ocurrió un error inesperado al procesar su solicitud.";
            header('Location: ' . BASE_URL . '/visas');
        }
        exit();
    }

    /**
     * Muestra la bandeja de trámites de visa (Admin).
     *
     * @return void
     */
    public function adminIndex(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Proteger ruta para administradores
        if (!isset($_SESSION['usuario']) || (int)$_SESSION['usuario']['id_rol'] !== 2) {
            $_SESSION['error'] = "Acceso denegado. Se requieren privilegios de administrador.";
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }

        try {
            $solicitudes = Visa::allSolicitudes();
        } catch (Exception $e) {
            error_log("Error en VisaController::adminIndex: " . $e->getMessage());
            $solicitudes = [];
        }

        $title = "Trámites de visas · Admin AJT";
        $pageKey = "visas";

        $baseViewsDir = dirname(__DIR__) . '/Views/';
        require_once $baseViewsDir . 'layouts/admin_header.php';
        require_once $baseViewsDir . 'admin/visas.php';
        require_once $baseViewsDir . 'layouts/admin_footer.php';
    }

    /**
     * Procesa la actualización del estado de una solicitud (Admin POST).
     *
     * @return void
     */
    public function adminUpdateEstado(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Proteger ruta para administradores
        if (!isset($_SESSION['usuario']) || (int)$_SESSION['usuario']['id_rol'] !== 2) {
            $_SESSION['error'] = "Acceso denegado.";
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/visas');
            exit();
        }

        $id_solicitud = isset($_POST['id_solicitud']) ? (int)$_POST['id_solicitud'] : 0;
        $estado = isset($_POST['estado']) ? trim($_POST['estado']) : '';

        $estadosValidos = ['nueva','contactado','documentos_pendientes','en_revision','en_tramite','aprobada','rechazada','cancelada'];
        if ($id_solicitud <= 0 || !in_array($estado, $estadosValidos)) {
            $_SESSION['error'] = "Datos de solicitud o estado no válidos.";
            header('Location: ' . BASE_URL . '/admin/visas');
            exit();
        }

        try {
            if (Visa::updateEstado($id_solicitud, $estado)) {
                $_SESSION['success'] = "Estado de la solicitud actualizado a '" . ucfirst($estado) . "' con éxito.";
            } else {
                $_SESSION['error'] = "No se pudo actualizar el estado de la solicitud.";
            }
        } catch (Exception $e) {
            error_log("Error en VisaController::adminUpdateEstado: " . $e->getMessage());
            $_SESSION['error'] = "Error interno al actualizar la solicitud.";
        }

        header('Location: ' . BASE_URL . '/admin/visas');
        exit();
    }
}

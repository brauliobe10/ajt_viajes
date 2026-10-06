<?php

declare(strict_types=1);

// Funcion del archivo: Gestiona perfil del cliente, reservas, comprobantes y cuenta.
namespace App\Controllers;

use App\Models\Usuario;
use App\Models\Reserva;
use App\Models\Visa;
use Exception;

/**
 * Clase ProfileController
 * Gestiona las acciones asociadas al perfil y panel del cliente.
 */
class ProfileController
{
    /**
     * Constructor del controlador.
     * Protege todas las rutas de perfil asegurando una sesión activa.
     */
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario'])) {
            $_SESSION['error'] = "Debe iniciar sesión para ingresar a su perfil.";
            header('Location: ' . BASE_URL . '/login');
            exit();
        }
    }

    /**
     * Despliega el panel "Mi Perfil" del cliente.
     *
     * @return void
     */
    public function miPerfil(): void
    {
        $id_usuario = (int)$_SESSION['usuario']['id_usuario'];

        try {
            $user = Usuario::findById($id_usuario);
            $reservas = Reserva::findByUsuario($id_usuario);
            $solicitudesVisa = Visa::findByUsuario($id_usuario);
        } catch (Exception $e) {
            error_log("Error en ProfileController::miPerfil: " . $e->getMessage());
            $_SESSION['error'] = "No se pudo cargar la información del perfil.";
            $user = [];
            $reservas = [];
            $solicitudesVisa = [];
        }

        $title = "Mi perfil — Viajes AJT";
        $description = "Consulta tus reservas y gestiona tu información en Viajes AJT.";
        $pageKey = "perfil";

        $baseViewsDir = dirname(__DIR__) . '/Views/';
        require_once $baseViewsDir . 'layouts/header.php';
        require_once $baseViewsDir . 'profile/mi_perfil.php';
        require_once $baseViewsDir . 'layouts/footer.php';
    }

    /**
     * Procesa el cambio de contraseña del usuario (POST).
     *
     * @return void
     */
    public function cambiarPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/profile/mi-perfil');
            exit();
        }

        $id_usuario = (int)$_SESSION['usuario']['id_usuario'];
        $current = $_POST['password_actual'] ?? '';
        $newPass = $_POST['password_nueva'] ?? '';
        $confirm = $_POST['password_confirm'] ?? '';

        // Validar que la contraseña actual sea correcta
        $user = Usuario::findById($id_usuario);
        if (!$user || !password_verify($current, $user['password_hash'])) {
            $_SESSION['error'] = "La contraseña actual no es correcta.";
            header('Location: ' . BASE_URL . '/profile/mi-perfil');
            exit();
        }

        // Validar nueva contraseña
        if (strlen($newPass) < 6) {
            $_SESSION['error'] = "La nueva contraseña debe tener al menos 6 caracteres.";
            header('Location: ' . BASE_URL . '/profile/mi-perfil');
            exit();
        }

        if ($newPass !== $confirm) {
            $_SESSION['error'] = "La nueva contraseña y su confirmación no coinciden.";
            header('Location: ' . BASE_URL . '/profile/mi-perfil');
            exit();
        }

        try {
            $hash = password_hash($newPass, PASSWORD_BCRYPT);
            if (Usuario::cambiarPassword($id_usuario, $hash)) {
                $_SESSION['success'] = "¡Contraseña actualizada correctamente!";
            } else {
                $_SESSION['error'] = "No se pudo actualizar la contraseña. Intente nuevamente.";
            }
        } catch (\Exception $e) {
            error_log("Error en ProfileController::cambiarPassword: " . $e->getMessage());
            $_SESSION['error'] = "Ocurrió un error al cambiar la contraseña.";
        }

        header('Location: ' . BASE_URL . '/profile/mi-perfil');
        exit();
    }

    /**
     * Muestra el comprobante de reserva (pagina imprimible).
     *
     * @param string $codigo Codigo de reserva (ej. AJT-123456)
     * @return void
     */
    public function comprobante(string $codigo): void
    {
        $id_usuario = (int)$_SESSION['usuario']['id_usuario'];

        try {
            $reserva = Reserva::findByCodigo($codigo, $id_usuario);

            if (!$reserva) {
                http_response_code(404);
                echo '<h1>404 - Reserva no encontrada</h1><p>El comprobante solicitado no existe o no pertenece a su cuenta.</p>';
                exit();
            }

            // Calcular desglose financiero
            $precioUnitario = (float)$reserva['precio_unitario'];
            $pax = (int)$reserva['cantidad_pasajeros'];
            $subtotal = $precioUnitario * $pax;
            $descuento = (float)$reserva['descuento'];
            $baseImponible = $subtotal - $descuento;
            $igv = round($baseImponible * 0.18, 2);
            $total = round($baseImponible + $igv, 2);

            $moneda = 'S/';

            $title = "Comprobante " . htmlspecialchars($codigo) . " — Viajes AJT";
            $description = "Comprobante de reserva Viajes AJT.";
            $pageKey = "perfil";

            $baseViewsDir = dirname(__DIR__) . '/Views/';
            require_once $baseViewsDir . 'profile/comprobante.php';

        } catch (Exception $e) {
            error_log("Error en ProfileController::comprobante: " . $e->getMessage());
            http_response_code(500);
            echo '<h1>500 - Error del servidor</h1><p>No se pudo cargar el comprobante.</p>';
            exit();
        }
    }

    /**
     * Procesa los cambios en los datos de la cuenta del usuario (POST).
     *
     * @return void
     */
    public function guardarCuenta(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/profile/mi-perfil');
            exit();
        }

        $id_usuario = (int)$_SESSION['usuario']['id_usuario'];
        $nombres = isset($_POST['nombres']) ? trim($_POST['nombres']) : '';
        $apellidos = isset($_POST['apellidos']) ? trim($_POST['apellidos']) : '';
        $telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
        $dni = isset($_POST['dni_pasaporte']) ? trim($_POST['dni_pasaporte']) : '';

        $errores = [];

        // Validar nombres
        if (empty($nombres) || mb_strlen($nombres) < 2 || mb_strlen($nombres) > 100) {
            $errores[] = "Los nombres son obligatorios y deben tener entre 2 y 100 caracteres.";
        } elseif (!preg_match('/^[\p{L}\s]+$/u', $nombres)) {
            $errores[] = "Los nombres solo pueden contener letras y espacios.";
        }

        // Validar apellidos
        if (empty($apellidos) || mb_strlen($apellidos) < 2 || mb_strlen($apellidos) > 100) {
            $errores[] = "Los apellidos son obligatorios y deben tener entre 2 y 100 caracteres.";
        } elseif (!preg_match('/^[\p{L}\s]+$/u', $apellidos)) {
            $errores[] = "Los apellidos solo pueden contener letras y espacios.";
        }

        // Validar teléfono (opcional)
        if (!empty($telefono)) {
            $telefonoDigits = preg_replace('/[^0-9]/', '', $telefono);
            if (strlen($telefonoDigits) < 7 || strlen($telefonoDigits) > 15) {
                $errores[] = "El teléfono debe tener entre 7 y 15 dígitos.";
            }
            $telefono = $telefonoDigits;
        }

        // Validar DNI/Pasaporte (opcional)
        if (!empty($dni) && (mb_strlen($dni) < 6 || mb_strlen($dni) > 20)) {
            $errores[] = "El DNI o pasaporte debe tener entre 6 y 20 caracteres.";
        }

        if (!empty($errores)) {
            $_SESSION['error'] = implode('<br>', $errores);
            header('Location: ' . BASE_URL . '/profile/mi-perfil');
            exit();
        }

        try {
            $data = [
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'telefono' => $telefono,
                'dni' => $dni
            ];

            if (Usuario::updateAccount($id_usuario, $data)) {
                // Actualizar sesión con nuevos nombres/apellidos
                $_SESSION['usuario']['nombres'] = $nombres;
                $_SESSION['usuario']['apellidos'] = $apellidos;
                
                $_SESSION['success'] = "Los datos de su cuenta se actualizaron correctamente.";
            } else {
                $_SESSION['error'] = "No se pudieron guardar los cambios.";
            }
        } catch (Exception $e) {
            error_log("Error en ProfileController::guardarCuenta: " . $e->getMessage());
            $_SESSION['error'] = "Ocurrió un error al intentar actualizar la cuenta.";
        }

        header('Location: ' . BASE_URL . '/profile/mi-perfil');
        exit();
    }
}

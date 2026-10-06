<?php

declare(strict_types=1);

// Funcion del archivo: Muestra y procesa el formulario publico de contacto.
namespace App\Controllers;

/**
 * Controlador de contacto público.
 * Gestiona la visualización del formulario y el envío de mensajes.
 */
class ContactoController
{
    /**
     * Muestra la página de contacto con formulario (GET).
     *
     * @return void
     */
    public function index(): void
    {
        $title = "Contacto — Viajes AJT";
        $description = "Habla con un asesor de Viajes AJT. Lima, Perú. WhatsApp, teléfono y formulario disponible.";
        $pageKey = "contacto";

        $baseViewsDir = dirname(__DIR__) . '/Views/';

        require_once $baseViewsDir . 'layouts/header.php';
        require_once $baseViewsDir . 'contacto.php';
        require_once $baseViewsDir . 'layouts/footer.php';
    }

    /**
     * Procesa el formulario de contacto (POST).
     * Valida los campos y almacena el mensaje en la base de datos.
     *
     * @return void
     */
    public function enviar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/contacto');
            exit();
        }

        \App\Helper\Csrf::validate('/contacto');

        // Sanitizar entradas
        $nombre    = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
        $email     = isset($_POST['email']) ? trim($_POST['email']) : '';
        $telefono  = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
        $asunto    = isset($_POST['asunto']) ? trim($_POST['asunto']) : '';
        $mensaje   = isset($_POST['mensaje']) ? trim($_POST['mensaje']) : '';

        // Validar campos obligatorios
        $errores = [];

        if (empty($nombre) || strlen($nombre) < 2 || strlen($nombre) > 100) {
            $errores[] = "El nombre es obligatorio y debe tener entre 2 y 100 caracteres.";
        } elseif (!preg_match('/^[\p{L}\s]+$/u', $nombre)) {
            $errores[] = "El nombre solo puede contener letras y espacios.";
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = "Ingrese un correo electrónico válido.";
        }

        $asuntosPermitidos = ['Paquete turístico', 'Asesoría de visa', 'Cotización corporativa', 'Otro'];
        if (empty($asunto) || !in_array($asunto, $asuntosPermitidos, true)) {
            $errores[] = "Seleccione un motivo de contacto válido.";
        }

        if (empty($mensaje) || strlen($mensaje) < 10 || strlen($mensaje) > 5000) {
            $errores[] = "El mensaje es obligatorio y debe tener entre 10 y 5000 caracteres.";
        }

        // Validar teléfono si fue proporcionado
        if (!empty($telefono)) {
            $telefonoDigits = preg_replace('/[^0-9]/', '', $telefono);
            if (strlen($telefonoDigits) < 7 || strlen($telefonoDigits) > 15) {
                $errores[] = "El teléfono debe tener entre 7 y 15 dígitos.";
            }
        }

        if (!empty($errores)) {
            $_SESSION['error'] = implode('<br>', $errores);
            $_SESSION['form_data'] = compact('nombre', 'email', 'telefono', 'asunto', 'mensaje');
            header('Location: ' . BASE_URL . '/contacto');
            exit();
        }

        try {
            $db = \App\Config\Database::getConnection();
            $stmt = $db->prepare(
                "INSERT INTO mensajes_contacto (nombre, email, telefono, asunto, mensaje)
                 VALUES (:nombre, :email, :telefono, :asunto, :mensaje)"
            );
            $stmt->execute([
                ':nombre'   => $nombre,
                ':email'    => $email,
                ':telefono' => $telefono,
                ':asunto'   => $asunto,
                ':mensaje'  => $mensaje,
            ]);

            $_SESSION['success'] = "¡Mensaje enviado con éxito! Un asesor se contactará contigo pronto.";
            unset($_SESSION['form_data']);
        } catch (\Exception $e) {
            error_log("Error en ContactoController::enviar: " . $e->getMessage());
            $_SESSION['error'] = "Ocurrió un error al enviar tu mensaje. Intenta nuevamente.";
            $_SESSION['form_data'] = compact('nombre', 'email', 'telefono', 'asunto', 'mensaje');
        }

        header('Location: ' . BASE_URL . '/contacto');
        exit();
    }
}

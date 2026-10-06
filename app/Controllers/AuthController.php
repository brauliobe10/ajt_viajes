<?php

declare(strict_types=1);

// Funcion del archivo: Gestiona inicio de sesion, registro y cierre de sesion.

namespace App\Controllers;

use App\Models\Usuario;
use App\Helper\RateLimiter;

/**
 * Clase AuthController
 * Gestiona el inicio de sesión, registro, cierre de sesión,
 * y validación de email vía Ajax.
 *
 * Extiende BaseController para autenticación, rendering y redirección.
 */
class AuthController extends BaseController
{
    /**
     * Muestra el formulario de inicio de sesión.
     *
     * @return void
     */
    public function login(): void
    {
        // Si el usuario ya está autenticado, enviarlo a su destino correspondiente.
        if (isset($_SESSION['usuario'])) {
            $this->redirect($this->getDashboardPath((int)$_SESSION['usuario']['id_rol']));
        }

        $title = "Iniciar sesión — Viajes AJT";
        $description = "Accede a Viajes AJT y continua automaticamente segun tu rol.";
        $pageKey = "login";

        $this->render('auth/login', [
            'title' => $title,
            'description' => $description,
            'pageKey' => $pageKey,
        ]);
    }

    /**
     * Procesa la solicitud de inicio de sesión (POST).
     *
     * @return void
     */
    public function procesarLogin(): void
    {
        $this->doLogin('/auth/login');
    }

    /**
     * Procesa el formulario legado de inicio de sesión del administrador.
     *
     * @return void
     */
    public function procesarAdminLogin(): void
    {
        $this->doLogin('/auth/login');
    }

    /**
     * Lógica común de login con rate limiting.
     */
    private function doLogin(string $redirectFail): void
    {
        $this->requireMethod('POST', $redirectFail);

        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $this->setFlash('error', 'Por favor, complete todos los campos.');
            $this->redirect($redirectFail);
        }

        // Rate limiting en dos ejes paralelos para frenar fuerza bruta real:
        //  - Por IP: frena a la máquina atacante sin importar qué emails pruebe.
        //  - Por email: protege una cuenta específica contra muchos intentos.
        // El hit() se registra SOLO en intento fallido (más abajo).
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        $ipLimiter   = new RateLimiter("login:ip:{$ip}", 10, 15);
        $emailLimiter = new RateLimiter("login:email:{$email}", 5, 15);

        if ($ipLimiter->isLimited() || $emailLimiter->isLimited()) {
            http_response_code(429);
            header('Retry-After: 900');

            $message = 'Demasiados intentos de inicio de sesión. Intente nuevamente en 15 minutos.';

            if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') ||
                str_contains($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '', 'XMLHttpRequest')) {
                $this->json(['error' => $message, 'remaining' => 0], 429);
            }

            $this->setFlash('error', $message);
            $_SESSION['toast'] = [
                'type' => 'warning',
                'title' => 'Protección de cuenta',
                'text' => $message,
            ];
            $_SESSION['rate_limit_warning'] = [
                'remaining' => 0,
                'max' => 5,
                'message' => $message
            ];
            $this->redirect($redirectFail);
        }

        // Alias corto legado
        if (strtolower($email) === 'admin') {
            $email = 'admin@viajesajt.com';
        }

        $user = Usuario::findByEmail($email);

        if ($user && password_verify($password, $user['password_hash'])) {
            $ipLimiter->clear();
            $emailLimiter->clear();
            session_regenerate_id(true);

            $_SESSION['usuario'] = [
                'id_usuario' => $user['id_usuario'],
                'id_rol'     => $user['id_rol'],
                'nombres'    => $user['nombres'],
                'apellidos'  => $user['apellidos'],
                'email'      => $user['email'],
            ];

            $this->setFlash('success', "¡Bienvenido de vuelta, " . htmlspecialchars($user['nombres']) . "!");

            $userRole = (int)$user['id_rol'];

            // Pending booking redirect
            if ($userRole === 1 && isset($_SESSION['pending_booking'])) {
                $pb = $_SESSION['pending_booking'];
                unset($_SESSION['pending_booking']);
                $this->redirect('/checkout?paquete=' . urlencode($pb['paquete']) . '&fecha_viaje=' . urlencode($pb['fecha_viaje']) . '&viajeros=' . (int)$pb['viajeros']);
            }

            // Pending visa redirect
            if ($userRole === 1 && isset($_SESSION['pending_visa'])) {
                unset($_SESSION['pending_visa']);
                $this->redirect('/visas');
            }

            $this->redirect($this->getDashboardPath($userRole));
        }

        $ipLimiter->hit();
        $emailLimiter->hit();
        
        $attemptsLeft = $emailLimiter->remainingAttempts();
        if ($attemptsLeft > 0 && $attemptsLeft < 5) {
            $msg = "Te quedan {$attemptsLeft} " . ($attemptsLeft === 1 ? "intento" : "intentos") . " de inicio de sesión.";
            $this->setFlash('warning', $msg);
            $_SESSION['rate_limit_warning'] = [
                'remaining' => $attemptsLeft,
                'max' => 5,
                'message' => $msg
            ];
        }
        
        $this->setFlash('error', 'Credenciales incorrectas. Verifique su correo y contraseña.');
        $this->redirect($redirectFail);
    }

    /**
     * Redirige la ruta legacy de login administrativo al flujo unificado.
     */
    public function showAdminLogin(): void
    {
        if (isset($_SESSION['usuario'])) {
            $this->redirect($this->getDashboardPath((int)$_SESSION['usuario']['id_rol']));
        }
        $this->redirect('/auth/login');
    }

    /**
     * Redirige la ruta legacy del selector al flujo unificado.
     */
    public function seleccionarPanel(): void
    {
        if (isset($_SESSION['usuario'])) {
            $this->redirect($this->getDashboardPath((int)$_SESSION['usuario']['id_rol']));
        }
        $this->redirect('/auth/login');
    }

    // ──────────────────────────────────────────────
    //  Redirige la ruta de destino segun el rol.
    // ──────────────────────────────────────────────
    protected function getDashboardPath(int $roleId): string
    {
        return $roleId === 2 ? '/admin/dashboard' : '/';
    }

    /**
     * Cierra la sesión activa del usuario.
     */
    public function logoutGet(): void
    {
        $this->setFlash('error', 'Para cerrar sesión use el botón seguro del sitio.');
        $this->redirect('/auth/login');
    }

    public function logout(): void
    {
        $this->requireMethod('POST', '/auth/login');

        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();

        session_start();
        $this->setFlash('success', 'Sesión cerrada correctamente.');
        $this->redirect('/auth/login');
    }

    /**
     * Muestra el formulario de registro de clientes.
     */
    public function registro(): void
    {
        if (isset($_SESSION['usuario'])) {
            $this->redirect($this->getDashboardPath((int)$_SESSION['usuario']['id_rol']));
        }

        $this->render('auth/registro', [
            'title' => 'Crear cuenta — Viajes AJT',
            'description' => 'Únete a Viajes AJT y gestiona tus reservas de forma rápida y segura.',
            'pageKey' => 'registro',
        ]);
    }

    /**
     * Procesa la solicitud de registro (POST) con rate limiting.
     */
    public function procesarRegistro(): void
    {
        $this->requireMethod('POST', '/auth/registro');

        // Rate limiting por IP para registro
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $limiter = new RateLimiter("registro:{$ip}", 3, 60); // 3 registros por hora por IP
        $limiter->check('registro', '/auth/registro');

        $nombres   = isset($_POST['nombres']) ? trim($_POST['nombres']) : '';
        $apellidos = isset($_POST['apellidos']) ? trim($_POST['apellidos']) : '';
        $email     = isset($_POST['email']) ? trim($_POST['email']) : '';
        $telefono  = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
        $password  = $_POST['password'] ?? '';
        $dni       = isset($_POST['dni_pasaporte']) ? trim($_POST['dni_pasaporte']) : '';

        // --- Validación server-side ---
        $errores = [];

        if ($nombres === '' || strlen($nombres) < 2 || strlen($nombres) > 100) {
            $errores[] = "Los nombres son obligatorios y deben tener entre 2 y 100 caracteres.";
        } elseif (!preg_match('/^[\p{L}\s]+$/u', $nombres)) {
            $errores[] = "Los nombres solo pueden contener letras y espacios.";
        }

        if ($apellidos === '' || strlen($apellidos) < 2 || strlen($apellidos) > 100) {
            $errores[] = "Los apellidos son obligatorios y deben tener entre 2 y 100 caracteres.";
        } elseif (!preg_match('/^[\p{L}\s]+$/u', $apellidos)) {
            $errores[] = "Los apellidos solo pueden contener letras y espacios.";
        }

        if ($email === '') {
            $errores[] = "El correo electrónico es obligatorio.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = "Ingrese un correo electrónico válido.";
        }

        if ($password === '') {
            $errores[] = "La contraseña es obligatoria.";
        } elseif (strlen($password) < 6) {
            $errores[] = "La contraseña debe tener al menos 6 caracteres.";
        }

        if ($telefono !== '') {
            $telefonoDigits = preg_replace('/[^0-9]/', '', $telefono);
            if (strlen($telefonoDigits) < 7 || strlen($telefonoDigits) > 15) {
                $errores[] = "El teléfono debe tener entre 7 y 15 dígitos.";
            }
        }

        if ($dni === '') {
            $errores[] = "El DNI o Pasaporte es obligatorio.";
        } elseif (strlen($dni) < 6 || strlen($dni) > 20) {
            $errores[] = "El DNI o Pasaporte debe tener entre 6 y 20 caracteres.";
        }

        if ($errores !== []) {
            $this->setFlash('error', implode('<br>', $errores));
            $this->conservarRegistroPostData();
            $this->redirect('/auth/registro');
        }

        try {
            if (Usuario::findByEmail($email) !== null) {
                $this->setFlash('error', 'El correo electrónico ingresado ya se encuentra registrado.');
                $this->conservarRegistroPostData();
                $this->redirect('/auth/registro');
            }

            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $userData = [
                'id_rol' => 1,
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'email' => $email,
                'password_hash' => $passwordHash,
                'telefono' => $telefono,
                'dni_pasaporte' => $dni,
            ];

            if (Usuario::create($userData)) {
                $user = Usuario::findByEmail($email);
                session_regenerate_id(true);

                $_SESSION['usuario'] = [
                    'id_usuario' => $user['id_usuario'],
                    'id_rol'     => $user['id_rol'],
                    'nombres'    => $user['nombres'],
                    'apellidos'  => $user['apellidos'],
                    'email'      => $user['email'],
                ];

                $this->setFlash('success', '¡Cuenta creada con éxito! Bienvenido a Viajes AJT.');
                $this->limpiarRegistroPostData();

                if (isset($_SESSION['pending_booking'])) {
                    $pb = $_SESSION['pending_booking'];
                    unset($_SESSION['pending_booking']);
                    $this->redirect('/checkout?paquete=' . urlencode($pb['paquete']) . '&fecha_viaje=' . urlencode($pb['fecha_viaje']) . '&viajeros=' . (int)$pb['viajeros']);
                }

                if (isset($_SESSION['pending_visa'])) {
                    unset($_SESSION['pending_visa']);
                    $this->redirect('/visas');
                }

                $this->redirect('/profile/mi-perfil');
            }

            $this->setFlash('error', 'No se pudo registrar la cuenta. Intente nuevamente.');
            $this->conservarRegistroPostData();
            $this->redirect('/auth/registro');
        } catch (\Exception $e) {
            error_log("Error en AuthController::procesarRegistro: " . $e->getMessage());
            $this->setFlash('error', 'Ocurrió un error inesperado al procesar el registro.');
            $this->conservarRegistroPostData();
            $this->redirect('/auth/registro');
        }
    }

    private function conservarRegistroPostData(): void
    {
        $_POST['nombres'] = $_POST['nombres'] ?? '';
        $_POST['apellidos'] = $_POST['apellidos'] ?? '';
        $_POST['email'] = $_POST['email'] ?? '';
        $_POST['telefono'] = $_POST['telefono'] ?? '';
        $_POST['dni_pasaporte'] = $_POST['dni_pasaporte'] ?? '';
    }

    private function limpiarRegistroPostData(): void
    {
        unset($_POST['nombres'], $_POST['apellidos'], $_POST['email'], $_POST['telefono'], $_POST['dni_pasaporte']);
    }

    /**
     * API JSON: valida si un correo electrónico ya está registrado (AJAX).
     */
    public function validateEmail(): void
    {
        header('Content-Type: application/json; charset=UTF-8');

        $email = isset($_GET['email']) ? trim($_GET['email']) : '';

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(['valid' => false, 'message' => 'Formato de correo inválido.']);
        }

        try {
            $user = Usuario::findByEmail($email);
            if ($user !== null) {
                $this->json(['valid' => false, 'message' => 'Este correo ya está registrado.']);
            }
            $this->json(['valid' => true]);
        } catch (\Exception $e) {
            error_log("Error en AuthController::validateEmail: " . $e->getMessage());
            $this->json(['valid' => false, 'message' => 'Error al verificar el correo.'], 500);
        }
    }

}

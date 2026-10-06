<?php

declare(strict_types=1);

/**
 * Front Controller
 * Único punto de entrada de la aplicación.
 *
 * Responsabilidades:
 * - Cargar variables de entorno (.env)
 * - Registrar manejadores globales de errores y excepciones
 * - Aplicar cabeceras de seguridad HTTP
 * - Configurar sesión con cookies seguras
 * - Autoloader PSR-4 para el namespace App\
 * - Protección CSRF global (GET y POST)
 * - Inicialización condicional del seeder (solo vía CLI o flag)
 * - Despachar la petición al router
 */

// ── 0. Autoloader PSR-4 para App\ (debe ir antes de usar otras clases) ───
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// ── 1. Registrar manejadores globales de errores ───────────────────────────
$debug = \App\Config\LocalConfig::APP_DEBUG;
\App\Helper\ErrorHandler::register((bool)$debug);

// ── 2. Aplicar cabeceras de seguridad HTTP ─────────────────────────────────
\App\Helper\SecurityHeaders::apply();

// ── 3. Configuración de BASE_URL ───────────────────────────────────────────
//
// Calculamos BASE_URL a partir de SCRIPT_FILENAME vs DOCUMENT_ROOT.
// Esto funciona correctamente tanto si se accede desde
//   http://localhost:8080/tecnoweb-viajesajt/public/     (directo)
// BASE_URL incluye el segmento /public del path.
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

$scriptFilename = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? '');
$docRoot = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/'));

if ($docRoot !== '' && strpos($scriptFilename, $docRoot) === 0) {
    $basePath = dirname(substr($scriptFilename, strlen($docRoot)));
    $basePath = str_replace('\\', '/', $basePath);
    $basePath = $basePath === '/' ? '' : $basePath;
} else {
    $basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
}

define('BASE_URL', $scheme . '://' . $host . $basePath);

// ── 4. Configuración y arranque de sesión ─────────────────────────────────
session_name('VIAJESAJTSESSID');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Lax',
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── 5. Protección CSRF global ─────────────────────────────────────────────
use App\Helper\Csrf;

// Inicializar el token si no existe
Csrf::token();

if (PHP_SAPI !== 'cli' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $token = $_POST['csrf_token'] ?? null;
    if (!Csrf::verifyToken($token)) {
        http_response_code(403);
        header('Content-Type: text/html; charset=UTF-8');
        echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>403 - Acceso denegado</title>';
        echo '<style>body{font-family:sans-serif;display:flex;justify-content:center;align-items:center;min-height:100vh;background:#f8fafc;}
              .card{text-align:center;padding:3em;background:white;border-radius:16px;max-width:480px;}
              h1{color:#dc2626;}a{display:inline-block;margin-top:1em;padding:.75em 2em;background:#1b2c8a;color:white;text-decoration:none;border-radius:8px;}</style>';
        echo '</head><body><div class="card"><h1>403 — Acceso denegado</h1>';
        echo '<p>Token de seguridad (CSRF) inválido o expirado. Por favor, vuelva atrás y recargue la página.</p>';
        echo '<a href="' . BASE_URL . '/">Volver al inicio</a></div></body></html>';
        exit();
    }
}

// ── 6. Enrutamiento y despacho ────────────────────────────────────────────
use App\Config\Router;

$router = new Router();

// Mapeamos las rutas básicas del sitio
$router->add('', 'HomeController@index', 'GET');
$router->add('index.php', 'HomeController@index', 'GET');

// Rutas de Catálogo Público
$router->add('catalog', 'CatalogController@index', 'GET');
$router->add('catalogo', 'CatalogController@index', 'GET');
$router->add('paquete/{slug}', 'CatalogController@detail', 'GET');
$router->add('api/paquetes/buscar', 'CatalogController@searchJson', 'GET');

// Rutas de Autenticación
$router->add('login', 'AuthController@login', 'GET');
$router->add('login', 'AuthController@procesarLogin', 'POST');
$router->add('auth/login', 'AuthController@login', 'GET');
$router->add('auth/login', 'AuthController@procesarLogin', 'POST');
$router->add('admin/login', 'AuthController@showAdminLogin', 'GET');
$router->add('admin/login', 'AuthController@procesarAdminLogin', 'POST');
$router->add('auth/registro', 'AuthController@registro', 'GET');
$router->add('auth/registro', 'AuthController@procesarRegistro', 'POST');
$router->add('registro', 'AuthController@registro', 'GET');
$router->add('logout', 'AuthController@logoutGet', 'GET');
$router->add('auth/logout', 'AuthController@logoutGet', 'GET');
$router->add('logout', 'AuthController@logout', 'POST');
$router->add('auth/logout', 'AuthController@logout', 'POST');
$router->add('seleccionar-panel', 'AuthController@seleccionarPanel', 'GET');
$router->add('api/validar-email', 'AuthController@validateEmail', 'GET');

// Rutas de Reservas (Booking)
$router->add('checkout', 'BookingController@checkout', 'GET');
$router->add('booking/procesar-checkout', 'BookingController@procesarCheckout', 'POST');

// Rutas de Perfil de Usuario (Profile)
$router->add('profile/mi-perfil', 'ProfileController@miPerfil', 'GET');
$router->add('perfil', 'ProfileController@miPerfil', 'GET');
$router->add('profile/guardar-cuenta', 'ProfileController@guardarCuenta', 'POST');
$router->add('profile/cambiar-password', 'ProfileController@cambiarPassword', 'POST');
$router->add('profile/comprobante/{codigo}', 'ProfileController@comprobante', 'GET');

// Rutas de Asesorías de Visas (Visas)
$router->add('visas', 'VisaController@index', 'GET');
$router->add('visas/solicitar', 'VisaController@procesarSolicitud', 'POST');
$router->add('admin/visas', 'VisaController@adminIndex', 'GET');
$router->add('admin/visas/update-estado', 'VisaController@adminUpdateEstado', 'POST');

// Rutas del CRUD de Paquetes (Admin)
$router->add('admin/paquetes', 'PaqueteController@index', 'GET');
$router->add('admin/paquetes/create', 'PaqueteController@crear', 'GET');
$router->add('admin/paquetes/store', 'PaqueteController@guardar', 'POST');
$router->add('admin/paquetes/edit/{id}', 'PaqueteController@editar', 'GET');
$router->add('admin/paquetes/update/{id}', 'PaqueteController@actualizar', 'POST');
$router->add('admin/paquetes/delete/{id}', 'PaqueteController@eliminar', 'POST');
$router->add('admin/paquetes/toggle-disponible/{id}', 'PaqueteController@toggleDisponible', 'POST');

// Rutas de Contacto
$router->add('contacto', 'ContactoController@index', 'GET');
$router->add('contacto', 'ContactoController@enviar', 'POST');

// Rutas de Páginas Legales
$router->add('terminos', 'LegalController@terminos', 'GET');
$router->add('privacidad', 'LegalController@privacidad', 'GET');
$router->add('cancelaciones', 'LegalController@cancelaciones', 'GET');
$router->add('libro-reclamaciones', 'LegalController@reclamaciones', 'GET');

// Rutas del Libro de Reclamaciones
$router->add('reclamaciones', 'ReclamacionController@index', 'GET');
$router->add('reclamaciones', 'ReclamacionController@store', 'POST');
$router->add('admin/reclamaciones', 'ReclamacionController@adminIndex', 'GET');
$router->add('admin/reclamaciones/update-estado', 'ReclamacionController@adminUpdate', 'POST');

// Rutas de Administración General (Admin)
$router->add('admin/dashboard', 'AdminController@dashboard', 'GET');
$router->add('admin/ventas', 'AdminController@ventas', 'GET');
$router->add('admin/ventas/exportar', 'AdminController@exportarVentas', 'GET');
$router->add('admin/reservas', 'AdminController@reservas', 'GET');
$router->add('admin/reservas/cambiar-estado', 'AdminController@cambiarEstadoReserva', 'POST');
$router->add('admin/reservas/exportar', 'AdminController@exportarReservas', 'GET');
$router->add('admin/usuarios', 'AdminController@usuarios', 'GET');
$router->add('admin/usuarios/registrar-administrador', 'AdminController@registrarAdministrador', 'POST');
$router->add('admin/usuarios/update-rol', 'AdminController@updateRol', 'POST');
$router->add('admin/ajustes', 'AdminController@ajustes', 'GET');
$router->add('admin/ajustes', 'AdminController@guardarAjustes', 'POST');
$router->add('admin/ajustes/cambiar-password', 'AdminController@cambiarPassword', 'POST');

// 9. Compatibilidad de alias de checkout por slug: /checkout/{slug} => /checkout?paquete={slug}
$dispatchUri = $_SERVER['REQUEST_URI'];
$uriPath = parse_url($dispatchUri, PHP_URL_PATH) ?? '';

// Normalizamos el path usando la misma lógica que el Router:
// extraemos el prefijo del proyecto desde SCRIPT_FILENAME vs DOCUMENT_ROOT
$scriptRealDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME'] ?? ''));
$docRoot = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/'));
$projectPrefix = '';
if ($docRoot !== '' && strpos($scriptRealDir, $docRoot) === 0) {
    $projectPrefix = substr($scriptRealDir, strlen($docRoot));
    // Quitar /public del prefijo para que coincida con la URL real
    if (substr($projectPrefix, -7) === '/public') {
        $projectPrefix = substr($projectPrefix, 0, -7);
    }
}
$normalizedPath = $uriPath;
if ($projectPrefix !== '' && strpos($normalizedPath, $projectPrefix) === 0) {
    $normalizedPath = substr($normalizedPath, strlen($projectPrefix));
}
// Acceso directo a /public/: recortar el segmento inicial para consistencia con el Router
if (strpos($normalizedPath, '/public') === 0) {
    $normalizedPath = substr($normalizedPath, strlen('/public'));
}
$normalizedPath = trim($normalizedPath, '/');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && preg_match('#^checkout/([^/]+)$#', $normalizedPath, $matches)) {
    $slug = urldecode($matches[1]);
    $_GET['paquete'] = $slug;
    $_REQUEST['paquete'] = $slug;
    $dispatchUri = BASE_URL . '/checkout?paquete=' . rawurlencode($slug);
}

// 10. Despachar la petición actual
$router->dispatch($dispatchUri);

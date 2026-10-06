<?php

declare(strict_types=1);

// Funcion del archivo: Coordina las pantallas y acciones del panel administrativo.
namespace App\Controllers;

use App\Config\Database;
use App\Helper\Config;
use App\Models\Reporte;
use App\Models\Reserva;
use App\Models\Usuario;
use Exception;

/**
 * Clase AdminController
 * Gestiona el panel de administración general (Dashboard, Ventas, Usuarios, Ajustes).
 */
class AdminController
{
    /**
     * Constructor del controlador.
     * Protege todos los accesos asegurando rol de administrador (id_rol = 2).
     */
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario']) || (int)$_SESSION['usuario']['id_rol'] !== 2) {
            $_SESSION['error'] = "Acceso denegado. Se requieren privilegios de administrador.";
            header('Location: ' . BASE_URL . '/auth/login');
            exit();
        }
    }

    /**
     * Muestra el Dashboard Administrativo con KPIs y gráficos reales.
     *
     * @return void
     */
    public function dashboard(): void
    {
        try {
            // 1. Obtener KPIs desde el modelo Reporte
            $kpis = Reporte::getDashboardKPIs();
            $ingresosTotal  = $kpis['ingresos_total'];
            $reservasCount  = $kpis['reservas_count'];
            $visasCount     = $kpis['visas_count'];
            $clientesCount  = $kpis['clientes_count'];

            // 2. Últimas reservas desde Reporte
            $ultimasReservas = Reporte::getRecentBookings(5);

            // 3. Ingresos mensuales desde Reporte
            $mesesLabels = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
            $ingresosMes = Reporte::getMonthlyIncome();

            // 4. Distribución por destinos desde Reporte
            $destinosDistribucion = Reporte::getDestinosDistribucion();

        } catch (Exception $e) {
            error_log("Error en AdminController::dashboard: " . $e->getMessage());
            $_SESSION['error'] = "No se pudieron cargar las estadísticas del dashboard.";
            $ingresosTotal = 0.00;
            $reservasCount = 0;
            $visasCount = 0;
            $clientesCount = 0;
            $ultimasReservas = [];
            $ingresosMes = array_fill(0, 12, 0);
            $destinosDistribucion = [];
        }

        $title = "Dashboard · Admin AJT";
        $pageKey = "dashboard";

        $baseViewsDir = dirname(__DIR__) . '/Views/';
        require_once $baseViewsDir . 'layouts/admin_header.php';
        require_once $baseViewsDir . 'admin/dashboard.php';
        require_once $baseViewsDir . 'layouts/admin_footer.php';
    }

    /**
     * Muestra el módulo de Ventas y Reportes.
     *
     * @return void
     */
    public function ventas(): void
    {
        try {
            $filtroMetodo = isset($_GET['metodo']) ? trim($_GET['metodo']) : '';
            $data = Reporte::getVentasData($filtroMetodo !== '' ? $filtroMetodo : null);
            $totalVendido     = $data['total_vendido'];
            $pagosConfirmados = $data['pagos_confirmados'];
            $pagosPendientes  = $data['pagos_pendientes'];
            $ticketPromedio   = $data['ticket_promedio'];
            $ventasMes        = $data['ventas_mes'];
            $transacciones    = $data['transacciones'];
            $topPaquetes      = $data['top_paquetes'];
            $maxTotal         = $data['max_total'];

            // Métodos de pago para el filtro
            $db = Database::getConnection();
            $stmtMetodos = $db->query("SELECT nombre FROM metodos_pago WHERE activo = 1 ORDER BY nombre");
            $metodosPago = $stmtMetodos->fetchAll();

            // Ingresos mensuales reales para el gráfico de barras
            $ingresosMensuales = Reporte::getMonthlyIncome();

        } catch (Exception $e) {
            error_log("Error en AdminController::ventas: " . $e->getMessage());
            $_SESSION['error'] = "No se pudo cargar la información de ventas.";
            $filtroMetodo = '';
            $totalVendido = 0.00;
            $pagosConfirmados = 0;
            $pagosPendientes = 0;
            $ticketPromedio = 0.00;
            $ventasMes = 0.00;
            $transacciones = [];
            $topPaquetes = [];
            $maxTotal = 1.0;
            $metodosPago = [];
            $ingresosMensuales = array_fill(0, 12, 0);
        }

        $title = "Ventas & Reportes · Admin AJT";
        $pageKey = "ventas";

        $baseViewsDir = dirname(__DIR__) . '/Views/';
        require_once $baseViewsDir . 'layouts/admin_header.php';
        require_once $baseViewsDir . 'admin/ventas.php';
        require_once $baseViewsDir . 'layouts/admin_footer.php';
    }

    /**
     * Muestra el panel de control de Usuarios.
     *
     * @return void
     */
    public function usuarios(): void
    {
        try {
            $data = Reporte::getUsuariosList();
            $totalClientes = $data['total_clientes'];
            $totalAdmins   = $data['total_admins'];
            $usuarios      = $data['usuarios'];

        } catch (Exception $e) {
            error_log("Error en AdminController::usuarios: " . $e->getMessage());
            $_SESSION['error'] = "No se pudieron cargar los datos de usuarios.";
            $totalClientes = 0;
            $totalAdmins = 0;
            $usuarios = [];
        }

        $title = "Usuarios · Admin AJT";
        $pageKey = "usuarios";

        $baseViewsDir = dirname(__DIR__) . '/Views/';
        require_once $baseViewsDir . 'layouts/admin_header.php';
        require_once $baseViewsDir . 'admin/usuarios.php';
        require_once $baseViewsDir . 'layouts/admin_footer.php';
    }

    /**
     * Registra una cuenta nueva con privilegios de administrador.
     */
    public function registrarAdministrador(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/usuarios?tab=admins');
            exit();
        }

        $nombres = trim($_POST['nombres'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $telefono = trim($_POST['telefono'] ?? '');
        $dni = trim($_POST['dni_pasaporte'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        $errores = [];

        if (mb_strlen($nombres) < 2 || mb_strlen($nombres) > 100 || !preg_match('/^[\p{L}\s]+$/u', $nombres)) {
            $errores[] = 'Ingrese nombres válidos (solo letras y espacios).';
        }
        if (mb_strlen($apellidos) < 2 || mb_strlen($apellidos) > 100 || !preg_match('/^[\p{L}\s]+$/u', $apellidos)) {
            $errores[] = 'Ingrese apellidos válidos (solo letras y espacios).';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'Ingrese un correo electrónico válido.';
        }
        if ($telefono !== '') {
            $telefonoDigits = preg_replace('/\D/', '', $telefono);
            if (strlen($telefonoDigits) < 7 || strlen($telefonoDigits) > 15) {
                $errores[] = 'El teléfono debe tener entre 7 y 15 dígitos.';
            }
        }
        if (mb_strlen($dni) < 6 || mb_strlen($dni) > 20) {
            $errores[] = 'El DNI o pasaporte debe tener entre 6 y 20 caracteres.';
        }
        if (strlen($password) < 8) {
            $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
        }
        if ($password !== $passwordConfirm) {
            $errores[] = 'Las contraseñas no coinciden.';
        }

        try {
            if (!$errores && Usuario::findByEmail($email) !== null) {
                $errores[] = 'Ya existe una cuenta registrada con ese correo.';
            }

            if (!$errores) {
                Usuario::create([
                    'id_rol' => 2,
                    'nombres' => $nombres,
                    'apellidos' => $apellidos,
                    'email' => $email,
                    'password_hash' => password_hash($password, PASSWORD_BCRYPT),
                    'dni_pasaporte' => $dni,
                    'telefono' => $telefono !== '' ? $telefono : null,
                ]);
                $_SESSION['success'] = 'Administrador registrado correctamente. Ya puede iniciar sesión.';
                header('Location: ' . BASE_URL . '/admin/usuarios?tab=admins');
                exit();
            }
        } catch (Exception $e) {
            error_log('Error en AdminController::registrarAdministrador: ' . $e->getMessage());
            $errores[] = 'No se pudo registrar el administrador. Intente nuevamente.';
        }

        $_SESSION['error'] = implode(' ', $errores);
        $_SESSION['admin_form_data'] = compact('nombres', 'apellidos', 'email', 'telefono', 'dni');
        header('Location: ' . BASE_URL . '/admin/usuarios?tab=admins&new=1');
        exit();
    }

    /**
     * Procesa la actualización del rol de un usuario (POST).
     *
     * @return void
     */
    public function updateRol(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/usuarios');
            exit();
        }

        $id_usuario = isset($_POST['id_usuario']) ? (int)$_POST['id_usuario'] : 0;
        $id_rol = isset($_POST['id_rol']) ? (int)$_POST['id_rol'] : 0;

        if ($id_usuario <= 0 || ($id_rol !== 1 && $id_rol !== 2)) {
            $_SESSION['error'] = "Datos de rol o usuario inválidos.";
            header('Location: ' . BASE_URL . '/admin/usuarios');
            exit();
        }

        // Prevenir que el admin se quite el rol a sí mismo
        if ($id_usuario === (int)$_SESSION['usuario']['id_usuario']) {
            $_SESSION['error'] = "No puede modificar los privilegios de su propia cuenta.";
            header('Location: ' . BASE_URL . '/admin/usuarios');
            exit();
        }

        if (Usuario::updateRol($id_usuario, $id_rol)) {
            $_SESSION['success'] = "El rol del usuario fue actualizado correctamente.";
        } else {
            $_SESSION['error'] = "No se pudo actualizar el rol del usuario.";
        }

        header('Location: ' . BASE_URL . '/admin/usuarios');
        exit();
    }

    /**
     * Carga los valores de configuración desde la tabla configuracion.
     *
     * @return array Clave => valor
     */
    private function cargarConfiguracion(): array
    {
        return Config::getAll();
    }

    /**
     * Muestra la vista de Ajustes del Sistema con datos reales.
     *
     * @return void
     */
    public function ajustes(): void
    {
        $config = $this->cargarConfiguracion();

        $title = "Ajustes · Admin AJT";
        $pageKey = "ajustes";

        $baseViewsDir = dirname(__DIR__) . '/Views/';
        require_once $baseViewsDir . 'layouts/admin_header.php';
        require_once $baseViewsDir . 'admin/ajustes.php';
        require_once $baseViewsDir . 'layouts/admin_footer.php';
    }

    /**
     * Exporta las transacciones de pagos como archivo CSV.
     *
     * @return void
     */
    public function exportarVentas(): void
    {
        try {
            $db = Database::getConnection();

            $sql = "SELECT pa.created_at AS fecha_pago,
                           r.codigo_reserva,
                           CONCAT(u.nombres, ' ', u.apellidos) AS cliente_nombre,
                           p.nombre AS paquete_nombre,
                           pa.monto,
                           mp.nombre AS metodo_pago,
                           pa.estado
                    FROM pagos pa
                    INNER JOIN usuarios u ON pa.id_usuario = u.id_usuario
                    INNER JOIN reservas r ON pa.id_reserva = r.id_reserva
                    INNER JOIN detalle_reservas dr ON r.id_reserva = dr.id_reserva
                    INNER JOIN paquetes p ON dr.id_paquete = p.id_paquete
                    LEFT JOIN metodos_pago mp ON pa.id_metodo_pago = mp.id_metodo
                    ORDER BY pa.id_pago DESC";

            $stmt = $db->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll();

            // Cabeceras de CSV con UTF-8 BOM
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="ventas_viajes_ajt_' . date('Y-m-d') . '.csv"');
            header('Cache-Control: no-store, no-cache');
            header('Pragma: no-cache');

            $handle = fopen('php://output', 'w');

            // UTF-8 BOM para compatibilidad con Excel
            fwrite($handle, "\xEF\xBB\xBF");

            // Cabeceras de columna
            fputcsv($handle, ['Fecha', 'Código Reserva', 'Cliente', 'Paquete', 'Monto (S/)', 'Método de Pago', 'Estado']);

            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $estadoLabel = match ($row['estado']) {
                        'validado'  => 'Validado',
                        'rechazado' => 'Rechazado',
                        default     => 'Pendiente',
                    };
                    fputcsv($handle, [
                        $row['fecha_pago'],
                        $row['codigo_reserva'],
                        $row['cliente_nombre'],
                        $row['paquete_nombre'],
                        number_format((float)$row['monto'], 2, '.', ''),
                        $row['metodo_pago'] ?? 'Sin registrar',
                        $estadoLabel,
                    ]);
                }
            } else {
                fputcsv($handle, ['No hay transacciones registradas']);
            }

            fclose($handle);
            exit();
        } catch (\Exception $e) {
            error_log("Error en AdminController::exportarVentas: " . $e->getMessage());
            $_SESSION['error'] = "Error al exportar las ventas. Intente nuevamente.";
            header('Location: ' . BASE_URL . '/admin/ventas');
            exit();
        }
    }

    /**
     * Exporta las reservas como archivo CSV.
     *
     * @return void
     */
    public function exportarReservas(): void
    {
        try {
            $db = Database::getConnection();

            $sql = "SELECT r.codigo_reserva,
                           CONCAT(u.nombres, ' ', u.apellidos) AS cliente,
                           p.nombre AS paquete,
                           dr.fecha_viaje,
                           dr.cantidad_pasajeros AS viajeros,
                           dr.precio_unitario,
                           r.descuento,
                           r.estado
                    FROM reservas r
                    INNER JOIN usuarios u ON r.id_usuario = u.id_usuario
                    INNER JOIN detalle_reservas dr ON r.id_reserva = dr.id_reserva
                    INNER JOIN paquetes p ON dr.id_paquete = p.id_paquete
                    ORDER BY r.id_reserva DESC";

            $stmt = $db->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll();

            // Cabeceras de CSV con UTF-8 BOM
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="reservas_viajes_ajt_' . date('Y-m-d') . '.csv"');
            header('Cache-Control: no-store, no-cache');
            header('Pragma: no-cache');

            $handle = fopen('php://output', 'w');

            // UTF-8 BOM para compatibilidad con Excel
            fwrite($handle, "\xEF\xBB\xBF");

            // Cabeceras de columna
            fputcsv($handle, ['Código', 'Cliente', 'Paquete', 'Fecha Viaje', 'Viajeros', 'Total (S/)', 'Estado']);

            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $estadoLabel = match ($row['estado']) {
                        'pagado'   => 'Pagado',
                        'cancelado' => 'Cancelado',
                        default     => 'Pendiente',
                    };
                    // Calcular total con IGV
                    $subtotal = (float)$row['precio_unitario'] * (int)$row['viajeros'];
                    $descuento = (float)$row['descuento'];
                    $baseImponible = $subtotal - $descuento;
                    $igv = round($baseImponible * 0.18, 2);
                    $totalConIGV = round($baseImponible + $igv, 2);
                    fputcsv($handle, [
                        $row['codigo_reserva'],
                        $row['cliente'],
                        $row['paquete'],
                        $row['fecha_viaje'],
                        (int)$row['viajeros'],
                        number_format($totalConIGV, 2, '.', ''),
                        $estadoLabel,
                    ]);
                }
            } else {
                fputcsv($handle, ['No hay reservas registradas']);
            }

            fclose($handle);
            exit();
        } catch (\Exception $e) {
            error_log("Error en AdminController::exportarReservas: " . $e->getMessage());
            $_SESSION['error'] = "Error al exportar las reservas. Intente nuevamente.";
            header('Location: ' . BASE_URL . '/admin/ventas');
            exit();
        }
    }

    /**
     * Muestra la gestión de reservas con filtro por estado.
     *
     * @return void
     */
    public function reservas(): void
    {
        try {
            $db = Database::getConnection();

            $filtro = isset($_GET['estado']) ? trim($_GET['estado']) : 'todas';
            $estadosValidos = ['todas', 'pendiente', 'pagado', 'cancelado'];
            if (!in_array($filtro, $estadosValidos)) {
                $filtro = 'todas';
            }

            $sql = "SELECT r.id_reserva, r.codigo_reserva, r.estado, r.created_at, r.descuento,
                           CONCAT(u.nombres, ' ', u.apellidos) AS cliente_nombre,
                           u.email AS cliente_email,
                           p.nombre AS paquete_nombre,
                           dr.fecha_viaje, dr.cantidad_pasajeros AS viajeros,
                           dr.precio_unitario,
                           pa.numero_operacion,
                           pa.estado AS pago_estado,
                           pa.comprobante_url,
                           mp.nombre AS metodo_pago_nombre
                    FROM reservas r
                    INNER JOIN usuarios u ON r.id_usuario = u.id_usuario
                    INNER JOIN detalle_reservas dr ON r.id_reserva = dr.id_reserva
                    INNER JOIN paquetes p ON dr.id_paquete = p.id_paquete
                    LEFT JOIN pagos pa ON r.id_reserva = pa.id_reserva
                    LEFT JOIN metodos_pago mp ON pa.id_metodo_pago = mp.id_metodo";

            $params = [];
            if ($filtro !== 'todas') {
                $sql .= " WHERE r.estado = :estado";
                $params[':estado'] = $filtro;
            }

            $sql .= " ORDER BY r.id_reserva DESC";

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $reservas = $stmt->fetchAll();

        } catch (Exception $e) {
            error_log("Error en AdminController::reservas: " . $e->getMessage());
            $_SESSION['error'] = "No se pudieron cargar las reservas.";
            $reservas = [];
        }

        $title = "Reservas · Admin AJT";
        $pageKey = "reservas";

        $baseViewsDir = dirname(__DIR__) . '/Views/';
        require_once $baseViewsDir . 'layouts/admin_header.php';
        require_once $baseViewsDir . 'admin/reservas.php';
        require_once $baseViewsDir . 'layouts/admin_footer.php';
    }

    /**
     * Cambia el estado de una reserva (POST).
     *
     * @return void
     */
    public function cambiarEstadoReserva(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/reservas');
            exit();
        }

        $id_reserva = isset($_POST['id_reserva']) ? (int)$_POST['id_reserva'] : 0;
        $nuevo_estado = isset($_POST['nuevo_estado']) ? trim($_POST['nuevo_estado']) : '';

        if ($id_reserva <= 0 || !in_array($nuevo_estado, ['pendiente', 'pagado', 'cancelado'], true)) {
            $_SESSION['error'] = "Datos de reserva o estado no válidos.";
            header('Location: ' . BASE_URL . '/admin/reservas');
            exit();
        }

        if (Reserva::updateEstado($id_reserva, $nuevo_estado)) {
            $labels = ['pendiente' => 'Pendiente', 'pagado' => 'Confirmada', 'cancelado' => 'Cancelada'];
            $_SESSION['success'] = "Reserva actualizada a estado '" . ($labels[$nuevo_estado] ?? $nuevo_estado) . "' con éxito. El pago asociado también fue sincronizado.";
        } else {
            $_SESSION['error'] = "No se pudo actualizar el estado de la reserva.";
        }

        header('Location: ' . BASE_URL . '/admin/reservas');
        exit();
    }

    /**
     * Procesa el guardado de ajustes del sistema (POST).
     *
     * @return void
     */
    public function guardarAjustes(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/ajustes');
            exit();
        }

        // Campos configurables: clave => tipo de transformación
        $definiciones = [
            'dias_anticipacion_reserva' => 'int',
            'agencia_nombre'            => 'str',
            'agencia_razon_social'      => 'str',
            'agencia_ruc'               => 'str',
            'agencia_direccion'         => 'str',
            'agencia_horario'           => 'str',
            'agencia_telefono'          => 'str',
            'agencia_whatsapp'          => 'str',
            'agencia_email'             => 'str',
            'agencia_facebook'          => 'str',
            'agencia_instagram'         => 'str',
            'agencia_tiktok'            => 'str',
            'agencia_youtube'           => 'str',
            'yape_numero'               => 'str',
            'yape_titular'              => 'str',
            'plin_numero'               => 'str',
            'plin_titular'              => 'str',
            'banco_nombre'              => 'str',
            'banco_cuenta'              => 'str',
            'banco_cci'                 => 'str',
        ];

        $campos = [];
        foreach ($definiciones as $clave => $tipo) {
            $valor = $_POST[$clave] ?? '';
            if ($tipo === 'int') {
                $valor = (int)$valor;
                if ($clave === 'dias_anticipacion_reserva' && ($valor < 1 || $valor > 365)) {
                    $valor = 30;
                }
            }
            $campos[$clave] = $valor;
        }

        $guardados = Config::saveMany($campos);

        if ($guardados > 0) {
            $_SESSION['success'] = "Se actualizaron {$guardados} campos de configuración correctamente.";
        } else {
            $_SESSION['error'] = "No se pudo guardar la configuración. Intente nuevamente.";
        }

        header('Location: ' . BASE_URL . '/admin/ajustes');
        exit();
    }

    /**
     * Cambia la contraseña del administrador autenticado (POST).
     *
     * @return void
     */
    public function cambiarPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/ajustes');
            exit();
        }

        $passwordActual = $_POST['password_actual'] ?? '';
        $passwordNueva  = $_POST['password_nueva'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        if ($passwordActual === '' || $passwordNueva === '' || $passwordConfirm === '') {
            $_SESSION['error'] = "Todos los campos de contraseña son obligatorios.";
            header('Location: ' . BASE_URL . '/admin/ajustes');
            exit();
        }

        if (strlen($passwordNueva) < 6) {
            $_SESSION['error'] = "La nueva contraseña debe tener al menos 6 caracteres.";
            header('Location: ' . BASE_URL . '/admin/ajustes');
            exit();
        }

        if ($passwordNueva !== $passwordConfirm) {
            $_SESSION['error'] = "La nueva contraseña y su confirmación no coinciden.";
            header('Location: ' . BASE_URL . '/admin/ajustes');
            exit();
        }

        $idUsuario = (int)$_SESSION['usuario']['id_usuario'];

        if (!Usuario::verifyPassword($idUsuario, $passwordActual)) {
            $_SESSION['error'] = "La contraseña actual no es correcta.";
            header('Location: ' . BASE_URL . '/admin/ajustes');
            exit();
        }

        if (Usuario::cambiarPassword($idUsuario, password_hash($passwordNueva, PASSWORD_BCRYPT))) {
            $_SESSION['success'] = "Contraseña actualizada correctamente.";
        } else {
            $_SESSION['error'] = "Error del sistema al cambiar la contraseña.";
        }

        header('Location: ' . BASE_URL . '/admin/ajustes');
        exit();
    }
}

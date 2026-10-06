<?php

declare(strict_types=1);

// Funcion del archivo: Calcula metricas y listados para dashboard, ventas y usuarios.
namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

/**
 * Clase Reporte
 * Modelo que centraliza consultas de KPIs, ventas, usuarios y estadísticas
 * previamente dispersas en AdminController como SQL crudo.
 */
class Reporte
{
    /**
     * Retorna los KPIs principales del dashboard administrativo.
     *
     * @return array{ingresos_total: float, reservas_count: int, visas_count: int, clientes_count: int, conversion_rate: float}
     */
    public static function getDashboardKPIs(): array
    {
        try {
            $db = Database::getConnection();

            // Ingresos totales (pagos validados)
            $stmt = $db->query("SELECT SUM(monto) FROM pagos WHERE estado = 'validado'");
            $ingresosTotal = (float)$stmt->fetchColumn();

            // Conteo de reservas
            $stmt = $db->query("SELECT COUNT(*) FROM reservas");
            $reservasCount = (int)$stmt->fetchColumn();

            // Conteo de solicitudes de visa
            $stmt = $db->query("SELECT COUNT(*) FROM solicitudes_visa");
            $visasCount = (int)$stmt->fetchColumn();

            // Conteo de clientes (usuarios con rol 1)
            $stmt = $db->query("SELECT COUNT(*) FROM usuarios WHERE id_rol = 1");
            $clientesCount = (int)$stmt->fetchColumn();

            // Tasa de conversión: reservas / clientes
            $conversionRate = $clientesCount > 0
                ? round(($reservasCount / $clientesCount) * 100, 1)
                : 0.0;

            return [
                'ingresos_total'  => $ingresosTotal,
                'reservas_count'  => $reservasCount,
                'visas_count'     => $visasCount,
                'clientes_count'  => $clientesCount,
                'conversion_rate' => $conversionRate,
            ];
        } catch (PDOException $e) {
            error_log("Error en Reporte::getDashboardKPIs: " . $e->getMessage());
            throw new PDOException("Error al obtener los KPIs del dashboard.");
        }
    }

    /**
     * Retorna las últimas reservas con datos de usuario, paquete y monto.
     *
     * @param int $limit Cantidad máxima de registros (default 10)
     * @return array
     */
    public static function getRecentBookings(int $limit = 10): array
    {
        try {
            $db = Database::getConnection();
            $sql = "SELECT r.*, u.nombres, u.apellidos, u.email,
                           (SELECT p.nombre FROM detalle_reservas dr INNER JOIN paquetes p ON dr.id_paquete = p.id_paquete WHERE dr.id_reserva = r.id_reserva LIMIT 1) AS paquete_nombre,
                           (SELECT SUM(dr.precio_unitario * dr.cantidad_pasajeros) FROM detalle_reservas dr WHERE dr.id_reserva = r.id_reserva) AS monto_total
                    FROM reservas r
                    INNER JOIN usuarios u ON r.id_usuario = u.id_usuario
                    ORDER BY r.id_reserva DESC
                    LIMIT :limite";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':limite', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Reporte::getRecentBookings: " . $e->getMessage());
            throw new PDOException("Error al obtener las últimas reservas.");
        }
    }

    /**
     * Retorna los ingresos mensuales como un array de 12 posiciones (Ene-Dic).
     *
     * @param int|null $year Año a filtrar (null = todos los años)
     * @return array Array de 12 floats indexado 0..11
     */
    public static function getMonthlyIncome(?int $year = null): array
    {
        $ingresosMes = array_fill(0, 12, 0.00);

        try {
            $db = Database::getConnection();
            $sql = "SELECT MONTH(created_at) AS mes, SUM(monto) AS total
                    FROM pagos
                    WHERE estado = 'validado'";
            $params = [];

            if ($year !== null) {
                $sql .= " AND YEAR(created_at) = :anio";
                $params[':anio'] = $year;
            }

            $sql .= " GROUP BY MONTH(created_at)";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            while ($row = $stmt->fetch()) {
                $m = (int)$row['mes'] - 1;
                if ($m >= 0 && $m < 12) {
                    $ingresosMes[$m] = (float)$row['total'];
                }
            }
        } catch (PDOException $e) {
            error_log("Error en Reporte::getMonthlyIncome: " . $e->getMessage());
        }

        return $ingresosMes;
    }

    /**
     * Retorna la distribución de reservas por destino (paquete).
     *
     * @param int $limit Cantidad de destinos Top (default 4)
     * @return array Cada elemento: ['nombre', 'pct', 'color', 'qty']
     */
    public static function getDestinosDistribucion(int $limit = 4): array
    {
        $colores = ['#1b2c8a', '#3a51d1', '#ffd60a', '#94a3b8'];

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare(
                "SELECT p.nombre, COUNT(dr.id_paquete) AS qty
                 FROM detalle_reservas dr
                 INNER JOIN paquetes p ON dr.id_paquete = p.id_paquete
                 GROUP BY dr.id_paquete
                 ORDER BY qty DESC
                 LIMIT :limite"
            );
            $stmt->bindValue(':limite', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $destinosRaw = $stmt->fetchAll();

            $totalQty = 0;
            foreach ($destinosRaw as $d) {
                $totalQty += (int)$d['qty'];
            }

            $destinosDistribucion = [];
            foreach ($destinosRaw as $idx => $d) {
                $pct = $totalQty > 0 ? round(((int)$d['qty'] / $totalQty) * 100) : 0;
                $nombreCorto = explode(',', $d['nombre'])[0];
                $destinosDistribucion[] = [
                    'nombre' => $nombreCorto,
                    'pct'    => $pct,
                    'color'  => $colores[$idx] ?? '#94a3b8',
                    'qty'    => (int)$d['qty'],
                ];
            }

            return $destinosDistribucion;
        } catch (PDOException $e) {
            error_log("Error en Reporte::getDestinosDistribucion: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retorna los datos del módulo de Ventas y Reportes.
     *
     * @param string|null $filtroMetodo Filtra por nombre de método de pago (null = todos)
     * @return array{total_vendido: float, pagos_confirmados: int, pagos_pendientes: int, ticket_promedio: float, ventas_mes: float, transacciones: array, top_paquetes: array, max_total: float}
     */
    public static function getVentasData(?string $filtroMetodo = null): array
    {
        try {
            $db = Database::getConnection();

            // Total vendido (pagos validados)
            $stmt = $db->query("SELECT COALESCE(SUM(monto), 0) FROM pagos WHERE estado = 'validado'");
            $totalVendido = (float)$stmt->fetchColumn();

            // Pagos confirmados
            $stmt = $db->query("SELECT COUNT(*) FROM pagos WHERE estado = 'validado'");
            $pagosConfirmados = (int)$stmt->fetchColumn();

            // Pagos pendientes
            $stmt = $db->query("SELECT COUNT(*) FROM pagos WHERE estado = 'pendiente'");
            $pagosPendientes = (int)$stmt->fetchColumn();

            // Ticket promedio = total vendido / pagos confirmados
            $ticketPromedio = $pagosConfirmados > 0 ? $totalVendido / $pagosConfirmados : 0.0;

            // Ventas del mes actual
            $stmt = $db->query("SELECT COALESCE(SUM(monto), 0) FROM pagos WHERE estado = 'validado' AND MONTH(created_at) = MONTH(CURRENT_DATE)");
            $ventasMes = (float)$stmt->fetchColumn();

            // Listado de transacciones (enfoque financiero)
            $sqlTrans = "SELECT r.codigo_reserva,
                                CONCAT(u.nombres, ' ', u.apellidos) AS cliente_nombre,
                                p.nombre AS paquete_nombre,
                                mp.nombre AS metodo_pago_nombre,
                                pa.monto AS monto_pago,
                                (dr.precio_unitario * dr.cantidad_pasajeros - r.descuento) AS monto_total,
                                pa.estado AS pago_estado,
                                pa.comprobante_url,
                                pa.created_at
                         FROM reservas r
                         INNER JOIN usuarios u ON r.id_usuario = u.id_usuario
                         INNER JOIN detalle_reservas dr ON r.id_reserva = dr.id_reserva
                         INNER JOIN paquetes p ON dr.id_paquete = p.id_paquete
                         LEFT JOIN pagos pa ON r.id_reserva = pa.id_reserva
                         LEFT JOIN metodos_pago mp ON pa.id_metodo_pago = mp.id_metodo";

            $params = [];
            if ($filtroMetodo !== null && $filtroMetodo !== '') {
                $sqlTrans .= " WHERE mp.nombre = :metodo";
                $params[':metodo'] = $filtroMetodo;
            }

            $sqlTrans .= " ORDER BY r.id_reserva DESC";
            $stmtTrans = $db->prepare($sqlTrans);
            $stmtTrans->execute($params);
            $transacciones = $stmtTrans->fetchAll();

            // Top paquetes
            $sqlTop = "SELECT p.nombre, SUM(dr.precio_unitario * dr.cantidad_pasajeros) AS total
                       FROM detalle_reservas dr
                       INNER JOIN paquetes p ON dr.id_paquete = p.id_paquete
                       GROUP BY dr.id_paquete
                       ORDER BY total DESC LIMIT 4";
            $stmtTop = $db->query($sqlTop);
            $topPaquetes = $stmtTop->fetchAll();

            $maxTotal = 1.0;
            if (!empty($topPaquetes)) {
                $maxTotal = (float)$topPaquetes[0]['total'];
                if ($maxTotal <= 0) {
                    $maxTotal = 1.0;
                }
            }

            return [
                'total_vendido'     => $totalVendido,
                'pagos_confirmados' => $pagosConfirmados,
                'pagos_pendientes'  => $pagosPendientes,
                'ticket_promedio'   => $ticketPromedio,
                'ventas_mes'        => $ventasMes,
                'transacciones'     => $transacciones,
                'top_paquetes'      => $topPaquetes,
                'max_total'         => $maxTotal,
            ];
        } catch (PDOException $e) {
            error_log("Error en Reporte::getVentasData: " . $e->getMessage());
            throw new PDOException("Error al obtener los datos de ventas.");
        }
    }

    /**
     * Retorna los datos del módulo de Usuarios (total clientes, admins y listado completo).
     *
     * @return array{total_clientes: int, total_admins: int, usuarios: array}
     */
    public static function getUsuariosList(): array
    {
        try {
            $db = Database::getConnection();

            // Total clientes
            $stmt = $db->query("SELECT COUNT(*) FROM usuarios WHERE id_rol = 1");
            $totalClientes = (int)$stmt->fetchColumn();

            // Total admins
            $stmt = $db->query("SELECT COUNT(*) FROM usuarios WHERE id_rol = 2");
            $totalAdmins = (int)$stmt->fetchColumn();

            // Listado completo de usuarios
            $sqlUsers = "SELECT u.*, r.nombre_rol,
                                (SELECT COUNT(*) FROM reservas res WHERE res.id_usuario = u.id_usuario) AS reservas_count
                         FROM usuarios u
                         INNER JOIN roles r ON u.id_rol = r.id_rol
                         ORDER BY u.id_usuario DESC";
            $stmtUsers = $db->query($sqlUsers);
            $usuarios = $stmtUsers->fetchAll();

            return [
                'total_clientes' => $totalClientes,
                'total_admins'   => $totalAdmins,
                'usuarios'       => $usuarios,
            ];
        } catch (PDOException $e) {
            error_log("Error en Reporte::getUsuariosList: " . $e->getMessage());
            throw new PDOException("Error al obtener el listado de usuarios.");
        }
    }
}

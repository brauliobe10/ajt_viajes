<?php

declare(strict_types=1);

// Funcion del archivo: Registra y consulta reservas, viajeros, pagos y comprobantes.
namespace App\Models;

use App\Config\Database;
use PDOException;

/**
 * Clase Reserva
 * Modelo transaccional para la gestión de reservas, detalles, pasajeros y pagos.
 */
class Reserva
{
    /**
     * Registra una nueva reserva completa de forma transaccional.
     *
     * @param array $reservaData
     * @param array $detalleData
     * @param array $viajeros
     * @param array $pagoData
     * @return string|null Retorna el código de reserva único (ej. AJT-XXXXXX) o null si falla.
     */
    public static function create(array $reservaData, array $detalleData, array $viajeros, array $pagoData): ?string
    {
        $db = null;
        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            // 1. Generar código de reserva único (ej. AJT-XXXXXX)
            $codigo = $reservaData['codigo_reserva'] ?? ('AJT-' . str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT));
            
            // 2. Insertar cabecera reserva
            $sqlRes = "INSERT INTO reservas (id_usuario, codigo_reserva, descuento, estado, telefono_pasajero, created_at)
                       VALUES (:id_usuario, :codigo, :descuento, :estado, :telefono_pasajero, NOW())";
            $stmtRes = $db->prepare($sqlRes);
            $stmtRes->execute([
                ':id_usuario' => (int)$reservaData['id_usuario'],
                ':codigo' => $codigo,
                ':descuento' => (float)($reservaData['descuento'] ?? 0.00),
                ':estado' => $reservaData['estado'] ?? 'pendiente',
                ':telefono_pasajero' => $reservaData['telefono_pasajero'] ?? null
            ]);
            $idReserva = (int)$db->lastInsertId();

            // 3. Insertar detalle reserva
            $sqlDet = "INSERT INTO detalle_reservas (id_reserva, id_paquete, fecha_viaje, cantidad_pasajeros, precio_unitario)
                       VALUES (:id_reserva, :id_paquete, :fecha_viaje, :cantidad_pasajeros, :precio_unitario)";
            $stmtDet = $db->prepare($sqlDet);
            $stmtDet->execute([
                ':id_reserva' => $idReserva,
                ':id_paquete' => (int)$detalleData['id_paquete'],
                ':fecha_viaje' => $detalleData['fecha_viaje'],
                ':cantidad_pasajeros' => (int)$detalleData['cantidad_pasajeros'],
                ':precio_unitario' => (float)$detalleData['precio_unitario']
            ]);
            $idDetalle = (int)$db->lastInsertId();

            // 4. Insertar viajeros
            $sqlVia = "INSERT INTO viajeros_reserva (id_detalle, nombres, apellidos, num_documento)
                       VALUES (:id_detalle, :nombres, :apellidos, :num_documento)";
            $stmtVia = $db->prepare($sqlVia);
            foreach ($viajeros as $v) {
                $stmtVia->execute([
                    ':id_detalle' => $idDetalle,
                    ':nombres' => trim($v['nombres']),
                    ':apellidos' => trim($v['apellidos']),
                    ':num_documento' => trim($v['num_documento'])
                ]);
            }

            // 5. Insertar pago
            $sqlPag = "INSERT INTO pagos (id_usuario, id_metodo_pago, id_reserva, numero_operacion, monto, estado, comprobante_url, created_at)
                       VALUES (:id_usuario, :id_metodo_pago, :id_reserva, :numero_operacion, :monto, :estado, :comprobante_url, NOW())";
            $stmtPag = $db->prepare($sqlPag);
            $stmtPag->execute([
                ':id_usuario' => (int)$reservaData['id_usuario'],
                ':id_metodo_pago' => (int)$pagoData['id_metodo_pago'],
                ':id_reserva' => $idReserva,
                ':numero_operacion' => trim($pagoData['numero_operacion']),
                ':monto' => (float)$pagoData['monto'],
                ':estado' => $pagoData['estado'] ?? 'pendiente',
                ':comprobante_url' => $pagoData['comprobante_url'] ?? null
            ]);

            $db->commit();
            return $codigo;
        } catch (PDOException $e) {
            if ($db && $db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error en Reserva::create: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene el listado de reservas realizadas por un usuario específico.
     *
     * @param int $id_usuario
     * @return array
     */
    public static function findByUsuario(int $id_usuario): array
    {
        try {
            $db = Database::getConnection();
            $sql = "SELECT r.*, 
                           dr.id_detalle,
                           dr.fecha_viaje, 
                           dr.cantidad_pasajeros, 
                           dr.precio_unitario,
                           p.nombre AS paquete_nombre,
                           p.slug AS paquete_slug,
                           (SELECT url_imagen FROM paquete_imagenes pi WHERE pi.id_paquete = p.id_paquete AND pi.es_principal = 1 LIMIT 1) AS imagen_url,
                           pa.numero_operacion AS pago_operacion,
                           pa.estado AS pago_estado,
                           mp.nombre AS metodo_pago_nombre
                    FROM reservas r
                    INNER JOIN detalle_reservas dr ON r.id_reserva = dr.id_reserva
                    INNER JOIN paquetes p ON dr.id_paquete = p.id_paquete
                    LEFT JOIN pagos pa ON r.id_reserva = pa.id_reserva
                    LEFT JOIN metodos_pago mp ON pa.id_metodo_pago = mp.id_metodo
                    WHERE r.id_usuario = :id_usuario
                    ORDER BY r.id_reserva DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute([':id_usuario' => $id_usuario]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Reserva::findByUsuario: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualiza el estado de una reserva y sincroniza el pago asociado.
     *
     * @param int $id_reserva
     * @param string $nuevoEstado 'pendiente', 'pagado', 'cancelado'
     * @return bool
     */
    public static function updateEstado(int $id_reserva, string $nuevoEstado): bool
    {
        $estadosValidos = ['pendiente', 'pagado', 'cancelado'];
        if (!in_array($nuevoEstado, $estadosValidos, true)) {
            return false;
        }

        $mapaPago = [
            'pendiente' => 'pendiente',
            'pagado'    => 'validado',
            'cancelado' => 'rechazado',
        ];

        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            $stmt = $db->prepare("UPDATE reservas SET estado = :estado WHERE id_reserva = :id_reserva");
            $stmt->execute([':estado' => $nuevoEstado, ':id_reserva' => $id_reserva]);

            $pagoStmt = $db->prepare("UPDATE pagos SET estado = :estado_pago WHERE id_reserva = :id_reserva");
            $pagoStmt->execute([
                ':estado_pago' => $mapaPago[$nuevoEstado],
                ':id_reserva'  => $id_reserva,
            ]);

            $db->commit();
            return true;
        } catch (PDOException $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error en Reserva::updateEstado: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca una reserva completa por su código y usuario propietario.
     *
     * @param string $codigo Código de reserva (ej. AJT-123456)
     * @param int $id_usuario ID del usuario propietario
     * @return array|null Datos completos de la reserva o null si no existe/no pertenece al usuario
     */
    public static function findByCodigo(string $codigo, int $id_usuario): ?array
    {
        try {
            $db = Database::getConnection();
            $sql = "SELECT r.*,
                           dr.id_detalle,
                           dr.fecha_viaje,
                           dr.cantidad_pasajeros,
                           dr.precio_unitario,
                           p.nombre AS paquete_nombre,
                           p.slug AS paquete_slug,
                           p.duracion_dias,
                           p.duracion_noches,
                           c.nombre AS ciudad_nombre,
                           cat.nombre AS categoria_nombre,
                           (SELECT url_imagen FROM paquete_imagenes pi WHERE pi.id_paquete = p.id_paquete AND pi.es_principal = 1 LIMIT 1) AS imagen_url,
                           pa.id_pago,
                           pa.numero_operacion AS pago_operacion,
                           pa.monto AS pago_monto,
                           pa.estado AS pago_estado,
                           pa.comprobante_url,
                           mp.nombre AS metodo_pago_nombre
                    FROM reservas r
                    INNER JOIN detalle_reservas dr ON r.id_reserva = dr.id_reserva
                    INNER JOIN paquetes p ON dr.id_paquete = p.id_paquete
                    LEFT JOIN ciudades c ON p.id_ciudad = c.id_ciudad
                    LEFT JOIN categorias_paquete cat ON p.id_categoria = cat.id_categoria
                    LEFT JOIN pagos pa ON r.id_reserva = pa.id_reserva
                    LEFT JOIN metodos_pago mp ON pa.id_metodo_pago = mp.id_metodo
                    WHERE r.codigo_reserva = :codigo AND r.id_usuario = :id_usuario
                    LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([':codigo' => $codigo, ':id_usuario' => $id_usuario]);
            $reserva = $stmt->fetch();

            if (!$reserva) {
                return null;
            }

            // Cargar viajeros asociados
            $sqlViajeros = "SELECT vr.*
                            FROM viajeros_reserva vr
                            INNER JOIN detalle_reservas dr ON vr.id_detalle = dr.id_detalle
                            WHERE dr.id_reserva = :id_reserva";
            $stmtV = $db->prepare($sqlViajeros);
            $stmtV->execute([':id_reserva' => $reserva['id_reserva']]);
            $reserva['viajeros'] = $stmtV->fetchAll();

            return $reserva;
        } catch (PDOException $e) {
            error_log("Error en Reserva::findByCodigo: " . $e->getMessage());
            return null;
        }
    }

}

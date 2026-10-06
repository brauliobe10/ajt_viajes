<?php

declare(strict_types=1);

// Funcion del archivo: Contiene consultas y persistencia del libro de reclamaciones.
namespace App\Models;

use App\Config\Database;
use PDOException;

/**
 * Clase Reclamacion
 * Modelo para gestionar el Libro de Reclamaciones (Ley 29571).
 */
class Reclamacion
{
    /**
     * Registra una nueva reclamación o sugerencia.
     *
     * @param array $data ['usuario_id', 'tipo', 'categoria', 'descripcion', 'pedido_cliente', 'medio_respuesta', 'reserva_id']
     * @return int|null Retorna el ID insertado o null si falla.
     */
    public static function create(array $data): ?int
    {
        try {
            $db = Database::getConnection();
            $sql = "INSERT INTO reclamaciones (usuario_id, reserva_id, solicitud_visa_id, tipo, categoria, descripcion, pedido_cliente, medio_respuesta)
                    VALUES (:usuario_id, :reserva_id, :solicitud_visa_id, :tipo, :categoria, :descripcion, :pedido_cliente, :medio_respuesta)";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':usuario_id' => (int)$data['usuario_id'],
                ':reserva_id' => !empty($data['reserva_id']) ? (int)$data['reserva_id'] : null,
                ':solicitud_visa_id' => !empty($data['solicitud_visa_id']) ? (int)$data['solicitud_visa_id'] : null,
                ':tipo' => $data['tipo'],
                ':categoria' => $data['categoria'],
                ':descripcion' => $data['descripcion'],
                ':pedido_cliente' => !empty($data['pedido_cliente']) ? $data['pedido_cliente'] : null,
                ':medio_respuesta' => $data['medio_respuesta'] ?? 'correo',
            ]);
            return (int)$db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error en Reclamacion::create: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene las reclamaciones de un usuario específico.
     *
     * @param int $userId
     * @return array
     */
    public static function findByUsuario(int $userId): array
    {
        try {
            $db = Database::getConnection();
            $sql = "SELECT r.*,
                           res.codigo_reserva,
                           p.nombre AS paquete_nombre
                    FROM reclamaciones r
                    LEFT JOIN reservas res ON r.reserva_id = res.id_reserva
                    LEFT JOIN detalle_reservas dr ON res.id_reserva = dr.id_reserva
                    LEFT JOIN paquetes p ON dr.id_paquete = p.id_paquete
                    WHERE r.usuario_id = :usuario_id
                    ORDER BY r.created_at DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute([':usuario_id' => $userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Reclamacion::findByUsuario: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene todas las reclamaciones con datos de usuario (admin).
     *
     * @return array
     */
    public static function findAll(): array
    {
        try {
            $db = Database::getConnection();
            $sql = "SELECT r.*,
                           u.nombres AS usuario_nombres,
                           u.apellidos AS usuario_apellidos,
                           u.email AS usuario_email,
                           res.codigo_reserva,
                           p.nombre AS paquete_nombre
                    FROM reclamaciones r
                    INNER JOIN usuarios u ON r.usuario_id = u.id_usuario
                    LEFT JOIN reservas res ON r.reserva_id = res.id_reserva
                    LEFT JOIN detalle_reservas dr ON res.id_reserva = dr.id_reserva
                    LEFT JOIN paquetes p ON dr.id_paquete = p.id_paquete
                    ORDER BY r.created_at DESC";
            $stmt = $db->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Reclamacion::findAll: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene una reclamación por ID con joins completos.
     *
     * @param int $id
     * @return array|null
     */
    public static function findById(int $id): ?array
    {
        try {
            $db = Database::getConnection();
            $sql = "SELECT r.*,
                           u.nombres AS usuario_nombres,
                           u.apellidos AS usuario_apellidos,
                           u.email AS usuario_email,
                           res.codigo_reserva,
                           p.nombre AS paquete_nombre
                    FROM reclamaciones r
                    INNER JOIN usuarios u ON r.usuario_id = u.id_usuario
                    LEFT JOIN reservas res ON r.reserva_id = res.id_reserva
                    LEFT JOIN detalle_reservas dr ON res.id_reserva = dr.id_reserva
                    LEFT JOIN paquetes p ON dr.id_paquete = p.id_paquete
                    WHERE r.id = :id
                    LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (PDOException $e) {
            error_log("Error en Reclamacion::findById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Actualiza el estado y la respuesta de una reclamación.
     *
     * @param int $id
     * @param string $estado
     * @param string|null $respuestaAdmin
     * @return bool
     */
    public static function updateEstado(int $id, string $estado, ?string $respuestaAdmin = null): bool
    {
        if (!in_array($estado, ['nuevo', 'en_revision', 'respondido', 'cerrado'])) {
            return false;
        }
        try {
            $db = Database::getConnection();
            $sql = "UPDATE reclamaciones SET estado = :estado, respuesta_admin = :respuesta WHERE id = :id";
            $stmt = $db->prepare($sql);
            return $stmt->execute([
                ':estado' => $estado,
                ':respuesta' => $respuestaAdmin,
                ':id' => $id,
            ]);
        } catch (PDOException $e) {
            error_log("Error en Reclamacion::updateEstado: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si un usuario tiene servicios activos (reservas, pagos o solicitudes_visa).
     *
     * @param int $userId
     * @return bool
     */
    public static function hasActiveServices(int $userId): bool
    {
        try {
            $db = Database::getConnection();

            // Check reservas
            $sql = "SELECT 1 FROM reservas WHERE id_usuario = :uid LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([':uid' => $userId]);
            if ($stmt->fetch()) return true;

            // Check pagos
            $sql = "SELECT 1 FROM pagos WHERE id_usuario = :uid LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([':uid' => $userId]);
            if ($stmt->fetch()) return true;

            // Check solicitudes_visa
            $sql = "SELECT 1 FROM solicitudes_visa WHERE id_usuario = :uid LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([':uid' => $userId]);
            if ($stmt->fetch()) return true;

            return false;
        } catch (PDOException $e) {
            error_log("Error en Reclamacion::hasActiveServices: " . $e->getMessage());
            return false;
        }
    }
}

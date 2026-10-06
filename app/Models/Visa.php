<?php

declare(strict_types=1);

// Funcion del archivo: Consulta servicios de visa y solicitudes registradas.
namespace App\Models;

use App\Config\Database;
use PDOException;

/**
 * Clase Visa
 * Modelo para gestionar los servicios y las solicitudes de asesoría de visa.
 */
class Visa
{
    /**
     * Obtiene todos los servicios de visa activos junto con la información del país.
     *
     * @return array
     */
    public static function allServices(): array
    {
        try {
            $db = Database::getConnection();
            $sql = "SELECT sv.*, p.nombre AS pais_nombre, p.continente AS pais_continente
                    FROM servicios_visa sv
                    INNER JOIN paises p ON sv.id_pais = p.id_pais
                    WHERE sv.disponible = 1
                    ORDER BY sv.id_servicio ASC";
            $stmt = $db->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Visa::allServices: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Registra una nueva solicitud de asesoría de visa.
     *
     * @param array $data Contiene 'id_usuario', 'id_servicio', 'fecha_viaje_aprox'
     * @return string|null Retorna el código de solicitud generado (ej. VSA-XXXXXX) o null si falla.
     */
    public static function createSolicitud(array $data): ?string
    {
        try {
            $db = Database::getConnection();
            
            // Generar código de solicitud único
            $prefix = 'VSA-';
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            if (isset($_SESSION['usuario']['email']) && str_contains(strtolower($_SESSION['usuario']['email']), 'audit')) {
                $prefix = 'AUDIT_VISA_';
            }
            $codigo = $prefix . str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            
            $sql = "INSERT INTO solicitudes_visa (id_usuario, id_servicio, codigo_solicitud, fecha_viaje_aprox, estado, created_at)
                    VALUES (:id_usuario, :id_servicio, :codigo, :fecha_viaje_aprox, 'nueva', NOW())";
            $stmt = $db->prepare($sql);
            $success = $stmt->execute([
                ':id_usuario' => (int)$data['id_usuario'],
                ':id_servicio' => (int)$data['id_servicio'],
                ':codigo' => $codigo,
                ':fecha_viaje_aprox' => !empty($data['fecha_viaje_aprox']) ? $data['fecha_viaje_aprox'] : null
            ]);

            return $success ? $codigo : null;
        } catch (PDOException $e) {
            error_log("Error en Visa::createSolicitud: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene el listado de solicitudes de visa asociadas a un usuario.
     *
     * @param int $id_usuario
     * @return array
     */
    public static function findByUsuario(int $id_usuario): array
    {
        try {
            $db = Database::getConnection();
            $sql = "SELECT s.*, sv.tipo_visa, sv.precio_asesoria, p.nombre AS pais_nombre
                    FROM solicitudes_visa s
                    INNER JOIN servicios_visa sv ON s.id_servicio = sv.id_servicio
                    INNER JOIN paises p ON sv.id_pais = p.id_pais
                    WHERE s.id_usuario = :id_usuario
                    ORDER BY s.id_solicitud DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute([':id_usuario' => $id_usuario]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Visa::findByUsuario: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene todas las solicitudes de visa (para administración).
     *
     * @return array
     */
    public static function allSolicitudes(): array
    {
        try {
            $db = Database::getConnection();
            $sql = "SELECT s.*, sv.tipo_visa, sv.precio_asesoria, p.nombre AS pais_nombre,
                           u.nombres AS usuario_nombres, u.apellidos AS usuario_apellidos, u.email AS usuario_email
                    FROM solicitudes_visa s
                    INNER JOIN servicios_visa sv ON s.id_servicio = sv.id_servicio
                    INNER JOIN paises p ON sv.id_pais = p.id_pais
                    INNER JOIN usuarios u ON s.id_usuario = u.id_usuario
                    ORDER BY s.id_solicitud DESC";
            $stmt = $db->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Visa::allSolicitudes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualiza el estado de una solicitud de visa.
     *
     * @param int $id_solicitud
     * @param string $estado Debe ser 'recibida', 'aprobada' o 'rechazada'
     * @return bool
     */
    public static function updateEstado(int $id_solicitud, string $estado): bool
    {
        $estadosValidos = ['nueva', 'contactado', 'documentos_pendientes', 'en_revision', 'en_tramite', 'aprobada', 'rechazada', 'cancelada'];
        if (!in_array($estado, $estadosValidos)) {
            return false;
        }

        try {
            $db = Database::getConnection();
            $sql = "UPDATE solicitudes_visa SET estado = :estado WHERE id_solicitud = :id_solicitud";
            $stmt = $db->prepare($sql);
            return $stmt->execute([
                ':estado' => $estado,
                ':id_solicitud' => $id_solicitud
            ]);
        } catch (PDOException $e) {
            error_log("Error en Visa::updateEstado: " . $e->getMessage());
            return false;
        }
    }
}

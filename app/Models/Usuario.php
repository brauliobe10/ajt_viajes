<?php

declare(strict_types=1);

// Funcion del archivo: Consulta y actualiza usuarios, credenciales y datos de cuenta.
namespace App\Models;

use App\Config\Database;
use PDOException;

/**
 * Clase Usuario
 * Modelo que interactúa con la tabla 'usuarios' de la base de datos.
 */
class Usuario
{
    /**
     * Busca un usuario en la base de datos por su dirección de correo electrónico.
     *
     * @param string $email
     * @return array|null Retorna el registro del usuario como array asociativo o null si no existe.
     */
    public static function findByEmail(string $email): ?array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();
            return $user ?: null;
        } catch (PDOException $e) {
            error_log("Error en Usuario::findByEmail: " . $e->getMessage());
            throw new PDOException("Error de base de datos al buscar el usuario.");
        }
    }

    /**
     * Busca un usuario por su ID.
     *
     * @param int $id
     * @return array|null
     */
    public static function findById(int $id): ?array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM usuarios WHERE id_usuario = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            $user = $stmt->fetch();
            return $user ?: null;
        } catch (PDOException $e) {
            error_log("Error en Usuario::findById: " . $e->getMessage());
            throw new PDOException("Error de base de datos al buscar el usuario por ID.");
        }
    }

    /**
     * Registra un nuevo usuario en la base de datos.
     *
     * @param array $data Contiene 'id_rol', 'nombres', 'apellidos', 'email', 'password_hash', 'dni_pasaporte', 'telefono'
     * @return bool
     */
    public static function create(array $data): bool
    {
        try {
            $db = Database::getConnection();
            $sql = "INSERT INTO usuarios (id_rol, nombres, apellidos, email, password_hash, dni_pasaporte, telefono)
                    VALUES (:id_rol, :nombres, :apellidos, :email, :password_hash, :dni_pasaporte, :telefono)";
            $stmt = $db->prepare($sql);
            return $stmt->execute([
                ':id_rol' => $data['id_rol'] ?? 1, // Por defecto Cliente (1)
                ':nombres' => trim($data['nombres']),
                ':apellidos' => trim($data['apellidos']),
                ':email' => trim($data['email']),
                ':password_hash' => $data['password_hash'],
                ':dni_pasaporte' => $data['dni_pasaporte'] ?? null,
                ':telefono' => $data['telefono'] ?? null
            ]);
        } catch (PDOException $e) {
            if ($db && $db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error en Usuario::create: " . $e->getMessage());
            throw new PDOException("Error al registrar el usuario en la base de datos.");
        }
    }

    /**
     * Actualiza la contraseña de un usuario.
     *
     * @param int $id_usuario
     * @param string $newPasswordHash
     * @return bool
     */
    public static function cambiarPassword(int $id_usuario, string $newPasswordHash): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("UPDATE usuarios SET password_hash = :password_hash WHERE id_usuario = :id_usuario");
            return $stmt->execute([
                ':password_hash' => $newPasswordHash,
                ':id_usuario' => $id_usuario
            ]);
        } catch (PDOException $e) {
            error_log("Error en Usuario::cambiarPassword: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza el rol de un usuario (1 = Cliente, 2 = Administrador).
     *
     * @param int $id_usuario
     * @param int $id_rol
     * @return bool
     */
    public static function updateRol(int $id_usuario, int $id_rol): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("UPDATE usuarios SET id_rol = :id_rol WHERE id_usuario = :id_usuario");
            return $stmt->execute([':id_rol' => $id_rol, ':id_usuario' => $id_usuario]);
        } catch (PDOException $e) {
            error_log("Error en Usuario::updateRol: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica la contraseña actual de un usuario.
     *
     * @param int $id_usuario
     * @param string $password Contraseña en texto plano para verificar
     * @return bool
     */
    public static function verifyPassword(int $id_usuario, string $password): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT password_hash FROM usuarios WHERE id_usuario = :id LIMIT 1");
            $stmt->execute([':id' => $id_usuario]);
            $user = $stmt->fetch();
            return $user && password_verify($password, $user['password_hash']);
        } catch (PDOException $e) {
            error_log("Error en Usuario::verifyPassword: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza los datos de la cuenta de un usuario.
     *
     * @param int $id_usuario
     * @param array $data
     * @return bool
     */
    public static function updateAccount(int $id_usuario, array $data): bool
    {
        try {
            $db = Database::getConnection();
            $sql = "UPDATE usuarios 
                    SET nombres = :nombres, apellidos = :apellidos, telefono = :telefono, dni_pasaporte = :dni
                    WHERE id_usuario = :id_usuario";
            $stmt = $db->prepare($sql);
            return $stmt->execute([
                ':nombres' => trim($data['nombres']),
                ':apellidos' => trim($data['apellidos']),
                ':telefono' => trim($data['telefono']),
                ':dni' => trim($data['dni']),
                ':id_usuario' => $id_usuario
            ]);
        } catch (PDOException $e) {
            error_log("Error en Usuario::updateAccount: " . $e->getMessage());
            return false;
        }
    }
}

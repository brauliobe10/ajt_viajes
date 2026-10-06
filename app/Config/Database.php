<?php

declare(strict_types=1);

namespace App\Config;

use PDO;
use PDOException;
use App\Config\LocalConfig;

/**
 * Clase Database
 * Gestiona la conexión a la base de datos utilizando el patrón Singleton.
 * Las credenciales se cargan desde LocalConfig de forma segura.
 */
class Database
{
    private static ?PDO $connection = null;

    /**
     * Obtiene la instancia única de conexión PDO.
     *
     * @return PDO
     */
    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            $host    = LocalConfig::DB_HOST;
            $port    = LocalConfig::DB_PORT;
            $db      = LocalConfig::DB_NAME;
            $user    = LocalConfig::DB_USER;
            $pass    = LocalConfig::DB_PASS;
            $charset = 'utf8mb4';

            $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$connection = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                // Registrar el error y mostrar un mensaje limpio.
                // Evitamos exponer credenciales en el mensaje de error.
                error_log("Error de conexión a la base de datos: " . $e->getMessage());
                throw new PDOException("Error al conectar con la base de datos. Por favor, intente más tarde.");
            }
        }

        return self::$connection;
    }
}

<?php

declare(strict_types=1);

// Funcion del archivo: Centraliza la lectura cacheada de valores de configuraci?n.
namespace App\Helper;

use App\Config\Database;
use PDOException;

/**
 * Config — Static Helper for configuracion table
 * 
 * Provides typed access to application configuration values stored in the
 * configuracion database table. Values are cached per-request on first access.
 * Falls back to defaults on query failure (never crashes the app).
 * 
 * Usage:
 *   Config::getString('agencia_nombre')
 *   Config::getInt('agencia_experiencia', 15)
 *   Config::getBool('testimonios_habilitado')
 */
class Config
{
    private static ?array $cache = null;

    /**
     * Load configuration from database into static cache.
     * Called lazily on first access to any get* method.
     */
    private static function loadCache(): void
    {
        if (self::$cache !== null) {
            return;
        }

        self::$cache = [];

        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->query('SELECT clave, valor FROM configuracion');

            while ($row = $stmt->fetch()) {
                self::$cache[$row['clave']] = $row['valor'];
            }
        } catch (PDOException $e) {
            // Fail silently — return defaults from getter methods
            error_log('Config::loadCache() — DB query failed: ' . $e->getMessage());
        }
    }

    /**
     * Get a string configuration value.
     *
     * @param string $key     Configuration key (clave)
     * @param string $default Fallback value if key is missing or empty
     * @return string
     */
    public static function getString(string $key, string $default = ''): string
    {
        self::loadCache();

        if (isset(self::$cache[$key]) && self::$cache[$key] !== '') {
            return self::$cache[$key];
        }

        return $default;
    }

    /**
     * Get an integer configuration value.
     *
     * @param string $key     Configuration key (clave)
     * @param int    $default Fallback value if key is missing, empty, or non-numeric
     * @return int
     */
    public static function getInt(string $key, int $default = 0): int
    {
        self::loadCache();

        if (isset(self::$cache[$key]) && self::$cache[$key] !== '' && is_numeric(self::$cache[$key])) {
            return (int) self::$cache[$key];
        }

        return $default;
    }

    /**
     * Get a boolean configuration value.
     *
     * Interprets '1', 'true', 'yes', 'on' as true (case-insensitive).
     *
     * @param string $key     Configuration key (clave)
     * @param bool   $default Fallback value if key is missing or empty
     * @return bool
     */
    public static function getBool(string $key, bool $default = false): bool
    {
        self::loadCache();

        if (isset(self::$cache[$key]) && self::$cache[$key] !== '') {
            $val = strtolower(self::$cache[$key]);
            return in_array($val, ['1', 'true', 'yes', 'on'], true);
        }

        return $default;
    }

    /**
     * Get all configuration values, optionally filtered by prefix.
     *
     * @param string $prefix Optional prefix filter (e.g. 'agencia_' returns only agencia_* keys)
     * @return array<string, string> Associative array of clave => valor
     */
    public static function getAll(string $prefix = ''): array
    {
        self::loadCache();

        if ($prefix === '') {
            return self::$cache;
        }

        $result = [];
        $len = strlen($prefix);

        foreach (self::$cache as $key => $value) {
            if (strncmp($key, $prefix, $len) === 0) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Save or update multiple configuration values at once.
     *
     * @param array $campos Associative array of clave => valor
     * @return int Number of rows affected/saved
     */
    public static function saveMany(array $campos): int
    {
        $guardados = 0;

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare(
                "INSERT INTO configuracion (clave, valor)
                 VALUES (:clave, :valor)
                 ON DUPLICATE KEY UPDATE valor = :valor2"
            );

            foreach ($campos as $clave => $valor) {
                $stmt->execute([
                    ':clave'  => $clave,
                    ':valor'  => (string)$valor,
                    ':valor2' => (string)$valor,
                ]);
                $guardados++;
            }

            self::$cache = null; // Invalidate cache
        } catch (PDOException $e) {
            error_log('Config::saveMany() — DB error: ' . $e->getMessage());
        }

        return $guardados;
    }
}

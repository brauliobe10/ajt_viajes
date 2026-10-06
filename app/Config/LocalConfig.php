<?php

declare(strict_types=1);

namespace App\Config;

/**
 * Clase LocalConfig
 * Centraliza la configuración local de desarrollo de la aplicación.
 * Reemplaza el uso de archivos .env y variables de entorno externas.
 */
final class LocalConfig
{
    public const APP_ENV = 'production';
    public const APP_DEBUG = false;

    // Configuración de base de datos
    public const DB_HOST = '127.0.0.1';
    public const DB_PORT = 3306;
    public const DB_NAME = 'viajes_ajt_3fn';
    public const DB_USER = 'root';
    public const DB_PASS = '';

    // Clave de seguridad para ejecutar el seeder vía web
    public const SEEDER_KEY = 'change-me';
}

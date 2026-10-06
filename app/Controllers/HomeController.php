<?php

declare(strict_types=1);

// Funcion del archivo: Prepara los datos que se muestran en la pagina principal.
namespace App\Controllers;

use App\Config\Database;
use App\Models\Paquete;
use PDO;

/**
 * Clase HomeController
 * Controlador principal para gestionar las acciones relacionadas a la página de inicio.
 */
class HomeController
{
    /**
     * Muestra la página de inicio de la aplicación.
     *
     * @return void
     */
    public function index(): void
    {
        $pdo = Database::getConnection();

        // ── 6.1 Destacados desde BD (destacado = 1) ──
        $paquetesDestacados = [];
        try {
            $todos = Paquete::all();
            $paquetesDestacados = array_values(array_filter($todos, function($p) {
                return (int)$p['disponible'] === 1 && (int)$p['destacado'] === 1;
            }));
            // Fallback: si no hay destacados, tomar los 4 mejor calificados
            if (empty($paquetesDestacados)) {
                $paquetesDestacados = array_slice(
                    array_values(array_filter($todos, fn($p) => (int)$p['disponible'] === 1)),
                    0, 4
                );
            }
        } catch (\Exception $e) {
            error_log("Error al cargar paquetes destacados en HomeController: " . $e->getMessage());
        }

        // ── 6.5 Ofertas reales (precio_anterior > precio_base) ──
        $paquetesOfertas = [];
        try {
            $paquetesOfertas = array_values(array_filter($todos ?? [], function($p) {
                return (int)$p['disponible'] === 1
                    && !empty($p['precio_anterior'])
                    && (float)$p['precio_anterior'] > (float)$p['precio_base'];
            }));
        } catch (\Exception $e) {
            error_log("Error al cargar ofertas en HomeController: " . $e->getMessage());
        }

        // ── 6.4 Temporadas / Categorías ──
        $categorias = [];
        try {
            $stmt = $pdo->query("SELECT id_categoria, nombre FROM categorias_paquete ORDER BY nombre");
            $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Error al cargar categorías en HomeController: " . $e->getMessage());
        }

        // Paquetes por categoría para la sección de temporadas
        $paquetesPorCategoria = [];
        foreach ($categorias as $cat) {
            $paquetesPorCategoria[$cat['id_categoria']] = array_values(array_filter($todos ?? [], function($p) use ($cat) {
                return (int)$p['disponible'] === 1 && (int)$p['id_categoria'] === (int)$cat['id_categoria'];
            }));
        }

        // ── Viajes por temporada (basado en tags existentes, sin nueva tabla) ──
        $seasonDefs = [
            ['name' => 'Verano',        'icon' => 'sun',   'tags' => ['playa', 'caribe', 'all inclusive', 'méxico', 'cancún']],
            ['name' => 'Semana Santa',  'icon' => 'cross', 'tags' => ['cultura', 'cusco', 'machu picchu', 'sagrado']],
            ['name' => 'Fiestas Patrias','icon' => 'flag', 'tags' => ['nacionales', 'aventura', 'andes', 'huaraz']],
            ['name' => 'Fin de Año',    'icon' => 'gift',  'tags' => ['familia', 'internacionales', 'europa', 'orlando', 'japón']],
        ];
        $temporadas = [];
        foreach ($seasonDefs as $season) {
            $matched = array_values(array_filter($todos ?? [], function($p) use ($season) {
                if ((int)$p['disponible'] !== 1) return false;
                $pkgTags = array_map('strtolower', $p['tags'] ?? []);
                foreach ($season['tags'] as $tag) {
                    if (in_array(strtolower($tag), $pkgTags, true)) return true;
                }
                return false;
            }));
            $temporadas[] = [
                'name' => $season['name'],
                'icon' => $season['icon'],
                'packages' => array_slice($matched, 0, 4),
                'count' => count($matched),
            ];
        }

        // ── Stats para autocomplete ──
        $ciudades = [];
        try {
            $stmt = $pdo->query("SELECT id_ciudad, nombre FROM ciudades ORDER BY nombre");
            $ciudades = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Error al cargar ciudades en HomeController: " . $e->getMessage());
        }

        // Estadísticas desde la BD (solo las que son datos reales)
        $statsDestinos = 0;
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM paquetes WHERE disponible = 1");
            $statsDestinos = (int)$stmt->fetchColumn();
        } catch (\Exception $e) {
            error_log("Error al cargar stats en HomeController: " . $e->getMessage());
        }
        $statsExperiencia = \App\Helper\Config::getInt('agencia_experiencia', 15);

        // ── Testimonios desde BD ──
        $testimonios = [];
        $soloIniciales = true;
        try {
            $stmt = $pdo->query("SELECT nombre, ciudad, comentario, rating, servicio, es_demo FROM testimonios WHERE activo = 1 ORDER BY id_testimonio ASC");
            $testimonios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // Detect if all testimonials are initial records.
            $soloIniciales = empty($testimonios) || !in_array(0, array_column($testimonios, 'es_demo'));
        } catch (\Exception $e) {
            error_log("Error al cargar testimonios en HomeController: " . $e->getMessage());
        }

        // Definición de variables que usará la cabecera
        $title = "Viajes AJT — Paquetes turísticos y asesoría de visas en Perú";
        $description = "Reserva tu próximo viaje con Viajes AJT. Paquetes nacionales e internacionales, asesoría especializada en visas y atención personalizada.";
        $pageKey = "home";

        // Cargar las plantillas y la vista principal
        $baseViewsDir = dirname(__DIR__) . '/Views/';

        require_once $baseViewsDir . 'layouts/header.php';
        require_once $baseViewsDir . 'home.php';
        require_once $baseViewsDir . 'layouts/footer.php';
    }
}

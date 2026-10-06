<?php

declare(strict_types=1);

// Funcion del archivo: Carga catalogo, detalle de paquetes y busqueda p?blica en JSON.
namespace App\Controllers;

use App\Config\Database;
use App\Models\Paquete;
use App\Services\ImageUrlService;
use Exception;

/**
 * Clase CatalogController
 * Gestiona el catálogo público de paquetes y la visualización de detalles del viaje.
 */
class CatalogController
{
    /**
     * Muestra la lista de paquetes con filtros opcionales.
     *
     * @return void
     */
    public function index(): void
    {
        $totalCatalogPackages = 0;
        $dificultades = [];
        $tags = [];

        try {
            $todosLosPaquetes = Paquete::all();

            $paquetes = array_values(array_filter($todosLosPaquetes, static function($p) {
                return (int)($p['disponible'] ?? 0) === 1;
            }));
            $totalCatalogPackages = count($paquetes);

            $dificultades = self::uniqueValues($paquetes, 'dificultad');
            $tags = self::uniqueTags($paquetes);

            $q = isset($_GET['q']) ? trim($_GET['q']) : (isset($_GET['destino']) ? trim($_GET['destino']) : '');
            $categoria = isset($_GET['categoria']) ? trim($_GET['categoria']) : '';
            $precioMin = isset($_GET['precio_min']) && $_GET['precio_min'] !== '' ? (float)$_GET['precio_min'] : null;
            $precioMax = isset($_GET['precio_max']) && $_GET['precio_max'] !== '' ? (float)$_GET['precio_max'] : null;
            $duracion = isset($_GET['duracion']) ? $_GET['duracion'] : [];
            $dificultad = isset($_GET['dificultad']) ? trim($_GET['dificultad']) : '';
            $tag = isset($_GET['tag']) ? trim($_GET['tag']) : '';
            $orden = isset($_GET['orden']) ? $_GET['orden'] : '';

            if ($q !== '') {
                $qLower = mb_strtolower($q, 'UTF-8');
                $paquetes = array_filter($paquetes, static function($p) use ($qLower) {
                    $haystack = mb_strtolower(implode(' ', [
                        $p['nombre'] ?? '',
                        $p['ciudad_nombre'] ?? '',
                        $p['categoria_nombre'] ?? '',
                        $p['descripcion_corta'] ?? '',
                        $p['descripcion'] ?? '',
                        $p['dificultad'] ?? '',
                        implode(' ', $p['tags'] ?? []),
                    ]), 'UTF-8');
                    return mb_strpos($haystack, $qLower) !== false;
                });
            }

            if ($categoria !== '' && mb_strtolower($categoria, 'UTF-8') !== 'todos') {
                $catLower = mb_strtolower($categoria, 'UTF-8');
                $paquetes = array_filter($paquetes, static function($p) use ($catLower) {
                    return mb_strtolower($p['categoria_nombre'] ?? '', 'UTF-8') === $catLower;
                });
            }

            if ($precioMin !== null) {
                $paquetes = array_filter($paquetes, static function($p) use ($precioMin) {
                    return (float)($p['precio_base'] ?? 0) >= $precioMin;
                });
            }
            if ($precioMax !== null) {
                $paquetes = array_filter($paquetes, static function($p) use ($precioMax) {
                    return (float)($p['precio_base'] ?? 0) <= $precioMax;
                });
            }

            if (!empty($duracion)) {
                if (!is_array($duracion)) {
                    $duracion = [$duracion];
                }
                $paquetes = array_filter($paquetes, static function($p) use ($duracion) {
                    $d = (int)($p['duracion_dias'] ?? 0);
                    foreach ($duracion as $rango) {
                        if ($rango === '1-3' && $d >= 1 && $d <= 3) return true;
                        if ($rango === '4-7' && $d >= 4 && $d <= 7) return true;
                        if ($rango === '8-14' && $d >= 8 && $d <= 14) return true;
                        if ($rango === '15+' && $d >= 15) return true;
                    }
                    return false;
                });
            }

            if ($dificultad !== '') {
                $difLower = mb_strtolower($dificultad, 'UTF-8');
                $paquetes = array_filter($paquetes, static function($p) use ($difLower) {
                    return mb_strtolower($p['dificultad'] ?? '', 'UTF-8') === $difLower;
                });
            }

            if ($tag !== '') {
                $tagLower = mb_strtolower($tag, 'UTF-8');
                $paquetes = array_filter($paquetes, static function($p) use ($tagLower) {
                    foreach (($p['tags'] ?? []) as $item) {
                        if (mb_strtolower($item, 'UTF-8') === $tagLower) {
                            return true;
                        }
                    }
                    return false;
                });
            }

            $paquetes = array_values($paquetes);
            if ($orden === 'precio_asc') {
                usort($paquetes, static function($a, $b) {
                    return (float)($a['precio_base'] ?? 0) <=> (float)($b['precio_base'] ?? 0);
                });
            } elseif ($orden === 'precio_desc') {
                usort($paquetes, static function($a, $b) {
                    return (float)($b['precio_base'] ?? 0) <=> (float)($a['precio_base'] ?? 0);
                });
            }

            $categorias = Paquete::getCategorias();
        } catch (Exception $e) {
            error_log("Error en CatalogController::index: " . $e->getMessage());
            $_SESSION['error'] = "Ocurrió un error al cargar el catálogo de paquetes.";
            $paquetes = [];
            $categorias = [];
            $dificultades = [];
            $tags = [];
        }

        $title = "Catálogo de paquetes — Viajes AJT";
        $description = "Explora paquetes turísticos nacionales e internacionales. Filtra por destino, precio y duración.";
        $pageKey = "catalog";
        $extraStyles = ['catalog.css'];

        $baseViewsDir = dirname(__DIR__) . '/Views/';
        require_once $baseViewsDir . 'layouts/header.php';
        require_once $baseViewsDir . 'catalog.php';
        require_once $baseViewsDir . 'layouts/footer.php';
    }

    // Funcion: Extrae valores unicos de un campo de los paquetes.
    private static function uniqueValues(array $paquetes, string $field): array
    {
        $values = [];
        foreach ($paquetes as $paquete) {
            $value = trim((string)($paquete[$field] ?? ''));
            if ($value !== '') {
                $values[$value] = $value;
            }
        }
        natcasesort($values);
        return array_values($values);
    }

    // Funcion: Reune etiquetas unicas de todos los paquetes.
    private static function uniqueTags(array $paquetes): array
    {
        $tags = [];
        foreach ($paquetes as $paquete) {
            foreach (($paquete['tags'] ?? []) as $tag) {
                $tag = trim((string)$tag);
                if ($tag !== '') {
                    $tags[$tag] = $tag;
                }
            }
        }
        natcasesort($tags);
        return array_values($tags);
    }

    /**
     * Muestra el detalle de un paquete turístico por su slug.
     *
     * @param string $slug
     * @return void
     */
    public function detail(string $slug): void
    {
        try {
            $paquete = ctype_digit($slug) ? Paquete::getDetailById((int)$slug) : Paquete::getDetailBySlug($slug);
            if (!$paquete || (int)$paquete['disponible'] !== 1) {
                http_response_code(404);
                echo "<h1>404 - Paquete no encontrado</h1><p>El paquete turístico que busca no existe o no está disponible.</p>";
                return;
            }

            $imagenes = $paquete['imagenes'] ?? [];

            // Itinerario: viene de la tabla paquete_itinerario via Paquete::getItinerario()
            // (con fallback a columna JSON legacy). La vista lo consume como array.
            $itinerario = $paquete['itinerario'] ?? [];

            $descripcion = $paquete['descripcion'] ?? '';
            $mainDesc = trim((string)(($paquete['descripcion_corta'] ?? '') ?: $descripcion));

            // Paquetes relacionados (misma ciudad o categoría, excluye el actual)
            $relacionados = Paquete::getRelatedPackages(
                (int)$paquete['id_paquete'],
                $paquete['ciudad_nombre'] ?? '',
                $paquete['categoria_nombre'] ?? '',
                4
            );

            // Geolocalización para el mapa
            $lat = \App\Helper\Config::getString('agencia_latitud');
            $lng = \App\Helper\Config::getString('agencia_longitud');


        } catch (Exception $e) {
            error_log("Error en CatalogController::detail: " . $e->getMessage());
            http_response_code(500);
            echo "<h1>500 - Error del servidor</h1><p>No se pudo procesar la solicitud del paquete.</p>";
            return;
        }

        $title = htmlspecialchars($paquete['nombre']) . " · Viajes AJT";
        $description = htmlspecialchars(mb_substr(strip_tags($mainDesc), 0, 150) . '...');
        $pageKey = "catalog"; // Para marcar la pestaña activa en la navbar
        $extraStyles = ['detail.css'];

        $baseViewsDir = dirname(__DIR__) . '/Views/';
        require_once $baseViewsDir . 'layouts/header.php';
        require_once $baseViewsDir . 'detail.php';
        require_once $baseViewsDir . 'layouts/footer.php';
    }

    /**
     * API JSON: búsqueda de paquetes por nombre o descripción (AJAX).
     *
     * @return void
     */
    public function searchJson(): void
    {
        header('Content-Type: application/json; charset=UTF-8');

        $q = isset($_GET['q']) ? trim($_GET['q']) : '';

        if ($q === '') {
            echo json_encode([]);
            return;
        }

        try {
            $db = Database::getConnection();
            $sql = "SELECT p.id_paquete, p.nombre, p.slug, p.descripcion_corta, p.precio_base, p.precio_anterior,
                           p.duracion_dias, p.duracion_noches, p.dificultad, p.vuelos, p.comidas, p.hotel,
                           (SELECT url_imagen FROM paquete_imagenes pi WHERE pi.id_paquete = p.id_paquete AND pi.es_principal = 1 LIMIT 1) AS imagen_principal,
                           (SELECT GROUP_CONCAT(pt.texto ORDER BY pt.orden ASC SEPARATOR ',') FROM paquete_tags pt WHERE pt.id_paquete = p.id_paquete) AS tags_csv,
                           cat.nombre AS categoria_nombre,
                           c.nombre AS ciudad_nombre
                    FROM paquetes p
                    INNER JOIN categorias_paquete cat ON p.id_categoria = cat.id_categoria
                    INNER JOIN ciudades c ON p.id_ciudad = c.id_ciudad
                    WHERE p.disponible = 1
                      AND (
                        p.nombre LIKE :q1 OR
                        p.descripcion LIKE :q2 OR
                        p.descripcion_corta LIKE :q3 OR
                        cat.nombre LIKE :q4 OR
                        c.nombre LIKE :q5 OR
                        p.dificultad LIKE :q6 OR
                        p.comidas LIKE :q7 OR
                        p.hotel LIKE :q8
                      )
                    ORDER BY p.id_paquete DESC
                    LIMIT 30";

            $stmt = $db->prepare($sql);
            $like = '%' . $q . '%';
            $stmt->execute([
                ':q1' => $like,
                ':q2' => $like,
                ':q3' => $like,
                ':q4' => $like,
                ':q5' => $like,
                ':q6' => $like,
                ':q7' => $like,
                ':q8' => $like,
            ]);

            $results = $stmt->fetchAll();
            $output = [];
            foreach ($results as $row) {
                $firstFallback = Paquete::firstFallbackBySlug($row['slug']);
                $tags = array_values(array_filter(array_map('trim', explode(',', (string)($row['tags_csv'] ?? '')))));
                $vuelos = $row['vuelos'] ?? '';
                $comidas = $row['comidas'] ?? '';
                $hotel = $row['hotel'] ?? '';
                $output[] = [
                    'id'                => (int)$row['id_paquete'],
                    'nombre'            => $row['nombre'],
                    'slug'              => $row['slug'],
                    'precio_base'       => (float)$row['precio_base'],
                    'precio_anterior'   => $row['precio_anterior'] !== null ? (float)$row['precio_anterior'] : null,
                    'duracion_dias'     => (int)$row['duracion_dias'],
                    'duracion_noches'   => (int)$row['duracion_noches'],
                    'descripcion_corta' => $row['descripcion_corta'] ?? '',
                    'imagen_principal'  => !empty($row['imagen_principal']) ? ImageUrlService::catalogOptimize($row['imagen_principal']) : $firstFallback,
                    'imagen_fallback'   => $firstFallback,
                    'categoria_nombre'  => $row['categoria_nombre'],
                    'ciudad_nombre'     => $row['ciudad_nombre'],
                    'dificultad'        => $row['dificultad'] ?? '',
                    'vuelos'            => $vuelos,
                    'comidas'           => $comidas,
                    'hotel'             => $hotel,
                    'tags'              => $tags,
                    'vuelo_incluido'    => self::isFlightIncluded($vuelos),
                    'todo_incluido'     => self::isAllInclusive($comidas, $hotel),
                    'familiar'          => self::isFamily($tags, $row['nombre'] ?? '', $row['descripcion_corta'] ?? ''),
                ];
            }

            echo json_encode($output, JSON_UNESCAPED_UNICODE);
        } catch (\PDOException $e) {
            error_log("Error en CatalogController::searchJson: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error al buscar paquetes.']);
        }
    }

    /**
     * Determines if flight is included based on the vuelos field text.
     * Returns true when the field has content AND doesn't say "no incluido".
     */
    private static function isFlightIncluded(string $vuelos): bool
    {
        $vuelos = trim(mb_strtolower($vuelos, 'UTF-8'));
        if ($vuelos === '') {
            return false;
        }
        // If it explicitly says "no incluido", flight is NOT included
        if (str_contains($vuelos, 'no incluido') || str_contains($vuelos, 'no incluidos')) {
            return false;
        }
        // If it has flight info and doesn't deny inclusion, it's included
        return true;
    }

    /**
     * Determines if the package is "all inclusive" based on comidas and hotel fields.
     */
    private static function isAllInclusive(string $comidas, string $hotel): bool
    {
        $text = mb_strtolower($comidas . ' ' . $hotel, 'UTF-8');
        return str_contains($text, 'todo incluido') || str_contains($text, 'all inclusive');
    }

    /**
     * Determines if the package is family-oriented based on tags, name, and description.
     */
    private static function isFamily(array $tags, string $nombre, string $descripcion): bool
    {
        $tagTexts = array_map(fn($t) => mb_strtolower((string)$t, 'UTF-8'), $tags);
        foreach ($tagTexts as $tag) {
            if (str_contains($tag, 'familiar') || str_contains($tag, 'familia')) {
                return true;
            }
        }
        $nameDesc = mb_strtolower($nombre . ' ' . $descripcion, 'UTF-8');
        return str_contains($nameDesc, 'familiar') || str_contains($nameDesc, 'familia');
    }

}

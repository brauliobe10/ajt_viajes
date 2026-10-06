<?php

declare(strict_types=1);

// Funcion del archivo: Contiene consultas y persistencia de paquetes, relaciones e imagenes.
namespace App\Models;

use App\Config\Database;
use App\Services\ImageUrlService;
use PDO;
use PDOException;

class Paquete
{
    private const RELATION_TABLES = [
        'incluye' => 'paquete_incluye',
        'no_incluye' => 'paquete_no_incluye',
        'documentos' => 'paquete_documentos',
        'tags' => 'paquete_tags',
    ];

    private const PACKAGE_COLUMNS = [
        'id_categoria', 'id_ciudad', 'nombre', 'slug', 'descripcion_corta', 'descripcion', 'itinerario',
        'precio_base', 'precio_anterior', 'duracion_dias', 'duracion_noches', 'cupo_maximo', 'dificultad',
        'hotel', 'habitacion', 'comidas', 'vuelos', 'movilidad', 'guia', 'destacado', 'disponible'
    ];

    // Funcion: Obtiene todos los paquetes registrados.
    public static function all(): array
    {
        try {
            $db = Database::getConnection();
            $sql = "SELECT p.*,
                           c.nombre AS ciudad_nombre,
                           c.id_pais,
                           cat.nombre AS categoria_nombre,
                           (SELECT url_imagen FROM paquete_imagenes pi WHERE pi.id_paquete = p.id_paquete AND pi.es_principal = 1 LIMIT 1) AS imagen_url,
                           (SELECT GROUP_CONCAT(pt.texto ORDER BY pt.orden ASC SEPARATOR ',') FROM paquete_tags pt WHERE pt.id_paquete = p.id_paquete) AS tags_csv,
                           (SELECT COALESCE(SUM(dr.cantidad_pasajeros), 0)
                            FROM detalle_reservas dr
                            WHERE dr.id_paquete = p.id_paquete) AS reservas_count
                    FROM paquetes p
                    INNER JOIN ciudades c ON p.id_ciudad = c.id_ciudad
                    INNER JOIN categorias_paquete cat ON p.id_categoria = cat.id_categoria
                    ORDER BY p.destacado DESC, p.id_paquete DESC";

            $stmt = $db->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            foreach ($rows as &$row) {
                if (empty($row['imagen_url'])) {
                    $row['imagen_url'] = self::firstFallbackBySlug($row['slug']);
                } else {
                    // Optimizar imagen principal para catálogo (800px en vez de 1200px)
                    $row['imagen_url'] = ImageUrlService::catalogOptimize($row['imagen_url']);
                }
                // Parsear tags desde subquery (evita N+1 queries)
                $tagsCsv = (string)($row['tags_csv'] ?? '');
                $row['tags'] = $tagsCsv !== '' ? array_values(array_filter(array_map('trim', explode(',', $tagsCsv)))) : [];
                unset($row['tags_csv']);
            }
            unset($row);
            return $rows;
        } catch (PDOException $e) {
            error_log("Error en Paquete::all: " . $e->getMessage());
            throw new PDOException("Error al consultar el listado de paquetes turísticos.");
        }
    }

    // Funcion: Busca un paquete por su identificador.
    public static function findById(int $id): ?array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM paquetes WHERE id_paquete = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            $paquete = $stmt->fetch();
            return $paquete ? self::attachRelations($paquete) : null;
        } catch (PDOException $e) {
            error_log("Error en Paquete::findById: " . $e->getMessage());
            throw new PDOException("Error al buscar el paquete turístico solicitado.");
        }
    }

    // Funcion: Busca un paquete por su slug publico.
    public static function findBySlug(string $slug): ?array
    {
        return self::getDetailBySlug($slug);
    }

    // Funcion: Obtiene el detalle completo de un paquete por id.
    public static function getDetailById(int $id): ?array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare(self::detailSql() . " WHERE p.id_paquete = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            $paquete = $stmt->fetch();
            return $paquete ? self::attachDetailData($paquete) : null;
        } catch (PDOException $e) {
            error_log("Error en Paquete::getDetailById: " . $e->getMessage());
            throw new PDOException("Error al buscar el detalle del paquete turístico.");
        }
    }

    // Funcion: Obtiene el detalle completo de un paquete por slug.
    public static function getDetailBySlug(string $slug): ?array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare(self::detailSql() . " WHERE p.slug = :slug LIMIT 1");
            $stmt->execute([':slug' => trim($slug)]);
            $paquete = $stmt->fetch();
            return $paquete ? self::attachDetailData($paquete) : null;
        } catch (PDOException $e) {
            error_log("Error en Paquete::getDetailBySlug: " . $e->getMessage());
            throw new PDOException("Error al buscar el paquete turístico por URL.");
        }
    }

    // Funcion: Arma la consulta base para cargar detalles del paquete.
    private static function detailSql(): string
    {
        return "SELECT p.*,
                       c.nombre AS ciudad_nombre,
                       c.id_pais,
                       cat.nombre AS categoria_nombre,
                       (SELECT url_imagen FROM paquete_imagenes pi WHERE pi.id_paquete = p.id_paquete AND pi.es_principal = 1 LIMIT 1) AS imagen_url
                FROM paquetes p
                INNER JOIN ciudades c ON p.id_ciudad = c.id_ciudad
                INNER JOIN categorias_paquete cat ON p.id_categoria = cat.id_categoria";
    }

    // Funcion: Agrega relaciones y datos extra al paquete.
    private static function attachDetailData(array $paquete): array
    {
        if (empty($paquete['imagen_url'])) {
            $fallbacks = self::fallbackImagesBySlug($paquete['slug']);
            $paquete['imagen_url'] = $fallbacks[0] ?? null;
        } else {
            // Optimizar imagen principal para detalle (1200px, calidad 80)
            $paquete['imagen_url'] = ImageUrlService::optimize($paquete['imagen_url']);
        }
        $paquete = self::attachRelations($paquete);
        $paquete['imagenes'] = self::getImages((int)$paquete['id_paquete']);
        return $paquete;
    }

    // Funcion: Carga listas relacionadas del paquete.
    private static function attachRelations(array $paquete): array
    {
        $id = (int)$paquete['id_paquete'];
        $paquete['incluye'] = self::getRelationList($id, 'incluye');
        $paquete['no_incluye'] = self::getRelationList($id, 'no_incluye');
        $paquete['documentos'] = self::getRelationList($id, 'documentos');
        $paquete['tags'] = self::getRelationList($id, 'tags');
        $paquete['politicas'] = self::getPoliticas($id);
        $paquete['itinerario'] = self::getItinerario($id);
        return $paquete;
    }

    // Funcion: Obtiene las imagenes asociadas a un paquete.
    public static function getImages(int $id_paquete): array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM paquete_imagenes WHERE id_paquete = :id_paquete ORDER BY orden ASC, es_principal DESC, id_imagen ASC");
            $stmt->execute([':id_paquete' => $id_paquete]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Paquete::getImages: " . $e->getMessage());
            return [];
        }
    }

    // Funcion: Obtiene una lista relacionada segun la tabla indicada.
    public static function getRelationList(int $id_paquete, string $relation): array
    {
        if (!isset(self::RELATION_TABLES[$relation])) {
            return [];
        }

        try {
            $db = Database::getConnection();
            $table = self::RELATION_TABLES[$relation];
            $stmt = $db->prepare("SELECT texto FROM {$table} WHERE id_paquete = :id_paquete ORDER BY orden ASC, id ASC");
            $stmt->execute([':id_paquete' => $id_paquete]);
            return array_column($stmt->fetchAll(), 'texto');
        } catch (PDOException $e) {
            error_log("Error en Paquete::getRelationList({$relation}): " . $e->getMessage());
            return [];
        }
    }

    // Funcion: Obtiene las politicas de un paquete.
    public static function getPoliticas(int $id_paquete): array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT titulo, descripcion FROM paquete_politicas WHERE id_paquete = :id_paquete ORDER BY orden ASC, id ASC");
            $stmt->execute([':id_paquete' => $id_paquete]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Paquete::getPoliticas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Returns the itinerary for a package from the normalized table.
     * Falls back to the legacy JSON column if the table has no rows.
     */
    public static function getItinerario(int $id_paquete): array
    {
        try {
            $db = Database::getConnection();

            // Primary: normalized table
            $stmt = $db->prepare("SELECT dia, titulo, descripcion FROM paquete_itinerario WHERE id_paquete = :id_paquete ORDER BY orden ASC, id ASC");
            $stmt->execute([':id_paquete' => $id_paquete]);
            $rows = $stmt->fetchAll();

            if (!empty($rows)) {
                return $rows;
            }

            // Fallback: legacy JSON column in paquetes table
            $stmtJson = $db->prepare("SELECT itinerario FROM paquetes WHERE id_paquete = :id_paquete LIMIT 1");
            $stmtJson->execute([':id_paquete' => $id_paquete]);
            $pkg = $stmtJson->fetch();
            if ($pkg && !empty($pkg['itinerario']) && $pkg['itinerario'] !== '[]') {
                $decoded = json_decode($pkg['itinerario'], true);
                if (is_array($decoded) && count($decoded) > 0) {
                    return $decoded;
                }
            }

            return [];
        } catch (PDOException $e) {
            error_log("Error en Paquete::getItinerario: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retorna solo la primera imagen de fallback optimizada para catálogo (800px).
     * Evita computar la galería completa cuando solo se necesita una imagen.
     */
    public static function firstFallbackBySlug(string $slug): string
    {
        $galleries = [
            'cusco-valle-sagrado-machu-picchu' => 'https://images.unsplash.com/photo-1526392060635-9d6019884377?auto=format&fit=crop&w=1200&q=80',
            'cancun-all-inclusive' => 'https://images.unsplash.com/photo-1552074284-5e88ef1aef18?auto=format&fit=crop&w=1200&q=80',
            'patagonia-argentina' => 'https://images.unsplash.com/photo-1483728642387-6c3bdd6c93e5?auto=format&fit=crop&w=1200&q=80',
            'capitales-europeas' => 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&w=1200&q=80',
            'japon-esencial' => 'https://images.unsplash.com/photo-1545569341-9eb8b30979d9?auto=format&fit=crop&w=1200&q=80',
            'cartagena-romantica' => 'https://images.unsplash.com/photo-1583531352515-8884af319dc1?auto=format&fit=crop&w=1200&q=80',
            'orlando-familiar' => 'https://images.unsplash.com/photo-1597466599360-3b9775841aec?auto=format&fit=crop&w=1200&q=80',
            'huaraz-laguna-69' => 'https://images.unsplash.com/photo-1454496522488-7a8e488e8606?auto=format&fit=crop&w=1200&q=80',
            'arequipa-canon-del-colca' => 'https://images.unsplash.com/photo-1580619305218-8423a7ef79b4?auto=format&fit=crop&w=1200&q=80',
            'mancora-beach-escape' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80',
            'tarapoto-aventura' => 'https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=1200&q=80',
            'iquitos-amazonia' => 'https://images.unsplash.com/photo-1516426122078-c23e76319801?auto=format&fit=crop&w=1200&q=80',
            'lima-historica-gastronomica' => 'https://images.unsplash.com/photo-1531968455001-5c5272a67c71?auto=format&fit=crop&w=1200&q=80',
            'paracas-e-ica' => 'https://images.unsplash.com/photo-1504544750208-dc0358e63f7f?auto=format&fit=crop&w=1200&q=80',
            'punta-cana-all-inclusive' => 'https://images.unsplash.com/photo-1548574505-5e239809ee19?auto=format&fit=crop&w=1200&q=80',
            'buenos-aires-cultural' => 'https://images.unsplash.com/photo-1589909202802-8f4aadce1849?auto=format&fit=crop&w=1200&q=80',
            'santiago-de-chile' => 'https://images.unsplash.com/photo-1555990793-da11153a5364?auto=format&fit=crop&w=1200&q=80',
            'rio-de-janeiro' => 'https://images.unsplash.com/photo-1483729558449-99ef09a8c325?auto=format&fit=crop&w=1200&q=80',
            'miami-shopping-beach' => 'https://images.unsplash.com/photo-1533106497176-45ae19e68ba2?auto=format&fit=crop&w=1200&q=80',
            'cusco-express' => 'https://images.unsplash.com/photo-1526392060635-9d6019884377?auto=format&fit=crop&w=1200&q=80',
            'machu-picchu-full-day' => 'https://images.unsplash.com/photo-1587595431973-160d0d94add1?auto=format&fit=crop&w=1200&q=80',
            'europa-clasica' => 'https://images.unsplash.com/photo-1499856871958-5b9627545d1a?auto=format&fit=crop&w=1200&q=80',
            'japon-sakura' => 'https://images.unsplash.com/photo-1522383225653-ed111181a951?auto=format&fit=crop&w=1200&q=80',
        ];

        $url = $galleries[$slug] ?? 'assets/img/hero/hero-home.jpg';
        return ImageUrlService::catalogOptimize($url);
    }

    // Funcion: Devuelve imagenes de respaldo segun el slug del paquete.
    public static function fallbackImagesBySlug(string $slug): array
    {
        $galleries = [
            'cusco-valle-sagrado-machu-picchu' => [
                'https://images.unsplash.com/photo-1526392060635-9d6019884377?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1580619305218-8423a7ef79b4?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1531065208531-4036c0dba3ca?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1587595431973-160d0d94add1?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1533632359083-0185df1be85d?auto=format&fit=crop&w=900&q=80',
            ],
            'cancun-all-inclusive' => [
                'https://images.unsplash.com/photo-1552074284-5e88ef1aef18?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1519046904884-53103b34b206?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1544551763-46a013bb70d5?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=900&q=80',
            ],
            'patagonia-argentina' => [
                'https://images.unsplash.com/photo-1483728642387-6c3bdd6c93e5?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1519681393784-d120267933ba?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?auto=format&fit=crop&w=900&q=80',
            ],
            'capitales-europeas' => [
                'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1511739001486-6bfe10ce785f?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1529260830199-42c24126f198?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1525874684015-58379d421a52?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1515542622106-78bda8ba0e5b?auto=format&fit=crop&w=900&q=80',
            ],
            'japon-esencial' => [
                'https://images.unsplash.com/photo-1545569341-9eb8b30979d9?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1526481280693-3bfa7568e0f3?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1503899036084-c55cdd92da26?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1542051841857-5f90071e7989?auto=format&fit=crop&w=900&q=80',
            ],
            'cartagena-romantica' => [
                'https://images.unsplash.com/photo-1583531352515-8884af319dc1?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1518509562904-e7ef99cdcc86?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1500375592092-40eb2168fd21?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1506929562872-bb421503ef21?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1517760444937-f6397edcbbcd?auto=format&fit=crop&w=900&q=80',
            ],
            'orlando-familiar' => [
                'https://images.unsplash.com/photo-1597466599360-3b9775841aec?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1533107862482-0e6974b06ec4?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1513889961551-628c1e5e2ee9?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1581351721010-8cf859cb14a4?auto=format&fit=crop&w=900&q=80',
            ],
            'huaraz-laguna-69' => [
                'https://images.unsplash.com/photo-1454496522488-7a8e488e8606?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1419242902214-272b3f66ee7a?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1447752875215-b2761acb3c5d?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1434394354979-a235cd36269d?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?auto=format&fit=crop&w=900&q=80',
            ],
            'arequipa-canon-del-colca' => [
                'https://images.unsplash.com/photo-1580619305218-8423a7ef79b4?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1518128958364-65859d70aa41?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1596422846543-75c6fc197f07?auto=format&fit=crop&w=900&q=80',
            ],
            'mancora-beach-escape' => [
                'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1519046904884-53103b34b206?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1506929562872-bb421503ef21?auto=format&fit=crop&w=900&q=80',
            ],
            'tarapoto-aventura' => [
                'https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1518495973542-4542c06a5843?auto=format&fit=crop&w=900&q=80',
            ],
            'iquitos-amazonia' => [
                'https://images.unsplash.com/photo-1516426122078-c23e76319801?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1547471080-7cc2caa01a7e?auto=format&fit=crop&w=900&q=80',
            ],
            'lima-historica-gastronomica' => [
                'https://images.unsplash.com/photo-1531968455001-5c5272a67c71?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1555990793-da11153a5364?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1504544750208-dc0358e63f7f?auto=format&fit=crop&w=900&q=80',
            ],
            'paracas-e-ica' => [
                'https://images.unsplash.com/photo-1504544750208-dc0358e63f7f?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1519046904884-53103b34b206?auto=format&fit=crop&w=900&q=80',
            ],
            'punta-cana-all-inclusive' => [
                'https://images.unsplash.com/photo-1548574505-5e239809ee19?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1519046904884-53103b34b206?auto=format&fit=crop&w=900&q=80',
            ],
            'buenos-aires-cultural' => [
                'https://images.unsplash.com/photo-1589909202802-8f4aadce1849?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1588867702719-9d23f5af0b12?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1594489428504-5c0c480a15fd?auto=format&fit=crop&w=900&q=80',
            ],
            'santiago-de-chile' => [
                'https://images.unsplash.com/photo-1555990793-da11153a5364?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1518128958364-65859d70aa41?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1596422846543-75c6fc197f07?auto=format&fit=crop&w=900&q=80',
            ],
            'rio-de-janeiro' => [
                'https://images.unsplash.com/photo-1483729558449-99ef09a8c325?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1547471080-7cc2caa01a7e?auto=format&fit=crop&w=900&q=80',
            ],
            'miami-shopping-beach' => [
                'https://images.unsplash.com/photo-1533106497176-45ae19e68ba2?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1519046904884-53103b34b206?auto=format&fit=crop&w=900&q=80',
            ],
            'cusco-express' => [
                'https://images.unsplash.com/photo-1526392060635-9d6019884377?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1580619305218-8423a7ef79b4?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1531065208531-4036c0dba3ca?auto=format&fit=crop&w=900&q=80',
            ],
            'machu-picchu-full-day' => [
                'https://images.unsplash.com/photo-1587595431973-160d0d94add1?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1526392060635-9d6019884377?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1580619305218-8423a7ef79b4?auto=format&fit=crop&w=900&q=80',
            ],
            'europa-clasica' => [
                'https://images.unsplash.com/photo-1499856871958-5b9627545d1a?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1511739001486-6bfe10ce785f?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1529260830199-42c24126f198?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1525874684015-58379d421a52?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1515542622106-78bda8ba0e5b?auto=format&fit=crop&w=900&q=80',
            ],
            'japon-sakura' => [
                'https://images.unsplash.com/photo-1522383225653-ed111181a951?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1526481280693-3bfa7568e0f3?auto=format&fit=crop&w=900&q=80',
                'https://images.unsplash.com/photo-1503899036084-c55cdd92da26?auto=format&fit=crop&w=900&q=80',
            ],
        ];

        $gallery = $galleries[$slug] ?? ['assets/img/hero/hero-home.jpg'];
        return array_map(static fn($url) => ImageUrlService::optimize($url), $gallery);
    }



    // Funcion: Crea un nuevo registro principal de paquete.
    public static function create(array $data, string $url_imagen = ''): bool
    {
        $db = null;
        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            $normalized = self::normalizePackageData($data);
            $columns = implode(', ', self::PACKAGE_COLUMNS);
            $placeholders = ':' . implode(', :', self::PACKAGE_COLUMNS);
            $stmt = $db->prepare("INSERT INTO paquetes ({$columns}) VALUES ({$placeholders})");
            $stmt->execute(self::paramsFromData($normalized));

            $id_paquete = (int)$db->lastInsertId();
            self::savePrincipalImage($db, $id_paquete, $url_imagen);
            self::syncPackageLists($id_paquete, $data, $db);

            $db->commit();
            return true;
        } catch (PDOException $e) {
            if ($db && $db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error en Paquete::create: " . $e->getMessage());
            throw new PDOException("Error al registrar el paquete turístico en la base de datos.");
        }
    }

    // Funcion: Actualiza los datos principales de un paquete.
    public static function update(int $id, array $data, string $url_imagen = '', bool $syncPrincipalImage = true): bool
    {
        $db = null;
        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            $normalized = self::normalizePackageData($data);
            $set = implode(', ', array_map(static fn(string $column): string => "{$column} = :{$column}", self::PACKAGE_COLUMNS));
            $params = self::paramsFromData($normalized);
            $params[':id'] = $id;

            $stmt = $db->prepare("UPDATE paquetes SET {$set} WHERE id_paquete = :id");
            $stmt->execute($params);

            if ($syncPrincipalImage) {
                self::savePrincipalImage($db, $id, $url_imagen);
            }
            self::syncPackageLists($id, $data, $db);

            $db->commit();
            return true;
        } catch (PDOException $e) {
            if ($db && $db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Error en Paquete::update: " . $e->getMessage());
            throw new PDOException("Error al actualizar la información del paquete turístico.");
        }
    }

    // Funcion: Normaliza los datos del paquete antes de guardarlos.
    private static function normalizePackageData(array $data): array
    {
        return [
            'id_categoria' => (int)($data['id_categoria'] ?? 0),
            'id_ciudad' => (int)($data['id_ciudad'] ?? 0),
            'nombre' => trim((string)($data['nombre'] ?? '')),
            'slug' => trim((string)($data['slug'] ?? '')),
            'descripcion_corta' => self::nullableText($data['descripcion_corta'] ?? null),
            'descripcion' => self::nullableText($data['descripcion'] ?? null),
            'itinerario' => self::normalizeItinerary($data['itinerario'] ?? '[]'),
            'precio_base' => (float)($data['precio_base'] ?? 0),
            'precio_anterior' => self::nullableDecimal($data['precio_anterior'] ?? null),
            'duracion_dias' => (int)($data['duracion_dias'] ?? 0),
            'duracion_noches' => (int)($data['duracion_noches'] ?? 0),
            'cupo_maximo' => self::nullableInt($data['cupo_maximo'] ?? null),
            'dificultad' => self::nullableText($data['dificultad'] ?? null),
            'hotel' => self::nullableText($data['hotel'] ?? null),
            'habitacion' => self::nullableText($data['habitacion'] ?? null),
            'comidas' => self::nullableText($data['comidas'] ?? null),
            'vuelos' => self::nullableText($data['vuelos'] ?? null),
            'movilidad' => self::nullableText($data['movilidad'] ?? null),
            'guia' => self::nullableText($data['guia'] ?? null),
            'destacado' => (int)!empty($data['destacado']),
            'disponible' => (int)($data['disponible'] ?? 1),
        ];
    }

    // Funcion: Convierte los datos del paquete en parametros SQL.
    private static function paramsFromData(array $data): array
    {
        $params = [];
        foreach (self::PACKAGE_COLUMNS as $column) {
            $params[':' . $column] = $data[$column];
        }
        return $params;
    }

    // Funcion: Convierte texto vacio en valor nulo.
    private static function nullableText($value): ?string
    {
        $value = trim((string)($value ?? ''));
        return $value === '' ? null : $value;
    }

    // Funcion: Convierte importes vacios en decimal o nulo.
    private static function nullableDecimal($value): ?float
    {
        if ($value === null || trim((string)$value) === '') {
            return null;
        }
        $number = (float)$value;
        return $number > 0 ? $number : null;
    }

    // Funcion: Convierte enteros vacios en numero o nulo.
    private static function nullableInt($value): ?int
    {
        if ($value === null || trim((string)$value) === '') {
            return null;
        }
        $number = (int)$value;
        return $number > 0 ? $number : null;
    }

    // Funcion: Normaliza el texto del itinerario recibido.
    private static function normalizeItinerary($value): string
    {
        $value = trim((string)$value);
        if ($value === '') {
            return '[]';
        }
        $decoded = json_decode($value, true);
        if (!is_array($decoded)) {
            return '[]';
        }
        return json_encode(array_values($decoded), JSON_UNESCAPED_UNICODE);
    }

    // Funcion: Guarda o actualiza la imagen principal del paquete.
    private static function savePrincipalImage(PDO $db, int $id_paquete, string $url_imagen): void
    {
        $url_imagen = ImageUrlService::optimize(trim($url_imagen));
        $stmtCheck = $db->prepare("SELECT id_imagen FROM paquete_imagenes WHERE id_paquete = :id AND es_principal = 1 LIMIT 1");
        $stmtCheck->execute([':id' => $id_paquete]);
        $existingImg = $stmtCheck->fetch();

        if ($url_imagen !== '') {
            if ($existingImg) {
                $stmtImg = $db->prepare("UPDATE paquete_imagenes SET url_imagen = :url WHERE id_imagen = :id_img");
                $stmtImg->execute([':url' => $url_imagen, ':id_img' => $existingImg['id_imagen']]);
            } else {
                $stmtImg = $db->prepare("INSERT INTO paquete_imagenes (id_paquete, url_imagen, es_principal) VALUES (:id_paquete, :url, 1)");
                $stmtImg->execute([':id_paquete' => $id_paquete, ':url' => $url_imagen]);
            }
            return;
        }

        if ($existingImg) {
            $stmtDel = $db->prepare("DELETE FROM paquete_imagenes WHERE id_imagen = :id_img");
            $stmtDel->execute([':id_img' => $existingImg['id_imagen']]);
        }
    }

    // Funcion: Sincroniza listas editables como inclusiones, politicas y galeria.
    public static function syncPackageLists(int $id_paquete, array $data, ?PDO $db = null): void
    {
        $ownTransaction = false;
        $db = $db ?? Database::getConnection();
        if (!$db->inTransaction()) {
            $db->beginTransaction();
            $ownTransaction = true;
        }

        try {
            foreach (self::RELATION_TABLES as $key => $table) {
                self::replaceSimpleList($db, $table, $id_paquete, self::listFromPost($data[$key . '_items'] ?? $data[$key] ?? []));
            }
            self::replacePoliticas($db, $id_paquete, self::politicasFromPost($data['politicas_items'] ?? $data['politicas'] ?? []));

            if ($ownTransaction) {
                $db->commit();
            }
        } catch (PDOException $e) {
            if ($ownTransaction && $db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    // Funcion: Reemplaza una lista simple asociada al paquete.
    private static function replaceSimpleList(PDO $db, string $table, int $id_paquete, array $items): void
    {
        $delete = $db->prepare("DELETE FROM {$table} WHERE id_paquete = :id_paquete");
        $delete->execute([':id_paquete' => $id_paquete]);

        $insert = $db->prepare("INSERT INTO {$table} (id_paquete, texto, orden) VALUES (:id_paquete, :texto, :orden)");
        foreach ($items as $index => $texto) {
            $insert->execute([
                ':id_paquete' => $id_paquete,
                ':texto' => $texto,
                ':orden' => $index + 1,
            ]);
        }
    }

    // Funcion: Reemplaza las politicas asociadas al paquete.
    private static function replacePoliticas(PDO $db, int $id_paquete, array $items): void
    {
        $delete = $db->prepare("DELETE FROM paquete_politicas WHERE id_paquete = :id_paquete");
        $delete->execute([':id_paquete' => $id_paquete]);

        $insert = $db->prepare("INSERT INTO paquete_politicas (id_paquete, titulo, descripcion, orden) VALUES (:id_paquete, :titulo, :descripcion, :orden)");
        foreach ($items as $index => $item) {
            $insert->execute([
                ':id_paquete' => $id_paquete,
                ':titulo' => $item['titulo'],
                ':descripcion' => $item['descripcion'],
                ':orden' => $index + 1,
            ]);
        }
    }

    // Funcion: Convierte datos enviados en una lista limpia.
    private static function listFromPost($value): array
    {
        if (is_array($value)) {
            $items = $value;
        } else {
            $items = preg_split('/\R+/', (string)$value) ?: [];
        }

        $clean = [];
        foreach ($items as $item) {
            $item = trim((string)$item);
            if ($item !== '') {
                $clean[] = $item;
            }
        }
        return array_values(array_unique($clean));
    }

    // Funcion: Convierte politicas enviadas en una estructura limpia.
    private static function politicasFromPost($value): array
    {
        if (is_array($value)) {
            $lines = $value;
        } else {
            $lines = preg_split('/\R+/', (string)$value) ?: [];
        }

        $items = [];
        foreach ($lines as $line) {
            if (is_array($line)) {
                $titulo = self::nullableText($line['titulo'] ?? null) ?? 'Policy';
                $descripcion = self::nullableText($line['descripcion'] ?? null);
            } else {
                $line = trim((string)$line);
                if ($line === '') {
                    continue;
                }
                $parts = preg_split('/\s*[:|]\s*/', $line, 2);
                $titulo = trim($parts[0] ?? 'Policy');
                $descripcion = trim($parts[1] ?? $line);
            }

            if ($descripcion !== null && $descripcion !== '') {
                $items[] = ['titulo' => $titulo, 'descripcion' => $descripcion];
            }
        }
        return $items;
    }

    // Funcion: Une una relacion del paquete en texto legible.
    public static function relationText(array $paquete, string $key): string
    {
        if ($key === 'politicas') {
            $lines = [];
            foreach (($paquete['politicas'] ?? []) as $item) {
                $lines[] = trim(($item['titulo'] ?? '') . ': ' . ($item['descripcion'] ?? ''));
            }
            return implode("\n", $lines);
        }
        return implode("\n", $paquete[$key] ?? []);
    }

    // Funcion: Elimina un paquete si no rompe sus relaciones.
    public static function delete(int $id): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("DELETE FROM paquetes WHERE id_paquete = :id");
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log("Error en Paquete::delete: " . $e->getMessage());
            throw new PDOException("No se pudo eliminar el paquete. Es posible que esté asociado a reservas activas.");
        }
    }

    // Funcion: Activa o desactiva la disponibilidad de un paquete.
    public static function setDisponible(int $id, int $disponible): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('UPDATE paquetes SET disponible = :disp WHERE id_paquete = :id');
        return $stmt->execute([':disp' => $disponible, ':id' => $id]);
    }

    // Funcion: Indica si el paquete tiene reservas asociadas.
    public static function hasReservas(int $id): bool
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('SELECT COUNT(*) FROM detalle_reservas WHERE id_paquete = :id');
        $stmt->execute([':id' => $id]);
        return (int)$stmt->fetchColumn() > 0;
    }

    // Funcion: Verifica si un slug ya esta usado por otro paquete.
    public static function slugExists(string $slug, ?int $excludeId = null): bool
    {
        try {
            $db = Database::getConnection();
            $sql = "SELECT COUNT(*) FROM paquetes WHERE slug = :slug";
            $params = [':slug' => $slug];

            if ($excludeId !== null) {
                $sql .= " AND id_paquete != :exclude_id";
                $params[':exclude_id'] = $excludeId;
            }

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error en Paquete::slugExists: " . $e->getMessage());
            throw new PDOException("Error al verificar la disponibilidad de la URL amigable (slug).");
        }
    }

    // Funcion: Obtiene la imagen principal del paquete.
    public static function getPrincipalImage(int $id): ?string
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT url_imagen FROM paquete_imagenes WHERE id_paquete = :id AND es_principal = 1 LIMIT 1");
            $stmt->execute([':id' => $id]);
            $img = $stmt->fetch();
            return $img ? $img['url_imagen'] : null;
        } catch (PDOException $e) {
            error_log("Error en Paquete::getPrincipalImage: " . $e->getMessage());
            return null;
        }
    }

    // Funcion: Lista las categorias disponibles para paquetes.
    public static function getCategorias(): array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM categorias_paquete ORDER BY nombre ASC");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Paquete::getCategorias: " . $e->getMessage());
            throw new PDOException("Error al obtener las categorías.");
        }
    }

    // Funcion: Lista las ciudades disponibles para paquetes.
    public static function getCiudades(): array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM ciudades ORDER BY nombre ASC");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Paquete::getCiudades: " . $e->getMessage());
            throw new PDOException("Error al obtener las ciudades de destino.");
        }
    }

    /**
     * Returns up to $limit related packages (same city or same category), excluding the current one.
     */
    public static function getRelatedPackages(int $currentId, string $cityName, string $categoryName, int $limit = 4): array
    {
        try {
            $db = Database::getConnection();
            $sql = "SELECT p.id_paquete, p.nombre, p.slug, p.descripcion_corta, p.precio_base, p.precio_anterior,
                           p.duracion_dias, p.duracion_noches, p.dificultad,
                           c.nombre AS ciudad_nombre,
                           cat.nombre AS categoria_nombre,
                           (SELECT url_imagen FROM paquete_imagenes pi WHERE pi.id_paquete = p.id_paquete AND pi.es_principal = 1 LIMIT 1) AS imagen_url
                    FROM paquetes p
                    INNER JOIN ciudades c ON p.id_ciudad = c.id_ciudad
                    INNER JOIN categorias_paquete cat ON p.id_categoria = cat.id_categoria
                    WHERE p.disponible = 1
                      AND p.id_paquete != :current_id
                      AND (c.nombre = :city OR cat.nombre = :category)
                    ORDER BY p.destacado DESC, p.id_paquete DESC
                    LIMIT :lim";

            $stmt = $db->prepare($sql);
            $stmt->bindValue(':current_id', $currentId, PDO::PARAM_INT);
            $stmt->bindValue(':city', $cityName, PDO::PARAM_STR);
            $stmt->bindValue(':category', $categoryName, PDO::PARAM_STR);
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll();

            foreach ($rows as &$row) {
                if (empty($row['imagen_url'])) {
                    $row['imagen_url'] = self::firstFallbackBySlug($row['slug']);
                } else {
                    $row['imagen_url'] = ImageUrlService::catalogOptimize($row['imagen_url']);
                }
            }
            unset($row);

            return $rows;
        } catch (PDOException $e) {
            error_log("Error en Paquete::getRelatedPackages: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Guarda la galería completa de imágenes de un paquete.
     * Recibe arrays del form: gallery_id[], gallery_url[], gallery_orden[], gallery_principal[]
     */
    public static function saveGallery(int $id_paquete, array $gallery, ?PDO $db = null): void
    {
        $ownTransaction = false;
        $db = $db ?? Database::getConnection();
        if (!$db->inTransaction()) {
            $db->beginTransaction();
            $ownTransaction = true;
        }

        $ids     = $gallery['gallery_id']     ?? [];
        $urls    = $gallery['gallery_url']    ?? [];
        $ordenes = $gallery['gallery_orden']  ?? [];
        $principalImage = (string)($gallery['principal_image'] ?? '');
        $featuredUrl = ImageUrlService::optimize(trim((string)($gallery['url_imagen'] ?? '')));
        $legacyPrincipales = $gallery['gallery_principal'] ?? [];
        if (!is_array($legacyPrincipales)) {
            $legacyPrincipales = [$legacyPrincipales];
        }

        try {
            // 1. Eliminar marcadas antes de recalcular la principal.
            $deleteIds = self::deleteIdsFromGalleryPost($gallery['delete_ids'] ?? []);
            foreach ($deleteIds as $delId) {
                $stmt = $db->prepare("DELETE FROM paquete_imagenes WHERE id_imagen = :id AND id_paquete = :pid");
                $stmt->execute([':id' => $delId, ':pid' => $id_paquete]);
            }

            $deleteLookup = array_flip(array_map('intval', $deleteIds));
            $legacyPrincipales = array_map('strval', $legacyPrincipales);
            $seenImageKeys = [];
            $preferredPrincipalKey = '';

            if (strpos($principalImage, 'existing:') === 0) {
                $principalId = (int)substr($principalImage, 9);
                foreach ($ids as $idx => $id_img) {
                    if ((int)$id_img !== $principalId) {
                        continue;
                    }
                    $candidateUrl = trim($urls[$idx] ?? '');
                    if ($featuredUrl !== '') {
                        $candidateUrl = $featuredUrl;
                    }
                    $candidateUrl = ImageUrlService::optimize($candidateUrl);
                    if ($candidateUrl !== '') {
                        $preferredPrincipalKey = ImageUrlService::imageKey($candidateUrl);
                    }
                    break;
                }
            } elseif (strpos($principalImage, 'new:') === 0) {
                $principalIdx = (int)substr($principalImage, 4);
                $candidateUrl = ImageUrlService::optimize(trim($gallery['new_url'][$principalIdx] ?? ''));
                if ($candidateUrl !== '') {
                    $preferredPrincipalKey = ImageUrlService::imageKey($candidateUrl);
                }
            }

            // 2. Actualizar existentes. Una URL vacía equivale a quitar la imagen.
            foreach ($ids as $idx => $id_img) {
                $id_img = (int)$id_img;
                if ($id_img <= 0 || isset($deleteLookup[$id_img])) {
                    continue;
                }

                $url = trim($urls[$idx] ?? '');
                if ($featuredUrl !== '' && $principalImage === 'existing:' . $id_img) {
                    $url = $featuredUrl;
                }
                $url = ImageUrlService::optimize($url);
                if ($url === '') {
                    $stmt = $db->prepare("DELETE FROM paquete_imagenes WHERE id_imagen = :id AND id_paquete = :pid");
                    $stmt->execute([':id' => $id_img, ':pid' => $id_paquete]);
                    continue;
                }

                $imageKey = ImageUrlService::imageKey($url);
                $orden = (int)($ordenes[$idx] ?? 0);
                $esPrincipal = ($principalImage === 'existing:' . $id_img || in_array((string)$id_img, $legacyPrincipales, true)) ? 1 : 0;

                if (isset($seenImageKeys[$imageKey])) {
                    $previous = $seenImageKeys[$imageKey];
                    if ($imageKey === $preferredPrincipalKey && empty($previous['principal']) && !empty($previous['id'])) {
                        $stmt = $db->prepare("DELETE FROM paquete_imagenes WHERE id_imagen = :id AND id_paquete = :pid");
                        $stmt->execute([':id' => $previous['id'], ':pid' => $id_paquete]);
                    } else {
                        $stmt = $db->prepare("DELETE FROM paquete_imagenes WHERE id_imagen = :id AND id_paquete = :pid");
                        $stmt->execute([':id' => $id_img, ':pid' => $id_paquete]);
                        continue;
                    }
                }

                $stmt = $db->prepare("UPDATE paquete_imagenes SET url_imagen = :url, orden = :orden, es_principal = :pri WHERE id_imagen = :id AND id_paquete = :pid");
                $stmt->execute([':url' => $url, ':orden' => $orden, ':pri' => $esPrincipal, ':id' => $id_img, ':pid' => $id_paquete]);
                $seenImageKeys[$imageKey] = ['id' => $id_img, 'principal' => $esPrincipal === 1];
            }

            // 3. Insertar nuevas.
            $newUrls    = $gallery['new_url']    ?? [];
            $newOrdenes = $gallery['new_orden']  ?? [];
            $newPri     = $gallery['new_principal'] ?? [];
            if (!is_array($newPri)) {
                $newPri = [$newPri];
            }
            $newPri = array_map('strval', $newPri);

            foreach ($newUrls as $idx => $url) {
                $url = ImageUrlService::optimize(trim($url));
                if ($url === '') {
                    continue;
                }
                $orden = (int)($newOrdenes[$idx] ?? 0);
                $esPrincipal = ($principalImage === 'new:' . $idx || in_array((string)$idx, $newPri, true)) ? 1 : 0;
                $imageKey = ImageUrlService::imageKey($url);

                if (isset($seenImageKeys[$imageKey])) {
                    $previous = $seenImageKeys[$imageKey];
                    if ($esPrincipal && !empty($previous['id'])) {
                        $db->prepare("UPDATE paquete_imagenes SET es_principal = 1 WHERE id_paquete = :pid AND id_imagen = :id")
                           ->execute([':pid' => $id_paquete, ':id' => $previous['id']]);
                        $seenImageKeys[$imageKey]['principal'] = true;
                    }
                    continue;
                }

                $stmt = $db->prepare("INSERT INTO paquete_imagenes (id_paquete, url_imagen, es_principal, orden) VALUES (:pid, :url, :pri, :orden)");
                $stmt->execute([':pid' => $id_paquete, ':url' => $url, ':pri' => $esPrincipal, ':orden' => $orden]);
                $seenImageKeys[$imageKey] = ['id' => (int)$db->lastInsertId(), 'principal' => $esPrincipal === 1];
            }

            // 4. Si solo se editó la imagen destacada y no hay galería, conservarla como principal.
            if (empty($ids) && empty($newUrls) && $featuredUrl !== '') {
                self::savePrincipalImage($db, $id_paquete, $featuredUrl);
            }

            // 5. Asegurar que haya exactamente una principal cuando existan imágenes.
            $stmt = $db->prepare("SELECT id_imagen FROM paquete_imagenes WHERE id_paquete = :pid AND es_principal = 1 ORDER BY orden ASC, id_imagen ASC LIMIT 1");
            $stmt->execute([':pid' => $id_paquete]);
            $first = $stmt->fetch();

            if (!$first) {
                $stmt = $db->prepare("SELECT id_imagen FROM paquete_imagenes WHERE id_paquete = :pid ORDER BY orden ASC, id_imagen ASC LIMIT 1");
                $stmt->execute([':pid' => $id_paquete]);
                $first = $stmt->fetch();
                if ($first) {
                    $db->prepare("UPDATE paquete_imagenes SET es_principal = 1 WHERE id_paquete = :pid AND id_imagen = :id")
                       ->execute([':pid' => $id_paquete, ':id' => $first['id_imagen']]);
                }
            }

            if ($first) {
                $db->prepare("UPDATE paquete_imagenes SET es_principal = 0 WHERE id_paquete = :pid AND id_imagen != :id")
                   ->execute([':pid' => $id_paquete, ':id' => $first['id_imagen']]);
            }

            if ($ownTransaction) {
                $db->commit();
            }
        } catch (PDOException $e) {
            if ($ownTransaction && $db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    // Funcion: Obtiene los ids de galeria que deben eliminarse.
    private static function deleteIdsFromGalleryPost($value): array
    {
        $raw = is_array($value) ? $value : [$value];
        $ids = [];
        foreach ($raw as $item) {
            foreach (explode(',', (string)$item) as $id) {
                $id = (int)trim($id);
                if ($id > 0) {
                    $ids[] = $id;
                }
            }
        }
        return array_values(array_unique($ids));
    }

    /**
     * Obtiene todas las imágenes de un paquete ordenadas.
     */
    public static function getGallery(int $id_paquete): array
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT id_imagen, url_imagen, es_principal, orden FROM paquete_imagenes WHERE id_paquete = :id ORDER BY orden ASC, es_principal DESC, id_imagen ASC");
            $stmt->execute([':id' => $id_paquete]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en Paquete::getGallery: " . $e->getMessage());
            return [];
        }
    }
}

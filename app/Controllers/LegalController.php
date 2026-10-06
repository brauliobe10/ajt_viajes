<?php

declare(strict_types=1);

// Funcion del archivo: Muestra paginas legales alimentadas desde configuraci?n.
namespace App\Controllers;

/**
 * LegalController
 *
 * Renders legal static pages (Términos, Privacidad, Cancelaciones, Reclamaciones)
 * with content pulled from the configuracion table via Config helper.
 */
class LegalController
{
    /**
     * Página de Términos y Condiciones.
     */
    public function terminos(): void
    {
        $title = "Términos y Condiciones — Viajes AJT";
        $description = "Términos y condiciones de Viajes AJT.";
        $pageKey = "terminos";

        $contenido = \App\Helper\Config::getString('terminos_contenido', '<p>Pendiente de configurar</p>');

        $baseViewsDir = dirname(__DIR__) . '/Views/';
        require_once $baseViewsDir . 'layouts/header.php';
        require_once $baseViewsDir . 'legal/terminos.php';
        require_once $baseViewsDir . 'layouts/footer.php';
    }

    /**
     * Página de Política de Privacidad.
     */
    public function privacidad(): void
    {
        $title = "Política de Privacidad — Viajes AJT";
        $description = "Política de privacidad de Viajes AJT.";
        $pageKey = "privacidad";

        $contenido = \App\Helper\Config::getString('privacidad_contenido', '<p>Pendiente de configurar</p>');

        $baseViewsDir = dirname(__DIR__) . '/Views/';
        require_once $baseViewsDir . 'layouts/header.php';
        require_once $baseViewsDir . 'legal/privacidad.php';
        require_once $baseViewsDir . 'layouts/footer.php';
    }

    /**
     * Página de Política de Cancelación.
     */
    public function cancelaciones(): void
    {
        $title = "Política de Cancelación — Viajes AJT";
        $description = "Política de cancelación de Viajes AJT.";
        $pageKey = "cancelaciones";

        $contenido = \App\Helper\Config::getString('cancelaciones_contenido', '<p>Pendiente de configurar</p>');

        $baseViewsDir = dirname(__DIR__) . '/Views/';
        require_once $baseViewsDir . 'layouts/header.php';
        require_once $baseViewsDir . 'legal/cancelaciones.php';
        require_once $baseViewsDir . 'layouts/footer.php';
    }

    /**
     * Página de Libro de Reclamaciones.
     */
    public function reclamaciones(): void
    {
        $title = "Libro de Reclamaciones — Viajes AJT";
        $description = "Libro de reclamaciones de Viajes AJT.";
        $pageKey = "reclamaciones";

        $libroUrl = \App\Helper\Config::getString('libro_reclamaciones_url');

        $baseViewsDir = dirname(__DIR__) . '/Views/';
        require_once $baseViewsDir . 'layouts/header.php';
        require_once $baseViewsDir . 'legal/reclamaciones.php';
        require_once $baseViewsDir . 'layouts/footer.php';
    }
}

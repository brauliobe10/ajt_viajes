<?php

declare(strict_types=1);

// Funcion del archivo: Administra creacion, edicion, disponibilidad e imagenes de paquetes.
namespace App\Controllers;

use App\Models\Paquete;
use App\Services\ImageUrlService;
use Exception;

/**
 * Clase PaqueteController
 * Gestiona el mantenimiento (CRUD) de los paquetes turísticos en el panel de administración.
 */
class PaqueteController
{
    /**
     * Constructor del controlador.
     * Implementa la protección de rutas verificando la existencia de una sesión de administrador activa.
     */
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Verificar si el usuario está autenticado y tiene rol de Administrador (id_rol = 2)
        if (!isset($_SESSION['usuario']) || (int)$_SESSION['usuario']['id_rol'] !== 2) {
            $_SESSION['error'] = "Acceso denegado. Debe iniciar sesión como administrador para acceder al panel.";
            header('Location: ' . BASE_URL . '/login');
            exit();
        }
    }

    /**
     * Muestra el listado de paquetes turísticos.
     *
     * @return void
     */
    public function index(): void
    {
        try {
            $paquetes = Paquete::all();
            $categorias = Paquete::getCategorias();
        } catch (Exception $e) {
            error_log("PaqueteController error: " . $e->getMessage());
            $_SESSION['error'] = "Error al cargar los paquetes.";
            $paquetes = [];
            $categorias = [];
        }

        $title = "Paquetes · Admin AJT";
        $pageKey = "paquetes";

        $baseViewsDir = dirname(__DIR__) . '/Views/';
        require_once $baseViewsDir . 'layouts/admin_header.php';
        require_once $baseViewsDir . 'admin/paquetes/index.php';
        require_once $baseViewsDir . 'layouts/admin_footer.php';
    }

    /**
     * Muestra el formulario para crear un nuevo paquete.
     *
     * @return void
     */
    public function crear(): void
    {
        try {
            $categorias = Paquete::getCategorias();
            $ciudades = Paquete::getCiudades();
        } catch (Exception $e) {
            error_log("PaqueteController error: " . $e->getMessage());
            $_SESSION['error'] = "Error al cargar los datos necesarios.";
            header('Location: ' . BASE_URL . '/admin/paquetes');
            exit();
        }

        $title = "Nuevo paquete · Admin AJT";
        $pageKey = "paquetes";

        $baseViewsDir = dirname(__DIR__) . '/Views/';
        require_once $baseViewsDir . 'layouts/admin_header.php';
        require_once $baseViewsDir . 'admin/paquetes/create.php';
        require_once $baseViewsDir . 'layouts/admin_footer.php';
    }

    /**
     * Procesa la creación de un nuevo paquete turístico (POST).
     *
     * @return void
     */
    public function guardar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/paquetes');
            exit();
        }

        // Sanitización y validación básica
        $data = $this->packageDataFromPost();
        $nombre = $data['nombre'];
        $slug = $data['slug'];
        $id_categoria = $data['id_categoria'];
        $id_ciudad = $data['id_ciudad'];
        $duracion_dias = $data['duracion_dias'];
        $precio_base = $data['precio_base'];
        $url_imagen = isset($_POST['url_imagen']) ? trim($_POST['url_imagen']) : '';

        // Validar campos obligatorios
        if (empty($nombre) || empty($slug) || $id_categoria <= 0 || $id_ciudad <= 0 || $duracion_dias <= 0 || $precio_base <= 0) {
            $_SESSION['error'] = "Por favor, complete todos los campos obligatorios marcados con (*).";
            $this->conservarPostData();
            header('Location: ' . BASE_URL . '/admin/paquetes/create');
            exit();
        }

        try {
            // Validar que el slug sea único
            if (Paquete::slugExists($slug)) {
                $_SESSION['error'] = "El slug (URL amigable) ya se encuentra registrado. Por favor, asigne uno diferente.";
                $this->conservarPostData();
                header('Location: ' . BASE_URL . '/admin/paquetes/create');
                exit();
            }

            // Valida la imagen antes de guardarla para evitar paquetes con fotos rotas.
            $url_imagen = ImageUrlService::optimize($url_imagen);
            if (!$this->validarImagenPrincipal($url_imagen)) {
                $this->conservarPostData();
                header('Location: ' . BASE_URL . '/admin/paquetes/create');
                exit();
            }

            if (Paquete::create($data, $url_imagen)) {
                $_SESSION['success'] = "¡El paquete turístico se ha creado con éxito!";
                $this->limpiarPostData();
                header('Location: ' . BASE_URL . '/admin/paquetes');
                exit();
            }
        } catch (Exception $e) {
            error_log("PaqueteController error: " . $e->getMessage());
            $_SESSION['error'] = "Error al procesar el paquete.";
            $this->conservarPostData();
            header('Location: ' . BASE_URL . '/admin/paquetes/create');
            exit();
        }
    }

    /**
     * Muestra el formulario para editar un paquete turístico existente.
     *
     * @param string $id
     * @return void
     */
    public function editar(string $id): void
    {
        $id = (int)$id;
        if ($id <= 0) {
            $_SESSION['error'] = "ID de paquete no válido.";
            header('Location: ' . BASE_URL . '/admin/paquetes');
            exit();
        }

        try {
            $paquete = Paquete::findById($id);
            if (!$paquete) {
                $_SESSION['error'] = "El paquete solicitado no existe en la base de datos.";
                header('Location: ' . BASE_URL . '/admin/paquetes');
                exit();
            }

            $categorias = Paquete::getCategorias();
            $ciudades = Paquete::getCiudades();
            $imagen_url = Paquete::getPrincipalImage($id);
            $galeria = Paquete::getGallery($id);
        } catch (Exception $e) {
            error_log("PaqueteController error: " . $e->getMessage());
            $_SESSION['error'] = "Error al recuperar la información.";
            header('Location: ' . BASE_URL . '/admin/paquetes');
            exit();
        }

        $title = "Editar paquete · Admin AJT";
        $pageKey = "paquetes";

        $baseViewsDir = dirname(__DIR__) . '/Views/';
        require_once $baseViewsDir . 'layouts/admin_header.php';
        require_once $baseViewsDir . 'admin/paquetes/edit.php';
        require_once $baseViewsDir . 'layouts/admin_footer.php';
    }

    /**
     * Procesa la actualización de un paquete turístico existente (POST).
     *
     * @param string $id
     * @return void
     */
    public function actualizar(string $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/paquetes');
            exit();
        }

        $id = (int)$id;
        if ($id <= 0) {
            $_SESSION['error'] = "ID de paquete no válido para actualizar.";
            header('Location: ' . BASE_URL . '/admin/paquetes');
            exit();
        }

        $data = $this->packageDataFromPost();
        $nombre = $data['nombre'];
        $slug = $data['slug'];
        $id_categoria = $data['id_categoria'];
        $id_ciudad = $data['id_ciudad'];
        $duracion_dias = $data['duracion_dias'];
        $precio_base = $data['precio_base'];
        $url_imagen = isset($_POST['url_imagen']) ? trim($_POST['url_imagen']) : '';

        // Validar campos obligatorios
        if (empty($nombre) || empty($slug) || $id_categoria <= 0 || $id_ciudad <= 0 || $duracion_dias <= 0 || $precio_base <= 0) {
            $_SESSION['error'] = "Por favor, complete todos los campos obligatorios marcados con (*).";
            header('Location: ' . BASE_URL . '/admin/paquetes/edit/' . $id);
            exit();
        }

        try {
            // Validar que el slug no esté en uso por otro paquete
            if (Paquete::slugExists($slug, $id)) {
                $_SESSION['error'] = "El slug (URL amigable) ya se encuentra registrado en otro paquete.";
                header('Location: ' . BASE_URL . '/admin/paquetes/edit/' . $id);
                exit();
            }

            // Revalida la imagen si se modifica el paquete, manteniendo la galería sin URLs rotas.
            $url_imagen = ImageUrlService::optimize($url_imagen);
            if (!$this->validarImagenPrincipal($url_imagen)) {
                header('Location: ' . BASE_URL . '/admin/paquetes/edit/' . $id);
                exit();
            }

            if (Paquete::update($id, $data, $url_imagen, false)) {
                // Guardar galería de imágenes
                $galleryData = $_POST;
                $galleryData['url_imagen'] = $url_imagen;
                Paquete::saveGallery($id, $galleryData);
                $_SESSION['success'] = "¡La información del paquete turístico se actualizó correctamente!";
                header('Location: ' . BASE_URL . '/admin/paquetes');
                exit();
            }
        } catch (Exception $e) {
            error_log("PaqueteController error: " . $e->getMessage());
            $_SESSION['error'] = "Error al procesar el paquete.";
            header('Location: ' . BASE_URL . '/admin/paquetes/edit/' . $id);
            exit();
        }
    }

    /**
     * Procesa la eliminación de un paquete turístico (POST).
     *
     * @param string $id
     * @return void
     */
    public function eliminar(string $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/paquetes');
            exit();
        }

        $id = (int)$id;
        if ($id <= 0) {
            $_SESSION['error'] = "ID de paquete no válido para eliminar.";
            header('Location: ' . BASE_URL . '/admin/paquetes');
            exit();
        }

        try {
            if (Paquete::hasReservas($id)) {
                // Tiene reservas: solo desactivar para conservar historial
                Paquete::setDisponible($id, 0);
                $_SESSION['success'] = "El paquete ha sido desactivado. Se conserva su historial de reservas.";
            } else {
                // Sin reservas: desactivar (soft delete preferido)
                Paquete::setDisponible($id, 0);
                $_SESSION['success'] = "El paquete ha sido desactivado con éxito.";
            }
        } catch (Exception $e) {
            error_log("PaqueteController error: " . $e->getMessage());
            $_SESSION['error'] = "Error al procesar la eliminación.";
        }

        header('Location: ' . BASE_URL . '/admin/paquetes');
        exit();
    }

    /**
     * Alterna el estado disponible/inactivo de un paquete.
     *
     * @param string $id
     * @return void
     */
    public function toggleDisponible(string $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/paquetes');
            exit();
        }

        $id = (int)$id;
        if ($id <= 0) {
            $_SESSION['error'] = "ID de paquete no válido.";
            header('Location: ' . BASE_URL . '/admin/paquetes');
            exit();
        }

        try {
            $paquete = Paquete::findById($id);
            if (!$paquete) {
                $_SESSION['error'] = "El paquete solicitado no existe.";
                header('Location: ' . BASE_URL . '/admin/paquetes');
                exit();
            }

            $nuevoEstado = $paquete['disponible'] ? 0 : 1;
            Paquete::setDisponible($id, $nuevoEstado);

            if ($nuevoEstado) {
                $_SESSION['success'] = "El paquete ha sido activado y está visible en el catálogo.";
            } else {
                $_SESSION['success'] = "El paquete ha sido desactivado y ya no es visible en el catálogo.";
            }
        } catch (Exception $e) {
            error_log("PaqueteController error: " . $e->getMessage());
            $_SESSION['error'] = "Error al cambiar el estado del paquete.";
        }

        header('Location: ' . BASE_URL . '/admin/paquetes');
        exit();
    }

    /**
     * Normaliza los campos comerciales y listas del formulario de paquetes.
     */
    private function packageDataFromPost(): array
    {
        return [
            'id_categoria'      => isset($_POST['id_categoria']) ? (int)$_POST['id_categoria'] : 0,
            'id_ciudad'         => isset($_POST['id_ciudad']) ? (int)$_POST['id_ciudad'] : 0,
            'nombre'            => isset($_POST['nombre']) ? trim($_POST['nombre']) : '',
            'slug'              => isset($_POST['slug']) ? trim($_POST['slug']) : '',
            'descripcion_corta' => isset($_POST['descripcion_corta']) ? trim($_POST['descripcion_corta']) : '',
            'descripcion'       => isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '',
            'itinerario'        => isset($_POST['itinerario']) ? trim($_POST['itinerario']) : '[]',
            'precio_base'       => isset($_POST['precio_base']) ? (float)$_POST['precio_base'] : 0.0,
            'precio_anterior'   => isset($_POST['precio_anterior']) ? trim($_POST['precio_anterior']) : '',
            'duracion_dias'     => isset($_POST['duracion_dias']) ? (int)$_POST['duracion_dias'] : 0,
            'duracion_noches'   => isset($_POST['duracion_noches']) ? (int)$_POST['duracion_noches'] : 0,
            'cupo_maximo'       => isset($_POST['cupo_maximo']) ? trim($_POST['cupo_maximo']) : '',
            'dificultad'        => isset($_POST['dificultad']) ? trim($_POST['dificultad']) : '',
            'hotel'             => isset($_POST['hotel']) ? trim($_POST['hotel']) : '',
            'habitacion'        => isset($_POST['habitacion']) ? trim($_POST['habitacion']) : '',
            'comidas'           => isset($_POST['comidas']) ? trim($_POST['comidas']) : '',
            'vuelos'            => isset($_POST['vuelos']) ? trim($_POST['vuelos']) : '',
            'movilidad'         => isset($_POST['movilidad']) ? trim($_POST['movilidad']) : '',
            'guia'              => isset($_POST['guia']) ? trim($_POST['guia']) : '',
            'destacado'         => isset($_POST['destacado']) ? 1 : 0,
            'disponible'        => isset($_POST['borrador']) ? 0 : (isset($_POST['disponible']) ? (int)$_POST['disponible'] : 1),
            'incluye_items'     => $_POST['incluye_items'] ?? '',
            'no_incluye_items'  => $_POST['no_incluye_items'] ?? '',
            'politicas_items'   => $_POST['politicas_items'] ?? '',
            'documentos_items'  => $_POST['documentos_items'] ?? '',
            'tags_items'        => $_POST['tags_items'] ?? '',
        ];
    }

    /**
     * Mantiene los datos enviados por POST en variables temporales para volver a llenar el formulario ante un error.
     */
    private function conservarPostData(): void
    {
        foreach ($this->postFields() as $field => $default) {
            $_POST[$field] = $_POST[$field] ?? $default;
        }
    }

    /**
     * Comprueba que la URL principal sea remota, accesible y de tipo imagen.
     */
    private function validarImagenPrincipal(string $url_imagen): bool
    {
        if ($url_imagen === '') {
            return true;
        }

        if (!ImageUrlService::isValid($url_imagen)) {
            $_SESSION['error'] = "La URL de imagen no responde correctamente o no es una imagen válida. Prueba con una URL directa de Unsplash, Pexels o Pixabay.";
            return false;
        }

        return true;
    }

    /**
     * Limpia los datos de POST temporales una vez que se procesó exitosamente.
     */
    private function limpiarPostData(): void
    {
        foreach (array_keys($this->postFields()) as $field) {
            unset($_POST[$field]);
        }
    }

    // Funcion: Define los campos permitidos que llegan desde el formulario.
    private function postFields(): array
    {
        return [
            'nombre' => '', 'slug' => '', 'id_categoria' => '', 'id_ciudad' => '',
            'duracion_dias' => '', 'duracion_noches' => '', 'precio_base' => '', 'precio_anterior' => '',
            'descripcion_corta' => '', 'descripcion' => '', 'itinerario' => '[]', 'disponible' => '1',
            'cupo_maximo' => '', 'dificultad' => '', 'hotel' => '', 'habitacion' => '', 'comidas' => '',
            'vuelos' => '', 'movilidad' => '', 'guia' => '', 'destacado' => '', 'url_imagen' => '',
            'incluye_items' => '', 'no_incluye_items' => '', 'politicas_items' => '', 'documentos_items' => '', 'tags_items' => '',
        ];
    }
}

<?php

declare(strict_types=1);

// Funcion del archivo: Registra rutas y despacha cada URL al controlador correspondiente.
namespace App\Config;

/**
 * Clase Router
 * Gestiona el registro de rutas y el despacho al controlador y método correspondientes.
 */
class Router
{
    private array $routes = [];

    /**
     * Registra una nueva ruta.
     *
     * @param string $route Ruta URL (ej. 'paquetes')
     * @param string $handler Formato "Controlador@metodo" (ej. "CatalogController@index")
     * @param string $method Método HTTP (ej. 'GET', 'POST')
     * @return void
     */
    public function add(string $route, string $handler, string $method = 'GET'): void
    {
        $this->routes[strtoupper($method)][trim($route, '/')] = $handler;
    }

    /**
     * Resuelve la petición actual y ejecuta el controlador adecuado.
     *
     * @param string $uri La URI de la petición actual ($_SERVER['REQUEST_URI'])
     * @return void
     */
    public function dispatch(string $uri): void
    {
        // Limpiamos los parámetros GET del path
        $uriPath = parse_url($uri, PHP_URL_PATH);

        // Detectar y quitar la ruta base de subdirectorios en XAMPP
        // La lógica: la diferencia entre SCRIPT_FILENAME (ruta real del script)
        // y DOCUMENT_ROOT nos da el prefijo URL del proyecto.
        // Ese mismo prefijo está al inicio de REQUEST_URI y debe ser removido.
        //
        // Nota: cuando se accede vía el .htaccess raíz del proyecto,
        // SCRIPT_FILENAME contiene /public/index.php pero REQUEST_URI no trae /public.
        // Por eso quitamos el segmento /public del prefijo calculado.
        $scriptRealDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME'] ?? ''));
        $docRoot = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/'));
        if ($docRoot !== '' && strpos($scriptRealDir, $docRoot) === 0) {
            $projectPrefix = substr($scriptRealDir, strlen($docRoot));
            // Si el prefijo termina en /public, quitarlo para que coincida con REQUEST_URI
            if (substr($projectPrefix, -7) === '/public') {
                $projectPrefix = substr($projectPrefix, 0, -7);
            }
            if ($projectPrefix !== '' && strpos($uriPath, $projectPrefix) === 0) {
                $uriPath = substr($uriPath, strlen($projectPrefix));
            }
        }

        // Acceso directo a la carpeta /public/ (p.ej. http://host/proyecto/public/):
        // SCRIPT_FILENAME incluye /public pero REQUEST_URI también, y al limpiar
        // el prefijo del proyecto se elimina /public del prefijo, no de la URI.
        // Recortamos el segmento /public inicial para que la home y las rutas
        // resuelvan correctamente en ambos modos de acceso.
        if (strpos($uriPath, '/public') === 0) {
            $uriPath = substr($uriPath, strlen('/public'));
        }

        $route = trim($uriPath, '/');
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $routePattern => $handler) {
                // Escapar las barras y convertir los marcadores {param} en grupos de captura
                $escapedPattern = str_replace('/', '\/', $routePattern);
                $regexPattern = '#^' . preg_replace('/\{[a-zA-Z0-9_]+\}/', '([^/]+)', $escapedPattern) . '$#';

                if (preg_match($regexPattern, $route, $matches)) {
                    array_shift($matches); // Remover la coincidencia completa de la expresión

                    [$controllerName, $actionName] = explode('@', $handler);
                    $controllerClass = "App\\Controllers\\" . $controllerName;

                    if (class_exists($controllerClass)) {
                        $controller = new $controllerClass();
                        if (method_exists($controller, $actionName)) {
                            // Ejecuta el método pasándole los argumentos capturados de la URL
                            call_user_func_array([$controller, $actionName], $matches);
                            return;
                        }
                    }
                }
            }
        }

        // Si la ruta no se encuentra, retornamos 404
        http_response_code(404);
        $baseViewsDir = dirname(__DIR__) . '/Views/';
        require_once $baseViewsDir . 'errors/404.php';
    }
}

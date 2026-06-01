<?php
/**
 * Core/App.php
 * Enrutador MVC principal.
 * Mapea URLs limpias a Controlador::metodo() con soporte de parametros dinamicos.
 */
class App {

    /**
     * Tabla de rutas estaticas: URL => [Controlador, metodo]
     */
    private array $rutas = [
        ''           => ['HomeController',      'index'],
        'inicio'     => ['HomeController',      'index'],
        'consulta'   => ['ExpedienteController','buscar'],
        'registro'   => ['ExpedienteController','registrar'],
        'auditoria'  => ['AuditoriaController', 'index'],
        'usuarios'   => ['UsuarioController',   'index'],
        'sedes'      => ['SedeController',      'index'],
        'ubicaciones'=> ['UbicacionController', 'index'],
        'respaldo'   => ['RespaldoController',  'index'],
        'respaldo/descargar'   => ['RespaldoController',  'descargar'],
        'verificar_expediente' => ['ExpedienteController', 'verificar'],
        'login'      => ['AuthController',      'login'],
        'salir'      => ['AuthController',      'logout'],
        'logout'     => ['AuthController',      'logout'],
        'obtener_ubicacion'    => ['UbicacionController', 'obtener'],
        'delitos'              => ['DelitoController',    'index'],
        'delitos/guardar'      => ['DelitoController',    'guardar'],
        'delitos/editar'       => ['DelitoController',    'editar'],
        'delitos/eliminar'     => ['DelitoController',    'eliminar'],
        'tribunales'           => ['TribunalController',  'index'],
        'tribunales/guardar'   => ['TribunalController',  'guardar'],
        'tribunales/editar'    => ['TribunalController',  'editar'],
        'tribunales/eliminar'  => ['TribunalController',  'eliminar'],
    ];

    /**
     * Tabla de rutas dinamicas con patrones regex.
     * Cada entrada: [patron, Controlador, metodo, nombre_param]
     */
    private array $rutasDinamicas = [
        ['#^expediente/(\d+)$#', 'ExpedienteController', 'historial', 'id'],
        ['#^editar/(\d+)$#',     'ExpedienteController', 'editar',    'id'],
        ['#^imprimir/(\d+)$#',   'ExpedienteController', 'imprimir',  'id'],
        ['#^usuarios/editar/(\d+)$#', 'UsuarioController', 'editar',  'id'],
        ['#^sedes/editar/(\d+)$#',    'SedeController',   'editar',   'id'],
    ];

    /**
     * Despacha la peticion al controlador y metodo correspondientes.
     */
    public function run(): void {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = ltrim(rtrim($uri, '/'), '/');

        // Normalizar index.php a ruta vacia para que coincida con el HomeController
        if ($uri === 'index.php') {
            $uri = '';
        }

        // 1. Intentar rutas dinamicas primero
        foreach ($this->rutasDinamicas as [$patron, $controlador, $metodo, $param]) {
            if (preg_match($patron, $uri, $matches)) {
                $_GET[$param] = $matches[1];
                $this->despachar($controlador, $metodo);
                return;
            }
        }

        // 2. Intentar rutas estaticas
        if (array_key_exists($uri, $this->rutas)) {
            [$controlador, $metodo] = $this->rutas[$uri];
            $this->despachar($controlador, $metodo);
            return;
        }

        // 3. Ruta no encontrada en MVC → Fallback al sistema legacy
        // Lanzamos una excepcion para que index.php la capture y ejecute el fallback
        throw new \RuntimeException("Ruta no encontrada: {$uri}", 404);
    }

    /**
     * Instancia el controlador y llama al metodo.
     */
    private function despachar(string $controlador, string $metodo): void {
        $archivoControlador = __DIR__ . '/../Controllers/' . $controlador . '.php';

        if (!file_exists($archivoControlador)) {
            throw new \RuntimeException("Controlador no encontrado: {$controlador}");
        }

        require_once $archivoControlador;

        if (!class_exists($controlador)) {
            throw new \RuntimeException("Clase no encontrada: {$controlador}");
        }

        $obj = new $controlador();

        if (!method_exists($obj, $metodo)) {
            throw new \RuntimeException("Metodo no encontrado: {$controlador}::{$metodo}");
        }

        $obj->$metodo();
    }
}

<?php

require_once __DIR__ . '/vendor/autoload.php';
// require_once 'vendor/autoload.php';

// 2. Inicializar DotEnv y cargar el archivo .env
try {
    // Busca el archivo .env en la misma carpeta donde está este index.php (__DIR__)
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} catch (Exception $e) {
    // Si olvidaron crear el archivo .env, el sistema se detiene por seguridad
    die("Error crítico: No se encontró el archivo de configuración de entorno (.env).");
}

define('RAIZ', str_replace('\\', '/', __DIR__) . '/');

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', isset($_SERVER['HTTPS']) ? 'Strict' : 'Lax');
session_start();

$sessionTimeout = (int)($_ENV['SESSION_TIMEOUT'] ?? 28800);

if (!empty($_SESSION['id'])) {
    if (isset($_SESSION['ultimo_acceso']) && (time() - $_SESSION['ultimo_acceso']) > $sessionTimeout) {
        $_SESSION = [];
        session_destroy();
        header('Location: ?p=login&expirado=1');
        exit;
    }
    $_SESSION['ultimo_acceso'] = time();
}

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://fonts.googleapis.com; font-src https://cdnjs.cloudflare.com https://fonts.gstatic.com; img-src 'self' data: https://ui-avatars.com; connect-src 'self' https://cdn.jsdelivr.net");

$paginasPermitidas = [
    'login', 'inicio', 'entrenador', 'drills', 'atleta', 'eventos', 'marcas',
    'periodizacion', 'antropometria', 'representante', 'calendario', 'salir',
    'lesion','categorias','bitacora','usuarios','roles','mantenimiento' ,'cargaBienestar'
];

$pagina = "inicio";
if (!empty($_GET['p']) && in_array($_GET['p'], $paginasPermitidas, true)) {
    $pagina = $_GET['p'];
}

$rutasPublicas = ['login'];

if (!in_array($pagina, $rutasPublicas, true) && empty($_SESSION['id'])) {
    header('Location: ?p=login');
    exit;
}

/*if (!in_array($pagina, $rutasPublicas, true) && !empty($_SESSION['id'])) {
    if (!\GrupoProyecto\SisBiomec\seguridad\Autorizacion::tieneAcceso($pagina)) {
        $_SESSION['error_403'] = true;
        header('Location: ?p=inicio');
        exit;
    }
}*/

$archivoControlador = "src/controlador/" . $pagina . "Controlador.php";

if (is_file($archivoControlador)) {
    require_once($archivoControlador);

    $claseControlador = "GrupoProyecto\\SisBiomec\\controlador\\" . ucfirst($pagina) . "Controlador";

    if (class_exists($claseControlador, false)) {
       $dependencias = [
    'GrupoProyecto\\SisBiomec\\controlador\\EntrenadorControlador' => [
        new \GrupoProyecto\SisBiomec\modelo\Entrenador()
    ],
];

    $params = $dependencias[$claseControlador] ?? [];
    $controlador = new $claseControlador(...$params);
    $controlador->handle();
    }
} else {
    header("HTTP/1.0 404 Not Found");
    echo "<h1>404 - La sección que buscas no existe</h1>";
}

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

$pagina = "inicio"; 
if (!empty($_GET['p'])) {
    $pagina = $_GET['p'];
}

$archivoControlador = "src/controlador/" . $pagina . "Controlador.php";

if (is_file($archivoControlador)) {
    require_once($archivoControlador);

    $claseControlador = "GrupoProyecto\\SisBiomec\\controlador\\" . ucfirst($pagina) . "Controlador";

    if (class_exists($claseControlador, false)) {
       $dependencias = [
    'GrupoProyecto\\SisBiomec\\controlador\\AtletaControlador' => [
        new \GrupoProyecto\SisBiomec\modelo\Atleta()
    ],
 
    'GrupoProyecto\\SisBiomec\\controlador\\EntrenadorControlador' => [
        new \GrupoProyecto\SisBiomec\modelo\Entrenador()
    ],
];
    }
} else {
    header("HTTP/1.0 404 Not Found");
    echo "<h1>404 - La sección que buscas no existe</h1>";
}

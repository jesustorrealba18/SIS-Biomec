<?php

require_once 'vendor/autoload.php';

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

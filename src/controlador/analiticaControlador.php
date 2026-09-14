<?php

if (empty($_SESSION['id'])) {
    header('Location: ?p=login');
    exit;
}

use GrupoProyecto\SisBiomec\modelo\Analitica;

$objAnalitica = new Analitica();
$datosAnalitica = $objAnalitica->obtenerDashboard();

require_once 'vista/analitica.php';

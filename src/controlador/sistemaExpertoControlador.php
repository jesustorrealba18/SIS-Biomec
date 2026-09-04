<?php
ob_start();

use GrupoProyecto\SisBiomec\modelo\Atleta;

/*
 * Sistema Experto (Componente Inteligente)
 * Vista de demostracion: combina datos reales de atletas (solo lectura)
 * con recomendaciones mock generadas por el arbol de decision.
 */

$objAtleta = new Atleta();
$atletas = $objAtleta->listar();

require_once 'vista/sistemaExperto.php';

<?php
// controlador/inicioControlador.php
session_start(); //

// Si el usuario NO ha iniciado sesión (la variable 'id' está vacía), 
// lo mandamos al login inmediatamente.[cite: 3]
if (empty($_SESSION['id'])) {
    header('Location: ?p=login');
    exit;
}
use GrupoProyecto\SisBiomec\modelo\InicioModelo;

$objInicio = new InicioModelo();
$titulo_pagina = "Panel de Inicio";

require_once 'vista/inicio.php';
?>
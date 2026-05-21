<?php
// src/controlador/representanteControlador.php

// 1. Filtro de Reglas Básicas (El pivote ataja a los intrusos)
session_start();
if (empty($_SESSION['id'])) { 
    header('Location: ?p=login'); 
    exit; 
}

// Traemos el Modelo que sí es una clase y hace el trabajo pesado
use GrupoProyecto\SisBiomec\modelo\Representante;
use GrupoProyecto\SisBiomec\modelo\Atleta;


if ($_SERVER['REQUEST_METHOD'] === 'GET') {

// ACCIÓN: Enviar la lista resumida de atletas para el buscador predictivo
    if (isset($_GET['accion']) && $_GET['accion'] === 'listarAtletasSelect') {
        header('Content-Type: application/json');
        // Instancias el modelo de Atleta (asegúrate de hacer el 'use' arriba)
        $objAtleta = new Atleta();
        
        // Asumiendo que tu líder tiene una función para listar todos los atletas activos
        // (Si la función se llama diferente en Atleta.php, cámbiala aquí)
        echo json_encode($objAtleta->listarAtletas()); 
        exit;
    }

require_once 'vista/marcas.php';
}
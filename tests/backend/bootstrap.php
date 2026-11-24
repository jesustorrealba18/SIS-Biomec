<?php
// tests/backend/bootstrap.php

// 1. Cargar el autoload de Composer
require_once __DIR__ . '/../../vendor/autoload.php';

// 2. Definir las credenciales con los MISMOS nombres que usa tu .env y tu Conexion.php
$dbConfig = [
    'DB_HOST'          => 'localhost',
    'DB_USER'          => 'root',
    'DB_PASS'          => '', // En XAMPP usualmente está vacío
    'DB_NAME_NATACION' => 'sis_natacion',
    'DB_NAME_SEGURIDAD'=> 'sis_seguridad'
];

foreach ($dbConfig as $key => $value) {
    // Llenamos $_ENV (por si Conexion usa $_ENV)
    $_ENV[$key] = $value;
    
    // Llenamos $_SERVER 
    $_SERVER[$key] = $value;
    
    // Llenamos getenv() (por si Conexion usa getenv())
    putenv("{$key}={$value}");
}
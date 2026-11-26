<?php
/* 
// 1. Cargamos los Stubs PRIMERO (antes de Composer)
// Esto evita que PHP cargue las clases reales que intentan conectar a MySQL
require_once __DIR__ . '/Stubs/ConexionStub.php';
require_once __DIR__ . '/Stubs/BitacoraStub.php';

// 2. Ahora sí, cargamos el autoload de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// 3. Cargamos las variables de entorno (.env)
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

// 4. Autoload manual para tus clases
spl_autoload_register(function ($class) {
    $prefix = 'GrupoProyecto\\SisBiomec\\';
    if (strpos($class, $prefix) !== 0) {
        return;
    }
    
    $rel = substr($class, strlen($prefix));
    $rel = str_replace('\\', '/', $rel);

    // Rutas basadas en tu estructura de carpetas (src/modelo, etc.)
    $candidatos = [
        __DIR__ . '/../src/modelo/' . $rel . '.php',
        __DIR__ . '/../src/seguridad/' . $rel . '.php',
        __DIR__ . '/../modelo/' . $rel . '.php',       // Por si acaso
        __DIR__ . '/../seguridad/' . $rel . '.php',    // Por si acaso
        __DIR__ . '/../' . $rel . '.php',
    ];

    foreach ($candidatos as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
}); */

// tests/bootstrap.php

// 1. Definir la constante RAIZ de forma segura para todo el proyecto
if (!defined('RAIZ')) {
    define('RAIZ', str_replace('\\', '/', dirname(__DIR__)) . '/');
}

// 2. Detectar si se están ejecutando pruebas de Integración (requieren MySQL real)
$argumentosCli = implode(' ', $_SERVER['argv'] ?? []);
$esPruebaIntegracion = (getenv('USE_REAL_DB') === '1')
    || (stripos($argumentosCli, 'Integration') !== false);

// 3. Cargar Stubs SOLO cuando NO sean pruebas de integración
if (!$esPruebaIntegracion) {
    if (file_exists(__DIR__ . '/Stubs/ConexionStub.php')) {
        require_once __DIR__ . '/Stubs/ConexionStub.php';
    }
    if (file_exists(__DIR__ . '/Stubs/BitacoraStub.php')) {
        require_once __DIR__ . '/Stubs/BitacoraStub.php';
    }
}

// 4. Cargar el autoloader de Composer
require_once RAIZ . 'vendor/autoload.php';

// 5. Cargar variables de entorno (.env)
if (file_exists(RAIZ . '.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(RAIZ);
    $dotenv->safeLoad();
}

// 6. Autoload manual de respaldo para clases no mapeadas en composer.json
spl_autoload_register(function ($class) {
    $prefix = 'GrupoProyecto\\SisBiomec\\';
    if (strpos($class, $prefix) !== 0) {
        return;
    }

    $rel = str_replace('\\', '/', substr($class, strlen($prefix)));
    $candidatos = [
        RAIZ . 'src/modelo/' . $rel . '.php',
        RAIZ . 'src/seguridad/' . $rel . '.php',
        RAIZ . 'modelo/' . $rel . '.php',
        RAIZ . 'seguridad/' . $rel . '.php',
        RAIZ . $rel . '.php',
    ];

    foreach ($candidatos as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
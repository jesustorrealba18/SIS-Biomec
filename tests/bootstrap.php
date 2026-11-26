<?php
// tests/bootstrap.php

// 1. Definir la constante RAIZ
if (!defined('RAIZ')) {
    define('RAIZ', str_replace('\\', '/', dirname(__DIR__)) . '/');
}

// 2. Detectar si se están ejecutando pruebas de Integración
$argumentosCli = implode(' ', $_SERVER['argv'] ?? []);
$esPruebaIntegracion = (getenv('USE_REAL_DB') === '1')
    || (stripos($argumentosCli, 'Integration') !== false);

// 3. Cargar Stubs / Interceptar Bitácora
if (!$esPruebaIntegracion) {
    // Para pruebas Unitarias, usamos los stubs normales
    if (file_exists(__DIR__ . '/Stubs/ConexionStub.php')) {
        require_once __DIR__ . '/Stubs/ConexionStub.php';
    }
    if (file_exists(__DIR__ . '/Stubs/BitacoraStub.php')) {
        require_once __DIR__ . '/Stubs/BitacoraStub.php';
    }
} else {
    // EN INTEGRACIÓN: Hacemos que Bitacora sea "tonta" para que no falle por la BD de seguridad
    if (!class_exists('GrupoProyecto\SisBiomec\seguridad\Bitacora', false)) {
        eval('
            namespace GrupoProyecto\SisBiomec\seguridad;
            class Bitacora {
                public static function registrar($id_usuario, $tabla, $accion, $id_registro, $campo = null, $antes = null, $despues = null) {
                    return true; // Simulamos que guardó el log sin tocar la BD
                }
            }
        ');
    }
}

// 4. Cargar autoloader
require_once RAIZ . 'vendor/autoload.php';

// 5. Cargar variables de entorno
$envFile = $esPruebaIntegracion ? '.env.test' : '.env';
if (file_exists(RAIZ . $envFile)) {
    $dotenv = Dotenv\Dotenv::createImmutable(RAIZ, $envFile);
    $dotenv->safeLoad();
}

// 6. Cargar clases base de Integración
if ($esPruebaIntegracion) {
    $bases = [
        __DIR__ . '/Integration/sesiones/BaseDatabaseTestCase.php',
        __DIR__ . '/Integration/grupo/BaseGrupoTestCase.php',
        __DIR__ . '/Integration/asignacion/BaseAsignacionTestCase.php',
        __DIR__ . '/Integration/drills/BaseDrillsTestCase.php',
        __DIR__ . '/Integration/entrenador/BaseEntrenadorTestCase.php',
        __DIR__ . '/Integration/horario/BaseHorarioTestCase.php',
    ];
    foreach ($bases as $base) {
        if (file_exists($base)) {
            require_once $base;
        }
    }
}

// 7. Autoload manual de respaldo
spl_autoload_register(function ($class) {
    $prefix = 'GrupoProyecto\\SisBiomec\\';
    if (strpos($class, $prefix) !== 0) return;

    $rel = str_replace('\\', '/', substr($class, strlen($prefix)));
    $candidatos = [
        RAIZ . 'src/modelo/' . $rel . '.php',
        RAIZ . 'src/seguridad/' . $rel . '.php',
        RAIZ . 'src/controlador/' . $rel . '.php',
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
<?php
// api.php ( Enrutador Frontal)

// 1. Cabeceras de seguridad estrictas
header('Content-Type: application/json; charset=utf-8');
// CSP corregido
header("Content-Security-Policy: script-src 'self' https://cdn.jsdelivr.net;");

// 2. Autocargador
spl_autoload_register(function ($clase) {
    // Busca en las carpetas
    $directorios = ['modelo/', 'controlador/', 'config/', 'utils/'];
    foreach ($directorios as $dir) {
        $archivo = $dir . $clase . '.php';
        if (file_exists($archivo)) { 
            require_once $archivo; 
            return; 
        }
    }
});

// Carga manual de utilidades si el autoloader no aplica
require_once 'utils/Respuesta.php'; 
require_once 'modelo/conexion.php'; // Usamos la conexión base 

$db = null;

try {
    // 3. Conexión a la Base de Datos (Inyección de Dependencias)
    $conexionObj = new Conexion();
    $db = $conexionObj->getConex1(); // Extraemos el objeto PDO puro

    // 4. Enrutamiento y Sanitización
    // Solo permitimos letras para evitar inyección de rutas
    $c = preg_replace('/[^a-zA-Z]/', '', $_GET['c'] ?? '');
    
    if (empty($c)) {
        Respuesta::enviar(400, "Controlador no especificado.");
    }
    
    $controllerName = $c . 'Controlador';
    $accion = $_GET['accion'] ?? 'listar';

    // 5. Seguridad: Lista Blanca
    // Si tus compañeros adaptan sus módulos, los agregas aquí. 
    // Por ahora, blindamos tu módulo de Representante.
    $permitidos = ['RepresentanteController'];
    if (!in_array($controllerName, $permitidos)) {
        Respuesta::enviar(403, "Módulo no autorizado o bloqueado por seguridad.");
    }

    // 6. Ejecución del Patrón Front Controller
    if (class_exists($controllerName)) {
        // Le pasamos la conexión al controlador al nacer
        $controller = new $controllerName($db);
        $controller->ejecutar($accion);
    } else {
        Respuesta::enviar(404, "El recurso solicitado no existe.");
    }

} catch (PDOException $e) {
    // Captura errores de SQL silenciosamente para no mostrar detalles de la BD al frontend
    // Logger::log($db, $e); // Opcional: si tienes tu clase Logger activa
    Respuesta::enviar(500, "Error de transacción en la base de datos.");
} catch (Exception $e) {
    Respuesta::enviar(500, "Error técnico interno.");
} finally {
    // 7. Destrucción de Memoria (<<destroy>> en tu diagrama UML)
    // Esto garantiza que la universidad vea que cierras los hilos de conexión
    $db = null; 
    $conexionObj = null;
}
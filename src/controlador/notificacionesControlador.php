<?php
// Controlador: notificacionesControlador.php
use GrupoProyecto\SisBiomec\modelo\Notificacion;

// 1. Candado de seguridad
if (empty($_SESSION['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit;
}

$accion = $_GET['accion'] ?? '';
$id_usuario_actual = $_SESSION['id'];

// ==========================================================
// PIVOTE: Listar Notificaciones para la Campana (AJAX)
// ==========================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $accion === 'listar') {
    header('Content-Type: application/json');
    
    // 2. Pedimos los datos al modelo (Delegación pura)
    $notificaciones = Notificacion::listarPorUsuario($id_usuario_actual, 10);
    $no_leidas = Notificacion::contarNoLeidas($id_usuario_actual);

    // 3. Pequeño formateo de presentación (responsabilidad del controlador/vista)
    foreach ($notificaciones as &$notif) {
        $notif['tiempo_relativo'] = date("d/m/Y h:i A", strtotime($notif['fecha']));
    }

    // 4. Respondemos al Frontend
    echo json_encode([
        'status' => 'success', 
        'data' => $notificaciones,
        'no_leidas' => $no_leidas
    ]);
    exit;
}


// ==========================================================
// PIVOTE AJAX: Marcar Notificación como Leída
// ==========================================================


        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'marcar_leida') {
            try {
                $input = json_decode(file_get_contents('php://input'), true);
                
                // 1. Instanciamos el modelo y lo hidratamos de forma segura
                $notificacion = new Notificacion();
                $notificacion->setDatos([
                    'id_notificacion' => $input['id'] ?? '', // Puede venir basura (letras)
                    'id_usuario'      => $_SESSION['id'] ?? ''
                ]);
    
                // 2. Ejecutamos el método que contiene el ValidacionesTrait
                if ($notificacion->marcarcomoLeida()) {
                    enviarJson(['status' => 'success']);
                } else {
                    // 3. ¡LA MAGIA! Extraemos el error exacto y se lo devolvemos al frontend
                    $errores = $notificacion->obtenerErrores();
                    $mensajeError = !empty($errores) ? reset($errores) : 'Error de integridad de datos.';
                    
                    enviarJson(['status' => 'error', 'message' => $mensajeError]);
                }
                
            } catch (\Throwable $th) {
                enviarJson(['status' => 'error', 'message' => 'Fallo interno: ' . $th->getMessage()]);
            }
        }

/*         if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'marcar_leida') {
            try {
                $input = json_decode(file_get_contents('php://input'), true);
                
                $id_notificacion = isset($input['id']) ? (int)$input['id'] : 0;
                $id_usuario_actual = (int)$_SESSION['id']; // Aseguramos que sea entero
    
                if ($id_notificacion > 0) {
                    $exito = Notificacion::marcarComoLeida($id_notificacion, $id_usuario_actual);
                    
                    if ($exito) {
                        enviarJson(['status' => 'success']);
                    } else {
                        enviarJson(['status' => 'error', 'message' => 'No se pudo actualizar en BD']);
                    }
                }
                
                enviarJson(['status' => 'error', 'message' => 'ID inválido']);
                
            } catch (\Throwable $th) {
                
                enviarJson(['status' => 'error', 'message' => 'Fallo interno: ' . $th->getMessage()]);
            }
        } */


function enviarJson($datos) {

if (ob_get_length()) {
        ob_clean();
    }

    header('Content-Type: application/json');
    echo json_encode($datos);
    exit; // ← crucial para que no se siga ejecutando HTML
}

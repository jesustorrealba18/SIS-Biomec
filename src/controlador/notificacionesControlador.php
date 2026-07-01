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
                // 1. Leemos el JSON entrante
                $input = json_decode(file_get_contents('php://input'), true);
                
                // 2. FORZAMOS el casteo a entero (INT) para evitar TypeError
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
                // Si ocurre CUALQUIER error fatal en PHP, evitamos que salga la pantalla HTML 
                // y devolvemos el error exacto en formato JSON para poder leerlo.
                enviarJson(['status' => 'error', 'message' => 'Fallo interno: ' . $th->getMessage()]);
            }
        }


        function enviarJson($datos) {
    header('Content-Type: application/json');
    echo json_encode($datos);
    exit; // ← crucial para que no se siga ejecutando HTML
}

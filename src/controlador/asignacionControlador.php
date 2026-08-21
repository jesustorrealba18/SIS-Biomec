<?php

if (empty($_SESSION['id'])) { 
    header('Location: ?p=login'); 
    exit; 
}

use GrupoProyecto\SisBiomec\modelo\Asignacion;
use GrupoProyecto\SisBiomec\seguridad\Autorizacion;

$objAsignacion = new Asignacion();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    if (isset($_POST['accion'])) {
        if ($_POST['accion'] === 'eliminar') {
            Autorizacion::exigir('asignacion', 'gestionar');
            $id = isset($_POST['id_asignacion']) ? (int)$_POST['id_asignacion'] : 0;
            if ($objAsignacion->desactivarAsignacion($id)) { 
                echo json_encode(['status' => 'success', 'message' => 'Asignación desactivada correctamente.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No se pudo desactivar la asignación.']);
            }
            exit;
        }
        
        if ($_POST['accion'] === 'eliminarFisico') {
            Autorizacion::exigir('asignacion', 'gestionar');
            $id = isset($_POST['id_asignacion']) ? (int)$_POST['id_asignacion'] : 0;
            if ($objAsignacion->eliminarAsignacion($id)) { 
                echo json_encode(['status' => 'success', 'message' => 'Asignación eliminada correctamente.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No se pudo eliminar la asignación.']);
            }
            exit;
        }
        
        if ($_POST['accion'] === 'reactivar') {
            Autorizacion::exigir('asignacion', 'gestionar');
            $id = isset($_POST['id_asignacion']) ? (int)$_POST['id_asignacion'] : 0;
            if ($objAsignacion->reactivarAsignacion($id)) { 
                echo json_encode(['status' => 'success', 'message' => 'Asignación reactivada correctamente.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No se pudo reactivar la asignación.']);
            }
            exit;
        }

        if ($_POST['accion'] === 'completar') {
            Autorizacion::exigir('asignacion', 'gestionar');
            $id = isset($_POST['id_asignacion']) ? (int)$_POST['id_asignacion'] : 0;
            
            if ($objAsignacion->completarAsignacion($id)) {
                $asignacionCompleta = $objAsignacion->obtenerAsignacionPorId($id);
                if ($asignacionCompleta) {
                    $objAsignacion->notificarFinAsignacion($asignacionCompleta);
                }
                
                echo json_encode(['status' => 'success', 'message' => 'Asignación completada.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No se pudo completar la asignación.']);
            }
            exit;
        }

        if ($_POST['accion'] === 'cambiarEstado') {
            Autorizacion::exigir('asignacion', 'gestionar');
            $id = isset($_POST['id_asignacion']) ? (int)$_POST['id_asignacion'] : 0;
            $estado = isset($_POST['estado']) ? $_POST['estado'] : '';
            if ($objAsignacion->cambiarEstadoAsignacion($id, $estado)) { 
                echo json_encode(['status' => 'success', 'message' => 'Estado actualizado correctamente.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar el estado.']);
            }
            exit;
        }
    }

    Autorizacion::exigir('asignacion', 'gestionar');
    
    $_POST['activa'] = isset($_POST['activa']) ? 1 : 0;

    if (isset($_POST['fecha_vigente_inicio']) && !isset($_POST['fecha_vigencia_inicio'])) {
        $_POST['fecha_vigencia_inicio'] = $_POST['fecha_vigente_inicio'];
    }
    if (isset($_POST['fecha_vigente_fin']) && !isset($_POST['fecha_vigencia_fin'])) {
        $_POST['fecha_vigencia_fin'] = $_POST['fecha_vigente_fin'];
    }

    $camposRequeridos = ['id_carril', 'id_bloque_horario', 'id_grupo', 'fecha_vigencia_inicio'];
    foreach ($camposRequeridos as $campo) {
        if (!isset($_POST[$campo]) || empty($_POST[$campo])) {
            echo json_encode([
                'status' => 'warning', 
                'errores' => [$campo => "El campo $campo es requerido"]
            ]);
            exit;
        }
    }
    
    $idOriginal = !empty($_POST['id_asignacion']) ? (int)$_POST['id_asignacion'] : null;
    
    error_log("Datos normalizados: " . print_r($_POST, true));
  
    $errores = $objAsignacion->validarDatos($_POST, $idOriginal);

    if (!empty($errores)) {
        echo json_encode(['status' => 'warning', 'errores' => $errores]);
        exit;
    }
    
    $objAsignacion->setDatos($_POST);
    $resultado = false; 

    try {
        if ($idOriginal) {
            error_log("Ejecutando EDITAR asignación ID: $idOriginal");
            $resultado = $objAsignacion->editarAsignacion();
            $idAsignacion = $idOriginal;
        } else {
            error_log("Ejecutando REGISTRAR nueva asignación");
            $resultado = $objAsignacion->registrarAsignacion();
            $idAsignacion = $objAsignacion->obtenerUltimoIdAsignacion();
            
            // Si falla obtener el ID, intentamos obtenerlo de otra forma
            if (!$idAsignacion) {
                $conex = $objAsignacion->getConex1();
                $sql = "SELECT MAX(id_asignacion) as id FROM asignacion_carril 
                        WHERE id_grupo = :id_grupo AND id_carril = :id_carril";
                $stmt = $conex->prepare($sql);
                $stmt->execute([
                    ':id_grupo' => $_POST['id_grupo'],
                    ':id_carril' => $_POST['id_carril']
                ]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $idAsignacion = $result['id'] ?? null;
            }
        }
        
        error_log("Resultado de la operación: " . ($resultado ? 'TRUE' : 'FALSE'));
        error_log("ID de asignación: " . ($idAsignacion ?? 'NULL'));

        if ($resultado) {
            // Notificación: Si la asignación está activa, notificar al grupo y entrenador
            if (isset($_POST['activa']) && $_POST['activa'] == 1 && $idAsignacion) {
                $asignacionCompleta = $objAsignacion->obtenerAsignacionPorId($idAsignacion);
                if ($asignacionCompleta) {
                    error_log("Enviando notificación de asignación para ID: " . $idAsignacion);
                    $objAsignacion->notificarAsignacionGrupo($asignacionCompleta);
                } else {
                    error_log("No se pudo obtener la asignación completa para ID: " . $idAsignacion);
                }
            }
            
            echo json_encode(['status' => 'success', 'message' => 'Asignación guardada con éxito.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error en base de datos al guardar.']);
        }
    } catch (Exception $e) {
        error_log("EXCEPCIÓN en asignacion: " . $e->getMessage());
        error_log($e->getTraceAsString());
        echo json_encode(['status' => 'error', 'message' => 'Error interno: ' . $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    if (isset($_GET['accion']) && $_GET['accion'] === 'listarAsignaciones') {
        header('Content-Type: application/json');
        $estadoInput = $_GET['estado'] ?? 'Activo';
        $estadoInt = ($estadoInput === 'Activo') ? 1 : 0;
        echo json_encode($objAsignacion->listarAsignaciones($estadoInt));
        exit;
    }

    if (isset($_GET['accion']) && $_GET['accion'] === 'listarCarriles') {
        header('Content-Type: application/json');
        echo json_encode($objAsignacion->listarCarrilesActivos());
        exit;
    }

    if (isset($_GET['accion']) && $_GET['accion'] === 'listarHorarios') {
        header('Content-Type: application/json');
        echo json_encode($objAsignacion->listarHorariosActivos());
        exit;
    }

    if (isset($_GET['accion']) && $_GET['accion'] === 'listarGruposParaSelect') {
        header('Content-Type: application/json');
        echo json_encode($objAsignacion->listarTodosLosGrupos());
        exit;
    }

    if (isset($_GET['accion']) && $_GET['accion'] === 'obtenerAsignacion' && isset($_GET['id'])) {
        header('Content-Type: application/json');
        echo json_encode($objAsignacion->obtenerAsignacionPorId((int)$_GET['id']));
        exit; 
    }

    if (isset($_GET['accion']) && $_GET['accion'] === 'obtenerDetalleCarril' && isset($_GET['id'])) {
        header('Content-Type: application/json');
        $carril = $objAsignacion->obtenerCarrilPorId((int)$_GET['id']);
        echo json_encode($carril);
        exit;
    }

    if (isset($_GET['accion']) && $_GET['accion'] === 'obtenerDetalleBloque' && isset($_GET['id'])) {
        header('Content-Type: application/json');
        $bloque = $objAsignacion->obtenerHorarioPorId((int)$_GET['id']);
        echo json_encode($bloque);
        exit;
    }

    if (isset($_GET['accion']) && $_GET['accion'] === 'obtenerDetalleGrupo' && isset($_GET['id'])) {
        header('Content-Type: application/json');
        $grupo = $objAsignacion->obtenerGrupoPorId((int)$_GET['id']);
        echo json_encode($grupo);
        exit;
    }

    if (isset($_GET['accion']) && $_GET['accion'] === 'listarCompletadas') {
        header('Content-Type: application/json');
        echo json_encode($objAsignacion->listarAsignacionesCompletadas());
        exit;
    }

    if (isset($_GET['accion']) && $_GET['accion'] === 'verificarVencidas') {
        header('Content-Type: application/json');
        $cantidad = $objAsignacion->verificarAsignacionesVencidas();
        echo json_encode([
            'status' => 'success', 
            'message' => "Se completaron $cantidad asignaciones vencidas."
        ]);
        exit;
    }

    if (isset($_GET['accion']) && $_GET['accion'] === 'carrilesDisponibles') {
        header('Content-Type: application/json');
        $dia = isset($_GET['dia']) ? $_GET['dia'] : null;
        $horaInicio = isset($_GET['hora_inicio']) ? $_GET['hora_inicio'] : null;
        $horaFin = isset($_GET['hora_fin']) ? $_GET['hora_fin'] : null;
        echo json_encode($objAsignacion->obtenerCarrilesDisponibles($dia, $horaInicio, $horaFin));
        exit;
    }

    require_once 'vista/asignacion.php';
}
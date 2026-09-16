<?php

use GrupoProyecto\SisBiomec\seguridad\Bitacora;
use GrupoProyecto\SisBiomec\seguridad\Autorizacion;
use GrupoProyecto\SisBiomec\modelo\Lesion;
use GrupoProyecto\SisBiomec\modelo\Atleta;
use GrupoProyecto\SisBiomec\modelo\Notificacion;


if (empty($_SESSION['id'])) { 
    header('Location: ?p=login'); 
    exit; 
}



$objLesion = new Lesion();
$id_usuario = $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = $_GET['accion'] ?? '';

    if ($accion === 'listarAtletasSelect') {
        header('Content-Type: application/json');
        echo json_encode((new Atleta())->listar());
        exit;
    }

    if ($accion === 'listarLesiones') {
        header('Content-Type: application/json');
        $estadoClinico = trim($_GET['estado'] ?? '');
        $id_atleta = (int)($_GET['id_atleta'] ?? 0);
        $tipo = trim($_GET['tipo'] ?? '');
        $zona = trim($_GET['zona'] ?? '');
        
        $modo = $_GET['modo'] ?? 'activos';
        $incluirInactivos = ($modo === 'papelera');
        
        $lesiones = $objLesion->listarLesiones($estadoClinico, $id_atleta, $tipo, $zona, $incluirInactivos);
        echo json_encode($lesiones);
        exit;
    }

    if ($accion === 'obtenerDetalleLesion') {
        header('Content-Type: application/json');
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            echo json_encode($objLesion->obtenerDetallePorId($id));
        } else {
            echo json_encode(['status' => 'error', 'message' => 'ID de lesión inválido']);
        }
        exit;
    }

    if ($accion === 'obtenerRiesgosActivos') {
    header('Content-Type: application/json');
    $riesgos = $objLesion->obtenerRiesgosActivos();
    echo json_encode($riesgos);
    exit;
    }

    // Cargar la vista HTML por defecto
    require_once 'vista/lesion.php';
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $accion = $_GET['accion'] ?? $_POST['accion'] ?? '';
    
    ob_start(); 

    if ($accion === 'registrar') {
        Autorizacion::exigir('lesiones', 'registrar');
        $res = $objLesion->registrarLesion($_POST);
        ob_end_clean();
        
        if ($res) {
            Bitacora::registrar($id_usuario, 'Lesiones', 'INSERT', null, 'Nueva lesión registrada', null, json_encode($_POST));
            Notificacion::NotificarLesiones('CREATE', $_POST, (int)$_POST['id_atleta'], $res['id_lesion']);
            echo json_encode(['status' => 'success', 'message' => 'Informe clínico registrado con éxito.']);
        } else {
            $err = $objLesion->obtenerErrores();
            echo json_encode(['status' => 'error', 'message' => reset($err) ?: 'Error al registrar la lesión.']);
        }
        exit;
    }

    if ($accion === 'actualizar') {
        Autorizacion::exigir('lesiones', 'editar');
        $id = (int)($_POST['id_lesion'] ?? 0);
        
        if ($id <= 0) { 
            ob_end_clean(); 
            echo json_encode(['status' => 'error', 'message' => 'ID inválido']); 
            exit; 
        }
        
        $res = $objLesion->actualizarLesion($_POST, $id);
        ob_end_clean();
        
        if ($res) {
            Bitacora::registrar($id_usuario, 'Lesiones', 'UPDATE', $id, 'Actualización de diagnóstico/estado', null, json_encode($_POST));
            $id_atleta =$objLesion->getCampo("id_atleta");
            Notificacion::NotificarLesiones('UPDATE', $_POST, (int)$id_atleta, $id);
            echo json_encode(['status' => 'success', 'message' => 'Informe clínico actualizado.']);
        } else {
            $err = $objLesion->obtenerErrores();
            echo json_encode(['status' => 'error', 'message' => reset($err) ?: 'Error al actualizar.']);
        }
        exit;
    }

    if ($accion === 'anular') {
        Autorizacion::exigir('lesiones', 'eliminar');
        $id = (int)($_POST['id_lesion'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? '');
        
        if ($id <= 0 || strlen($motivo) < 5) { 
            ob_end_clean(); 
            echo json_encode(['status' => 'error', 'message' => 'Debe proveer un ID válido y un motivo justificado (min. 10 caracteres).']); 
            exit; 
        }
        $detalleLesion = $objLesion->obtenerDetallePorId($id);
        
        $res = $objLesion->eliminarLesionLogicamente($id, $motivo);
        ob_end_clean();
        
        if ($res) {
            Bitacora::registrar($id_usuario, 'Lesiones', 'SOFT_DELETE', $id, 'Movido a papelera', null, "Motivo: $motivo");
           if ($detalleLesion) {
               Notificacion::NotificarLesiones('DELETE', $detalleLesion, (int)$detalleLesion['id_atleta'], $id);
            }
            echo json_encode(['status' => 'success', 'message' => 'Registro movido a la papelera.']);
        } else {
            $err = $objLesion->obtenerErrores();
            echo json_encode(['status' => 'error', 'message' => reset($err) ?: 'Error al anular.']);
        }
        exit;
    }

    if ($accion === 'reactivar') {
        Autorizacion::exigir('lesiones', 'reactivar');
        $id = (int)($_POST['id_lesion'] ?? 0);
        
        if ($id <= 0) { 
            ob_end_clean(); 
            echo json_encode(['status' => 'error', 'message' => 'ID inválido']); 
            exit; 
        }
        $detalleLesion = $objLesion->obtenerDetallePorId($id);
        $res = $objLesion->reactivarLesion($id);
        ob_end_clean();
        
        if ($res) {
            Bitacora::registrar($id_usuario, 'Lesiones', 'REACTIVATE', $id, 'Restaurado desde papelera', null, null);
            if ($detalleLesion) {
               Notificacion::NotificarLesiones('RESTORE', $detalleLesion, (int)$detalleLesion['id_atleta'], $id);
            }
            echo json_encode(['status' => 'success', 'message' => 'Lesión reactivada exitosamente.']);
        } else {
            $err = $objLesion->obtenerErrores();
            echo json_encode(['status' => 'error', 'message' => reset($err) ?: 'Error al reactivar el registro.']);
        }
        exit;
    }

    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Acción POST no soportada.']);
    exit;
}
<?php

if (empty($_SESSION['id'])) { 
    header('Location: ?p=login'); 
    exit; 
}

use GrupoProyecto\SisBiomec\modelo\Grupo;
use GrupoProyecto\SisBiomec\seguridad\Autorizacion;

$objGrupo = new Grupo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    if (isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
        Autorizacion::exigir('atletas', 'gestionar');
        $id = isset($_POST['id_grupo']) ? (int)$_POST['id_grupo'] : 0;

        if ($objGrupo->cambiarEstadoGrupo($id, 0)) { 
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo desactivar el grupo.']);
        }
        exit;
    }

    if (isset($_POST['accion']) && $_POST['accion'] === 'reactivar') {
        Autorizacion::exigir('atletas', 'gestionar');
        $id = isset($_POST['id_grupo']) ? (int)$_POST['id_grupo'] : 0;
        
        if ($objGrupo->cambiarEstadoGrupo($id, 1)) { 
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo reactivar el grupo.']);
        }
        exit;
    }    
    
    Autorizacion::exigir('atletas', 'gestionar');
    $idOriginal = !empty($_POST['id_grupo_original']) ? $_POST['id_grupo_original'] : null;
    
    $errores = $objGrupo->validarDatos($_POST, $idOriginal);

    if (!empty($errores)) {
        echo json_encode(['status' => 'warning', 'errores' => $errores]);
        exit;
    }

    $resultado = false; 

    if ($idOriginal) {
        $resultado = $objGrupo->actualizarGrupo($_POST);
    } else {
        $resultado = $objGrupo->registrarGrupo($_POST);
    }

    if ($resultado) {
        echo json_encode(['status' => 'success', 'message' => 'Operación realizada con éxito.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error en base de datos.']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    if (isset($_GET['accion']) && $_GET['accion'] === 'listarGrupos') {
        header('Content-Type: application/json');
        $estadoInput = $_GET['estado'] ?? 'Activo';
        $estadoInt = ($estadoInput === 'Activo') ? 1 : 0;
        echo json_encode($objGrupo->listarGrupos($estadoInt));
        exit;
    }

    if (isset($_GET['accion']) && $_GET['accion'] === 'listarEntrenadoresGlobales') {
        header('Content-Type: application/json');
        
        try {
            $conex = null;
            if (method_exists($objGrupo, 'obtenerConexion')) {
                $conex = $objGrupo->obtenerConexion();
            } elseif (method_exists($objGrupo, 'getConexion')) {
                $conex = $objGrupo->getConexion();
            } else {
                $conex = $objGrupo->pdo;
            }

            if ($conex) {
                $stmt = $conex->query("SELECT id_entrenador, nombres, apellidos, cedula FROM entrenador ORDER BY nombres ASC");
                $entrenadores = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode($entrenadores);
            } else {
                error_log("Error Controlador: No se pudo conectar para listar entrenadores.");
                echo json_encode([]);
            }
        } catch (Exception $e) {
            error_log("Error en controlador entrenadores: " . $e->getMessage());
            echo json_encode([]);
        }
        exit;
    }

    if (isset($_GET['accion']) && $_GET['accion'] === 'obtenerGrupo' && isset($_GET['id'])) {
        header('Content-Type: application/json');
        echo json_encode($objGrupo->obtenerPorId((int)$_GET['id']));
        exit; 
    }

    require_once 'vista/grupo.php';
}
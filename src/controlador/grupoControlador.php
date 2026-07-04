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

    if (isset($_POST['accion']) && $_POST['accion'] === 'guardarGrupo') {
        Autorizacion::exigir('atletas', 'gestionar');
        $idOriginal = !empty($_POST['id_grupo_original']) ? $_POST['id_grupo_original'] : null;
        
        $objGrupo->setDatos($_POST);
        $errores = $objGrupo->validarDatos($_POST);

        if (!empty($errores)) {
            echo json_encode(['status' => 'warning', 'errores' => $errores]);
            exit;
        }

        $resultado = false; 
        $mensaje_error = '';

        try {
            if ($idOriginal) {
                $resultado = $objGrupo->editarGrupo();
            } else {
                $resultado = $objGrupo->registrarGrupo();
            }
            
            if (!$resultado) {
                $mensaje_error = $objGrupo->obtenerUltimoError() ?: 'Error desconocido al guardar.';
            }
        } catch (Exception $e) {
            $mensaje_error = $e->getMessage();
            $resultado = false;
        }

        if ($resultado) {
            echo json_encode(['status' => 'success', 'message' => 'Operación realizada con éxito.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error en base de datos: ' . $mensaje_error]);
        }
        exit;
    }

    if (isset($_POST['accion']) && $_POST['accion'] === 'asignarAtletas') {
        Autorizacion::exigir('atletas', 'gestionar');
        
        $id_grupo = isset($_POST['id_grupo']) ? (int)$_POST['id_grupo'] : 0;
        $atletas = isset($_POST['atletas']) ? $_POST['atletas'] : [];
        
        $datos = [
            'id_grupo' => $id_grupo,
            'atletas' => $atletas
        ];
        
        $errores = $objGrupo->validarAsignacion($datos);
        
        if (!empty($errores)) {
            echo json_encode(['status' => 'warning', 'errores' => $errores]);
            exit;
        }
        
        $resultado = $objGrupo->asignarGrupoAtletas($datos);
        
        if ($resultado) {
            echo json_encode(['status' => 'success', 'message' => 'Atletas asignados correctamente.']);
        } else {
            $mensaje_error = $objGrupo->obtenerUltimoError() ?: 'Error desconocido al asignar.';
            echo json_encode(['status' => 'error', 'message' => 'Error al asignar atletas: ' . $mensaje_error]);
        }
        exit;
    }

    if (isset($_POST['accion']) && $_POST['accion'] === 'desasignarAtleta') {
        Autorizacion::exigir('atletas', 'gestionar');
        
        $id_atleta = isset($_POST['id_atleta']) ? (int)$_POST['id_atleta'] : 0;
        
        if ($id_atleta <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'ID de atleta inválido.']);
            exit;
        }
        
        $resultado = $objGrupo->desasignarAtletas([$id_atleta]);
        
        if ($resultado) {
            echo json_encode(['status' => 'success', 'message' => 'Atleta desasignado correctamente.']);
        } else {
            $mensaje_error = $objGrupo->obtenerUltimoError() ?: 'Error desconocido.';
            echo json_encode(['status' => 'error', 'message' => 'Error al desasignar atleta: ' . $mensaje_error]);
        }
        exit;
    }

    if (isset($_POST['accion']) && $_POST['accion'] === 'cambiarGrupoAtleta') {
        Autorizacion::exigir('atletas', 'gestionar');
        
        $id_atleta = isset($_POST['id_atleta']) ? (int)$_POST['id_atleta'] : 0;
        $id_nuevo_grupo = isset($_POST['id_nuevo_grupo']) ? (int)$_POST['id_nuevo_grupo'] : 0;
        
        if ($id_atleta <= 0 || $id_nuevo_grupo <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Datos inválidos.']);
            exit;
        }
        
        $resultado = $objGrupo->cambiarGrupoAtleta($id_atleta, $id_nuevo_grupo);
        
        if ($resultado) {
            echo json_encode(['status' => 'success', 'message' => 'Grupo cambiado correctamente.']);
        } else {
            $mensaje_error = $objGrupo->obtenerUltimoError() ?: 'Error desconocido.';
            echo json_encode(['status' => 'error', 'message' => 'Error al cambiar grupo: ' . $mensaje_error]);
        }
        exit;
    }

    if (isset($_POST['accion']) && $_POST['accion'] === 'verificarDuplicado') {
        $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
        $id_excluir = isset($_POST['id_excluir']) ? (int)$_POST['id_excluir'] : null;
        
        if (empty($nombre)) {
            echo json_encode(['existe' => false]);
            exit;
        }
        
        $existe = $objGrupo->verificarNombreExistente($nombre, $id_excluir);
        echo json_encode(['existe' => $existe]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    if (isset($_GET['accion'])) {
        $accion = $_GET['accion'];

        if ($accion === 'listarGrupos') {
            header('Content-Type: application/json');
            $estadoInput = $_GET['estado'] ?? 'Activo';
            $estadoInt = ($estadoInput === 'Activo') ? 1 : 0;
            echo json_encode($objGrupo->listarGrupos($estadoInt));
            exit;
        }

        if ($accion === 'listarEntrenador') {
            header('Content-Type: application/json');
            
            try {
                $entrenadores = $objGrupo->listarEntrenadoresDisponibles();
                
                if (!is_array($entrenadores)) {
                    $entrenadores = [];
                }
                
                echo json_encode($entrenadores, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                
            } catch (Exception $e) {
                echo json_encode([]);
            }
            exit;
        }

        if ($accion === 'listarCategorias') {
            header('Content-Type: application/json');
            echo json_encode($objGrupo->listarCategorias());
            exit;
        }

        if ($accion === 'listarAtletasPorCategoria' && isset($_GET['id_categoria'])) {
            header('Content-Type: application/json');
            $id_categoria = (int)$_GET['id_categoria'];
            echo json_encode($objGrupo->listarAtletasPorCategoria($id_categoria));
            exit;
        }

        if ($accion === 'obtenerGrupo' && isset($_GET['id'])) {
            header('Content-Type: application/json');
            echo json_encode($objGrupo->obtenerPorId((int)$_GET['id']));
            exit; 
        }

        if ($accion === 'listarAtletasDisponibles') {
            header('Content-Type: application/json');
            echo json_encode($objGrupo->listarAtletasDisponibles());
            exit;
        }

        if ($accion === 'listarAtletasPorGrupo' && isset($_GET['id_grupo'])) {
            header('Content-Type: application/json');
            $id_grupo = (int)$_GET['id_grupo'];
            echo json_encode($objGrupo->listarAtletasPorGrupo($id_grupo));
            exit;
        }

        if ($accion === 'listarTodosAtletas') {
            header('Content-Type: application/json');
            echo json_encode($objGrupo->listarTodosAtletas());
            exit;
        }

        if ($accion === 'listarGruposConConteo') {
            header('Content-Type: application/json');
            echo json_encode($objGrupo->listarGruposConConteo());
            exit;
        }

        if ($accion === 'listarAtletasPorEdad' && isset($_GET['edad_min']) && isset($_GET['edad_max'])) {
            header('Content-Type: application/json');
            $edad_min = (int)$_GET['edad_min'];
            $edad_max = (int)$_GET['edad_max'];
            echo json_encode($objGrupo->listarAtletasPorEdad($edad_min, $edad_max));
            exit;
        }

        if ($accion === 'verificarAsignacion' && isset($_GET['id_atleta'])) {
            header('Content-Type: application/json');
            $id_atleta = (int)$_GET['id_atleta'];
            $tieneAsignacion = $objGrupo->atletaTieneAsignacion($id_atleta);
            echo json_encode(['tiene_asignacion' => $tieneAsignacion]);
            exit;
        }

        if ($accion === 'obtenerGrupoActualAtleta' && isset($_GET['id_atleta'])) {
            header('Content-Type: application/json');
            $id_atleta = (int)$_GET['id_atleta'];
            echo json_encode($objGrupo->obtenerGrupoActualAtleta($id_atleta));
            exit;
        }
        
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Acción no reconocida: ' . $accion]);
        exit;
    }

    require_once 'vista/grupo.php';
}
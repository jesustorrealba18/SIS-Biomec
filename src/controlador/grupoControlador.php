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
    $accion = isset($_GET['accion']) ? $_GET['accion'] : '';

    $accionesPost = [
        'eliminar' => function() use ($objGrupo) {
            Autorizacion::exigir('atletas', 'gestionar');
            $id = isset($_POST['id_grupo']) ? (int)$_POST['id_grupo'] : 0;
            $resultado = $objGrupo->cambiarEstadoGrupo($id, 0);
            return $resultado ? ['status' => 'success'] : ['status' => 'error', 'message' => 'No se pudo desactivar el grupo.'];
        },
        
        'reactivar' => function() use ($objGrupo) {
            Autorizacion::exigir('atletas', 'gestionar');
            $id = isset($_POST['id_grupo']) ? (int)$_POST['id_grupo'] : 0;
            $resultado = $objGrupo->cambiarEstadoGrupo($id, 1);
            return $resultado ? ['status' => 'success'] : ['status' => 'error', 'message' => 'No se pudo reactivar el grupo.'];
        },
        
        'guardarGrupo' => function() use ($objGrupo) {
            Autorizacion::exigir('atletas', 'gestionar');
            $idOriginal = !empty($_POST['id_grupo_original']) ? $_POST['id_grupo_original'] : null;
            $objGrupo->setDatos($_POST);
            $errores = $objGrupo->validarDatos($_POST);
            
            if (!empty($errores)) {
                return ['status' => 'warning', 'errores' => $errores];
            }
            
            try {
                $resultado = $idOriginal ? $objGrupo->editarGrupo() : $objGrupo->registrarGrupo();
                return $resultado 
                    ? ['status' => 'success', 'message' => 'Operación realizada con éxito.']
                    : ['status' => 'error', 'message' => 'Error en base de datos: ' . ($objGrupo->obtenerUltimoError() ?: 'Error desconocido al guardar.')];
            } catch (Exception $e) {
                return ['status' => 'error', 'message' => 'Error en base de datos: ' . $e->getMessage()];
            }
        },
        
        'asignarAtletas' => function() use ($objGrupo) {
            Autorizacion::exigir('atletas', 'gestionar');
            $datos = [
                'id_grupo' => isset($_POST['id_grupo']) ? (int)$_POST['id_grupo'] : 0,
                'atletas' => isset($_POST['atletas']) ? $_POST['atletas'] : []
            ];
            
            $errores = $objGrupo->validarAsignacion($datos);
            if (!empty($errores)) {
                return ['status' => 'warning', 'errores' => $errores];
            }
            
            $resultado = $objGrupo->asignarGrupoAtletas($datos);
            return $resultado 
                ? ['status' => 'success', 'message' => 'Atletas asignados correctamente.']
                : ['status' => 'error', 'message' => 'Error al asignar atletas: ' . ($objGrupo->obtenerUltimoError() ?: 'Error desconocido al asignar.')];
        },
        
        'desasignarAtleta' => function() use ($objGrupo) {
            Autorizacion::exigir('atletas', 'gestionar');
            $id_atleta = isset($_POST['id_atleta']) ? (int)$_POST['id_atleta'] : 0;
            
            if ($id_atleta <= 0) {
                return ['status' => 'error', 'message' => 'ID de atleta inválido.'];
            }
            
            $resultado = $objGrupo->desasignarAtletas([$id_atleta]);
            return $resultado 
                ? ['status' => 'success', 'message' => 'Atleta desasignado correctamente.']
                : ['status' => 'error', 'message' => 'Error al desasignar atleta: ' . ($objGrupo->obtenerUltimoError() ?: 'Error desconocido.')];
        },
        
        'cambiarGrupoAtleta' => function() use ($objGrupo) {
            Autorizacion::exigir('atletas', 'gestionar');
            $id_atleta = isset($_POST['id_atleta']) ? (int)$_POST['id_atleta'] : 0;
            $id_nuevo_grupo = isset($_POST['id_nuevo_grupo']) ? (int)$_POST['id_nuevo_grupo'] : 0;
            
            if ($id_atleta <= 0 || $id_nuevo_grupo <= 0) {
                return ['status' => 'error', 'message' => 'Datos inválidos.'];
            }
            
            $resultado = $objGrupo->cambiarGrupoAtleta($id_atleta, $id_nuevo_grupo);
            return $resultado 
                ? ['status' => 'success', 'message' => 'Grupo cambiado correctamente.']
                : ['status' => 'error', 'message' => 'Error al cambiar grupo: ' . ($objGrupo->obtenerUltimoError() ?: 'Error desconocido.')];
        },
        
        'verificarDuplicado' => function() use ($objGrupo) {
            $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
            if (empty($nombre)) {
                return ['existe' => false];
            }
            $id_excluir = isset($_POST['id_excluir']) ? (int)$_POST['id_excluir'] : null;
            return ['existe' => $objGrupo->verificarNombreExistente($nombre, $id_excluir)];
        }
    ];

    if (isset($accionesPost[$accion])) {
        echo json_encode($accionesPost[$accion]());
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = isset($_GET['accion']) ? $_GET['accion'] : '';

    $accionesGet = [
        'listarGrupos' => function() use ($objGrupo) {
            $estadoInput = $_GET['estado'] ?? 'Activo';
            $estadoInt = ($estadoInput === 'Activo') ? 1 : 0;
            return $objGrupo->listarGrupos($estadoInt);
        },
        
        'listarEntrenador' => function() use ($objGrupo) {
            try {
                $entrenadores = $objGrupo->listarEntrenadoresDisponibles();
                return is_array($entrenadores) ? $entrenadores : [];
            } catch (Exception $e) {
                return [];
            }
        },
        
        'listarCategorias' => function() use ($objGrupo) {
            return $objGrupo->listarCategorias();
        },
        
        'listarAtletasPorCategoria' => function() use ($objGrupo) {
            $id_categoria = isset($_GET['id_categoria']) ? (int)$_GET['id_categoria'] : 0;
            return $objGrupo->listarAtletasPorCategoria($id_categoria);
        },
        
        'obtenerGrupo' => function() use ($objGrupo) {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            return $objGrupo->obtenerPorId($id);
        },
        
        'listarAtletasDisponibles' => function() use ($objGrupo) {
            return $objGrupo->listarAtletasDisponibles();
        },
        
        'listarAtletasPorGrupo' => function() use ($objGrupo) {
            $id_grupo = isset($_GET['id_grupo']) ? (int)$_GET['id_grupo'] : 0;
            return $objGrupo->listarAtletasPorGrupo($id_grupo);
        },
        
        'listarTodosAtletas' => function() use ($objGrupo) {
            return $objGrupo->listarTodosAtletas();
        },
        
        'listarGruposConConteo' => function() use ($objGrupo) {
            return $objGrupo->listarGruposConConteo();
        },
        
        'listarAtletasPorEdad' => function() use ($objGrupo) {
            $edad_min = isset($_GET['edad_min']) ? (int)$_GET['edad_min'] : 0;
            $edad_max = isset($_GET['edad_max']) ? (int)$_GET['edad_max'] : 0;
            return $objGrupo->listarAtletasPorEdad($edad_min, $edad_max);
        },
        
        'verificarAsignacion' => function() use ($objGrupo) {
            $id_atleta = isset($_GET['id_atleta']) ? (int)$_GET['id_atleta'] : 0;
            $tieneAsignacion = $objGrupo->atletaTieneAsignacion($id_atleta);
            return ['tiene_asignacion' => $tieneAsignacion];
        },
        
        'obtenerGrupoActualAtleta' => function() use ($objGrupo) {
            $id_atleta = isset($_GET['id_atleta']) ? (int)$_GET['id_atleta'] : 0;
            return $objGrupo->obtenerGrupoActualAtleta($id_atleta);
        }
    ];

    if (isset($accionesGet[$accion])) {
        header('Content-Type: application/json');
        echo json_encode($accionesGet[$accion]());
        exit;
    }
    
    require_once 'vista/grupo.php';
}
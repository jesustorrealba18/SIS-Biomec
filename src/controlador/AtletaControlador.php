<?php

ob_start();
session_start();

if (empty($_SESSION['id'])) {
    header('Location: ?p=login');
    exit;
}

use GrupoProyecto\SisBiomec\modelo\Atleta;
use GrupoProyecto\SisBiomec\seguridad\Bitacora;

$objAtleta = new Atleta();

function jsonSalida($datos) {
    if (ob_get_level()) ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode($datos);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

    if ($accion === 'guardar') {
        try {
            $datos = $_POST;
            $datos['id_usuario'] = $_SESSION['id'] ?? null;

            $objAtleta->setDatos($datos);

            if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
                $fotoRuta = $objAtleta->procesarFoto($_FILES['foto'], null);
                $objAtleta->setCampo('foto', $fotoRuta);
            }

            if ($objAtleta->hayErrores()) {
                jsonSalida(['status' => 'warning', 'errores' => $objAtleta->obtenerErrores()]);
            }

            $resultado = $objAtleta->guardar();

            if ($resultado) {
                Bitacora::registrar($_SESSION['id'], 'Atleta', 'INSERT', null);
                jsonSalida(['status' => 'success', 'message' => 'Atleta registrado correctamente.']);
            } else {
                jsonSalida(['status' => 'error', 'message' => 'Error al registrar el atleta en la base de datos.']);
            }
        } catch (\Throwable $e) {
            jsonSalida(['status' => 'error', 'message' => 'Error interno: ' . $e->getMessage()]);
        }
    }

    if ($accion === 'editar') {
        try {
            $datos = $_POST;
            $datos['id_usuario'] = $_SESSION['id'] ?? null;
            $idAtleta = (int)($datos['id_atleta'] ?? 0);

            $objAtleta->setDatos($datos);

            if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
                $actual = $objAtleta->obtenerDetallePorId($idAtleta);
                $fotoActual = $actual['foto'] ?? null;
                $fotoRuta = $objAtleta->procesarFoto($_FILES['foto'], $fotoActual);
                $objAtleta->setCampo('foto', $fotoRuta);
            }

            if ($objAtleta->hayErrores()) {
                jsonSalida(['status' => 'warning', 'errores' => $objAtleta->obtenerErrores()]);
            }

            $resultado = $objAtleta->actualizar();

            if ($resultado) {
                Bitacora::registrar($_SESSION['id'], 'Atleta', 'UPDATE', $idAtleta);
                jsonSalida(['status' => 'success', 'message' => 'Atleta actualizado correctamente.']);
            } else {
                jsonSalida(['status' => 'error', 'message' => 'Error al actualizar el atleta en la base de datos.']);
            }
        } catch (\Throwable $e) {
            jsonSalida(['status' => 'error', 'message' => 'Error interno: ' . $e->getMessage()]);
        }
    }

    if ($accion === 'eliminar') {
        try {
            $id = (int)($_POST['id_atleta'] ?? 0);

            if ($id > 0) {
                $resultado = $objAtleta->eliminar($id);

                if ($resultado) {
                    Bitacora::registrar($_SESSION['id'], 'Atleta', 'DELETE', $id);
                    jsonSalida(['status' => 'success', 'message' => 'Atleta desactivado correctamente.']);
                } else {
                    jsonSalida(['status' => 'error', 'message' => 'No se pudo desactivar el atleta.']);
                }
            } else {
                jsonSalida(['status' => 'error', 'message' => 'ID de atleta no proporcionado.']);
            }
        } catch (\Throwable $e) {
            jsonSalida(['status' => 'error', 'message' => 'Error interno: ' . $e->getMessage()]);
        }
    }

    jsonSalida(['status' => 'error', 'message' => 'Acción no reconocida.']);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = $_GET['accion'] ?? '';

    if ($accion === 'listar') {
        jsonSalida($objAtleta->listar());
    }

    if ($accion === 'obtener' && isset($_GET['id'])) {
        jsonSalida($objAtleta->obtenerDetallePorId((int)$_GET['id']));
    }

    if ($accion === 'categorias') {
        jsonSalida($objAtleta->obtenerCategorias());
    }

    require_once 'vista/atleta.php';
}

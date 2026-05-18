<?php

namespace GrupoProyecto\SisBiomec\controlador;

use GrupoProyecto\SisBiomec\modelo\Atleta;

class AtletaControlador {
    private Atleta $atleta;

    public function __construct(Atleta $atleta) {
        $this->atleta = $atleta;
    }

    public function handle() {
        session_start();

        if (empty($_SESSION['id'])) {
            header('Location: ?p=login');
            exit;
        }

        if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
            $this->eliminar((int)$_GET['eliminar']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->guardar();
            return;
        }

        $this->listar();
    }

    private function eliminar(int $id): void {
        $this->atleta->eliminarAtleta($id);
        header('Location: ?p=atleta&m=eliminado');
        exit;
    }

    private function guardar(): void {
        $datos = $_POST;
        $datos['id_usuario'] = $_SESSION['id'] ?? null;

        $excluirId = !empty($datos['id_atleta']) ? (int)$datos['id_atleta'] : null;
        $this->atleta->validarDatos($datos, $excluirId);

        $fotoActual = null;
        if ($excluirId) {
            $atletaActual = $this->atleta->obtenerPorId($excluirId);
            $fotoActual = $atletaActual['foto'] ?? null;
        }

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
            $datos['foto'] = $this->atleta->procesarFoto($_FILES['foto'], $fotoActual);
        } else {
            $datos['foto'] = $fotoActual;
        }

        $errores = $this->atleta->obtenerErrores();

        if (empty($errores)) {
            if ($excluirId) {
                $this->atleta->editarAtleta($datos);
                header('Location: ?p=atleta&m=editado');
            } else {
                $this->atleta->registrarAtleta($datos);
                header('Location: ?p=atleta&m=registrado');
            }
            exit;
        }

        $this->listar($errores, $datos);
    }

    private function listar(array $errores = [], array $datosForm = []): void {
        $atletas = $this->atleta->listarAtletas();
        $categorias = $this->atleta->obtenerCategorias();
        require_once 'vista/atleta.php';
    }
}

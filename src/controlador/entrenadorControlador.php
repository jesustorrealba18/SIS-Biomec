<?php

namespace GrupoProyecto\SisBiomec\controlador;

use GrupoProyecto\SisBiomec\modelo\entrenador;

class EntrenadorControlador {
    private entrenador $entrenador;

    public function __construct(entrenador $entrenador) {
        $this->entrenador = $entrenador;
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
        $this->entrenador->eliminarEntrenador($id);
        header('Location: ?p=entrenador&m=eliminado');
        exit;
    }

    private function guardar(): void {
        $datos = $_POST;
        $datos['id_usuario'] = $_SESSION['id'] ?? null;

        $excluirId = !empty($datos['id_atleta']) ? (int)$datos['id_entrenador'] : null;
        $this->entrenador->validarDatos($datos, $excluirId);

        $fotoActual = null;
        if ($excluirId) {
            $entrenadorActual = $this->entrenador->obtenerPorId($excluirId);
            $fotoActual = $entrenadorActual['foto'] ?? null;
        }

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
            $datos['foto'] = $this->entrenador->procesarFoto($_FILES['foto'], $fotoActual);
        } else {
            $datos['foto'] = $fotoActual;
        }

        $errores = $this->entrenador->obtenerErrores();

        if (empty($errores)) {
            if ($excluirId) {
                $this->entrenador->editarEntrenador($datos);
                header('Location: ?p=entrenador&m=editado');
            } else {
                $this->entrenador->registrarEntrenador($datos);
                header('Location: ?p=entrenador&m=registrado');
            }
            exit;
        }

        $this->listar($errores, $datos);
    }

    private function listar(array $errores = [], array $datosForm = []): void {
        $atletas = $this->entrenador->listarEntrenador();
        require_once 'vista/entrenador.php';
    }
}

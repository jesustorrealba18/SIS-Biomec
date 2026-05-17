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
            $this->guardar($_POST);
            return;
        }

        $this->listar();
    }

    private function eliminar(int $id): void {
        $this->atleta->eliminarAtleta($id);
        header('Location: ?p=atleta&m=eliminado');
        exit;
    }

    private function guardar(array $datos): void {
        $excluirId = !empty($datos['id_atleta']) ? (int)$datos['id_atleta'] : null;
        $errores = $this->atleta->validarDatos($datos, $excluirId);

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
        require_once 'vista/atleta.php';
    }
}

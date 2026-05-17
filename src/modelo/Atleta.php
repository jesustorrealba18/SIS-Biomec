<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class Atleta extends Conexion {
    use ValidacionesTrait;

    public function validarDatos(array $datos, ?int $excluirId = null): array {
        $this->resetearErrores();

        $cedula    = $datos['cedula'] ?? '';
        $nombres   = $datos['nombres'] ?? '';
        $apellidos = $datos['apellidos'] ?? '';
        $fecha     = $datos['fecha_nacimiento'] ?? '';
        $genero    = $datos['genero'] ?? '';
        $fichaje   = $datos['fichaje_federativo'] ?? '';
        $lateralidad = $datos['lateralidad'] ?? '';

        $this->requerido($cedula, 'cedula');
        $this->longitud($cedula, 'cedula', 5, 20);
        $this->soloNumeros($cedula, 'cedula');
        $this->unico($this->getConex1(), $cedula, 'atleta', 'cedula', $excluirId, 'id_atleta');

        $this->requerido($nombres, 'nombres');
        $this->longitud($nombres, 'nombres', 2, 100);
        $this->soloLetras($nombres, 'nombres');

        $this->requerido($apellidos, 'apellidos');
        $this->longitud($apellidos, 'apellidos', 2, 100);
        $this->soloLetras($apellidos, 'apellidos');

        $this->requerido($fecha, 'fecha_nacimiento');
        $this->fechaValida($fecha, 'fecha_nacimiento');
        $this->fechaNoFutura($fecha, 'fecha_nacimiento');

        $this->requerido($genero, 'genero');
        $this->enEnum($genero, 'genero', ['M', 'F']);

        if ($fichaje !== '') {
            $this->longitud($fichaje, 'fichaje_federativo', 1, 50);
        }

        if ($lateralidad !== '') {
            $this->enEnum($lateralidad, 'lateralidad', ['Derecho', 'Zurdo', 'Ambidiestro']);
        }

        return $this->errores;
    }

    public function listarAtletas() {
        $conex = $this->getConex1();
        try {
            $sql = "SELECT *, TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) AS edad FROM atleta";
            $stmt = $conex->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function registrarAtleta($datos) {
        $conex = $this->getConex1();
        try {
            $sql = "INSERT INTO atleta (cedula, nombres, apellidos, fecha_nacimiento, genero, fichaje_federativo, lateralidad) 
                    VALUES (:cedula, :nombres, :apellidos, :fecha_nac, :genero, :fichaje, :lateralidad)";
            
            $stmt = $conex->prepare($sql);
            return $stmt->execute([
                ':cedula'    => $datos['cedula'],
                ':nombres'   => $datos['nombres'],
                ':apellidos' => $datos['apellidos'],
                ':fecha_nac' => $datos['fecha_nacimiento'],
                ':genero'    => $datos['genero'],
                ':fichaje'   => $datos['fichaje_federativo'],
                ':lateralidad' => $datos['lateralidad']
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function editarAtleta($datos) {
        $conex = $this->getConex1();
        try {
            $sql = "UPDATE atleta SET cedula = :cedula, nombres = :nombres, apellidos = :apellidos, 
                    fecha_nacimiento = :fecha_nac, genero = :genero, fichaje_federativo = :fichaje, 
                    lateralidad = :lateralidad WHERE id_atleta = :id";
            $stmt = $conex->prepare($sql);
            return $stmt->execute([
                ':cedula'    => $datos['cedula'],
                ':nombres'   => $datos['nombres'],
                ':apellidos' => $datos['apellidos'],
                ':fecha_nac' => $datos['fecha_nacimiento'],
                ':genero'    => $datos['genero'],
                ':fichaje'   => $datos['fichaje_federativo'],
                ':lateralidad' => $datos['lateralidad'],
                ':id'        => $datos['id_atleta']
            ]);
        } catch (PDOException $e) { return false; }
    }

    public function eliminarAtleta($id) {
        $conex = $this->getConex1();
        try {
            $sql = "DELETE FROM atleta WHERE id_atleta = :id";
            $stmt = $conex->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) { return false; }
    }
}

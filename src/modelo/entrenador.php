<?php
require_once('modelo/conexion.php');

class Entrenador extends Conexion {
    public function __construct() {
        parent::__construct();
    }

    public function listarEntrenador() {
        $conex = $this->getConex1();
        try {
            $sql = "SELECT *, TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) AS edad FROM entrenador";
            $stmt = $conex->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function registrarEntrenador($datos) {
        $conex = $this->getConex1();
        try {
            $sql = "INSERT INTO entrenador (cedula, nombres, apellidos, fecha_nacimiento, genero) 
                    VALUES (:cedula, :nombres, :apellidos, :fecha_nac, :genero)";
            
            $stmt = $conex->prepare($sql);
            return $stmt->execute([
                ':cedula'    => $datos['cedula'],
                ':nombres'   => $datos['nombres'],
                ':apellidos' => $datos['apellidos'],
                ':fecha_nac' => $datos['fecha_nacimiento'],
                ':genero'    => $datos['genero']
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function obtenerPorId($id) {
        $conex = $this->getConex1();
        try {
            $sql = "SELECT * FROM entrenador WHERE id_entrenador = :id";
            $stmt = $conex->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return null; }
    }

    public function editarEntrenador($datos) {
        $conex = $this->getConex1();
        try {
            $sql = "UPDATE entrenado SET cedula = :cedula, nombres = :nombres, apellidos = :apellidos, 
                    fecha_nacimiento = :fecha_nac, genero = :genero WHERE id_entrenador = :id";
            $stmt = $conex->prepare($sql);
            return $stmt->execute([
                ':cedula'    => $datos['cedula'],
                ':nombres'   => $datos['nombres'],
                ':apellidos' => $datos['apellidos'],
                ':fecha_nac' => $datos['fecha_nacimiento'],
                ':genero'    => $datos['genero'],
                ':id'        => $datos['id_entrenador']
            ]);
        } catch (PDOException $e) { return false; }
    }

    public function eliminarEntrenador($id) {
        $conex = $this->getConex1();
        try {
            $sql = "DELETE FROM entrenador WHERE id_entrenador = :id";
            $stmt = $conex->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) { return false; }
    }
}
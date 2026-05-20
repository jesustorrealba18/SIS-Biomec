<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class Representante extends Conexion {
    // Importamos el trait de validaciones (Regla del profesor)
    use ValidacionesTrait;

    public function __construct() {
        parent::__construct();
    }

    // El modelo hace las validaciones pesadas, no el controlador
    public function validarDatos(array $datos, ?string $excluirCedula = null): array {
        $this->resetearErrores();

        $cedula = $datos['cedula'] ?? '';
        $nombres = $datos['nombres'] ?? '';
        $apellidos = $datos['apellidos'] ?? '';
        $correo = $datos['correo'] ?? '';

        $this->requerido($cedula, 'cedula');
        $this->soloNumeros($cedula, 'cedula');
        // Si no estamos editando, validamos que la cédula sea única
        if (!$excluirCedula) {
            $this->unico($this->getConex1(), $cedula, 'representante', 'cedula');
        }

        $this->requerido($nombres, 'nombres');
        $this->soloLetras($nombres, 'nombres');

        $this->requerido($apellidos, 'apellidos');
        $this->soloLetras($apellidos, 'apellidos');

        return $this->obtenerErrores();
    }

    public function registrarRepresentante(array $datos): bool {
        $conex = $this->getConex1();
        try {
            // Iniciamos transacción por si falla algo
            $conex->beginTransaction();

            $sql = "INSERT INTO representante (cedula, nombres, apellidos, telefono_principal, telefono_emergencia, correo, parentesco, direccion_residencia) 
                    VALUES (:cedula, :nombres, :apellidos, :tel1, :tel2, :correo, :parentesco, :direccion)";
            
            $stmt = $conex->prepare($sql);
            $stmt->execute([
                ':cedula'     => $datos['cedula'],
                ':nombres'    => $datos['nombres'],
                ':apellidos'  => $datos['apellidos'],
                ':tel1'       => $datos['telefono_principal'] ?? '',
                ':tel2'       => $datos['telefono_emergencia'] ?? '',
                ':correo'     => $datos['correo'] ?? '',
                ':parentesco' => $datos['parentesco'] ?? '',
                ':direccion'  => $datos['direccion_residencia'] ?? ''
            ]);

            // Si hay atletas seleccionados en el modal, los vinculamos
            if (!empty($datos['atletas_ids']) && is_array($datos['atletas_ids'])) {
                $this->vincularAtletas($conex, $datos['cedula'], $datos['atletas_ids']);
            }

            $conex->commit();
            return true;
        } catch (PDOException $e) {
            $conex->rollBack();
            return false;
        }
    }

    private function vincularAtletas(PDO $conex, string $cedula_representante, array $atletas_ids) {
        $sql = "INSERT INTO atleta_representante (cedula_representante, id_atleta) VALUES (:cedula, :id_atleta)";
        $stmt = $conex->prepare($sql);
        
        foreach ($atletas_ids as $id_atleta) {
            $stmt->execute([
                ':cedula' => $cedula_representante,
                ':id_atleta' => $id_atleta
            ]);
        }
    }

    public function listarRepresentantes(): array {
        $conex = $this->getConex1();
        $sql = "SELECT * FROM representante ORDER BY nombres ASC";
        $stmt = $conex->query($sql);
        return $stmt->fetchAll();
    }
}
?>
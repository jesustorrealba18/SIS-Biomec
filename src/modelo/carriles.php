<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class carriles extends Conexion {
    use ValidacionesTrait;

    public function __construct() {
        parent::__construct('sis_natacion'); 
    }

    public function validarDatos(array $datos, ?string $excluirID = null): array {
        $this->resetearErrores();

        $numero                = $datos['numero'] ?? '';
        $capacidad_maxima      = $datos['capacidad_maxima'] ?? '';
        $activo                = $datos['activo'] ?? '';
        
        $this->requerido($numero, 'numero');
        $this->soloNumeros($numero, 'numero');
        $this->longitud($numero, 'numero', 1, 4);
        
       $conex = $this->getConex1();
        if ($excluirID === null) {
            $sql = "SELECT COUNT(*) FROM carriles WHERE numero = :numero";
            $stmt = $conex->prepare($sql);
            $stmt->execute([':numero' => $numero]);
            $existe = $stmt->fetchColumn();
            if ($existe > 0) {
                $this->errores['numero'] = 'Ya existe un carril con este número';
            }
        } else {
            $sql = "SELECT COUNT(*) FROM carriles WHERE numero = :numero AND id_carril != :id";
            $stmt = $conex->prepare($sql);
            $stmt->execute([':numero' => $numero, ':id' => $excluirID]);
            $existe = $stmt->fetchColumn();
            if ($existe > 0) {
                $this->errores['numero'] = 'Ya existe otro carril con este número';
            }
        }


        $this->requerido($capacidad_maxima, 'capacidad_maxima');
        $this->soloNumeros($capacidad_maxima, 'capacidad_maxima');
        $this->longitud($capacidad_maxima, 'capacidad_maxima', 1, 3); 
        $this->requerido($activo, 'activo');
        
        return $this->obtenerErrores();
    }

    public function registrarCarriles(array $datos): bool {
        $conex = $this->getConex1();
        try {
            $conex->beginTransaction();

            $sql = "INSERT INTO carriles (
                        numero, capacidad_maxima, activo
                    ) VALUES (
                        :numero, :capacidad_maxima, :activo
                    )";
            
            $stmt = $conex->prepare($sql);
            
            $stmt->execute([
                ':numero'               => $datos['numero'] ?? '',
                ':capacidad_maxima'     => $datos['capacidad_maxima'] ?? '',
                ':activo'               => $datos['activo'] ?? '',
            ]);

            $conex->commit();
            return true;
        } catch (PDOException $e) {
            $conex->rollBack();
            error_log("Error BD registrar Carril: " . $e->getMessage());
            return false;
        }
    }

    public function listarCarriles(): array {
        $conex = $this->getConex1();
        try {
            $sql = "SELECT id_carril, numero, capacidad_maxima, activo FROM carriles ORDER BY numero ASC";
            $stmt = $conex->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error Listando Carriles: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerPorId(int $id_carril): ?array {
        $conex = $this->getConex1();
        try {
            // CORREGIDO: faltaba el SELECT *
            $sql = "SELECT * FROM carriles WHERE id_carril = :id";
            $stmt = $conex->prepare($sql);
            $stmt->execute([':id' => $id_carril]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ? $resultado : null;
        } catch (PDOException $e) {
            error_log("Error en obtenerPorId: " . $e->getMessage());
            return null;
        }
    }
    
    public function actualizarCarriles(array $datos): bool {
        $conex = $this->getConex1();
        try {
            $conex->beginTransaction();

            $sql = "UPDATE carriles SET 
                        numero = :numero, 
                        capacidad_maxima = :capacidad_maxima, 
                        activo = :activo
                    WHERE id_carril = :id_carril";
                
            $stmt = $conex->prepare($sql);
            
            $id_carril = isset($datos['id_carril']) ? (int)$datos['id_carril'] : 0;
            
            $status = $stmt->execute([
                ':numero'              => $datos['numero'] ?? '',
                ':capacidad_maxima'    => $datos['capacidad_maxima'] ?? '',
                ':activo'              => $datos['activo'] ?? '',
                ':id_carril'           => $id_carril
            ]);
    
            $conex->commit();
            return $status;
        } catch (PDOException $e) {
            $conex->rollBack();
            error_log("Error en actualizar Carril: " . $e->getMessage());
            return false;
        }
    }

    public function eliminarCarril($id): bool {
        $conex = $this->getConex1();
        try {
            $sql = "DELETE FROM carriles WHERE id_carril = :id";
            $stmt = $conex->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) { 
            error_log("Error al eliminar el carril: " . $e->getMessage());
            return false; 
        }
    }
}
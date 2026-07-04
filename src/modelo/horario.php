<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class horario extends Conexion {
    use ValidacionesTrait;

    private array $datos = [];

    public function __construct() {
        parent::__construct('sis_natacion');
    }

    public function setDatos(array $datos): void {
        $this->datos = $datos;
    }

    public function setIdEliminar(int $id): void {
        $this->datos['id_asignacion'] = $id;
    }

    public function validarDatos(array $datos, $excluirID = null): array {
        $this->resetearErrores();

        $dia_semana = trim($datos['dia_semana'] ?? '');
        $hora_inicio = trim($datos['hora_inicio'] ?? '');
        $hora_fin = trim($datos['hora_fin'] ?? '');

        if (empty($dia_semana)) {
            $this->errores['dia_semana'] = 'El día de la semana es requerido';
        }
        
        if (empty($hora_inicio)) {
            $this->errores['hora_inicio'] = 'La hora de inicio es requerida';
        } elseif (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $hora_inicio)) {
            $this->errores['hora_inicio'] = 'Formato de hora inválido (debe ser HH:MM)';
        }
 
        if (empty($hora_fin)) {
            $this->errores['hora_fin'] = 'La hora de fin es requerida';
        } elseif (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $hora_fin)) {
            $this->errores['hora_fin'] = 'Formato de hora inválido (debe ser HH:MM)';
        }
        
        if (!isset($this->errores['hora_inicio']) && !isset($this->errores['hora_fin'])) {
            if ($hora_inicio >= $hora_fin) {
                $this->errores['hora_fin'] = 'La hora de fin debe ser mayor a la hora de inicio';
            }
        }
       
        if (!empty($this->errores)) {
            return $this->obtenerErrores();
        }
        
        try {
            $conex = $this->getConex1();
            if (!$conex) {
                $this->errores['base_datos'] = 'Error de conexión a la base de datos';
                return $this->obtenerErrores();
            }
            
            if ($excluirID === null) {
                $sql = "SELECT COUNT(*) as total 
                        FROM bloques_horarios 
                        WHERE dia_semana = ? 
                        AND (
                            (hora_inicio <= ? AND hora_fin > ?) 
                            OR (hora_inicio < ? AND hora_fin >= ?) 
                            OR (hora_inicio >= ? AND hora_fin <= ?)
                        )";
                $params = [$dia_semana, $hora_inicio, $hora_inicio, $hora_fin, $hora_fin, $hora_inicio, $hora_fin];
            } else {
                $sql = "SELECT COUNT(*) as total 
                        FROM bloques_horarios 
                        WHERE dia_semana = ? 
                        AND id_bloque != ?
                        AND (
                            (hora_inicio <= ? AND hora_fin > ?) 
                            OR (hora_inicio < ? AND hora_fin >= ?) 
                            OR (hora_inicio >= ? AND hora_fin <= ?)
                        )";
                $params = [$dia_semana, $excluirID, $hora_inicio, $hora_inicio, $hora_fin, $hora_fin, $hora_inicio, $hora_fin];
            }
            
            $stmt = $conex->prepare($sql);
            $stmt->execute($params);
            $existe = $stmt->fetchColumn();
            
            if ($existe > 0) {
                $this->errores['horario'] = 'Ya existe un bloque de horario que se cruza con este en el mismo día';
            }
            
        } catch (PDOException $e) {
            error_log("Error en validarDatos: " . $e->getMessage());
            $this->errores['base_datos'] = 'Error en la base de datos: ' . $e->getMessage();
        }
        
        return $this->obtenerErrores();
    }

    public function registrarHorario(): bool {
        return $this->registrarHorarioP($this->datos);
    }

    public function editarHorario(): bool {
        return $this->editarHorarioP($this->datos);
    }

    public function eliminarHorario(): bool {
        $id = $this->datos['id_asignacion'] ?? 0;
        return $this->eliminarHorarioP($id);
    }


    private function registrarHorarioP(array $datos): bool {
        $conex = $this->getConex1();
        try {
            $conex->beginTransaction();

            $sql = "INSERT INTO bloques_horarios (dia_semana, hora_inicio, hora_fin) 
                    VALUES (:dia_semana, :hora_inicio, :hora_fin)";
            
            $stmt = $conex->prepare($sql);
            
            $resultado = $stmt->execute([
                ':dia_semana' => $datos['dia_semana'],
                ':hora_inicio' => $datos['hora_inicio'],
                ':hora_fin' => $datos['hora_fin']
            ]);

            $conex->commit();
            return $resultado;
        } catch (PDOException $e) {
            if (isset($conex)) $conex->rollBack();
            error_log("Error BD registrar Bloque: " . $e->getMessage());
            return false;
        }
    }

    public function listarHorario(): array {
        $conex = $this->getConex1();
        try {
            $sql = "SELECT id_bloque, dia_semana, 
                    TIME_FORMAT(hora_inicio, '%H:%i') as hora_inicio, 
                    TIME_FORMAT(hora_fin, '%H:%i') as hora_fin 
                    FROM bloques_horarios 
                    ORDER BY FIELD(dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'), hora_inicio";
            $stmt = $conex->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error Listando Horario: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerPorId(int $id_bloque): ?array {
        $conex = $this->getConex1();
        try {
            $sql = "SELECT id_bloque, dia_semana, 
                    TIME_FORMAT(hora_inicio, '%H:%i') as hora_inicio, 
                    TIME_FORMAT(hora_fin, '%H:%i') as hora_fin 
                    FROM bloques_horarios WHERE id_bloque = :id";
            $stmt = $conex->prepare($sql);
            $stmt->execute([':id' => $id_bloque]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ? $resultado : null;
        } catch (PDOException $e) {
            error_log("Error en obtenerPorId: " . $e->getMessage());
            return null;
        }
    }
    
    private function editarHorarioP(array $datos): bool {
        $conex = $this->getConex1();
        try {
            $conex->beginTransaction();

            $sql = "UPDATE bloques_horarios SET 
                        dia_semana = :dia_semana, 
                        hora_inicio = :hora_inicio, 
                        hora_fin = :hora_fin
                    WHERE id_bloque = :id_bloque";
                
            $stmt = $conex->prepare($sql);
            
            $status = $stmt->execute([
                ':dia_semana' => $datos['dia_semana'],
                ':hora_inicio' => $datos['hora_inicio'],
                ':hora_fin' => $datos['hora_fin'],
                ':id_bloque' => $datos['id_bloque']
            ]);
    
            $conex->commit();
            return $status;
        } catch (PDOException $e) {
            if (isset($conex)) $conex->rollBack();
            error_log("Error en actualizar Horario: " . $e->getMessage());
            return false;
        }
    }

    private function eliminarHorarioP($id): bool {
        $conex = $this->getConex1();
        try {
            $sql = "DELETE FROM bloques_horarios WHERE id_bloque = :id";
            $stmt = $conex->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) { 
            error_log("Error al eliminar el bloque: " . $e->getMessage());
            return false; 
        }
    }
}
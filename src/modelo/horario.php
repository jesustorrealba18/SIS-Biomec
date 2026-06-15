<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class horario extends Conexion {
    use ValidacionesTrait;

    public function __construct() {
        parent::__construct('sis_natacion');
    }

    
public function validarDatos(array $datos, ?string $excluirID = null): array {
    $this->resetearErrores();

    $dia_semana     = trim($datos['dia_semana'] ?? '');
    $hora_inicio    = trim($datos['hora_inicio'] ?? '');
    $hora_fin       = trim($datos['hora_fin'] ?? '');

    if (empty($dia_semana)) {
        $this->errores['dia_semana'] = 'El día de la semana es requerido';
    }
    
    if (empty($hora_inicio)) {
        $this->errores['hora_inicio'] = 'La hora de inicio es requerida';
    } elseif (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $hora_inicio)) {
        $this->errores['hora_inicio'] = 'Formato de hora inválido (HH:MM)';
    }
    
    if (empty($hora_fin)) {
        $this->errores['hora_fin'] = 'La hora de fin es requerida';
    } elseif (!preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $hora_fin)) {
        $this->errores['hora_fin'] = 'Formato de hora inválido (HH:MM)';
    }
    
    if (!empty($hora_inicio) && !empty($hora_fin) && $hora_inicio >= $hora_fin) {
        $this->errores['hora_fin'] = 'La hora de fin debe ser mayor a la hora de inicio';
    }
   
    if (!empty($this->errores)) {
        return $this->obtenerErrores();
    }
    

    $hora_inicio_sql = $hora_inicio . ':00';
    $hora_fin_sql = $hora_fin . ':00';
    
    try {
        $conex = $this->getConex1();
        if (!$conex) {
            $this->errores['base_datos'] = 'Error de conexión a la base de datos';
            return $this->obtenerErrores();
        }
        
        if ($excluirID === null) {
            $sql = "SELECT COUNT(*) FROM bloques_horarios 
                    WHERE dia_semana = :dia 
                    AND (
                        (hora_inicio <= :inicio AND hora_fin > :inicio)
                        OR (hora_inicio < :fin AND hora_fin >= :fin)
                        OR (hora_inicio >= :inicio AND hora_fin <= :fin)
                    )";
            $stmt = $conex->prepare($sql);
            $stmt->execute([
                ':dia' => $dia_semana,
                ':inicio' => $hora_inicio_sql,
                ':fin' => $hora_fin_sql
            ]);
        } else {
            $sql = "SELECT COUNT(*) FROM bloques_horarios 
                    WHERE id_bloque != :id 
                    AND dia_semana = :dia 
                    AND (
                        (hora_inicio <= :inicio AND hora_fin > :inicio)
                        OR (hora_inicio < :fin AND hora_fin >= :fin)
                        OR (hora_inicio >= :inicio AND hora_fin <= :fin)
                    )";
            $stmt = $conex->prepare($sql);
            $stmt->execute([
                ':id' => $excluirID,
                ':dia' => $dia_semana,
                ':inicio' => $hora_inicio_sql,
                ':fin' => $hora_fin_sql
            ]);
        }
        
        $existe = $stmt->fetchColumn();
        
        if ($existe > 0) {
            $this->errores['horario'] = 'Ya existe un bloque de horario que se cruza con este';
        }
    } catch (PDOException $e) {
        error_log("Error en validarDatos: " . $e->getMessage());
        $this->errores['base_datos'] = 'Error al validar el horario';
    }
    
    return $this->obtenerErrores();
}

    public function registrarHorario(array $datos): bool {
        $conex = $this->getConex1();
        try {
            $conex->beginTransaction();

            $sql = "INSERT INTO bloques_horarios (
                        dia_semana, hora_inicio, hora_fin
                    ) VALUES (
                        :dia_semana, :hora_inicio, :hora_fin
                    )";
            
            $stmt = $conex->prepare($sql);
            
            $stmt->execute([
                ':dia_semana'   => $datos['dia_semana'] ?? '',
                ':hora_inicio'  => $datos['hora_inicio'] ?? '',
                ':hora_fin'     => $datos['hora_fin'] ?? '',
            ]);

            $conex->commit();
            return true;
        } catch (PDOException $e) {
            $conex->rollBack();
            error_log("Error BD registrar Bloque: " . $e->getMessage());
            return false;
        }
    }

    public function listarHorario(): array {
        $conex = $this->getConex1();
        try {
            $sql = "SELECT id_bloque, dia_semana, hora_inicio, hora_fin 
                    FROM bloques_horarios";
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
            $sql = "SELECT * FROM bloques_horarios WHERE id_bloque = :id";
            $stmt = $conex->prepare($sql);
            $stmt->execute([':id' => $id_bloque]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ? $resultado : null;
        } catch (PDOException $e) {
            error_log("Error en obtenerPorId: " . $e->getMessage());
            return null;
        }
    }
    
    public function actualizarHorario(array $datos): bool {
        $conex = $this->getConex1();
        try {
            $conex->beginTransaction();

            $sql = "UPDATE bloques_horarios SET 
                        dia_semana = :dia_semana, 
                        hora_inicio = :hora_inicio, 
                        hora_fin = :hora_fin
                    WHERE id_bloque = :id_bloque";
                
            $stmt = $conex->prepare($sql);
            
            $id_bloque = isset($datos['id_bloque']) ? (int)$datos['id_bloque'] : 0;
            
            $status = $stmt->execute([
                ':dia_semana'   => $datos['dia_semana'] ?? '',
                ':hora_inicio'  => $datos['hora_inicio'] ?? '',
                ':hora_fin'     => $datos['hora_fin'] ?? '',
                ':id_bloque'    => $id_bloque
            ]);
    
            $conex->commit();
            return $status;
        } catch (PDOException $e) {
            $conex->rollBack();
            error_log("Error en actualizar Horario: " . $e->getMessage());
            return false;
        }
    }

    public function eliminarHorario($id): bool {
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
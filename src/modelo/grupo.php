<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class Grupo extends Conexion {
    use ValidacionesTrait;

    public function __construct() {
        parent::__construct('sis_natacion'); 
    }

    public function validarDatos(array $datos, ?string $excluirId = null): array {
        $this->resetearErrores();

        $nombre = $datos['nombre'] ?? '';
        $descripcion = $datos['descripcion'] ?? '';

        $this->requerido($nombre, 'nombre');
        
        return $this->obtenerErrores();
    }

    /**
     * Registra un nuevo grupo de entrenamiento
     */
    public function registrarGrupo(array $datos): bool {
        $conex = $this->pdo;
        try {
            $sql = "INSERT INTO grupos_entrenamiento (
                        nombre, descripcion, id_entrenador, activo
                    ) VALUES (
                        :nombre, :descripcion, :id_entrenador, 1
                    )";
            
            $stmt = $conex->prepare($sql);
            
            return $stmt->execute([
                ':nombre'        => $datos['nombre'] ?? '',
                ':descripcion'   => $datos['descripcion'] ?? null,
                ':id_entrenador' => !empty($datos['id_entrenador']) ? (int)$datos['id_entrenador'] : null
            ]);
        } catch (PDOException $e) {
            error_log("Error BD Grupo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Modifica el estado activo/inactivo del grupo (Borrado lógico)
     */
    public function cambiarEstadoGrupo(int $id, int $estado): bool {
        $conex = $this->pdo;
        try {
            $sql = "UPDATE grupos_entrenamiento SET activo = :estado WHERE id_grupo = :id";
            $stmt = $conex->prepare($sql);
            return $stmt->execute([':estado' => $estado, ':id' => $id]);
        } catch (PDOException $e) {
            error_log("Error Estado Grupo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lista los grupos según el estado 'activo' (1 o 0)
     * Trae los datos del entrenador asociado globalmente, sin importar filtros de estado del entrenador.
     */
    public function listarGrupos(int $estado = 1): array {
        $conex = $this->pdo;
        try {
            $sql = "SELECT 
                        g.id_grupo, g.nombre, g.descripcion, g.activo, g.id_entrenador,
                        CONCAT(e.nombres, ' ', e.apellidos) as entrenador_nombre,
                        e.cedula as entrenador_cedula
                    FROM grupos_entrenamiento g
                    LEFT JOIN entrenador e ON g.id_entrenador = e.id_entrenador
                    WHERE g.activo = :estado 
                    ORDER BY g.id_grupo DESC";
                    
            $stmt = $conex->prepare($sql);
            $stmt->execute([':estado' => $estado]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error Listando Grupos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene los datos de un grupo por su ID
     */
    public function obtenerPorId(int $id): ?array {
        $conex = $this->pdo;
        try {
            $sql = "SELECT * FROM grupos_entrenamiento WHERE id_grupo = :id";
            $stmt = $conex->prepare($sql);
            $stmt->execute([':id' => $id]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ? $resultado : null;
        } catch (PDOException $e) {
            error_log("Error en obtenerPorId Grupo: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Actualiza los datos del grupo
     */
    public function actualizarGrupo(array $datos): bool {
        $conex = $this->pdo;
        try {
            $sql = "UPDATE grupos_entrenamiento SET 
                        nombre = :nombre, 
                        descripcion = :descripcion, 
                        id_entrenador = :id_entrenador
                    WHERE id_grupo = :id_grupo";
                    
            $stmt = $conex->prepare($sql);
            $id_grupo = (int)($datos['id_grupo_original'] ?? 0);
            
            return $stmt->execute([
                ':nombre'        => $datos['nombre'] ?? '',
                ':descripcion'   => $datos['descripcion'] ?? null,
                ':id_entrenador' => !empty($datos['id_entrenador']) ? (int)$datos['id_entrenador'] : null,
                ':id_grupo'      => $id_grupo
            ]);
        } catch (PDOException $e) {
            error_log("Error en actualizarGrupo: " . $e->getMessage());
            return false;
        }
    }
}
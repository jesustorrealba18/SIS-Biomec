<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class Asignacion extends Conexion {
    use ValidacionesTrait;

    public function __construct() {
        parent::__construct('sis_natacion'); 
    }

    public function validarDatos(array $datos, ?string $excluirId = null): array {
    $this->resetearErrores();

    $id_carril              = $datos['id_carril'] ?? '';
    $id_bloque_horario      = $datos['id_bloque_horario'] ?? '';
    $id_grupo               = $datos['id_grupo'] ?? '';  // Este viene del formulario como id_grupo
    $dia_especifico         = $datos['dia_especifico'] ?? '';
    $fecha_vigencia_inicio  = $datos['fecha_vigencia_inicio'] ?? '';  // CORREGIDO
    $fecha_vigencia_fin     = $datos['fecha_vigencia_fin'] ?? '';     // CORREGIDO
    $activa                 = $datos['activa'] ?? '';

    $this->requerido($id_carril, 'id_carril');
    $this->soloNumeros($id_carril, 'id_carril');

    $this->requerido($id_bloque_horario, 'id_bloque_horario');
    $this->soloNumeros($id_bloque_horario, 'id_bloque_horario');

    $this->requerido($id_grupo, 'id_grupo');
    $this->soloNumeros($id_grupo, 'id_grupo');

    // dia_especifico NO es requerido, puede ser NULL
    if (!empty($dia_especifico)) {
        // Validar formato de fecha si se proporciona
    }

    $this->requerido($fecha_vigencia_inicio, 'fecha_vigencia_inicio');

    if (!empty($fecha_vigencia_fin) && $fecha_vigencia_inicio > $fecha_vigencia_fin) {
        $this->errores['fecha_vigencia_fin'] = 'La fecha de fin debe ser mayor a la fecha de inicio';
    }

    $this->requerido($activa, 'activa');
    $this->soloNumeros($activa, 'activa');
    
    $conex = $this->getConex1();
    if ($excluirId === null) {
        $sql = "SELECT COUNT(*) FROM asignacion_carril 
                WHERE id_carril = :id_carril 
                AND id_bloque_horario = :id_bloque_horario
                AND (dia_especifico = :dia_especifico OR (dia_especifico IS NULL AND :dia_especifico IS NULL))
                AND activa = 1";
        $stmt = $conex->prepare($sql);
        $stmt->execute([
            ':id_carril' => $id_carril,
            ':id_bloque_horario' => $id_bloque_horario,
            ':dia_especifico' => empty($dia_especifico) ? null : $dia_especifico
        ]);
    } else {
        $sql = "SELECT COUNT(*) FROM asignacion_carril 
                WHERE id_asignacion != :id
                AND id_carril = :id_carril 
                AND id_bloque_horario = :id_bloque_horario
                AND (dia_especifico = :dia_especifico OR (dia_especifico IS NULL AND :dia_especifico IS NULL))
                AND activa = 1";
        $stmt = $conex->prepare($sql);
        $stmt->execute([
            ':id' => $excluirId,
            ':id_carril' => $id_carril,
            ':id_bloque_horario' => $id_bloque_horario,
            ':dia_especifico' => empty($dia_especifico) ? null : $dia_especifico
        ]);
    }
    
    $existe = $stmt->fetchColumn();
    if ($existe > 0) {
        $this->errores['asignacion'] = 'Ya existe una asignación activa para este carril, horario y día';
    }
    
    return $this->obtenerErrores();
}

   public function registrarAsignacion(array $datos): bool {
    $conex = $this->getConex1();
    try {
        $conex->beginTransaction();

        $sql = "INSERT INTO asignacion_carril (
                    id_carril, id_bloque_horario, id_grupo, dia_especifico, 
                    fecha_vigencia_inicio, fecha_vigencia_fin, activa
                ) VALUES (
                    :id_carril, :id_bloque_horario, :id_grupo, :dia_especifico, 
                    :fecha_vigencia_inicio, :fecha_vigencia_fin, :activa
                )";
        
        $stmt = $conex->prepare($sql);
        
        $resultado = $stmt->execute([
            ':id_carril'                => (int)$datos['id_carril'],
            ':id_bloque_horario'        => (int)$datos['id_bloque_horario'],
            ':id_grupo'                 => (int)$datos['id_grupo'],  // CORREGIDO: dg_grupo
            ':dia_especifico'           => empty($datos['dia_especifico']) ? null : $datos['dia_especifico'],
            ':fecha_vigencia_inicio'    => $datos['fecha_vigencia_inicio'] ?? '',
            ':fecha_vigencia_fin'       => empty($datos['fecha_vigencia_fin']) ? null : $datos['fecha_vigencia_fin'],
            ':activa'                   => isset($datos['activa']) ? (int)$datos['activa'] : 1
        ]);

        $conex->commit();
        return $resultado;
        
    } catch (PDOException $e) {
        $conex->rollBack();
        error_log("Error BD registrar Asignacion: " . $e->getMessage());
        return false;
    }
}

    public function listarAsignaciones(int $activa = 1): array {
    $conex = $this->getConex1();
    try {
        $sql = "SELECT 
                    a.id_asignacion,
                    a.id_carril,
                    a.id_bloque_horario,
                    a.id_grupo,
                    a.dia_especifico,
                    a.fecha_vigencia_inicio,
                    a.fecha_vigencia_fin,
                    a.activa,
                    c.numero as carril_numero,
                    c.capacidad_maxima,
                    bh.dia_semana,
                    bh.hora_inicio,
                    bh.hora_fin,
                    g.nombre as grupo_nombre
                FROM asignacion_carril a
                LEFT JOIN carriles c ON a.id_carril = c.id_carril
                LEFT JOIN bloques_horarios bh ON a.id_bloque_horario = bh.id_bloque
                LEFT JOIN grupos_entrenamiento g ON a.id_grupo = g.id_grupo
                WHERE a.activa = :activa
                ORDER BY a.fecha_vigencia_inicio DESC";
                
        $stmt = $conex->prepare($sql);
        $stmt->execute([':activa' => $activa]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error Listando Asignaciones: " . $e->getMessage());
        return [];
    }
}

    public function obtenerAsignacionPorId(int $id): ?array {
    $conex = $this->getConex1();
    try {
        $sql = "SELECT 
                    a.*,
                    a.id_grupo as id_grupo,
                    c.numero as carril_numero,
                    bh.dia_semana,
                    bh.hora_inicio,
                    bh.hora_fin,
                    g.nombre as grupo_nombre
                FROM asignacion_carril a
                LEFT JOIN carriles c ON a.id_carril = c.id_carril
                LEFT JOIN bloques_horarios bh ON a.id_bloque_horario = bh.id_bloque
                LEFT JOIN grupos_entrenamiento g ON a.id_grupo = g.id_grupo
                WHERE a.id_asignacion = :id";
        $stmt = $conex->prepare($sql);
        $stmt->execute([':id' => $id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ? $resultado : null;
    } catch (PDOException $e) {
        error_log("Error en obtenerAsignacionPorId: " . $e->getMessage());
        return null;
    }
}

   public function actualizarAsignacion(array $datos): bool {
    $conex = $this->getConex1();
    try {
        $conex->beginTransaction();

        $sql = "UPDATE asignacion_carril SET 
                    id_carril = :id_carril,
                    id_bloque_horario = :id_bloque_horario,
                    id_grupo = :id_grupo,
                    dia_especifico = :dia_especifico,
                    fecha_vigencia_inicio = :fecha_vigencia_inicio,
                    fecha_vigencia_fin = :fecha_vigencia_fin,
                    activa = :activa
                WHERE id_asignacion = :id_asignacion";
                
        $stmt = $conex->prepare($sql);
        
        $id_asignacion = isset($datos['id_asignacion']) ? (int)$datos['id_asignacion'] : 0;
        
        $resultado = $stmt->execute([
            ':id_asignacion'            => $id_asignacion,
            ':id_carril'                => (int)$datos['id_carril'],
            ':id_bloque_horario'        => (int)$datos['id_bloque_horario'],
            ':id_grupo'                 => (int)$datos['id_grupo'],  // CORREGIDO
            ':dia_especifico'           => empty($datos['dia_especifico']) ? null : $datos['dia_especifico'],
            ':fecha_vigencia_inicio'    => $datos['fecha_vigencia_inicio'] ?? '',
            ':fecha_vigencia_fin'       => empty($datos['fecha_vigencia_fin']) ? null : $datos['fecha_vigencia_fin'],
            ':activa'                   => isset($datos['activa']) ? (int)$datos['activa'] : 1
        ]);

        $conex->commit();
        return $resultado;
        
    } catch (PDOException $e) {
        $conex->rollBack();
        error_log("Error en actualizarAsignacion: " . $e->getMessage());
        return false;
    }
}

    public function eliminarAsignacion($id): bool {
        $conex = $this->getConex1();
        try {
            $sql = "DELETE FROM asignacion_carril WHERE id_asignacion = :id";
            $stmt = $conex->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) { 
            error_log("Error al eliminar la asignacion: " . $e->getMessage());
            return false; 
        }
    }

    public function desactivarAsignacion($id): bool {
        $conex = $this->getConex1();
        try {
            $sql = "UPDATE asignacion_carril SET activa = 0 WHERE id_asignacion = :id";
            $stmt = $conex->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) { 
            error_log("Error al desactivar la asignacion: " . $e->getMessage());
            return false; 
        }
    }

    public function reactivarAsignacion($id): bool {
    $conex = $this->getConex1();
    try {
        $sql = "UPDATE asignacion_carril SET activa = 1 WHERE id_asignacion = :id";
        $stmt = $conex->prepare($sql);
        return $stmt->execute([':id' => $id]);
    } catch (PDOException $e) { 
        error_log("Error al reactivar la asignacion: " . $e->getMessage());
        return false; 
    }
}

    public function listarGrupos(int $estado = 1): array {
        $conex = $this->getConex1();
        try {
            $sql = "SELECT 
                        g.id_grupo, 
                        g.nombre, 
                        g.descripcion, 
                        g.activo,
                        CONCAT(e.nombres, ' ', e.apellidos) as entrenador_nombre
                    FROM grupos_entrenamiento g
                    LEFT JOIN entrenador e ON g.id_entrenador = e.id_entrenador
                    WHERE g.activo = :estado 
                    ORDER BY g.nombre ASC";
                    
            $stmt = $conex->prepare($sql);
            $stmt->execute([':estado' => $estado]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error Listando Grupos: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerGrupoPorId(int $id): ?array {
        $conex = $this->getConex1();
        try {
            $sql = "SELECT * FROM grupos_entrenamiento WHERE id_grupo = :id";
            $stmt = $conex->prepare($sql);
            $stmt->execute([':id' => $id]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ? $resultado : null;
        } catch (PDOException $e) {
            error_log("Error en obtenerGrupoPorId: " . $e->getMessage());
            return null;
        }
    }

    public function listarTodosLosGrupos(): array {
        $conex = $this->getConex1();
        try {
            $sql = "SELECT id_grupo, nombre 
                    FROM grupos_entrenamiento 
                    WHERE activo = 1
                    ORDER BY nombre ASC";
            $stmt = $conex->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error listando todos los grupos: " . $e->getMessage());
            return [];
        }
    }

public function listarCarrilesActivos(): array {
    $conex = $this->getConex1();
    try {
        $sql = "SELECT id_carril, numero, capacidad_maxima 
                FROM carriles 
                WHERE activo = 1 
                ORDER BY numero ASC";
        $stmt = $conex->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error listando carriles: " . $e->getMessage());
        return [];
    }
}

public function obtenerCarrilPorId(int $id): ?array {
    $conex = $this->getConex1();
    try {
        $sql = "SELECT id_carril, numero, capacidad_maxima 
                FROM carriles 
                WHERE id_carril = :id AND activo = 1";
        $stmt = $conex->prepare($sql);
        $stmt->execute([':id' => $id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ? $resultado : null;
    } catch (PDOException $e) {
        error_log("Error obteniendo carril: " . $e->getMessage());
        return null;
    }
}

public function listarHorariosActivos(): array {
    $conex = $this->getConex1();
    try {
        $sql = "SELECT id_bloque, dia_semana, hora_inicio, hora_fin 
                FROM bloques_horarios 
                ORDER BY FIELD(dia_semana, 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'), hora_inicio";
        $stmt = $conex->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error listando bloques horarios: " . $e->getMessage());
        return [];
    }
}

public function obtenerHorarioPorId(int $id): ?array {
    $conex = $this->getConex1();
    try {
        $sql = "SELECT id_bloque, dia_semana, hora_inicio, hora_fin 
                FROM bloques_horarios 
                WHERE id_bloque = :id";
        $stmt = $conex->prepare($sql);
        $stmt->execute([':id' => $id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ? $resultado : null;
    } catch (PDOException $e) {
        error_log("Error obteniendo bloque de horario: " . $e->getMessage());
        return null;
    }
}

public function obtenerDatosParaFormulario(): array {
    return [
        'carriles' => $this->listarCarrilesActivos(),
        'bloques_horarios' => $this->listarHorariosActivos(),
        'grupos' => $this->listarTodosLosGrupos()
    ];
}

}
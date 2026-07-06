<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class Asignacion extends Conexion {
    use ValidacionesTrait;

    private array $datos = [];

    public function __construct() {
        parent::__construct('sis_natacion'); 
        error_log("=== MODELO ASIGNACION CONSTRUIDO ===");
    }

    public function setDatos(array $datos) {
        $this->datos = $datos;
        error_log("=== setDatos() llamado con: " . print_r($datos, true));
    }
    
    public function validarDatos(array $datos, ?string $excluirId = null): array {
        error_log("=== validarDatos() INICIO ===");
        $this->resetearErrores();

        $id_carril              = $datos['id_carril'] ?? '';
        $id_bloque_horario      = $datos['id_bloque_horario'] ?? '';
        $id_grupo               = $datos['id_grupo'] ?? '';
        $dia_especifico         = $datos['dia_especifico'] ?? '';
        $fecha_vigencia_inicio  = $datos['fecha_vigencia_inicio'] ?? $datos['fecha_vigente_inicio'] ?? '';
        $fecha_vigencia_fin     = $datos['fecha_vigencia_fin'] ?? $datos['fecha_vigente_fin'] ?? '';
        $activa                 = $datos['activa'] ?? '';

        error_log("id_carril: $id_carril, id_bloque: $id_bloque_horario, id_grupo: $id_grupo");

        $this->requerido($id_carril, 'id_carril');
        $this->soloNumeros($id_carril, 'id_carril');
        
        $this->requerido($id_bloque_horario, 'id_bloque_horario');
        $this->soloNumeros($id_bloque_horario, 'id_bloque_horario');

        $this->requerido($id_grupo, 'id_grupo');
        $this->soloNumeros($id_grupo, 'id_grupo');

        $this->requerido($fecha_vigencia_inicio, 'fecha_vigencia_inicio');
        
        if (!empty($fecha_vigencia_fin) && $fecha_vigencia_inicio > $fecha_vigencia_fin) {
            $this->errores['fecha_vigencia_fin'] = 'La fecha de fin debe ser mayor o igual a la fecha de inicio';
        }

        $this->requerido($activa, 'activa');
        $this->soloNumeros($activa, 'activa');
        
        $conex = $this->getConex1();
        
        try {
            $diaEspecificoValue = empty($dia_especifico) ? null : $dia_especifico;
            
            if ($excluirId !== null) {
                $sqlDuplicado = "SELECT COUNT(*) FROM asignacion_carril 
                                 WHERE id_asignacion != :id
                                 AND id_carril = :id_carril 
                                 AND id_bloque_horario = :id_bloque_horario
                                 AND (dia_especifico = :dia_especifico OR (dia_especifico IS NULL AND :dia_especifico IS NULL))
                                 AND activa = 1";
                
                $stmtDuplicado = $conex->prepare($sqlDuplicado);
                $stmtDuplicado->bindParam(':id', $excluirId, PDO::PARAM_INT);
                $stmtDuplicado->bindParam(':id_carril', $id_carril, PDO::PARAM_INT);
                $stmtDuplicado->bindParam(':id_bloque_horario', $id_bloque_horario, PDO::PARAM_INT);
                $stmtDuplicado->bindParam(':dia_especifico', $diaEspecificoValue, $diaEspecificoValue === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                
            } else {
                $sqlDuplicado = "SELECT COUNT(*) FROM asignacion_carril 
                                 WHERE id_carril = :id_carril 
                                 AND id_bloque_horario = :id_bloque_horario
                                 AND (dia_especifico = :dia_especifico OR (dia_especifico IS NULL AND :dia_especifico IS NULL))
                                 AND activa = 1";
                
                $stmtDuplicado = $conex->prepare($sqlDuplicado);
                $stmtDuplicado->bindParam(':id_carril', $id_carril, PDO::PARAM_INT);
                $stmtDuplicado->bindParam(':id_bloque_horario', $id_bloque_horario, PDO::PARAM_INT);
                $stmtDuplicado->bindParam(':dia_especifico', $diaEspecificoValue, $diaEspecificoValue === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            }
            
            error_log("SQL Duplicado: " . $sqlDuplicado);
            error_log("Parámetros: id_carril=$id_carril, id_bloque=$id_bloque_horario, dia_especifico=" . ($diaEspecificoValue ?? 'NULL'));
            
            $stmtDuplicado->execute();
            $existe = $stmtDuplicado->fetchColumn();
            
            if ($existe > 0) {
                $this->errores['asignacion'] = 'Ya existe una asignación activa para este carril, horario y día';
            }
            
        } catch (PDOException $e) {
            error_log("Error en validación de duplicados: " . $e->getMessage());
        }
        
        error_log("validarDatos() - errores: " . print_r($this->errores, true));
        
        return $this->obtenerErrores();
    }

    public function registrarAsignacion(): bool {
        error_log("=== registrarAsignacion() INICIO ===");
        return $this->registrarAsignacionP($this->datos);
    }

    public function editarAsignacion(): bool {
        error_log("=== editarAsignacion() INICIO ===");
        return $this->editarAsignacionP($this->datos);
    }

    public function eliminarAsignacion(): bool {
        $id = $this->datos['id_asignacion'] ?? 0;
        return $this->eliminarAsignacionP($id);
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
                    WHERE a.id_asignacion = :id";
            $stmt = $conex->prepare($sql);
            $stmt->execute([':id' => $id]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            error_log("=== obtenerAsignacionPorId($id) resultado: " . print_r($resultado, true));
            
            if ($resultado) {
                $resultado['fecha_vigencia_inicio'] = $resultado['fecha_vigencia_inicio'] ?? null;
                $resultado['fecha_vigencia_fin'] = $resultado['fecha_vigencia_fin'] ?? null;
            }
            
            return $resultado ? $resultado : null;
        } catch (PDOException $e) {
            error_log("Error en obtenerAsignacionPorId: " . $e->getMessage());
            return null;
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
            $sql = "SELECT 
                        g.id_grupo, 
                        g.nombre, 
                        g.descripcion, 
                        g.activo,
                        CONCAT(e.nombres, ' ', e.apellidos) as entrenador_nombre,
                        e.cedula as entrenador_cedula
                    FROM grupos_entrenamiento g
                    LEFT JOIN entrenador e ON g.id_entrenador = e.id_entrenador
                    WHERE g.id_grupo = :id";
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
            $sql = "SELECT id_carril, numero, capacidad_maxima, activo 
                    FROM carriles 
                    WHERE id_carril = :id";
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
        
    private function registrarAsignacionP(array $datos): bool {
        error_log("=== registrarAsignacionP() INICIO ===");
        error_log("Datos recibidos: " . print_r($datos, true));
        
        $conex = $this->getConex1();
        try {
            $conex->beginTransaction();
            
            $idCarril = isset($datos['id_carril']) ? (int)$datos['id_carril'] : 0;
            $idBloque = isset($datos['id_bloque_horario']) ? (int)$datos['id_bloque_horario'] : 0;
            $idGrupo = isset($datos['id_grupo']) ? (int)$datos['id_grupo'] : 0;
            $diaEsp = !empty($datos['dia_especifico']) ? $datos['dia_especifico'] : null;
            $fechaInicio = isset($datos['fecha_vigencia_inicio']) ? $datos['fecha_vigencia_inicio'] : null;
            $fechaFin = isset($datos['fecha_vigencia_fin']) && !empty($datos['fecha_vigencia_fin']) ? $datos['fecha_vigencia_fin'] : null;
            $activa = isset($datos['activa']) ? (int)$datos['activa'] : 1;
            
            error_log("Valores extraídos:");
            error_log("idCarril: $idCarril, idBloque: $idBloque, idGrupo: $idGrupo");
            error_log("diaEsp: " . ($diaEsp ?? 'NULL'));
            error_log("fechaInicio: " . ($fechaInicio ?? 'NULL'));
            error_log("fechaFin: " . ($fechaFin ?? 'NULL'));
            error_log("activa: $activa");
            
            $sql = "INSERT INTO asignacion_carril (
                id_carril, 
                id_bloque_horario, 
                id_grupo, 
                dia_especifico, 
                fecha_vigencia_inicio, 
                fecha_vigencia_fin, 
                activa
            ) VALUES (
                :id_carril, 
                :id_bloque_horario, 
                :id_grupo, 
                :dia_especifico, 
                :fecha_vigencia_inicio, 
                :fecha_vigencia_fin, 
                :activa
            )";
            
            error_log("SQL: " . $sql);
            
            $stmt = $conex->prepare($sql);
            
            $params = [
                ':id_carril' => $idCarril,
                ':id_bloque_horario' => $idBloque,
                ':id_grupo' => $idGrupo,
                ':dia_especifico' => $diaEsp,
                ':fecha_vigencia_inicio' => $fechaInicio,
                ':fecha_vigencia_fin' => $fechaFin,
                ':activa' => $activa
            ];
            
            error_log("Params: " . print_r($params, true));
            
            $resultado = $stmt->execute($params);
            
            error_log("Resultado execute(): " . ($resultado ? 'TRUE' : 'FALSE'));

            $conex->commit();
            return $resultado;
            
        } catch (PDOException $e) {
            $conex->rollBack();
            error_log("ERROR registrar Asignacion: " . $e->getMessage());
            error_log("Datos: " . print_r($datos, true));
            return false;
        }
    }

    private function editarAsignacionP(array $datos): bool {
        error_log("=== editarAsignacionP() INICIO ===");
        
        $conex = $this->getConex1();
        try {
            $conex->beginTransaction();

            $idAsignacion = isset($datos['id_asignacion']) ? (int)$datos['id_asignacion'] : 0;
            $idCarril = isset($datos['id_carril']) ? (int)$datos['id_carril'] : 0;
            $idBloque = isset($datos['id_bloque_horario']) ? (int)$datos['id_bloque_horario'] : 0;
            $idGrupo = isset($datos['id_grupo']) ? (int)$datos['id_grupo'] : 0;
            $diaEsp = !empty($datos['dia_especifico']) ? $datos['dia_especifico'] : null;
            $fechaInicio = isset($datos['fecha_vigencia_inicio']) ? $datos['fecha_vigencia_inicio'] : null;
            $fechaFin = isset($datos['fecha_vigencia_fin']) && !empty($datos['fecha_vigencia_fin']) ? $datos['fecha_vigencia_fin'] : null;
            $activa = isset($datos['activa']) ? (int)$datos['activa'] : 1;
            
            error_log("EDITAR ID: $idAsignacion, Carril: $idCarril, Bloque: $idBloque, Grupo: $idGrupo");
            
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
            
            $params = [
                ':id_asignacion' => $idAsignacion,
                ':id_carril' => $idCarril,
                ':id_bloque_horario' => $idBloque,
                ':id_grupo' => $idGrupo,
                ':dia_especifico' => $diaEsp,
                ':fecha_vigencia_inicio' => $fechaInicio,
                ':fecha_vigencia_fin' => $fechaFin,
                ':activa' => $activa
            ];
            
            error_log("Params UPDATE: " . print_r($params, true));
            
            $resultado = $stmt->execute($params);

            $conex->commit();
            error_log("Resultado UPDATE: " . ($resultado ? 'TRUE' : 'FALSE'));
            return $resultado;
            
        } catch (PDOException $e) {
            $conex->rollBack();
            error_log("ERROR editar Asignacion: " . $e->getMessage());
            error_log("Datos: " . print_r($datos, true));
            return false;
        }
    }

    private function eliminarAsignacionP(int $id): bool {
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
}
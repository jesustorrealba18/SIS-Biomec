<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class Asignacion extends Conexion {
    use ValidacionesTrait;

    private array $datos = [];

    public function __construct() {
        parent::__construct('sis_natacion'); 
    }

    public function setDatos(array $datos) {
        $this->datos = $datos;
    }
    
    public function validarDatos(array $datos, ?string $excluirId = null): array {
        $this->resetearErrores();

        $id_carril              = $datos['id_carril'] ?? '';
        $id_bloque_horario      = $datos['id_bloque_horario'] ?? '';
        $id_grupo               = $datos['id_grupo'] ?? '';
        $dia_especifico         = $datos['dia_especifico'] ?? '';
        $fecha_vigencia_inicio  = $datos['fecha_vigencia_inicio'] ?? $datos['fecha_vigente_inicio'] ?? '';
        $fecha_vigencia_fin     = $datos['fecha_vigencia_fin'] ?? $datos['fecha_vigente_fin'] ?? '';
        $activa                 = $datos['activa'] ?? '';

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

        // Verificar duplicados solo si no hay errores previos
        if (empty($this->errores)) {
            $conex = $this->getConex1();
            if ($conex) {
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
                    } else {
                        $sqlDuplicado = "SELECT COUNT(*) FROM asignacion_carril 
                                         WHERE id_carril = :id_carril 
                                         AND id_bloque_horario = :id_bloque_horario 
                                         AND (dia_especifico = :dia_especifico OR (dia_especifico IS NULL AND :dia_especifico IS NULL)) 
                                         AND activa = 1";
                        
                        $stmtDuplicado = $conex->prepare($sqlDuplicado);
                    }
                    
                    $stmtDuplicado->bindParam(':id_carril', $id_carril, PDO::PARAM_INT);
                    $stmtDuplicado->bindParam(':id_bloque_horario', $id_bloque_horario, PDO::PARAM_INT);
                    
                    if ($diaEspecificoValue === null) {
                        $stmtDuplicado->bindValue(':dia_especifico', null, PDO::PARAM_NULL);
                    } else {
                        $stmtDuplicado->bindParam(':dia_especifico', $diaEspecificoValue, PDO::PARAM_STR);
                    }
                    
                    $stmtDuplicado->execute();
                    $existe = $stmtDuplicado->fetchColumn();
                    
                    if ($existe > 0) {
                        $this->errores['asignacion'] = 'Ya existe una asignación activa para este carril, horario y día';
                    }
                    
                } catch (PDOException $e) {
                    // Manejo de excepción silencioso
                }
            }
        }

        return $this->obtenerErrores();
    }

    public function registrarAsignacion(): bool {
        return $this->registrarAsignacionP($this->datos);
    }

    public function editarAsignacion(): bool {
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
            
            if ($resultado) {
                $resultado['fecha_vigencia_inicio'] = $resultado['fecha_vigencia_inicio'] ?? null;
                $resultado['fecha_vigencia_fin'] = $resultado['fecha_vigencia_fin'] ?? null;
            }
            
            return $resultado ? $resultado : null;
        } catch (PDOException $e) {
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
            
            $resultado = $stmt->execute($params);

            $conex->commit();
            return $resultado;
            
        } catch (PDOException $e) {
            $conex->rollBack();
            return false;
        }
    }

    private function editarAsignacionP(array $datos): bool {
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
            
            $resultado = $stmt->execute($params);

            $conex->commit();
            return $resultado;
            
        } catch (PDOException $e) {
            $conex->rollBack();
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
            return false; 
        }
    }

    public function completarAsignacion($id): bool {
        $conex = $this->getConex1();
        try {
            $sql = "SELECT activa FROM asignacion_carril WHERE id_asignacion = :id";
            $stmt = $conex->prepare($sql);
            $stmt->execute([':id' => $id]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$resultado || $resultado['activa'] == 0) {
                return false;
            }

            $sql = "UPDATE asignacion_carril SET 
                    activa = 0,
                    fecha_completacion = NOW(),
                    estado = 'completada'
                    WHERE id_asignacion = :id";
            $stmt = $conex->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) { 
            return false; 
        }
    }

    public function verificarAsignacionesVencidas(): int {
        $conex = $this->getConex1();
        $contador = 0;
        try {
            $sql = "SELECT id_asignacion FROM asignacion_carril 
                    WHERE activa = 1 
                    AND fecha_vigencia_fin IS NOT NULL 
                    AND fecha_vigencia_fin < CURDATE()
                    AND (estado IS NULL OR estado != 'completada')";
            $stmt = $conex->prepare($sql);
            $stmt->execute();
            $vencidas = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($vencidas as $id) {
                if ($this->completarAsignacion($id)) {
                    $contador++;
                }
            }
            return $contador;
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function listarAsignacionesCompletadas(): array {
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
                        a.fecha_completacion,
                        a.estado,
                        c.numero as carril_numero,
                        bh.dia_semana,
                        bh.hora_inicio,
                        bh.hora_fin,
                        g.nombre as grupo_nombre
                    FROM asignacion_carril a
                    LEFT JOIN carriles c ON a.id_carril = c.id_carril
                    LEFT JOIN bloques_horarios bh ON a.id_bloque_horario = bh.id_bloque
                    LEFT JOIN grupos_entrenamiento g ON a.id_grupo = g.id_grupo
                    WHERE a.estado = 'completada'
                    ORDER BY a.fecha_completacion DESC, a.fecha_vigencia_fin DESC";
            $stmt = $conex->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function cambiarEstadoAsignacion($id, $estado): bool {
        $conex = $this->getConex1();
        try {
            $sql = "UPDATE asignacion_carril SET estado = :estado WHERE id_asignacion = :id";
            $stmt = $conex->prepare($sql);
            return $stmt->execute([':id' => $id, ':estado' => $estado]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function obtenerCarrilesDisponibles($diaSemana = null, $horaInicio = null, $horaFin = null): array {
        $conex = $this->getConex1();
        try {
            $params = [];
            $sql = "SELECT c.id_carril, c.numero, c.capacidad_maxima 
                    FROM carriles c
                    WHERE c.activo = 1 
                    AND NOT EXISTS (
                        SELECT 1 FROM asignacion_carril a
                        LEFT JOIN bloques_horarios b ON a.id_bloque_horario = b.id_bloque
                        WHERE a.id_carril = c.id_carril
                        AND a.activa = 1
                        AND (a.estado IS NULL OR a.estado != 'completada')";
            
            if ($diaSemana) {
                $sql .= " AND b.dia_semana = :diaSemana";
                $params[':diaSemana'] = $diaSemana;
            }
            if ($horaInicio && $horaFin) {
                $sql .= " AND (
                    (b.hora_inicio < :horaFin AND b.hora_fin > :horaInicio)
                )";
                $params[':horaInicio'] = $horaInicio;
                $params[':horaFin'] = $horaFin;
            }
            
            $sql .= ") ORDER BY c.numero ASC";
            
            $stmt = $conex->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Notifica a los atletas del grupo y al entrenador sobre la asignación
     */
    public function notificarAsignacionGrupo(array $asignacion): void
    {
        try {
            $id_grupo = $asignacion['id_grupo'];
            $carril_numero = $asignacion['carril_numero'] ?? 'desconocido';
            $dia_semana = $asignacion['dia_semana'] ?? 'sin día';
            $hora_inicio = $asignacion['hora_inicio'] ?? '';
            $hora_fin = $asignacion['hora_fin'] ?? '';
            $fecha_inicio = $asignacion['fecha_vigencia_inicio'] ?? '';

            error_log("=== INICIANDO notificarAsignacionGrupo ===");
            error_log("ID Grupo: $id_grupo, Carril: $carril_numero");

            $titulo = "📋 Asignación de Carril";
            $mensaje = "Tu grupo ha sido asignado al Carril {$carril_numero} los {$dia_semana} de {$hora_inicio} a {$hora_fin} (vigente desde {$fecha_inicio}).";
            $icono = "fa-bell";
            $color = "emerald";
            $enlace = "?p=asignacion";

            $conexNegocio = $this->getConex1(); // sis_natacion
            
            // ---- 1. NOTIFICAR A LOS ATLETAS DEL GRUPO ----
            $sqlAtletas = "SELECT a.id_atleta, a.cedula, CONCAT(a.nombres, ' ', a.apellidos) as nombre_completo
                           FROM grupo_atleta ga
                           INNER JOIN atletas a ON ga.id_atleta = a.id_atleta
                           WHERE ga.id_grupo = :id_grupo AND a.cedula IS NOT NULL AND a.cedula != ''";
            $stmt = $conexNegocio->prepare($sqlAtletas);
            $stmt->execute([':id_grupo' => $id_grupo]);
            $atletas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Atletas encontrados: " . count($atletas));

            $conexSeguridad = new Conexion('sis_seguridad');
            
            foreach ($atletas as $atleta) {
                if (!empty($atleta['cedula'])) {
                    $sqlUser = "SELECT id_usuario FROM usuarios WHERE cedula = :cedula AND activo = 1";
                    $stmtUser = $conexSeguridad->getConex1()->prepare($sqlUser);
                    $stmtUser->execute([':cedula' => $atleta['cedula']]);
                    $usuario = $stmtUser->fetch(PDO::FETCH_ASSOC);
                    
                    if ($usuario && !empty($usuario['id_usuario'])) {
                        $resultado = Notificacion::enviar(
                            $usuario['id_usuario'],
                            $titulo,
                            "{$atleta['nombre_completo']}, {$mensaje}",
                            $icono,
                            $color,
                            $enlace
                        );
                        error_log("Notificación enviada a atleta {$atleta['nombre_completo']} (ID usuario: {$usuario['id_usuario']}): " . ($resultado ? 'OK' : 'FALLÓ'));
                    } else {
                        error_log("Atleta sin usuario en sis_seguridad (cédula: {$atleta['cedula']})");
                    }
                }
            }

            // ---- 2. NOTIFICAR AL ENTRENADOR DEL GRUPO ----
            $sqlEntrenador = "SELECT e.id_entrenador, e.cedula, CONCAT(e.nombres, ' ', e.apellidos) as nombre_completo
                              FROM grupos_entrenamiento g
                              INNER JOIN entrenador e ON g.id_entrenador = e.id_entrenador
                              WHERE g.id_grupo = :id_grupo AND e.cedula IS NOT NULL AND e.cedula != ''";
            $stmtEnt = $conexNegocio->prepare($sqlEntrenador);
            $stmtEnt->execute([':id_grupo' => $id_grupo]);
            $entrenador = $stmtEnt->fetch(PDO::FETCH_ASSOC);
            
            error_log("Entrenador encontrado: " . ($entrenador ? $entrenador['nombre_completo'] : 'NINGUNO'));

            if ($entrenador && !empty($entrenador['cedula'])) {
                $sqlUserEnt = "SELECT id_usuario FROM usuarios WHERE cedula = :cedula AND activo = 1";
                $stmtUserEnt = $conexSeguridad->getConex1()->prepare($sqlUserEnt);
                $stmtUserEnt->execute([':cedula' => $entrenador['cedula']]);
                $usuarioEnt = $stmtUserEnt->fetch(PDO::FETCH_ASSOC);

                if ($usuarioEnt && !empty($usuarioEnt['id_usuario'])) {
                    $resultado = Notificacion::enviar(
                        $usuarioEnt['id_usuario'],
                        "📋 Nueva Asignación de Carril",
                        "Tu grupo ha sido asignado al Carril {$carril_numero} los {$dia_semana} de {$hora_inicio} a {$hora_fin}.",
                        $icono,
                        "purple",
                        $enlace
                    );
                    error_log("Notificación enviada a entrenador {$entrenador['nombre_completo']} (ID usuario: {$usuarioEnt['id_usuario']}): " . ($resultado ? 'OK' : 'FALLÓ'));
                } else {
                    error_log("Entrenador sin usuario en sis_seguridad (cédula: {$entrenador['cedula']})");
                }
            }

        } catch (PDOException $e) {
            error_log("Error notificando asignación de grupo: " . $e->getMessage());
            error_log($e->getTraceAsString());
        }
    }

    /**
     * Notifica a los atletas y al entrenador que la asignación ha finalizado
     */
    public function notificarFinAsignacion(array $asignacion): void
    {
        try {
            $id_grupo = $asignacion['id_grupo'];
            $carril_numero = $asignacion['carril_numero'] ?? 'desconocido';

            $titulo = "🏊 Asignación Finalizada";
            $mensaje = "La asignación del Carril {$carril_numero} para tu grupo ha finalizado.";
            $icono = "fa-flag-checkered";
            $color = "amber";
            $enlace = "?p=asignacion";

            $conexNegocio = $this->getConex1(); // sis_natacion
            
            // ---- 1. NOTIFICAR A LOS ATLETAS ----
            $sqlAtletas = "SELECT a.id_atleta, a.cedula, CONCAT(a.nombres, ' ', a.apellidos) as nombre_completo
                           FROM grupo_atleta ga
                           INNER JOIN atletas a ON ga.id_atleta = a.id_atleta
                           WHERE ga.id_grupo = :id_grupo AND a.cedula IS NOT NULL AND a.cedula != ''";
            $stmt = $conexNegocio->prepare($sqlAtletas);
            $stmt->execute([':id_grupo' => $id_grupo]);
            $atletas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $conexSeguridad = new Conexion('sis_seguridad');
            
            foreach ($atletas as $atleta) {
                if (!empty($atleta['cedula'])) {
                    $sqlUser = "SELECT id_usuario FROM usuarios WHERE cedula = :cedula AND activo = 1";
                    $stmtUser = $conexSeguridad->getConex1()->prepare($sqlUser);
                    $stmtUser->execute([':cedula' => $atleta['cedula']]);
                    $usuario = $stmtUser->fetch(PDO::FETCH_ASSOC);
                    
                    if ($usuario && !empty($usuario['id_usuario'])) {
                        Notificacion::enviar(
                            $usuario['id_usuario'],
                            $titulo,
                            "{$atleta['nombre_completo']}, {$mensaje}",
                            $icono,
                            $color,
                            $enlace
                        );
                    }
                }
            }

            // ---- 2. NOTIFICAR AL ENTRENADOR ----
            $sqlEntrenador = "SELECT e.id_entrenador, e.cedula, CONCAT(e.nombres, ' ', e.apellidos) as nombre_completo
                              FROM grupos_entrenamiento g
                              INNER JOIN entrenador e ON g.id_entrenador = e.id_entrenador
                              WHERE g.id_grupo = :id_grupo AND e.cedula IS NOT NULL AND e.cedula != ''";
            $stmtEnt = $conexNegocio->prepare($sqlEntrenador);
            $stmtEnt->execute([':id_grupo' => $id_grupo]);
            $entrenador = $stmtEnt->fetch(PDO::FETCH_ASSOC);

            if ($entrenador && !empty($entrenador['cedula'])) {
                $sqlUserEnt = "SELECT id_usuario FROM usuarios WHERE cedula = :cedula AND activo = 1";
                $stmtUserEnt = $conexSeguridad->getConex1()->prepare($sqlUserEnt);
                $stmtUserEnt->execute([':cedula' => $entrenador['cedula']]);
                $usuarioEnt = $stmtUserEnt->fetch(PDO::FETCH_ASSOC);

                if ($usuarioEnt && !empty($usuarioEnt['id_usuario'])) {
                    Notificacion::enviar(
                        $usuarioEnt['id_usuario'],
                        "🏊 Fin de Asignación",
                        "La asignación del Carril {$carril_numero} para tu grupo ha finalizado.",
                        $icono,
                        "purple",
                        $enlace
                    );
                }
            }

        } catch (PDOException $e) {
            error_log("Error notificando fin de asignación: " . $e->getMessage());
        }
    }

    public function obtenerUltimoIdAsignacion(): ?int
    {
        $conex = $this->getConex1();
        try {
            // Usamos lastInsertId de PDO
            $id = $conex->lastInsertId();
            if ($id && $id > 0) {
                return (int) $id;
            }
            
            // Fallback: buscar el último ID insertado
            $sql = "SELECT MAX(id_asignacion) as id FROM asignacion_carril";
            $stmt = $conex->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['id'] ? (int)$result['id'] : null;
        } catch (PDOException $e) {
            error_log("Error en obtenerUltimoIdAsignacion: " . $e->getMessage());
            return null;
        }
    }
}
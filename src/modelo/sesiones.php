<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class Sesiones extends Conexion {

    use ValidacionesTrait;

    private array $datos = [];

    public function __construct() {
        parent::__construct('sis_natacion');
    }

    public function setDatos(array $datos): void {
        $this->datos = $datos;
    }

    public function validarDatos(array $datos, $excluirId = null): array {
        $this->resetearErrores();

        $id_entrenador = $datos['id_entrenador'] ?? '';
        $this->requerido($id_entrenador, 'id_entrenador');
        if (!empty($id_entrenador)) {
            $this->soloNumeros($id_entrenador, 'id_entrenador');
        }

        $id_grupo = $datos['id_grupo'] ?? '';
        $this->requerido($id_grupo, 'id_grupo');
        if (!empty($id_grupo)) {
            $this->soloNumeros($id_grupo, 'id_grupo');
            if ((int)$id_grupo <= 0) {
                $this->agregarError('id_grupo', 'El ID del grupo debe ser un número válido mayor a 0.');
            }
        }

        if (!empty($datos['id_microciclo'])) {
            $this->soloNumeros($datos['id_microciclo'], 'id_microciclo');
            if ((int)$datos['id_microciclo'] <= 0) {
                $this->agregarError('id_microciclo', 'El ID del microciclo debe ser un número válido mayor a 0.');
            }
        }

        $fecha = $datos['fecha'] ?? '';
        $this->requerido($fecha, 'fecha');
        if (!empty($fecha)) {
            $this->fechaValida($fecha, 'fecha');
    
            if ($excluirId === null && $fecha < date('Y-m-d')) {
                $this->agregarError('fecha', 'No se permite planificar sesiones para fechas pasadas.');
            }
        }

        $tipo_sesion = $datos['tipo_sesion'] ?? '';
        $this->requerido($tipo_sesion, 'tipo_sesion');
        if (!empty($tipo_sesion)) {
            $this->enEnum($tipo_sesion, 'tipo_sesion', [
                'Tecnica', 'Resistencia', 'Velocidad', 'Recuperacion', 
                'Fuerza', 'Flexibilidad', 'Competencia'
            ]);
        }

        $duracion = $datos['duracion_minutos'] ?? '';
        $this->requerido($duracion, 'duracion_minutos');
        if (!empty($duracion)) {
            $this->soloNumeros($duracion, 'duracion_minutos');
            if ((int)$duracion < 15) {
                $this->agregarError('duracion_minutos', 'La duración mínima de la sesión debe ser de 15 minutos.');
            }
            if ((int)$duracion > 480) {
                $this->agregarError('duracion_minutos', 'La duración máxima de la sesión es de 480 minutos (8 horas).');
            }
        }

        $calentamiento = $datos['calentamiento'] ?? '';
        $this->longitud($calentamiento, 'calentamiento', 0, 100);
        if (!empty($calentamiento) && !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s.,;:()\-_\n]+$/', $calentamiento)) {
            $this->agregarError('calentamiento', 'El calentamiento contiene caracteres no permitidos.');
        }

        $vuelta_calma = $datos['vuelta_calma'] ?? '';
        $this->longitud($vuelta_calma, 'vuelta_calma', 0, 100);
        if (!empty($vuelta_calma) && !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s.,;:()\-_\n]+$/', $vuelta_calma)) {
            $this->agregarError('vuelta_calma', 'La vuelta a la calma contiene caracteres no permitidos.');
        }

        $observaciones = $datos['observaciones'] ?? '';
        $this->longitud($observaciones, 'observaciones', 0, 500);
        if (!empty($observaciones) && !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s.,;:()\-_\n]+$/', $observaciones)) {
            $this->agregarError('observaciones', 'Las observaciones contienen caracteres no permitidos.');
        }

        return $this->obtenerErrores();
    }

    public function validarDatosSerie(array $datos): array {
        $this->resetearErrores();

        $bloque = $datos['bloque'] ?? '';
        $this->requerido($bloque, 'bloque');
        if (!empty($bloque)) {
            $this->enEnum($bloque, 'bloque', ['Calentamiento', 'Principal', 'VuletaCalma']);
        }

        $id_drill = $datos['id_drill'] ?? '';
        $ejercicio_descripcion = $datos['ejercicio_descripcion'] ?? '';
        
        if (empty($id_drill) && empty($ejercicio_descripcion)) {
            $this->agregarError('ejercicio_descripcion', 'Debe seleccionar un drill o describir el ejercicio.');
        }
        
        if (!empty($id_drill)) {
            $this->soloNumeros($id_drill, 'id_drill');
        }
        
        if (!empty($ejercicio_descripcion)) {
            $this->longitud($ejercicio_descripcion, 'ejercicio_descripcion', 3, 500);
            if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s.,;:()\-_\n]+$/', $ejercicio_descripcion)) {
                $this->agregarError('ejercicio_descripcion', 'La descripción contiene caracteres no permitidos.');
            }
        }

        $repeticiones = $datos['repeticiones'] ?? '';
        $this->requerido($repeticiones, 'repeticiones');
        if (!empty($repeticiones)) {
            $this->soloNumeros($repeticiones, 'repeticiones');
            if ((int)$repeticiones < 1) {
                $this->agregarError('repeticiones', 'Las repeticiones mínimas deben ser 1.');
            }
            if ((int)$repeticiones > 100) {
                $this->agregarError('repeticiones', 'Las repeticiones máximas son 100.');
            }
        }

        $distancia = $datos['distancia_m'] ?? '';
        $this->requerido($distancia, 'distancia_m');
        if (!empty($distancia)) {
            $this->soloNumeros($distancia, 'distancia_m');
            if ((int)$distancia < 25) {
                $this->agregarError('distancia_m', 'La distancia mínima por repetición es de 25 metros.');
            }
            if ((int)$distancia > 10000) {
                $this->agregarError('distancia_m', 'La distancia máxima por repetición es de 10000 metros.');
            }
        }

        $descanso = $datos['descanso_seg'] ?? '';
        $this->requerido($descanso, 'descanso_seg');
        if (!empty($descanso)) {
            $this->soloNumeros($descanso, 'descanso_seg');
            if ((int)$descanso < 0) {
                $this->agregarError('descanso_seg', 'El descanso no puede ser menor a 0 segundos.');
            }
            if ((int)$descanso > 600) {
                $this->agregarError('descanso_seg', 'El descanso máximo es de 600 segundos (10 minutos).');
            }
        }

        $zona = $datos['zona_intensidad'] ?? '';
        $this->requerido($zona, 'zona_intensidad');
        if (!empty($zona)) {
            $this->enEnum($zona, 'zona_intensidad', ['Z1', 'Z2', 'Z3', 'Z4', 'Z5']);
        }

        if (!empty($datos['ritmo_objetivo'])) {
            $ritmo = $datos['ritmo_objetivo'];
            if (strlen($ritmo) > 20) {
                $this->agregarError('ritmo_objetivo', 'El ritmo objetivo no puede exceder los 20 caracteres.');
            }
        }

        if (!empty($datos['orden_ejecucion'])) {
            $this->soloNumeros($datos['orden_ejecucion'], 'orden_ejecucion');
        }

        return $this->obtenerErrores();
    }

    public function validarCierreSesion(array $datos, int $volumenPlanificado): array {
        $this->resetearErrores();

        $volumen_ejecutado = $datos['volumen_ejecutado'] ?? '';
        $this->requerido($volumen_ejecutado, 'volumen_ejecutado');
        if (!empty($volumen_ejecutado)) {
            $this->soloNumeros($volumen_ejecutado, 'volumen_ejecutado');
            if ((int)$volumen_ejecutado < 0) {
                $this->agregarError('volumen_ejecutado', 'El volumen ejecutado no puede ser negativo.');
            }
            if ((int)$volumen_ejecutado > $volumenPlanificado) {
                $this->agregarError('volumen_ejecutado', 'El volumen ejecutado no puede superar al volumen planificado (' . $volumenPlanificado . 'm).');
            }
        }

        if (!empty($datos['observaciones'])) {
            $observaciones = $datos['observaciones'];
            $this->longitud($observaciones, 'observaciones', 0, 5000);
            if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s.,;:()\-_\n]+$/', $observaciones)) {
                $this->agregarError('observaciones', 'Las observaciones contienen caracteres no permitidos.');
            }
        }

        return $this->obtenerErrores();
    }

    public function registrarSesion(array $datosSesion, array $series): bool {
        return $this->registrarSesionP($datosSesion, $series);
    }

    public function editarSesion(int $id_sesion, array $datosSesion, array $series): bool {
        return $this->editarSesionP($id_sesion, $datosSesion, $series);
    }

    public function completarSesion(array $datosCierre): bool {
        $conex = $this->pdo;
        try {
            $sql = "UPDATE sesiones SET 
                        volumen_ejecutado = :volumen_ejecutado,
                        estado = :estado,
                        observaciones = :observaciones,
                        fecha_modificacion = NOW()
                    WHERE id_sesion = :id_sesion";

            $stmt = $conex->prepare($sql);
            return $stmt->execute([
                ':volumen_ejecutado'=> (int)$datosCierre['volumen_ejecutado'],
                ':estado'           => $datosCierre['estado'] ?? 'Completada',
                ':observaciones'    => $datosCierre['observaciones'] ?? null,
                ':id_sesion'        => (int)$datosCierre['id_sesion']
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function cancelarSesion(int $id_sesion): bool {
        $conex = $this->pdo;
        try {
            $sql = "UPDATE sesiones SET estado = 'Cancelada', fecha_modificacion = NOW() WHERE id_sesion = :id_sesion";
            $stmt = $conex->prepare($sql);
            return $stmt->execute([':id_sesion' => $id_sesion]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function obtenerDetalleSesion(int $id_sesion): ?array {
        $conex = $this->pdo;
        try {
            $sqlBase = "SELECT s.*, g.nombre as grupo_nombre
                        FROM sesiones s
                        INNER JOIN grupos_entrenamiento g ON s.id_grupo = g.id_grupo
                        WHERE s.id_sesion = :id";
            $stmtBase = $conex->prepare($sqlBase);
            $stmtBase->execute([':id' => $id_sesion]);
            $sesion = $stmtBase->fetch(PDO::FETCH_ASSOC);

            if (!$sesion) return null;

            $sqlSeries = "SELECT sd.*, d.nombre as drill_nombre, d.estilo as drill_estilo
                          FROM series_sesion sd
                          LEFT JOIN drills d ON sd.id_drill = d.id_drill
                          WHERE sd.id_sesion = :id
                          ORDER BY sd.orden_ejecucion ASC";
            $stmtSeries = $conex->prepare($sqlSeries);
            $stmtSeries->execute([':id' => $id_sesion]);
            $sesion['series'] = $stmtSeries->fetchAll(PDO::FETCH_ASSOC);

            return $sesion;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function listarSesiones(?int $id_grupo = null, ?string $estado = null): array {
        $conex = $this->pdo;
        try {
            $sql = "SELECT s.*, g.nombre as grupo_nombre
                    FROM sesiones s
                    INNER JOIN grupos_entrenamiento g ON s.id_grupo = g.id_grupo
                    WHERE 1=1";
            
            $params = [];
            
            if ($id_grupo) {
                $sql .= " AND s.id_grupo = :id_grupo";
                $params[':id_grupo'] = $id_grupo;
            }
            
            if ($estado) {
                $sql .= " AND s.estado = :estado";
                $params[':estado'] = $estado;
            }
            
            $sql .= " ORDER BY s.fecha DESC, s.id_sesion DESC";
            
            $stmt = $conex->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function obtenerSeriesSesion(int $id_sesion): array {
        $conex = $this->pdo;
        try {
            $sql = "SELECT sd.*, d.nombre as drill_nombre, d.estilo as drill_estilo
                    FROM series_sesion sd
                    LEFT JOIN drills d ON sd.id_drill = d.id_drill
                    WHERE sd.id_sesion = :id_sesion
                    ORDER BY sd.orden_ejecucion ASC";
            $stmt = $conex->prepare($sql);
            $stmt->execute([':id_sesion' => $id_sesion]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function listarMicrociclos(): array {
        $conex = $this->pdo;
        try {
            $sql = "SELECT id_microciclo, nombre FROM microciclos ORDER BY nombre";
            $stmt = $conex->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

     //Listar sesiones para el módulo de Marcas (NO MODIFICAR)
    
    public function listarSesionesSelectMarca(): array {
        try {
            $sql = "SELECT s.id_sesion, s.fecha, s.tipo_sesion, g.nombre AS grupo_nombre 
                    FROM sesiones s 
                    INNER JOIN grupos_entrenamiento g ON s.id_grupo = g.id_grupo
                    WHERE s.estado IN ('Completada', 'Parcial')
                    ORDER BY s.fecha DESC LIMIT 30";
            return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error listando sesiones para marcas: " . $e->getMessage());
            return [];
        }
    }

    public function validarDatosSesion(array $datos): array {
        return $this->validarDatos($datos);
    }

    private function registrarSesionP(array $datosSesion, array $series): bool {
        $conex = $this->pdo;
        try {
            $conex->beginTransaction();

            $volumen_calentamiento = 0;
            $volumen_principal = 0;
            $volumen_vuelta_calma = 0;

            foreach ($series as &$serie) {
                $volumen_serie = (int)$serie['repeticiones'] * (int)$serie['distancia_m'];
                $serie['volumen_calculado'] = $volumen_serie;

                if ($serie['bloque'] === 'Calentamiento') {
                    $volumen_calentamiento += $volumen_serie;
                } elseif ($serie['bloque'] === 'Principal') {
                    $volumen_principal += $volumen_serie;
                } elseif ($serie['bloque'] === 'VuletaCalma') {
                    $volumen_vuelta_calma += $volumen_serie;
                }
            }

            $volumen_planificado = $volumen_calentamiento + $volumen_principal + $volumen_vuelta_calma;

            $sql = "INSERT INTO sesiones (
                        id_microciclo, id_grupo, fecha, tipo_sesion, 
                        calentamiento, vuelta_calma, 
                        volumen_planificado, observaciones, estado, id_entrenador, duracion_minutos
                    ) VALUES (
                        :id_microciclo, :id_grupo, :fecha, :tipo_sesion, 
                        :calentamiento, :vuelta_calma, 
                        :volumen_planificado, :observaciones, 'Planificada', :id_entrenador, :duracion_minutos
                    )";

            $stmt = $conex->prepare($sql);
            
            $params = [
                ':id_microciclo'      => !empty($datosSesion['id_microciclo']) ? (int)$datosSesion['id_microciclo'] : null,
                ':id_grupo'           => (int)$datosSesion['id_grupo'],
                ':fecha'              => $datosSesion['fecha'],
                ':tipo_sesion'        => $datosSesion['tipo_sesion'],
                ':calentamiento'      => $datosSesion['calentamiento'] ?? null,
                ':vuelta_calma'       => $datosSesion['vuelta_calma'] ?? null,
                ':volumen_planificado'=> $volumen_planificado,
                ':observaciones'      => $datosSesion['observaciones'] ?? null,
                ':id_entrenador'      => (int)$datosSesion['id_entrenador'],
                ':duracion_minutos'   => $datosSesion['duracion_minutos'] ?? null
            ];
            
            if (!$stmt->execute($params)) {
                throw new PDOException("Error en INSERT");
            }

            $id_sesion = (int)$conex->lastInsertId();

            $sqlSerie = "INSERT INTO series_sesion (
                            id_sesion, orden_ejecucion, bloque, id_drill, 
                            ejercicio_descripcion, repeticiones, distancia_m, 
                            descanso_seg, zona_intensidad, ritmo_objetivo
                        ) VALUES (
                            :id_sesion, :orden, :bloque, :id_drill, 
                            :descripcion, :repeticiones, :distancia, 
                            :descanso, :zona, :ritmo
                        )";
            
            $stmtSerie = $conex->prepare($sqlSerie);
            
            $orden = 1;
            foreach ($series as $s) {
                $paramsSerie = [
                    ':id_sesion'    => $id_sesion,
                    ':orden'        => $orden++, 
                    ':bloque'       => $s['bloque'],
                    ':id_drill'     => !empty($s['id_drill']) ? (int)$s['id_drill'] : null,
                    ':descripcion'  => empty($s['id_drill']) ? ($s['ejercicio_descripcion'] ?? null) : null,
                    ':repeticiones' => (int)$s['repeticiones'],
                    ':distancia'    => (int)$s['distancia_m'],
                    ':descanso'     => (int)$s['descanso_seg'],
                    ':zona'         => $s['zona_intensidad'],
                    ':ritmo'        => $s['ritmo_objetivo'] ?? null
                ];
                $stmtSerie->execute($paramsSerie);
            }

            $conex->commit();
            return true;
            
        } catch (PDOException $e) {
            $conex->rollBack();
            return false;
        }
    }

    private function editarSesionP(int $id_sesion, array $datosSesion, array $series): bool {
        $conex = $this->pdo;
        try {
            $conex->beginTransaction();

            $volumen_calentamiento = 0;
            $volumen_principal = 0;
            $volumen_vuelta_calma = 0;

            foreach ($series as &$serie) {
                $volumen_serie = (int)$serie['repeticiones'] * (int)$serie['distancia_m'];
                $serie['volumen_calculado'] = $volumen_serie;

                if ($serie['bloque'] === 'Calentamiento') {
                    $volumen_calentamiento += $volumen_serie;
                } elseif ($serie['bloque'] === 'Principal') {
                    $volumen_principal += $volumen_serie;
                } elseif ($serie['bloque'] === 'VuletaCalma') {
                    $volumen_vuelta_calma += $volumen_serie;
                }
            }

            $volumen_planificado = $volumen_calentamiento + $volumen_principal + $volumen_vuelta_calma;

            $sql = "UPDATE sesiones SET 
                        id_microciclo = :id_microciclo, 
                        id_grupo = :id_grupo, 
                        fecha = :fecha, 
                        tipo_sesion = :tipo_sesion, 
                        calentamiento = :calentamiento, 
                        vuelta_calma = :vuelta_calma, 
                        volumen_planificado = :volumen_planificado, 
                        observaciones = :observaciones,
                        id_entrenador = :id_entrenador,
                        duracion_minutos = :duracion_minutos,
                        fecha_modificacion = NOW()
                    WHERE id_sesion = :id_sesion";

            $stmt = $conex->prepare($sql);
            $stmt->execute([
                ':id_microciclo'      => !empty($datosSesion['id_microciclo']) ? (int)$datosSesion['id_microciclo'] : null,
                ':id_grupo'           => (int)$datosSesion['id_grupo'],
                ':fecha'              => $datosSesion['fecha'],
                ':tipo_sesion'        => $datosSesion['tipo_sesion'],
                ':calentamiento'      => $datosSesion['calentamiento'] ?? null,
                ':vuelta_calma'       => $datosSesion['vuelta_calma'] ?? null,
                ':volumen_planificado'=> $volumen_planificado,
                ':observaciones'      => $datosSesion['observaciones'] ?? null,
                ':id_entrenador'      => (int)$datosSesion['id_entrenador'],
                ':duracion_minutos'   => $datosSesion['duracion_minutos'] ?? null,
                ':id_sesion'          => $id_sesion
            ]);

            $sqlDelete = "DELETE FROM series_sesion WHERE id_sesion = :id_sesion";
            $stmtDelete = $conex->prepare($sqlDelete);
            $stmtDelete->execute([':id_sesion' => $id_sesion]);

            $sqlSerie = "INSERT INTO series_sesion (
                            id_sesion, orden_ejecucion, bloque, id_drill, 
                            ejercicio_descripcion, repeticiones, distancia_m, 
                            descanso_seg, zona_intensidad, ritmo_objetivo
                        ) VALUES (
                            :id_sesion, :orden, :bloque, :id_drill, 
                            :descripcion, :repeticiones, :distancia, 
                            :descanso, :zona, :ritmo
                        )";
            $stmtSerie = $conex->prepare($sqlSerie);

            $orden = 1;
            foreach ($series as $s) {
                $stmtSerie->execute([
                    ':id_sesion'    => $id_sesion,
                    ':orden'        => $orden++, 
                    ':bloque'       => $s['bloque'],
                    ':id_drill'     => !empty($s['id_drill']) ? (int)$s['id_drill'] : null,
                    ':descripcion'  => empty($s['id_drill']) ? ($s['ejercicio_descripcion'] ?? null) : null,
                    ':repeticiones' => (int)$s['repeticiones'],
                    ':distancia'    => (int)$s['distancia_m'],
                    ':descanso'     => (int)$s['descanso_seg'],
                    ':zona'         => $s['zona_intensidad'],
                    ':ritmo'        => $s['ritmo_objetivo'] ?? null
                ]);
            }

            $conex->commit();
            return true;
        } catch (PDOException $e) {
            $conex->rollBack();
            return false;
        }
    }
}
<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;


class Evento extends Conexion {

    use ValidacionesTrait;

    public function __construct() {
        parent::__construct('sis_natacion');
    }

    // =====================================================================
    // 1. MOTOR DE VALIDACION (Utiliza los metodos de ValidacionesTrait)
    // =====================================================================

    public function validarDatosEvento(array $datos): array {
        $this->resetearErrores();

        $this->requerido($datos['nombre'] ?? '', 'nombre');
        $this->longitud($datos['nombre'] ?? '', 'nombre', 2, 200);

        $this->requerido($datos['fecha_inicio'] ?? '', 'fecha_inicio');
        $this->fechaValida($datos['fecha_inicio'] ?? '', 'fecha_inicio');

        $fechaFin = $datos['fecha_fin'] ?? '';
        if (!empty($fechaFin)) {
            $this->fechaValida($fechaFin, 'fecha_fin');
            if (!empty($datos['fecha_inicio']) && $fechaFin < $datos['fecha_inicio']) {
                $this->agregarError('fecha_fin', 'La fecha de fin no puede ser anterior a la fecha de inicio.');
            }
        }

        $this->requerido($datos['tipo'] ?? '', 'tipo');
        $this->enEnum($datos['tipo'] ?? '', 'tipo', ['Regional', 'Nacional', 'Internacional', 'Selectivo', 'Control']);

        $nivel = $datos['nivel'] ?? '';
        if (!empty($nivel)) {
            $this->enEnum($nivel, 'nivel', ['A', 'B', 'C']);
        }

        $this->requerido($datos['estado'] ?? '', 'estado');
        $this->enEnum($datos['estado'] ?? '', 'estado', ['Planificado', 'Inscrito', 'En Progreso', 'Finalizado', 'Cancelado']);

        $this->longitud($datos['sede'] ?? '', 'sede', 0, 200);
        $this->longitud($datos['organizador'] ?? '', 'organizador', 0, 200);

        return $this->obtenerErrores();
    }

    public function validarDatosMeta(array $datos): array {
        $this->resetearErrores();

        $this->requerido($datos['id_atleta'] ?? '', 'id_atleta');
        $this->soloNumeros($datos['id_atleta'] ?? '', 'id_atleta');

        $this->requerido($datos['estilo'] ?? '', 'estilo');
        $this->enEnum($datos['estilo'] ?? '', 'estilo', ['Libre', 'Espalda', 'Braza', 'Mariposa', 'Combinado']);

        $this->requerido($datos['distancia'] ?? '', 'distancia');
        $this->soloNumeros($datos['distancia'] ?? '', 'distancia');

        $this->requerido($datos['marca_objetivo_seg'] ?? '', 'marca_objetivo_seg');
        if (!empty($datos['marca_objetivo_seg']) && (float)$datos['marca_objetivo_seg'] <= 0) {
            $this->agregarError('marca_objetivo_seg', 'La marca objetivo debe ser mayor a 0.');
        }

        return $this->obtenerErrores();
    }

    // =====================================================================
    // 2. MAQUINA DE ESTADOS DEL EVENTO
    // =====================================================================

    public function actualizarEstadoEvento(int $id, string $nuevo_estado): bool {
        $conex = $this->pdo;
        try {
            $sqlActual = "SELECT estado FROM eventos WHERE id_evento = :id";
            $stmtActual = $conex->prepare($sqlActual);
            $stmtActual->execute([':id' => $id]);
            $row = $stmtActual->fetch(PDO::FETCH_ASSOC);

            if (!$row) return false;

            $estadoActual = $row['estado'];

            $transiciones = [
                'Planificado'  => ['Inscrito', 'Cancelado'],
                'Inscrito'     => ['En Progreso', 'Cancelado'],
                'En Progreso'  => ['Finalizado', 'Cancelado'],
                'Cancelado'    => ['Planificado'],
            ];

            if (!isset($transiciones[$estadoActual]) || !in_array($nuevo_estado, $transiciones[$estadoActual])) {
                return false;
            }

            $sql = "UPDATE eventos SET estado = :nuevo_estado WHERE id_evento = :id";
            $stmt = $conex->prepare($sql);
            return $stmt->execute([
                ':nuevo_estado' => $nuevo_estado,
                ':id' => $id
            ]);
        } catch (PDOException $e) {
            error_log("Error en transicion de estado de evento: " . $e->getMessage());
            return false;
        }
    }

    // =====================================================================
    // 3. OPERACIONES DE BASE DE DATOS (Transacciones)
    // =====================================================================

    public function registrarEvento(array $datos): bool {
        $conex = $this->pdo;
        try {
            $conex->beginTransaction();

            $sql = "INSERT INTO eventos (nombre, fecha_inicio, fecha_fin, sede, tipo, nivel, organizador, estado, observaciones)
                    VALUES (:nombre, :fecha_inicio, :fecha_fin, :sede, :tipo, :nivel, :organizador, :estado, :obs)";

            $stmt = $conex->prepare($sql);
            $stmt->execute([
                ':nombre'       => $datos['nombre'],
                ':fecha_inicio' => $datos['fecha_inicio'],
                ':fecha_fin'    => !empty($datos['fecha_fin']) ? $datos['fecha_fin'] : null,
                ':sede'         => $datos['sede'] ?? null,
                ':tipo'         => $datos['tipo'],
                ':nivel'        => !empty($datos['nivel']) ? $datos['nivel'] : null,
                ':organizador'  => $datos['organizador'] ?? null,
                ':estado'       => $datos['estado'] ?? 'Planificado',
                ':obs'          => $datos['observaciones'] ?? null
            ]);

            $id_evento = (int)$conex->lastInsertId();

            if (!empty($datos['tiempos_corte']) && is_array($datos['tiempos_corte'])) {
                $sqlTC = "INSERT INTO tiempos_corte_evento (id_evento, id_categoria, estilo, distancia, tiempo_corte_segundos)
                          VALUES (:id_evento, :id_categoria, :estilo, :distancia, :tiempo)";
                $stmtTC = $conex->prepare($sqlTC);

                foreach ($datos['tiempos_corte'] as $tc) {
                    if (!empty($tc['id_categoria']) && !empty($tc['estilo']) && !empty($tc['distancia'])) {
                        $stmtTC->execute([
                            ':id_evento'  => $id_evento,
                            ':id_categoria' => (int)$tc['id_categoria'],
                            ':estilo'     => $tc['estilo'],
                            ':distancia'  => (int)$tc['distancia'],
                            ':tiempo'     => !empty($tc['tiempo_corte_segundos']) ? (float)$tc['tiempo_corte_segundos'] : null
                        ]);
                    }
                }
            }

            $conex->commit();
            return true;
        } catch (PDOException $e) {
            $conex->rollBack();
            error_log("Error en transaccion de registro de evento: " . $e->getMessage());
            return false;
        }
    }

    public function editarEvento(array $datos): bool {
        $conex = $this->pdo;
        try {
            $conex->beginTransaction();

            $sql = "UPDATE eventos SET
                        nombre = :nombre, fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin,
                        sede = :sede, tipo = :tipo, nivel = :nivel, organizador = :organizador,
                        observaciones = :obs
                    WHERE id_evento = :id_evento";

            $stmt = $conex->prepare($sql);
            $stmt->execute([
                ':nombre'       => $datos['nombre'],
                ':fecha_inicio' => $datos['fecha_inicio'],
                ':fecha_fin'    => !empty($datos['fecha_fin']) ? $datos['fecha_fin'] : null,
                ':sede'         => $datos['sede'] ?? null,
                ':tipo'         => $datos['tipo'],
                ':nivel'        => !empty($datos['nivel']) ? $datos['nivel'] : null,
                ':organizador'  => $datos['organizador'] ?? null,
                ':obs'          => $datos['observaciones'] ?? null,
                ':id_evento'    => (int)$datos['id_evento']
            ]);

            $sqlDel = "DELETE FROM tiempos_corte_evento WHERE id_evento = :id_evento";
            $stmtDel = $conex->prepare($sqlDel);
            $stmtDel->execute([':id_evento' => (int)$datos['id_evento']]);

            if (!empty($datos['tiempos_corte']) && is_array($datos['tiempos_corte'])) {
                $sqlTC = "INSERT INTO tiempos_corte_evento (id_evento, id_categoria, estilo, distancia, tiempo_corte_segundos)
                          VALUES (:id_evento, :id_categoria, :estilo, :distancia, :tiempo)";
                $stmtTC = $conex->prepare($sqlTC);

                foreach ($datos['tiempos_corte'] as $tc) {
                    if (!empty($tc['id_categoria']) && !empty($tc['estilo']) && !empty($tc['distancia'])) {
                        $stmtTC->execute([
                            ':id_evento'    => (int)$datos['id_evento'],
                            ':id_categoria' => (int)$tc['id_categoria'],
                            ':estilo'       => $tc['estilo'],
                            ':distancia'    => (int)$tc['distancia'],
                            ':tiempo'       => !empty($tc['tiempo_corte_segundos']) ? (float)$tc['tiempo_corte_segundos'] : null
                        ]);
                    }
                }
            }

            $conex->commit();
            return true;
        } catch (PDOException $e) {
            $conex->rollBack();
            error_log("Error en transaccion de edicion de evento: " . $e->getMessage());
            return false;
        }
    }

    public function registrarMetasLote(int $id_evento, array $metas): bool {
        $conex = $this->pdo;
        try {
            $conex->beginTransaction();

            $sqlPB = "SELECT MIN(tiempo_final_seg) as pb
                      FROM marcas
                      WHERE id_atleta = :id_atleta AND estilo = :estilo
                      AND distancia_m = :distancia AND estado = 'Activo'";
            $stmtPB = $conex->prepare($sqlPB);

            $sqlDel = "DELETE FROM metas_competitivas
                       WHERE id_evento = :id_evento
                       AND id_atleta = :id_atleta AND estilo = :estilo AND distancia = :distancia";
            $stmtDel = $conex->prepare($sqlDel);

            $sqlInsert = "INSERT INTO metas_competitivas (id_evento, id_atleta, estilo, distancia, marca_objetivo_seg, pb_actual_seg, diferencia_pct)
                          VALUES (:id_evento, :id_atleta, :estilo, :distancia, :objetivo, :pb, :dif)";
            $stmtInsert = $conex->prepare($sqlInsert);

            foreach ($metas as $meta) {
                $idAtleta = (int)$meta['id_atleta'];
                $estilo = $meta['estilo'];
                $distancia = (int)$meta['distancia'];
                $objetivo = (float)$meta['marca_objetivo_seg'];

                $stmtDel->execute([
                    ':id_evento' => $id_evento,
                    ':id_atleta' => $idAtleta,
                    ':estilo'    => $estilo,
                    ':distancia' => $distancia
                ]);

                $stmtPB->execute([
                    ':id_atleta' => $idAtleta,
                    ':estilo'    => $estilo,
                    ':distancia' => $distancia
                ]);
                $rowPB = $stmtPB->fetch(PDO::FETCH_ASSOC);

                $pbActual = null;
                $diferenciaPct = null;

                if (!empty($rowPB['pb'])) {
                    $pbActual = (float)$rowPB['pb'];
                    if ($pbActual > 0) {
                        $diferenciaPct = round((($objetivo - $pbActual) / $pbActual) * 100, 2);
                    }
                }

                $stmtInsert->execute([
                    ':id_evento' => $id_evento,
                    ':id_atleta' => $idAtleta,
                    ':estilo'    => $estilo,
                    ':distancia' => $distancia,
                    ':objetivo'  => $objetivo,
                    ':pb'        => $pbActual,
                    ':dif'       => $diferenciaPct
                ]);
            }

            $conex->commit();
            return true;
        } catch (PDOException $e) {
            $conex->rollBack();
            error_log("Error en transaccion de metas competitivas: " . $e->getMessage());
            return false;
        }
    }

    public function inscribirAtletasLote(int $id_evento, array $atletas_ids): bool {
        $conex = $this->pdo;
        try {
            $conex->beginTransaction();

            $sql = "INSERT IGNORE INTO evento_inscripcion (id_evento, id_atleta)
                    VALUES (:id_evento, :id_atleta)";
            $stmt = $conex->prepare($sql);

            foreach ($atletas_ids as $id_atleta) {
                $stmt->execute([
                    ':id_evento' => $id_evento,
                    ':id_atleta' => (int)$id_atleta
                ]);
            }

            $conex->commit();
            return true;
        } catch (PDOException $e) {
            $conex->rollBack();
            error_log("Error en transaccion de inscripciones: " . $e->getMessage());
            return false;
        }
    }

    // =====================================================================
    // 4. METODOS DE CONSULTA
    // =====================================================================

    public function listarEventos(?string $estado = null, ?string $tipo = null): array {
        $conex = $this->pdo;
        try {
            $sql = "SELECT e.*,
                           (SELECT COUNT(*) FROM metas_competitivas mc WHERE mc.id_evento = e.id_evento) as total_metas,
                           (SELECT COUNT(*) FROM evento_inscripcion ei WHERE ei.id_evento = e.id_evento) as total_inscritos
                    FROM eventos e
                    WHERE (:estado_n IS NULL OR e.estado = :estado_v)
                      AND (:tipo_n IS NULL OR e.tipo = :tipo_v)
                    ORDER BY e.fecha_inicio ASC, e.id_evento DESC";
            $stmt = $conex->prepare($sql);
            $stmt->execute([
                ':estado_n' => $estado,
                ':estado_v' => $estado,
                ':tipo_n'   => $tipo,
                ':tipo_v'   => $tipo
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function obtenerDetallePorId(int $id_evento): ?array {
        $conex = $this->pdo;
        try {
            $sqlBase = "SELECT * FROM eventos WHERE id_evento = :id";
            $stmtBase = $conex->prepare($sqlBase);
            $stmtBase->execute([':id' => $id_evento]);
            $evento = $stmtBase->fetch(PDO::FETCH_ASSOC);

            if (!$evento) return null;

            $sqlTC = "SELECT tc.*, c.nombre as categoria_nombre
                      FROM tiempos_corte_evento tc
                      INNER JOIN categorias_feveda c ON tc.id_categoria = c.id_categoria
                      WHERE tc.id_evento = :id
                      ORDER BY c.edad_minima ASC, tc.estilo ASC, tc.distancia ASC";
            $stmtTC = $conex->prepare($sqlTC);
            $stmtTC->execute([':id' => $id_evento]);
            $evento['tiempos_corte'] = $stmtTC->fetchAll(PDO::FETCH_ASSOC);

            $sqlMetas = "SELECT mc.*, CONCAT(a.nombres, ' ', a.apellidos) as nombre_atleta
                         FROM metas_competitivas mc
                         INNER JOIN atletas a ON mc.id_atleta = a.id_atleta
                         WHERE mc.id_evento = :id
                         ORDER BY a.apellidos ASC";
            $stmtMetas = $conex->prepare($sqlMetas);
            $stmtMetas->execute([':id' => $id_evento]);
            $evento['metas'] = $stmtMetas->fetchAll(PDO::FETCH_ASSOC);

            $sqlInsc = "SELECT ei.id_inscripcion, ei.id_atleta, a.cedula,
                               CONCAT(a.nombres, ' ', a.apellidos) as nombre_atleta
                        FROM evento_inscripcion ei
                        INNER JOIN atletas a ON ei.id_atleta = a.id_atleta
                        WHERE ei.id_evento = :id
                        ORDER BY a.apellidos ASC";
            $stmtInsc = $conex->prepare($sqlInsc);
            $stmtInsc->execute([':id' => $id_evento]);
            $evento['inscripciones'] = $stmtInsc->fetchAll(PDO::FETCH_ASSOC);

            return $evento;
        } catch (PDOException $e) {
            error_log("Error en obtenerDetallePorId de evento: " . $e->getMessage());
            return null;
        }
    }

    public function obtenerEventosProximos(int $dias = 14): array {
        $conex = $this->pdo;
        try {
            $sql = "SELECT id_evento, nombre, fecha_inicio, fecha_fin, tipo, sede, estado
                    FROM eventos
                    WHERE estado IN ('Planificado', 'Inscrito')
                      AND fecha_inicio BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :dias DAY)
                    ORDER BY fecha_inicio ASC";
            $stmt = $conex->prepare($sql);
            $stmt->execute([':dias' => $dias]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function obtenerAtletasConCompetenciaProxima(int $dias = 14): array {
        $conex = $this->pdo;
        try {
            $sql = "SELECT DISTINCT ei.id_atleta, e.nombre as evento_nombre, e.fecha_inicio
                    FROM evento_inscripcion ei
                    INNER JOIN eventos e ON ei.id_evento = e.id_evento
                    WHERE e.estado IN ('Planificado', 'Inscrito')
                      AND e.fecha_inicio BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :dias DAY)
                    ORDER BY e.fecha_inicio ASC";
            $stmt = $conex->prepare($sql);
            $stmt->execute([':dias' => $dias]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function obtenerEventosCalendario(?int $mes = null, ?int $anio = null): array {
        $conex = $this->pdo;
        try {
            $sql = "SELECT id_evento, nombre as title, fecha_inicio as start,
                           COALESCE(fecha_fin, fecha_inicio) as end, tipo
                    FROM eventos
                    WHERE estado != 'Cancelado'
                      AND (MONTH(fecha_inicio) = :mes_v OR :mes_n IS NULL)
                      AND (YEAR(fecha_inicio) = :anio_v OR :anio_n IS NULL)
                    ORDER BY fecha_inicio ASC";
            $stmt = $conex->prepare($sql);
            $stmt->execute([
                ':mes_n'  => $mes,
                ':mes_v'  => $mes,
                ':anio_n' => $anio,
                ':anio_v' => $anio
            ]);
            $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $colores = [
                'Regional'      => '#3b82f6',
                'Nacional'      => '#10b981',
                'Internacional' => '#f59e0b',
                'Selectivo'     => '#f97316',
                'Control'       => '#6b7280'
            ];

            foreach ($eventos as &$ev) {
                $ev['color'] = $colores[$ev['tipo']] ?? '#6366f1';
                $ev['url'] = '?p=eventos&accion=obtenerDetalle&id=' . $ev['id_evento'];
                if ($ev['start'] === $ev['end']) {
                    unset($ev['end']);
                }
            }

            return $eventos;
        } catch (PDOException $e) {
            return [];
        }
    }

    public function obtenerEventosComoObjetivo(): array {
        $conex = $this->pdo;
        try {
            $sql = "SELECT id_evento, nombre, fecha_inicio, tipo, nivel
                    FROM eventos
                    WHERE estado = 'Planificado'
                      AND fecha_inicio IS NOT NULL
                    ORDER BY fecha_inicio ASC";
            $stmt = $conex->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function obtenerTiemposCorte(int $id_evento): array {
        $conex = $this->pdo;
        try {
            $sql = "SELECT tc.*, c.nombre as categoria_nombre
                    FROM tiempos_corte_evento tc
                    INNER JOIN categorias_feveda c ON tc.id_categoria = c.id_categoria
                    WHERE tc.id_evento = :id
                    ORDER BY c.edad_minima ASC, tc.estilo ASC, tc.distancia ASC";
            $stmt = $conex->prepare($sql);
            $stmt->execute([':id' => $id_evento]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function listarInscripciones(int $id_evento): array {
        $conex = $this->pdo;
        try {
            $sql = "SELECT ei.id_inscripcion, ei.id_atleta, a.cedula,
                           CONCAT(a.nombres, ' ', a.apellidos) as nombre_atleta,
                           TIMESTAMPDIFF(YEAR, a.fecha_nacimiento, CURDATE()) as edad,
                           cat.nombre as categoria_nombre
                    FROM evento_inscripcion ei
                    INNER JOIN atletas a ON ei.id_atleta = a.id_atleta
                    LEFT JOIN categorias_feveda cat ON a.id_categoria = cat.id_categoria
                    WHERE ei.id_evento = :id
                    ORDER BY a.apellidos ASC";
            $stmt = $conex->prepare($sql);
            $stmt->execute([':id' => $id_evento]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function eliminarInscripcion(int $id_evento, int $id_atleta): bool {
        $conex = $this->pdo;
        try {
            $sql = "DELETE FROM evento_inscripcion WHERE id_evento = :id_evento AND id_atleta = :id_atleta";
            $stmt = $conex->prepare($sql);
            return $stmt->execute([
                ':id_evento' => $id_evento,
                ':id_atleta' => $id_atleta
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function eliminarMeta(int $id_meta): bool {
        $conex = $this->pdo;
        try {
            $sql = "DELETE FROM metas_competitivas WHERE id_meta = :id_meta";
            $stmt = $conex->prepare($sql);
            return $stmt->execute([':id_meta' => $id_meta]);
        } catch (PDOException $e) {
            return false;
        }
    }

     // Funcion para el modulo de Marcas No tocar
    public function listarEventosSelectMarca(): array {
        // Eventos activos o recientes
         try {
        $sql = "SELECT id_evento, nombre, nivel ,tipo, sede, fecha_inicio, fecha_fin
                FROM eventos 
                WHERE estado IN ('En Progreso', 'Finalizado')
                ORDER BY fecha_inicio DESC";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
         } catch (PDOException $e) {
            return [];
        }
    }

}

<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class Lesion extends Conexion {
    use ValidacionesTrait;
    use AutoBinderTrait;

    
    private array $datos = [];
    private array $camposPermitidos = [
        'id_lesion', 'id_atleta', 'zona_anatomica', 'lado', 'tipo',
        'nivel_molestia', 'diagnostico', 'tratamiento', 'fecha_inicio',
        'fecha_estimada_recup', 'estado', 'profesional', 'observaciones',
        'activo', 'motivo_eliminacion'
    ];

   
    private function setAtributos(array $payload): void {
        foreach ($this->camposPermitidos as $campo) {
            if (isset($payload[$campo])) {
                if (is_array($payload[$campo])) {
                    $this->datos[$campo] = $payload[$campo];
                } elseif ($payload[$campo] !== '') {
                    $this->datos[$campo] = trim($payload[$campo]);
                } else {
                    $this->datos[$campo] = null;
                }
            } else {
                $this->datos[$campo] = null;
            }
        }
        if (!isset($this->datos['activo'])) {
            $this->datos['activo'] = 1;
        }
    }

    public function getCampo(string $clave) {
        return $this->datos[$clave] ?? null;
    }

    private function validarAtributosInternos(bool $paraActualizacion = false): bool {
        $this->resetearErrores();

        if (!$paraActualizacion || isset($this->datos['id_atleta'])) {
            $this->requerido((string)($this->datos['id_atleta'] ?? ''), 'id_atleta');
        }
        if (!$paraActualizacion || isset($this->datos['fecha_inicio'])) {
            $this->requerido((string)($this->datos['fecha_inicio'] ?? ''), 'fecha_inicio');
        }
        if (!$paraActualizacion || isset($this->datos['zona_anatomica'])) {
            $this->requerido((string)($this->datos['zona_anatomica'] ?? ''), 'zona_anatomica');
        }
        if (!$paraActualizacion || isset($this->datos['tipo'])) {
            $this->requerido((string)($this->datos['tipo'] ?? ''), 'tipo');
        }

        $valor = $this->datos['nivel_molestia'] ?? null;
        if ($valor === null || $valor === '') {
            $this->agregarError('nivel_molestia', 'El nivel de molestia es obligatorio.');
        } elseif (!is_numeric($valor) || $valor < 1 || $valor > 10) {
            $this->agregarError('nivel_molestia', 'Debe ser un número entre 1 y 10.');
        }

      

        if (!empty($this->datos['fecha_inicio'])) {
            $fechaInicio = $this->datos['fecha_inicio'];
            $hoy = date('Y-m-d');
            $haceUnMes = date('Y-m-d', strtotime('-1 month'));

            if ($fechaInicio > $hoy) {
                $this->agregarError('fecha_inicio', 'La fecha de inicio no puede ser futura.');
            } elseif ($fechaInicio < $haceUnMes) {
                $this->agregarError('fecha_inicio', 'El sistema solo permite registrar lesiones con hasta 1 mes de antigüedad.');
            }
        }

        if (!empty($this->datos['fecha_estimada_recup']) && !empty($this->datos['fecha_inicio'])
            && $this->datos['fecha_estimada_recup'] < $this->datos['fecha_inicio']) {
            $this->agregarError('fecha_estimada_recup', 'No puede ser anterior a la fecha de inicio.');
        }

        $zonasValidas = ['Hombro','Rodilla','Espalda','Codo','Tobillo','Cervical','Lumbar','Muslo','Gemelo','Pie','Otra'];
        if (!empty($this->datos['zona_anatomica']) && !in_array($this->datos['zona_anatomica'], $zonasValidas)) {
            $this->agregarError('zona_anatomica', 'Zona anatómica no válida.');
        }

        $ladosValidos = ['Izquierdo','Derecho','Bilateral'];
        if (!empty($this->datos['lado']) && !in_array($this->datos['lado'], $ladosValidos)) {
            $this->agregarError('lado', 'Lado no válido.');
        }

        $tiposValidos = ['Sobreuso','Aguda','Recidiva'];
        if (!empty($this->datos['tipo']) && !in_array($this->datos['tipo'], $tiposValidos)) {
            $this->agregarError('tipo', 'Tipo de lesión no válido.');
        }

        $estadosClinicos = ['Activa','EnRehabilitacion','Recuperada','Cronica'];
        if (!empty($this->datos['estado']) && !in_array($this->datos['estado'], $estadosClinicos)) {
            $this->agregarError('estado', 'Estado clínico no válido.');
        }

        return empty($this->obtenerErrores());
    }


    public function listarLesiones(string $estadoClinico = '', int $id_atleta = 0, string $tipo = '', string $zona = '', bool $modoPapelera = false): array {
        try {
            $sql = "SELECT l.*, CONCAT(a.nombres, ' ', a.apellidos) AS nombre_atleta 
                    FROM lesiones l
                    INNER JOIN atletas a ON l.id_atleta = a.id_atleta
                    WHERE 1=1";
            $params = [];

          
            if ($modoPapelera) {
                $sql .= " AND l.activo = 0";
            } else {
                $sql .= " AND l.activo = 1";
            }

            if (!empty($estadoClinico)) {
                $sql .= " AND l.estado = :estado";
                $params[':estado'] = $estadoClinico;
            }
            if ($id_atleta > 0) {
                $sql .= " AND l.id_atleta = :id_atleta";
                $params[':id_atleta'] = $id_atleta;
            }
            if (!empty($tipo)) {
                $sql .= " AND l.tipo = :tipo";
                $params[':tipo'] = $tipo;
            }
            if (!empty($zona)) {
                $sql .= " AND l.zona_anatomica = :zona";
                $params[':zona'] = $zona;
            }

            $sql .= " ORDER BY l.fecha_inicio DESC";
            
            $stmt = $this->pdo->prepare($sql);
            foreach ($params as $key => &$val) {
                $stmt->bindValue($key, $val);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error en listarLesiones: " . $e->getMessage());
            return [];
        }
    }

 

public function obtenerDetallePorId(int $id_lesion): ?array {
    try {
        $sql = "SELECT l.*, a.nombres, a.apellidos, a.cedula
                FROM lesiones l
                INNER JOIN atletas a ON l.id_atleta = a.id_atleta
                WHERE l.id_lesion = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id_lesion, PDO::PARAM_INT);
        $stmt->execute();
        $detalle = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$detalle) return null;

       
        $fechaInicio = $detalle['fecha_inicio'];
        $fechaInicioRango = date('Y-m-d', strtotime($fechaInicio . ' -15 days'));
        $fechaFinRango   = date('Y-m-d', strtotime($fechaInicio . ' +15 days'));

        $sqlRPE = "SELECT fecha, rpe 
                   FROM registro_rpe 
                   WHERE id_atleta = :id_atleta 
                     AND fecha BETWEEN :fecha_inicio AND :fecha_fin
                     AND deleted_at IS NULL   
                   ORDER BY fecha ASC";
        $stmtRPE = $this->pdo->prepare($sqlRPE);
        $stmtRPE->bindValue(':id_atleta', $detalle['id_atleta'], PDO::PARAM_INT);
        $stmtRPE->bindValue(':fecha_inicio', $fechaInicioRango);
        $stmtRPE->bindValue(':fecha_fin', $fechaFinRango);
        $stmtRPE->execute();
        $rpeData = $stmtRPE->fetchAll(PDO::FETCH_ASSOC);

        $detalle['rpe_fechas'] = array_column($rpeData, 'fecha');
        $detalle['rpe_historico'] = array_column($rpeData, 'rpe');

       
        $promedio = $this->obtenerPromedioRPEPrevio($detalle['id_atleta'], $detalle['fecha_inicio']);
        $detalle['rpe_promedio_3_dias'] = round($promedio, 1);

        
        $alerta = false;
        if ($detalle['activo'] == 1 && $promedio > 8.5) {
            $diagnostico = strtolower($detalle['diagnostico'] ?? '');
            if (strpos($diagnostico, 'molestia leve') !== false) {
                $alerta = true;
            }
        }
        $detalle['alerta_riesgo'] = $alerta;

        return $detalle;
    } catch (PDOException $e) {
        error_log("Error en obtenerDetallePorId: " . $e->getMessage());
        return null;
    }
}

    
    public function obtenerHistorial(int $id_atleta): array {
        try {
            $sql = "SELECT id_lesion, fecha_inicio, zona_anatomica, lado, tipo, nivel_molestia, diagnostico, estado 
                    FROM lesiones 
                    WHERE id_atleta = :id_atleta AND activo = 1
                    ORDER BY fecha_inicio DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id_atleta', $id_atleta, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en obtenerHistorial: " . $e->getMessage());
            return [];
        }
    }


   
    public function obtenerPromedioRPEPrevio(int $id_atleta, string $fecha_lesion): float {
        try {
            $sql = "SELECT COALESCE(AVG(rpe), 0) as promedio_rpe 
                FROM registro_rpe 
                WHERE id_atleta = :id_atleta 
                  AND deleted_at IS NULL
                  AND fecha BETWEEN DATE_SUB(:fecha_desde, INTERVAL 3 DAY) AND :fecha_hasta";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id_atleta', $id_atleta, PDO::PARAM_INT);
            $stmt->bindValue(':fecha_desde', $fecha_lesion, PDO::PARAM_STR);
            $stmt->bindValue(':fecha_hasta', $fecha_lesion, PDO::PARAM_STR);
            $stmt->execute();
            
            return (float) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error calculando RPE previo: " . $e->getMessage());
            return 0.0;
        }
    }

    
    private function obtenerMapaParametros(bool $esActualizacion = false): array {
        $mapa = [
            
            ':zona_anatomica'       => ['zona_anatomica', PDO::PARAM_STR],
            ':lado'                 => ['lado', PDO::PARAM_STR],
            ':tipo'                 => ['tipo', PDO::PARAM_STR],
            ':nivel_molestia'       => ['nivel_molestia', PDO::PARAM_INT],
            ':diagnostico'          => ['diagnostico', PDO::PARAM_STR],
            ':tratamiento'          => ['tratamiento', PDO::PARAM_STR],
            ':fecha_inicio'         => ['fecha_inicio', PDO::PARAM_STR],
            ':fecha_estimada_recup' => ['fecha_estimada_recup', PDO::PARAM_STR],
            ':estado'               => ['estado', PDO::PARAM_STR],
            ':profesional'          => ['profesional', PDO::PARAM_STR],
            ':observaciones'        => ['observaciones', PDO::PARAM_STR]
        ];

        if ($esActualizacion) {
            $mapa[':id_lesion'] = ['id_lesion', PDO::PARAM_INT];
        } else {
            $mapa[':id_atleta'] = ['id_atleta', PDO::PARAM_INT];
        }
        return $mapa;
    }

    
    private function gestionarAlertasClinicas(int $id_lesion, array $nuevos, ?array $viejos = null): void {
        $newMolestia = (int)$nuevos['nivel_molestia'];
        $newTipo     = $nuevos['tipo'];
        $newEstado   = $nuevos['estado'] ?? 'Activa';
        
        $condicionAltaNueva = ($newMolestia >= 8 || $newTipo === 'Recidiva');
        $condicionAltaAnterior = false;
        $oldEstado = '';

        if ($viejos !== null) {
            $condicionAltaAnterior = ((int)$viejos['nivel_molestia'] >= 8 || $viejos['tipo'] === 'Recidiva');
            $oldEstado = $viejos['estado'] ?? '';
        }

        if ($condicionAltaNueva && !$condicionAltaAnterior) {
            $gravedad = ($newMolestia >= 9 || $newTipo === 'Recidiva') ? 3 : 2;
            $tipoAlerta = ($newTipo === 'Recidiva') ? 'ALERTA_RECIDIVA' : 'DOLOR_AGUDO';
            $mensaje = $viejos 
                ? "Actualización clínica: Lesión ({$newTipo}) empeoró a molestia {$newMolestia}/10." 
                : "Atención requerida: Lesión ({$newTipo}) en {$nuevos['zona_anatomica']} con nivel de molestia {$newMolestia}/10.";

            $sqlAlert = "INSERT INTO alertas_biologicas (id_atleta, modulo_origen, id_registro_origen, tipo_alerta, gravedad, mensaje, activo) 
                         VALUES (:id_atleta, 'LESIONES', :id_registro_origen, :tipo_alerta, :gravedad, :mensaje, 1)";
            $stmtAlert = $this->pdo->prepare($sqlAlert);
            $stmtAlert->execute([
                ':id_atleta' => $nuevos['id_atleta'], ':id_registro_origen' => $id_lesion,
                ':tipo_alerta' => $tipoAlerta, ':gravedad' => $gravedad, ':mensaje' => $mensaje
            ]);
        } 
        elseif (!$condicionAltaNueva && $condicionAltaAnterior) {
            $this->desactivarAlertasDolor($id_lesion);
        }

        if ($viejos !== null && $newEstado === 'Recuperada' && $oldEstado !== 'Recuperada') {
            $sqlAlta = "INSERT INTO alertas_biologicas (id_atleta, modulo_origen, id_registro_origen, tipo_alerta, gravedad, mensaje, activo) 
                        VALUES (:id_atleta, 'LESIONES', :id_registro_origen, 'ALTA_MEDICA', 1, 'Evolución favorable: Atleta dado de alta médica.', 1)";
            $stmtAlta = $this->pdo->prepare($sqlAlta);
            $stmtAlta->execute([':id_atleta' => $nuevos['id_atleta'], ':id_registro_origen' => $id_lesion]);

            $this->desactivarAlertasDolor($id_lesion);
        }
    }

    private function desactivarAlertasDolor(int $id_lesion): void {
        $sql = "UPDATE alertas_biologicas SET activo = 0 
                WHERE modulo_origen = 'LESIONES' AND id_registro_origen = :id_lesion 
                AND tipo_alerta IN ('DOLOR_AGUDO', 'ALERTA_RECIDIVA')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_lesion' => $id_lesion]);
    }


    public function registrarLesion(array $payload): bool|array {
        $this->setAtributos($payload);
        
        if (!$this->validarAtributosInternos(false)) {
            return false;
        }

        return $this->ejecutarRegistro();
    }


    private function ejecutarRegistro(): bool|array {
        try {
            $this->pdo->beginTransaction();

            $sql = "INSERT INTO lesiones (
                        id_atleta, zona_anatomica, lado, tipo, nivel_molestia, diagnostico, tratamiento, 
                        fecha_inicio, fecha_estimada_recup, estado, profesional, observaciones, activo
                    ) VALUES (
                        :id_atleta, :zona_anatomica, :lado, :tipo, :nivel_molestia, :diagnostico, :tratamiento, 
                        :fecha_inicio, :fecha_estimada_recup, COALESCE(:estado, 'Activa'), :profesional, :observaciones, 1
                    )";
                    
            $stmt = $this->pdo->prepare($sql);
            $this->autoBind($stmt, $this->obtenerMapaParametros(false), $this->datos);
            $stmt->execute();
            $id_insertado = $this->pdo->lastInsertId();

            $this->gestionarAlertasClinicas($id_insertado, $this->datos, null);

            $this->pdo->commit();
            return ['exito' => true, 'id_lesion' => $id_insertado, 'mensaje' => 'Lesión registrada correctamente.'];

        } catch (PDOException $e) {
            $this->pdo->rollBack();
             if ($e->getCode() == 23000) {
                $this->agregarError('integridad', 'Los datos vinculados (Atleta o lesion) fueron alterados y no existen en el sistema.');
                return false;
            }
            error_log("Error transaccional en ejecutarRegistro: " . $e->getMessage());
            $this->agregarError('bd', 'Error interno al registrar la lesión.');
            return false;
        }
    }
 

    public function actualizarLesion(array $payload, int $id_lesion): bool {
        $this->setAtributos($payload);
        
        if (!$this->validarAtributosInternos(true)) {
            return false;
        }

        return $this->ejecutarActualizacion($id_lesion);
    }


    private function ejecutarActualizacion(int $id_lesion): bool {
        try {
            $this->pdo->beginTransaction();

            $sqlOld = "SELECT id_atleta, nivel_molestia, estado, tipo FROM lesiones WHERE id_lesion = :id FOR UPDATE";  
            $stmtOld = $this->pdo->prepare($sqlOld);
            $stmtOld->execute([':id' => $id_lesion]);
            $oldData = $stmtOld->fetch(PDO::FETCH_ASSOC);

            if (!$oldData) {
                $this->pdo->rollBack();
                $this->agregarError('actualizacion', 'No se encontró la lesión.');
                return false;
            }

            $this->datos['id_atleta'] = (int)$oldData['id_atleta'];

            $sql = "UPDATE lesiones SET
                        zona_anatomica = :zona_anatomica, lado = :lado,
                        tipo = :tipo, nivel_molestia = :nivel_molestia, diagnostico = :diagnostico,
                        tratamiento = :tratamiento, fecha_inicio = :fecha_inicio,
                        fecha_estimada_recup = :fecha_estimada_recup, estado = :estado,
                        profesional = :profesional, observaciones = :observaciones
                    WHERE id_lesion = :id_lesion AND activo = 1";

            $stmt = $this->pdo->prepare($sql);
            $this->datos['id_lesion'] = $id_lesion;
            
            $this->autoBind($stmt, $this->obtenerMapaParametros(true), $this->datos);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                $this->pdo->rollBack();
                $this->agregarError('actualizacion', 'No se encontró la lesión activa o no se realizaron cambios.');
                return false;
            }

            $this->gestionarAlertasClinicas($id_lesion, $this->datos, $oldData);

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
             if ($e->getCode() == 23000) {
                $this->agregarError('integridad', 'Los datos vinculados (Atleta o lesion) fueron alterados y no existen en el sistema.');
                return false;
            }
            error_log("Error transaccional en ejecutarActualizacion: " . $e->getMessage());
            $this->agregarError('bd', 'Error interno al actualizar la lesión.');
            return false;
        }
    }


 

    public function eliminarLesionLogicamente(int $id_lesion, string $motivo): bool {
        if ($id_lesion <= 0 || empty(trim($motivo))) {
            $this->agregarError('eliminacion', 'El ID y el motivo son obligatorios.');
            return false;
        }

        return $this->ejecutarEliminacionLogica($id_lesion, $motivo);
    }

    private function ejecutarEliminacionLogica(int $id_lesion, string $motivo): bool {
        try {
            $this->pdo->beginTransaction();

            $sql = "UPDATE lesiones 
                    SET activo = 0, motivo_eliminacion = :motivo 
                    WHERE id_lesion = :id_lesion AND activo = 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':motivo' => trim($motivo),
                ':id_lesion' => $id_lesion
            ]);
            
            if ($stmt->rowCount() === 0) {
                $this->pdo->rollBack();
                $this->agregarError('eliminacion', 'No se encontró la lesión activa.');
                return false;
            }

            $sqlAlertas = "UPDATE alertas_biologicas 
                           SET activo = FALSE 
                           WHERE modulo_origen = 'LESIONES' AND id_registro_origen = :id_lesion";
            $stmtAlertas = $this->pdo->prepare($sqlAlertas);
            $stmtAlertas->execute([':id_lesion' => $id_lesion]);

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
             if ($e->getCode() == 23000) {
                $this->agregarError('integridad', 'Los datos vinculados (Atleta o lesion) fueron alterados y no existen en el sistema.');
                return false;
            }
            error_log("Error transaccional en ejecutarEliminacionLogica: " . $e->getMessage());
            return false;
        }
    }

    public function reactivarLesion(int $id_lesion): bool {
        if ($id_lesion <= 0) {
            $this->agregarError('reactivacion', 'ID de lesión inválido.');
            return false;
        }

        return $this->ejecutarReactivacion($id_lesion);
    }

    private function ejecutarReactivacion(int $id_lesion): bool {
        try {
            $this->pdo->beginTransaction();

            $sql = "UPDATE lesiones 
                    SET activo = 1, motivo_eliminacion = NULL 
                    WHERE id_lesion = :id AND activo = 0";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id_lesion]);

            if ($stmt->rowCount() === 0) {
                $this->pdo->rollBack();
                $this->agregarError('reactivacion', 'La lesión no se encuentra en la papelera.');
                return false;
            }

            $sqlAlertas = "UPDATE alertas_biologicas 
                           SET activo = TRUE 
                           WHERE modulo_origen = 'LESIONES' AND id_registro_origen = :id_lesion";
            $stmtAlertas = $this->pdo->prepare($sqlAlertas);
            $stmtAlertas->execute([':id_lesion' => $id_lesion]);

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
             if ($e->getCode() == 23000) {
                $this->agregarError('integridad', 'Los datos vinculados (Atleta o lesion) fueron alterados y no existen en el sistema.');
                return false;
            }
            error_log("Error transaccional en ejecutarReactivacion: " . $e->getMessage());
            return false;
        }
    }


   public function obtenerRiesgosActivos(): array {
    try {
        $sql = "SELECT ab.*, a.nombres, a.apellidos 
                FROM alertas_biologicas ab
                INNER JOIN atletas a ON ab.id_atleta = a.id_atleta
                WHERE ab.modulo_origen = 'LESIONES' 
                      AND ab.activo = TRUE 
                ORDER BY ab.gravedad DESC, ab.fecha_creacion DESC LIMIT 5";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error en obtenerRiesgosActivos: " . $e->getMessage());
        return [];
    }
    }
   
}
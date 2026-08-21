<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;


class Marca extends Conexion {
    use ValidacionesTrait;
    use AutoBinderTrait;

    
    private array $datos = [];

    /**
     * Lista blanca de campos permitidos (Protección contra Asignación Masiva)
     */
    private array $camposPermitidos = [
        'id_atleta', 'id_sesion', 'id_evento', 'estilo', 'distancia_m',
        'tipo_piscina', 'tiempo_final_seg', 'tiempo_reaccion_seg',
        'virajes', 'nivel_evento', 'fecha', 'observaciones',
        'num_brazadas', 'splits','brazadas_por_largo','id_marca','accion','motivo_eliminacion',
        'distancia_total', 'estado_carrera', 'ultima_distancia_recorrida_m', 'ultimo_tiempo_parcial_ms'
    ];

    
    /**
     * Mapea el payload externo  filtrando campos basura.
     * Soporta de forma segura variables escalares y arreglos estructurados (Splits).
     */
    public function setAtributos(array $payload): void {
        foreach ($this->camposPermitidos as $campo) {
            if (isset($payload[$campo])) {
                // Si el campo es un arreglo (como los splits), lo guardamos directo sin comparar con string
                if (is_array($payload[$campo])) {
                    $this->datos[$campo] = $payload[$campo];
                } 
                // Si es un dato normal, validamos que no esté vacío
                elseif ($payload[$campo] !== '') {
                    $this->datos[$campo] = $payload[$campo];
                } else {
                    $this->datos[$campo] = null;
                }
            } else {
                $this->datos[$campo] = null;
            }
        }
    }

    public function getCampo(string $clave) {
        return $this->datos[$clave] ?? null;
    }

    public function obtenerDatos(): array {
        return $this->datos;
    }

   private function validarAtributosInternos(): bool {
    $this->resetearErrores();

    $id_atleta = (string)($this->datos['id_atleta'] ?? '');
    $estilo = (string)($this->datos['estilo'] ?? '');
    $distancia = (string)($this->datos['distancia_m'] ?? '');
    $piscina = (string)($this->datos['tipo_piscina'] ?? '');
    $tiempo = (string)($this->datos['tiempo_final_seg'] ?? '');
    $fecha = (string)($this->datos['fecha'] ?? '');
    $reaccion = (string)($this->datos['tiempo_reaccion_seg'] ?? '');
    $brazadas = (string)($this->datos['brazadas_por_largo'] ?? '');
    $obs = (string)($this->datos['observaciones'] ?? '');
    
    // Capturamos AMBOS arreglos
    $splits = $this->datos['splits'] ?? [];
    $virajes = $this->datos['virajes'] ?? [];

    // 1. Validaciones Obligatorias Básicas
    if ($this->requerido($id_atleta, 'Atleta Seleccionado')) {
        $this->soloNumeros($id_atleta, 'Atleta Seleccionado');
    }

    if ($this->requerido($tiempo, 'Tiempo Final')) {
        $this->decimalValido($tiempo, 'Tiempo Final');
    }

    if ($this->requerido($fecha, 'Fecha del Registro')) {
        if ($this->fechaValida($fecha, 'Fecha del Registro')) {
            $this->fechaNoFutura($fecha, 'Fecha del Registro');
        }
    }

    // 2. Validaciones de Dominio Restringido (Listas Select)
    if ($this->requerido($estilo, 'Estilo')) {
        $this->enEnum($estilo, 'Estilo', ['Libre', 'Espalda', 'Braza', 'Mariposa', 'Combinado']);
    }

    if ($this->requerido($distancia, 'Distancia')) {
        $this->enEnum($distancia, 'Distancia', ['50', '100', '200', '400', '800', '1500']);
    }

    if ($this->requerido($piscina, 'Tipo de Piscina')) {
        $this->enEnum($piscina, 'Tipo de Piscina', ['50m', '25m']);
    }

    // 3. Validaciones Opcionales
    if ($reaccion !== '') {
        $this->decimalValido($reaccion, 'Tiempo de Reacción');
        $this->longitud($reaccion, 'Tiempo de Reacción', 1, 5);
    }

    if ($brazadas !== '') {
        $this->soloNumeros($brazadas, 'Brazadas por Largo');
        $this->longitud($brazadas, 'Brazadas por Largo', 1, 3);
    }

    if ($obs !== '') {
        $this->longitud($obs, 'Observaciones Técnicas', 1, 255);
    }

    // 4. VALIDACIÓN ESTRICTA DE SPLITS Y VIRAJES DINÁMICOS
    if (!empty($distancia) && is_numeric($distancia)) {
        $distanciaInt = (int)$distancia;
        $tramosEsperados = $distanciaInt / 25; 

        if (is_array($splits) && !empty($splits)) {
            if (count($splits) !== $tramosEsperados) {
                $this->agregarError('splits', "Incoherencia: Una prueba de {$distanciaInt}m requiere exactamente {$tramosEsperados} tiempos parciales.");
            } else {
                foreach ($splits as $distancia_parcial => $tiempo_parcial) {
                    $dist_parcial_int = (int)$distancia_parcial;
                    
                    if ($dist_parcial_int % 25 !== 0 || $dist_parcial_int > $distanciaInt) {
                        $this->agregarError('splits', "El tramo de {$distancia_parcial}m está corrupto o no pertenece a esta prueba.");
                    }
                    
                    // Validar Split
                    $this->decimalValido((string)$tiempo_parcial, "Parcial {$distancia_parcial}m");

                    // Validar Viraje (Si existe para esta misma distancia y no está vacío)
                    if (isset($virajes[$distancia_parcial]) && $virajes[$distancia_parcial] !== '') {
                        $this->decimalValido((string)$virajes[$distancia_parcial], "Viraje en {$distancia_parcial}m");
                    }
                }
            }
        }
    }

    return empty($this->obtenerErrores());
}


    /**
     * Valida reglas que son exclusivas de Marcas e Integridad Logística
     */
    private function validarReglasDeNegocio(): bool {
        
        $id_sesion = $this->datos['id_sesion'] ?? null;
        $id_evento = $this->datos['id_evento'] ?? null;
        $id_atleta = $this->datos['id_atleta'] ?? null;

        // Regla 1: Validar la regla XOR Estricta
        if (!empty($id_sesion) && !empty($id_evento)) {
            $this->agregarError('contexto', 'Una marca no puede pertenecer a un entrenamiento y competencia a la vez.');
        } elseif (empty($id_sesion) && empty($id_evento)) {
            $this->agregarError('contexto', 'Debe especificar si la marca se registró durante una sesión de entrenamiento o un evento competitivo.');
        }

        // Reglas 2 y 4 FUSIONADAS: Integridad de Asistencia y Cronológica (Sesión)
        if (!empty($id_sesion) && !empty($id_atleta)) {
            // INNER JOIN garantiza que si el registro existe en asistencia, traiga la fecha de la sesión
            $sqlAsistencia = "SELECT a.estado, s.fecha 
                              FROM asistencia a 
                              INNER JOIN sesiones s ON a.id_sesion = s.id_sesion 
                              WHERE a.id_sesion = :sesion AND a.id_atleta = :atleta";
            
            $stmtA = $this->pdo->prepare($sqlAsistencia);
            
            $mapaAsis = [
                ':sesion' => ['id_sesion', PDO::PARAM_INT],
                ':atleta' => ['id_atleta', PDO::PARAM_INT]
            ];

            $this->autoBind($stmtA, $mapaAsis, $this->datos); 
            $stmtA->execute(); 
            
            // Usamos FETCH_ASSOC para traer todo el arreglo (estado y fecha)
            $resultadoAsistencia = $stmtA->fetch(PDO::FETCH_ASSOC);

            // Validar la Logística (Asistencia)
            if (!$resultadoAsistencia || $resultadoAsistencia['estado'] !== 'Presente') {
                $this->agregarError('integridad_asistencia', 'Fraude Logístico: El atleta seleccionado no figura como "Presente" en la lista de asistencia de esta sesión.');
            } 
            // Validar la Cronología (Solo si pasó la prueba anterior y hay fecha ingresada)
            elseif (!empty($fecha_ingresada) && $fecha_ingresada !== $resultadoAsistencia['fecha']) {
                $this->agregarError('fecha', 'Inconsistencia Cronológica: La fecha seleccionada no coincide con el día en que se realizó esta sesión.');
            }
        }

        // Reglas 3 y 5 FUSIONADAS: Integridad de Inscripción y Cronológica (Evento)
        if (!empty($id_evento) && !empty($id_atleta)) {
            // Ya no usamos COUNT(*), simplemente pedimos las fechas si existe la inscripción
            $sqlEvento = "SELECT e.fecha_inicio, e.fecha_fin 
                          FROM evento_inscripcion ei 
                          INNER JOIN eventos e ON ei.id_evento = e.id_evento 
                          WHERE ei.id_evento = :evento AND ei.id_atleta = :atleta";
            
            $stmtE = $this->pdo->prepare($sqlEvento);
            $stmtE->bindValue(':evento', (int)$this->datos['id_evento'], PDO::PARAM_INT);
            $stmtE->bindValue(':atleta', (int)$this->datos['id_atleta'], PDO::PARAM_INT);
            $stmtE->execute();

            // Usamos FETCH_ASSOC para traer el rango de fechas
            $resultadoEvento = $stmtE->fetch(PDO::FETCH_ASSOC);

            // Validar la Logística (Si es false, significa que el conteo es 0, no está inscrito)
            if (!$resultadoEvento) {
                $this->agregarError('integridad_evento', 'Fraude Logístico: El atleta seleccionado no se encuentra formalmente inscrito en este evento.');
            } 
            // Validar la Cronología (Solo si está inscrito y hay fecha ingresada)
            elseif (!empty($fecha_ingresada)) {
                if ($fecha_ingresada < $resultadoEvento['fecha_inicio'] || $fecha_ingresada > $resultadoEvento['fecha_fin']) {
                    $this->agregarError('fecha', 'Inconsistencia Cronológica: La fecha ingresada está fuera del rango de días de esta competencia.');
                }
            }
        }

        return empty($this->obtenerErrores());
    }

    // =====================================================================
    // OPERACIÓN TRANSACCIONAL (BACKEND)
    // =====================================================================
   
   public function getRegistrarMarca(){

        if (!$this->validarAtributosInternos()) {
            return false; 
        }

        
        if (!$this->validarReglasDeNegocio()) {
            return false;
        }

    return $this->registrarMarca();
   }

  
  public function syncTelemetria(): array {
    $id_atleta = (int)($this->datos['id_atleta'] ?? 0);
    
    if ($id_atleta <= 0) {
        return ['status' => 'error', 'message' => 'Falta el ID del atleta para la telemetría.'];
    }

    return $this->procesarTelemetria();
}

    public function getActualizarMarca(){

        $id = (int)($this->datos['id_marca'] ?? 0);
        if ($id <= 0) {
            
            $this->agregarError('id', 'No se proporcionó un identificador de marca válido para actualizar.');
            return false;
        }

        if (!$this->validarAtributosInternos()) {
            return false; 
        }

        
        if (!$this->validarReglasDeNegocio()) {
            return false;
        }

        return $this->actualizarMarca();
   }

    public function getEliminarMarca(){


        $id = (int)($this->datos['id_marca'] ?? 0);
        if ($id <= 0) {
            $this->agregarError('id_marca', 'No se proporcionó un identificador válido para archivar el registro.');
            return false;
        }

        // Usamos mb_strlen para contar bien los caracteres con acentos
        $motivo = $this->datos['motivo_eliminacion'] ?? '';
        if (empty($motivo) || mb_strlen($motivo) < 5) {
            $this->agregarError('motivo_eliminacion', 'Debe proporcionar una justificación detallada (mínimo 5 letras) para archivar la marca.');
            return false;
        }

        $infoMarca = $this->obtenerInfoBasicaMarca($id);
        $this->datos['id_atleta'] = $infoMarca['id_atleta'] ?? null;

        return $this->eliminarMarca();
   }

    public function getReactivarMarca(){
         $id = (int)($this->datos['id_marca'] ?? 0);
        if ($id <= 0) {
            $this->agregarError('id_marca', 'No se proporcionó un identificador válido para archivar el registro.');
            return false;
        }

        $infoMarca = $this->obtenerInfoBasicaMarca($id);
        $this->datos['id_atleta'] = $infoMarca['id_atleta'] ?? null;

        return $this->reactivarMarca();
   }


/**
 * Calcula y guarda el SWOLF para una marca
 */
/* private function guardarSwolf(int $idMarca, array $datos): void
{
    if (empty($datos['brazadas_por_largo'])) {
        return; // No hay datos para SWOLF
    }

    $brazadas = (int)$datos['brazadas_por_largo'];
    $longitudPiscina = ($datos['tipo_piscina'] === '25m') ? 25 : 50;
    $cantidadLargos = (int)$datos['distancia_m'] / $longitudPiscina;
    $tiempoFinal = (float)$datos['tiempo_final_seg'];
    $tiempoPorLargo = $tiempoFinal / $cantidadLargos;
    $swolfCalculado = (int)round($tiempoPorLargo + $brazadas);

    $sql = "INSERT INTO marcas_swolf (id_marca, num_brazadas, swolf) 
            VALUES (:id_marca, :brazadas, :swolf)";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindValue(':id_marca', $idMarca, PDO::PARAM_INT);
    $stmt->bindValue(':brazadas', $brazadas, PDO::PARAM_INT);
    $stmt->bindValue(':swolf', $swolfCalculado, PDO::PARAM_INT);
    $stmt->execute();
} */

private function guardarSwolf(
    int $idMarca,
    int $brazadasPorLargo,
    string $tipoPiscina,
    int $distanciaMetros,
    float $tiempoFinalSeg
): void {
    if ($brazadasPorLargo <= 0) {
        return;
    }

    $longitudPiscina = ($tipoPiscina === '25m') ? 25 : 50;
    $cantidadLargos = $distanciaMetros / $longitudPiscina;
    $tiempoPorLargo = $tiempoFinalSeg / $cantidadLargos;
    $swolfCalculado = (int)round($tiempoPorLargo + $brazadasPorLargo);

    $sql = "INSERT INTO marcas_swolf (id_marca, num_brazadas, swolf) 
            VALUES (:id_marca, :brazadas, :swolf)";
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindValue(':id_marca', $idMarca, PDO::PARAM_INT);
    $stmt->bindValue(':brazadas', $brazadasPorLargo, PDO::PARAM_INT);
    $stmt->bindValue(':swolf', $swolfCalculado, PDO::PARAM_INT);
    $stmt->execute();
}

/**
 * Guarda los splits (tramos) de una marca
 */
private function guardarSplits(int $idMarca, array $splits, array $virajes): void
{
    if (empty($splits) || !is_array($splits)) {
        return;
    }

    // Actualizado para incluir tiempo_viraje_seg
    $sql = "INSERT INTO marcas_splits (id_marca, parcial_numero, distancia_parcial_m, tiempo_parcial_seg, tiempo_viraje_seg) 
            VALUES (:id_marca, :numero, :distancia_parcial, :tiempo_parcial, :tiempo_viraje)";
    $stmt = $this->pdo->prepare($sql);

    $numeroSplit = 1;
    foreach ($splits as $distanciaParcial => $tiempoSeg) {
        $tiempoParcial = (float)$tiempoSeg;
        
        if ($tiempoParcial > 0) {
            $stmt->bindValue(':id_marca', $idMarca, PDO::PARAM_INT);
            $stmt->bindValue(':numero', $numeroSplit, PDO::PARAM_INT);
            $stmt->bindValue(':distancia_parcial', (int)$distanciaParcial, PDO::PARAM_INT);
            $stmt->bindValue(':tiempo_parcial', $tiempoParcial, PDO::PARAM_STR);
            
            // Buscar si existe un viraje para esta distancia exacta
            if (isset($virajes[$distanciaParcial]) && $virajes[$distanciaParcial] !== '') {
                $stmt->bindValue(':tiempo_viraje', (float)$virajes[$distanciaParcial], PDO::PARAM_STR);
            } else {
                $stmt->bindValue(':tiempo_viraje', null, PDO::PARAM_NULL);
            }

            $stmt->execute();
            $numeroSplit++;
        }
    }
}

/**
 * Determina si una marca es Personal Best (PB)
 */
private function calcularPB(int $idAtleta, string $estilo, int $distancia, string $tipoPiscina, float $tiempoFinal, ?int $idMarcaExcluir = null): int
{
    $sql = "SELECT MIN(tiempo_final_seg) as mejor_tiempo 
            FROM marcas 
            WHERE id_atleta = :id_atleta 
              AND estilo = :estilo 
              AND distancia_m = :distancia 
              AND tipo_piscina = :tipo_piscina 
              AND estado = 'Activo'";
    
    $params = [
        ':id_atleta' => $idAtleta,
        ':estilo' => $estilo,
        ':distancia' => $distancia,
        ':tipo_piscina' => $tipoPiscina
    ];

    if ($idMarcaExcluir !== null) {
        $sql .= " AND id_marca != :id_marca_actual";
        $params[':id_marca_actual'] = $idMarcaExcluir;
    }

    $stmt = $this->pdo->prepare($sql);
    foreach ($params as $clave => $valor) {
        $stmt->bindValue($clave, $valor, is_int($valor) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->execute();
    $historial = $stmt->fetch(PDO::FETCH_ASSOC);

    return (empty($historial['mejor_tiempo']) || $tiempoFinal < (float)$historial['mejor_tiempo']) ? 1 : 0;
}

private function registrarMarca(): bool
{
    try {
        $this->pdo->beginTransaction();

        $tiempoFinal = (float)$this->datos['tiempo_final_seg'];
        $es_pb = $this->calcularPB(
            (int)$this->datos['id_atleta'],
            $this->datos['estilo'],
            (int)$this->datos['distancia_m'],
            $this->datos['tipo_piscina'],
            $tiempoFinal
        );

            // -------------------------------------------------------------
            // INSERTAR EN TABLA PRINCIPAL: `marcas`
            // -------------------------------------------------------------
            $sqlInsert = "INSERT INTO marcas (id_atleta, id_sesion, id_evento, estilo, distancia_m, tipo_piscina, tiempo_final_seg, tiempo_reaccion_seg, es_pb, fecha, observaciones) 
                          VALUES (:id_atleta, :id_sesion, :id_evento, :estilo, :distancia, :piscina, :tiempo, :reaccion, :es_pb, :fecha, :obs)";
            
            $stmt = $this->pdo->prepare($sqlInsert);
            
            
            $mapaPrincipal = [
                ':id_atleta' => ['id_atleta', PDO::PARAM_INT],
                ':id_sesion' => ['id_sesion', PDO::PARAM_INT],
                ':id_evento' => ['id_evento', PDO::PARAM_INT],
                ':estilo'    => ['estilo', PDO::PARAM_STR],
                ':distancia' => ['distancia_m', PDO::PARAM_INT],
                ':piscina'   => ['tipo_piscina', PDO::PARAM_STR],
                ':tiempo'    => ['tiempo_final_seg', PDO::PARAM_STR],
                ':reaccion'  => ['tiempo_reaccion_seg', PDO::PARAM_STR],
                ':es_pb'     => ['es_pb_local', PDO::PARAM_INT], 
                ':fecha'     => ['fecha', PDO::PARAM_STR],
                ':obs'       => ['observaciones', PDO::PARAM_STR]
            ];

           
            $this->autoBind($stmt, $mapaPrincipal, $this->datos, ['es_pb_local' => $es_pb]);
            
            $stmt->execute(); 
           /*  $id_marca_insertada = $this->pdo->lastInsertId();
            $this->datos['id_marca'] = $id_marca_insertada; // Guardamos el ID generado para notificaciones y splits
           */

        $idMarca = $this->pdo->lastInsertId();
        $this->datos['id_marca'] = $idMarca;

        // Guardar SWOLF y Splits
        $this->guardarSwolf(
            $idMarca,
            (int)($this->datos['brazadas_por_largo'] ?? 0),
            $this->datos['tipo_piscina'],
            (int)$this->datos['distancia_m'],
            (float)$this->datos['tiempo_final_seg']
        );
        $this->guardarSplits($idMarca, $this->datos['splits'] ?? [], $this->datos['virajes'] ?? []);

        $this->pdo->commit();
        return true;

    } catch (PDOException $e) {
          $this->pdo->rollBack();

            if ($e->getCode() == 23000) {
                $this->agregarError('integridad', 'Los datos vinculados (Atleta, Sesión o Evento) fueron alterados y no existen en el sistema.');
                return false;
            }
           error_log("ERROR REAL DE SQL: " . $e->getMessage()); 
            error_log("TRACE: " . $e->getTraceAsString());
            return false;
    }
}

// =====================================================================
    // LOGICA PRIVADA DE TELEMETRÍA (SRP)
    // =====================================================================
   // =====================================================================
    // LOGICA PRIVADA DE TELEMETRÍA (SRP)
    // =====================================================================
    // =====================================================================
    // LOGICA PRIVADA DE TELEMETRÍA (SRP)
    // =====================================================================
    // =====================================================================
    // LOGICA PRIVADA DE TELEMETRÍA (SRP)
    // =====================================================================
    private function procesarTelemetria(): array {
    try {
        $id_atleta = (int)$this->datos['id_atleta'];
        $distancia_total = (int)($this->datos['distancia_total'] ?? 0);
        $tipo_piscina = trim($this->datos['tipo_piscina'] ?? '');
        $estilo = trim($this->datos['estilo'] ?? '');
        $estado_carrera = trim($this->datos['estado_carrera'] ?? '');
        $ultima_distancia = (int)($this->datos['ultima_distancia_recorrida_m'] ?? 0);
        $ultimo_tiempo = (float)($this->datos['ultimo_tiempo_parcial_ms'] ?? 0);

        // =================================================================
        // 1. LIMPIEZA ABSOLUTA (Solo si se cancela o inicia un nuevo crono)
        // =================================================================
        if (in_array($estado_carrera, ['cancelado', 'iniciando'])) {
            $sqlDel = "DELETE FROM telemetria_live WHERE id_atleta = :id_atleta";
            $stmtDel = $this->pdo->prepare($sqlDel);
            $stmtDel->execute([':id_atleta' => $id_atleta]);
            
            // Si el entrenador le dio a la "X" (Cancelar), detenemos la ejecución aquí.
            if ($estado_carrera === 'cancelado') {
                return ['status' => 'success', 'message' => 'Telemetría purgada con éxito.'];
            }
            // NOTA: Si es 'iniciando', el código no entra a este if y CONTINÚA 
            // hacia abajo para hacer un INSERT limpio. ¡Tu lógica aquí es brillante!
        }

        // =================================================================
        // 2. INSERCIÓN O ACTUALIZACIÓN (iniciando, en_curso, en_viraje, finalizado)
        // =================================================================
        $inicio_timestamp = round(microtime(true) * 1000); 
        // ¿Es el momento exacto en que suena la bocina?
        $es_arranque = ($estado_carrera === 'en_curso' && $ultima_distancia === 0 && $ultimo_tiempo == 0);

        // Verificamos si ya existe la carrera
        $stmtCheck = $this->pdo->prepare("SELECT inicio_timestamp_ms FROM telemetria_live WHERE id_atleta = :id LIMIT 1");
        $stmtCheck->execute([':id' => $id_atleta]);
        $existe = $stmtCheck->fetch(\PDO::FETCH_ASSOC);

        if ($existe) {
            // ---> UPDATE <---
            $sqlUpd = "UPDATE telemetria_live SET 
                       estado_carrera = :estado_carrera,
                       ultima_distancia_recorrida_m = :ultima_distancia,
                       ultimo_tiempo_parcial_ms = :ultimo_tiempo,
                       ultima_actualizacion = CURRENT_TIMESTAMP";
            
            if ($es_arranque) {
                $sqlUpd .= ", inicio_timestamp_ms = :inicio_timestamp";
            }
            
            $sqlUpd .= " WHERE id_atleta = :id_atleta";
            
            $stmt = $this->pdo->prepare($sqlUpd);
            $params = [
                ':estado_carrera' => $estado_carrera,
                ':ultima_distancia' => $ultima_distancia,
                ':ultimo_tiempo' => $ultimo_tiempo,
                ':id_atleta' => $id_atleta
            ];
            
            if ($es_arranque) { $params[':inicio_timestamp'] = $inicio_timestamp; }
            
            $stmt->execute($params);

        } else {
            // ---> INSERT NUEVO <---
            $sqlIns = "INSERT INTO telemetria_live 
                      (id_atleta, distancia_total, tipo_piscina, estilo, estado_carrera, inicio_timestamp_ms, ultima_distancia_recorrida_m, ultimo_tiempo_parcial_ms) 
                      VALUES 
                      (:id_atleta, :distancia_total, :tipo_piscina, :estilo, :estado_carrera, :inicio_timestamp, :ultima_distancia, :ultimo_tiempo)";
            
            $stmt = $this->pdo->prepare($sqlIns);
            $stmt->execute([
                ':id_atleta' => $id_atleta,
                ':distancia_total' => $distancia_total,
                ':tipo_piscina' => $tipo_piscina,
                ':estilo' => $estilo,
                ':estado_carrera' => $estado_carrera,
                ':inicio_timestamp' => $inicio_timestamp,
                ':ultima_distancia' => $ultima_distancia,
                ':ultimo_tiempo' => $ultimo_tiempo
            ]);
        }

        return ['status' => 'success', 'message' => 'Telemetría sincronizada'];

    } catch (\PDOException $e) {
        error_log("Error de DB en procesarTelemetria: " . $e->getMessage());
        return ['status' => 'error', 'message' => 'Error interno sincronizando telemetría.'];
    }
}

private function actualizarMarca(): bool
{
    try {
        $this->pdo->beginTransaction();

        $idMarca = (int)$this->datos['id_marca'];
        $tiempoFinal = (float)$this->datos['tiempo_final_seg'];

        $es_pb = $this->calcularPB(
            (int)$this->datos['id_atleta'],
            $this->datos['estilo'],
            (int)$this->datos['distancia_m'],
            $this->datos['tipo_piscina'],
            $tiempoFinal,
            $idMarca // Excluir la marca actual
        );

          // -------------------------------------------------------------
            // ACTUALIZACIÓN DE LA TABLA PRINCIPAL
            // -------------------------------------------------------------
            $sqlUpdate = "UPDATE marcas SET 
                           estilo = :estilo, 
                            distancia_m = :distancia, tipo_piscina = :piscina, tiempo_final_seg = :tiempo, 
                            tiempo_reaccion_seg = :reaccion, 
                            es_pb = :es_pb, fecha = :fecha, observaciones = :obs
                          WHERE id_marca = :id_marca_condicion";
            
            $stmt = $this->pdo->prepare($sqlUpdate);
            
            $mapaPrincipal = [
              
                ':estilo'    => ['estilo', PDO::PARAM_STR],
                ':distancia' => ['distancia_m', PDO::PARAM_INT],
                ':piscina'   => ['tipo_piscina', PDO::PARAM_STR],
                ':tiempo'    => ['tiempo_final_seg', PDO::PARAM_STR],
                ':reaccion'  => ['tiempo_reaccion_seg', PDO::PARAM_STR],     
                ':es_pb'     => ['es_pb_local', PDO::PARAM_INT], 
                ':fecha'     => ['fecha', PDO::PARAM_STR],
                ':obs'       => ['observaciones', PDO::PARAM_STR],
                ':id_marca_condicion' => ['id_marca_condicion', PDO::PARAM_INT]
            ];

            $this->autoBind($stmt, $mapaPrincipal, $this->datos, [
                'es_pb_local' => $es_pb, 
                'id_marca_condicion' => $idMarca
            ]);
            
            $stmt->execute();

            // -------------------------------------------------------------
            // LIMPIEZA DE TABLAS SECUNDARIAS (Delete & Re-insert Pattern)
            // -------------------------------------------------------------
            
            $stmtDelSwolf = $this->pdo->prepare("DELETE FROM marcas_swolf WHERE id_marca = :id");
            $stmtDelSwolf->bindValue(':id', $idMarca, PDO::PARAM_INT);
            $stmtDelSwolf->execute();

            $stmtDelSplits = $this->pdo->prepare("DELETE FROM marcas_splits WHERE id_marca = :id");
            $stmtDelSplits->bindValue(':id', $idMarca, PDO::PARAM_INT);
            $stmtDelSplits->execute();

            // -------------------------------------------------------------
            // RE-INSERCIÓN DE MÉTRICAS (SWOLF Y SPLITS)
            // -------------------------------------------------------------

        $this->guardarSwolf(
            $idMarca,
            (int)($this->datos['brazadas_por_largo'] ?? 0),
            $this->datos['tipo_piscina'],
            (int)$this->datos['distancia_m'],
            (float)$this->datos['tiempo_final_seg']
        );
       $this->guardarSplits($idMarca, $this->datos['splits'] ?? [], $this->datos['virajes'] ?? []);
        $this->pdo->commit();
        return true;

    } catch (PDOException $e) {
         $this->pdo->rollBack();
             if ($e->getCode() == 23000) {
                $this->agregarError('integridad', 'Los datos vinculados (Atleta, Sesión o Evento) fueron alterados y no existen en el sistema.');
                return false;
            }
            error_log("Error en actualizacion de marca: " . $e->getMessage());
            return false;
    }
}


  /*   private function registrarMarca(): bool {
      
        
       try {
            $this->pdo->beginTransaction();

            $tiempo_final = (float)$this->datos['tiempo_final_seg'];

            // -------------------------------------------------------------
            // A) DETECCIÓN AUTOMÁTICA DE PERSONAL BEST (PB)
            // -------------------------------------------------------------
            $sqlPB = "SELECT MIN(tiempo_final_seg) as mejor_tiempo 
                      FROM marcas 
                      WHERE id_atleta = :id_atleta AND estilo = :estilo 
                      AND distancia_m = :distancia AND tipo_piscina = :piscina AND estado = 'Activo'";
                      
            $stmtPB = $this->pdo->prepare($sqlPB);
            
            
            $mapaPB = [
                ':id_atleta' => ['id_atleta', PDO::PARAM_INT],
                ':estilo'    => ['estilo', PDO::PARAM_STR],
                ':distancia' => ['distancia_m', PDO::PARAM_INT],
                ':piscina'   => ['tipo_piscina', PDO::PARAM_STR]
            ];
            
            $this->autoBind($stmtPB, $mapaPB, $this->datos); 
            
            $stmtPB->execute(); 
            $historial = $stmtPB->fetch(PDO::FETCH_ASSOC);
            
            $es_pb = (empty($historial['mejor_tiempo']) || $tiempo_final < (float)$historial['mejor_tiempo']) ? 1 : 0;

            // -------------------------------------------------------------
            // INSERTAR EN TABLA PRINCIPAL: `marcas`
            // -------------------------------------------------------------
            $sqlInsert = "INSERT INTO marcas (id_atleta, id_sesion, id_evento, estilo, distancia_m, tipo_piscina, tiempo_final_seg, tiempo_reaccion_seg, tiempo_viraje_seg, es_pb, fecha, observaciones) 
                          VALUES (:id_atleta, :id_sesion, :id_evento, :estilo, :distancia, :piscina, :tiempo, :reaccion, :viraje, :es_pb, :fecha, :obs)";
            
            $stmt = $this->pdo->prepare($sqlInsert);
            
            
            $mapaPrincipal = [
                ':id_atleta' => ['id_atleta', PDO::PARAM_INT],
                ':id_sesion' => ['id_sesion', PDO::PARAM_INT],
                ':id_evento' => ['id_evento', PDO::PARAM_INT],
                ':estilo'    => ['estilo', PDO::PARAM_STR],
                ':distancia' => ['distancia_m', PDO::PARAM_INT],
                ':piscina'   => ['tipo_piscina', PDO::PARAM_STR],
                ':tiempo'    => ['tiempo_final_seg', PDO::PARAM_STR],
                ':reaccion'  => ['tiempo_reaccion_seg', PDO::PARAM_STR],
                ':viraje'    => ['tiempo_viraje_seg', PDO::PARAM_STR],
            //    ':nivel'     => ['nivel_evento', PDO::PARAM_STR],
                ':es_pb'     => ['es_pb_local', PDO::PARAM_INT], 
                ':fecha'     => ['fecha', PDO::PARAM_STR],
                ':obs'       => ['observaciones', PDO::PARAM_STR]
            ];

           
            $this->autoBind($stmt, $mapaPrincipal, $this->datos, ['es_pb_local' => $es_pb]);
            
            $stmt->execute(); 
            $id_marca_insertada = $this->pdo->lastInsertId();
            $this->datos['id_marca'] = $id_marca_insertada; // Guardamos el ID generado para notificaciones y splits
           // -------------------------------------------------------------
            // C) CÁLCULO SWOLF
            // -------------------------------------------------------------
          
            if (!empty($this->datos['brazadas_por_largo'])) {
                $brazadas = (int)$this->datos['brazadas_por_largo'];
                
               
                $longitud_piscina = ($this->datos['tipo_piscina'] === '25m') ? 25 : 50;
                $cantidad_largos = (int)$this->datos['distancia_m'] / $longitud_piscina;
                
                $tiempo_por_largo = $tiempo_final / $cantidad_largos;
                $swolf_calculado = (int)round($tiempo_por_largo + $brazadas);

                $sqlSwolf = "INSERT INTO marcas_swolf (id_marca, num_brazadas, swolf) 
                             VALUES (:id_marca, :brazadas, :swolf)";
                $stmtSwolf = $this->pdo->prepare($sqlSwolf);
                
                $stmtSwolf->bindValue(':id_marca', $id_marca_insertada, PDO::PARAM_INT);
                $stmtSwolf->bindValue(':brazadas', $brazadas, PDO::PARAM_INT);
                $stmtSwolf->bindValue(':swolf', $swolf_calculado, PDO::PARAM_INT);
                
                $stmtSwolf->execute();
            }

            // -------------------------------------------------------------
            // TRAMOS TRANSACCIONALES (Splits)
            // -------------------------------------------------------------
            
            if (!empty($this->datos['splits']) && is_array($this->datos['splits'])) {
                $sqlSplit = "INSERT INTO marcas_splits (id_marca, parcial_numero, distancia_parcial_m, tiempo_parcial_seg) 
                             VALUES (:id_marca, :numero, :distancia_parcial, :tiempo_parcial)";
                $stmtSplit = $this->pdo->prepare($sqlSplit);
                
                $numeroSplit = 1;
                
                foreach ($this->datos['splits'] as $dist_parcial => $tiempo_seg) {
                    $tiempoParcialLimpio = (float)$tiempo_seg; 
                    
                    if ($tiempoParcialLimpio > 0) {
                        $stmtSplit->bindValue(':id_marca', $id_marca_insertada, PDO::PARAM_INT);
                        $stmtSplit->bindValue(':numero', $numeroSplit, PDO::PARAM_INT);
                        $stmtSplit->bindValue(':distancia_parcial', (int)$dist_parcial, PDO::PARAM_INT);
                        $stmtSplit->bindValue(':tiempo_parcial', $tiempoParcialLimpio, PDO::PARAM_STR);
                        
                        $stmtSplit->execute();
                        $numeroSplit++;
                    }
                }
            }

            $this->pdo->commit();



            return true;

        } catch (PDOException $e) {
            $this->pdo->rollBack();

            if ($e->getCode() == 23000) {
                $this->agregarError('integridad', 'Los datos vinculados (Atleta, Sesión o Evento) fueron alterados y no existen en el sistema.');
                return false;
            }
           error_log("ERROR REAL DE SQL: " . $e->getMessage()); 
            error_log("TRACE: " . $e->getTraceAsString());
            return false;
        }
    }

   
    private function actualizarMarca(): bool {
        
        $id_marca = (int)$this->datos['id_marca'];

        try {
            $this->pdo->beginTransaction();
            $tiempo_final = (float)$this->datos['tiempo_final_seg'];

            // -------------------------------------------------------------
            // RECALCULAR PERSONAL BEST (Excluyendo la marca actual)
            // -------------------------------------------------------------
            $sqlPB = "SELECT MIN(tiempo_final_seg) as mejor_tiempo 
                      FROM marcas 
                      WHERE id_atleta = :id_atleta AND estilo = :estilo 
                      AND distancia_m = :distancia AND tipo_piscina = :piscina 
                      AND estado = 'Activo' AND id_marca != :id_marca_actual";
                      
            $stmtPB = $this->pdo->prepare($sqlPB);
            
            $mapaPB = [
                ':id_atleta' => ['id_atleta', PDO::PARAM_INT],
                ':estilo'    => ['estilo', PDO::PARAM_STR],
                ':distancia' => ['distancia_m', PDO::PARAM_INT],
                ':piscina'   => ['tipo_piscina', PDO::PARAM_STR],
                ':id_marca_actual' => ['id_marca_actual', PDO::PARAM_INT]
            ];
            
            
            $this->autoBind($stmtPB, $mapaPB, $this->datos, ['id_marca_actual' => $id_marca]); 
            
            $stmtPB->execute();
            $historial = $stmtPB->fetch(PDO::FETCH_ASSOC);
            
            $es_pb = (empty($historial['mejor_tiempo']) || $tiempo_final < (float)$historial['mejor_tiempo']) ? 1 : 0;

            // -------------------------------------------------------------
            // ACTUALIZACIÓN DE LA TABLA PRINCIPAL
            // -------------------------------------------------------------
            $sqlUpdate = "UPDATE marcas SET 
                           estilo = :estilo, 
                            distancia_m = :distancia, tipo_piscina = :piscina, tiempo_final_seg = :tiempo, 
                            tiempo_reaccion_seg = :reaccion, tiempo_viraje_seg = :viraje, 
                            es_pb = :es_pb, fecha = :fecha, observaciones = :obs
                          WHERE id_marca = :id_marca_condicion";
            
            $stmt = $this->pdo->prepare($sqlUpdate);
            
            $mapaPrincipal = [
              
                ':estilo'    => ['estilo', PDO::PARAM_STR],
                ':distancia' => ['distancia_m', PDO::PARAM_INT],
                ':piscina'   => ['tipo_piscina', PDO::PARAM_STR],
                ':tiempo'    => ['tiempo_final_seg', PDO::PARAM_STR],
                ':reaccion'  => ['tiempo_reaccion_seg', PDO::PARAM_STR],
                ':viraje'    => ['tiempo_viraje_seg', PDO::PARAM_STR],
               
                ':es_pb'     => ['es_pb_local', PDO::PARAM_INT], 
                ':fecha'     => ['fecha', PDO::PARAM_STR],
                ':obs'       => ['observaciones', PDO::PARAM_STR],
                ':id_marca_condicion' => ['id_marca_condicion', PDO::PARAM_INT]
            ];

            $this->autoBind($stmt, $mapaPrincipal, $this->datos, [
                'es_pb_local' => $es_pb, 
                'id_marca_condicion' => $id_marca
            ]);
            
            $stmt->execute();

            // -------------------------------------------------------------
            // LIMPIEZA DE TABLAS SECUNDARIAS (Delete & Re-insert Pattern)
            // -------------------------------------------------------------
            
            $stmtDelSwolf = $this->pdo->prepare("DELETE FROM marcas_swolf WHERE id_marca = :id");
            $stmtDelSwolf->bindValue(':id', $id_marca, PDO::PARAM_INT);
            $stmtDelSwolf->execute();

            $stmtDelSplits = $this->pdo->prepare("DELETE FROM marcas_splits WHERE id_marca = :id");
            $stmtDelSplits->bindValue(':id', $id_marca, PDO::PARAM_INT);
            $stmtDelSplits->execute();

            // -------------------------------------------------------------
            // RE-INSERCIÓN DE MÉTRICAS (SWOLF Y SPLITS)
            // -------------------------------------------------------------
            if (!empty($this->datos['brazadas_por_largo'])) {
                $brazadas = (int)$this->datos['brazadas_por_largo'];
                $longitud_piscina = ($this->datos['tipo_piscina'] === '25m') ? 25 : 50;
                $cantidad_largos = (int)$this->datos['distancia_m'] / $longitud_piscina;
                
                $swolf_calculado = (int)round(($tiempo_final / $cantidad_largos) + $brazadas);

                $stmtSwolf = $this->pdo->prepare("INSERT INTO marcas_swolf (id_marca, num_brazadas, swolf) VALUES (:id_marca, :brazadas, :swolf)");
                $stmtSwolf->bindValue(':id_marca', $id_marca, PDO::PARAM_INT);
                $stmtSwolf->bindValue(':brazadas', $brazadas, PDO::PARAM_INT);
                $stmtSwolf->bindValue(':swolf', $swolf_calculado, PDO::PARAM_INT);
                $stmtSwolf->execute();
            }

            if (!empty($this->datos['splits']) && is_array($this->datos['splits'])) {
                $stmtSplit = $this->pdo->prepare("INSERT INTO marcas_splits (id_marca, parcial_numero, distancia_parcial_m, tiempo_parcial_seg) VALUES (:id_marca, :numero, :distancia_parcial, :tiempo_parcial)");
                
                $numeroSplit = 1;
                foreach ($this->datos['splits'] as $dist_parcial => $tiempo_seg) {
                    $tiempoParcialLimpio = (float)$tiempo_seg; 
                    if ($tiempoParcialLimpio > 0) {
                        $stmtSplit->bindValue(':id_marca', $id_marca, PDO::PARAM_INT);
                        $stmtSplit->bindValue(':numero', $numeroSplit, PDO::PARAM_INT);
                        $stmtSplit->bindValue(':distancia_parcial', (int)$dist_parcial, PDO::PARAM_INT);
                        $stmtSplit->bindValue(':tiempo_parcial', $tiempoParcialLimpio, PDO::PARAM_STR);
                        $stmtSplit->execute();
                        $numeroSplit++;
                    }
                }
            }

            $this->pdo->commit();

            return true;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
             if ($e->getCode() == 23000) {
                $this->agregarError('integridad', 'Los datos vinculados (Atleta, Sesión o Evento) fueron alterados y no existen en el sistema.');
                return false;
            }
            error_log("Error en actualizacion de marca: " . $e->getMessage());
            return false;
        }
    } */

  // =====================================================================
    // MÉTODOS DE CONSULTA Y ESTADO (Listados y Soft Delete)
    // =====================================================================
    
    private function eliminarMarca(): bool {
        try {
            $sql = "UPDATE marcas 
                    SET estado = 'Inactivo', motivo_eliminacion = :motivo 
                    WHERE id_marca = :id";
            
            $stmt = $this->pdo->prepare($sql);
            
            $stmt->bindValue(':id', (int)$this->datos['id_marca'], PDO::PARAM_INT);
            $stmt->bindValue(':motivo', trim($this->datos['motivo_eliminacion']), PDO::PARAM_STR);
           


            return  $stmt->execute();
                       
        } catch (PDOException $e) {
             if ($e->getCode() == 23000) {
                $this->agregarError('integridad', 'Los datos vinculados (Atleta, Sesión o Evento) fueron alterados y no existen en el sistema.');
                return false;
            }
            error_log("Error en eliminarMarca: " . $e->getMessage());
            return false;
        }
    }

   
    private function reactivarMarca(): bool {
        try {
            $sql = "UPDATE marcas 
                    SET estado = 'Activo', motivo_eliminacion = NULL 
                    WHERE id_marca = :id";
            
            $stmt = $this->pdo->prepare($sql);
            
            $stmt->bindValue(':id', (int)$this->datos['id_marca'], PDO::PARAM_INT);
            $stmt->execute();
           
            return true;
            
        } catch (PDOException $e) {

             if ($e->getCode() == 23000) {
                $this->agregarError('integridad', 'Los datos vinculados (Atleta, Sesión o Evento) fueron alterados y no existen en el sistema.');
                return false;
            }
            
            error_log("Error en reactivarMarca: " . $e->getMessage());
            return false;
        }
    }

    public function listarMarcas(string $estado = 'Activo', int $id_atleta = 0, int $distancia = 0, string $estilo = '', string $piscina = ''): array {
        
        $estadosPermitidos = ['Activo', 'Inactivo'];
        if (!in_array($estado, $estadosPermitidos)) {
            return [];
        }

        try {
            $sql = "SELECT m.id_marca, m.estilo, m.distancia_m, m.tipo_piscina, 
                        m.tiempo_final_seg, m.fecha, m.es_pb, 
                        IF(m.id_evento IS NOT NULL, e.tipo, 'Control') AS nivel_evento,
                        CONCAT(a.nombres, ' ', a.apellidos) as nombre_atleta, a.cedula 
                    FROM marcas m 
                    LEFT JOIN eventos e ON m.id_evento = e.id_evento
                    INNER JOIN atletas a ON m.id_atleta = a.id_atleta 
                    WHERE m.estado = :estado";

        
           
            if ($id_atleta > 0) {
                $sql .= " AND m.id_atleta = :id_atleta";
            }
            if ($distancia > 0) {
                $sql .= " AND m.distancia_m = :distancia";
            }
            if ($estilo !== '') {
                $sql .= " AND m.estilo = :estilo";
            }
            if ($piscina !== '') {
                $sql .= " AND m.tipo_piscina = :piscina";
            }
            
            $sql .= " ORDER BY m.fecha DESC";
            
            $stmt = $this->pdo->prepare($sql);
            
            
            $stmt->bindValue(':estado', $estado, PDO::PARAM_STR);
            
            if ($id_atleta > 0) {
                $stmt->bindValue(':id_atleta', $id_atleta, PDO::PARAM_INT);
            }
            if ($distancia > 0) {
                $stmt->bindValue(':distancia', $distancia, PDO::PARAM_INT);
            }
            if ($estilo !== '') {
                $stmt->bindValue(':estilo', $estilo, PDO::PARAM_STR);
            }
            if ($piscina !== '') {
                $stmt->bindValue(':piscina', $piscina, PDO::PARAM_STR);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error crítico en filtros listarMarcas: " . $e->getMessage());
            return [];
        }
    }


   /**
     * Extrae el desglose científico de una marca y la cronología evolutiva del atleta.
     * Genera un payload anidado ideal para renderizado de Dashboards y Gráficas.
     */

public function obtenerDetallePorId(int $id_marca): ?array {
        try {
            // 1. Obtener datos base (Si no existe, cortamos la ejecución temprano)
            $marca = $this->obtenerMarcaBase($id_marca);
            if (!$marca) return null;

            // 2. Orquestar el resto de las consultas usando métodos privados
            $marca['splits']              = $this->obtenerSplits($id_marca);
            $marca['swolf_data']          = $this->obtenerSwolf($id_marca);
            $marca['historial_evolucion'] = $this->obtenerHistorialEvolucion(
                (int)$marca['id_atleta'], 
                $marca['estilo'], 
                (int)$marca['distancia_m'], 
                $marca['tipo_piscina']
            );

            return $marca;
            
        } catch (PDOException $e) {
            error_log("Error en extractor de detalles: " . $e->getMessage());
            return null;
        }
    }

    // =========================================================================
    // MÉTODOS PRIVADOS DE EXTRACCIÓN (SRP)
    // =========================================================================

    private function obtenerMarcaBase(int $id_marca): ?array {
        $sql = "SELECT m.*, a.nombres as atleta_nombres, a.apellidos as atleta_apellidos, a.cedula 
                FROM marcas m 
                INNER JOIN atletas a ON m.id_atleta = a.id_atleta 
                WHERE m.id_marca = :id_marca";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_marca', $id_marca, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function obtenerSplits(int $id_marca): array {
        $sql = "SELECT distancia_parcial_m, tiempo_parcial_seg, tiempo_viraje_seg
                FROM marcas_splits 
                WHERE id_marca = :id_marca 
                ORDER BY parcial_numero ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_marca', $id_marca, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function obtenerSwolf(int $id_marca): ?array {
        $sql = "SELECT num_brazadas, swolf FROM marcas_swolf WHERE id_marca = :id_marca";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_marca', $id_marca, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function obtenerHistorialEvolucion(int $idAtleta, string $estilo, int $distancia, string $piscina): array {
        $sql = "SELECT fecha, tiempo_final_seg 
                FROM marcas 
                WHERE id_atleta = :id_atleta 
                AND estilo = :estilo 
                AND distancia_m = :distancia_m 
                AND tipo_piscina = :tipo_piscina 
                AND estado = 'Activo' 
                ORDER BY fecha ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_atleta', $idAtleta, PDO::PARAM_INT);
        $stmt->bindValue(':estilo', $estilo, PDO::PARAM_STR);
        $stmt->bindValue(':distancia_m', $distancia, PDO::PARAM_INT);
        $stmt->bindValue(':tipo_piscina', $piscina, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


/*     public function obtenerDetallePorId(int $id_marca): ?array {
        
        try {
            $sqlBase = "SELECT m.*, a.nombres as atleta_nombres, a.apellidos as atleta_apellidos, a.cedula 
                        FROM marcas m 
                        INNER JOIN atletas a ON m.id_atleta = a.id_atleta 
                        WHERE m.id_marca = :id_marca";
            
            $stmtBase = $this->pdo->prepare($sqlBase);
            $stmtBase->bindValue(':id_marca', $id_marca, PDO::PARAM_INT);
            $stmtBase->execute();
            
            $marca = $stmtBase->fetch(PDO::FETCH_ASSOC);

            if (!$marca) return null;

            $sqlSplits = "SELECT distancia_parcial_m, tiempo_parcial_seg 
                          FROM marcas_splits 
                          WHERE id_marca = :id_marca 
                          ORDER BY parcial_numero ASC";
            
            $stmtSplits = $this->pdo->prepare($sqlSplits);
            $stmtSplits->bindValue(':id_marca', $id_marca, PDO::PARAM_INT);
            $stmtSplits->execute();
            
            $marca['splits'] = $stmtSplits->fetchAll(PDO::FETCH_ASSOC);

            $sqlSwolf = "SELECT num_brazadas, swolf FROM marcas_swolf WHERE id_marca = :id_marca";
            
            $stmtSwolf = $this->pdo->prepare($sqlSwolf);
            $stmtSwolf->bindValue(':id_marca', $id_marca, PDO::PARAM_INT);
            $stmtSwolf->execute();
            
            $marca['swolf_data'] = $stmtSwolf->fetch(PDO::FETCH_ASSOC) ?: null;

            // Serie temporal para la Gráfica de Evolución
            // Filtra estrictamente por las mismas características biométricas de la prueba
            $sqlHistorial = "SELECT fecha, tiempo_final_seg 
                             FROM marcas 
                             WHERE id_atleta = :id_atleta 
                             AND estilo = :estilo 
                             AND distancia_m = :distancia_m 
                             AND tipo_piscina = :tipo_piscina 
                             AND estado = 'Activo' 
                             ORDER BY fecha ASC";
            
            $stmtHistorial = $this->pdo->prepare($sqlHistorial);
            
            $stmtHistorial->bindValue(':id_atleta', (int)$marca['id_atleta'], PDO::PARAM_INT);
            $stmtHistorial->bindValue(':estilo', $marca['estilo'], PDO::PARAM_STR);
            $stmtHistorial->bindValue(':distancia_m', (int)$marca['distancia_m'], PDO::PARAM_INT);
            $stmtHistorial->bindValue(':tipo_piscina', $marca['tipo_piscina'], PDO::PARAM_STR);
            
            $stmtHistorial->execute();
            $marca['historial_evolucion'] = $stmtHistorial->fetchAll(PDO::FETCH_ASSOC);

            return $marca;
            
        } catch (PDOException $e) {
            error_log("Error en extractor de detalles: " . $e->getMessage());
            return null;
        }
    } */

    /**
     * Recupera los datos básicos de una marca para orquestar notificaciones y bitácora
     */
    public function obtenerInfoBasicaMarca(int $id_marca): array {
        try {
            $sql = "SELECT id_atleta, distancia_m, estilo, tiempo_final_seg 
                    FROM marcas 
                    WHERE id_marca = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id_marca, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException $e) {
            error_log("Error al recuperar info básica de la marca: " . $e->getMessage());
            return [];
        }
    }

    // =====================================================================
    // MÉTODOS PARA LA PANTALLA PÚBLICA (PIZARRA EN VIVO)
    // =====================================================================
/*     public function obtenerTelemetriaActual(): ?array {
        try {
            // Buscamos el registro activo. LIMIT 1 asume 1 nadador a la vez.
            $sql = "SELECT t.*, a.nombres, a.apellidos, a.cedula 
                    FROM telemetria_live t 
                    INNER JOIN atletas a ON t.id_atleta = a.id_atleta 
                    LIMIT 1";
                    
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e) {
            error_log("Error al consultar telemetría pública: " . $e->getMessage());
            return null;
        }
    } */

public function obtenerTelemetriaActual(int $id_atleta): ?array {
    try {
        // Ahora filtramos estrictamente por el ID de la sala/atleta
        $sql = "SELECT t.*, a.nombres, a.apellidos, a.cedula 
                FROM telemetria_live t 
                INNER JOIN atletas a ON t.id_atleta = a.id_atleta 
                WHERE t.id_atleta = :id_atleta
                LIMIT 1";
                
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_atleta', $id_atleta, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    } catch (\PDOException $e) {
        error_log("Error al consultar telemetría del atleta $id_atleta: " . $e->getMessage());
        return null;
    }
}

public function obtenerLobbyActivo(): array {
    try {
        // 1. GARBAGE COLLECTOR: Eliminar carreras huérfanas (sin actualización en 15 mins)
        $this->pdo->exec("DELETE FROM telemetria_live WHERE ultima_actualizacion < (NOW() - INTERVAL 15 MINUTE)");

        // 2. OBTENER SALAS ACTIVAS
        $sql = "SELECT t.id_atleta, t.distancia_total, t.tipo_piscina, t.estilo, t.estado_carrera, t.ultima_actualizacion,
                       a.nombres, a.apellidos, a.cedula 
                FROM telemetria_live t 
                INNER JOIN atletas a ON t.id_atleta = a.id_atleta 
                ORDER BY t.ultima_actualizacion DESC";
                
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
        error_log("Error al consultar el lobby en vivo: " . $e->getMessage());
        return [];
    }
}





}



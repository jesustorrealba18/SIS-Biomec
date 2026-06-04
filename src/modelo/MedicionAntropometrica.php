<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;


class MedicionAntropometrica extends Conexion {
    use ValidacionesTrait;
    use AutoBinderTrait;

    // =====================================================================
    // 1. ENCAPSULAMIENTO ESTRICTO
    // =====================================================================
    /**
     * Bolsa de propiedades encapsulada. Único punto de almacenamiento de datos.
     */
    private array $datos = [];

    /**
     * Lista blanca de campos permitidos (Protección contra Asignación Masiva)
     * Campos alineados con la tabla `mediciones_antropometricas` del esquema SQL.
     */
    private array $camposPermitidos = [
        'id_atleta', 'fecha', 'peso_kg', 'talla_cm',
        'envergadura_cm', 'perimetro_abdominal_cm',
        'imc', 'porcentaje_grasa', 'responsable'
    ];

    // NO SE DECLARA CONSTRUCTOR: PHP invoca automáticamente el de la clase Conexion.

    // =====================================================================
    // 2. HIDRATACIÓN Y DEPURACIÓN INTERNA (MÉTODOS PRIVADOS)
    // =====================================================================
    /**
     * Mapea el payload externo al arreglo privado filtrando campos basura.
     */
    private function setAtributos(array $payload): void {
        foreach ($this->camposPermitidos as $campo) {
            if (isset($payload[$campo]) && $payload[$campo] !== '') {
                $this->datos[$campo] = $payload[$campo];
            } else {
                $this->datos[$campo] = null;
            }
        }
    }

    /**
     * Aplica las reglas de negocio sobre el estado del objeto.
     */
    private function validarAtributosInternos(): bool {
        $this->resetearErrores();

        // Validaciones obligatorias extrayendo de la bolsa encapsulada (RF-05.1)
        $this->requerido((string)($this->datos['id_atleta'] ?? ''), 'id_atleta');
        $this->requerido((string)($this->datos['fecha']     ?? ''), 'fecha');
        $this->requerido((string)($this->datos['peso_kg']   ?? ''), 'peso_kg');
        $this->requerido((string)($this->datos['talla_cm']  ?? ''), 'talla_cm');
        $this->requerido((string)($this->datos['envergadura_cm']         ?? ''), 'envergadura_cm');
        $this->requerido((string)($this->datos['perimetro_abdominal_cm'] ?? ''), 'perimetro_abdominal_cm');
        $this->requerido((string)($this->datos['responsable'] ?? ''), 'responsable');

        // Impedir fechas futuras para mantener coherencia de datos
        if (!empty($this->datos['fecha']) && $this->datos['fecha'] > date('Y-m-d')) {
            $this->agregarError('fecha', 'La fecha de la evaluación no puede ser una fecha futura.');
        }

        // Reglas de integridad numérica
        if (!empty($this->datos['peso_kg']) && (float)$this->datos['peso_kg'] <= 0) {
            $this->agregarError('peso_kg', 'El peso debe ser mayor a 0 kg.');
        }
        if (!empty($this->datos['talla_cm']) && (float)$this->datos['talla_cm'] <= 0) {
            $this->agregarError('talla_cm', 'La talla debe ser mayor a 0 cm.');
        }
        if (!empty($this->datos['envergadura_cm']) && (float)$this->datos['envergadura_cm'] <= 0) {
            $this->agregarError('envergadura_cm', 'La envergadura debe ser mayor a 0 cm.');
        }
        if (!empty($this->datos['perimetro_abdominal_cm']) && (float)$this->datos['perimetro_abdominal_cm'] <= 0) {
            $this->agregarError('perimetro_abdominal_cm', 'El perímetro abdominal debe ser mayor a 0 cm.');
        }

        return empty($this->obtenerErrores());
    }

    // =====================================================================
    // 3. OPERACIONES TRANSACCIONALES (Cumpliendo ACID)
    // =====================================================================

    /**
     * RF-05.1: Registra una nueva medición calculando automáticamente el IMC.
     */
    public function registrarMedicion(array $payload): bool {

        // 1. Hidratación interna segura
        $this->setAtributos($payload);

        // 2. Autovalidación de integridad
        if (!$this->validarAtributosInternos()) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $peso_kg  = (float)$this->datos['peso_kg'];
            $talla_cm = (float)$this->datos['talla_cm'];

            // -----------------------------------------------------------------
            // A) CÁLCULO AUTOMÁTICO DEL IMC (RF-05.1)
            // Fórmula: Peso (kg) / Talla (m)²
            // -----------------------------------------------------------------
            $talla_m = $talla_cm / 100;
            $imc_calculado = round($peso_kg / ($talla_m * $talla_m), 1);

            // Inyectamos el IMC calculado en la bolsa de datos
            $this->datos['imc'] = $imc_calculado;

            // -----------------------------------------------------------------
            // B) INSERCIÓN EN LA TABLA PRINCIPAL: `mediciones_antropometricas`
            // -----------------------------------------------------------------
            $sqlInsert = "INSERT INTO mediciones_antropometricas 
                            (id_atleta, fecha, peso_kg, talla_cm, envergadura_cm, 
                             perimetro_abdominal_cm, imc, porcentaje_grasa, responsable) 
                          VALUES 
                            (:id_atleta, :fecha, :peso_kg, :talla_cm, :envergadura_cm,
                             :perimetro_abdominal_cm, :imc, :porcentaje_grasa, :responsable)";

            $stmt = $this->pdo->prepare($sqlInsert);

            $mapaPrincipal = [
                ':id_atleta'              => ['id_atleta',              PDO::PARAM_INT],
                ':fecha'                  => ['fecha',                  PDO::PARAM_STR],
                ':peso_kg'                => ['peso_kg',                PDO::PARAM_STR],
                ':talla_cm'               => ['talla_cm',               PDO::PARAM_STR],
                ':envergadura_cm'         => ['envergadura_cm',         PDO::PARAM_STR],
                ':perimetro_abdominal_cm' => ['perimetro_abdominal_cm', PDO::PARAM_STR],
                ':imc'                    => ['imc',                    PDO::PARAM_STR],
                ':porcentaje_grasa'       => ['porcentaje_grasa',       PDO::PARAM_STR],
                ':responsable'            => ['responsable',            PDO::PARAM_STR],
            ];

            $this->autoBind($stmt, $mapaPrincipal, $this->datos);
            $stmt->execute();

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error en transacción de medición antropométrica: " . $e->getMessage());
            return false;
        }
    }

    /**
     * RF-05.3: Actualiza una medición existente, recalculando el IMC dinámicamente.
     * La auditoría en Bitácora es orquestada por el Controlador.
     */
    public function actualizarMedicion(array $payload, int $id_medicion): bool {

        // 1. Hidratación y validación estricta
        $this->setAtributos($payload);

        if (!$this->validarAtributosInternos()) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $peso_kg  = (float)$this->datos['peso_kg'];
            $talla_cm = (float)$this->datos['talla_cm'];

            // Recálculo dinámico del IMC tras corrección de errores de transcripción (RF-05.3)
            $talla_m = $talla_cm / 100;
            $imc_calculado = round($peso_kg / ($talla_m * $talla_m), 1);

            $this->datos['imc'] = $imc_calculado;

            // -----------------------------------------------------------------
            // A) ACTUALIZACIÓN DE LA TABLA PRINCIPAL
            // -----------------------------------------------------------------
            $sqlUpdate = "UPDATE mediciones_antropometricas SET
                            fecha                  = :fecha,
                            peso_kg                = :peso_kg,
                            talla_cm               = :talla_cm,
                            envergadura_cm         = :envergadura_cm,
                            perimetro_abdominal_cm = :perimetro_abdominal_cm,
                            imc                    = :imc,
                            porcentaje_grasa       = :porcentaje_grasa,
                            responsable            = :responsable
                          WHERE id_medicion = :id_medicion_condicion";

            $stmt = $this->pdo->prepare($sqlUpdate);

            $mapaPrincipal = [
                ':fecha'                  => ['fecha',                  PDO::PARAM_STR],
                ':peso_kg'                => ['peso_kg',                PDO::PARAM_STR],
                ':talla_cm'               => ['talla_cm',               PDO::PARAM_STR],
                ':envergadura_cm'         => ['envergadura_cm',         PDO::PARAM_STR],
                ':perimetro_abdominal_cm' => ['perimetro_abdominal_cm', PDO::PARAM_STR],
                ':imc'                    => ['imc',                    PDO::PARAM_STR],
                ':porcentaje_grasa'       => ['porcentaje_grasa',       PDO::PARAM_STR],
                ':responsable'            => ['responsable',            PDO::PARAM_STR],
            ];

            // Pasamos el ID de la medición como variable local para la condición WHERE
            $this->autoBind($stmt, $mapaPrincipal, $this->datos, [
                'id_medicion_condicion' => $id_medicion
            ]);

            $stmt->execute();

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error en actualización de medición antropométrica: " . $e->getMessage());
            return false;
        }
    }

    // =====================================================================
    // 4. MÉTODOS DE CONSULTA Y ESTADO (Listados y Soft Delete)
    // =====================================================================

    /**
     * RF-05: Listado con filtros dinámicos.
     * Permite filtrar por atleta, rango de fechas o responsable.
     */
    public function listarMediciones(
        int    $id_atleta   = 0,
        string $fecha_inicio = '',
        string $fecha_fin    = ''
    ): array {

        try {
            $sql = "SELECT m.id_medicion, m.fecha, m.peso_kg, m.talla_cm,
                           m.envergadura_cm, m.perimetro_abdominal_cm, 
                           m.imc, m.porcentaje_grasa, m.responsable,
                           CONCAT(a.nombres, ' ', a.apellidos) AS nombre_atleta, a.cedula
                    FROM mediciones_antropometricas m
                    INNER JOIN atletas a ON m.id_atleta = a.id_atleta
                    WHERE 1 = 1";

            // CONCATENACIÓN DINÁMICA DE FILTROS
            if ($id_atleta > 0) {
                $sql .= " AND m.id_atleta = :id_atleta";
            }
            if ($fecha_inicio !== '') {
                $sql .= " AND m.fecha >= :fecha_inicio";
            }
            if ($fecha_fin !== '') {
                $sql .= " AND m.fecha <= :fecha_fin";
            }

            $sql .= " ORDER BY m.fecha DESC";

            $stmt = $this->pdo->prepare($sql);

            // ENLACES ESTRICTOS con tipado fuerte
            if ($id_atleta > 0) {
                $stmt->bindValue(':id_atleta', $id_atleta, PDO::PARAM_INT);
            }
            if ($fecha_inicio !== '') {
                $stmt->bindValue(':fecha_inicio', $fecha_inicio, PDO::PARAM_STR);
            }
            if ($fecha_fin !== '') {
                $stmt->bindValue(':fecha_fin', $fecha_fin, PDO::PARAM_STR);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error crítico en listarMediciones: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Extrae el detalle completo de una medición y la cronología evolutiva del atleta.
     * Genera un payload anidado ideal para renderizado de Dashboards y Gráficas (Chart.js).
     */
    public function obtenerDetallePorId(int $id_medicion): ?array {

        try {
            // 1. Datos base de la medición e identificación del atleta
            $sqlBase = "SELECT m.*, CONCAT(a.nombres, ' ', a.apellidos) AS nombre_atleta, a.cedula
                        FROM mediciones_antropometricas m
                        INNER JOIN atletas a ON m.id_atleta = a.id_atleta
                        WHERE m.id_medicion = :id_medicion";

            $stmtBase = $this->pdo->prepare($sqlBase);
            // Tipado estricto (Seguridad contra inyección y type-juggling)
            $stmtBase->bindValue(':id_medicion', $id_medicion, PDO::PARAM_INT);
            $stmtBase->execute();

            $medicion = $stmtBase->fetch(PDO::FETCH_ASSOC);

            // Si el ID no existe en la BD, abortamos con Early Return
            if (!$medicion) return null;

            // 2. Serie temporal para la Gráfica de Evolución (RF-05.2)
            // Extrae todas las mediciones del atleta ordenadas cronológicamente para Chart.js
            $sqlHistorial = "SELECT fecha, peso_kg, talla_cm, imc, porcentaje_grasa
                             FROM mediciones_antropometricas
                             WHERE id_atleta = :id_atleta
                             ORDER BY fecha ASC";

            $stmtHistorial = $this->pdo->prepare($sqlHistorial);
            // Casting explícito al extraer datos del array para garantizar integridad de tipos en MySQL
            $stmtHistorial->bindValue(':id_atleta', (int)$medicion['id_atleta'], PDO::PARAM_INT);
            $stmtHistorial->execute();

            $medicion['historial_evolucion'] = $stmtHistorial->fetchAll(PDO::FETCH_ASSOC);

            return $medicion;

        } catch (PDOException $e) {
            error_log("Error en extractor de detalles de medición: " . $e->getMessage());
            return null;
        }
    }

    /**
     * RF-05: Dashboard de Seguimiento Antropométrico (Vista Central).
     * Extrae a todos los atletas activos con su evaluación física más reciente
     * y calcula los días transcurridos para facilitar alertas en la vista.
     * Prioriza a aquellos con más días sin evaluación (alertas rojas primero).
     */
    public function obtenerDashboardPrincipal(): array {
        try {
            $sql = "SELECT a.id_atleta, a.nombres, a.apellidos, a.cedula,
                           m.id_medicion,
                           m.fecha        AS ultima_fecha,
                           m.peso_kg, m.talla_cm, m.imc, m.porcentaje_grasa,
                           DATEDIFF(CURRENT_DATE, m.fecha) AS dias_sin_evaluacion
                    FROM atletas a
                    LEFT JOIN mediciones_antropometricas m
                        ON m.id_atleta = a.id_atleta
                        AND m.fecha = (
                            SELECT MAX(fecha)
                            FROM mediciones_antropometricas
                            WHERE id_atleta = a.id_atleta
                        )
                    WHERE a.estado = 'Activo'
                    ORDER BY dias_sin_evaluacion DESC, a.nombres ASC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error en obtenerDashboardPrincipal: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Elimina físicamente un registro de medición por su ID.
     * NOTA: La tabla `mediciones_antropometricas` no posee columna `estado` ni
     * `motivo_eliminacion`, por lo que se realiza un DELETE real (hard delete).
     * Se recomienda al equipo evaluar añadir esas columnas para auditoría futura.
     */
    public function eliminarMedicion(int $id_medicion): bool {
        try {
            $sql = "DELETE FROM mediciones_antropometricas WHERE id_medicion = :id_medicion";

            $stmt = $this->pdo->prepare($sql);
            // Tipado fuerte para el ID numérico
            $stmt->bindValue(':id_medicion', $id_medicion, PDO::PARAM_INT);

            return $stmt->execute();

        } catch (PDOException $e) {
            error_log("Error en eliminarMedicion: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retorna los errores de validación generados por validarAtributosInternos().
     * Permite al Controlador inspeccionar el detalle de fallos de negocio.
     */
    public function obtenerErroresValidacion(): array {
        return $this->obtenerErrores();
    }
}
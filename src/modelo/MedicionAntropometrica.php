<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class MedicionAntropometrica extends Conexion {
    use ValidacionesTrait;
    use AutoBinderTrait;

    // =====================================================================
    // 1. ENCAPSULAMIENTO ESTRICTO Y LISTA BLANCA (REGLA 1)
    // =====================================================================
    private array $datos = [];

    // Lista estricta basada exactamente en los campos de tu base de datos
    private array $camposPermitidos = [
        'id_medicion', 'id_atleta', 'fecha', 'peso_kg', 'talla_cm',
        'envergadura_cm', 'perimetro_abdominal_cm',
        'imc', 'porcentaje_grasa', 'responsable',
        // Variables de filtro para listados
        'filtro_id_atleta', 'filtro_fecha_inicio', 'filtro_fecha_fin'
    ];

    // =====================================================================
    // 2. HIDRATACIÓN DEL MODELO (REGLA 2)
    // =====================================================================
    /**
     * Siempre usa setDatos para cargar información de manera controlada.
     */
    public function setDatos(array $datosExternos): void {
        foreach ($this->camposPermitidos as $campo) {
            if (isset($datosExternos[$campo]) && $datosExternos[$campo] !== '') {
                $this->datos[$campo] = $datosExternos[$campo];
            } elseif (!array_key_exists($campo, $this->datos)) {
                $this->datos[$campo] = null; // Mantiene el esquema limpio
            }
        }
    }

    // =====================================================================
    // 3. MÉTODOS PÚBLICOS: VALIDACIÓN Y ORQUESTACIÓN (REGLAS 4 y 6)
    // =====================================================================
    
    public function registrarMedicion(array $datos): bool {
        // 1. Cargamos datos con setDatos
        $this->setDatos($datos);

        // 2. Método público valida que los datos sean correctos antes de operar
        if (!$this->validarDatos()) {
            return false;
        }

        // 3. Llamadas a métodos privados sin pasarles parámetros
        $this->calcularIMC();
        return $this->insertarMedicionBD();
    }

    public function actualizarMedicion(array $datos, int $id_medicion): bool {
        // Validación de parámetro adicional (Regla 6)
        if ($id_medicion <= 0) {
            $this->agregarError('id_medicion', 'Identificador de medición inválido.');
            return false;
        }

        $this->setDatos($datos);
        
        // Guardamos el parámetro adicional en nuestro atributo propio
        $this->datos['id_medicion'] = $id_medicion;

        if (!$this->validarDatos()) {
            return false;
        }

        $this->calcularIMC();
        return $this->actualizarMedicionBD();
    }

    public function eliminarMedicion(int $id_medicion): bool {
        // Validación de parámetro adicional
        if ($id_medicion <= 0) {
            return false;
        }

        // Almacenamiento en atributo propio para el método privado
        $this->datos['id_medicion'] = $id_medicion;
        return $this->eliminarMedicionBD();
    }

  

    public function obtenerDetallePorId(int $id_medicion): ?array {
        if ($id_medicion <= 0) return null;
        
        $this->datos['id_medicion'] = $id_medicion;
        return $this->ejecutarConsultaDetalle();
    }

   /*  public function obtenerDashboardPrincipal(): array {
        return $this->ejecutarConsultaDashboard();
    } */
   public function obtenerDashboardPrincipal(int $id_atleta = 0, int $id_usuario = 0, string $rol = ''): array {
    // Ahora pasamos correctamente todos los parámetros
    return $this->ejecutarConsultaDashboard($id_atleta, $id_usuario, $rol);
}

    public function obtenerErroresValidacion(): array {
        return $this->obtenerErrores();
    }

    // =====================================================================
    // 4. MÉTODOS PRIVADOS: ATÓMICOS, SIN PARÁMETROS Y SIN REGLAS (REGLAS 3 y 5)
    // =====================================================================
    
    /**
     * Valida reglas de negocio apoyándose netamente en el arreglo propio $this->datos
     */
    private function validarDatos(): bool {
        $this->resetearErrores();

        $this->requerido((string)($this->datos['id_atleta'] ?? ''), 'id_atleta');
        $this->requerido((string)($this->datos['fecha'] ?? ''), 'fecha');
        $this->requerido((string)($this->datos['peso_kg'] ?? ''), 'peso_kg');
        $this->requerido((string)($this->datos['talla_cm'] ?? ''), 'talla_cm');
        $this->requerido((string)($this->datos['envergadura_cm'] ?? ''), 'envergadura_cm');
        $this->requerido((string)($this->datos['perimetro_abdominal_cm'] ?? ''), 'perimetro_abdominal_cm');
        $this->requerido((string)($this->datos['responsable'] ?? ''), 'responsable');

        if (!empty($this->datos['fecha']) && $this->datos['fecha'] > date('Y-m-d')) {
            $this->agregarError('fecha', 'La fecha no puede ser futura.');
        }
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

    /**
     * Cálculo atómico interno utilizando $this->datos
     */
    private function calcularIMC(): void {
        $peso_kg  = (float)$this->datos['peso_kg'];
        $talla_m = ((float)$this->datos['talla_cm']) / 100;
        
        $this->datos['imc'] = round($peso_kg / ($talla_m * $talla_m), 1);
    }

    /**
     * Operación SQL atómica para INSERCIÓN
     */
    private function insertarMedicionBD(): bool {
        try {
            $sql = "INSERT INTO mediciones_antropometricas 
                        (id_atleta, fecha, peso_kg, talla_cm, envergadura_cm, 
                         perimetro_abdominal_cm, imc, porcentaje_grasa, responsable) 
                    VALUES 
                        (:id_atleta, :fecha, :peso_kg, :talla_cm, :envergadura_cm,
                         :perimetro_abdominal_cm, :imc, :porcentaje_grasa, :responsable)";

            $stmt = $this->pdo->prepare($sql);
            $mapa = [
                ':id_atleta'              => ['id_atleta', PDO::PARAM_INT],
                ':fecha'                  => ['fecha', PDO::PARAM_STR],
                ':peso_kg'                => ['peso_kg', PDO::PARAM_STR],
                ':talla_cm'               => ['talla_cm', PDO::PARAM_STR],
                ':envergadura_cm'         => ['envergadura_cm', PDO::PARAM_STR],
                ':perimetro_abdominal_cm' => ['perimetro_abdominal_cm', PDO::PARAM_STR],
                ':imc'                    => ['imc', PDO::PARAM_STR],
                ':porcentaje_grasa'       => ['porcentaje_grasa', PDO::PARAM_STR],
                ':responsable'            => ['responsable', PDO::PARAM_STR],
            ];

            $this->autoBind($stmt, $mapa, $this->datos);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en insertarMedicionBD: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Operación SQL atómica para ACTUALIZACIÓN
     */
    private function actualizarMedicionBD(): bool {
        try {
            $sql = "UPDATE mediciones_antropometricas SET
                        fecha                  = :fecha,
                        peso_kg                = :peso_kg,
                        talla_cm               = :talla_cm,
                        envergadura_cm         = :envergadura_cm,
                        perimetro_abdominal_cm = :perimetro_abdominal_cm,
                        imc                    = :imc,
                        porcentaje_grasa       = :porcentaje_grasa,
                        responsable            = :responsable
                    WHERE id_medicion = :id_medicion";

            $stmt = $this->pdo->prepare($sql);
            $mapa = [
                ':fecha'                  => ['fecha', PDO::PARAM_STR],
                ':peso_kg'                => ['peso_kg', PDO::PARAM_STR],
                ':talla_cm'               => ['talla_cm', PDO::PARAM_STR],
                ':envergadura_cm'         => ['envergadura_cm', PDO::PARAM_STR],
                ':perimetro_abdominal_cm' => ['perimetro_abdominal_cm', PDO::PARAM_STR],
                ':imc'                    => ['imc', PDO::PARAM_STR],
                ':porcentaje_grasa'       => ['porcentaje_grasa', PDO::PARAM_STR],
                ':responsable'            => ['responsable', PDO::PARAM_STR],
                ':id_medicion'            => ['id_medicion', PDO::PARAM_INT], // Usa el atributo interno
            ];

            $this->autoBind($stmt, $mapa, $this->datos);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en actualizarMedicionBD: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Operación SQL atómica para ELIMINACIÓN
     */
    private function eliminarMedicionBD(): bool {
        try {
            $sql = "DELETE FROM mediciones_antropometricas WHERE id_medicion = :id_medicion";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id_medicion', $this->datos['id_medicion'], PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en eliminarMedicionBD: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Operación SQL atómica para LISTADOS
     */
    private function ejecutarConsultaListado(): array {
        try {
            $sql = "SELECT m.id_medicion, m.fecha, m.peso_kg, m.talla_cm,
                           m.envergadura_cm, m.perimetro_abdominal_cm, 
                           m.imc, m.porcentaje_grasa, m.responsable,
                           CONCAT(a.nombres, ' ', a.apellidos) AS nombre_atleta, a.cedula
                    FROM mediciones_antropometricas m
                    INNER JOIN atletas a ON m.id_atleta = a.id_atleta
                    WHERE 1 = 1";

            if ($this->datos['filtro_id_atleta'] > 0) {
                $sql .= " AND m.id_atleta = :id_atleta";
            }
            if ($this->datos['filtro_fecha_inicio'] !== '') {
                $sql .= " AND m.fecha >= :fecha_inicio";
            }
            if ($this->datos['filtro_fecha_fin'] !== '') {
                $sql .= " AND m.fecha <= :fecha_fin";
            }
            $sql .= " ORDER BY m.fecha DESC";

            $stmt = $this->pdo->prepare($sql);

            if ($this->datos['filtro_id_atleta'] > 0) {
                $stmt->bindValue(':id_atleta', $this->datos['filtro_id_atleta'], PDO::PARAM_INT);
            }
            if ($this->datos['filtro_fecha_inicio'] !== '') {
                $stmt->bindValue(':fecha_inicio', $this->datos['filtro_fecha_inicio'], PDO::PARAM_STR);
            }
            if ($this->datos['filtro_fecha_fin'] !== '') {
                $stmt->bindValue(':fecha_fin', $this->datos['filtro_fecha_fin'], PDO::PARAM_STR);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ejecutarConsultaListado: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Operación SQL atómica para DETALLES (Incluye Historial)
     */
    private function ejecutarConsultaDetalle(): ?array {
        try {
            $sqlBase = "SELECT m.*, CONCAT(a.nombres, ' ', a.apellidos) AS nombre_atleta, a.cedula
                        FROM mediciones_antropometricas m
                        INNER JOIN atletas a ON m.id_atleta = a.id_atleta
                        WHERE m.id_medicion = :id_medicion";
            
            $stmtBase = $this->pdo->prepare($sqlBase);
            $stmtBase->bindValue(':id_medicion', $this->datos['id_medicion'], PDO::PARAM_INT);
            $stmtBase->execute();
            $medicion = $stmtBase->fetch(PDO::FETCH_ASSOC);

            if (!$medicion) return null;

            $sqlHistorial = "SELECT fecha, peso_kg, talla_cm, imc, porcentaje_grasa
                             FROM mediciones_antropometricas
                             WHERE id_atleta = :id_atleta
                             ORDER BY fecha ASC";
            
            $stmtHistorial = $this->pdo->prepare($sqlHistorial);
            $stmtHistorial->bindValue(':id_atleta', (int)$medicion['id_atleta'], PDO::PARAM_INT);
            $stmtHistorial->execute();

            $medicion['historial_evolucion'] = $stmtHistorial->fetchAll(PDO::FETCH_ASSOC);
            return $medicion;
        } catch (PDOException $e) {
            error_log("Error en ejecutarConsultaDetalle: " . $e->getMessage());
            return null;
        }
    }



/*    private function ejecutarConsultaDashboard(int $id_atleta = 0): array {
    try {
        $sql = "SELECT a.id_atleta, a.nombres, a.apellidos, a.cedula,
                       c.nombre AS categoria,
                       m.id_medicion,
                       m.fecha AS ultima_fecha,
                       m.peso_kg AS peso, 
                       m.talla_cm AS talla, 
                       m.imc, m.porcentaje_grasa, m.responsable,
                       m.deleted_at,
                       DATEDIFF(CURRENT_DATE, m.fecha) AS dias_sin_evaluacion
                FROM atletas a
                LEFT JOIN categorias_feveda c ON a.id_categoria = c.id_categoria
                LEFT JOIN mediciones_antropometricas m
                    ON m.id_atleta = a.id_atleta
                    AND m.fecha = (
                        SELECT MAX(fecha)
                        FROM mediciones_antropometricas
                        WHERE id_atleta = a.id_atleta
                        AND deleted_at IS NULL
                    )
                    AND m.deleted_at IS NULL
                WHERE a.estado = 'Activo'";
        if ($id_atleta > 0) {
            $sql .= " AND a.id_atleta = :id_atleta";
        }
        $sql .= " ORDER BY dias_sin_evaluacion DESC, a.nombres ASC";

        $stmt = $this->pdo->prepare($sql);
        if ($id_atleta > 0) {
            $stmt->bindValue(':id_atleta', $id_atleta, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error en ejecutarConsultaDashboard: " . $e->getMessage());
        return [];
    }
} */


    private function ejecutarConsultaDashboard(int $id_atleta = 0, int $id_usuario = 0, string $rol = ''): array {
    try {
        $sql = "SELECT a.id_atleta, a.nombres, a.apellidos, a.cedula,
                       c.nombre AS categoria,
                       m.id_medicion,
                       m.fecha AS ultima_fecha,
                       m.peso_kg AS peso, 
                       m.talla_cm AS talla, 
                       m.imc, m.porcentaje_grasa, m.responsable,
                       m.deleted_at,
                       DATEDIFF(CURRENT_DATE, m.fecha) AS dias_sin_evaluacion
                FROM atletas a
                LEFT JOIN categorias_feveda c ON a.id_categoria = c.id_categoria
                LEFT JOIN mediciones_antropometricas m
                    ON m.id_atleta = a.id_atleta
                    AND m.fecha = (
                        SELECT MAX(fecha)
                        FROM mediciones_antropometricas
                        WHERE id_atleta = a.id_atleta
                        AND deleted_at IS NULL
                    )
                    AND m.deleted_at IS NULL
                WHERE a.estado = 'Activo'";

        // 1. Filtro explícito de la Vista (si aplica)
        if ($id_atleta > 0) {
            $sql .= " AND a.id_atleta = :id_atleta";
        }

        // 2. MAGIA DE SEGURIDAD: Restricciones por Rol
        if (strpos($rol, 'Atleta') !== false) {
            // El atleta solo ve el registro asociado a su usuario
            $sql .= " AND a.id_usuario = :id_usuario_seguridad";
        } elseif (strpos($rol, 'Representante') !== false) {
            // El representante ve los atletas donde él sea el representante asignado
            $sql .= " AND a.id_atleta IN (
                          SELECT ar.id_atleta 
                          FROM atleta_representante ar 
                          JOIN representantes r ON ar.id_representante = r.id_representante 
                          WHERE r.id_usuario = :id_usuario_seguridad
                      )";
        }
        // Médicos y Administradores no entran en los IF anteriores, por lo que ven a todos.

        $sql .= " ORDER BY dias_sin_evaluacion DESC, a.nombres ASC";

        $stmt = $this->pdo->prepare($sql);
        
        // Bindeamos los parámetros dinámicamente
        if ($id_atleta > 0) {
            $stmt->bindValue(':id_atleta', $id_atleta, PDO::PARAM_INT);
        }
        if (strpos($rol, 'Atleta') !== false || strpos($rol, 'Representante') !== false) {
            $stmt->bindValue(':id_usuario_seguridad', $id_usuario, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error en ejecutarConsultaDashboard: " . $e->getMessage());
        return [];
    }
}


 /**
 * Anular medición (soft delete)
 */
public function anularMedicion(int $id_medicion, string $motivo): bool {
    try {
        $sql = "UPDATE mediciones_antropometricas 
                SET deleted_at = NOW(), motivo_eliminacion = :motivo 
                WHERE id_medicion = :id_medicion";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_medicion', $id_medicion, PDO::PARAM_INT);
        $stmt->bindValue(':motivo', $motivo, PDO::PARAM_STR);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error en anularMedicion: " . $e->getMessage());
        return false;
    }
}

/**
 * Reactivar medición
 */
public function reactivarMedicion(int $id_medicion): bool {
    try {
        $sql = "UPDATE mediciones_antropometricas 
                SET deleted_at = NULL, motivo_eliminacion = NULL 
                WHERE id_medicion = :id_medicion";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id_medicion', $id_medicion, PDO::PARAM_INT);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error en reactivarMedicion: " . $e->getMessage());
        return false;
    }
}

/**
 * Eliminar físicamente (hard delete) - ya existe el método eliminarMedicion
 * pero lo dejamos por claridad
 */
public function eliminarFisicoMedicion(int $id_medicion): bool {
    return $this->eliminarMedicion($id_medicion);
}
/**
 * Listar mediciones con filtros y soporte para papelera
 */
public function listarMediciones(int $id_atleta = 0, string $fecha_inicio = '', string $fecha_fin = '', string $modo = 'activos'): array {
    $this->datos['filtro_id_atleta'] = $id_atleta > 0 ? $id_atleta : 0;
    $this->datos['filtro_fecha_inicio'] = $fecha_inicio;
    $this->datos['filtro_fecha_fin'] = $fecha_fin;
    $this->datos['filtro_modo'] = $modo; // 'activos' o 'papelera'
    return $this->ejecutarConsultaListadoConPapelera();
}

/**
 * Consulta SQL con soporte para papelera
 */
private function ejecutarConsultaListadoConPapelera(): array {
    try {
        $sql = "SELECT m.id_medicion, m.fecha, m.peso_kg, m.talla_cm,
                       m.envergadura_cm, m.perimetro_abdominal_cm, 
                       m.imc, m.porcentaje_grasa, m.responsable,
                       m.deleted_at, m.motivo_eliminacion,
                       CONCAT(a.nombres, ' ', a.apellidos) AS nombre_atleta, 
                       a.cedula, a.id_atleta
                FROM mediciones_antropometricas m
                INNER JOIN atletas a ON m.id_atleta = a.id_atleta
                WHERE 1=1";

        if ($this->datos['filtro_modo'] === 'activos') {
            $sql .= " AND m.deleted_at IS NULL";
        } else {
            $sql .= " AND m.deleted_at IS NOT NULL";
        }

        if ($this->datos['filtro_id_atleta'] > 0) {
            $sql .= " AND m.id_atleta = :id_atleta";
        }
        if ($this->datos['filtro_fecha_inicio'] !== '') {
            $sql .= " AND m.fecha >= :fecha_inicio";
        }
        if ($this->datos['filtro_fecha_fin'] !== '') {
            $sql .= " AND m.fecha <= :fecha_fin";
        }
        $sql .= " ORDER BY m.fecha DESC";

        $stmt = $this->pdo->prepare($sql);

        if ($this->datos['filtro_id_atleta'] > 0) {
            $stmt->bindValue(':id_atleta', $this->datos['filtro_id_atleta'], PDO::PARAM_INT);
        }
        if ($this->datos['filtro_fecha_inicio'] !== '') {
            $stmt->bindValue(':fecha_inicio', $this->datos['filtro_fecha_inicio'], PDO::PARAM_STR);
        }
        if ($this->datos['filtro_fecha_fin'] !== '') {
            $stmt->bindValue(':fecha_fin', $this->datos['filtro_fecha_fin'], PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error en ejecutarConsultaListadoConPapelera: " . $e->getMessage());
        return [];
    }
}


/**
 * Obtener indicadores KPIs para el dashboard
 */
public function obtenerKPIs(): array {
    try {
        // 1. Pendientes (última medición > 85 días o nula)
        $sqlPendientes = "SELECT COUNT(*) as total 
                          FROM atletas a
                          LEFT JOIN (
                              SELECT id_atleta, MAX(fecha) as ultima_fecha
                              FROM mediciones_antropometricas
                              WHERE deleted_at IS NULL
                              GROUP BY id_atleta
                          ) m ON a.id_atleta = m.id_atleta
                          WHERE a.estado = 'Activo'
                          AND (m.ultima_fecha IS NULL OR DATEDIFF(CURDATE(), m.ultima_fecha) > 84)";
        $stmt = $this->pdo->prepare($sqlPendientes);
        $stmt->execute();
        $pendientes = (int)$stmt->fetchColumn();

        // 2. IMC promedio de atletas activos (última medición)
        $sqlImc = "SELECT AVG(m.imc) as promedio
                   FROM atletas a
                   LEFT JOIN (
                       SELECT id_atleta, imc, fecha
                       FROM mediciones_antropometricas
                       WHERE deleted_at IS NULL
                       ORDER BY fecha DESC
                   ) m ON a.id_atleta = m.id_atleta
                   WHERE a.estado = 'Activo'";
        $stmt = $this->pdo->prepare($sqlImc);
        $stmt->execute();
        $imc_promedio = (float)$stmt->fetchColumn() ?? null;

        // 3. Mediciones en el último mes (todas, no solo últimas)
        $sqlMes = "SELECT COUNT(*) as total 
                   FROM mediciones_antropometricas
                   WHERE deleted_at IS NULL
                   AND fecha >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
        $stmt = $this->pdo->prepare($sqlMes);
        $stmt->execute();
        $mediciones_mes = (int)$stmt->fetchColumn();

        // 4. Cobertura: % de atletas activos con medición en últimos 90 días
        $sqlCobertura = "SELECT 
                            COUNT(*) as total_activos,
                            SUM(CASE WHEN m.ultima_fecha IS NOT NULL AND DATEDIFF(CURDATE(), m.ultima_fecha) <= 90 THEN 1 ELSE 0 END) as medidos
                         FROM atletas a
                         LEFT JOIN (
                             SELECT id_atleta, MAX(fecha) as ultima_fecha
                             FROM mediciones_antropometricas
                             WHERE deleted_at IS NULL
                             GROUP BY id_atleta
                         ) m ON a.id_atleta = m.id_atleta
                         WHERE a.estado = 'Activo'";
        $stmt = $this->pdo->prepare($sqlCobertura);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $total = (int)$row['total_activos'];
        $medidos = (int)$row['medidos'];
        $cobertura = $total > 0 ? round(($medidos / $total) * 100, 1) : 0;

        return [
            'pendientes' => $pendientes,
            'imc_promedio' => $imc_promedio,
            'mediciones_mes' => $mediciones_mes,
            'cobertura' => $cobertura
        ];
    } catch (PDOException $e) {
        error_log("Error en obtenerKPIs: " . $e->getMessage());
        return ['pendientes' => 0, 'imc_promedio' => null, 'mediciones_mes' => 0, 'cobertura' => 0];
    }
}

/**
 * Listar atletas con medición vencida (alerta)
 */
public function listarAlertas(): array {
    try {
        $sql = "SELECT a.id_atleta, a.nombres, a.apellidos, c.nombre AS categoria,
                       MAX(m.fecha) AS ultima_fecha,
                       DATEDIFF(CURDATE(), MAX(m.fecha)) AS dias_sin_evaluacion
                FROM atletas a
                LEFT JOIN categorias_feveda c ON a.id_categoria = c.id_categoria
                LEFT JOIN mediciones_antropometricas m ON a.id_atleta = m.id_atleta AND m.deleted_at IS NULL
                WHERE a.estado = 'Activo'
                GROUP BY a.id_atleta
                HAVING dias_sin_evaluacion > 84 OR MAX(m.fecha) IS NULL
                ORDER BY dias_sin_evaluacion DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error en listarAlertas: " . $e->getMessage());
        return [];
    }
}



}


<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class TestFisico extends Conexion {
    use ValidacionesTrait;
    use AutoBinderTrait;

    private array $datos = [];

    private array $camposPermitidos = [
        'id_atleta', 'id_tipo_test', 'id_test_pers', 'fecha',
        'id_usuario_toma', 'observaciones', 'estado',
        'valores', 'id_registro_test', 'accion'
    ];

    public function setAtributos(array $payload): void {
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
    }

    private function validarAtributosInternos(bool $paraActualizacion = false): bool {
        $this->resetearErrores();

        if (!$paraActualizacion || isset($this->datos['id_atleta'])) {
            $this->requerido((string)($this->datos['id_atleta'] ?? ''), 'id_atleta');
            $this->soloNumeros((string)($this->datos['id_atleta'] ?? ''), 'id_atleta');
        }

        if (!empty($this->datos['fecha'])) {
            $this->fechaValida($this->datos['fecha'], 'fecha');
        }

        if (!empty($this->datos['observaciones'])) {
            $this->longitud($this->datos['observaciones'], 'observaciones', 0, 500);
        }

        if (!empty($this->datos['estado'])) {
            $this->enEnum($this->datos['estado'], 'estado', ['Completo', 'Parcial', 'Cancelado']);
        }

        return empty($this->obtenerErrores());
    }

    public function getRegistrarTest(): bool {
        $tipoTest = (int)($this->datos['id_tipo_test'] ?? 0);
        $testPers = (int)($this->datos['id_test_pers'] ?? 0);

        if ($tipoTest <= 0 && $testPers <= 0) {
            $this->agregarError('tipo_test', 'Debe seleccionar un tipo de test (predefinido o personalizado).');
            return false;
        }

        if (!$this->validarAtributosInternos(false)) {
            return false;
        }

        $valores = $this->datos['valores'] ?? [];
        if (!is_array($valores) || empty($valores)) {
            $this->agregarError('valores', 'Debe ingresar al menos un valor para las variables del test.');
            return false;
        }

        return $this->registrarTestBD($tipoTest, $testPers);
    }

    private function registrarTestBD(int $tipoTest, int $testPers): bool {
        try {
            $this->pdo->beginTransaction();

            $sql = "INSERT INTO registros_test (
                        id_atleta, id_tipo_test, id_test_pers,
                        fecha, id_usuario_toma, observaciones, estado
                    ) VALUES (
                        :id_atleta, :id_tipo_test, :id_test_pers,
                        :fecha, :id_usuario_toma, :observaciones, :estado
                    )";

            $stmt = $this->pdo->prepare($sql);
            $mapa = [
                ':id_atleta'       => ['id_atleta', PDO::PARAM_INT],
                ':id_tipo_test'    => ['id_tipo_test', PDO::PARAM_INT],
                ':id_test_pers'    => ['id_test_pers', PDO::PARAM_INT],
                ':fecha'           => ['fecha', PDO::PARAM_STR],
                ':id_usuario_toma'  => ['id_usuario_toma', PDO::PARAM_INT],
                ':observaciones'   => ['observaciones', PDO::PARAM_STR],
                ':estado'          => ['estado', PDO::PARAM_STR]
            ];

            $estadoDefault = !empty($this->datos['estado']) ? $this->datos['estado'] : 'Completo';

            $this->autoBind($stmt, $mapa, $this->datos, [
                'estado' => $estadoDefault
            ]);
            $stmt->execute();

            $idRegistro = $this->pdo->lastInsertId();

            $valores = $this->datos['valores'] ?? [];

            $sqlValor = "INSERT INTO valores_test (id_registro_test, id_variable, valor, unidad_medida)
                         VALUES (:id_registro, :id_variable, :valor, :unidad)";

            $stmtValor = $this->pdo->prepare($sqlValor);

            foreach ($valores as $idVar => $valor) {
                $idVarInt = (int)$idVar;
                $valorFloat = (float)$valor;
                $unidad = $this->obtenerUnidadVariable($idVarInt, $tipoTest, $testPers);

                $stmtValor->bindValue(':id_registro', (int)$idRegistro, PDO::PARAM_INT);
                $stmtValor->bindValue(':id_variable', $idVarInt, PDO::PARAM_INT);
                $stmtValor->bindValue(':valor', $valorFloat);
                $stmtValor->bindValue(':unidad', $unidad, PDO::PARAM_STR);
                $stmtValor->execute();
            }

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error en registrarTestBD: " . $e->getMessage());
            $this->agregarError('bd', 'Error interno al registrar el test.');
            return false;
        }
    }

    public function getActualizarTest(): bool {
        $id = (int)($this->datos['id_registro_test'] ?? 0);
        if ($id <= 0) {
            $this->agregarError('id_registro_test', 'No se proporcionó un identificador de registro válido para actualizar.');
            return false;
        }

        if (!$this->validarAtributosInternos(true)) {
            return false;
        }

        return $this->actualizarTestBD($id);
    }

    private function actualizarTestBD(int $id_registro): bool {
        try {
            $this->pdo->beginTransaction();

            $sql = "UPDATE registros_test SET
                        id_atleta = :id_atleta,
                        id_tipo_test = :id_tipo_test,
                        id_test_pers = :id_test_pers,
                        fecha = :fecha,
                        observaciones = :observaciones,
                        estado = :estado
                    WHERE id_registro_test = :id_registro";

            $stmt = $this->pdo->prepare($sql);
            $mapa = [
                ':id_atleta'      => ['id_atleta', PDO::PARAM_INT],
                ':id_tipo_test'   => ['id_tipo_test', PDO::PARAM_INT],
                ':id_test_pers'   => ['id_test_pers', PDO::PARAM_INT],
                ':fecha'          => ['fecha', PDO::PARAM_STR],
                ':observaciones'  => ['observaciones', PDO::PARAM_STR],
                ':estado'         => ['estado', PDO::PARAM_STR],
                ':id_registro'    => ['id_registro_local', PDO::PARAM_INT]
            ];

            $this->autoBind($stmt, $mapa, $this->datos, [
                'id_registro_local' => $id_registro
            ]);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                $this->pdo->rollBack();
                $this->agregarError('actualizacion', 'No se encontro el registro a actualizar.');
                return false;
            }

            $valores = $this->datos['valores'] ?? [];
            if (is_array($valores) && !empty($valores)) {
                $stmtDel = $this->pdo->prepare("DELETE FROM valores_test WHERE id_registro_test = :id");
                $stmtDel->bindValue(':id', $id_registro, PDO::PARAM_INT);
                $stmtDel->execute();

                $tipoTest = (int)($this->datos['id_tipo_test'] ?? 0);
                $testPers = (int)($this->datos['id_test_pers'] ?? 0);

                $sqlValor = "INSERT INTO valores_test (id_registro_test, id_variable, valor, unidad_medida)
                             VALUES (:id_registro, :id_variable, :valor, :unidad)";
                $stmtValor = $this->pdo->prepare($sqlValor);

                foreach ($valores as $idVar => $valor) {
                    $idVarInt = (int)$idVar;
                    $valorFloat = (float)$valor;
                    $unidad = $this->obtenerUnidadVariable($idVarInt, $tipoTest, $testPers);

                    $stmtValor->bindValue(':id_registro', $id_registro, PDO::PARAM_INT);
                    $stmtValor->bindValue(':id_variable', $idVarInt, PDO::PARAM_INT);
                    $stmtValor->bindValue(':valor', $valorFloat);
                    $stmtValor->bindValue(':unidad', $unidad, PDO::PARAM_STR);
                    $stmtValor->execute();
                }
            }

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error en actualizarTestBD: " . $e->getMessage());
            $this->agregarError('bd', 'Error interno al actualizar el test.');
            return false;
        }
    }

    public function eliminarTest(int $id): bool {
        if ($id <= 0) {
            $this->agregarError('id_registro_test', 'No se proporcionó un identificador válido para eliminar el test.');
            return false;
        }
        return $this->eliminarTestBD($id);
    }

    private function eliminarTestBD(int $id): bool {
        try {
            $this->pdo->beginTransaction();
            $stmtDel = $this->pdo->prepare("DELETE FROM valores_test WHERE id_registro_test = :id");
            $stmtDel->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtDel->execute();

            $stmt = $this->pdo->prepare("DELETE FROM registros_test WHERE id_registro_test = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $resultado = $stmt->execute();

            if ($stmt->rowCount() === 0) {
                $this->pdo->rollBack();
                return false;
            }
            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error en eliminarTestBD: " . $e->getMessage());
            return false;
        }
    }

    public function listarTests(int $id_atleta = 0, int $id_tipo_test = 0, string $estado = ''): array {
        return $this->listarTestsBD($id_atleta, $id_tipo_test, $estado);
    }

    private function listarTestsBD(int $id_atleta = 0, int $id_tipo_test = 0, string $estado = ''): array {
        try {
            $sql = "SELECT rt.id_registro_test, rt.id_atleta, rt.id_tipo_test, rt.id_test_pers,
                           rt.fecha, rt.observaciones, rt.estado, rt.fecha_creacion,
                           CONCAT(a.nombres, ' ', a.apellidos) AS nombre_atleta, a.cedula,
                           tp.nombre AS nombre_test, tp.unidad_medida AS unidad_test,
                           tp.valor_referencia_min, tp.valor_referencia_max
                    FROM registros_test rt
                    INNER JOIN atletas a ON rt.id_atleta = a.id_atleta
                    LEFT JOIN tipos_test_predefinidos tp ON rt.id_tipo_test = tp.id_tipo_test
                    WHERE 1=1";

            $params = [];

            if ($id_atleta > 0) {
                $sql .= " AND rt.id_atleta = :id_atleta";
                $params[':id_atleta'] = [$id_atleta, PDO::PARAM_INT];
            }
            if ($id_tipo_test > 0) {
                $sql .= " AND rt.id_tipo_test = :id_tipo_test";
                $params[':id_tipo_test'] = [$id_tipo_test, PDO::PARAM_INT];
            }
            if (!empty($estado)) {
                $sql .= " AND rt.estado = :estado";
                $params[':estado'] = [$estado, PDO::PARAM_STR];
            }

            $sql .= " ORDER BY rt.fecha DESC, rt.fecha_creacion DESC";

            $stmt = $this->pdo->prepare($sql);
            foreach ($params as $marcador => $config) {
                $stmt->bindValue($marcador, $config[0], $config[1]);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en listarTests: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerDetallePorId(int $id): ?array {
        try {
            $sql = "SELECT rt.*, 
                           CONCAT(a.nombres, ' ', a.apellidos) AS nombre_atleta, a.cedula,
                           tp.nombre AS nombre_test, tp.unidad_medida AS unidad_test,
                           tp.tipo_medicion, tp.valor_referencia_min, tp.valor_referencia_max
                    FROM registros_test rt
                    INNER JOIN atletas a ON rt.id_atleta = a.id_atleta
                    LEFT JOIN tipos_test_predefinidos tp ON rt.id_tipo_test = tp.id_tipo_test
                    WHERE rt.id_registro_test = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $detalle = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$detalle) return null;

            $sqlVal = "SELECT vt.id_valor, vt.id_variable, vt.valor, vt.unidad_medida,
                              v.nombre_variable, v.unidad AS variable_unidad, v.orden_mostrar
                       FROM valores_test vt
                       INNER JOIN variables_test v ON vt.id_variable = v.id_variable
                       WHERE vt.id_registro_test = :id_registro
                       ORDER BY v.orden_mostrar ASC";
            $stmtVal = $this->pdo->prepare($sqlVal);
            $stmtVal->bindValue(':id_registro', $id, PDO::PARAM_INT);
            $stmtVal->execute();
            $detalle['valores_detalle'] = $stmtVal->fetchAll(PDO::FETCH_ASSOC);

            $sqlHist = "SELECT rt.fecha, vt.valor, tp.nombre AS nombre_test
                        FROM registros_test rt
                        INNER JOIN valores_test vt ON rt.id_registro_test = vt.id_registro_test
                        LEFT JOIN tipos_test_predefinidos tp ON rt.id_tipo_test = tp.id_tipo_test
                        WHERE rt.id_atleta = :id_atleta 
                        AND rt.id_tipo_test = :id_tipo_test
                        AND rt.estado = 'Completo'
                        AND vt.id_variable = (
                            SELECT vt2.id_variable FROM valores_test vt2 
                            WHERE vt2.id_registro_test = :id_registro_original LIMIT 1
                        )
                        ORDER BY rt.fecha ASC";
            $stmtH = $this->pdo->prepare($sqlHist);
            $stmtH->bindValue(':id_atleta', (int)$detalle['id_atleta'], PDO::PARAM_INT);
            $stmtH->bindValue(':id_tipo_test', (int)$detalle['id_tipo_test'], PDO::PARAM_INT);
            $stmtH->bindValue(':id_registro_original', $id, PDO::PARAM_INT);
            $stmtH->execute();
            $detalle['historial_evolucion'] = $stmtH->fetchAll(PDO::FETCH_ASSOC);

            return $detalle;
        } catch (PDOException $e) {
            error_log("Error en obtenerDetallePorId: " . $e->getMessage());
            return null;
        }
    }

    public function listarTiposTests(): array {
        try {
            $sql = "SELECT * FROM tipos_test_predefinidos WHERE activo = 1 AND es_personalizado = 0 ORDER BY id_tipo_test ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en listarTiposTests: " . $e->getMessage());
            return [];
        }
    }

    public function listarVariablesPorTipo(int $id_tipo_test): array {
        try {
            $sql = "SELECT * FROM variables_test WHERE id_tipo_test = :id_tipo_test AND activa = 1 ORDER BY orden_mostrar ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id_tipo_test', $id_tipo_test, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en listarVariablesPorTipo: " . $e->getMessage());
            return [];
        }
    }

    public function listarVariablesPorPersonalizado(int $id_test_pers): array {
        try {
            $sql = "SELECT * FROM variables_test WHERE id_test_pers = :id_test_pers AND activa = 1 ORDER BY orden_mostrar ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id_test_pers', $id_test_pers, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en listarVariablesPorPersonalizado: " . $e->getMessage());
            return [];
        }
    }

    public function resumenPorAtleta(int $id_atleta): array {
        try {
            $sql = "SELECT tp.id_tipo_test, tp.nombre, tp.unidad_medida,
                           COUNT(rt.id_registro_test) AS total_aplicaciones,
                           MAX(rt.fecha) AS ultima_fecha
                    FROM tipos_test_predefinidos tp
                    LEFT JOIN registros_test rt 
                        ON tp.id_tipo_test = rt.id_tipo_test AND rt.id_atleta = :id_atleta AND rt.estado = 'Completo'
                    WHERE tp.activo = 1 AND tp.es_personalizado = 0
                    GROUP BY tp.id_tipo_test, tp.nombre, tp.unidad_medida
                    ORDER BY tp.id_tipo_test ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id_atleta', $id_atleta, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en resumenPorAtleta: " . $e->getMessage());
            return [];
        }
    }

    private function obtenerUnidadVariable(int $idVariable, int $idTipoTest, int $idTestPers): string {
        try {
            if ($idTipoTest > 0) {
                $sql = "SELECT unidad FROM variables_test WHERE id_variable = :id AND id_tipo_test = :tipo LIMIT 1";
                $stmt = $this->pdo->prepare($sql);
                $stmt->bindValue(':id', $idVariable, PDO::PARAM_INT);
                $stmt->bindValue(':tipo', $idTipoTest, PDO::PARAM_INT);
            } else {
                $sql = "SELECT unidad FROM variables_test WHERE id_variable = :id AND id_test_pers = :pers LIMIT 1";
                $stmt = $this->pdo->prepare($sql);
                $stmt->bindValue(':id', $idVariable, PDO::PARAM_INT);
                $stmt->bindValue(':pers', $idTestPers, PDO::PARAM_INT);
            }
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? $row['unidad'] : '';
        } catch (PDOException $e) {
            return '';
        }
    }

    public function crearTipoPredefinido(array $datos, array $variables): bool {
        $this->resetearErrores();

        $nombre = trim($datos['nombre'] ?? '');
        if ($nombre === '') {
            $this->agregarError('nombre', 'El nombre del tipo de test es obligatorio.');
        } else {
            $this->longitud($nombre, 'nombre', 2, 100);
        }

        $descripcion = trim($datos['descripcion'] ?? '');
        if ($descripcion !== '') {
            $this->longitud($descripcion, 'descripcion', 0, 300);
        }

        $tipoMedicion = trim($datos['tipo_medicion'] ?? '');
        if ($tipoMedicion !== '') {
            $this->longitud($tipoMedicion, 'tipo_medicion', 2, 80);
        }

        $unidadMedida = trim($datos['unidad_medida'] ?? '');
        if ($unidadMedida !== '') {
            $this->longitud($unidadMedida, 'unidad_medida', 1, 30);
        }

        if (!empty($datos['valor_referencia_min'])) {
            if (!$this->decimalValido((string)$datos['valor_referencia_min'], 'valor_referencia_min')) {
                return false;
            }
        }
        if (!empty($datos['valor_referencia_max'])) {
            if (!$this->decimalValido((string)$datos['valor_referencia_max'], 'valor_referencia_max')) {
                return false;
            }
        }

        if (empty($variables)) {
            $this->agregarError('variables', 'Debe agregar al menos una variable.');
        } else {
            foreach ($variables as $i => $v) {
                $nomVar = trim($v['nombre_variable'] ?? '');
                if ($nomVar === '') {
                    $this->agregarError("variable_{$i}", "El nombre de la variable #" . ($i + 1) . " es obligatorio.");
                } else {
                    $this->longitud($nomVar, "variable_{$i}", 2, 80);
                }
                $uniVar = trim($v['unidad'] ?? '');
                if ($uniVar !== '') {
                    $this->longitud($uniVar, "unidad_{$i}", 1, 20);
                }
            }
        }

        if (!empty($this->obtenerErrores())) return false;

        try {
            $this->pdo->beginTransaction();

            $sql = "INSERT INTO tipos_test_predefinidos (nombre, descripcion, tipo_medicion, unidad_medida, valor_referencia_min, valor_referencia_max, activo, es_personalizado)
                    VALUES (:nombre, :descripcion, :tipo_medicion, :unidad_medida, :ref_min, :ref_max, 1, 0)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':nombre'       => $datos['nombre'],
                ':descripcion'  => $datos['descripcion'] ?? null,
                ':tipo_medicion' => $datos['tipo_medicion'] ?? null,
                ':unidad_medida' => $datos['unidad_medida'] ?? null,
                ':ref_min'      => !empty($datos['valor_referencia_min']) ? (float)$datos['valor_referencia_min'] : null,
                ':ref_max'      => !empty($datos['valor_referencia_max']) ? (float)$datos['valor_referencia_max'] : null,
            ]);
            $idTipo = (int)$this->pdo->lastInsertId();

            $sqlVar = "INSERT INTO variables_test (id_tipo_test, nombre_variable, unidad, orden_mostrar, activa)
                       VALUES (:id_tipo, :nombre, :unidad, :orden, 1)";
            $stmtVar = $this->pdo->prepare($sqlVar);
            $orden = 1;
            foreach ($variables as $v) {
                $stmtVar->execute([
                    ':id_tipo' => $idTipo,
                    ':nombre'  => $v['nombre_variable'],
                    ':unidad'  => $v['unidad'] ?? null,
                    ':orden'   => $orden++,
                ]);
            }

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error en crearTipoPredefinido: " . $e->getMessage());
            return false;
        }
    }

    public function editarTipoPredefinido(int $id, array $datos, array $variables): bool {
        $this->resetearErrores();

        $nombre = trim($datos['nombre'] ?? '');
        if ($nombre === '') {
            $this->agregarError('nombre', 'El nombre del tipo de test es obligatorio.');
        } else {
            $this->longitud($nombre, 'nombre', 2, 100);
        }

        $descripcion = trim($datos['descripcion'] ?? '');
        if ($descripcion !== '') {
            $this->longitud($descripcion, 'descripcion', 0, 300);
        }

        $tipoMedicion = trim($datos['tipo_medicion'] ?? '');
        if ($tipoMedicion !== '') {
            $this->longitud($tipoMedicion, 'tipo_medicion', 2, 80);
        }

        $unidadMedida = trim($datos['unidad_medida'] ?? '');
        if ($unidadMedida !== '') {
            $this->longitud($unidadMedida, 'unidad_medida', 1, 30);
        }

        if (!empty($datos['valor_referencia_min'])) {
            if (!$this->decimalValido((string)$datos['valor_referencia_min'], 'valor_referencia_min')) return false;
        }
        if (!empty($datos['valor_referencia_max'])) {
            if (!$this->decimalValido((string)$datos['valor_referencia_max'], 'valor_referencia_max')) return false;
        }

        if (empty($variables)) {
            $this->agregarError('variables', 'Debe agregar al menos una variable.');
        } else {
            foreach ($variables as $i => $v) {
                $nomVar = trim($v['nombre_variable'] ?? '');
                if ($nomVar === '') {
                    $this->agregarError("variable_{$i}", "El nombre de la variable #" . ($i + 1) . " es obligatorio.");
                } else {
                    $this->longitud($nomVar, "variable_{$i}", 2, 80);
                }
                $uniVar = trim($v['unidad'] ?? '');
                if ($uniVar !== '') {
                    $this->longitud($uniVar, "unidad_{$i}", 1, 20);
                }
            }
        }

        if (!empty($this->obtenerErrores())) return false;

        try {
            $this->pdo->beginTransaction();

            $sql = "UPDATE tipos_test_predefinidos SET nombre=:nombre, descripcion=:descripcion, tipo_medicion=:tipo_medicion, unidad_medida=:unidad_medida, valor_referencia_min=:ref_min, valor_referencia_max=:ref_max
                    WHERE id_tipo_test=:id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':nombre'         => $datos['nombre'],
                ':descripcion'    => $datos['descripcion'] ?? null,
                ':tipo_medicion'  => $datos['tipo_medicion'] ?? null,
                ':unidad_medida'  => $datos['unidad_medida'] ?? null,
                ':ref_min'        => !empty($datos['valor_referencia_min']) ? (float)$datos['valor_referencia_min'] : null,
                ':ref_max'        => !empty($datos['valor_referencia_max']) ? (float)$datos['valor_referencia_max'] : null,
                ':id'             => $id,
            ]);

            $this->pdo->exec("DELETE FROM variables_test WHERE id_tipo_test = $id AND id_test_pers IS NULL");

            $sqlVar = "INSERT INTO variables_test (id_tipo_test, nombre_variable, unidad, orden_mostrar, activa)
                       VALUES (:id_tipo, :nombre, :unidad, :orden, 1)";
            $stmtVar = $this->pdo->prepare($sqlVar);
            $orden = 1;
            foreach ($variables as $v) {
                $stmtVar->execute([
                    ':id_tipo' => $id,
                    ':nombre'  => $v['nombre_variable'],
                    ':unidad'  => $v['unidad'] ?? null,
                    ':orden'   => $orden++,
                ]);
            }

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error en editarTipoPredefinido: " . $e->getMessage());
            return false;
        }
    }

    public function eliminarTipoPredefinido(int $id): bool {
        try {
            $this->pdo->beginTransaction();
            $count = $this->pdo->query("SELECT COUNT(*) FROM registros_test WHERE id_tipo_test = $id")->fetchColumn();
            if ($count > 0) {
                $this->pdo->rollBack();
                $this->agregarError('eliminar', 'No se puede eliminar un tipo de test que tiene registros asociados.');
                return false;
            }
            $this->pdo->exec("DELETE FROM variables_test WHERE id_tipo_test = $id AND id_test_pers IS NULL");
            $this->pdo->exec("DELETE FROM tipos_test_predefinidos WHERE id_tipo_test = $id");
            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error en eliminarTipoPredefinido: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerTipoConVariables(int $id): ?array {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM tipos_test_predefinidos WHERE id_tipo_test = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $tipo = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$tipo) return null;

            $stmtVar = $this->pdo->prepare("SELECT * FROM variables_test WHERE id_tipo_test = :id AND id_test_pers IS NULL AND activa = 1 ORDER BY orden_mostrar ASC");
            $stmtVar->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtVar->execute();
            $tipo['variables'] = $stmtVar->fetchAll(PDO::FETCH_ASSOC);

            return $tipo;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function crearTestPersonalizado(array $datos, array $variables): int {
        $this->resetearErrores();

        $nombre = trim($datos['nombre'] ?? '');
        if ($nombre === '') {
            $this->agregarError('nombre', 'El nombre del test es obligatorio.');
        } else {
            $this->longitud($nombre, 'nombre', 2, 100);
        }

        $descripcion = trim($datos['descripcion'] ?? '');
        if ($descripcion !== '') {
            $this->longitud($descripcion, 'descripcion', 0, 300);
        }

        $tipoMedicion = trim($datos['tipo_medicion'] ?? '');
        if ($tipoMedicion !== '') {
            $this->longitud($tipoMedicion, 'tipo_medicion', 2, 80);
        }

        $unidadMedida = trim($datos['unidad_medida'] ?? '');
        if ($unidadMedida !== '') {
            $this->longitud($unidadMedida, 'unidad_medida', 1, 30);
        }

        if (!empty($datos['valor_referencia_min'])) {
            if (!$this->decimalValido((string)$datos['valor_referencia_min'], 'valor_referencia_min')) return 0;
        }
        if (!empty($datos['valor_referencia_max'])) {
            if (!$this->decimalValido((string)$datos['valor_referencia_max'], 'valor_referencia_max')) return 0;
        }

        if (empty($variables)) {
            $this->agregarError('variables', 'Debe agregar al menos una variable.');
        } else {
            foreach ($variables as $i => $v) {
                $nomVar = trim($v['nombre_variable'] ?? '');
                if ($nomVar === '') {
                    $this->agregarError("variable_{$i}", "El nombre de la variable #" . ($i + 1) . " es obligatorio.");
                } else {
                    $this->longitud($nomVar, "variable_{$i}", 2, 80);
                }
                $uniVar = trim($v['unidad'] ?? '');
                if ($uniVar !== '') {
                    $this->longitud($uniVar, "unidad_{$i}", 1, 20);
                }
            }
        }

        if (!empty($this->obtenerErrores())) return 0;

        try {
            $this->pdo->beginTransaction();

            $sql = "INSERT INTO tests_personalizados (nombre, descripcion, tipo_medicion, unidad_medida, valor_referencia_min, valor_referencia_max, activo, id_usuario_creador)
                    VALUES (:nombre, :descripcion, :tipo_medicion, :unidad_medida, :ref_min, :ref_max, 1, :id_usuario)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':nombre'          => $datos['nombre'],
                ':descripcion'     => $datos['descripcion'] ?? null,
                ':tipo_medicion'   => $datos['tipo_medicion'] ?? null,
                ':unidad_medida'   => $datos['unidad_medida'] ?? null,
                ':ref_min'        => !empty($datos['valor_referencia_min']) ? (float)$datos['valor_referencia_min'] : null,
                ':ref_max'        => !empty($datos['valor_referencia_max']) ? (float)$datos['valor_referencia_max'] : null,
                ':id_usuario'      => (int)$datos['id_usuario_creador'],
            ]);
            $idPers = (int)$this->pdo->lastInsertId();

            $sqlVar = "INSERT INTO variables_test (id_test_pers, nombre_variable, unidad, orden_mostrar, activa)
                       VALUES (:id_pers, :nombre, :unidad, :orden, 1)";
            $stmtVar = $this->pdo->prepare($sqlVar);
            $orden = 1;
            foreach ($variables as $v) {
                $stmtVar->execute([
                    ':id_pers' => $idPers,
                    ':nombre'  => $v['nombre_variable'],
                    ':unidad'  => $v['unidad'] ?? null,
                    ':orden'   => $orden++,
                ]);
            }

            $this->pdo->commit();
            return $idPers;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error en crearTestPersonalizado: " . $e->getMessage());
            $this->agregarError('bd', 'Error interno al crear test personalizado.');
            return 0;
        }
    }

    public function listarTestsPersonalizados(int $id_usuario = 0): array {
        try {
            $sql = "SELECT * FROM tests_personalizados WHERE activo = 1";
            if ($id_usuario > 0) {
                $sql .= " AND id_usuario_creador = :id_usuario";
            }
            $sql .= " ORDER BY fecha_creacion DESC";
            $stmt = $this->pdo->prepare($sql);
            if ($id_usuario > 0) {
                $stmt->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
            }
            $stmt->execute();
            $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($tests as &$t) {
                $stmtVar = $this->pdo->prepare("SELECT * FROM variables_test WHERE id_test_pers = :id AND id_tipo_test IS NULL AND activa = 1 ORDER BY orden_mostrar");
                $stmtVar->bindValue(':id', $t['id_test_pers'], PDO::PARAM_INT);
                $stmtVar->execute();
                $t['variables'] = $stmtVar->fetchAll(PDO::FETCH_ASSOC);
            }
            return $tests;
        } catch (PDOException $e) {
            return [];
        }
    }

    public function editarTestPersonalizado(int $id, array $datos, array $variables): bool {
        $this->resetearErrores();

        $nombre = trim($datos['nombre'] ?? '');
        if ($nombre === '') {
            $this->agregarError('nombre', 'El nombre del test es obligatorio.');
        } else {
            $this->longitud($nombre, 'nombre', 2, 100);
        }

        $descripcion = trim($datos['descripcion'] ?? '');
        if ($descripcion !== '') {
            $this->longitud($descripcion, 'descripcion', 0, 300);
        }

        $tipoMedicion = trim($datos['tipo_medicion'] ?? '');
        if ($tipoMedicion !== '') {
            $this->longitud($tipoMedicion, 'tipo_medicion', 2, 80);
        }

        $unidadMedida = trim($datos['unidad_medida'] ?? '');
        if ($unidadMedida !== '') {
            $this->longitud($unidadMedida, 'unidad_medida', 1, 30);
        }

        if (!empty($datos['valor_referencia_min'])) {
            if (!$this->decimalValido((string)$datos['valor_referencia_min'], 'valor_referencia_min')) return false;
        }
        if (!empty($datos['valor_referencia_max'])) {
            if (!$this->decimalValido((string)$datos['valor_referencia_max'], 'valor_referencia_max')) return false;
        }

        if (empty($variables)) {
            $this->agregarError('variables', 'Debe agregar al menos una variable.');
        } else {
            foreach ($variables as $i => $v) {
                $nomVar = trim($v['nombre_variable'] ?? '');
                if ($nomVar === '') {
                    $this->agregarError("variable_{$i}", "El nombre de la variable #" . ($i + 1) . " es obligatorio.");
                } else {
                    $this->longitud($nomVar, "variable_{$i}", 2, 80);
                }
                $uniVar = trim($v['unidad'] ?? '');
                if ($uniVar !== '') {
                    $this->longitud($uniVar, "unidad_{$i}", 1, 20);
                }
            }
        }

        if (!empty($this->obtenerErrores())) return false;

        try {
            $this->pdo->beginTransaction();

            $sql = "UPDATE tests_personalizados SET nombre = :nombre, descripcion = :descripcion, tipo_medicion = :tipo_medicion,
                    unidad_medida = :unidad_medida, valor_referencia_min = :ref_min, valor_referencia_max = :ref_max
                    WHERE id_test_pers = :id AND activo = 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id'            => $id,
                ':nombre'        => $datos['nombre'],
                ':descripcion'   => $datos['descripcion'] ?? null,
                ':tipo_medicion' => $datos['tipo_medicion'] ?? null,
                ':unidad_medida' => $datos['unidad_medida'] ?? null,
                ':ref_min'       => !empty($datos['valor_referencia_min']) ? (float)$datos['valor_referencia_min'] : null,
                ':ref_max'       => !empty($datos['valor_referencia_max']) ? (float)$datos['valor_referencia_max'] : null,
            ]);

            $this->pdo->exec("DELETE FROM variables_test WHERE id_test_pers = $id AND id_tipo_test IS NULL AND activa = 1");

            if (!empty($variables)) {
                $sqlVar = "INSERT INTO variables_test (id_test_pers, nombre_variable, unidad, orden_mostrar, activa)
                           VALUES (:id_pers, :nombre, :unidad, :orden, 1)";
                $stmtVar = $this->pdo->prepare($sqlVar);
                $orden = 1;
                foreach ($variables as $v) {
                    $stmtVar->execute([
                        ':id_pers' => $id,
                        ':nombre'  => $v['nombre_variable'],
                        ':unidad'  => $v['unidad'] ?? null,
                        ':orden'   => $orden++,
                    ]);
                }
            }

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error en editarTestPersonalizado: " . $e->getMessage());
            $this->agregarError('bd', 'Error interno al editar test personalizado.');
            return false;
        }
    }

    public function obtenerTestPersonalizado(int $id): ?array {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM tests_personalizados WHERE id_test_pers = :id AND activo = 1");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $test = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$test) return null;

            $stmtVar = $this->pdo->prepare("SELECT * FROM variables_test WHERE id_test_pers = :id AND id_tipo_test IS NULL AND activa = 1 ORDER BY orden_mostrar");
            $stmtVar->bindValue(':id', $id, PDO::PARAM_INT);
            $stmtVar->execute();
            $test['variables'] = $stmtVar->fetchAll(PDO::FETCH_ASSOC);
            return $test;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function eliminarTestPersonalizado(int $id): bool {
        try {
            $this->pdo->beginTransaction();

            $count = $this->pdo->query("SELECT COUNT(*) FROM registros_test WHERE id_test_pers = $id")->fetchColumn();
            if ($count > 0) {
                $this->pdo->rollBack();
                $this->agregarError('eliminar', 'No se puede eliminar un test personalizado que tiene registros asociados.');
                return false;
            }

            $this->pdo->exec("DELETE FROM variables_test WHERE id_test_pers = $id AND id_tipo_test IS NULL");
            $this->pdo->exec("DELETE FROM tests_personalizados WHERE id_test_pers = $id");
            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error en eliminarTestPersonalizado: " . $e->getMessage());
            return false;
        }
    }
}

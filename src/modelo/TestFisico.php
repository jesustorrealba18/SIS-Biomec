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

    public function registrarTest(array $payload): bool {
        $this->setAtributos($payload);

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
                ':id_usuario_toma'  => ['id_usuario_toma_local', PDO::PARAM_INT],
                ':observaciones'   => ['observaciones', PDO::PARAM_STR],
                ':estado'          => ['estado', PDO::PARAM_STR]
            ];

            $idUsuario = $payload['id_usuario_toma'] ?? ($_SESSION['id'] ?? 0);
            $estadoDefault = !empty($this->datos['estado']) ? $this->datos['estado'] : 'Completo';

            $this->autoBind($stmt, $mapa, $this->datos, [
                'id_usuario_toma_local' => (int)$idUsuario,
                'estado' => $estadoDefault
            ]);
            $stmt->execute();

            $idRegistro = $this->pdo->lastInsertId();

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
            error_log("Error en registrarTest: " . $e->getMessage());
            $this->agregarError('bd', 'Error interno al registrar el test.');
            return false;
        }
    }

    public function actualizarTest(array $payload, int $id_registro): bool {
        $this->setAtributos($payload);

        if (!$this->validarAtributosInternos(true)) {
            return false;
        }

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
                ':id_registro'    => [$id_registro, PDO::PARAM_INT]
            ];

            $this->autoBind($stmt, $mapa, $this->datos);
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
            error_log("Error en actualizarTest: " . $e->getMessage());
            $this->agregarError('bd', 'Error interno al actualizar el test.');
            return false;
        }
    }

    public function eliminarTest(int $id): bool {
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
            error_log("Error en eliminarTest: " . $e->getMessage());
            return false;
        }
    }

    public function listarTests(int $id_atleta = 0, int $id_tipo_test = 0, string $estado = ''): array {
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
}

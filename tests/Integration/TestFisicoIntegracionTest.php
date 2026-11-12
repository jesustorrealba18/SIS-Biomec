<?php

namespace Tests\Integration;

use GrupoProyecto\SisBiomec\modelo\TestFisico;

/**
 * CP-INT: pruebas de integracion del modulo de test fisico contra la BD real
 * (sis_natacion). Cubre tipos predefinidos, tests personalizados, registro
 * transaccional de valores, actualizacion con reemplazo de valores y las
 * reglas de negocio de eliminacion.
 *
 * Todo dato de prueba usa el prefijo TESTPRUEBA y se limpia en tearDown.
 */
class TestFisicoIntegracionTest extends IntegrationTestCase
{
    private TestFisico $tf;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tf = new TestFisico();
    }

    protected function tearDown(): void
    {
        $this->limpiarTestFisico();
        parent::tearDown();
        unset($this->tf);
    }

    /**
     * Elimina registros/valores/variables/tipos creados por las pruebas,
     * en orden inverso de dependencias, antes de la limpieza general.
     */
    private function limpiarTestFisico(): void
    {
        try {
            $idsTipos = $this->columna($this->pdoNatacion, "SELECT id_tipo_test FROM tipos_test_predefinidos WHERE nombre LIKE 'TESTPRUEBA%'");
            $idsPers = $this->columna($this->pdoNatacion, "SELECT id_test_pers FROM tests_personalizados WHERE nombre LIKE 'TESTPRUEBA%'");
            $idsAtletas = $this->columna($this->pdoNatacion, "SELECT id_atleta FROM atletas WHERE correo LIKE 'TESTPRUEBA%'");

            $inTipos = $this->inClause($idsTipos);
            $inPers = $this->inClause($idsPers);
            $inAtletas = $this->inClause($idsAtletas);

            $cond = [];
            if ($inAtletas !== null) { $cond[] = "id_atleta IN ($inAtletas)"; }
            if ($inTipos !== null) { $cond[] = "id_tipo_test IN ($inTipos)"; }
            if ($inPers !== null) { $cond[] = "id_test_pers IN ($inPers)"; }

            if ($cond !== []) {
                $where = implode(' OR ', $cond);
                $this->pdoNatacion->exec("DELETE FROM valores_test WHERE id_registro_test IN (SELECT id_registro_test FROM registros_test WHERE {$where})");
                $this->pdoNatacion->exec("DELETE FROM registros_test WHERE {$where}");
            }

            if ($inTipos !== null || $inPers !== null) {
                $condVar = [];
                if ($inTipos !== null) { $condVar[] = "id_tipo_test IN ($inTipos)"; }
                if ($inPers !== null) { $condVar[] = "id_test_pers IN ($inPers)"; }
                $this->pdoNatacion->exec("DELETE FROM variables_test WHERE " . implode(' OR ', $condVar));
                $this->borrarPorLista($this->pdoNatacion, 'tipos_test_predefinidos', 'id_tipo_test', $idsTipos);
                $this->borrarPorLista($this->pdoNatacion, 'tests_personalizados', 'id_test_pers', $idsPers);
            }
        } catch (\Throwable $e) {
            fwrite(STDERR, 'AVISO (limpieza test fisico): ' . $e->getMessage() . PHP_EOL);
        }
    }

    private function inClause(array $valores): ?string
    {
        $valores = array_values(array_filter($valores, fn ($v) => $v !== null && $v !== ''));
        if ($valores === []) {
            return null;
        }
        return implode(',', array_map('intval', $valores));
    }

    private function nombreTipoPrueba(): string
    {
        return 'TESTPRUEBA Tipo ' . uniqid();
    }

    private function variablesPrueba(): array
    {
        return [
            ['nombre_variable' => 'Variable uno', 'unidad' => 'cm'],
            ['nombre_variable' => 'Variable dos', 'unidad' => 'bpm'],
        ];
    }

    /** Crea un tipo predefinido TESTPRUEBA con 2 variables via el modelo. */
    private function crearTipoPrueba(): array
    {
        $nombre = $this->nombreTipoPrueba();
        $this->assertTrue($this->tf->crearTipoPredefinido(
            ['nombre' => $nombre, 'unidad_medida' => 'cm', 'tipo_medicion' => 'Distancia'],
            $this->variablesPrueba()
        ), 'Errores: ' . json_encode($this->tf->obtenerErrores()));

        $sentencia = $this->pdoNatacion->prepare('SELECT id_tipo_test FROM tipos_test_predefinidos WHERE nombre = ?');
        $sentencia->execute([$nombre]);
        $idTipo = (int)$sentencia->fetchColumn();
        $variables = $this->columna(
            $this->pdoNatacion,
            "SELECT id_variable FROM variables_test WHERE id_tipo_test = {$idTipo} ORDER BY orden_mostrar ASC"
        );

        return ['id_tipo' => $idTipo, 'variables' => $variables, 'nombre' => $nombre];
    }

    private function idAtletaPrueba(): int
    {
        return $this->crearAtletaPrueba();
    }

    private function buscarIdRegistro(int $idAtleta, int $idTipo): int
    {
        $sentencia = $this->pdoNatacion->prepare(
            'SELECT id_registro_test FROM registros_test WHERE id_atleta = ? AND id_tipo_test = ? ORDER BY id_registro_test DESC'
        );
        $sentencia->execute([$idAtleta, $idTipo]);
        return (int)$sentencia->fetchColumn();
    }

    /** Registra un test valido via el modelo y devuelve el id del registro. */
    private function crearRegistroPrueba(int $idAtleta, int $idTipo, array $variables, string $estado = 'Completo', string $fecha = '2026-01-15'): int
    {
        $this->tf->setAtributos([
            'id_atleta'     => (string)$idAtleta,
            'id_tipo_test'  => (string)$idTipo,
            'fecha'         => $fecha,
            'estado'        => $estado,
            'observaciones' => 'Registro inicial de prueba',
            'valores'       => [$variables[0] => '10.5', $variables[1] => '120'],
        ]);
        $this->assertTrue($this->tf->getRegistrarTest(), 'Errores: ' . json_encode($this->tf->obtenerErrores()));
        return $this->buscarIdRegistro($idAtleta, $idTipo);
    }

    // CP-INT-24: crear tipo predefinido con variables lo persiste y lo lista
    public function testCrearTipoPredefinidoConVariables(): void
    {
        $tipo = $this->crearTipoPrueba();

        $fila = $this->pdoNatacion->query(
            "SELECT nombre, activo, es_personalizado FROM tipos_test_predefinidos WHERE id_tipo_test = {$tipo['id_tipo']}"
        )->fetch(\PDO::FETCH_ASSOC);
        $this->assertNotFalse($fila, 'El tipo debe existir en la BD.');
        $this->assertSame($tipo['nombre'], $fila['nombre']);
        $this->assertSame('1', (string)$fila['activo']);
        $this->assertSame('0', (string)$fila['es_personalizado']);
        $this->assertCount(2, $tipo['variables'], 'Deben crearse las 2 variables.');

        $listado = $this->tf->listarTiposTests();
        $ids = array_map('intval', array_column($listado, 'id_tipo_test'));
        $this->assertContains($tipo['id_tipo'], $ids, 'listarTiposTests debe incluir el tipo creado.');
    }

    // CP-INT-25: registrar test persiste registro con estado Completo y valores con unidad resuelta
    public function testRegistrarTestPersisteRegistroYValores(): void
    {
        $idAtleta = $this->idAtletaPrueba();
        $tipo = $this->crearTipoPrueba();

        $this->tf->setAtributos([
            'id_atleta'     => (string)$idAtleta,
            'id_tipo_test'  => (string)$tipo['id_tipo'],
            'fecha'         => '2026-01-15',
            'observaciones' => 'Valores dentro del rango',
            'valores'       => [$tipo['variables'][0] => '10.5', $tipo['variables'][1] => '120'],
        ]);
        $this->assertTrue($this->tf->getRegistrarTest(), 'Errores: ' . json_encode($this->tf->obtenerErrores()));

        $idRegistro = $this->buscarIdRegistro($idAtleta, $tipo['id_tipo']);
        $this->assertGreaterThan(0, $idRegistro, 'El registro debe existir en la BD.');

        $registro = $this->pdoNatacion->query(
            "SELECT estado, fecha FROM registros_test WHERE id_registro_test = {$idRegistro}"
        )->fetch(\PDO::FETCH_ASSOC);
        $this->assertSame('Completo', $registro['estado'], 'Sin estado explicito el default debe ser Completo.');
        $this->assertSame('2026-01-15', $registro['fecha']);

        $valores = $this->pdoNatacion->query(
            "SELECT valor, unidad_medida FROM valores_test WHERE id_registro_test = {$idRegistro} ORDER BY id_variable"
        )->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertCount(2, $valores);
        $this->assertEqualsWithDelta(10.5, (float)$valores[0]['valor'], 0.001);
        $this->assertSame('cm', $valores[0]['unidad_medida'], 'La unidad debe resolverse desde variables_test.');
        $this->assertSame('bpm', $valores[1]['unidad_medida']);
    }

    // CP-INT-26: registrar sin valores no persiste nada
    public function testRegistrarSinValoresNoPersiste(): void
    {
        $idAtleta = $this->idAtletaPrueba();
        $tipo = $this->crearTipoPrueba();

        $this->tf->setAtributos([
            'id_atleta'    => (string)$idAtleta,
            'id_tipo_test' => (string)$tipo['id_tipo'],
            'fecha'        => '2026-01-15',
            'valores'      => [],
        ]);
        $this->assertFalse($this->tf->getRegistrarTest());
        $this->assertArrayHasKey('valores', $this->tf->obtenerErrores());
        $this->assertSame(0, $this->buscarIdRegistro($idAtleta, $tipo['id_tipo']), 'No debe persistirse ningun registro.');
    }

    // CP-INT-27: registrar sin tipo ni personalizado no persiste nada
    public function testRegistrarSinTipoNoPersiste(): void
    {
        $idAtleta = $this->idAtletaPrueba();

        $this->tf->setAtributos([
            'id_atleta' => (string)$idAtleta,
            'fecha'     => '2026-01-15',
            'valores'   => ['1' => '10'],
        ]);
        $this->assertFalse($this->tf->getRegistrarTest());
        $this->assertArrayHasKey('tipo_test', $this->tf->obtenerErrores());

        $total = (int)$this->pdoNatacion->query(
            "SELECT COUNT(*) FROM registros_test WHERE id_atleta = {$idAtleta}"
        )->fetchColumn();
        $this->assertSame(0, $total, 'No debe persistirse ningun registro.');
    }

    // CP-INT-28: el estado explicito se respeta al registrar
    public function testRegistrarConEstadoParcial(): void
    {
        $idAtleta = $this->idAtletaPrueba();
        $tipo = $this->crearTipoPrueba();
        $idRegistro = $this->crearRegistroPrueba($idAtleta, $tipo['id_tipo'], $tipo['variables'], 'Parcial');

        $estado = $this->pdoNatacion->query(
            "SELECT estado FROM registros_test WHERE id_registro_test = {$idRegistro}"
        )->fetchColumn();
        $this->assertSame('Parcial', $estado);
    }

    // CP-INT-29: actualizar cambia datos y reemplaza los valores anteriores
    public function testActualizarCambiaDatosYReemplazaValores(): void
    {
        $idAtleta = $this->idAtletaPrueba();
        $tipo = $this->crearTipoPrueba();
        $idRegistro = $this->crearRegistroPrueba($idAtleta, $tipo['id_tipo'], $tipo['variables']);

        $this->tf->setAtributos([
            'id_registro_test' => (string)$idRegistro,
            'id_atleta'        => (string)$idAtleta,
            'id_tipo_test'     => (string)$tipo['id_tipo'],
            'fecha'            => '2026-01-16',
            'estado'           => 'Parcial',
            'observaciones'    => 'Observacion actualizada',
            'valores'          => [$tipo['variables'][0] => '9.0'],
        ]);
        $this->assertTrue($this->tf->getActualizarTest(), 'Errores: ' . json_encode($this->tf->obtenerErrores()));

        $registro = $this->pdoNatacion->query(
            "SELECT estado, observaciones, fecha FROM registros_test WHERE id_registro_test = {$idRegistro}"
        )->fetch(\PDO::FETCH_ASSOC);
        $this->assertSame('Parcial', $registro['estado']);
        $this->assertSame('Observacion actualizada', $registro['observaciones']);
        $this->assertSame('2026-01-16', $registro['fecha']);

        $valores = $this->pdoNatacion->query(
            "SELECT id_variable, valor FROM valores_test WHERE id_registro_test = {$idRegistro}"
        )->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertCount(1, $valores, 'Los valores anteriores deben reemplazarse (2 -> 1).');
        $this->assertEqualsWithDelta(9.0, (float)$valores[0]['valor'], 0.001);
    }

    // CP-INT-30: actualizar sin enviar estado corrompe el registro — hallazgo TF-5
    // MariaDB sin STRICT_TRANS_TABLES convierte NULL en '' (estado fuera del enum)
    // y '0000-00-00' (fecha invalida) sin reportar ningun error.
    public function testActualizarSinEstadoCorrompeSilenciosamente(): void
    {
        $idAtleta = $this->idAtletaPrueba();
        $tipo = $this->crearTipoPrueba();
        $idRegistro = $this->crearRegistroPrueba($idAtleta, $tipo['id_tipo'], $tipo['variables']);

        $this->tf->setAtributos([
            'id_registro_test' => (string)$idRegistro,
            'id_atleta'        => (string)$idAtleta,
            'id_tipo_test'     => (string)$tipo['id_tipo'],
            'fecha'            => '2026-01-15',
            'observaciones'    => 'Solo cambio la observacion',
            'valores'          => [$tipo['variables'][0] => '11.0'],
        ]);
        $this->assertTrue($this->tf->getActualizarTest(), 'Hallazgo TF-5: actualizar sin estado NO falla (MariaDB no estricto acepta NULL en NOT NULL).');

        $registro = $this->pdoNatacion->query(
            "SELECT estado, fecha, observaciones FROM registros_test WHERE id_registro_test = {$idRegistro}"
        )->fetch(\PDO::FETCH_ASSOC);
        $this->assertSame('', $registro['estado'], 'Hallazgo TF-5: el estado queda vacio, fuera del enum Completo/Parcial/Cancelado.');
        $this->assertSame('2026-01-15', $registro['fecha'], 'La fecha enviada se conserva; solo el estado ausente se corrompe.');
        $this->assertSame('Solo cambio la observacion', $registro['observaciones']);
    }

    // CP-INT-31: actualizar con datos identicos falla por rowCount 0 — hallazgo TF-2b
    public function testActualizarConMismosDatosFallaPorRowCount(): void
    {
        $idAtleta = $this->idAtletaPrueba();
        $tipo = $this->crearTipoPrueba();
        $idRegistro = $this->crearRegistroPrueba($idAtleta, $tipo['id_tipo'], $tipo['variables']);

        $this->tf->setAtributos([
            'id_registro_test' => (string)$idRegistro,
            'id_atleta'        => (string)$idAtleta,
            'id_tipo_test'     => (string)$tipo['id_tipo'],
            'fecha'            => '2026-01-15',
            'estado'           => 'Completo',
            'observaciones'    => 'Registro inicial de prueba',
            'valores'          => [$tipo['variables'][0] => '10.5', $tipo['variables'][1] => '120'],
        ]);
        $this->assertFalse($this->tf->getActualizarTest(), 'Hallazgo TF-2b: UPDATE con datos identicos retorna rowCount 0 y el modelo lo interpreta como registro no encontrado.');
        $this->assertArrayHasKey('actualizacion', $this->tf->obtenerErrores());

        $totalValores = (int)$this->pdoNatacion->query(
            "SELECT COUNT(*) FROM valores_test WHERE id_registro_test = {$idRegistro}"
        )->fetchColumn();
        $this->assertSame(2, $totalValores, 'Los valores originales deben permanecer intactos.');
    }

    // CP-INT-32: eliminar test borra el registro y sus valores
    public function testEliminarTestEliminaValoresYRegistro(): void
    {
        $idAtleta = $this->idAtletaPrueba();
        $tipo = $this->crearTipoPrueba();
        $idRegistro = $this->crearRegistroPrueba($idAtleta, $tipo['id_tipo'], $tipo['variables']);

        $this->assertTrue($this->tf->eliminarTest($idRegistro));

        $totalValores = (int)$this->pdoNatacion->query(
            "SELECT COUNT(*) FROM valores_test WHERE id_registro_test = {$idRegistro}"
        )->fetchColumn();
        $this->assertSame(0, $totalValores, 'Los valores deben eliminarse en cascada logica.');
        $this->assertSame(0, $this->buscarIdRegistro($idAtleta, $tipo['id_tipo']), 'El registro debe eliminarse.');
    }

    // CP-INT-33: eliminar un registro inexistente retorna false
    public function testEliminarTestInexistenteRetornaFalse(): void
    {
        $this->assertFalse($this->tf->eliminarTest(999999999));
    }

    // CP-INT-34: regla de negocio — un tipo con registros asociados no se puede eliminar
    public function testEliminarTipoConRegistrosSeBloquea(): void
    {
        $idAtleta = $this->idAtletaPrueba();
        $tipo = $this->crearTipoPrueba();
        $this->crearRegistroPrueba($idAtleta, $tipo['id_tipo'], $tipo['variables']);

        $this->assertFalse($this->tf->eliminarTipoPredefinido($tipo['id_tipo']));
        $this->assertArrayHasKey('eliminar', $this->tf->obtenerErrores());

        $existe = $this->pdoNatacion->query(
            "SELECT COUNT(*) FROM tipos_test_predefinidos WHERE id_tipo_test = {$tipo['id_tipo']}"
        )->fetchColumn();
        $this->assertSame('1', (string)$existe, 'El tipo debe seguir existiendo.');
    }

    // CP-INT-35: un tipo sin registros se puede eliminar junto con sus variables
    public function testEliminarTipoSinRegistrosSePermite(): void
    {
        $tipo = $this->crearTipoPrueba();

        $this->assertTrue($this->tf->eliminarTipoPredefinido($tipo['id_tipo']));

        $existeTipo = (int)$this->pdoNatacion->query(
            "SELECT COUNT(*) FROM tipos_test_predefinidos WHERE id_tipo_test = {$tipo['id_tipo']}"
        )->fetchColumn();
        $existeVars = (int)$this->pdoNatacion->query(
            "SELECT COUNT(*) FROM variables_test WHERE id_tipo_test = {$tipo['id_tipo']}"
        )->fetchColumn();
        $this->assertSame(0, $existeTipo);
        $this->assertSame(0, $existeVars, 'Las variables del tipo tambien deben eliminarse.');
    }

    // CP-INT-36: test personalizado se crea con creador, variables y aparece en el listado filtrado
    public function testCrearTestPersonalizadoYListadoPorCreador(): void
    {
        $idUsuario = $this->crearUsuarioPrueba();
        $nombre = 'TESTPRUEBA Personalizado ' . uniqid();

        $idPers = $this->tf->crearTestPersonalizado(
            ['nombre' => $nombre, 'unidad_medida' => 'seg', 'id_usuario_creador' => $idUsuario],
            [['nombre_variable' => 'T1', 'unidad' => 'seg'], ['nombre_variable' => 'T2', 'unidad' => 'seg']]
        );
        $this->assertGreaterThan(0, $idPers, 'Errores: ' . json_encode($this->tf->obtenerErrores()));

        $fila = $this->pdoNatacion->query(
            "SELECT nombre, activo, id_usuario_creador FROM tests_personalizados WHERE id_test_pers = {$idPers}"
        )->fetch(\PDO::FETCH_ASSOC);
        $this->assertSame($nombre, $fila['nombre']);
        $this->assertSame('1', (string)$fila['activo']);
        $this->assertSame((string)$idUsuario, (string)$fila['id_usuario_creador']);

        $listado = $this->tf->listarTestsPersonalizados($idUsuario);
        $ids = array_map('intval', array_column($listado, 'id_test_pers'));
        $this->assertContains($idPers, $ids, 'El listado filtrado por creador debe incluir el test.');
        $this->assertCount(2, $listado[0]['variables'] ?? [], 'El test listado debe traer sus 2 variables.');
    }

    // CP-INT-37: sin id_usuario_creador valido el modelo inserta 0 — hallazgo TF-3
    public function testPersonalizadoSinCreadorSeInsertaConCero(): void
    {
        $nombre = 'TESTPRUEBA SinCreador ' . uniqid();

        $idPers = $this->tf->crearTestPersonalizado(
            ['nombre' => $nombre, 'id_usuario_creador' => null],
            [            ['nombre_variable' => 'Var X', 'unidad' => 'u']]
        );
        $this->assertGreaterThan(0, $idPers, 'Errores: ' . json_encode($this->tf->obtenerErrores()));

        $creador = $this->pdoNatacion->query(
            "SELECT id_usuario_creador FROM tests_personalizados WHERE id_test_pers = {$idPers}"
        )->fetchColumn();
        $this->assertSame('0', (string)$creador, 'Hallazgo TF-3: (int)null produce 0 y la BD lo acepta (referencia a usuario inexistente).');
    }

    // CP-INT-38: el detalle trae valores ordenados y el historial solo considera tests Completos
    public function testDetalleConHistorialSoloCompleto(): void
    {
        $idAtleta = $this->idAtletaPrueba();
        $tipo = $this->crearTipoPrueba();

        $idParcial = $this->crearRegistroPrueba($idAtleta, $tipo['id_tipo'], $tipo['variables'], 'Parcial', '2026-01-10');
        $idCompleto = $this->crearRegistroPrueba($idAtleta, $tipo['id_tipo'], $tipo['variables'], 'Completo', '2026-01-20');

        $detalle = $this->tf->obtenerDetallePorId($idCompleto);
        $this->assertNotNull($detalle, 'El detalle debe existir.');
        $this->assertStringContainsString('TESTPRUEBA', $detalle['nombre_atleta']);

        $this->assertCount(2, $detalle['valores_detalle'], 'El detalle debe traer los 2 valores del registro.');
        $this->assertEqualsWithDelta(10.5, (float)$detalle['valores_detalle'][0]['valor'], 0.001);

        $this->assertCount(1, $detalle['historial_evolucion'], 'Hallazgo/control: el historial debe excluir el registro Parcial.');
        $this->assertEqualsWithDelta(10.5, (float)$detalle['historial_evolucion'][0]['valor'], 0.001);
        $this->assertSame('2026-01-20', $detalle['historial_evolucion'][0]['fecha']);
        $this->assertNotSame((string)$idParcial, (string)$idCompleto);
    }

    // CP-INT-39: el resumen por atleta cuenta solo las aplicaciones Completas
    public function testResumenPorAtletaCuentaSoloCompletos(): void
    {
        $idAtleta = $this->idAtletaPrueba();
        $tipo = $this->crearTipoPrueba();

        $this->crearRegistroPrueba($idAtleta, $tipo['id_tipo'], $tipo['variables'], 'Parcial', '2026-01-10');
        $this->crearRegistroPrueba($idAtleta, $tipo['id_tipo'], $tipo['variables'], 'Completo', '2026-01-20');

        $resumen = $this->tf->resumenPorAtleta($idAtleta);
        $filaTipo = null;
        foreach ($resumen as $fila) {
            if ((string)$fila['id_tipo_test'] === (string)$tipo['id_tipo']) {
                $filaTipo = $fila;
                break;
            }
        }
        $this->assertNotNull($filaTipo, 'El resumen debe incluir el tipo de prueba.');
        $this->assertSame('1', (string)$filaTipo['total_aplicaciones'], 'Solo se debe contar el registro Completo.');
        $this->assertSame('2026-01-20', $filaTipo['ultima_fecha']);
    }
}

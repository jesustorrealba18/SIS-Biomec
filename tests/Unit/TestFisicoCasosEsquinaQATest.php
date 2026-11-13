<?php

namespace Tests\Unit;

use PDO;
use PHPUnit\Framework\TestCase;
use GrupoProyecto\SisBiomec\modelo\TestFisico;

/**
 * =============================================================================
 * SUITE ADVERSARIAL DE QA - MODULO: src/modelo/TestFisico.php
 * =============================================================================
 * Casos esquina: tipos incorrectos (arrays), nulos, coerciones silenciosas
 * ((float)'abc' = 0), claves inexistentes, rowCount() de MySQL, duplicados.
 * Si una prueba FALLA se reprodujo un defecto. Informe formal en
 * docs/qa-evidencia/TestFisico/informe-errores-TestFisico.md
 * Limpieza: registros marcados con observaciones 'QATF%' / id_atleta 99999999;
 * catalogos creados con id > snapshot capturado en setUp().
 * =============================================================================
 */
class TestFisicoCasosEsquinaQATest extends TestCase
{
    private TestFisico $modelo;
    private PDO $pdo;
    private int $snapshotTipo;
    private int $snapshotPers;

    protected function setUp(): void
    {
        $this->modelo = new TestFisico();
        $this->pdo = new PDO(
            'mysql:host=localhost;dbname=sis_natacion;charset=utf8mb4',
            'root',
            '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $this->snapshotTipo = (int)$this->pdo->query('SELECT COALESCE(MAX(id_tipo_test),0) FROM tipos_test_predefinidos')->fetchColumn();
        $this->snapshotPers = (int)$this->pdo->query('SELECT COALESCE(MAX(id_test_pers),0) FROM tests_personalizados')->fetchColumn();
    }

    protected function tearDown(): void
    {
        try {
            // Registros y valores de prueba (valores_test no tiene FK: borrar a mano).
            $ids = $this->pdo->query(
                "SELECT id_registro_test FROM registros_test WHERE observaciones LIKE 'QATF%' OR id_atleta = 99999999"
            )->fetchAll(PDO::FETCH_COLUMN);
            if ($ids) {
                $marcadores = implode(',', array_fill(0, count($ids), '?'));
                $this->pdo->prepare("DELETE FROM valores_test WHERE id_registro_test IN ($marcadores)")->execute($ids);
                $this->pdo->prepare("DELETE FROM registros_test WHERE id_registro_test IN ($marcadores)")->execute($ids);
            }
            // Catalogos creados durante la sesion de pruebas.
            $this->pdo->exec("DELETE FROM variables_test WHERE id_tipo_test > {$this->snapshotTipo} OR id_test_pers > {$this->snapshotPers}");
            $this->pdo->exec("DELETE FROM tipos_test_predefinidos WHERE id_tipo_test > {$this->snapshotTipo}");
            $this->pdo->exec("DELETE FROM tests_personalizados WHERE id_test_pers > {$this->snapshotPers}");
        } catch (\Throwable $e) {
            fwrite(STDERR, 'AVISO limpieza QA TestFisico: ' . $e->getMessage() . PHP_EOL);
        }
        unset($this->modelo, $this->pdo);
    }

    // =========================================================================
    // Datos base (atleta 1, tipo 1 con variable 1 existen en la BD de desarrollo)
    // =========================================================================

    private function datosValidos(array $overrides = []): array
    {
        return array_merge([
            'id_atleta'      => '1',
            'id_tipo_test'   => '1',
            'fecha'          => date('Y-m-d'),
            'observaciones'  => 'QATF' . bin2hex(random_bytes(4)),
            'estado'         => 'Completo',
            'id_usuario_toma' => '1',
            'valores'        => ['1' => '12.5'],
        ], $overrides);
    }

    // =========================================================================
    // QA-TF-01 | setAtributos() + validarAtributosInternos() - TestFisico.php:23-24, 52-53
    // Caso esquina: estado enviado como array (estado[]=Completo por HTTP).
    // Contrato: registrar error de validacion, jamas TypeError (HTTP 500).
    // Defecto: setAtributos almacena arrays tal cual y enEnum() exige string.
    // =========================================================================
    public function testEstadoComoArrayDebeRegistrarErrorNoLanzarTypeError(): void
    {
        $excepcion = null;
        try {
            $this->modelo->setAtributos($this->datosValidos(['estado' => ['Completo']]));
            $this->modelo->getRegistrarTest();
        } catch (\Throwable $e) {
            $excepcion = $e;
        }
        $this->assertNull(
            $excepcion,
            'BUG-TF-01: getRegistrarTest() con estado tipo array crashea: [' . get_class($excepcion) . '] ' . $excepcion->getMessage()
        );
        $this->assertArrayHasKey('estado', $this->modelo->obtenerErrores(), 'BUG-TF-01: debio registrarse error de validacion para estado.');
    }

    // =========================================================================
    // QA-TF-02 | validarAtributosInternos() - TestFisico.php:44-46
    // Caso esquina: registro sin fecha (la validacion la declara OPCIONAL pero
    // la columna registros_test.fecha es NOT NULL).
    // Contrato: validar fecha como obligatoria y explicar el fallo al usuario.
    // Defecto: pasa validacion -> PDOException -> error generico 'bd'.
    // =========================================================================
    public function testRegistroSinFechaDebeGenerarErrorDeValidacionExplicito(): void
    {
        $this->modelo->setAtributos($this->datosValidos(['fecha' => '']));
        $resultado = $this->modelo->getRegistrarTest();

        $this->assertFalse($resultado, 'BUG-TF-02: sin fecha el registro no debe llegar a la BD.');
        $this->assertArrayHasKey(
            'fecha',
            $this->modelo->obtenerErrores(),
            'BUG-TF-02: sin fecha el fallo se reporta solo como error generico de BD: ' . json_encode($this->modelo->obtenerErrores())
        );
    }

    // =========================================================================
    // QA-TF-03 | registrarTestBD() - TestFisico.php:122 (cast (float))
    // Caso esquina: valor de variable no numerico ('veinticinco' / 'abc').
    // Contrato: rechazar valores no numericos con error de validacion.
    // Defecto: (float)'abc' = 0.0 silencioso -> se registra medicion 0.00.
    // =========================================================================
    public function testValorNoNumericoNoDebeRegistrarseComoCero(): void
    {
        $marca = 'QATF' . bin2hex(random_bytes(4));
        $this->modelo->setAtributos($this->datosValidos([
            'observaciones' => $marca,
            'valores'       => ['1' => 'no-es-un-numero'],
        ]));
        $resultado = $this->modelo->getRegistrarTest();

        $valor = $this->pdo->prepare(
            'SELECT vt.valor FROM valores_test vt INNER JOIN registros_test rt ON rt.id_registro_test = vt.id_registro_test WHERE rt.observaciones = ?'
        );
        $valor->execute([$marca]);
        $guardado = $valor->fetchColumn();

        $this->assertFalse(
            $resultado,
            'BUG-TF-03: getRegistrarTest() retorno true y la BD guardo la medicion "' . $valorGuardado = var_export($guardado, true) . '" (coercion silenciosa (float)"no-es-un-numero" = 0.0).'
        );
        $this->assertFalse($guardado !== false, 'BUG-TF-03: la fila con valor corrupto quedo persistida.');
    }

    // =========================================================================
    // QA-TF-04 | validarAtributosInternos() - TestFisico.php:39-42
    // Caso esquina: id_atleta inexistente (99999999; soloNumeros pasa porque
    // son digitos y registros_test NO tiene FK a atletas).
    // Contrato: rechazar la referencia a un atleta inexistente.
    // Defecto: se persiste un registro huerfano y retorna true.
    // =========================================================================
    public function testIdAtletaInexistenteNoDebePermitirRegistroHuerfano(): void
    {
        $this->modelo->setAtributos($this->datosValidos(['id_atleta' => '99999999']));
        $resultado = $this->modelo->getRegistrarTest();

        $this->assertFalse(
            $resultado,
            'BUG-TF-04: getRegistrarTest() retorno true para id_atleta=99999999 (inexistente): registro huerfano persistido (sin FK que lo impida).'
        );
    }

    // =========================================================================
    // QA-TF-05 | actualizarTestBD() - TestFisico.php:185-189
    // Caso esquina: actualizacion con valores IDENTICOS a los ya almacenados.
    // Contrato: la actualizacion sin cambios debe completarse con exito.
    // Defecto: rowCount() de MySQL cuenta filas CAMBIADAS (0) y el metodo
    // reporta "No se encontro el registro a actualizar" para un registro existente.
    // =========================================================================
    public function testActualizacionSinCambiosNoDebeReportarRegistroInexistente(): void
    {
        $marca = 'QATF' . bin2hex(random_bytes(4));
        $datos = $this->datosValidos(['observaciones' => $marca]);

        $this->modelo->setAtributos($datos);
        $this->assertTrue($this->modelo->getRegistrarTest(), 'Precondicion: el registro inicial debe crearse.');

        $id = $this->pdo->prepare('SELECT id_registro_test FROM registros_test WHERE observaciones = ?');
        $id->execute([$marca]);
        $idRegistro = (int)$id->fetchColumn();

        $this->modelo->setAtributos(array_merge($datos, ['id_registro_test' => (string)$idRegistro]));
        $resultado = $this->modelo->getActualizarTest();

        $this->assertTrue(
            $resultado,
            'BUG-TF-05: actualizar el registro existente #' . $idRegistro . ' con los mismos valores retorno false con error "' .
            ($this->modelo->obtenerErrores()['actualizacion'] ?? 'ninguno') . '" — rowCount()=0 se confunde con inexistencia.'
        );
    }

    // =========================================================================
    // QA-TF-06 | crearTipoPredefinido() - TestFisico.php:462-471
    // Caso esquina: valor_referencia_min=100 y valor_referencia_max=10.
    // Contrato: min no puede ser mayor que max (incoherencia logica).
    // Defecto: no existe validacion cruzada; el tipo se crea.
    // =========================================================================
    public function testReferenciaMinMayorQueMaxDebeSerRechazada(): void
    {
        $resultado = $this->modelo->crearTipoPredefinido(
            [
                'nombre'               => 'QATF MINMAX ' . bin2hex(random_bytes(3)),
                'valor_referencia_min' => '100',
                'valor_referencia_max' => '10',
            ],
            [['nombre_variable' => 'Variable QA', 'unidad' => 'm']]
        );

        $this->assertFalse(
            $resultado,
            'BUG-TF-06: crearTipoPredefinido() acepto valor_referencia_min=100 > valor_referencia_max=10 y creo el tipo de test.'
        );
        $this->assertNotEmpty($this->modelo->obtenerErrores(), 'BUG-TF-06: la incoherencia min>max no genero ningun error.');
    }

    // =========================================================================
    // QA-TF-07 | crearTipoPredefinido() - TestFisico.php:477 (trim)
    // Caso esquina: nombre_variable de una variable llega como array.
    // Contrato: registrar error, jamas TypeError (HTTP 500).
    // Defecto: trim(array) lanza TypeError no capturado.
    // =========================================================================
    public function testNombreVariableComoArrayDebeRegistrarErrorNoLanzarTypeError(): void
    {
        $excepcion = null;
        try {
            $this->modelo->crearTipoPredefinido(
                ['nombre' => 'QATF Crash ' . bin2hex(random_bytes(3))],
                [['nombre_variable' => ['x'], 'unidad' => 'm']]
            );
        } catch (\Throwable $e) {
            $excepcion = $e;
        }
        $this->assertNull(
            $excepcion,
            'BUG-TF-07: crearTipoPredefinido() con nombre_variable tipo array crashea: [' . get_class($excepcion) . '] ' . $excepcion->getMessage()
        );
    }

    // =========================================================================
    // QA-TF-08 | crearTestPersonalizado() - TestFisico.php:724
    // Caso esquina: crear un test personalizado sin id_usuario_creador.
    // Contrato: validar el campo y registrar error explicativo.
    // Defecto: acceso directo $datos['id_usuario_creador'] -> Warning
    // "Undefined array key" y se inserta id_usuario_creador = 0.
    // =========================================================================
    public function testCrearTestPersonalizadoSinCreadorNoDebeEmitirWarningNiAceptarCero(): void
    {
        $excepcion = null;
        $idCreado = 0;
        try {
            $idCreado = $this->modelo->crearTestPersonalizado(
                ['nombre' => 'QATF SinCreador ' . bin2hex(random_bytes(3))],
                [['nombre_variable' => 'Variable QA', 'unidad' => 'm']]
            );
        } catch (\Throwable $e) {
            $excepcion = $e;
        }
        $this->assertNull(
            $excepcion,
            'BUG-TF-08: crearTestPersonalizado() sin id_usuario_creador revienta con: [' . get_class($excepcion) . '] ' . $excepcion->getMessage()
        );
        $this->assertSame(
            0,
            $idCreado,
            'BUG-TF-08: se creo el test #' . $idCreado . ' sin creador (id_usuario_creador queda en 0).'
        );
    }

    // =========================================================================
    // QA-TF-09 | crearTipoPredefinido() - TestFisico.php:437-528
    // Caso esquina: nombre duplicado de tipo de test ya existente
    // ('Lactato 4x200 Acum'). La tabla NO tiene UNIQUE(nombre) y no se usa unico().
    // Contrato: rechazar duplicados de catalogo.
    // Defecto: se crea el duplicado y retorna true.
    // =========================================================================
    public function testNombreDeTipoDeTestDuplicadoDebeSerRechazado(): void
    {
        $existente = $this->pdo->query(
            'SELECT nombre FROM tipos_test_predefinidos WHERE activo = 1 AND es_personalizado = 0 LIMIT 1'
        )->fetchColumn();
        if ($existente === false) {
            $this->markTestSkipped('No hay tipos de test predefinidos activos en la BD.');
        }

        $resultado = $this->modelo->crearTipoPredefinido(
            ['nombre' => $existente],
            [['nombre_variable' => 'Variable QA', 'unidad' => 'm']]
        );

        $this->assertFalse(
            $resultado,
            'BUG-TF-09: crearTipoPredefinido() creo un duplicado exacto del tipo "' . $existente . '" (sin unique ni validacion de nombre).'
        );
    }

    // =========================================================================
    // QA-TF-10 | CONTROL (prueba de humo del arnes)
    // =========================================================================
    public function testControlRegistroValidoRetornaTrue(): void
    {
        $this->modelo->setAtributos($this->datosValidos());
        $this->assertTrue($this->modelo->getRegistrarTest(), 'Control: registro valido debe retornar true.');
    }
}

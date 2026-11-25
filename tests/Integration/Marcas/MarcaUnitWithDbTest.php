<?php

namespace Tests\Integration\Marcas;

use GrupoProyecto\SisBiomec\modelo\Marca;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\TestDox;
use PDO;

/**
 * Tests marcados como "UNI" en la matriz pero que requieren conexión a BD
 * porque realizan INSERT/SELECT/DELETE sobre marcas, marcas_splits y marcas_swolf.
 * Se ubican en Integration para mantener la coherencia con la infraestructura.
 */
class MarcaUnitWithDbTest extends TestCase
{
    private static $pdo;
    private static $idMarcaCreada = null;

    public static function setUpBeforeClass(): void
    {
        self::$pdo = new PDO('mysql:host=localhost;dbname=sis_natacion;charset=utf8', 'root', '');
        self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    protected function setUp(): void
    {
        self::$idMarcaCreada = null;
    }

    protected function tearDown(): void
    {
        if (self::$idMarcaCreada) {
            self::$pdo->exec("DELETE FROM marcas_splits WHERE id_marca = " . (int)self::$idMarcaCreada);
            self::$pdo->exec("DELETE FROM marcas_swolf WHERE id_marca = " . (int)self::$idMarcaCreada);
            self::$pdo->exec("DELETE FROM marcas WHERE id_marca = " . (int)self::$idMarcaCreada);
            self::$idMarcaCreada = null;
        }
    }

    private function payloadBase(array $overrides = []): array
    {
        $stmt = self::$pdo->query("SELECT fecha FROM sesiones WHERE id_sesion = 8");
        $fechaRealSesion = $stmt->fetchColumn() ?: '2026-11-09';

        return array_merge([
            'id_atleta'           => 13,
            'id_sesion'           => 8,
            'id_evento'           => null,
            'estilo'              => 'Libre',
            'distancia_m'         => 100,
            'tipo_piscina'        => '50m',
            'tiempo_final_seg'    => '60.00',
            'tiempo_reaccion_seg' => '0.65',
            'nivel_evento'        => 'Control',
            'fecha'               => $fechaRealSesion,
            'splits'              => [25 => '15.00', 50 => '15.00', 75 => '15.00', 100 => '15.00'],
            'virajes'             => [],
            'brazadas_por_largo'  => 16,
            'observaciones'       => 'TestUnitWithDb'
        ], $overrides);
    }

    // ─────────────────────────────────────────────────────────────
    // CP-UNI-07.01: Cálculo automático SWOLF
    // ─────────────────────────────────────────────────────────────
    #[TestDox('CP-UNI-07.01: Cálculo automático SWOLF')]
    public function test_CP_UNI_07_01_swolf_calculo(): void
    {
        $marca = new Marca();
        $marca->setAtributos($this->payloadBase([
            'tiempo_final_seg'   => '60.00',
            'brazadas_por_largo' => 16,
            'splits'             => [25 => '15.00', 50 => '15.00', 75 => '15.00', 100 => '15.00'],
        ]));

        $this->assertTrue($marca->getRegistrarMarca(), 'FALLO: No se pudo registrar la marca.');
        self::$idMarcaCreada = $marca->getCampo('id_marca');

        $stmt = self::$pdo->prepare("SELECT num_brazadas, swolf FROM marcas_swolf WHERE id_marca = :id");
        $stmt->execute([':id' => self::$idMarcaCreada]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotFalse($resultado, 'FALLO: No se generó fila en marcas_swolf.');
        $this->assertEquals(16, (int)$resultado['num_brazadas']);
        $this->assertEquals(46, (int)$resultado['swolf'], 'SWOLF esperado 46 = (60/2) + 16');
    }

    // ─────────────────────────────────────────────────────────────
    // CP-UNI-07.02: Sin brazadas → no genera SWOLF
    // ─────────────────────────────────────────────────────────────
    #[TestDox('CP-UNI-07.02: Sin brazadas no genera SWOLF')]
    public function test_CP_UNI_07_02_sin_brazadas_no_swolf(): void
    {
        $marca = new Marca();
        $marca->setAtributos($this->payloadBase(['brazadas_por_largo' => '']));

        $this->assertTrue($marca->getRegistrarMarca(), 'FALLO: No debería fallar por no tener brazadas.');
        self::$idMarcaCreada = $marca->getCampo('id_marca');

        $stmt = self::$pdo->prepare("SELECT COUNT(*) FROM marcas_swolf WHERE id_marca = :id");
        $stmt->execute([':id' => self::$idMarcaCreada]);
        $this->assertEquals(0, (int)$stmt->fetchColumn(), 'FALLO: Se generó SWOLF sin brazadas.');

        $stmt = self::$pdo->prepare("SELECT COUNT(*) FROM marcas WHERE id_marca = :id");
        $stmt->execute([':id' => self::$idMarcaCreada]);
        $this->assertEquals(1, (int)$stmt->fetchColumn(), 'FALLO: La marca no se guardó.');
    }

    // ─────────────────────────────────────────────────────────────
    // CP-UNI-06.19: Update de marca inexistente
    // ─────────────────────────────────────────────────────────────
    #[TestDox('CP-UNI-06.19: Actualizar marca inexistente debe rechazarse')]
    public function test_CP_UNI_06_19_update_inexistente(): void
    {
        $marca = new Marca();
        $marca->setAtributos($this->payloadBase(['id_marca' => 999999]));

        $resultado = $marca->getActualizarMarca();

        $this->assertFalse($resultado, 'FALLO: El sistema aceptó un UPDATE sobre un ID inexistente.');

        $errores = $marca->obtenerErrores();
        $this->assertArrayHasKey('id_marca', $errores);
        $this->assertStringContainsString('no existe', json_encode($errores, JSON_UNESCAPED_UNICODE));
    }

    // ─────────────────────────────────────────────────────────────
    // CP-UNI-06.25: Borrado de splits en actualización
    // ─────────────────────────────────────────────────────────────
    #[TestDox('CP-UNI-06.25: Borrado de splits al actualizar sin enviarlos')]
    public function test_CP_UNI_06_25_borrar_splits_al_actualizar(): void
    {
        // 1. Crear marca con 4 splits
        $marca = new Marca();
        $marca->setAtributos($this->payloadBase([
            'tiempo_final_seg' => '58.00',
            'splits'           => [25 => '14.50', 50 => '14.50', 75 => '14.50', 100 => '14.50'],
        ]));
        $this->assertTrue($marca->getRegistrarMarca(), 'FALLO: No se pudo crear la marca inicial.');
        $idMarca = $marca->getCampo('id_marca');
        self::$idMarcaCreada = $idMarca;

        $stmt = self::$pdo->prepare("SELECT COUNT(*) FROM marcas_splits WHERE id_marca = :id");
        $stmt->execute([':id' => $idMarca]);
        $this->assertEquals(4, (int)$stmt->fetchColumn(), 'FALLO: La marca inicial no tiene 4 splits.');

        // 2. Actualizar SIN splits
        $marcaUpdate = new Marca();
        $marcaUpdate->setAtributos($this->payloadBase([
            'id_marca'         => $idMarca,
            'tiempo_final_seg' => '57.00',
            'splits'           => [],
        ]));

        $this->assertTrue($marcaUpdate->getActualizarMarca(), 'FALLO: No se pudo actualizar la marca.');

        // 3. Verificar borrado de splits
        $stmt = self::$pdo->prepare("SELECT COUNT(*) FROM marcas_splits WHERE id_marca = :id");
        $stmt->execute([':id' => $idMarca]);
        $this->assertEquals(0, (int)$stmt->fetchColumn(), 'FALLO: Los splits huérfanos no se borraron.');

        // 4. Verificar actualización de tiempo
        $stmt = self::$pdo->prepare("SELECT tiempo_final_seg FROM marcas WHERE id_marca = :id");
        $stmt->execute([':id' => $idMarca]);
        $this->assertEquals(57.00, (float)$stmt->fetchColumn(), 'FALLO: El tiempo final no se actualizó.');
    }
}
<?php

namespace Tests\Unit\Marcas;

use GrupoProyecto\SisBiomec\modelo\Marca;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\TestDox;
use ReflectionClass;

class MarcaValidationTest extends TestCase
{
    private Marca $marca;
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        $this->reflection = new ReflectionClass(Marca::class);
        // Evitamos la conexión a BD instanciando sin constructor
        $this->marca = $this->reflection->newInstanceWithoutConstructor();
    }

    private function invokePrivate(string $method, array $args = [])
    {
        $m = $this->reflection->getMethod($method);
        if (PHP_VERSION_ID < 80100) {
            $m->setAccessible(true);
        }
        return $m->invokeArgs($this->marca, $args);
    }

    private function payloadBase(array $overrides = []): array
    {
        return array_merge([
            'id_atleta'        => 1,
            'id_sesion'        => 1,
            'id_evento'        => null,
            'estilo'           => 'Libre',
            'distancia_m'      => 100,
            'tipo_piscina'     => '50m',
            'tiempo_final_seg' => '60.00',
            'fecha'            => date('Y-m-d'),
            'splits'           => [25 => '15.00', 50 => '15.00', 75 => '15.00', 100 => '15.00'],
            'virajes'          => [],
        ], $overrides);
    }

    // ─────────────────────────────────────────────────────────────
    // GRUPO 1 — RF-06: Validaciones base
    // ─────────────────────────────────────────────────────────────

    #[TestDox('CP-UNI-06.01: XOR sesión+evento')]
    public function test_CP_UNI_06_01_xor_sesion_evento(): void
    {
        $this->markTestIncomplete('Requiere BD (valida asistencia y evento). Se hará en Semana 12.');
    }

    #[TestDox('CP-UNI-06.02: Sin sesión ni evento')]
    public function test_CP_UNI_06_02_sin_contexto(): void
    {
        $this->markTestIncomplete('Requiere BD. Se hará en Semana 12.');
    }

    #[TestDox('CP-UNI-06.03: id_atleta="0" debe rechazarse')]
    public function test_CP_UNI_06_03_atleta_cero_debe_fallar(): void
    {
        $this->marca->setAtributos($this->payloadBase(['id_atleta' => '0']));
        $valido = $this->invokePrivate('validarAtributosInternos');
        
        $this->assertFalse($valido, 'FALLO: El sistema aceptó id_atleta="0".');
    }

    #[TestDox('CP-UNI-06.04: id_sesion[] (array) debe rechazarse')]
    public function test_CP_UNI_06_04_id_sesion_array_debe_fallar(): void
    {
        $this->marca->setAtributos($this->payloadBase(['id_sesion' => [1, 2]]));
        $valido = $this->invokePrivate('validarAtributosInternos');
        
        $this->assertFalse($valido, 'FALLO: El sistema aceptó un array en id_sesion.');
    }

    #[TestDox('CP-UNI-06.05: Distancia 1000m fuera del enum')]
    public function test_CP_UNI_06_05_distancia_invalida(): void
    {
        $this->marca->setAtributos($this->payloadBase(['distancia_m' => '1000']));
        $this->assertFalse($this->invokePrivate('validarAtributosInternos'));
    }

    #[TestDox('CP-UNI-06.06: Tipo piscina "75m" fuera del enum')]
    public function test_CP_UNI_06_06_piscina_invalida(): void
    {
        $this->marca->setAtributos($this->payloadBase(['tipo_piscina' => '75m']));
        $this->assertFalse($this->invokePrivate('validarAtributosInternos'));
    }

    #[TestDox('CP-UNI-06.07: Estilo "crol" (minúsculas) debe rechazarse')]
    public function test_CP_UNI_06_07_estilo_invalido(): void
    {
        $this->marca->setAtributos($this->payloadBase(['estilo' => 'crol']));
        $this->assertFalse($this->invokePrivate('validarAtributosInternos'));
    }

    #[TestDox('CP-UNI-06.08: Fecha futura debe rechazarse')]
    public function test_CP_UNI_06_08_fecha_futura(): void
    {
        $this->marca->setAtributos($this->payloadBase(['fecha' => '2099-01-01']));
        $this->assertFalse($this->invokePrivate('validarAtributosInternos'));
    }

    #[TestDox('CP-UNI-06.09: Tiempo final "0.00" debe rechazarse')]
    public function test_CP_UNI_06_09_tiempo_cero(): void
    {
        $this->marca->setAtributos($this->payloadBase([
            'tiempo_final_seg' => '0.00',
            'splits' => [25 => '0.00', 50 => '0.00', 75 => '0.00', 100 => '0.00'],
        ]));
        $this->assertFalse($this->invokePrivate('validarAtributosInternos'), 'FALLO: El sistema aceptó tiempo 0.00');
    }

    #[TestDox('CP-UNI-06.10: Tiempo final negativo debe rechazarse')]
    public function test_CP_UNI_06_10_tiempo_negativo(): void
    {
        $this->marca->setAtributos($this->payloadBase([
            'tiempo_final_seg' => '-5.00',
            'splits' => [25 => '-1.00', 50 => '-1.00', 75 => '-1.00', 100 => '-2.00'],
        ]));
        $this->assertFalse($this->invokePrivate('validarAtributosInternos'));
    }

    #[TestDox('CP-UNI-06.11: Tiempo final absurdo (99999s)')]
    public function test_CP_UNI_06_11_tiempo_absurdo(): void
    {
        $this->marca->setAtributos($this->payloadBase([
            'tiempo_final_seg' => '99999.00',
            'splits' => [25 => '24999.00', 50 => '24999.00', 75 => '24999.00', 100 => '25002.00'],
        ]));
        $this->assertFalse($this->invokePrivate('validarAtributosInternos'), 'FALLO: El sistema aceptó un tiempo biológicamente imposible.');
    }

    #[TestDox('CP-UNI-06.12: Reacción 999.99s debe rechazarse')]
    public function test_CP_UNI_06_12_reaccion_absurda(): void
    {
        $this->marca->setAtributos($this->payloadBase(['tiempo_reaccion_seg' => '999.99']));
        $this->assertFalse($this->invokePrivate('validarAtributosInternos'));
    }

    // ─────────────────────────────────────────────────────────────
    // GRUPO 2 — RF-06.1: Splits
    // ─────────────────────────────────────────────────────────────

    #[TestDox('CP-UNI-06.13: 100m con solo 3 splits')]
    public function test_CP_UNI_06_13_splits_incompletos(): void
    {
        $this->marca->setAtributos($this->payloadBase([
            'splits' => [25 => '15.00', 50 => '15.00', 75 => '15.00'],
        ]));
        $this->assertFalse($this->invokePrivate('validarAtributosInternos'));
    }

    #[TestDox('CP-UNI-06.14: Split a 30m (no múltiplo de 25)')]
    public function test_CP_UNI_06_14_split_no_multiplo(): void
    {
        $this->marca->setAtributos($this->payloadBase([
            'splits' => [25 => '15.00', 30 => '15.00', 50 => '15.00', 100 => '30.00'],
        ]));
        $this->assertFalse($this->invokePrivate('validarAtributosInternos'));
    }

    #[TestDox('CP-UNI-06.15: Split con clave 0')]
    public function test_CP_UNI_06_15_split_cero(): void
    {
        $this->marca->setAtributos($this->payloadBase([
            'splits' => [0 => '15.00', 25 => '15.00', 50 => '15.00', 75 => '15.00', 100 => '15.00'],
        ]));
        $this->assertFalse($this->invokePrivate('validarAtributosInternos'), 'FALLO: El sistema aceptó un split en 0m.');
    }

    #[TestDox('CP-UNI-06.16: Split con clave negativa')]
    public function test_CP_UNI_06_16_split_negativo(): void
    {
        $this->marca->setAtributos($this->payloadBase([
            'splits' => [-25 => '15.00', 50 => '15.00', 75 => '15.00', 100 => '15.00'],
        ]));
        $this->assertFalse($this->invokePrivate('validarAtributosInternos'), 'FALLO: El sistema aceptó un split en -25m.');
    }

    #[TestDox('CP-UNI-06.17: Splits desordenados o sin meta')]
    public function test_CP_UNI_06_17_splits_desordenados(): void
    {
        $this->marca->setAtributos($this->payloadBase([
            'splits' => [25 => '15.00', 50 => '15.00', 75 => '15.00', 90 => '15.00'],
        ]));
        $this->assertFalse($this->invokePrivate('validarAtributosInternos'), 'FALLO: El sistema aceptó un split final en 90m en vez de 100m.');
    }

    #[TestDox('CP-UNI-06.18: Viraje en 25m para piscina 50m')]
    public function test_CP_UNI_06_18_viraje_inexistente(): void
    {
        $this->marca->setAtributos($this->payloadBase([
            'tipo_piscina' => '50m',
            'distancia_m'  => 200,
            'splits' => [
                25 => '15.00', 50 => '15.00', 75 => '15.00', 100 => '15.00',
                125 => '15.00', 150 => '15.00', 175 => '15.00', 200 => '15.00',
            ],
            'virajes' => [25 => '0.80'],
        ]));
        $this->assertFalse($this->invokePrivate('validarAtributosInternos'), 'FALLO: El sistema aceptó un viraje en una pared que no existe.');
    }

    // ─────────────────────────────────────────────────────────────
    // GRUPO 3 — RF-06.3 / RF-06.4: Update y Delete
    // ─────────────────────────────────────────────────────────────

    #[TestDox('CP-UNI-06.19: Actualizar id_marca inexistente')]
    public function test_CP_UNI_06_19_update_inexistente(): void
    {
        $this->markTestIncomplete('Requiere BD. Semana 12.');
    }

    #[TestDox('CP-UNI-06.20: Motivo con 4 letras')]
    public function test_CP_UNI_06_20_motivo_corto(): void
    {
        $this->marca->setAtributos(['id_marca' => 1, 'motivo_eliminacion' => 'abcd']);
        $this->assertFalse($this->invokePrivate('getEliminarMarca'));
    }

    #[TestDox('CP-UNI-06.21: Motivo de solo espacios')]
    public function test_CP_UNI_06_21_motivo_espacios(): void
    {
        $this->marca->setAtributos(['id_marca' => 1, 'motivo_eliminacion' => '     ']);
        $this->assertFalse($this->invokePrivate('getEliminarMarca'), 'FALLO: El sistema aceptó 5 espacios como justificación válida.');
    }

    // ─────────────────────────────────────────────────────────────
    // GRUPO 4 — RF-07: SWOLF
    // ─────────────────────────────────────────────────────────────

    #[TestDox('CP-UNI-07.01: Cálculo SWOLF')]
    public function test_CP_UNI_07_01_swolf(): void
    {
        $this->markTestIncomplete('Requiere BD (INSERT). Semana 12.');
    }

    #[TestDox('CP-UNI-07.02: Brazadas 0 no genera SWOLF')]
    public function test_CP_UNI_07_02_sin_brazadas(): void
    {
        $this->markTestIncomplete('Requiere BD. Semana 12.');
    }

    #[TestDox('CP-UNI-07.03: Brazadas negativas')]
    public function test_CP_UNI_07_03_brazadas_negativas(): void
    {
        $this->marca->setAtributos($this->payloadBase(['brazadas_por_largo' => '-5']));
        $this->assertFalse($this->invokePrivate('validarAtributosInternos'));
    }

    #[TestDox('CP-UNI-07.04: Brazadas de 1000 (límite 999)')]
    public function test_CP_UNI_07_04_brazadas_1000(): void
    {
        $this->marca->setAtributos($this->payloadBase(['brazadas_por_largo' => '1000']));
        $this->assertFalse($this->invokePrivate('validarAtributosInternos'));
    }
}
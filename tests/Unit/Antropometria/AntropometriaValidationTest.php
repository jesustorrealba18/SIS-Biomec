<?php

namespace Tests\Unit\Antropometria;

use GrupoProyecto\SisBiomec\modelo\MedicionAntropometrica;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\TestDox;
use ReflectionClass;

class AntropometriaValidationTest extends TestCase
{
    private MedicionAntropometrica $antropometria;
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        $this->reflection = new ReflectionClass(MedicionAntropometrica::class);
        $this->antropometria = $this->reflection->newInstanceWithoutConstructor();
    }

    private function invokePrivate(string $method, array $args = [])
    {
        $m = $this->reflection->getMethod($method);
        $m->setAccessible(true);
        return $m->invokeArgs($this->antropometria, $args);
    }

    private function payloadBase(array $overrides = []): array
    {
        return array_merge([
            'id_atleta' => '5',
            'fecha' => date('Y-m-d'),
            'peso_kg' => '75',
            'talla_cm' => '180',
            'envergadura_cm' => '180',
            'perimetro_abdominal_cm' => '80',
            'porcentaje_grasa' => '20',
            'responsable' => 'Dr. Test'
        ], $overrides);
    }

    // ─────────────────────────────────────────────────────────────
    // GRUPO 2 — Validaciones de Fechas
    // ─────────────────────────────────────────────────────────────

    #[TestDox('CP-UNI-05.01: Fecha futura debe rechazarse')]
    public function test_CP_UNI_05_01_fecha_futura(): void
    {
        $this->antropometria->setDatos($this->payloadBase(['fecha' => date('Y-m-d', strtotime('+1 day'))]));
        $this->assertFalse($this->invokePrivate('validarDatos'));
    }

    #[TestDox('CP-UNI-05.02: Boundary justo: Fecha de hoy debe aceptarse')]
    public function test_CP_UNI_05_02_fecha_hoy(): void
    {
        $this->antropometria->setDatos($this->payloadBase(['fecha' => date('Y-m-d')]));
        $this->assertTrue($this->invokePrivate('validarDatos'));
    }

    #[TestDox('CP-UNI-05.03: Fecha de hace exactamente 30 días debe aceptarse')]
    public function test_CP_UNI_05_03_fecha_30_dias(): void
    {
        $this->antropometria->setDatos($this->payloadBase(['fecha' => date('Y-m-d', strtotime('-30 days'))]));
        $this->assertTrue($this->invokePrivate('validarDatos'));
    }

    #[TestDox('CP-UNI-05.04: Fecha mayor a 1 mes debe rechazarse')]
    public function test_CP_UNI_05_04_fecha_antigua(): void
    {
        $this->antropometria->setDatos($this->payloadBase(['fecha' => date('Y-m-d', strtotime('-2 months'))]));
        $this->assertFalse($this->invokePrivate('validarDatos'));
    }

    #[TestDox('CP-UNI-05.05: Format bypass de fecha (2026-1-1)')]
    public function test_CP_UNI_05_05_formato_fecha_invalido(): void
    {
        $this->antropometria->setDatos($this->payloadBase(['fecha' => '2026-1-1']));
        $this->assertFalse($this->invokePrivate('validarDatos'));
    }

    // ─────────────────────────────────────────────────────────────
    // GRUPO 3 — Límites Biológicos (Peso, Talla, Envergadura)
    // ─────────────────────────────────────────────────────────────

    #[TestDox('CP-UNI-05.06: Peso menor a 20kg debe rechazarse')]
    public function test_CP_UNI_05_06_peso_inferior(): void
    {
        $this->antropometria->setDatos($this->payloadBase(['peso_kg' => '19.99']));
        $this->assertFalse($this->invokePrivate('validarDatos'));
    }

    #[TestDox('CP-UNI-05.07: Peso en boundary (20 y 200) debe aceptarse')]
    public function test_CP_UNI_05_07_peso_boundary(): void
    {
        $this->antropometria->setDatos($this->payloadBase(['peso_kg' => '20']));
        $this->assertTrue($this->invokePrivate('validarDatos'));
        $this->antropometria->setDatos($this->payloadBase(['peso_kg' => '200']));
        $this->assertTrue($this->invokePrivate('validarDatos'));
    }

    #[TestDox('CP-UNI-05.08: Peso mayor a 200kg debe rechazarse')]
    public function test_CP_UNI_05_08_peso_superior(): void
    {
        $this->antropometria->setDatos($this->payloadBase(['peso_kg' => '200.01']));
        $this->assertFalse($this->invokePrivate('validarDatos'));
    }

    #[TestDox('CP-UNI-05.09: Cast silencioso en peso (150kg)')]
    public function test_CP_UNI_05_09_cast_silencioso(): void
    {
        $this->antropometria->setDatos($this->payloadBase(['peso_kg' => '150kg']));
        $this->assertFalse($this->invokePrivate('validarDatos'));
    }

    #[TestDox('CP-UNI-05.10: Talla menor a 100cm debe rechazarse')]
    public function test_CP_UNI_05_10_talla_inferior(): void
    {
        $this->antropometria->setDatos($this->payloadBase(['talla_cm' => '99.99']));
        $this->assertFalse($this->invokePrivate('validarDatos'));
    }

    #[TestDox('CP-UNI-05.11: Talla en boundary (100 y 250) debe aceptarse')]
    public function test_CP_UNI_05_11_talla_boundary(): void
    {
        $this->antropometria->setDatos($this->payloadBase(['talla_cm' => '100']));
        $this->assertTrue($this->invokePrivate('validarDatos'));
        $this->antropometria->setDatos($this->payloadBase(['talla_cm' => '250']));
        $this->assertTrue($this->invokePrivate('validarDatos'));
    }

    #[TestDox('CP-UNI-05.12: Talla mayor a 250cm debe rechazarse')]
    public function test_CP_UNI_05_12_talla_superior(): void
    {
        $this->antropometria->setDatos($this->payloadBase(['talla_cm' => '250.01']));
        $this->assertFalse($this->invokePrivate('validarDatos'));
    }

    #[TestDox('CP-UNI-05.13: Envergadura menor a 100cm debe rechazarse')]
    public function test_CP_UNI_05_13_envergadura_inferior(): void
    {
        $this->antropometria->setDatos($this->payloadBase(['envergadura_cm' => '99.99']));
        $this->assertFalse($this->invokePrivate('validarDatos'));
    }

    #[TestDox('CP-UNI-05.14: Envergadura boundary (100 y 250) debe aceptarse')]
    public function test_CP_UNI_05_14_envergadura_boundary(): void
    {
        $this->antropometria->setDatos($this->payloadBase(['envergadura_cm' => '100']));
        $this->assertTrue($this->invokePrivate('validarDatos'));
        $this->antropometria->setDatos($this->payloadBase(['envergadura_cm' => '250']));
        $this->assertTrue($this->invokePrivate('validarDatos'));
    }

    // ─────────────────────────────────────────────────────────────
    // GRUPO 4 — Límites (Perímetro, Grasa, IMC)
    // ─────────────────────────────────────────────────────────────

    #[TestDox('CP-UNI-05.15: Perímetro menor a 40cm debe rechazarse')]
    public function test_CP_UNI_05_15_perimetro_inferior(): void
    {
        $this->antropometria->setDatos($this->payloadBase(['perimetro_abdominal_cm' => '39.99']));
        $this->assertFalse($this->invokePrivate('validarDatos'));
    }

    #[TestDox('CP-UNI-05.16: Perímetro boundary (40 y 150) debe aceptarse')]
    public function test_CP_UNI_05_16_perimetro_boundary(): void
    {
        $this->antropometria->setDatos($this->payloadBase(['perimetro_abdominal_cm' => '40']));
        $this->assertTrue($this->invokePrivate('validarDatos'));
        $this->antropometria->setDatos($this->payloadBase(['perimetro_abdominal_cm' => '150']));
        $this->assertTrue($this->invokePrivate('validarDatos'));
    }

    #[TestDox('CP-UNI-05.17: Grasa menor a 3% debe rechazarse')]
    public function test_CP_UNI_05_17_grasa_inferior(): void
    {
        $this->antropometria->setDatos($this->payloadBase(['porcentaje_grasa' => '2.99']));
        $this->assertFalse($this->invokePrivate('validarDatos'));
    }

    #[TestDox('CP-UNI-05.18: Grasa boundary (3 y 50) debe aceptarse')]
    public function test_CP_UNI_05_18_grasa_boundary(): void
    {
        $this->antropometria->setDatos($this->payloadBase(['porcentaje_grasa' => '3']));
        $this->assertTrue($this->invokePrivate('validarDatos'));
        $this->antropometria->setDatos($this->payloadBase(['porcentaje_grasa' => '50']));
        $this->assertTrue($this->invokePrivate('validarDatos'));
    }

    #[TestDox('CP-UNI-05.19: Grasa mayor a 50% debe rechazarse')]
    public function test_CP_UNI_05_19_grasa_superior(): void
    {
        $this->antropometria->setDatos($this->payloadBase(['porcentaje_grasa' => '50.01']));
        $this->assertFalse($this->invokePrivate('validarDatos'));
    }

    #[TestDox('CP-UNI-05.20: Cálculo correcto de IMC')]
    public function test_CP_UNI_05_20_calculo_imc(): void
    {
        $this->antropometria->setDatos($this->payloadBase(['peso_kg' => '75', 'talla_cm' => '180']));
        $this->invokePrivate('calcularIMC');
        
        $datos = $this->reflection->getProperty('datos');
        $datos->setAccessible(true);
        $valores = $datos->getValue($this->antropometria);

        $this->assertEquals(23.1, $valores['imc']);
    }

    #[TestDox('CP-UNI-05.21: Caso borde IMC con talla extrema')]
    public function test_CP_UNI_05_21_imc_talla_extrema(): void
    {
        // La talla 0.01 falla en la validación biológica (min 100), por lo que no debería llegar al cálculo.
        $this->antropometria->setDatos($this->payloadBase(['talla_cm' => '0.01']));
        $this->assertFalse($this->invokePrivate('validarDatos'));
    }

    // ─────────────────────────────────────────────────────────────
    // GRUPO 5 — Validación de Payload e IDs
    // ─────────────────────────────────────────────────────────────

    #[TestDox('CP-UNI-05.22: id_atleta = 0 debe rechazarse')]
    public function test_CP_UNI_05_22_id_cero(): void
    {
        $this->antropometria->setDatos($this->payloadBase(['id_atleta' => '0']));
        $this->assertFalse($this->invokePrivate('validarDatos'));
    }

    #[TestDox('CP-UNI-05.23: id_atleta negativo debe rechazarse')]
    public function test_CP_UNI_05_23_id_negativo(): void
    {
        $this->antropometria->setDatos($this->payloadBase(['id_atleta' => '-5']));
        $this->assertFalse($this->invokePrivate('validarDatos'));
    }

    #[TestDox('CP-UNI-05.24: Inyección de Array en id_atleta')]
    public function test_CP_UNI_05_24_array_en_id_atleta(): void
    {
        $this->antropometria->setDatos($this->payloadBase(['id_atleta' => [5]]));
        $this->assertFalse($this->invokePrivate('validarDatos'));
    }

    #[TestDox('CP-UNI-05.26: Editar con id_medicion = 0')]
    public function test_CP_UNI_05_26_editar_id_cero(): void
    {
        $this->assertFalse($this->antropometria->actualizarMedicion($this->payloadBase(), 0));
    }

    #[TestDox('CP-UNI-05.27: Anular con id_medicion = 0')]
    public function test_CP_UNI_05_27_anular_id_cero(): void
    {
        $this->assertFalse($this->antropometria->anularMedicion(0, 'Motivo válido'));
    }

    #[TestDox('CP-UNI-05.28: Anular con motivo vacío')]
    public function test_CP_UNI_05_28_anular_motivo_vacio(): void
    {
        $this->assertFalse($this->antropometria->anularMedicion(1, '   '));
    }
}
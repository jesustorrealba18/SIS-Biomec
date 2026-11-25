<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use GrupoProyecto\SisBiomec\modelo\Sesiones;

class SesionesValidarSerieTest extends TestCase
{
    private Sesiones $ses;

    protected function setUp(): void
    {
        $this->ses = new Sesiones();
    }

    private function serieBase(array $override = []): array
    {
        return array_merge([
            'bloque'                => 'Principal',
            'id_drill'              => '3',
            'ejercicio_descripcion' => '',
            'repeticiones'          => '4',
            'distancia_m'           => '100',
            'descanso_seg'          => '30',
            'zona_intensidad'       => 'Z3',
            'ritmo_objetivo'        => '1:30',
        ], $override);
    }

    public function testSerieValida(): void
    {
        $this->assertEmpty($this->ses->validarSerie($this->serieBase()));
    }

    public function testBloqueInvalido(): void
    {
        $err = $this->ses->validarSerie($this->serieBase(['bloque' => 'Otro']));
        $this->assertArrayHasKey('bloque', $err);
    }

    public function testSinDrillNiDescripcion(): void
    {
        $err = $this->ses->validarSerie($this->serieBase(['id_drill' => '', 'ejercicio_descripcion' => '']));
        $this->assertArrayHasKey('ejercicio_descripcion', $err);
    }

    public function testSoloConDescripcionEsValida(): void
    {
        $err = $this->ses->validarSerie($this->serieBase([
            'id_drill' => '',
            'ejercicio_descripcion' => 'Nado libre continuo',
        ]));
        $this->assertArrayNotHasKey('ejercicio_descripcion', $err);
    }

    public function testDescripcionCorta(): void
    {
        $err = $this->ses->validarSerie($this->serieBase([
            'id_drill' => '',
            'ejercicio_descripcion' => 'ab',
        ]));
        $this->assertArrayHasKey('ejercicio_descripcion', $err);
    }

    public function testRepeticionesRequeridas(): void
    {
        $err = $this->ses->validarSerie($this->serieBase(['repeticiones' => '']));
        $this->assertArrayHasKey('repeticiones', $err);
    }

    public function testRepeticionesFueraDeRango(): void
    {
        $this->assertArrayHasKey(
            'repeticiones',
            $this->ses->validarSerie($this->serieBase(['repeticiones' => '150']))
        );
    }

    public function testDistanciaMinima(): void
    {
        $err = $this->ses->validarSerie($this->serieBase(['distancia_m' => '10']));
        $this->assertArrayHasKey('distancia_m', $err);
        $this->assertStringContainsString('25', $err['distancia_m']);
    }

    public function testDistanciaMaxima(): void
    {
        $this->assertArrayHasKey(
            'distancia_m',
            $this->ses->validarSerie($this->serieBase(['distancia_m' => '20000']))
        );
    }

    public function testDescansoMaximo(): void
    {
        $this->assertArrayHasKey(
            'descanso_seg',
            $this->ses->validarSerie($this->serieBase(['descanso_seg' => '601']))
        );
    }

    public function testZonaInvalida(): void
    {
        $this->assertArrayHasKey(
            'zona_intensidad',
            $this->ses->validarSerie($this->serieBase(['zona_intensidad' => 'Z9']))
        );
    }

    public function testRitmoObjetivoMuyLargo(): void
    {
        $this->assertArrayHasKey(
            'ritmo_objetivo',
            $this->ses->validarSerie($this->serieBase(['ritmo_objetivo' => str_repeat('x', 25)]))
        );
    }

    /** @dataProvider zonasValidas */
    public function testZonasValidasPasan(string $zona): void
    {
        $err = $this->ses->validarSerie($this->serieBase(['zona_intensidad' => $zona]));
        $this->assertArrayNotHasKey('zona_intensidad', $err);
    }

    public static function zonasValidas(): array
    {
        return [['Z1'], ['Z2'], ['Z3'], ['Z4'], ['Z5']];
    }

    /** @dataProvider bloquesValidos */
    public function testBloquesValidosPasan(string $bloque): void
    {
        $err = $this->ses->validarSerie($this->serieBase(['bloque' => $bloque]));
        $this->assertArrayNotHasKey('bloque', $err);
    }

    public static function bloquesValidos(): array
    {
        return [['Calentamiento'], ['Principal'], ['VueltaCalma']];
    }
}
<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use GrupoProyecto\SisBiomec\modelo\Sesiones;

class SesionesSettersTest extends TestCase
{
    public function testSetDatosFiltraCamposPermitidos(): void
    {
        $s = new Sesiones();
        $s->setDatos([
            'id_sesion' => '1',
            'id_grupo'  => '2',
            'foo'       => 'bar',
        ]);
        $this->assertSame('1', $s->getCampo('id_sesion'));
        $this->assertSame('2', $s->getCampo('id_grupo'));
        $this->assertNull($s->getCampo('foo'));
    }

    public function testGetCampoInexistenteDevuelveNull(): void
    {
        $this->assertNull((new Sesiones())->getCampo('no_existe'));
    }

    public function testSetSeriesIgnoraNoArrays(): void
    {
        $s = new Sesiones();
        $s->setSeries([
            ['bloque' => 'Principal', 'repeticiones' => 3, 'distancia_m' => 100],
            'no_es_array',
            ['bloque' => 'Calentamiento'],
        ]);

        $ref = new \ReflectionProperty(Sesiones::class, 'series');
        $ref->setAccessible(true);
        $series = $ref->getValue($s);

        $this->assertCount(2, $series);
    }

    public function testSetSeriesFiltraCamposPermitidos(): void
    {
        $s = new Sesiones();
        $s->setSeries([
            ['bloque' => 'Principal', 'campo_extra' => 'x', 'repeticiones' => 3],
        ]);

        $ref = new \ReflectionProperty(Sesiones::class, 'series');
        $ref->setAccessible(true);
        $series = $ref->getValue($s);

        $this->assertArrayNotHasKey('campo_extra', $series[0]);
        $this->assertSame('Principal', $series[0]['bloque']);
    }
}
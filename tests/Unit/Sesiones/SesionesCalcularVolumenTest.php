<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use GrupoProyecto\SisBiomec\modelo\Sesiones;

class SesionesCalcularVolumenTest extends TestCase
{
    private function invocar(string $metodo, array $args): array
    {
        $ref = new \ReflectionMethod(Sesiones::class, $metodo);
        $ref->setAccessible(true);
        return $ref->invoke(new Sesiones(), ...$args);
    }

    public function testVolumenTotal(): void
    {
        $r = $this->invocar('calcularVolumenes', [[
            ['bloque' => 'Calentamiento', 'repeticiones' => 2, 'distancia_m' => 100],
            ['bloque' => 'Principal',     'repeticiones' => 4, 'distancia_m' => 200],
            ['bloque' => 'VueltaCalma',   'repeticiones' => 1, 'distancia_m' => 100],
        ]]);

        $this->assertSame(200,  $r['calentamiento']);
        $this->assertSame(800,  $r['principal']);
        $this->assertSame(100,  $r['vuelta_calma']);
        $this->assertSame(1100, $r['total']);
    }

    public function testSeriesVacias(): void
    {
        $r = $this->invocar('calcularVolumenes', [[]]);
        $this->assertSame(0, $r['total']);
    }

    public function testBloqueDesconocidoNoSuma(): void
    {
        $r = $this->invocar('calcularVolumenes', [[
            ['bloque' => 'Otro', 'repeticiones' => 5, 'distancia_m' => 100],
        ]]);
        $this->assertSame(0, $r['total']);
    }
}
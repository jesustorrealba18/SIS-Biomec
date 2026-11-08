<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use GrupoProyecto\SisBiomec\modelo\CargaBienestar;

class CargaBienestarTest extends TestCase
{
    private function invocarMetodoPrivado($objeto, $nombreMetodo, $argumentos = [])
    {
        $reflexion = new \ReflectionMethod(get_class($objeto), $nombreMetodo);
        $reflexion->setAccessible(true);
        return $reflexion->invokeArgs($objeto, $argumentos);
    }

    public function testRPEExcedidoDebeFallar()
    {
        $modelo = new CargaBienestar();
        $datos = [
            'id_atleta' => 1, 'fecha' => date('Y-m-d'),
            'rpe' => 15, // El máximo es 10
        ];

        $this->invocarMetodoPrivado($modelo, 'setAtributos', [$datos]);
        $esValido = $this->invocarMetodoPrivado($modelo, 'validarAtributosInternos', [false]);
        $errores = $modelo->obtenerErrores(); 

        $this->assertFalse($esValido);
        $this->assertArrayHasKey('rpe', $errores);
        $this->assertStringContainsString('entre 1 y 10', $errores['rpe']);
    }

    public function testFechaAntiguaDebeFallar()
    {
        $modelo = new CargaBienestar();
        // Simulamos fecha de hace 2 meses
        $fechaAntigua = date('Y-m-d', strtotime('-2 months'));
        
        $datos = [
            'id_atleta' => 1, 'fecha' => $fechaAntigua, 'rpe' => 5
        ];

        $this->invocarMetodoPrivado($modelo, 'setAtributos', [$datos]);
        $esValido = $this->invocarMetodoPrivado($modelo, 'validarAtributosInternos', [false]);
        $errores = $modelo->obtenerErrores();

        $this->assertFalse($esValido);
        $this->assertArrayHasKey('fecha', $errores);
        $this->assertStringContainsString('hasta 1 mes de antigüedad', $errores['fecha']);
    }
}
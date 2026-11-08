<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use GrupoProyecto\SisBiomec\modelo\MedicionAntropometrica;

class MedicionAntropometricaTest extends TestCase
{
    // Helper para invocar métodos privados sin conectar a la BD
    private function invocarMetodoPrivado($objeto, $nombreMetodo, $argumentos = [])
    {
        $reflexion = new \ReflectionMethod(get_class($objeto), $nombreMetodo);
        $reflexion->setAccessible(true);
        return $reflexion->invokeArgs($objeto, $argumentos);
    }

    public function testPesoExcedidoDebeFallar()
    {
        $modelo = new MedicionAntropometrica();
        
        $datos = [
            'id_atleta' => 1, 'fecha' => date('Y-m-d'), 
            'peso_kg' => '201', 'talla_cm' => '180', 
            'envergadura_cm' => '180', 'perimetro_abdominal_cm' => '80',
            'responsable' => 'Tester QA'
        ];
        
        // Inyectamos los datos privados y validamos
        $this->invocarMetodoPrivado($modelo, 'setDatos', [$datos]);
        $esValido = $this->invocarMetodoPrivado($modelo, 'validarDatos');
        $errores = $modelo->obtenerErroresValidacion();

        $this->assertFalse($esValido, 'El sistema debió rechazar el peso de 201 kg.');
        $this->assertArrayHasKey('peso_kg', $errores);
        $this->assertStringContainsString('entre 20 kg y 200 kg', $errores['peso_kg']);
    }

    public function testTallaExcedidaDebeFallar()
    {
        $modelo = new MedicionAntropometrica();
        
        $datos = [
            'id_atleta' => 1, 'fecha' => date('Y-m-d'), 
            'peso_kg' => '80', 'talla_cm' => '251', // Talla inválida
            'envergadura_cm' => '180', 'perimetro_abdominal_cm' => '80',
            'responsable' => 'Tester QA'
        ];
        
        $this->invocarMetodoPrivado($modelo, 'setDatos', [$datos]);
        $esValido = $this->invocarMetodoPrivado($modelo, 'validarDatos');
        $errores = $modelo->obtenerErroresValidacion();

        $this->assertFalse($esValido);
        $this->assertArrayHasKey('talla_cm', $errores);
    }

    public function testCalculoIMCDebeSerCorrecto()
    {
        $modelo = new MedicionAntropometrica();
        $datos = [
            'id_atleta' => 1, 'fecha' => date('Y-m-d'), 
            'peso_kg' => '75', 'talla_cm' => '180', // 1.80m
            'envergadura_cm' => '180', 'perimetro_abdominal_cm' => '80',
            'responsable' => 'Tester QA'
        ];
        
        $this->invocarMetodoPrivado($modelo, 'setDatos', [$datos]);
        $this->invocarMetodoPrivado($modelo, 'calcularIMC');
        
        // Extraemos el valor de la propiedad privada 'datos' usando ReflectionProperty
        $reflexion = new \ReflectionProperty(get_class($modelo), 'datos');
        $reflexion->setAccessible(true);
        $datosInternos = $reflexion->getValue($modelo);

        // IMC = 75 / (1.80 * 1.80) = 23.148... redondeado a 1 decimal = 23.1
        $this->assertEquals(23.1, $datosInternos['imc'], 'El cálculo del IMC es incorrecto.');
    }
}
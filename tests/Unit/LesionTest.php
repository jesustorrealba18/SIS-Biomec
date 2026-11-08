<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use GrupoProyecto\SisBiomec\modelo\Lesion;

class LesionTest extends TestCase
{
    private function invocarMetodoPrivado($objeto, $nombreMetodo, $argumentos = [])
    {
        $reflexion = new \ReflectionMethod(get_class($objeto), $nombreMetodo);
        $reflexion->setAccessible(true);
        return $reflexion->invokeArgs($objeto, $argumentos);
    }

    public function testIncoherenciaMolestiaGravedadDebeFallar()
    {
        $modelo = new Lesion();
        $datos = [
            'id_atleta' => 1, 'fecha_inicio' => date('Y-m-d'),
            'zona_anatomica' => 'Hombro', 'tipo' => 'Sobreuso',
            'nivel_molestia' => 9, 
            'gravedad_clinica' => 'Leve', // ¡Incoherencia! (9 debería ser Grave)
            'diagnostico' => 'Dolor agudo en el hombro'
        ];

        $this->invocarMetodoPrivado($modelo, 'setAtributos', [$datos]);
        $esValido = $this->invocarMetodoPrivado($modelo, 'validarAtributosInternos', [false]);
        $errores = $modelo->obtenerErrores();

        $this->assertFalse($esValido, 'El sistema debió rechazar la lesión por incoherencia clínica.');
        $this->assertArrayHasKey('gravedad_clinica', $errores);
        $this->assertStringContainsString('requiere gravedad Grave', $errores['gravedad_clinica']);
    }

    public function testEnfermedadNoPuedeTenerLadoDebeFallar()
    {
        $modelo = new Lesion();
        $datos = [
            'id_atleta' => 1, 'fecha_inicio' => date('Y-m-d'),
            'zona_anatomica' => 'Respiratorio', 'tipo' => 'Enfermedad',
            'nivel_molestia' => 5, 'gravedad_clinica' => 'Moderada',
            'lado' => 'Izquierdo', // ¡Error! Una enfermedad no tiene lado
            'diagnostico' => 'Gripe fuerte'
        ];

        $this->invocarMetodoPrivado($modelo, 'setAtributos', [$datos]);
        $esValido = $this->invocarMetodoPrivado($modelo, 'validarAtributosInternos', [false]);
        $errores = $modelo->obtenerErrores();

        $this->assertFalse($esValido);
        $this->assertArrayHasKey('lado', $errores);
        $this->assertStringContainsString('no tienen lateralidad', $errores['lado']);
    }

    public function testLesionValidaDebePasar()
    {
        $modelo = new Lesion();
        $datos = [
            'id_atleta' => 1, 'fecha_inicio' => date('Y-m-d'),
            'zona_anatomica' => 'Rodilla', 'tipo' => 'Aguda',
            'nivel_molestia' => 4, 
            'gravedad_clinica' => 'Moderada', // Coherente
            'lado' => 'Derecho',
            'diagnostico' => 'Esguince leve'
        ];

        $this->invocarMetodoPrivado($modelo, 'setAtributos', [$datos]);
        $esValido = $this->invocarMetodoPrivado($modelo, 'validarAtributosInternos', [false]);

        $this->assertTrue($esValido, 'Una lesión coherente debería pasar la validación.');
    }
}
<?php

namespace Tests\Backend;

use PHPUnit\Framework\TestCase;
use GrupoProyecto\SisBiomec\modelo\Sesiones;

class SesionesTest extends TestCase
{

    private function crearSesionConDatos(array $overrides = []): Sesiones
    {
        $sesion = new Sesiones();
        $datosBase = [
            'id_entrenador'    => 1,
            'id_grupo'         => 1,
            'fecha'            => date('Y-m-d', strtotime('+1 day')),
            'tipo_sesion'      => 'Tecnica',
            'duracion_minutos' => 60,
        ];
        $sesion->setDatos(array_merge($datosBase, $overrides));
        return $sesion;

        }
    public function testDatosValidosNoGeneranErrores()
    {
        $sesion = $this->crearSesionConDatos();
        $errores = $sesion->validarDatos();
        $this->assertEmpty($errores, 'Los datos base deberían ser válidos: ' . print_r($errores, true));
    }

    public function testNoPermiteFechaPasadaAlRegistrar()
    {
        $sesion = $this->crearSesionConDatos([
            'fecha' => date('Y-m-d', strtotime('-1 day')),
        ]);
        $errores = $sesion->validarDatos();
        $this->assertArrayHasKey('fecha', $errores);
    }

    public function testPermiteFechaPasadaAlEditar()
    {
        $sesion = $this->crearSesionConDatos([
            'fecha' => date('Y-m-d', strtotime('-1 day')),
        ]);
        $errores = $sesion->validarDatos(123);
        $this->assertArrayNotHasKey('fecha', $errores);
    }

    public function testRechazaTipoSesionInvalido()
    {
        $sesion = $this->crearSesionConDatos(['tipo_sesion' => 'Inventado']);
        $errores = $sesion->validarDatos();
        $this->assertArrayHasKey('tipo_sesion', $errores);
    }

    public function testRechazaDuracionMenorA15Minutos()
    {
        $sesion = $this->crearSesionConDatos(['duracion_minutos' => 10]);
        $errores = $sesion->validarDatos();
        $this->assertArrayHasKey('duracion_minutos', $errores);
    }

    public function testRechazaDuracionMayorA480Minutos()
    {
        $sesion = $this->crearSesionConDatos(['duracion_minutos' => 500]);
        $errores = $sesion->validarDatos();
        $this->assertArrayHasKey('duracion_minutos', $errores);
    }

    public function testRechazaIdGrupoNoNumerico()
    {
        $sesion = $this->crearSesionConDatos(['id_grupo' => 'abc']);
        $errores = $sesion->validarDatos();
        $this->assertArrayHasKey('id_grupo', $errores);
    }

    public function testRechazaIdEntrenadorVacio()
    {
        $sesion = $this->crearSesionConDatos(['id_entrenador' => '']);
        $errores = $sesion->validarDatos();
        $this->assertArrayHasKey('id_entrenador', $errores);
    }
    private function serieValida(array $overrides = []): array
    {
        return array_merge([
            'bloque'               => 'Principal',
            'id_drill'             => 1,
            'repeticiones'         => 4,
            'distancia_m'          => 100,
            'descanso_seg'         => 30,
            'zona_intensidad'      => 'Z2',
        ], $overrides);
    }

    public function testSerieValidaNoGeneraErrores()
    {
        $sesion = $this->crearSesionConDatos();
        $errores = $sesion->validarSerie($this->serieValida());
        $this->assertEmpty($errores, 'La serie base debería ser válida: ' . print_r($errores, true));
    }

    public function testSerieRechazaBloqueInvalido()
    {
        $sesion = $this->crearSesionConDatos();
        $errores = $sesion->validarSerie($this->serieValida(['bloque' => 'Otro']));
        $this->assertArrayHasKey('bloque', $errores);
    }

    public function testSerieRechazaZonaInvalida()
    {
        $sesion = $this->crearSesionConDatos();
        $errores = $sesion->validarSerie($this->serieValida(['zona_intensidad' => 'Z9']));
        $this->assertArrayHasKey('zona_intensidad', $errores);
    }

    public function testSerieRechazaDistanciaMenorA25()
    {
        $sesion = $this->crearSesionConDatos();
        $errores = $sesion->validarSerie($this->serieValida(['distancia_m' => 10]));
        $this->assertArrayHasKey('distancia_m', $errores);
    }

    public function testSerieRechazaRepeticionesMayorA100()
    {
        $sesion = $this->crearSesionConDatos();
        $errores = $sesion->validarSerie($this->serieValida(['repeticiones' => 150]));
        $this->assertArrayHasKey('repeticiones', $errores);
    }

    public function testSerieRechazaDescansoMayorA600()
    {
        $sesion = $this->crearSesionConDatos();
        $errores = $sesion->validarSerie($this->serieValida(['descanso_seg' => 700]));
        $this->assertArrayHasKey('descanso_seg', $errores);
    }

    public function testSerieRequiereDrillODescripcion()
    {
        $sesion = $this->crearSesionConDatos();
        $errores = $sesion->validarSerie($this->serieValida([
            'id_drill'              => '',
            'ejercicio_descripcion' => '',
        ]));
        $this->assertArrayHasKey('ejercicio_descripcion', $errores);
    }

    public function testCierreRechazaVolumenEjecutadoMayorAlPlanificado()
    {
        $sesion = $this->crearSesionConDatos(['volumen_ejecutado' => 500]);
        $errores = $sesion->validarCierreSesion(400);
        $this->assertArrayHasKey('volumen_ejecutado', $errores);
    }

    public function testCierreAceptaVolumenEjecutadoValido()
    {
        $sesion = $this->crearSesionConDatos(['volumen_ejecutado' => 300]);
        $errores = $sesion->validarCierreSesion(400);
        $this->assertArrayNotHasKey('volumen_ejecutado', $errores);
    }
}
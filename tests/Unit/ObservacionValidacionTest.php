<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use GrupoProyecto\SisBiomec\modelo\ObservacionTecnica;

/**
 * CP-UNI: pruebas unitarias del motor de validacion del modulo de
 * observaciones tecnicas. Las rutas de fallo retornan false antes de
 * tocar la BD; las validaciones internas se invocan por reflexion.
 */
class ObservacionValidacionTest extends TestCase
{
    private ObservacionTecnica $ot;

    protected function setUp(): void
    {
        $this->ot = new ObservacionTecnica();
    }

    protected function tearDown(): void
    {
        unset($this->ot);
    }

    private function validarInternos(bool $paraActualizacion = false, array $datos = []): void
    {
        if ($datos !== []) {
            $this->ot->setAtributos($datos);
        }
        $metodo = new \ReflectionMethod(ObservacionTecnica::class, 'validarAtributosInternos');
        $metodo->setAccessible(true);
        $metodo->invoke($this->ot, $paraActualizacion);
    }

    private function datosValidos(): array
    {
        return [
            'id_atleta'         => '5',
            'id_aspecto_tecnico' => '2',
            'calificacion'      => '4',
            'id_sesion'         => '10',
            'observacion_texto' => 'Recuperacion de agua correcta pero virajes lentos',
        ];
    }

    // CP-UNI-58: observacion completa valida no genera errores
    public function testObservacionCompletaValidaNoGeneraErrores(): void
    {
        $this->validarInternos(false, $this->datosValidos());
        $this->assertSame([], $this->ot->obtenerErrores());
    }

    // CP-UNI-59: sin datos se exigen los tres campos obligatorios
    public function testRegistroVacioExigeCamposObligatorios(): void
    {
        $this->validarInternos(false, []);
        $errores = $this->ot->obtenerErrores();
        $this->assertArrayHasKey('id_atleta', $errores);
        $this->assertArrayHasKey('id_aspecto_tecnico', $errores);
        $this->assertArrayHasKey('calificacion', $errores);
        $this->assertArrayNotHasKey('id_sesion', $errores, 'id_sesion es opcional.');
        $this->assertArrayNotHasKey('observacion_texto', $errores, 'observacion_texto es opcional.');
    }

    // CP-UNI-60: id_atleta no numerico se rechaza
    public function testIdAtletaNoNumericoSeRechaza(): void
    {
        $datos = $this->datosValidos();
        $datos['id_atleta'] = 'xyz';
        $this->validarInternos(false, $datos);
        $this->assertArrayHasKey('id_atleta', $this->ot->obtenerErrores());
    }

    // CP-UNI-61: id_aspecto_tecnico no numerico se rechaza
    public function testAspectoNoNumericoSeRechaza(): void
    {
        $datos = $this->datosValidos();
        $datos['id_aspecto_tecnico'] = 'patada';
        $this->validarInternos(false, $datos);
        $this->assertArrayHasKey('id_aspecto_tecnico', $this->ot->obtenerErrores());
    }

    // CP-UNI-62: calificacion fuera del rango 1-5 se rechaza
    public function testCalificacionFueraDeRangoSeRechaza(): void
    {
        $datos = $this->datosValidos();
        $datos['calificacion'] = '6';
        $this->validarInternos(false, $datos);
        $this->assertArrayHasKey('calificacion', $this->ot->obtenerErrores());
    }

    // CP-UNI-62b: comportamiento actual documentado — la calificacion 0 se
    // acepta porque el codigo usa !empty() (falsy en PHP). Hallazgo OF-2.
    public function testCalificacionCeroSeAceptaComportamientoActual(): void
    {
        $datos = $this->datosValidos();
        $datos['calificacion'] = '0';
        $this->validarInternos(false, $datos);
        $this->assertSame([], $this->ot->obtenerErrores(), 'Hallazgo OF-2: calificacion 0 pasa la validacion actual.');
    }

    // CP-UNI-63: las calificaciones 1 a 5 se aceptan
    public function testCalificacionesValidasSeAceptan(): void
    {
        foreach (['1', '2', '3', '4', '5'] as $calificacion) {
            $datos = $this->datosValidos();
            $datos['calificacion'] = $calificacion;
            $this->validarInternos(false, $datos);
            $this->assertSame([], $this->ot->obtenerErrores(), "La calificacion {$calificacion} deberia ser valida.");
        }
    }

    // CP-UNI-64: texto que excede 500 caracteres se rechaza
    public function testTextoExcesivoSeRechaza(): void
    {
        $datos = $this->datosValidos();
        $datos['observacion_texto'] = str_repeat('b', 501);
        $this->validarInternos(false, $datos);
        $this->assertArrayHasKey('observacion_texto', $this->ot->obtenerErrores());
    }

    // CP-UNI-65: id_sesion no numerico se rechaza
    public function testIdSesionNoNumericoSeRechaza(): void
    {
        $datos = $this->datosValidos();
        $datos['id_sesion'] = 'sesion-1';
        $this->validarInternos(false, $datos);
        $this->assertArrayHasKey('id_sesion', $this->ot->obtenerErrores());
    }

    // CP-UNI-66: registrar sin datos retorna false sin tocar la BD
    public function testRegistrarSinDatosRetornaFalse(): void
    {
        $this->ot->setAtributos([]);
        $this->assertFalse($this->ot->getRegistrarObservacion());
        $this->assertArrayHasKey('id_atleta', $this->ot->obtenerErrores());
    }

    // CP-UNI-67: actualizar sin identificador se rechaza
    public function testActualizarSinIdSeRechaza(): void
    {
        $this->ot->setAtributos(['calificacion' => '3']);
        $this->assertFalse($this->ot->getActualizarObservacion());
        $this->assertArrayHasKey('id_observacion', $this->ot->obtenerErrores());
    }

    // CP-UNI-68: eliminar con identificador invalido se rechaza
    public function testEliminarConIdInvalidoSeRechaza(): void
    {
        $this->assertFalse($this->ot->eliminarObservacion(0));
        $this->assertArrayHasKey('id_observacion', $this->ot->obtenerErrores());
    }

    // CP-UNI-69: en actualizacion se permiten omitir los campos (solo se validan si vienen)
    public function testActualizacionPermiteCamposOmitidos(): void
    {
        $this->validarInternos(true, ['calificacion' => '2']);
        $this->assertSame([], $this->ot->obtenerErrores());
    }
}

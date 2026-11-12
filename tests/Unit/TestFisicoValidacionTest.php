<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use GrupoProyecto\SisBiomec\modelo\TestFisico;

/**
 * CP-UNI: pruebas unitarias del motor de validacion del modulo de test fisico.
 * Las validaciones fallidas se verifican ANTES de tocar la BD (rutas publicas
 * que retornan false temprano) y por reflexion sobre validarAtributosInternos.
 */
class TestFisicoValidacionTest extends TestCase
{
    private TestFisico $tf;

    protected function setUp(): void
    {
        $this->tf = new TestFisico();
    }

    protected function tearDown(): void
    {
        unset($this->tf);
    }

    private function validarInternos(bool $paraActualizacion = false, array $datos = []): void
    {
        if ($datos !== []) {
            $this->tf->setAtributos($datos);
        }
        $metodo = new \ReflectionMethod(TestFisico::class, 'validarAtributosInternos');
        $metodo->setAccessible(true);
        $metodo->invoke($this->tf, $paraActualizacion);
    }

    private function datosRegistroValidos(): array
    {
        return [
            'id_atleta'    => '5',
            'fecha'        => '2026-01-15',
            'observaciones' => 'Rendimiento dentro del rango esperado',
            'estado'       => 'Completo',
        ];
    }

    private function datosTipoValido(): array
    {
        return [
            'nombre'       => 'Test de Salto TESTPRUEBA',
            'descripcion'  => 'Descripcion de prueba',
            'tipo_medicion' => 'Distancia',
            'unidad_medida' => 'cm',
            'valor_referencia_min' => '10',
            'valor_referencia_max' => '300',
        ];
    }

    private function variablesValidas(): array
    {
        return [
            ['nombre_variable' => 'Salto izquierdo', 'unidad' => 'cm'],
            ['nombre_variable' => 'Salto derecho', 'unidad' => 'cm'],
        ];
    }

    // CP-UNI-40: registro completo valido no genera errores
    public function testRegistroCompletoValidoNoGeneraErrores(): void
    {
        $this->validarInternos(false, $this->datosRegistroValidos());
        $this->assertSame([], $this->tf->obtenerErrores());
    }

    // CP-UNI-41: sin datos solo se exige id_atleta (fecha/estado/observaciones opcionales)
    public function testRegistroVacioSoloExigeIdAtleta(): void
    {
        $this->validarInternos(false, []);
        $errores = $this->tf->obtenerErrores();
        $this->assertArrayHasKey('id_atleta', $errores);
        $this->assertArrayNotHasKey('fecha', $errores, 'fecha es opcional en la validacion.');
        $this->assertArrayNotHasKey('estado', $errores, 'estado es opcional en la validacion.');
    }

    // CP-UNI-42: id_atleta no numerico se rechaza
    public function testIdAtletaNoNumericoSeRechaza(): void
    {
        $datos = $this->datosRegistroValidos();
        $datos['id_atleta'] = 'abc';
        $this->validarInternos(false, $datos);
        $this->assertArrayHasKey('id_atleta', $this->tf->obtenerErrores());
    }

    // CP-UNI-43: fecha con formato invalido se rechaza
    public function testFechaInvalidaSeRechaza(): void
    {
        $datos = $this->datosRegistroValidos();
        $datos['fecha'] = '15/01/2026';
        $this->validarInternos(false, $datos);
        $this->assertArrayHasKey('fecha', $this->tf->obtenerErrores());
    }

    // CP-UNI-44: observaciones que exceden 500 caracteres se rechazan
    public function testObservacionesExcesivasSeRechazan(): void
    {
        $datos = $this->datosRegistroValidos();
        $datos['observaciones'] = str_repeat('a', 501);
        $this->validarInternos(false, $datos);
        $this->assertArrayHasKey('observaciones', $this->tf->obtenerErrores());
    }

    // CP-UNI-45: estado fuera del catalogo se rechaza
    public function testEstadoFueraDeEnumSeRechaza(): void
    {
        $datos = $this->datosRegistroValidos();
        $datos['estado'] = 'Aplazado';
        $this->validarInternos(false, $datos);
        $this->assertArrayHasKey('estado', $this->tf->obtenerErrores());
    }

    // CP-UNI-46: los tres estados validos del catalogo se aceptan
    public function testEstadosValidosSeAceptan(): void
    {
        foreach (['Completo', 'Parcial', 'Cancelado'] as $estado) {
            $datos = $this->datosRegistroValidos();
            $datos['estado'] = $estado;
            $this->validarInternos(false, $datos);
            $this->assertSame([], $this->tf->obtenerErrores(), "El estado {$estado} deberia ser valido.");
        }
    }

    // CP-UNI-47: registrar sin tipo de test (predefinido ni personalizado) se rechaza sin tocar BD
    public function testRegistrarSinTipoTestSeRechaza(): void
    {
        $this->tf->setAtributos(['id_atleta' => '5', 'valores' => ['1' => '10']]);
        $this->assertFalse($this->tf->getRegistrarTest());
        $this->assertArrayHasKey('tipo_test', $this->tf->obtenerErrores());
    }

    // CP-UNI-48: registrar con tipo valido pero sin valores se rechaza sin tocar BD
    public function testRegistrarSinValoresSeRechaza(): void
    {
        $this->tf->setAtributos(['id_atleta' => '5', 'id_tipo_test' => '3', 'valores' => []]);
        $this->assertFalse($this->tf->getRegistrarTest());
        $this->assertArrayHasKey('valores', $this->tf->obtenerErrores());
    }

    // CP-UNI-49: valores que no son un arreglo se rechazan sin tocar BD
    public function testRegistrarValoresNoArraySeRechaza(): void
    {
        $this->tf->setAtributos(['id_atleta' => '5', 'id_tipo_test' => '3', 'valores' => 'texto']);
        $this->assertFalse($this->tf->getRegistrarTest());
        $this->assertArrayHasKey('valores', $this->tf->obtenerErrores());
    }

    // CP-UNI-50: actualizar sin identificador de registro se rechaza
    public function testActualizarSinIdSeRechaza(): void
    {
        $this->tf->setAtributos(['id_atleta' => '5', 'estado' => 'Completo']);
        $this->assertFalse($this->tf->getActualizarTest());
        $this->assertArrayHasKey('id_registro_test', $this->tf->obtenerErrores());
    }

    // CP-UNI-51: en actualizacion se permite omitir id_atleta (solo se valida si viene)
    public function testActualizacionPermiteOmitirIdAtleta(): void
    {
        $this->validarInternos(true, ['estado' => 'Parcial']);
        $this->assertSame([], $this->tf->obtenerErrores());
    }

    // CP-UNI-52: eliminar con identificador invalido se rechaza
    public function testEliminarConIdInvalidoSeRechaza(): void
    {
        $this->assertFalse($this->tf->eliminarTest(0));
        $this->assertArrayHasKey('id_registro_test', $this->tf->obtenerErrores());
    }

    // CP-UNI-53: crear tipo predefinido sin nombre se rechaza sin tocar BD
    public function testCrearTipoSinNombreSeRechaza(): void
    {
        $datos = $this->datosTipoValido();
        unset($datos['nombre']);
        $this->assertFalse($this->tf->crearTipoPredefinido($datos, $this->variablesValidas()));
        $this->assertArrayHasKey('nombre', $this->tf->obtenerErrores());
    }

    // CP-UNI-54: crear tipo predefinido con nombre demasiado corto se rechaza
    public function testCrearTipoNombreCortoSeRechaza(): void
    {
        $datos = $this->datosTipoValido();
        $datos['nombre'] = 'A';
        $this->assertFalse($this->tf->crearTipoPredefinido($datos, $this->variablesValidas()));
        $this->assertArrayHasKey('nombre', $this->tf->obtenerErrores());
    }

    // CP-UNI-55: crear tipo predefinido sin variables se rechaza
    public function testCrearTipoSinVariablesSeRechaza(): void
    {
        $this->assertFalse($this->tf->crearTipoPredefinido($this->datosTipoValido(), []));
        $this->assertArrayHasKey('variables', $this->tf->obtenerErrores());
    }

    // CP-UNI-56: variable sin nombre dentro del tipo se rechaza
    public function testCrearTipoConVariableSinNombreSeRechaza(): void
    {
        $variables = [['nombre_variable' => '', 'unidad' => 'cm']];
        $this->assertFalse($this->tf->crearTipoPredefinido($this->datosTipoValido(), $variables));
        $this->assertArrayHasKey('variable_0', $this->tf->obtenerErrores());
    }

    // CP-UNI-57: valor de referencia no decimal se rechaza
    public function testCrearTipoReferenciaNoDecimalSeRechaza(): void
    {
        $datos = $this->datosTipoValido();
        $datos['valor_referencia_min'] = 'diez';
        $this->assertFalse($this->tf->crearTipoPredefinido($datos, $this->variablesValidas()));
        $this->assertArrayHasKey('valor_referencia_min', $this->tf->obtenerErrores());
    }
}

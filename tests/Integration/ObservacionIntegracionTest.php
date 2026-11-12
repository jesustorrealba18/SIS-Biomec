<?php

namespace Tests\Integration;

use GrupoProyecto\SisBiomec\modelo\ObservacionTecnica;

/**
 * CP-INT: pruebas de integracion del modulo de observaciones tecnicas contra
 * la BD real (sis_natacion). Cubre registro, actualizacion, borrado fisico,
 * listados, historial por aspecto y resumen de calificaciones.
 *
 * Todo dato de prueba usa el prefijo TESTPRUEBA y se limpia en tearDown.
 */
class ObservacionIntegracionTest extends IntegrationTestCase
{
    private ObservacionTecnica $ot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ot = new ObservacionTecnica();
    }

    protected function tearDown(): void
    {
        $this->limpiarObservaciones();
        parent::tearDown();
        unset($this->ot);
    }

    private function limpiarObservaciones(): void
    {
        try {
            $idsAspectos = $this->columna($this->pdoNatacion, "SELECT id_aspecto FROM aspectos_tecnicos WHERE nombre LIKE 'TESTPRUEBA%'");
            $idsAtletas = $this->columna($this->pdoNatacion, "SELECT id_atleta FROM atletas WHERE correo LIKE 'TESTPRUEBA%'");

            $this->borrarPorLista($this->pdoNatacion, 'observaciones_tecnicas', 'id_aspecto_tecnico', $idsAspectos);
            $this->borrarPorLista($this->pdoNatacion, 'observaciones_tecnicas', 'id_atleta', $idsAtletas);
            $this->borrarPorLista($this->pdoNatacion, 'aspectos_tecnicos', 'id_aspecto', $idsAspectos);
        } catch (\Throwable $e) {
            fwrite(STDERR, 'AVISO (limpieza observaciones): ' . $e->getMessage() . PHP_EOL);
        }
    }

    private function crearAspectoPrueba(): int
    {
        $nombre = 'TESTPRUEBA Aspecto ' . uniqid();
        $sentencia = $this->pdoNatacion->prepare('INSERT INTO aspectos_tecnicos (nombre, descripcion) VALUES (?, ?)');
        $sentencia->execute([$nombre, 'Aspecto tecnico de prueba']);
        return (int)$this->pdoNatacion->lastInsertId();
    }

    private function datosObservacion(int $idAtleta, int $idAspecto, string $calificacion = '4', string $texto = 'Tecnica de brazada correcta'): array
    {
        return [
            'id_atleta'         => (string)$idAtleta,
            'id_aspecto_tecnico' => (string)$idAspecto,
            'calificacion'      => $calificacion,
            'observacion_texto' => $texto,
        ];
    }

    /** Registra una observacion completa via el modelo y devuelve su id. */
    private function crearObservacionPrueba(int $idAtleta, int $idAspecto, string $calificacion = '4'): int
    {
        $idUsuario = $this->crearUsuarioPrueba();
        $datos = array_merge($this->datosObservacion($idAtleta, $idAspecto, $calificacion), ['id_usuario' => (string)$idUsuario]);
        $this->ot->setAtributos($datos);
        $this->assertTrue($this->ot->getRegistrarObservacion(), 'Errores: ' . json_encode($this->ot->obtenerErrores()));

        return (int)$this->pdoNatacion->query(
            "SELECT id_observacion FROM observaciones_tecnicas WHERE id_atleta = {$idAtleta} AND id_aspecto_tecnico = {$idAspecto} ORDER BY id_observacion DESC LIMIT 1"
        )->fetchColumn();
    }

    // CP-INT-40: registrar observacion completa la persiste con su fecha
    public function testRegistrarPersisteFila(): void
    {
        $idAtleta = $this->crearAtletaPrueba();
        $idAspecto = $this->crearAspectoPrueba();
        $idUsuario = $this->crearUsuarioPrueba();

        $datos = array_merge($this->datosObservacion($idAtleta, $idAspecto, '4', 'Salida de muro explosiva'), ['id_usuario' => (string)$idUsuario]);
        $this->ot->setAtributos($datos);
        $this->assertTrue($this->ot->getRegistrarObservacion(), 'Errores: ' . json_encode($this->ot->obtenerErrores()));

        $fila = $this->pdoNatacion->query(
            "SELECT calificacion, observacion_texto, fecha_registro FROM observaciones_tecnicas WHERE id_atleta = {$idAtleta} AND id_aspecto_tecnico = {$idAspecto}"
        )->fetch(\PDO::FETCH_ASSOC);
        $this->assertNotFalse($fila, 'La observacion debe existir en la BD.');
        $this->assertSame('4', (string)$fila['calificacion']);
        $this->assertSame('Salida de muro explosiva', $fila['observacion_texto']);
        $this->assertNotEmpty($fila['fecha_registro']);
    }

    // CP-INT-41: sin id_usuario falla a nivel BD — hallazgo OF-1 (columna NOT NULL sin validacion del modelo)
    public function testRegistrarSinIdUsuarioFallaErrorBD(): void
    {
        $idAtleta = $this->crearAtletaPrueba();
        $idAspecto = $this->crearAspectoPrueba();

        $this->ot->setAtributos($this->datosObservacion($idAtleta, $idAspecto));
        $this->assertFalse($this->ot->getRegistrarObservacion(), 'Hallazgo OF-1: sin id_usuario debe fallar porque la columna es NOT NULL.');
        $this->assertArrayHasKey('bd', $this->ot->obtenerErrores(), 'El modelo reporta error BD generico en lugar de una validacion de campo.');

        $total = (int)$this->pdoNatacion->query(
            "SELECT COUNT(*) FROM observaciones_tecnicas WHERE id_atleta = {$idAtleta}"
        )->fetchColumn();
        $this->assertSame(0, $total, 'No debe persistirse la observacion.');
    }

    // CP-INT-42: calificacion fuera del catalogo no persiste
    public function testRegistrarCalificacionInvalidaSeRechaza(): void
    {
        $idAtleta = $this->crearAtletaPrueba();
        $idAspecto = $this->crearAspectoPrueba();
        $idUsuario = $this->crearUsuarioPrueba();

        $datos = array_merge($this->datosObservacion($idAtleta, $idAspecto, '9'), ['id_usuario' => (string)$idUsuario]);
        $this->ot->setAtributos($datos);
        $this->assertFalse($this->ot->getRegistrarObservacion());
        $this->assertArrayHasKey('calificacion', $this->ot->obtenerErrores());

        $total = (int)$this->pdoNatacion->query(
            "SELECT COUNT(*) FROM observaciones_tecnicas WHERE id_atleta = {$idAtleta}"
        )->fetchColumn();
        $this->assertSame(0, $total);
    }

    // CP-INT-43: actualizar cambia calificacion y texto
    public function testActualizarCambiaCalificacionYTexto(): void
    {
        $idAtleta = $this->crearAtletaPrueba();
        $idAspecto = $this->crearAspectoPrueba();
        $idObservacion = $this->crearObservacionPrueba($idAtleta, $idAspecto, '3');

        $idUsuario = (int)$this->crearUsuarioPrueba();
        $datos = array_merge(
            $this->datosObservacion($idAtleta, $idAspecto, '5', 'Mejora notable tras correccion'),
            ['id_observacion' => (string)$idObservacion]
        );
        $this->ot->setAtributos($datos);
        $this->assertTrue($this->ot->getActualizarObservacion(), 'Errores: ' . json_encode($this->ot->obtenerErrores()));

        $fila = $this->pdoNatacion->query(
            "SELECT calificacion, observacion_texto FROM observaciones_tecnicas WHERE id_observacion = {$idObservacion}"
        )->fetch(\PDO::FETCH_ASSOC);
        $this->assertSame('5', (string)$fila['calificacion']);
        $this->assertSame('Mejora notable tras correccion', $fila['observacion_texto']);
    }

    // CP-INT-44: actualizar con identificador inexistente retorna false
    public function testActualizarIdInexistenteFalla(): void
    {
        $idAtleta = $this->crearAtletaPrueba();
        $idAspecto = $this->crearAspectoPrueba();

        $datos = array_merge($this->datosObservacion($idAtleta, $idAspecto, '4'), ['id_observacion' => '999999999']);
        $this->ot->setAtributos($datos);
        $this->assertFalse($this->ot->getActualizarObservacion());
        $this->assertArrayHasKey('actualizacion', $this->ot->obtenerErrores());
    }

    // CP-INT-45: eliminar es fisico y no idempotente
    public function testEliminarEsFisicoYNoIdempotente(): void
    {
        $idAtleta = $this->crearAtletaPrueba();
        $idAspecto = $this->crearAspectoPrueba();
        $idObservacion = $this->crearObservacionPrueba($idAtleta, $idAspecto);

        $this->assertTrue($this->ot->eliminarObservacion($idObservacion));
        $this->assertFalse($this->ot->eliminarObservacion($idObservacion), 'El segundo borrado debe fallar (DELETE fisico).');

        $total = (int)$this->pdoNatacion->query(
            "SELECT COUNT(*) FROM observaciones_tecnicas WHERE id_observacion = {$idObservacion}"
        )->fetchColumn();
        $this->assertSame(0, $total);
    }

    // CP-INT-46: listar con filtro por atleta trae el nombre del aspecto
    public function testListarFiltroPorAtleta(): void
    {
        $idAtleta = $this->crearAtletaPrueba();
        $idAspecto = $this->crearAspectoPrueba();
        $this->crearObservacionPrueba($idAtleta, $idAspecto, '2');

        $listado = $this->ot->listarObservaciones($idAtleta);
        $this->assertCount(1, $listado);
        $this->assertSame('2', (string)$listado[0]['calificacion']);
        $this->assertStringContainsString('TESTPRUEBA', $listado[0]['nombre_atleta']);
        $this->assertStringContainsString('TESTPRUEBA', $listado[0]['nombre_aspecto']);
    }

    // CP-INT-47: el detalle trae el historial completo del aspecto en orden ascendente
    public function testDetalleConHistorialPorAspecto(): void
    {
        $idAtleta = $this->crearAtletaPrueba();
        $idAspecto = $this->crearAspectoPrueba();
        $this->crearObservacionPrueba($idAtleta, $idAspecto, '3');
        $this->crearObservacionPrueba($idAtleta, $idAspecto, '5');

        $idUltima = (int)$this->pdoNatacion->query(
            "SELECT id_observacion FROM observaciones_tecnicas WHERE id_atleta = {$idAtleta} ORDER BY id_observacion DESC LIMIT 1"
        )->fetchColumn();

        $detalle = $this->ot->obtenerDetallePorId($idUltima);
        $this->assertNotNull($detalle);
        $this->assertStringContainsString('TESTPRUEBA', $detalle['nombre_aspecto']);
        $this->assertCount(2, $detalle['historial_aspecto'], 'El historial debe incluir ambas evaluaciones.');
        $this->assertSame('3', (string)$detalle['historial_aspecto'][0]['calificacion'], 'Orden ascendente por fecha de registro.');
        $this->assertSame('5', (string)$detalle['historial_aspecto'][1]['calificacion']);
    }

    // CP-INT-48: el resumen por aspecto calcula promedio, maximo y minimo
    public function testResumenPorAspectoPromedioMaxMin(): void
    {
        $idAtleta = $this->crearAtletaPrueba();
        $idAspecto = $this->crearAspectoPrueba();
        $this->crearObservacionPrueba($idAtleta, $idAspecto, '3');
        $this->crearObservacionPrueba($idAtleta, $idAspecto, '5');

        $resumen = $this->ot->resumenPorAspecto($idAtleta);
        $filaAspecto = null;
        foreach ($resumen as $fila) {
            if ((string)$fila['id_aspecto'] === (string)$idAspecto) {
                $filaAspecto = $fila;
                break;
            }
        }
        $this->assertNotNull($filaAspecto, 'El resumen debe incluir el aspecto de prueba.');
        $this->assertSame('2', (string)$filaAspecto['total_evaluaciones']);
        $this->assertSame(4.0, (float)$filaAspecto['promedio']);
        $this->assertSame('5', (string)$filaAspecto['maximo']);
        $this->assertSame('3', (string)$filaAspecto['minimo']);
    }
}

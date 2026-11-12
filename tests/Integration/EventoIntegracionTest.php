<?php

namespace Tests\Integration;

use GrupoProyecto\SisBiomec\modelo\Evento;

/**
 * CP-INT: pruebas de integracion del modulo de eventos contra la BD real
 * (sis_natacion): registro, edicion, maquina de estados, inscripciones
 * idempotentes y deteccion de conflictos de fechas.
 */
class EventoIntegracionTest extends IntegrationTestCase
{
    private function datosEvento(string $nombre): array
    {
        return [
            'nombre'       => $nombre,
            'fecha_inicio' => date('Y-m-d', strtotime('+7 days')),
            'fecha_fin'    => date('Y-m-d', strtotime('+9 days')),
            'sede'         => 'Piscina de pruebas',
            'tipo'         => 'Regional',
            'nivel'        => 'B',
            'organizador'  => 'FEVEDA',
            'estado'       => 'Planificado',
        ];
    }

    private function registrarEventoDePrueba(?string $nombre = null): int
    {
        $nombre = $nombre ?? $this->nombreEventoPrueba();
        $evento = new Evento();
        $this->assertTrue($evento->registrarEvento($this->datosEvento($nombre)), 'registrarEvento debe retornar true.');
        return $this->buscarIdEvento($nombre);
    }

    private function buscarIdEvento(string $nombre): int
    {
        $sentencia = $this->pdoNatacion->prepare('SELECT id_evento FROM eventos WHERE nombre = ?');
        $sentencia->execute([$nombre]);
        return (int)$sentencia->fetchColumn();
    }

    // CP-INT-13: registrar persiste y el detalle es recuperable
    public function testRegistrarEventoYObtenerDetalle(): void
    {
        $nombre = $this->nombreEventoPrueba();
        $id = $this->registrarEventoDePrueba($nombre);
        $this->assertGreaterThan(0, $id, 'El evento debe quedar registrado en la BD.');

        $detalle = (new Evento())->obtenerDetallePorId($id);
        $this->assertIsArray($detalle);
        $this->assertSame($nombre, $detalle['nombre']);
        $this->assertArrayHasKey('tiempos_corte', $detalle);
        $this->assertArrayHasKey('metas', $detalle);
        $this->assertArrayHasKey('inscripciones', $detalle);
    }

    // CP-INT-14: editar modifica los campos del evento
    public function testEditarEventoModificaCampos(): void
    {
        $id = $this->registrarEventoDePrueba();

        $evento = new Evento();
        $nombreNuevo = $this->nombreEventoPrueba();
        $datos = $this->datosEvento($nombreNuevo);
        $datos['id_evento'] = $id;
        $datos['tipo'] = 'Nacional';

        $this->assertTrue($evento->editarEvento($datos), 'editarEvento debe retornar true.');

        $fila = $this->pdoNatacion->query("SELECT nombre, tipo FROM eventos WHERE id_evento = {$id}")->fetch(\PDO::FETCH_ASSOC);
        $this->assertSame($nombreNuevo, $fila['nombre']);
        $this->assertSame('Nacional', $fila['tipo']);
    }

    // CP-INT-15: la maquina de estados solo permite transiciones validas
    public function testMaquinaDeEstadosAplicaSoloTransicionesValidas(): void
    {
        $id = $this->registrarEventoDePrueba();
        $evento = new Evento();

        $this->assertFalse($evento->actualizarEstadoEvento($id, 'En Progreso'), 'Planificado -> En Progreso es invalido.');
        $this->assertTrue($evento->actualizarEstadoEvento($id, 'Cancelado'), 'Planificado -> Cancelado es valido.');
        $this->assertFalse($evento->actualizarEstadoEvento($id, 'En Progreso'), 'Cancelado -> En Progreso es invalido.');
        $this->assertTrue($evento->actualizarEstadoEvento($id, 'Planificado'), 'Cancelado -> Planificado es valido.');
        $this->assertFalse($evento->actualizarEstadoEvento($id, 'Finalizado'), 'Planificado -> Finalizado es invalido.');
        $this->assertTrue($evento->actualizarEstadoEvento($id, 'Inscrito'), 'Planificado -> Inscrito es valido.');
        $this->assertFalse($evento->actualizarEstadoEvento($id, 'Finalizado'), 'Inscrito -> Finalizado es invalido.');
        $this->assertTrue($evento->actualizarEstadoEvento($id, 'En Progreso'), 'Inscrito -> En Progreso es valido.');
        $this->assertFalse($evento->actualizarEstadoEvento($id, 'Planificado'), 'En Progreso -> Planificado es invalido.');
        $this->assertTrue($evento->actualizarEstadoEvento($id, 'Finalizado'), 'En Progreso -> Finalizado es valido.');
        $this->assertFalse($evento->actualizarEstadoEvento($id, 'Cancelado'), 'Finalizado es un estado terminal.');
    }

    // CP-INT-16: inscribir es idempotente y la inscripcion se elimina
    public function testInscripcionEsIdempotenteYSeElimina(): void
    {
        $idAtleta = $this->crearAtletaPrueba();
        $idEvento = $this->registrarEventoDePrueba();
        $evento = new Evento();

        $this->assertTrue($evento->inscribirAtletasLote($idEvento, [$idAtleta]), 'La primera inscripcion debe exitir.');
        $this->assertTrue($evento->inscribirAtletasLote($idEvento, [$idAtleta]), 'La segunda inscripcion (duplicada) no debe fallar.');

        $total = (int)$this->pdoNatacion->query(
            "SELECT COUNT(*) FROM evento_inscripcion WHERE id_evento = {$idEvento} AND id_atleta = {$idAtleta}"
        )->fetchColumn();
        $this->assertSame(1, $total, 'La inscripcion duplicada debe ignorarse (INSERT IGNORE).');

        $inscripciones = $evento->listarInscripciones($idEvento);
        $this->assertCount(1, $inscripciones);

        $this->assertTrue($evento->eliminarInscripcion($idEvento, $idAtleta));
        $total = (int)$this->pdoNatacion->query(
            "SELECT COUNT(*) FROM evento_inscripcion WHERE id_evento = {$idEvento} AND id_atleta = {$idAtleta}"
        )->fetchColumn();
        $this->assertSame(0, $total, 'La inscripcion debe eliminarse.');
    }

    // CP-INT-17: se detectan conflictos de fechas entre eventos
    public function testDetectaConflictosDeInscripcion(): void
    {
        $idAtleta = $this->crearAtletaPrueba();

        // Evento A: de +5 a +6 dias. Evento B: de +4 a +8 dias (solapa con A).
        $nombreA = $this->nombreEventoPrueba();
        $datosA = $this->datosEvento($nombreA);
        $datosA['fecha_inicio'] = date('Y-m-d', strtotime('+5 days'));
        $datosA['fecha_fin'] = date('Y-m-d', strtotime('+6 days'));
        (new Evento())->registrarEvento($datosA);
        $idEventoA = $this->buscarIdEvento($nombreA);

        $nombreB = $this->nombreEventoPrueba();
        $datosB = $this->datosEvento($nombreB);
        $datosB['fecha_inicio'] = date('Y-m-d', strtotime('+4 days'));
        $datosB['fecha_fin'] = date('Y-m-d', strtotime('+8 days'));
        (new Evento())->registrarEvento($datosB);
        $idEventoB = $this->buscarIdEvento($nombreB);

        $evento = new Evento();
        $evento->inscribirAtletasLote($idEventoA, [$idAtleta]);

        $conflictos = $evento->obtenerConflictosInscripcion($idEventoB, [$idAtleta]);
        $this->assertArrayHasKey($idAtleta, $conflictos, 'Debe detectarse el solapamiento de fechas con el evento A.');
    }
}

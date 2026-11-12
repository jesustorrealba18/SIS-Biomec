<?php

namespace Tests\Integration;

use GrupoProyecto\SisBiomec\modelo\Notificacion;

/**
 * CP-INT: pruebas de integracion del modulo de notificaciones contra la BD
 * real (sis_seguridad). Verifica envio, listado, conteo de no leidas y la
 * regla de seguridad: solo el duenio puede marcar su notificacion como leida.
 */
class NotificacionIntegracionTest extends IntegrationTestCase
{
    // CP-INT-18: enviar persiste y listarPorUsuario la devuelve
    public function testEnviarPersisteYListarDevuelveLaNotificacion(): void
    {
        $idUsuario = $this->crearUsuarioPrueba();

        $this->assertTrue(
            Notificacion::enviar($idUsuario, 'TESTPRUEBA Titulo', 'Mensaje de prueba', 'fa-exclamation', 'red', '?p=inicio'),
            'enviar() debe retornar true.'
        );

        $listado = Notificacion::listarPorUsuario($idUsuario, 10);
        $this->assertCount(1, $listado);
        $this->assertSame('TESTPRUEBA Titulo', $listado[0]['titulo']);
        $this->assertSame('fa-exclamation', $listado[0]['icono']);
        $this->assertSame('red', $listado[0]['color']);
        $this->assertSame('?p=inicio', $listado[0]['enlace_url']);
        $this->assertSame(0, (int)$listado[0]['leida'], 'La notificacion nace sin leer.');
    }

    // CP-INT-19: contarNoLeidas refleja el estado y marcar es idempotente
    public function testContarNoLeidasYMarcarLeidaEsIdempotente(): void
    {
        $idUsuario = $this->crearUsuarioPrueba();
        Notificacion::enviar($idUsuario, 'TESTPRUEBA N1', 'Mensaje 1');
        Notificacion::enviar($idUsuario, 'TESTPRUEBA N2', 'Mensaje 2');

        $this->assertSame(2, Notificacion::contarNoLeidas($idUsuario));

        $idNotificacion = (int)Notificacion::listarPorUsuario($idUsuario, 1)[0]['id_notificacion'];
        $notificacion = new Notificacion();
        $notificacion->setDatos(['id_notificacion' => $idNotificacion, 'id_usuario' => $idUsuario]);

        $this->assertTrue($notificacion->marcarcomoLeida(), 'El duenio puede marcarla como leida.');
        $this->assertSame(1, Notificacion::contarNoLeidas($idUsuario));
        $this->assertTrue($notificacion->marcarcomoLeida(), 'Marcar de nuevo es idempotente.');
        $this->assertSame(1, Notificacion::contarNoLeidas($idUsuario));
    }

    // CP-INT-20: otro usuario NO puede marcar la notificacion ajena
    public function testOtroUsuarioNoPuedeMarcarNotificacionAjena(): void
    {
        $idDuenio = $this->crearUsuarioPrueba();
        $idIntruso = $this->crearUsuarioPrueba();

        Notificacion::enviar($idDuenio, 'TESTPRUEBA Privada', 'Mensaje privado');
        $idNotificacion = (int)Notificacion::listarPorUsuario($idDuenio, 1)[0]['id_notificacion'];

        $intruso = new Notificacion();
        $intruso->setDatos(['id_notificacion' => $idNotificacion, 'id_usuario' => $idIntruso]);
        $this->assertFalse($intruso->marcarcomoLeida(), 'Un usuario ajeno no debe poder marcarla.');

        $leida = (int)$this->pdoSeguridad->query("SELECT leida FROM notificaciones WHERE id_notificacion = {$idNotificacion}")->fetchColumn();
        $this->assertSame(0, $leida, 'La notificacion debe seguir sin leer.');
    }

    // CP-INT-21: obtenerIdUsuarioPorCorreo resuelve correos activos
    public function testObtenerIdUsuarioPorCorreo(): void
    {
        $idUsuario = $this->crearUsuarioPrueba();
        $correo = $this->pdoSeguridad->query("SELECT correo FROM usuarios WHERE id_usuario = {$idUsuario}")->fetchColumn();

        $this->assertSame($idUsuario, Notificacion::obtenerIdUsuarioPorCorreo($correo));
        $this->assertNull(Notificacion::obtenerIdUsuarioPorCorreo('nadie@test.local'), 'Un correo inexistente devuelve null.');
        $this->assertNull(Notificacion::obtenerIdUsuarioPorCorreo(''), 'Un correo vacio devuelve null.');
    }
}

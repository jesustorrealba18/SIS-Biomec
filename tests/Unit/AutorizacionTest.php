<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use GrupoProyecto\SisBiomec\seguridad\Autorizacion;

/**
 * CP-UNI: pruebas unitarias del control de acceso (RBAC) basado en sesion.
 * No toca la base de datos: se simula la sesion manipulando $_SESSION.
 */
class AutorizacionTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    // CP-UNI-30: Las paginas publicas permiten acceso sin permisos cargados
    public function testPaginasPublicasPermitenAccesoSinSesion(): void
    {
        $this->assertTrue(Autorizacion::tieneAcceso('inicio'));
        $this->assertTrue(Autorizacion::tieneAcceso('salir'));
        $this->assertTrue(Autorizacion::tieneAcceso('analitica'));
    }

    // CP-UNI-31: Una pagina que no existe en el mapa se deniega
    public function testPaginaNoMapeadaSeDeniega(): void
    {
        $this->assertFalse(Autorizacion::tieneAcceso('pagina_fantasma_xyz'));
    }

    // CP-UNI-32: Sin permisos en sesion, cualquier accion protegida falla
    public function testVerificarSinPermisosEnSesionEsFalso(): void
    {
        $this->assertFalse(Autorizacion::verificar('atletas', 'ver'));
        $this->assertFalse(Autorizacion::tieneAcceso('atleta'));
    }

    // CP-UNI-33: Con el permiso exacto en sesion, la accion se permite
    public function testVerificarConPermisoCorrectoEnSesion(): void
    {
        $_SESSION['permisos'] = ['atletas.ver', 'eventos.ver'];
        $this->assertTrue(Autorizacion::verificar('atletas', 'ver'));
        $this->assertTrue(Autorizacion::tieneAcceso('atleta'));
        $this->assertFalse(Autorizacion::verificar('atletas', 'eliminar'), 'Un permiso no concedido debe fallar.');
    }

    // CP-UNI-34: La comparacion de permisos es estricta (sensible a mayusculas)
    public function testVerificacionDePermisosEsEstricta(): void
    {
        $_SESSION['permisos'] = ['ATLETAS.ver'];
        $this->assertFalse(Autorizacion::verificar('atletas', 'ver'));
    }

    // CP-UNI-35: tieneRol evalua la lista CSV de roles de la sesion
    public function testTieneRolEvaluaListaCsv(): void
    {
        $_SESSION['rol'] = 'Administrador, Entrenador';
        $this->assertTrue(Autorizacion::tieneRol('Administrador'));
        $this->assertTrue(Autorizacion::tieneRol('Entrenador'));
        $this->assertFalse(Autorizacion::tieneRol('Atleta'));
        $this->assertFalse(Autorizacion::tieneRol('admin'), 'La comparacion es exacta, sin prefijos.');
    }
}

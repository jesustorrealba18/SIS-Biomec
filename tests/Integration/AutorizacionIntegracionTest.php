<?php

namespace Tests\Integration;

use GrupoProyecto\SisBiomec\seguridad\Autorizacion;

/**
 * CP-INT: pruebas de integracion del RBAC contra la BD real (sis_seguridad).
 * cargarPermisos consulta usuario_roles -> rol_permisos -> permisos y puebla
 * la sesion; aqui se valida con el usuario administrador semilla.
 */
class AutorizacionIntegracionTest extends IntegrationTestCase
{
    protected function tearDown(): void
    {
        $_SESSION = [];
        parent::tearDown();
    }

    // CP-INT-22: cargarPermisos puebla la sesion con los permisos reales del rol
    public function testCargarPermisosPueblaLaSesion(): void
    {
        $idAdmin = (int)$this->pdoSeguridad->query(
            "SELECT id_usuario FROM usuarios ORDER BY id_usuario LIMIT 1"
        )->fetchColumn();
        if ($idAdmin <= 0) {
            $this->markTestSkipped('No hay usuarios en la BD de seguridad.');
        }

        $_SESSION = [];
        $this->assertTrue(Autorizacion::cargarPermisos($idAdmin), 'cargarPermisos debe retornar true.');
        $this->assertNotEmpty($_SESSION['permisos'], 'El usuario debe tener permisos cargados.');

        $primerPermiso = (string)reset($_SESSION['permisos']);
        [$modulo, $accion] = explode('.', $primerPermiso);
        $this->assertTrue(Autorizacion::verificar($modulo, $accion), "El permiso cargado '{$primerPermiso}' debe verificarse.");
        $this->assertFalse(Autorizacion::verificar('modulo_inexistente', 'accion_inexistente'));
    }

    // CP-INT-23: un usuario sin roles obtiene sesion con permisos vacios
    public function testUsuarioSinRolesObtienePermisosVacios(): void
    {
        $idUsuario = $this->crearUsuarioPrueba();

        $_SESSION = [];
        $this->assertTrue(Autorizacion::cargarPermisos($idUsuario));
        $this->assertSame([], $_SESSION['permisos'], 'Sin roles asignados la lista debe estar vacia.');
        $this->assertFalse(Autorizacion::tieneAcceso('atleta'), 'Sin permisos no hay acceso a rutas privadas.');
        $this->assertTrue(Autorizacion::tieneAcceso('inicio'), 'Las paginas publicas siguen accesibles.');
    }
}

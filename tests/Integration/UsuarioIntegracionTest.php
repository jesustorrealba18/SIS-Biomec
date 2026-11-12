<?php

namespace Tests\Integration;

use GrupoProyecto\SisBiomec\modelo\UsuarioModelo;

/**
 * CP-INT: pruebas de integracion del modelo de usuarios contra la BD real
 * de desarrollo (sis_seguridad). Cada prueba limpia sus datos al terminar.
 */
class UsuarioIntegracionTest extends IntegrationTestCase
{
    private function rolAtletaId(): int
    {
        return (int)$this->pdoSeguridad->query(
            "SELECT id_rol FROM roles WHERE nombre = 'Atleta' AND activo = 1 LIMIT 1"
        )->fetchColumn();
    }

    private function crearUsuarioDePrueba(UsuarioModelo $modelo, ?int $rolId = null): array
    {
        $rolId = $rolId ?? $this->rolAtletaId();
        $datos = [
            'cedula'     => $this->cedulaPrueba(),
            'nombres'    => 'TESTPRUEBA',
            'apellidos'  => 'Integracion',
            'correo'     => $this->correoPrueba(),
            'contrasena' => 'Clave123',
            'roles'      => $rolId > 0 ? [$rolId] : [],
        ];
        $id = $modelo->crearUsuario($datos);
        return [$id, $datos];
    }

    // CP-INT-01: crear persiste hash bcrypt y asigna el rol
    public function testCrearUsuarioPersisteHashYRoles(): void
    {
        $rolId = $this->rolAtletaId();
        if ($rolId <= 0) {
            $this->markTestSkipped('No hay rol Atleta activo.');
        }
        $modelo = new UsuarioModelo();
        [$id, $datos] = $this->crearUsuarioDePrueba($modelo, $rolId);

        $this->assertIsInt($id, 'crearUsuario debe devolver el id insertado.');
        $this->assertGreaterThan(0, $id);

        $usuario = $modelo->obtenerPorId($id);
        $this->assertNotNull($usuario, 'El usuario creado debe existir.');
        $this->assertSame('TESTPRUEBA', $usuario['nombres']);
        $this->assertSame($datos['correo'], $usuario['correo']);
        $this->assertStringContainsString('Atleta', (string)$usuario['roles']);

        $hash = $this->pdoSeguridad->prepare('SELECT contrasena_hash FROM usuarios WHERE id_usuario = ?');
        $hash->execute([$id]);
        $this->assertTrue(password_verify('Clave123', (string)$hash->fetchColumn()), 'El hash guardado debe verificar la contrasena.');
    }

    // CP-INT-02: validarDatos detecta un correo que ya existe en la BD
    public function testValidarDatosDetectaCorreoDuplicado(): void
    {
        $existente = $this->pdoSeguridad->query('SELECT correo FROM usuarios WHERE correo IS NOT NULL LIMIT 1')->fetchColumn();
        if (!$existente) {
            $this->markTestSkipped('No hay usuarios con correo en la BD.');
        }
        $modelo = new UsuarioModelo();
        $errores = $modelo->validarDatos([
            'cedula'    => '',
            'nombres'   => 'TESTPRUEBA',
            'apellidos' => 'Integracion',
            'correo'    => $existente,
        ]);
        $this->assertArrayHasKey('correo', $errores, 'Un correo existente debe marcar error de duplicado.');
    }

    // CP-INT-03: actualizar modifica los datos y resincroniza roles
    public function testActualizarUsuarioModificaDatos(): void
    {
        $rolId = $this->rolAtletaId();
        if ($rolId <= 0) {
            $this->markTestSkipped('No hay rol Atleta activo.');
        }
        $modelo = new UsuarioModelo();
        [$id, $datos] = $this->crearUsuarioDePrueba($modelo, $rolId);
        $this->assertIsInt($id);

        $actualizado = $modelo->actualizarUsuario([
            'id_usuario' => $id,
            'cedula'     => $datos['cedula'],
            'nombres'    => 'TESTPRUEBA EDITADO',
            'apellidos'  => 'Integracion',
            'correo'     => $datos['correo'],
            'roles'      => [$rolId],
        ]);
        $this->assertTrue($actualizado);

        $usuario = $modelo->obtenerPorId($id);
        $this->assertSame('TESTPRUEBA EDITADO', $usuario['nombres']);
        $this->assertStringContainsString('Atleta', (string)$usuario['roles'], 'El rol debe conservarse tras editar.');
    }

    // CP-INT-04: toggleActivo alterna el estado de la cuenta
    public function testToggleActivoAlternaElEstado(): void
    {
        $modelo = new UsuarioModelo();
        [$id] = $this->crearUsuarioDePrueba($modelo);
        $this->assertIsInt($id);

        $this->assertTrue($modelo->toggleActivo($id, false));
        $activo = (int)$this->pdoSeguridad->query("SELECT activo FROM usuarios WHERE id_usuario = {$id}")->fetchColumn();
        $this->assertSame(0, $activo, 'El usuario debe quedar inactivo.');

        $this->assertTrue($modelo->toggleActivo($id, true));
        $activo = (int)$this->pdoSeguridad->query("SELECT activo FROM usuarios WHERE id_usuario = {$id}")->fetchColumn();
        $this->assertSame(1, $activo, 'El usuario debe quedar activo.');
    }

    // CP-INT-05: resetearContrasena re-hashea y desbloquea la cuenta
    public function testResetearContrasenaActualizaHashYDesbloquea(): void
    {
        $modelo = new UsuarioModelo();
        [$id] = $this->crearUsuarioDePrueba($modelo);
        $this->assertIsInt($id);

        // Simulamos una cuenta bloqueada por intentos fallidos
        $this->pdoSeguridad->exec("UPDATE usuarios SET intentos_fallidos = 5, bloqueado_hasta = NOW() + INTERVAL 1 HOUR WHERE id_usuario = {$id}");

        $this->assertTrue($modelo->resetearContrasena($id, 'NuevaClave456'));

        $fila = $this->pdoSeguridad->query("SELECT contrasena_hash, intentos_fallidos, bloqueado_hasta FROM usuarios WHERE id_usuario = {$id}")->fetch(\PDO::FETCH_ASSOC);
        $this->assertTrue(password_verify('NuevaClave456', $fila['contrasena_hash']));
        $this->assertSame(0, (int)$fila['intentos_fallidos'], 'Los intentos fallidos deben resetearse.');
        $this->assertNull($fila['bloqueado_hasta'], 'El bloqueo debe eliminarse.');
    }

    // CP-INT-06: eliminar borra el registro (y rol por CASCADE)
    public function testEliminarUsuarioBorraElRegistro(): void
    {
        $modelo = new UsuarioModelo();
        [$id] = $this->crearUsuarioDePrueba($modelo);
        $this->assertIsInt($id);

        $this->assertTrue($modelo->eliminarUsuario($id));
        $this->assertNull($modelo->obtenerPorId($id), 'El usuario eliminado no debe existir.');

        $roles = $this->pdoSeguridad->query("SELECT COUNT(*) FROM usuario_roles WHERE id_usuario = {$id}")->fetchColumn();
        $this->assertSame(0, (int)$roles, 'Los roles se eliminan por CASCADE.');
    }
}

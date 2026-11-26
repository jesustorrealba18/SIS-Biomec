<?php
namespace Tests\Integration\entrenador;

require_once __DIR__ . '/BaseEntrenadorTestCase.php';

use GrupoProyecto\SisBiomec\modelo\entrenador;

class EntrenadorModeloTest extends BaseEntrenadorTestCase
{
    private entrenador $modelo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->modelo = new entrenador($_ENV['DB_NAME_NATACION']);
    }

    public function testRegistrarEntrenadorExitoso(): void
    {
        $this->modelo->setDatos([
            'cedula'           => 'V-99887766',
            'nombres'          => 'Luis',
            'apellidos'        => 'Gómez',
            'fecha_nacimiento' => '1990-01-01',
            'genero'           => 'M',
            'correo'           => 'luis@test.com',
            'telefono'         => '04149876543',
            'direccion'        => 'Av. Principal',
        ]);

        $this->assertTrue($this->modelo->registrarEntrenador(), "Debería registrar al entrenador.");

        $entrenador = $this->db->query("SELECT * FROM entrenador WHERE cedula = 'V-99887766'")->fetch(\PDO::FETCH_ASSOC);
        $this->assertNotFalse($entrenador);
        $this->assertSame('Luis', $entrenador['nombres']);
    }

    public function testRegistrarFallaPorCedulaDuplicada(): void
    {
        $this->crearEntrenadorPersistido('V-11223344');

        $this->modelo->setDatos([
            'cedula'           => 'V-11223344',
            'nombres'          => 'Otro',
            'apellidos'        => 'Entrenador',
            'fecha_nacimiento' => '1990-01-01',
            'genero'           => 'M',
            'correo'           => 'otro@test.com',
            'telefono'         => '04140000000',
            'direccion'        => 'Calle 2',
        ]);

        $this->assertFalse($this->modelo->registrarEntrenador());
        $errores = $this->modelo->validarDatos();
        $this->assertArrayHasKey('cedula', $errores);
    }

    public function testCambiarEstadoEntrenador(): void
    {
        $id = $this->crearEntrenadorPersistido();

        $this->assertTrue($this->modelo->eliminarEntrenador($id));
        $estado = $this->db->query("SELECT estado FROM entrenador WHERE id_entrenador = {$id}")->fetchColumn();
        $this->assertSame('Inactivo', $estado);

        $this->assertTrue($this->modelo->reactivarEntrenador($id));
        $estado = $this->db->query("SELECT estado FROM entrenador WHERE id_entrenador = {$id}")->fetchColumn();
        $this->assertSame('Activo', $estado);
    }

    public function testListarYObtenerPorId(): void
    {
        $id = $this->crearEntrenadorPersistido();
        
        $lista = $this->modelo->listarEntrenador();
        $this->assertCount(1, $lista);

        $detalle = $this->modelo->obtenerPorId($id);
        $this->assertIsArray($detalle);
        $this->assertArrayHasKey('edad', $detalle);
    }
}
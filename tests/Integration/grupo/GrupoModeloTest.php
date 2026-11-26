<?php
namespace Tests\Integration\grupo;

require_once __DIR__ . '/BaseGrupoTestCase.php'; 

use GrupoProyecto\SisBiomec\modelo\Grupo;

class GrupoModeloTest extends BaseGrupoTestCase
{
    private Grupo $modelo;

    protected function setUp(): void
    {
        parent::setUp();
        // SOLUCIÓN CLAVE: Pasamos el nombre de la BD de test al modelo
        $this->modelo = new Grupo($_ENV['DB_NAME_NATACION']);
    }

    public function testRegistrarGrupoExitoso(): void
    {
        $this->modelo->setDatos([
            'nombre'        => 'Nuevo Grupo A',
            'descripcion'   => 'Un grupo de prueba',
            'id_entrenador' => 1,
        ]);

        $this->assertTrue($this->modelo->registrarGrupo(), "El grupo debería registrarse correctamente.");

        $grupo = $this->db->query("SELECT * FROM grupos_entrenamiento WHERE nombre = 'Nuevo Grupo A'")->fetch(\PDO::FETCH_ASSOC);
        $this->assertNotFalse($grupo, "El grupo no se encontró en la BD.");
        $this->assertSame(1, (int)$grupo['activo']);
    }

    public function testRegistrarGrupoFallaPorNombreDuplicado(): void
    {
        $this->crearGrupoPersistido('Grupo Existente');

        $this->modelo->setDatos([
            'nombre'        => 'Grupo Existente',
            'descripcion'   => 'Intentando duplicar',
            'id_entrenador' => 1,
        ]);

        $this->assertFalse($this->modelo->registrarGrupo(), "No debería permitir registrar un grupo con nombre duplicado.");
        $this->assertSame('El nombre del grupo ya existe.', $this->modelo->obtenerUltimoError());
    }

    public function testEditarGrupoExitoso(): void
    {
        $id = $this->crearGrupoPersistido('Grupo Original');

        $this->modelo->setDatos([
            'id_grupo_original' => $id,
            'nombre'            => 'Grupo Editado',
            'descripcion'       => 'Descripción actualizada',
            'id_entrenador'     => 1,
        ]);

        $this->assertTrue($this->modelo->editarGrupo(), "Debería editar el grupo correctamente.");

        $grupo = $this->db->query("SELECT * FROM grupos_entrenamiento WHERE id_grupo = {$id}")->fetch(\PDO::FETCH_ASSOC);
        $this->assertSame('Grupo Editado', $grupo['nombre']);
        $this->assertSame('Descripción actualizada', $grupo['descripcion']);
    }

    public function testCambiarEstadoGrupo(): void
    {
        $id = $this->crearGrupoPersistido();
        $this->assertSame(1, $this->estadoGrupoDe($id));

        $this->assertTrue($this->modelo->cambiarEstadoGrupo($id, 0), "Debería desactivar el grupo.");
        $this->assertSame(0, $this->estadoGrupoDe($id));

        $this->assertTrue($this->modelo->cambiarEstadoGrupo($id, 1), "Debería reactivar el grupo.");
        $this->assertSame(1, $this->estadoGrupoDe($id));
    }

    public function testAsignarAtletasAGrupoExitoso(): void
    {
        $id_grupo = $this->crearGrupoPersistido();

        $this->modelo->setDatosAsignacion([
            'id_grupo' => $id_grupo,
            'atletas'  => [1, 2],
        ]);

        $this->assertTrue($this->modelo->asignarGrupoAtletas(), "Debería asignar los atletas correctamente.");

        $asignaciones = $this->db->query("SELECT * FROM grupo_atleta WHERE id_grupo = {$id_grupo}")->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertCount(2, $asignaciones);
    }

    public function testAsignarAtletasFallaSiYaEstanEnOtroGrupo(): void
    {
        $id_grupo1 = $this->crearGrupoPersistido('Grupo 1');
        $id_grupo2 = $this->crearGrupoPersistido('Grupo 2');

        $this->db->exec("INSERT INTO grupo_atleta (id_grupo, id_atleta, fecha_asignacion) VALUES ({$id_grupo1}, 1, NOW())");

        $this->modelo->setDatosAsignacion([
            'id_grupo' => $id_grupo2,
            'atletas'  => [1],
        ]);

        $this->assertFalse($this->modelo->asignarGrupoAtletas(), "No debería permitir asignar un atleta que ya está en otro grupo activo.");
        $this->assertStringContainsString('ya pertenece al grupo', $this->modelo->obtenerUltimoError());
        
        $count = (int)$this->db->query("SELECT COUNT(*) FROM grupo_atleta WHERE id_grupo = {$id_grupo2}")->fetchColumn();
        $this->assertSame(0, $count);
    }

    public function testCambiarGrupoAtletaExitoso(): void
    {
        $id_grupo1 = $this->crearGrupoPersistido('Grupo 1');
        $id_grupo2 = $this->crearGrupoPersistido('Grupo 2');

        $this->db->exec("INSERT INTO grupo_atleta (id_grupo, id_atleta, fecha_asignacion) VALUES ({$id_grupo1}, 1, NOW())");

        $this->assertTrue($this->modelo->cambiarGrupoAtleta(1, $id_grupo2), "Debería cambiar al atleta de grupo.");

        $grupoActual = $this->db->query("SELECT id_grupo FROM grupo_atleta WHERE id_atleta = 1")->fetchColumn();
        $this->assertSame($id_grupo2, (int)$grupoActual);
    }

    public function testObtenerPorIdYListarGrupos(): void
    {
        $id = $this->crearGrupoPersistido('Grupo Único');

        $detalle = $this->modelo->obtenerPorId($id);
        $this->assertIsArray($detalle);
        $this->assertSame('Grupo Único', $detalle['nombre']);

        $lista = $this->modelo->listarGrupos(1);
        $this->assertCount(1, $lista);
        $this->assertSame('Grupo Único', $lista[0]['nombre']);
    }

    public function testListarAtletasDisponibles(): void
    {
        $disponibles = $this->modelo->listarAtletasDisponibles();
        $this->assertCount(3, $disponibles);

        $id_grupo = $this->crearGrupoPersistido();
        $this->db->exec("INSERT INTO grupo_atleta (id_grupo, id_atleta, fecha_asignacion) VALUES ({$id_grupo}, 1, NOW())");

        $disponibles = $this->modelo->listarAtletasDisponibles();
        $this->assertCount(2, $disponibles);
    }
}
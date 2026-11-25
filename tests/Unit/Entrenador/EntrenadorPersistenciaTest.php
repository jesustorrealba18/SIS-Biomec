<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use GrupoProyecto\SisBiomec\modelo\entrenador;

class EntrenadorPersistenciaTest extends TestCase
{
    private \PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

        $this->pdo->sqliteCreateFunction('CURDATE', function () { return date('Y-m-d'); }, 0);
        $this->pdo->sqliteCreateFunction('TIMESTAMPDIFF', function ($unit, $start, $end) {
            return date('Y') - date('Y', strtotime($start));
        }, 3);

        $this->pdo->exec("
            CREATE TABLE entrenador (
                id_entrenador INTEGER PRIMARY KEY AUTOINCREMENT,
                cedula TEXT, nombres TEXT, apellidos TEXT, fecha_nacimiento TEXT,
                genero TEXT, correo TEXT, telefono TEXT, direccion TEXT,
                curriculum_pdf TEXT, curriculum_nombre_original TEXT,
                estado TEXT DEFAULT 'Activo', id_usuario INTEGER
            );
        ");
    }

    private function entrenador(): entrenador
    {
        $obj = new entrenador();
        $ref = new \ReflectionClass(\GrupoProyecto\SisBiomec\modelo\Conexion::class);
        $prop = $ref->getProperty('conex1');
        $prop->setAccessible(true);
        $prop->setValue($obj, $this->pdo);
        return $obj;
    }

    public function testRegistrarEntrenador(): void
    {
        $e = $this->entrenador();
        $e->setDatos([
            'cedula' => 'V-11111111', 'nombres' => 'Pedro', 'apellidos' => 'Perez',
            'fecha_nacimiento' => '1985-05-20', 'genero' => 'M',
            'correo' => 'pedro@test.com', 'telefono' => '04140000000', 'direccion' => 'Calle 1'
        ]);

        $this->assertTrue($e->registrarEntrenador());
        $row = $this->pdo->query("SELECT * FROM entrenador")->fetch();
        $this->assertSame('V-11111111', $row['cedula']);
        $this->assertSame('Activo', $row['estado']);
    }

    public function testEditarEntrenador(): void
    {
        $this->pdo->exec("INSERT INTO entrenador (id_entrenador, cedula, nombres) VALUES (1, 'V-11111111', 'Viejo')");
        
        $e = $this->entrenador();
        $e->setDatos([
            'id_entrenador' => '1',
            'cedula' => 'V-11111111', 'nombres' => 'Nuevo', 'apellidos' => 'Perez',
            'fecha_nacimiento' => '1985-05-20', 'genero' => 'M',
            'correo' => 'pedro@test.com', 'telefono' => '04140000000', 'direccion' => 'Calle 1'
        ]);

        $this->assertTrue($e->editarEntrenador());
        $nombre = $this->pdo->query("SELECT nombres FROM entrenador WHERE id_entrenador=1")->fetchColumn();
        $this->assertSame('Nuevo', $nombre);
    }

    public function testEliminarYReactivarEntrenador(): void
    {
        $this->pdo->exec("INSERT INTO entrenador (id_entrenador, estado) VALUES (1, 'Activo')");
        $e = $this->entrenador();

        // Desactivar
        $this->assertTrue($e->eliminarEntrenador(1));
        $this->assertSame('Inactivo', $this->pdo->query("SELECT estado FROM entrenador WHERE id_entrenador=1")->fetchColumn());

        // Reactivar
        $this->assertTrue($e->reactivarEntrenador(1));
        $this->assertSame('Activo', $this->pdo->query("SELECT estado FROM entrenador WHERE id_entrenador=1")->fetchColumn());
    }

    public function testListarEntrenador(): void
    {
        $this->pdo->exec("INSERT INTO entrenador (nombres) VALUES ('Juan'), ('Maria')");
        $lista = $this->entrenador()->listarEntrenador();
        $this->assertCount(2, $lista);
    }
}
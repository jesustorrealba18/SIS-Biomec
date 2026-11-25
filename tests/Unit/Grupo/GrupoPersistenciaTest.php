<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use GrupoProyecto\SisBiomec\modelo\Grupo;

class GrupoPersistenciaTest extends TestCase
{
    private \PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

        // Magia: Le enseñamos a SQLite funciones de MySQL
        $this->pdo->sqliteCreateFunction('NOW', function () {
            return date('Y-m-d H:i:s');
        }, 0);
        $this->pdo->sqliteCreateFunction('CURDATE', function () {
            return date('Y-m-d');
        }, 0);
        // Simulamos CONCAT
        $this->pdo->sqliteCreateFunction('CONCAT', function (...$args) {
            return implode('', $args);
        }, -1);

        $this->pdo->exec("
            CREATE TABLE grupos_entrenamiento (
                id_grupo INTEGER PRIMARY KEY AUTOINCREMENT,
                nombre TEXT,
                descripcion TEXT,
                id_entrenador INTEGER,
                activo INTEGER DEFAULT 1
            );
            CREATE TABLE entrenador (
                id_entrenador INTEGER PRIMARY KEY,
                nombres TEXT,
                apellidos TEXT,
                cedula TEXT,
                correo TEXT
            );
            CREATE TABLE atletas (
                id_atleta INTEGER PRIMARY KEY,
                nombres TEXT,
                apellidos TEXT,
                correo TEXT,
                estado INTEGER DEFAULT 1
            );
            CREATE TABLE grupo_atleta (
                id_grupo INTEGER,
                id_atleta INTEGER,
                fecha_asignacion TEXT,
                PRIMARY KEY (id_grupo, id_atleta)
            );
            INSERT INTO entrenador (id_entrenador, nombres, apellidos, cedula) VALUES (1, 'Juan', 'Perez', '12345');
            INSERT INTO atletas (id_atleta, nombres, apellidos) VALUES (1, 'Pedro', 'Gomez'), (2, 'Maria', 'Lopez');
        ");
    }

    private function grupo(): Grupo
    {
        $obj = new Grupo();
        $ref = new \ReflectionClass(\GrupoProyecto\SisBiomec\modelo\Conexion::class);
        $prop = $ref->getProperty('conex1');
        $prop->setAccessible(true);
        $prop->setValue($obj, $this->pdo);
        return $obj;
    }

    public function testRegistrarGrupo(): void
    {
        $g = $this->grupo();
        $g->setDatos([
            'nombre' => 'Grupo Test',
            'descripcion' => 'Test de desc',
            'id_entrenador' => '1'
        ]);

        $this->assertTrue($g->registrarGrupo());
        
        $row = $this->pdo->query("SELECT * FROM grupos_entrenamiento")->fetch();
        $this->assertSame('Grupo Test', $row['nombre']);
        $this->assertSame(1, (int)$row['activo']);
    }

    public function testEditarGrupo(): void
    {
        $this->pdo->exec("INSERT INTO grupos_entrenamiento (id_grupo, nombre) VALUES (1, 'Antiguo')");
        
        $g = $this->grupo();
        $g->setDatos([
            'id_grupo_original' => '1',
            'nombre' => 'Nuevo Nombre',
            'id_entrenador' => '1'
        ]);

        $this->assertTrue($g->editarGrupo());
        $nombre = $this->pdo->query("SELECT nombre FROM grupos_entrenamiento WHERE id_grupo=1")->fetchColumn();
        $this->assertSame('Nuevo Nombre', $nombre);
    }

    public function testCambiarEstadoGrupo(): void
    {
        $this->pdo->exec("INSERT INTO grupos_entrenamiento (id_grupo, activo) VALUES (1, 1)");
        $g = $this->grupo();

        $this->assertTrue($g->cambiarEstadoGrupo(1, 0));
        $activo = $this->pdo->query("SELECT activo FROM grupos_entrenamiento WHERE id_grupo=1")->fetchColumn();
        $this->assertSame(0, (int)$activo);
    }

    public function testAsignarAtletasYDesasignar(): void
    {
        $this->pdo->exec("INSERT INTO grupos_entrenamiento (id_grupo) VALUES (1)");
        $g = $this->grupo();

        // Asignar
        $g->setDatosAsignacion(['id_grupo' => 1, 'atletas' => [1, 2]]);
        $this->assertTrue($g->asignarGrupoAtletas());
        
        $count = $this->pdo->query("SELECT COUNT(*) FROM grupo_atleta")->fetchColumn();
        $this->assertSame(2, (int)$count);

        // Desasignar
        $this->assertTrue($g->desasignarAtletas([1]));
        $countNuevo = $this->pdo->query("SELECT COUNT(*) FROM grupo_atleta")->fetchColumn();
        $this->assertSame(1, (int)$countNuevo);
    }

    public function testVerificarNombreExistente(): void
    {
        $this->pdo->exec("INSERT INTO grupos_entrenamiento (id_grupo, nombre, activo) VALUES (1, 'Existente', 1)");
        $g = $this->grupo();

        $this->assertTrue($g->verificarNombreExistente('Existente'));
        $this->assertFalse($g->verificarNombreExistente('NoExiste'));
    }

    public function testObtenerPorId(): void
    {
        $this->pdo->exec("INSERT INTO grupos_entrenamiento (id_grupo, nombre, id_entrenador) VALUES (1, 'Grupo 1', 1)");
        $g = $this->grupo();
        
        $datos = $g->obtenerPorId(1);
        $this->assertNotNull($datos);
        $this->assertSame('Grupo 1', $datos['nombre']);
        $this->assertSame('Juan Perez', $datos['entrenador_nombre']); // Probando el CONCAT
    }
}
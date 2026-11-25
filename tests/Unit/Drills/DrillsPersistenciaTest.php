<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use GrupoProyecto\SisBiomec\modelo\drills;

class DrillsPersistenciaTest extends TestCase
{
    private \PDO $pdo;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Simulamos que hay un usuario logueado en la sesión
        // Esto evita problemas si Bitacora intenta leer $_SESSION['id']
        if (session_status() === PHP_SESSION_NONE) {
            $_SESSION = ['id' => 1];
        }

        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

        // CONCAT para las consultas que unen nombres de atletas
        $this->pdo->sqliteCreateFunction('CONCAT', function (...$args) { 
            return implode('', $args); 
        }, -1);

        // Creamos las tablas con TODAS las columnas que usa el modelo
        $this->pdo->exec("
            CREATE TABLE drills (
                id_drill INTEGER PRIMARY KEY AUTOINCREMENT,
                nombre TEXT, estilo TEXT, categoria TEXT, enfoque_tecnico TEXT,
                descripcion TEXT, instrucciones TEXT, metraje_sugerido TEXT,
                dificultad TEXT, material_requerido TEXT, personalizado INTEGER,
                id_usuario_creador INTEGER, activo INTEGER DEFAULT 1, fecha_creacion TEXT
            );
            CREATE TABLE atletas (
                id_atleta INTEGER PRIMARY KEY, 
                nombres TEXT, 
                apellidos TEXT, 
                cedula TEXT
            );
        ");
    }

    private function drill(): drills
    {
        $obj = new drills();
        $ref = new \ReflectionClass(\GrupoProyecto\SisBiomec\modelo\Conexion::class);
        $prop = $ref->getProperty('conex1');
        $prop->setAccessible(true);
        $prop->setValue($obj, $this->pdo);
        return $obj;
    }

    public function testRegistrarDrill(): void
    {
        $d = $this->drill();
        $d->setDatos([
            'nombre' => 'Crol Test', 
            'estilo' => 'Libre', 
            'categoria' => 'Tecnico',
            'enfoque_tecnico' => 'Tecnica de brazos', 
            'descripcion' => 'Descripcion del drill',
            'instrucciones' => 'Instrucciones paso a paso', 
            'metraje_sugerido' => '4x50m',
            'dificultad' => 'Basico', 
            'material_requerido' => 'Ninguno',
            'activo' => 1, 
            'id_usuario_creador' => 1
        ]);
        
        $this->assertTrue($d->registrarDrills());

        $row = $this->pdo->query("SELECT * FROM drills")->fetch();
        $this->assertSame('Crol Test', $row['nombre']);
        $this->assertSame(1, (int)$row['activo']);
    }

    public function testEditarDrill(): void
    {
        $this->pdo->exec("INSERT INTO drills (id_drill, nombre, activo) VALUES (1, 'Viejo', 1)");

        $d = $this->drill();
        $d->setDatos([
            'id_drill' => '1', 
            'nombre' => 'Nuevo Nombre', 
            'estilo' => 'Espalda',
            'categoria' => 'Fuerza', 
            'enfoque_tecnico' => 'Enfoque', 
            'descripcion' => 'Descripcion valida',
            'instrucciones' => 'Instrucciones validas', 
            'metraje_sugerido' => '100m',
            'dificultad' => 'Avanzado', 
            'material_requerido' => 'Aletas', 
            'activo' => 1
        ]);
        
        $this->assertTrue($d->editarDrills());

        $nombre = $this->pdo->query("SELECT nombre FROM drills WHERE id_drill=1")->fetchColumn();
        $this->assertSame('Nuevo Nombre', $nombre);
    }

    public function testEliminarDrillLoDesactiva(): void
    {
        $this->pdo->exec("INSERT INTO drills (id_drill, nombre, activo) VALUES (1, 'Drill Activo', 1)");
        
        $d = $this->drill();
        $d->setIdEliminar(1);
        
        $this->assertTrue($d->eliminarDrills());

        $activo = $this->pdo->query("SELECT activo FROM drills WHERE id_drill=1")->fetchColumn();
        $this->assertSame(0, (int)$activo);
    }

    public function testCambiarEstado(): void
    {
        $this->pdo->exec("INSERT INTO drills (id_drill, nombre, activo) VALUES (1, 'Drill', 0)");
        
        $d = $this->drill();
        $this->assertTrue($d->cambiarEstado(1, 1));
        
        $this->assertSame(1, (int)$this->pdo->query("SELECT activo FROM drills WHERE id_drill=1")->fetchColumn());
    }

    public function testListarDrillsActivos(): void
    {
        $this->pdo->exec("
            INSERT INTO drills (id_drill, nombre, activo) VALUES (1, 'Activo', 1), (2, 'Inactivo', 0);
            INSERT INTO atletas (id_atleta, nombres, apellidos) VALUES (1, 'Pedro', 'Gomez');
        ");
        
        $lista = $this->drill()->listarDrills(false);
        
        $this->assertCount(1, $lista);
        $this->assertSame('Activo', $lista[0]['nombre']);
    }

    public function testListarDrillsIncluyendoInactivos(): void
    {
        $this->pdo->exec("
            INSERT INTO drills (id_drill, nombre, activo) VALUES (1, 'Activo', 1), (2, 'Inactivo', 0);
            INSERT INTO atletas (id_atleta, nombres, apellidos) VALUES (1, 'Pedro', 'Gomez');
        ");
        
        $lista = $this->drill()->listarDrills(true);
        
        $this->assertCount(2, $lista);
    }
}
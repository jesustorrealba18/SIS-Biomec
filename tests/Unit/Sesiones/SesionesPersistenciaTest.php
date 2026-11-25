<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use GrupoProyecto\SisBiomec\modelo\Sesiones;

class SesionesPersistenciaTest extends TestCase
{
    private \PDO $pdo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

        // LA MAGIA: Le decimos a SQLite que entienda la función NOW() de MySQL
        $this->pdo->sqliteCreateFunction('NOW', function () {
            return date('Y-m-d H:i:s');
        }, 0);

        $this->pdo->exec("
            CREATE TABLE grupos_entrenamiento (id_grupo INTEGER PRIMARY KEY, nombre TEXT);
            CREATE TABLE microciclos (id_microciclo INTEGER PRIMARY KEY, nombre TEXT);
            CREATE TABLE drills (id_drill INTEGER PRIMARY KEY, nombre TEXT, estilo TEXT);
            CREATE TABLE sesiones (
                id_sesion INTEGER PRIMARY KEY AUTOINCREMENT,
                id_microciclo INTEGER NULL,
                id_grupo INTEGER NOT NULL,
                fecha TEXT NOT NULL,
                tipo_sesion TEXT NOT NULL,
                calentamiento TEXT,
                vuelta_calma TEXT,
                volumen_planificado INTEGER DEFAULT 0,
                volumen_ejecutado INTEGER DEFAULT 0,
                observaciones TEXT,
                estado TEXT DEFAULT 'Planificada',
                id_entrenador INTEGER NOT NULL,
                duracion_minutos INTEGER,
                fecha_modificacion TEXT
            );
            CREATE TABLE series_sesion (
                id_serie INTEGER PRIMARY KEY AUTOINCREMENT,
                id_sesion INTEGER NOT NULL,
                orden_ejecucion INTEGER,
                bloque TEXT,
                id_drill INTEGER NULL,
                ejercicio_descripcion TEXT NULL,
                repeticiones INTEGER,
                distancia_m INTEGER,
                descanso_seg INTEGER,
                zona_intensidad TEXT,
                ritmo_objetivo TEXT
            );
            INSERT INTO grupos_entrenamiento (id_grupo, nombre) VALUES (1, 'Grupo A');
            INSERT INTO drills (id_drill, nombre, estilo) VALUES (1, 'Crol', 'Libre');
        ");
    }

    private function sesiones(): Sesiones
    {
        $obj = new Sesiones();

        // Usamos Reflexión para inyectar nuestro PDO de SQLite directamente en la clase Conexion
        $ref = new \ReflectionClass(\GrupoProyecto\SisBiomec\modelo\Conexion::class);
        $prop = $ref->getProperty('conex1');
        $prop->setAccessible(true);
        $prop->setValue($obj, $this->pdo);

        return $obj;
    }

    public function testRegistrarSesionConSeriesYVolumen(): void
    {
        $s = $this->sesiones();
        $s->setDatos([
            'id_grupo'         => '1',
            'id_entrenador'    => '7',
            'fecha'            => date('Y-m-d', strtotime('+1 day')),
            'tipo_sesion'      => 'Tecnica',
            'duracion_minutos' => '60',
        ]);
        $s->setSeries([
            ['bloque' => 'Calentamiento', 'id_drill' => null, 'ejercicio_descripcion' => 'Trote',
             'repeticiones' => 2, 'distancia_m' => 100, 'descanso_seg' => 30, 'zona_intensidad' => 'Z2'],
            ['bloque' => 'Principal', 'id_drill' => 1,
             'repeticiones' => 4, 'distancia_m' => 200, 'descanso_seg' => 45, 'zona_intensidad' => 'Z3'],
        ]);

        $this->assertTrue($s->registrarSesion());

        $ses = $this->pdo->query("SELECT * FROM sesiones")->fetch();
        $this->assertSame(1000, (int)$ses['volumen_planificado']);
        $this->assertSame('Planificada', $ses['estado']);

        $series = $this->pdo->query("SELECT * FROM series_sesion ORDER BY orden_ejecucion")->fetchAll();
        $this->assertCount(2, $series);
        $this->assertSame(1, (int)$series[0]['orden_ejecucion']);
        $this->assertSame(2, (int)$series[1]['orden_ejecucion']);
    }

    public function testIniciarSesionPlanificada(): void
    {
        $this->pdo->exec("INSERT INTO sesiones (id_grupo, fecha, tipo_sesion, id_entrenador, estado)
                          VALUES (1, '2030-01-01', 'Tecnica', 7, 'Planificada')");
        $id = (int)$this->pdo->lastInsertId();

        $s = $this->sesiones();
        $s->setDatos(['id_sesion' => (string)$id]);

        $this->assertTrue($s->InicializarSesion());
        $this->assertSame('Parcial',
            $this->pdo->query("SELECT estado FROM sesiones WHERE id_sesion=$id")->fetchColumn());
    }

    public function testIniciarSesionYaParcialDevuelveFalse(): void
    {
        $this->pdo->exec("INSERT INTO sesiones (id_grupo, fecha, tipo_sesion, id_entrenador, estado)
                          VALUES (1, '2030-01-01', 'Tecnica', 7, 'Parcial')");
        $id = (int)$this->pdo->lastInsertId();

        $s = $this->sesiones();
        $s->setDatos(['id_sesion' => (string)$id]);

        $this->assertFalse($s->InicializarSesion());
    }

    public function testCompletarSesionParcial(): void
    {
        $this->pdo->exec("INSERT INTO sesiones (id_grupo, fecha, tipo_sesion, id_entrenador, estado, volumen_planificado)
                          VALUES (1, '2030-01-01', 'Tecnica', 7, 'Parcial', 1000)");
        $id = (int)$this->pdo->lastInsertId();

        $s = $this->sesiones();
        $s->setDatos([
            'id_sesion'         => (string)$id,
            'volumen_ejecutado' => '900',
            'observaciones'     => 'Cumplido',
            'estado'            => 'Completada',
        ]);

        $this->assertTrue($s->completarSesion());
        $row = $this->pdo->query("SELECT estado, volumen_ejecutado FROM sesiones WHERE id_sesion=$id")->fetch();
        $this->assertSame('Completada', $row['estado']);
        $this->assertSame(900, (int)$row['volumen_ejecutado']);
    }

    public function testCompletarSesionCanceladaDevuelveFalse(): void
    {
        $this->pdo->exec("INSERT INTO sesiones (id_grupo, fecha, tipo_sesion, id_entrenador, estado, volumen_planificado)
                          VALUES (1, '2030-01-01', 'Tecnica', 7, 'Cancelada', 1000)");
        $id = (int)$this->pdo->lastInsertId();

        $s = $this->sesiones();
        $s->setDatos(['id_sesion' => (string)$id, 'volumen_ejecutado' => '500']);

        $this->assertFalse($s->completarSesion());
    }

    public function testCancelarSesionPlanificada(): void
    {
        $this->pdo->exec("INSERT INTO sesiones (id_grupo, fecha, tipo_sesion, id_entrenador, estado)
                          VALUES (1, '2030-01-01', 'Tecnica', 7, 'Planificada')");
        $id = (int)$this->pdo->lastInsertId();

        $s = $this->sesiones();
        $s->setDatos(['id_sesion' => (string)$id]);

        $this->assertTrue($s->cancelarSesion());
        $this->assertSame('Cancelada',
            $this->pdo->query("SELECT estado FROM sesiones WHERE id_sesion=$id")->fetchColumn());
    }

    public function testEditarSesionReemplazaSeries(): void
    {
        $this->pdo->exec("INSERT INTO sesiones (id_grupo, fecha, tipo_sesion, id_entrenador, estado, volumen_planificado)
                          VALUES (1, '2030-01-01', 'Tecnica', 7, 'Planificada', 0)");
        $id = (int)$this->pdo->lastInsertId();
        $this->pdo->exec("INSERT INTO series_sesion (id_sesion, orden_ejecucion, bloque, repeticiones, distancia_m, descanso_seg, zona_intensidad)
                          VALUES ($id, 1, 'Principal', 1, 100, 30, 'Z3')");

        $s = $this->sesiones();
        $s->setDatos([
            'id_sesion'        => (string)$id,
            'id_grupo'         => '1',
            'id_entrenador'    => '7',
            'fecha'            => date('Y-m-d', strtotime('+2 days')),
            'tipo_sesion'      => 'Resistencia',
            'duracion_minutos' => '90',
        ]);
        $s->setSeries([
            ['bloque' => 'Principal', 'id_drill' => 1, 'repeticiones' => 3, 'distancia_m' => 200,
             'descanso_seg' => 40, 'zona_intensidad' => 'Z4'],
        ]);

        $this->assertTrue($s->editarSesion());

        $series = $this->pdo->query("SELECT * FROM series_sesion WHERE id_sesion=$id")->fetchAll();
        $this->assertCount(1, $series);
        $this->assertSame(3, (int)$series[0]['repeticiones']);
        $this->assertSame(600, (int)$this->pdo->query("SELECT volumen_planificado FROM sesiones WHERE id_sesion=$id")->fetchColumn());
    }

    public function testObtenerDetalleSesion(): void
    {
        $this->pdo->exec("INSERT INTO sesiones (id_grupo, fecha, tipo_sesion, id_entrenador, estado, volumen_planificado)
                          VALUES (1, '2030-01-01', 'Tecnica', 7, 'Planificada', 500)");
        $id = (int)$this->pdo->lastInsertId();
        $this->pdo->exec("INSERT INTO series_sesion (id_sesion, orden_ejecucion, bloque, repeticiones, distancia_m, descanso_seg, zona_intensidad)
                          VALUES ($id, 1, 'Principal', 2, 100, 20, 'Z3')");

        $detalle = $this->sesiones()->obtenerDetalleSesion($id);
        $this->assertNotNull($detalle);
        $this->assertSame('Grupo A', $detalle['grupo_nombre']);
        $this->assertCount(1, $detalle['series']);
    }

    public function testListarSesionesFiltroPorGrupo(): void
    {
        $this->pdo->exec("INSERT INTO grupos_entrenamiento (id_grupo, nombre) VALUES (2, 'Grupo B')");
        $this->pdo->exec("INSERT INTO sesiones (id_grupo, fecha, tipo_sesion, id_entrenador)
                          VALUES (1, '2030-01-01', 'Tecnica', 7), (2, '2030-01-02', 'Resistencia', 7)");

        $lista = $this->sesiones()->listarSesiones(1);
        $this->assertCount(1, $lista);
        $this->assertSame('Grupo A', $lista[0]['grupo_nombre']);
    }
}
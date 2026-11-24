<?php

namespace Tests\Integration;

use GrupoProyecto\SisBiomec\modelo\Sesiones;
use PHPUnit\Framework\TestCase;
use PDO;

class SesionesPersistenciaTest extends TestCase
{
    private PDO $pdo;
    private Sesiones $sesiones;

    protected function setUp(): void
    {
        // 1. Configurar SQLite en memoria
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec("
            CREATE TABLE sesiones (
                id_sesion INTEGER PRIMARY KEY AUTOINCREMENT,
                id_microciclo INTEGER NULL,
                id_grupo INTEGER NOT NULL,
                id_entrenador INTEGER NOT NULL,
                fecha TEXT NOT NULL,
                tipo_sesion TEXT NOT NULL,
                duracion_minutos INTEGER,
                calentamiento TEXT,
                vuelta_calma TEXT,
                volumen_planificado INTEGER DEFAULT 0,
                volumen_ejecutado INTEGER,
                observaciones TEXT,
                estado TEXT DEFAULT 'Planificada',
                fecha_modificacion TEXT
            );
            CREATE TABLE series_sesion (
                id_serie INTEGER PRIMARY KEY AUTOINCREMENT,
                id_sesion INTEGER NOT NULL,
                orden_ejecucion INTEGER NOT NULL,
                bloque TEXT NOT NULL,
                id_drill INTEGER NULL,
                ejercicio_descripcion TEXT,
                repeticiones INTEGER NOT NULL,
                distancia_m INTEGER NOT NULL,
                descanso_seg INTEGER NOT NULL,
                zona_intensidad TEXT NOT NULL,
                ritmo_objetivo TEXT
            );
            CREATE TABLE microciclos (id_microciclo INTEGER PRIMARY KEY, nombre TEXT);
            CREATE TABLE grupos_entrenamiento (id_grupo INTEGER PRIMARY KEY, nombre TEXT);
            CREATE TABLE drills (id_drill INTEGER PRIMARY KEY, nombre TEXT, estilo TEXT);
        ");

        // 2. Instanciar Sesiones
        $this->sesiones = new Sesiones(); 
        
        // 3. INYECTAR PDO USANDO REFLEXIÓN (Sin tocar Conexion.php)
        $reflexion = new \ReflectionClass(\GrupoProyecto\SisBiomec\modelo\Conexion::class);
        $propiedad = $reflexion->getProperty('pdo');
        $propiedad->setAccessible(true);
        $propiedad->setValue($this->sesiones, $this->pdo);
    }
    /** @test */
    public function registrar_sesion_inserta_cabecera_y_series(): void
    {
        $this->sesiones->setDatos([
            'id_grupo'         => 1,
            'id_entrenador'    => 7,
            'fecha'            => date('Y-m-d', strtotime('+1 day')),
            'tipo_sesion'      => 'Resistencia',
            'duracion_minutos' => 90,
        ]);
        $this->sesiones->setSeries([
            ['bloque' => 'Calentamiento', 'id_drill' => 1, 'repeticiones' => 2, 'distancia_m' => 100,
             'descanso_seg' => 20, 'zona_intensidad' => 'Z1'],
            ['bloque' => 'Principal', 'id_drill' => 2, 'repeticiones' => 4, 'distancia_m' => 200,
             'descanso_seg' => 30, 'zona_intensidad' => 'Z3'],
        ]);

        $this->assertTrue($this->sesiones->registrarSesion());

        $sesion = $this->pdo->query('SELECT * FROM sesiones')->fetch(PDO::FETCH_ASSOC);
        $this->assertSame('Planificada', $sesion['estado']);
        $this->assertSame(1000, (int)$sesion['volumen_planificado']); // 200+800

        $series = $this->pdo->query('SELECT * FROM series_sesion ORDER BY orden_ejecucion')->fetchAll(PDO::FETCH_ASSOC);
        $this->assertCount(2, $series);
        $this->assertSame('Calentamiento', $series[0]['bloque']);
        $this->assertSame(1, (int)$series[0]['orden_ejecucion']);
    }

    /** @test */
    public function iniciar_sesion_solo_cambia_si_estaba_planificada(): void
    {
        $this->pdo->exec("INSERT INTO sesiones (id_grupo, id_entrenador, fecha, tipo_sesion, estado)
                          VALUES (1, 7, '2030-01-01', 'Tecnica', 'Planificada')");
        $id = (int)$this->pdo->lastInsertId();

        $this->sesiones->setDatos(['id_sesion' => $id]);
        $this->assertTrue($this->sesiones->InicializarSesion());

        // Segundo intento: ya no está Planificada → debe fallar
        $this->sesiones->setDatos(['id_sesion' => $id]);
        $this->assertFalse($this->sesiones->InicializarSesion(), 'Fix #1: debe retornar false si ya no está Planificada');
    }

    /** @test */
    public function cancelar_sesion_completada_debe_fallar(): void
    {
        $this->pdo->exec("INSERT INTO sesiones (id_grupo, id_entrenador, fecha, tipo_sesion, estado)
                          VALUES (1, 7, '2030-01-01', 'Tecnica', 'Completada')");
        $id = (int)$this->pdo->lastInsertId();

        $this->sesiones->setDatos(['id_sesion' => $id]);
        $this->assertFalse($this->sesiones->cancelarSesion(), 'Fix #2: no debería cancelar una sesión completada');
    }

    /** @test */
    public function completar_sesion_con_volumen_superior_falla(): void
    {
        $this->pdo->exec("INSERT INTO sesiones (id_grupo, id_entrenador, fecha, tipo_sesion, volumen_planificado, estado)
                          VALUES (1, 7, '2030-01-01', 'Tecnica', 1000, 'Parcial')");
        $id = (int)$this->pdo->lastInsertId();

        $this->sesiones->setDatos(['id_sesion' => $id, 'volumen_ejecutado' => 1500]);
        $this->assertFalse($this->sesiones->completarSesion());
    }

    /** @test */
    public function completar_sesion_valida_cambia_estado(): void
    {
        $this->pdo->exec("INSERT INTO sesiones (id_grupo, id_entrenador, fecha, tipo_sesion, volumen_planificado, estado)
                          VALUES (1, 7, '2030-01-01', 'Tecnica', 1000, 'Parcial')");
        $id = (int)$this->pdo->lastInsertId();

        $this->sesiones->setDatos([
            'id_sesion'         => $id,
            'volumen_ejecutado' => 900,
            'estado'            => 'Completada',
            'observaciones'     => 'Buena sesión',
        ]);
        $this->assertTrue($this->sesiones->completarSesion());

        $estado = $this->pdo->query("SELECT estado FROM sesiones WHERE id_sesion = {$id}")->fetchColumn();
        $this->assertSame('Completada', $estado);
    }

    /** @test */
    public function completar_sesion_ignora_estado_no_permitido(): void
    {
        $this->pdo->exec("INSERT INTO sesiones (id_grupo, id_entrenador, fecha, tipo_sesion, volumen_planificado, estado)
                          VALUES (1, 7, '2030-01-01', 'Tecnica', 1000, 'Parcial')");
        $id = (int)$this->pdo->lastInsertId();

        // Intento de "revertir" el estado a Planificada
        $this->sesiones->setDatos([
            'id_sesion'         => $id,
            'volumen_ejecutado' => 900,
            'estado'            => 'Planificada',
        ]);
        $this->sesiones->completarSesion();

        $estado = $this->pdo->query("SELECT estado FROM sesiones WHERE id_sesion = {$id}")->fetchColumn();
        $this->assertSame('Completada', $estado, 'Fix #3: estado debe forzarse a Completada si se envía uno inválido');
    }
}
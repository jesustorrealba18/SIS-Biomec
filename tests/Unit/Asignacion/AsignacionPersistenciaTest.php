<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use GrupoProyecto\SisBiomec\modelo\Asignacion;

class AsignacionPersistenciaTest extends TestCase
{
    private \PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

        // Emulamos funciones de MySQL
        $this->pdo->sqliteCreateFunction('NOW', function () { return date('Y-m-d H:i:s'); }, 0);
        $this->pdo->sqliteCreateFunction('CURDATE', function () { return date('Y-m-d'); }, 0);
        $this->pdo->sqliteCreateFunction('CONCAT', function (...$args) { return implode('', $args); }, -1);
        $this->pdo->sqliteCreateFunction('FIELD', function (...$args) {
            $valor = array_shift($args);
            $indice = array_search($valor, $args);
            return $indice !== false ? $indice + 1 : 0;
        }, -1);

        // Creamos las tablas con las columnas que usa el modelo
        $this->pdo->exec("
            CREATE TABLE asignacion_carril (
                id_asignacion INTEGER PRIMARY KEY AUTOINCREMENT,
                id_carril INTEGER,
                id_bloque_horario INTEGER,
                id_grupo INTEGER,
                dia_especifico TEXT,
                fecha_vigencia_inicio TEXT,
                fecha_vigencia_fin TEXT,
                activa INTEGER DEFAULT 1,
                fecha_completacion TEXT,
                estado TEXT
            );
            CREATE TABLE carriles (id_carril INTEGER PRIMARY KEY, numero INTEGER, capacidad_maxima INTEGER, activo INTEGER);
            CREATE TABLE bloques_horarios (id_bloque INTEGER PRIMARY KEY, dia_semana TEXT, hora_inicio TEXT, hora_fin TEXT);
            CREATE TABLE grupos_entrenamiento (id_grupo INTEGER PRIMARY KEY, nombre TEXT, descripcion TEXT, activo INTEGER, id_entrenador INTEGER);
            CREATE TABLE entrenador (id_entrenador INTEGER PRIMARY KEY, nombres TEXT, apellidos TEXT, cedula TEXT);
            
            INSERT INTO carriles (id_carril, numero, activo) VALUES (1, 10, 1);
            INSERT INTO bloques_horarios (id_bloque, dia_semana, hora_inicio, hora_fin) VALUES (1, 'Lunes', '08:00', '10:00');
            INSERT INTO grupos_entrenamiento (id_grupo, nombre, activo) VALUES (1, 'Grupo Test', 1);
        ");
    }

    private function asignacion(): Asignacion
    {
        $obj = new Asignacion();
        $ref = new \ReflectionClass(\GrupoProyecto\SisBiomec\modelo\Conexion::class);
        $prop = $ref->getProperty('conex1');
        $prop->setAccessible(true);
        $prop->setValue($obj, $this->pdo);
        return $obj;
    }

    public function testRegistrarAsignacion(): void
    {
        $a = $this->asignacion();
        $a->setDatos([
            'id_carril' => '1',
            'id_bloque_horario' => '1',
            'id_grupo' => '1',
            'dia_especifico' => 'Lunes',
            'fecha_vigencia_inicio' => date('Y-m-d'),
            'fecha_vigencia_fin' => date('Y-m-d', strtotime('+1 month')),
            'activa' => '1'
        ]);

        $this->assertTrue($a->registrarAsignacion());
        
        $row = $this->pdo->query("SELECT * FROM asignacion_carril")->fetch();
        $this->assertSame(1, (int)$row['id_carril']);
        $this->assertSame('Lunes', $row['dia_especifico']);
    }

    public function testEditarAsignacion(): void
    {
        $this->pdo->exec("INSERT INTO asignacion_carril (id_asignacion, id_carril, id_bloque_horario, id_grupo, activa) VALUES (1, 1, 1, 1, 1)");
        
        $a = $this->asignacion();
        $a->setDatos([
            'id_asignacion' => '1',
            'id_carril' => '1',
            'id_bloque_horario' => '1',
            'id_grupo' => '1',
            'dia_especifico' => 'Martes', // Cambiamos el día
            'fecha_vigencia_inicio' => date('Y-m-d'),
            'activa' => '1'
        ]);

        $this->assertTrue($a->editarAsignacion());
        $dia = $this->pdo->query("SELECT dia_especifico FROM asignacion_carril WHERE id_asignacion=1")->fetchColumn();
        $this->assertSame('Martes', $dia);
    }

    public function testDesactivarYReactivarAsignacion(): void
    {
        $this->pdo->exec("INSERT INTO asignacion_carril (id_asignacion, id_carril, activa) VALUES (1, 1, 1)");
        $a = $this->asignacion();

        // Desactivar
        $this->assertTrue($a->desactivarAsignacion(1));
        $this->assertSame(0, (int)$this->pdo->query("SELECT activa FROM asignacion_carril WHERE id_asignacion=1")->fetchColumn());

        // Reactivar
        $this->assertTrue($a->reactivarAsignacion(1));
        $this->assertSame(1, (int)$this->pdo->query("SELECT activa FROM asignacion_carril WHERE id_asignacion=1")->fetchColumn());
    }

    public function testCompletarAsignacion(): void
    {
        $this->pdo->exec("INSERT INTO asignacion_carril (id_asignacion, id_carril, id_grupo, activa, estado) VALUES (1, 1, 1, 1, 'pendiente')");
        $a = $this->asignacion();

        $this->assertTrue($a->completarAsignacion(1));
        $row = $this->pdo->query("SELECT activa, estado FROM asignacion_carril WHERE id_asignacion=1")->fetch();
        $this->assertSame(0, (int)$row['activa']);
        $this->assertSame('completada', $row['estado']);
    }

    public function testVerificarAsignacionesVencidas(): void
    {
        // Insertamos una asignación que ya venció ayer
        $ayer = date('Y-m-d', strtotime('-1 day'));
        $this->pdo->exec("INSERT INTO asignacion_carril (id_asignacion, id_grupo, activa, fecha_vigencia_fin, estado) 
                          VALUES (1, 1, 1, '$ayer', 'pendiente')");
        
        $a = $this->asignacion();
        $completadas = $a->verificarAsignacionesVencidas();

        $this->assertSame(1, $completadas);
        $estado = $this->pdo->query("SELECT estado FROM asignacion_carril WHERE id_asignacion=1")->fetchColumn();
        $this->assertSame('completada', $estado);
    }
}
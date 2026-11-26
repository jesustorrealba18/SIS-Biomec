<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use GrupoProyecto\SisBiomec\modelo\Grupo;

class GrupoListarTest extends TestCase
{
    private \PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

        $this->pdo->sqliteCreateFunction('CONCAT', function (...$args) { 
            return implode('', $args); 
        }, -1);

        $this->pdo->exec("
            CREATE TABLE grupos_entrenamiento (
                id_grupo INTEGER PRIMARY KEY, 
                nombre TEXT, 
                descripcion TEXT, 
                id_entrenador INTEGER, 
                activo INTEGER
            );
            CREATE TABLE entrenador (
                id_entrenador INTEGER PRIMARY KEY, 
                nombres TEXT, 
                apellidos TEXT, 
                cedula TEXT
            );
            CREATE TABLE grupo_atleta (
                id_grupo INTEGER, 
                id_atleta INTEGER
            );
            
            -- Insertamos datos de prueba
            INSERT INTO grupos_entrenamiento (id_grupo, nombre, activo) VALUES (1, 'Grupo A', 1), (2, 'Grupo B', 0);
            INSERT INTO entrenador (id_entrenador, nombres, apellidos, cedula) VALUES (1, 'Juan', 'Perez', '123');
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

    public function testListarGruposActivos(): void
    {
        $lista = $this->grupo()->listarGrupos(1);
        
        $this->assertCount(1, $lista);
        $this->assertSame('Grupo A', $lista[0]['nombre']);
    }

    public function testListarEntrenadoresDisponibles(): void
    {
        $entrenadores = $this->grupo()->listarEntrenadoresDisponibles();
        
        $this->assertCount(1, $entrenadores);
        $this->assertSame('Juan', $entrenadores[0]['nombres']);
    }
}
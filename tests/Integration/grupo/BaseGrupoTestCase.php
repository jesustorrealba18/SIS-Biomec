<?php
namespace Tests\Integration\grupo;

use Tests\Integration\sesiones\BaseDatabaseTestCase;
use PDO;
use PDOException;

abstract class BaseGrupoTestCase extends BaseDatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->db->exec('SET FOREIGN_KEY_CHECKS = 0');
        $tablas = ['grupo_atleta', 'grupos_entrenamiento', 'atletas', 'categorias_feveda', 'entrenador'];
        foreach ($tablas as $tabla) {
            try {
                $this->db->exec("TRUNCATE TABLE {$tabla}");
            } catch (\PDOException $e) {}
        }
        $this->db->exec('SET FOREIGN_KEY_CHECKS = 1');

        $this->sembrarDatosGrupo();
    }

    protected function sembrarDatosGrupo(): void
    {
        try {
            $this->db->exec("INSERT INTO entrenador (
                id_entrenador, cedula, nombres, apellidos, fecha_nacimiento, genero, correo, telefono, direccion, estado
            ) VALUES (
                1, 'V-12345678', 'Ana', 'Pérez', '1990-01-01', 'F', 'ana@test.com', '04141234567', 'Calle Falsa 123', 'Activo'
            )");
        } catch (PDOException $e) {
            $this->fail("Error insertando entrenador: " . $e->getMessage());
        }

        try {
            $this->db->exec("INSERT INTO categorias_feveda (id_categoria, nombre, edad_minima, edad_maxima, activa) 
                             VALUES (1, 'Infantil', 8, 10, 1)");
        } catch (PDOException $e) {
            $this->fail("Error insertando categoría: " . $e->getMessage());
        }

        try {
            $this->db->exec("INSERT INTO atletas (
                id_atleta, cedula, nombres, apellidos, fecha_nacimiento, correo, telefono, direccion, estado, id_categoria
            ) VALUES 
            (1, 'V-11111111', 'Pedro', 'Gómez', '2015-05-05', 'pedro@test.com', '04141111111', 'Calle 1', 1, 1),
            (2, 'V-22222222', 'María', 'López', '2014-06-06', 'maria@test.com', '04142222222', 'Calle 2', 1, 1),
            (3, 'V-33333333', 'Luis', 'Martínez', '2013-07-07', 'luis@test.com', '04143333333', 'Calle 3', 1, 1)");
        } catch (PDOException $e) {
            $this->fail("Error insertando atletas: " . $e->getMessage());
        }
    }

    protected function crearGrupoPersistido(string $nombre = 'Grupo Test'): int
    {
        $this->db->exec("INSERT INTO grupos_entrenamiento (nombre, descripcion, id_entrenador, activo) 
                         VALUES ('{$nombre}', 'Descripción de prueba', 1, 1)");
        return (int)$this->db->lastInsertId();
    }

    protected function estadoGrupoDe(int $id): int
    {
        return (int)$this->db->query("SELECT activo FROM grupos_entrenamiento WHERE id_grupo = {$id}")->fetchColumn();
    }
}
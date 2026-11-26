<?php
namespace Tests\Integration\asignacion;

use Tests\Integration\sesiones\BaseDatabaseTestCase;
use PDO;
use PDOException;

abstract class BaseAsignacionTestCase extends BaseDatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->db->exec('SET FOREIGN_KEY_CHECKS = 0');
        $tablas = ['asignacion_carril', 'bloques_horarios', 'carriles', 'grupos_entrenamiento', 'entrenador'];
        foreach ($tablas as $tabla) {
            try {
                $this->db->exec("TRUNCATE TABLE {$tabla}");
            } catch (\PDOException $e) {}
        }
        $this->db->exec('SET FOREIGN_KEY_CHECKS = 1');

        $this->sembrarDatosBaseAsignacion();
    }

    protected function sembrarDatosBaseAsignacion(): void
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
            $this->db->exec("INSERT INTO grupos_entrenamiento (id_grupo, nombre, descripcion, id_entrenador, activo) 
                             VALUES (1, 'Grupo Test', 'Descripción test', 1, 1)");
        } catch (PDOException $e) {
            $this->fail("Error insertando grupo: " . $e->getMessage());
        }

        try {
            $this->db->exec("INSERT INTO carriles (id_carril, numero, capacidad_maxima, activo) 
                             VALUES (1, 1, 10, 1)");
            $this->db->exec("INSERT INTO carriles (id_carril, numero, capacidad_maxima, activo) 
                             VALUES (2, 2, 10, 1)");
        } catch (PDOException $e) {
            $this->fail("Error insertando carriles: " . $e->getMessage());
        }

        try {
            $this->db->exec("INSERT INTO bloques_horarios (id_bloque, dia_semana, hora_inicio, hora_fin) 
                             VALUES (1, 'Lunes', '08:00:00', '10:00:00')");
            $this->db->exec("INSERT INTO bloques_horarios (id_bloque, dia_semana, hora_inicio, hora_fin) 
                             VALUES (2, 'Martes', '10:00:00', '12:00:00')");
        } catch (PDOException $e) {
            $this->fail("Error insertando bloques horarios: " . $e->getMessage());
        }
    }

    protected function crearAsignacionPersistida(array $overrides = []): int
    {
        $datos = array_merge([
            'id_carril'             => 1,
            'id_bloque_horario'     => 1,
            'id_grupo'              => 1,
            'fecha_vigencia_inicio' => date('Y-m-d'),
            'fecha_vigencia_fin'    => date('Y-m-d', strtotime('+1 month')),
            'activa'                => 1,
        ], $overrides);

        $sql = "INSERT INTO asignacion_carril (id_carril, id_bloque_horario, id_grupo, fecha_vigencia_inicio, fecha_vigencia_fin, activa) 
                VALUES (:id_carril, :id_bloque_horario, :id_grupo, :fecha_vigencia_inicio, :fecha_vigencia_fin, :activa)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($datos);
        return (int)$this->db->lastInsertId();
    }
}
<?php
namespace Tests\Integration\sesiones;

use PHPUnit\Framework\TestCase;
use PDO;

if (!class_exists('GrupoProyecto\SisBiomec\modelo\Notificacion', false)) {
    eval('
        namespace GrupoProyecto\SisBiomec\modelo; 
        class Notificacion {
            public static function __callStatic($name, $args) { return true; }
            public static function enviar($id, $titulo, $msg, $icono, $color, $url) { return true; }
            public static function obtenerIdUsuarioPorCorreo($correo) { return 1; }
        }
    ');
}

abstract class BaseDatabaseTestCase extends TestCase
{
    protected PDO $db;

    protected function setUp(): void
    {
        parent::setUp();

        $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $user = $_ENV['DB_USER'] ?? 'root';
        $pass = $_ENV['DB_PASS'] ?? '';
        $name = $_ENV['DB_NAME_NATACION'] ?? 'sis_natacion_test';

        if ($name === 'sis_natacion') {
            $this->fail('¡Peligro! Estás apuntando a la BD real. Revisa .env.test');
        }

        $this->db = new PDO(
            "mysql:host={$host};dbname={$name};charset=utf8mb4",
            $user,
            $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $this->limpiarTablas();
        $this->sembrarDatosBase();
    }

    private function limpiarTablas(): void
    {
        $this->db->exec('SET FOREIGN_KEY_CHECKS = 0');
        $tablas = ['series_sesion', 'sesiones'];
        foreach ($tablas as $tabla) {
            try {
                $this->db->exec("TRUNCATE TABLE {$tabla}");
            } catch (\PDOException $e) {}
        }
        $this->db->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    protected function sembrarDatosBase(): void
    {
        try {
            $this->db->exec("INSERT INTO entrenador (
                id_entrenador, cedula, nombres, apellidos, fecha_nacimiento, genero, correo, telefono, direccion, estado
            ) VALUES (
                1, 'V-12345678', 'Ana', 'Pérez', '1990-01-01', 'F', 'ana@test.com', '04141234567', 'Calle Falsa 123', 'Activo'
            )");
        } catch (\PDOException $e) {}

        try {
            $this->db->exec("INSERT INTO grupos_entrenamiento (id_grupo, nombre, descripcion, id_entrenador, activo)
                             VALUES (1, 'Grupo Test', 'Descripción test', 1, 1)");
        } catch (\PDOException $e) {}

        try {
            $this->db->exec("INSERT INTO microciclos (id_microciclo, nombre) VALUES (1, 'Micro A')");
        } catch (\PDOException $e) {}

        try {
            $this->db->exec("INSERT INTO drills (
                id_drill, nombre, estilo, categoria, enfoque_tecnico, descripcion, instrucciones, metraje_sugerido, dificultad, material_requerido, activo
            ) VALUES (
                1, 'Drill Test', 'Libre', 'Tecnico', 'Enfoque de prueba', 'Descripción de prueba', 'Instrucciones de prueba', '50m', 'Basico', 'Ninguno', 1
            )");
        } catch (\PDOException $e) {}
    }

    protected function payloadSesionValida(array $overrides = []): array
    {
        return array_merge([
            'id_entrenador'    => 1,
            'id_grupo'         => 1,
            'id_microciclo'    => null, // Permitimos nulo
            'fecha'            => date('Y-m-d', strtotime('+30 days')), // Forzamos a 30 días para evitar el bloqueo de fecha
            'tipo_sesion'      => 'Tecnica', // Sin tilde
            'duracion_minutos' => 90,
            'calentamiento'    => '200m libre suave',
            'vuelta_calma'     => '100m espalda',
            'observaciones'    => 'Sesión de prueba',
        ], $overrides);
    }

    protected function serieValida(array $overrides = []): array
    {
        return array_merge([
            'bloque'                => 'Principal',
            'id_drill'              => 1,
            'ejercicio_descripcion' => '',
            'repeticiones'          => 4,
            'distancia_m'           => 100,
            'descanso_seg'          => 30,
            'zona_intensidad'       => 'Z3',
            'ritmo_objetivo'        => '1:30',
        ], $overrides);
    }
}
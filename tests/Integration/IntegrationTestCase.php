<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use PDO;

/**
 * Clase base de las pruebas de integracion.
 *
 * IMPORTANTE: estas pruebas corren contra la base de datos real de desarrollo
 * (sis_natacion y sis_seguridad). Para dejar la BD como estaba:
 * - Todo dato de prueba se marca con el prefijo TESTPRUEBA (correo/nombre).
 * - tearDown() elimina SIEMPRE los datos creados, en orden inverso de FK.
 * - La bitacora de auditoria si registra las acciones (comportamiento
 *   esperado del sistema) y se limpia por CASCADE al borrar el usuario.
 */
abstract class IntegrationTestCase extends TestCase
{
    protected PDO $pdoNatacion;
    protected PDO $pdoSeguridad;

    protected function setUp(): void
    {
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $usuario = $_ENV['DB_USER'] ?? 'root';
        $clave = $_ENV['DB_PASS'] ?? '';
        $opciones = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

        $this->pdoNatacion = new PDO(
            sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $host, $_ENV['DB_NAME_NATACION'] ?? 'sis_natacion'),
            $usuario,
            $clave,
            $opciones
        );
        $this->pdoSeguridad = new PDO(
            sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $host, $_ENV['DB_NAME_SEGURIDAD'] ?? 'sis_seguridad'),
            $usuario,
            $clave,
            $opciones
        );
    }

    protected function tearDown(): void
    {
        $this->limpiarDatosDePrueba();
        unset($this->pdoNatacion, $this->pdoSeguridad);
    }

    /**
     * Elimina todos los datos marcados con prefijo TESTPRUEBA.
     * Se ejecuta en cada tearDown para no dejar residuos en la BD de desarrollo.
     */
    protected function limpiarDatosDePrueba(): void
    {
        try {
            $idsUsuarios = $this->columna($this->pdoSeguridad, "SELECT id_usuario FROM usuarios WHERE correo LIKE 'TESTPRUEBA%'");
            $this->borrarPorLista($this->pdoSeguridad, 'notificaciones', 'id_usuario', $idsUsuarios);
            $this->borrarPorLista($this->pdoSeguridad, 'usuario_roles', 'id_usuario', $idsUsuarios);
            $this->borrarPorLista($this->pdoSeguridad, 'usuarios', 'id_usuario', $idsUsuarios);

            $idsEventos = $this->columna($this->pdoNatacion, "SELECT id_evento FROM eventos WHERE nombre LIKE 'TESTPRUEBA%'");
            $this->borrarPorLista($this->pdoNatacion, 'metas_competitivas', 'id_evento', $idsEventos);
            $this->borrarPorLista($this->pdoNatacion, 'evento_inscripcion', 'id_evento', $idsEventos);
            $this->borrarPorLista($this->pdoNatacion, 'tiempos_corte_evento', 'id_evento', $idsEventos);
            $this->borrarPorLista($this->pdoNatacion, 'eventos', 'id_evento', $idsEventos);

            $idsAtletas = $this->columna($this->pdoNatacion, "SELECT id_atleta FROM atletas WHERE correo LIKE 'TESTPRUEBA%'");
            $this->borrarPorLista($this->pdoNatacion, 'metas_competitivas', 'id_atleta', $idsAtletas);
            $this->borrarPorLista($this->pdoNatacion, 'evento_inscripcion', 'id_atleta', $idsAtletas);
            $this->borrarPorLista($this->pdoNatacion, 'atleta_datos_medicos', 'id_atleta', $idsAtletas);
            $this->borrarPorLista($this->pdoNatacion, 'atletas', 'id_atleta', $idsAtletas);
        } catch (\Throwable $e) {
            fwrite(STDERR, 'AVISO (limpieza de pruebas): ' . $e->getMessage() . PHP_EOL);
        }
    }

    protected function columna(PDO $pdo, string $sql): array
    {
        return $pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN);
    }

    protected function borrarPorLista(PDO $pdo, string $tabla, string $columna, array $valores): void
    {
        $valores = array_values(array_filter($valores, fn ($v) => $v !== null && $v !== ''));
        if ($valores === []) {
            return;
        }
        $placeholders = implode(',', array_fill(0, count($valores), '?'));
        $pdo->prepare("DELETE FROM {$tabla} WHERE {$columna} IN ({$placeholders})")->execute($valores);
    }

    protected function correoPrueba(): string
    {
        return 'TESTPRUEBA' . uniqid() . '@test.local';
    }

    protected function cedulaPrueba(): string
    {
        return 'V-' . random_int(90000000, 99999999);
    }

    protected function nombreEventoPrueba(): string
    {
        return 'TESTPRUEBA Evento ' . uniqid();
    }

    protected function crearUsuarioPrueba(): int
    {
        $sentencia = $this->pdoSeguridad->prepare(
            "INSERT INTO usuarios (nombres, apellidos, correo, contrasena_hash) VALUES ('TESTPRUEBA', 'INTEGRACION', ?, ?)"
        );
        $sentencia->execute([$this->correoPrueba(), password_hash('Clave123', PASSWORD_BCRYPT)]);
        return (int)$this->pdoSeguridad->lastInsertId();
    }

    protected function crearAtletaPrueba(): int
    {
        $idCategoria = (int)$this->pdoNatacion->query(
            "SELECT id_categoria FROM categorias_feveda WHERE activa = 1 ORDER BY edad_minima DESC LIMIT 1"
        )->fetchColumn();
        if ($idCategoria <= 0) {
            $this->markTestSkipped('No hay categorias FEVEDA activas para asociar atletas de prueba.');
        }
        $sentencia = $this->pdoNatacion->prepare(
            "INSERT INTO atletas (cedula, nombres, apellidos, fecha_nacimiento, sexo, estado, id_categoria, correo)
             VALUES (?, 'TESTPRUEBA', 'ATLETA', '2000-03-15', 'M', 'Activo', ?, ?)"
        );
        $sentencia->execute([$this->cedulaPrueba(), $idCategoria, $this->correoPrueba()]);
        return (int)$this->pdoNatacion->lastInsertId();
    }
}

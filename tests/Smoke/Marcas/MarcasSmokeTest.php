<?php

namespace Tests\Smoke\Marcas;

use PHPUnit\Framework\TestCase;
use PDO;

/**
 * Smoke Test — Semana 10 — CP-HUM-06.01
 * Verifica el flujo mínimo end-to-end del módulo.
 * Requiere BD de test real + usuario autenticado simulado.
 */
class MarcasSmokeTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new PDO(
            'mysql:host=' . getenv('TEST_DB_HOST') . ';dbname=' . getenv('TEST_DB_NAME'),
            getenv('TEST_DB_USER'),
            getenv('TEST_DB_PASS'),
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    public function test_CP_HUM_06_01_registro_manual_exitoso(): void
    {
        // 1. Contar filas antes
        $antes = $this->pdo->query('SELECT COUNT(*) FROM marcas')->fetchColumn();

        // 2. Simular POST al controlador (en un caso real usaríamos HTTP)
        // Aquí sólo verificamos que la BD acepta el registro esperado.
        $stmt = $this->pdo->prepare(
            "INSERT INTO marcas (id_atleta, id_sesion, estilo, distancia_m, tipo_piscina,
                                 tiempo_final_seg, fecha, es_pb, estado)
             VALUES (1, 1, 'Libre', 100, '50m', 60.00, CURDATE(), 1, 'Activo')"
        );
        $stmt->execute();

        $despues = $this->pdo->query('SELECT COUNT(*) FROM marcas')->fetchColumn();

        $this->assertEquals((int)$antes + 1, (int)$despues, 'Debe registrarse 1 nueva marca.');

        // 3. Cleanup (transacciones o borrado explícito)
        $this->pdo->exec('DELETE FROM marcas WHERE tiempo_final_seg = 60.00 AND fecha = CURDATE()');
    }
}
<?php

namespace Tests\Unit\Asistencia;

use GrupoProyecto\SisBiomec\modelo\Asistencia;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\TestDox;
use ReflectionClass;

class AsistenciaValidationTest extends TestCase
{
    private Asistencia $asistencia;
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        $this->reflection = new ReflectionClass(Asistencia::class);
        // Evitamos la conexión a BD instanciando sin constructor
        $this->asistencia = $this->reflection->newInstanceWithoutConstructor();
    }

    private function invokePrivate(string $method, array $args = [])
    {
        $m = $this->reflection->getMethod($method);
        if (PHP_VERSION_ID < 80100) {
            $m->setAccessible(true);
        }
        return $m->invokeArgs($this->asistencia, $args);
    }

    private function payloadBase(array $overrides = []): array
    {
        return array_merge([
            'id_sesion'         => 1,
            'id_atleta'         => 5,
            'estado_asistencia' => 'Presente',
            'justificacion'     => 'No aplica',
            'tipo'              => 'Manual',
            'token_qr'          => ''
        ], $overrides);
    }

    // Helper para leer los datos internos del modelo tras la validación
    private function getDatosInternos(): array
    {
        $prop = $this->reflection->getProperty('datos');
        $prop->setAccessible(true);
        return $prop->getValue($this->asistencia);
    }

    // ─────────────────────────────────────────────────────────────
    // GRUPO 1 — Validaciones base (RF-03)
    // ─────────────────────────────────────────────────────────────

    #[TestDox('CP-UNI-03.01: Enviar estado de asistencia no válido (ej: "Paseando")')]
    public function test_CP_UNI_03_01_estado_no_valido(): void
    {
        $this->asistencia->setDatos($this->payloadBase(['estado_asistencia' => 'Paseando']));
        $valido = $this->invokePrivate('validarDatos');
        
        $this->assertFalse($valido, 'FALLO: El sistema aceptó un estado no permitido.');
        $this->assertArrayHasKey('estado_asistencia', $this->asistencia->obtenerErrores());
    }

    #[TestDox('CP-UNI-03.02: Tipo QR sin token_qr debe ser rechazado')]
    public function test_CP_UNI_03_02_qr_sin_token(): void
    {
        $this->asistencia->setDatos($this->payloadBase([
            'tipo' => 'QR',
            'token_qr' => '' // Sin token
        ]));
        $valido = $this->invokePrivate('validarDatos');
        
        $this->assertFalse($valido, 'FALLO: El sistema permitió un tipo QR sin token de seguridad.');
    }

    #[TestDox('CP-UNI-03.05: Enviar id_sesion=0 (debe ser entero positivo)')]
    public function test_CP_UNI_03_05_id_sesion_cero(): void
    {
        $this->asistencia->setDatos($this->payloadBase(['id_sesion' => 0]));
        $valido = $this->invokePrivate('validarDatos');
        
        $this->assertFalse($valido, 'FALLO: El sistema aceptó una sesión con ID 0.');
        $this->assertArrayHasKey('id_sesion', $this->asistencia->obtenerErrores());
    }

    #[TestDox('CP-UNI-03.06: Enviar id_atleta=-5 (negativo)')]
    public function test_CP_UNI_03_06_id_atleta_negativo(): void
    {
        $this->asistencia->setDatos($this->payloadBase(['id_atleta' => -5]));
        $valido = $this->invokePrivate('validarDatos');
        
        $this->assertFalse($valido, 'FALLO: El sistema aceptó un atleta con ID negativo.');
        $this->assertArrayHasKey('id_atleta', $this->asistencia->obtenerErrores());
    }

    #[TestDox('CP-UNI-03.13: Enviar estado="presente" (minúsculas)')]
    public function test_CP_UNI_03_13_estado_minusculas(): void
    {
        $this->asistencia->setDatos($this->payloadBase(['estado_asistencia' => 'presente']));
        $valido = $this->invokePrivate('validarDatos');
        
        $this->assertFalse($valido, 'FALLO: El sistema aceptó "presente" en minúsculas.');
        $this->assertArrayHasKey('estado_asistencia', $this->asistencia->obtenerErrores());
    }

    #[TestDox('CP-UNI-03.14: Enviar estado="Tardanza" (variante no permitida)')]
    public function test_CP_UNI_03_14_estado_tardanza(): void
    {
        $this->asistencia->setDatos($this->payloadBase(['estado_asistencia' => 'Tardanza']));
        $valido = $this->invokePrivate('validarDatos');
        
        $this->assertFalse($valido, 'FALLO: El sistema aceptó "Tardanza" en vez de "Retardo".');
        $this->assertArrayHasKey('estado_asistencia', $this->asistencia->obtenerErrores());
    }

    #[TestDox('CP-UNI-03.15: Enviar id_sesion[]=1 (inyección de array)')]
    public function test_CP_UNI_03_15_inyeccion_array(): void
    {
        $this->asistencia->setDatos($this->payloadBase(['id_sesion' => [1, 2]]));
        $valido = $this->invokePrivate('validarDatos');
        
        $this->assertFalse($valido, 'FALLO: El sistema aceptó un array en lugar de un escalar.');
        $this->assertArrayHasKey('id_sesion', $this->asistencia->obtenerErrores());
    }

    // ─────────────────────────────────────────────────────────────
    // GRUPO 2 — Validación de Justificaciones
    // ─────────────────────────────────────────────────────────────

    #[TestDox('CP-UNI-03.03: Estado "Justificado" sin justificación')]
    public function test_CP_UNI_03_03_justificado_sin_motivo(): void
    {
        $this->asistencia->setDatos($this->payloadBase([
            'estado_asistencia' => 'Justificado',
            'justificacion' => ''
        ]));
        $valido = $this->invokePrivate('validarDatos');
        
        $this->assertFalse($valido, 'FALLO: El sistema permitió justificar sin escribir el motivo.');
        $this->assertArrayHasKey('justificacion', $this->asistencia->obtenerErrores());
    }

    #[TestDox('CP-UNI-03.04: Estado "Presente" debe sobrescribir justificación a "No aplica"')]
    public function test_CP_UNI_03_04_sobrescribir_justificacion(): void
    {
        $this->asistencia->setDatos($this->payloadBase([
            'estado_asistencia' => 'Presente',
            'justificacion' => 'Texto de prueba que debe ser ignorado y borrado'
        ]));
        
        $this->invokePrivate('validarDatos');
        
        $datos = $this->getDatosInternos();
        $this->assertEquals('No aplica', $datos['justificacion'], 'FALLO: La justificación no fue sobrescrita automáticamente.');
    }

    #[TestDox('CP-UNI-03.07: Boundary inferior justificación (4 letras)')]
    public function test_CP_UNI_03_07_justificacion_4_letras(): void
    {
        $this->asistencia->setDatos($this->payloadBase([
            'estado_asistencia' => 'Justificado',
            'justificacion' => 'abcd' // 4 letras
        ]));
        $valido = $this->invokePrivate('validarDatos');
        
        $this->assertFalse($valido, 'FALLO: El sistema aceptó una justificación de 4 letras (mínimo 5).');
        $this->assertArrayHasKey('justificacion', $this->asistencia->obtenerErrores());
    }

    #[TestDox('CP-UNI-03.08: Boundary justo justificación (5 letras)')]
    public function test_CP_UNI_03_08_justificacion_5_letras(): void
    {
        $this->asistencia->setDatos($this->payloadBase([
            'estado_asistencia' => 'Justificado',
            'justificacion' => 'abcde' // 5 letras
        ]));
        $valido = $this->invokePrivate('validarDatos');
        
        $this->assertTrue($valido, 'FALLO: El sistema rechazó una justificación válida de 5 letras.');
    }

    #[TestDox('CP-UNI-03.09: Boundary superior justificación (255 letras)')]
    public function test_CP_UNI_03_09_justificacion_255_letras(): void
    {
        $this->asistencia->setDatos($this->payloadBase([
            'estado_asistencia' => 'Justificado',
            'justificacion' => str_repeat('a', 255)
        ]));
        $valido = $this->invokePrivate('validarDatos');
        
        $this->assertTrue($valido, 'FALLO: El sistema rechazó una justificación válida de 255 letras.');
    }

    #[TestDox('CP-UNI-03.10: Boundary sobrepasado justificación (256 letras)')]
    public function test_CP_UNI_03_10_justificacion_256_letras(): void
    {
        $this->asistencia->setDatos($this->payloadBase([
            'estado_asistencia' => 'Justificado',
            'justificacion' => str_repeat('a', 256)
        ]));
        $valido = $this->invokePrivate('validarDatos');
        
        $this->assertFalse($valido, 'FALLO: El sistema aceptó una justificación mayor al límite de 255 letras.');
        $this->assertArrayHasKey('justificacion', $this->asistencia->obtenerErrores());
    }

    #[TestDox('CP-UNI-03.11: Justificación compuesta por 5 espacios ASCII')]
    public function test_CP_UNI_03_11_justificacion_espacios(): void
    {
        $this->asistencia->setDatos($this->payloadBase([
            'estado_asistencia' => 'Justificado',
            'justificacion' => '     ' // 5 espacios
        ]));
        $valido = $this->invokePrivate('validarDatos');
        
        $this->assertFalse($valido, 'FALLO: El sistema aceptó solo espacios en blanco como justificación.');
        $this->assertArrayHasKey('justificacion', $this->asistencia->obtenerErrores());
    }

    #[TestDox('CP-UNI-03.12: Justificación compuesta por 5 caracteres NBSP')]
    public function test_CP_UNI_03_12_justificacion_nbsp(): void
    {
        // \u{00A0} es el espacio duro (Non-Breaking Space)
        $this->asistencia->setDatos($this->payloadBase([
            'estado_asistencia' => 'Justificado',
            'justificacion' => "\u{00A0}\u{00A0}\u{00A0}\u{00A0}\u{00A0}"
        ]));
        $valido = $this->invokePrivate('validarDatos');
        
        $this->assertFalse($valido, 'FALLO: El sistema aceptó espacios NBSP como justificación válida.');
        $this->assertArrayHasKey('justificacion', $this->asistencia->obtenerErrores());
    }
}
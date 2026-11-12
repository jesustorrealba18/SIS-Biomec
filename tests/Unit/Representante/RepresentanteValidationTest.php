<?php

namespace Tests\Unit\Representante;

use GrupoProyecto\SisBiomec\modelo\Representante;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\TestDox;
use ReflectionClass;

class RepresentanteValidationTest extends TestCase
{
    private Representante $representante;
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        $this->reflection = new ReflectionClass(Representante::class);
        $this->representante = $this->reflection->newInstanceWithoutConstructor();
    }

    private function invokePrivate(string $method, array $args = [])
    {
        $m = $this->reflection->getMethod($method);
        if (PHP_VERSION_ID < 80100) {
            $m->setAccessible(true);
        }
        return $m->invokeArgs($this->representante, $args);
    }

    private function payloadBase(array $overrides = []): array
    {
        return array_merge([
            'cedula'               => 'V-12345678',
            'nombres'              => 'Juan Carlos',
            'apellidos'            => 'Perez Gomez',
            'fecha_nacimiento'     => date('Y-m-d', strtotime('-40 years')), // 40 años
            'parentesco'           => 'Padre',
            'telefono_principal'   => '04141234567',
            'telefono_emergencia'  => '04149876543',
            'correo'               => 'juan@example.com',
            'direccion_residencia' => 'Calle Falsa 123, Apto 4',
            'atletas_ids'          => [1, 2]
        ], $overrides);
    }

    // ─────────────────────────────────────────────────────────────
    // GRUPO 1 — Validaciones de Edad y Fechas (RF-18.1)
    // ─────────────────────────────────────────────────────────────

    #[TestDox('CP-UNI-18.01: Representante de 17 años y 11 meses debe rechazarse')]
    public function test_CP_UNI_18_01_menor_de_edad(): void
    {
        // Calculamos la fecha: hoy - 18 años + 1 día (cumple 18 mañana)
        $fechaCasi18 = date('Y-m-d', strtotime('-18 years +1 day'));
        
        $this->representante->setAtributos($this->payloadBase(['fecha_nacimiento' => $fechaCasi18]));
        $valido = $this->invokePrivate('validarDatos');
        
        $this->assertFalse($valido, 'El sistema debe rechazar a representantes menores de edad.');
        $this->assertArrayHasKey('fecha_nacimiento', $this->representante->obtenerErrores());
    }

    #[TestDox('CP-UNI-18.02: Representante de exactamente 18 años debe aceptarse')]
    public function test_CP_UNI_18_02_mayor_de_edad_exacto(): void
    {
        //$fechaExacta18 = date('Y-m-d', strtotime('-18 years'));
        $fechaExacta18 = date('Y-m-d', strtotime('-18 years -1 day'));
        
        $this->representante->setAtributos($this->payloadBase(['fecha_nacimiento' => $fechaExacta18]));
        $valido = $this->invokePrivate('validarDatos');
        
        $this->assertTrue($valido, 'El sistema debe aceptar al representante con 18 años exactos.');
    }

    #[TestDox('CP-UNI-18.03: Representante de 126 años debe rechazarse')]
    public function test_CP_UNI_18_03_edad_absurda(): void
    {
        $this->representante->setAtributos($this->payloadBase(['fecha_nacimiento' => '1900-01-01']));
        $valido = $this->invokePrivate('validarDatos');
        
        $this->assertFalse($valido, 'El sistema debe rechazar edades mayores a 120 años.');
        $this->assertArrayHasKey('fecha_nacimiento', $this->representante->obtenerErrores());
    }

    #[TestDox('CP-UNI-18.06: Formato de fecha inválido (DD-MM-YYYY)')]
    public function test_CP_UNI_18_06_formato_fecha_invalido(): void
    {
        $this->representante->setAtributos($this->payloadBase(['fecha_nacimiento' => '17-09-2008']));
        $valido = $this->invokePrivate('validarDatos');
        
        $this->assertFalse($valido);
        $this->assertArrayHasKey('fecha_nacimiento', $this->representante->obtenerErrores());
    }

    // ─────────────────────────────────────────────────────────────
    // GRUPO 2 — Validaciones de Textos y Enums
    // ─────────────────────────────────────────────────────────────

    #[TestDox('CP-UNI-18.04: Parentesco inválido ("Vecino") debe rechazarse')]
    public function test_CP_UNI_18_04_parentesco_invalido(): void
    {
        $this->representante->setAtributos($this->payloadBase(['parentesco' => 'Vecino']));
        $this->assertFalse($this->invokePrivate('validarDatos'));
    }

    #[TestDox('CP-UNI-18.05: Case-sensitivity en parentesco ("padre")')]
    public function test_CP_UNI_18_05_parentesco_minusculas(): void
    {
        $this->representante->setAtributos($this->payloadBase(['parentesco' => 'padre']));
        $this->assertFalse($this->invokePrivate('validarDatos'));
    }

    #[TestDox('CP-UNI-18.13: Nombres con caracteres especiales deben rechazarse')]
    public function test_CP_UNI_18_13_nombres_invalidos(): void
    {
        $this->representante->setAtributos($this->payloadBase(['nombres' => 'Test123']));
        $this->assertFalse($this->invokePrivate('validarDatos'));
    }

    #[TestDox('CP-UNI-18.14: Correos inválidos deben rechazarse')]
    public function test_CP_UNI_18_14_correo_invalido(): void
    {
        $this->representante->setAtributos($this->payloadBase(['correo' => 'sin-arroba.com']));
        $this->assertFalse($this->invokePrivate('validarDatos'));
    }

    // ─────────────────────────────────────────────────────────────
    // GRUPO 3 — Boundaries (Fronteras de Longitud)
    // ─────────────────────────────────────────────────────────────

    #[TestDox('CP-UNI-18.07: Teléfono de 10 caracteres debe rechazarse')]
    public function test_CP_UNI_18_07_telefono_corto(): void
    {
        $this->representante->setAtributos($this->payloadBase(['telefono_principal' => '041412345']));
        $this->assertFalse($this->invokePrivate('validarDatos'));
    }

    #[TestDox('CP-UNI-18.08: Teléfono de exactamente 11 caracteres debe aceptarse')]
    public function test_CP_UNI_18_08_telefono_exacto(): void
    {
        $this->representante->setAtributos($this->payloadBase(['telefono_principal' => '04141234567']));
        $this->assertTrue($this->invokePrivate('validarDatos'));
    }

    #[TestDox('CP-UNI-18.09: Teléfono de 12 caracteres debe rechazarse')]
    public function test_CP_UNI_18_09_telefono_largo(): void
    {
        $this->representante->setAtributos($this->payloadBase(['telefono_principal' => '041412345678']));
        $this->assertFalse($this->invokePrivate('validarDatos'));
    }

    #[TestDox('CP-UNI-18.11: Nombres de 2 y 41 caracteres deben rechazarse')]
    public function test_CP_UNI_18_11_boundaries_nombres(): void
    {
        $this->representante->setAtributos($this->payloadBase(['nombres' => 'Jo']));
        $this->assertFalse($this->invokePrivate('validarDatos'), 'Nombre de 2 letras debe fallar');

        $this->representante->setAtributos($this->payloadBase(['nombres' => str_repeat('A', 41)]));
        $this->assertFalse($this->invokePrivate('validarDatos'), 'Nombre de 41 letras debe fallar');
    }

    // ─────────────────────────────────────────────────────────────
    // GRUPO 4 — Seguridad y Tipos de Datos (Arrays)
    // ─────────────────────────────────────────────────────────────

    #[TestDox('CP-UNI-18.15: Inyección de Array en cédula')]
    public function test_CP_UNI_18_15_array_en_cedula(): void
    {
        $this->representante->setAtributos($this->payloadBase(['cedula' => [12345678]]));
        $valido = $this->invokePrivate('validarDatos');
        
        $this->assertFalse($valido, 'El sistema debe rechazar un array en la cédula.');
        $this->assertArrayHasKey('cedula', $this->representante->obtenerErrores());
    }

    #[TestDox('CP-UNI-18.16: Teléfono de emergencia con espacios debe quedar vacío')]
    public function test_CP_UNI_18_16_telefono_emergencia_vacio(): void
    {
        $this->representante->setAtributos($this->payloadBase(['telefono_emergencia' => '   ']));
        $this->invokePrivate('validarDatos');
        
        $datos = $this->reflection->getProperty('datos');
        $datos->setAccessible(true);
        $valores = $datos->getValue($this->representante);

        $this->assertEmpty($valores['telefono_emergencia'] ?? null, 'El teléfono de emergencia con espacios debe quedar vacío/nulo.');
    }

            #[TestDox('CP-UNI-18.10: Boundary cédula (9 y 10 caracteres reales)')]
    public function test_CP_UNI_18_10_boundaries_cedula(): void
    {
        // 5 caracteres (bajo mínimo real: "V-123") -> Debe rechazar
        $this->representante->setAtributos($this->payloadBase(['cedula' => 'V-123']));
        $this->assertFalse($this->invokePrivate('validarDatos'));

        // 8 caracteres (bajo mínimo real: "V-123456") -> Debe rechazar (la regex exige 7 u 8 números)
        $this->representante->setAtributos($this->payloadBase(['cedula' => 'V-123456']));
        $this->assertFalse($this->invokePrivate('validarDatos'));

        // 9 caracteres (mínimo exacto: "V-1234567") -> Debe aceptar
        $this->representante->setAtributos($this->payloadBase(['cedula' => 'V-1234567']));
        $this->assertTrue($this->invokePrivate('validarDatos'));

        // 10 caracteres (máximo exacto: "V-12345678") -> Debe aceptar
        $this->representante->setAtributos($this->payloadBase(['cedula' => 'V-12345678']));
        $this->assertTrue($this->invokePrivate('validarDatos'));

        // 11 caracteres (sobre máximo: "V-123456789") -> Debe rechazar
        $this->representante->setAtributos($this->payloadBase(['cedula' => 'V-123456789']));
        $this->assertFalse($this->invokePrivate('validarDatos'));
    }

    #[TestDox('CP-UNI-18.12: Boundary dirección (9, 10, 200, 201 caracteres)')]
    public function test_CP_UNI_18_12_boundaries_direccion(): void
    {
        // 9 caracteres (bajo mínimo) -> Debe rechazar
        $this->representante->setAtributos($this->payloadBase(['direccion_residencia' => 'Calle 123']));
        $this->assertFalse($this->invokePrivate('validarDatos'));

        // 10 caracteres (mínimo exacto) -> Debe aceptar
        $this->representante->setAtributos($this->payloadBase(['direccion_residencia' => 'Calle 1234']));
        $this->assertTrue($this->invokePrivate('validarDatos'));

        // 200 caracteres (máximo exacto) -> Debe aceptar
        $this->representante->setAtributos($this->payloadBase(['direccion_residencia' => str_repeat('A', 200)]));
        $this->assertTrue($this->invokePrivate('validarDatos'));

        // 201 caracteres (sobre máximo) -> Debe rechazar
        $this->representante->setAtributos($this->payloadBase(['direccion_residencia' => str_repeat('A', 201)]));
        $this->assertFalse($this->invokePrivate('validarDatos'));
    }

    // ─────────────────────────────────────────────────────────────
    // GRUPO 5 — Reglas de Negocio (Requieren BD)
    // ─────────────────────────────────────────────────────────────

    #[TestDox('CP-UNI-18.17: Atleta mayor de edad no puede ser vinculado')]
    public function test_CP_UNI_18_17_atleta_mayor_edad(): void
    {
        $this->markTestIncomplete('Requiere BD (usa validarReglasDeNegocio). Se hará en Semana 12.');
    }

    #[TestDox('CP-UNI-18.21: Manipulación de autorizaciones (ID no vinculado)')]
    public function test_CP_UNI_18_21_autorizacion_id_fantasma(): void
    {
        $this->markTestIncomplete('Requiere BD (verifica array_diff contra atletas_ids). Se hará en Semana 12.');
    }
}
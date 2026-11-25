<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use GrupoProyecto\SisBiomec\modelo\entrenador;

class EntrenadorValidarTest extends TestCase
{
    private entrenador $entrenador;
    private \PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
        // Tabla necesaria para la validación de cédula única
        $this->pdo->exec("CREATE TABLE entrenador (cedula TEXT, id_entrenador INTEGER)");

        $this->entrenador = new entrenador();
        $ref = new \ReflectionClass(\GrupoProyecto\SisBiomec\modelo\Conexion::class);
        $prop = $ref->getProperty('conex1');
        $prop->setAccessible(true);
        $prop->setValue($this->entrenador, $this->pdo);
    }

    private function datosValidos(array $o = []): array
    {
        return array_merge([
            'cedula' => 'V-12345678',
            'nombres' => 'Juan Carlos',
            'apellidos' => 'Perez Gomez',
            'fecha_nacimiento' => '1990-05-15',
            'genero' => 'M',
            'correo' => 'juan@example.com',
            'telefono' => '04141234567',
            'direccion' => 'Av. Principal, Calle 1',
        ], $o);
    }

    public function testDatosValidosNoGeneranErrores(): void
    {
        $this->entrenador->setDatos($this->datosValidos());
        $this->assertEmpty($this->entrenador->validarDatos());
    }

    public function testCedulaInvalidaFalla(): void
    {
        $this->entrenador->setDatos($this->datosValidos(['cedula' => '12345678']));
        $this->assertArrayHasKey('cedula', $this->entrenador->validarDatos());
    }

    public function testCedulaDuplicadaFalla(): void
    {
        $this->pdo->exec("INSERT INTO entrenador (cedula) VALUES ('V-87654321')");
        $this->entrenador->setDatos($this->datosValidos(['cedula' => 'V-87654321']));
        $this->assertArrayHasKey('cedula', $this->entrenador->validarDatos());
    }

    public function testCorreoInvalidoFalla(): void
    {
        $this->entrenador->setDatos($this->datosValidos(['correo' => 'correo-malo']));
        $this->assertArrayHasKey('correo', $this->entrenador->validarDatos());
    }

    public function testMenorDeEdadFalla(): void
    {
        $fechaMenor = date('Y-m-d', strtotime('-15 years'));
        $this->entrenador->setDatos($this->datosValidos(['fecha_nacimiento' => $fechaMenor]));
        $this->assertArrayHasKey('fecha_nacimiento', $this->entrenador->validarDatos());
    }

    public function testTelefonoIncorrectoFalla(): void
    {
        $this->entrenador->setDatos($this->datosValidos(['telefono' => '123']));
        $this->assertArrayHasKey('teléfono', $this->entrenador->validarDatos());
    }

    public function testCurriculumNoEsPdfFalla(): void
    {
        // Simulamos un archivo de texto
        $archivoFalso = [
            'error' => UPLOAD_ERR_OK,
            'size' => 1024,
            'tmp_name' => tempnam(sys_get_temp_dir(), 'test'),
            'name' => 'test.txt'
        ];
        file_put_contents($archivoFalso['tmp_name'], 'Esto es texto plano');

        $this->entrenador->setDatos($this->datosValidos());
        $this->entrenador->setCurriculum($archivoFalso);
        
        $errores = $this->entrenador->validarDatos();
        $this->assertArrayHasKey('curriculum', $errores);
        
        unlink($archivoFalso['tmp_name']);
    }
}
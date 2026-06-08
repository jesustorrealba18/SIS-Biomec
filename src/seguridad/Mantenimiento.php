<?php

namespace GrupoProyecto\SisBiomec\seguridad;
use GrupoProyecto\SisBiomec\modelo\Conexion;
use Exception;
use PDOException;

// Respetamos la Regla: La clase Model debe extender de la clase Conexión
class Mantenimiento extends Conexion {
    
    private string $rutaBackups;

    public function __construct() {
        // Invocamos al constructor padre para cumplir con la herencia.
        // Usamos la constante de entorno; el constructor de Conexion se encarga de validarla.
        parent::__construct($_ENV['DB_NAME_SEGURIDAD']);

        // Definimos la ruta donde se guardarán temporalmente los respaldos
        // Se asume que la carpeta 'assets/backups' existe en la raíz de tu proyecto
        $this->rutaBackups = dirname(__DIR__, 2) . '/assets/backups/';
    }

   /**
     * Genera un volcado (Dump) de ambas bases de datos.
     */
    public function generarRespaldo(): string {
        if (!is_dir($this->rutaBackups)) {
            mkdir($this->rutaBackups, 0775, true);
        }

        $nombreArchivo = 'SGRD_Backup_' . date('Y-m-d_H-i-s') . '.sql';
        $rutaCompleta = $this->rutaBackups . $nombreArchivo;

        $user = escapeshellarg($_ENV['DB_USER']);
        $host = escapeshellarg($_ENV['DB_HOST']);
        $dbNatacion = escapeshellarg($_ENV['DB_NAME_NATACION']);
        $dbSeguridad = escapeshellarg($_ENV['DB_NAME_SEGURIDAD']);
        $passFlag = empty($_ENV['DB_PASS']) ? '' : '--password=' . escapeshellarg($_ENV['DB_PASS']);

        // Ruta explícita para evitar que Apache se pierda
        $binario = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'C:\\xampp\\mysql\\bin\\mysqldump.exe' : 'mysqldump';

        $comando = sprintf(
            '%s --user=%s %s --host=%s --databases %s %s > %s',
            $binario, $user, $passFlag, $host, $dbNatacion, $dbSeguridad, escapeshellarg($rutaCompleta)
        );

        $resultado = null;
        $codigoSalida = null;
        exec($comando . ' 2>&1', $resultado, $codigoSalida);

        if ($codigoSalida !== 0) {
            $errorMsg = implode("\n", $resultado);
            // Ahora la alerta te mostrará el comando exacto que PHP intentó procesar
            throw new Exception("Error en mysqldump.\nComando: $comando\nDetalle: $errorMsg");
        }

        return $rutaCompleta;
    }

    /**
     * Restaura el sistema inyectando el SQL recibido por parámetro.
     */
    public function restaurarSistema(string $rutaArchivoSql): bool {
        if (!file_exists($rutaArchivoSql)) {
            throw new Exception("El archivo SQL cargado no fue encontrado en el servidor temporal.");
        }

        $user = escapeshellarg($_ENV['DB_USER']);
        $host = escapeshellarg($_ENV['DB_HOST']);
        $passFlag = empty($_ENV['DB_PASS']) ? '' : '--password=' . escapeshellarg($_ENV['DB_PASS']);

        // Para restaurar usamos mysql, no mysqldump
        $binario = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'C:\\xampp\\mysql\\bin\\mysql.exe' : 'mysql';

        $comando = sprintf(
            '%s --user=%s %s --host=%s < %s',
            $binario, $user, $passFlag, $host, escapeshellarg($rutaArchivoSql)
        );

        $resultado = null;
        $codigoSalida = null;
        exec($comando . ' 2>&1', $resultado, $codigoSalida);

        if ($codigoSalida !== 0) {
            $errorMsg = implode("\n", $resultado);
            // Ahora la alerta te mostrará el comando exacto que PHP intentó procesar
            throw new Exception("Error en mysql (Restauración).\nComando: $comando\nDetalle: $errorMsg");
        }

        return true;
    }
}
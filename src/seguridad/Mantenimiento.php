<?php

namespace GrupoProyecto\SisBiomec\seguridad;

use Exception;

class Mantenimiento {
    
    private string $dbUser;
    private string $dbPass;
    private string $dbHost;
    private string $dbNatacion;
    private string $dbSeguridad;
    private string $rutaBackups;

    public function __construct() {
        // Leemos directamente de las variables de entorno
        $this->dbUser = $_ENV['DB_USER'] ?? 'root';
        $this->dbPass = $_ENV['DB_PASS'] ?? '';
        $this->dbHost = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $this->dbNatacion = $_ENV['DB_NAME'] ?? 'sis_natacion'; // Ajusta según el nombre de tu variable .env
        $this->dbSeguridad = $_ENV['DB_NAME_SEGURIDAD'] ?? 'sis_seguridad';

        // Definimos la ruta donde se guardarán temporalmente los respaldos
        // Esta ruta debe apuntar a la carpeta assets/backups de tu proyecto
        $this->rutaBackups = dirname(__DIR__, 3) . '/assets/backups/';
    }

    /**
     * Genera un volcado (Dump) de ambas bases de datos en un solo archivo SQL.
     */
    public function generarRespaldo(): string {
        // Aseguramos que el directorio exista
        if (!is_dir($this->rutaBackups)) {
            mkdir($this->rutaBackups, 0775, true);
        }

        $nombreArchivo = 'SGRD_Backup_' . date('Y-m-d_H-i-s') . '.sql';
        $rutaCompleta = $this->rutaBackups . $nombreArchivo;

        // Construimos el comando nativo. El parámetro --databases permite incluir ambas BD
        $comando = sprintf(
            'mysqldump --user=%s --password=%s --host=%s --databases %s %s > %s',
            escapeshellarg($this->dbUser),
            escapeshellarg($this->dbPass),
            escapeshellarg($this->dbHost),
            escapeshellarg($this->dbNatacion),
            escapeshellarg($this->dbSeguridad),
            escapeshellarg($rutaCompleta)
        );

        // Ejecutamos en la terminal del servidor
        $resultado = null;
        $codigoSalida = null;
        exec($comando . ' 2>&1', $resultado, $codigoSalida);

        if ($codigoSalida !== 0) {
            $errorMsg = implode("\n", $resultado);
            throw new Exception("Error interno al ejecutar mysqldump: " . $errorMsg);
        }

        if (!file_exists($rutaCompleta) || filesize($rutaCompleta) === 0) {
            throw new Exception("El archivo de respaldo se creó vacío o no existe.");
        }

        return $rutaCompleta;
    }

    /**
     * Restaura el sistema inyectando el SQL recibido por parámetro.
     */
    public function restaurarSistema(string $rutaArchivoSql): bool {
        if (!file_exists($rutaArchivoSql)) {
            throw new Exception("El archivo SQL temporal no fue encontrado en el servidor.");
        }

        // Construimos el comando de restauración nativo
        $comando = sprintf(
            'mysql --user=%s --password=%s --host=%s < %s',
            escapeshellarg($this->dbUser),
            escapeshellarg($this->dbPass),
            escapeshellarg($this->dbHost),
            escapeshellarg($rutaArchivoSql)
        );

        $resultado = null;
        $codigoSalida = null;
        exec($comando . ' 2>&1', $resultado, $codigoSalida);

        if ($codigoSalida !== 0) {
            $errorMsg = implode("\n", $resultado);
            throw new Exception("Error al inyectar los datos en MySQL/MariaDB: " . $errorMsg);
        }

        return true;
    }
}
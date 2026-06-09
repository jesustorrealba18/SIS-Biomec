<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;
use GrupoProyecto\SisBiomec\seguridad\ConexionException;

class Conexion {
    
    private string $host;
    private string $user;
    private string $pass;

    protected ?PDO $pdo = null;

    public function __construct(?string $baseDatos = null) {
        
        $this->host = $_ENV['DB_HOST'];
        $this->user = $_ENV['DB_USER'];
        $this->pass = $_ENV['DB_PASS'];

        $dbPermitidas = [$_ENV['DB_NAME_NATACION'], $_ENV['DB_NAME_SEGURIDAD']];
        $database = $baseDatos ?? $_ENV['DB_NAME_NATACION'];

        if (!in_array($database, $dbPermitidas)) {
            error_log("ALERTA DE SEGURIDAD: Intento de conexión a BD no autorizada -> " . $database);
            throw new ConexionException("Error interno del servidor.", 'seguridad');
        }

        try {
            $dsn = "mysql:host={$this->host};dbname={$database};charset=utf8mb4";
            
            $this->pdo = new PDO($dsn, $this->user, $this->pass);
            
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            
        } catch (PDOException $e) {
            error_log("Fallo crítico de infraestructura en la BD [{$database}]: " . $e->getMessage());
            throw new ConexionException("No se pudo establecer conexión con la base de datos.", 'db', 0, $e);
        }
    }

    public function getConex1(): ?PDO {
        return $this->pdo;
    }
}

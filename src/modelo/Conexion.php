<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class Conexion {
    
    // 1. ATRIBUTOS DE CONFIGURACIÓN (Privados: Solo esta clase los conoce)
    private string $host;
    private string $user;
    private string $pass;

    // 2. EL OBJETO DE CONEXIÓN (Protected: Disponible para Conexion y sus hijos como Marca)
    protected ?PDO $pdo = null;

    public function __construct(?string $baseDatos = null) {
        
        // Mapeamos las variables globales al estado interno de la clase (Pureza OOP)
        $this->host = $_ENV['DB_HOST'];
        $this->user = $_ENV['DB_USER'];
        $this->pass = $_ENV['DB_PASS'];

        // =====================================================================
        // PROTECCIÓN CONTRA INYECCIÓN DE ORIGEN DE DATOS (Lista Blanca)
        // =====================================================================
        $dbPermitidas = [$_ENV['DB_NAME_NATACION'], $_ENV['DB_NAME_SEGURIDAD']];
        $database = $baseDatos ?? $_ENV['DB_NAME_NATACION'];

        if (!in_array($database, $dbPermitidas)) {
            // Si alguien intenta inyectar por el constructor una BD que no es la del club 
            // de natación o la de seguridad, el sistema se autodestruye.
            error_log("ALERTA DE SEGURIDAD: Intento de conexión a BD no autorizada -> " . $database);
            die("Error 500: Origen de datos no autorizado.");
        }

        try {
            $dsn = "mysql:host={$this->host};dbname={$database};charset=utf8mb4";
            
            // Usamos los atributos propios de la clase, no la variable global
            $this->pdo = new PDO($dsn, $this->user, $this->pass);
            
            // Configuración de auditoría y tipado fuerte
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            
        } catch (PDOException $e) {
            error_log("Fallo crítico de infraestructura en la BD [{$database}]: " . $e->getMessage());
            die("Error 500: Fallo de integridad del sistema.");
        }
    }
}
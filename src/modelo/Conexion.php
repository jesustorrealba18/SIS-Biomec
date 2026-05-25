<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class Conexion {
    private $pdo;

    public function __construct($baseDatos = null) {
        // Si no se le pasa una BD específica por parámetro, usa la de natación por defecto
        $database = $baseDatos ?? $_ENV['DB_NAME_NATACION'];
        
        try {
            $dsn = "mysql:host=" . $_ENV['DB_HOST'] . ";dbname=" . $database . ";charset=utf8mb4";
            
            // Usamos las variables de entorno, CERO código hardcodeado (Regla de Sequera)
            $this->pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS']);
            
            // 1. Desactivar emulación de prepares (Blindaje contra Inyección SQL Avanzada)
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            // 2. Modo de errores estricto
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
        } catch (PDOException $e) {
            // Trazabilidad silenciosa: Guardamos el error real en el servidor (archivo de logs)
            error_log("Fallo crítico de conexión BD (" . $database . "): " . $e->getMessage());
            
            // Al usuario final NO se le muestra la ruta ni el error real
            die("Error 500: Fallo de integridad en el sistema. Contacte al administrador.");
        }
    }

    public function getConex1() {
        return $this->pdo;
    }
}

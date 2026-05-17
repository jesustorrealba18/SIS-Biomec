<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;
class Conexion {
    private $host = "localhost";
    private $user = "root";
    private $pass = "";
    private $db   = "gestion_natacion";
    private $pdo;

    public function __construct() {
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db . ";charset=utf8";
            $this->pdo = new PDO($dsn, $this->user, $this->pass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    public function getConex1() {
        return $this->pdo;
    }
}

<?php

namespace GrupoProyecto\SisBiomec\seguridad;

use GrupoProyecto\SisBiomec\modelo\Conexion;
use PDO;
use PDOException;

// Regla de Sequera: "La clase Model debe extender de la clase Conexión"
class Bitacora extends Conexion {
    
    public function __construct() {
        // Al instanciar, obligamos al padre (Conexion) a conectarse a la BD de Seguridad
        parent::__construct($_ENV['DB_NAME_SEGURIDAD']);
    }

    /**
     * Método ESTÁTICO: Permite registrar auditorías sin instanciar la clase repetidas veces.
     * Captura automáticamente la IP del usuario.
     */
    public static function registrar($id_usuario, $modulo, $operacion, $id_afectado = null, $campo = null, $val_ant = null, $val_nue = null) {
        try {
            // Usamos un patrón Factory interno para cumplir la regla de herencia
            $instancia = new self(); 
            $conex = $instancia->getConex1();
            
            // Atrapamos la IP real desde donde hicieron el movimiento
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'IP_Desconocida';

            $sql = "INSERT INTO bitacora 
                    (id_usuario, modulo_afectado, tipo_operacion, id_registro_afectado, campo_modificado, valor_anterior, valor_nuevo, ip_origen) 
                    VALUES (:usr, :mod, :ope, :id_afec, :campo, :ant, :nue, :ip)";
            
            $stmt = $conex->prepare($sql);
            $stmt->execute([
                ':usr'     => (int)$id_usuario,
                ':mod'     => $modulo,
                ':ope'     => $operacion, // Ej: 'DELETE', 'UPDATE'
                ':id_afec' => $id_afectado,
                ':campo'   => $campo,
                ':ant'     => $val_ant,
                ':nue'     => $val_nue,
                ':ip'      => $ip
            ]);
            
        } catch (PDOException $e) {
            // REGLA DE ORO DE AUDITORÍA: Si la bitácora falla, no debe tumbar el sistema principal.
            // Solo debe dejar el grito de auxilio en el log de errores de PHP.
            error_log("ALERTA GRAVE - FALLO EN BITÁCORA DE SEGURIDAD: " . $e->getMessage());
        }
    }
}
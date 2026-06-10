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
            $navegador = $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido';

            $sql = "INSERT INTO bitacora 
                    (id_usuario, modulo_afectado, tipo_operacion, id_registro_afectado, campo_modificado, valor_anterior, valor_nuevo, ip_origen) 
                    VALUES (:usr, :mod, :ope, :id_afec, :campo, :ant, :nue, :ip)";
            
            $stmt = $conex->prepare($sql);
            $stmt->bindValue(':usr', (int)$id_usuario, PDO::PARAM_INT);
            $stmt->bindValue(':mod', $modulo, PDO::PARAM_STR);
            $stmt->bindValue(':ope', $operacion, PDO::PARAM_STR);
            $stmt->bindValue(':id_afec', $id_afectado, $id_afectado === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':campo', $campo, $campo === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':ant', $val_ant, $val_ant === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':nue', $val_nue, $val_nue === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
            $stmt->execute();
            
        } catch (PDOException $e) {
            // REGLA DE ORO DE AUDITORÍA: Si la bitácora falla, no debe tumbar el sistema principal.
            // Solo debe dejar el grito de auxilio en el log de errores de PHP.
            error_log("ALERTA GRAVE - FALLO EN BITÁCORA DE SEGURIDAD: " . $e->getMessage());
        }
    }

    /**
     * Extrae el historial de la bitácora uniendo los datos del usuario responsable.
     */
    public static function listar($limite = 500) {
        try {
            $instancia = new self();
            $conex = $instancia->getConex1(); // La conexión se queda aquí, en el Modelo

            $sql = "SELECT b.*, u.nombres, u.apellidos, r.nombre AS rol_nombre 
                    FROM bitacora b 
                    LEFT JOIN usuarios u ON b.id_usuario = u.id_usuario
                    LEFT JOIN usuario_roles ur ON u.id_usuario = ur.id_usuario
                    LEFT JOIN roles r ON ur.id_rol = r.id_rol
                    ORDER BY b.fecha_operacion DESC LIMIT :limite";
            
            $stmt = $conex->prepare($sql);
            // PDO::PARAM_INT es vital cuando usamos LIMIT en consultas preparadas
            $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error al consultar bitácora: " . $e->getMessage());
            return []; // Devolvemos un arreglo vacío para no romper la vista
        }
    }
}
<?php
namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class Notificacion extends Conexion {

    
    public function __construct() {
      
        parent::__construct('sis_seguridad'); 
    }

    /**
     * 1. MÉTODO CORE: Guarda la notificación en sis_seguridad
     */
    public static function enviar(int $id_usuario, string $titulo, string $mensaje, string $icono = 'fa-bell', string $color = 'indigo'): bool {
        try {
            $instancia = new self(); // Se conecta a sis_seguridad
            $sql = "INSERT INTO notificaciones (id_usuario, titulo, mensaje, icono, color) 
                    VALUES (:id_usuario, :titulo, :mensaje, :icono, :color)";
            $stmt = $instancia->pdo->prepare($sql);
            return $stmt->execute([
                ':id_usuario' => $id_usuario,
                ':titulo' => $titulo,
                ':mensaje' => $mensaje,
                ':icono' => $icono,
                ':color' => $color
            ]);
        } catch (PDOException $e) {
            error_log("Error Notificacion (Seguridad): " . $e->getMessage());
            return false;
        }
    }

    /**
     * 2. MÉTODO INTELIGENTE: Busca en sis_natacion y escribe en sis_seguridad
     */
    public static function notificarAtletaYRepresentante(int $id_atleta, string $titulo, string $mensaje, string $icono = 'fa-bell', string $color = 'indigo'): void {
        try {
            // Para BUSCAR a los usuarios, necesitamos conectarnos temporalmente a sis_natacion
            // Instanciamos la conexión normal de negocio
            $dbNegocio = new Conexion('sis_natacion'); 

            // A) Buscar el id_usuario del Atleta
            $sqlAtleta = "SELECT id_usuario FROM atletas WHERE id_atleta = :id_atleta AND id_usuario IS NOT NULL";
            $stmtA = $dbNegocio->pdo->prepare($sqlAtleta);
            $stmtA->execute([':id_atleta' => $id_atleta]);
            $userAtleta = $stmtA->fetch(PDO::FETCH_ASSOC);

            if ($userAtleta) {
                // Escribimos en sis_seguridad
                self::enviar($userAtleta['id_usuario'], $titulo, $mensaje, $icono, $color);
            }

            // B) Buscar los id_usuario de los Representantes vinculados
            $sqlRep = "SELECT r.id_usuario 
                       FROM representantes r 
                       INNER JOIN atleta_representante ar ON r.id_representante = ar.id_representante 
                       WHERE ar.id_atleta = :id_atleta AND r.id_usuario IS NOT NULL";
            $stmtR = $dbNegocio->pdo->prepare($sqlRep);
            $stmtR->execute([':id_atleta' => $id_atleta]);
            $representantes = $stmtR->fetchAll(PDO::FETCH_ASSOC);

            // Enviamos copia a cada representante
            foreach ($representantes as $rep) {
                self::enviar($rep['id_usuario'], $titulo, $mensaje, $icono, $color);
            }

        } catch (PDOException $e) {
            error_log("Error Routing Notificacion: " . $e->getMessage());
        }
    }

    /**
     * Marca una notificación como leída asegurando que pertenezca al usuario
     */
    public static function marcarComoLeida(int $id_notificacion, int $id_usuario): bool {
        try {
            $instancia = new self();
            $sql = "UPDATE notificaciones SET leida = 1 
                    WHERE id_notificacion = :id_notificacion AND id_usuario = :id_usuario";
            
            $stmt = $instancia->pdo->prepare($sql);
            return $stmt->execute([
                ':id_notificacion' => $id_notificacion,
                ':id_usuario' => $id_usuario
            ]);
        } catch (\Throwable $e) { // <-- CAMBIO CLAVE: Atrapa cualquier error fatal
            error_log("Error al marcar leída: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene la lista de notificaciones de un usuario
     */
    public static function listarPorUsuario(int $id_usuario, int $limite = 10): array {
        try {
            $instancia = new self(); // Se conecta a sis_seguridad automáticamente
            $sql = "SELECT id_notificacion, titulo, mensaje, icono, color, leida, fecha 
                    FROM notificaciones 
                    WHERE id_usuario = :id_usuario 
                    ORDER BY fecha DESC LIMIT :limite";
            
            $stmt = $instancia->pdo->prepare($sql);
            $stmt->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al listar notificaciones: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Cuenta cuántas notificaciones no ha leído el usuario
     */
    public static function contarNoLeidas(int $id_usuario): int {
        try {
            $instancia = new self();
            $sql = "SELECT COUNT(*) FROM notificaciones WHERE id_usuario = :id_usuario AND leida = 0";
            $stmt = $instancia->pdo->prepare($sql);
            $stmt->execute([':id_usuario' => $id_usuario]);
            
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }


}
<?php
namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class Notificacion extends Conexion {
    use ValidacionesTrait;
    use AutoBinderTrait;

    private array $datos = [];
    private array $camposPermitidos = ['id_notificacion', 'id_usuario'];
    
    public function __construct() {
      
        parent::__construct('sis_seguridad'); 
    }

    public function setDatos(array $datos): self {
        foreach ($datos as $clave => $valor) {
            if (in_array($clave, $this->camposPermitidos)) {
                $this->datos[$clave] = is_string($valor) ? trim($valor) : $valor;
            }
        }
        return $this;
    }

    private function ValidacionBackend(): bool {
        $this->resetearErrores();

         // 1. EXTRAER DATOS ENCAPSULADOS
        $id_notif = $this->datos['id_notificacion'] ?? '';
        $id_user = $this->datos['id_usuario'] ?? '';

        // 2. VALIDACIONES DE FORMATO (Usando tu Trait)
        if (!$this->requerido((string)$id_notif, 'ID Notificación') || 
            !$this->soloNumeros((string)$id_notif, 'ID Notificación')) {
            return false;
        }

        if (!$this->requerido((string)$id_user, 'Usuario') || 
            !$this->soloNumeros((string)$id_user, 'Usuario')) {
            return false;
        }

          // 3. VALIDACIÓN DE EXISTENCIA Y PROPIEDAD
            $sqlCheck = "SELECT leida FROM notificaciones WHERE id_notificacion = :id_notificacion AND id_usuario = :id_usuario";
            $stmtCheck = $this->pdo->prepare($sqlCheck);
            $stmtCheck->execute([
                ':id_notificacion' => (int)$id_notif, 
                ':id_usuario' => (int)$id_user
            ]);
            
            $notificacion = $stmtCheck->fetch(\PDO::FETCH_ASSOC);

            if (!$notificacion) {
                $this->agregarError('Seguridad', 'La notificación no existe o no te pertenece.');
               // error_log("ALERTA SEGURIDAD: Manipulación detectada. Usuario {$id_user} intentó alterar notificación {$id_notif}.");
               return false;
            }

        if (isset($notificacion['leida']) && $notificacion['leida'] == 1) {
            return true;
        }

         return empty($this->obtenerErrores());
     } 

        public function marcarcomoLeida(): bool {
     

            if (!$this->ValidacionBackend()) {
                return false; 
            }

        return $this->marcarLeida();
        }


    private function marcarLeida(): bool {
       
        try {
        $id_notif = $this->datos['id_notificacion'] ?? '';
        

            // 4. EJECUCIÓN SEGURA
            $sql = "UPDATE notificaciones SET leida = 1 WHERE id_notificacion = :id_notificacion";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':id_notificacion' => (int)$id_notif]);

        } catch (\Throwable $e) {
            $this->agregarError('Base de Datos', 'Ocurrió un error interno al actualizar.');
            error_log("Error crítico en marcarLeida: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 1. MÉTODO CORE: Guarda la notificación en sis_seguridad
     */
    public static function enviar(int $id_usuario, string $titulo, string $mensaje, string $icono = 'fa-bell', string $color = 'indigo', ?string $enlace_url = null): bool {
        try {
            $instancia = new self(); // Se conecta a sis_seguridad
            $sql = "INSERT INTO notificaciones (id_usuario, titulo, mensaje, icono, color, enlace_url) 
                    VALUES (:id_usuario, :titulo, :mensaje, :icono, :color, :enlace_url)";
            $stmt = $instancia->pdo->prepare($sql);
            return $stmt->execute([
                ':id_usuario' => $id_usuario,
                ':titulo' => $titulo,
                ':mensaje' => $mensaje,
                ':icono' => $icono,
                ':color' => $color,
                ':enlace_url' => $enlace_url
            ]);
        } catch (PDOException $e) {
            error_log("Error Notificacion (Seguridad): " . $e->getMessage());
            return false;
        }
    }

    /**
     * 2. MÉTODO INTELIGENTE: Busca en sis_natacion y escribe en sis_seguridad
     */

    public static function notificarAtletaYRepresentante(int $id_atleta, string $titulo, string $mensaje, string $icono = 'fa-bell', string $color = 'indigo', ?string $enlace_url = null): void {
        try {
            // Para BUSCAR a los usuarios, necesitamos conectarnos temporalmente a sis_natacion
            $dbNegocio = new Conexion('sis_natacion'); 

            // A) Buscar el id_usuario del Atleta Y SU EDAD
            $sqlAtleta = "SELECT id_usuario, TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) AS edad 
                          FROM atletas 
                          WHERE id_atleta = :id_atleta";
            $stmtA = $dbNegocio->pdo->prepare($sqlAtleta);
            $stmtA->execute([':id_atleta' => $id_atleta]);
            $userAtleta = $stmtA->fetch(\PDO::FETCH_ASSOC);

            if ($userAtleta) {
                // 1. Notificar al Atleta (Si tiene un usuario web asignado)
                if (!empty($userAtleta['id_usuario'])) {
                    self::enviar($userAtleta['id_usuario'], $titulo, $mensaje, $icono, $color, $enlace_url);
                }

                // 2. REGLA DE NEGOCIO: Solo notificar al representante si el atleta es menor de 18 años
                if ($userAtleta['edad'] < 18) {
                    $sqlRep = "SELECT r.id_usuario 
                               FROM representantes r 
                               INNER JOIN atleta_representante ar ON r.id_representante = ar.id_representante 
                               WHERE ar.id_atleta = :id_atleta AND r.id_usuario IS NOT NULL";
                    $stmtR = $dbNegocio->pdo->prepare($sqlRep);
                    $stmtR->execute([':id_atleta' => $id_atleta]);
                    $representantes = $stmtR->fetchAll(\PDO::FETCH_ASSOC);

                    // Enviamos copia a cada representante
                    foreach ($representantes as $rep) {
                        // Le cambiamos un poco el título para que sepa que es sobre su representado
                        self::enviar($rep['id_usuario'], "Atleta a tu cargo: " . $titulo, $mensaje, $icono, $color, $enlace_url);
                    }
                }
            }

        } catch (\PDOException $e) {
            error_log("Error Routing Notificacion: " . $e->getMessage());
        }
    }
/*     public static function notificarAtletaYRepresentante(int $id_atleta, string $titulo, string $mensaje, string $icono = 'fa-bell', string $color = 'indigo'): void {
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
    } */

    /**
     * Marca una notificación como leída asegurando que pertenezca al usuario
     */
   /*  public static function marcarComoLeida(int $id_notificacion, int $id_usuario): bool {


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
    } */

    /**
     * Marca una notificación como leída con VALIDACIÓN ESTRICTA de seguridad.
     */
   /*  public static function marcarComoLeida(int $id_notificacion, int $id_usuario): bool {
        // 1. Validación de cordura básica
        if ($id_notificacion <= 0 || $id_usuario <= 0) return false;

        try {
            $instancia = new self();
            
            // 2. VALIDACIÓN DE EXISTENCIA Y PROPIEDAD (Evita Hackeo de IDs)
            $sqlCheck = "SELECT leida FROM notificaciones WHERE id_notificacion = :id_notificacion AND id_usuario = :id_usuario";
            $stmtCheck = $instancia->pdo->prepare($sqlCheck);
            $stmtCheck->execute([
                ':id_notificacion' => $id_notificacion, 
                ':id_usuario' => $id_usuario
            ]);
            
            $notificacion = $stmtCheck->fetch(\PDO::FETCH_ASSOC);

            // Si devuelve false, significa que el ID no existe o no le pertenece a este usuario
            if (!$notificacion) {
                // Registramos el intento de vulneración en los logs del servidor
                error_log("ALERTA SEGURIDAD: El usuario ID {$id_usuario} intentó manipular la notificación ID {$id_notificacion} (Inexistente o ajena).");
                return false;
            }

            // Si ya estaba leída (por un clic anterior), simplemente devolvemos true para no desgastar la BD
            if ($notificacion['leida'] == 1) {
                return true;
            }

            // 3. Ejecución segura (Solo llega aquí si existe, es de él, y no estaba leída)
            $sql = "UPDATE notificaciones SET leida = 1 WHERE id_notificacion = :id_notificacion";
            $stmt = $instancia->pdo->prepare($sql);
            return $stmt->execute([':id_notificacion' => $id_notificacion]);

        } catch (\Throwable $e) {
            error_log("Error crítico en marcarLeida: " . $e->getMessage());
            return false;
        }
    } */

    /**
     * Obtiene la lista de notificaciones de un usuario
     */
    public static function listarPorUsuario(int $id_usuario, int $limite = 10): array {
        try {
            $instancia = new self(); // Se conecta a sis_seguridad automáticamente
            $sql = "SELECT id_notificacion, titulo, mensaje, icono, color, leida, fecha, enlace_url 
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
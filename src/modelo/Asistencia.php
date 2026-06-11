<?php
// Ruta: modelo/Asistencia.php
namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class Asistencia extends Conexion {
    use ValidacionesTrait;
    use AutoBinderTrait;

    // =====================================================================
    // 1. ENCAPSULAMIENTO ESTRICTO (El estándar de tu proyecto)
    // =====================================================================
    private array $datos = [];

    // Lista blanca para evitar inyecciones masivas por POST
    private array $camposPermitidos = [
        'id_sesion', 'id_atleta', 'estado_asistencia', 'justificacion'
    ];

    // NO SE DECLARA CONSTRUCTOR: PHP invoca automáticamente el de la clase Conexion.

    // =====================================================================
    // 2. HIDRATACIÓN DEL OBJETO
    // =====================================================================
    public function setDatos(array $datos): self {
        foreach ($datos as $clave => $valor) {
            if (in_array($clave, $this->camposPermitidos)) {
                $this->datos[$clave] = is_string($valor) ? trim($valor) : $valor;
            }
        }
        return $this;
    }

    public function getCampo(string $clave, $default = null) {
        return $this->datos[$clave] ?? $default;
    }

    // =====================================================================
    // 3. CONSULTAS Y LÓGICA DE NEGOCIO
    // =====================================================================

    /**
     * Trae todos los atletas y cruza su estado actual de asistencia en la sesión
     */
   public function obtenerAtletasPorSesion(int $id_sesion): array {
        try {
            // =====================================================================
            // LÓGICA DE CONVOCATORIA REAL: Sesión -> Grupo -> Atleta -> Asistencia
            // =====================================================================
            $sql = "SELECT a.id_atleta, a.cedula, a.nombres, a.apellidos, 
                           c.nombre AS categoria_nombre, 
                           ast.estado, ast.justificacion
                    FROM sesiones s
                    -- 1. Conectamos la sesión con el grupo planificado
                    INNER JOIN grupo_atleta ga ON s.id_grupo = ga.id_grupo
                    -- 2. Conectamos el grupo con los atletas que pertenecen a él
                    INNER JOIN atletas a ON ga.id_atleta = a.id_atleta
                    -- 3. Traemos la categoría federativa del atleta si posee
                    LEFT JOIN categorias_feveda c ON a.id_categoria = c.id_categoria
                    -- 4. LEFT JOIN crítico: Muestra el estado de asistencia si ya fue capturado
                    LEFT JOIN asistencia ast ON a.id_atleta = ast.id_atleta AND ast.id_sesion = s.id_sesion
                    WHERE s.id_sesion = :id_sesion 
                      AND a.estado = 'Activo'
                    ORDER BY a.nombres ASC";

            $stmt = $this->pdo->prepare($sql);
            
            // Inyección estricta con tipado explícito para cumplir el estándar de infraestructura
            $stmt->bindValue(':id_sesion', $id_sesion, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error Modelo Asistencia (Listar Convocados): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Extrae las sesiones aptas para el registro de asistencia (Planificadas y Parciales)
     */
    public function obtenerSesionesActivas(): array {
        try {
            // Unimos con grupos_entrenamiento para armar una etiqueta limpia en la interfaz
            $sql = "SELECT s.id_sesion, g.nombre AS grupo_nombre,
             s.fecha, s.estado FROM sesiones s 
             INNER JOIN grupos_entrenamiento g ON s.id_grupo = g.id_grupo 
             WHERE s.estado IN ('Planificada', 'Parcial') 
             AND DATE(s.fecha) = CURDATE() ORDER BY s.fecha ASC;";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Error Modelo Asistencia (Listar Sesiones Activas): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Identifica el QR, autohidrata el objeto y dispara el guardado
     */
    public function registrarPorQR(string $token_qr): array {
        try {
            $sqlBuscar = "SELECT id_atleta, nombres, apellidos FROM atletas WHERE token_asistencia = :token";
            $stmtBuscar = $this->pdo->prepare($sqlBuscar);
            $stmtBuscar->bindValue(':token', $token_qr);
            $stmtBuscar->execute();
            
            $atleta = $stmtBuscar->fetch(PDO::FETCH_ASSOC);

            if (!$atleta) {
                return ['exito' => false, 'mensaje' => 'Token de seguridad inválido.'];
            }

            // Hidratamos el objeto internamente simulando un envío de formulario
            $this->datos['id_atleta'] = $atleta['id_atleta'];
            $this->datos['estado_asistencia'] = 'Presente';
            $this->datos['justificacion'] = 'Validación Biométrica QR';

            // Llamamos al método unificado
            $exito = $this->guardar();

            return [
                'exito' => $exito,
                'nombre_atleta' => $atleta['nombres'] . ' ' . $atleta['apellidos'],
                'mensaje' => $exito ? 'Asistencia registrada' : 'Error al guardar.'
            ];

        } catch (PDOException $e) {
            // error_log("Error Modelo Asistencia (QR): " . $e->getMessage());
            // return ['exito' => false, 'mensaje' => 'Error de infraestructura.'];
            return ['exito' => false, 'mensaje' => 'Error BD: ' . $e->getMessage()];
        }
    }

    /**
     * MOTOR SQL (UPSERT UNIFICADO)
     */
    public function guardar(): bool {
        try {
            // Utilizamos los datos encapsulados
            $id_sesion = $this->datos['id_sesion'] ?? null;
            $id_atleta = $this->datos['id_atleta'] ?? null;
            $estado = $this->datos['estado_asistencia'] ?? null;
            $justif = $this->datos['justificacion'] ?? 'Sin justificación';

            if (!$id_sesion || !$id_atleta || !$estado) return false;

            $sql = "INSERT INTO asistencia (id_sesion, id_atleta, estado, justificacion, fecha_registro) 
                    VALUES (:id_sesion, :id_atleta, :estado, :justificacion, NOW())
                    ON DUPLICATE KEY UPDATE 
                    estado = VALUES(estado), justificacion = VALUES(justificacion), fecha_registro = NOW()";

            $stmt = $this->pdo->prepare($sql);
            
            // Si en el futuro configuras AutoBinderTrait aquí, podrías omitir estos bindValue
            $stmt->bindValue(':id_sesion', $id_sesion, PDO::PARAM_INT);
            $stmt->bindValue(':id_atleta', $id_atleta, PDO::PARAM_INT);
            $stmt->bindValue(':estado', $estado, PDO::PARAM_STR);
            $stmt->bindValue(':justificacion', $justif, PDO::PARAM_STR);

            return $stmt->execute();

        } catch (PDOException $e) {
            //error_log("Error Modelo Asistencia (Guardar): " . $e->getMessage());
            throw new \Exception($e->getMessage());
           // return false;
        }
    }
}
<?php
namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class Asistencia extends Conexion {
    use ValidacionesTrait;
    use AutoBinderTrait;

   
    private array $datos = [];

    // Lista blanca para evitar inyecciones masivas por POST
    private array $camposPermitidos = [
        'id_sesion', 'id_atleta', 'estado_asistencia', 'justificacion','token_qr','tipo'
    ];

    
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

    
    private function validarDatos(): bool {
        $this->resetearErrores();

        $id_sesion = $this->datos['id_sesion'] ?? '';
        $id_atleta = $this->datos['id_atleta'] ?? '';
        $estado    = $this->datos['estado_asistencia'] ?? '';
        $justif    = $this->datos['justificacion'] ?? '';
        $tipo      = $this->datos['tipo'] ?? 'Manual';

        // Validar Sesión y Atleta
        $this->requerido((string)$id_sesion, 'id_sesion');
        $this->soloNumeros((string)$id_sesion, 'id_sesion');
        
        $this->requerido((string)$id_atleta, 'id_atleta');
        $this->soloNumeros((string)$id_atleta, 'id_atleta');

        // Validar Estado contra el diccionario permitido (Lista Blanca Absoluta)
        $estadosPermitidos = ['Presente', 'Ausente', 'Justificado', 'Retardo'];
        if (!in_array($estado, $estadosPermitidos, true)) {
            $this->agregarError('estado_asistencia', 'Violación: El estado de asistencia no es válido.');
        }

        // Condicional: Si es justificado, DEBE tener una justificación
       if ($estado === 'Justificado') {
            $this->requerido($justif, 'justificacion');
            $this->longitud($justif, 'justificacion', 5, 255);
        } else {
            // Si es 'Presente', 'Ausente' o 'Retardo', limpiamos la base de datos automáticamente
            $this->datos['justificacion'] = 'No aplica'; 
        }

        // Validar Tipo (Protección contra manipulación del DOM)
        $tiposPermitidos = ['Manual', 'QR'];
        if (!in_array($tipo, $tiposPermitidos, true)) {
            $this->agregarError('tipo', 'Violación: Tipo de registro no autorizado.');
        }

        return empty($this->obtenerErrores());
    }



    public function RegistrarManual(): bool {
        if (!$this->validarDatos()) {
            return false;
        }
        return $this->guardar();
    }
    
    public function RegistrarPorQR(): array {

      $this->resetearErrores();
        
        $id_sesion = $this->datos['id_sesion'] ?? '';
        $token = $this->datos['token_qr'] ?? '';

        if (empty($id_sesion) || empty($token)) {
            return ['exito' => false, 'mensaje' => 'Falta el token o la sesión.'];
        }


        return $this->TransaccionRegistrarQR();
    }

  
    /**
     * Trae todos los atletas y cruza su estado actual de asistencia en la sesión
     */
   public function obtenerAtletasPorSesion(int $id_sesion): array {
        try {
           
            $sql = "SELECT a.id_atleta, a.cedula, a.nombres, a.apellidos, 
                           c.nombre AS categoria_nombre, 
                           ast.estado, ast.justificacion, ast.tipo
                    FROM sesiones s
                    -- . Conectamos la sesión con el grupo planificado
                    INNER JOIN grupo_atleta ga ON s.id_grupo = ga.id_grupo
                    -- . Conectamos el grupo con los atletas que pertenecen a él
                    INNER JOIN atletas a ON ga.id_atleta = a.id_atleta
                    -- . Traemos la categoría federativa del atleta si posee
                    LEFT JOIN categorias_feveda c ON a.id_categoria = c.id_categoria
                    -- . LEFT JOIN crítico: Muestra el estado de asistencia si ya fue capturado
                    LEFT JOIN asistencia ast ON a.id_atleta = ast.id_atleta AND ast.id_sesion = s.id_sesion
                    WHERE s.id_sesion = :id_sesion 
                      AND a.estado = 'Activo'
                    ORDER BY a.nombres ASC";

            $stmt = $this->pdo->prepare($sql);
            
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


        private function TransaccionRegistrarQR(): array {
        $tokenLimpio = preg_replace('/^token/', '', trim($this->datos['token_qr']));

        try {
            $sqlBuscar = "SELECT id_atleta, nombres, apellidos FROM atletas WHERE token_asistencia = :token";
            $stmtBuscar = $this->pdo->prepare($sqlBuscar);
            $stmtBuscar->bindValue(':token', $tokenLimpio, PDO::PARAM_STR);
            $stmtBuscar->execute();
            
            $atleta = $stmtBuscar->fetch(PDO::FETCH_ASSOC);

            if (!$atleta) {
                return ['exito' => false, 'mensaje' => 'Token de seguridad inválido o no registrado.'];
            }

            $id_sesion = $this->datos['id_sesion'];
            $nombreCompleto = $atleta['nombres'] . ' ' . $atleta['apellidos'];

            $sqlCheck = "SELECT estado, tipo FROM asistencia WHERE id_sesion = :id_sesion AND id_atleta = :id_atleta";
            $stmtCheck = $this->pdo->prepare($sqlCheck);
            $stmtCheck->execute([':id_sesion' => $id_sesion, ':id_atleta' => $atleta['id_atleta']]);
            $registroPrevio = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($registroPrevio && $registroPrevio['estado'] === 'Presente') {
                return [
                    'exito' => false, 
                    'status_http' => 'info', // Gatilla la alerta azul
                    'nombre_atleta' => $nombreCompleto,
                    'mensaje' => "¡Ya estaba presente! Registrado vía {$registroPrevio['tipo']}."
                ];
            }

            $this->datos['id_atleta'] = $atleta['id_atleta'];
            $this->datos['estado_asistencia'] = 'Presente';
            $this->datos['justificacion'] = 'Validación Biométrica QR';
            $this->datos['tipo'] = 'QR';

            $exito = $this->guardar();

            if ($exito) {
                return [
                    'exito' => true,
                    'status_http' => 'success',
                    'nombre_atleta' => $nombreCompleto,
                    'mensaje' => 'Asistencia registrada exitosamente.'
                ];
            } else {
                $errores = $this->obtenerErrores();
                $mensajeFinal = !empty($errores) ? reset($errores) : 'Error de integridad al guardar.';
                return ['exito' => false, 'mensaje' => $mensajeFinal];
            }

        } catch (PDOException $e) {
            return ['exito' => false, 'mensaje' => 'Error BD: ' . $e->getMessage()];
        }
    }

   
    private function guardar(): bool {
        try {

        $this->pdo->beginTransaction();

            $sqlCheck = "SELECT estado, tipo FROM asistencia WHERE id_sesion = :id_sesion AND id_atleta = :id_atleta";
            $stmtCheck = $this->pdo->prepare($sqlCheck);
            $stmtCheck->execute([
                ':id_sesion' => $this->datos['id_sesion'],
                ':id_atleta' => $this->datos['id_atleta']
            ]);
            $registroPrevio = $stmtCheck->fetch(\PDO::FETCH_ASSOC);

            if ($registroPrevio && $registroPrevio['tipo'] === 'QR' && $registroPrevio['estado'] === 'Presente') {
                $this->agregarError('estado_asistencia', 'Acción denegada: Este atleta ya validó su presencia mediante el Escáner QR de forma inmutable.');
                $this->pdo->rollBack();
                return false;
            }



            $id_sesion = $this->datos['id_sesion'] ?? null;
            $id_atleta = $this->datos['id_atleta'] ?? null;
            $estado = $this->datos['estado_asistencia'] ?? null;
            $justif = $this->datos['justificacion'] ?? 'Sin justificación';
            $tipo = $this->datos['tipo'] ?? 'Manual';

            if (!$id_sesion || !$id_atleta || !$estado) return false;

            
            $sql = "INSERT INTO asistencia (id_sesion, id_atleta, estado, justificacion, tipo, fecha) 
                    VALUES (:id_sesion, :id_atleta, :estado, :justificacion, :tipo, NOW())
                    ON DUPLICATE KEY UPDATE 
                    estado = VALUES(estado), 
                    justificacion = VALUES(justificacion), 
                    tipo = IF(asistencia.estado = 'Presente' AND VALUES(estado) = 'Presente' AND asistencia.tipo = 'QR', 'QR', VALUES(tipo)), 
                    fecha = NOW()";
                    
            $stmt = $this->pdo->prepare($sql);
            
            $stmt->bindValue(':id_sesion', $id_sesion, PDO::PARAM_INT);
            $stmt->bindValue(':id_atleta', $id_atleta, PDO::PARAM_INT);
            $stmt->bindValue(':estado', $estado, PDO::PARAM_STR);
            $stmt->bindValue(':justificacion', $justif, PDO::PARAM_STR);
            $stmt->bindValue(':tipo', $tipo, PDO::PARAM_STR);

            $stmt->execute();

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {
          

           if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

             if ($e->getCode() == 23000) {
                $this->agregarError('integridad', 'Los datos vinculados (Atleta o Sesión) fueron alterados y no existen en el sistema.');
                return false;
            }


            error_log("Error de Transacción en Asistencia (Guardar): " . $e->getMessage());
            $this->agregarError('bd', 'Error interno al procesar la asistencia.');
            return false;
        }
    }
}
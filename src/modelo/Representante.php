<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class Representante extends Conexion {
    use ValidacionesTrait;

    public function __construct() {
        // Asegúrate de que el nombre de la BD coincida con la tuya
        parent::__construct('sis_natacion'); 
    }

    public function validarDatos(array $datos, ?string $excluirCedula = null): array {
        $this->resetearErrores();

        $cedula = $datos['cedula'] ?? '';
        $nombres = $datos['nombres'] ?? '';
        $apellidos = $datos['apellidos'] ?? '';

        $this->requerido($cedula, 'cedula');
        $this->soloNumeros($cedula, 'cedula');
        
        if (!$excluirCedula) {
            // Validamos contra la tabla real 'representantes'
            $this->unico($this->getConex1(), $cedula, 'representantes', 'cedula');
        }

        $this->requerido($nombres, 'nombres');
        $this->soloLetras($nombres, 'nombres');

        $this->requerido($apellidos, 'apellidos');
        $this->soloLetras($apellidos, 'apellidos');

        return $this->obtenerErrores();
    }

    /**
     * Registra al representante en la tabla principal y lanza la vinculación
     */
    public function registrarRepresentante(array $datos): bool {
        $conex = $this->getConex1();
        try {
            $conex->beginTransaction();

            // 1. Insertamos usando exactamente tus nuevos atributos
            // Nota: fecha_creacion se omite porque MySQL suele llenarlo solo con CURRENT_TIMESTAMP
            $sql = "INSERT INTO representantes (
                        cedula, nombres, apellidos, parentesco, 
                        telefono_principal, telefono_secundario, 
                        correo, direccion, id_usuario
                    ) VALUES (
                        :cedula, :nombres, :apellidos, :parentesco, 
                        :tel_prin, :tel_sec, 
                        :correo, :direccion, :id_usuario
                    )";
            
            $stmt = $conex->prepare($sql);
            
            // Mapeo inteligente: Conectamos los names del HTML viejo con las columnas nuevas
            $stmt->execute([
                ':cedula'     => $datos['cedula'] ?? '',
                ':nombres'    => $datos['nombres'] ?? '',
                ':apellidos'  => $datos['apellidos'] ?? '',
                ':parentesco' => $datos['parentesco'] ?? '',
                ':tel_prin'   => $datos['telefono_principal'] ?? '',
                ':tel_sec'    => $datos['telefono_emergencia'] ?? '', // En tu HTML se llama emergencia
                ':correo'     => $datos['correo'] ?? '',
                ':direccion'  => $datos['direccion_residencia'] ?? '', // En tu HTML se llama residencia
                ':id_usuario' => $_SESSION['id'] ?? 1 // Capturamos qué usuario del sistema lo registró
            ]);

            // Capturamos el ID autoincrementable
            $id_representante = $conex->lastInsertId();

            // 2. Vinculamos los atletas marcados en los checkboxes
            if (!empty($datos['atletas_ids']) && is_array($datos['atletas_ids'])) {
                $this->vincularAtletas($conex, $id_representante, $datos);
            }

            $conex->commit();
            return true;
        } catch (PDOException $e) {
            $conex->rollBack();
            error_log("Error BD Representante: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Tabla intermedia: atleta_representante
     */
    private function vincularAtletas(PDO $conex, int $id_representante, array $datos) {
        $sql = "INSERT INTO atleta_representante (
                    id_atleta, 
                    id_representante, 
                    autorizacion_medica, 
                    fecha_aut_medica, 
                    autorizacion_imagen, 
                    fecha_aut_imagen, 
                    recibe_notificaciones
                ) VALUES (
                    :id_atleta, 
                    :id_representante, 
                    :aut_medica, 
                    :fecha_medica, 
                    :aut_imagen, 
                    :fecha_imagen, 
                    :notificaciones
                )";
                
        $stmt = $conex->prepare($sql);
        
        foreach ($datos['atletas_ids'] as $id_atleta) {
            $stmt->execute([
                ':id_atleta'        => $id_atleta,
                ':id_representante' => $id_representante,
                ':aut_medica'       => $datos['autorizacion_medica'] ?? 'No',
                ':fecha_medica'     => !empty($datos['fecha_aut_medica']) ? $datos['fecha_aut_medica'] : null,
                ':aut_imagen'       => $datos['autorizacion_imagen'] ?? 'No',
                ':fecha_imagen'     => !empty($datos['fecha_aut_imagen']) ? $datos['fecha_aut_imagen'] : null,
                ':notificaciones'   => $datos['recibe_notificaciones'] ?? 'No'
            ]);
        }
    }

    public function listarRepresentantes(): array {
        $conex = $this->getConex1();
        try {
            // Usamos LEFT JOIN para traer a los atletas y GROUP_CONCAT para unirlos en una sola línea separados por un " | "
            $sql = "SELECT 
                        r.id_representante, 
                        r.cedula, 
                        r.nombres, 
                        r.apellidos, 
                        r.telefono_principal, 
                        r.parentesco,
                        GROUP_CONCAT(CONCAT(a.id_atleta, ':', a.nombres, ' ', a.apellidos) SEPARATOR '|') as atletas_vinculados
                    FROM representantes r
                    LEFT JOIN atleta_representante ar ON r.id_representante = ar.id_representante
                    LEFT JOIN atletas a ON ar.id_atleta = a.id_atleta
                    GROUP BY r.id_representante
                    ORDER BY r.id_representante DESC";
                    
            $stmt = $conex->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error Listando: " . $e->getMessage());
            return [];
        }
    }

/**
 * Obtiene los datos de un representante por su ID
 */
public function obtenerPorId(int $id): ?array {
    $conex = $this->getConex1();
    try {
        $sql = "SELECT * FROM representantes WHERE id_representante = :id";
        $stmt = $conex->prepare($sql);
        $stmt->execute([':id' => $id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ? $resultado : null;
    } catch (PDOException $e) {
         error_log("Error en obtenerPorId: " . $e->getMessage());
        return null;
    }
}

/**
 * Actualiza los datos del representante y refresca sus vinculaciones
 */
public function actualizarRepresentante(array $datos): bool {
    $conex = $this->getConex1();
    try {
        $conex->beginTransaction();

        // 1. Actualizar datos personales en la tabla principal
        $sql = "UPDATE representantes SET 
                    cedula = :cedula, nombres = :nombres, apellidos = :apellidos, 
                    parentesco = :parentesco, telefono_principal = :tel_prin, 
                    telefono_secundario = :tel_sec, correo = :correo, direccion = :direccion
                WHERE id_representante = :id_rep";
                
        $stmt = $conex->prepare($sql);
        $id_representante = (int)($datos['cedula_original'] ?? 0);
        
        $stmt->execute([
            ':cedula'     => $datos['cedula'] ?? '',
            ':nombres'    => $datos['nombres'] ?? '',
            ':apellidos'  => $datos['apellidos'] ?? '',
            ':parentesco' => $datos['parentesco'] ?? '',
            ':tel_prin'   => $datos['telefono_principal'] ?? '',
            ':tel_sec'    => $datos['telefono_emergencia'] ?? '',
            ':correo'     => $datos['correo'] ?? '',
            ':direccion'  => $datos['direccion_residencia'] ?? '',
            ':id_rep'     => $id_representante
        ]);

        // 2. Limpiar vinculaciones anteriores para evitar duplicados o conflictos de llaves
        $sqlDelete = "DELETE FROM atleta_representante WHERE id_representante = :id_rep";
        $stmtDel = $conex->prepare($sqlDelete);
        $stmtDel->execute([':id_rep' => $id_representante]);

        // 3. Insertar las nuevas vinculaciones marcadas en el formulario
        if (!empty($datos['atletas_ids']) && is_array($datos['atletas_ids'])) {
            $this->vincularAtletas($conex, $id_representante, $datos);
        }

        $conex->commit();
        return true;
    } catch (PDOException $e) {
        $conex->rollBack();
        error_log("Error en actualizarRepresentante: " . $e->getMessage());
        return false;
    }
}



}

?>
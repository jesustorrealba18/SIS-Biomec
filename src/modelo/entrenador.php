<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class entrenador extends Conexion {
    use ValidacionesTrait;

    private array $datos = [];

    public function __construct() {
        parent::__construct('sis_natacion');
    }

    public function setDatos(array $datos): void {
        $this->datos = $datos;
    }

    public function setIdEliminar(int $id): void {
        $this->datos['id_entrenador'] = $id;
    }

    public function validarDatos(array $datos, ?string $excluirCedula = null): array {
        $this->resetearErrores();

        $cedula       = $datos['cedula'] ?? '';
        $nombres      = $datos['nombres'] ?? '';
        $apellidos    = $datos['apellidos'] ?? '';
        $fecha        = $datos['fecha_nacimiento'] ?? '';
        $genero       = $datos['genero'] ?? '';
        $correo       = $datos['correo'] ?? '';
        $telefono     = $datos['telefono'] ?? '';
        $direccion    = $datos['direccion'] ?? '';

        $this->requerido($cedula, 'cedula');
        $this->soloNumeros($cedula, 'cedula');
        $this->longitud($cedula, 'cedula', 8, 8);
        
        if ($excluirCedula === null) {
            $this->unico($this->getConex1(), $cedula, 'entrenador', 'cedula');
        } else {
            $conex = $this->getConex1();
            $sql = "SELECT COUNT(*) FROM entrenador WHERE cedula = :cedula AND id_entrenador != :id";
            $stmt = $conex->prepare($sql);
            $stmt->execute([
                ':cedula' => $cedula,
                ':id' => $datos['id_entrenador'] ?? 0
            ]);
            if ($stmt->fetchColumn() > 0) {
                $this->errores['cedula'] = 'La cédula ya está registrada por otro entrenador.';
            }
        }

        $this->requerido($nombres, 'nombres');
        $this->soloLetras($nombres, 'nombres');
        $this->longitud($nombres, 'nombres', 2, 50);

        $this->requerido($apellidos, 'apellidos');
        $this->soloLetras($apellidos, 'apellidos');
        $this->longitud($apellidos, 'apellidos', 2, 50);

        $this->requerido($fecha, 'fecha_nacimiento');
        $this->fechaValida($fecha, 'fecha_nacimiento');
        $this->fechaNoFutura($fecha, 'fecha_nacimiento');
        // Validación de edad mínima 18 años
        $this->edadMinima($fecha, 'fecha_nacimiento', 18);

        $this->requerido($genero, 'genero');
        $this->enEnum($genero, 'genero', ['M', 'F']);

        $this->requerido($correo, 'correo');
        $this->correoValido($correo, 'correo');

        $this->requerido($telefono, 'teléfono');
        $this->soloNumeros($telefono, 'teléfono'); 
        $this->longitud($telefono, 'teléfono', 11, 11);

        $this->requerido($direccion, 'direccion');
        $this->longitud($direccion, 'direccion', 2, 50);

        return $this->obtenerErrores();
    }

    public function registrarEntrenador(array $datos): bool {
        return $this->registrarEntrenadorP($datos);
    }

    public function editarEntrenador(array $datos): bool {
        return $this->editarEntrenadorP($datos);
    }

    public function eliminarEntrenador(int $id): bool {
        return $this->eliminarEntrenadorP($id);
    }

    private function registrarEntrenadorP(array $datos): bool {
        $conex = $this->getConex1();
        try {
            $conex->beginTransaction();

            $sql = "INSERT INTO entrenador (
                        cedula, nombres, apellidos, fecha_nacimiento, genero, correo, telefono, direccion, id_usuario
                    ) VALUES (
                        :cedula, :nombres, :apellidos, :fecha_nacimiento, :genero, :correo, :telefono, :direccion, :id_usuario
                    )";
            
            $stmt = $conex->prepare($sql);
            
            $stmt->execute([
                ':cedula'           => $datos['cedula'] ?? '',
                ':nombres'          => $datos['nombres'] ?? '',
                ':apellidos'        => $datos['apellidos'] ?? '',
                ':fecha_nacimiento' => $datos['fecha_nacimiento'] ?? '',
                ':genero'           => $datos['genero'] ?? '',
                ':correo'           => $datos['correo'] ?? '',
                ':telefono'         => $datos['telefono'] ?? '',
                ':direccion'        => $datos['direccion'] ?? '',
                ':id_usuario'       => null 
            ]);

            $conex->commit();
            return true;
        } catch (PDOException $e) {
            $conex->rollBack();
            error_log("Error BD registrar Entrenador: " . $e->getMessage());
            return false;
        }
    }

    public function listarEntrenador(): array {
        $conex = $this->getConex1();
        try {
            $sql = "SELECT id_entrenador, cedula, nombres, apellidos, fecha_nacimiento, genero, correo, telefono, direccion FROM entrenador";
            $stmt = $conex->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error Listando Entrenadores: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerPorId(int $id_entrenador): ?array {
        $conex = $this->getConex1();
        try {
            $sql = "SELECT *, TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) AS edad 
                    FROM entrenador 
                    WHERE id_entrenador = :id";
            $stmt = $conex->prepare($sql);
            $stmt->execute([':id' => $id_entrenador]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ? $resultado : null;
        } catch (PDOException $e) {
            error_log("Error en obtenerPorId: " . $e->getMessage());
            return null;
        }
    }
    
    private function editarEntrenadorP(array $datos): bool {
        $conex = $this->getConex1();
        try {
            $conex->beginTransaction();

            $sql = "UPDATE entrenador SET 
                        cedula = :cedula, 
                        nombres = :nombres, 
                        apellidos = :apellidos, 
                        fecha_nacimiento = :fecha_nacimiento, 
                        genero = :genero, 
                        correo = :correo, 
                        telefono = :telefono, 
                        direccion = :direccion
                    WHERE id_entrenador = :id_entrenador";
                
            $stmt = $conex->prepare($sql);
            $id_entrenador = isset($datos['id_entrenador']) ? (int)$datos['id_entrenador'] : 0;
            
            $status = $stmt->execute([
                ':cedula'           => $datos['cedula'] ?? '',
                ':nombres'          => $datos['nombres'] ?? '',
                ':apellidos'        => $datos['apellidos'] ?? '',
                ':fecha_nacimiento' => $datos['fecha_nacimiento'] ?? '',
                ':genero'           => $datos['genero'] ?? '',
                ':correo'           => $datos['correo'] ?? '',
                ':telefono'         => $datos['telefono'] ?? '',
                ':direccion'        => $datos['direccion'] ?? '',
                ':id_entrenador'    => $id_entrenador
            ]);
    
            $conex->commit();
            return $status;
        } catch (PDOException $e) {
            $conex->rollBack();
            error_log("Error en actualizarEntrenador: " . $e->getMessage());
            return false;
        }
    }

    private function eliminarEntrenadorP(int $id): bool {
        $conex = $this->getConex1();
        try {
            $sql = "DELETE FROM entrenador WHERE id_entrenador = :id";
            $stmt = $conex->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) { 
            error_log("Error al eliminar entrenador: " . $e->getMessage());
            return false;
        }
    }
}
<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class drills extends Conexion {
    use ValidacionesTrait;

    private array $datos = [];

    public function __construct() {
        parent::__construct('sis_natacion');
    }

    public function setDatos(array $datos): void {
        $this->datos = $datos;
    }

    public function setIdEliminar(int $id): void {
        $this->datos['id_drill'] = $id;
    }

    public function validarDatos(array $datos, $excluirId = null, string $tipoAccion = 'registrar'): array {
        $this->resetearErrores();

        $id_drill           = $datos['id_drill'] ?? '';
        $nombre             = $datos['nombre'] ?? '';
        $estilo             = $datos['estilo'] ?? '';
        $categoria          = $datos['categoria'] ?? '';
        $enfoque_tecnico    = $datos['enfoque_tecnico'] ?? '';
        $descripcion        = $datos['descripcion'] ?? '';
        $instrucciones      = $datos['instrucciones'] ?? '';
        $metraje_sugerido   = $datos['metraje_sugerido'] ?? '';
        $dificultad         = $datos['dificultad'] ?? '';
        $material_requerido = $datos['material_requerido'] ?? '';
        $personalizado      = $datos['personalizado'] ?? 0;
        $id_usuario_creador = $datos['id_usuario_creador'] ?? '';
        $activo             = isset($datos['activo']) ? 1 : 0; 
        $fecha_creacion     = $datos['fecha_creacion'] ?? '';

        if ($tipoAccion === 'editar') {
            $this->requerido($id_drill, 'id_drill');
            if (!empty($id_drill) && (!is_numeric($id_drill) || $id_drill <= 0)) {
                $this->errores['id_drill'] = 'El ID debe ser un número entero positivo.';
            }
        }
        
        if ($tipoAccion === 'registrar') {
            $this->unico($this->getConex1(), $nombre, 'drills', 'nombre');
        }

        $this->requerido($nombre, 'nombre');
        $this->soloLetras($nombre, 'nombre');
        $this->longitud($nombre, 'nombre', 2, 100);

        $this->requerido($estilo, 'estilo');
        $this->enEnum($estilo, 'estilo', ['Libre', 'Espalda', 'Braza', 'Mariposa', 'Combinado', 'Multi']);

        $this->requerido($categoria, 'categoria');
        $this->enEnum($categoria, 'categoria', ['Tecnico', 'Fuerza', 'Velocidad', 'Coordinacion', 'Resistencia']);

        $this->requerido($enfoque_tecnico, 'enfoque_tecnico');
        $this->longitud($enfoque_tecnico, 'enfoque_tecnico', 5, 100);

        $this->requerido($descripcion, 'descripcion');
        $this->longitud($descripcion, 'descripcion', 10, 500);

        $this->requerido($instrucciones, 'instrucciones');
        $this->longitud($instrucciones, 'instrucciones', 5, 1000);

        $this->requerido($metraje_sugerido, 'metraje_sugerido');
        if (!empty($metraje_sugerido)) {
            if (strlen($metraje_sugerido) > 50) {
                $this->errores['metraje_sugerido'] = 'El metraje no puede exceder los 50 caracteres.';
            }
            if (!preg_match('/^[\d\sxXmM\+\-\(\)\/]+$/', $metraje_sugerido)) {
                $this->errores['metraje_sugerido'] = 'Formato inválido. Ejemplos válidos: 50m, 4x50m, 3x100m, 2000m';
            }
        }

        $this->requerido($dificultad, 'dificultad');
        $this->enEnum($dificultad, 'dificultad', ['Basico', 'Intermedio', 'Avanzado']);

        $this->requerido($material_requerido, 'material_requerido');
        $this->enEnum($material_requerido, 'material_requerido', ['Ninguno', 'Pullboy', 'Aletas', 'Tabla', 'Paddle', 'Resistente', 'Pullboy_Aletas', 'Multiple']);

        if (!empty($fecha_creacion)) {
            $this->fechaValida($fecha_creacion, 'fecha_creacion');
        }

        return $this->obtenerErrores();
    }

    public function registrarDrills(): bool {
        return $this->registrarDrillsP($this->datos);
    }

    public function editarDrills(): bool {
        return $this->editarDrillsP($this->datos);
    }

    public function eliminarDrills(): bool {
        $id = $this->datos['id_drill'] ?? 0;
        return $this->eliminarDrillsP($id);
    }

    private function registrarDrillsP(array $datos): bool {
        $conex = $this->getConex1();
        try {
            $conex->beginTransaction();

            $sql = "INSERT INTO drills (
                        nombre, estilo, categoria, enfoque_tecnico, descripcion, 
                        instrucciones, metraje_sugerido, dificultad, material_requerido, 
                        personalizado, id_usuario_creador, activo, fecha_creacion
                    ) VALUES (
                        :nombre, :estilo, :categoria, :enfoque_tecnico, :descripcion, 
                        :instrucciones, :metraje_sugerido, :dificultad, :material_requerido, 
                        :personalizado, :id_usuario_creador, :activo, :fecha_creacion
                    )";
            
            $stmt = $conex->prepare($sql);
            
            $activo = !empty($datos['activo']) ? 1 : 0;
            $personalizado = !empty($datos['personalizado']) ? 1 : 0;

            $stmt->execute([
                ':nombre'             => $datos['nombre'] ?? '',
                ':estilo'             => $datos['estilo'] ?? '',
                ':categoria'          => $datos['categoria'] ?? '',
                ':enfoque_tecnico'    => $datos['enfoque_tecnico'] ?? '',
                ':descripcion'        => $datos['descripcion'] ?? '',
                ':instrucciones'      => $datos['instrucciones'] ?? '',
                ':metraje_sugerido'   => $datos['metraje_sugerido'] ?? '0', // Ahora es string
                ':dificultad'         => $datos['dificultad'] ?? '',
                ':material_requerido' => $datos['material_requerido'] ?? '',
                ':personalizado'      => $personalizado,
                ':id_usuario_creador' => $datos['id_usuario_creador'] ?? ($_SESSION['id'] ?? 1),
                ':activo'             => $activo,
                ':fecha_creacion'     => $datos['fecha_creacion'] ?? date('Y-m-d H:i:s')
            ]);

            $conex->commit();
            return true;
        } catch (PDOException $e) {
            $conex->rollBack();
            error_log("Error al registrar el drill: " . $e->getMessage());
            return false;
        }
    }

    public function listarDrills(): array {
        $conex = $this->getConex1();
        try {
            $sql = "SELECT id_drill, nombre, estilo, categoria, enfoque_tecnico, 
                           descripcion, instrucciones, metraje_sugerido, dificultad, 
                           material_requerido, personalizado, id_usuario_creador, 
                           activo, fecha_creacion 
                    FROM drills 
                    ORDER BY nombre ASC";
            $stmt = $conex->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error listando drills: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerPorId(int $id_drill): ?array {
        $conex = $this->getConex1();
        try {
            $sql = "SELECT * FROM drills WHERE id_drill = :id_drill";
            $stmt = $conex->prepare($sql);
            $stmt->execute([':id_drill' => $id_drill]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ?: null;
        } catch (PDOException $e) {
            error_log("Error en obtenerPorId: " . $e->getMessage());
            return null;
        }
    }
    
    private function editarDrillsP(array $datos): bool {
        $conex = $this->getConex1();
        try {
            $conex->beginTransaction();

            $sql = "UPDATE drills SET 
                        nombre = :nombre, 
                        estilo = :estilo, 
                        categoria = :categoria, 
                        enfoque_tecnico = :enfoque_tecnico, 
                        descripcion = :descripcion, 
                        instrucciones = :instrucciones, 
                        metraje_sugerido = :metraje_sugerido,
                        dificultad = :dificultad, 
                        material_requerido = :material_requerido, 
                        personalizado = :personalizado, 
                        activo = :activo
                    WHERE id_drill = :id_drill";
                
            $stmt = $conex->prepare($sql);
            $id_drill = isset($datos['id_drill']) ? (int)$datos['id_drill'] : 0;
            
            $activo = !empty($datos['activo']) ? 1 : 0;
            $personalizado = !empty($datos['personalizado']) ? 1 : 0;
    
            $status = $stmt->execute([
                ':nombre'             => $datos['nombre'] ?? '',
                ':estilo'             => $datos['estilo'] ?? '',
                ':categoria'          => $datos['categoria'] ?? '',
                ':enfoque_tecnico'    => $datos['enfoque_tecnico'] ?? '',
                ':descripcion'        => $datos['descripcion'] ?? '',
                ':instrucciones'      => $datos['instrucciones'] ?? '',
                ':metraje_sugerido'   => $datos['metraje_sugerido'] ?? '0', // Ahora es string
                ':dificultad'         => $datos['dificultad'] ?? '',
                ':material_requerido' => $datos['material_requerido'] ?? '',
                ':personalizado'      => $personalizado,
                ':activo'             => $activo,
                ':id_drill'           => $id_drill
            ]); 
    
            $conex->commit();
            return $status;
        } catch (PDOException $e) {
            $conex->rollBack();
            error_log("Error en editar drill: " . $e->getMessage());
            return false;
        }
    }

    private function eliminarDrillsP(int $id): bool {
        $conex = $this->getConex1();
        try {
            $checkSql = "SELECT id_drill FROM drills WHERE id_drill = :id";
            $checkStmt = $conex->prepare($checkSql);
            $checkStmt->execute([':id' => $id]);
            
            if (!$checkStmt->fetch()) {
                error_log("Drill con ID $id no encontrado");
                return false;
            }

            $sql = "DELETE FROM drills WHERE id_drill = :id";
            $stmt = $conex->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) { 
            error_log("Error al eliminar drill: " . $e->getMessage());
            return false; 
        }
    }
}
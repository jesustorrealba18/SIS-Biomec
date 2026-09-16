<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class drills extends Conexion {
    use ValidacionesTrait;

    private const CAMPOS_PERMITIDOS = ['id_drill', 'nombre', 'estilo', 'categoria', 'enfoque_tecnico', 'descripcion', 'instrucciones',
        'metraje_sugerido', 'dificultad', 'material_requerido', 'personalizado', 'id_usuario_creador', 'activo', 'fecha_creacion',
    ];

    private const ESTILOS_VALIDOS = ['Libre', 'Espalda', 'Braza', 'Mariposa', 'Combinado', 'Multi'];
    private const CATEGORIAS_VALIDAS = ['Tecnico', 'Fuerza', 'Velocidad', 'Coordinacion', 'Resistencia'];
    private const DIFICULTADES_VALIDAS = ['Basico', 'Intermedio', 'Avanzado'];
    private const MATERIALES_VALIDOS = ['Ninguno', 'Pullboy', 'Aletas', 'Tabla', 'Paddle', 'Resistente', 'Pullboy_Aletas', 'Multiple'];

    private array $datos = [];

    public function __construct() {
        parent::__construct('sis_natacion');
    }

    public function setDatos(array $datos): void {
        $this->datos = array_intersect_key($datos, array_flip(self::CAMPOS_PERMITIDOS));
    }

    public function setIdEliminar(int $id): void {
        $this->datos['id_drill'] = $id;
    }

    public function validarDatos(string $tipoAccion = 'registrar', ?int $excluirId = null): array {
        $this->resetearErrores();

        $id_drill           = $this->datos['id_drill'] ?? '';
        $nombre             = $this->datos['nombre'] ?? '';
        $estilo             = $this->datos['estilo'] ?? '';
        $categoria          = $this->datos['categoria'] ?? '';
        $enfoque_tecnico    = $this->datos['enfoque_tecnico'] ?? '';
        $descripcion        = $this->datos['descripcion'] ?? '';
        $instrucciones      = $this->datos['instrucciones'] ?? '';
        $metraje_sugerido   = $this->datos['metraje_sugerido'] ?? '';
        $dificultad         = $this->datos['dificultad'] ?? '';
        $material_requerido = $this->datos['material_requerido'] ?? '';
        $fecha_creacion     = $this->datos['fecha_creacion'] ?? '';

        if ($tipoAccion === 'editar') {
            $this->requerido($id_drill, 'id_drill');
            if (!empty($id_drill) && (!is_numeric($id_drill) || $id_drill <= 0)) {
                $this->errores['id_drill'] = 'El ID debe ser un número entero positivo.';
            }
        }

        $this->requerido($nombre, 'nombre');
        $this->soloLetras($nombre, 'nombre');
        $this->longitud($nombre, 'nombre', 2, 100);

       if (empty($this->errores['nombre'])) {
        $this->unico(
        $this->getConex1(),
        $nombre,
        'drills',
        'nombre',
        ($tipoAccion === 'editar') ? $excluirId : null,
        'id_drill'
           );
       }

        $this->requerido($estilo, 'estilo');
        $this->enEnum($estilo, 'estilo', self::ESTILOS_VALIDOS);

        $this->requerido($categoria, 'categoria');
        $this->enEnum($categoria, 'categoria', self::CATEGORIAS_VALIDAS);

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
            } elseif (!preg_match('/^[\d\sxXmM\+\-\(\)\/]+$/', $metraje_sugerido)) {
                $this->errores['metraje_sugerido'] = 'Formato inválido. Ejemplos válidos: 50m, 4x50m, 3x100m, 2000m';
            }
        }

        $this->requerido($dificultad, 'dificultad');
        $this->enEnum($dificultad, 'dificultad', self::DIFICULTADES_VALIDAS);

        $this->requerido($material_requerido, 'material_requerido');
        $this->enEnum($material_requerido, 'material_requerido', self::MATERIALES_VALIDOS);

        if (!empty($fecha_creacion)) {
            $this->fechaValida($fecha_creacion, 'fecha_creacion');
        }

        return $this->obtenerErrores();
    }

    public function registrarDrills(): bool {
        $errores = $this->validarDatos('registrar');
        if (!empty($errores)) {
            return false;
        }
        return $this->registrarDrillsP();
    }

    public function editarDrills(): bool {
        $id = (int)($this->datos['id_drill'] ?? 0);
        if ($id <= 0) {
            return false;
        }
        $errores = $this->validarDatos('editar', $id);
        if (!empty($errores)) {
            return false;
        }
        return $this->editarDrillsP();
    }

    public function eliminarDrills(): bool {
        $id = (int)($this->datos['id_drill'] ?? 0);
        if ($id <= 0) {
            return false;
        }
        return $this->eliminarDrillsP($id);
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
        if ($id_drill <= 0) {
            return null;
        }
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
    
    private function registrarDrillsP(): bool {
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

            $activo        = !empty($this->datos['activo']) ? 1 : 0;
            $personalizado = !empty($this->datos['personalizado']) ? 1 : 0;

            $stmt->execute([
                ':nombre'             => $this->datos['nombre'] ?? '',
                ':estilo'             => $this->datos['estilo'] ?? '',
                ':categoria'          => $this->datos['categoria'] ?? '',
                ':enfoque_tecnico'    => $this->datos['enfoque_tecnico'] ?? '',
                ':descripcion'        => $this->datos['descripcion'] ?? '',
                ':instrucciones'      => $this->datos['instrucciones'] ?? '',
                ':metraje_sugerido'   => $this->datos['metraje_sugerido'] ?? '0',
                ':dificultad'         => $this->datos['dificultad'] ?? '',
                ':material_requerido' => $this->datos['material_requerido'] ?? '',
                ':personalizado'      => $personalizado,
                ':id_usuario_creador' => $this->datos['id_usuario_creador'] ?? ($_SESSION['id'] ?? 1),
                ':activo'             => $activo,
                ':fecha_creacion'     => $this->datos['fecha_creacion'] ?? date('Y-m-d H:i:s'),
            ]);

            $conex->commit();
            return true;
        } catch (PDOException $e) {
            $conex->rollBack();
            error_log("Error al registrar el drill: " . $e->getMessage());
            return false;
        }
    }

    private function editarDrillsP(): bool {
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

            $id_drill      = (int)($this->datos['id_drill'] ?? 0);
            $activo        = !empty($this->datos['activo']) ? 1 : 0;
            $personalizado = !empty($this->datos['personalizado']) ? 1 : 0;

            $status = $stmt->execute([
                ':nombre'             => $this->datos['nombre'] ?? '',
                ':estilo'             => $this->datos['estilo'] ?? '',
                ':categoria'          => $this->datos['categoria'] ?? '',
                ':enfoque_tecnico'    => $this->datos['enfoque_tecnico'] ?? '',
                ':descripcion'        => $this->datos['descripcion'] ?? '',
                ':instrucciones'      => $this->datos['instrucciones'] ?? '',
                ':metraje_sugerido'   => $this->datos['metraje_sugerido'] ?? '0',
                ':dificultad'         => $this->datos['dificultad'] ?? '',
                ':material_requerido' => $this->datos['material_requerido'] ?? '',
                ':personalizado'      => $personalizado,
                ':activo'             => $activo,
                ':id_drill'           => $id_drill,
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
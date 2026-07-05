<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class Grupo extends Conexion {
    use ValidacionesTrait;

    private array $datos = [];
    private ?string $ultimoError = null;

    public function __construct() {
        parent::__construct('sis_natacion');
    }

    public function setDatos(array $datos): void {
        $this->datos = $datos;
    }

    public function getDatos(): array {
        return $this->datos;
    }

    public function obtenerUltimoError(): ?string {
        return $this->ultimoError;
    }

    private function setUltimoError(string $error): void {
        $this->ultimoError = $error;
    }

    public function validarDatos(array $datos, $excluirId = null): array {
        $this->resetearErrores();

        $nombre = $datos['nombre'] ?? '';
        $this->requerido($nombre, 'nombre');
        
        if (!empty($nombre) && strlen($nombre) > 100) {
            $this->agregarError('nombre', 'El nombre no puede tener más de 100 caracteres.');
        }

        if (!empty($nombre) && !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s0-9\-]+$/', $nombre)) {
            $this->agregarError('nombre', 'El nombre solo puede contener letras, números, guiones y espacios.');
        }

        return $this->obtenerErrores();
    }

    public function validarAsignacion(array $datos): array {
        $this->resetearErrores();

        if (empty($datos['id_grupo']) || !is_numeric($datos['id_grupo'])) {
            $this->agregarError('id_grupo', 'El grupo es requerido.');
        }

        if (empty($datos['atletas']) || !is_array($datos['atletas']) || count($datos['atletas']) === 0) {
            $this->agregarError('atletas', 'Debe seleccionar al menos un atleta.');
        }

        if (!empty($datos['atletas']) && is_array($datos['atletas'])) {
            foreach ($datos['atletas'] as $id_atleta) {
                if (!is_numeric($id_atleta) || $id_atleta <= 0) {
                    $this->agregarError('atletas', 'ID de atleta inválido.');
                    break;
                }
            }
        }

        return $this->obtenerErrores();
    }

    public function verificarNombreExistente(string $nombre, ?int $id_excluir = null): bool {
        $conex = $this->pdo;
        try {
            $sql = "SELECT COUNT(*) FROM grupos_entrenamiento 
                    WHERE nombre = :nombre AND activo = 1";
            
            if ($id_excluir) {
                $sql .= " AND id_grupo != :id_excluir";
            }
            
            $stmt = $conex->prepare($sql);
            $params = [':nombre' => $nombre];
            
            if ($id_excluir) {
                $params[':id_excluir'] = $id_excluir;
            }
            
            $stmt->execute($params);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function registrarGrupo(): bool {
        return $this->registrarGrupoP($this->datos);
    }

    public function editarGrupo(): bool {
        return $this->editarGrupoP($this->datos);
    }

    private function registrarGrupoP(array $datos): bool {
        $conex = $this->pdo;
        try {
            if ($this->verificarNombreExistente($datos['nombre'] ?? '')) {
                $this->setUltimoError('El nombre del grupo ya existe.');
                return false;
            }

            $sql = "INSERT INTO grupos_entrenamiento (
                        nombre, descripcion, id_entrenador, activo
                    ) VALUES (
                        :nombre, :descripcion, :id_entrenador, 1
                    )";
            
            $stmt = $conex->prepare($sql);
            
            return $stmt->execute([
                ':nombre'        => trim($datos['nombre'] ?? ''),
                ':descripcion'   => trim($datos['descripcion'] ?? ''),
                ':id_entrenador' => !empty($datos['id_entrenador']) ? (int)$datos['id_entrenador'] : null
            ]);
        } catch (PDOException $e) {
            $this->setUltimoError($e->getMessage());
            return false;
        }
    }

    private function editarGrupoP(array $datos): bool {
        $conex = $this->pdo;
        try {
            $id_grupo = (int)($datos['id_grupo_original'] ?? 0);
            
            if ($this->verificarNombreExistente($datos['nombre'] ?? '', $id_grupo)) {
                $this->setUltimoError('El nombre del grupo ya existe.');
                return false;
            }

            $sql = "UPDATE grupos_entrenamiento SET 
                        nombre = :nombre, 
                        descripcion = :descripcion, 
                        id_entrenador = :id_entrenador
                    WHERE id_grupo = :id_grupo";
                    
            $stmt = $conex->prepare($sql);
            
            return $stmt->execute([
                ':nombre'        => trim($datos['nombre'] ?? ''),
                ':descripcion'   => trim($datos['descripcion'] ?? ''),
                ':id_entrenador' => !empty($datos['id_entrenador']) ? (int)$datos['id_entrenador'] : null,
                ':id_grupo'      => $id_grupo
            ]);
        } catch (PDOException $e) {
            $this->setUltimoError($e->getMessage());
            return false;
        }
    }

    public function cambiarEstadoGrupo(int $id, int $estado): bool {
        $conex = $this->pdo;
        try {
            $sql = "UPDATE grupos_entrenamiento SET activo = :estado WHERE id_grupo = :id";
            $stmt = $conex->prepare($sql);
            return $stmt->execute([':estado' => $estado, ':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function obtenerPorId(int $id): ?array {
        $conex = $this->pdo;
        try {
            $sql = "SELECT 
                        g.*,
                        CONCAT(e.nombres, ' ', e.apellidos) as entrenador_nombre
                    FROM grupos_entrenamiento g
                    LEFT JOIN entrenador e ON g.id_entrenador = e.id_entrenador
                    WHERE g.id_grupo = :id";
            $stmt = $conex->prepare($sql);
            $stmt->execute([':id' => $id]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ? $resultado : null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function listarGrupos(int $estado = 1): array {
        $conex = $this->pdo;
        try {
            $sql = "SELECT 
                        g.id_grupo, 
                        g.nombre, 
                        g.descripcion, 
                        g.activo, 
                        g.id_entrenador,
                        CONCAT(e.nombres, ' ', e.apellidos) as entrenador_nombre,
                        e.cedula as entrenador_cedula,
                        COUNT(ga.id_atleta) as total_atletas
                    FROM grupos_entrenamiento g
                    LEFT JOIN entrenador e ON g.id_entrenador = e.id_entrenador
                    LEFT JOIN grupo_atleta ga ON g.id_grupo = ga.id_grupo
                    WHERE g.activo = :estado 
                    GROUP BY g.id_grupo
                    ORDER BY g.id_grupo DESC";
                    
            $stmt = $conex->prepare($sql);
            $stmt->execute([':estado' => $estado]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function listarGruposPorEntrenador(int $id_entrenador): array {
        $conex = $this->pdo;
        try {
            $esAdmin = false;
            if (isset($_SESSION['rol'])) {
                $esAdmin = ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 'admin' || $_SESSION['rol'] == 'Administrador');
            }
            
            if ($esAdmin) {
                $sql = "SELECT id_grupo, nombre, descripcion, activo 
                        FROM grupos_entrenamiento 
                        WHERE activo = 1
                        ORDER BY nombre";
                $stmt = $conex->prepare($sql);
                $stmt->execute();
            } else {
                $sql = "SELECT id_grupo, nombre, descripcion, activo 
                        FROM grupos_entrenamiento 
                        WHERE id_entrenador = :id_entrenador AND activo = 1
                        ORDER BY nombre";
                $stmt = $conex->prepare($sql);
                $stmt->execute([':id_entrenador' => $id_entrenador]);
            }
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function listarGruposConConteo(): array {
        $conex = $this->pdo;
        try {
            $sql = "SELECT 
                        g.id_grupo,
                        g.nombre,
                        g.descripcion,
                        g.activo,
                        CONCAT(e.nombres, ' ', e.apellidos) as entrenador,
                        COUNT(ga.id_atleta) as total_atletas
                    FROM grupos_entrenamiento g
                    LEFT JOIN entrenador e ON g.id_entrenador = e.id_entrenador
                    LEFT JOIN grupo_atleta ga ON g.id_grupo = ga.id_grupo
                    WHERE g.activo = 1
                    GROUP BY g.id_grupo
                    ORDER BY g.nombre ASC";
            
            $stmt = $conex->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function listarEntrenadoresDisponibles(): array {
        $conex = $this->pdo;
        try {
            if (!$conex) {
                return [];
            }
            
            $sql = "SELECT id_entrenador, nombres, apellidos, cedula 
                    FROM entrenador 
                    ORDER BY nombres ASC";
            
            $stmt = $conex->prepare($sql);
            $stmt->execute();
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!is_array($resultados)) {
                return [];
            }
            
            $limpios = [];
            foreach ($resultados as $entrenador) {
                $limpios[] = [
                    'id_entrenador' => (int)$entrenador['id_entrenador'],
                    'nombres' => trim($entrenador['nombres'] ?? ''),
                    'apellidos' => trim($entrenador['apellidos'] ?? ''),
                    'cedula' => trim($entrenador['cedula'] ?? '')
                ];
            }
            
            return $limpios;
            
        } catch (PDOException $e) {
            return [];
        } catch (Exception $e) {
            return [];
        }
    }

    public function listarCategorias(): array {
        $conex = $this->pdo;
        try {
            $sql = "SELECT 
                        id_categoria, 
                        nombre, 
                        edad_minima, 
                        edad_maxima,
                        CONCAT(nombre, ' (', edad_minima, '-', edad_maxima, ' años)') as nombre_completo
                    FROM categorias_feveda 
                    WHERE activa = 1
                    ORDER BY edad_minima ASC";
            
            $stmt = $conex->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function listarAtletasPorCategoria(int $id_categoria): array {
        $conex = $this->pdo;
        try {
            $sql = "SELECT 
                        a.id_atleta,
                        a.nombres,
                        a.apellidos,
                        a.cedula,
                        a.fecha_nacimiento,
                        a.id_categoria,
                        c.nombre as categoria_nombre,
                        TIMESTAMPDIFF(YEAR, a.fecha_nacimiento, CURDATE()) as edad
                    FROM atletas a
                    LEFT JOIN categorias_feveda c ON a.id_categoria = c.id_categoria
                    WHERE a.estado = 1
                    AND a.id_categoria = :id_categoria
                    AND NOT EXISTS (
                        SELECT 1 
                        FROM grupo_atleta ga 
                        WHERE ga.id_atleta = a.id_atleta
                    )
                    ORDER BY a.apellidos, a.nombres ASC";
        
            $stmt = $conex->prepare($sql);
            $stmt->execute([':id_categoria' => $id_categoria]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function asignarGrupoAtletas(array $datos): bool {
        $conex = $this->pdo;
        
        try {
            $conex->beginTransaction();

            $id_grupo = (int)$datos['id_grupo'];
            $atletas = $datos['atletas'];
            $fecha_asignacion = date('Y-m-d');

            if (count($atletas) > 0) {
                // Eliminar asignaciones existentes
                $sqlEliminar = "DELETE FROM grupo_atleta 
                                WHERE id_atleta IN (" . implode(',', array_fill(0, count($atletas), '?')) . ")";
                
                $stmtEliminar = $conex->prepare($sqlEliminar);
                $stmtEliminar->execute($atletas);

                // Insertar nuevas asignaciones
                $sqlInsert = "INSERT INTO grupo_atleta (id_grupo, id_atleta, fecha_asignacion) 
                              VALUES (?, ?, ?)";
                
                $stmtInsert = $conex->prepare($sqlInsert);
                
                foreach ($atletas as $id_atleta) {
                    $stmtInsert->execute([
                        $id_grupo,
                        (int)$id_atleta,
                        $fecha_asignacion
                    ]);
                }
            }

            $conex->commit();
            return true;

        } catch (PDOException $e) {
            if ($conex->inTransaction()) {
                $conex->rollBack();
            }
            $this->setUltimoError($e->getMessage());
            return false;
        }
    }

    public function desasignarAtletas(array $atletas): bool {
        $conex = $this->pdo;
        try {
            if (count($atletas) === 0) {
                return true;
            }

            $sql = "DELETE FROM grupo_atleta 
                    WHERE id_atleta IN (" . implode(',', array_fill(0, count($atletas), '?')) . ")";
            
            $stmt = $conex->prepare($sql);
            return $stmt->execute($atletas);
        } catch (PDOException $e) {
            $this->setUltimoError($e->getMessage());
            return false;
        }
    }

    public function cambiarGrupoAtleta(int $id_atleta, int $id_nuevo_grupo): bool {
        $conex = $this->pdo;
        try {
            $conex->beginTransaction();

            $sqlEliminar = "DELETE FROM grupo_atleta WHERE id_atleta = ?";
            $stmtEliminar = $conex->prepare($sqlEliminar);
            $stmtEliminar->execute([$id_atleta]);

            $sqlInsert = "INSERT INTO grupo_atleta (id_grupo, id_atleta, fecha_assignacion) 
                          VALUES (?, ?, CURDATE())";
            $stmtInsert = $conex->prepare($sqlInsert);
            $stmtInsert->execute([$id_nuevo_grupo, $id_atleta]);

            $conex->commit();
            return true;

        } catch (PDOException $e) {
            if ($conex->inTransaction()) {
                $conex->rollBack();
            }
            $this->setUltimoError($e->getMessage());
            return false;
        }
    }

    public function listarAtletasDisponibles(): array {
    $conex = $this->pdo;
    try {
        $sql = "SELECT 
                    a.id_atleta,
                    a.nombres,
                    a.apellidos,
                    a.cedula,
                    a.fecha_nacimiento,
                    a.id_categoria,
                    c.nombre as categoria_nombre,
                    TIMESTAMPDIFF(YEAR, a.fecha_nacimiento, CURDATE()) as edad
                FROM atletas a
                LEFT JOIN categorias_feveda c ON a.id_categoria = c.id_categoria
                WHERE a.estado = 1
                AND NOT EXISTS (
                    SELECT 1 
                    FROM grupo_atleta ga 
                    INNER JOIN grupos_entrenamiento g ON ga.id_grupo = g.id_grupo
                    WHERE ga.id_atleta = a.id_atleta 
                    AND g.activo = 1
                )
                ORDER BY a.apellidos, a.nombres ASC";
        
        $stmt = $conex->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

 public function listarAtletasPorGrupo(int $id_grupo): array {
    $conex = $this->pdo;
    try {
        $sql = "SELECT 
                    a.id_atleta,
                    a.nombres,
                    a.apellidos,
                    a.cedula,
                    a.fecha_nacimiento,
                    a.id_categoria,
                    c.nombre as categoria_nombre,
                    TIMESTAMPDIFF(YEAR, a.fecha_nacimiento, CURDATE()) as edad,
                    ga.fecha_assignacion
                FROM grupo_atleta ga
                INNER JOIN atletas a ON ga.id_atleta = a.id_atleta
                LEFT JOIN categorias_feveda c ON a.id_categoria = c.id_categoria
                WHERE ga.id_grupo = :id_grupo 
                AND a.estado = 1
                ORDER BY a.apellidos, a.nombres ASC";
        
        $stmt = $conex->prepare($sql);
        $stmt->execute([':id_grupo' => $id_grupo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error listarAtletasPorGrupo: " . $e->getMessage());
        return [];
    }
}

    public function listarTodosAtletas(): array {
        $conex = $this->pdo;
        try {
            $sql = "SELECT 
                        a.id_atleta,
                        a.nombres,
                        a.apellidos,
                        a.cedula,
                        a.fecha_nacimiento,
                        a.estado,
                        a.id_categoria,
                        c.nombre as categoria_nombre,
                        TIMESTAMPDIFF(YEAR, a.fecha_nacimiento, CURDATE()) as edad,
                        ga.id_grupo as grupo_actual,
                        g.nombre as nombre_grupo
                    FROM atletas a
                    LEFT JOIN categorias_feveda c ON a.id_categoria = c.id_categoria
                    LEFT JOIN grupo_atleta ga ON a.id_atleta = ga.id_atleta
                    LEFT JOIN grupos_entrenamiento g ON ga.id_grupo = g.id_grupo
                    WHERE a.estado = 1
                    ORDER BY a.apellidos, a.nombres ASC";
            
            $stmt = $conex->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function listarAtletasPorEdad(int $edad_min, int $edad_max): array {
        $conex = $this->pdo;
        try {
            $sql = "SELECT 
                        a.id_atleta,
                        a.nombres,
                        a.apellidos,
                        a.fecha_nacimiento,
                        a.id_categoria,
                        c.nombre as categoria_nombre,
                        TIMESTAMPDIFF(YEAR, a.fecha_nacimiento, CURDATE()) as edad
                    FROM atletas a
                    LEFT JOIN categorias_feveda c ON a.id_categoria = c.id_categoria
                    WHERE a.estado = 1
                    AND TIMESTAMPDIFF(YEAR, a.fecha_nacimiento, CURDATE()) BETWEEN ? AND ?
                    AND NOT EXISTS (
                        SELECT 1 FROM grupo_atleta ga 
                        WHERE ga.id_atleta = a.id_atleta
                    )
                    ORDER BY edad ASC, a.apellidos ASC";
            
            $stmt = $conex->prepare($sql);
            $stmt->execute([$edad_min, $edad_max]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function atletaTieneAsignacion(int $id_atleta): bool {
        $conex = $this->pdo;
        try {
            $sql = "SELECT COUNT(*) FROM grupo_atleta WHERE id_atleta = ?";
            $stmt = $conex->prepare($sql);
            $stmt->execute([$id_atleta]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function obtenerGrupoActualAtleta(int $id_atleta): ?array {
        $conex = $this->pdo;
        try {
            $sql = "SELECT 
                        g.id_grupo,
                        g.nombre,
                        g.descripcion,
                        ga.fecha_assignacion
                    FROM grupo_atleta ga
                    INNER JOIN grupos_entrenamiento g ON ga.id_grupo = g.id_grupo
                    WHERE ga.id_atleta = ?";
            
            $stmt = $conex->prepare($sql);
            $stmt->execute([$id_atleta]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }
}
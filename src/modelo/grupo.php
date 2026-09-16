<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;
use PDOException;

class Grupo extends Conexion {
    use ValidacionesTrait;

    private const CAMPOS_PERMITIDOS = ['id_grupo', 'id_grupo_original', 'nombre', 'descripcion', 'id_entrenador', 'activo',
    ];

    private const CAMPOS_ASIGNACION = ['id_grupo', 'atletas',
    ];

    private array $datos = [];
    private array $datosAsignacion = [];
    private ?string $ultimoError = null;

    public function __construct() {
        parent::__construct('sis_natacion');
    }

    public function setDatos(array $datos): void {
        $this->datos = array_intersect_key($datos, array_flip(self::CAMPOS_PERMITIDOS));
    }

    public function getDatos(): array {
        return $this->datos;
    }

    public function setDatosAsignacion(array $datos): void {
        $this->datosAsignacion = [
            'id_grupo' => isset($datos['id_grupo']) ? (int)$datos['id_grupo'] : 0,
            'atletas'  => isset($datos['atletas']) && is_array($datos['atletas']) ? $datos['atletas'] : [],
        ];
    }

    public function obtenerUltimoError(): ?string {
        return $this->ultimoError;
    }

    private function setUltimoError(string $error): void {
        $this->ultimoError = $error;
    }

    public function validarDatos($excluirId = null): array {
        $this->resetearErrores();

        $nombre        = $this->datos['nombre'] ?? '';
        $descripcion   = $this->datos['descripcion'] ?? '';
        $id_entrenador = $this->datos['id_entrenador'] ?? '';

        $this->requerido($nombre, 'nombre');

        if (!empty($nombre)) {
            if (strlen($nombre) > 50) {
                $this->agregarError('nombre', 'El nombre no puede tener más de 50 caracteres.');
            } elseif (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s0-9\-]+$/', $nombre)) {
                $this->agregarError('nombre', 'El nombre solo puede contener letras, números, guiones y espacios.');
            }
        }

        if (!empty($descripcion) && strlen($descripcion) > 255) {
            $this->agregarError('descripcion', 'La descripción no puede tener más de 255 caracteres.');
        }

        if (!empty($id_entrenador) && (!is_numeric($id_entrenador) || (int)$id_entrenador <= 0)) {
            $this->agregarError('id_entrenador', 'Entrenador inválido.');
        }

        return $this->obtenerErrores();
    }

    public function validarAsignacion(): array {
        $this->resetearErrores();

        $id_grupo = $this->datosAsignacion['id_grupo'] ?? 0;
        $atletas  = $this->datosAsignacion['atletas'] ?? [];

        if (empty($id_grupo) || !is_numeric($id_grupo) || $id_grupo <= 0) {
            $this->agregarError('id_grupo', 'El grupo es requerido.');
        }

        if (empty($atletas) || !is_array($atletas) || count($atletas) === 0) {
            $this->agregarError('atletas', 'Debe seleccionar al menos un atleta.');
        } else {
            foreach ($atletas as $id_atleta) {
                if (!is_numeric($id_atleta) || (int)$id_atleta <= 0) {
                    $this->agregarError('atletas', 'ID de atleta inválido.');
                    break;
                }
            }
        }

        return $this->obtenerErrores();
    }

    public function verificarNombreExistente(string $nombre, ?int $id_excluir = null): bool {
        if (empty($nombre)) {
            return false;
        }
        $conex = $this->getConex1();
        try {
            $sql = "SELECT COUNT(*) FROM grupos_entrenamiento 
                    WHERE nombre = :nombre AND activo = 1";
            $params = [':nombre' => $nombre];

            if ($id_excluir !== null && $id_excluir > 0) {
                $sql .= " AND id_grupo != :id_excluir";
                $params[':id_excluir'] = $id_excluir;
            }

            $stmt = $conex->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error en verificarNombreExistente: " . $e->getMessage());
            return false;
        }
    }

    public function registrarGrupo(): bool {
        $errores = $this->validarDatos();
        if (!empty($errores)) {
            return false;
        }
        if ($this->verificarNombreExistente($this->datos['nombre'] ?? '')) {
            $this->setUltimoError('El nombre del grupo ya existe.');
            return false;
        }
        return $this->registrarGrupoP();
    }

    public function editarGrupo(): bool {
        $id = (int)($this->datos['id_grupo_original'] ?? 0);
        if ($id <= 0) {
            return false;
        }
        $errores = $this->validarDatos($id);
        if (!empty($errores)) {
            return false;
        }
        if ($this->verificarNombreExistente($this->datos['nombre'] ?? '', $id)) {
            $this->setUltimoError('El nombre del grupo ya existe.');
            return false;
        }
        return $this->editarGrupoP();
    }

    public function cambiarEstadoGrupo(int $id, int $estado): bool {
        if ($id <= 0) {
            return false;
        }
        if (!in_array($estado, [0, 1], true)) {
            return false;
        }
        return $this->cambiarEstadoGrupoP($id, $estado);
    }

    public function asignarGrupoAtletas(): bool {
        $errores = $this->validarAsignacion();
        if (!empty($errores)) {
            return false;
        }
        return $this->asignarGrupoAtletasP();
    }

    public function desasignarAtletas(array $atletas): bool {
        if (empty($atletas)) {
            return true;
        }
        $ids = [];
        foreach ($atletas as $id) {
            $id = (int)$id;
            if ($id > 0) {
                $ids[] = $id;
            }
        }
        if (empty($ids)) {
            return false;
        }
        return $this->desasignarAtletasP($ids);
    }

    public function cambiarGrupoAtleta(int $id_atleta, int $id_nuevo_grupo): bool {
        if ($id_atleta <= 0 || $id_nuevo_grupo <= 0) {
            return false;
        }
        return $this->cambiarGrupoAtletaP($id_atleta, $id_nuevo_grupo);
    }

    public function obtenerPorId(int $id): ?array {
        if ($id <= 0) {
            return null;
        }
        $conex = $this->getConex1();
        try {
            $sql = "SELECT 
                        g.*,
                        CONCAT(e.nombres, ' ', e.apellidos) as entrenador_nombre,
                        e.cedula as entrenador_cedula
                    FROM grupos_entrenamiento g
                    LEFT JOIN entrenador e ON g.id_entrenador = e.id_entrenador
                    WHERE g.id_grupo = :id";
            $stmt = $conex->prepare($sql);
            $stmt->execute([':id' => $id]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ?: null;
        } catch (PDOException $e) {
            error_log("Error en obtenerPorId: " . $e->getMessage());
            return null;
        }
    }

    public function listarGrupos(int $estado = 1): array {
        $conex = $this->getConex1();
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
            error_log("Error en listarGrupos: " . $e->getMessage());
            return [];
        }
    }

    public function listarGruposPorEntrenador(int $id_entrenador): array {
        $conex = $this->getConex1();
        try {
            $esAdmin = false;
            if (isset($_SESSION['rol'])) {
                $esAdmin = in_array($_SESSION['rol'], [1, 'admin', 'Administrador'], true);
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
            error_log("Error en listarGruposPorEntrenador: " . $e->getMessage());
            return [];
        }
    }

    public function listarGruposConConteo(): array {
        $conex = $this->getConex1();
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
            error_log("Error en listarGruposConConteo: " . $e->getMessage());
            return [];
        }
    }

    public function listarEntrenadoresDisponibles(): array {
        $conex = $this->getConex1();
        try {
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
                    'nombres'       => trim($entrenador['nombres'] ?? ''),
                    'apellidos'     => trim($entrenador['apellidos'] ?? ''),
                    'cedula'        => trim($entrenador['cedula'] ?? ''),
                ];
            }
            return $limpios;
        } catch (PDOException $e) {
            error_log("Error en listarEntrenadoresDisponibles: " . $e->getMessage());
            return [];
        }
    }

    public function listarCategorias(): array {
        $conex = $this->getConex1();
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
            error_log("Error en listarCategorias: " . $e->getMessage());
            return [];
        }
    }

public function listarAtletasPorCategoria(int $id_categoria): array {
    if ($id_categoria <= 0) {
        return [];
    }
    $conex = $this->getConex1();
    try {
        $sql = "SELECT 
                    a.id_atleta, a.nombres, a.apellidos, a.cedula, a.fecha_nacimiento,
                    a.id_categoria, c.nombre as categoria_nombre,
                    TIMESTAMPDIFF(YEAR, a.fecha_nacimiento, CURDATE()) as edad
                FROM atletas a
                LEFT JOIN categorias_feveda c ON a.id_categoria = c.id_categoria
                WHERE a.estado = 1
                AND a.id_categoria = :id_categoria
                AND NOT EXISTS (
                    SELECT 1 
                    FROM grupo_atleta ga 
                    INNER JOIN grupos_entrenamiento g ON ga.id_grupo = g.id_grupo
                    WHERE ga.id_atleta = a.id_atleta AND g.activo = 1
                )
                ORDER BY a.apellidos, a.nombres ASC";
        $stmt = $conex->prepare($sql);
        $stmt->execute([':id_categoria' => $id_categoria]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error en listarAtletasPorCategoria: " . $e->getMessage());
        return [];
    }
}

    public function listarAtletasDisponibles(): array {
        $conex = $this->getConex1();
        try {
            $sql = "SELECT 
                        a.id_atleta, a.nombres, a.apellidos, a.cedula, a.fecha_nacimiento,
                        a.id_categoria, c.nombre as categoria_nombre,
                        TIMESTAMPDIFF(YEAR, a.fecha_nacimiento, CURDATE()) as edad
                    FROM atletas a
                    LEFT JOIN categorias_feveda c ON a.id_categoria = c.id_categoria
                    WHERE a.estado = 1
                    AND NOT EXISTS (
                        SELECT 1 
                        FROM grupo_atleta ga 
                        INNER JOIN grupos_entrenamiento g ON ga.id_grupo = g.id_grupo
                        WHERE ga.id_atleta = a.id_atleta AND g.activo = 1
                    )
                    ORDER BY a.apellidos, a.nombres ASC";
            $stmt = $conex->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en listarAtletasDisponibles: " . $e->getMessage());
            return [];
        }
    }

    public function listarAtletasPorGrupo(int $id_grupo): array {
        if ($id_grupo <= 0) {
            return [];
        }
        $conex = $this->getConex1();
        try {
            $sql = "SELECT 
                        a.id_atleta, a.nombres, a.apellidos, a.cedula, a.fecha_nacimiento,
                        a.id_categoria, c.nombre as categoria_nombre,
                        TIMESTAMPDIFF(YEAR, a.fecha_nacimiento, CURDATE()) as edad
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
            error_log("Error en listarAtletasPorGrupo: " . $e->getMessage());
            return [];
        }
    }

    public function listarTodosAtletas(): array {
        $conex = $this->getConex1();
        try {
            $sql = "SELECT 
                        a.id_atleta, a.nombres, a.apellidos, a.cedula, a.fecha_nacimiento,
                        a.estado, a.id_categoria, c.nombre as categoria_nombre,
                        TIMESTAMPDIFF(YEAR, a.fecha_nacimiento, CURDATE()) as edad,
                        ga.id_grupo as grupo_actual, g.nombre as nombre_grupo
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
            error_log("Error en listarTodosAtletas: " . $e->getMessage());
            return [];
        }
    }

    public function listarAtletasPorEdad(int $edad_min, int $edad_max): array {
        if ($edad_min <= 0 || $edad_max <= 0 || $edad_min > $edad_max) {
            return [];
        }
        $conex = $this->getConex1();
        try {
            $sql = "SELECT 
                        a.id_atleta, a.nombres, a.apellidos, a.fecha_nacimiento,
                        a.id_categoria, c.nombre as categoria_nombre,
                        TIMESTAMPDIFF(YEAR, a.fecha_nacimiento, CURDATE()) as edad
                    FROM atletas a
                    LEFT JOIN categorias_feveda c ON a.id_categoria = c.id_categoria
                    WHERE a.estado = 1
                    AND TIMESTAMPDIFF(YEAR, a.fecha_nacimiento, CURDATE()) BETWEEN ? AND ?
                    AND NOT EXISTS (
                    SELECT 1 
                    FROM grupo_atleta ga 
                    INNER JOIN grupos_entrenamiento g ON ga.id_grupo = g.id_grupo
                    WHERE ga.id_atleta = a.id_atleta AND g.activo = 1
        )
                    ORDER BY edad ASC, a.apellidos ASC";
            $stmt = $conex->prepare($sql);
            $stmt->execute([$edad_min, $edad_max]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en listarAtletasPorEdad: " . $e->getMessage());
            return [];
        }
    }

    public function atletaTieneAsignacion(int $id_atleta): bool {
        if ($id_atleta <= 0) {
            return false;
        }
        $conex = $this->getConex1();
        try {
            $sql = "SELECT COUNT(*) FROM grupo_atleta WHERE id_atleta = ?";
            $stmt = $conex->prepare($sql);
            $stmt->execute([$id_atleta]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Error en atletaTieneAsignacion: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerGrupoActualAtleta(int $id_atleta): ?array {
        if ($id_atleta <= 0) {
            return null;
        }
        $conex = $this->getConex1();
        try {
            $sql = "SELECT 
                        g.id_grupo, g.nombre, g.descripcion, ga.fecha_asignacion
                    FROM grupo_atleta ga
                    INNER JOIN grupos_entrenamiento g ON ga.id_grupo = g.id_grupo
                    WHERE ga.id_atleta = ?";
            $stmt = $conex->prepare($sql);
            $stmt->execute([$id_atleta]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ?: null;
        } catch (PDOException $e) {
            error_log("Error en obtenerGrupoActualAtleta: " . $e->getMessage());
            return null;
        }
    }

    private function registrarGrupoP(): bool {
        $conex = $this->getConex1();
        try {
            $sql = "INSERT INTO grupos_entrenamiento (
                        nombre, descripcion, id_entrenador, activo
                    ) VALUES (
                        :nombre, :descripcion, :id_entrenador, 1
                    )";
            $stmt = $conex->prepare($sql);
            $resultado = $stmt->execute([
                ':nombre'        => trim($this->datos['nombre'] ?? ''),
                ':descripcion'   => trim($this->datos['descripcion'] ?? ''),
                ':id_entrenador' => !empty($this->datos['id_entrenador']) ? (int)$this->datos['id_entrenador'] : null,
            ]);

            if ($resultado) {
                $id_grupo = (int)$conex->lastInsertId();
                $this->notificarEventoGrupo('crear_grupo', [
                    'id_grupo'      => $id_grupo,
                    'nombre'        => $this->datos['nombre'] ?? '',
                    'id_entrenador' => $this->datos['id_entrenador'] ?? null,
                ]);
            }

            return $resultado;
        } catch (PDOException $e) {
            $this->setUltimoError($e->getMessage());
            error_log("Error en registrarGrupoP: " . $e->getMessage());
            return false;
        }
    }

    private function editarGrupoP(): bool {
        $conex = $this->getConex1();
        try {
            $id_grupo = (int)($this->datos['id_grupo_original'] ?? 0);

            $sql = "UPDATE grupos_entrenamiento SET 
                        nombre = :nombre, 
                        descripcion = :descripcion, 
                        id_entrenador = :id_entrenador
                    WHERE id_grupo = :id_grupo";
            $stmt = $conex->prepare($sql);
            return $stmt->execute([
                ':nombre'        => trim($this->datos['nombre'] ?? ''),
                ':descripcion'   => trim($this->datos['descripcion'] ?? ''),
                ':id_entrenador' => !empty($this->datos['id_entrenador']) ? (int)$this->datos['id_entrenador'] : null,
                ':id_grupo'      => $id_grupo,
            ]);
        } catch (PDOException $e) {
            $this->setUltimoError($e->getMessage());
            error_log("Error en editarGrupoP: " . $e->getMessage());
            return false;
        }
    }

    private function cambiarEstadoGrupoP(int $id, int $estado): bool {
        $conex = $this->getConex1();
        try {
            $sqlInfo = "SELECT nombre FROM grupos_entrenamiento WHERE id_grupo = ?";
            $stmtInfo = $conex->prepare($sqlInfo);
            $stmtInfo->execute([$id]);
            $grupo = $stmtInfo->fetch(PDO::FETCH_ASSOC);

            $sql = "UPDATE grupos_entrenamiento SET activo = :estado WHERE id_grupo = :id";
            $stmt = $conex->prepare($sql);
            $resultado = $stmt->execute([':estado' => $estado, ':id' => $id]);

            if ($resultado && $estado === 0 && $grupo) {
                $this->notificarEventoGrupo('archivar_grupo', [
                    'id_grupo' => $id,
                    'nombre'   => $grupo['nombre'] ?? 'Grupo de entrenamiento',
                ]);
            }

            return $resultado;
        } catch (PDOException $e) {
            error_log("Error en cambiarEstadoGrupoP: " . $e->getMessage());
            return false;
        }
    }

    private function asignarGrupoAtletasP(): bool {
        $conex = $this->getConex1();
        try {
            $conex->beginTransaction();

            $id_grupo = (int)$this->datosAsignacion['id_grupo'];
            $atletas  = $this->datosAsignacion['atletas'];
            $fecha_asignacion = date('Y-m-d H:i:s');

            $sqlActuales = "SELECT id_atleta FROM grupo_atleta WHERE id_grupo = ?";
            $stmtActuales = $conex->prepare($sqlActuales);
            $stmtActuales->execute([$id_grupo]);
            $atletasActuales = $stmtActuales->fetchAll(PDO::FETCH_COLUMN);

            $sqlEliminar = "DELETE FROM grupo_atleta WHERE id_grupo = ?";
            $stmtEliminar = $conex->prepare($sqlEliminar);
            $stmtEliminar->execute([$id_grupo]);

            $sqlInsert = "INSERT INTO grupo_atleta (id_grupo, id_atleta, fecha_asignacion) 
                          VALUES (?, ?, ?)";
            $stmtInsert = $conex->prepare($sqlInsert);

            $insertados = [];
            foreach ($atletas as $id_atleta) {
                $id_atleta = (int)$id_atleta;
                if ($id_atleta > 0) {
                    $stmtInsert->execute([$id_grupo, $id_atleta, $fecha_asignacion]);
                    $insertados[] = $id_atleta;
                }
            }

            $conex->commit();

            if (!empty($insertados)) {
                $sqlInfo = "SELECT nombre FROM grupos_entrenamiento WHERE id_grupo = ?";
                $stmtInfo = $conex->prepare($sqlInfo);
                $stmtInfo->execute([$id_grupo]);
                $grupoInfo = $stmtInfo->fetch(PDO::FETCH_ASSOC);

                $this->notificarEventoGrupo('asignar_atletas', [
                    'id_grupo' => $id_grupo,
                    'atletas'  => $insertados,
                    'nombre'   => $grupoInfo['nombre'] ?? 'Grupo de entrenamiento',
                ]);

                $removidos = array_diff($atletasActuales, $insertados);
                if (!empty($removidos)) {
                    $this->notificarAtletasRemovidos($removidos, $grupoInfo['nombre'] ?? 'el grupo');
                }
            }

            return !empty($insertados);
        } catch (PDOException $e) {
            if ($conex->inTransaction()) {
                $conex->rollBack();
            }
            $this->setUltimoError($e->getMessage());
            error_log("Error en asignarGrupoAtletasP: " . $e->getMessage());
            return false;
        }
    }

    private function desasignarAtletasP(array $ids): bool {
        $conex = $this->getConex1();
        try {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $sql = "DELETE FROM grupo_atleta WHERE id_atleta IN ({$placeholders})";
            $stmt = $conex->prepare($sql);
            return $stmt->execute($ids);
        } catch (PDOException $e) {
            $this->setUltimoError($e->getMessage());
            error_log("Error en desasignarAtletasP: " . $e->getMessage());
            return false;
        }
    }

    private function cambiarGrupoAtletaP(int $id_atleta, int $id_nuevo_grupo): bool {
        $conex = $this->getConex1();
        try {
            $conex->beginTransaction();

            $sqlActual = "SELECT id_grupo FROM grupo_atleta WHERE id_atleta = ?";
            $stmtActual = $conex->prepare($sqlActual);
            $stmtActual->execute([$id_atleta]);
            $grupo_actual = $stmtActual->fetchColumn();

            $sqlEliminar = "DELETE FROM grupo_atleta WHERE id_atleta = ?";
            $stmtEliminar = $conex->prepare($sqlEliminar);
            $stmtEliminar->execute([$id_atleta]);

            $sqlInsert = "INSERT INTO grupo_atleta (id_grupo, id_atleta, fecha_asignacion) 
                          VALUES (?, ?, CURDATE())";
            $stmtInsert = $conex->prepare($sqlInsert);
            $stmtInsert->execute([$id_nuevo_grupo, $id_atleta]);

            $conex->commit();

            if ($grupo_actual) {
                $this->notificarCambioGrupoAtleta($id_atleta, (int)$grupo_actual, $id_nuevo_grupo);
            }

            return true;
        } catch (PDOException $e) {
            if ($conex->inTransaction()) {
                $conex->rollBack();
            }
            $this->setUltimoError($e->getMessage());
            error_log("Error en cambiarGrupoAtletaP: " . $e->getMessage());
            return false;
        }
    }

    private function notificarAtletasRemovidos(array $ids_atletas, string $nombre_grupo): void {
        try {
            $conex = $this->getConex1();
            foreach ($ids_atletas as $id_atleta) {
                $sqlAtleta = "SELECT correo FROM atletas WHERE id_atleta = ? AND estado = 1";
                $stmtA = $conex->prepare($sqlAtleta);
                $stmtA->execute([$id_atleta]);
                $atleta = $stmtA->fetch(PDO::FETCH_ASSOC);

                if ($atleta && !empty($atleta['correo'])) {
                    $id_usuario = Notificacion::obtenerIdUsuarioPorCorreo($atleta['correo']);
                    if ($id_usuario) {
                        Notificacion::enviar($id_usuario, "Removido del grupo de entrenamiento", 
                        "Has sido removido del grupo \"{$nombre_grupo}\". Contacta a tu entrenador para más información.", 'fa-user-minus', 'orange', "?p=grupo"
                        );
                    }
                }
            }
        } catch (\Throwable $e) {
            error_log("Error en notificarAtletasRemovidos: " . $e->getMessage());
        }
    }

    private function notificarCambioGrupoAtleta(int $id_atleta, int $grupo_anterior, int $grupo_nuevo): void {
        try {
            $conex = $this->getConex1();

            $sqlAtleta = "SELECT correo FROM atletas WHERE id_atleta = ? AND estado = 1";
            $stmtAtleta = $conex->prepare($sqlAtleta);
            $stmtAtleta->execute([$id_atleta]);
            $atleta = $stmtAtleta->fetch(PDO::FETCH_ASSOC);

            if (!$atleta || empty($atleta['correo'])) {
                return;
            }

            $id_usuario = Notificacion::obtenerIdUsuarioPorCorreo($atleta['correo']);
            if (!$id_usuario) {
                return;
            }

            $sqlGrupos = "SELECT id_grupo, nombre FROM grupos_entrenamiento WHERE id_grupo IN (?, ?)";
            $stmtG = $conex->prepare($sqlGrupos);
            $stmtG->execute([$grupo_anterior, $grupo_nuevo]);
            $grupos = $stmtG->fetchAll(PDO::FETCH_ASSOC);

            $nombreAnterior = 'grupo anterior';
            $nombreNuevo = 'nuevo grupo';
            foreach ($grupos as $g) {
                if ((int)$g['id_grupo'] === $grupo_anterior) $nombreAnterior = $g['nombre'];
                if ((int)$g['id_grupo'] === $grupo_nuevo) $nombreNuevo = $g['nombre'];
            }

            Notificacion::enviar(
                $id_usuario, "Cambio de grupo de entrenamiento",
                "Has sido movido del grupo \"{$nombreAnterior}\" al grupo \"{$nombreNuevo}\". Contacta a tu entrenador para más información.", 'fa-exchange-alt', 'blue', "?p=grupo&accion=ver&id={$grupo_nuevo}"
            );
        } catch (\Throwable $e) {
            error_log("Error en notificarCambioGrupoAtleta: " . $e->getMessage());
        }
    }

    private function notificarEventoGrupo(string $evento, array $datos): void {
        try {
            if (!class_exists('\GrupoProyecto\SisBiomec\modelo\Notificacion')) {
                return;
            }

            switch ($evento) {
                case 'asignar_atletas':
                    if (!empty($datos['id_grupo']) && !empty($datos['atletas'])) {
                        Notificacion::notificarAsignacionGrupo(
                            $datos['id_grupo'],
                            $datos['atletas'],
                            'ASIGNAR'
                        );
                    }
                    break;

                case 'crear_grupo':
                    if (!empty($datos['id_grupo']) && !empty($datos['id_entrenador'])) {
                        $conex = $this->getConex1();
                        $sql = "SELECT correo FROM entrenador WHERE id_entrenador = :id_entrenador";
                        $stmt = $conex->prepare($sql);
                        $stmt->execute([':id_entrenador' => $datos['id_entrenador']]);
                        $entrenador = $stmt->fetch(PDO::FETCH_ASSOC);

                        if ($entrenador && !empty($entrenador['correo'])) {
                            $id_usuario = Notificacion::obtenerIdUsuarioPorCorreo($entrenador['correo']);
                            if ($id_usuario) {
                                Notificacion::enviar(
                                    $id_usuario,
                                    "Nuevo grupo de entrenamiento creado",
                                    "Se ha creado el grupo \"{$datos['nombre']}\". Ya puedes comenzar a asignar atletas.",
                                    'fa-users-cog',
                                    'indigo',
                                    "?p=grupo&accion=ver&id={$datos['id_grupo']}"
                                );
                            }
                        }
                    }
                    break;

                case 'archivar_grupo':
                    if (!empty($datos['id_grupo']) && !empty($datos['nombre'])) {
                        Notificacion::notificarGrupoArchivado(
                            $datos['id_grupo'],
                            $datos['nombre']
                        );
                    }
                    break;
            }
        } catch (\Throwable $e) {
            error_log("Error en notificarEventoGrupo: " . $e->getMessage());
        }
    }
}
<?php

namespace GrupoProyecto\SisBiomec\modelo;

trait ValidacionesTrait {
    protected array $errores = [];

    protected function resetearErrores(): void {
        $this->errores = [];
    }

    protected function agregarError(string $campo, string $mensaje): void {
        if (!isset($this->errores[$campo])) {
            $this->errores[$campo] = $mensaje;
        }
    }

    public function obtenerErrores(): array {
        return $this->errores;
    }

    protected function requerido(string $valor, string $campo): bool {
        if (trim($valor) === '') {
            $this->agregarError($campo, "El campo {$campo} es obligatorio.");
            return false;
        }
        return true;
    }

    protected function longitud(string $valor, string $campo, int $min, int $max): bool {
        $len = mb_strlen(trim($valor));
        if ($len < $min || $len > $max) {
            $this->agregarError($campo, "{$campo} debe tener entre {$min} y {$max} caracteres.");
            return false;
        }
        return true;
    }

    protected function soloLetras(string $valor, string $campo): bool {
        if (!preg_match('/^[\p{L}\s]+$/u', trim($valor))) {
            $this->agregarError($campo, "{$campo} solo puede contener letras y espacios.");
            return false;
        }
        return true;
    }

    protected function fechaValida(string $valor, string $campo): bool {
        $partes = explode('-', $valor);
        if (count($partes) !== 3 || !checkdate((int)$partes[1], (int)$partes[2], (int)$partes[0])) {
            $this->agregarError($campo, "{$campo} debe ser una fecha válida.");
            return false;
        }
        return true;
    }

    protected function fechaNoFutura(string $valor, string $campo): bool {
        if (strtotime($valor) > strtotime('today')) {
            $this->agregarError($campo, "{$campo} no puede ser una fecha futura.");
            return false;
        }
        return true;
    }

    protected function enEnum(string $valor, string $campo, array $valores): bool {
        if (!in_array($valor, $valores, true)) {
            $this->agregarError($campo, "{$campo} tiene un valor no permitido.");
            return false;
        }
        return true;
    }

    protected function unico(\PDO $conex, string $valor, string $tabla, string $columna, ?int $excluirId = null, string $pkColumna = 'id'): bool {
        $sql = "SELECT COUNT(*) FROM {$tabla} WHERE {$columna} = :valor";
        $params = [':valor' => $valor];
        if ($excluirId !== null) {
            $sql .= " AND {$pkColumna} != :id";
            $params[':id'] = $excluirId;
        }
        $stmt = $conex->prepare($sql);
        $stmt->execute($params);
        if ($stmt->fetchColumn() > 0) {
            $this->agregarError($columna, "Ya existe un registro con este valor de {$columna}.");
            return false;
        }
        return true;
    }

    protected function soloNumeros(string $valor, string $campo): bool {
        if (!preg_match('/^[0-9]+$/', trim($valor))) {
            $this->agregarError($campo, "{$campo} solo puede contener números.");
            return false;
        }
        return true;
    }

    protected function correoValido(string $valor, string $campo): bool {
        if (!filter_var(trim($valor), FILTER_VALIDATE_EMAIL)) {
            $this->agregarError($campo, "{$campo} no tiene un formato válido.");
            return false;
        }
        return true;
    }

    protected function cedula(string $valor, string $campo): bool {
        if (!preg_match('/^[VEve]-\d{7,8}$/', trim($valor))) {
            $this->agregarError($campo, "{$campo} no tiene un formato válido. Use V-1234567 o V-12345678.");
            return false;
        }
        return true;
    }

    protected function telefono(string $valor, string $campo): bool {
        if (!preg_match('/^[\d\-\+\(\)\s]{7,20}$/', trim($valor))) {
            $this->agregarError($campo, "{$campo} no tiene un formato de teléfono válido.");
            return false;
        }
        return true;
    }
}

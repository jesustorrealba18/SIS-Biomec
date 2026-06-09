<?php

namespace GrupoProyecto\SisBiomec\seguridad;

class ConexionException extends \RuntimeException {
    private string $tipoError;

    public function __construct(string $mensajePublico, string $tipoError = 'db', int $codigo = 0, ?\Throwable $previous = null) {
        parent::__construct($mensajePublico, $codigo, $previous);
        $this->tipoError = $tipoError;
    }

    public function getTipoError(): string {
        return $this->tipoError;
    }
}

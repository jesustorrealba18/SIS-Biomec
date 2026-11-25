<?php
namespace GrupoProyecto\SisBiomec\seguridad;

if (!class_exists(Bitacora::class, false)) {
    class Bitacora {
        public static function registrar($id_usuario, $modulo, $accion, $id_afectado, $campo = null, $anterior = null, $nuevo = null): void {
            // En pruebas no hace nada, solo evita el error
            return;
        }
    }
}
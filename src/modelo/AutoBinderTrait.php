<?php

namespace GrupoProyecto\SisBiomec\modelo;

use PDO;

trait AutoBinderTrait {
    
    /**
     * Enlazador dinámico de parámetros PDO.
     * Es PROTECTED para que ninguna capa externa (como el Controlador) pueda invocarlo.
     */
    protected function autoBind(\PDOStatement $stmt, array $mapa, array $fuenteDatos, array $valoresLocales = []): void {
        foreach ($mapa as $marcador => $config) {
            $nombreCampo = $config[0];
            $tipoPdo     = $config[1];

            // 1. Extraemos el valor, dando prioridad a las variables locales inyectadas
            if (array_key_exists($nombreCampo, $valoresLocales)) {
                $valor = $valoresLocales[$nombreCampo];
            } else {
                $valor = $fuenteDatos[$nombreCampo] ?? null;
            }

            // 2. Blindaje contra Nulos (Evita errores de integridad en MySQL)
            if ($valor === null || $valor === '') {
                $stmt->bindValue($marcador, null, PDO::PARAM_NULL);
                continue; // Salta a la siguiente iteración del bucle
            }

            // 3. Casteo dinámico según el mapa
            switch ($tipoPdo) {
                case PDO::PARAM_INT:
                    $stmt->bindValue($marcador, (int)$valor, PDO::PARAM_INT);
                    break;
                case PDO::PARAM_BOOL:
                    $stmt->bindValue($marcador, (bool)$valor, PDO::PARAM_BOOL);
                    break;
                default:
                    // PARAM_STR abarca textos, fechas y decimales (flotantes)
                    $stmt->bindValue($marcador, $valor, PDO::PARAM_STR);
                    break;
            }
        }
    }
}
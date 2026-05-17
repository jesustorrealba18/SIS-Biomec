<?php
class Respuesta {
    private static $mensajes = [
        'success_reg' => 'Operación completada con éxito.',
        'error_db'    => 'Error técnico interno. El equipo ha sido notificado.',
        'error_dup'   => 'El registro ya existe en nuestro sistema.',
        'error_404'   => 'El recurso solicitado no existe.'
    ];

    public static function manejarExcepcion($db, $e) {
        if ($e instanceof PDOException && strval($e->getCode()) === '23000') {
            self::enviar(400, 'error_dup', 'error', $e);
        }

        Logger::log($db, $e);
        self::enviar(500, 'error_db', 'error', $e);
    }

    public static function enviar($codigo, $clave, $status = 'error', $errorOriginal = null) {
        // Obligatorio para que JS entienda el JSON
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($codigo);
        
        $respuesta = [
            "status"  => $status,
            "message" => self::$mensajes[$clave] ?? $clave
        ];

        // Solo se muestra si MODO_DESARROLLO está definido y es true
        if (defined('MODO_DESARROLLO') && MODO_DESARROLLO && $errorOriginal){
            $respuesta['debug'] = [
                'mensaje' => $errorOriginal->getMessage(), // Añadí esto para que veas el error real
                'archivo' => $errorOriginal->getFile(),
                'linea'   => $errorOriginal->getLine()
            ];
        }

        echo json_encode($respuesta);
        exit;
    }
}
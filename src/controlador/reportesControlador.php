<?php
ob_start();

if (empty($_SESSION['id'])) {
    header('Location: ?p=login');
    exit;
}

use GrupoProyecto\SisBiomec\modelo\Reporte;
use GrupoProyecto\SisBiomec\seguridad\Autorizacion;
use GrupoProyecto\SisBiomec\seguridad\Bitacora;

$objReporte = new Reporte();

function jsonSalida($datos)
{
    if (ob_get_level()) ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode($datos);
    exit;
}

function formatoTiempo($segundos)
{
    if ($segundos === null || $segundos === '') return '-';
    $s = (float)$segundos;
    $min = floor($s / 60);
    $sec = $s - ($min * 60);
    return $min > 0
        ? sprintf('%d:%05.2f', $min, $sec)
        : sprintf('%.2f', $sec);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $accion = $_GET['accion'] ?? '';

    Autorizacion::exigir('reportes', 'ver');

    if ($accion === 'evolucion_marcas') {
        $idAtleta = (int)($_GET['id_atleta'] ?? 0);
        $estilo = $_GET['estilo'] ?? '';
        $distancia = (int)($_GET['distancia'] ?? 0);
        $piscina = $_GET['piscina'] ?? '';
        $fechaIni = $_GET['fecha_ini'] ?? '';
        $fechaFin = $_GET['fecha_fin'] ?? '';
        jsonSalida($objReporte->evolucionMarcas($idAtleta, $estilo, $distancia, $piscina, $fechaIni, $fechaFin));
    }

    if ($accion === 'asistencia_grupo') {
        $idGrupo = (int)($_GET['id_grupo'] ?? 0);
        $fechaIni = $_GET['fecha_ini'] ?? '';
        $fechaFin = $_GET['fecha_fin'] ?? '';
        jsonSalida($objReporte->asistenciaGrupo($idGrupo, $fechaIni, $fechaFin));
    }

    if ($accion === 'volumen_semanal') {
        $idGrupo = (int)($_GET['id_grupo'] ?? 0);
        $fechaIni = $_GET['fecha_ini'] ?? '';
        $fechaFin = $_GET['fecha_fin'] ?? '';
        jsonSalida($objReporte->volumenSemanal($idGrupo, $fechaIni, $fechaFin));
    }

    if ($accion === 'carga_srpe') {
        $idGrupo = (int)($_GET['id_grupo'] ?? 0);
        $idAtleta = (int)($_GET['id_atleta'] ?? 0);
        $fechaIni = $_GET['fecha_ini'] ?? '';
        $fechaFin = $_GET['fecha_fin'] ?? '';
        jsonSalida($objReporte->cargaSRPE($idGrupo, $idAtleta, $fechaIni, $fechaFin));
    }

    if ($accion === 'ficha_atleta') {
        $idAtleta = (int)($_GET['id_atleta'] ?? 0);
        $datos = $objReporte->fichaAtleta($idAtleta);
        jsonSalida($datos ?: ['error' => 'Atleta no encontrado']);
    }

    if ($accion === 'select_atletas') {
        jsonSalida($objReporte->obtenerAtletasSelect());
    }

    if ($accion === 'select_grupos') {
        jsonSalida($objReporte->obtenerGruposSelect());
    }

    if ($accion === 'select_categorias') {
        jsonSalida($objReporte->obtenerCategoriasSelect());
    }

    if ($accion === 'select_entrenadores') {
       jsonSalida($objReporte->obtenerEntrenadoresSelect());
    }

    if ($accion === 'lista_entrenadores') {
        $idEntrenador = isset($_GET['id_entrenador']) && $_GET['id_entrenador'] !== '' 
          ? (int)$_GET['id_entrenador'] 
          : null;
         jsonSalida($objReporte->listaEntrenadores($idEntrenador));
    }

    if ($accion === 'lista_grupos') {
        $estado = $_GET['estado'] ?? 'Activo';
        jsonSalida($objReporte->listaGrupos($estado));
    }

    if ($accion === 'detalle_grupo') {
        $idGrupo = (int)($_GET['id_grupo'] ?? 0);
        $datos = $objReporte->detalleGrupo($idGrupo);
        jsonSalida($datos ?: ['error' => 'Grupo no encontrado']);
    }

    if ($accion === 'listar_sesiones_select') {
        jsonSalida($objReporte->listarSesionesSelect());
    }

    if ($accion === 'detalle_sesion') {
        $idSesion = (int)($_GET['id_sesion'] ?? 0);
        $datos = $objReporte->detalleSesion($idSesion);
        jsonSalida($datos ?: ['error' => 'Sesion no encontrada']);
    }

    if ($accion === 'resumen_sesiones_grupo') {
        $idGrupo = (int)($_GET['id_grupo'] ?? 0);
        $estado = $_GET['estado'] ?? '';
        $fechaIni = $_GET['fecha_ini'] ?? '';
        $fechaFin = $_GET['fecha_fin'] ?? '';
        jsonSalida($objReporte->resumenSesionesGrupo($idGrupo, $estado, $fechaIni, $fechaFin));
    }

    require_once 'vista/reportes.php';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'generar_pdf') {
        Autorizacion::exigir('reportes', 'ver');

        try {
            require_once RAIZ . 'vendor/autoload.php';
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->setPaper('A4', 'portrait');

            $tipo = $_POST['tipo_reporte'] ?? '';
            $graficaImagen = $_POST['grafica_imagen'] ?? '';
            $fechaGeneracion = date('d/m/Y H:i');
            $generadoPor = $_SESSION['nombre'] ?? 'Sistema';

            $html = '';

            switch ($tipo) {
                case 'evolucion_marcas':
                    $idAtleta = (int)($_POST['id_atleta'] ?? 0);
                    $estilo = $_POST['estilo'] ?? '';
                    $distancia = (int)($_POST['distancia'] ?? 0);
                    $piscina = $_POST['piscina'] ?? '';
                    $fechaIni = $_POST['fecha_ini'] ?? '';
                    $fechaFin = $_POST['fecha_fin'] ?? '';
                    $datos = $objReporte->evolucionMarcas($idAtleta, $estilo, $distancia, $piscina, $fechaIni, $fechaFin);
                    $atleta = $objReporte->fichaAtleta($idAtleta);
                    $nombreAtleta = $atleta ? ($atleta['nombres'] . ' ' . $atleta['apellidos']) : 'Atleta';
                    $titulo = "Evolucion de Marcas - {$estilo} {$distancia}m ({$piscina})";

                    $filas = '';
                    foreach ($datos as $d) {
                        $pbBadge = ($d['es_pb'] == 1) ? '<span style="background:#f59e0b;color:#fff;padding:1px 6px;border-radius:4px;font-size:9px;font-weight:bold;">PB</span>' : '';
                        $filas .= '<tr>'
                            . '<td style="padding:6px 10px;border-bottom:1px solid #e5e7eb;">' . $d['fecha'] . '</td>'
                            . '<td style="padding:6px 10px;border-bottom:1px solid #e5e7eb;">' . formatoTiempo($d['tiempo_final_seg']) . '</td>'
                            . '<td style="padding:6px 10px;border-bottom:1px solid #e5e7eb;text-align:center;">' . $pbBadge . '</td>'
                            . '<td style="padding:6px 10px;border-bottom:1px solid #e5e7eb;">' . $d['contexto'] . '</td>'
                            . '</tr>';
                    }

                    $imgTag = $graficaImagen ? '<img src="' . $graficaImagen . '" style="width:100%;max-width:650px;margin:0 auto 20px;display:block;">' : '';

                    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>'
                        . 'body{font-family:Helvetica,Arial,sans-serif;margin:30px;color:#1f2937;} '
                        . 'h1{color:#4f46e5;font-size:18px;margin-bottom:4px;} '
                        . 'h2{color:#374151;font-size:13px;font-weight:normal;margin-bottom:20px;} '
                        . 'table{width:100%;border-collapse:collapse;font-size:11px;margin-bottom:20px;} '
                        . 'th{background:#4f46e5;color:#fff;padding:8px 10px;text-align:left;} '
                        . 'td{padding:6px 10px;border-bottom:1px solid #e5e7eb;} '
                        . 'tr:nth-child(even) td{background:#f9fafb;} '
                        . 'footer{margin-top:30px;font-size:9px;color:#9ca3af;text-align:center;} '
                        . '</style></head><body>'
                        . '<h1>' . $titulo . '</h1>'
                        . '<h2>Atleta: ' . htmlspecialchars($nombreAtleta) . ' | Periodo: ' . $fechaIni . ' a ' . $fechaFin . '</h2>'
                        . $imgTag
                        . '<table><thead><tr><th>Fecha</th><th>Tiempo</th><th style="text-align:center;">PB</th><th>Contexto</th></tr></thead><tbody>' . $filas . '</tbody></table>'
                        . '<footer>Generado el ' . $fechaGeneracion . ' por ' . htmlspecialchars($generadoPor) . '</footer>'
                        . '</body></html>';
                    break;

                case 'asistencia_grupo':
                    $idGrupo = (int)($_POST['id_grupo'] ?? 0);
                    $fechaIni = $_POST['fecha_ini'] ?? '';
                    $fechaFin = $_POST['fecha_fin'] ?? '';
                    $datos = $objReporte->asistenciaGrupo($idGrupo, $fechaIni, $fechaFin);
                    $titulo = 'Reporte de Asistencia por Grupo';

                    $filas = '';
                    $totP = $totA = $totJ = $totR = 0;
                    foreach ($datos as $d) {
                        $total = (int)$d['total_sesiones'];
                        $p = (int)$d['presentes'];
                        $a = (int)$d['ausentes'];
                        $j = (int)$d['justificados'];
                        $r = (int)$d['retardos'];
                        $totP += $p; $totA += $a; $totJ += $j; $totR += $r;
                        $pct = $total > 0 ? round(($p / $total) * 100, 1) : 0;
                        $colorPct = $pct >= 90 ? '#10b981' : ($pct >= 75 ? '#f59e0b' : '#ef4444');
                        $filas .= '<tr>'
                            . '<td style="padding:6px 10px;border-bottom:1px solid #e5e7eb;">' . htmlspecialchars($d['nombre_atleta']) . '</td>'
                            . '<td style="padding:6px 10px;border-bottom:1px solid #e5e7eb;text-align:center;">' . $total . '</td>'
                            . '<td style="padding:6px 10px;border-bottom:1px solid #e5e7eb;text-align:center;">' . $p . '</td>'
                            . '<td style="padding:6px 10px;border-bottom:1px solid #e5e7eb;text-align:center;">' . $a . '</td>'
                            . '<td style="padding:6px 10px;border-bottom:1px solid #e5e7eb;text-align:center;">' . $j . '</td>'
                            . '<td style="padding:6px 10px;border-bottom:1px solid #e5e7eb;text-align:center;">' . $r . '</td>'
                            . '<td style="padding:6px 10px;border-bottom:1px solid #e5e7eb;text-align:center;font-weight:bold;color:' . $colorPct . ';">' . $pct . '%</td>'
                            . '</tr>';
                    }

                    $imgTag = $graficaImagen ? '<img src="' . $graficaImagen . '" style="width:100%;max-width:500px;margin:0 auto 20px;display:block;">' : '';

                    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>'
                        . 'body{font-family:Helvetica,Arial,sans-serif;margin:30px;color:#1f2937;} '
                        . 'h1{color:#4f46e5;font-size:18px;margin-bottom:4px;} '
                        . 'h2{color:#374151;font-size:13px;font-weight:normal;margin-bottom:20px;} '
                        . 'table{width:100%;border-collapse:collapse;font-size:11px;margin-bottom:20px;} '
                        . 'th{background:#4f46e5;color:#fff;padding:8px 10px;text-align:left;} '
                        . 'td{padding:6px 10px;border-bottom:1px solid #e5e7eb;} '
                        . 'tr:nth-child(even) td{background:#f9fafb;} '
                        . 'footer{margin-top:30px;font-size:9px;color:#9ca3af;text-align:center;} '
                        . '</style></head><body>'
                        . '<h1>' . $titulo . '</h1>'
                        . '<h2>Periodo: ' . $fechaIni . ' a ' . $fechaFin . '</h2>'
                        . $imgTag
                        . '<table><thead><tr><th>Atleta</th><th style="text-align:center;">Total</th><th style="text-align:center;">Pres.</th><th style="text-align:center;">Aus.</th><th style="text-align:center;">Just.</th><th style="text-align:center;">Ret.</th><th style="text-align:center;">%</th></tr></thead><tbody>' . $filas . '</tbody></table>'
                        . '<footer>Generado el ' . $fechaGeneracion . ' por ' . htmlspecialchars($generadoPor) . '</footer>'
                        . '</body></html>';
                    break;

                case 'volumen_semanal':
                    $idGrupo = (int)($_POST['id_grupo'] ?? 0);
                    $fechaIni = $_POST['fecha_ini'] ?? '';
                    $fechaFin = $_POST['fecha_fin'] ?? '';
                    $datos = $objReporte->volumenSemanal($idGrupo, $fechaIni, $fechaFin);
                    $titulo = 'Volumen Semanal de Entrenamiento';

                    $filas = '';
                    foreach ($datos as $d) {
                        $plan = (int)$d['metros_planificados'];
                        $ejec = (int)$d['metros_ejecutados'];
                        $pct = $plan > 0 ? round(($ejec / $plan) * 100, 1) : 0;
                        $colorPct = $pct >= 95 ? '#10b981' : ($pct >= 80 ? '#f59e0b' : '#ef4444');
                        $filas .= '<tr>'
                            . '<td style="padding:6px 10px;border-bottom:1px solid #e5e7eb;">' . $d['rango'] . '</td>'
                            . '<td style="padding:6px 10px;border-bottom:1px solid #e5e7eb;text-align:center;">' . number_format($plan) . ' m</td>'
                            . '<td style="padding:6px 10px;border-bottom:1px solid #e5e7eb;text-align:center;">' . number_format($ejec) . ' m</td>'
                            . '<td style="padding:6px 10px;border-bottom:1px solid #e5e7eb;text-align:center;">' . $d['total_sesiones'] . '</td>'
                            . '<td style="padding:6px 10px;border-bottom:1px solid #e5e7eb;text-align:center;font-weight:bold;color:' . $colorPct . ';">' . $pct . '%</td>'
                            . '</tr>';
                    }

                    $imgTag = $graficaImagen ? '<img src="' . $graficaImagen . '" style="width:100%;max-width:650px;margin:0 auto 20px;display:block;">' : '';

                    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>'
                        . 'body{font-family:Helvetica,Arial,sans-serif;margin:30px;color:#1f2937;} '
                        . 'h1{color:#4f46e5;font-size:18px;margin-bottom:4px;} '
                        . 'h2{color:#374151;font-size:13px;font-weight:normal;margin-bottom:20px;} '
                        . 'table{width:100%;border-collapse:collapse;font-size:11px;margin-bottom:20px;} '
                        . 'th{background:#4f46e5;color:#fff;padding:8px 10px;text-align:left;} '
                        . 'td{padding:6px 10px;border-bottom:1px solid #e5e7eb;} '
                        . 'tr:nth-child(even) td{background:#f9fafb;} '
                        . 'footer{margin-top:30px;font-size:9px;color:#9ca3af;text-align:center;} '
                        . '</style></head><body>'
                        . '<h1>' . $titulo . '</h1>'
                        . '<h2>Periodo: ' . $fechaIni . ' a ' . $fechaFin . '</h2>'
                        . $imgTag
                        . '<table><thead><tr><th>Semana</th><th style="text-align:center;">Planificado</th><th style="text-align:center;">Ejecutado</th><th style="text-align:center;">Sesiones</th><th style="text-align:center;">Cumplimiento</th></tr></thead><tbody>' . $filas . '</tbody></table>'
                        . '<footer>Generado el ' . $fechaGeneracion . ' por ' . htmlspecialchars($generadoPor) . '</footer>'
                        . '</body></html>';
                    break;

                case 'carga_srpe':
                    $idGrupo = (int)($_POST['id_grupo'] ?? 0);
                    $idAtleta = (int)($_POST['id_atleta'] ?? 0);
                    $fechaIni = $_POST['fecha_ini'] ?? '';
                    $fechaFin = $_POST['fecha_fin'] ?? '';
                    $datos = $objReporte->cargaSRPE($idGrupo, $idAtleta, $fechaIni, $fechaFin);
                    $titulo = 'Monitoreo de Carga (sRPE)';

                    $filas = '';
                    foreach ($datos as $d) {
                        $colorRPE = (int)$d['rpe'] >= 8 ? '#ef4444' : ((int)$d['rpe'] >= 6 ? '#f59e0b' : '#10b981');
                        $filas .= '<tr>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;">' . $d['fecha'] . '</td>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;">' . htmlspecialchars($d['nombre_atleta']) . '</td>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;text-align:center;font-weight:bold;color:' . $colorRPE . ';">' . $d['rpe'] . '</td>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;text-align:center;">' . ($d['srpe'] ?: '-') . '</td>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;text-align:center;">' . ($d['horas_sueno'] ?: '-') . '</td>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;text-align:center;">' . ($d['calidad_sueno'] ?: '-') . '</td>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;text-align:center;">' . ($d['estres_percibido'] ?: '-') . '</td>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;text-align:center;">' . ($d['sensacion_muscular'] ?: '-') . '</td>'
                            . '</tr>';
                    }

                    $imgTag = $graficaImagen ? '<img src="' . $graficaImagen . '" style="width:100%;max-width:650px;margin:0 auto 20px;display:block;">' : '';

                    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>'
                        . 'body{font-family:Helvetica,Arial,sans-serif;margin:30px;color:#1f2937;} '
                        . 'h1{color:#4f46e5;font-size:18px;margin-bottom:4px;} '
                        . 'h2{color:#374151;font-size:13px;font-weight:normal;margin-bottom:20px;} '
                        . 'table{width:100%;border-collapse:collapse;font-size:10px;margin-bottom:20px;} '
                        . 'th{background:#4f46e5;color:#fff;padding:6px 8px;text-align:left;font-size:10px;} '
                        . 'td{padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;} '
                        . 'tr:nth-child(even) td{background:#f9fafb;} '
                        . 'footer{margin-top:30px;font-size:9px;color:#9ca3af;text-align:center;} '
                        . '</style></head><body>'
                        . '<h1>' . $titulo . '</h1>'
                        . '<h2>Periodo: ' . $fechaIni . ' a ' . $fechaFin . '</h2>'
                        . $imgTag
                        . '<table><thead><tr><th>Fecha</th><th>Atleta</th><th style="text-align:center;">RPE</th><th style="text-align:center;">sRPE</th><th style="text-align:center;">Sueno (h)</th><th style="text-align:center;">Calidad</th><th style="text-align:center;">Estres</th><th style="text-align:center;">Muscular</th></tr></thead><tbody>' . $filas . '</tbody></table>'
                        . '<footer>Generado el ' . $fechaGeneracion . ' por ' . htmlspecialchars($generadoPor) . '</footer>'
                        . '</body></html>';
                    break;

                case 'ficha_atleta':
                    $idAtleta = (int)($_POST['id_atleta'] ?? 0);
                    $atleta = $objReporte->fichaAtleta($idAtleta);

                    if (!$atleta) {
                        jsonSalida(['status' => 'error', 'message' => 'Atleta no encontrado.']);
                    }

                    $fotoHtml = '';
                    if (!empty($atleta['foto'])) {
                        $fotoPath = RAIZ . $atleta['foto'];
                        if (file_exists($fotoPath)) {
                            $fotoData = base64_encode(file_get_contents($fotoPath));
                            $fotoHtml = '<img src="data:image/jpeg;base64,' . $fotoData . '" style="width:100px;height:100px;border-radius:50%;object-fit:cover;border:3px solid #4f46e5;">';
                        }
                    }

                    function campo($label, $valor) {
                        return '<tr><td style="padding:5px 10px;border-bottom:1px solid #e5e7eb;color:#6b7280;font-size:11px;width:35%;">' . $label . '</td><td style="padding:5px 10px;border-bottom:1px solid #e5e7eb;font-size:11px;font-weight:500;">' . htmlspecialchars($valor ?: '-') . '</td></tr>';
                    }

                    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>'
                        . 'body{font-family:Helvetica,Arial,sans-serif;margin:30px;color:#1f2937;} '
                        . 'h1{color:#4f46e5;font-size:18px;margin-bottom:20px;} '
                        . 'table{width:100%;border-collapse:collapse;font-size:11px;margin-bottom:20px;} '
                        . 'td{padding:5px 10px;border-bottom:1px solid #e5e7eb;font-size:11px;} '
                        . 'section{margin-bottom:20px;} '
                        . 'section h3{color:#4f46e5;font-size:13px;border-bottom:2px solid #4f46e5;padding-bottom:4px;margin-bottom:10px;} '
                        . 'footer{margin-top:30px;font-size:9px;color:#9ca3af;text-align:center;} '
                        . '</style></head><body>'
                        . '<div style="display:flex;align-items:center;gap:20px;margin-bottom:20px;">'
                        . $fotoHtml
                        . '<div><h1 style="margin:0;">' . htmlspecialchars($atleta['nombres'] . ' ' . $atleta['apellidos']) . '</h1>'
                        . '<p style="color:#6b7280;font-size:12px;margin:4px 0 0 0;">C.I.: ' . htmlspecialchars($atleta['cedula']) . ' | Edad: ' . $atleta['edad'] . ' anos | ' . $atleta['sexo'] . '</p></div>'
                        . '</div>'
                        . '<section><h3>Datos Personales</h3><table>'
                        . campo('Direccion', $atleta['direccion'])
                        . campo('Telefono', $atleta['telefono'])
                        . campo('Correo', $atleta['correo'])
                        . campo('Fecha de Registro en el Club', $atleta['fecha_registro_club'])
                        . campo('Estado', $atleta['estado'])
                        . '</table></section>'
                        . '<section><h3>Datos Federativos</h3><table>'
                        . campo('Categoria', $atleta['categoria_nombre'])
                        . campo('Grupo de Entrenamiento', $atleta['grupo_nombre'])
                        . campo('No. Registro FEVEDA', $atleta['numero_feveda'])
                        . campo('Club de Procedencia', $atleta['club_procedencia'])
                        . '</table></section>'
                        . '<section><h3>Datos Medicos</h3><table>'
                        . campo('Grupo Sanguineo', $atleta['grupo_sanguineo'])
                        . campo('Seguro Medico', $atleta['seguro_medico'])
                        . campo('Alergias', $atleta['alergias'])
                        . campo('Condiciones Preexistentes', $atleta['condiciones_previas'])
                        . campo('Contacto de Emergencia', $atleta['contacto_emergencia_nombre'] . ' - ' . $atleta['contacto_emergencia_telefono'] . ' (' . $atleta['contacto_emergencia_parentesco'] . ')')
                        . '</table></section>'
                        . '<section><h3>Representante</h3><table>'
                        . campo('Nombre', $atleta['rep_nombres'] . ' ' . $atleta['rep_apellidos'])
                        . campo('Cedula', $atleta['rep_cedula'])
                        . campo('Telefono', $atleta['rep_telefono'])
                        . campo('Parentesco', $atleta['rep_parentesco'])
                        . campo('Correo', $atleta['rep_correo'])
                        . '</table></section>'
                        . '<footer>Generado el ' . $fechaGeneracion . ' por ' . htmlspecialchars($generadoPor) . '</footer>'
                        . '</body></html>';
                    break;

                case 'lista_atletas':
                    $idGrupo = (int)($_POST['id_grupo'] ?? 0);
                    $idCategoria = (int)($_POST['id_categoria'] ?? 0);
                    $estado = $_POST['estado'] ?? '';
                    $datos = $objReporte->listaAtletas($idGrupo, $idCategoria, $estado);
                    $titulo = 'Lista de Atletas';

                    $filtroGrupo = $idGrupo > 0 ? ' - Grupo: ' . htmlspecialchars($_POST['grupo_nombre'] ?? '') : '';
                    $filtroCat = $idCategoria > 0 ? ' - Categoria: ' . htmlspecialchars($_POST['categoria_nombre'] ?? '') : '';
                    $filtroEstado = $estado !== '' ? ' - Estado: ' . htmlspecialchars($estado) : '';
                    $subtitulo = 'Filtros:' . $filtroGrupo . $filtroCat . $filtroEstado;

                    $filas = '';
                    $num = 0;
                    foreach ($datos as $d) {
                        $num++;
                        $filas .= '<tr>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;">' . $num . '</td>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;">' . htmlspecialchars($d['cedula']) . '</td>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;font-weight:500;">' . htmlspecialchars($d['nombres'] . ' ' . $d['apellidos']) . '</td>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;">' . htmlspecialchars($d['categoria_nombre'] ?: '-') . '</td>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;">' . htmlspecialchars($d['grupo_nombre'] ?: '-') . '</td>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;">' . htmlspecialchars($d['fecha_registro_club']) . '</td>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;">' . htmlspecialchars($d['telefono']) . '</td>'
                            . '</tr>';
                    }

                    $total = count($datos);
                    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>'
                        . 'body{font-family:Helvetica,Arial,sans-serif;margin:30px;color:#1f2937;} '
                        . 'h1{color:#4f46e5;font-size:18px;margin-bottom:4px;} '
                        . 'h2{color:#374151;font-size:12px;font-weight:normal;margin-bottom:4px;} '
                        . 'h2 span{font-weight:bold;color:#4f46e5;} '
                        . 'table{width:100%;border-collapse:collapse;font-size:10px;margin-top:15px;} '
                        . 'th{background:#4f46e5;color:#fff;padding:6px 8px;text-align:left;font-size:10px;} '
                        . 'td{padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;} '
                        . 'tr:nth-child(even) td{background:#f9fafb;} '
                        . 'footer{margin-top:30px;font-size:9px;color:#9ca3af;text-align:center;} '
                        . '</style></head><body>'
                        . '<h1>' . $titulo . '</h1>'
                        . '<h2>' . $subtitulo . '</h2>'
                        . '<p style="font-size:11px;color:#6b7280;margin-bottom:10px;">Total: <span>' . $total . '</span> atletas</p>'
                        . '<table><thead><tr><th>#</th><th>Cedula</th><th>Nombre Completo</th><th>Categoria</th><th>Grupo</th><th>Fecha Registro</th><th>Telefono</th></tr></thead><tbody>' . $filas . '</tbody></table>'
                        . '<footer>Generado el ' . $fechaGeneracion . ' por ' . htmlspecialchars($generadoPor) . '</footer>'
                        . '</body></html>';
                    break;

                case 'lista_representantes':
                    $estado = $_POST['estado'] ?? 'Activo';
                    $datos = $objReporte->listaRepresentantes($estado);
                    $titulo = 'Lista de Representantes';
                    $subtitulo = 'Estado: ' . htmlspecialchars($estado);

                    $filas = '';
                    $num = 0;
                    foreach ($datos as $d) {
                        $num++;
                        $filas .= '<tr>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;">' . $num . '</td>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;">' . htmlspecialchars($d['cedula']) . '</td>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;font-weight:500;">' . htmlspecialchars($d['nombres'] . ' ' . $d['apellidos']) . '</td>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;">' . htmlspecialchars($d['telefono_principal']) . '</td>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;">' . htmlspecialchars($d['parentesco']) . '</td>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;">' . htmlspecialchars($d['atletas_vinculados'] ?: 'Sin atletas vinculados') . '</td>'
                            . '</tr>';
                    }

                    $total = count($datos);
                    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>'
                        . 'body{font-family:Helvetica,Arial,sans-serif;margin:30px;color:#1f2937;} '
                        . 'h1{color:#4f46e5;font-size:18px;margin-bottom:4px;} '
                        . 'h2{color:#374151;font-size:12px;font-weight:normal;margin-bottom:4px;} '
                        . 'h2 span{font-weight:bold;color:#4f46e5;} '
                        . 'table{width:100%;border-collapse:collapse;font-size:10px;margin-top:15px;} '
                        . 'th{background:#4f46e5;color:#fff;padding:6px 8px;text-align:left;font-size:10px;} '
                        . 'td{padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;} '
                        . 'tr:nth-child(even) td{background:#f9fafb;} '
                        . 'footer{margin-top:30px;font-size:9px;color:#9ca3af;text-align:center;} '
                        . '</style></head><body>'
                        . '<h1>' . $titulo . '</h1>'
                        . '<h2>' . $subtitulo . '</h2>'
                        . '<p style="font-size:11px;color:#6b7280;margin-bottom:10px;">Total: <span>' . $total . '</span> representantes</p>'
                        . '<table><thead><tr><th>#</th><th>Cedula</th><th>Nombre Completo</th><th>Telefono</th><th>Parentesco</th><th>Atletas Vinculados</th></tr></thead><tbody>' . $filas . '</tbody></table>'
                        . '<footer>Generado el ' . $fechaGeneracion . ' por ' . htmlspecialchars($generadoPor) . '</footer>'
                        . '</body></html>';
                    break;

                case 'lista_entrenadores':
                    $idEntrenador = isset($_POST['id_entrenador']) && $_POST['id_entrenador'] !== '' 
                        ? (int)$_POST['id_entrenador'] 
                        : null;
                    
                    $datos = $objReporte->listaEntrenadores($idEntrenador);
                    $titulo = $idEntrenador ? 'Ficha del Entrenador' : 'Lista de Entrenadores';

                    $filas = '';
                    $num = 0;
                    foreach ($datos as $d) {
                        $num++;
                        $genero = $d['genero'] === 'M' ? 'Masculino' : 'Femenino';
                        $filas .= '<tr>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;">' . $num . '</td>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;">' . htmlspecialchars($d['cedula']) . '</td>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;font-weight:500;">' . htmlspecialchars($d['nombres'] . ' ' . $d['apellidos']) . '</td>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;">' . htmlspecialchars($d['edad']) . ' años</td>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;">' . htmlspecialchars($genero) . '</td>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;">' . htmlspecialchars($d['telefono']) . '</td>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;">' . htmlspecialchars($d['correo'] ?: '-') . '</td>'
                            . '</tr>';
                    }

                    $total = count($datos);
                    $subtitulo = $idEntrenador 
                        ? 'Ficha completa del entrenador' 
                        : 'Total: ' . $total . ' entrenadores registrados';

                    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
                        body{font-family:Helvetica,Arial,sans-serif;margin:30px;color:#1f2937;}
                        h1{color:#4f46e5;font-size:18px;margin-bottom:4px;}
                        h2{color:#374151;font-size:12px;font-weight:normal;margin-bottom:4px;}
                        h2 span{font-weight:bold;color:#4f46e5;}
                        table{width:100%;border-collapse:collapse;font-size:10px;margin-top:15px;}
                        th{background:#4f46e5;color:#fff;padding:6px 8px;text-align:left;font-size:10px;}
                        td{padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;}
                        tr:nth-child(even) td{background:#f9fafb;}
                        footer{margin-top:30px;font-size:9px;color:#9ca3af;text-align:center;}
                        .ficha-dato{display:grid;grid-template-columns:120px 1fr;gap:8px;margin:4px 0;font-size:12px;}
                        .ficha-label{color:#6b7280;font-weight:bold;}
                    </style></head><body>';

                    if ($idEntrenador !== null && count($datos) > 0) {
                        $d = $datos[0];
                        $genero = $d['genero'] === 'M' ? 'Masculino' : 'Femenino';
                        $html .= '<h1>' . htmlspecialchars($d['nombres'] . ' ' . $d['apellidos']) . '</h1>';
                        $html .= '<div style="margin:20px 0;">';
                        $html .= '<div class="ficha-dato"><span class="ficha-label">Cédula:</span><span>' . htmlspecialchars($d['cedula']) . '</span></div>';
                        $html .= '<div class="ficha-dato"><span class="ficha-label">Fecha Nacimiento:</span><span>' . htmlspecialchars($d['fecha_nacimiento']) . ' (' . $d['edad'] . ' años)</span></div>';
                        $html .= '<div class="ficha-dato"><span class="ficha-label">Género:</span><span>' . htmlspecialchars($genero) . '</span></div>';
                        $html .= '<div class="ficha-dato"><span class="ficha-label">Teléfono:</span><span>' . htmlspecialchars($d['telefono']) . '</span></div>';
                        $html .= '<div class="ficha-dato"><span class="ficha-label">Correo:</span><span>' . htmlspecialchars($d['correo'] ?: '-') . '</span></div>';
                        $html .= '<div class="ficha-dato"><span class="ficha-label">Dirección:</span><span>' . htmlspecialchars($d['direccion'] ?: '-') . '</span></div>';
                        $html .= '<div class="ficha-dato"><span class="ficha-label">Atletas a cargo:</span><span>' . htmlspecialchars($d['total_atletas'] ?? '0') . '</span></div>';
                        $html .= '<div class="ficha-dato"><span class="ficha-label">Grupos asignados:</span><span>' . htmlspecialchars($d['grupos_asignados'] ?: 'Ninguno') . '</span></div>';
                        $html .= '</div>';
                    } else {
                        $html .= '<h1>' . $titulo . '</h1>
                            <h2>' . $subtitulo . '</h2>
                            <table><thead><tr><th>#</th><th>Cédula</th><th>Nombre Completo</th><th>Edad</th><th>Género</th><th>Teléfono</th><th>Correo</th></tr></thead><tbody>' . $filas . '</tbody></table>';
                    }

                    $html .= '<footer>Generado el ' . $fechaGeneracion . ' por ' . htmlspecialchars($generadoPor) . '</footer>
                        </body></html>';
                    break;

                case 'lista_grupos':
                    $estado = $_POST['estado'] ?? 'Activo';
                    $datos = $objReporte->listaGrupos($estado);
                    $titulo = 'Lista de Grupos de Entrenamiento';
                    
                    $estadoTexto = $estado === 'Todos' ? 'Todos los estados' : ucfirst(strtolower($estado));
                    $subtitulo = 'Estado: ' . $estadoTexto;
                    
                    $filas = '';
                    $num = 0;
                    foreach ($datos as $d) {
                        $num++;
                        $estadoBadge = $d['activo'] == 1 
                            ? '<span style="background:#10b981;color:#fff;padding:2px 8px;border-radius:12px;font-size:9px;font-weight:bold;">Activo</span>'
                            : '<span style="background:#6b7280;color:#fff;padding:2px 8px;border-radius:12px;font-size:9px;font-weight:bold;">Archivado</span>';
                        $entrenador = $d['entrenador_nombre'] ? htmlspecialchars($d['entrenador_nombre']) . ' (' . htmlspecialchars($d['entrenador_cedula']) . ')' : '<span style="color:#9ca3af;">Sin entrenador</span>';
                        $filas .= '<tr>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;">' . $num . '</td>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;font-weight:500;">' . htmlspecialchars($d['nombre']) . '</td>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;">' . htmlspecialchars($d['descripcion'] ?: '-') . '</td>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;">' . $entrenador . '</td>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;text-align:center;">' . $d['total_atletas'] . '</td>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;text-align:center;">' . $estadoBadge . '</td>'
                            . '</tr>';
                    }
                    
                    $total = count($datos);
                    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
                        body{font-family:Helvetica,Arial,sans-serif;margin:30px;color:#1f2937;}
                        h1{color:#4f46e5;font-size:18px;margin-bottom:4px;}
                        h2{color:#374151;font-size:12px;font-weight:normal;margin-bottom:4px;}
                        table{width:100%;border-collapse:collapse;font-size:10px;margin-top:15px;}
                        th{background:#4f46e5;color:#fff;padding:6px 8px;text-align:left;font-size:10px;}
                        th.center{text-align:center;}
                        td{padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;}
                        td.center{text-align:center;}
                        tr:nth-child(even) td{background:#f9fafb;}
                        footer{margin-top:30px;font-size:9px;color:#9ca3af;text-align:center;border-top:1px solid #e5e7eb;padding-top:15px;}
                        .total{font-size:11px;color:#6b7280;margin-bottom:10px;}
                        .total span{font-weight:bold;color:#4f46e5;}
                    </style></head><body>';
                    
                    $html .= '<h1>' . $titulo . '</h1>';
                    $html .= '<h2>' . $subtitulo . '</h2>';
                    $html .= '<p class="total">Total: <span>' . $total . '</span> grupos</p>';
                    $html .= '<table><thead><tr>
                        <th style="width:30px;">#</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Entrenador</th>
                        <th class="center">Atletas</th>
                        <th class="center">Estado</th>
                    </tr></thead><tbody>' . $filas . '</tbody></table>';
                    $html .= '<footer>Generado el ' . $fechaGeneracion . ' por ' . htmlspecialchars($generadoPor) . '</footer>';
                    $html .= '</body></html>';
                    break;

                case 'detalle_grupo':
                    $idGrupo = (int)($_POST['id_grupo'] ?? 0);
                    $datos = $objReporte->detalleGrupo($idGrupo);
                    
                    if (!$datos) {
                        jsonSalida(['status' => 'error', 'message' => 'Grupo no encontrado.']);
                    }
                    
                    $grupo = $datos['grupo'];
                    $atletas = $datos['atletas'];
                    $totalAtletas = $datos['total_atletas'];
                    
                    $titulo = 'Detalle del Grupo: ' . htmlspecialchars($grupo['nombre']);
                    
                    $estadoBadge = $grupo['activo'] == 1 
                        ? '<span style="background:#10b981;color:#fff;padding:2px 10px;border-radius:12px;font-size:10px;font-weight:bold;">ACTIVO</span>'
                        : '<span style="background:#6b7280;color:#fff;padding:2px 10px;border-radius:12px;font-size:10px;font-weight:bold;">ARCHIVADO</span>';
                    
                    $infoGrupo = '
                        <div style="background:#f9fafb;border-radius:8px;padding:15px;margin:15px 0;">
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                                <div><strong style="color:#4f46e5;">Nombre:</strong> ' . htmlspecialchars($grupo['nombre']) . '</div>
                                <div><strong style="color:#4f46e5;">Estado:</strong> ' . $estadoBadge . '</div>
                                <div><strong style="color:#4f46e5;">Entrenador:</strong> ' . htmlspecialchars($grupo['entrenador_nombre'] ?: 'Sin asignar') . '</div>
                                <div><strong style="color:#4f46e5;">Cédula Entrenador:</strong> ' . htmlspecialchars($grupo['entrenador_cedula'] ?: '-') . '</div>
                                <div><strong style="color:#4f46e5;">Teléfono Entrenador:</strong> ' . htmlspecialchars($grupo['entrenador_telefono'] ?: '-') . '</div>
                                <div><strong style="color:#4f46e5;">Correo Entrenador:</strong> ' . htmlspecialchars($grupo['entrenador_correo'] ?: '-') . '</div>
                                <div style="grid-column:span 2;"><strong style="color:#4f46e5;">Descripción:</strong> ' . htmlspecialchars($grupo['descripcion'] ?: 'Sin descripción') . '</div>
                                <div style="grid-column:span 2;"><strong style="color:#4f46e5;">Total Atletas:</strong> ' . $totalAtletas . '</div>
                            </div>
                        </div>
                    ';
                    
                    if (count($atletas) > 0) {
                        $filasAtletas = '';
                        $num = 0;
                        foreach ($atletas as $a) {
                            $num++;
                            $filasAtletas .= '<tr>'
                                . '<td style="padding:4px 8px;border-bottom:1px solid #e5e7eb;font-size:9px;text-align:center;">' . $num . '</td>'
                                . '<td style="padding:4px 8px;border-bottom:1px solid #e5e7eb;font-size:9px;">' . htmlspecialchars($a['cedula']) . '</td>'
                                . '<td style="padding:4px 8px;border-bottom:1px solid #e5e7eb;font-size:9px;font-weight:500;">' . htmlspecialchars($a['nombres'] . ' ' . $a['apellidos']) . '</td>'
                                . '<td style="padding:4px 8px;border-bottom:1px solid #e5e7eb;font-size:9px;text-align:center;">' . $a['edad'] . '</td>'
                                . '<td style="padding:4px 8px;border-bottom:1px solid #e5e7eb;font-size:9px;">' . htmlspecialchars($a['categoria_nombre'] ?: '-') . '</td>'
                                . '<td style="padding:4px 8px;border-bottom:1px solid #e5e7eb;font-size:9px;">' . htmlspecialchars($a['telefono'] ?: '-') . '</td>'
                                . '<td style="padding:4px 8px;border-bottom:1px solid #e5e7eb;font-size:9px;">' . htmlspecialchars($a['rep_nombres'] ? $a['rep_nombres'] . ' ' . $a['rep_apellidos'] : '-') . '</td>'
                                . '</tr>';
                        }
                        
                        $tablaAtletas = '
                            <h3 style="color:#4f46e5;font-size:13px;margin:20px 0 10px 0;border-bottom:2px solid #4f46e5;padding-bottom:5px;">Atletas Asignados (' . count($atletas) . ')</h3>
                            <table style="width:100%;border-collapse:collapse;font-size:9px;">
                                <thead>
                                    <tr>
                                        <th style="background:#4f46e5;color:#fff;padding:5px 8px;text-align:center;">#</th>
                                        <th style="background:#4f46e5;color:#fff;padding:5px 8px;text-align:left;">Cédula</th>
                                        <th style="background:#4f46e5;color:#fff;padding:5px 8px;text-align:left;">Nombre</th>
                                        <th style="background:#4f46e5;color:#fff;padding:5px 8px;text-align:center;">Edad</th>
                                        <th style="background:#4f46e5;color:#fff;padding:5px 8px;text-align:left;">Categoría</th>
                                        <th style="background:#4f46e5;color:#fff;padding:5px 8px;text-align:left;">Teléfono</th>
                                        <th style="background:#4f46e5;color:#fff;padding:5px 8px;text-align:left;">Representante</th>
                                    </tr>
                                </thead>
                                <tbody>' . $filasAtletas . '</tbody>
                            </table>
                        ';
                    } else {
                        $tablaAtletas = '
                            <div style="text-align:center;padding:20px;color:#6b7280;font-size:12px;">
                                No hay atletas asignados a este grupo
                            </div>
                        ';
                    }
                    
                    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
                        body{font-family:Helvetica,Arial,sans-serif;margin:30px;color:#1f2937;}
                        h1{color:#4f46e5;font-size:18px;margin-bottom:4px;}
                        h2{color:#374151;font-size:12px;font-weight:normal;margin-bottom:4px;}
                        table{width:100%;border-collapse:collapse;margin-top:10px;}
                        th{background:#4f46e5;color:#fff;padding:5px 8px;text-align:left;font-size:9px;}
                        td{padding:4px 8px;border-bottom:1px solid #e5e7eb;font-size:9px;}
                        tr:nth-child(even) td{background:#f9fafb;}
                        footer{margin-top:30px;font-size:9px;color:#9ca3af;text-align:center;border-top:1px solid #e5e7eb;padding-top:15px;}
                    </style></head><body>';
                    
                    $html .= '<h1>' . $titulo . '</h1>';
                    $html .= '<h2>Ficha completa del grupo de entrenamiento</h2>';
                    $html .= $infoGrupo;
                    $html .= $tablaAtletas;
                    $html .= '<footer>Generado el ' . $fechaGeneracion . ' por ' . htmlspecialchars($generadoPor) . '</footer>';
                    $html .= '</body></html>';
                    break;

                case 'detalle_sesion':
                    $idSesion = (int)($_POST['id_sesion'] ?? 0);
                    $datos = $objReporte->detalleSesion($idSesion);
                    
                    if (!$datos) {
                        jsonSalida(['status' => 'error', 'message' => 'Sesion no encontrada.']);
                    }
                    
                    $titulo = 'Detalle de Sesion: ' . $datos['fecha'];
                    
                    $estadoBadge = $datos['estado'] === 'Completada' 
                        ? '<span style="background:#10b981;color:#fff;padding:2px 10px;border-radius:12px;font-size:10px;font-weight:bold;">COMPLETADA</span>'
                        : ($datos['estado'] === 'Parcial' 
                            ? '<span style="background:#f59e0b;color:#fff;padding:2px 10px;border-radius:12px;font-size:10px;font-weight:bold;">PARCIAL</span>'
                            : ($datos['estado'] === 'Planificada'
                                ? '<span style="background:#6366f1;color:#fff;padding:2px 10px;border-radius:12px;font-size:10px;font-weight:bold;">PLANIFICADA</span>'
                                : '<span style="background:#ef4444;color:#fff;padding:2px 10px;border-radius:12px;font-size:10px;font-weight:bold;">CANCELADA</span>'));
                    
                    $infoSesion = '
                        <div style="background:#f9fafb;border-radius:8px;padding:15px;margin:15px 0;">
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                                <div><strong style="color:#4f46e5;">Fecha:</strong> ' . htmlspecialchars($datos['fecha']) . '</div>
                                <div><strong style="color:#4f46e5;">Estado:</strong> ' . $estadoBadge . '</div>
                                <div><strong style="color:#4f46e5;">Grupo:</strong> ' . htmlspecialchars($datos['grupo_nombre']) . '</div>
                                <div><strong style="color:#4f46e5;">Entrenador:</strong> ' . htmlspecialchars($datos['entrenador_nombre'] ?: 'Sin asignar') . '</div>
                                <div><strong style="color:#4f46e5;">Tipo:</strong> ' . htmlspecialchars($datos['tipo_sesion']) . '</div>
                                <div><strong style="color:#4f46e5;">Duración:</strong> ' . $datos['duracion_minutos'] . ' min</div>
                                <div><strong style="color:#4f46e5;">Volumen Planificado:</strong> ' . number_format($datos['volumen_planificado'] ?? 0) . ' m</div>
                                <div><strong style="color:#4f46e5;">Volumen Ejecutado:</strong> ' . number_format($datos['volumen_ejecutado'] ?? 0) . ' m</div>
                                <div style="grid-column:span 2;"><strong style="color:#4f46e5;">Calentamiento:</strong> ' . htmlspecialchars($datos['calentamiento'] ?: 'Ninguno') . '</div>
                                <div style="grid-column:span 2;"><strong style="color:#4f46e5;">Vuelta a la Calma:</strong> ' . htmlspecialchars($datos['vuelta_calma'] ?: 'Ninguno') . '</div>
                                <div style="grid-column:span 2;"><strong style="color:#4f46e5;">Observaciones:</strong> ' . htmlspecialchars($datos['observaciones'] ?: 'Sin observaciones') . '</div>
                            </div>
                        </div>
                    ';
                    
                    $resumenBloques = '
                        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin:15px 0;">
                            <div style="background:#eef2ff;border-radius:6px;padding:10px;text-align:center;">
                                <p style="font-size:10px;color:#6366f1;font-weight:bold;margin:0;">CALENTAMIENTO</p>
                                <p style="font-size:16px;font-weight:bold;color:#1f2937;margin:5px 0 0 0;">' . number_format($datos['vol_calentamiento'] ?? 0) . ' m</p>
                            </div>
                            <div style="background:#dbeafe;border-radius:6px;padding:10px;text-align:center;">
                                <p style="font-size:10px;color:#2563eb;font-weight:bold;margin:0;">BLOQUE PRINCIPAL</p>
                                <p style="font-size:16px;font-weight:bold;color:#1f2937;margin:5px 0 0 0;">' . number_format($datos['vol_principal'] ?? 0) . ' m</p>
                            </div>
                            <div style="background:#d1fae5;border-radius:6px;padding:10px;text-align:center;">
                                <p style="font-size:10px;color:#059669;font-weight:bold;margin:0;">VUELTA A LA CALMA</p>
                                <p style="font-size:16px;font-weight:bold;color:#1f2937;margin:5px 0 0 0;">' . number_format($datos['vol_vuelta_calma'] ?? 0) . ' m</p>
                            </div>
                        </div>
                    ';
                    
                    if (count($datos['series']) > 0) {
                        $filasSeries = '';
                        $num = 0;
                        foreach ($datos['series'] as $s) {
                            $num++;
                            $volumen = ($s['repeticiones'] ?? 0) * ($s['distancia_m'] ?? 0);
                            $nombreDrill = $s['drill_nombre'] ?: $s['ejercicio_descripcion'] ?: 'Ejercicio libre';
                            $filasSeries .= '<tr>'
                                . '<td style="padding:4px 8px;border-bottom:1px solid #e5e7eb;font-size:9px;text-align:center;">' . $num . '</td>'
                                . '<td style="padding:4px 8px;border-bottom:1px solid #e5e7eb;font-size:9px;">' . htmlspecialchars($s['bloque']) . '</td>'
                                . '<td style="padding:4px 8px;border-bottom:1px solid #e5e7eb;font-size:9px;">' . htmlspecialchars($nombreDrill) . '</td>'
                                . '<td style="padding:4px 8px;border-bottom:1px solid #e5e7eb;font-size:9px;text-align:center;">' . $s['repeticiones'] . '</td>'
                                . '<td style="padding:4px 8px;border-bottom:1px solid #e5e7eb;font-size:9px;text-align:center;">' . $s['distancia_m'] . ' m</td>'
                                . '<td style="padding:4px 8px;border-bottom:1px solid #e5e7eb;font-size:9px;text-align:center;">' . $s['descanso_seg'] . ' s</td>'
                                . '<td style="padding:4px 8px;border-bottom:1px solid #e5e7eb;font-size:9px;text-align:center;">' . htmlspecialchars($s['zona_intensidad']) . '</td>'
                                . '<td style="padding:4px 8px;border-bottom:1px solid #e5e7eb;font-size:9px;text-align:center;font-weight:bold;color:#4f46e5;">' . number_format($volumen) . ' m</td>'
                                . '</tr>';
                        }
                        
                        $tablaSeries = '
                            <h3 style="color:#4f46e5;font-size:13px;margin:20px 0 10px 0;border-bottom:2px solid #4f46e5;padding-bottom:5px;">Series Planificadas (' . count($datos['series']) . ')</h3>
                            <table style="width:100%;border-collapse:collapse;font-size:9px;">
                                <thead>
                                    <tr>
                                        <th style="background:#4f46e5;color:#fff;padding:5px 8px;text-align:center;">#</th>
                                        <th style="background:#4f46e5;color:#fff;padding:5px 8px;text-align:left;">Bloque</th>
                                        <th style="background:#4f46e5;color:#fff;padding:5px 8px;text-align:left;">Ejercicio</th>
                                        <th style="background:#4f46e5;color:#fff;padding:5px 8px;text-align:center;">Rep.</th>
                                        <th style="background:#4f46e5;color:#fff;padding:5px 8px;text-align:center;">Distancia</th>
                                        <th style="background:#4f46e5;color:#fff;padding:5px 8px;text-align:center;">Descanso</th>
                                        <th style="background:#4f46e5;color:#fff;padding:5px 8px;text-align:center;">Intensidad</th>
                                        <th style="background:#4f46e5;color:#fff;padding:5px 8px;text-align:center;">Volumen</th>
                                    </tr>
                                </thead>
                                <tbody>' . $filasSeries . '</tbody>
                            </table>
                        ';
                    } else {
                        $tablaSeries = '
                            <div style="text-align:center;padding:20px;color:#6b7280;font-size:12px;">
                                No hay series registradas para esta sesión
                            </div>
                        ';
                    }
                    
                    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
                        body{font-family:Helvetica,Arial,sans-serif;margin:30px;color:#1f2937;}
                        h1{color:#4f46e5;font-size:18px;margin-bottom:4px;}
                        h2{color:#374151;font-size:12px;font-weight:normal;margin-bottom:4px;}
                        table{width:100%;border-collapse:collapse;margin-top:10px;}
                        th{background:#4f46e5;color:#fff;padding:5px 8px;text-align:left;font-size:9px;}
                        td{padding:4px 8px;border-bottom:1px solid #e5e7eb;font-size:9px;}
                        tr:nth-child(even) td{background:#f9fafb;}
                        footer{margin-top:30px;font-size:9px;color:#9ca3af;text-align:center;border-top:1px solid #e5e7eb;padding-top:15px;}
                    </style></head><body>';
                    
                    $html .= '<h1>' . $titulo . '</h1>';
                    $html .= '<h2>Ficha completa de la sesión de entrenamiento</h2>';
                    $html .= $infoSesion;
                    $html .= $resumenBloques;
                    $html .= $tablaSeries;
                    $html .= '<footer>Generado el ' . $fechaGeneracion . ' por ' . htmlspecialchars($generadoPor) . '</footer>';
                    $html .= '</body></html>';
                    break;

                case 'resumen_sesiones_grupo':
                    $idGrupo = (int)($_POST['id_grupo'] ?? 0);
                    $estado = $_POST['estado'] ?? '';
                    $fechaIni = $_POST['fecha_ini'] ?? '';
                    $fechaFin = $_POST['fecha_fin'] ?? '';
                    $graficaImagen = $_POST['grafica_imagen'] ?? '';
                    
                    $datos = $objReporte->resumenSesionesGrupo($idGrupo, $estado, $fechaIni, $fechaFin);
                    $grupos = $objReporte->obtenerGruposSelect();
                    $nombreGrupo = '';
                    foreach ($grupos as $g) {
                        if ($g['id_grupo'] == $idGrupo) {
                            $nombreGrupo = $g['nombre'];
                            break;
                        }
                    }
                    
                    $titulo = 'Resumen de Sesiones - ' . htmlspecialchars($nombreGrupo);
                    $subtitulo = 'Periodo: ' . $fechaIni . ' a ' . $fechaFin . ($estado ? ' | Estado: ' . htmlspecialchars($estado) : '');
                    
                    $totalPlanificado = 0;
                    $totalEjecutado = 0;
                    $totalSesiones = count($datos);
                    
                    foreach ($datos as $d) {
                        $totalPlanificado += (int)($d['volumen_planificado'] ?? 0);
                        $totalEjecutado += (int)($d['volumen_ejecutado'] ?? 0);
                    }
                    
                    $filas = '';
                    $num = 0;
                    foreach ($datos as $d) {
                        $num++;
                        $pl = (int)($d['volumen_planificado'] ?? 0);
                        $ej = (int)($d['volumen_ejecutado'] ?? 0);
                        $pct = $pl > 0 ? round(($ej / $pl) * 100, 1) : 0;
                        $colorPct = $pct >= 95 ? '#10b981' : ($pct >= 80 ? '#f59e0b' : '#ef4444');
                        $estadoColor = $d['estado'] === 'Completada' ? '#10b981' : ($d['estado'] === 'Parcial' ? '#f59e0b' : ($d['estado'] === 'Planificada' ? '#6366f1' : '#ef4444'));
                        
                        $filas .= '<tr>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;text-align:center;">' . $num . '</td>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;">' . htmlspecialchars($d['fecha']) . '</td>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;">' . htmlspecialchars($d['tipo_sesion']) . '</td>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;text-align:center;color:' . $estadoColor . ';font-weight:bold;">' . htmlspecialchars($d['estado']) . '</td>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;text-align:center;">' . number_format($pl) . ' m</td>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;text-align:center;">' . number_format($ej) . ' m</td>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;text-align:center;color:' . $colorPct . ';font-weight:bold;">' . $pct . '%</td>'
                            . '<td style="padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;text-align:center;">' . $d['duracion_minutos'] . ' min</td>'
                            . '</tr>';
                    }
                    
                    $imgTag = $graficaImagen ? '<img src="' . $graficaImagen . '" style="width:100%;max-width:650px;margin:0 auto 20px;display:block;">' : '';
                    
                    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
                        body{font-family:Helvetica,Arial,sans-serif;margin:30px;color:#1f2937;}
                        h1{color:#4f46e5;font-size:18px;margin-bottom:4px;}
                        h2{color:#374151;font-size:12px;font-weight:normal;margin-bottom:4px;}
                        table{width:100%;border-collapse:collapse;font-size:10px;margin-top:15px;}
                        th{background:#4f46e5;color:#fff;padding:6px 8px;text-align:left;font-size:10px;}
                        th.center{text-align:center;}
                        td{padding:5px 8px;border-bottom:1px solid #e5e7eb;font-size:10px;}
                        td.center{text-align:center;}
                        tr:nth-child(even) td{background:#f9fafb;}
                        footer{margin-top:30px;font-size:9px;color:#9ca3af;text-align:center;border-top:1px solid #e5e7eb;padding-top:15px;}
                        .resumen{display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin:15px 0;}
                        .resumen-item{background:#f9fafb;border-radius:6px;padding:10px;text-align:center;border:1px solid #e5e7eb;}
                        .resumen-item .numero{font-size:20px;font-weight:bold;color:#4f46e5;}
                        .resumen-item .label{font-size:10px;color:#6b7280;font-weight:bold;text-transform:uppercase;}
                    </style></head><body>';
                    
                    $html .= '<h1>' . $titulo . '</h1>';
                    $html .= '<h2>' . $subtitulo . '</h2>';
                    $html .= '<div class="resumen">';
                    $html .= '<div class="resumen-item"><div class="label">Total Sesiones</div><div class="numero">' . $totalSesiones . '</div></div>';
                    $html .= '<div class="resumen-item"><div class="label">Volumen Planificado</div><div class="numero">' . number_format($totalPlanificado) . ' m</div></div>';
                    $html .= '<div class="resumen-item"><div class="label">Volumen Ejecutado</div><div class="numero">' . number_format($totalEjecutado) . ' m</div></div>';
                    $html .= '</div>';
                    $html .= $imgTag;
                    $html .= '<table><thead><tr>
                        <th style="width:30px;text-align:center;">#</th>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th class="center">Estado</th>
                        <th class="center">Planificado</th>
                        <th class="center">Ejecutado</th>
                        <th class="center">Cumplimiento</th>
                        <th class="center">Duración</th>
                    </tr></thead><tbody>' . $filas . '</tbody></table>';
                    $html .= '<footer>Generado el ' . $fechaGeneracion . ' por ' . htmlspecialchars($generadoPor) . '</footer>';
                    $html .= '</body></html>';
                    break;

                default:
                    jsonSalida(['status' => 'error', 'message' => 'Tipo de reporte no reconocido.']);
            }

            if (empty($html)) {
                jsonSalida(['status' => 'error', 'message' => 'No se pudo generar el reporte.']);
            }

            $dompdf->loadHtml($html);
            $dompdf->render();
            $dompdf->stream('reporte_' . $tipo . '_' . date('Ymd_His') . '.pdf', ['Attachment' => true]);
            exit;

        } catch (\Throwable $e) {
            error_log('reportesControlador::generar_pdf - ' . $e->getMessage());
            jsonSalida(['status' => 'error', 'message' => 'Error al generar el PDF.']);
        }
    }

    jsonSalida(['status' => 'error', 'message' => 'Accion no reconocida.']);
}
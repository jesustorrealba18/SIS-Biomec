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

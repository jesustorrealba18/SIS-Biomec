<?php
// test_dompdf.php - Versión SIMPLE y corregida

// Cargar autoload
require_once __DIR__ . '/vendor/autoload.php';

// Verificar que la clase existe
if (!class_exists('Dompdf\\Dompdf')) {
    die('❌ Dompdf no está disponible');
}

echo '✅ Dompdf clase disponible<br>';

try {
    // Crear instancia
    $dompdf = new \Dompdf\Dompdf();
    
    // HTML simple para probar
    $html = '<h1 style="color:#4f46e5;">✅ Prueba exitosa</h1>
             <p>Fecha: ' . date('d/m/Y H:i:s') . '</p>
             <p>dompdf está funcionando correctamente.</p>';
    
    // Generar PDF
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    
    // Guardar
    $output = $dompdf->output();
    file_put_contents('test_pdf_simple.pdf', $output);
    
    echo '✅ PDF generado correctamente<br>';
    echo '<a href="test_pdf_simple.pdf" target="_blank">📄 Descargar PDF</a>';
    
} catch (Exception $e) {
    echo '❌ Error: ' . $e->getMessage();
}
?>
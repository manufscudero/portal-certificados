<?php
ob_start();

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/lib/fpdf.php';

// Obtener parámetros desde la URL
$plantilla_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$nombre_persona = isset($_GET['nombre']) ? trim($_GET['nombre']) : 'NOMBRE DEL PARTICIPANTE';

// Convertir texto a mayúsculas
$nombre_persona = mb_strtoupper($nombre_persona, 'UTF-8');

// Consultar la plantilla en la BD
$stmt = $pdo->prepare("SELECT * FROM plantillas_certificados WHERE id = ?");
$stmt->execute([$plantilla_id]);
$plantilla = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$plantilla) {
    die("Error: Plantilla no encontrada (ID: " . htmlspecialchars($plantilla_id) . ").");
}

// Rutas de archivos
$path_png = __DIR__ . '/uploads/plantillas/' . $plantilla['imagen_png'];
$path_ttf = __DIR__ . '/uploads/fuentes/' . $plantilla['fuente_ttf'];

if (!file_exists($path_png)) {
    die("Error: La imagen de la plantilla no existe en la ruta: " . htmlspecialchars($path_png));
}

// Obtener dimensiones reales de la imagen para determinar orientación
list($ancho_px, $alto_px) = getimagesize($path_png);
$orientacion = ($ancho_px >= $alto_px) ? 'L' : 'P';

// Medidas estándar A4 en mm
$ancho_a4 = ($orientacion == 'L') ? 297 : 210;
$alto_a4  = ($orientacion == 'L') ? 210 : 297;

// Instanciar FPDF
$pdf = new FPDF($orientacion, 'mm', 'A4');
$pdf->AddPage();
$pdf->SetAutoPageBreak(false);

// Insertar la imagen de fondo abarcando la página completa
$pdf->Image($path_png, 0, 0, $ancho_a4, $alto_a4);

// Procesar color HEX
$hex = ltrim($plantilla['color_hex'] ?? '#000000', '#');
if (strlen($hex) === 3) {
    $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
}
$r = hexdec(substr($hex, 0, 2));
$g = hexdec(substr($hex, 2, 2));
$b = hexdec(substr($hex, 4, 2));

// Proporción de conversión para el tamaño de fuente (de px/web a puntos FPDF)
$tamano_fuente = (float)($plantilla['tamano_fuente'] ?? 24);
// Si el tamaño en BD es superior a 60, lo escalamos para evitar que ocupe toda la hoja
if ($tamano_fuente > 60) {
    $tamano_fuente = $tamano_fuente * 0.35; 
}

// Posición vertical (Y) en mm
$pos_y_mm = ($alto_a4 * (float)$plantilla['posicion_y']) / 100;

// Configurar color de texto
$pdf->SetTextColor($r, $g, $b);

// Asignar fuente 
$fuente_cargada = false;
if (file_exists($path_ttf) && method_exists($pdf, 'AddFont')) {
    try {
        $nombre_fuente = pathinfo($plantilla['fuente_ttf'], PATHINFO_FILENAME);
        // Si tu librería soporta TTF directamente (ej: tFPDF)
        $pdf->AddFont($nombre_fuente, '', $plantilla['fuente_ttf'], true);
        $pdf->SetFont($nombre_fuente, '', $tamano_fuente);
        $fuente_cargada = true;
    } catch (Exception $e) {
        $fuente_cargada = false;
    }
}

if (!$fuente_cargada) {
    $pdf->SetFont('Arial', 'B', $tamano_fuente);
}

// Posicionamiento de texto
$pdf->SetXY(0, $pos_y_mm);

// Convertir caracteres para FPDF básico (manejo de tildes y Ñ)
if (function_exists('iconv')) {
    $texto_final = @iconv('UTF-8', 'windows-1252//TRANSLIT', $nombre_persona);
} else {
    $texto_final = utf8_decode($nombre_persona);
}

// Imprimir el nombre centrado horizontalmente
$pdf->Cell($ancho_a4, 15, $texto_final, 0, 1, 'C');

// Limpiar cualquier búfer de salida previo antes de enviar los encabezados PDF
if (ob_get_length()) {
    ob_end_clean();
}

// Enviar el PDF directamente al navegador
$pdf->Output('I', 'Certificado_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $nombre_persona) . '.pdf');
exit;
<?php

require_once 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;

if ( ! isset($_GET['pdf']) ) {
  $content = '<html>';
  $content .= '<head>';
  $content .= '<style>';
  $content .= 'body { font-family: DejaVu Sans; }';
  $content .= '</style>';
  $content .= '</head><body>';
  $content .= '<h1>Ejemplo generaci&oacute;n PDF</h1>';
  $content .= '<a href="inspeccion_pdf.php?pdf=1">Generar documento PDF</a>';
  $content .= '</body></html>';
  echo $content;
  exit;
}

$content = '<html>';
$content .= '<head>';
$content .= '<style>';
$content .= '</style>';
$content .= '</head><body>';
$content .= '<h1>Ejemplo generaci&oacute;n PDF</h1>';
$content .= 'Almacena en una variable todo el contenido que quieras incorporar ';
$content .= 'en el documento <b>formato HTML</b> para generar a partir de &eacute;ste ';
$content .= 'el documento PDF.<br><br>';
$content .= 'Ejemplo lista<br>';
$content .= '<ul><li>Uno</li><li>Dos</li><li>Tres</li></ul>';
//$content .= 'Ejemplo imagen<br><br>';
//$content .= '<img src="logo-openwebinars.png" alt="" />';
//$content .= '</body></html>';

echo $content; exit;

$dompdf = new Dompdf();
$dompdf->loadHtml($content);
$dompdf->setPaper('A4', 'landscape'); // (Opcional) Configurar papel y orientación
$dompdf->render(); // Generar el PDF desde contenido HTML
$pdf = $dompdf->output(); // Obtener el PDF generado
$dompdf->stream(); // Enviar el PDF generado al navegador
?>
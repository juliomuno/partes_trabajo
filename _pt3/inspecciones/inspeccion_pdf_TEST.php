<?php

  include "../comun/funciones.php";
  include "../comun/db_con.php";
      
  session_start();

  if (!isset($_SESSION['GLB_USR_ID'])) {
    php_redirect('../index.php');
  }
           
  $op = $_REQUEST['op'];
  $id = $_REQUEST['id'];
  $formularios = array();
  $voperarios = array();
  $vsubc = array();
  $vope_subc = array();

  if ($op != 'C') {
    $sentencia = DB_CONSULTA("SELECT * FROM INSP INNER JOIN INSP_TIP_OBR on ITO_ID=INSP_TIP_OBR INNER JOIN INSP_TIP_TRA on ITT_ID=INSP_TIP_TRA WHERE INSP_ID=" . $id);
    if (mysql_num_rows($sentencia) == 1) {
      $row = mysql_fetch_assoc($sentencia);
      $insp_id = $row['INSP_ID'];
      $insp_fec = date("Y-m-d", strtotime($row['INSP_FEC']));
      $insp_pre = $row['INSP_PRE'];
      $insp_dir = $row['INSP_DIR'];
      $insp_pob = $row['INSP_POB'];
      $insp_lat = $row['INSP_LAT'];
      $insp_lon = $row['INSP_LON'];
      $insp_tip_obr = $row['INSP_TIP_OBR'];
      $insp_tip_tra = $row['INSP_TIP_TRA'];
      $insp_usu = $row['INSP_USU'];
      $insp_cssdf = $row['INSP_CSSDF'];
      $insp_sorp = $row['INSP_SORP'];
      $insp_nsorp = $row['INSP_NSORP'];
      $insp_cobs = $row['INSP_COBS'];
      $insp_cinc = $row['INSP_CINC'];
      $insp_inci = $row['INSP_INCI'];
      $par_veh = $row['INSP_VEH'];
      $insp_ext1 = $row['INSP_EXT1'];
      $insp_ext2 = $row['INSP_EXT2'];
      $insp_jef = $row['INSP_JEF'];
      $insp_rpr = $row['INSP_RPR'];
      $insp_obs = $row['INSP_OBS'];
      $insp_tra_des = $row['INSP_TRA_DES'];
      $insp_des_inc = $row['INSP_DES_INC'];
      $ito_nom = $row['ITO_NOM'];
      $itt_nom = $row['ITT_NOM'];
      
      $sentencia = DB_CONSULTA("SELECT * FROM LIST_OPERARIOS_INSPECCIONES WHERE Codigo=" . $insp_usu); 
      while ($row = mysql_fetch_assoc($sentencia)) {
        $nombre = $row['Nombre'];
      } 

      $sentencia = DB_CONSULTA("SELECT Nombre FROM LIST_VEHICULOS WHERE Codigo=" . $par_veh);
      while ($row = mysql_fetch_assoc($sentencia)) {
        $vehiculo = $row['Nombre'];
      } 
      $sentencia = DB_CONSULTA("SELECT * FROM LIST_POBLACIONES WHERE Codigo=" . $insp_pob); 
      while ($row = mysql_fetch_assoc($sentencia)) {
        $nompoblacion = $row['Nombre'];
      } 

      $sentencia = DB_CONSULTA("SELECT Nombre FROM LIST_OPERARIOS WHERE Codigo=" . $insp_jef);
      while ($row = mysql_fetch_assoc($sentencia)) {
        $jefetrabajo = $row['Nombre'];
      }
      
      $sentencia = DB_CONSULTA("SELECT Nombre FROM LIST_OPERARIOS WHERE Codigo=" . $insp_rpr);
      while ($row = mysql_fetch_assoc($sentencia)) {
        $recursopreventivo = $row['Nombre'];
      }
          
      $sentencia = DB_CONSULTA("SELECT * FROM INSP_OPE WHERE IO_INSP=" . $id);
      while ($row = mysql_fetch_assoc($sentencia)) {
        $voperarios[] = $row['IO_OPE'];
      }

      $sentencia = DB_CONSULTA("SELECT * FROM INSP_OPE_SUBC WHERE IOS_INSP=" . $id);
      while ($row = mysql_fetch_assoc($sentencia)) {
        $vope_subc[] = $row['IOS_OPE'];
        $vsubc[] = $row['IOS_SUBC'];
      }

      $sentencia = DB_CONSULTA("SELECT DISTINCT IR_FRM FROM INSP_RESP WHERE IR_INSP=" . $id);
      while ($row = mysql_fetch_assoc($sentencia)) {
        $formularios[] = $row['IR_FRM'];
      }
    } else {
      exit;
    }
  } else {
    $insp_id = '';
    $insp_fec = str_html_fecha(getdate());
    $insp_dir = '';
    $insp_pob = 0;
    $insp_lat = '';
    $insp_lon = '';
    $insp_tip_obr = 0;
    $insp_tip_tra = 0;
    $insp_usu = 0;
    $insp_cssdf = false;
    $insp_sorp = false;
    $insp_nsorp = false;
    $insp_cobs = false;
    $insp_cinc = false;
    $insp_inci = false;
    $insp_subc = 0;
    $insp_obs = '';
    $insp_tra_des = '';
    $insp_des_inc = '';
    $par_veh = 0;
    $insp_ext1 = "";
    $insp_ext2 = "";
    $insp_jef = "";
    $insp_rpr = "";

  }

require_once 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;

$content = '<html lang="es">';
$content .= '<head>';
$content .= '<meta charset="utf-8">';
$content .= '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
$content .= '<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1">';
$content .= '<title>Moneleg - Inspecciones</title>';
$content .= '<link rel="stylesheet" href="../bootstrap-3.2.0/css/bootstrap.min.css"  media="screen">';
$content .= '<link rel="stylesheet" href="../plugins/font-awesome/css/font-awesome.css">';
$content .= '<link rel="stylesheet" href="../css/framework.css" media="screen">';
$content .= '<style>';
$content .= '.table { background: rgb(300,300,300);}';
$content .= 'th.center, td.center {text-align: center;}';
$content .= 'img { display: block'; 
$content .= 'margin: 3 auto;';
$content .= 'max-width: 100%;';
$content .= 'width: 60%;';
$content .= 'max-height=50%;'; 
$content .= 'height=25%;}';
//$content .= 'hr { page-break-after: always;border: 0;margin: 0;padding: 0;}'; 
$content .= '</style>';
$content .= '</head>';
$content .= '<body class="">';
$content .= '<div class="container">';
$content .= '<form name="formulario" id="formulario" class="form-horizontal" role="form" method="POST">';
$content .= '<h4 align="center">Datos Generales</h4>';
$content .= '<div class="form-group">';
$content .= '<label for="num_incidencia" class="col-lg-2">Código:</label>';
$content .= $insp_id;
$content .= '</div>';  
$content .= '<div class="form-group">';
$content .= '<label for="fecha" class="col-lg-2">Fecha:</label>';
$content .=  date("d-m-Y", strtotime($insp_fec));
$content .= '</div>';
$content .= '<div class="form-group">';
$content .= '<label for="presupuesto" class="col-lg-2">Presupuesto:</label>';
$content .= $insp_pre;
$content .= '</div>';
if ($ito_nom != ''){ 
  $content .= '<div class="form-group">';
  $content .= '<label for="" class="col-lg-2">Tipo de Obra:</label>';  
  $content .= $ito_nom;
  $content .= '</div>';    
}
  $content .= '<div class="form-group">';
  $content .= '<label for="presupuesto" class="col-lg-2">Tipo de Trabajo:</label>';  
  $content .= $itt_nom;
  $content .= '</div>'; 
if ($nombre != ''){     
  $content .= '<div class="form-group">';
  $content .= '<label for="presupuesto" class="col-lg-2">Inspeccionado por:</label>';  
  $content .= $nombre;
  $content .= '</div>';
}
$content .= '<div class="form-group">';
$content .= '<label for="presupuesto" class="col-lg-2">Tipo de visita:</label>';
if ($insp_nsorp == 1) {
  $content .= 'No sorpresiva';
}
if ($insp_sorp == 1) {
  $content .= 'Sorpresiva';
}
if ($insp_cssdf == 1) { 
  $content .= ' CSS-DF';
} 
$content .= '</div>';
//$content .= '<hr />';
$content .= '<h4 align="center">Datos de Localización</h4>';
$content .= '<div class="form-group">';
$content .= '<label for="direccion" class="col-lg-2">Direcci&oacute;n:</label>';
$content .= '<div class="col-lg-10">'; 
$content .= $insp_dir;
$content .= '</div></div>';
$content .= '<div class="form-group">';
$content .= '<label for="poblacion" class="col-lg-2">Poblaci&oacute;n:</label>';
$content .= '<div class="col-lg-10">';
$content .= $nompoblacion;
$content .= '</div></div>';
$content .= '<div class="form-group">';
if ($vehiculo != '' ) {
  $content .= '<label for="vehiculo" class="col-lg-2">Veh&iacute;culo:</label>';
  $content .= '<div class="col-lg-10">';
  $content .= $vehiculo;
  $content .= '</div></div>';
}
if ($insp_ext1 != '0'  ) {
  $content .= '<div class="form-group"><label for="extintor1"  class="col-lg-2">Extintor 1:</label>';
  $content .= $insp_ext1;
  $content .= '</div>';
}
if ($insp_ext2 != '0' ) {
  $content .= '<div class="form-group"><label for="extintor2" class="col-lg-2">Extintor 2:</label>';  
  $content .= $insp_ext2;
  $content .= '</div>';
}
$content .= '<h4 align="center">Trabajo Realizado</h4><div class="form-group">';
$content .= '<label for="realizado" class="col-lg-2">Descripción del Trabajo:</label>';
$content .= '<div class="col-lg-10">';
$content .= $insp_tra_des;
$content .= '</div></div>';

if ($jefetrabajo != '' || $recursopreventivo != '') {
  $content .= '<h4 align="center">Operarios Moneleg</h4> <div class="form-group">';
  if ($jefetrabajo != ''){
    $content .= '<label for="vehiculo" class="col-lg-2">Jefe de trabajo:</label><div class="col-lg-10">';
    $content .= $jefetrabajo;
    $content .= '</div></div>';
  }
  if ($recursopreventivo != ''){
    $content .= '<div class="form-group"><label for="vehiculo" class="col-lg-2">Recurso preventivo:</label>';
    $content .= '<div class="col-lg-10">';
    $content .= $recursopreventivo;
    $content .= '</div></div>';
  }
}

$content .= '<div class="form-group"><label for="vehiculo" class="col-lg-2">Operarios:</label>';
foreach($voperarios as $valor) {
   $sentencia = DB_CONSULTA("SELECT * FROM LIST_OPERARIOS WHERE Codigo=" . $valor); 
      while ($row = mysql_fetch_assoc($sentencia)) {
        $operarios = $row['Nombre'];
      }
  $content .= '<div class="col-lg-10">';    
  $content .= $operarios;
  $content .= '</div>';       
}  
$content .= '</div>';
$content .= '<h4 align="center">Operarios Subcontratas</h4> <div class="form-group">';
$content .= '<div class="form-group"><label for="vehiculo" class="col-lg-2">Subcontratas:</label>';
//foreach($vope_subc as $clave => $valor) {
//   $sentencia = DB_CONSULTA("SELECT * FROM LIST_INSPECCIONES_SUBCONTRATAS WHERE Codigo=" . $valor); 
//      while ($row = mysql_fetch_assoc($sentencia)) {
//        $subcontrata = $row['Nombre'];
//      }
//  $content .= '<div class="col-lg-10">';    
//  $content .= $operarios;
//  $content .= '</div>';       
 
//$content .= '</div>';
//$content .= '<div class="form-group"><label for="vehiculo" class="col-lg-2">Operarios:</label>';
//   $sentencia = DB_CONSULTA("SELECT * FROM LIST_INSPECCIONES_SUBCONTRATAS_OPERARIOS WHERE Codigo=" . $valor); 
//      while ($row = mysql_fetch_assoc($sentencia)) {
//        $operario = $row['Nombre'];
//      }
//  $content .= '<div class="col-lg-10">';    
//  $content .= $operarios;
//  $content .= '</div>';       
//}  
$content .= '</div>';
$content .= '<h4 align="center">Resultado</h4>';
$content .= '<div class="form-group"><label for="vehiculo" class="col-lg-2">Resultado inspección:</label>';
if ($insp_cinc == 1){
  $content .= '<div class="col-lg-2">';
  $content .= 'CON INCIDENCIA';
  $content .= '<div class="col-lg-10">';
  $content .= $insp_des_inc;
  $content .= '</div> </div>';
}
if ($insp_cinc == 0){
  $content .= '<div class="col-lg-2">';
  $content .= 'SIN INCIDENCIA';
  $content .= '</div>';
}
if ($insp_cobs == 1){
  $content .= '<div class="col-lg-2">';
  $content .= 'CON OBSERVACIONES';
  $content .= '<div>';
  $content .= $insp_obs;
  $content .= '</div></div> ';
}
if ($insp_cobs == 0){
  $content .= '<div class="col-lg-2">';
  $content .= 'SIN OBSERVACIONES';
  $content .= '</div>'; 
}
$content .= '</div>'; 

$content .= '<div class="form-group">';
$content .= '<h4 align="center">Firma</h4>';
if (file_exists("./firmas/firma_".$insp_id.".png")){ 
  $content .= '<div class="centrador">';
  $content .= '<img src="./firmas/firma_'.$insp_id.'.png">';
  $content .= '</div>';
}                 
$content .= '</div>';
//INICIO FORMULARIO DE SEGURIDAD
$sql = "SELECT * FROM FRM";
$sentencia = DB_CONSULTA($sql);
while ($row = mysql_fetch_assoc($sentencia)) { 

  if (in_array($row['FRM_ID'], $formularios)) {
    $content .= '<div style="page-break-before: always" id="nuevoparte1">';
    $content .= '<h3 align="center">Formulario Seguridad</h3>';
    $content .= '<div class="row"> </div> <div class="row"> </div>';
    $content .= '</div>';
    $sql = "SELECT INSP_RESP.*, PREG_ID, PREG_ORD, PREG_NOM, PREG_CAT.* FROM INSP_RESP, PREG, PREG_CAT WHERE IR_INSP=" . $insp_id . " AND IR_FRM= 1 AND IR_PREG=PREG_ID AND PREG_CAT = PREG_CAT.PC_ID ORDER BY PC_ORD, PREG_ORD";
    $sentencia = DB_CONSULTA($sql);
    $i = 1;
    while ($row = mysql_fetch_assoc($sentencia)) {
      if ($nombre_categoria != $row['PC_NOM']) {
        $nombre_categoria = $row['PC_NOM'];
        $content .= '<h4>' . $row['PC_ORD'] . '. ' . $nombre_categoria . '</h4>';
      }
      $content .= '<div class="row">';
      $content .= '<p>' . $row['PC_ORD'] . '.' . $row['PREG_ORD'] . '. ' . $row['PREG_NOM'] . '</p>';     
      $content .= '</div>';
  
      $sentenciaaux = DB_CONSULTA("SELECT RESP_NOM FROM RESP WHERE RESP_ID=" . $row['IR_RESP']); 
      if (mysql_num_rows($sentenciaaux) == 1) {
        $rowaux = mysql_fetch_assoc($sentenciaaux);
        $content .= '<div class="row"> <div class="col-md-12"> <div class="form-group">';
        $content .= $rowaux['RESP_NOM'];
        $content .= '</div></div></div>';
      }
      
      if ($row['IR_IMG'] == '') {
        $imagen_preliminar = "";
      } else {
        $imagen_preliminar = "../inspecciones_imagenes/" . $row['IR_IMG'];                
        //if (file_exists("../inspecciones_imagenes/".$row['IR_IMG'])){         
        //  $content .= '<img src="../inspecciones_imagenes/'.$row['IR_IMG'].'" width="340" height="300">';
        //} 
        //$content .= '<img src="'.$imagen_preliminar.'" width="350" class="img_miniatura">';                               
        $content .= '<img src="'.$imagen_preliminar.'"/>';
      }
      //$content .= '<a><img src="'.$imagen_preliminar.'" width="150" class="img_miniatura"/></a>';  
            
    $i++;
    }
  } 
}
//FIN FORMULARIO DE SUGURIDAD


$content .= '</form></div>';
$content .= '</body></html>';

echo $content

$dompdf = new Dompdf();
$dompdf->loadHtml($content);
$dompdf->setPaper('A4', 'portrait'); // (Opcional) Configurar papel y orientación
$dompdf->render(); // Generar el PDF desde contenido HTML
$pdf = $dompdf->output(); // Obtener el PDF generado
$dompdf->stream("insp_".$insp_id.".pdf"); // Enviar el PDF generado al navegador
?>
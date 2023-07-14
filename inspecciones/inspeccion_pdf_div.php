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

//generar inspección general de manera independiente
$content = insp_general_crear();
$content .= '</form></div>';
$content .= '</body></html>';
echo $content;
    exit();
PDF_generar($content,"insp_".$insp_id);

//generar los formularios con los datos generales al inicio
$sSQL = "SELECT IR_FRM, FRM_NOM FROM INSP_RESP INNER JOIN FRM ON INSP_RESP.IR_FRM=FRM.FRM_ID WHERE IR_INSP=".$insp_id." GROUP BY IR_FRM, FRM_NOM";
$sentencia = DB_CONSULTA($sSQL); 
  while ($row = mysql_fetch_assoc($sentencia)) {
    $content = insp_general_crear();
    $content .= insp_formulario_crear($row['IRM_FRM'],$row['FRM_NOM']);

    $content .= '</form></div>';
    $content .= '</body></html>';
    //echo $content;
    //exit();
    PDF_generar($content,"insp_".$insp_id."_frm".$row['IRM_FRM']);
  }

function insp_general_crear(){
    $cont = '<html lang="es">';
    $cont .= '<head>';
    $cont .= '<meta charset="utf-8">';
    $cont .= '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
    $cont .= '<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1">';
    $cont .= '<title>Moneleg - Inspecciones</title>';
    $cont .= '<link rel="stylesheet" href="../bootstrap-3.2.0/css/bootstrap.min.css"  media="screen">';
    $cont .= '<link rel="stylesheet" href="../plugins/font-awesome/css/font-awesome.css">';
    $cont .= '<link rel="stylesheet" href="../css/framework.css" media="screen">';
    $cont .= '<style>';
    $cont .= '.table { background: rgb(300,300,300);}';
    $cont .= 'th.center, td.center {text-align: center;}';
    $cont .= 'img { display: block'; 
    $cont .= 'margin: 3 auto;';
    $cont .= 'max-width: 100%;';
    $cont .= 'width: 40%;';
    //$cont .= 'width: 60%;';
    $cont .= 'max-height=50%;'; 
    $cont .= 'height=17%;}';
    //$cont .= 'height=25%;}';
    $cont .= 'hr { page-break-after: always;border: 0;margin: 0;padding: 0;}'; 
    $cont .= '</style>';
    $cont .= '</head>';
    $cont .= '<body class="" style="margin: 10mm 12mm 10mm 12mm;">';
    $cont .= '<div class="container">';
    $cont .= '<form name="formulario" id="formulario" class="form-horizontal" role="form" method="POST">';
    $cont .= '<h4 align="center">DATOS GENERALES</h4>';
    $cont .= inspecciones_registro("num_incidencia","Código",$insp_id,0);
    $cont .= inspecciones_registro("fecha","Fecha:",date("d-m-Y", strtotime($insp_fec)),0);
    $cont .= inspecciones_registro("presupuesto","Presupuesto",$insp_pre,0);
    if ($ito_nom != ''){ 
      $cont .= inspecciones_registro("tip_obr","Tipo de Obra",$ito_nom,0);
    }
      $cont .= inspecciones_registro("tip_tra","Tipo de Trabajo",$itt_nom,0);
    if ($nombre != ''){     
      $cont .= inspecciones_registro("ins_por","Inspeccionado por",$nombre,0);
    }
    $saux = "";
    if ($insp_nsorp == 1) {
      $saux .= 'No sorpresiva';
    }
    if ($insp_sorp == 1) {
      $saux .= 'Sorpresiva';
    }
    if ($insp_cssdf == 1) { 
      $saux .= ' CSS-DF';
    } 
    $cont .= inspecciones_registro("tip_vis","Tipo de visita",$saux,0);
    $cont .= '<h4 align="center">DATOS DE LOCALIZACIÓN</h4>';
    $cont .= inspecciones_registro("direccion","Dirección",$insp_dir,0);
    $cont .= inspecciones_registro("poblacion","Población",$nompoblacion,0);
    if ($vehiculo != '' ) {
      $cont .= inspecciones_registro("vehiculo","Vehículo",$vehiculo,0);
    }
    if ($insp_ext1 != '0'  ) {
      $cont .= inspecciones_registro("extintor1","Extintor 1",$insp_ext1,0);
    }
    if ($insp_ext2 != '0' ) {
      $cont .= inspecciones_registro("extintor2","Extintor 2",$insp_ext2,0);
    }
    $cont .= '<h4 align="center">TRABAJO REALIZADO</h4><div class="form-group">';
    $cont .= inspecciones_registro("realizado","Descripción del Trabajo",$insp_tra_des,0);

    if ($jefetrabajo != '' || $recursopreventivo != '') {
      $cont .= '<hr><h4 align="center">OPERARIOS MONELEG</h4>';
      if ($jefetrabajo != ''){
        $cont .= inspecciones_registro("jef_tra","Jefe de trabajo",$jefetrabajo,0);
      }
      if ($recursopreventivo != ''){
        $cont .= inspecciones_registro("rec_pre","Resurso preventivo",$recursopreventivo,0);
      }
    }

    $cont .= '<div class="form-group"><label for="vehiculo" class="col-lg-2">Operarios:</label>';
    foreach($voperarios as $valor) {
       $sentencia = DB_CONSULTA("SELECT * FROM LIST_OPERARIOS WHERE Codigo=" . $valor); 
          while ($row = mysql_fetch_assoc($sentencia)) {
            $operarios = $row['Nombre'];
          }
      $cont .= '<div class="col-lg-10">';    
      $cont .= $operarios;
      $cont .= '</div>';       
    }  
    $cont .= '</div>';
    $cont .= '<h4 align="center">OPERARIOS SUBCONTRATAS</h4>';
    $cont .= '<div class="form-group">';
    $cont .= '<div class="form-group"><label for="vehiculo" class="col-lg-2">Subcontratas:</label>';
    $cont .= '</div>';
    $cont .= '<hr>';
    $cont .= '<h4 align="center">RESULTADOS</h4>';
    $cont .= '<div class="form-group"><label for="vehiculo" class="col-lg-2">Resultado inspección:</label>';
    if ($insp_cinc == 1){
      $cont .= '<div class="col-lg-2">';
      $cont .= 'CON INCIDENCIA';
      $cont .= '<div class="col-lg-10">';
      $cont .= $insp_des_inc;
      $cont .= '</div> </div>';
    }
    if ($insp_cinc == 0){
      $cont .= '<div class="col-lg-2">';
      $cont .= 'SIN INCIDENCIA';
      $cont .= '</div>';
    }
    if ($insp_cobs == 1){
      $cont .= '<div class="col-lg-2">';
      $cont .= 'CON OBSERVACIONES';
      $cont .= '<div>';
      $cont .= $insp_obs;
      $cont .= '</div></div> ';
    }
    if ($insp_cobs == 0){
      $cont .= '<div class="col-lg-2">';
      $cont .= 'SIN OBSERVACIONES';
      $cont .= '</div>'; 
    }
    $cont .= '</div>'; 

    $cont .= '<div class="form-group">';
    $cont .= '<h4 align="center">Firma</h4>';
    if (file_exists("./firmas/firma_".$insp_id.".png")){ 
      $cont .= '<div class="centrador">';
      $cont .= '<img src="./firmas/firma_'.$insp_id.'.png">';
      $cont .= '</div>';
    }                 
    $cont .= '</div>';
    return $cont;
  }


function insp_formulario_crear($form_id,$form_nom){
    //FORMULARIOS
    $cont .= '<div style="page-break-before: always" id="nuevoparte1">';
    $cont .= '<h3 align="center">'$form_nom.$form_id.'</h3>';
    $cont .= '<div class="row"> </div> <div class="row"> </div>';
    $cont .= '</div>';
    $sql = "SELECT INSP_RESP.*, PREG_ID, PREG_ORD, PREG_NOM, PREG_CAT.* FROM INSP_RESP, PREG, PREG_CAT WHERE IR_INSP=" . $form_id . " AND IR_FRM= 1 AND IR_PREG=PREG_ID AND PREG_CAT = PREG_CAT.PC_ID ORDER BY PC_ORD, PREG_ORD";
    $sentencia = DB_CONSULTA($sql);
    $i = 1;
    while ($row = mysql_fetch_assoc($sentencia)) {
      if ($nombre_categoria != $row['PC_NOM']) {
        $nombre_categoria = $row['PC_NOM'];
        $cont .= '<h4>' . $row['PC_ORD'] . '. ' . $nombre_categoria . '</h4>';
      }
      //$cont .= '<div class="row">';
      //$cont .= '<p>' . $row['PC_ORD'] . '.' . $row['PREG_ORD'] . '. ' . $row['PREG_NOM'] . '</p>';     
      //$cont .= '</div>';
      $pregunta = $row['PC_ORD'] . '.' . $row['PREG_ORD'] . '. ' . $row['PREG_NOM'];
  
      $sentenciaaux = DB_CONSULTA("SELECT RESP_NOM FROM RESP WHERE RESP_ID=" . $row['IR_RESP']); 
      if (mysql_num_rows($sentenciaaux) == 1) {
        $rowaux = mysql_fetch_assoc($sentenciaaux);
        //$cont .= '<div class="row"> <div class="col-md-12"> <div class="form-group">';
        //$cont .= $rowaux['RESP_NOM'];
        //$cont .= '</div></div></div>';
        $respuesta = $rowaux['RESP_NOM'];
      }
      $cont .= inspecciones_registro("",$pregunta,$respuesta,1);
      
      if ($row['IR_IMG'] == '') {
        $imagen_preliminar = "";
      } else {
        $imagen_preliminar = "../inspecciones_imagenes/" . $row['IR_IMG'];                
        //if (file_exists("../inspecciones_imagenes/".$row['IR_IMG'])){         
        //  $cont .= '<img src="../inspecciones_imagenes/'.$row['IR_IMG'].'" width="340" height="300">';
        //} 
        $cont .= '<img src="'.$imagen_preliminar.'"/>';
      }
            
    $i++;
    }
    return $cont;
    //FIN PREVENCIÓN
  }

//echo $content;
//exit(); 

function PDF_generar($contenido,$nombre_archivo) {
  $dompdf = new Dompdf();
  $dompdf->loadHtml($contenido);
  $dompdf->setPaper('A4', 'portrait'); // (Opcional) Configurar papel y orientación
  $dompdf->render(); // Generar el PDF desde contenido HTML
  $pdf = $dompdf->output(); // Obtener el PDF generado

  //Enviar al navegador para guardar manualmente *********************************
  //$dompdf->stream("insp_".$insp_id.".pdf"); 
  //******************************************************************************

  //Guardarlo directamente en el servidor ****************************************
  file_put_contents( "../inspecciones_pdf/".$nombre_archivo.".pdf", $pdf);
  // Una vez lo guardes en local lo puedes subir o enviar a un ftp.
  //******************************************************************************
  }

?>
<script type="text/javascript">
window.location.assign('inspecciones.php');
</script>
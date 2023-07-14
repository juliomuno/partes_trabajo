 
<?php
  include "../comun/funciones.php";
  include "../comun/db_con.php";
  include "fpdf/fpdf.php";


  session_start();

  if (!isset($_SESSION['GLB_USR_ID'])) 
  {
    php_redirect('../index.php');
  }
           
  $op = $_REQUEST['op'];
  $id = $_REQUEST['id'];
  $formularios = array();
  $voperarios = array();
  $vsubc = array();
  $vope_subc = array();

  if ($op != 'C') 
  {
    $sentencia = DB_CONSULTA("SELECT * FROM INSP WHERE INSP_ID=" . $id);
    if (mysql_num_rows($sentencia) == 1) 
    {
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
      $sentencia = DB_CONSULTA("SELECT * FROM INSP_OPE WHERE IO_INSP=" . $id);      
      
      while ($row = mysql_fetch_assoc($sentencia)) 
      {
        $voperarios[] = $row['IO_OPE'];
      }
      $sentencia = DB_CONSULTA("SELECT * FROM INSP_OPE_SUBC WHERE IOS_INSP=" . $id);
      
      while ($row = mysql_fetch_assoc($sentencia)) 
      {
        $vope_subc[] = $row['IOS_OPE'];
        $vsubc[] = $row['IOS_SUBC'];
      }
      $sentencia = DB_CONSULTA("SELECT DISTINCT IR_FRM FROM INSP_RESP WHERE IR_INSP=" . $id);
      
      while ($row = mysql_fetch_assoc($sentencia)) 
      {
        $formularios[] = $row['IR_FRM'];
      }
    } 
    else 
    {
      exit;
    }

  } 
  else 
  {
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

//$pdf = new FPDF();
//$pdf->AddPage();
//$pdf->SetFont('Arial','B',16);
//$pdf->Cell(40,10,'¡Hola, Mundo!');
//$pdf->Output();

?>


 
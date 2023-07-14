<?php
  include "comun/funciones.php";
  include "comun/db_con.php";

  $par_id = $_REQUEST['par_id'];
  $par_tip_ave = $_REQUEST['par_tip_ave'];

  if ($par_tip_ave==0) $par_tip_ave=0;

  $sentencia_tra = DB_CONSULTA("SELECT * FROM PAR_TRA WHERE PT_PAR=" . $par_id);
  $par_tra = array();
  while ($row = mysql_fetch_assoc($sentencia_tra)) {
    $par_tra[] = $row['PT_TRA'];
  }

  echo DB_LIST_CHECK("LIST_TRABAJOS_AVERIAS","Codigo","Nombre","Tipo=" . $par_tip_ave,"Nombre","chk_tip_tra[]","", $par_tra);
?>
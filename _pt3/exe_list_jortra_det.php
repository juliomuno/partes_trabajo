<?php

if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	
	$respuesta = new stdClass();
	
	include "comun/funciones.php";
	include "comun/db_con.php";

	session_start();

	if (!isset($_SESSION['GLB_USR_ID'])) {
    	php_redirect('index.php');
    }

	$tip = $_REQUEST['txt_tipo'];
	$ini = $_REQUEST['txt_hor_ini'];
	$fin = $_REQUEST['txt_hor_fin'];
	$uj_id = $_REQUEST['txt_uj_id'];
	$fini = $_REQUEST['txt_hor_fini_ori'];
	$ffin = $_REQUEST['txt_hor_ffin_ori'];
	
	$respuesta->error = false;
	if (!$respuesta->error) {
		
		DB_EJECUTA("BEGIN"); // Comienza transacción

		$lid=DB_ULTIMO ("MISC","MSC_ID","");

		$sSQL = "INSERT INTO MISC (MSC_ID, MSC_CLV, MSC_VAL1, MSC_VAL2, MSC_FEC1";
		if ($fin<>""){
			$sSQL .= ", MSC_FEC2";
		}
		$sSQL .= ") ";
		$sSQL .= "VALUES (" . $lid . ", 'SOL_MOD_" . $tip . "', " . $uj_id . ", " . $_SESSION['GLB_USR_ID'] . ", '" . $fini . " " . $ini . ":00'";
		if ($fin<>""){
			$sSQL .= ", '" . $ffin . " " . $fin . ":00'";
		}
		$sSQL .= ")";
		$resultado = DB_EJECUTA($sSQL);
		if (!$resultado) {
			$respuesta->error = true;
		    $respuesta->mensaje = "Error al guardar el parte de trabajo. " . $sSQL;
		}
	}

	if (!$respuesta->error) {
		DB_EJECUTA("COMMIT");
		$respuesta->tipo_mensaje_alerta = "success";
		$respuesta->mensaje = "El registro se a&ntilde;adi&oacute; correctamente.";
	} else {
		DB_EJECUTA("ROLLBACK");
		$respuesta->tipo_mensaje_alerta = "danger";
	}
	$respuesta->onclick = "javascript: window.location.assign('list_jortra.php');";
	echo json_encode($respuesta);
} else {
	throw new Exception("Error Processing Request", 1);   
}

?>
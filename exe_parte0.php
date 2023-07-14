<?php

if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	
	$respuesta = new stdClass();
	
	include "comun/funciones.php";
	include "comun/db_con.php";
	
	session_start();
	
	if (!isset($_SESSION['GLB_USR_ID'])) {
    	php_redirect('index.php');
    }
    
	$op = $_REQUEST['op'];
	$num_trabajadores = 8;
	
	if ($op == "C") {
		$par_id = date('Y') . substr("00000" . DB_ULTIMO("PAR","PAR_ID", "LEFT(PAR_ID,4)=" . date('Y')), -5, 5);
	} else {
		$par_id = $_REQUEST['id'];
	}
	
	$respuesta->error = false;
	
	$par_tip = $_REQUEST['par_tip'];

	$par_fec = STR_formato_cadena($_REQUEST['txt_fec']);
	$par_fec_rea = STR_formato_cadena(date("Y-m-d H:i:s"));
	$fecha_dia_anterior = STR_formato_cadena(fecha_dia_anterior(date("Y-m-d")));
	$par_ninc = STR_formato_cadena($_REQUEST['txt_ninc']);
	$par_fin = $_REQUEST['chk_fin'];
	$par_uot = $_REQUEST['cmb_uot'];
	$par_dir = STR_formato_cadena($_REQUEST['txt_dir']);
	$par_pob = $_REQUEST['cmb_pob'];
	$par_rea = STR_formato_cadena($_REQUEST['txt_rea']);
	$par_obs = STR_formato_cadena($_REQUEST['txt_obs']);
	$par_veh = $_REQUEST['cmb_veh'];
	$par_km = $_REQUEST['txt_km'];
	$par_km_hor = $_REQUEST['txt_km_hor'];
	$par_tip_ave = $_REQUEST['cmb_tip_ave'];	

	$respuesta->error = true;
	$respuesta->mensaje = "";
	$i = 0;
	foreach($_POST as $nombre_campo => $valor){ 
   		//$asignacion = "\$" . $nombre_campo . "='" . $valor . "';"; 
   		if (is_array($valor)) {
   			$i += count($valor);
   		} else {
   			$i++;
   		}
	}

	$respuesta->mensaje = "Valor: " . $i;

	//$respuesta->mensaje = "Fecna: " . $par_fec . " Vehículo: " . $par_veh;

	if (!$respuesta->error) {
		//DB_EJECUTA("COMMIT");
		$respuesta->tipo_mensaje_alerta = "success";
		$respuesta->mensaje = "El registro se a&ntilde;adi&oacute; correctamente. ID=" . $par_id;
		$respuesta->onclick = "javascript: window.location.assign('principal.php');";
	} else {
		//DB_EJECUTA("ROLLBACK");
		$respuesta->tipo_mensaje_alerta = "danger";
		$respuesta->onclick = "javascript: habilitar_capa('nuevoparte1');";
	}
	echo json_encode($respuesta);
} else {
	throw new Exception("Error Processing Request", 1);   
}

?>
<?php

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	
	$respuesta = new stdClass();
	
	include "comun/funciones.php";
	include "comun/db_con.php";

	session_start();

	if (!isset($_SESSION['GLB_USR_ID'])) {
    	php_redirect('index.php');
    }

	$op = $_REQUEST['op'];
	$respuesta->error = false;

	$pd_ope = $_REQUEST['txt_ope'];
	$pd_cond = $_REQUEST["txt_ope_sel"];
	$par_tip = $_REQUEST['par_tip'];
	$pd_hdes = $_REQUEST['txt_des_hor'];
	
	$par_fec = STR_formato_cadena($_REQUEST['txt_fec']);
	$par_fec_fin = STR_fechor_esc15("FIN_TRA");
	$fecha_dia_anterior = STR_formato_cadena(fecha_dia_anterior(date("Y-m-d")));
	$par_hini = STR_formato_hora($_REQUEST['txt_hini']);
	$par_hfin = STR_formato_hora($_REQUEST['txt_hfin']);
	$par_ninc = "NULL";
	$par_dir = STR_formato_cadena($_REQUEST['txt_dir']);
	$par_pob = "11130";
	$par_cli_nom = STR_formato_cadena($_REQUEST['txt_cli_nom']);
	$par_veh = "0";
	$pd_hnor = $_REQUEST['txt_hnor_1'];
	$pd_hext = $_REQUEST['txt_hext1'];
	
	if (!$respuesta->error) {
		/*$sSQL = "SELECT UJ_PEE FROM USR_JOR WHERE UJ_USU = " . $_SESSION['GLB_USR_ID'] . " AND UJ_JOR = 0 AND UJ_TIP_STOP = 1 AND UJ_FEC_FIN IS NULL AND UJ_FEC_INI >= ". $fecha_dia_anterior . ";";
		$consulta = DB_CONSULTA($sSQL);

		$num_pi_pee = array();
		
		while ($register = mysql_fetch_array($consulta)) {
			array_push($num_pi_pee, $register[0]);
		}*/

		if ($op == "C") {
			// Actualizar nº de parte y fecha fin en USR_JOR
			if ($pd_hdes == ""){
				$pd_hdes = "NULL";
			} else {
				$pd_hdes = str_replace(",", ".", $pd_hdes);
			}
			$sSQL = "UPDATE USR_JOR SET UJ_FEC_FIN=" . $par_fec_fin . ", UJ_MOD=1, UJ_HNOR=" . str_replace(",", ".", $pd_hnor) . ", UJ_HEXT=" . str_replace(",", ".", $pd_hext) . ", UJ_HDES = " . $pd_hdes . " WHERE UJ_USU=" . $_SESSION['GLB_USR_ID'] . " AND UJ_JOR=0 AND UJ_TIP_STOP=1 AND UJ_FEC_FIN IS NULL AND UJ_FEC_INI>=" . $fecha_dia_anterior;

			$resultado = DB_EJECUTA($sSQL);

			if (!$resultado) {
				$respuesta->error = true;
		      	$respuesta->mensaje = "No se encontró ningún Inicio de Trabajo abierto.";
			}
		}
	}

	/** -------- Para finalizar el trabajo en parte basic ---------- */

	/*if (!$respuesta->error) {
		$imagen = "SELECT PI_IMG FROM PAR_IMG_TEMP WHERE PI_PEE= " . $num_pi_pee[0] . ";";
		$res = DB_CONSULTA($imagen);

		$imagenes = array();

		while ($registro = mysql_fetch_array($res)) {
		  array_push($imagenes, $registro[0]);
		}
		  
		// Procedemos a añadir la/s imágen/es
		for ($i = 0; $i < count($imagenes); $i++) { 
			// campo PI_INI no existente en BD
			$sSQL = "INSERT INTO PAR_IMG (PI_IMG, PI_PAR) VALUES(" .STR_formato_cadena($imagenes[$i]). "," .$par_id. ");";
			$resultado = DB_EJECUTA($sSQL);

			if (!$resultado) {
				$respuesta->error = true;
				$respuesta->mensaje = "Error al añadir imágen a la tabla PAR_IMG. " . $sSQL;
			}
		}

		// Eliminamos las imágenes de la tabla PAR_IMG_TEMP
		for ($i = 0; $i < count($imagenes); $i++) { 
			$sSQL = "DELETE FROM PAR_IMG_TEMP WHERE PI_PEE=" . $num_pi_pee[0] . ";";
			$resultado = DB_EJECUTA($sSQL);
			
			if (!$resultado) {
				$respuesta->error = true;
				$respuesta->mensaje = "Error al eliminar imágen de la tabla PAR_IMG_TEMP." . $sSQL;
			}
		}
	}*/

	if (!$respuesta->error) {
		DB_EJECUTA("COMMIT");
		$respuesta->tipo_mensaje_alerta = "success";
		$respuesta->mensaje = "El registro se a&ntilde;adi&oacute; correctamente.";
		$respuesta->onclick = "javascript: window.location.assign('principal.php');";
	} else {
		DB_EJECUTA("ROLLBACK");
		$respuesta->tipo_mensaje_alerta = "danger";
		$respuesta->onclick = "javascript: habilitar_capa('nuevoparte1');";
	}

	echo json_encode($respuesta);
} else {
	throw new Exception("Error Processing Request", 1);
}

?>
<?php

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	
	$respuesta = new stdClass();
	
	include "comun/funciones.php";
	include "comun/db_con.php";

	$op = $_REQUEST['op'];
	if ($op == "C") {
	$par_id = date('Y') . substr("00000" . DB_ULTIMO("PAR","PAR_ID", "LEFT(PAR_ID,4)=" . date('Y')), -5, 5);
	} else {
		$par_id = $_REQUEST['id'];
	}

	$respuesta->error = false;

	$pd_ope = $_REQUEST['cmb_ope'];

	$par_tip = $_REQUEST['par_tip'];
	$par_fec_rea = STR_formato_cadena(date("Y-m-d H:i:s"));
	$par_fec = STR_formato_cadena($_REQUEST['txt_fec']);
	$par_hini = STR_formato_hora($_REQUEST['txt_hini']);
	$par_hfin = STR_formato_hora($_REQUEST['txt_hfin']);
	$par_ninc = STR_formato_cadena($_REQUEST['txt_ninc']);
	$par_dir = STR_formato_cadena($_REQUEST['txt_dir']);
	$par_pob = $_REQUEST['cmb_pob'];
	$par_rea = STR_formato_cadena($_REQUEST['txt_rea']);
	$par_obs = STR_formato_cadena($_REQUEST['txt_obs']);
	$par_veh = $_REQUEST['cmb_veh'];
	$par_km = $_REQUEST['txt_km'];
	$par_km_hor = STR_formato_hora($_REQUEST['txt_km_hor']);

	
	// En este tipo de parte sólo se indican las horas de un operario
	$pd_hnor = restar_horas($par_hini, $par_hfin);
	$pd_hext = "NULL";
	
	if ($par_km == '') {
		$par_km = "NULL";
	}


	// Comprobación de las imágenes
	// Imágenes
	for($i=1; $i<=4; $i++) {
		$campo_file = 'txt_img' . $i;
	
		$nombre_archivo = $_FILES[$campo_file]['name'];
		$tipo_archivo = $_FILES[$campo_file]['type'];
		$tamano_archivo = $_FILES[$campo_file]['size'];
		
		if ($nombre_archivo != '') {
			//compruebo si las características del archivo son las que deseo 
			if (!((strpos($tipo_archivo, "gif") || strpos($tipo_archivo, "jpeg")) && ($tamano_archivo < 2000000))) { 
				$respuesta->error = true;
				$respuesta->mensaje = "La extensión o el tamaño de los archivos no es correcta. <br><br><table><tr><td><li>Se permiten archivos .gif o .jpg<br><li>Se permiten archivos de 1 MB máximo.</td></tr></table>"; 
			}
		}
	}

	if (!$respuesta->error) {
		if ($op == "C") {
			$sSQL = "INSERT INTO PAR (PAR_ID, PAR_TIP, PAR_FEC_REA, PAR_FEC, PAR_HINI, PAR_HFIN, PAR_NINC, PAR_DIR, PAR_POB, PAR_REA, PAR_OBS, PAR_VEH, PAR_KM, PAR_KM_HOR, PAR_MOD) ";
			$sSQL .= "VALUES (" . $par_id . ", " . $par_tip . ", " . $par_fec_rea . ", " . $par_fec . ", " . $par_hini . ", " . $par_hfin . ", " . $par_ninc . ", " . $par_dir . ", " . $par_pob . ", " . $par_rea . ", " . $par_obs . ", " . $par_veh . ", " . $par_km . ", " . $par_km_hor . ", 1)";
		} else {
			$sSQL = "UPDATE PAR SET ";
			$sSQL .= "PAR_FEC_REA=" . $par_fec_rea . ",";
			$sSQL .= "PAR_FEC=" . $par_fec . ",";
			$sSQL .= "PAR_HINI=" . $par_hini . ",";
			$sSQL .= "PAR_HFIN=" . $par_hfin . ",";
			$sSQL .= "PAR_NINC=" . $par_ninc . ",";
			$sSQL .= "PAR_DIR=" . $par_dir . ",";
			$sSQL .= "PAR_POB=" . $par_pob . ",";
			$sSQL .= "PAR_REA=" . $par_rea . ",";
			$sSQL .= "PAR_OBS=" . $par_obs . ",";
			$sSQL .= "PAR_VEH=" . $par_veh . ",";
			$sSQL .= "PAR_KM=" . $par_km . ",";
			$sSQL .= "PAR_KM_HOR=" . $par_km_hor . ",";
			$sSQL .= "PAR_MOD=1 ";
			$sSQL .= " WHERE PAR_ID=" . $par_id;
		}

		////mysql_query("SET AUTOCOMMIT=0");
		////mysql_query("START TRANSACTION");

		$resultado = DB_EJECUTA($sSQL);

		if ($op == "M") {
			$sSQL = "DELETE FROM PAR_TRA WHERE PT_PAR=" . $par_id;
			$resultado = DB_EJECUTA($sSQL);
		}

		$tip_tra = $_REQUEST['opt_tip_tra'];
		$sSQL = "INSERT INTO PAR_TRA (PT_PAR, PT_TRA) VALUES ";
		$sSQL .= "(" . $par_id . ", " . $tip_tra . ")";
		$resultado = DB_EJECUTA($sSQL);
		
		if ($op == "M") {
			$sSQL = "DELETE FROM PAR_DET WHERE PD_PAR=" . $par_id;
			$resultado = DB_EJECUTA($sSQL);
		}
		
		// Operarios
		$sSQL = "INSERT INTO PAR_DET (PD_PAR, PD_OPE, PD_NOR, PD_EXT) VALUES ";
		$sSQL .= "(" . $par_id . "," . $pd_ope . "," . $pd_hnor . "," . $pd_hext . ")";
		$resultado = DB_EJECUTA($sSQL);
		
		if ($op == "M") {
			$sSQL = "SELECT * FROM PAR_IMG WHERE PI_PAR=" . $par_id;
			$sentencia = DB_CONSULTA($sSQL);
			$i = 1;
			while ($row = mysql_fetch_assoc($sentencia)) {
				if ($_REQUEST['lbl_img' . $i] != $row['PI_IMG']) {
					$resultado = DB_EJECUTA("DELETE FROM PAR_IMG WHERE PI_ID=" . $row['PI_ID']);
				}
				$i++;
			}
		}
		
		//mysql_query("COMMIT");

		// Imágenes
		for($i=1; $i<=4; $i++) {
			$campo_file = 'txt_img' . $i;				
			$nombre_archivo = $_FILES[$campo_file]['name'];

			if ($nombre_archivo != '') {
				$nuevo_nombre_archivo = uniqid();
				
	   			if (move_uploaded_file($_FILES[$campo_file]['tmp_name'], "partes_imagenes/" . $nuevo_nombre_archivo . ".jpg")){ 
      				$sSQL = "INSERT INTO PAR_IMG (PI_PAR, PI_IMG) VALUES (" . $par_id . "," . STR_formato_cadena($nuevo_nombre_archivo . ".jpg") . ")";
					$resultado = DB_EJECUTA($sSQL);
   				}else{ 
		      		$respuesta->error = true;
		      		$respuesta->mensaje = "Ocurrió algún error al subir el fichero. El parte de guard&oacute; aunque no pudieron guardarse las imágenes.";
   				}
   			}
		}

	}

	if (!$respuesta->error) {
		$respuesta->tipo_mensaje_alerta = "success";
		$respuesta->mensaje = "El registro se a&ntilde;adi&oacute; correctamente. ID=" . $par_id;
		$respuesta->onclick = "javascript: window.location.assign('inicio.php');";
	} else {
		//mysql_query("ROLLBACK");
		$respuesta->tipo_mensaje_alerta = "danger";
		$respuesta->onclick = "javascript: habilitar_capa('nuevoparte1');";
	}

	echo json_encode($respuesta);
} else {
	throw new Exception("Error Processing Request", 1);
}

?>
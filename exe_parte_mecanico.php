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
	if ($op == "C") {
		$par_id = date('Y') . substr("00000" . DB_ULTIMO("PAR","PAR_ID", "LEFT(PAR_ID,4)=" . date('Y')), -5, 5);
	} else {
		$par_id = $_REQUEST['id'];
	}

	$respuesta->error = false;

	$par_tip = $_REQUEST['par_tip'];
	$ope1 = $_REQUEST['cmb_ope1'];
	$pd_cond1 = $_REQUEST["txt_ope_sel1"];
	$ope2 = $_REQUEST['cmb_ope2'];
	$pd_cond2 = $_REQUEST["txt_ope_sel2"];
	$par_fec = STR_formato_cadena($_REQUEST['txt_fec']);
	$par_fec_rea = STR_formato_cadena(date("Y-m-d H:i:s"));
	$fecha_dia_anterior = STR_formato_cadena(fecha_dia_anterior(date("Y-m-d")));
	$par_hini = STR_formato_hora($_REQUEST['txt_hini']);
	$par_hfin = STR_formato_hora($_REQUEST['txt_hfin']);
	$par_tip_mec = $_REQUEST['cmb_tip_mec'];
	$par_rea = STR_formato_cadena($_REQUEST['txt_rea']);
	$par_rea_art = $_REQUEST['cmd_reaart'];
	$par_km_act = STR_formato_numero($_REQUEST['txt_kmact']);
	$par_obs = STR_formato_cadena($_REQUEST['txt_obs']);
	$par_dir = STR_formato_cadena('');
	$par_pob = 11015;
	
	// Por ahora no se indica un nuevo campo en PAR para representar $par_tip_mec
	if ($par_tip_mec == 1) { // Mantenimiento de Vehículos
		$par_veh = $_REQUEST['cmb_veh'];
	} else {
		$par_veh = 'NULL';
	}
	
	// En este tipo de partes sólo se indican las horas una vez
	$pd_hnor = restar_horas($_REQUEST['txt_hini'], $_REQUEST['txt_hfin']);
	$pd_hext = "NULL";
	
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
			$sSQL = "INSERT INTO PAR (PAR_ID, PAR_TIP, PAR_FEC, PAR_FEC_REA, PAR_HINI, PAR_HFIN, PAR_DIR, PAR_POB, PAR_REA, PAR_OBS, PAR_VEH, PAR_TIP_MEC, PAR_MOD) ";
			$sSQL .= "VALUES (" . $par_id . ", " . $par_tip . ", " . $par_fec . ", " . $par_fec_rea . ", " . $par_hini . ", " . $par_hfin . ", " . $par_dir . ", " . $par_pob . ", " . $par_rea . ", " . $par_obs . ", " . $par_veh . "," . $par_tip_mec . ",1)";
		} else {
			$sSQL = "UPDATE PAR SET ";
			$sSQL .= "PAR_FEC=" . $par_fec . ",";
			$sSQL .= "PAR_FEC_REA=" . $par_fec_rea . ",";
			$sSQL .= "PAR_HINI=" . $par_hini . ",";
			$sSQL .= "PAR_HFIN=" . $par_hfin . ",";
			$sSQL .= "PAR_DIR=" . $par_dir . ",";
			$sSQL .= "PAR_POB=" . $par_pob . ",";
			$sSQL .= "PAR_REA=" . $par_rea . ",";
			$sSQL .= "PAR_OBS=" . $par_obs . ",";
			$sSQL .= "PAR_VEH=" . $par_veh . ",";
			$sSQL .= "PAR_TIP_MEC=" . $par_tip_mec . ",";
			$sSQL .= "PAR_MOD=1 ";
			$sSQL .= " WHERE PAR_ID=" . $par_id;
		}

		DB_EJECUTA("BEGIN"); // Comienza transacción

		$resultado = DB_EJECUTA($sSQL);

		if (!$resultado) {
			$respuesta->error = true;
		    $respuesta->mensaje = "Error al guardar el parte de trabajo. Tabla PAR." . $sSQL;
		}

		if ($op == "M") {
			$sSQL = "DELETE FROM PAR_DET WHERE PD_PAR=" . $par_id;
			$resultado = DB_EJECUTA($sSQL);

			if (!$resultado) {
				$respuesta->error = true;
		    	$respuesta->mensaje = "Error al guardar el parte de trabajo. Eliminar PAR_DET." . $sSQL;
			}		
		}

		if (!$respuesta->error) {
			// Operarios
			$pos_aux=strpos($pd_cond1,GLB_SUBF_CONDUCTOR);
			if ($pos_aux!==false) {
				$pd_cond1 = substr($pd_cond1,$pos_aux+17);
			} else {
				$pd_cond1 = "0";
			}
			$sSQL = "INSERT INTO PAR_DET (PD_PAR, PD_OPE, PD_NOR, PD_EXT, PD_CON) VALUES ";
			$sSQL .= "(" . $par_id . "," . $ope1 . "," . $pd_hnor . "," . $pd_hext . "," . $pd_cond1 . ")";
			$resultado = DB_EJECUTA($sSQL);
			
			if (!$resultado) {
				$respuesta->error = true;
			    $respuesta->mensaje = "Error al guardar el parte de trabajo. Tabla PAR_DET." . $sSQL;
			}

			if ($ope2 != '') {
				$pos_aux=strpos($pd_cond2,GLB_SUBF_CONDUCTOR);
				if ($pos_aux!==false) {
					$pd_cond2 = substr($pd_cond2,$pos_aux+17);
				} else {
					$pd_cond2 = "0";
				}
				$sSQL = "INSERT INTO PAR_DET (PD_PAR, PD_OPE, PD_NOR, PD_EXT, PD_CON) VALUES ";
				$sSQL .= "(" . $par_id . "," . $ope2 . "," . $pd_hnor . "," . $pd_hext . "," . $pd_cond2 . ")";
				$resultado = DB_EJECUTA($sSQL);

				if (!$resultado) {
					$respuesta->error = true;
			    	$respuesta->mensaje = "Error al guardar el parte de trabajo. Tabla PAR_DET." . $sSQL;
				}
			}
		}

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

		if (!$respuesta->error) {
			// Imágenes
			for($i=1; $i<=4; $i++) {
				$campo_file = 'txt_img' . $i;				
				$nombre_archivo = $_FILES[$campo_file]['name'];

				if ($nombre_archivo != '') {
					$nuevo_nombre_archivo = uniqid();
					
		   			if (move_uploaded_file($_FILES[$campo_file]['tmp_name'], "partes_imagenes/" . $nuevo_nombre_archivo . ".jpg")){ 
	      				$sSQL = "INSERT INTO PAR_IMG (PI_PAR, PI_IMG) VALUES (" . $par_id . "," . STR_formato_cadena($nuevo_nombre_archivo . ".jpg") . ")";
						$resultado = DB_EJECUTA($sSQL);
						if (!$resultado) {
							$respuesta->error = true;
			    			$respuesta->mensaje = "Error al guardar el parte de trabajo. Tabla PAR_IMG";
			    		}
	   				}else{ 
			      		$respuesta->error = true;
			      		$respuesta->mensaje = "Ocurrió algún error al subir el fichero. El parte de guard&oacute; aunque no pudieron guardarse las imágenes.";
	   				}
	   			}
			}
		}
	}

	if (!$respuesta->error) {
		if ($op == "C") {
			// Actualizar nº de parte y fecha fin en USR_JOR
			$sSQL = "UPDATE USR_JOR SET UJ_PAR=" . $par_id . ", UJ_FEC_FIN=" . $par_fec_rea . ", UJ_MOD=1 WHERE UJ_USU=" . $_SESSION['GLB_USR_ID'] . " AND UJ_JOR=0 AND UJ_FEC_FIN IS NULL AND UJ_FEC_INI>=" . $fecha_dia_anterior;
	
			$resultado = DB_EJECUTA($sSQL);

			if (!$resultado) {
				$respuesta->error = true;
		      	$respuesta->mensaje = "No se encontró ningún Inicio de Trabajo abierto.";
			}
		}
	}

	if (!$respuesta->error) {
		DB_EJECUTA("COMMIT");
		$respuesta->tipo_mensaje_alerta = "success";
		$respuesta->mensaje = "El registro se a&ntilde;adi&oacute; correctamente. ID=" . $par_id;
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
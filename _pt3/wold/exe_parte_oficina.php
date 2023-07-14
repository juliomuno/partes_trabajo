<?php

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	
	$respuesta = new stdClass();
	
	include "../comun/funciones.php";
	include "../comun/db_con.php";

	session_start();

	if (!isset($_SESSION['GLB_USR_ID'])) {
    	php_redirect('../index.php');
    }

	$op = $_REQUEST['op'];
	if ($op == "C") {
		$par_id = date('Y') . substr("00000" . DB_ULTIMO("PAR","PAR_ID", "LEFT(PAR_ID,4)=" . date('Y')), -5, 5);
	} else {
		$par_id = $_REQUEST['id'];
	}

	$respuesta->error = false;

	$pd_ope = $_REQUEST['cmb_ope'];
	$pd_cond = $_REQUEST["txt_ope_sel"];

	$par_tip = $_REQUEST['par_tip'];
	
	$par_fec = STR_formato_cadena($_REQUEST['txt_fec']);
	$par_fec_rea = STR_formato_cadena(date("Y-m-d H:i:s"));
	$fecha_dia_anterior = STR_formato_cadena(fecha_dia_anterior(date("Y-m-d")));
	$par_hini = STR_formato_hora($_REQUEST['txt_hini']);
	$par_hfin = STR_formato_hora($_REQUEST['txt_hfin']);
	//$par_ninc = STR_formato_cadena($_REQUEST['txt_ninc']);
	$par_hnor = STR_formato_cadena($_REQUEST['txt_hnor']);
	$par_ninc = "NULL";
	$par_dir = STR_formato_cadena($_REQUEST['txt_dir']);
	$par_pob = "11130";
	$par_cli_nom = STR_formato_cadena($_REQUEST['txt_cli_nom']);
	$par_rea = STR_formato_cadena($_REQUEST['txt_rea']);
	$par_obs = STR_formato_cadena($_REQUEST['txt_obs']);
	$par_veh = "0";
	$par_km = $_REQUEST['txt_km'];
	$par_km_hor = STR_formato_hora($_REQUEST['txt_km_hor']);
	
	// En este tipo de parte sólo se indican las horas de un operario
	//if ($par_hnor==''){
		$pd_hnor = restar_horas($_REQUEST['txt_hini'], $_REQUEST['txt_hfin']);
	//} else {
	//	$pd_hnor = $par_hnor
	//}

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
			$sSQL = "INSERT INTO PAR (PAR_ID, PAR_TIP, PAR_FEC_REA, PAR_FEC, PAR_HINI, PAR_HFIN, PAR_NINC, PAR_DIR, PAR_POB, PAR_CLI_NOM, PAR_REA, PAR_OBS, PAR_VEH, PAR_KM, PAR_KM_HOR, PAR_MOD) ";
			$sSQL .= "VALUES (" . $par_id . ", " . $par_tip . ", " . $par_fec_rea . ", " . $par_fec . ", " . $par_hini . ", " . $par_hfin . ", NULL, " . $par_dir . ", 11015, " . $par_cli_nom . ", " . $par_rea . ", " . $par_obs . ", " . "0" . ", " . $par_km . ", " . $par_km_hor . ", 1)";
		} else {
			$sSQL = "UPDATE PAR SET ";
			$sSQL .= "PAR_FEC=" . $par_fec . ",";
			$sSQL .= "PAR_FEC_REA=" . $par_fec_rea . ",";
			$sSQL .= "PAR_HINI=" . $par_hini . ",";
			$sSQL .= "PAR_HFIN=" . $par_hfin . ",";
			$sSQL .= "PAR_NINC=" . $par_ninc . ",";
			$sSQL .= "PAR_DIR=" . $par_dir . ",";
			$sSQL .= "PAR_POB=" . $par_pob . ",";
			$sSQL .= "PAR_CLI_NOM=" . $par_cli_nom . ",";
			$sSQL .= "PAR_REA=" . $par_rea . ",";
			$sSQL .= "PAR_OBS=" . $par_obs . ",";
			$sSQL .= "PAR_VEH=" . $par_veh . ",";
			$sSQL .= "PAR_KM=" . $par_km . ",";
			$sSQL .= "PAR_KM_HOR=" . $par_km_hor . ",";
			$sSQL .= "PAR_MOD=1 ";
			$sSQL .= " WHERE PAR_ID=" . $par_id;
		}
		DB_EJECUTA("BEGIN"); // Comienza transacción

		$resultado = DB_EJECUTA($sSQL);

		if (!$resultado) {
			$respuesta->error = true;
		    $respuesta->mensaje = "Error al guardar el parte de trabajo. Tabla PAR." . $sSQL;
		}

		if (!$respuesta->error) {
			if ($op == "M") {
				$sSQL = "DELETE FROM PAR_MAT WHERE PM_PAR=" . $par_id;
				$resultado = DB_EJECUTA($sSQL);

				if (!$resultado) {
					$respuesta->error = true;
			    	$respuesta->mensaje = "Error al guardar el parte de trabajo. Eliminar PAR_MAT.";
				}
			}
		}

		if (!$respuesta->error) {
			// Material
			$varticulos = $_REQUEST['txt_mat_articulos'];
			$voperarios = $_REQUEST['txt_mat_operarios'];
			$vcantidades = $_REQUEST['txt_mat_cantidades'];
			foreach( $varticulos as $clave => $valor ) {
		  		if ( $vcantidades[$clave] <> '') {
		  			// Procesar artículo
		  			$pm_can = STR_formato_numero($vcantidades[$clave]);
		  			$pm_ope = STR_formato_numero($voperarios[$clave]);

		  			$sSQL = "INSERT INTO PAR_MAT (PM_PAR, PM_OPE, PM_ART, PM_CAN) ";
		  			$sSQL .= " VALUES (".  $par_id . ", " . $pm_ope . ", " . $varticulos[$clave] . ", " . $pm_can . ")";
		  			
		  			$resultado = DB_EJECUTA($sSQL);
		  			if (!$resultado) {
						$respuesta->error = true;
			    		$respuesta->mensaje = $respuesta->mensaje . " Error al guardar el parte de trabajo. Tabla PAR_MAT." . $sSQL;
					}
		  		}
			}
		}

		if (!$respuesta->error) {
			if ($op == "M") {
				$sSQL = "DELETE FROM PAR_DET WHERE PD_PAR=" . $par_id;
				$resultado = DB_EJECUTA($sSQL);

				if (!$resultado) {
					$respuesta->error = true;
			    	$respuesta->mensaje = "Error al guardar el parte de trabajo. Eliminar PAR_DET.";
				}		
			}
		}

		if (!$respuesta->error) {
			// Operarios
			$pos_aux=strpos($pd_cond,GLB_SUBF_CONDUCTOR);
			if ($pos_aux!==false) {
				$pd_cond = substr($pd_cond,$pos_aux+17);
			} else {
				$pd_cond = "0";
			}
			$sSQL = "INSERT INTO PAR_DET (PD_PAR, PD_OPE, PD_NOR, PD_EXT, PD_CON) VALUES ";
			//$sSQL .= "(" . $par_id . "," . $pd_ope . "," . $pd_hnor . "," . $pd_hext . ")";
			$sSQL .= "(" . $par_id . "," . $pd_ope . "," . $par_hnor . "," . $pd_hext . "," . $pd_cond . ")";
			$resultado = DB_EJECUTA($sSQL);
			
			if (!$resultado) {
				$respuesta->error = true;
			    $respuesta->mensaje = "Error al guardar el parte de trabajo. Tabla PAR_DET: " . $sSQL;
			}
		}
		
		if (!$respuesta->error) {
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
		}

		if (!$respuesta->error) {
			// Imágenes
			for($i=1; $i<=4; $i++) {
				$campo_file = 'txt_img' . $i;				
				$nombre_archivo = $_FILES[$campo_file]['name'];

				if ($nombre_archivo != '') {
					$nuevo_nombre_archivo = uniqid();
					
		   			if (move_uploaded_file($_FILES[$campo_file]['tmp_name'], "../partes_imagenes/" . $nuevo_nombre_archivo . ".jpg")){ 
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
			// crea el registro de USR_JOR
			$sSQL = "INSERT INTO USR_JOR(UJ_FEC_INI, UJ_FEC_FIN, UJ_PAR, UJ_USU, UJ_JOR, UJ_TIP_STOP, UJ_MOD, UJ_INIGPSLAT, UJ_INIGPSLON, UJ_INIDIR) ";
			$sSQL .= "VALUES (" . $par_fec_rea . ", " . $par_fec_rea . ", " . $par_id . ", " . $_SESSION['GLB_USR_ID'] . ", 0, " . GLB_PARADA_TRABAJO . ", 1, '', '', '')";
			// Actualizar nº de parte y fecha fin en USR_JOR
			//$sSQL = "UPDATE USR_JOR SET UJ_PAR=" . $par_id . ", UJ_FEC_FIN=" . $par_fec_rea . ", UJ_MOD=1 WHERE UJ_USU=" . $_SESSION['GLB_USR_ID'] . " AND UJ_JOR=0 AND UJ_FEC_FIN IS NULL AND UJ_FEC_INI>=" . $fecha_dia_anterior;

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
		$respuesta->onclick = "javascript: window.location.assign('../principal.php');";
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
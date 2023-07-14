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

	$ope1 = $_REQUEST['cmb_ope1'];
	$pd_hnor1 = $_REQUEST['txt_hnor1'];
	$pd_hext1 = $_REQUEST['txt_hext1'];
	$pd_cond1 = $_REQUEST["txt_ope_sel1"];

	$ope2 = $_REQUEST['cmb_ope2'];
	$pd_hnor2 = $_REQUEST['txt_hnor2'];
	$pd_hext2 = $_REQUEST['txt_hext2'];
	$pd_cond2 = $_REQUEST["txt_ope_sel2"];

	$ope3 = $_REQUEST['cmb_ope3'];
	$pd_hnor3 = $_REQUEST['txt_hnor3'];
	$pd_hext3 = $_REQUEST['txt_hext3'];
	$pd_cond3 = $_REQUEST["txt_ope_sel3"];

	$ope4 = $_REQUEST['cmb_ope4'];
	$pd_hnor4 = $_REQUEST['txt_hnor4'];
	$pd_hext4 = $_REQUEST['txt_hext4'];
	$pd_cond4 = $_REQUEST["txt_ope_sel4"];

	$ope5 = $_REQUEST['cmb_ope5'];
	$pd_hnor5 = $_REQUEST['txt_hnor5'];
	$pd_hext5 = $_REQUEST['txt_hext5'];
	$pd_cond5 = $_REQUEST["txt_ope_sel5"];

	$par_tip = $_REQUEST['par_tip'];
	$par_fec_rea = STR_formato_cadena(date("Y-m-d H:i:s"));
	$fecha_dia_anterior = STR_formato_cadena(fecha_dia_anterior(date("Y-m-d")));
	$par_fec = STR_formato_cadena($_REQUEST['txt_fec']);
	$par_rea = STR_formato_cadena($_REQUEST['txt_rea']);
	$par_obs = STR_formato_cadena($_REQUEST['txt_obs']);
	$par_veh = $_REQUEST['cmb_veh'];
	$par_km = $_REQUEST['txt_km'];
	$par_dir = STR_formato_cadena($_REQUEST['txt_dir']);
	$par_fin = $_REQUEST['chk_fin'];

	// CM y Punto
	$par_cm = $_REQUEST['txt_cm'];
	
	$par_pun = $_REQUEST['txt_pun'];
	if ($par_pun == '') {
		$par_pun = "NULL";
	}
	
	// Indicar estos valores explícitamente
	$par_km_hor = 0; //$_REQUEST['txt_km_hor'];
	$par_pob = 11015; // Chiclana

	// Comprobación de las imágenes
	// Imágenes
	for($i=1; $i<=10; $i++) {
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
			$sSQL = "INSERT INTO PAR (PAR_ID, PAR_TIP, PAR_FEC_REA, PAR_FEC, PAR_FIN, PAR_DIR, PAR_POB, PAR_REA, PAR_OBS, PAR_VEH, PAR_KM, PAR_KM_HOR, PAR_CM, PAR_PUN, PAR_MOD) ";
			$sSQL .= "VALUES (" . $par_id . ", " . $par_tip . ", " . $par_fec_rea . ", " . $par_fec . ", " . $par_fin . ", " . $par_dir . ", " . $par_pob . ", " . $par_rea . ", " . $par_obs . ", " . $par_veh . ", " . $par_km . ", " . $par_km_hor . ", " . $par_cm . ", " . $par_pun . ", 1)";
		} else {
			$sSQL = "UPDATE PAR SET ";
			$sSQL .= "PAR_FEC=" . $par_fec . ",";
			$sSQL .= "PAR_FEC_REA=" . $par_fec_rea . ",";
			$sSQL .= "PAR_FIN=" . $par_fin . ",";
			$sSQL .= "PAR_DIR=" . $par_dir . ",";
			$sSQL .= "PAR_POB=" . $par_pob . ",";
			$sSQL .= "PAR_REA=" . $par_rea . ",";
			$sSQL .= "PAR_OBS=" . $par_obs . ",";
			$sSQL .= "PAR_VEH=" . $par_veh . ",";
			$sSQL .= "PAR_KM=" . $par_km . ",";
			$sSQL .= "PAR_KM_HOR=" . $par_km_hor . ",";
			$sSQL .= "PAR_CM=" . $par_cm . ",";
			$sSQL .= "PAR_PUN=" . $par_pun . ",";
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
			$sSQL = "DELETE FROM PAR_ART WHERE PA_PAR=" . $par_id;
			$resultado = DB_EJECUTA($sSQL);

			if (!$resultado) {
				$respuesta->error = true;
		    	$respuesta->mensaje = "Error al guardar el parte de trabajo. Eliminar PAR_ART.";
			}
		}

		// Partidas Ejecutadas (Producción)
		$varticulos = $_REQUEST['txt_articulos'];
		$vcantidades = $_REQUEST['txt_cantidades'];

		foreach( $varticulos as $clave => $valor ) {
	  		if ( $vcantidades[$clave] <> '') {
	  			// Procesar artículo
	  			$pd_can = STR_formato_numero($vcantidades[$clave]);
	  			
	  			$sSQL = "INSERT INTO PAR_ART (PA_PAR, PA_ART, PA_CAN, PA_HOR) ";
	  			$sSQL .= " VALUES (".  $par_id . "," . $varticulos[$clave] . ", " . $pd_can . ",NULL)";
	  			$resultado = DB_EJECUTA($sSQL);

	  			if (!$resultado) {
					$respuesta->error = true;
		    		$respuesta->mensaje = "Error al guardar el parte de trabajo. Tabla PAR_ART" . $sSQL;
				}		
	  		}
		}

		if ($op == "M") {
			$sSQL = "DELETE FROM PAR_DET WHERE PD_PAR=" . $par_id;
			$resultado = DB_EJECUTA($sSQL);

			if (!$resultado) {
				$respuesta->error = true;
		    	$respuesta->mensaje = "Error al guardar el parte de trabajo. Eliminar PAR_DET.";
			}
		}

		// Operarios
		$pos_aux=strpos($pd_cond1,GLB_SUBF_CONDUCTOR);
		if ($pos_aux!==false) {
			$pd_cond1 = substr($pd_cond1,$pos_aux+17);
		} else {
			$pd_cond1 = "0";
		}
		$sSQL = "INSERT INTO PAR_DET (PD_PAR, PD_OPE, PD_NOR, PD_EXT, PD_CON) VALUES ";
		if ($pd_hnor1 == "") {
			$pd_hnor1 = "NULL";
		} else {
			$pd_hnor1 = str_replace(",", ".", $pd_hnor1);
		}
		if ($pd_hext1 == "") {
			$pd_hext1 = "NULL";
		} else {
			$pd_hext1 = str_replace(",", ".", $pd_hext1);
		}
		$sSQL .= "(" . $par_id . "," . $ope1 . "," . $pd_hnor1 . "," . $pd_hext1 . "," . $pd_cond1 . ")";
		$resultado = DB_EJECUTA($sSQL);
		
		if (!$resultado) {
			$respuesta->error = true;
		    $respuesta->mensaje = "Error al guardar el parte de trabajo. Tabla PAR_DET";
		}

		if ($ope2 != "") {
			$pos_aux=strpos($pd_cond2,GLB_SUBF_CONDUCTOR);
			if ($pos_aux!==false) {
				$pd_cond2 = substr($pd_cond2,$pos_aux+17);
			} else {
				$pd_cond2 = "0";
			}
			$sSQL = "INSERT INTO PAR_DET (PD_PAR, PD_OPE, PD_NOR, PD_EXT, PD_CON) VALUES ";
			if ($pd_hnor2 == "") {
				$pd_hnor2 = "NULL";
			} else {
				$pd_hnor2 = str_replace(",", ".", $pd_hnor2);
			}
			if ($pd_hext2 == "") {
				$pd_hext2 = "NULL";
			} else {
				$pd_hext2 = str_replace(",", ".", $pd_hext2);
			}
			$sSQL .= "(" . $par_id . "," . $ope2 . "," . $pd_hnor2 . "," . $pd_hext2 . "," . $pd_cond2 . ")";
			$resultado = DB_EJECUTA($sSQL);

			if (!$resultado) {
				$respuesta->error = true;
			    $respuesta->mensaje = "Error al guardar el parte de trabajo. Tabla PAR_DET Operario2";
			}
		}

		if ($ope3 != "") {
			$pos_aux=strpos($pd_cond3,GLB_SUBF_CONDUCTOR);
			if ($pos_aux!==false) {
				$pd_cond3 = substr($pd_cond3,$pos_aux+17);
			} else {
				$pd_cond3 = "0";
			}
			$sSQL = "INSERT INTO PAR_DET (PD_PAR, PD_OPE, PD_NOR, PD_EXT, PD_CON) VALUES ";
			if ($pd_hnor3 == "") {
				$pd_hnor3 = "NULL";
			} else {
				$pd_hnor3 = str_replace(",", ".", $pd_hnor3);
			}
			if ($pd_hext3 == "") {
				$pd_hext3 = "NULL";
			} else {
				$pd_hext3 = str_replace(",", ".", $pd_hext3);
			}
			$sSQL .= "(" . $par_id . "," . $ope3 . "," . $pd_hnor3 . "," . $pd_hext3 . "," . $pd_cond3 . ")";
			$resultado = DB_EJECUTA($sSQL);

			if (!$resultado) {
				$respuesta->error = true;
			    $respuesta->mensaje = "Error al guardar el parte de trabajo. Tabla PAR_DET Operario3";
			}
		}

		if ($ope4 != "") {
			$pos_aux=strpos($pd_cond4,GLB_SUBF_CONDUCTOR);
			if ($pos_aux!==false) {
				$pd_cond4 = substr($pd_cond4,$pos_aux+17);
			} else {
				$pd_cond4 = "0";
			}
			$sSQL = "INSERT INTO PAR_DET (PD_PAR, PD_OPE, PD_NOR, PD_EXT, PD_CON) VALUES ";
			if ($pd_hnor4 == "") {
				$pd_hnor4 = "NULL";
			} else {
				$pd_hnor4 = str_replace(",", ".", $pd_hnor4);
			}
			if ($pd_hext4 == "") {
				$pd_hext4 = "NULL";
			} else {
				$pd_hext4 = str_replace(",", ".", $pd_hext4);
			}
			$sSQL .= "(" . $par_id . "," . $ope4 . "," . $pd_hnor4 . "," . $pd_hext4 . "," . $pd_cond4 . ")";
			$resultado = DB_EJECUTA($sSQL);

			if (!$resultado) {
				$respuesta->error = true;
			    $respuesta->mensaje = "Error al guardar el parte de trabajo. Tabla PAR_DET Operario4";
			}
		}

		if ($ope5 != "") {
			$pos_aux=strpos($pd_cond5,GLB_SUBF_CONDUCTOR);
			if ($pos_aux!==false) {
				$pd_cond5 = substr($pd_cond5,$pos_aux+17);
			} else {
				$pd_cond5 = "0";
			}
			$sSQL = "INSERT INTO PAR_DET (PD_PAR, PD_OPE, PD_NOR, PD_EXT, PD_CON) VALUES ";
			if ($pd_hnor5 == "") {
				$pd_hnor5 = "NULL";
			} else {
				$pd_hnor5 = str_replace(",", ".", $pd_hnor5);
			}
			if ($pd_hext5 == "") {
				$pd_hext5 = "NULL";
			} else {
				$pd_hext5 = str_replace(",", ".", $pd_hext5);
			}
			$sSQL .= "(" . $par_id . "," . $ope5 . "," . $pd_hnor5 . "," . $pd_hext5 . "," . $pd_cond5 . ")";
			$resultado = DB_EJECUTA($sSQL);

			if (!$resultado) {
				$respuesta->error = true;
			    $respuesta->mensaje = "Error al guardar el parte de trabajo. Tabla PAR_DET Operario5";
			}
		}
		

		if ($op == "M") {
			$sSQL = "DELETE FROM PAR_MAT WHERE PM_PAR=" . $par_id;
			$resultado = DB_EJECUTA($sSQL);

			if (!$resultado) {
				$respuesta->error = true;
		    	$respuesta->mensaje = "Error al guardar el parte de trabajo. Eliminar PAR_MAT.";
			}
		}

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
			for($i=1; $i<=10; $i++) {
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
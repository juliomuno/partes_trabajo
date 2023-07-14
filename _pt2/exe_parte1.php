<?php

if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	
	$respuesta = new stdClass();
	
	include "comun/funciones.php";
	include "comun/db_con.php";

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
	
	// Comprobación de las imágenes
	// Imágenes
	for($i=1; $i<=4; $i++) {
		$campo_file = 'txt_img' . $i;
	
		$nombre_archivo = $_FILES[$campo_file]['name'];
		$tipo_archivo = $_FILES[$campo_file]['type'];
		$tamano_archivo = $_FILES[$campo_file]['size'];
		
		if ($nombre_archivo != '') {
			//Compruebo si las características del archivo son las que deseo 
			if (!(strpos($tipo_archivo, "gif") || strpos($tipo_archivo, "jpeg") || strpos($tipo_archivo, "jpg"))) {
				$respuesta->error = true;
				$respuesta->mensaje = "La extensión no es correcta. <br><br><table><tr><td><li>Se permiten archivos .gif o .jpg</td></tr></table>";
			} else if (!($tamano_archivo < 3000000)) {
				$respuesta->error = true;
				$respuesta->mensaje = "El tamaño de los archivos no es correcto. <br><br><table><tr><td><li>Se permiten archivos de 3 MB máximo.</td></tr></table>";
			}
		}
	}

	if (!$respuesta->error) {
		if ($op == "C") {
			$sSQL = "INSERT INTO PAR (PAR_ID, PAR_TIP, PAR_FEC_REA, PAR_FEC, PAR_NINC, PAR_FIN, PAR_UOT, PAR_DIR, PAR_POB, PAR_REA, PAR_OBS, PAR_TIP_AVE, PAR_VEH, PAR_KM, PAR_KM_HOR, PAR_MOD) ";
			$sSQL .= "VALUES (" . $par_id . ", " . $par_tip . ", " . $par_fec_rea . ", " . $par_fec . ", " . $par_ninc . ", " . $par_fin . ", " . $par_uot . ", " . $par_dir . ", " . $par_pob . ", " . $par_rea . ", " . $par_obs . ", " . $par_tip_ave . ", " . $par_veh . ", " . $par_km . ", " . $par_km_hor . ", 1)";
		
		} else {
			$sSQL = "UPDATE PAR SET ";
			$sSQL .= "PAR_FEC_REA=" . $par_fec_rea . ",";
			$sSQL .= "PAR_FEC=" . $par_fec . ",";
			$sSQL .= "PAR_NINC=" . $par_ninc . ",";
			$sSQL .= "PAR_FIN=" . $par_fin . ",";
			$sSQL .= "PAR_UOT=" . $par_uot . ",";
			$sSQL .= "PAR_DIR=" . $par_dir . ",";
			$sSQL .= "PAR_POB=" . $par_pob . ",";
			$sSQL .= "PAR_REA=" . $par_rea . ",";
			$sSQL .= "PAR_OBS=" . $par_obs . ",";
			$sSQL .= "PAR_TIP_AVE=" . $par_tip_ave . ",";
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
	}
	
	if (!$respuesta->error) {
		if ($op == "M") {
			$sSQL = "DELETE FROM PAR_TRA WHERE PT_PAR=" . $par_id;
			$resultado = DB_EJECUTA($sSQL);

			if (!$resultado) {
				$respuesta->error = true;
		    	$respuesta->mensaje = "Error al guardar el parte de trabajo. Eliminar PAR_TRA.";
			}
		}
	}

	if (!$respuesta->error) {
		$vtip_tra = $_REQUEST['chk_tip_tra'];
		if (isset($vtip_tra)) {
			$sSQL = "INSERT INTO PAR_TRA (PT_PAR, PT_TRA) VALUES ";
			for ($i=0;$i<count($vtip_tra);$i++) {     
				$sSQL .= "(" . $par_id . ", " . $vtip_tra[$i] . "),";
			}
			$sSQL = substr($sSQL, 0, strlen($sSQL)-1);
			$resultado = DB_EJECUTA($sSQL);

			if (!$resultado) {
				$respuesta->error = true;
		    	$respuesta->mensaje = "Error al guardar el parte de trabajo. Tabla PAR_TRA";
			}
		}
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
		if ($op == 'M') {
			$sSQL = "DELETE FROM PAR_DET WHERE PD_PAR=" . $par_id;
			$resultado = DB_EJECUTA($sSQL);

			if (!$resultado) {
				$respuesta->error = true;
				$respuesta->mensaje = "Error al guardar el parte de trabajo. Eliminar PAR_DET.";
			}
		}
	}


	// Operarios
	if (!$respuesta->error) {
		
		//if (!empty($_REQUEST["cmb_ope"]) && is_array($_REQUEST["cmb_ope"]) ) { 
		    
		    //foreach ($_REQUEST["cmb_ope"] as $cmb_ope) { 
		for($i=1;$i<=$num_trabajadores;$i++) {	
	    	if (!$respuesta->error) {
				$pd_ope = $_REQUEST["cmb_ope" . $i];
				$pd_hnor = $_REQUEST["txt_hnor" . $i];
				$pd_hext = $_REQUEST["txt_hext" . $i];
				
				if ($pd_ope != "") {
					$sSQL = "INSERT INTO PAR_DET (PD_PAR, PD_OPE, PD_NOR, PD_EXT) VALUES ";
					if ($pd_hnor == "") {
						$pd_hnor = "NULL";
					} else {
						$pd_hnor = str_replace(",", ".", $pd_hnor);
					}
					if ($pd_hext == "") {
						$pd_hext = "NULL";
					} else {
						$pd_hext = str_replace(",", ".", $pd_hext);
					}
					$sSQL .= "(" . $par_id . "," . $pd_ope . "," . $pd_hnor . "," . $pd_hext . ")";
					$resultado = DB_EJECUTA($sSQL);

					if (!$resultado) {
						$respuesta->error = true;
				    	$respuesta->mensaje = "Error al guardar el parte de trabajo. Tabla PAR_DET";
					}
				}
			}
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

	if (!$respuesta->error) {
		DB_EJECUTA("COMMIT");
		$respuesta->tipo_mensaje_alerta = "success";
		$respuesta->mensaje = "El registro se a&ntilde;adi&oacute; correctamente. ID=" . $par_id;
		$respuesta->onclick = "javascript: window.location.assign('inicio.php');";
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
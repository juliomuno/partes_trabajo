<?php
$img_ctd = 0;

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

	$pd_ope = $_REQUEST['txt_ope'];
	$pd_cond = $_REQUEST["txt_ope_sel1"];
	$pd_hdes = $_REQUEST['txt_des_hor'];
	$par_tip = $_REQUEST['par_tip'];
	
	$par_fec = STR_formato_cadena($_REQUEST['txt_fec']);
	$par_fec_rea = STR_formato_cadena(date("Y-m-d H:i:s"));
	$par_fec_fin = STR_fechor_esc15("FIN_TRA");
	$fecha_dia_anterior = STR_formato_cadena(fecha_dia_anterior(date("Y-m-d")));

	$par_hini = STR_formato_hora($_REQUEST['txt_hini']);
	$par_hfin = STR_formato_hora($_REQUEST['txt_hfin']);
	$par_ninc = STR_formato_cadena($_REQUEST['txt_ninc']);
	$par_dir = STR_formato_cadena($_REQUEST['txt_dir']);
	$par_pob = $_REQUEST['cmb_pob'];
	$par_rea = STR_formato_cadena($_REQUEST['txt_rea']);
	$par_obs = STR_formato_cadena($_REQUEST['txt_obs']);
	$ope_sel = $_REQUEST['txt_pla_ope_sel'];

	// En este tipo de parte sólo se indican las horas de un operario
	//$pd_hnor = restar_horas($_REQUEST['txt_hini'], $_REQUEST['txt_hfin']);
	$pd_hnor = $_REQUEST['txt_hnor_1'];
	$pd_hext = $_REQUEST['txt_hext1'];

	// Comprobación de las imágenes
	// Imágenes
	for($i=1; $i<=$img_ctd; $i++) {
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
			$sSQL .= "VALUES (" . $par_id . ", " . $par_tip . ", " . $par_fec_rea . ", " . $par_fec . ", " . $par_hini . ", " . $par_hfin . ", " . $par_ninc . ", " . $par_dir . ", " . $par_pob . ", " . $par_rea . ", " . $par_obs . ", NULL, NULL, NULL, 1)";
		} else {
			$sSQL = "UPDATE PAR SET ";
			$sSQL .= "PAR_FEC=" . $par_fec . ",";
			$sSQL .= "PAR_FEC_REA=" . $par_fec_rea . ",";
			$sSQL .= "PAR_HINI=" . $par_hini . ",";
			$sSQL .= "PAR_HFIN=" . $par_hfin . ",";
			$sSQL .= "PAR_NINC=" . $par_ninc . ",";
			$sSQL .= "PAR_DIR=" . $par_dir . ",";
			$sSQL .= "PAR_POB=" . $par_pob . ",";
			$sSQL .= "PAR_REA=" . $par_rea . ",";
			$sSQL .= "PAR_OBS=" . $par_obs . ",";
			$sSQL .= "PAR_VEH=NULL,";
			$sSQL .= "PAR_KM=NULL,";
			$sSQL .= "PAR_KM_HOR=NULL,";
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
			$sSQL = "DELETE FROM PAR_TRA WHERE PT_PAR=" . $par_id;
			$resultado = DB_EJECUTA($sSQL);
			if (!$resultado) {
				$respuesta->error = true;
		    	$respuesta->mensaje = "Error al guardar el parte de trabajo. Eliminar PAR_TRA.";
			}
		}

		$tip_tra = $_REQUEST['opt_tip_tra'];
		$sSQL = "INSERT INTO PAR_TRA (PT_PAR, PT_TRA) VALUES ";
		$sSQL .= "(" . $par_id . ", " . $tip_tra . ")";
		$resultado = DB_EJECUTA($sSQL);
		if (!$resultado) {
			$respuesta->error = true;
		    $respuesta->mensaje = "Error al guardar el parte de trabajo. Tabla PAR_TRA.";
		}		

		/*
		if ($op == "M") {
			$sSQL = "DELETE FROM PAR_DET WHERE PD_PAR=" . $par_id;
			$resultado = DB_EJECUTA($sSQL);

			if (!$resultado) {
				$respuesta->error = true;
		    	$respuesta->mensaje = "Error al guardar el parte de trabajo. Eliminar PAR_DET.";
			}		
		}
		*/

		// Operarios
		if ($op == "C"){
			$pos_aux=strpos($pd_cond,GLB_SUBF_CONDUCTOR);
			if ($pos_aux!==false) {$pd_cond = substr($pd_cond,$pos_aux+17);} else {$pd_cond = "0";}
			if ($pd_hdes == "") {$pd_hdes = "NULL";} else {$pd_hdes = str_replace(",", ".", $pd_hdes);}
			$sSQL = "INSERT INTO PAR_DET (PD_PAR, PD_OPE, PD_NOR, PD_EXT, PD_CON, PD_DESP, PD_HINI, PD_HFIN) VALUES ";
			$sSQL .= "(" . $par_id . "," . $pd_ope . "," . $pd_hnor . "," . $pd_hext . "," . $pd_cond . "," . $pd_hdes . "," . $par_hini . "," . $par_hfin . ")";
			$resultado = DB_EJECUTA($sSQL);
			
			if (!$resultado) {
				$respuesta->error = true;
			    $respuesta->mensaje = "Error al guardar el parte de trabajo. Tabla PAR_DET" . $sSQL;
			}
			//marcar al usuario como trabajo sin iniciar
			$sSQL = "UPDATE USR_WEB SET USR_TRA_INI=0 WHERE USR_ID=" . $pd_ope;
			$resultado = DB_EJECUTA($sSQL);
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

		//Como el elemento es un arreglos utilizamos foreach para extraer todos los valores
		foreach($_FILES["txt_img"]['tmp_name'] as $key => $tmp_name){
			//Validamos que el archivo exista
			if($_FILES["txt_img"]['name'][$key]) {
				$filename = $_FILES["txt_img"]['name'][$key]; //Obtenemos el nombre original del archivo
				$source = $_FILES["txt_img"]['tmp_name'][$key]; //Obtenemos un nombre temporal del archivo
				
				if ($filename != ''){
					$filename = uniqid();

					//Movemos y validamos que el archivo se haya cargado correctamente
					//El primer campo es el origen y el segundo el destino
					if(move_uploaded_file($source, "../partes_imagenes/" . $filename . ".jpg")) {	
						$sSQL = "INSERT INTO PAR_IMG (PI_IMG, PI_PAR, PI_PRL) VALUES (" . STR_formato_cadena($filename . ".jpg") . "," . $par_id . ",0)";
						$resultado = DB_EJECUTA($sSQL);

							if (!$resultado) {
								$respuesta->error = true;
								$respuesta->mensaje = "Error al guardar el parte de trabajo. Tabla PAR_IMG";
							}
					} else { 
						$respuesta->error = true;
						$respuesta->mensaje = "Ocurrió algún error al subir el fichero. El parte de guard&oacute; aunque no pudieron guardarse las imágenes.";
					}
				}
			}
		}
		
		if (!$respuesta->error) {			
			// Imágenes
			for($i=1; $i<=$img_ctd; $i++) {
				$campo_file = 'txt_img' . $i;				
				$nombre_archivo = $_FILES[$campo_file]['name'];

				if ($nombre_archivo != '') {
					$nuevo_nombre_archivo = uniqid();
					
		   			if (move_uploaded_file($_FILES[$campo_file]['tmp_name'], "../partes_imagenes/" . $nuevo_nombre_archivo . ".jpg")){ 
	      				$sSQL = "INSERT INTO PAR_IMG (PI_PAR, PI_IMG, PI_PRL) VALUES (" . $par_id . "," . STR_formato_cadena($nuevo_nombre_archivo . ".jpg") . ",0)";
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
		$sSQL = "SELECT UJ_PEE FROM USR_JOR WHERE UJ_USU = " . $_SESSION['GLB_USR_ID'] . " AND UJ_JOR = 0 AND UJ_TIP_STOP = 1 AND UJ_FEC_FIN IS NULL AND UJ_FEC_INI >= ". $fecha_dia_anterior . ";";
		$consulta = DB_CONSULTA($sSQL);

		$num_pi_pee = array();
		
		while ($register = mysql_fetch_array($consulta)) {
			array_push($num_pi_pee, $register[0]);
		}

		if ($op == "C") {
			// Actualizar nº de parte y fecha fin en USR_JOR
			$sSQL = "UPDATE USR_JOR SET UJ_PAR=" . $par_id . ", UJ_FEC_FIN=" . $par_fec_fin . ", UJ_MOD=1 WHERE UJ_USU=" . $_SESSION['GLB_USR_ID'] . " AND UJ_JOR=0 AND UJ_TIP_STOP=1 AND UJ_FEC_FIN IS NULL AND UJ_FEC_INI>=" . $fecha_dia_anterior;

			$resultado = DB_EJECUTA($sSQL);

			if (!$resultado) {
				$respuesta->error = true;
		      	$respuesta->mensaje = "No se encontró ningún Inicio de Trabajo abierto.";
			}

			//generar registro de operarios que inician a la vez
			$ope_sel_reg=explode(",", $ope_sel);
			$max = sizeof($ope_sel_reg);
			for ($i=0;$i<$max;$i++){
				if ($ope_sel_reg[$i]!=""){
					$ope=$_REQUEST['txt_chk_ope'.$ope_sel_reg[$i]];
					$hno=$_REQUEST['txt_chk_hn_'.$ope_sel_reg[$i]];
					$hex=$_REQUEST['txt_chk_he'.$ope_sel_reg[$i]];
					$hde=$_REQUEST['txt_chk_hd'.$ope_sel_reg[$i]];
					if ($hex==""){$hex=0;}
					if ($hno==""){$hno=0;}
					if ($hde==""){$hde=0;}

					$sSQL = "UPDATE USR_JOR SET UJ_FEC_FIN=" . $par_fec_fin . ", UJ_HNOR=" . str_replace(",", ".", $hno) . ", UJ_HEXT=" . str_replace(",", ".", $hex) . ", UJ_HDES=" . str_replace(",", ".", $hde) . ", UJ_MOD=1 WHERE UJ_USU=" . $ope . " AND UJ_JOR=0 AND UJ_TIP_STOP=1 AND UJ_FEC_FIN IS NULL AND UJ_FEC_INI>=" . $fecha_dia_anterior;
					$resultado = DB_EJECUTA($sSQL);
					
					if (!$resultado) {
						$respuesta->error = true;
					    $respuesta->mensaje = "Error al guardar la información." . $sSQL;
					}
					//marcar al usuario como trabajo sin iniciar
					$sSQL = "UPDATE USR_WEB SET USR_TRA_INI=0 WHERE USR_ID=" . $ope;
					$resultado = DB_EJECUTA($sSQL);
				}
			}
		}
	}

	/** -------- Para finalizar el trabajo en parte de peqneq ---------- */
	
	if (!$respuesta->error) {
		$imagen = "SELECT PI_IMG FROM PAR_IMG_TEMP WHERE PI_PEE= " . $num_pi_pee[0] . ";";
		$res = DB_CONSULTA($imagen);

		$imagenes = array();

		while ($registro = mysql_fetch_array($res)) {
		  array_push($imagenes, $registro[0]);
		}
		  
		// Procedemos a añadir la/s imágen/es
		for ($i = 0; $i < count($imagenes); $i++) { 
			// campo PI_INI no existente ya en la BD
			$sSQL = "INSERT INTO PAR_IMG (PI_IMG, PI_PAR, PI_PRL) VALUES(" .STR_formato_cadena($imagenes[$i]). "," .$par_id. ",1);";
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
	}

	if (!$respuesta->error) {
		// Añadimos las nuevas imágenes obligatoriamente con nombre único
		foreach($_FILES["archivo"]['tmp_name'] as $key => $tmp_name){
			//Validamos que el archivo exista
			if($_FILES["archivo"]['name'][$key]) {
				$filename = $_FILES["archivo"]['name'][$key]; //Obtenemos el nombre original del archivo
				$source = $_FILES["archivo"]['tmp_name'][$key]; //Obtenemos un nombre temporal del archivo
				

				if ($filename != ''){
					$filename = uniqid();

					//Movemos y validamos que el archivo se haya cargado correctamente
					//El primer campo es el origen y el segundo el destino
					if(move_uploaded_file($source, "../partes_imagenes/" . $filename . ".jpg")) {	
						$sSQL = "INSERT INTO PAR_IMG (PI_IMG, PI_PAR, PI_PRL) VALUES (" . STR_formato_cadena($filename . ".jpg") . "," . $par_id . ",1)";
						$resultado = DB_EJECUTA($sSQL);

						if (!$resultado) {
							$respuesta->error = true;
							$respuesta->mensaje = "Error al guardar el parte de trabajo. Tabla PAR_IMG";
						}
					} else { 
						$respuesta->error = true;
						$respuesta->mensaje = "Ocurrió algún error al subir el fichero. El parte de guard&oacute; aunque no pudieron guardarse las imágenes.";
					}
				}
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
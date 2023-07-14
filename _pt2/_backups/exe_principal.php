<?php

if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	
	$respuesta = new stdClass();
	
	include "comun/funciones.php";
	include "comun/db_con.php";

	session_start();
	
	if (!isset($_SESSION['GLB_USR_ID'])) {
    	php_redirect('index.php');
    }

	$par_tip = $_REQUEST['par_tip'];
	$fecha_hora = STR_fechor_esc15($par_tip);
	$fecha = STR_formato_cadena(date("Y-m-d"));
	$fecha_dia_anterior = STR_formato_cadena(fecha_dia_anterior(date("Y-m-d")));
	$usuario = $_SESSION['GLB_USR_ID'];
	$ope_sel = $_REQUEST['txt_pla_ope_sel'];
	$pla_pee = $_REQUEST['txt_pee'];
	$pla_jef = $_REQUEST['txt_jef'];
	$Latitud = $_REQUEST['txt_lat'];
	$Longitud = $_REQUEST['txt_lon'];
	$Direccion = $_REQUEST['txt_dir'];
	$vehiculo = $_REQUEST['cmb_veh'];
	$veh_uso = $_REQUEST['cmb_veh_uso'];
	$veh_kil = $_REQUEST['txt_km_act'];
	if ($veh_kil == ''){
		$veh_kil = 0;
	}
	$veh_kil += 0;

	$respuesta->error = false;

	// comprobación de no introducir unos kilómetros inferiores al último registrado
	if (($par_tip == "INI_JOR" || $par_tip=="INI_VEH") && $veh_uso=="Conductor" && $vehiculo!=326) {
		$par_km_ult=0;
		$sSQL = "SELECT * FROM `USR_JOR` where UJ_VEH_ID=" . $vehiculo . " AND UJ_TIP_STOP=3 ORDER BY UJ_ID DESC";
        $veh_sel = DB_CONSULTA($sSQL);

        if ($row = mysql_fetch_assoc($veh_sel)) {
          $par_km_ult = $row['UJ_VEH_KIL_FIN'];
        }
        $par_km_ult += 0;
        if ($par_km_ult ==0){
        	$par_km_ult = $row['UJ_VEH_KIL'];
        }
        if($par_km_ult>$veh_kil && $par_km_ult>0){
			$respuesta->error = true;
			//$respuesta->mensaje = "Los kilómetros totales debe ser superior a la última lectura: " . $par_km_ult . " km";
			$respuesta->mensaje = "Los kilómetros totales debe ser superior o igual a la última lectura";
		}
	}
	$veh_kil = STR_formato_numero($veh_kil);

	/*
	// Comprobación de las imágenes
	for($i=1; $i<=1; $i++) {
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
	*/

	if (!$respuesta->error) {
		if ($par_tip == "INI_JOR") {
			//$sSQL = "INSERT INTO USR_JOR(UJ_FEC_INI, UJ_USU, UJ_JOR, UJ_MOD, UJ_INIGPSLAT, UJ_INIGPSLON, UJ_INIDIR, UJ_VEH_ID, UJ_VEH_USO, UJ_VEH_KIL) ";
			//$sSQL .= "VALUES (" . $fecha_hora . ", " . $usuario . ", 1, 1, '" . $Latitud . "', '" . $Longitud . "','" . $Direccion . "'," . $vehiculo . ", '" . $veh_uso . "', " . $veh_kil . ")";
			$sSQL = "INSERT INTO USR_JOR(UJ_FEC_INI, UJ_USU, UJ_JOR, UJ_MOD, UJ_INIGPSLAT, UJ_INIGPSLON, UJ_INIDIR) ";
			$sSQL .= "VALUES (" . $fecha_hora . ", " . $usuario . ", 1, 1, '" . $Latitud . "', '" . $Longitud . "','" . $Direccion . "')";
			$campo_imagen = "UJ_IMG_INI";

		} else if ($par_tip == "FIN_JOR") {
			$pj_id = DB_LEE_CAMPO("USR_JOR", "UJ_ID", "UJ_USU=" . $usuario . " AND UJ_JOR=1 AND UJ_FEC_INI>=" . $fecha_dia_anterior . " AND UJ_FEC_INI<=" . $fecha_hora . " AND UJ_FEC_FIN IS NULL");

			if ($pj_id == 0) {
				$respuesta->error = true;
		    	$respuesta->mensaje = "No se encontró ningún registro de Inicio de Jornada para el día actual.";
			} else {
				$sSQL = "UPDATE USR_JOR SET ";
				$sSQL .= "UJ_FEC_FIN=" . $fecha_hora . ", UJ_MOD=1, ";
				$sSQL .= "UJ_FINGPSLAT='" . $Latitud . "', ";
				$sSQL .= "UJ_FINGPSLON='" . $Longitud . "', ";
				$sSQL .= "UJ_FINDIR='" . $Direccion . "' ";
				$sSQL .= "WHERE UJ_ID=" . $pj_id;
			}
			$campo_imagen = "UJ_IMG_FIN";

			/*
			//marcar al usuario como trabajo iniciado
			$sSQL = "UPDATE USR_WEB SET USR_TRA_INI=0 WHERE USR_ID=" . $usuario;
			$resultado = DB_EJECUTA($sSQL);
			*/

		} else if ($par_tip == "INI_TRA") {
			$sSQL = "INSERT INTO USR_JOR(UJ_FEC_INI, UJ_USU, UJ_JOR, UJ_TIP_STOP, UJ_MOD, UJ_INIGPSLAT, UJ_INIGPSLON, UJ_INIDIR, UJ_PEE, UJ_PLA_JEF) ";
			$sSQL .= "VALUES (" . $fecha_hora . ", " . $usuario . ", 0, " . GLB_PARADA_TRABAJO . ", 1, '" . $Latitud . "', '" . $Longitud . "', '" . $Direccion . "', ";
			if ($pla_pee<>0){
				$sSQL .= $pla_pee;
			} else {
				$sSQL .= "NULL";
			}
			$sSQL .= ", " . $pla_jef .  ")";
			$campo_imagen = "UJ_IMG_INI";
		
		} else if ($par_tip == "FIN_TRA") {
			$pj_id = DB_LEE_CAMPO("USR_JOR", "UJ_ID", "UJ_USU=" . $usuario . " AND UJ_JOR=0 AND UJ_FEC_FIN IS NULL AND UJ_FEC_INI>=" . $fecha . " AND UJ_FEC_INI<=" . $fecha_hora . " AND UJ_TIP_STOP = " . GLB_PARADA_TRABAJO . " AND UJ_FEC_FIN IS NULL");
			
			if ($pj_id == 0) {
				$respuesta->error = true;
		    	$respuesta->mensaje = "No se encontró ningún registro de Inicio de Trabajo para el día actual.";
			} else {
				$sSQL = "UPDATE USR_JOR SET ";
				$sSQL .= "UJ_FEC_FIN=" . $fecha_hora . ", UJ_MOD=1, ";
				$sSQL .= "UJ_FINGPSLAT='" . $Latitud . "', ";
				$sSQL .= "UJ_FINGPSLON='" . $Longitud . "', ";
				$sSQL .= "UJ_FINDIR='" . $Direccion . "' ";
				$sSQL .= "WHERE UJ_ID=" . $pj_id;
			}
			$campo_imagen = "UJ_IMG_FIN";
		
		} else if ($par_tip == "INI_DES") {
			$sSQL = "INSERT INTO USR_JOR(UJ_FEC_INI, UJ_USU, UJ_JOR, UJ_TIP_STOP, UJ_MOD, UJ_INIGPSLAT, UJ_INIGPSLON, UJ_INIDIR) ";
			$sSQL .= "VALUES (" . $fecha_hora . ", " . $usuario . ", 0, " . GLB_PARADA_DESCANSO . ", 1, '" . $Latitud . "', '" . $Longitud . "', '" . $Direccion . "')";
			$campo_imagen = "UJ_IMG_INI";
		
		} else if ($par_tip == "FIN_DES") {
			$pj_id = DB_LEE_CAMPO("USR_JOR", "UJ_ID", "UJ_USU=" . $usuario . " AND UJ_JOR=0 AND UJ_FEC_FIN IS NULL AND UJ_FEC_INI>=" . $fecha_dia_anterior . " AND UJ_FEC_INI<=" . $fecha_hora . " AND UJ_TIP_STOP = " . GLB_PARADA_DESCANSO . " AND UJ_FEC_FIN IS NULL");
			
			if ($pj_id == 0) {
				$respuesta->error = true;
		    	$respuesta->mensaje = "No se encontró ningún registro de Inicio de Descanso para el día actual.";
			} else {
				$sSQL = "UPDATE USR_JOR SET ";
				$sSQL .= "UJ_FEC_FIN=" . $fecha_hora . ", UJ_MOD=1, ";
				$sSQL .= "UJ_FINGPSLAT='" . $Latitud . "', ";
				$sSQL .= "UJ_FINGPSLON='" . $Longitud . "', ";
				$sSQL .= "UJ_FINDIR='" . $Direccion . "' ";
				$sSQL .= "WHERE UJ_ID=" . $pj_id;
			}
			$campo_imagen = "UJ_IMG_FIN";

		} else if ($par_tip == "FIN_VEH") {
			//inicio de jornada con vehículo
			$pj_id = DB_LEE_CAMPO("USR_JOR", "UJ_ID", "UJ_USU=" . $usuario . " AND UJ_FEC_INI>=" . $fecha_dia_anterior . " AND UJ_FEC_INI<=" . $fecha_hora . " AND UJ_FEC_FIN IS NULL AND NOT UJ_VEH_ID IS NULL");
			$tip_par = DB_LEE_CAMPO("USR_JOR", "UJ_TIP_STOP","UJ_ID=" . $pj_id);
			if ($pj_id != 0) {//cerrar kilómetros del inicio de jornada/PAR
				if ($tip_par != 3){
					$sSQL = "UPDATE USR_JOR SET ";
					$sSQL .= "UJ_MOD=1, ";
					$sSQL .= "UJ_VEH_KIL_FIN=" .$veh_kil . " ";
					$sSQL .= "WHERE UJ_ID=" . $pj_id;
				} else {
					$sSQL = "UPDATE USR_JOR SET ";
					$sSQL .= "UJ_FEC_FIN=" . $fecha_hora . ", UJ_MOD=1, ";
					$sSQL .= "UJ_VEH_KIL_FIN=" .$veh_kil . " ";
					$sSQL .= "WHERE UJ_ID=" . $pj_id;
				}
			}
		
		}

		if (!$respuesta->error) {
			DB_EJECUTA("BEGIN"); // Comienza transacción
			
			$resultado = DB_EJECUTA($sSQL);
			
			if (!$resultado) {
				$respuesta->error = true;
			    $respuesta->mensaje = "Error al guardar la información." . $sSQL;
			}

			if ($pj_id == 0) {
				$pj_id = mysql_insert_id();
			}

			
			//if(($par_tip=="INI_JOR" || $par_tip=="INI_VEH") && $veh_uso=="Conductor" && $vehiculo!=326) {
			if(($par_tip=="INI_JOR" || $par_tip=="INI_VEH") && $vehiculo!=326) {
				if($veh_uso=="Conductor") {
					// marcar uso vehículo
					$sSQL = "UPDATE VEH SET VEH_USU_ACT=" . $usuario . " WHERE VEH_ID=" . $vehiculo;
					$resultado = DB_EJECUTA($sSQL);
				}
				// registro de uso de vehículo
				$sSQL = "INSERT INTO USR_JOR(UJ_FEC_INI, UJ_USU, UJ_JOR, UJ_TIP_STOP, UJ_MOD, UJ_INIGPSLAT, UJ_INIGPSLON, UJ_INIDIR, UJ_VEH_ID, UJ_VEH_USO, UJ_VEH_KIL) VALUES (" . $fecha_hora . ", " . $usuario . ", 0, " . GLB_CAMBIO_VEHICULO . ", 1, '" . $Latitud . "', '" . $Longitud . "', '" . $Direccion . "'," . $vehiculo . ", '" . $veh_uso . "', " . $veh_kil . ")";
				$resultado = DB_EJECUTA($sSQL);
			} elseif(($par_tip=="FIN_JOR" || $par_tip=="FIN_VEH") && $veh_uso=="Conductor" && $vehiculo!=326) {
				// desmarcar uso vehículo
				$sSQL = "UPDATE VEH SET VEH_USU_ACT=NULL WHERE VEH_ID=" . $vehiculo;
				$resultado = DB_EJECUTA($sSQL);

				// registrar fin de uso de vehículo
				$pj_id = DB_LEE_CAMPO("USR_JOR", "UJ_ID", "UJ_USU=" . $usuario . " AND UJ_TIP_STOP=3 AND UJ_FEC_INI>=" . $fecha_dia_anterior . " AND UJ_FEC_INI<=" . $fecha_hora . " AND UJ_FEC_FIN IS NULL");
				if ($pj_id != 0) {
					$sSQL = "UPDATE USR_JOR SET ";
					$sSQL .= "UJ_FEC_FIN=" . $fecha_hora . ", UJ_MOD=1, ";
					$sSQL .= "UJ_FINGPSLAT='" . $Latitud . "', ";
					$sSQL .= "UJ_FINGPSLON='" . $Longitud . "', ";
					$sSQL .= "UJ_FINDIR='" . $Direccion . "', ";
					$sSQL .= "UJ_VEH_KIL_FIN=" .$veh_kil . " ";
					$sSQL .= "WHERE UJ_ID=" . $pj_id;
					$resultado = DB_EJECUTA($sSQL);
				}
				
			}
			if (!$resultado) {
				$respuesta->error = true;
			    $respuesta->mensaje = "Error al guardar la información." . $sSQL;
			}

			// evitar generar inicio de trabajo de miembros si al guardar el inicio del jefe da error
			if (!$respuesta->error) {
				if ($par_tip == "INI_JOR") {
					//marcar al usuario como trabajo iniciado
					$sSQL = "UPDATE USR_WEB SET USR_JOR_INI=1 WHERE USR_ID=" . $usuario;
					$resultado = DB_EJECUTA($sSQL);
					if (!$resultado) {
						$respuesta->error = true;
					    $respuesta->mensaje = "Error al guardar la información." . $sSQL;
					}

					// desmarcar el uso de cualquier otro vehículo diferente al actual (evitar tener un vehículo de otro día que no hizo fin de jornada)
					$sSQL = "UPDATE VEH SET VEH_USU_ACT=NULL WHERE (VEH_ID<>" . $vehiculo . " AND VEH_USU_ACT=" . $usuario . ")";
					$resultado = DB_EJECUTA($sSQL);
					if (!$resultado) {
						$respuesta->error = true;
					    $respuesta->mensaje = "Error al guardar la información." . $sSQL;
					}

				} elseif ($par_tip == "INI_TRA") {
					//generar registro de operarios que inician a la vez
					$ope_sel_reg=explode(",", $ope_sel);
					$max = sizeof($ope_sel_reg);
					for ($i=0;$i<$max;$i++){
						if ($ope_sel_reg[$i]!=""){
							$sSQL = "INSERT INTO USR_JOR(UJ_FEC_INI, UJ_USU, UJ_JOR, UJ_TIP_STOP, UJ_MOD, UJ_INIGPSLAT, UJ_INIGPSLON, UJ_INIDIR, UJ_PEE, UJ_PLA_JEF) ";
							$sSQL .= "VALUES (" . $fecha_hora . ", " . $ope_sel_reg[$i] . ", 0, " . GLB_PARADA_TRABAJO . ", 1, '" . $Latitud . "', '" . $Longitud . "', '" . $Direccion . "', ";
							if ($pla_pee<>0){$sSQL .= $pla_pee;} else {$sSQL .= "NULL";}
							$sSQL .= ",0)";
							$resultado = DB_EJECUTA($sSQL);
							if (!$resultado) {
								$respuesta->error = true;
							    $respuesta->mensaje = "Error al guardar la información." . $sSQL;
							}

							//marcar operario como trabajo iniciado
							$sSQL = "UPDATE USR_WEB SET USR_TRA_INI=1 WHERE USR_ID=" . $ope_sel_reg[$i];
							$resultado = DB_EJECUTA($sSQL);
						}
					}

					//marcar al usuario como trabajo iniciado
					$sSQL = "UPDATE USR_WEB SET USR_TRA_INI=1 WHERE USR_ID=" . $usuario;
					$resultado = DB_EJECUTA($sSQL);
				}
			}


		}
	}
	

	if (!$respuesta->error) {
		// Imágenes
		for($i=1; $i<=1; $i++) {
			$campo_file = 'txt_img' . $i;				
			$nombre_archivo = $_FILES[$campo_file]['name'];

			if ($nombre_archivo != '') {
				$nuevo_nombre_archivo = uniqid();
				
	   			if (move_uploaded_file($_FILES[$campo_file]['tmp_name'], "../partes_imagenes/" . $nuevo_nombre_archivo . ".jpg")){ 
      				$sSQL = "UPDATE USR_JOR SET " . $campo_imagen . "=" . STR_formato_cadena($nuevo_nombre_archivo . ".jpg") . " WHERE UJ_ID=" . $pj_id;
					$resultado = DB_EJECUTA($sSQL);

					if (!$resultado) {
						$respuesta->error = true;
		    			$respuesta->mensaje = "Error al guardar el parte de trabajo." . $sSQL;
		    		}
   				}else{ 
		      		$respuesta->error = true;
		      		$respuesta->mensaje = "Ocurrió algún error al subir el fichero. El parte no se guard&oacute.";
   				}
   			}
		}
	}

	if (!$respuesta->error) {
		DB_EJECUTA("COMMIT");
		$respuesta->tipo_mensaje_alerta = "success";
		$respuesta->mensaje = "El registro se a&ntilde;adi&oacute; correctamente. " . $fecha_hora;
		$respuesta->onclick = "javascript: window.location.assign('principal.php');";
	} else {
		DB_EJECUTA("ROLLBACK");
		$respuesta->tipo_mensaje_alerta = "danger";
		$respuesta->onclick = "javascript: habilitar_capa('capa_contenidos');";
	}
	echo json_encode($respuesta);
} else {
	throw new Exception("Error Processing Request", 1);   
}

?>
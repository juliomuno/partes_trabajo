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
	$fecha_hora = STR_formato_cadena(date("Y-m-d H:i:s"));
	$fecha = STR_formato_cadena(date("Y-m-d"));
	$usuario = $_SESSION['GLB_USR_ID'];

	$respuesta->error = false;
	
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
	
	if (!$respuesta->error) {
		if ($par_tip == "INI_JOR") {
			$sSQL = "INSERT INTO USR_JOR(UJ_FEC_INI, UJ_USU, UJ_JOR, UJ_MOD) ";
			$sSQL .= "VALUES (" . $fecha_hora . ", " . $usuario . ", 1, 1)";
			$campo_imagen = "UJ_IMG_INI";
		} else if ($par_tip == "FIN_JOR") {
			$pj_id = DB_LEE_CAMPO("USR_JOR", "UJ_ID", "UJ_USU=" . $usuario . " AND UJ_JOR=1 AND UJ_FEC_INI>=" . $fecha . " AND UJ_FEC_INI<=" . $fecha_hora . " AND UJ_FEC_FIN IS NULL");
			
			if ($pj_id == 0) {
				$respuesta->error = true;
		    	$respuesta->mensaje = "No se encontró ningún registro de Inicio de Jornada para el día actual.";
			} else {
				$sSQL = "UPDATE USR_JOR SET ";
				$sSQL .= "UJ_FEC_FIN=" . $fecha_hora . ", UJ_MOD=1 ";
				$sSQL .= "WHERE UJ_ID=" . $pj_id;
			}
			$campo_imagen = "UJ_IMG_FIN";
		} else if ($par_tip == "INI_TRA") {
			$sSQL = "INSERT INTO USR_JOR(UJ_FEC_INI, UJ_USU, UJ_JOR, UJ_MOD) ";
			$sSQL .= "VALUES (" . $fecha_hora . ", " . $usuario . ", 0, 1)";
			$campo_imagen = "UJ_IMG_INI";
		} else if ($par_tip == "FIN_TRA") {
			$pj_id = DB_LEE_CAMPO("USR_JOR", "UJ_ID", "UJ_USU=" . $usuario . " AND UJ_JOR=0 AND UJ_FEC_FIN IS NULL AND UJ_FEC_INI>=" . $fecha . " AND UJ_FEC_INI<=" . $fecha_hora . " AND UJ_FEC_FIN IS NULL");
			
			if ($pj_id == 0) {
				$respuesta->error = true;
		    	$respuesta->mensaje = "No se encontró ningún registro de Inicio de Trabajo para el día actual.";
			} else {
				$sSQL = "UPDATE USR_JOR SET ";
				$sSQL .= "UJ_FEC_FIN=" . $fecha_hora . ", UJ_MOD=1 ";
				$sSQL .= "WHERE UJ_ID=" . $pj_id;
			}
			$campo_imagen = "UJ_IMG_FIN";
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
		}
	}
	

	if (!$respuesta->error) {
		// Imágenes
		for($i=1; $i<=1; $i++) {
			$campo_file = 'txt_img' . $i;				
			$nombre_archivo = $_FILES[$campo_file]['name'];

			if ($nombre_archivo != '') {
				$nuevo_nombre_archivo = uniqid();
				
	   			if (move_uploaded_file($_FILES[$campo_file]['tmp_name'], "partes_imagenes/" . $nuevo_nombre_archivo . ".jpg")){ 
      				$sSQL = "UPDATE USR_JOR SET " . $campo_imagen . "=" . STR_formato_cadena($nuevo_nombre_archivo . ".jpg") . " WHERE UJ_ID=" . $pj_id;
					$resultado = DB_EJECUTA($sSQL);

					if (!$resultado) {
						$respuesta->error = true;
		    			$respuesta->mensaje = "Error al guardar el parte de trabajo." . $sSQL;
		    		}
   				}else{ 
		      		$respuesta->error = true;
		      		$respuesta->mensaje = "Ocurrió algún error al subir el fichero.";
   				}
   			}
		}

		// Audio
		$campo_audio = $_REQUEST['txt_audio'];
		if ($campo_audio != '') {
			$nuevo_nombre_archivo = uniqid();
			// Nuestro base64 contiene un esquema Data URI (data:image/png;base64,)
			// que necesitamos remover para poder guardar nuestra imagen
			// Usa explode para dividir la cadena de texto en la , (coma)
			$base_to_php = explode(',', $campo_audio);
			// El segundo item del array base_to_php contiene la información que necesitamos (base64 plano)
			// y usar base64_decode para obtener la información binaria de la imagen
			$data_audio = base64_decode($base_to_php[1]);// BBBFBfj42Pj4....

			// Proporciona una locación a la nueva imagen (con el nombre y formato especifico)
			$filepath = './partes_audios/' . $nuevo_nombre_archivo . '.mp3';

			$respuesta->mensaje = $filepath;
			
			// Finalmente guarda la imágen en el directorio especificado y con la informacion dada
			if (file_put_contents($filepath, $data_audio)) {
				$sSQL = "UPDATE USR_JOR SET UJ_AUD=" . STR_formato_cadena($nuevo_nombre_archivo . ".mp3") . " WHERE UJ_ID=" . $pj_id;
					$resultado = DB_EJECUTA($sSQL);

					if (!$resultado) {
						$respuesta->error = true;
		    			$respuesta->mensaje = "Error al guardar." . $sSQL;
		    		}
			} else {
				$respuesta->error = true;
	      		$respuesta->mensaje = "Ocurrió algún error al subir el Audio";
			}
		}
	}
	
	if (!$respuesta->error) {
		DB_EJECUTA("COMMIT");
		$respuesta->tipo_mensaje_alerta = "success";
		$respuesta->mensaje = "El registro se a&ntilde;adi&oacute; correctamente.";
		$respuesta->onclick = "javascript: window.location.assign('principal.php');";
	} else {
		DB_EJECUTA("ROLLBACK");
		$respuesta->tipo_mensaje_alerta = "danger";
		$respuesta->onclick = "javascript: habilitar_capa('capa_foto');";
	}
	echo json_encode($respuesta);
} else {
	throw new Exception("Error Processing Request", 1);   
}

?>
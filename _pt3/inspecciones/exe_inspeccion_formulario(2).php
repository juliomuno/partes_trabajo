<?php

if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	
	$respuesta = new stdClass();
	
	include "../comun/funciones.php";
	include "../comun/db_con.php";
	
	session_start();
	
	if (!isset($_SESSION['GLB_USR_ID'])) {
    php_redirect('../index.php');
  }
  
	$op = $_REQUEST['op'];
	$insp_id = $_REQUEST['insp_id'];
	$frm_id = $_REQUEST['frm_id'];
	$vrespuestas = $_REQUEST['cmb_resp'];
	$vpreguntas = $_REQUEST['txt_preg'];

	$respuesta->error = false;
	
	// Comprobación de las imágenes
	// Imágenes
	foreach( $vpreguntas as $clave => $valor ) {
		$campo_file = 'txt_img' . $valor;
	
		$nombre_archivo = $_FILES[$campo_file]['name'];
		$tipo_archivo = $_FILES[$campo_file]['type'];
		$tamano_archivo = $_FILES[$campo_file]['size'];
		
		if ($nombre_archivo != '') {
			//compruebo si las características del archivo son las que deseo 
			if (!((strpos($tipo_archivo, "gif") || strpos($tipo_archivo, "jpeg")) && ($tamano_archivo < 2000000))) { 
				$respuesta->error = true;
				$respuesta->mensaje = "La extensión o el tamaño de los archivos no es correcta. <br><br><table><tr><td><li>Se permiten archivos .gif o .jpg<br><li>Se permiten archivos de 2 MB máximo.</td></tr></table>"; 
			}
		}
	}

	if (!$respuesta->error) {
		DB_EJECUTA("BEGIN"); // Comienza transacción
		
		if ($op == 'C') {
			$sSQL = "INSERT INTO INSP_RESP (IR_INSP, IR_FRM, IR_PREG, IR_RESP, IR_IMG) VALUES ";
			$i = 0;
			foreach( $vrespuestas as $clave => $valor ) {
				$sSQL .= "(" . $insp_id . ", " . $frm_id . ", " . $vpreguntas[$clave] . ", " . $valor;
				if ($_REQUEST['lbl_img' . $vpreguntas[$clave]] == '') {
					$sSQL .= ", IR_IMG=NULL";
				} else {
					$campo_file = 'txt_img' . $vpreguntas[$clave];				
					$nombre_archivo = $_FILES[$campo_file]['name'];

					if ($nombre_archivo != '') {
						$nuevo_nombre_archivo = uniqid();
	   				if (move_uploaded_file($_FILES[$campo_file]['tmp_name'], "../inspecciones_imagenes/" . $nuevo_nombre_archivo . ".jpg")){ 
      				$sSQL .= ", " . STR_formato_cadena($nuevo_nombre_archivo . ".jpg");
   					} else { 
		      		$respuesta->error = true;
		      		$respuesta->mensaje = "Ocurrió algún error al subir el fichero. El parte de guard&oacute; aunque no pudieron guardarse las imágenes.";
   					}
   				}
   			}
   			$sSQL .= ") ,";
	  	}

	  	$sSQL = substr($sSQL, 0, -1);
	  	
	  	$resultado = DB_EJECUTA($sSQL);

	  	if (!$resultado) {
				$respuesta->error = true;
				$respuesta->mensaje = "Error al guardar el parte de trabajo. Tabla PAR." . $sSQL;
			}
		} else {
			foreach( $vrespuestas as $clave => $valor ) {
				if (!$respuesta->error) {	
					$sSQL = "UPDATE INSP_RESP SET IR_RESP=" . $valor;

					if ($_REQUEST['lbl_img' . $vpreguntas[$clave]] == '') {
						$sSQL .= ", IR_IMG=NULL";
					} else {
						$campo_file = 'txt_img' . $vpreguntas[$clave];				
						$nombre_archivo = $_FILES[$campo_file]['name'];

						if ($nombre_archivo != '') {
							$nuevo_nombre_archivo = uniqid();
		   				if (move_uploaded_file($_FILES[$campo_file]['tmp_name'], "../inspecciones_imagenes/" . $nuevo_nombre_archivo . ".jpg")){ 
	      				$sSQL .= ", IR_IMG=" . STR_formato_cadena($nuevo_nombre_archivo . ".jpg");
	   					} else { 
			      		$respuesta->error = true;
			      		$respuesta->mensaje = "Ocurrió algún error al subir el fichero. El parte de guard&oacute; aunque no pudieron guardarse las imágenes.";
	   					}
	   				}
	   			}

	   			$sSQL .= " WHERE IR_INSP=" . $insp_id . " AND IR_FRM=" . $frm_id . " AND IR_PREG=" . $vpreguntas[$clave];
				}

				if (!$respuesta->error) {
					$resultado = DB_EJECUTA($sSQL);
					if (!$resultado) {
						$respuesta->error = true;
						$respuesta->mensaje = "Error al guardar el formulario de inspección." . $sSQL;
					}
				}
			}
		}
	}


	if (!$respuesta->error) {
		DB_EJECUTA("COMMIT");
		$respuesta->tipo_mensaje_alerta = "success";
		$respuesta->mensaje = "El registro se a&ntilde;adi&oacute; correctamente. ID=" . $insp_id;
		$respuesta->onclick = "javascript: window.location.assign('inspeccion.php?id=" . $insp_id . "&op=M');";
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
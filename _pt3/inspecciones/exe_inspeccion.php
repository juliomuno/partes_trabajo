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
	
	if ($op == "C") {
		$insp_id = date('Y') . substr("0000" . DB_ULTIMO("INSP","INSP_ID", "LEFT(INSP_ID,4)=" . date('Y')), -4, 4);
	} else {
		$insp_id = $_REQUEST['id'];
	}
	
	$respuesta->error = false;
	
	$voperarios = $_REQUEST['cmb_operarios'];
	$vsubc = $_REQUEST['cmb_subc'];
	$vope_subc = $_REQUEST['cmb_ope_subc'];
	$insp_fec = STR_formato_cadena($_REQUEST['txt_fec']);
	$insp_pre = $_REQUEST['txt_pre'];
	$insp_tip_tra = $_REQUEST['cmb_tip_tra'];
	$insp_tip_obr = $_REQUEST['cmb_tip_obr'];
	$insp_usu = $_REQUEST['cmb_ins_usu'];
	$insp_dir = STR_formato_cadena($_REQUEST['txt_dir']);
	$insp_pob = STR_formato_cadena($_REQUEST['cmb_pob']);
	$insp_lat = STR_formato_cadena($_REQUEST['txt_lat']);
	$insp_lon = STR_formato_cadena($_REQUEST['txt_lon']);
	$insp_veh = $_REQUEST['cmb_veh'];
	$insp_ext1 = STR_formato_cadena($_REQUEST['txt_ext1']);
	$insp_ext2 = STR_formato_cadena($_REQUEST['txt_ext2']);
	$insp_jef = STR_formato_cadena($_REQUEST['cmb_ins_jef']);
	$insp_rpr = STR_formato_cadena($_REQUEST['cmb_ins_rpr']);

	if ($insp_ext1=='') {
		$insp_ext1 = 0;
	}

	if ($insp_ext2=='') {
		$insp_ext2 = 0;
	}

	if ($insp_pre==''){
		$insp_pre=0;
	}

	
	if ($insp_rpr==""){
		$insp_rpr=0;
	}
	
	
	$insp_cssdf = $_REQUEST['chk_cssdf'];
	if ($insp_cssdf =='') {
		$insp_cssdf = 0;
	}
	$insp_sorp = $_REQUEST['chk_sorp'];
	if ($insp_sorp =='') {
		$insp_sorp = 0;
	}

	$insp_nsorp = $_REQUEST['chk_nsorp'];
	if ($insp_nsorp =='') {
		$insp_nsorp = 0;
	}

	$insp_cobs = $_REQUEST['chk_cobs'];
	if ($insp_cobs =='') {
		$insp_cobs = 0;
	}

	$insp_cinc = $_REQUEST['chk_cinc'];
	if ($insp_cinc =='') {
		$insp_cinc = 0;
	}

	if ($insp_usu=='') {
		$insp_usu = 0;
	}

	if ($insp_veh==''){
		$insp_veh=0;
	}

	$insp_obs = STR_formato_cadena($_REQUEST['txt_obs']);
	$insp_tra_des = STR_formato_cadena($_REQUEST['txt_tra_des']);
	$insp_des_inc = STR_formato_cadena($_REQUEST['txt_des_inc']);
	
	$campo_file = 'imagen';
	$nombre_archivo = $_FILES[$campo_file]['name'];
	$tipo_archivo = $_FILES[$campo_file]['type'];
	$tamano_archivo = $_FILES[$campo_file]['size'];

	if (!$respuesta->error) {
		if ($op == "C") {
			$sSQL = "INSERT INTO INSP (INSP_ID, INSP_FEC, INSP_PRE, INSP_USR, INSP_TIP_OBR, INSP_TIP_TRA, INSP_DIR, INSP_POB, INSP_TRA_DES, INSP_OBS, INSP_CSSDF, INSP_SORP, INSP_NSORP, INSP_LAT, INSP_LON, INSP_USU, INSP_VEH, INSP_DES_INC, INSP_COBS, INSP_CINC, INSP_EXT1, INSP_EXT2, INSP_JEF, INSP_RPR, INSP_MOD) ";
			$sSQL .= "VALUES (";
			$sSQL .= $insp_id;
			$sSQL .= ", " . $insp_fec;
			$sSQL .= ", " . $insp_pre;
			$sSQL .= ", " . $_SESSION["GLB_USR_ID"];
			$sSQL .= ", " . $insp_tip_obr;
			$sSQL .= ", " . $insp_tip_tra;
			$sSQL .= ", " . $insp_dir;
			$sSQL .= ", " . $insp_pob;
			$sSQL .= ", " . $insp_tra_des;
			$sSQL .= ", " . $insp_obs;
			$sSQL .= ", " . $insp_cssdf;
			$sSQL .= ", " . $insp_sorp;
			$sSQL .= ", " . $insp_nsorp;
			$sSQL .= ", " . $insp_lat;
			$sSQL .= ", " . $insp_lon;
			$sSQL .= ", " . $insp_usu;
			$sSQL .= ", " . $insp_veh;
			$sSQL .= ", " . $insp_des_inc;
			$sSQL .= ", " . $insp_cobs;
			$sSQL .= ", " . $insp_cinc;
			$sSQL .= ", " . $insp_ext1;
			$sSQL .= ", " . $insp_ext2;
			$sSQL .= ", " . $insp_jef;
			$sSQL .= ", " . $insp_rpr;
			$sSQL .= ", 1)";
		
		} else {
			$sSQL = "UPDATE INSP SET ";
			$sSQL .= "INSP_FEC=" . $insp_fec . ",";
			$sSQL .= "INSP_PRE=" . $insp_pre . ",";
			if ($_SESSION['GLB_USR_ID']!=962){
				$sSQL .= "INSP_USR=" . $_SESSION["GLB_USR_ID"] . ",";
				}
			$sSQL .= "INSP_TIP_OBR=" . $insp_tip_obr . ",";
			$sSQL .= "INSP_TIP_TRA=" . $insp_tip_tra . ",";
			$sSQL .= "INSP_DIR=" . $insp_dir . ",";
			$sSQL .= "INSP_POB=" . $insp_pob . ",";
			$sSQL .= "INSP_TRA_DES=" . $insp_tra_des . ",";
			$sSQL .= "INSP_OBS=" . $insp_obs . ",";
			$sSQL .= "INSP_CSSDF=" . $insp_cssdf . ",";
			$sSQL .= "INSP_SORP=" . $insp_sorp . ",";
			$sSQL .= "INSP_NSORP=" . $insp_nsorp . ",";
			$sSQL .= "INSP_USU=" . $insp_usu . ",";
			$sSQL .= "INSP_LAT=" . $insp_lat . ",";
			$sSQL .= "INSP_LON=" . $insp_lon . ",";
			$sSQL .= "INSP_VEH=" . $insp_veh . ",";
			$sSQL .= "INSP_DES_INC=" . $insp_des_inc . ",";
			$sSQL .= "INSP_COBS=" . $insp_cobs . ",";
			$sSQL .= "INSP_CINC=" . $insp_cinc . ",";
			$sSQL .= "INSP_EXT1=" . $insp_ext1 . ",";
			$sSQL .= "INSP_EXT2=" . $insp_ext2 . ",";
			$sSQL .= "INSP_JEF=" . $insp_jef . ",";
			$sSQL .= "INSP_RPR=" . $insp_rpr . ",";
			$sSQL .= "INSP_MOD=1 ";
			$sSQL .= " WHERE INSP_ID=" . $insp_id;
		}

		DB_EJECUTA("BEGIN"); // Comienza transacción
		
		$resultado = DB_EJECUTA($sSQL);
		
		if (!$resultado) {
			$respuesta->error = true;
		  $respuesta->mensaje = "Error al guardar la inspección. Tabla INSP. " . $sSQL;
		}
	}

	if (!$respuesta->error) {
		$sSQL = "DELETE FROM INSP_OPE WHERE IO_INSP=" . $insp_id;
		$resultado = DB_EJECUTA($sSQL);
		if (!$resultado) {
			$respuesta->error = true;
		  $respuesta->mensaje = "Error al eliminar detalle de operarios. " . $sSQL;
		}
		if (!$respuesta->error) {
			foreach( $voperarios as $clave => $valor ) {
				if (!$respuesta->error) {
					$sSQL = "INSERT INTO INSP_OPE (IO_INSP, IO_OPE) ";
					$sSQL .= "VALUES (";
					$sSQL .= $insp_id;
					$sSQL .= ", " . $valor;
					$sSQL .= ")";
					$resultado = DB_EJECUTA($sSQL);
					if (!$resultado) {
						$respuesta->error = true;
					  $respuesta->mensaje = "Error al eliminar detalle de operarios. " . $sSQL;
					}			
				}
			}	
		}
	}

	// Detalle operarios subcontrata
	if (!$respuesta->error) {
		$sSQL = "DELETE FROM INSP_OPE_SUBC WHERE IOS_INSP=" . $insp_id;
		$resultado = DB_EJECUTA($sSQL);
		if (!$resultado) {
			$respuesta->error = true;
		  $respuesta->mensaje = "Error al eliminar detalle de operarios subcontrata. " . $sSQL;
		}
		if (!$respuesta->error) {
			foreach( $vope_subc as $clave => $valor ) {
				if (!$respuesta->error) {
					$sSQL = "INSERT INTO INSP_OPE_SUBC (IOS_INSP, IOS_OPE, IOS_SUBC) ";
					$sSQL .= "VALUES (";
					$sSQL .= $insp_id;
					$sSQL .= ", " . $valor;
					$sSQL .= ", " . $vsubc[$clave];
					$sSQL .= ")";
					$resultado = DB_EJECUTA($sSQL);
					if (!$resultado) {
						$respuesta->error = true;
					  $respuesta->mensaje = "Error al insertar detalle de operarios subcontrata. " . $sSQL;
					}			
				}
			}	
		}
	}

	if (!$respuesta->error) {
		if ($op == "C"){
			// comprovamos si se envió la imagen
			if (isset($_POST['imagen'])) { 

			    // mostrar la imagen
			    //echo '<img src="'.$_POST['imagen'].'" border="1">';
			    //echo $_POST['imagen'];
				
			    // funcion para gusrfdar la imagen base64 en el servidor
			    // el nombre debe tener la extension
			    function uploadImgBase64 ($name){
			    	$base64=$_POST['imagen'];
			        // decodificamos el base64
			        $datosBase64 = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64));
			        // definimos la ruta donde se guardara en el server
			        $path= $_SERVER['DOCUMENT_ROOT'].'/partes_trabajo/inspecciones/firmas/'.$name;

			        // guardamos la imagen en el server
			        if(!file_put_contents($path, $datosBase64)){
			            // retorno si falla
			            $respuesta->error = true;
						$respuesta->mensaje = "Error al subir la firma.";
			            return false;
			        }else{
			            // retorno si todo fue bien
						return true;
					}
			    }
			}
		    // llamamos a la funcion uploadImgBase64( nombre_fina.png) 
		    uploadImgBase64('firma_'.$insp_id.'.png' );
		}
	}

	if (!$respuesta->error) {
		DB_EJECUTA("COMMIT");
		$respuesta->tipo_mensaje_alerta = "success";
		$respuesta->mensaje = "El registro se a&ntilde;adi&oacute; correctamente. ID=" . $insp_id . "<br>PULSE ACEPTAR Y ESPERE QUE SE GENERE EL PDF";
		//$respuesta->onclick = "javascript: window.location.assign('inspecciones.php');";
		$respuesta->onclick = "javascript: window.location.assign('inspeccion_pdf.php?op=M&id=".$insp_id."');";
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
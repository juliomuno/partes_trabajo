<?php

// Variables globales
define ("GLB_APP_PATH", "/partes_trabajo/");
define ("GLB_IMG_PATH", $GLB_APP_PATH . 'img/');
define ("GLB_CSS_PATH", $GLB_APP_PATH . 'css/');

// Variables de conexión a base de datos
define ("MYSQL_HOST", "localhost");		
define ("MYSQL_BD", "comercialmon_app_partes_beta");
define ("MYSQL_USER", "comercialmon_app_partes_beta");			
define ("MYSQL_PASSWORD", "AA11aa11");

// Variables globales con campos de la base de datos
define ("GLB_PARADA_TRABAJO", 1);
define ("GLB_PARADA_DESCANSO", 2);
define ("GLB_CAMBIO_VEHICULO", 3);
define ("GLB_SUBF_CONDUCTOR"," como conductor ");

function php_redirect($pagina) {
	echo '<script type="text/javascript">';
    echo 'window.location.assign("'. $pagina . '");';
    echo '</script>';
}

function CERRAR_SESION() {
	unset($_SESSION['GLB_USR_ID']);
    unset($_SESSION['GLB_USR_NOM']);
    php_redirect("index.php");
}

function DB_ERROR($cad) {
	echo ('<p class="titulo"> ERROR </p>');
	echo ('<p>' . $cad . '</p>');
	echo ('<p><input type="button" value="Regresar" onClick="history.back();"></input></p>');
	//echo ('<script language="javascript">window.location="http://www.google.es";</script>');
	exit;
}

// Función de conexión a base de datos
// Salida: Correcto: Objeto mysqli de conexión a base de datos
//		   Incorrecto: false
function DB_CONECTA() {
	
	global $bd;

	$bd = mysql_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD);
	
	if (!$bd) {
		DB_ERROR('Connection database FAILED: ' . mysql_error());
	}
	else{
				
		//UTF-8 Enable. (insert, update).
		mysql_set_charset('utf8');
			
		//Select the database connection we have previously opened.
		mysql_select_db(MYSQL_BD, $bd) or DB_ERROR('No se pudo seleccionar la base de datos');
		
		//UTF-8 Enable. (select).
		mysql_query("SET NAMES 'utf8'");
	}
	
	//Returns the instance of the database. 
	return $bd;
/*
	$bd = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_BD);
	if($bd->connect_errno > 0){
    	die('No se pudo conectar a la base de datos [' . $bd->connect_error . ']');
    }
	
	//UTF-8 Enable. (insert, update).
    mysqli_set_charset('utf8');

    //UTF-8 Enable. (select).
	$bd->query("SET NAMES 'utf8'");
*/  
}

function DB_DESCONECTA() {

}

function DB_LEE_CAMPO($tabla,$campo,$where) {
	if (!$bd) {
		DB_CONECTA();
	}
	
	if (trim($tabla) != "" && $campo != "") {
		$sql = "SELECT DISTINCT " . $campo . " FROM " . $tabla;
		if ($where != "") {
			$sql .= " WHERE " . $where;
		}
		$sentence = mysql_query($sql);
		
		if (!$sentence) {
			if (mysql_num_rows($sentence) == 0) {
				return 0;
			} else {
				DB_ERROR("Error al procesar la consulta " . mysql_error());	
			}
		}

		if (mysql_num_rows($sentence) >= 1) {
			$row = mysql_fetch_array($sentence);
			return $row[0];
		} else {
			return 0;
		}

	} else {
		return 0;
	}
}

function DB_MAXIMO ($tabla,$campo,$where) {

	if (!$bd) {
		DB_CONECTA();
	}
	
	if (trim($tabla) != "" && $campo != "") {
		$sql = "SELECT MAX(" . $campo . ") FROM " . $tabla;
		if ($where != "") {
			$sql .= " WHERE " . $where;
		}
		
		$sentence = mysql_query($sql);

		if (!$sentence) {
			if (mysql_num_rows($sentence) == 0) {
				return 0;
			} else {
				DB_ERROR("Error al procesar la consulta " . mysql_error());	
			}
		}
		
		if (mysql_num_rows($sentence) == 1) {
			$row = mysql_fetch_array($sentence);
			return $row[0];
		} else {
			return 0;
		}

	} else {
		return 0;
	}
}

function DB_ULTIMO ($tabla,$campo,$where) {
	return DB_MAXIMO($tabla,$campo,$where)+1;
}

function DB_EJECUTA($sql) {
	if (!$bd) {
		DB_CONECTA();
	}
	
	$sentence = mysql_query($sql);
	if (!$sentence) {
		//DB_ERROR("Error al procesar la consulta " . mysql_error());	
		return 0;
	} else {
		$valor = mysql_affected_rows();
		//if ($valor <= 0) {
		//	DB_ERROR("La sentencia SQL " . $sql . " no se ejecutó correctamente." . mysql_error());
		//}
		if ($valor == 0) {
			$valor = 1;
		}
		return $valor;
	}
}

function DB_CONSULTA($sql) {
	
	if (!$bd) {
		DB_CONECTA();
	}

	$sentence = mysql_query($sql);
	if (!$sentence) {
		if (mysql_num_rows($sentence) != 0) {
			DB_ERROR("Error al procesar la consulta " . mysql_error());	
		}
	}
	
	return $sentence;
}

function DB_COMBOBOX($tabla,$clave,$valor,$condicion,$orden,$id,$nombre,$clase,$seleccionado,$nulo,$disabled,$onchange,$opcDefecto) {	

	if (!$bd) {
		DB_CONECTA();
	}

	if ($opcDefecto==""){
		$opcDefecto="-1";
	}

	$sql = "SELECT " . $clave . "," . $valor  . " FROM " . $tabla;
	if ($condicion != "") {
		$sql .= " WHERE " . $condicion;
	} 
	
	if ($orden != "") {
		$sql .= " ORDER BY " . $orden;
	} 
	
	$sentence = mysql_query($sql);
	if (!$sentence) {
		DB_ERROR("Error al procesar la consulta " . mysql_error());
	}

	if (mysql_num_rows($sentence) > 0) {
		if ($disabled != '') { 
			$disabled = "disabled";
		}

		$cad = '<select class="' . $clase . '" ' . $disabled . ' name="' . $nombre . '" id="' . $id . '" onchange="' . $onchange . '">';
				
		if ($nulo != "") {
			$cad = $cad . '<option value="--' . $nulo . '--"></option>';
		} else {
			$cad = $cad . '<option value=""></option>';
		}
		
		while ($row = mysql_fetch_assoc($sentence)) {
			if ($row[$clave] == $seleccionado) {
				$selected = " selected";
			} else {
				$selected = "";
			}

			if ($row[$clave] == $opcDefecto && $selected == ""){
				$selected = " selected";
			}

			$cad .= '<option ' . $selected . ' value="' . $row[$clave] . '">' . $row[$valor]  . '</option>';
		}
		
		$cad .= "</select>";
	} else {
		$cad = '<select ' . $disabled . ' name="' . $nombre . ' id="' . $id . '"></select>';
	}
	
	// Liberar recursos
	//mysql_free_result($sentence);
	
	return $cad;
}

// Sustituido por función PHP in_array
/*function buscar_valor($vector, $valor) {
	$encontrado = false;
	$i = 0;

	while ($i<count($vector) and !$encontrado) {
		if ($vector[$i] == $valor) {
			$encontrado = true;
		} else {
			$i++;
		}
	}
	return $encontrado;
}*/


// Genera tabla de checkbox
function DB_LIST_CHECK($tabla,$clave,$valor,$condicion,$orden,$nombre,$disabled,$vdefecto,$script,$sql_det) {
	
	if (!$bd) {
		DB_CONECTA();
	}
	
	$sql = "SELECT " . $clave . "," . $valor  . " FROM " . $tabla;
	if ($condicion != "") {
		$sql .= " WHERE " . $condicion;
	} 
	
	if ($orden != "") {
		$sql .= " ORDER BY " . $orden;
	} 
	$cad = $sql;	
	
	$sentence = mysql_query($sql);
	if (!$sentence) {
		DB_ERROR("Error al procesar la consulta " . mysql_error());
	}

	if (mysql_num_rows($sentence) > 0) {
		if ($disabled != '') { 
			$disabled = "disabled";
		}
	
		$cad = '<ul class="list-group"' . ' ' . $disabled . '>';
		$i = 1;
		
		while ($row = mysql_fetch_assoc($sentence)) {
			$txt_det="";
			if ($sql_det!=""){
				$sql_det=str_replace("{ID}", $row[$clave], $sql_det);
				//$txt_det=$sql_det;
				$sentence_det = mysql_query($sql_det);
				if (mysql_num_rows($sentence_det) > 0) {
					while ($row_det = mysql_fetch_assoc($sentence_det)) {
						$txt_det.=$row_det[0];
					}
				}

			}
			$cad .= '<li class="list-group-item">';
          	$cad .= '<label style="font-weight: normal;">';
          	if (in_array($row[$clave], $vdefecto)) {
            	$checked = "checked";
            } else {
            	$checked = "";
            }

            $cad .= '<input type="checkbox" name="' . $nombre . '" value="' . $row[$clave] . '" ' . $checked . ' ' . $script . '> ' . $row[$valor] . $txt_det . '</label></li>';
			$i++;            
		}
		
		$cad .= "</ul>";
	} else {
		$cad = '<ul class="list-group"' . ' ' . $disabled . '></ul>';
	}
	
	//mysql_free_result($sentence);
	return $cad;
}


// Genera tabla de checkbox
function DB_LIST_CHECK_OPE_DISPONIBLES($tabla,$clave,$valor,$condicion,$orden,$nombre,$disabled,$vdefecto,$script,$sql_det) {
	
	//$tabla="LIST_PLANIFICACION_JEF INNER JOIN USR_WEB ON USR_WEB.USR_ID=LIST_PLANIFICACION_JEF.Operario";
	//$clave="Operario";
	//$valor="CONCAT_WS(' ',USR_NOM,USR_APE)";
	//$condicion="PEE_ID=".$pee_id." AND JEFE=0";

	if (!$bd) {
		DB_CONECTA();
	}
	
	//$sql = "SELECT " . $clave . "," . $valor  . " FROM " . $tabla;
	$sql = "SELECT " . $clave . "," . $valor  . ", USR_JOR_INI, USR_TRA_INI FROM " . $tabla;
	if ($condicion != "") {
		$sql .= " WHERE " . $condicion;
	} 
	
	if ($orden != "") {
		$sql .= " ORDER BY " . $orden;
	} 
	$cad = $sql;	
	
	$sentence = mysql_query($sql);
	if (!$sentence) {
		DB_ERROR("Error al procesar la consulta " . mysql_error());
	}

	if (mysql_num_rows($sentence) > 0) {
		if ($disabled != '') { 
			$disabled = "disabled";
		}
	
		$cad = '<ul class="list-group"' . ' ' . $disabled . '>';
		$i = 1;
		
		while ($row = mysql_fetch_assoc($sentence)) {
			$txt_det="";
			if ($sql_det!=""){
				$sql_det=str_replace("{ID}", $row[$clave], $sql_det);
				//$txt_det=$sql_det;
				$sentence_det = mysql_query($sql_det);
				if (mysql_num_rows($sentence_det) > 0) {
					while ($row_det = mysql_fetch_assoc($sentence_det)) {
						$txt_det.=$row_det[0];
					}
				}

			}
			$cad .= '<li class="list-group-item">';
          	$cad .= '<label style="font-weight: normal;">';
          	//if (in_array($row[$clave], $vdefecto)) {
            //	$checked = "checked";
            //} else {
            	$checked = "";
            //}

            $est="";
            $dis="";
            
            if ($row['USR_JOR_INI']==0){
            	//date("w")=número de la semana, 1=lunes
	            //date("G")=hora sin ceros
	            if (date("w")==0 || date("w")==6 || date("G")<6 || date("G")>15){
            		//quitar control de jornada
            	} else {
	            	$est="Sin inicio de jornada<br>";
	            	$dis="disabled";
	            }
            } else if ($row['USR_TRA_INI']==1){
            	$est="Pte. finalizar último trabajo<br>";
            	$dis="disabled";
            }
            /*
            //tiene inicio de jornada?
            $ssql="SELECT UJ_USU, UJ_FEC_INI FROM USR_JOR WHERE UJ_FEC_INI>=CURDATE() AND UJ_FEC_FIN IS NULL AND UJ_JOR=1 AND UJ_USU=" . $row[$clave];
            $sentence_jor = mysql_query($ssql);
            if (mysql_num_rows($sentence_jor) == 0) {
            	$est="Sin inicio de jornada<br>";
            	$dis="disabled";
            } else {
            	//tiene algún trabajo abierto?
            	$ssql="SELECT UJ_USU, UJ_FEC_INI FROM USR_JOR WHERE UJ_FEC_INI>=CURDATE() AND UJ_FEC_FIN IS NULL AND UJ_TIP_STOP=1 AND UJ_USU=" . $row[$clave];
            	$sentence_tra = mysql_query($ssql);
            	if (mysql_num_rows($sentence_tra) > 0) {
	            	$est="Pte. finalizar último trabajo<br>";
	            	$dis="disabled";
	            }
            }
            */

            $cad .= '<input type="checkbox" name="' . $nombre . '" ' . $dis . ' value="' . $row[$clave] . '" ' . $checked . ' ' . $script . '> <b>' . $est . '</b> ' . $row[$valor] . $txt_det . '</label></li>';
			$i++;            
		}
		
		$cad .= "</ul>";
	} else {
		$cad = '<ul class="list-group"' . ' ' . $disabled . '></ul>';
	}
	
	//mysql_free_result($sentence);
	return $cad;
}

// Genera tabla de checkbox
function DB_LIST_CHECK_PARTES($condicion,$disabled,$vdefecto,$script,$tipmsg,$op,$campo_hnoc) {
	
	if (!$bd) {
		DB_CONECTA();
	}
	
	$sql = "SELECT *, CONCAT_WS(' ',USR_NOM,USR_APE) AS USUARIO FROM USR_JOR INNER JOIN USR_WEB ON UJ_USU=USR_ID";
	if ($condicion != "") {
		$sql .= " WHERE " . $condicion;
	} 
	
	$cad = $sql;	

	$sentence = mysql_query($sql);
	if (!$sentence) {
		DB_ERROR("Error al procesar la consulta " . mysql_error());
	}

	if (mysql_num_rows($sentence) > 0) {
		if ($disabled != '') { 
			$disabled = "disabled";
		}
		
		$cad = '<script type="text/javascript">';
		$cad .= 'function horas_normales_ope(index){';
        $cad .= 'var hor;';
        $cad .= 'var ext;';
        $cad .= 'var noc;';
        $cad .= 'var aux;';
        $cad .= 'if (document.getElementById("txt_chk_dif_hor"+index+"").value.length==0){';
        $cad .= '  hor=0;';
        $cad .= '} else {';
        $cad .= '  hor=document.getElementById("txt_chk_dif_hor"+index+"").value;';
        $cad .= '}';
        $cad .= 'if (document.getElementById("txt_chk_he"+index+"").value.length==0){';
        $cad .= '  ext=0;';
        $cad .= '} else {';
        $cad .= '  ext=document.getElementById("txt_chk_he"+index+"").value;';
        $cad .= '}';
        $cad .= 'noc=0;';
        if ($campo_hnoc<>""){
	        $cad .= 'if (document.getElementById("txt_chk_hnc"+index+"").value.length==0){';
	        $cad .= '  noc=0;';
	        $cad .= '} else {';
	        $cad .= '  noc=document.getElementById("txt_chk_hnc"+index+"").value;';
	        $cad .= '}';
    	}
        $cad .= 'aux=hor-ext-noc;';
        $cad .= 'if (aux<0){';
        $cad .= '  aux=0;';
/*
        $cad .= '  document.getElementById("txt_chk_hn"+index+"").value=document.getElementById("txt_chk_dif_hor"+index+"").value;';
        $cad .= '  document.getElementById("txt_chk_he"+index+"").value="";';
        $cad .= '  msg = "Las horas Extras no pueden ser superior a: " + hor;';
        if ($tipmsg==2){
        	$cad .= '  document.getElementById("modal_errores_text").innerHTML = msg;';
        	$cad .= '  $("#modal_errores").modal("show");';
        } else {
        	$cad .= '  document.getElementById("modal_text").innerHTML = msg;';
        	$cad .= '  $("#myModal").modal("show");';
        }
        $cad .= '  return 0;';
*/
        $cad .= '}';
        $cad .= 'document.getElementById("txt_chk_hn"+index+"").value=aux.toFixed(2);';
        $cad .= 'document.getElementById("txt_chk_hn_"+index+"").value=aux.toFixed(2);';
      	$cad .= '}';
      	$cad .= '</script>';

		$cad .= '<ul class="list-group"' . ' ' . $disabled . '>';
		$i = 1;
		
		while ($row = mysql_fetch_assoc($sentence)) {
			$cad .= '<li class="list-group-item">';
          	$cad .= '<label style="font-weight: normal;">';
          	if (in_array($row['UJ_USU'], $vdefecto)) {
            	$checked = "checked";
            } else {
            	$checked = "";
            }

            if ($op=="C"){
	            $hor_dif = calcular_tiempo_trasnc_initra($row['UJ_USU']);
	            $hor_ext="";
	            $hor_noc="";

	            // capturar el tiempo de desplazamiento
			    $par_jor_ult_fec=hora_traslado_inicio($row['UJ_USU']);
			    $par_ini_fec=ultima_hora_inicio_parte($row['UJ_USU']);
			    $des_hor1=0;
			    if ($par_jor_ult_fec!=0){
			       $des_hor1=calcular_tiempo_trasnc($par_jor_ult_fec,$par_ini_fec);
			    }
			    $caption ="(máx: " . $hor_dif . "h)";
			    $cad .= '<input type="checkbox" name="chk_pla_ope' . $i . '" id="chk_pla_ope' . $i . '" value="' . $i . '" ' . $checked . ' ' . $script . '> ' . $row['USUARIO'] . '</label>';
			} else {
				$hor_dif = $row['UJ_HNOR'];
				$hor_ext = $row['UJ_HEXT'];
				$hor_noc = $row['UJ_HNOC'];
				$caption ="";
				$cad .= $row['USUARIO'] . '</label>';
			}

            $cad .= '<input type="hidden" id="txt_chk_ope' . $i . '" name="txt_chk_ope' . $i . '" value="' . $row['UJ_USU'] . '">';
            $cad .= '<input type="hidden" id="txt_chk_dif_hor' . $i . '" name="txt_chk_dif_hor' . $i . '" value="' . $hor_dif . '">';
            $cad .= '<input type="hidden" id="txt_chk_hn_' . $i .'" name="txt_chk_hn_' . $i .'" value="' . $hor_dif . '">';
            $cad .= '<input type="hidden" id="txt_chk_hd' . $i .'" name="txt_chk_hd' . $i .'" value="' . $des_hor1 . '">';
            $cad .= '<table><tr><td valign="top">';
            $cad .= '<br>H.Nor: ' . $caption . '<input type="number" class="form-control decimal" name="txt_chk_hn' . $i .'" id="txt_chk_hn' . $i .'" disabled value="' . $hor_dif . '">';
            $cad .= '</td><td>';
            $cad .= '<br>H.Ext: <input type="number" class="form-control decimal" name="txt_chk_he' . $i . '" id="txt_chk_he' . $i . '" onchange="javascript:horas_normales_ope(' . $i . ');" ' . $disabled . ' value="'.$hor_ext.'">';
            if ($campo_hnoc<>""){
            	$cad .= '<br>H.Noc: <input type="number" class="form-control decimal" name="txt_chk_hnc' . $i . '" id="txt_chk_hnc' . $i . '" onchange="javascript:horas_normales_ope(' . $i . ');" value="'.$hor_noc.'">';
            }
            $cad .= '</li></td></tr></table>';

			$i++;            
		}
		
		$cad .= "</ul>";
	} else {
		$cad = '<ul class="list-group"' . ' ' . $disabled . '></ul>';
	}
	
	//mysql_free_result($sentence);
	return $cad;
}


// Genera lista de opciones (con radio buttons)
function DB_LIST_OPTION($tabla,$clave,$valor,$condicion,$orden,$nombre,$disabled,$defecto) {
	
	if (!$bd) {
		DB_CONECTA();
	}
	
	$sql = "SELECT " . $clave . "," . $valor  . " FROM " . $tabla;
	if ($condicion != "") {
		$sql .= " WHERE " . $condicion;
	} 
	
	if ($orden != "") {
		$sql .= " ORDER BY " . $orden;
	} 
	$cad = $sql;	
	
	$sentence = mysql_query($sql);
	if (!$sentence) {
		DB_ERROR("Error al procesar la consulta " . mysql_error());
	}

	if (mysql_num_rows($sentence) > 0) {
		if ($disabled != '') { 
			$disabled = "disabled";
		}
	
		$cad = '<ul class="list-group"' . ' ' . $disabled . '>';
		$i = 1;
		
		while ($row = mysql_fetch_assoc($sentence)) {
			$cad .= '<li class="list-group-item">';
			
          	$cad .= '<label style="font-weight: normal;">';
          	if ($row[$clave] == $defecto) {
            	$checked = "checked";
            } else {
            	$checked = "";
            }

            $cad .= '<input type="radio" id="' . $nombre . '" name="' . $nombre . '" value="' . $row[$clave] . '" ' . $checked . '> ' . $row[$valor] . '</label></li>';
			$i++;            
		}
		
			$cad .= "</ul>";
	} else {
		$cad = '<ul class="list-group"' . ' ' . $disabled . '></ul>';
	}
	
	//mysql_free_result($sentence);
	return $cad;
}




// Muestra todos los artículos del presupuesto asociado al parte de trabajo
// y rellena los datos almacenados en el parte de cantidad-horas por artículo
// Obtiene una tabla de 3 columnas: 1 Descripción, 2 Cantidad, 3 Horas
function DB_PARTE_DETALLE($par_id, $pee_id) {
	
	// Comprueba una partida en el vector $par_art[]
  	// SALIDA: Array asociativo (cantidad => , horas => )
	function buscar_articulo($par_art, $articulo, $capitulo) {
		$encontrado = false;
	    $i = 0;
	    // Inicializamos el vector resultado
	    $vdatos[] = array('cantidad' => '', 'horas' => '');
	    
	    while ($i < count($par_art) && !$encontrado) {
	      if (($par_art[$i]['pa_art'] == $articulo) && ($par_art[$i]['pa_cap'] == $capitulo)) {
	        $vdatos['cantidad'] = $par_art[$i]['pa_can'];
	        $vdatos['horas'] = $par_art[$i]['pa_hor'];
	        
	        $encontrado = true;
	      } else {
	        $i++;
	      }
	    }
	    
	    return $vdatos;
	}

	// Guardar registros guardados en vector asociativo $par_art
    $sentencia_art = DB_CONSULTA("SELECT * FROM PAR_ART WHERE PA_PAR=" . $par_id);
	$par_art = array();
  	while ($row = mysql_fetch_assoc($sentencia_art)) {
    	$par_art[] = array(
      		'pa_art' => $row['PA_ART'],
      		'pa_cap' => $row['PA_CAP'],
      		'pa_can' => $row['PA_CAN'],
      		'pa_hor' => $row['PA_HOR']
    	);
  	}

  	$par_pre = DB_LEE_CAMPO("PLA_ENC_ENC","PEE_PRE","PEE_ID=" . $pee_id);

  	// Guardar planificación en vector asociativo $pla_enc_art
  	$sentencia_art = DB_CONSULTA("SELECT * FROM PLA_ENC_ART WHERE PEA_PEE=" . $pee_id);
  	$pla_enc_art = array();
	while ($row = mysql_fetch_assoc($sentencia_art)) {
    	$pla_enc_art[] = array(
      		'pa_art' => $row['PEA_ART'],
      		'pa_cap' => $row['PEA_CAP'],
      		'pa_can' => $row['PEA_CAN'],
      		'pa_hor' => $row['PEA_HOR']
    	);
  	}  	

  	// Generar tabla con todos los registros a mostrar
	$cad = '<table class="table table-bordered table-condensed" style="background-color:white;">';
    $cad .= '<tr>';
    	$cad .= '<th style="width:80%;">Descripci&oacute;n</th>';
    	$cad .= '<th>Cant.&nbsp;</th>';
  		$cad .= '<th>Horas</th>';
    $cad .= '</tr>';
 	
    $cap = 0;
    $sSQL = "SELECT * FROM LIST_PRESUPUESTOS_DETALLES WHERE PC_PRE=" . $par_pre;
    $sentencia = DB_CONSULTA($sSQL);
    while ($row = mysql_fetch_assoc($sentencia)) {
    	if ($row['PC_CAP'] != $cap) {
        	$cad .= '<tr style="background-color:orange;">';
            	$cad .= '<td colspan="3">Cap. ' . $row['PC_CAP'] . ' - ' . $row['PC_TIT'] . '</td>';
            $cad .= '</tr>';
            $cap = $row['PC_CAP'];
        }
     	
     	if ((integer)$par_id==0) {
     		$varticulo = buscar_articulo($pla_enc_art, $row['PCD_ART'], $row['PC_CAP']);	
     	} else {
     		$varticulo = buscar_articulo($par_art, $row['PCD_ART'], $row['PC_CAP']);	
     	}
     	
     	if ((double)$varticulo['cantidad'] == 0) {
     		$planificado = "";
     	} else {
     		$planificado = " planificado";
     	}
     
     	$cad .= '<tr class="encargado ' . $row['PCD_ENC'] . $planificado . '">';
        	$cad .= '<td>';
          		$cad .= '<p>' . $row['PCD_ART'] . ' - Cap: ' . $row['PC_CAP'] . '</p>';
          		$cad .= '<p>' . $row['PCD_DES'] . '</p>';
          		// Si tiene hecho el replanteo, se muestran las horas de replanteo sino las presupuestadas
          		if ((double)$row['PCD_CAN_REP'] > 0) {
          			$cantidad = $row['PCD_CAN_REP'];
          		} else {
          			$cantidad = $row['PCD_CAN'];
          		}
          		$cad .= '<p>' . 'Cantidad: ' . STR_numero($cantidad) . ' ' . $row['PCD_UD'] . '</p>'; //' - Horas: ' . STR_hora($row['PCD_HOR_REP']) . '</p>';
        	$cad .= '</td>';
        	$cad .= '<td><input type="hidden" name="txt_articulos[]" value="' . $row['PCD_ART'] . '" />
                  	<input type="hidden" name="txt_capitulos[]" value="' . $row['PC_CAP'] . '" />';
        			$varticulo = buscar_articulo($par_art, $row['PCD_ART'], $row['PC_CAP']);
            		$cad .= '<input type="number" class="form-control decimal" name="txt_cantidades[]" size="4em;" value="' . $varticulo['cantidad'] . '" />';
            $cad .= '</td>';
        	$cad .= '<td><input type="time" class="form-control hora" name="txt_horas[]" size="4em;" value="' . STR_hora($varticulo['horas']) . '"/>
        		</td>';
      	$cad .= '</tr>';
    }

    $cad .= '</table>';

    return $cad;
}


function DB_MATERIALES_AVERIAS($par_id, $tipo_trabajo_averia) {
		
	// Comprueba un material en el vector $par_art[]
  	// SALIDA: Array asociativo (cantidad => )
	function buscar_articulo($par_art, $articulo) {
		$encontrado = false;
	    $i = 0;
	    // Inicializamos el vector resultado
	    $vdatos[] = array('cantidad' => '');
	    
	    while ($i < count($par_art) && !$encontrado) {
	      if ($par_art[$i]['pa_art'] == $articulo) {
	        $vdatos['cantidad'] = $par_art[$i]['pa_can'];
	        
	        $encontrado = true;
	      } else {
	        $i++;
	      }
	    }

	    return $vdatos;
	}

	// Todos los trabajos en BT tienen los mismos materiales
	if ($tipo_trabajo_averia == 3 || $tipo_trabajo_averia == 4 || $tipo_trabajo_averia == 5) {
		$tipo_trabajo_averia = 3;
	}

	// Guardar registros guardados en vector asociativo $par_art
    $sentencia_art = DB_CONSULTA("SELECT * FROM PAR_ART WHERE PA_PAR=" . $par_id);
	$par_art = array();
  	while ($row = mysql_fetch_assoc($sentencia_art)) {
    	$par_art[] = array(
      		'pa_art' => $row['PA_ART'],
      		'pa_cap' => $row['PA_CAP'],
      		'pa_can' => $row['PA_CAN'],
      		'pa_hor' => $row['PA_HOR']
    	);
  	}

  	// Generar tabla con todos los registros a mostrar
	$cad = '<table class="table table-bordered table-condensed" style="background-color:white;">';
    $cad .= '<tr>';
    	$cad .= '<th style="width:80%;">Descripci&oacute;n</th>';
  		$cad .= '<th>Cant.</th>';
    $cad .= '</tr>';
 	
    $sSQL = "SELECT * FROM PAR_TIP_AVE_ART WHERE PTAA_PTA=" . $tipo_trabajo_averia . " ORDER BY PTAA_DES";
    $sentencia = DB_CONSULTA($sSQL);
    while ($row = mysql_fetch_assoc($sentencia)) {
     	$varticulo = buscar_articulo($par_art, $row['PTAA_ART']);	
     	
     	/*if ((double)$varticulo['cantidad'] == 0) {
     		$seleccionado = "";
     	} else {
     		$seleccionado = " seleccionado";
     	}*/
     
     	$cad .= '<tr class="material">';
        	$cad .= '<td>';
          		//$cad .= '<p>' . $row['PTAA_ART'] . ' - Unidad: ' . $row['PTAA_UNI'] . '</p>';
          		$cad .= '<p>' . $row['PTAA_DES'] . ' (' . $row['PTAA_UNI'] . ')' . '</p>';
        	$cad .= '</td>';
        	$cad .= '<td>';
        			$cad .= '<input type="hidden" name="txt_articulos[]" value="' . $row['PTAA_ART'] . '" />';
            		$cad .= '<input type="number" class="form-control decimal" name="txt_cantidades[]" size="4em;" value="' . $varticulo['cantidad'] . '" />';
            $cad .= '</td>';
      	$cad .= '</tr>';
    }

    $cad .= '</table>';

    return $cad;
}


// En función del tipo de parte, indica los artículos para colocar cantidades
function DB_PARTE_ARTICULOS_TIPO_PARTE($par_id, $par_tip) {
	// Comprueba un material en el vector $par_art[]
  	// SALIDA: Array asociativo (cantidad => )
	function buscar_articulo($par_art, $articulo) {
		$encontrado = false;
	    $i = 0;
	    // Inicializamos el vector resultado
	    $vdatos[] = array('cantidad' => '');
	    
	    while ($i < count($par_art) && !$encontrado) {
	      if ($par_art[$i]['pa_art'] == $articulo) {
	        $vdatos['cantidad'] = $par_art[$i]['pa_can'];
	        
	        $encontrado = true;
	      } else {
	        $i++;
	      }
	    }

	    return $vdatos;
	}

	// Guardar registros en vector asociativo $par_art
    $sentencia_art = DB_CONSULTA("SELECT * FROM PAR_ART WHERE PA_PAR=" . $par_id);
	$par_art = array();
  	while ($row = mysql_fetch_assoc($sentencia_art)) {
    	$par_art[] = array(
      		'pa_art' => $row['PA_ART'],
      		'pa_cap' => $row['PA_CAP'],
      		'pa_can' => $row['PA_CAN'],
      		'pa_hor' => $row['PA_HOR']
    	);
  	}

  	// Generar tabla con todos los registros a mostrar
	$cad = '<table class="table table-bordered table-condensed" style="background-color:white;">';
    $cad .= '<tr>';
    	$cad .= '<th style="width:80%;">Descripci&oacute;n</th>';
  		$cad .= '<th>Cant.</th>';
    $cad .= '</tr>';
 	
    $sSQL = "SELECT * FROM PAR_TIP_ART WHERE PTA_PT=" . $par_tip . " ORDER BY PTA_DES";
    $sentencia = DB_CONSULTA($sSQL);
    while ($row = mysql_fetch_assoc($sentencia)) {
     	$varticulo = buscar_articulo($par_art, $row['PTA_ART']);
     	     
     	$cad .= '<tr class="mano_obra">';
        	$cad .= '<td>';
          		$cad .= '<p>' . $row['PTA_DES'] . ' (' . $row['PTA_UNI'] . ')' . '</p>';
        	$cad .= '</td>';
        	$cad .= '<td>';
        			$cad .= '<input type="hidden" name="txt_articulos[]" value="' . $row['PTA_ART'] . '" />';
            		$cad .= '<input type="number" class="form-control decimal" name="txt_cantidades[]" size="4em;" value="' . $varticulo['cantidad'] . '" />';
            $cad .= '</td>';
      	$cad .= '</tr>';
    }

    $cad .= '</table>';

    return $cad;
}


// Muestra los materiales de los operarios seleccionados en vope[]
function DB_MATERIALES($par_id, $vope) {

	// Comprueba un material en el vector $par_mat[]
  	// SALIDA: Array asociativo (cantidad => )
	function buscar_articulo($par_mat, $articulo, $operario) {
		$encontrado = false;
	    $i = 0;
	    
	    // Inicializamos el vector resultado
	    $vdatos[] = array('cantidad' => '');
	    
	    while ($i < count($par_mat) && !$encontrado) {
	      if ($par_mat[$i]['pm_art'] == $articulo && $par_mat[$i]['pm_ope'] == $operario) {
	        $vdatos['cantidad'] = $par_mat[$i]['pm_can'];    
	        $encontrado = true;
	      } else {
	        $i++;
	      }
	    }
	    return $vdatos;
	}

	
	// Por ahora no hace nada
	// Comprueba un material en el vector $par_mat[]
  	// SALIDA: Array asociativo (cantidad => )
	function buscar_articulo_ultimo_movimiento($alm_ult_mov, $articulo) {
		$encontrado = false;
	    $i = 0;
	    
	    // Inicializamos el vector resultado
	    $vdatos[] = array('cantidad' => '');
	    
	    while ($i < count($alm_ult_mov) && !$encontrado) {
	      if ($alm_ult_mov[$i]['amo_art'] == $articulo) {
	        $vdatos['cantidad'] = $alm_ult_mov[$i]['amo_can'];
	        $encontrado = true;
	      } else {
	        $i++;
	      }
	    }
		
	    return $vdatos;
	}
	

	// Generar tabla con todos los registros a mostrar
	$cad .= '<table class="table table-bordered table-condensed" style="background-color:white;">';
    $cad .= '<tr>';
    	$cad .= '<th style="width:80%;">Descripci&oacute;n</th>';
  		$cad .= '<th style="width:20%;">Cant.</th>';
    $cad .= '</tr>';

    // Guardar registros en vector asociativo $par_mat
    $sentencia_art = DB_CONSULTA("SELECT * FROM PAR_MAT WHERE PM_PAR=" . $par_id);
	$par_mat = array();
  	while ($row = mysql_fetch_assoc($sentencia_art)) {
    	$par_mat[] = array(
      		'pm_art' => $row['PM_ART'],
      		'pm_ope' => $row['PM_OPE'],
      		'pm_can' => $row['PM_CAN'],
    	);
  	}

    $sSQL = "SELECT * FROM ALM_OPE WHERE ";
    for ($i=0; $i<8; $i++) {
    	$sSQL .= "AO_OPE=" . $vope[$i] . " OR ";
    }
    $sSQL = substr($sSQL,0,strlen($sSQL)-4);
    $sSQL .= " ORDER BY AO_OPE, AO_DES";
    
    $sentencia = DB_CONSULTA($sSQL);
    $operario = 0;
    while ($row = mysql_fetch_assoc($sentencia)) {
     	if ($row['AO_OPE'] != $operario) {
     		$operario = $row['AO_OPE'];
     		$cad .= '<tr style="background-color:orange;">';
     			$cad .= '<td colspan="2">';
     				$cad .= DB_LEE_CAMPO("LIST_OPERARIOS","Nombre", "Codigo=" . $operario);
     			$cad .= '</td>';
     		$cad .= '<tr>';

     		// Guardar Último Movimiento de Almacén del Operario
/*     		$fecha = DB_LEE_CAMPO("ALM_MOV_OPE", "MAX(AMO_FEC)","AMO_OPE=" . $operario);
     		if ($fecha != 0) {
     			// Guardar registros en vector asociativo $par_mat
    			$sentencia_art = DB_CONSULTA("SELECT AMO_ART, AMO_CAN FROM ALM_MOV_OPE WHERE AMO_FEC='" . $fecha . "' AND AMO_OPE=" . $operario);
				$alm_ult_mov = array();
  				while ($row_aux = mysql_fetch_assoc($sentencia_art)) {
    				$alm_ult_mov[] = array(
      					'amo_art' => $row_aux['AMO_ART'],
      					'amo_can' => $row_aux['AMO_CAN'],
    				);
  				}
     		} else {
     			$alm_ult_mov = array();
     		}
*/
     		$alm_ult_mov = array();
     	
     	}

     	$varticulo = buscar_articulo($par_mat, $row['AO_ART'], $row['AO_OPE']);
     	
     	// En función de la opción por defecto, mostrar o no la fila
     	// Cambiar por Último Movimiento
      	if ($row['AO_STK']>0 || $varticulo['cantidad']>0) {
     		$cad .= '<tr class="material">';
     	} else {
     		$cad .= '<tr class="material" style="display:none;">';
     	}
     	
	     	$vart_ult_mov = buscar_articulo_ultimo_movimiento($alm_ult_mov, $row['AO_ART']);
	        $cad .= '<td>';
          		$cad .= '<p>' . $row['AO_DES'] . ' (' . $row['AO_UNI'] . ') (' . $row['AO_ART'] . ')</p>';
          		$cad .= '<p style="float:left;">Stock: ' . $row['AO_STK'] . '</p>';
          		if ($vart_ult_mov['cantidad']>0) {
          			$cad .='<p style="float:right;">&Uacute;lt. Movimiento:' . $var_ult_mov['cantidad'] . '</p>';
          		}
        	$cad .= '</td>';
        	$cad .= '<td>';
            		$cad .= '<input type="hidden" name="txt_mat_operarios[]" value="' . $row['AO_OPE'] . '" />';
            		$cad .= '<input type="hidden" name="txt_mat_cantidades_almacen[]" value="' . $row['AO_STK'] . '" />';
            		
            		$cad .= '<input type="hidden" name="txt_mat_cantidades_ult_movimiento[]" value="' . $vart_ult_mov['cantidad']  . '" />';
            		$cad .= '<input type="hidden" name="txt_mat_articulos[]" value="' . $row['AO_ART'] . '" />';
            		$cad .= '<input type="hidden" name="txt_mat_descripciones[]" value="' . $row['AO_DES'] . '" />';

            	$cad .= '<input type="number" class="form-control decimal" name="txt_mat_cantidades[]" size="4em;" value="' . $varticulo['cantidad'] . '" />';
            $cad .= '</td>';
      	$cad .= '</tr>';
    }

    $cad .= '</table>';
	
    return $cad;

}

function ultima_hora_parte() {
    $hora = DB_LEE_CAMPO("PAR, PAR_DET", "MAX(PAR_HFIN) Horas", "PAR_FEC=curdate() AND PD_OPE=" . $_SESSION['GLB_USR_ID'] . " AND PAR_ID=PD_PAR");
    if ($hora == 0) {
    	return "07:30";
    } else {
    	return STR_hora($hora);
    }
}

?>


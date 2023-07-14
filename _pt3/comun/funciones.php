<?php

// Establece zona horaria a España
date_default_timezone_set("Europe/Madrid");

// Devuelve un valor de año-mes-dia para pasarlo como parámetro por defecto a input type="date"
function STR_html_fecha($fecha) {
	$d = str_pad($fecha[mday], 2, "0",STR_PAD_LEFT);
	$m = str_pad($fecha[mon], 2, "0", STR_PAD_LEFT);
	$y = $fecha[year];
	return $y . "-" . $m . "-" . $d;
}

function STR_fecha($fecha) {
	return date("d/m/Y",strtotime($fecha));
}

// Lee un valor de $hora en formato decimal y lo coloca como hh:mm
function STR_hora($hora) {
	if ($hora == '') {
		return '00:00';
	} else {
		$array_hora = explode(".", $hora);
		if (count($array_hora) == 1) {
			return str_pad($hora,2,"0", STR_PAD_LEFT) . ":00";
		} else {
			return str_pad($array_hora[0], 2, "0", STR_PAD_LEFT) . ":" . str_pad(round(str_pad($array_hora[1], 2, "0", STR_PAD_RIGHT)/100*60), 2, "0", STR_PAD_LEFT);
		}
	}
}

function STR_hora2($hora) {
	$array_fh = explode(" ", $hora);
	if ($hora == '' && count($arra)==1) {
		return '00:00';
	} else {
		$array_hora = explode(":", $array_fh[1]);
		if (count($array_hora) == 1) {
			return $hora;
		} else {
			return str_pad($array_hora[0], 2, "0", STR_PAD_RIGHT) . ":" . str_pad($array_hora[1], 2, "0", STR_PAD_RIGHT);
		}
	}
}

// Formatea un número para representarlo correctamente 1.000,25
function STR_numero($valor, $num_decimales=2, $millares) {
	$simbolo_millar = '';
	
	if ($millares) {
		$simbolo_millar = '.';
	}

	return number_format($valor,$num_decimales,",",$simbolo_millar);
}

// escala la hora cada 15 minutos o toma la real
function STR_fechor_esc15($par_tip){
	
	if ($par_tip == "INI_TRA" || $par_tip == "FIN_TRA") {
		// escalar hora
		$fec_act_hor=date("H");
		$fec_act_min=date("i");

		if ($fec_act_min>=0 && $fec_act_min<=7){
			$fec_act_min=100;
		} else if($fec_act_min>=53 && $fec_act_min<=59){
			$fec_act_min=100;
			$fec_act_hor=$fec_act_hor+1;
		} else if($fec_act_min>=38 && $fec_act_min<=52){
			$fec_act_min=145;
		} else if($fec_act_min>=23 && $fec_act_min<=37){
			$fec_act_min=130;
		} else {
			$fec_act_min=115;
		}
		$fec_escalada = date("Y-m-d ") . $fec_act_hor . ":" . substr($fec_act_min, 1) . ":00";

		// evitar solapamientos con trabajos anteriores o el propio inicio del actual por finalizar
		if ($par_tip == "INI_TRA"){
			$ult_hor_fin=ultima_hora_fin_parte($_SESSION['GLB_USR_ID']);
			if ($ult_hor_fin!=0 && $ult_hor_fin>=$fec_escalada){
				$fec_escalada=$ult_hor_fin;
			}
		} else if($par_tip="FIN_TRA"){
			$ult_hor_ini=ultima_hora_inicio_parte($_SESSION['GLB_USR_ID']);
			if ($ult_hor_ini>=$fec_escalada){
				$fec_escalada=date("Y-m-d H:i:s",strtotime('+15 minute', strtotime($ult_hor_ini)));
			}
		}
		return STR_formato_cadena($fec_escalada);

	} else {
		// hora real
		return STR_formato_cadena(date("Y-m-d H:i:s"));

	}
}

// Prepara cadena para guardarla en MYSQL
function STR_formato_cadena($valor) {
	return "'" . str_replace("'","\'", $valor) . "'";
}

// Prepara número para guardarlo en MYSQL
function STR_formato_numero($valor) {
	return str_replace(",", ".", $valor);
}

// Prepara valor hh:mm para guardarla en MYSQL como decimal
function STR_formato_hora($hora) {
	$array_hora = explode(":", $hora);

	if (count($array_hora) == 0) {
		return "NULL";
	} else if ($array_hora[0] == 0 && $array_hora[1] == 0) {
		return "NULL";
	} else {
		return $array_hora[0] + round($array_hora[1]/60, 2);
	}
}

// Devuelve la diferencia de dos horas en formato decimal
function restar_horas($horaini, $horafin) {
	$horai=substr($horaini,0,2);
	$mini=substr($horaini,3,2);

	if (strlen($horaini) > 5) {
		$segi=substr($horaini,6,2);
	} else {
		$segi = 0;
	}

	$horaf=substr($horafin,0,2);
	$minf=substr($horafin,3,2);
	if (strlen($horafin) > 5) {
 		$segf=substr($horafin,6,2);
 	} else {
 		$segf = 0;
 	}
 
	$ini=((($horai*60)*60)+($mini*60)+$segi);
	$fin=((($horaf*60)*60)+($minf*60)+$segf);
 
	$dif=round(($fin-$ini)/3600, 2);
 
/*	$difh=floor($dif/3600);
	$difm=floor(($dif-($difh*3600))/60);
	$difs=$dif-($difm*60)-($difh*3600);
	return date("H:i:s",mktime($difh,$difm,$difs));*/
	return $dif;
}

// Devuelve el día anterior a las 15:00:00
function fecha_dia_anterior($fecha) {
	$fecha = date('Y-m-d', strtotime($fecha));
	return date('Y-m-d H:i:s', strtotime('-1 day' , strtotime($fecha) + (14 * 60 * 60))) ;
}

//Devuelve el nombre del mes
function mes_nombre($mes_num) {
	switch($mes_num)
	{   
	    case 1:	return "Enero"; break;
	    case 2: return "Febrero"; break;
	    case 3: return "Marzo"; break;
	    case 4: return "Abril"; break;
	    case 5: return "Mayo"; break;
	    case 6: return "Junio"; break;
	    case 7: return "Julio"; break;
	    case 8: return "Agosto"; break;
	    case 9: return "Septiembre"; break;
	    case 10: return "Octubre"; break;
	    case 11: return "Noviembre"; break;
	    case 12: return "Diciembre"; break;
	}

}

//módulo de inspecciones maquetación de registro
function inspecciones_registro($nom_campo, $col1, $col2, $tipo) {
	$ancho = 30;
	if ($tipo==1){
		$ancho = 85;
	};
	$content = '<div class="form-group">';
	$content .= '<table width="100%"><tr><td width="'.$ancho.'%">';
	$content .= '<label for="' . $nom_campo . '" class="col-lg-2">' . $col1 . ':</label>';
	$content .= '</td><td valign="top">';
	$content .= $col2;
	$content .= '</td></tr></table>';
	$content .= '</div>';  
	return $content;
}

function calcular_tiempo_trasnc_initra($usuario){
	//$fecha_hora_actual = date("Y-m-d H:i:s");
	$fecha_hora_actual = str_replace("'","",STR_fechor_esc15("FIN_TRA"));
    $fecha_dia_anterior = fecha_dia_anterior(date("Y-m-d"));
    $uj_fec_ini = ultima_hora_inicio_parte($usuario);

   	$hor_dif = calcular_tiempo_trasnc($uj_fec_ini,$fecha_hora_actual);
   	$hor_des=0;
   	$hor_des = horas_descanso($usuario,$uj_fec_ini,$fecha_hora_actual);
    return $hor_dif-$hor_des;
    //return $hor_des;
}

function horas_descanso($usuario,$fecha_desde,$fecha_hasta){
	$hdes=0;
	//identificar si al usuario ya se le ha descontado el tiempo de desayuno
	$hdesayuno=0.25;
	
	$sql = "SELECT * FROM USR_JOR WHERE UJ_USU=".$usuario." AND  UJ_TIP_STOP=2 AND ((UJ_FEC_INI<='".$fecha_desde."' AND UJ_FEC_FIN>='".$fecha_desde."') OR (UJ_FEC_INI<='".$fecha_hasta."' AND UJ_FEC_FIN>='".$fecha_hasta."') OR (UJ_FEC_INI>='".$fecha_desde."' AND UJ_FEC_FIN<='".$fecha_hasta."')) ORDER BY UJ_FEC_INI";
	$sentencia = DB_CONSULTA($sql);
	// I=$fecha_desde trabajo  / F=$fecha_hasta trabajo
	//    I------F		trabajo
	//  ..|.. 	 |		caso 1 (descanso)
	//    |    ..|..	caso 2 (descanso)
	//    |  ..  |		caso 3 (descanso)
	$hdes=0;
	while ($row = mysql_fetch_assoc($sentencia)) {
		if ($row['UJ_FEC_INI']<=$fecha_desde && $row['UJ_FEC_FIN']>=$fecha_desde && $row['UJ_FEC_FIN']<=$fecha_hasta){
			$hdes=$hdes+calcular_tiempo_trasnc($fecha_desde,$row['UJ_FEC_FIN']);
		} else if ($row['UJ_FEC_INI']>=$fecha_desde && $row['UJ_FEC_INI']<=$fecha_hasta && $row['UJ_FEC_FIN']>=$fecha_hasta){
			$hdes=$hdes+calcular_tiempo_trasnc($row['UJ_FEC_INI'],$fecha_hasta);
		} else if ($row['UJ_FEC_INI']>=$fecha_desde && $row['UJ_FEC_FIN']<=$fecha_hasta){
			$hdes=$hdes+calcular_tiempo_trasnc($row['UJ_FEC_INI'],$row['UJ_FEC_FIN']);
		}

	}
	//descontar los 15 minutos reglamentarios de desayuno
	$hdes=$hdes-$hdesayuno;
	if ($hdes<0){
		$hdes=0;
	}
	if ($hdesayuno>0){
		//marcar que este usuario ya se le ha descontado el desayuno hoy

	}
	return $hdes;
}

function ultima_hora_inicio_parte($usuario) {
	// proyectar a 45 minutos vista la hora actual para cubrir los escalados de horas
	//$fecha_hora_actual = date("Y-m-d H:i:s");
	$fecha_hora_actual = date("Y-m-d H:i:s",strtotime ( '+45 minute' , strtotime (date("Y-m-d H:i:s"))));
    $fecha_dia_anterior = fecha_dia_anterior(date("Y-m-d"));
    $sql = "SELECT * FROM USR_JOR WHERE UJ_USU=" . $usuario . " AND UJ_JOR=0 AND UJ_TIP_STOP=1 AND UJ_FEC_INI>=" . STR_formato_cadena($fecha_dia_anterior) . " AND UJ_FEC_INI<=" . STR_formato_cadena($fecha_hora_actual) . " AND UJ_FEC_FIN IS NULL ORDER BY UJ_FEC_INI DESC";
    $sentencia = DB_CONSULTA($sql);
    if (mysql_num_rows($sentencia) >= 1) {
	  	$row = mysql_fetch_assoc($sentencia);
	  	return $row['UJ_FEC_INI'];
	  } else {
	  	return date("Y-m-d H:i:s");
	  }
}

function ultima_hora_fin_parte($usuario) {
	// proyectar a 45 minutos vista la hora actual para cubrir los escalados de horas
	//$fecha_hora_actual = date("Y-m-d H:i:s");
	$fecha_hora_actual = date("Y-m-d H:i:s",strtotime ( '+45 minute' , strtotime (date("Y-m-d H:i:s"))));
    $fecha_dia_anterior = fecha_dia_anterior(date("Y-m-d"));
    $sql = "SELECT * FROM USR_JOR WHERE UJ_USU=" . $usuario . " AND UJ_JOR=0 AND UJ_TIP_STOP=1 AND UJ_FEC_INI>=" . STR_formato_cadena($fecha_dia_anterior) . " AND UJ_FEC_INI<=" . STR_formato_cadena($fecha_hora_actual) . " AND NOT UJ_FEC_FIN IS NULL ORDER BY UJ_FEC_FIN DESC";
    $sentencia = DB_CONSULTA($sql);
    if (mysql_num_rows($sentencia) >= 1) {
	  	$row = mysql_fetch_assoc($sentencia);
	  	return $row['UJ_FEC_FIN'];
	  } else {
	  	return 0;
	  }
}

function hora_inicio_jornada($usuario){
	$fecha_hora_actual = date("Y-m-d H:i:s");
    $fecha_dia_anterior = fecha_dia_anterior(date("Y-m-d"));
	$jor_ini=DB_LEE_CAMPO("USR_JOR", "UJ_FEC_INI", "UJ_USU=" . $usuario . " AND UJ_JOR=1 AND UJ_FEC_INI>=" . STR_formato_cadena($fecha_dia_anterior) . " AND UJ_FEC_INI<=" . STR_formato_cadena($fecha_hora_actual) . " AND UJ_FEC_FIN IS NULL");
    if ($jor_ini!=""){
      	return $jor_ini;
    } else {
    	return 0;
    }
}

function hora_traslado_inicio($usuario){
	$fec_aux = hora_inicio_jornada($usuario);
    $par_jor_ult_fec=$fec_aux;
    $fec_aux = ultima_hora_fin_parte($usuario);
    if ($fec_aux!=0){$par_jor_ult_fec=$fec_aux;}
    return $par_jor_ult_fec;
}

//devuelve el total de horas transcurridas en centesimal (1,5 = 1h y 30m)
function calcular_tiempo_trasnc($fecha_desde,$fecha_hasta){
	/*
	// separar la fecha de la hora
	$sep_fh[1]=split(' ',$fecha_desde);
	$sep_fh[2]=split(' ',$fecha_hasta);
	// quedarnos solo con la hora
	$hora1=$sep_fh[1][1];
	$hora2=$sep_fh[2][1];
	// separar cada elemento de la hora
	$separar[1]=split(':',$hora1);
	$separar[2]=split(':',$hora2);

	$total_minutos_trasncurridos[1] = ($separar[1][0]*60)+$separar[1][1];
	$total_minutos_trasncurridos[2] = ($separar[2][0]*60)+$separar[2][1];
	$total_minutos_trasncurridos = $total_minutos_trasncurridos[2]-$total_minutos_trasncurridos[1];

	return round($total_minutos_trasncurridos/60,2);
	*/
	return round(minutosTranscurridos($fecha_desde,$fecha_hasta)/60,2);
}

function minutosTranscurridos($fecha_i,$fecha_f){
	$minutos = (strtotime($fecha_i)-strtotime($fecha_f))/60;
	$minutos = abs($minutos); $minutos = floor($minutos);
	return $minutos;
	}

//devuelve el valor único para pee sin planificar
function nuevo_contador_sinplanificar($usuario){
	// localizar el código mínimo existente en pee como usuario jefe
	$val = DB_LEE_CAMPO("USR_JOR","MIN(UJ_PEE)","UJ_PEE LIKE '-" . $usuario . "%' AND UJ_PLA_JEF=1");
	if ($val==0){
		$val=($usuario . str_replace(":","",date("H:i:s")))*-1;
	}else{
		$val=$val-1;
	}
	return $val;
}

?>
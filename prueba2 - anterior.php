<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no"/>
    <title>Prueba de fechas</title>

	<?php
    // Establece zona horaria a España
	date_default_timezone_set("Europe/Madrid");

    
	// Devuelve el último viernes de la fecha indicada para los fines de semana y el lunes
	function fecha_viernes($fecha) {
		
		$fecha = date('Y-m-d', strtotime($fecha));
		
		$dia_semana = date('N', strtotime($fecha));
		if ($dia_semana < 5 && $dia_semana > 1) {
			return date('Y-m-d H:i:s', strtotime($fecha) + (15 * 60 * 60));
			//date('Y-m-d', strtotime('+1 week'))
		} else {
			if ($dia_semana >= 5) {
				$snum_dias = '-' . ($dia_semana - 5);
			} else {
				$snum_dias = '-3'; // de lunes al viernes anterior
			}
			return date('Y-m-d H:i:s', strtotime($snum_dias . ' day' , strtotime($fecha) + (15 * 60 * 60))) ;
		}
	}

    ?>
</head>

<body>
<p>Fecha Viernes <?= fecha_viernes(date("Y-m-d H:i:s"));?></p>
</body>

</html>
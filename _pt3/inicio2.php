<!DOCTYPE html>
<hmtl lang="es">

<head>
	<title>Bienvenido a Moneleg-Partes de Trabajo</title>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0">
	
	<link href="bootstrap-3.2.0/css/bootstrap.min.css" rel="stylesheet" media="screen">
	<link rel="stylesheet" href="plugins/font-awesome/css/font-awesome.css">
	<link rel="stylesheet" type="text/css" href="css/framework.css" media="screen">	
  
  <style>
      td.detalle_fila {
          background-color:white;
          border-top:1px solid gray;
          border-bottom:1px solid gray;
      }
      
      td.detalle_fila:hover{
          background-color:#F2F2F2;
          cursor: pointer;
      }

      td.id_registro {
          font-weight: bold;
          color: #428BCA; //Azul
      }
  </style>

  <script type="text/javascript">
    function ejecuta(pee_old,pee_new){
      document.location.href ="inicio3.php?old="+pee_old+"&new="+pee_new;
    }

  </script>
  <?php
    include "comun/db_con.php";
    include "comun/funciones.php";
    session_start();

    if (!isset($_SESSION['GLB_USR_ID'])) {
      php_redirect('index.php');
    }

    $sentencia = DB_CONSULTA("SELECT PT_WEB, USR_MUL_PAR FROM USR_WEB, PAR_TIP WHERE USR_ID=" . $_SESSION['GLB_USR_ID'] . " AND PT_ID=USR_PAR_HAB");
    if (mysql_num_rows($sentencia) == 1) {
      $row = mysql_fetch_assoc($sentencia);
      if ($row["USR_MUL_PAR"] == 1) {
        $nombre_pagina = "nuevo_parte.php?op=C";
      } else {
        $nombre_pagina = $row['PT_WEB'] . "?op=C";
      }
    } else {
      // Parte General
      $nombre_pagina = "nuevoparte.php?op=C";
    }

    $sentencia2 = DB_CONSULTA("SELECT * FROM USR_JOR WHERE UJ_USU=" . $_SESSION['GLB_USR_ID'] . " AND UJ_FEC_FIN IS NULL AND UJ_TIP_STOP=1 ORDER BY UJ_ID DESC");
    if (mysql_num_rows($sentencia) == 1) {
      $row = mysql_fetch_assoc($sentencia2);
      if ($row['UJ_PLA_JEF'] ==0) {
        //añadirse a parte con jefe
        $nombre_pagina = "nuevoparte_basic.php?op=C";
      } elseif ($row['UJ_PEE']>0) {
        //parte planificación
        $nombre_pagina = "nuevoparte_planificacion.php?op=C";
      }

    }


    // capturar pee_id del trabajo en curso
    $trabajos = "";
    $fecha_hora_actual = date("Y-m-d H:i:s");
    $fecha_dia_anterior = fecha_dia_anterior(date("Y-m-d"));
    $pee_id = DB_LEE_CAMPO("USR_JOR","UJ_PEE","UJ_USU=" . $_SESSION['GLB_USR_ID'] . " AND UJ_JOR=0 AND UJ_TIP_STOP=1 AND UJ_FEC_INI>=" . STR_formato_cadena($fecha_dia_anterior) . " AND UJ_FEC_INI<=" . STR_formato_cadena($fecha_hora_actual) . " AND UJ_FEC_FIN IS NULL");
    $jef_tra = DB_LEE_CAMPO("USR_JOR","UJ_PLA_JEF","UJ_USU=" . $_SESSION['GLB_USR_ID'] . " AND UJ_JOR=0 AND UJ_TIP_STOP=1 AND UJ_FEC_INI>=" . STR_formato_cadena($fecha_dia_anterior) . " AND UJ_FEC_INI<=" . STR_formato_cadena($fecha_hora_actual) . " AND UJ_FEC_FIN IS NULL");
    // solo entrar mostrar planificación si es jefe del trabajo actual
    if ($pee_id<0 && $jef_tra!=0){
      //trabajo sin planificar
      $sql = "SELECT * FROM LIST_PLANIFICACION_JEF";
      $sql .= " WHERE Operario=" . $_SESSION['GLB_USR_ID'] . " AND Fecha=CURDATE()";
      $sql .= " AND Parte IS NULL";
      $sql .= " AND JEFE=1";
      $rst = DB_CONSULTA($sql);
      $numero = mysql_num_rows($rst);
      while ($row = mysql_fetch_assoc($rst)){
        $trabajos.= '<tr><td class="detalle_fila" onclick="javascript: ejecuta('.$pee_id.','.$row['PEE_ID'].');"><table style="width:100%;"><tr><td class="id_registro"><strong>'.$row['Presupuesto'].'</td><td align="right">'.STR_fecha($row['Fecha']).'</td></tr><tr><td colspan="2">'.$row['Descripción'].'</td></tr><tr><td colspan="2">'.$row['Dirección'].' / '.$row['Población'].'</td></tr><tr><td colspan="2"><strong>ASIGNAR Y FINALIZAR TRABAJO</strong></td></tr></table></td></tr>';
      }
    }


  ?>
</head>

<body class="framework">
	
    <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
      		<span class="navbar-left"><a href="javascript: window.location.href='principal.php';" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
          <span class="navbar-right" style="visibility: hidden;"><a href="#" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
          <p class="titulo">Cargando parte</p>
  	</nav>
    <input type="hidden" name="" id="txt" value="<?php echo $nombre_pagina; ?>">

    <?php
    if ($numero>0){
      ?>
    <ul class="lista-iconos">
      <!--
      <li class="btn-primary">
        <a href="<?php echo $nombre_pagina;?>?op=C" class="icono"><i class="fa fa-file-text fa-3x"></i>Nuevo Parte</a>
      </li>
      <li class="btn-primary">
        <a href="list_planificacion.php" class="icono"><i class="fa fa-calendar-o fa-3x"></i>Planificaci&oacute;n</a>
      </li>
      -->
      <li class="btn-primary">
        <a href="<?php echo $nombre_pagina;?>" class="icono"><i class="fa fa-file-text fa-3x"></i>FINALIZAR SIN PLANIFICAR</a>
      </li>
    </ul>

    <div id="capa_planificacion" style="display:block;">
    <table class="table" style="margin:5px; width: 98%;">
      <tr><td><strong>ASIGNAR TRABAJO ACTUAL A UNO PLANIFICADO</strong></td></tr>
      <?php echo $trabajos; ?>
    </table>
    </div>
    <?php
      } else {
        echo '<script type="text/javascript">document.location.href = document.getElementById("txt").value;</script>';
      }
      ?>

 </div>
</body>

<!--
<script type="text/javascript">
  document.location.href = document.getElementById("txt").value;
</script>
-->
</html>
<!DOCTYPE html>
<hmtl lang="es">

<head>
	<title>Bienvenido a Moneleg-Partes de Trabajo</title>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0">
	
	<link href="bootstrap-3.2.0/css/bootstrap.min.css" rel="stylesheet" media="screen">
	<link rel="stylesheet" href="plugins/font-awesome/css/font-awesome.css">
	<link rel="stylesheet" type="text/css" href="css/framework.css" media="screen">	
  
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
        $nombre_pagina = "nuevo_parte.php";
      } else {
        $nombre_pagina = $row['PT_WEB'];
      }
    } else {
      // Parte General
      $nombre_pagina = "nuevoparte.php";
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

  ?>
</head>

<body class="framework">
	
    <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
      		<span class="navbar-left"><a href="javascript: window.location.href='principal.php';" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
          <span class="navbar-right" style="visibility: hidden;"><a href="#" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
          <p class="titulo">Cargando parte</p>
  	</nav>
    <input type="hidden" name="" id="txt" value="<?php echo $nombre_pagina; ?>">
  	<ul class="lista-iconos">
      <!--
  		<li class="btn-primary">
  			<a href="<?php echo $nombre_pagina;?>?op=C" class="icono"><i class="fa fa-file-text fa-3x"></i>Nuevo Parte</a>
  		</li>
      <li class="btn-primary">
        <a href="list_planificacion.php" class="icono"><i class="fa fa-calendar-o fa-3x"></i>Planificaci&oacute;n</a>
      </li>
      -->
    </ul>
 </div>
</body>

<script type="text/javascript">
  document.location.href = document.getElementById("txt").value;
</script>
</html>
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
    session_start();
    
    if (!isset($_SESSION['GLB_USR_ID'])) {
      php_redirect('index.php');
    }

    php_redirect("principal.php");

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
  ?>
</head>

<body class="framework">
	
    <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
      		<p class="titulo">Inicio</p>
  	</nav>
  	
  	<ul class="lista-iconos">
  		<li class="btn-primary">
  			<a href="<?php echo $nombre_pagina;?>?op=C" class="icono"><i class="fa fa-file-text fa-3x"></i>Nuevo Parte</a>
  		</li>
  		<li class="btn-primary">
  			<a href="list_partes.php" class="icono"><i class="fa fa-edit fa-3x"></i>Editar Parte</a>
  		</li>
  		<li class="btn-primary">
        <a href="list_planificacion.php" class="icono"><i class="fa fa-calendar-o fa-3x"></i>Planificaci&oacute;n</a>
      </li>
      <li class="btn-primary">
        <a href="documentacion.php" class="icono"><i class="fa fa-book fa-3x"></i>Documentaci&oacute;n</a>
      </li>

      <!-- Opción incluida en la aplicación -->
      <li>
        <a href="#" data-toggle="" class="icono">
          <i class="fa fa-file fa-3x"></i>
          <span class="text-white text-center">Configurable por tipo de parte</span>
        </a>
      </li>

      <li class="btn-primary">
        <a href="cerrar_sesion.php" class="icono"><i class="fa fa-sign-out fa-3x"></i>Salir</a>
      </li>
    </ul>
 </div>
</body>

</html>
<?php
	session_start();
  if (!isset($_SESSION['GLB_USR_ID'])) {
    php_redirect('../index.php');
  }
?>

<!DOCTYPE html>
<html lang="es">
<head>
	<title>Moneleg Partes de Trabajo</title>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0">

	<link rel="stylesheet" href="../bootstrap-3.2.0/css/bootstrap.min.css" media="screen">
	<link rel="stylesheet" href="../font-awesome-4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="../css/framework.css" type="text/css" media="screen">	
</head>

<body class="framework">
  <div class="container">
    <!-- Inicio capa de contenidos -->
    <div id="capa_contenidos">   	  
      <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
          <span class="navbar-left"><a href="../index.php" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
          <p class="titulo">Inspecciones</span>
        <span class="navbar-rigth" style="visibility:hidden;"><a href="#" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Sig.</a></span>
    </nav>
    	
    	<ul class="lista-botones">
    		<li>
    			<a href="inspeccion.php?id=0&op=C" data-toggle="" class="icono btn btn-primary btn-block" id="btn_nueva_inspeccion"><i class="fa fa-file-o fa-3x"></i>Nueva Inspección</a>
    		</li>
    		<li>
    			<a href="list_inspecciones.php" data-toggle="" class="icono btn btn-primary btn-block" id="btn_editar_inspeccion"><i class="fa fa-file-text-o fa-3x"></i>Editar Inspección</a>
    		</li>
    	</ul>
    </div>
    <!-- ./capa_contenidos -->
  </div>
  <!-- ./container -->

  <script src="js/jquery-1.11.0.min.js"></script>
  <script type="text/javascript" src="js/ajax.js"></script>
  <script type="text/javascript" src="js/validacion.js"></script>
</body>

</html>
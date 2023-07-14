<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1">
    
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp"></script>
    <script type="text/javascript" src="js/ajax.js"></script>
    <script type="text/javascript" src="js/validacion.js"></script>

    <title>Moneleg - Partes de Trabajo</title>
  
    <!-- CSS de Bootstrap -->
    <!--<link href="bootstrap-3.2.0/css/bootstrap.min.css" rel="stylesheet" media="screen">-->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet" media="screen">
    <link rel="stylesheet" href="plugins/font-awesome/css/font-awesome.css">
    <link rel="stylesheet" type="text/css" href="css/framework.css" media="screen">
    
    <!-- librerías opcionales que activan el soporte de HTML5 para IE8 -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <style type="text/css">

      .item_detalle:hover {
        background-color: #EEE;
        cursor: hand;
      }

    .panel-heading .accordion-toggle:after {
        /* symbol for "opening" panels */
        font-family: 'Glyphicons Halflings';  /* essential for enabling glyphicon */
        content: "\e114";    /* adjust as needed, taken from bootstrap.css */
        float: right;        /* adjust as needed */
        color: white;         /* adjust as needed */
    }
    .panel-heading .accordion-toggle.collapsed:after {
        /* symbol for "collapsed" panels */
        content: "\e080";    /* adjust as needed, taken from bootstrap.css */
    }

    </style>

    <?php
      include "comun/funciones.php";
      include "comun/db_con.php";
      
      session_start();

      if (!isset($_SESSION['GLB_USR_ID'])) {
        //php_redirect('index.php');
      }
            
    ?>
    
  <script type="text/javascript">

    function valida_enter(e) {
      var tecla;

      tecla=(document.all) ? e.keyCode : e.which; 
      if (tecla == 13) {
        cargar('list_documentacion2.php?criterio=' + document.formulario.txt_buscar.value,'capa_documentacion');  
        document.getElementById("cmd_buscar").focus();
      }
    }

    function open_window(path) {
      //newWindow(path, "Documentación");
      window.location.href = path;
    }

    window.onload = function() {
      cargar('list_documentacion2.php','capa_documentacion');
    }

  </script>
</head>

<body class="framework">
	<div class="container">
    <form name="formulario" id="formulario" class="form-horizontal" role="form" method="POST" action="" enctype="multipart/form-data" onsubmit="return false;">
    
    <!-- Inicio Contenido -->
      <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
              <span class="navbar-left"><a href="inicio.php" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
              <span class="navbar-right" style="visibility:hidden;"><a href="javascript: habilitar_capa('nuevoparte2');" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
              <p class="titulo">Documentaci&oacute;n</span>
      </nav>
      
      <div class="bloque" id="capa_filtros">
        <div class="input-group bloque-reducido">
          <input type="text" class="form-control" name="txt_buscar" id="txt_buscar" placeholder="Descripci&oacute;n" onkeypress="valida_enter(event);" value="">
          <span class="input-group-btn">
            <span class="btn btn-default" onclick="javascript: document.formulario.txt_buscar.value='';document.formulario.txt_buscar.focus();cargar('list_documentacion2.php?criterio=','capa_documentacion');"><i class="fa fa-times"></i></span>
          </span>
          <span class="input-group-btn">
            <button class="btn btn-primary" type="button" id="cmd_buscar" onclick="javascript: cargar('list_documentacion2.php?criterio=' + document.formulario.txt_buscar.value,'capa_documentacion');">Buscar</button>
          </span>
        </div>
      </div>
      
      <div id="capa_documentacion"></div>
    
    </form>
  </div> <!-- Fin .container -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
  <!--<script type="text/javascript" src="bootstrap-3.2.0/js/bootstrap.min.js"></script>-->
</body>

</html>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1">
    
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp"></script>
    <script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
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
        cursor: pointer;
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
      $sdoc=$_REQUEST['doc'];
      if ($sdoc==""){
      	$sdoc="0";
      }

  		$sSQL = "SELECT DOC_PREV.*, DOC_USR_LOG.DUL_IDE FROM DOC_USR_CFG LEFT JOIN DOC_USR_LOG ON (DUL_FEC>=DUC_FEC_INI AND DUL_FEC<=DUC_FEC_FIN AND DUL_FEC>=CURDATE() AND DUL_FEC<CURDATE()+1 AND (DUL_USR=DUC_USR OR DUL_USR=" . $_SESSION['GLB_USR_ID'] . ") AND DUL_DOC=DUC_DOC) INNER JOIN DOC_PREV ON DOC_ID=DUC_DOC WHERE (DUC_USR=" . $_SESSION['GLB_USR_ID'] . " OR DUC_USR=-1) AND (DUC_FEC_INI<=CURDATE() AND DUC_FEC_FIN>=CURDATE())";
    	$sentencia = DB_CONSULTA($sSQL);
    	$bexi=0;
      	while ($row = mysql_fetch_assoc($sentencia)) {
      		if ($row["DUL_IDE"]==''){
          		$bexi = 1;
          	}
      	}
      	echo $bexi;
      	if ($bexi==0){	
          	php_redirect("principal.php");
        }

    ?>
    
  <script type="text/javascript">

    function valida_enter(e) {
      var tecla;

      tecla=(document.all) ? e.keyCode : e.which; 
      if (tecla == 13) {
        cargar('list_documentacion_diaria.php?criterio=' + document.formulario.txt_buscar.value,'capa_documentacion');  
        document.getElementById("cmd_buscar").focus();
      }
    }

    function open_window(path) {
      newWindow(path, "Documentación");
      //window.location.href = path;
    }

    window.onload = function() {
    	var sdoc;
    	sdoc = <?php echo($sdoc); ?>;
    	cargar('list_documentacion_diaria.php?usr=' + <?php echo($_SESSION['GLB_USR_ID']); ?> + '&doc=' + sdoc,'capa_documentacion');
    }

  </script>

</head>

<body class="framework">
	<div class="container">
    <form name="formulario" id="formulario" class="form-horizontal" role="form" method="POST" action="" enctype="multipart/form-data" onsubmit="return false;">
    
    <!-- Inicio Contenido -->
      <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
              <p class="titulo">Documentaci&oacute;n a Revisar</span>
      </nav>
      
      <div class="bloque" id="capa_filtros">
        <div class="input-group bloque-reducido">
          Es necesario revisar la siguiente documentación antes de comenzar a trabajar
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
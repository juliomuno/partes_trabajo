<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1">
    
    <script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
    <script type="text/javascript" src="../js/ajax.js"></script>
    <script type="text/javascript" src="../js/validacion.js"></script>

    <title>Moneleg - Partes de Trabajo</title>
 
    <!-- CSS de Bootstrap -->
    <link href="../bootstrap-3.2.0/css/bootstrap.min.css" rel="stylesheet" media="screen">
    <link rel="stylesheet" href="../plugins/font-awesome/css/font-awesome.css">
    <link rel="stylesheet" type="text/css" href="../css/framework.css" media="screen">
    
    <!-- librerías opcionales que activan el soporte de HTML5 para IE8 -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <?php
      include "../comun/funciones.php";
      include "../comun/db_con.php";
      
      session_start();

      if (!isset($_SESSION['GLB_USR_ID'])) {
        php_redirect('index.php');
      }

      $par_tip = $_REQUEST['par_tip'];
      if ($par_tip == '') {
        // Coger tipo de parte por defecto
        $sentencia = DB_CONSULTA("SELECT USR_PAR_HAB FROM USR_WEB WHERE USR_ID=" . $_SESSION['GLB_USR_ID']);
        if (mysql_num_rows($sentencia) == 1) {
          $row = mysql_fetch_assoc($sentencia);
          
          $par_tip = $row["USR_PAR_HAB"];
        } else {
          // Parte General
          $par_tip = 1;
        }
      } else {
        // Redireccionar al parte seleccionado
        $web = DB_LEE_CAMPO("PAR_TIP", "PT_WEB","PT_ID=" . $par_tip);
        php_redirect($web . "?op=C");
      }
    ?>            

    <script type="text/javascript">
      function getRadioButtonSelectedValue(ctrl)
      {
        for(i=0;i<ctrl.length;i++)
          if(ctrl[i].checked) return ctrl[i].value;
      }
      
      function procesar() {
        document.location.href="nuevo_parte.php?par_tip=" + getRadioButtonSelectedValue(document.formulario.chk_tip_par);;
      }
    </script>
</head>

<body class="framework">
	<div class="container">
    <form name="formulario">
    <!-- Inicio Nuevo Parte 1/7 -->
    <div id="nuevoparte1">
      <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
              <span class="navbar-left"><a href="javascript: window.history.back();" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
              <span class="navbar-right"><a href="javascript: procesar();" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
              <p class="titulo">Nuevo Parte</span>
      </nav>
      
      <h4>Seleccione tipo de Parte</h4>
      <?php echo DB_LIST_OPTION("LIST_TIPOS_PARTES","Codigo","Nombre","","Nombre","chk_tip_par","", $par_tip);?>
    
    </div>    
    </form>
  </div> <!-- Fin .container -->
</body>

</html>
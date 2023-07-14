<?php
  include "comun/db_con.php";

  $usuario = $_REQUEST['usuario'];
  $password = $_REQUEST['password'];

  if (!isset($usuario)) {
    if(isset($_COOKIE['usuario_cookie'])) { 
      $usuario = $_COOKIE['usuario_cookie'];
    }
    $msg_error = "";
  } else {
    if ($usuario == '' and $password == '') {
      $msg_error = "";
    } else {
      $sql = "SELECT USR_ID, USR_NOM, USR_APE, USR_NEW, USR_OFI FROM USR_WEB WHERE USR_LOG = '" . $usuario . "' AND USR_PWD = '" . $password . "'";
      $sentencia = DB_CONSULTA($sql);
      if (!$sentencia) {
        
      } else {
        if (mysql_num_rows($sentencia) == 1) {
          $row = mysql_fetch_assoc($sentencia);
          session_start();
          $_SESSION['GLB_USR_ID'] = $row['USR_ID'];
          $_SESSION['GLB_USR_NOM'] = $row['USR_NOM'] . ' ' . $row['USR_APE'];
          $_SESSION['GLB_USR_OFI'] = $row['USR_OFI'];
          setcookie('usuario_cookie', $usuario);
          if($row['USR_NEW']!=1){
            php_redirect("inicio.php");
          } else {
            php_redirect("_pt2/inicio.php");
          }
        } else {
          $msg_error = "Usuario/Contrase&ntilde;a incorrectos";
        }
      }
    }
  }
?>

<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1">
    <title>Moneleg - Partes de Trabajo</title>
 
    <!-- CSS de Bootstrap -->
    <link href="bootstrap-3.2.0/css/bootstrap.min.css" rel="stylesheet" media="screen">
    <link href="bootstrap-3.2.0/css/bootstrap-theme.css" rel="stylesheet" media="screen">
    <link rel="stylesheet" href="plugins/font-awesome/css/font-awesome.css">
     <link rel="stylesheet" type="text/css" href="css/framework.css" media="screen">
 
    <!-- librerías opcionales que activan el soporte de HTML5 para IE8 -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <script type="text/javascript">
        window.onload = function() {
          document.formulario.usuario.focus();

          if ('<?php echo $msg_error;?>' != '') {
            document.getElementById("error").className = "error alert alert-danger";
          } else {
            document.getElementById("error").className = "";
          }
        }
    </script>
  </head>
  
  <body class="login">
    <div class="container">
    	<h2>Moneleg</h2>
		  <h4>Partes de Trabajo</h4>
		  <div class="sublogin">
			<form class="form-horizontal" role="form" name="formulario" method="post" action="index.php">
	  			<div id="error"><?php echo $msg_error;?></div>
          <div class="form-group">
	    			<label for="usuario" class="col-xs-3" >Usuario:</label>
	    			<div class="col-xs-7">
	    				<input type="text" class="form-control" id="usuario" name="usuario" value="<?php echo $usuario;?>">
	    			</div>
	  			</div>
	  			<div class="form-group">
	    			<label for="password" class="col-xs-3">Password:</label>
	    			<div class="col-xs-7">
	    				<input type="password" class="form-control" id="password" name="password">
	    			</div>
	  			</div>
	  			<button type="submit" class="btn btn-primary col-xs-offset-3">Conectar</button>
			</form>
		</div>
    </div>
  </body>
</html>
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
    $pee_old=$_REQUEST['old'];
    $pee_new=$_REQUEST['new'];
    $sql="UPDATE USR_JOR SET UJ_PEE=".$pee_new." WHERE UJ_PEE=".$pee_old;
    DB_EJECUTA($sql);

  ?>
</head>

<body class="framework">
    <script type="text/javascript">document.location.href = "inicio2.php";</script>';
</body>

</html>
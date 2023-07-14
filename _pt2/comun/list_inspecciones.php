<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1">

    <title>Moneleg - Inspecciones</title>
 
    <!-- CSS de Bootstrap -->
    <link rel="stylesheet" href="../bootstrap-3.2.0/css/bootstrap.min.css" media="screen">
    <link rel="stylesheet" href="../plugins/font-awesome/css/font-awesome.css">
    <link rel="stylesheet" href="../css/framework.css" media="screen">
 
    <!-- librerías opcionales que activan el soporte de HTML5 para IE8 -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

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
    
    <?php 
      include "../comun/db_con.php";
      include "../comun/funciones.php";

      session_start();
    ?>
</head>

<body class="framework">
    <div class="container">
    
    <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
      <span class="navbar-left"><a href="inspecciones.php" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
      <p class="titulo">Inspecciones</span>
      <span class="navbar-rigth" style="visibility:hidden;"><a href="#" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Sig.</a></span>
    </nav>

    <table class="table" style="margin:5px;">
      <?php 

      $sql = "SELECT * FROM LIST_INSPECCIONES";
      $sql .= " WHERE Usuario=" . $_SESSION['GLB_USR_ID'];
      $sentencia = DB_CONSULTA($sql);
      while ($row = mysql_fetch_assoc($sentencia)) { ?>
        <tr>
            <td class="detalle_fila" onclick="javascript: window.location.href='inspeccion.php?op=M&id=<?php echo $row['INSP_ID']?>';">
                <table style="width:100%;">
                    <tr>
                        <td class="id_registro"><strong><?php echo $row['INSP_ID'];?></td>
                        <td align="right"><?php echo STR_fecha($row['Fecha']);?></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $row['Presupuesto'];?></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo $row['Presupuesto Descripción'];?></td>
                    </tr>
                </table>
            </td>
            <!--INICIO R.L convertido en pdf-->
            <td class="detalle_fila" onclick="javascript: window.location.href='inspeccion_pdf.php?';">                
               
            </td>
        </tr>
      <?php }?>
    </table>

    <script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
    <script type="text/javascript" src="../js/ajax.js"></script>
</body>
</html>
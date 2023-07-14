<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1">
    
    <script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
    <script type="../text/javascript" src="js/ajax.js"></script>

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

        $opcion = $_REQUEST['opcion'];
        if (!isset($opcion)) {
            $opcion = "pendientes";
        }
    ?>
</head>

<body class="framework">
    <div class="container">
    
    <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
          <span class="navbar-left"><a href="../inicio.php" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
          <p class="titulo">Planificaci&oacute;n</span>
        <span class="navbar-rigth" style="visibility:hidden;"><a href="#" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Sig.</a></span>
    </nav>

    <ul class="nav nav-pills">
        <?php if ($opcion == 'pendientes') { 
            echo '<li class="active"><a href="list_planificacion.php?opcion=pendientes">Pendientes</a></li>';
            echo '<li><a href="list_planificacion.php?opcion=finalizado">Finalizados</a></li>';
            } else {
                echo '<li class=""><a href="list_planificacion.php?opcion=pendientes">Pendientes</a></li>';
                echo '<li class="active"><a href="list_planificacion.php?opcion=finalizado">Finalizados</a></li>';
            } ?>
    </ul>
    <table class="table" style="margin:5px;">
        <?php 
        $sql = "SELECT * FROM LIST_PLANIFICACION";
        $sql .= " WHERE Operario=" . $_SESSION['GLB_USR_ID'] . " AND Fecha=CURDATE()";
        
        if ($opcion == 'pendientes') {
            $sql .= " AND Parte IS NULL";
        } else {
            $sql .= " AND NOT Parte IS NULL";
        }
        
        $sentencia = DB_CONSULTA($sql);
        while ($row = mysql_fetch_assoc($sentencia)) { ?>
            <tr>
                <?php if ($opcion == 'pendientes') { ?>
                    <td class="detalle_fila" onclick="javascript: window.location.href='nuevoparte_planificacion.php?op=C&pre_id=<?php echo $row['Presupuesto']?>';">
                <?php } else { ?>
                    <td class="detalle_fila" onclick="javascript: window.location.href='nuevoparte_planificacion.php?op=M&id=<?php echo $row['Parte']?>';">
                <?php } ?>
                    <table style="width:100%;">
                        <tr>
                            <td class="id_registro"><strong><?php echo $row['Presupuesto'];?></td>
                            <td align="right"><?php echo STR_fecha($row['Fecha']);?></td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo $row['Descripción'];?></td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo $row['Dirección'];?></td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo $row['Población'];?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        <?php }?>
    </table>

</body>
</html>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1">
    
    <script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
    <script type="text/javascript" src="js/ajax.js"></script>

    <title>Moneleg - Modificar marcajes</title>
 
    <!-- CSS de Bootstrap -->
    <link href="bootstrap-3.2.0/css/bootstrap.min.css" rel="stylesheet" media="screen">
    <link rel="stylesheet" href="plugins/font-awesome/css/font-awesome.css">
    <link rel="stylesheet" type="text/css" href="css/framework.css" media="screen">
 
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


        td.detalle_fila_parte {
            background-color:#affaaf;
            border-top:1px solid gray;
            border-bottom:1px solid gray;
        }
        
        td.detalle_fila_parte:hover{
            background-color:white;
            cursor: pointer;
        }
    </style>
    
    <?php 
        include "comun/db_con.php";
        include "comun/funciones.php";

        session_start();

        $opcion = $_REQUEST['opcion'];
        if (!isset($opcion)) {
            $opcion = "hoy";
        }
    ?>
</head>

<body class="framework">
    <div class="container">
    
    <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
          <span class="navbar-left"><a href="inicio.php" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
          <p class="titulo">Buscar Partes</span>
        <span class="navbar-rigth" style="visibility:hidden;"><a href="#" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Sig.</a></span>
    </nav>

    <!-- INICIO: DÍAS DE PARTES -->
    <ul class="nav nav-pills">
        <?php 
        $act="";
        if ($opcion == 'hoy') { 
            $hoy=getdate();
            $fec=$hoy["year"] . "-" . $hoy["mon"] . "-" . $hoy["mday"];
            $act="class=\"active\"";
        } else {
            $fec=$opcion;
        }
        echo '<li ' . $act . '><a href="list_jortra.php?opcion=hoy">Hoy</a></li>';

        $sql = "SELECT DATE_FORMAT(UJ_FEC_INI,'%Y-%m-%d') AS FECHA FROM USR_JOR WHERE UJ_USU=" . $_SESSION['GLB_USR_ID'] . " AND UJ_FEC_INI>=CURDATE()-7 AND UJ_FEC_INI<CURDATE() GROUP BY DATE_FORMAT(UJ_FEC_INI,'%Y-%m-%d') ORDER BY DATE_FORMAT(UJ_FEC_INI,'%Y-%m-%d') DESC";
        $sentencia2 = DB_CONSULTA($sql);
        $fec_ayer="";
        $ictd=0;
        while ($row2 = mysql_fetch_assoc($sentencia2)) {
            if ($ictd<10){
                $act="";
                if ($opcion==$row2[FECHA]){
                    $act="class=\"active\"";
                }
                echo '<li ' . $act . '><a href="list_jortra.php?opcion=' . $row2['FECHA'] . '">Día: ' . $row2['FECHA'] . '</a></li>';
            }
            $ictd=$ictd+1;
        }
        ?>
    </ul>
    <!-- INICIO: DÍAS DE PARTES -->


    <table class="table" style="margin:5px;">
        <?php 
        $sql = "SELECT USR_JOR.*, VEH_MAT, MAR_NOM, MOD_NOM, PT_WEB FROM USR_JOR LEFT JOIN VEH ON UJ_VEH_ID=VEH_ID LEFT JOIN VEH_MAR ON MAR_ID=VEH_MAR LEFT JOIN VEH_MOD ON MOD_ID=VEH_MOD LEFT JOIN PAR ON PAR_ID=UJ_PAR LEFT JOIN PAR_TIP ON PT_ID=PAR_TIP WHERE UJ_USU=";
        $sql .= $_SESSION['GLB_USR_ID'] . " AND UJ_FEC_INI>='" . $fec . "' AND UJ_FEC_INI<='" . $fec . " 23:59:59' ORDER BY UJ_FEC_INI";

        $par_exi="";
        $sentencia = DB_CONSULTA($sql);
        while ($row = mysql_fetch_assoc($sentencia)) {
            $tip="";
            $det="";
            $url="";
            $ope="";
            $class="detalle_fila";
            if ($row['UJ_JOR']==1){
                $tip="Jornada";
            } else if ($row['UJ_TIP_STOP']==3){
                $tip="Vehículo";
                $tip="";
                $det=$row['VEH_MAT'] . " - " . $row['MAR_NOM'] . " " . $row['MOD_NOM'];
            } else if ($row['UJ_TIP_STOP']==1){
                $par_exi .= " AND Codigo<>" . $row['UJ_PAR'];
                $tip="Trabajo";
                $class="detalle_fila_parte";
                if ($row['UJ_PLA_JEF']==1){
                    $det="Parte (jefe): " . $row['UJ_PAR'];
                } else {
                    $det="Parte (miembro): " . $row['UJ_PAR'];
                }
                if ($row['UJ_PAR']!=""){
                    $tip.=" (ver detalle)";
                    $det.=" Pto.Pla.: " . DB_LEE_CAMPO("PLA_ENC_ENC","PEE_PRE","PEE_ID=" . $row['UJ_PEE']);
                    $url="onclick=\"javascript: window.location.href='" . $row['PT_WEB'] . "?op=M&id=" . $row['UJ_PAR'] . "'\"";
                } else {
                    //if (STR_hora2($row['UJ_FEC_FIN'])!=""){
                    //    $det.=" (en ejecución)";
                    //} else {
                        //$par_id=DB_LEE_CAMPO("USR_JOR INNER JOIN USR_JOR AS USR_JOR1 ON USR_JOR.UJ_PEE=USR_JOR1.UJ_PEE AND USR_JOR1.UJ_PLA_JEF=1","USR_JOR1.UJ_PAR","USR_JOR.UJ_ID=" . $row['UJ_ID']);
                        $sql = "SELECT USR_JOR1.UJ_PAR, PAR_TIP.PT_WEB FROM USR_JOR INNER JOIN USR_JOR AS USR_JOR1 ON USR_JOR.UJ_PEE=USR_JOR1.UJ_PEE AND USR_JOR1.UJ_PLA_JEF=1 INNER JOIN PAR ON USR_JOR1.UJ_PAR=PAR_ID INNER JOIN PAR_TIP ON PAR.PAR_TIP=PAR_TIP.PT_ID WHERE USR_JOR.UJ_ID=" . $row['UJ_ID'];
                        $sentencia3=DB_CONSULTA($sql);
                        if ($row3 = mysql_fetch_assoc($sentencia3)) {
                            if ($row['UJ_PLA_JEF']==1){
                                $det="Parte (jefe): " . $row3['UJ_PAR'];
                            } else {
                                $det="Parte (miembro): " . $row3['UJ_PAR'];
                            }
                            $det.=" Pto.Pla.: " . DB_LEE_CAMPO("PLA_ENC_ENC","PEE_PRE","PEE_ID=" . $row['UJ_PEE']);
                            $url="onclick=\"javascript: window.location.href='" . $row3['PT_WEB'] . "?op=M&id=" . $row3['UJ_PAR'] . "'\"";
                        } else {
                            $det.=" Pto.Pla.: " . DB_LEE_CAMPO("PLA_ENC_ENC","PEE_PRE","PEE_ID=" . $row['UJ_PEE']);
                            $det.=" (en ejecución)";
                        }
                    //}
                }
                $ope=DB_LEE_CAMPO("USR_JOR","COUNT(*)","UJ_PEE=" . $row['UJ_PEE']);
                if ($ope>1){
                    $ope=" / " . $ope . " operarios";
                } else {
                    $ope=" / solo";
                }

                if ($row['UJ_PEE']<0){
                    $det.=" / sin planificar";
                } else {
                    $det.=" / planificado";
                }
            } else if ($row['UJ_TIP_STOP']==2){
                $tip="Descanso";
            }

            $hor="";
            if ($row['UJ_FEC_FIN']!=""){
                $hor=" " . calcular_tiempo_trasnc($row['UJ_FEC_INI'],$row['UJ_FEC_FIN']) . " h.";
            }
            ?>
            <tr>
                <td class="<?php echo $class;?>" <?php echo $url;?>>
                    <table style="width:100%;">
                        <tr>
                            <td class="id_registro"><strong><?php echo $tip;?></td>
                            <td align="right"><?php echo STR_hora2($row['UJ_FEC_INI']) . " - " . STR_hora2($row['UJ_FEC_FIN']);?></td>
                        </tr>
                        <tr>
                            <td colspan="2"><?php echo $det . $ope;?></td>
                        </tr>
                        <tr>
                            <td>
                            <?php
                            if ($tip!=""){
                                ?>
                            <span class="navbar-left"><a href="list_jortra_det.php?ujid=<?php echo $row['UJ_ID'] . '&tip=' . substr($tip,0,3);?>" class="btn btn-primary">Solicitar modificación horas</a></span>
                            <?php } ?>
                            </td>
                            <td></td>
                        </tr>
                    </table>
                </td>
            </tr>
        <?php }?>

        <tr><td></td></tr>
            <tr><td><strong>COMO ENCARGADO</strong></td></tr>
            <?php
            $sql = "SELECT * FROM LIST_PARTES";
            $sql .= " WHERE encargado_pla=" . $_SESSION['GLB_USR_ID'];
            $sql .= " AND Fecha>='" . $fec . "' AND Fecha<='" . $fec . " 23:59:59'";
            $sql .= " AND NOT PAR_ID_LOCAL IS NULL";
            $sql .= $par_exi;

            $sentencia = DB_CONSULTA($sql);
            while ($row = mysql_fetch_assoc($sentencia)) {
                ?>
                <tr>
                    <td class="detalle_fila" onclick="javascript: window.location.href='<?php echo $row["Nuevo Parte"];?>?op=M&id=<?php echo $row['Codigo']?>';">
                        <table style="width:100%;">
                            <tr>
                                <td class="id_registro"><strong><?php echo $row['Codigo']; echo strpos($partes,$row['Codigo'] . ";");?></td>
                                <td align="right"><?php echo STR_fecha($row['Fecha']);?></td>
                            </tr>
                            <tr>
                                <td colspan="2">N&deg; Incidencia/Orden: <?php echo $row['Incidencia'];?></td>
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
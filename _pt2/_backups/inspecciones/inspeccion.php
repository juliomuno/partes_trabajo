<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1">
    
    <title>Moneleg - Inspecciones</title>
    
    <!-- CSS de Bootstrap -->
    <link rel="stylesheet" href="../bootstrap-3.2.0/css/bootstrap.min.css"  media="screen">
    <link rel="stylesheet" href="../plugins/font-awesome/css/font-awesome.css">
    <link rel="stylesheet" href="../css/framework.css" media="screen">
    
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
        php_redirect('../index.php');
      }
           
      $op = $_REQUEST['op'];
      $id = $_REQUEST['id'];
      $formularios = array();
      $voperarios = array();
      $vsubc = array();
      $vope_subc = array();

      if ($op != 'C') {
        $sentencia = DB_CONSULTA("SELECT * FROM INSP WHERE INSP_ID=" . $id);
        if (mysql_num_rows($sentencia) == 1) {
          $row = mysql_fetch_assoc($sentencia);
          $insp_id = $row['INSP_ID'];
          $insp_fec = date("Y-m-d", strtotime($row['INSP_FEC']));
          $insp_pre = $row['INSP_PRE'];
          $insp_dir = $row['INSP_DIR'];
          $insp_pob = $row['INSP_POB'];
          $insp_lat = $row['INSP_LAT'];
          $insp_lon = $row['INSP_LON'];
          $insp_tip_obr = $row['INSP_TIP_OBR'];
          $insp_tip_tra = $row['INSP_TIP_TRA'];
          $insp_usu = $row['INSP_USU'];
          $insp_cssdf = $row['INSP_CSSDF'];
          $insp_sorp = $row['INSP_SORP'];
          $insp_nsorp = $row['INSP_NSORP'];
          $insp_cobs = $row['INSP_COBS'];
          $insp_cinc = $row['INSP_CINC'];
          $insp_inci = $row['INSP_INCI'];
          $par_veh = $row['INSP_VEH'];
          $insp_ext1 = $row['INSP_EXT1'];
          $insp_ext2 = $row['INSP_EXT2'];
          $insp_jef = $row['INSP_JEF'];
          $insp_rpr = $row['INSP_RPR'];

          $insp_obs = $row['INSP_OBS'];
          $insp_tra_des = $row['INSP_TRA_DES'];
          $insp_des_inc = $row['INSP_DES_INC'];

          $sentencia = DB_CONSULTA("SELECT * FROM INSP_OPE WHERE IO_INSP=" . $id);
          while ($row = mysql_fetch_assoc($sentencia)) {
            $voperarios[] = $row['IO_OPE'];
          }

          $sentencia = DB_CONSULTA("SELECT * FROM INSP_OPE_SUBC WHERE IOS_INSP=" . $id);
          while ($row = mysql_fetch_assoc($sentencia)) {
            $vope_subc[] = $row['IOS_OPE'];
            $vsubc[] = $row['IOS_SUBC'];
          }

          $sentencia = DB_CONSULTA("SELECT DISTINCT IR_FRM FROM INSP_RESP WHERE IR_INSP=" . $id);
          while ($row = mysql_fetch_assoc($sentencia)) {
            $formularios[] = $row['IR_FRM'];
          }
        } else {
          exit;
        }

      } else {
        $insp_id = '';
        $insp_fec = str_html_fecha(getdate());
        $insp_dir = '';
        $insp_pob = 0;
        $insp_lat = '';
        $insp_lon = '';
        $insp_tip_obr = 0;
        $insp_tip_tra = 0;
        $insp_usu = 0;
        $insp_cssdf = false;
        $insp_sorp = false;
        $insp_nsorp = false;
        $insp_cobs = false;
        $insp_cinc = false;
        $insp_inci = false;
        $insp_subc = 0;
        $insp_obs = '';
        $insp_tra_des = '';
        $insp_des_inc = '';
        $par_veh = 0;
        $insp_ext1 = "";
        $insp_ext2 = "";
        $insp_jef = "";
        $insp_rpr = "";
      }

    ?>
    
    <style>
      .table {
        background: rgb(255,255,255);
      }
      th.center, td.center {
        text-align: center;
      }

    </style>
</head>

<body class="framework">
  <div class="container">
    <form name="formulario" id="formulario" class="form-horizontal" role="form" method="POST" action="exe_inspeccion.php" enctype="multipart/form-data" onsubmit="return false;">
    <input type="hidden" name="op" value="<?php echo $op;?>" />
    <input type="hidden" name="id" value="<?php echo $id;?>" />
    
    <!-- Modal -->
    <div class="modal fade" id="myModal" role="dialog">
      <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Guardar Inspección</h4>
          </div>
          <div class="modal-body alert-warning" id="modal_text">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="javascript: habilitar_capa('nuevoparte1');">Cerrar</button>
          </div>
        </div>
      </div>
    </div>


    <div id="div_inspeccion_general" style="display:none;">
      <!-- Inicio Nuevo Parte 1/5 -->
      <div id="nuevoparte1">
        <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
          <span class="navbar-left"><a href="inspecciones.php" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
          <span class="navbar-right"><a href="javascript: habilitar_capa('nuevoparte2');" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
          <p class="titulo">Inspección 1/6</p>
        </nav>
        
        <ul class="nav nav-pills" style="margin-bottom:.5em;">
          <li id="li_inspeccion_general" class="active"><a href="#" onclick="javascript:habilita_opcion(true);">General</a></li>
          <li id="li_inspeccion_formularios"><a href="#" onclick="javascript: habilita_opcion(false);">Formularios</a></li>
        </ul>

        <h4>Datos Generales</h4>
        <div class="form-group">
          <label for="num_incidencia" class="col-lg-2">Código:</label>
          <div class="col-lg-10">
            <input type="number" class="form-control entero" name="txt_id" disabled value="<?php echo $insp_id;?>"></input>
          </div>
        </div>
        <!-- ./form-group -->
        
        <div class="form-group">
          <label for="fecha" class="col-lg-2">Fecha:</label>
          <div class="col-lg-10">
            <input type="date" class="form-control" name="txt_fec" value="<?php echo $insp_fec;?>"></input>
          </div>
        </div>
        <!-- ./form-group -->

        <div class="form-group">
          <label for="presupuesto" class="col-lg-2">Presupuesto:</label>
          <div class="col-lg-10">
            <input type="number" class="form-control entero" name="txt_pre" value="<?php echo $insp_pre;?>"></input>
          </div>
        </div>
        <!-- ./form-group -->
        
        <div class="form-group">
          <label for="" class="col-lg-2">Tipo de Obra:</label>
          <div class="col-lg-10">
            <?php echo DB_COMBOBOX("INSP_TIP_OBR","ITO_ID","ITO_NOM","","ITO_NOM","cmb_tip_obr","cmb_tip_obr","form-control",$insp_tip_obr,"","",""); ?>
          </div>
        </div>
        <!-- ./form-group -->

        <div class="form-group">
          <label for="presupuesto" class="col-lg-2">Tipo de Trabajo:</label>
          <div class="col-lg-10">
            <?php echo DB_COMBOBOX("INSP_TIP_TRA","ITT_ID","ITT_NOM","","ITT_NOM","cmb_tip_tra","cmb_tip_tra","form-control",$insp_tip_tra,"","",""); ?>
          </div>
        </div>
        <!-- ./form-group -->

        <div class="form-group">
          <label for="presupuesto" class="col-lg-2">Inspeccionado por:</label>
          <div class="col-lg-10">
            <?php echo DB_COMBOBOX("LIST_OPERARIOS_INSPECCIONES","Codigo","Nombre","","Nombre","cmb_ins_usu","cmb_ins_usu","form-control",$insp_usu,"","",""); ?>
          </div>
        </div>
        <!-- ./form-group -->

        <label class="checkbox-inline">
          <?php if ($insp_nsorp == 1) {$checked="checked";} else {$checked="";}?>
          <input type="checkbox" name="chk_nsorp" value="1" <?php echo $checked;?>> No sorpresiva
        </label>
        <label class="checkbox-inline">
          <?php if ($insp_sorp == 1) {$checked="checked";} else {$checked="";}?>
          <input type="checkbox" name="chk_sorp" value="1" <?php echo $checked;?>> Sorpresiva
        </label>
        <label class="checkbox-inline">
          <?php if ($insp_cssdf == 1) {$checked="checked";} else {$checked="";}?>
          <input type="checkbox" name="chk_cssdf" value="1" <?php echo $checked;?>> CSS-DF
        </label>

      </div>
      <!-- ./nuevoparte1 -->

      <!-- Nuevo Parte 2 -->
      <div id="nuevoparte2" style="display:none">
        <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
          <span class="navbar-left"><a href="javascript: habilitar_capa('nuevoparte1');" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
          <span class="navbar-right"><a href="javascript: habilitar_capa('nuevoparte3');" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
          <p class="titulo">Inspección 2/6</p>
        </nav>

        <h4>Datos de Localización</h4>
        <div class="form-group">
          <label for="direccion" class="col-lg-2">Direcci&oacute;n:</label>
          <div class="col-lg-10">
            <div class="input-group">
              <input type="text" class="form-control" name="txt_dir" value="<?php echo $insp_dir;?>">
              <span class="input-group-btn">
                <button class="btn btn-default btn-primary" type="button" title="Localización" onclick="javascript: obtener_localizacion();"><i class="fa fa-map-marker"></i></button>
              </span>
            </div>
          </div>
          <!-- ./col -->
        </div>
        <!-- ./form-group -->
        
        <div class="form-group">
          <label for="poblacion" class="col-lg-2">Poblaci&oacute;n:</label>
          <div class="col-lg-10" id="combo_poblacion">
            <?php echo DB_COMBOBOX("LIST_POBLACIONES","Codigo","Nombre","","Nombre","cmb_pob","cmb_pob","form-control",$insp_pob,"","",""); ?>
          </div>
        </div>

        <div class="form-group">
          <label for="vehiculo" class="col-lg-2">Veh&iacute;culo:</label>
          <div class="col-lg-10">
            <?php echo DB_COMBOBOX("LIST_VEHICULOS","Codigo","Nombre","","Nombre","cmb_veh","cmb_veh","form-control",$par_veh,"","",""); ?>
          </div>
        </div>

        <div class="form-group">
          <label for="extintor1" class="col-lg-2">Extintor 1:</label>
          <div class="col-lg-10">
            <select class="form-control" name="txt_ext1">
            	<?php
            	echo "<option " . $selected . " value=''> </option>";
              if ($insp_ext1 == '0') {$selected = " selected";} else {$selected = "";}
              if ($insp_ext1 == '') {$selected = " selected";} else {$selected = "";}
              ?>
            	<option <?php echo $selected ?> value="0">no aplica</option>
              <?php
              if ($insp_ext1 == 0) {$selected = " selected";} else {$selected = "";}
              $mes=date("n")-1;
            	$anio=date("Y");
            	for ($i=1; $i<=12; $i++)
            		{
            			if ($mes<12){$mes++;} else {$mes=1;$anio++;}
            			$valor=$anio . $mes;
            			if ($insp_ext1 == $valor) {$selected = " selected";} else {$selected = "";}
            		?>
            	<option <?php echo $selected ?> value="<?php echo $anio . $mes ?>"><?php echo mes_nombre($mes) . " / " . $anio ?></option>
            		<?php
            		}
            		?>
            </select>
          </div>
        </div>
        <!-- ./form-group -->

        <div class="form-group">
          <label for="extintor2" class="col-lg-2">Extintor 2:</label>
          <div class="col-lg-10">
            <select class="form-control" name="txt_ext2">
            	<?php
            	echo "<option " . $selected . " value=''> </option>";
              if ($insp_ext2 == '0') {$selected = " selected";} else {$selected = "";}
              if ($insp_ext2 == '') {$selected = " selected";} else {$selected = "";}
              ?>
              <option <?php echo $selected ?> value="0">no aplica</option>
              <?php
              if ($insp_ext2 == 0) {$selected = " selected";} else {$selected = "";}
            	$mes=date("n")-1;
            	$anio=date("Y");
            	for ($i=1; $i<=12; $i++)
            		{
            			if ($mes<12){$mes++;} else {$mes=1;$anio++;}
            			$valor=$anio . $mes;
            			if ($insp_ext2 == $valor) {$selected = " selected";} else {$selected = "";}
            		?>
            	<option <?php echo $selected ?> value="<?php echo $anio . $mes ?>"><?php echo mes_nombre($mes) . " / " . $anio ?></option>
            		<?php
            		}
            		?>
            </select>
          </div>
        </div>
        <!-- ./form-group -->


        <div class="form-group bloque-reducido" style="display: none;">
          <label for="latitud" class="col-lg-2">Latitud:</label>
          <div class="col-lg-10">
            <input type="text" class="form-control" name="txt_lat" />
          </div>
        </div>
        <div class="form-group bloque-reducido" style="display: none;">
          <label for="longitud" class="col-lg-2">Longitud:</label>
          <div class="col-lg-10">
            <input type="text" class="form-control" name="txt_lon" />
          </div>
        </div>

      </div>
      <!-- ./nuevoparte2 -->

      <!-- Nuevo Parte 3 -->
      <div id="nuevoparte3" style="display:none">
        <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
          <span class="navbar-left"><a href="javascript: habilitar_capa('nuevoparte2');" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
          <span class="navbar-right"><a href="javascript: habilitar_capa('nuevoparte4');" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
          <p class="titulo">Inspección 3/6</p>
        </nav>
        <h4>Trabajo Realizado</h4>
        <div class="form-group">
          <label for="realizado" class="col-lg-2">Descripción del Trabajo:</label>
          <div class="col-lg-10">
            <textarea class="form-control" rows="4" name="txt_tra_des"><?php echo $insp_tra_des;?></textarea>
          </div>
        </div>
      </div>
      <!-- ./nuevoparte3 -->

      <!-- Nuevo Parte 4 -->
      <div id="nuevoparte4" style="display:none">
        <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
          <span class="navbar-left"><a href="javascript: habilitar_capa('nuevoparte3');" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
          <span class="navbar-right"><a href="javascript: habilitar_capa('nuevoparte6');" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
          <p class="titulo">Inspección 4/6</p>
        </nav>
        <h4>Operarios Moneleg</h4>

    	<div class="form-group">
          <label for="vehiculo" class="col-lg-2">Jefe de trabajo:</label>
          <div class="col-lg-10">
            <?php echo DB_COMBOBOX("LIST_OPERARIOS","Codigo","Nombre","","Nombre","cmb_ins_jef","cmb_ins_jef","form-control",$insp_jef,"","",""); ?>
          </div>
      	</div>

      	<div class="form-group">
          <label for="vehiculo" class="col-lg-2">Recurso preventivo:</label>
          <div class="col-lg-10">
            <?php echo DB_COMBOBOX("LIST_OPERARIOS","Codigo","Nombre","","Nombre","cmb_ins_rpr","cmb_ins_rpr","form-control",$insp_rpr,"","",""); ?>
          </div>
      	</div>

        <table class="table table-borderless">
          <thead>
            <tr>
              <th>Operarios</th>
              <th style="text-align: right;">
                <button type="button" class="btn btn-success" id="add_button_ope"><i class="fa fa-plus"></i></button>
              </th>
            </tr>
          </thead>
          <tbody class="container_ope">

          </tbody>
        </table>

        <h4>Operarios Subcontratas</h4>
        <table class="table table-borderless">
          <thead>
            <tr>
              <th>Subcontratas</th>
              <th>Operarios</th>
              <th style="text-align: right;">
                <button type="button" class="btn btn-success" id="add_button_ope_subc"><i class="fa fa-plus"></i></button>
              </th>
            </tr>
          </thead>
          <tbody class="container_ope_subc">

          </tbody>
        </table>
        <div id="prueba"></div>
      </div>
      <!-- ./nuevoparte4 -->

      <!-- Nuevo Parte 6 -->
      <div id="nuevoparte6" style="display:none">
        <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
          <span class="navbar-left"><a href="javascript: habilitar_capa('nuevoparte4');" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
          <span class="navbar-right"><a href="javascript: habilitar_capa('nuevoparte5');" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
          <p class="titulo">Inspección 5/6</p>
        </nav>
        <h4>Resultado</h4>
        <div class="form-group">
          <label class="checkbox-inline">
            <?php if ($insp_cinc == 1) {$checked="checked";} else {$checked="";}?>
            <input type="radio" name="chk_cinc" value="1" <?php echo $checked;?>> Con incidencia
          </label>
          <label class="checkbox-inline">
            <?php if ($insp_cinc == 0) {$checked="checked";} else {$checked="";}?>
            <input type="radio" name="chk_cinc" value="0" <?php echo $checked;?>> Sin incidencia
          </label>
        </div>

        <div class="form-group">
          <label for="realizado" class="col-lg-2">Descripción de la incidencia:</label>
          <div class="col-lg-10">
            <textarea class="form-control" rows="4" name="txt_des_inc"><?php echo $insp_des_inc;?></textarea>
          </div>
        </div>

        <div class="form-group">
          <label class="checkbox-inline">
            <?php if ($insp_cobs == 1) {$checked="checked";} else {$checked="";}?>
            <input type="radio" name="chk_cobs" value="1" <?php echo $checked;?>> Con observaciones
          </label>
          <label class="checkbox-inline">
            <?php if ($insp_cobs == 0) {$checked="checked";} else {$checked="";}?>
            <input type="radio" name="chk_cobs" value="0" <?php echo $checked;?>> Sin observaciones
          </label>
        </div>
        <div class="form-group">
          <label for="observaciones" class="col-lg-2">Observaciones:</label>
          <div class="col-lg-10">
            <textarea class="form-control" rows="4" name="txt_obs"><?php echo $insp_obs;?></textarea>
          </div>
        </div>

        <div class="form-group">
          <h4>Firma</h4>
          <!--<canvas id="pizarra" style="width: 300px;height: 300px;background-color: #ffffff;"></canvas>-->
          <?php
          if (file_exists("./firmas/firma_".$insp_id.".png")){ ?>
          	<div class='centrador'>
	          	<canvas id='canvas' width="10" height="10" style='border: 1px solid #CCC;'>
	              <p>Tu navegador no soporta canvas</p>
	            </canvas>
	          	<img src="<?php echo './firmas/firma_'.$insp_id.'.png' ?>">
	          	<input type='hidden' name='imagen' id='imagen' />
	        </div>
	      <?php }else{ ?>
	          <div class='centrador'>
	            <canvas id='canvas' width="380" height="200" style='border: 1px solid #CCC;'>
	              <p>Tu navegador no soporta canvas</p>
	            </canvas>
	          </div>
	          <div class='centrador'>
	            <!--<form id='formCanvas' method='post' action='#' ENCTYPE='multipart/form-data'>-->
	              <!--<span class="navbar-left"><a href="javascript: LimpiarTrazado();" class="btn btn-primary"><i class="fa"> </i>Borrar firma</a></span>-->
	              <!--<span class="navbar-right"><a href="javascript: GuardarTrazado();" class="btn btn-primary"><i class="fa"> </i>Guardar firma</a></span>-->
	              <input type='hidden' name='imagen' id='imagen' />
	            <!--</form>-->
	          </div>
          <?php } ?>
        </div>
      </div>
      <!-- ./nuevoparte6 -->

      <!-- Nuevo Parte 5 -->
      <div id="nuevoparte5" style="display:none">
        <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
            <span class="navbar-left"><a href="javascript: habilitar_capa('nuevoparte6');" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
            <span class="navbar-right" style="visibility:hidden;"><a href="#" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
            <p class="titulo">Inspección 6/6</p>
        </nav>

        <div id="cargando"></div>
        <button type="button" id="btn_guardar" class="btn btn-danger btn-block" onclick="javascript: validar_formulario();">Guardar</button>
      </div>
      <!-- ./nuevoparte5 -->
    
    </div>
    <!-- ./div_inspeccion_general -->

    <div id="div_inspeccion_formularios" style="display:none;">
      <!-- Inicio Nuevo Parte 1 -->
      <div id="nuevoparte1">
        <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
          <span class="navbar-left"><a href="inspecciones.php" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
          <span class="navbar-right" style="visibility:hidden;"><a href="javascript: habilitar_capa('nuevoparte2');" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
          <p class="titulo">Parte Inspección</p>
        </nav>

        <ul class="nav nav-pills" style="margin-bottom:.5em;">
          <li id="li_inspeccion_general"><a href="#" onclick="javascript:habilita_opcion(true);">General</a></li>
          <li id="li_inspeccion_formularios" class="active"><a href="#" onclick="javascript: habilita_opcion(false);">Formularios</a></li>
        </ul>

        <table class="table table-striped" style="margin:5px;">
          <thead>
          <tr>
            <th>Descripción</th>
            <th class="center">Operación</th>
          </tr>
          </thead>
          <tbody>
            <?php
              $sql = "SELECT * FROM FRM ORDER BY FRM_ORD";
              $sentencia = DB_CONSULTA($sql);
              while ($row = mysql_fetch_assoc($sentencia)) { ?>
              <tr>
                <td><?php echo $row['FRM_NOM'];?></td>
                <td class="center">
                  <?php
                    if (in_array($row['FRM_ID'], $formularios)) {
                      echo '<a href="inspeccion_formulario.php?insp_id=' . $id . '&op=M&frm_id=' . $row['FRM_ID'] . '" class="btn btn-success" role="button">Actualizar</a>';
                    } else {
                      echo '<a href="inspeccion_formulario.php?insp_id=' . $id . '&op=C&frm_id=' . $row['FRM_ID'] . '" class="btn btn-primary" role="button">Crear</a>';
                    }
                  ?>
                </td>
              </tr>
            <?php
              }
            ?>
          </tr>
          </tbody>
        </table>
      </div>
      <!-- ./nuevo_parte1 -->
    </div>
    <!-- ./div_inspeccion_formularios -->
    
    <!-- Muestra Id del registro guardado -->
    <div id="nuevoparte_fin"></div>
    <!-- Fin nuevoparte_fin -->
    </form>
  
  </div> <!-- Fin .container -->

  <script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
  <script type="text/javascript" src="../js/ajax.js"></script>
  <script type="text/javascript" src="../js/validacion.js"></script>
  <!-- API Google Maps -->
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDLRg5FYmic8kj1vZJX3SulcNPu9EBjM24" async defer></script>

  <script type="text/javascript" src="../bootstrap-3.2.0/js/bootstrap.min.js"></script>
  
  <script>
    var myXhr = $.ajaxSettings.xhr();

    /* BLOQUE GOOGLE MAPS */
    function codeLatLng(position) {
      var latlng = new google.maps.LatLng(parseFloat(document.formulario.txt_lat.value), parseFloat(document.formulario.txt_lon.value));
      var geocoder = new google.maps.Geocoder();
      var direccion_completa = "";
      var direccion = "";
      var pos_ini = 0;
      var pos_fin = 0;
      
      geocoder.geocode({'latLng': latlng}, function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
          if (results[0]) {
            direccion_completa = document.formulario.txt_dir.value = results[0].formatted_address;
            // Colocar sólo la dirección
            pos_ini = direccion_completa.lastIndexOf(", 1"); // Buscar el CP
            direccion = direccion_completa.substr(0, pos_ini);
            document.formulario.txt_dir.value = direccion;
            
            // Buscar Población
            direccion = direccion_completa.substr(pos_ini+7);
            pos_fin = direccion.indexOf(",");
            direccion = direccion.substr(0, pos_fin);
            GUI_COMBO_SELTEXT(document.formulario.cmb_pob, direccion.trim());
          } else {
            alert('No se han encontrado resultados');
          }
        } else {
          alert('Error Geocoder: ' + status);
        }
      });
    }

    function obtener_localizacion() {
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(coordenadas,gestiona_errores,{enableHighAccuracy: true, maximumAge: 60000,
      timeout: 5000});
      }else{
        alert('Tu navegador no soporta la API de geolocalizacion');  
      }
    }

    function coordenadas(position) {
      var latitud = position.coords.latitude;
      var longitud = position.coords.longitude;
      var geocoder;

      document.formulario.txt_lat.value = latitud;
      document.formulario.txt_lon.value = longitud;
      codeLatLng(position);
    }
    
    function gestiona_errores(err) {
      if (err.code == 0) {
        alert("Error desconocido");
      }
      if (err.code == 1) {
        alert("El usuario no ha compartido su posicion");
      }
      if (err.code == 2) {
        alert("No se puede obtener la posicion actual");
      }
      if (err.code == 3) {
        alert("Tiempo de espera agotado");
      }
    }

    /* FIN BLOQUE GOOGLE MAPS */

    function habilita_opcion(bgeneral) {
      if ('<?php echo $op?>' == 'C') {
        $("#li_inspeccion_general").addClass("active");
        $("#div_inspeccion_general").show();
        $("#li_inspeccion_formularios").hide();
      } else {
        if (bgeneral) {
          $("#li_inspeccion_formularios").show();
          $("#li_inspeccion_general").addClass("active");
          $("#li_inspeccion_formularios").removeClass("active");
          $("#div_inspeccion_general").show();
          $("#div_inspeccion_formularios").hide();
        } else {
          $("#li_inspeccion_general").removeClass("active");
          $("#li_inspeccion_formularios").addClass("active");
          $("#div_inspeccion_general").hide();
          $("#div_inspeccion_formularios").show();
        }
      }
    }

    function habilitar_capa(id) {
      document.getElementById('nuevoparte1').style.display = "none";
      document.getElementById('nuevoparte2').style.display = "none";
      document.getElementById('nuevoparte3').style.display = "none";
      document.getElementById('nuevoparte4').style.display = "none";
      document.getElementById('nuevoparte5').style.display = "none";
      document.getElementById('nuevoparte6').style.display = "none";
      document.getElementById('nuevoparte_fin').style.display = "none";
      document.getElementById(id).style.display = "block";
    }

    function muestra_progreso(porcentaje) {
      var msg;
      
      msg = '<h3 style="text-align:center;">' + porcentaje + "%</h3>";
      msg += '<div class="progress">';
      msg += '<div class="progress-bar progress-bar-infor" role="progressbar" aria-valuenow="' + porcentaje + '" aria-valuemin="0" aria-valuemax="100" style="width:' + porcentaje + '%">';
      //msg += "<span>" + porcentaje + "%</span>";
      msg += "</div>";
      msg += '</div>';
      msg += '<button type="button" id="btn_cancelar" class="btn btn-danger btn-block" onclick="javascript: cancelar_progreso();">Cancelar</button>'
        
      if (porcentaje == 0) {
        $("#cargando").html("").show();
      }
      
      $("#cargando").html(msg);
    }

    function cancelar_progreso() {
      myXhr.abort();
      
      $("#btn_guardar").attr("style", "visibility:visible");
      $("#cargando").html("");
                
      habilitar_capa("nuevoparte1");
    }

    // Página destino que recoge los datos y los procesa
    function uploadAjax(pagina){
      var data = new FormData($('#formulario')[0]);
      var url = pagina;
      $.ajax({
        url:url,
        type:"POST",
        dataType: 'json',
        contentType:false,
        data:data,
        processData:false,
        cache:false,
        xhr: function() {
          //var myXhr = $.ajaxSettings.xhr();
          if(myXhr.upload){
              myXhr.upload.addEventListener('progress',progress, false);
          }
          return myXhr;
        },
        beforeSend: function() {
          $("#btn_guardar").attr("style","visibility:hidden");
          muestra_progreso(0);
        },
      }).done(function(respuesta) {
        texto_html = '<div class="alert alert-' + respuesta.tipo_mensaje_alerta + '">' + respuesta.mensaje + '</div>';
        texto_html += '<p style="text-align:center;">';
        texto_html += '<button type="button" class="btn btn-' + respuesta.tipo_mensaje_alerta + ' btn-lg" onClick="' + respuesta.onclick + '">Aceptar</button>';
        texto_html += '</p>';
        texto_html += '</div>';
        $("#btn_guardar").attr("style","visibility:visible");
        $("#cargando").html("");

        $("#nuevoparte_fin").html(texto_html);
        habilitar_capa("nuevoparte_fin");
        
      });
    }

    function progress(e){
      if(e.lengthComputable){
        var max = e.total;
        var current = e.loaded;
        
        var Percentage = Math.floor((current * 100)/max);
        
        muestra_progreso(Percentage);
      }
    }

    function validar_formulario(){ 
      var msg = "";

      if (document.formulario.txt_fec.value.length==0) {
        msg = msg + "<p>Es necesario indicar una Fecha.</p>";
      } else if (!fecha_valida(document.formulario.txt_fec.value)) {
        msg = msg + "<p>La Fecha indicada no tiene un formato v&aacute;lido. DD/MM/YYYY</p>";
      }

      if (document.formulario.txt_pre.length == 0) {
        msg += "<p>Es necesario indicar un Presupuesto.</p>";
      } else if (isNaN(document.formulario.txt_pre.value.replace(",","."))) {
        msg += "<p>Es necesario indicar un Presupuesto v&aacute;lido.</p>";
      }

      if (document.formulario.txt_dir.length == 0) {
        msg += "<p>Es necesario indicar una Dirección.</p>";
      }

      if (document.formulario.cmb_ins_jef.length == 0) {
        msg += "<p>Es necesario indicar un Jefe de Trabajo.</p>"; 
      }

      if (document.formulario.cmb_pob.length == 0) {
        msg += "<p>Es necesario indicar una Población.</p>"; 
      }
      
      if (document.formulario.cmb_tip_obr.length == 0) {
        msg += "<p>Es necesario indicar un Tipo de Obra.</p>";  
      }

      if (document.formulario.cmb_tip_tra.length == 0) {
        msg += "<p>Es necesario indicar un Tipo de Trabajo.</p>";  
      }
      
      //guardar imagen firma
      GuardarTrazado();
      //msg += "firma: "+document.formulario.imagen.value;

      if (msg != "") {
        //document.getElementById("errores").innerHTML = msg
        document.getElementById("modal_text").innerHTML = msg;
        $("#myModal").modal("show");
        return 0;
      } else {
        //document.getElementById("cargando").style.display = "block";
        //document.formulario.submit();
        // Se cambia por petición Ajax con barra de progreso
        uploadAjax("exe_inspeccion.php");
      }
    } 

    window.onload = function() {
      var max_filtros_ope = 10;
      var max_filtros_ope_subc = 10;
      
      var wrapper_ope = $(".container_ope");
      var wrapper_ope_subc = $(".container_ope_subc");
      
      var add_button_ope = $("#add_button_ope");
      var add_button_ope_subc = $("#add_button_ope_subc");
      
      var cadena_html2_ope = "", cadena_html_ope = "";
      var cadena_html2_ope_subc = "", cadena_html_ope_subc = "";
      var num_ope = 1, num_ope_indice = 0;
      var num_ope_subc = 1, num_ope_subc_indice = 0;


      habilita_opcion(true);

      <?php foreach($voperarios as $valor) { ?>
        cadena_html_ope += "<tr>";
        cadena_html_ope += "<td>";
        cadena_html_ope += '<?php echo DB_COMBOBOX("LIST_OPERARIOS","Codigo","Nombre","","Nombre","cmb_operarios[]","cmb_operarios[]","form-control",$valor,"","","")?>';
        cadena_html_ope += '</td>';
        cadena_html_ope += '<td style="text-align: right;"><button type="button" class="btn btn-danger delete"><i class="fa fa-trash-o"></i></button></td>';
        cadena_html_ope += '</tr>';
        num_ope++;
        num_ope_indice++;
      <?php } ?>
      $(wrapper_ope).append(cadena_html_ope);

      
      $(add_button_ope).click(function(e){
  			var cadena_html2_ope = "";

      	cadena_html2_ope = "<tr>";
	      cadena_html2_ope += "<td>";
	      cadena_html2_ope += '<?php echo DB_COMBOBOX("LIST_OPERARIOS","Codigo","Nombre","","Nombre","cmb_operarios[]","cmb_operarios[]","form-control","","","","")?>';
	      cadena_html2_ope += '</td>';
	      cadena_html2_ope += '<td style="text-align: right;"><button type="button" class="btn btn-danger delete"><i class="fa fa-trash-o"></i></button></td>';

        e.preventDefault();
        if(num_ope < max_filtros_ope){
            num_ope++;
            num_ope_indice++;
            $(wrapper_ope).append(cadena_html2_ope);
        } else {
          alert('Has alcanzado el límite de operarios disponibles');
        }
      });

      $(wrapper_ope).on("click",".delete", function(e){
        e.preventDefault(); 
        $(this).parents('tr').remove(); 
        num_ope--;
        num_ope_indice--;
      });


      <?php foreach($vope_subc as $clave => $valor) { ?>
        cadena_html_ope_subc += "<tr>";
	      cadena_html_ope_subc += "<td>";
	      cadena_html_ope_subc += '<?php echo DB_COMBOBOX("LIST_INSPECCIONES_SUBCONTRATAS","Codigo","Nombre","","Nombre","cmb_subc[]","cmb_subc[]","form-control", $vsubc[$clave] ,"","","javascript: rellena_operarios_subcontrata($(this).val(), ' + num_ope_subc_indice + ');")?>';
	      cadena_html_ope_subc += '</td>';
	      cadena_html_ope_subc += '<td><div id="capa_' + num_ope_subc_indice + '">';
	      cadena_html_ope_subc += '<?php echo DB_COMBOBOX("LIST_INSPECCIONES_SUBCONTRATAS_OPERARIOS","Codigo","Nombre","Subcontrata=" . $vsubc[$clave],"Nombre","cmb_ope_subc[]","cmb_ope_subc[]","form-control",$valor,"","","");?>';
	      cadena_html_ope_subc += '</div></td>';
	      cadena_html_ope_subc += '<td style="text-align: right;"><button type="button" class="btn btn-danger delete"><i class="fa fa-trash-o"></i></button></td>';

        num_ope_subc++;
        num_ope_subc_indice++;
      <?php } ?>
      $(wrapper_ope_subc).append(cadena_html_ope_subc);

      
      $(add_button_ope_subc).click(function(e){
        var cadena_html2_ope_subc = "";

        // Operarios Subcontratas
	      cadena_html2_ope_subc = "<tr>";
	      cadena_html2_ope_subc += "<td>";
	      cadena_html2_ope_subc += '<?php echo DB_COMBOBOX("LIST_INSPECCIONES_SUBCONTRATAS","Codigo","Nombre","","Nombre","cmb_subc[]","cmb_subc[]","form-control","","","","javascript: rellena_operarios_subcontrata($(this).val(), ' + num_ope_subc_indice + ');")?>';
	      cadena_html2_ope_subc += '</td>';
	      cadena_html2_ope_subc += '<td><div id="capa_' + num_ope_subc_indice + '">';
	      cadena_html2_ope_subc += '<?php echo DB_COMBOBOX("LIST_INSPECCIONES_SUBCONTRATAS_OPERARIOS","Codigo","Nombre","Subcontrata=0","Nombre","cmb_ope_subc[]","cmb_ope_subc[]","form-control","","","","");?>';
	      cadena_html2_ope_subc += '</div></td>';
	      cadena_html2_ope_subc += '<td style="text-align: right;"><button type="button" class="btn btn-danger delete"><i class="fa fa-trash-o"></i></button></td>';

        e.preventDefault();
        if(num_ope_subc < max_filtros_ope_subc){
            num_ope_subc++;
            num_ope_subc_indice++;
            $(wrapper_ope_subc).append(cadena_html2_ope_subc);
        } else {
          alert('Has alcanzado el límite de operarios por subcontrata disponibles');
        }
      });

      $(wrapper_ope_subc).on("click",".delete", function(e){
        e.preventDefault(); 
        $(this).parents('tr').remove(); 
        num_ope_subc--;
        num_ope_indice--;
      });

    }

    function rellena_operarios_subcontrata(subcontrata, indice) {
      if (subcontrata == "") {
      	subcontrata = 0;
      }
      
      cargar("cmb_operarios_subcontrata.php?indice=" + indice + "&subcontrata=" + subcontrata, "capa_" + indice);
    }
  </script>

  <!-- DIBUJO A MANO ALZADA (INICIO) -->
  <script type="text/javascript">
    /* Variables de Configuracion */
    var idCanvas='canvas';
    //var idForm='formCanvas';
    var idForm='formulario';
    var inputImagen='imagen';
    var estiloDelCursor='crosshair';
    var colorDelTrazo='#555';
    var colorDeFondo='#fff';
    var grosorDelTrazo=2;
     
    /* Variables necesarias */
    var contexto=null;
    var valX=0;
    var valY=0;
    var flag=false;
    var imagen=document.getElementById(inputImagen); 
    var anchoCanvas=document.getElementById(idCanvas).offsetWidth;
    var altoCanvas=document.getElementById(idCanvas).offsetHeight;
    var pizarraCanvas=document.getElementById(idCanvas);
     
    /* Esperamos el evento load */
    window.addEventListener('load',IniciarDibujo,false);
     
    function IniciarDibujo(){
      /* Creamos la pizarra */
      pizarraCanvas.style.cursor=estiloDelCursor;
      contexto=pizarraCanvas.getContext('2d');
      contexto.fillStyle=colorDeFondo;
      contexto.fillRect(0,0,anchoCanvas,altoCanvas);
      contexto.strokeStyle=colorDelTrazo;
      contexto.lineWidth=grosorDelTrazo;
      contexto.lineJoin='round';
      contexto.lineCap='round';
      /* Capturamos los diferentes eventos */
      pizarraCanvas.addEventListener('mousedown',MouseDown,false);
      pizarraCanvas.addEventListener('mouseup',MouseUp,false);
      pizarraCanvas.addEventListener('mousemove',MouseMove,false);
      pizarraCanvas.addEventListener('touchstart',TouchStart,false);
      pizarraCanvas.addEventListener('touchmove',TouchMove,false);
      pizarraCanvas.addEventListener('touchend',TouchEnd,false);
      pizarraCanvas.addEventListener('touchleave',TouchEnd,false);
    }
     
    function MouseDown(e){
      flag=true;
      contexto.beginPath();
      valX=e.pageX-posicionX(pizarraCanvas); valY=e.pageY-posicionY(pizarraCanvas);
      contexto.moveTo(valX,valY);
    }
     
    function MouseUp(e){
      contexto.closePath();
      flag=false;
    }
     
    function MouseMove(e){
      if(flag){
        contexto.beginPath();
        contexto.moveTo(valX,valY);
        valX=e.pageX-posicionX(pizarraCanvas); valY=e.pageY-posicionY(pizarraCanvas);
        contexto.lineTo(valX,valY);
        contexto.closePath();
        contexto.stroke();
      }
    }
     
    function TouchMove(e){
      e.preventDefault();
      if (e.targetTouches.length == 1) { 
        var touch = e.targetTouches[0]; 
        MouseMove(touch);
      }
    }
     
    function TouchStart(e){
      if (e.targetTouches.length == 1) { 
        var touch = e.targetTouches[0]; 
        MouseDown(touch);
      }
    }
     
    function TouchEnd(e){
      if (e.targetTouches.length == 1) { 
        var touch = e.targetTouches[0]; 
        MouseUp(touch);
      }
    }
     
    function posicionY(obj) {
      var valor = obj.offsetTop;
      if (obj.offsetParent) valor += posicionY(obj.offsetParent);
      return valor;
    }
     
    function posicionX(obj) {
      var valor = obj.offsetLeft;
      if (obj.offsetParent) valor += posicionX(obj.offsetParent);
      return valor;
    }
     
    /* Limpiar pizarra */
    function LimpiarTrazado(){
      contexto=document.getElementById(idCanvas).getContext('2d');
      contexto.fillStyle=colorDeFondo;
      contexto.fillRect(0,0,anchoCanvas,altoCanvas);
    }
     
    /* Enviar el trazado */
    function GuardarTrazado(){
      imagen.value=document.getElementById(idCanvas).toDataURL('image/png');
      //document.forms[idForm].submit();
    }
  </script>
  
  	<?php 
	// comprovamos si se envió la imagen
	if (isset($_POST['imagen'])) { 
	    // mostrar la imagen
	    echo '<img src="'.$_POST['imagen'].'" border="1">';

	    // funcion para gusrfdar la imagen base64 en el servidor
	    // el nombre debe tener la extension
	    function uploadImgBase64 ($base64, $name){
	        // decodificamos el base64
	        $datosBase64 = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64));
	        // definimos la ruta donde se guardara en el server
	        $path= $_SERVER['DOCUMENT_ROOT'].'/firmas/'.$name;
	        // guardamos la imagen en el server
	        if(!file_put_contents($path, $datosBase64)){
	            // retorno si falla
	            return false;
	        }
	        else{
	            // retorno si todo fue bien
	            return true;
	        }
	    }

	    // llamamos a la funcion uploadImgBase64( img_base64, nombre_fina.png) 
	    uploadImgBase64($_POST['imagen'], 'mi_imagen_'.date('d_m_Y_H_i_s').'.png' );
	}
	?>
     
    <?php if (isset($_POST['imagen'])) { ?>
    <div class='centrador'>
        <img src="<?php echo $_POST['imagen'];?>" border="1">
    </div>
    <?php } ?>
  <!-- DIBUJO A MANO ALZADA (FIN) -->

</body>

</html>
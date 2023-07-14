<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1">
    
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp"></script>
    <script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
    <script type="text/javascript" src="js/validacion.js"></script>

    <title>Moneleg - Partes de Trabajo</title>
 
    <!-- CSS de Bootstrap -->
    <link href="bootstrap-3.2.0/css/bootstrap.min.css" rel="stylesheet" media="screen">
    
    <link rel="stylesheet" href="plugins/font-awesome/css/font-awesome.css">
    <link rel="stylesheet" type="text/css" href="css/framework.css" media="screen">
    
    <!-- librerías opcionales que activan el soporte de HTML5 para IE8 -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <?php
      include "comun/funciones.php";
      include "comun/db_con.php";
      
      session_start();
      
      if (!isset($_SESSION['GLB_USR_ID'])) {
        php_redirect('index.php');
      }
      
      $op = $_REQUEST['op'];
      $id = $_REQUEST['id'];
      $par_tip = 5; // Tipo de Parte (tabla PAR_TIP)
      $img_ctd = 0;
      
      // capturar el tiempo de desplazamiento
      $par_jor_ult_fec=hora_traslado_inicio($_SESSION['GLB_USR_ID']);
      $par_ini_fec=ultima_hora_inicio_parte($_SESSION['GLB_USR_ID']);
      if ($par_jor_ult_fec!=0){
        $des_hor=calcular_tiempo_trasnc($par_jor_ult_fec,$par_ini_fec);
      }

      if ($op != 'C') {
        $sentencia = DB_CONSULTA("SELECT * FROM PAR WHERE PAR_ID=" . $id);
        if (mysql_num_rows($sentencia) == 1) {
          $row = mysql_fetch_assoc($sentencia);
          $par_id = $row['PAR_ID'];
          $par_fec = date("Y-m-d", strtotime($row['PAR_FEC']));
          $par_hini = STR_Hora($row['PAR_HINI']);
          $par_hfin = STR_Hora($row['PAR_HFIN']);
          $par_rea = $row['PAR_REA'];
          $par_pob = $row['PAR_POB'];
          $par_obs = $row['PAR_OBS'];

          $sentencia_ope = DB_CONSULTA("SELECT * FROM PAR_DET WHERE PD_PAR=" . $id . " AND PD_OPE=" . $_SESSION['GLB_USR_ID']);
          $i = 0;
          $par_ope = array(2);
          
          while ($row = mysql_fetch_assoc($sentencia_ope)) {
            $par_ope[$i] = array(
              'pd_ope' => $row['PD_OPE'],
              'pd_nor' => $row['PD_NOR'],
              'pd_ext' => $row['PD_EXT']
            );
            $hor_dif=$row['PD_NOR']+$row['PD_EXT'];
            $i++;
          }

          $sentencia_img = DB_CONSULTA("SELECT * FROM PAR_IMG WHERE PI_PAR=" . $id);
          $par_img = array();
          while ($row = mysql_fetch_assoc($sentencia_img)) {
            $par_img[] = $row['PI_IMG'];
          }
        } else {
          exit;
        }
      } else {
        $hor_dif = calcular_tiempo_trasnc_initra($_SESSION['GLB_USR_ID']);
        $par_fec = str_html_fecha(getdate());
        $par_hini = STR_hora2(ultima_hora_inicio_parte($_SESSION['GLB_USR_ID']));
        //$par_hfin = date("H:i:00");
        $par_hfin = substr(str_replace("'", "", STR_fechor_esc15("FIN_TRA")), 11);
        $par_ope = array(2);
        $sentencia = DB_CONSULTA("SELECT USR_ID, USR_COM_HAB, USR_VEH_HAB FROM USR_WEB WHERE USR_ID=" . $_SESSION['GLB_USR_ID']);
        if (mysql_num_rows($sentencia) == 1) {
          $row = mysql_fetch_assoc($sentencia);
          for ($i=0; $i<=1; $i++) {
            if ($i == 0) {
              $par_ope[$i] = array(
                'pd_ope' => $_SESSION['GLB_USR_ID'],
                'pd_nor' => $hor_dif,
                'pd_ext' => ''
              );
            } else {
              $par_ope[$i] = array(
                'pd_ope' => $row['USR_COM_HAB'],
                'pd_nor' => $hor_dif,
                'pd_ext' => ''
              );
            }
          }
        }

        $fecha_hora_actual = date("Y-m-d H:i:s");
        $fecha_dia_anterior = fecha_dia_anterior(date("Y-m-d"));
        // capturar pee_id del trabajo en curso
        $pee_id = DB_LEE_CAMPO("USR_JOR","UJ_PEE","UJ_USU=" . $_SESSION['GLB_USR_ID'] . " AND UJ_JOR=0 AND UJ_TIP_STOP=1 AND UJ_FEC_INI>=" . STR_formato_cadena($fecha_dia_anterior) . " AND UJ_FEC_INI<=" . STR_formato_cadena($fecha_hora_actual) . " AND UJ_FEC_FIN IS NULL");
      }

    ?>
    
  <script type="text/javascript">

      // Muy importante. Necesaria para validar entradas en campos input de tipo numérico
      window.onload = function() {
          validar_campos_input();
      }

      function marcar_conductores() {
        var ope_sel="";
        for(var i = 1; i < 3; i++) {
          ope_sel=document.getElementById("cmb_ope" + i)[document.getElementById("cmb_ope" + i).selectedIndex].innerHTML;
          document.getElementById("txt_ope_sel" + i).value=ope_sel;
        }
      }

      function habilita_mano_obra_seleccionado(bseleccionado) {
        if (bseleccionado) {
          document.getElementById("li_mano_obra_seleccionado").className = "active";
          document.getElementById("li_mano_obra_todos").className = "";
          mostrar_mano_obra_seleccionado();
        } else {
          document.getElementById("li_mano_obra_seleccionado").className = "";
          document.getElementById("li_mano_obra_todos").className = "active";
          mostrar_mano_obra_seleccionado();
        }
      }

      function mostrar_mano_obra_seleccionado() {
        var filas;
        var bseleccionado;
        
        if (document.getElementById("li_mano_obra_seleccionado").className == 'active') {
          bseleccionado = true;
        } else {
          bseleccionado = false;
        }
      
        filas = document.getElementsByClassName("mano_obra");

        for (var i = 0; i < filas.length; i++) {
          if (bseleccionado) {
            var txt_cant = document.formulario.elements["txt_cantidades[]"];
            var cantidad = 0;
            
            if (txt_cant[i].value == '' || isNaN(txt_cant[i].value)) {
              cantidad = 0;
            } else {
              cantidad = parseFloat(txt_cant[i].value);
            }

            if (cantidad > 0) {
              filas[i].style.display = "";
            } else {
              filas[i].style.display = "none";
            }
          } else {
            filas[i].style.display = "";
          }
        }
      }
      
      function registrar_seleccion_opes(cod_ope,checked){
        var str=document.getElementById('txt_pla_ope_sel').value;
        var res=str.replace(cod_ope+",","");
        if (checked==1){
          document.getElementById('txt_pla_ope_sel').value = res + cod_ope + ',';
        } else {
          document.getElementById('txt_pla_ope_sel').value = res;
        }
      }
      
      function horas_normales(){
        var hor;
        var ext;
        var noc;
        var aux;

        if (document.formulario.txt_dif_hor.value.length==0){
          hor=0;
        } else {
          hor=document.formulario.txt_dif_hor.value;
        }

        if (document.formulario.txt_hext1.value.length==0){
          ext=0;
        } else {
          ext=document.formulario.txt_hext1.value;
        }

        noc=0;
        aux=hor-ext-noc;
        if (aux<0){
          aux=0;
          //document.formulario.txt_hnor1.value=document.formulario.txt_dif_hor.value;
          //msg = "Las horas Extras no pueden ser superior a: " + hor;
          //document.getElementById("modal_errores_text").innerHTML = msg;
          //$("#modal_errores").modal("show");
          //return 0;
        }
        document.formulario.txt_hnor1.value=aux.toFixed(2);
        document.formulario.txt_hnor_1.value=document.formulario.txt_hnor1.value;
      }

      function validar_formulario(){ 
        var msg;
        msg = ""

        //alert("TRAZA 1");

        if (document.formulario.cmb_ope1.value.length == 0) {
          msg += "<p>Es necesario indicar un operario 1.</p>";
        } else if (isNaN(document.formulario.cmb_ope1.value.replace(",","."))) {
          msg += "<p>Es necesario indicar un Operario 1 válido.</p>";
        }

        if (document.formulario.txt_hnor1.value.length == 0 && document.formulario.txt_hext1.value.length == 0) {
          msg += "<p>Es necesario indicar las horas del operario 1.</p>";
        } else if (isNaN(document.formulario.txt_hnor1.value) && !isNaN(document.formulario.txt_hext1.value)) {
          msg += "<p>Es necesario indicar las horas del operario 1.</p>";
        }

        if (document.formulario.txt_fec.value.length==0) {
          msg = msg + "<p>Es necesario indicar una Fecha.</p>";
        } else if (!fecha_valida(document.formulario.txt_fec.value)) {
          msg = msg + "<p>La Fecha indicada no tiene un formato v&aacute;lido. DD/MM/YYYY</p>";
        }

        /*
        if (document.formulario.txt_img1.length == 0) {
          msg += "<p>Es necesario adjuntar, al menos, una foto del trabajo.</p>";
        }
        */

        if (document.formulario.cmb_pob.value.length == 0) {
          msg += "<p>Es necesario indicar una poblaci&oacute;n.</p>";
        } else if (isNaN(document.formulario.cmb_pob.value.replace(",","."))) {
          msg += "<p>Es necesario indicar una poblaci&oacute;n.</p>";
        }

        // Verificar el nuevo "añadir fotos"
        if (document.formulario.lbl_img_jpg.value.length == 0) {
          msg += "<p>Es necesario adjuntar, al menos, una foto del trabajo.</p>";
        }

        if (msg != "") {
          //document.getElementById("errores").innerHTML = msg
          document.getElementById("modal_errores_text").innerHTML = msg;
          $("#modal_errores").modal("show");
          return 0;
        } else {
          //document.getElementById("cargando").style.display = "block";
          //document.formulario.submit();
          // Se cambia por petición Ajax con barra de progreso
          uploadAjax("exe_parte_contadores.php");
        }
      }

      function habilitar_capa(id) {
        document.getElementById('nuevoparte1').style.display = "none";
        document.getElementById('nuevoparte3').style.display = "none";
        document.getElementById('nuevoparte4').style.display = "none";
        document.getElementById('nuevoparte5').style.display = "none";
        document.getElementById('nuevoparte6').style.display = "none";
        document.getElementById('nuevoparte_fin').style.display = "none";
        document.getElementById(id).style.display = "block";
      }


    function GUI_COMBO_SELTEXT(combo, elemento) {
      var cantidad = combo.length;
      var i = 0;
      var encontrado = false;

      while (i<cantidad && !encontrado) {
        if (combo[i].text == elemento) {
          combo[i].selected = true;
          encontrado = true;
        } else {  
          i++;
        }
      }
    }

  
    function muestra_progreso(porcentaje) {
      var msg;
      
      msg = '<h3 style="text-align:center;">' + porcentaje + "%</h3>";
      msg += '<div class="progress">';
      msg += '<div class="progress-bar progress-bar-infor" role="progressbar" aria-valuenow="' + porcentaje + '" aria-valuemin="0" aria-valuemax="100" style="width:' + porcentaje + '%">';
      //msg += "<span>" + porcentaje + "%</span>";
      msg += "</div>";
      msg += "</div>";

      if (porcentaje == 0) {
        $("#cargando").html("").show();
      }
      
      $("#cargando").html(msg);
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
                var myXhr = $.ajaxSettings.xhr();
                if(myXhr.upload){
                    myXhr.upload.addEventListener('progress',progress, false);
                }
                return myXhr;
              },
              beforeSend: function() {
                $("#btn_guardar").attr("disabled", true);
                muestra_progreso(0);
              },
          }).done(function(respuesta) {
                texto_html = '<div class="alert alert-' + respuesta.tipo_mensaje_alerta + '">' + respuesta.mensaje + '</div>';
                texto_html += '<p style="text-align:center;">';
                texto_html += '<button type="button" class="btn btn-' + respuesta.tipo_mensaje_alerta + ' btn-lg" onClick="' + respuesta.onclick + '">Aceptar</button>';
                texto_html += '</p>';
                texto_html += '</div>';
                $("#btn_guardar").attr("disabled", false);
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

          if (Percentage >= 0 && Percentage <= 100) {
            muestra_progreso(Percentage);
          }
      }  
    }


    /* Código para input type="file" */
    $(document).on('change', '.btn-file :file', function() {
      var input = $(this),
      numFiles = input.get(0).files ? input.get(0).files.length : 1,
      label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
      input.trigger('fileselect', [numFiles, label]);
    });

    function mostrar_miniatura(input, id) {
      if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
          $('#'+id).attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
      }
    }

    $(document).ready( function() {
      $('.btn-file :file').on('fileselect', function(event, numFiles, label) {
        
        var input = $(this).parents('.input-group').find(':text'),
            log = numFiles > 1 ? numFiles + ' ficheros seleccionados' : label;
        
        if( input.length ) {
            input.val(log);
        } else {
            if( log ) alert(log);
        }
        
      });
      
      <?php
      for($i=1; $i<=$img_ctd; $i++) {
        ?>
        $('#txt_img<?php echo $i;?>').change(function(){
          mostrar_miniatura(this, 'img_foto<?php echo $i;?>');
        });
        <?
      }
      ?>
      
    });

  </script>
</head>

<body class="framework" onload="javascript: marcar_conductores();">
  <div class="container">
    <form name="formulario" id="formulario" class="form-horizontal" role="form" method="POST" action="exe_parte_contadores.php" enctype="multipart/form-data" onsubmit="return false;">
    <input type="hidden" name="op" value="<?php echo $op;?>" />
    <input type="hidden" name="id" value="<?php echo $id;?>" />
    <input type="hidden" name="par_tip" value="<?php echo $par_tip;?>" />
    <input type="hidden" name="txt_des_hor" id="txt_des_hor" value="<?php echo $des_hor; ?>">
    
    <!-- Modal Errores -->
    <div class="modal fade" id="modal_errores" role="dialog">
      <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Guardar Parte de Trabajo</h4>
          </div>
          <div class="modal-body alert-warning" id="modal_errores_text">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="javascript: habilitar_capa('nuevoparte1');">Cerrar</button>
          </div>
        </div>
      </div>
    </div>

    <div id="nuevoparte1">
      <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
              <span class="navbar-left"><a href="javascript: window.history.back();" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
              <span class="navbar-right"><a href="javascript: horas_normales();habilitar_capa('nuevoparte3');" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
              <p class="titulo">Nuevo Parte 1/5</span>
      </nav>
      
      <h4>Datos de Trabajadores</h4>
      <?php
      $horas_disabled="";
      if ($op == "M"){
        $horas_disabled="disabled='disabled'";
      }
      ?>

      <!-- Bloque de Trabajadores -->
      <div class="bloque">
        <div class="form-group bloque-reducido">
          <label for="operario1" class="col-lg-2">Operario1 (Conductor):</label>
          <div class="col-lg-10">
            <?php echo DB_COMBOBOX("LIST_OPERARIOS","Codigo","Nombre","","Nombre","cmb_ope1","cmb_ope1","form-control",$par_ope[0]['pd_ope'],"","1","javascript: marcar_conductores();"); ?>
            <input type="hidden" id="txt_ope" name="txt_ope" value="<?php echo $par_ope[0]['pd_ope'] ?>">
            <input type="hidden" id="txt_fec" name="txt_fec" value="<?php echo $par_fec;?>"></input>  
            <input type="hidden" id="txt_hini" name="txt_hini" value="<?php echo $par_hini;?>" />
            <input type="hidden" id="txt_hfin" name="txt_hfin" value="<?php echo $par_hfin;?>"  />
          </div>
        </div>
        <div class="form-group bloque-reducido">
          <label for="horas_normales1" class="col-xs-3 col-xs-offset-1">Normales (máx: <?php echo $hor_dif ?>h):</label>
          <div class="col-xs-3">
            <input type="number" class="form-control decimal" id="txt_hnor1" name="txt_hnor1" disabled="disabled" value="<?php echo $par_ope[0]['pd_nor'];?>" />
            <input type="hidden" id="txt_hnor_1" name="txt_hnor_1" value="<?php echo $par_ope[0]['pd_nor'];?>" />
            <input type="hidden" id="txt_dif_hor" name="txt_dif_hor" value="<?php echo $hor_dif ?>">
          </div>
          <label for="horas_extras1" class="col-xs-2">Extras:</label>
          <div class="col-xs-3">
            <input type="number" class="form-control decimal" id="txt_hext1" name="txt_hext1" <?php echo $horas_disabled;?> onchange="javascript:horas_normales();" value="<?php echo $par_ope[0]['pd_ext'];?>"  />
          </div>
        </div>
        <br><br>

        <h4>Datos de la Actuaci&oacute;n</h4>
        <div class="form-group bloque-reducido">
          <label for="poblacion" class="col-lg-2">PDS:</label>
          <div class="col-lg-10">
            <?php echo DB_COMBOBOX("LIST_POBLACIONES_CONTADORES","Codigo","Nombre","","Nombre","cmb_pob","cmb_pob","form-control",$par_pob,"","",""); ?>
          </div>
        </div>
      </div>

      <?php
        if ($pee_id!=""){
          ?>
          <div class="col-lg-10">
            <label for="ope_sel<?php echo $i ?>" class="">Finalizar trabajo a:</label>
            <input type="hidden" name="txt_pla_ope_sel" id="txt_pla_ope_sel" >
            <?php 
            echo DB_LIST_CHECK_PARTES("UJ_PLA_JEF=0 AND UJ_FEC_FIN IS NULL AND UJ_FEC_INI>=CURDATE()-1 AND UJ_PEE=".$pee_id,"","","onchange=javascript:registrar_seleccion_opes(this.value,this.checked);","",$op);
            ?>
          </div>
          <?php
          }
        ?>

    </div>    
    <!-- Fin Nuevo Parte1/6 -->

    <!-- Nuevo Parte 3/6 -->
    <div id="nuevoparte3" style="display:none">
      <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
              <span class="navbar-left"><a href="javascript: habilitar_capa('nuevoparte1');" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
              <span class="navbar-right"><a href="javascript: habilitar_capa('nuevoparte4');" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
              <p class="titulo">Nuevo Parte 2/5</span>
      </nav>

      <ul class="nav nav-pills" style="margin-bottom:.5em;">
        <li id="li_mano_obra_todos" class="active"><a href="#" onclick="javascript:habilita_mano_obra_seleccionado(false);">Todos</a></li>
        <li id="li_mano_obra_seleccionado"><a href="#" onclick="javascript: habilita_mano_obra_seleccionado(true);">Seleccionado</a></li>
      </ul>
      
      <h4>Producci&oacute;n</h4>
      <?php echo DB_PARTE_ARTICULOS_TIPO_PARTE($id, $par_tip); ?>

    </div>      
    <!-- Fin Nuevo Parte 3/6 -->

    <!-- Nuevo Parte 4/6 -->
    <div id="nuevoparte4" style="display:none">
      <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
              <span class="navbar-left"><a href="javascript: habilitar_capa('nuevoparte3');" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
              <span class="navbar-right"><a href="javascript: habilitar_capa('nuevoparte5');" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
              <p class="titulo">Nuevo Parte 3/5</span>
      </nav>

      <h4>Descripci&oacute;n del Trabajo</h4>
      
      <div class="form-group">
        <label for="realizado" class="col-lg-2">Realizado:</label>
        <div class="col-lg-10">
          <textarea class="form-control" rows="2" name="txt_rea"><?php echo $par_rea;?></textarea>
        </div>
      </div>

      <div class="form-group">
        <label for="observaciones" class="col-lg-2">Observaciones:</label>
        <div class="col-lg-10">
          <textarea class="form-control" rows="2" name="txt_obs"><?php echo $par_obs;?></textarea>
        </div>
      </div>
    </div>
    <!-- Fin Nuevo Parte4/6 -->

    <div id="nuevoparte5" style="display:none">
      <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
              <span class="navbar-left"><a href="javascript: habilitar_capa('nuevoparte4');" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
              <span class="navbar-right"><a href="javascript: habilitar_capa('nuevoparte6');" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
              <p class="titulo">Nuevo Parte 4/5</span>
      </nav>

      <h4>Fotos</h4>
      
      <div class="col-xs-12">
        <div class="input-group form-group">
          <span class="input-group-btn">
              <span class="btn btn-primary btn-file">
                Selecci&oacute; Múlt.&hellip; <input type="file" id="select_image" name="txt_img[]" value="txt_img[]" accept=".jpg,.jpeg" multiple="multiple">
              </span>
          </span>
          <input type="text" class="form-control" readonly id="lbl_img<?php echo $i;?>" name="lbl_img<?php echo $i;?>" value="<?php echo $par_img[$i-1];?>">
          <span class="input-group-btn">
            <span class="btn btn-default" onclick="javascript: document.formulario.lbl_img<?php echo $i;?>.value='';document.formulario.txt_img<?php echo $i;?>.value='';$('#img_foto<?php echo $i;?>').attr('src','img/ico_camera.png');"><i class="fa fa-times"></i></span>
          </span>
        </div>
      </div>

      <?php
      for($i=1; $i<=$img_ctd; $i++) {
        ?>
        <div class="col-xs-12">
          <div class="input-group form-group">
            <span class="input-group-btn">
                <span class="btn btn-primary btn-file">
                  Seleccionar&hellip; <input type="file" id="txt_img<?php echo $i;?>" name="txt_img<?php echo $i;?>" accept=".jpg,.jpeg">
                </span>
            </span>
            <input type="text" class="form-control" readonly id="lbl_img" name="lbl_img" value="">
          </div>
        </div>
        <?
      }
      ?>

      <h4>Vista previa de Im&aacute;genes</h4>
      <?php
      for($i=1; $i<=$img_ctd; $i++) {
        ?>
        <div class="col-xs-3">
          <?php if ($par_img[$i-1] == '') {
            $imagen_preliminar = "img/ico_camera.png";
          } else {
            $imagen_preliminar = "../partes_imagenes/" .  $par_img[$i-1];
          }?>
          <img src="<?php echo $imagen_preliminar;?>" width="75" class="img_miniatura" id="img_foto<?php echo $i;?>" name="img_foto<?php echo $i;?>"/>
        </div>
        <?
      }
      ?>
      
      <!-- Establecida la opción de añadir imágen -->
      <?php
        $imagen = DB_CONSULTA("SELECT * FROM PAR_IMG WHERE PI_PAR=" . $id);
        $par_img_jpg = array();
        while ($row = mysql_fetch_assoc($imagen)) {
          $par_img_jpg[] = $row['PI_IMG'];
        }
      ?>

      <!-- Añadir múltiples imágenes -->
      <label for="descripcion" style="margin-top: 6%;">Designación de RP, JT y asignación parejas Buddy Partner / Charla diaria de SYS:</label>

      <div class="col-xs-12 mb-lg-4 mt-lg-4">
        <div class="input-group form-group">
          <span class="input-group-btn">
              <span class="btn btn-primary btn-file">
                <i class="fa fa-picture-o" aria-hidden="true"></i>
                Añadir Fotos <input type="file" id="fil_image" name="archivo[]" value="archivo[]" accept=".jpg" multiple="multiple">
              </span>
          </span>
          <input type="text" class="form-control" readonly id="lbl_img_jpg" name="lbl_img_jpg<?php echo $i;?>" value="<?php echo $par_img_jpg[$i-1];?>">
          <span class="input-group-btn">
            <span class="btn btn-default" onclick="javascript: document.formulario.lbl_img_jpg<?php echo $i;?>.value='';document.formulario.txt_img_jpg<?php echo $i;?>.value='';$('#img_foto<?php echo $i;?>').attr('src','img/ico_camera.png');"><i class="fa fa-times"></i></span>
          </span>
        </div>
      </div>
      
    </div>
    <!-- Fin Nuevo Parte 5/6 -->

    <!-- Inicio Nuevo Parte 6/6 -->
    <div id="nuevoparte6" style="display:none">
      <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
              <span class="navbar-left"><a href="javascript: habilitar_capa('nuevoparte5');" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
              <span class="navbar-right" style="visibility:hidden;"><a href="javascript: habilitar_capa('nuevoparte6');" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
              <p class="titulo">Nuevo Parte 5/5</span>
      </nav>

      <div id="cargando"></div>
      <button type="button" id="btn_guardar" class="btn btn-danger btn-block" onclick="javascript: validar_formulario();">Guardar</button>
    </div>
    <!-- Fin Nuevo Parte 6/6 -->

    <!-- Muestra Id del registro guardado -->
    <div id="nuevoparte_fin"></div>
    <!-- Fin nuevoparte_fin -->


    </form>
  
  </div> <!-- Fin .container -->
  <script type="text/javascript" src="bootstrap-3.2.0/js/bootstrap.min.js"></script>
</body>

</html>
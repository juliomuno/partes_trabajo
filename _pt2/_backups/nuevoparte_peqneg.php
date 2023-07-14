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
      $par_tip = 2; // Tipo de Parte (tabla PAR_TIP)
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
          $par_ninc = $row['PAR_NINC'];
          $par_dir = $row['PAR_DIR'];
          $par_pob = $row['PAR_POB'];
          $par_rea = $row['PAR_REA'];
          $par_obs = $row['PAR_OBS'];
          $par_veh = $row['PAR_VEH'];
          $par_km = $row['PAR_KM'];
          $par_km_hor = STR_hora($row['PAR_KM_HOR']);

          $sentencia_ope = DB_CONSULTA("SELECT * FROM PAR_DET WHERE PD_PAR=" . $id . " AND PD_OPE=" . $_SESSION['GLB_USR_ID']);
          while ($row = mysql_fetch_assoc($sentencia_ope)) {
            $par_ope = $row['PD_OPE'];
            $par_hnor = $row['PD_NOR'];
            $par_hext = $row['PD_EXT'];
            $hor_dif = $row['PD_NOR']+$row['PD_EXT'];
          }

          $sentencia_tra = DB_CONSULTA("SELECT * FROM PAR_TRA WHERE PT_PAR=" . $id);
          if (mysql_num_rows($sentencia_tra) == 1) {
            $row = mysql_fetch_assoc($sentencia_tra);
            $par_tra = $row['PT_TRA'];
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
        $par_fec = str_html_fecha(getdate());
        //$par_hini = ultima_hora_parte();
        $par_hini = STR_hora2(ultima_hora_inicio_parte($_SESSION['GLB_USR_ID']));
        //$par_hfin = date("H:i:00");
        $par_hfin = substr(str_replace("'", "", STR_fechor_esc15("FIN_TRA")), 11);
        $hor_dif = calcular_tiempo_trasnc_initra($_SESSION['GLB_USR_ID']);
        $par_hnor = $hor_dif;
        $par_hext = 0;
        $sentencia = DB_CONSULTA("SELECT USR_ID, USR_VEH_HAB FROM USR_WEB WHERE USR_ID=" . $_SESSION['GLB_USR_ID']);
        if (mysql_num_rows($sentencia) == 1) {
          $row = mysql_fetch_assoc($sentencia);
          $par_ope = $_SESSION['GLB_USR_ID'];
          $par_veh = $row['USR_VEH_HAB'];    
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

        if (document.formulario.cmb_ope.value.length == 0) {
          msg += "<p>Es necesario indicar un operario.</p>";
        } else if (isNaN(document.formulario.cmb_ope.value.replace(",","."))) {
          msg += "<p>Es necesario indicar un Operario válido.</p>";
        }

        if ((document.formulario.txt_ninc.value.length > 0) && isNaN(document.formulario.txt_ninc.value)) {
          msg += "<p>Es necesario indicar un N&deg; de Incidencia num&eacute;rico.</p>";
        }

        if (document.formulario.txt_dir.value.length == 0) {
          msg += "<p>Es necesario indicar una Direcci&oacute;n.</p>";
        }

        if (document.formulario.cmb_pob.value.length == 0) {
          msg += "<p>Es necesario indicar una Poblaci&oacute;n.</p>";
        } else if (isNaN(document.formulario.cmb_pob.value.replace(",","."))) {
          msg += "<p>Es necesario indicar una Poblaci&oacute;n v&aacute;lida.</p>";
        } 

        // Comprobar que se ha seleccionado un tipo de trabajo
        if (gui_option_leevalor(document.formulario.opt_tip_tra) == '') {
          msg += "<p>Es necesario indicar el Tipo de Trabajo realizado.</p>";
        }
        
        /*
        if (document.formulario.txt_img1.length == 0) {
          msg += "<p>Es necesario adjuntar, al menos, una foto del trabajo.</p>";
        }
        */

        if (msg != "") {
          //document.getElementById("errores").innerHTML = msg
          document.getElementById("modal_errores_text").innerHTML = msg;
          $("#modal_errores").modal("show");
          return 0;
        } else {
          //document.getElementById("cargando").style.display = "block";
          //document.formulario.submit();
          // Se cambia por petición Ajax con barra de progreso
          uploadAjax("exe_parte_peqneg.php");
          
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

    /*var options_localizacion = {
      enableHighAccuracy: true,
      timeout: 3000,
      maximumAge: 0
    };*/

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

    function codeLatLng(position) {
      var latlng = new google.maps.LatLng(parseFloat(document.formulario.latitud.value), parseFloat(document.formulario.longitud.value));
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
        navigator.geolocation.getCurrentPosition(coordenadas,gestiona_errores);
      }else{
        alert('Tu navegador no soporta la API de geolocalizacion');  
      }
    }

    function coordenadas(position) {
      var latitud = position.coords.latitude;
      var longitud = position.coords.longitude;
      var geocoder;

      document.formulario.latitud.value = latitud;
      document.formulario.longitud.value = longitud;
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

    function marcar_conductores() {
      var ope_sel="";
      ope_sel=document.getElementById("cmb_ope")[document.getElementById("cmb_ope").selectedIndex].innerHTML;
      document.getElementById("txt_ope_sel1").value=ope_sel;
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

      // Sólo se permiten 8 dígitos para el Nº de Orden
      // Jquery no implementa la función oninput, por lo que se hace en Javascript
      // Al ser un campo de tipo 'number', no permite la propiedad maxlength
      document.getElementById("txt_ninc").oninput=function(){
        if (this.value.length > 8) {
          this.value = this.value.slice(0,8);
        }
      };
      
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
    <form name="formulario" id="formulario" class="form-horizontal" role="form" method="POST" action="exe_parte_peqneg.php" enctype="multipart/form-data" onsubmit="return false;">
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
              <span class="navbar-right"><a href="javascript: horas_normales();habilitar_capa('nuevoparte2');" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
              <p class="titulo">Nuevo Parte 1/6</span>
      </nav>
      
      <h4>Datos de Trabajadores</h4>
      <?
      $horas_disabled="";
      if ($op == "M"){
        $horas_disabled="disabled='disabled'";
      }
      ?>
      <!-- Bloque de Trabajadores -->
      <div class="bloque">
        <div class="form-group bloque-reducido">
          <label for="operario1" class="col-lg-2">Operario:</label>
          <div class="col-lg-10">
            <?php echo DB_COMBOBOX("LIST_OPERARIOS","Codigo","Nombre","","Nombre","cmb_ope","cmb_ope","form-control",$par_ope,"","1","javascript: marcar_conductores();"); ?>
            <input type="hidden" name="txt_ope_sel1" id="txt_ope_sel1">
            <input type="hidden" id="txt_ope" name="txt_ope" value="<?php echo $par_ope ?>">
            <input type="hidden" class="form-control" name="txt_fec" value="<?php echo $par_fec;?>"></input>  
            <input type="hidden" class="form-control hora" id="txt_hini" name="txt_hini" value="<?php echo $par_hini;?>" />
            <input type="hidden" class="form-control hora" id="txt_hfin" name="txt_hfin" value="<?php echo $par_hfin;?>"  />
          </div>

          <div class="col-xs-6 bloque">
            <label for="horas_normales1" class="">Normales (máx: <?php echo $hor_dif ?>h):</label>
            <input type="hidden" id="txt_dif_hor" name="txt_dif_hor" value="<?php echo $hor_dif ?>">
            <input type="number" class="form-control decimal" id="txt_hnor1" name="txt_hnor1" disabled="disabled" value="<?php echo $par_hnor;?>" />
            <input type="hidden" id="txt_hnor_1" name="txt_hnor_1" value="<?php echo $par_hnor;?>" />
          </div>

          <div class="col-xs-6 bloque">
            <label for="horas_extras<?php echo $i ?>" class="">Extras:</label>
              <input type="number" class="form-control decimal" id="txt_hext1" name="txt_hext1" onchange="javascript:horas_normales();" <?php echo $horas_disabled;?> value="<?php echo $par_hext;?>" />
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
            echo DB_LIST_CHECK_PARTES("UJ_PLA_JEF=0 AND UJ_FEC_FIN IS NULL AND UJ_FEC_INI>=CURDATE()-1 AND UJ_PEE=".$pee_id,"","","onchange=javascript:registrar_seleccion_opes(this.value,this.checked);","2",$op);
            ?>
          </div>
          <?php
          }
        ?>
    </div>    
    <!-- Fin Nuevo Parte1/6 -->

    <!-- Nuevo Parte 2/6 -->
    <div id="nuevoparte2" style="display:none">
      <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
              <span class="navbar-left"><a href="javascript: habilitar_capa('nuevoparte1');" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
              <span class="navbar-right"><a href="javascript: habilitar_capa('nuevoparte3');" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
              <p class="titulo">Nuevo Parte 2/6</span>
      </nav>

      <h4>Datos de la Actuaci&oacute;n</h4>
      <div class="bloque">
        <div class="form-group">
          <label for="num_incidencia" class="col-lg-2">N&deg; de Orden:</label>
          <div class="col-lg-10">
            <input type="number" class="form-control entero" name="txt_ninc" id="txt_ninc" value="<?php echo $par_ninc;?>"></input>
          </div>
        </div>        
      </div>

      <h4>Datos de Localizaci&oacute;n</h4>
            
      <div class="form-group">
          <label for="direccion" class="col-lg-2">Direcci&oacute;n:</label>
          <div class="col-lg-10">
            <div class="input-group">
              <input type="text" class="form-control" name="txt_dir" value="<?php echo $par_dir;?>">
              <span class="input-group-btn">
                <button class="btn btn-default btn-primary" type="button" title="Localización" onclick="javascript: obtener_localizacion();"><i class="fa fa-map-marker"></i></button>
              </span>
            </div>
        </div>
      </div>

      <div class="form-group">
        <label for="poblacion" class="col-lg-2">Poblaci&oacute;n:</label>
        <div class="col-lg-10" id="combo_poblacion">
          <?php echo DB_COMBOBOX("LIST_POBLACIONES","Codigo","Nombre","","Nombre","cmb_pob","cmb_pob","form-control",$par_pob,"","",""); ?>
        </div>
      </div>

      <div class="form-group bloque-reducido" style="display:none;">
        <label for="latitud" class="col-lg-2">Latitud:</label>
        <div class="col-lg-10">
          <input type="text" class="form-control" name="latitud" disabled/>
        </div>
      </div>
      <div class="form-group bloque-reducido" style="display:none;">
        <label for="longitud" class="col-lg-2">Longitud:</label>
        <div class="col-lg-10">
          <input type="text" class="form-control" name="longitud" />
        </div>
      </div>
    </div>
    <!-- Fin Nuevo Parte2/6 -->

    <!-- Nuevo Parte 3/6 -->
    <div id="nuevoparte3" style="display:none">
      <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
              <span class="navbar-left"><a href="javascript: habilitar_capa('nuevoparte2');" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
              <span class="navbar-right"><a href="javascript: habilitar_capa('nuevoparte4');" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
              <p class="titulo">Nuevo Parte 3/6</span>
      </nav>

      <h4>Trabajo Realizado</h4>
      <?php echo DB_LIST_OPTION("LIST_TRABAJOS_PEQUEÑO_NEGOCIO","Codigo","Nombre","","Nombre","opt_tip_tra","", $par_tra);?>
    </div>      
    <!-- Fin Nuevo Parte 3/6 -->

    <!-- Nuevo Parte 4/6 -->
    <div id="nuevoparte4" style="display:none">
      <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
              <span class="navbar-left"><a href="javascript: habilitar_capa('nuevoparte3');" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
              <span class="navbar-right"><a href="javascript: habilitar_capa('nuevoparte5');" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
              <p class="titulo">Nuevo Parte 4/6</span>
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
              <p class="titulo">Nuevo Parte 5/6</span>
      </nav>

      <h4>Fotos</h4>
      
      <div class="col-xs-12">
        <div class="input-group form-group">
          <span class="input-group-btn">
              <span class="btn btn-primary btn-file">
                Selecci&oacute; Múlt.&hellip; <input type="file" id="archivo[]" name="archivo[]" accept=".jpg,.jpeg" multiple="">
              </span>
          </span>
          <input type="text" class="form-control" readonly id="lbl_img" name="lbl_img" value="">
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
            <input type="text" class="form-control" readonly id="lbl_img<?php echo $i;?>" name="lbl_img<?php echo $i;?>" value="<?php echo $par_img[$i-1];?>">
            <span class="input-group-btn">
              <span class="btn btn-default" onclick="javascript: document.formulario.lbl_img<?php echo $i;?>.value='';document.formulario.txt_img<?php echo $i;?>.value='';$('#img_foto<?php echo $i;?>').attr('src','img/ico_camera.png');"><i class="fa fa-times"></i></span>
            </span>
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
    
    </div>
    <!-- Fin Nuevo Parte 5/6 -->

    <!-- Inicio Nuevo Parte 6/6 -->
    <div id="nuevoparte6" style="display:none">
      <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
              <span class="navbar-left"><a href="javascript: habilitar_capa('nuevoparte5');" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
              <span class="navbar-right" style="visibility:hidden;"><a href="javascript: habilitar_capa('nuevoparte6');" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
              <p class="titulo">Nuevo Parte 6/6</span>
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
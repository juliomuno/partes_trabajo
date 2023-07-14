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
      
      $par_tip = 9; // Tipo de Parte (tabla PAR_TIP)

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
          $par_cli_nom = $row['PAR_CLI_NOM'];
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
            $hor_dif=$row['PD_NOR']+$row['PD_EXT'];
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
        $par_id = 0;
        $par_fec = str_html_fecha(getdate());
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
      }

    ?>
    
  <script type="text/javascript">

      // Muy importante. Necesaria para validar entradas en campos input de tipo numérico
      window.onload = function() {
          validar_campos_input();
          cargar("nuevoparte_materiales.php?par_id=<?php echo $par_id; ?>&ope1=" + document.getElementById("cmb_ope").value + "&ope2=0&ope3=0&ope4=0", "capa_materiales");
      }

       function marcar_conductores() {
        var ope_sel="";
        ope_sel=document.getElementById("cmb_ope")[document.getElementById("cmb_ope").selectedIndex].innerHTML;
        document.getElementById("txt_ope_sel").value=ope_sel;
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
        var msg = "";

        
        if (document.formulario.cmb_ope.value.length == 0) {
          msg += "<p>Es necesario indicar un operario.</p>";
        } else if (isNaN(document.formulario.cmb_ope.value.replace(",","."))) {
          msg += "<p>Es necesario indicar un Operario válido.</p>";
        }

        if ((document.formulario.txt_hnor_1.value.length==0) && (document.formulario.txt_hnor_1.value.length=='')) {
          msg += "<p>Es necesario indicar el total de horas.</p>";
        }

        if ((document.formulario.txt_hext1.value.length==0) && (document.formulario.txt_hext1.value.length=='')) {
          document.formulario.txt_hext1.value = 0;
        }

        if (msg != "") {
          //document.getElementById("errores").innerHTML = msg
          document.getElementById("modal_errores_text").innerHTML = msg;
          $("#modal_errores").modal("show");
          return 0;
        } else {
          // Se cambia por petición Ajax con barra de progreso
          uploadAjax("exe_parte_oficina.php");
        }
      }

      function habilitar_capa(id) {
        document.getElementById('nuevoparte1').style.display = "none";
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

      // Sólo se permiten 7 dígitos para el Nº de Orden y 9 si se indica un presupuesto
      // Jquery no implementa la función oninput, por lo que se hace en Javascript
      // Al ser un campo de tipo 'number', no permite la propiedad maxlength
      document.getElementById("txt_ninc").oninput=function(){
        if (this.value.length > 9) {
          this.value = this.value.slice(0,9);
        }
      };
    
      $('#txt_img1').change(function(){
        mostrar_miniatura(this, 'img_foto1');
      });
      
      $('#txt_img2').change(function(){
        mostrar_miniatura(this, 'img_foto2');
      });
      
      $('#txt_img3').change(function(){
        mostrar_miniatura(this, 'img_foto3');
      });
      
      $('#txt_img4').change(function(){
        mostrar_miniatura(this, 'img_foto4');
      });      
    });

  </script>
</head>

<body class="framework" onload="javascript: marcar_conductores();">
	<div class="container">
    <form name="formulario" id="formulario" class="form-horizontal" role="form" method="POST" action="exe_parte_oficina.php" enctype="multipart/form-data" onsubmit="return false;">
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
              <span class="navbar-right"><a href="javascript: horas_normales();habilitar_capa('nuevoparte6');" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
              <p class="titulo">Nuevo Parte 1/2</span>
      </nav>
      
      <h4>Datos de Trabajadores</h4>

      <div class="bloque">
        <div class="form-group bloque-reducido">
          <label for="operario1" class="col-lg-2">Operario:</label>
          <div class="col-lg-10">
            <?php echo DB_COMBOBOX("LIST_OPERARIOS_OFICINA","Codigo","Nombre","","Nombre","cmb_ope","cmb_ope","form-control",$par_ope,"","1","javascript: marcar_conductores(); cargar('nuevoparte_materiales.php?par_id=" . $par_id . "&ope1=' + this.value + '&ope2=0&ope3=0&ope4=0', 'capa_materiales');"); ?>
            <input type="hidden" name="txt_ope_sel" id="txt_ope_sel">
            <input type="hidden" id="txt_ope" name="txt_ope" value="<?php echo $par_ope ?>">
            <input type="hidden" class="form-control" name="txt_fec" value="<?php echo $par_fec;?>"></input>  
            <input type="hidden" class="form-control hora" id="txt_hini" name="txt_hini" value="<?php echo $par_hini;?>" />
            <input type="hidden" class="form-control hora" id="txt_hfin" name="txt_hfin" value="<?php echo $par_hfin;?>"  />
          </div>
        </div>
      </div>

      <div class="col-xs-6 bloque">
        <label for="horas_normales1" class="">Normales (máx: <?php echo $hor_dif ?>h):</label>
        <input type="number" class="form-control decimal" id="txt_hnor1" name="txt_hnor1" disabled="disabled" value="<?php echo $par_hnor;?>" />
        <input type="hidden" id="txt_hnor_1" name="txt_hnor_1" value="<?php echo $par_hnor;?>" />
        <input type="hidden" id="txt_dif_hor" name="txt_dif_hor" value="<?php echo $hor_dif ?>">
      </div>

      <div class="col-xs-6 bloque">
        <label for="horas_extras<?php echo $i ?>" class="">Extras:</label>
          <input type="number" class="form-control decimal" id="txt_hext1" name="txt_hext1" onchange="javascript:horas_normales();" value="<?php echo $par_hext;?>" />
      </div>

      <div class="bloque">
        <br><br><br><br><br>
        <h4>Descripci&oacute;n del Trabajo</h4>

        <div class="form-group">
          <label for="realizado" class="col-lg-2">Realizado:</label>
          <div class="col-lg-10">
            <textarea class="form-control" rows="6" name="txt_rea"><?php echo $par_rea;?></textarea>
          </div>
        </div>

        <div class="form-group">
          <label for="observaciones" class="col-lg-2">Observaciones:</label>
          <div class="col-lg-10">
            <textarea class="form-control" rows="6" name="txt_obs"><?php echo $par_obs;?></textarea>
          </div>
        </div>
      </div>

    </div>    
    <!-- Fin Nuevo Parte1/3 -->

    <!-- Inicio Nuevo Parte 6/6 -->
    <div id="nuevoparte6" style="display:none">
      <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
              <span class="navbar-left"><a href="javascript: habilitar_capa('nuevoparte1');" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
              <span class="navbar-right" style="visibility:hidden;"><a href="javascript: habilitar_capa('nuevoparte6');" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
              <p class="titulo">Nuevo Parte 2/2</span>
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
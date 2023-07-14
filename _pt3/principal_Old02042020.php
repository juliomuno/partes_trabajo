<!DOCTYPE html>
<hmtl lang="es">

<head>
	<title>Bienvenido a Moneleg-Partes de Trabajo</title>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0">
	
  <script src="js/jquery-1.11.0.min.js"></script>
  <script type="text/javascript" src="js/ajax.js"></script>
  <script type="text/javascript" src="js/validacion.js"></script>

  <!-- API Google Maps -->
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDLRg5FYmic8kj1vZJX3SulcNPu9EBjM24" async defer></script>


	<link href="bootstrap-3.2.0/css/bootstrap.min.css" rel="stylesheet" media="screen">
	<link rel="stylesheet" href="font-awesome-4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" href="css/framework.css" media="screen">	
  
  <?php      
    include "comun/funciones.php";
    include "comun/db_con.php";
    session_start();

    if (!isset($_SESSION['GLB_USR_ID'])) {
      php_redirect('index.php');
    }
    
    $usuario = $_SESSION['GLB_USR_ID'];
    $fecha_hora_actual = STR_formato_cadena(date("Y-m-d H:i:s"));
    $fecha_actual = STR_formato_cadena(date("Y-m-d"));
    $fecha_dia_anterior = STR_formato_cadena(fecha_dia_anterior(date("Y-m-d")));
  ?>

    <script type="text/javascript">
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

    function ejecuta(tipo_parte) {
      var msg;
      msg = ""

      if (tipo_parte == '') {
        tipo_parte = document.formulario.par_tip.value;
      }

      if (tipo_parte == "EDIT_PAR") {
        document.location.href = "list_partes.php";
        return 1;
      } else if (tipo_parte == "DOC") {
        document.location.href = "documentacion.php";
      } else if (tipo_parte == "FIN_TRA") {
        document.location.href = "inicio2.php";
      } else if (tipo_parte == 'INSP') {
      	document.location.href = "inspecciones/inspecciones.php";
      }

      /*if (tipo_parte == "INI_JOR" || tipo_parte == "FIN_JOR") {
        document.formulario.par_tip.value = tipo_parte;
      } else if (tipo_parte == "INI_TRA" || tipo_parte == "FIN_TRA") {
        // Verificar imágenes
        if (document.formulario.lbl_img1.value.length == 0) {
          msg += "<p>Es necesario seleccionar una imagen.</p>";
        }
      }*/

      // Verificar imágenes
      /*if (document.formulario.lbl_img1.value.length == 0) {
        msg += "<p>Es necesario seleccionar una imagen.</p>";
      }*/

      // Verificar dirección geolocalizada
      if (document.formulario.txt_dir.value.length == 0) {
        msg += "<p>La dirección es necesaria.</p>";
      }

      if (msg != '') {
          document.getElementById("modal_text").innerHTML = msg;
          $("#myModal").modal("show");
          return 0;
      } else {
        uploadAjax("exe_principal.php");
      }
    }

    function buscar_imagen(tipo_parte) {
      document.formulario.par_tip.value = tipo_parte;
      habilitar_capa("capa_foto");
    }

    function buscar_geolocalizacion(tipo_parte) {
      document.formulario.par_tip.value = tipo_parte;
      obtener_localizacion();
      habilitar_capa("capa_geolocalización");
    }

    function habilitar_capa(id) {
      document.getElementById('cargando').style.display = "none";
      document.getElementById('capa_contenidos').style.display = "none";
      document.getElementById('capa_info').style.display = "none";
      document.getElementById('capa_foto').style.display = "none";
      document.getElementById('capa_geolocalización').style.display = "none";
      document.getElementById(id).style.display = "block";
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
                habilitar_capa("cargando");
                muestra_progreso(0);
              },
          }).done(function(respuesta) {
                texto_html = '<div class="alert alert-' + respuesta.tipo_mensaje_alerta + '">' + respuesta.mensaje + '</div>';
                texto_html += '<p style="text-align:center;">';
                texto_html += '<button type="button" class="btn btn-' + respuesta.tipo_mensaje_alerta + ' btn-lg" onClick="' + respuesta.onclick + '">Aceptar</button>';
                texto_html += '</p>';
                texto_html += '</div>';
                $("#cargando").html("");
                $("#capa_info").html(texto_html);
                habilitar_capa("capa_info");
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

      $('#txt_img1').change(function(){
        mostrar_miniatura(this, 'img_foto1');
      });
    });

    window.onload = function() {
      var d;
      d = new Date();
      <?php
        $id_tmp = DB_LEE_CAMPO("USR_JOR", "UJ_ID", "UJ_USU=" . $usuario . " AND UJ_JOR=1 AND UJ_FEC_INI>=" . $fecha_actual . " AND UJ_FEC_INI<=" . $fecha_hora_actual . " AND UJ_FEC_FIN IS NULL");
        $refBlq = "";

        $bRealizaInsp = DB_LEE_CAMPO("USR_WEB", "USR_INS", "USR_ID=" . $usuario);
      ?>
      
      if (<?= $id_tmp?> == '0') { // No ha iniciado jornada
        // Domingo, Sábado, Lunes mañana o Viernes tarde
        //if (d.getDay() == 0 || d.getDay() == 6 || d.getHours() <= 6 || d.getHours() >= 15) {
        if (d.getDay() == 0 || d.getDay() == 6) {
          <?php
            $id_tmp = DB_LEE_CAMPO("USR_JOR", "UJ_ID", "UJ_USU=" . $usuario . " AND UJ_JOR=0 AND UJ_FEC_INI>=" . $fecha_dia_anterior . " AND UJ_FEC_INI<=" . $fecha_hora_actual . " AND UJ_FEC_FIN IS NULL");
          ?>
          if (<?= $id_tmp?> == '0') { // No ha iniciado trabajo o parada
            $('#boton_inicio_jornada').addClass("disabled");
            $('#boton_fin_jornada').addClass("disabled");
            $('#boton_inicio_trabajo').removeClass("disabled");
            $('#boton_fin_trabajo').addClass("disabled");
            $('#boton_inicio_descanso').removeClass("disabled");
            $('#boton_fin_descanso').addClass("disabled");
          } else { // Ha iniciado trabajo o parada
            <?php
              $id_tmp = DB_LEE_CAMPO("USR_JOR", "UJ_ID", "UJ_USU=" . $usuario . " AND UJ_JOR=0 AND UJ_TIP_STOP=" . GLB_PARADA_DESCANSO . " AND UJ_FEC_INI>=" . $fecha_dia_anterior . " AND UJ_FEC_INI<=" . $fecha_hora_actual . " AND UJ_FEC_FIN IS NULL");
            ?>
            if (<?= $id_tmp?> != '0') { // Ha iniciado descanso
              $('#boton_inicio_jornada').addClass("disabled");
              $('#boton_fin_jornada').addClass("disabled");
              $('#boton_inicio_trabajo').addClass("disabled");
              $('#boton_fin_trabajo').addClass("disabled");
              $('#boton_inicio_descanso').addClass("disabled");
              $('#boton_fin_descanso').removeClass("disabled");
            } else { // Ha iniciado trabajo
              $('#boton_inicio_jornada').addClass("disabled");
              $('#boton_fin_jornada').addClass("disabled");
              $('#boton_inicio_trabajo').addClass("disabled");
              $('#boton_fin_trabajo').removeClass("disabled");
              $('#boton_inicio_descanso').removeClass("disabled");
              $('#boton_fin_descanso').addClass("disabled");
            }
          }
        } else { // No ha iniciado jornada - día laborable
          $('#boton_inicio_jornada').removeClass("disabled");
          $('#boton_fin_jornada').addClass("disabled");
          $('#boton_inicio_trabajo').addClass("disabled");
          $('#boton_fin_trabajo').addClass("disabled");
          $('#boton_ini_descanso').addClass("disabled");
          $('#boton_fin_descanso').addClass("disabled");
        }
      } else { // Ha iniciado jornada
      <?php
        $id_tmp = DB_LEE_CAMPO("USR_JOR", "UJ_ID", "UJ_USU=" . $usuario . " AND UJ_JOR=0 AND UJ_FEC_INI>=" . $fecha_actual . " AND UJ_FEC_INI<=" . $fecha_hora_actual . " AND UJ_FEC_FIN IS NULL");
      ?>
        if (<?= $id_tmp?> == '0') { // No ha iniciado trabajo o parada
          $('#boton_inicio_jornada').addClass("disabled");
          $('#boton_fin_jornada').removeClass("disabled");
          $('#boton_inicio_trabajo').removeClass("disabled");
          $('#boton_fin_trabajo').addClass("disabled");
          $('#boton_inicio_descanso').removeClass("disabled");
          $('#boton_fin_descanso').addClass("disabled");
        } else { // Ha iniciado trabajo o parada
          <?php
            $id_tmp = DB_LEE_CAMPO("USR_JOR", "UJ_ID", "UJ_USU=" . $usuario . " AND UJ_JOR=0 AND UJ_TIP_STOP=" . GLB_PARADA_DESCANSO . " AND UJ_FEC_INI>=" . $fecha_dia_anterior . " AND UJ_FEC_INI<=" . $fecha_hora_actual . " AND UJ_FEC_FIN IS NULL");
          ?>
          if (<?= $id_tmp?> != '0') { // Ha iniciado descanso
            $('#boton_inicio_jornada').addClass("disabled");
            $('#boton_fin_jornada').addClass("disabled");
            $('#boton_inicio_trabajo').addClass("disabled");
            $('#boton_fin_trabajo').addClass("disabled");
            $('#boton_inicio_descanso').addClass("disabled");
            $('#boton_fin_descanso').removeClass("disabled");
          } else { // Ha iniciado trabajo
            $('#boton_inicio_jornada').addClass("disabled");
            $('#boton_fin_jornada').addClass("disabled");
            $('#boton_inicio_trabajo').addClass("disabled");
            $('#boton_fin_trabajo').removeClass("disabled");
            $('#boton_inicio_descanso').removeClass("disabled");
            $('#boton_fin_descanso').addClass("disabled");
          }
        }
      } 
    }
  </script>
</head>

<body class="framework">
  <div class="container">
    <form name="formulario" id="formulario" class="form-horizontal" role="form" method="POST" action="exe_principal.php" enctype="multipart/form-data" onsubmit="return false;">
      <input type="hidden" name="par_tip" value="" />
    
    <!-- Modal -->
    <div class="modal fade" id="myModal" role="dialog">
      <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Parte de Trabajo</h4>
          </div>
          <div class="modal-body alert-warning" id="modal_text">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="">Cerrar</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Muestra la barra de progreso -->
    <div id="cargando"></div>
    <!-- Fin barra de progreso -->

    <!-- Inicio capa de contenidos -->
    <div id="capa_contenidos">   	  
      <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
        <p class="titulo">Inicio</p>
    	</nav>

    	<ul class="lista-botones">
    		<li>
          <a href="javascript: buscar_geolocalizacion('INI_JOR');" data-toggle="" class="icono btn btn-primary btn-block" id="boton_inicio_jornada"><i class="fa fa-calendar-plus-o fa-3x"></i>Inicio Jornada</a>
    		</li>
    		
        <li>
          <a href="javascript: buscar_geolocalizacion('INI_TRA');" data-toggle="" class="icono btn btn-primary btn-block" id="boton_inicio_trabajo"><i class="fa fa-file fa-3x"></i>Inicio Trabajo</a>
    		</li>
        
        <li>
          <a href="javascript: buscar_geolocalizacion('INI_DES');" data-toggle="" class="icono btn btn-primary btn-block" id="boton_inicio_descanso"><i class="fa fa-hourglass-o fa-3x"></i>Inicio Descanso</a>
        </li>

        <li>
          <a href="javascript: buscar_geolocalizacion('FIN_DES');" data-toggle="" class="icono btn btn-primary btn-block" id="boton_fin_descanso"><i class="fa fa-hourglass fa-3x"></i>Fin Descanso</a>
        </li>

        <li>
          <a href="javascript: ejecuta('FIN_TRA');" data-toggle="" class="icono btn btn-primary btn-block" id="boton_fin_trabajo"><i class="fa fa-file-text fa-3x"></i>Fin Trabajo</a>
        </li>
        
        <li>
          <a href="javascript: buscar_geolocalizacion('FIN_JOR');" data-toggle="" class="icono btn btn-primary btn-block" id="boton_fin_jornada"><i class="fa fa-calendar-minus-o fa-3x"></i>Fin Jornada</a>
        </li>
        
        <li>
          <a href="javascript: ejecuta('EDIT_PAR');" data-toggle="" class="icono btn btn-primary btn-block" id="boton_editar_parte"><i class="fa fa-edit fa-3x"></i>Editar Partes</a>
        </li>

        <li>
          <a href="javascript: ejecuta('DOC');" data-toggle="" class="icono btn btn-primary btn-block" id="boton_editar_parte"><i class="fa fa-book fa-3x"></i>Documentaci&oacute;n</a>
        </li>

        <?php 
					// Sólo para Antonio Moreno
					//if ($_SESSION['GLB_USR_ID'] == 549) 
          if ($bRealizaInsp) {?>
        <li>
          <a href="javascript: ejecuta('INSP');" data-toggle="" class="icono btn btn-primary btn-block" id="boton_inspeccion"><i class="fa fa-check-square-o fa-3x"></i>Inspecciones</a>
        </li>
        <?php } ?>

        <li>
          <a href="cerrar_sesion.php" class="icono btn btn-primary btn-block" id="boton_cerrar_sesion"><i class="fa fa-sign-out fa-3x"></i>Salir</a>
          <p style="font-size: 0.8em"><?= $refBlq?></p>
        </li>
      </ul>
    </div>
    <!-- Fin capa_contenidos-->


    <!-- Inicio capa_foto -->
    <div id="capa_foto" style="display:none;">
      <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
        <span class="navbar-left"><a href="javascript: habilitar_capa('capa_contenidos');" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
        <p class="titulo">Foto</p>
      </nav>

      <div class="col-xs-12">
        <div class="input-group form-group">
          <span class="input-group-btn">
              <span class="btn btn-primary btn-file">
                Seleccionar&hellip; <input type="file" id="txt_img1" name="txt_img1" accept="image/*" capture="camera">
              </span>
          </span>
          <input type="text" class="form-control" readonly id="lbl_img1" name="lbl_img1" value="">
          <span class="input-group-btn">
            <span class="btn btn-default" onclick="javascript: document.formulario.lbl_img1.value='';document.formulario.txt_img1.value='';$('#img_foto1').attr('src','img/ico_camera_grande.png');"><i class="fa fa-times"></i></span>
          </span>
        </div>
      </div>
      
      <div class="col-xs-12" style="text-align: center;">
        <img src="img/ico_camera_grande.png" width="200px" class="img_miniatura" id="img_foto1" name="img_foto1" />
      </div>
      
      <div class="col-xs-12" style="margin-top: 15px;">
        <button type="button" id="btn_guardar" class="btn btn-danger btn-block" onclick="javascript: ejecuta('');">Guardar</button>
      </div>
    </div>

    <!-- Fin capa_foto -->

    <!-- Inicio capa_geolocalización -->
    <div id="capa_geolocalización" style="display:none;">
      <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
        <span class="navbar-left"><a href="javascript: habilitar_capa('capa_contenidos');" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
        <p class="titulo">Mi Ubicación</p>
      </nav>

      <div class="bloque">
        <div class="col-xs-4 bloque">
          <label for="horas_normales<?php echo $i?>" class="">Latitud:</label>
          <input type="text" id="txt_lat" name="txt_lat" class="form-control decimal" readonly>
        </div>

        <div class="col-xs-4 bloque">
          <label for="horas_normales<?php echo $i?>" class="">Longitud:</label>
          <input type="text" id="txt_lon" name="txt_lon" class="form-control decimal" readonly>
        </div>

        <div class="col-xs-12" style="text-align: center;">
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
          </div>
        </div>

        <div class="col-xs-12" style="text-align: center;">
          <a href="#" onclick="javascript: obtener_localizacion();"><img src="img/ico_geolocalizar.png" width="200px" class="img_miniatura" id="img_foto2" name="img_foto2" /></a>
        </div>

        <div class="col-xs-12" style="margin-top: 15px;">
          <button type="button" id="btn_guardar" class="btn btn-danger btn-block" onclick="javascript: ejecuta('');">Guardar</button>
        </div>
      </div>
      
    </div>
    <!-- Fin capa_geolocalización -->

    <!-- Muestra los mensajes de información y error -->
    <div id="capa_info"></div>
    <!-- Fin capa_info -->

    </form>
  </div>
  <script src="bootstrap-3.2.0/js/bootstrap.min.js"></script>
</body>

</html>
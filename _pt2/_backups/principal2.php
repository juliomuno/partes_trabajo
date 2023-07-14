<!DOCTYPE html>
<hmtl lang="es">

<head>
	<title>Bienvenido a Moneleg-Partes de Trabajo</title>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0">
	
  <script src="js/jquery-1.11.0.min.js"></script>
  <script type="text/javascript" src="js/ajax.js"></script>
  <script type="text/javascript" src="js/validacion.js"></script>

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

    // Variables para Audio
    var recordButton, stopButton, recorder;
    var blob;

    
    function startRecording() {
      recordButton.disabled = true;
      stopButton.disabled = false;

      recorder.start();
    }

    function stopRecording() {
      recordButton.disabled = false;
      stopButton.disabled = true;

      // Stopping the recorder will eventually trigger the `dataavailable` event and we can complete the recording process
      recorder.stop();
	}

    function onRecordingReady(e) {
      var audio = document.getElementById('audio');
      // e.data contains a blob representing the recording
      audio.src = URL.createObjectURL(e.data);
      //audio.play();
      var reader = new FileReader();

      reader.readAsDataURL(e.data); 
      reader.onloadend = function() {
        var base64data = reader.result;                
        document.getElementById("txt_audio").value = base64data;
        $("#lbl_audio").html("Completado");
      }
    }

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
      if (document.formulario.lbl_img1.value.length == 0) {
        msg += "<p>Es necesario seleccionar una imagen.</p>";
      }

      if (msg != '') {
          document.getElementById("modal_text").innerHTML = msg;
          $("#myModal").modal("show");
          return 0;
      } else {
        uploadAjax("exe_principal2.php");
     }
    }


    function activar_audio(bactivar) {
    	
    	if (bactivar) {
    		$("#capa_audio").show();
    		
    		// Inicializar audio
    		$("#lbl_audio").html("Esperando audio ...");
      		document.formulario.txt_audio.value = "";
    		
    		if (recordButton == undefined) {
	    		recordButton = document.getElementById('record');
			    stopButton = document.getElementById('stop');
			}

			// get audio stream from user's mic
		      navigator.mediaDevices.getUserMedia({
		        audio: true
		      })
		      .then(function (stream) {
		        recordButton.disabled = false;
		        recordButton.addEventListener('click', startRecording);
		        stopButton.addEventListener('click', stopRecording);
		        recorder = new MediaRecorder(stream);

		        // listen to dataavailable, which gets triggered whenever we have
		        // an audio blob available
		        recorder.addEventListener('dataavailable', onRecordingReady);
			  }).catch(function (err) {
			  	alert(err.name + '. ' + err.message);
			  });
		} else {
    		$("#capa_audio").hide();
		}
    }


    function buscar_imagen(tipo_parte) {
      document.formulario.par_tip.value = tipo_parte;
      
      if (tipo_parte == 'INI_TRA' || tipo_parte == 'FIN_TRA') {
      	activar_audio(true);
      } else {
      	activar_audio(false);
      }
     
      habilitar_capa("capa_foto");
      
    }


    function habilitar_capa(id) {
      document.getElementById('cargando').style.display = "none";
      document.getElementById('capa_contenidos').style.display = "none";
      document.getElementById('capa_info').style.display = "none";
      document.getElementById('capa_foto').style.display = "none";
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
        $id_tmp = DB_LEE_CAMPO("USR_JOR", "UJ_ID", "UJ_USU=" . $usuario . " AND UJ_JOR=1 AND UJ_FEC_INI>=" . $fecha_actual . " AND UJ_FEC_INI<=" . $fecha_hora_actual);
      ?>
    
      if (<?= $id_tmp?> == '0') { // Domingo, Sábado, madrugadas o tardes
        if (d.getDay() == 0 || d.getDay() == 6 || d.getHours() <= 6 || d.getHours() >= 15) {
          <?php
            $id_tmp = DB_LEE_CAMPO("USR_JOR", "UJ_ID", "UJ_USU=" . $usuario . " AND UJ_JOR=0 AND UJ_FEC_INI>=" . $fecha_dia_anterior . " AND UJ_FEC_INI<=" . $fecha_hora_actual . " AND UJ_FEC_FIN IS NULL");
          ?>
          if (<?= $id_tmp?> == '0') {
            $('#boton_inicio_jornada').addClass("disabled");
            $('#boton_fin_jornada').addClass("disabled");
            $('#boton_inicio_trabajo').removeClass("disabled");
            $('#boton_fin_trabajo').addClass("disabled");
          } else {
            $('#boton_inicio_jornada').addClass("disabled");
            $('#boton_fin_jornada').addClass("disabled");
            $('#boton_inicio_trabajo').addClass("disabled");
            $('#boton_fin_trabajo').removeClass("disabled");
          }      
        } else {
          $('#boton_inicio_jornada').removeClass("disabled");
          $('#boton_fin_jornada').addClass("disabled");
          $('#boton_inicio_trabajo').addClass("disabled");
          $('#boton_fin_trabajo').addClass("disabled");
        }
      } else {
      <?php
        $id_tmp = DB_LEE_CAMPO("USR_JOR", "UJ_ID", "UJ_USU=" . $usuario . " AND UJ_JOR=0 AND UJ_FEC_INI>=" . $fecha_actual . " AND UJ_FEC_INI<=" . $fecha_hora_actual . " AND UJ_FEC_FIN IS NULL");
      ?>
        if (<?= $id_tmp?> == '0') {
          <?php
            $id_tmp = DB_LEE_CAMPO("USR_JOR", "UJ_ID", "UJ_USU=" . $usuario . " AND UJ_JOR=1 AND UJ_FEC_INI>=" . $fecha_actual . " AND UJ_FEC_INI<=" . $fecha_hora_actual . " AND UJ_FEC_FIN IS NULL");
          ?>
          if (<?= $id_tmp?> == '0') {
            $('#boton_inicio_jornada').addClass("disabled");
            $('#boton_fin_jornada').addClass("disabled");
            if (<?= $_SESSION['GLB_USR_ID']?> == '811' || <?= $_SESSION['GLB_USR_ID']?> == '864') {
              $('#boton_inicio_trabajo').addClass("disabled");
              $('#boton_fin_trabajo').removeClass("disabled");
            } else {
              $('#boton_inicio_trabajo').removeClass("disabled");
              $('#boton_fin_trabajo').addClass("disabled");
            }
          } else {
            $('#boton_inicio_jornada').addClass("disabled");
            $('#boton_fin_jornada').removeClass("disabled");
            $('#boton_inicio_trabajo').removeClass("disabled");
            $('#boton_fin_trabajo').addClass("disabled");
          }
        } else {
            $('#boton_inicio_jornada').addClass("disabled");
            $('#boton_fin_jornada').addClass("disabled");
            $('#boton_inicio_trabajo').addClass("disabled");
            $('#boton_fin_trabajo').removeClass("disabled");
        }
      } 
    }

  </script>
</head>

<body class="framework">
  <div class="container">
    <form name="formulario" id="formulario" class="form-horizontal" role="form" method="POST" action="exe_principal2.php" enctype="multipart/form-data" onsubmit="return false;">
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
    			<a href="javascript: buscar_imagen('INI_JOR');" data-toggle="" class="icono btn btn-primary btn-block" id="boton_inicio_jornada"><i class="fa fa-calendar-plus-o fa-3x"></i>Inicio Jornada</a>
    		</li>
    		
        <li>
    			<a href="javascript: buscar_imagen('INI_TRA');" data-toggle="" class="icono btn btn-primary btn-block" id="boton_inicio_trabajo"><i class="fa fa-file fa-3x"></i>Inicio Trabajo</a>
    		</li>
        
        <li>
          <a href="javascript: ejecuta('FIN_TRA');" data-toggle="" class="icono btn btn-primary btn-block" id="boton_fin_trabajo"><i class="fa fa-file-text fa-3x"></i>Fin Trabajo</a>
        </li>
        
        <li>
          <a href="javascript: buscar_imagen('FIN_JOR');" data-toggle="" class="icono btn btn-primary btn-block" id="boton_fin_jornada"><i class="fa fa-calendar-minus-o fa-3x"></i>Fin Jornada</a>
        </li>
        
        <li>
          <a href="javascript: ejecuta('EDIT_PAR');" data-toggle="" class="icono btn btn-primary btn-block" id="boton_editar_parte"><i class="fa fa-edit fa-3x"></i>Editar Partes</a>
        </li>

        <li>
          <a href="javascript: ejecuta('DOC');" data-toggle="" class="icono btn btn-primary btn-block" id="boton_editar_parte"><i class="fa fa-book fa-3x"></i>Documentaci&oacute;n</a>
        </li>

        <li>
          <a href="cerrar_sesion.php" class="icono btn btn-primary btn-block" id="boton_cerrar_sesion"><i class="fa fa-sign-out fa-3x"></i>Salir</a>
        </li>
      </ul>
    </div>
    <!-- Fin capa_contenidos-->


    <!-- Inicio capa_foto -->
    <div id="capa_foto" style="display:none;">
      <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
        <span class="navbar-left"><a href="javascript: habilitar_capa('capa_contenidos');" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
        <span class="navbar-right" style="visibility:hidden;"><a href="#" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Sig.</a></span>
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
        <img src="img/ico_camera_grande.png" width="150px" class="img_miniatura" id="img_foto1" name="img_foto1" />
      </div>

      <div id="capa_audio" class="col-xs-12" style="padding-left:0;margin-top: 15px;">
        <p>
          <button type="button" id="record" class="btn btn-primary">Grabar Audio</button> <button type="button" id="stop" class="btn btn-primary" disabled="">Stop</button>
        <p>
        <p>
          <audio id="audio" controls=""></audio>
        </p>
        <p>
        	<label id="lbl_audio"></label>
       	</p>
        <input type="hidden" name="txt_audio" rows="10" cols="50" id="txt_audio" />
      </div>

      <div class="col-xs-12" style="margin-top: 15px;padding-left:0;">
        <button type="button" id="btn_guardar" class="btn btn-danger btn-block" onclick="javascript: ejecuta('');">Guardar</button>
      </div>
    </div>
    <!-- Fin capa_foto -->

    <!-- Muestra los mensajes de información y error -->
    <div id="capa_info"></div>
    <!-- Fin capa_info -->

    </form>
  </div>
  <script src="bootstrap-3.2.0/js/bootstrap.min.js"></script>
  
</body>

</html>
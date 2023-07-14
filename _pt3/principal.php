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
    include "comun/funciones.php";
    include "comun/db_con.php";
    session_start();

    if (!isset($_SESSION['GLB_USR_ID'])) {
      php_redirect('index.php');
    }
    if (isset($_SESSION['tiempo'])){
      $vida_session = time() - $_SESSION['tiempo'];
      // tiempo en segundos
      if ($vida_session>120){
        session_destroy();
        php_redirect('index.php');
      }
    }
    $_SESSION['tiempo'] = time();
    
    $usuario = $_SESSION['GLB_USR_ID'];
    $fecha_hora_actual = STR_formato_cadena(date("Y-m-d H:i:s"));
    // aumentar 45 min para localizar marcajes escalados por encima de la hora actual. ej: hor_act=08:53 -> hor_escalada=09:00
    $fecha_hora_actual = STR_formato_cadena(date("Y-m-d H:i:s",strtotime ( '+45 minute' , strtotime (date("Y-m-d H:i:s")))));

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
        document.location.href = "list_jortra.php";
        return 1;
      } else if (tipo_parte == "DOC") {
        document.location.href = "documentacion.php";
      } else if (tipo_parte == "FIN_TRA") {
        document.location.href = "inicio2.php";
        return 1;
      } else if (tipo_parte == 'INSP') {
      	document.location.href = "../inspecciones/inspecciones.php";
      } else if (tipo_parte == 'TRA_MAN'){
        document.location.href = "wold/inicio2.php";
      } else if (tipo_parte == 'PLA'){
        document.location.href = "pla.php";
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

      if (document.formulario.par_tip.value == 'INI_JOR' || document.formulario.par_tip.value == 'FIN_JOR' || document.formulario.par_tip.value == 'INI_VEH' || document.formulario.par_tip.value == 'FIN_VEH'){
        // ini. Verificar el vehículo
        if(document.getElementById("cmb_veh").value.length == 0){
          msg += "<p>Es necesario seleccionar un veh&iacute;culo.</p>";
        }

        if (document.getElementById("cmb_veh").value != 326) {
          //comprobar si está en uso
          $veh_texto_valor=document.getElementById('cmb_veh')[document.getElementById('cmb_veh').selectedIndex].innerHTML;
          if ($veh_texto_valor.indexOf("*** usado *") != -1 && document.getElementById("cmb_veh_uso").value=="Conductor" && document.getElementById("veh_cod_en_uso").value!=document.getElementById("cmb_veh").value) {
            $veh_usu_pos=$veh_texto_valor.indexOf("usu actual:");
            $veh_usu_ult=$veh_texto_valor.substr($veh_usu_pos+12,4);  //el código del último usuario que lo usaba el vehículo
            $veh_usu_pos=$veh_usu_pos+11;
            if ($veh_usu_ult!=<?php echo $_SESSION['GLB_USR_ID']?>) {
              msg += $veh_usu_ult+"<p>Veh&iacute;culo en uso por: <b>" + $veh_texto_valor.substr($veh_usu_pos) + "</b><br>El debe ejecutar <b>FIN VEH&Iacute;CULO</b></p>";
            }
          } else {
            if (document.getElementById("cmb_veh_uso").value == "{sin vehículo}" || document.getElementById("cmb_veh_uso").value.length == 0) {
              msg += "<p>Es necesario indicar el tipo de USO del veh&iacute;culo.</p>";
            }
            if (document.formulario.txt_km_act.disabled == false) {
              if (document.getElementById("txt_km_act").value == '' || document.getElementById("txt_km_act").value == '0') {
                if (document.getElementById("cmb_veh_uso").value=="Conductor"){
                  msg += "<p>Es necesario indicar los kil&oacute;metros totales actuales del veh&iacute;culo.</p>";
                }
              } else {
                if (document.getElementById("txt_km_act_ini").value=="") {
                  document.getElementById("txt_km_act_ini").value=0;
                }
                if (Math.abs(document.getElementById("txt_km_act_ini").value)>Math.abs(document.getElementById("txt_km_act").value)) {
                  //msg += "<p>Los kilómetros totales debe ser superior al de inicio. Km inicio: " + document.getElementById("txt_km_act_ini").value + "</p>";
                  msg += "<p>Los kilómetros totales debe ser superior o igual al de inicio. Km inicio</p>";
                }
              }
            }
          }
        }
        // fin. Verificar el vehículo
      }

      if (msg != '') {
          document.getElementById("modal_text").innerHTML = msg;
          $("#myModal").modal("show");
          return 0;
      } else if (tipo_parte=="INI_TRA") {
        var tra_jef=document.getElementById('txt_jef').value;
        var ope_ctd=document.getElementById('txt_pla_ope_sel').value;
        var ope_ofi=document.getElementById('txt_ope_ofi').value;
        var ope_array=ope_ctd.split(",");
        var msg="";
        if (tra_jef==1){
          // Verificar imágenes
          if (document.formulario.lbl_img.value.length == 0) {
            msg="Es necesario seleccionar una imagen.";

            if (msg != '') {
              document.getElementById("modal_text").innerHTML = msg;
              $("#myModal").modal("show");
              return 0;
            }
          }

          if (ope_ofi==0){
            if (ope_array.length==1){
              msg="Confirme que quiere iniciar trabajo<b> Solo.</b><br>No tiene seleccionado a ningún operario."
            } else {
              msg="Confirme que quiere iniciar trabajo<br><b>Incluyendo "+(ope_array.length-1)+" operarios.</b>"
            }
          } else {
            uploadAjax("exe_principal.php");
          }
        } else {
          // Verificar condición del Jefe de equipo
          if (document.formulario.lbl_img.value.length!=0) {
            msg="Solo puede añadir fotos de Designación el Jefe de equipo. Elimine la foto adjunta para continuar."

            if (msg!=""){
              document.getElementById("modal_title_jefe").innerHTML = "MENSAJE DE AVISO";
              document.getElementById("modal_text_jefe").innerHTML = msg;
              $("#myModal_jefe").modal("show");
              return 0;
            }
          }

          msg="Confirme que quiere UNIRSE al trabajo"
        }

        if (msg!=""){
         document.getElementById("modal_title_sn").innerHTML = "INICIAR TRABAJO";
         document.getElementById("modal_text_sn").innerHTML = msg;
          $("#myModal_sn").modal("show");
          return 0;
        }
      } else {
        uploadAjax("exe_principal.php");
      }
    }

    function activar_kilometros(value) {
      // solo introducir kilómetros con uso conductor
      document.formulario.txt_km_act.disabled = true;
      if (value == "Conductor"){
        document.formulario.txt_km_act.value='';
        document.formulario.txt_km_act.disabled = false;
      }
    }

    function buscar_imagen(tipo_parte) {
      document.formulario.par_tip.value = tipo_parte;
      habilitar_capa("capa_foto");
    }

    function registrar_seleccion_opes(pee_id,cod_ope,checked){
      if (document.getElementById('txt_pla_pee').value != pee_id){
        document.getElementById('txt_pla_pee').value = pee_id;
        document.getElementById('txt_pla_ope_sel').value = "";
      }
      var str=document.getElementById('txt_pla_ope_sel').value;
      var res=str.replace(cod_ope+",","");
      if (checked==1){
        document.getElementById('txt_pla_ope_sel').value = res + cod_ope + ',';
      } else {
        document.getElementById('txt_pla_ope_sel').value = res;
      }
    }

    function buscar_geolocalizacion(tipo_parte) {
      document.formulario.par_tip.value = tipo_parte;
      obtener_localizacion();
      if (tipo_parte=="INI_JOR"){
        document.getElementById('capa_vehiculo').style.display = "block";
        document.getElementById('capa_pla_list').style.display = "block";
        document.getElementById('capa_pla_list2').style.display = "block";
      }else if(tipo_parte=="FIN_JOR" || tipo_parte=="INI_VEH" || tipo_parte=='FIN_VEH'){
        document.getElementById('capa_vehiculo').style.display = "block";
      } else if (tipo_parte=="INI_TRA"){
        document.getElementById('capa_planificacion').style.display = "block";
        document.getElementById('capa_guardar').style.display = "none";
        // Visualizamos el campo "Añadir Fotos" en Iniciar Trabajo
        document.getElementById('capa_imagenes').style.display = "block";
      }
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

    function habilitar_todos_botones(){
      $('#boton_inicio_jornada').removeClass("disabled");
      $('#boton_fin_jornada').removeClass("disabled");
      $('#boton_inicio_trabajo').removeClass("disabled");
      $('#boton_fin_trabajo').removeClass("disabled");
      $('#boton_inicio_descanso').removeClass("disabled");
      $('#boton_fin_descanso').removeClass("disabled");
      $('#boton_tra_manual').removeClass("disabled");
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
      var iTip;
      d = new Date();
      <?php
        //hora inicio de jornada de hoy
        $jor_ini=hora_inicio_jornada($usuario);
        if ($jor_ini!=""){
          $jor_ini_str=" (".substr($jor_ini, 11,5).")";
        };
        
        //hora inicio de último trabajo de hoy
        $tra_ini=DB_LEE_CAMPO("USR_JOR", "UJ_FEC_INI", "UJ_USU=" . $usuario . " AND UJ_TIP_STOP=1 AND UJ_FEC_INI>=" . $fecha_dia_anterior . " AND UJ_FEC_INI<=" . $fecha_hora_actual . " AND UJ_FEC_FIN IS NULL");
        $tra_ini_str="";
        if ($tra_ini!='0'){
          $tra_ini_str=" (últ.trab.: ".substr($tra_ini, 11,5).")";
        }
        //hor fin de último trabajo de hoy
        $ult_tra_fec = ultima_hora_fin_parte($usuario);
        if ($ult_tra_fec!='0' && $tra_ini_str==''){
          $ult_tra_fec = " (últ.trab.: " . substr($ult_tra_fec,11,5) . ")";
        } else {
          $ult_tra_fec = "";
        }

        //vehículo actual en uso
        $veh_sel_nombre=DB_LEE_CAMPO("USR_JOR INNER JOIN VEH ON USR_JOR.UJ_VEH_ID=VEH.VEH_ID INNER JOIN VEH_MAR ON VEH.VEH_MAR=VEH_MAR.MAR_ID INNER JOIN VEH_MOD ON VEH.VEH_MOD=VEH_MOD.MOD_ID", "CONCAT_WS(' ',VEH_MAT, MAR_NOM, MOD_NOM) AS VEHICULO", "UJ_USU=" . $usuario . "  AND UJ_FEC_INI>=" . $fecha_dia_anterior . " AND UJ_FEC_FIN IS NULL AND NOT UJ_VEH_ID IS NULL AND UJ_VEH_USO='Conductor' AND UJ_TIP_STOP=3");
        if ($veh_sel_nombre=='0'){
          $veh_sel_nombre="";
        }

        //registro de inicio de jornada
        $id_tmp = DB_LEE_CAMPO("USR_JOR", "UJ_ID", "UJ_USU=" . $usuario . " AND UJ_JOR=1 AND UJ_FEC_INI>=" . $fecha_dia_anterior . " AND UJ_FEC_INI<=" . $fecha_hora_actual . " AND UJ_FEC_FIN IS NULL");
        $refBlq = "";

        $bRealizaInsp = DB_LEE_CAMPO("USR_WEB", "USR_INS", "USR_ID=" . $usuario);
        $bofi = DB_LEE_CAMPO("USR_WEB", "USR_OFI", "USR_ID=" . $usuario);
      ?>
      iTip=0;
      if (<?= $id_tmp?> == '0') { // No ha iniciado jornada
        // Domingo, Sábado, Lunes mañana o Viernes tarde
        //addClass=desactivo - removeClass=activo
        //if (d.getDay() == 0 || d.getDay() == 6 || d.getHours() <= 6 || d.getHours() >= 15) {
        //20210307 se anula la restricción de no poder hacer partes el fin de semana
        //if (d.getDay() == 0 || d.getDay() == 6) {
        if (1==2) {
          <?php
            $id_tmp = DB_LEE_CAMPO("USR_JOR", "UJ_ID", "UJ_USU=" . $usuario . " AND UJ_JOR=0 AND UJ_TIP_STOP<>3 AND UJ_FEC_INI>=" . $fecha_dia_anterior . " AND UJ_FEC_INI<=" . $fecha_hora_actual . " AND UJ_FEC_FIN IS NULL");
          ?>
          if (<?= $id_tmp?> == '0') { // No ha iniciado trabajo o parada
            iTip=1;
            $('#boton_inicio_jornada').addClass("disabled");
            $('#boton_fin_jornada').addClass("disabled");
            $('#boton_inicio_trabajo').addClass("disabled");
            $('#boton_fin_trabajo').addClass("disabled");
            $('#boton_inicio_descanso').addClass("disabled");
            $('#boton_fin_descanso').addClass("disabled");
            $('#boton_tra_manual').addClass("disabled");
          } else { // Ha iniciado trabajo o parada
            iTip=2;
            <?php
              $id_tmp = DB_LEE_CAMPO("USR_JOR", "UJ_ID", "UJ_USU=" . $usuario . " AND UJ_JOR=0 AND UJ_TIP_STOP=" . GLB_PARADA_DESCANSO . " AND UJ_FEC_INI>=" . $fecha_dia_anterior . " AND UJ_FEC_INI<=" . $fecha_hora_actual . " AND UJ_FEC_FIN IS NULL");
            ?>
            if (<?= $id_tmp?> != '0') { // Ha iniciado descanso
              iTip=3;
              $('#boton_inicio_jornada').addClass("disabled");
              $('#boton_fin_jornada').addClass("disabled");
              $('#boton_inicio_trabajo').addClass("disabled");
              $('#boton_fin_trabajo').addClass("disabled");
              $('#boton_inicio_descanso').addClass("disabled");
              $('#boton_fin_descanso').removeClass("disabled");
              $('#boton_tra_manual').addClass("disabled");
            } else { // Ha iniciado trabajo
              iTip=4;
              $('#boton_inicio_jornada').addClass("disabled");
              $('#boton_fin_jornada').addClass("disabled");
              $('#boton_inicio_trabajo').addClass("disabled");
              $('#boton_fin_trabajo').removeClass("disabled");
              $('#boton_inicio_descanso').removeClass("disabled");
              $('#boton_fin_descanso').addClass("disabled");
              $('#boton_tra_manual').addClass("disabled");
            }
          }
        } else { // No ha iniciado jornada - día laborable
          iTip=5;
          $('#boton_inicio_jornada').removeClass("disabled");
          $('#boton_fin_jornada').addClass("disabled");
          <?php
          if ($tra_ini!='0'){
            ?>
            $('#boton_inicio_trabajo').addClass("disabled");
            $('#boton_fin_trabajo').removeClass("disabled");
            <?php
          } else {
            ?>
            $('#boton_inicio_trabajo').removeClass("disabled");
            $('#boton_fin_trabajo').addClass("disabled");
            <?php
          } ?>
          $('#boton_inicio_descanso').addClass("disabled");
          $('#boton_fin_descanso').addClass("disabled");
          $('#boton_tra_manual').addClass("disabled");
        }
      } else { // Ha iniciado jornada
      <?php
        $id_tmp = DB_LEE_CAMPO("USR_JOR", "UJ_ID", "UJ_USU=" . $usuario . " AND UJ_JOR=0 AND UJ_TIP_STOP<>3 AND UJ_FEC_INI>=" . $fecha_dia_anterior . " AND UJ_FEC_INI<=" . $fecha_hora_actual . " AND UJ_FEC_FIN IS NULL");
      ?>
        if (<?= $id_tmp?> == '0') { // No ha iniciado trabajo o parada
          iTip=6;
          $('#boton_inicio_jornada').addClass("disabled");
          $('#boton_fin_jornada').removeClass("disabled");
          $('#boton_inicio_trabajo').removeClass("disabled");
          $('#boton_fin_trabajo').addClass("disabled");
          $('#boton_inicio_descanso').removeClass("disabled");
          $('#boton_fin_descanso').addClass("disabled");
          $('#boton_tra_manual').removeClass("disabled");
        } else { // Ha iniciado trabajo o parada
          <?php
            $id_tmp = DB_LEE_CAMPO("USR_JOR", "UJ_ID", "UJ_USU=" . $usuario . " AND UJ_JOR=0 AND UJ_TIP_STOP=" . GLB_PARADA_DESCANSO . " AND UJ_FEC_INI>=" . $fecha_dia_anterior . " AND UJ_FEC_INI<=" . $fecha_hora_actual . " AND UJ_FEC_FIN IS NULL");
          ?>
          if (<?= $id_tmp?> != '0') { // Ha iniciado descanso
            iTip=7;
            $('#boton_inicio_jornada').addClass("disabled");
            $('#boton_fin_jornada').addClass("disabled");
            $('#boton_inicio_trabajo').addClass("disabled");
            $('#boton_fin_trabajo').addClass("disabled");
            $('#boton_inicio_descanso').addClass("disabled");
            $('#boton_fin_descanso').removeClass("disabled");
            $('#boton_tra_manual').removeClass("disabled");
          } else { // Ha iniciado trabajo
            iTip=8;
            $('#boton_inicio_jornada').addClass("disabled");
            $('#boton_fin_jornada').addClass("disabled");
            $('#boton_inicio_trabajo').addClass("disabled");
            $('#boton_fin_trabajo').removeClass("disabled");
            $('#boton_inicio_descanso').removeClass("disabled");
            $('#boton_fin_descanso').addClass("disabled");
            $('#boton_tra_manual').addClass("disabled");
          }
        }
      }
      document.getElementById("txt_tipact").value=iTip;

      <?php
        $id_tmp = DB_LEE_CAMPO("USR_JOR", "UJ_ID", "UJ_USU=" . $usuario . " AND UJ_TIP_STOP=" . GLB_CAMBIO_VEHICULO . " AND UJ_FEC_INI>=" . $fecha_dia_anterior . " AND UJ_FEC_INI<=" . $fecha_hora_actual . " AND UJ_FEC_FIN IS NULL");
      ?>
      if (<?= $id_tmp?> != 0) { // vehículo iniciado
        $('#boton_inicio_vehiculo').addClass("disabled");
        $('#boton_fin_vehiculo').removeClass("disabled");
      } else {
        //if (iTip==5){
        //  $('#boton_inicio_vehiculo').addClass("disabled");
        //} else {
          $('#boton_inicio_vehiculo').removeClass("disabled");
        //}
        $('#boton_fin_vehiculo').addClass("disabled");
      }

      <?php 
      //activar botón trabajo manual
        $tra_man=DB_LEE_CAMPO("MISC","MSC_ID","MSC_CLV='PAR_TRA_MAN' AND MSC_VAL1=" . $usuario . " AND MSC_FEC1<=" . $fecha_hora_actual . " AND MSC_FEC2>=" . $fecha_hora_actual);
      ?>
        if (<?= $tra_man?> == 0){
          $('#boton_tra_manual').addClass("disabled");
        }
    }
  </script>
</head>

<body class="framework">
  <div class="container">
    <form name="formulario" id="formulario" class="form-horizontal" role="form" method="POST" action="exe_principal.php" enctype="multipart/form-data" onsubmit="return false;">
      <input type="hidden" name="par_tip" id="par_id" value="" />
    
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

    <!-- Modal SI/NO-->
    <div class="modal fade" id="myModal_sn" role="dialog">
      <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title" id="modal_title_sn">Parte de Trabajo</h4>
          </div>
          <div class="modal-body alert-warning" id="modal_text_sn">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="">Cancelar</button>
            <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="javascript: uploadAjax('exe_principal.php');">Aceptar</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal mensaje jefe de equipo-->
    <div class="modal fade" id="myModal_jefe" role="dialog">
      <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title" id="modal_title_jefe">Parte de Trabajo</h4>
          </div>
          <div class="modal-body alert-warning" id="modal_text_jefe">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="javascript: document.getElementById('modal_text_sn');">Cerrar</button>
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
        <!--<p class="titulo">Inicio<a onclick="javascript: habilitar_todos_botones();">.</a></p>-->
        <p class="titulo">Inicio</p>
    	</nav>

      <ul class="lista-botones">
        <li>
          <?php
          echo "Usuario actual: (".$usuario.") ".DB_LEE_CAMPO("USR_WEB", "CONCAT_WS(' ',USR_NOM,USR_APE)", "USR_ID=" . $usuario)."<br>";
          if ($veh_sel_nombre!=""){
              echo "Veh. actual: <b>".$veh_sel_nombre."</b>";
              }
          ?>
          <input type="hidden" name="txt_tipact" id="txt_tipact">
          <input type="hidden" id="txt_ope_ofi" value="<?php echo $_SESSION['GLB_USR_OFI']; ?>">
        </li>
    		<li>
          <a href="javascript: buscar_geolocalizacion('INI_JOR');" data-toggle="" class="icono btn btn-primary btn-block" id="boton_inicio_jornada"><i class="fa fa-calendar-plus-o fa-3x"></i>Inicio Jornada<?php echo $jor_ini_str; ?></a>
    		</li>

        <li>
          <a href="javascript: buscar_geolocalizacion('INI_TRA');" data-toggle="" class="icono btn btn-primary btn-block" id="boton_inicio_trabajo"><i class="fa fa-file fa-3x"></i>Inicio Trabajo<? echo $tra_ini_str;?></a>
    		</li>
        
        <table width="100%">
          <tr>
            <td width="50%">
              <li>
                <a href="javascript: buscar_geolocalizacion('INI_DES');" data-toggle="" class="icono btn btn-primary btn-block" id="boton_inicio_descanso"><i class="fa fa-hourglass-o fa-3x"></i>Inicio Descanso</a>
              </li>
            </td>
            <td width="5px"></td>
            <td>
              <li>
                <a href="javascript: buscar_geolocalizacion('FIN_DES');" data-toggle="" class="icono btn btn-primary btn-block" id="boton_fin_descanso"><i class="fa fa-hourglass fa-3x"></i>FIN DESCANSO</a>
              </li>
            </td>
          </tr>
          <tr style="height: 5px;"><td colspan="3"></td></tr>
        </table>

        <table width="100%">
          <tr>
            <td width="50%">
              <li>
                <a href="javascript: buscar_geolocalizacion('INI_VEH');" data-toggle="" class="icono btn btn-primary btn-block" id="boton_inicio_vehiculo"><i class="fa fa-car fa-3x"></i>Inicio Veh&iacute;culo</a>
              </li>
            </td>
            <td width="5px"></td>
            <td>
              <li>
                <a href="javascript: buscar_geolocalizacion('FIN_VEH');" data-toggle="" class="icono btn btn-primary btn-block" id="boton_fin_vehiculo"><i class="fa fa-car fa-3x"></i>FIN VEH&Iacute;CULO 
                  <?php 
                  if($veh_sel_nombre!=''){
                    //echo " (".substr($veh_sel_nombre, 1,8)."...)";
                  } 
                  ?>
                </a>
              </li>
            </td>
          </tr>
          <tr style="height: 5px;"><td colspan="3"></td></tr>
        </table>


        <table width="100%">
          <tr>
            <td width="50%">
              <li>
                <a href="javascript: ejecuta('PLA');" data-toggle="" class="icono btn btn-primary btn-block" id="boton_planificacion"><i class="fa fa-calendar fa-3x"></i>Ver planificación</a>
              </li>
            </td>
            <td width="5px"></td>
            <td>
              <li>
                <a href="javascript: ejecuta('TRA_MAN');" data-toggle="" class="icono btn btn-primary btn-block" id="boton_tra_manual"><i class="fa fa-file fa-3x"></i>AÑADIR TRABAJO MANUAL</a>
              </li>
            </td>
          </tr>
          <tr style="height: 5px;"><td colspan="3"></td></tr>
        </table>


        <li>
          <a href="javascript: ejecuta('FIN_TRA');" data-toggle="" class="icono btn btn-primary btn-block" id="boton_fin_trabajo"><i class="fa fa-file-text fa-3x"></i>FIN TRABAJO <?php echo $ult_tra_fec ?></a>
        </li>

        <li>
          <a href="javascript: buscar_geolocalizacion('FIN_JOR');" data-toggle="" class="icono btn btn-primary btn-block" id="boton_fin_jornada"><i class="fa fa-calendar-minus-o fa-3x"></i>FIN JORNADA</a>
        </li>
        
        <li>
          <a href="javascript: ejecuta('EDIT_PAR');" data-toggle="" class="icono btn btn-primary btn-block" id="boton_editar_parte"><i class="fa fa-edit fa-3x"></i>Consultar jornadas/partes</a>
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
        <?php 
          }
          ?>

        <li>
          <a href="#" data-toggle="" class="icono btn btn-primary btn-block"><i class="fa fa-cog fa-3x"></i>
            Configurable por tipo de parte
          </a>
        </li>

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
          <p>&nbsp;</p>
          <p>&nbsp;</p>
        </div>

        <div id="capa_vehiculo" style="display:none;">
          <?php 
            // capturar el vehículo habitual del usuario
            $par_veh = DB_LEE_CAMPO("USR_WEB","USR_VEH_HAB","USR_ID = " . $_SESSION['GLB_USR_ID']);
            // capturar el vehículo del inicio de trabajo como conductor
            $sSQL = "SELECT * FROM `USR_JOR` where UJ_USU=" . $_SESSION['GLB_USR_ID'] . " AND UJ_TIP_STOP=3 AND UJ_FEC_INI>='" . date("Y-m-d") . " 00:00:00' AND UJ_FEC_FIN IS NULL ORDER BY UJ_ID DESC";
            $veh_sel = DB_CONSULTA($sSQL);
            if ($row = mysql_fetch_assoc($veh_sel)) {
              $par_veh = $row['UJ_VEH_ID'];
              $par_km_act = $row['UJ_VEH_KIL'];
              $par_veh_con = $row['UJ_VEH_USO'];
            }
          ?>
          <div class="col-xs-12">
            <label for="vehiculo" class="col-lg-2">Veh&iacute;culo:</label>
            <div class="col-lg-10">
              <?php
              echo DB_COMBOBOX("LIST_VEHICULOS_DISPONIBLES","Codigo","Nombre","","Nombre","cmb_veh","cmb_veh","form-control",$par_veh,"","",""); 
              ?>
              <input type="hidden" name="veh_cod_en_uso" id="veh_cod_en_uso" value="<?php echo $par_veh ?>">
            </div>
          </div>
          <div class="col-xs-12 bloque">
            <label for="vehiculo" class="col-lg-2">Uso:</label>
            <div class="col-lg-10">
              <?php echo DB_COMBOBOX("LIST_VEH_CONDUCTOR","Nombre","Nombre","","Nombre","cmb_veh_uso","cmb_veh_uso","form-control",$par_veh_con,"","","activar_kilometros(this.value);"); ?>
            </div>
          </div>
          <div class="col-xs-6 bloque">
            <label for="horas_normales<?php echo $i?>" class="">Kil&oacute;metros actuales:</label>
            <input type="text" id="txt_km_act" name="txt_km_act" class="form-control decimal">
            <input type="hidden" name="txt_km_act_ini" id="txt_km_act_ini" value="<?php echo $par_km_act ?>">
          </div>

        </div>
        
        <div id="capa_pla_list" style="display:none;">
          <?php
          $sSQL = "SELECT * FROM PLA_ENC where PE_FEC = '" . date("Y-m-d") . " 00:00:00'";
          $pla_enc=DB_CONSULTA($sSQL);
          $pla_num=0;
          if ($row = mysql_fetch_assoc($pla_enc)) {
            $pla_num=$row['PE_ID'];
            $pla_fec=$row['PE_FEC'];
          }

          $pla_ctd = DB_LEE_CAMPO("PLA_ENC_ENC","COUNT(*)","PEE_PE=" . $pla_num . " AND PEE_ENC= " . $_SESSION['GLB_USR_ID']);
          if($pla_num!=0 && $pla_ctd!=0){
            ?>
            <p></p>
            <div class="col-xs-12 bloque">
              <output class=""><b>Confirme planificación del personal.<br>El botón GUARDAR está al final de la lista<br><br>PLANIFICACIÓN <? echo $pla_fec; ?> (de su personal):</b></output>
            <?php
            $sSQL="SELECT PLA_ENC_DET.PED_OPE, CONCAT_WS(' ',USR_WEB.USR_NOM, USR_WEB.USR_APE) AS OPERARIO, SUM(PLA_ENC_DET.PED_HOR) AS HORAS FROM ((PLA_ENC_ENC INNER JOIN PLA_ENC_DET ON PLA_ENC_ENC.PEE_ID=PLA_ENC_DET.PED_PEE) INNER JOIN PRE ON PRE.PRE_ID=PLA_ENC_ENC.PEE_PRE) LEFT JOIN USR_WEB ON USR_WEB.USR_ID=PLA_ENC_DET.PED_OPE WHERE PLA_ENC_ENC.PEE_PE=" . $pla_num . " AND PLA_ENC_ENC.PEE_ENC=" . $usuario . " GROUP BY PLA_ENC_DET.PED_OPE, CONCAT_WS(' ',USR_WEB.USR_NOM, USR_WEB.USR_APE) ORDER BY SUM(PLA_ENC_DET.PED_HOR) DESC, CONCAT_WS(' ',USR_WEB.USR_NOM, USR_WEB.USR_APE)";
            $pla_det=DB_CONSULTA($sSQL);
            $ope_ult='';
            echo "<table width='100%'>";
            echo "<tr>";
            echo "<td style='text-align:right;'><b>Horas plan.&nbsp&nbsp</b></td>";
            echo "<td width='75%''><b>Operario</b></td>";
            echo "</tr>";
            while ($row = mysql_fetch_assoc($pla_det)) {
              echo "<tr>";
              echo "<td style='text-align:right; vertical-align:top;'>" . $row['HORAS'] . " hr.&nbsp&nbsp</td>";
              echo "<td width='75%''><a href='#det_" . $row['PED_OPE'] . "'>" . $row['OPERARIO'] . "</a></td>";
              echo "</tr>";
            }
            echo "</table><br><br>";
            ?>
            </div>
            <?php
          }
        ?>
        </div>

        <?php
          $sentencia_img = DB_CONSULTA("SELECT * FROM PAR_IMG_TEMP WHERE PI_PAR=" . $id);
          $par_img_temp = array();
          while ($row = mysql_fetch_assoc($sentencia_img)) {
            $par_img_temp[] = $row['PI_IMG'];
          }
        ?>

        <div id="capa_imagenes" style="display: none;">
          <div class="col-xs-10 mb-lg-4 mt-lg-4" style="margin-top: 2%;">
            <label for="descripcion">Designación de RP, JT y asignación parejas Buddy Partner / Charla diaria de SYS:</label>
          </div>

          <!-- Añadir múltiples imágenes -->
          <div class="col-xs-10 mb-lg-4 mt-lg-4">
            <div class="input-group">
              <span class="input-group-btn">
                  <span class="btn btn-primary btn-file">
                    <i class="fa fa-picture-o" aria-hidden="true"></i>
                    Añadir Fotos <input type="file" id="fil_image" name="archivo[]" value="archivo[]" accept=".jpg" multiple="multiple">
                  </span>
              </span>
              <input type="text" class="form-control" readonly id="lbl_img<?php echo $i;?>" name="lbl_img<?php echo $i;?>" value="<?php echo $par_img_temp[$i-1];?>">
              <span class="input-group-btn">
                <span class="btn btn-default" onclick="javascript: document.formulario.lbl_img<?php echo $i;?>.value='';document.formulario.txt_img<?php echo $i;?>.value='';$('#img_foto<?php echo $i;?>').attr('src','img/ico_camera.png');"><i class="fa fa-times"></i></span>
              </span>
            </div>
          </div>
        </div>
        
        <div id="capa_planificacion" style="display:none;">
          <input type="hidden" id="txt_pee" name="txt_pee">
          <input type="hidden" id="txt_jef" name="txt_jef">
          <input type="hidden" name="txt_pla_pee" id="txt_pla_pee">
          <input type="hidden" name="txt_pla_ope_sel" id="txt_pla_ope_sel" >
          <table class="table" style="margin:5px;">
          <?php 
          //20210621 INI. se anula iniciar planificado
          if (1==2){
            $sql = "SELECT * FROM LIST_PLANIFICACION_JEF";
            $sql .= " WHERE Operario=" . $_SESSION['GLB_USR_ID'] . " AND Fecha=CURDATE()";
            $sql .= " AND Parte IS NULL";
            $sql .= " AND JEFE=1";
            $sentencia = DB_CONSULTA($sql);
            $numero = mysql_num_rows($sentencia);
            if ($numero!=0){
              ?>
              <tr><td><strong>INICIAR TRABAJO PLANIFICADO COMO JEFE<?php echo mysqli_num_rows($sentencia);?></strong></td></tr>
              <?php
            }
            while ($row = mysql_fetch_assoc($sentencia)) { ?>
                <tr>
                  <td class="detalle_fila" onclick="javascript: document.getElementById('txt_pee').value=<?php echo $row['PEE_ID']?>;document.getElementById('txt_jef').value=1;ejecuta('');">
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
                            <td></td>
                            <td align="right"><b><?php echo $row['Población'];?></b></td>
                        </tr>
                    </table>
                  </td>
                </tr>
                <tr>
                  <td>

            <?php 
              echo DB_LIST_CHECK_OPE_DISPONIBLES("LIST_PLANIFICACION_JEF INNER JOIN USR_WEB ON USR_WEB.USR_ID=LIST_PLANIFICACION_JEF.Operario","Operario","CONCAT_WS(' ',USR_NOM,USR_APE)","PEE_ID=".$row['PEE_ID']." AND JEFE=0","","chk_pla_ope","","","onchange=javascript:registrar_seleccion_opes(".$row['PEE_ID'].",this.value,this.checked);","");
              }
              ?>
                </td>
              </tr>
              <?php
            }
            //20210621 FIN. se anula iniciar planificado
            ?>
          </table>
          <br><br>

          <table class="table" style="margin:5px;">
          <?php 
          //20210621 INI. se anula unirse a trabajo iniciado por jefe planificado
          if (1==2){
            $sql = "SELECT LIST_PLANIFICACION_JEF.*, USR_JOR.UJ_PEE, CONCAT_WS(' ',USR_WEB.USR_NOM,USR_WEB.USR_APE) AS jefe_nombre ";
            $sql .= " FROM LIST_PLANIFICACION_JEF LEFT JOIN USR_JOR ON PEE_ID=UJ_PEE AND UJ_PLA_JEF=1 LEFT JOIN USR_WEB ON USR_JOR.UJ_USU=USR_WEB.USR_ID ";
            $sql .= " WHERE (UJ_TIP_STOP=1 OR UJ_TIP_STOP IS NULL) AND Fecha=CURDATE() AND Parte IS NULL AND (JEFE IS NULL OR JEFE=0)";
            $sql .= " AND Operario=" . $_SESSION['GLB_USR_ID'];
            $sql .= " ORDER BY USR_WEB.USR_NOM, USR_WEB.USR_APE";
            $sentencia = DB_CONSULTA($sql);

            $jefe_nombre="";
            $pre_ptes_ini_jef="";
            while ($row = mysql_fetch_assoc($sentencia)) { 
              if ($row['UJ_PEE']==""){
                // registrar los trabajos planificados sin iniciar aún por el jefe
                $jef_nom=DB_LEE_CAMPO("PLA_ENC_DET INNER JOIN USR_WEB ON PED_OPE=USR_ID","CONCAT_WS(' ',USR_NOM,USR_APE)","PED_PEE=" . $row['PEE_ID'] . " AND PED_JEF=1");
                if ($jef_nom=="0"){$jef_nom="sin jefe asignado";}
                $pre_ptes_ini_jef .= $row['Presupuesto'] . " - (" . substr($jef_nom, 0, 20) . "...)<br>";
              } else {
                // trabajos iniciados por el jefe
                if ($jefe_nombre<>$row['jefe_nombre']){
                  ?>
                <tr><td>Unirse a parte iniciado por: <strong><?php echo $row['jefe_nombre']; ?></strong></td></tr>
                  <?php
                }
                $jefe_nombre=$row['jefe_nombre'];
                ?>
                <tr>
                  <td class="detalle_fila" onclick="javascript: document.getElementById('txt_pee').value=<?php echo $row['PEE_ID']?>;document.getElementById('txt_jef').value=0;ejecuta('');">
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
                            <td colspan="2"><b><?php echo $row['Población'];?></b></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td align="right">Pulsa para unirse</td>
                        </tr>
                    </table>
                  </td>
                </tr>
              <?php
              }
            }

            if ($pre_ptes_ini_jef!=""){
              ?>
              <tr><td><strong>Presupuestos PENDIENTES DE INICIAR por el jefe de trabajo</strong><br><?php echo $pre_ptes_ini_jef; ?><br><br></td></tr>
              <?php
            }
          }
          //20210621 FIN. se anula unirse a trabajo iniciado por jefe planificado


          if ($bofi!=1){
            ?>
            <tr><td><strong>UNIRSE A</strong></td></tr>
            <?php
            $sql = "SELECT CONCAT_WS(' ',USR_NOM,USR_APE) AS jefe_nombre, USR_JOR.* ";
            $sql .= " FROM USR_JOR INNER JOIN USR_WEB ON UJ_USU=USR_ID ";
            $sql .= " WHERE UJ_FEC_INI>=CURDATE() AND UJ_FEC_FIN IS NULL AND UJ_PEE<0 AND UJ_PLA_JEF=1 AND USR_OFI=0";
            $sql .= " ORDER BY UJ_FEC_INI;";
            $sentencia = DB_CONSULTA($sql);
            
            $jefe_nombre="";
            while ($row = mysql_fetch_assoc($sentencia)) { 
                /*
                // trabajos iniciados por el jefe
                if ($jefe_nombre<>$row['jefe_nombre']){
                  ?>
                <tr><td>Unirse a parte iniciado por: <strong><?php echo $row['jefe_nombre']; ?></strong></td></tr>
                  <?php
                }
                $jefe_nombre=$row['jefe_nombre'];
                */
                ?>
                <tr>
                  <td class="detalle_fila" onclick="javascript: document.getElementById('txt_pee').value=<?php echo $row['UJ_PEE']?>;document.getElementById('txt_jef').value=0;ejecuta('');">
                    <table style="width:100%;">
                        <tr>
                            <td class="id_registro" colspan="2">UNIRSE A: <strong><?php echo $row['jefe_nombre']; ?></td>
                        </tr>
                        <tr>
                            <td colspan="2">Iniciado: <?php echo STR_hora($row['UJ_FEC_INI']);?></td>
                        </tr>
                        <tr>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td align="right">Pulsa para unirse</td>
                        </tr>
                    </table>
                  </td>
                </tr>
              <?php
            }
          }

          ?>
            <tr><td><br><br></td></tr>
            <tr><td><strong>INICIAR TRABAJO NUEVO</strong></td></tr>
            <tr>
              <td class="detalle_fila" onclick="javascript: document.getElementById('txt_pee').value=<?php echo nuevo_contador_sinplanificar($_SESSION['GLB_USR_ID']); ?>;document.getElementById('txt_jef').value=1;ejecuta('');">
              <!--<td class="detalle_fila" onclick="javascript: document.getElementById('txt_pee').value=-<?php echo $_SESSION['GLB_USR_ID'] . str_replace(":","",date("H:i:s"))
              ; ?>;document.getElementById('txt_jef').value=1;ejecuta('');">-->
                <table style="width:100%;">
                    <tr>
                        <td class="id_registro"><strong>INICIAR TRABAJO</td>
                        <td align="right"></td>
                    </tr>
                    <tr>
                        <td colspan="2">NUEVO PARTE</td>
                    </tr>
                    <tr>
                        <td colspan="2"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td align="right">Pulsa para iniciar</td>
                    </tr>
                </table>
              </td>
            </tr>
            <tr>
                <td>
                <?php 
                if ($bofi!=1){
                  //date("w")=número de la semana, 1=lunes
                  //date("G")=hora sin ceros
                  if (date("w")==0 || date("w")==6 || date("G")<6 || date("G")>15){
                    //fuera de jornada, aparecerán aunque no tenga inicio de jornada
                    echo DB_LIST_CHECK_OPE_DISPONIBLES("LIST_OPERARIOS_DISPONIBLE_AHORA","USR_ID","OPERARIO","USR_ID<>" . $_SESSION['GLB_USR_ID'],"USR_TRA_INI,OPERARIO","chk_pla_ope","","","onchange=javascript:registrar_seleccion_opes(-1,this.value,this.checked);");
                  } else {
                    //dentro de jornada, solo los que tengan inicio de jornada
                    echo DB_LIST_CHECK_OPE_DISPONIBLES("LIST_OPERARIOS_DISPONIBLES_HOY","UJ_USU","Operario","UJ_USU<>" . $_SESSION['GLB_USR_ID'] . " AND USR_JOR_INI=1","USR_JOR_INI DESC, USR_TRA_INI, Operario","chk_pla_ope","","","onchange=javascript:registrar_seleccion_opes(-1,this.value,this.checked);");
                  }
                }
                ?>
                </td>
              </tr>
          </table>
        </div>

        <div id="capa_guardar" class="col-xs-12" style="margin-top: 15px; margin-bottom: 15px;">
          <button type="button" id="btn_guardar" class="btn btn-danger btn-block" onclick="javascript: ejecuta('');">Guardar</button>
        </div>

        <div id="capa_pla_list2" style="display:none;">
          <div class="col-xs-12 bloque">
          <?php
          if($pla_num!=0 && $pla_ctd!=0){
            ?>
            <p></p>
            <?php
            echo "<label class=''>DETALLE OBRAS:</output>";
            $sSQL="SELECT PLA_ENC_DET.PED_OPE, CONCAT_WS(' ',USR_WEB.USR_NOM, USR_WEB.USR_APE) AS OPERARIO, SUM(PLA_ENC_DET.PED_HOR) AS HORAS, PLA_ENC_ENC.PEE_PRE, PRE.PRE_DES FROM ((PLA_ENC_ENC INNER JOIN PLA_ENC_DET ON PLA_ENC_ENC.PEE_ID=PLA_ENC_DET.PED_PEE) INNER JOIN PRE ON PRE.PRE_ID=PLA_ENC_ENC.PEE_PRE) LEFT JOIN USR_WEB ON USR_WEB.USR_ID=PLA_ENC_DET.PED_OPE WHERE PLA_ENC_ENC.PEE_PE=" . $pla_num . " AND PLA_ENC_ENC.PEE_ENC=" . $usuario . " GROUP BY PLA_ENC_ENC.PEE_PRE, PLA_ENC_DET.PED_OPE, CONCAT_WS(' ',USR_WEB.USR_NOM, USR_WEB.USR_APE) ORDER BY CONCAT_WS(' ',USR_WEB.USR_NOM, USR_WEB.USR_APE), SUM(PLA_ENC_DET.PED_HOR) DESC, PLA_ENC_ENC.PEE_PRE";
            $pla_det=DB_CONSULTA($sSQL);
            $ope_ult='';
            while ($row = mysql_fetch_assoc($pla_det)) {
              if ($ope_ult!=$row['OPERARIO']){
                echo "</div>";
                echo "<div class='col-xs-12 bloque'>";
                echo "<label class='' id='det_" . $row['PED_OPE'] . "'>" . $row['OPERARIO'] . "</label>";
              }
              $ope_ult=$row['OPERARIO'];
              echo "<table width='100%'><tr><td width='100px' style='text-align:right; vertical-align:top;'>" . $row['HORAS'] . " hr.&nbsp&nbsp</td><td width='100px' style='text-align:middle; vertical-align:top;'>" . $row['PEE_PRE'] . "</td><td text-align='middle'>" . substr($row['PRE_DES'],0,60) . "...</td></tr></table>" ;
            }
          }
          ?>
          </div>
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
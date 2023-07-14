<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1">
    
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp"></script>
    <!--<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>-->
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

      $uj_id = $_REQUEST['ujid'];
      if ($uj_id==""){
          $uj_id=0;
      }
      $sentencia_jor = DB_CONSULTA("SELECT * FROM USR_JOR WHERE UJ_ID=" . $uj_id);
      $ini="";
      $fin="";
      if (mysql_num_rows($sentencia_jor) == 1) {
          $row = mysql_fetch_assoc($sentencia_jor);
          $ini=$row['UJ_FEC_INI'];
          $fin=$row['UJ_FEC_FIN'];
      }

      $solicitud_pte="";
      $sentencia_sol = DB_CONSULTA("SELECT * FROM MISC WHERE MSC_CLV LIKE 'SOL_MOD%' AND MSC_VAL1=" . $uj_id);
      if (mysql_num_rows($sentencia_sol) >= 1) {
          $row = mysql_fetch_assoc($sentencia_sol);
          $solicitud_pte="Existe una solicitud pendiente de confirmar.<br><b>Hora inicio: " . STR_hora2($row['MSC_FEC1']) . "</b><br><b>Hora fin: " . STR_hora2($row['MSC_FEC2']) . "</b>";
      }

      $tip = $_REQUEST['tip'];
      if ($tip=="Tra"){
          $tip_nom="Trabajo";
      } else if ($tip=="Jor"){
          $tip_nom="Jornada";
      }  else if ($tip=="Veh"){
          $tip_nom="Vehículo";
      }  else if ($tip=="Des"){
          $tip_nom="Descanso";
      }
    ?>
    
  <script type="text/javascript">

      var myXhr = $.ajaxSettings.xhr();

      // Muy importante. Necesaria para validar entradas en campos input de tipo numérico
      window.onload = function() {
          validar_campos_input();
      }

      function validar_formulario(){ 
        var msg = "";

        if (document.formulario.txt_tipo.value.length == 0) {
          msg += "<p>OPERACIÓN a realizar no definida.</p>";
        } 

        if (document.formulario.txt_hor_ini.value==document.formulario.txt_hor_ini_ori.value && document.formulario.txt_hor_fin.value==document.formulario.txt_hor_fin_ori.value){
          msg = msg + "<p>Sin cambios en hora INICIO/FIN detectado.</p>";
        }

        if (document.formulario.txt_hor_ini.value.length==0) {
          msg = msg + "<p>Es necesario indicar una hora de INICIO válida.</p>";
        } 

        /*
        if (document.formulario.txt_hor_fin.value.length==0) {
          msg = msg + "<p>Es necesario indicar una hora de FIN válida.</p>";
        } 
        */

        if (msg != "") {
          //document.getElementById("errores").innerHTML = msg
          document.getElementById("modal_text").innerHTML = msg;
          $("#myModal").modal("show");
          return 0;
        } else {
          //document.getElementById("cargando").style.display = "block";
          //document.formulario.submit();
          // Se cambia por petición Ajax con barra de progreso
          uploadAjax("exe_list_jortra_det.php");
        }
      } 

      function habilitar_capa(id) {
        document.getElementById('nuevoparte1').style.display = "none";
        document.getElementById('nuevoparte2').style.display = "none";
        document.getElementById('nuevoparte_fin').style.display = "none";
        document.getElementById(id).style.display = "block";
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


    /* Código para input type="file" */
    $(document).on('change', '.btn-file :file', function() {
      var input = $(this),
      numFiles = input.get(0).files ? input.get(0).files.length : 1,
      label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
      input.trigger('fileselect', [numFiles, label]);
    });

  </script>
</head>

<body class="framework">
  <div class="container">
    <form name="formulario" id="formulario" class="form-horizontal" role="form" method="POST" action="exe_list_jortra_det.php" enctype="multipart/form-data" onsubmit="return false;">
    <input type="hidden" id="txt_tipo" name="txt_tipo" value="<?php echo $tip_nom;?>" />
    <input type="hidden" id="txt_uj_id" name="txt_uj_id" value="<?php echo $uj_id;?>" />
    <input type="hidden" name="txt_hor_fini_ori" value="<?php echo date("Y-m-d",strtotime($ini));?>">
    <input type="hidden" name="txt_hor_ffin_ori" value="<?php echo date("Y-m-d",strtotime($fin));?>">
    <input type="hidden" id="txt_hor_ini_ori" value="<?php echo STR_hora2($ini);?>">
    <input type="hidden" id="txt_hor_fin_ori" value="<?php echo STR_hora2($fin);?>">

    <!-- Modal -->
    <div class="modal fade" id="myModal" role="dialog">
      <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Guardar Parte de Trabajo</h4>
          </div>
          <div class="modal-body alert-warning" id="modal_text">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="javascript: habilitar_capa('nuevoparte1');">Cerrar</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Inicio Nuevo Parte 1/2 -->
    <div id="nuevoparte1">
      <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
              <span class="navbar-left"><a href="list_jortra.php" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
              <?php
              if ($solicitud_pte==""){
                ?>
              <span class="navbar-right"><a href="javascript: habilitar_capa('nuevoparte2');" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
              <?php } ?>
              <p class="titulo">Modificar Marcaje 1/2</span>
      </nav>
      
      <h4>Modificar horas inicio / fin</h4>

      <div class="bloque">
        <?php
        if ($solicitud_pte<>"") {
          echo $solicitud_pte . "<br><br>";
        }
        ?>

        <div class="form-group bloque-reducido">
          <div class="col-xs-4 bloque">
            <label for="operario<?php echo $i?>" class="col-lg-2">Operación:</label>
            <input type="text" class="form-control" id="txt_tipo_nom" name="txt_tipo_nom" disabled="disabled" value="<?php echo $tip_nom;?>" />
          </div>
    
          <div class="col-xs-4 bloque">
            <label for="horas_ini" class="">Hora inicio:</label>
            <input type="time" class="form-control" id="txt_hor_ini" name="txt_hor_ini" value="<?php echo STR_hora2($ini);?>" />
          </div>

          <div class="col-xs-4 bloque">
            <label for="horas_fin" class="">Hora fin:</label>
            <input type="time" class="form-control" id="txt_hor_fin" name="txt_hor_fin" value="<?php echo STR_hora2($fin);?>" />
          </div>
        </div>
        
      </div>

    </div>    
    <!-- Fin Nuevo Parte 1/2 -->

    <!-- Inicio Nuevo Parte 2/2 -->
    <div id="nuevoparte2" style="display:none">
      <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
              <span class="navbar-left"><a href="javascript: habilitar_capa('nuevoparte1');" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
              <p class="titulo">Modificar Marcaje 2/2</span>
      </nav>

      <div id="cargando"></div>
      <button type="button" id="btn_guardar" class="btn btn-danger btn-block" onclick="javascript: validar_formulario();">Guardar solicitud modificación de horas</button>
    </div>
    <!-- Fin Nuevo Parte 2/2 -->

    <!-- Muestra Id del registro guardado -->
    <div id="nuevoparte_fin"></div>
    <!-- Fin nuevoparte_fin -->
    
    </form>
  
  </div> <!-- Fin .container -->
  <script type="text/javascript" src="bootstrap-3.2.0/js/bootstrap.min.js"></script>
</body>

</html>
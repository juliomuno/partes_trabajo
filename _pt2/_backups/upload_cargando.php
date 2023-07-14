<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1">
    
    <script type="text/javascript" src="//code.jquery.com/jquery-1.11.0.min.js"></script>
    <!--<script type="text/javascript" src="js/validacion.js"></script>-->
    <!--<script type="text/javascript" src="js/ajax.js"></script>-->

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
    
  <script type="text/javascript">

      function showMessage(message) {
        $("#cargando").html("").show();
        $("#cargando").html(message);
      }

      function showProgreso(porcentaje) {
        var msg;
        
        msg = '<h3 style="text-align=center;">' + porcentaje + "%</h3>";
        msg += '<div class="progress">';
        msg += '<div class="progress-bar progress-bar-infor" role="progressbar" aria-valuenow="' + porcentaje + '" aria-valuemin="0" aria-valuemax="100" style="width:' + porcentaje + '%">';
        //msg += "<span>" + porcentaje + "%</span>";
        msg += "</div>";
        msg += '</div>';

        if (porcentaje == 0) {
          $("#cargando").html("").show();
        }
        $("#cargando").html(msg);
        //$("#cargando").attr("aria-valuenow") = porcentaje;
        //$("#cargando").style.width = porcentaje + "%";
      }

      function uploadAjax(){
        var data = new FormData($('#formulario')[0]);
        var url = "exe_upload_cargando.php";
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
                  showProgreso(0);
                },
            }).done(function(respuesta) {
                  if (respuesta.error) {
                    showMessage("<p>Terminó con errores </p>");  
                  } else {
                    showMessage("<p>Terminó " + respuesta.mensaje + "</p>");  
                  }
            });
      }

      function progress(e){
        if(e.lengthComputable){
            var max = e.total;
            var current = e.loaded;

            var Percentage = Math.floor((current * 100)/max);

            if(Percentage >= 100)
            {
              showProgreso(Percentage);
            } else if (Percentage >= 0)  {
              showProgreso(Percentage);
            }
        }  
      }

      $(document).ready(function() {
        
        //hacemos la petición ajax  
        /*$("#formulario").ajax {
          url: 'exe_upload_cargando.php',  
          type: 'POST',
          // Form data
          //datos del formulario
          data: formData,
          //necesario para subir archivos via ajax
          cache: false,
          contentType: false,
          processData: false,
          //mientras enviamos el archivo
          beforeSend: function(){
              message = $("<p>Cargando datos ...</p>");
              showMessage(message)        
          },
          uploadProgress: function(event, position, total, percentComplete) { 
            //Progress bar
            showMessage("<p>Porcentaje: " + percentComplete + "</p>");
          },
          //una vez finalizado correctamente
          success: function(data){
              message = $("<p>La imagen se subió correctamente</p>");
              showMessage(message);
          },
          //si ha ocurrido un error
          error: function(){
              message = $("<p>Ha ocurrido un error.</p>");
              showMessage(message);
          }
        }*/
        
      });

  </script>
</head>

<body class="framework">
	<div class="container">
    <div id="cargando"></div>
    <form id="formulario" name="formulario" class="form-horizontal" role="form" method="POST" enctype="multipart/form-data" onsubmit="return false;">
    
      <input type="file" name="txt_img1" id="txt_img1"/>
      <input type="file" name="txt_img2" id="txt_img2"/>
      <input type="text" name="txt_nombre" id="txt_nombre" />
      <button type="button" id="btn_guardar" class="btn btn-danger btn-block" onclick="uploadAjax();">Guardar</button>
      </form>
  </div> <!-- Fin .container -->
  
  <script type="text/javascript" src="bootstrap-3.2.0/js/bootstrap.min.js"></script>
</body>

</html>
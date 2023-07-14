<?php
  include "../comun/funciones.php";
  include "../comun/db_con.php";
  
  session_start();

  if (!isset($_SESSION['GLB_USR_ID'])) {
    php_redirect('../index.php');
  }
       
  $op = $_REQUEST['op'];
  $insp_id = $_REQUEST['insp_id'];
  $frm_id = $_REQUEST['frm_id'];

  $nombre_formulario = DB_LEE_CAMPO("FRM", "FRM_NOM", "FRM_ID=" . $frm_id);
  $nombre_categoria = '';

  if (strlen($nombre_formulario)>20){
    $nombre_formulario=substr($nombre_formulario, -20);
  }
?>

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

  <style>
    hr {
      margin:1em 0em 1em 0em;
      padding:0;
    }
  </style>

  <!-- librerías opcionales que activan el soporte de HTML5 para IE8 -->
  <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>

<body class="framework">
	<div class="container">
		<form name="formulario" id="formulario" class="form-horizontal" role="form" method="POST" action="exe_inspeccion_formulario.php" enctype="multipart/form-data" onsubmit="return false;">
		<input type="hidden" name="op" value="<?php echo $op;?>" />
    <input type="hidden" name="insp_id" value="<?php echo $insp_id;?>" />
    <input type="hidden" name="frm_id" value="<?php echo $frm_id;?>" />

		<!-- Modal -->
    <div class="modal fade" id="myModal" role="dialog">
      <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Guardar For. <?php echo $nombre_formulario?></h4>
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
        <span class="navbar-left"><a href="inspeccion.php?id=<?php echo $insp_id?>&op=M" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
        <span class="navbar-right"><a href="javascript: habilitar_capa('nuevoparte2');" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
        <p class="titulo">For. <?php echo $nombre_formulario?></p>
      </nav>
			<?php 
			if ($op == 'C') {
	 			$sql = "SELECT PREG.*, PREG_CAT.* FROM PREG, PREG_CAT WHERE PREG_FRM=" . $frm_id . " AND PREG_CAT=PC_ID ORDER BY PC_ORD, PREG_ORD";
	 			$sentencia = DB_CONSULTA($sql);
	 			$i = 1;
	 			while ($row = mysql_fetch_assoc($sentencia)) {
	 				if ($nombre_categoria != $row['PC_NOM']) {
	 					$nombre_categoria = $row['PC_NOM'];
	 					echo '<h4>' . $row['PC_ORD'] . '. ' . $nombre_categoria . '</h4>';
	 				}
	 				echo '<div class="row">';
	 				echo    '<p>' . $row['PC_ORD'] . '.' . $row['PREG_ORD'] . '. ' . $row['PREG_NOM'] . '</p>';
	 				echo    '<input type="hidden" name="txt_preg[]" value="' . $row['PREG_ID'] . '" />';
          echo '</div>';
	 			?>
          <div class="row">
  	 				<div class="col-md-12">
              <div class="form-group">
  	 				  <?php echo DB_COMBOBOX("RESP","RESP_ID","RESP_NOM","","RESP_NOM","cmb_resp[]","cmb_resp[]" ,"form-control","","","","",3);?>
  	 				  </div>
              <!-- ./form-group -->
            </div>
            <!-- ./col -->
          </div>
          <!-- ./row -->

          <div class="row">
  	 				<div class="col-md-12">
  		        <div class="input-group form-group">
  		          <span class="input-group-btn">
  		              <span class="btn btn-primary btn-file">
  		                Seleccionar&hellip; <input type="file" id="txt_img<?php echo $row['PREG_ID']?>" name="txt_img<?php echo $row['PREG_ID']?>" accept=".jpg,.jpeg">
  		              </span>
  		          </span>
  		          <input type="text" class="form-control" readonly id="lbl_img<?php echo $row['PREG_ID']?>" name="lbl_img<?php echo $row['PREG_ID']?>" value="">
  		          <span class="input-group-btn">
  		            <span class="btn btn-default" onclick="javascript: document.formulario.lbl_img<?php echo $row['PREG_ID']?>.value='';document.formulario.txt_img<?php echo $row['PREG_ID']?>.value='';$('#img_foto<?php echo $row['PREG_ID']?>').attr('src','../img/ico_camera.png');"><i class="fa fa-times"></i></span>
  		          </span>
  		        </div>
  		        <!-- ./form-group -->
  		      </div>
  		      <!-- ./col -->
		      </div>
  		    <!-- ./row -->
  		    
          <div class="row">
  			    <div class="col-xs-3">
  	          <?php $imagen_preliminar = "";?>
  	        
  	        	<img src="<?php echo $imagen_preliminar;?>" width="150" class="img_miniatura" id="img_foto<?php echo $row['PREG_ID'];?>" name="img_foto<?php echo $row['PREG_ID'];?>"/>
  	      	</div>
  	      	<!-- ./col -->
  	      </div>
  	 			<!-- ./row -->
	 			 <hr />
	 			<?php
	 				$i++;
	 			}
	 		} else {
        $sql = "SELECT INSP_RESP.*, PREG_ID, PREG_ORD, PREG_NOM, PREG_CAT.* FROM INSP_RESP, PREG, PREG_CAT WHERE IR_INSP=" . $insp_id . " AND IR_FRM=" . $frm_id . " AND IR_PREG=PREG_ID AND PREG_CAT = PREG_CAT.PC_ID ORDER BY PC_ORD, PREG_ORD";
        $sentencia = DB_CONSULTA($sql);
        $i = 1;
        while ($row = mysql_fetch_assoc($sentencia)) {
          if ($nombre_categoria != $row['PC_NOM']) {
	 					$nombre_categoria = $row['PC_NOM'];
	 					echo '<h4>' . $row['PC_ORD'] . '. ' . $nombre_categoria . '</h4>';
	 				}
	 				echo '<div class="row">';
	 				echo    '<p>' . $row['PC_ORD'] . '.' . $row['PREG_ORD'] . '. ' . $row['PREG_NOM'] . '</p>';
	 				echo    '<input type="hidden" name="txt_preg[]" value="' . $row['PREG_ID'] . '" />';
          echo '</div>';
        ?>
        	<div class="row">
		 				<div class="col-md-12">
	            <div class="form-group">
		 				  <?php echo DB_COMBOBOX("RESP","RESP_ID","RESP_NOM","","RESP_NOM","cmb_resp[]","cmb_resp[]" ,"form-control",$row['IR_RESP'],"","","");?>
		 				  </div>
	            <!-- ./form-group -->
	          </div>
	          <!-- ./col -->
          </div>
          <!-- ./row -->


          <div class="row">
            <div class="col-md-12">
              <div class="input-group form-group">
                <span class="input-group-btn">
                    <span class="btn btn-primary btn-file">
                      Seleccionar&hellip; <input type="file" id="txt_img<?php echo $row['PREG_ID']?>" name="txt_img<?php echo $row['PREG_ID']?>" accept=".jpg,.jpeg">
                    </span>
                </span>
                <input type="text" class="form-control" readonly id="lbl_img<?php echo $row['PREG_ID']?>" name="lbl_img<?php echo $row['PREG_ID']?>" value="<?php echo $row['IR_IMG']?>">
                <span class="input-group-btn">
                  <span class="btn btn-default" onclick="javascript: document.formulario.lbl_img<?php echo $row['PREG_ID']?>.value='';document.formulario.txt_img<?php echo $row['PREG_ID']?>.value='';$('#img_foto<?php echo $row['PREG_ID']?>').attr('src','../img/ico_camera.png');"><i class="fa fa-times"></i></span>
                </span>
              </div>
              <!-- ./form-group -->
            </div>
            <!-- ./col -->
          </div>
          <!-- ./row -->

          <div class="row">
            <div class="col-xs-3">
              <?php if ($row['IR_IMG'] == '') {
                $imagen_preliminar = "";
              } else {
                $imagen_preliminar = "../inspecciones_imagenes/" . $row['IR_IMG'];
              }?>
              <a href="<?php echo $imagen_preliminar;?>"><img src="<?php echo $imagen_preliminar;?>" width="150" class="img_miniatura" id="img_foto<?php echo $row['PREG_ID'];?>" name="img_foto<?php echo $row['PREG_ID'];?>"/></a>
            </div>
            <!-- ./col -->
          </div>
          <!-- ./row -->
          <hr />
        <?php
          $i++;
        }
      }
	  	?>

    </div>
    <!-- ./nuevoparte1 -->

    <div id="nuevoparte2" style="display:none">
        <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
            <span class="navbar-left"><a href="javascript: habilitar_capa('nuevoparte1');" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
            <span class="navbar-right" style="visibility:hidden;"><a href="#" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
            <p class="titulo">For. <?php echo $nombre_formulario?></p>
        </nav>

      <div id="cargando"></div>
      <div class="row" style="margin-bottom: 1em;">
        <div class="col-xs-12">
          <button type="button" id="btn_guardar" class="btn btn-danger btn-block" onclick="javascript: validar_formulario();">Guardar</button>
        </div>
        <!-- ./col -->
      </div>
      <!-- ./row -->
    </div>  
    <!-- ./nuevoparte2 -->
	  	
  	<!-- Muestra Id del registro guardado -->
    <div id="nuevoparte_fin"></div>
    <!-- Fin nuevoparte_fin -->
  	
  	</form>
  </div>
  <!-- ./container -->

	<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
  <script type="text/javascript" src="../js/ajax.js"></script>
  <script type="text/javascript" src="../js/validacion.js"></script>
  <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3.exp"></script>
  <script type="text/javascript" src="../bootstrap-3.2.0/js/bootstrap.min.js"></script>
  
  <script>
    var myXhr = $.ajaxSettings.xhr();

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

    $("input[name^='txt_img']").change(function(){
        mostrar_miniatura(this, $(this).attr("id").replace("txt_img", "img_foto"));
    });

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
    });

    function habilitar_capa(id) {
      document.getElementById('nuevoparte1').style.display = "none";
      document.getElementById('nuevoparte2').style.display = "none";
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

      $("select[name='cmb_resp[]']").each(function(indice, elemento) {
    		if ($(elemento).val() == 0) {
    			msg += '<p>Es necesario indicar una respuesta para la pregunta ' + (indice+1) + '</p>';
				}
			});

      if (msg != "") {
        //document.getElementById("errores").innerHTML = msg
        document.getElementById("modal_text").innerHTML = msg;
        $("#myModal").modal("show");
        return 0;
      } else {
        //document.getElementById("cargando").style.display = "block";
        //document.formulario.submit();
        // Se cambia por petición Ajax con barra de progreso
        uploadAjax("exe_inspeccion_formulario.php");
      }
    } 
	
</script>

</body>
</html>
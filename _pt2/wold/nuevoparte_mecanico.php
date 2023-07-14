<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1">
    
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp"></script>
    <script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
    <script type="text/javascript" src="../js/validacion.js"></script>

    <title>Moneleg - Partes de Trabajo</title>
 
    <!-- CSS de Bootstrap -->
    <link href="../bootstrap-3.2.0/css/bootstrap.min.css" rel="stylesheet" media="screen">
    
    <link rel="stylesheet" href="../plugins/font-awesome/css/font-awesome.css">
    <link rel="stylesheet" type="text/css" href="../css/framework.css" media="screen">
    
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
      $par_tip = 7; // Tipo de Parte (tabla PAR_TIP)
      
      if ($op != 'C') {
        $sentencia = DB_CONSULTA("SELECT * FROM PAR WHERE PAR_ID=" . $id);
        if (mysql_num_rows($sentencia) == 1) {
          $row = mysql_fetch_assoc($sentencia);
          $par_id = $row['PAR_ID'];
          $par_fec = date("Y-m-d", strtotime($row['PAR_FEC']));
          $par_hini = STR_Hora($row['PAR_HINI']);
          $par_hfin = STR_Hora($row['PAR_HFIN']);
          $par_veh = $row['PAR_VEH'];
          if (!is_null($par_veh)) { // Tabla PAR_TIP_MEC
            $par_tip_mec = 1; // Mantenimiento Vehículos
          } else {
            $par_tip_mec = 2; // Trabajos Varios
          }

          $par_rea = $row['PAR_REA'];
          $par_obs = $row['PAR_OBS'];
          
          $sentencia_ope = DB_CONSULTA("SELECT * FROM PAR_DET WHERE PD_PAR=" . $id);
          $par_ope = array();
          while ($row = mysql_fetch_assoc($sentencia_ope)) {
            $par_ope[] = $row['PD_OPE'];
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
        $par_hini = ultima_hora_parte();
        $par_hfin = date("H:i:00");
        $par_tip_mec = 1; // Predeterminado Mantenimiento de Vehículos - Tabla PAR_TIP_MEC
        $par_ope = array(2);

        $sentencia = DB_CONSULTA("SELECT USR_ID, USR_COM_HAB FROM USR_WEB WHERE USR_ID=" . $_SESSION['GLB_USR_ID']);
        if (mysql_num_rows($sentencia) == 1) {
          $row = mysql_fetch_assoc($sentencia);
          for ($i=0; $i<=1; $i++) {
            if ($i == 0) {
              $par_ope[$i] = $_SESSION['GLB_USR_ID'];
            } else {
              $par_ope[$i] = $row['USR_COM_HAB'];
            }
          }
        }
      }

    ?>

    <script type="text/javascript">

      // Muy importante. Necesaria para validar entradas en campos input de tipo numérico
      window.onload = function() {
          validar_campos_input();
          // Cargar Página de Materiales de Operarios
          cargar("nuevoparte_materiales.php?par_id=<?php echo $par_id; ?>&ope1=" + document.getElementById("cmb_ope1").value + "&ope2=" + document.getElementById("cmb_ope2").value, "capa_materiales");
          habilitar_cmb_veh();
      }

      function habilitar_cmb_veh() {
        var valor;
        valor = document.formulario.cmb_tip_mec.value;
        
        if (valor.length == 0) {
          document.getElementById("bloque_vehiculo").style.display = "none";
        } else if (valor == 1) {
          document.getElementById("bloque_vehiculo").style.display = "block";
        } else if (valor == 2) {
          document.getElementById("bloque_vehiculo").style.display = "none";
        }
      }
      
      function marcar_conductores() {
        var ope_sel="";
        for(var i = 1; i < 3; i++) {
          ope_sel=document.getElementById("cmb_ope" + i)[document.getElementById("cmb_ope" + i).selectedIndex].innerHTML;
          document.getElementById("txt_ope_sel" + i).value=ope_sel;
        }
      }

      function validar_formulario(){ 
        var msg="";

        if (document.formulario.cmb_ope1.value.length == 0) {
          msg += "<p>Es necesario indicar un operario 1.</p>";
        } else if (isNaN(document.formulario.cmb_ope1.value.replace(",","."))) {
          msg += "<p>Es necesario indicar un Operario 1 v&aacute;lido.</p>";
        }

        if (document.formulario.txt_fec.value.length==0) {
          msg += msg + "<p>Es necesario indicar una Fecha.</p>";
        } else if (!fecha_valida(document.formulario.txt_fec.value)) {
          msg += msg + "<p>La Fecha indicada no tiene un formato v&aacute;lido. DD/MM/YYYY</p>";
        }

        if (document.formulario.txt_kmact.value.length==0 || document.formulario.txt_kmact.value.length=="") {
          msg += msg + "<p>Es necesario indicar los kil&oacute;metros actuales del veh&iacute;culo.</p>";
        }

        if (document.formulario.cmb_tip_mec.value.length==0) {
          msg += "<p>Es necesario indicar un tipo de trabajo.</p>";
        } else if (isNaN(document.formulario.cmb_tip_mec.value)) {
          msg += "<p>Es necesario indicar un tipo de trabajo v&aacute;lido.</p>";
        } else {
          if (document.formulario.cmb_tip_mec.value == 1) { // Mantenimiento Vehículos - Tabla PAR_TIP_MEC
            if (document.formulario.cmb_veh.value.length == 0) {
              msg += "<p>Es necesario indicar el veh&iacute;culo reparado.</p>";
            }
          } else if (document.formulario.cmb_tip_mec.value == 2) { // Trabajos Varios - Tabla PAR_TIP_MEC
            document.formulario.cmb_veh.value = '';
          } else if (document.formulario.cmb_tip_mec.value == 3) { // Repostaje - Tabla PAR_TIP_MEC
            document.formulario.cmb_veh.value = '';
          }
          if (document.formulario.cmd_reaart.value.length==0) {
            msg += "<p>Es necesario indicar la unidad realizada.</p>";
          }
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
          uploadAjax("exe_parte_mecanico.php");
        }
      }

      function habilitar_capa(id) {
        document.getElementById('nuevoparte1').style.display = "none";
        document.getElementById('nuevoparte2').style.display = "none";
        document.getElementById('nuevoparte3').style.display = "none";
        document.getElementById('nuevoparte4').style.display = "none";
        document.getElementById('nuevoparte5').style.display = "none";
        document.getElementById('nuevoparte_fin').style.display = "none";
        document.getElementById(id).style.display = "block";
      }

      function valida_enter(e) {
        var tecla;

        tecla=(document.all) ? e.keyCode : e.which; 
        if (tecla == 13) {
          mostrar_material_almacen(true);
          //document.getElementById("txt_buscar_articulo").select();
          document.getElementById("cmd_buscar_articulos").focus();
        } 
      }

      // Muestra el material del almacén del operario seleccionado o el último movimiento
      // Además filtra por la búsqueda indicada
      function mostrar_material_almacen(bseleccionado) {
        var filas, cantidad, cantidad_sel;
        var bseleccionado;
        var loperario;
        var sdescripcion;
        var txt_cantidades;
        var txt_operarios;
        var txt_descripciones;
        var bmostrar_todos;

        sdescripcion = document.getElementById("txt_buscar_articulo").value;
        
        if (sdescripcion == "") {
          bmostrar_todos = true;
        } else {
          bmostrar_todos = false;
        }

        filas = document.getElementsByClassName("material");
        
        txt_operarios = document.formulario.elements["txt_mat_operarios[]"];
        txt_cantidades = document.formulario.elements["txt_mat_cantidades[]"];
        txt_descripciones = document.formulario.elements["txt_mat_descripciones[]"];
        
        if (bseleccionado) {
          // Mostrar cantidades de almacén del operario
          txt_cant = document.formulario.elements["txt_mat_cantidades_almacen[]"];
        } else {
          txt_cant = document.formulario.elements["txt_mat_cantidades_ult_movimiento[]"];
        }
        
        for (var i = 0; i < filas.length; i++) {
          if (txt_cantidades[i].value == '' || isNaN(txt_cantidades[i].value)) {
            cantidad_sel = 0;
          } else {
            cantidad_sel = parseFloat(txt_cantidades[i].value);
          }

          // Mostrar todas las filas sino hay operario seleccionado
          if (loperario == 0) {
            
            cantidad = 0;
            if (txt_cant[i].value == '' || isNaN(txt_cant[i].value)) {   
              cantidad = 0;
            } else {
              cantidad = parseFloat(txt_cant[i].value);
            }
            
            if (cantidad > 0 || cantidad_sel > 0) {
              if (bmostrar_todos) {                
                filas[i].style.display = "";
              } else {
                if (quitar_tildes(txt_descripciones[i].value.toUpperCase()).indexOf(sdescripcion.toUpperCase())>=0) {
                  filas[i].style.display = "";
                } else {
                  filas[i].style.display = "none";
                }
              }
            } else {
              filas[i].style.display = "none";
            }
        
          // Mostrar filas del operario seleccionado
          } else {
            if (txt_operarios[i].value == loperario) {
              cantidad = 0;
              if (txt_cant[i].value == '' || isNaN(txt_cant[i].value)) {   
                cantidad = 0;
              } else {
                cantidad = parseFloat(txt_cant[i].value);
              }
              
              if (cantidad > 0 || cantidad_sel > 0) {
                if (bmostrar_todos) {                
                  filas[i].style.display = "";
                } else {
                  if (quitar_tildes(txt_descripciones[i].value.toUpperCase()).indexOf(sdescripcion.toUpperCase())>=0) {
                    filas[i].style.display = "";
                  } else {
                    filas[i].style.display = "none";
                  }
                }
              }
            } else {
              filas[i].style.display = "none";
            }
          }
        }
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
    <form name="formulario" id="formulario" class="form-horizontal" role="form" method="POST" action="exe_parte_mecanico.php" enctype="multipart/form-data" onsubmit="return false;">
    <input type="hidden" name="op" value="<?php echo $op;?>" />
    <input type="hidden" name="id" value="<?php echo $id;?>" />
    <input type="hidden" name="par_tip" value="<?php echo $par_tip;?>" />
    
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
  
    <!-- Nuevo Parte 1/4 -->
    <div id="nuevoparte1">
      <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
              <span class="navbar-left"><a href="../inicio.php" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
              <span class="navbar-right"><a href="javascript: habilitar_capa('nuevoparte2');" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
              <p class="titulo">Nuevo Parte 1/5</span>
      </nav>
      
      <h4>Datos de Trabajadores</h4>

      <!-- Bloque de Trabajadores -->
      <div class="bloque">
        <div class="form-group bloque-reducido">
          <label for="operario1" class="col-lg-2">Operario1:</label>
          <div class="col-lg-10">
            <?php echo DB_COMBOBOX("LIST_OPERARIOS","Codigo","Nombre","","Nombre","cmb_ope1","cmb_ope1","form-control",$par_ope[0],"","","javascript: marcar_conductores();"); ?>
            <input type="hidden" name="txt_ope_sel1" id="txt_ope_sel1">
          </div>
        </div>
      </div>

      <div class="bloque">
        <div class="form-group bloque-reducido">
          <label for="operario2" class="col-lg-2">Operario2:</label>
          <div class="col-lg-10">
            <?php echo DB_COMBOBOX("LIST_OPERARIOS","Codigo","Nombre","","Nombre","cmb_ope2","cmb_ope2","form-control",$par_ope[1],"","","javascript: marcar_conductores();"); ?>
            <input type="hidden" name="txt_ope_sel2" id="txt_ope_sel2">
          </div>
        </div>
      </div>

      <div class="form-group">
        <label for="fecha" class="col-lg-2">Fecha:</label>
        <div class="col-lg-10">
          <input type="date" class="form-control" name="txt_fec" value="<?php echo $par_fec;?>"></input>  
        </div>
      </div>

      <div class="bloque">
        <div class="form-group bloque-reducido">
          <label for="hora_inicio" class="col-xs-2">Hora Ini:</label>
          <div class="col-xs-4">
            <input type="time" class="form-control hora" id="txt_hini" name="txt_hini" value="<?php echo $par_hini;?>" />
          </div>
          <label for="hora_fin" class="col-xs-2">Hora Fin:</label>
          <div class="col-xs-4">
            <input type="time" class="form-control hora" id="txt_hfin" name="txt_hfin" value="<?php echo $par_hfin;?>"  />
          </div>
        </div>
      </div>
    </div>    
    <!-- Fin Nuevo Parte1/4 -->

    <!-- Nuevo Parte 2/4 -->
    <div id="nuevoparte2" style="display:none">
      <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
              <span class="navbar-left"><a href="javascript: habilitar_capa('nuevoparte1');" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
              <span class="navbar-right"><a href="javascript: habilitar_capa('nuevoparte3');" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
              <p class="titulo">Nuevo Parte 2/5</span>
      </nav>

      <h4>Datos del Trabajo</h4>
      
      <div class="form-group">
        <label for="tipo_trabajo" class="col-lg-2">Tipo de Trabajo:</label>
        <div class="col-lg-10" id="combo_tipo_trabajo">
          <?php echo DB_COMBOBOX("LIST_TRABAJOS_MECANICO","Codigo","Nombre","","Nombre","cmb_tip_mec","cmb_tip_mec","form-control",$par_tip_mec,"","","javascript:habilitar_cmb_veh();"); ?>
        </div>
      </div>

      <div class="form-group bloque-reducido" id="bloque_vehiculo">
        <label for="vehiculo" class="col-lg-2">Veh&iacute;culo:</label>
        <div class="col-lg-10">
          <?php echo DB_COMBOBOX("LIST_VEHICULOS","Codigo","Nombre","","Nombre","cmb_veh","cmb_veh","form-control",$par_veh,"","",""); ?>
        </div>
      </div>

      <div class="bloque">
        <div class="form-group bloque-reducido">
          <label for="km_actuales" class="col-xs-2">KM actuales:</label>
          <div class="col-xs-4">
            <input type="number" class="form-control decimal" id="txt_kmact" name="txt_kmact" value="<?php echo $par_kmact;?>" />
          </div>
        </div>
      </div>
      
      <div class="form-group bloque-reducido">
        <label for="realizado" class="col-lg-2">Realizado (unidades):</label>
        <div class="col-lg-10">
          <?php echo DB_COMBOBOX("LIST_ARTICULOS_MECANICOS","CodArt","Articulo","","Articulo","cmd_reaart","cmd_reaart","form-control",$par_reaart,"","",""); ?>
        </div>
      </div>
    

      <div class="form-group">
        <label for="observaciones" class="col-lg-2">Observaciones:</label>
        <div class="col-lg-10">
          <textarea class="form-control" rows="4" name="txt_obs"><?php echo $par_obs;?></textarea>
        </div>
      </div>      
    </div>
    <!-- Fin Nuevo Parte2/4 -->

    <!-- Inicio Nuevo Parte 3/7 -->
    <div id="nuevoparte3" style="display:none">
      <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
              <span class="navbar-left"><a href="javascript: habilitar_capa('nuevoparte2');" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
              <span class="navbar-right"><a href="javascript: habilitar_capa('nuevoparte4');" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
              <p class="titulo">Nuevo Parte 3/5</span>
      </nav>

      <div id="capa_materiales">
        <ul class="nav nav-pills" style="margin-bottom:.5em;">
          <li id="li_material_todos" class="active"><a href="#" onclick="javascript:habilita_material_seleccionado(false);">Todos</a></li>
          <li id="li_material_seleccionado"><a href="#" onclick="javascript: habilita_material_seleccionado(true);">Seleccionado</a></li>
        </ul>
            
        <h4>Materiales</h4>

        <div class="bloque" id="capa_filtros_materiales">
          <div class="input-group bloque-reducido">
              <input type="text" class="form-control" name="txt_buscar_articulo" id="txt_buscar_articulo" placeholder="Buscar Art&iacute;culo" onkeypress="valida_enter(event);">
              <span class="input-group-btn">
                <span class="btn btn-default" onclick="javascript: document.formulario.txt_buscar_articulo.value='';document.formulario.txt_buscar_articulo.focus();mostrar_material_almacen(true);"><i class="fa fa-times"></i></span>
              </span>
              <span class="input-group-btn">
                <button class="btn btn-primary" type="button" id="cmd_buscar_articulos" onclick="javascript: mostrar_material_almacen(true);">Buscar</button>
              </span>
            </div>
        </div>
        <? echo DB_MATERIALES($par_id, $vope)?>

      </div>
    </div>
    <!-- Fin Nuevo Parte 3/7 -->

    <div id="nuevoparte4" style="display:none">
      <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
              <span class="navbar-left"><a href="javascript: habilitar_capa('nuevoparte3');" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
              <span class="navbar-right"><a href="javascript: habilitar_capa('nuevoparte5');" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
              <p class="titulo">Nuevo Parte 4/5</span>
      </nav>

      <h4>Fotos</h4>
      
      <div class="col-xs-12">
        <div class="input-group form-group">
          <span class="input-group-btn">
              <span class="btn btn-primary btn-file">
                Seleccionar&hellip; <input type="file" id="txt_img1" name="txt_img1" accept=".jpg,.jpeg">
              </span>
          </span>
          <input type="text" class="form-control" readonly id="lbl_img1" name="lbl_img1" value="<?php echo $par_img[0];?>">
          <span class="input-group-btn">
            <span class="btn btn-default" onclick="javascript: document.formulario.lbl_img1.value='';document.formulario.txt_img1.value='';$('#img_foto1').attr('src','img/ico_camera.png');"><i class="fa fa-times"></i></span>
          </span>
        </div>
      </div>
      
      <div class="col-xs-12">
        <div class="input-group form-group">
          <span class="input-group-btn">
              <span class="btn btn-primary btn-file">
                Seleccionar&hellip; <input type="file" id="txt_img2" name="txt_img2" accept=".jpg,.jpeg" >
              </span>
          </span>
          <input type="text" class="form-control" readonly id="lbl_img2" name="lbl_img2" value="<?php echo $par_img[1];?>">
          <span class="input-group-btn">
            <span class="btn btn-default" onclick="javascript: document.formulario.lbl_img2.value='';document.formulario.txt_img2.value='';$('#img_foto2').attr('src','img/ico_camera.png');"><i class="fa fa-times"></i></span>
          </span>
        </div>
      </div>
    
      <div class="col-xs-12">
        <div class="input-group form-group">
          <span class="input-group-btn">
              <span class="btn btn-primary btn-file">
                Seleccionar&hellip; <input type="file" id="txt_img3" name="txt_img3" accept=".jpg,.jpeg">
              </span>
          </span>
          <input type="text" class="form-control" readonly id="lbl_img3" name="lbl_img3" value="<?php echo $par_img[2];?>">
          <span class="input-group-btn">
            <span class="btn btn-default" onclick="javascript: document.formulario.lbl_img3.value='';document.formulario.txt_img3.value='';$('#img_foto3').attr('src','img/ico_camera.png');"><i class="fa fa-times"></i></span>
          </span>
        </div>
      </div>

      <div class="col-xs-12">
        <div class="input-group form-group">
          <span class="input-group-btn">
              <span class="btn btn-primary btn-file">
                Seleccionar&hellip; <input type="file" id="txt_img4" name="txt_img4" accept=".jpg,.jpeg">
              </span>
          </span>
          <input type="text" class="form-control" readonly id="lbl_img4" name="lbl_img4" value="<?php echo $par_img[3];?>">
          <span class="input-group-btn">
            <span class="btn btn-default" onclick="javascript: document.formulario.lbl_img4.value='';document.formulario.txt_img4.value='';$('#img_foto4').attr('src','img/ico_camera.png');"><i class="fa fa-times"></i></span>
          </span>
        </div>
      </div>

      
      <h4>Vista previa de Im&aacute;genes</h4>
      <div class="col-xs-3">
        <?php if ($par_img[0] == '') {
          $imagen_preliminar = "../img/ico_camera.png";
        } else {
          $imagen_preliminar = "../partes_imagenes/" .  $par_img[0];
        }?>
        <img src="<?php echo $imagen_preliminar;?>" width="75" class="img_miniatura" id="img_foto1" name="img_foto1"/>
      </div>
      <div class="col-xs-3">
        <?php if ($par_img[1] == '') {
          $imagen_preliminar = "../img/ico_camera.png";
        } else {
          $imagen_preliminar = "../partes_imagenes/" .  $par_img[1];
        }?>
        <img src="<?php echo $imagen_preliminar;?>" width="75" class="img_miniatura" id="img_foto2" name="img_foto2"/>
      </div>
      <div class="col-xs-3">
        <?php if ($par_img[2] == '') {
          $imagen_preliminar = "../img/ico_camera.png";
        } else {
          $imagen_preliminar = "../partes_imagenes/" . $par_img[2];
        }?>
        <img src="<?php echo $imagen_preliminar;?>" width="75" class="img_miniatura" id="img_foto3" name="img_foto3"/>
      </div>
      <div class="col-xs-3">
        <?php if ($par_img[3] == '') {
          $imagen_preliminar = "../img/ico_camera.png";
        } else {
          $imagen_preliminar = "../partes_imagenes/" .  $par_img[3];
        }?>
        <img src="<?php echo $imagen_preliminar;?>" width="75" class="img_miniatura" id="img_foto4" name="img_foto4"/>
      </div>
    
    </div>
    <!-- Fin Nuevo Parte 4/4 -->

    <!-- Inicio Nuevo Parte 5/5 -->
    <div id="nuevoparte5" style="display:none">
      <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
              <span class="navbar-left"><a href="javascript: habilitar_capa('nuevoparte4');" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
              <span class="navbar-right" style="visibility:hidden;"><a href="javascript: habilitar_capa('nuevoparte5');" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
              <p class="titulo">Nuevo Parte 5/5</span>
      </nav>

      <div id="cargando"></div>
      <button type="button" id="btn_guardar" class="btn btn-danger btn-block" onclick="javascript: validar_formulario();">Guardar</button>
    </div>
    <!-- Fin Nuevo Parte 5/5 -->

    <!-- Muestra Id del registro guardado -->
    <div id="nuevoparte_fin"></div>
    <!-- Fin nuevoparte_fin -->

    </form>
  </div> <!-- Fin .container -->
  <script type="text/javascript" src="../bootstrap-3.2.0/js/bootstrap.min.js"></script>
</body>

</html>
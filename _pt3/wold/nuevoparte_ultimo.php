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
            
      $op = $_REQUEST['op'];
      $id = $_REQUEST['id'];
      $par_tip = 1; // Tipo de Parte en PAR_TIP

      if ($op != 'C') {
        $sentencia = DB_CONSULTA("SELECT * FROM PAR WHERE PAR_ID=" . $id);
        if (mysql_num_rows($sentencia) == 1) {
          $row = mysql_fetch_assoc($sentencia);
          $par_id = $row['PAR_ID'];
          $par_fec = date("Y-m-d", strtotime($row['PAR_FEC']));
          $par_ninc = $row['PAR_NINC'];
          $par_fin = $row['PAR_FIN'];
          $par_uot = $row['PAR_UOT'];
          $par_dir = $row['PAR_DIR'];
          $par_pob = $row['PAR_POB'];
          $par_rea = $row['PAR_REA'];
          $par_obs = $row['PAR_OBS'];
          $par_tip_ave = $row['PAR_TIP_AVE'];
          $par_veh = $row['PAR_VEH'];
          $par_km = $row['PAR_KM'];
          $par_km_hor = $row['PAR_KM_HOR'];

          $sentencia_ope = DB_CONSULTA("SELECT * FROM PAR_DET WHERE PD_PAR=" . $id);
          $i = 0;
          $par_ope = array(2);
          
          while ($row = mysql_fetch_assoc($sentencia_ope)) {
            $par_ope[$i] = array(
              'pd_ope' => $row['PD_OPE'],
              'pd_nor' => $row['PD_NOR'],
              'pd_ext' => $row['PD_EXT']
            );
            $i++;
          }


          $sentencia_tra = DB_CONSULTA("SELECT * FROM PAR_TRA WHERE PT_PAR=" . $id);
          $par_tra = array();
          while ($row = mysql_fetch_assoc($sentencia_tra)) {
            $par_tra[] = $row['PT_TRA'];
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
        $par_uot = 1;
        $par_ope = array(2);
        $sentencia = DB_CONSULTA("SELECT USR_ID, USR_COM_HAB, USR_VEH_HAB FROM USR_WEB WHERE USR_ID=" . $_SESSION['GLB_USR_ID']);
        if (mysql_num_rows($sentencia) == 1) {
          $row = mysql_fetch_assoc($sentencia);
          for ($i=0; $i<=1; $i++) {
            if ($i == 0) {
              $par_ope[$i] = array(
                'pd_ope' => $_SESSION['GLB_USR_ID'],
                'pd_nor' => '',
                'pd_ext' => ''
              );
            } else {
              $par_ope[$i] = array(
                'pd_ope' => $row['USR_COM_HAB'],
                'pd_nor' => '',
                'pd_ext' => ''
              );
            }
          }
          $par_veh = $row['USR_VEH_HAB'];
        }
      }

    ?>
    
  <script type="text/javascript">

      var myXhr = $.ajaxSettings.xhr();

      // Muy importante. Necesaria para validar entradas en campos input de tipo numérico
      window.onload = function() {
          validar_campos_input();
          // Cargar Página de Materiales de Operarios
          cargar("nuevoparte_materiales.php?par_id=<?php echo $par_id; ?>&ope1=" + document.getElementById("cmb_ope1").value + "&ope2=" + document.getElementById("cmb_ope2").value + "&ope3=" + document.getElementById("cmb_ope3").value + "&ope4=" + document.getElementById("cmb_ope4").value, "capa_materiales");
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

      function habilita_material_seleccionado(bseleccionado) {
        var balmacen;
        if (bseleccionado) {
          document.getElementById("li_material_seleccionado").className = "active";
          document.getElementById("li_material_todos").className = "";
          document.getElementById("capa_filtros_materiales").style.display = "none";
          mostrar_material_seleccionado();
        } else {
          document.getElementById("li_material_seleccionado").className = "";
          document.getElementById("li_material_todos").className = "active";
          document.getElementById("capa_filtros_materiales").style.display = "";
          //if (document.getElementById("li_material_ultimo_movimiento").className == "") {
            balmacen = true;  
          //} else {
          //  balmacen = false;
          //}
          mostrar_material_almacen(balmacen);
        }
      }

      function habilita_material_almacen(bseleccionado) {
        if (bseleccionado) {
          document.getElementById("li_material_ultimo_movimiento").className = "";
          document.getElementById("li_material_almacen").className = "active";
        } else {
          document.getElementById("li_material_ultimo_movimiento").className = "active";
          document.getElementById("li_material_almacen").className = "";
        }
        mostrar_material_almacen(bseleccionado);
      }


      function mostrar_operario_seleccionado(loperario) {
        var filas;
        var balmacen;
        
        if (loperario == '') {
          loperario = 0;
        }
        // Desabilitado mientras no pongamos el último movimiento
        //if (document.getElementById("li_material_ultimo_movimiento").className == "") {
            balmacen = true;
        //  } else {
        //    balmacen = false;
        //  }
          mostrar_material_almacen(balmacen);
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

        // Operario seleccionado en el desplegable de materiales
        if (document.formulario.cmb_almacen_operarios.value.length == 0) {
          loperario = 0;
        } else {
          loperario = document.getElementById("cmb_almacen_operarios").value  
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


      function mostrar_material_seleccionado() {
        var filas;
        var bseleccionado;
        
        if (document.getElementById("li_material_seleccionado").className == 'active') {
          bseleccionado = true;
        } else {
          bseleccionado = false;
        }
      
        filas = document.getElementsByClassName("material");

        for (var i = 0; i < filas.length; i++) {
          if (bseleccionado) {
            var txt_cant = document.formulario.elements["txt_mat_cantidades[]"];
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

      function validar_formulario(){ 
        var msg;
        msg = ""

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

        if (document.formulario.txt_ninc.value.length == 0) {
          msg += "<p>Es necesario indicar un N&deg; de Incidencia.</p>";
        } else if (isNaN(document.formulario.txt_ninc.value) && !isNaN(document.formulario.txt_ninc.value)) {
          msg += "<p>Es necesario indicar un N&deg; de Incidencia num&eacute;rico.</p>";
        }

        if (document.formulario.txt_fec.value.length==0) {
          msg = msg + "<p>Es necesario indicar una Fecha.</p>";
        } else if (!fecha_valida(document.formulario.txt_fec.value)) {
          msg = msg + "<p>La Fecha indicada no tiene un formato v&aacute;lido. DD/MM/YYYY</p>";
        }

        if (document.formulario.cmb_tip_ave.value.length == 0) {
          msg += "<p>Es necesario indicar un Tipo de Aver&iacute;a.</p>";
        } else if (isNaN(document.formulario.cmb_tip_ave.value.replace(",","."))) {
          msg += "<p>Es necesario indicar un Tipo de Aver&iacute;a v&aacute;lido.</p>";
        }        

        if (document.formulario.cmb_uot.value.length == 0) {
          msg += "<p>Es necesario indicar una UOT.</p>";
        } else if (isNaN(document.formulario.cmb_uot.value.replace(",","."))) {
          msg += "<p>Es necesario indicar una UOT v&aacute;lida.</p>";
        }        

        if (document.formulario.txt_dir.value.length == 0) {
          msg += "<p>Es necesario indicar una Direcci&oacute;n.</p>";
        }

        if (document.formulario.cmb_pob.value.length == 0) {
          msg += "<p>Es necesario indicar una Poblaci&oacute;n.</p>";
        } else if (isNaN(document.formulario.cmb_pob.value.replace(",","."))) {
          msg += "<p>Es necesario indicar una Poblaci&oacute;n v&aacute;lida.</p>";
        } 

        if (document.formulario.cmb_veh.value.length == 0) {
          msg += "<p>Es necesario indicar un Veh&iacute;culo.</p>";
        } else if (isNaN(document.formulario.cmb_veh.value.replace(",","."))) {
          msg += "<p>Es necesario indicar un Veh&iacute;culo v&aacute;lido.</p>";
        }

        if (document.formulario.txt_km.value.length == 0) {
          msg += "<p>Es necesario indicar los Kil&oacute;metros.</p>";
        } else if (isNaN(document.formulario.cmb_uot.value.replace(",","."))) {
          msg += "<p>Es necesario indicar los Kil&oacute;metros v&aacute;lidos.</p>";
        }

        if (document.formulario.txt_km_hor.value.length == 0) {
          msg += "<p>Es necesario indicar el tiempo de Desplazamiento.</p>";
        } else if (isNaN(document.formulario.txt_km_hor.value.replace(",","."))) {
          msg += "<p>Es necesario indicar un tiempo de desplazamiento v&aacute;lido.</p>";
        }


        if (msg != "") {
          //document.getElementById("errores").innerHTML = msg
          document.getElementById("modal_text").innerHTML = msg;
          $("#myModal").modal("show");
          return 0;
        } else {
          //document.getElementById("cargando").style.display = "block";
          //document.formulario.submit();
          // Se cambia por petición Ajax con barra de progreso
          uploadAjax("exe_parte.php");
        }
      } 

      function habilitar_capa(id) {
        document.getElementById('nuevoparte1').style.display = "none";
        document.getElementById('nuevoparte2').style.display = "none";
        document.getElementById('nuevoparte3').style.display = "none";
        document.getElementById('nuevoparte4').style.display = "none";
        document.getElementById('nuevoparte5').style.display = "none";
        document.getElementById('nuevoparte6').style.display = "none";
        document.getElementById('nuevoparte7').style.display = "none";
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

<body class="framework">
	<div class="container">
    <form name="formulario" id="formulario" class="form-horizontal" role="form" method="POST" action="exe_parte2.php" enctype="multipart/form-data" onsubmit="return false;">
    <input type="hidden" name="op" value="<?php echo $op;?>" />
    <input type="hidden" name="id" value="<?php echo $id;?>" />
    <input type="hidden" name="par_tip" value="<?php echo $par_tip;?>" />
    
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

    <!-- Inicio Nuevo Parte 1/7 -->
    <div id="nuevoparte1">
      <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
              <span class="navbar-left"><a href="inicio.php" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
              <span class="navbar-right"><a href="javascript: habilitar_capa('nuevoparte2');" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
              <p class="titulo">Nuevo Parte 1/7</span>
      </nav>
      
      <h4>Datos de Trabajadores</h4>

      <!-- Bloque de Trabajadores -->
      <div class="bloque">
        <div class="form-group bloque-reducido">
          <label for="operario1" class="col-lg-2">Operario1 (Conductor):</label>
          <div class="col-lg-10">
            <?php echo DB_COMBOBOX("LIST_OPERARIOS","Codigo","Nombre","","Nombre","cmb_ope1","cmb_ope1","form-control",$par_ope[0]['pd_ope'],"","","javascript: cargar('nuevoparte_materiales.php?par_id=" . $par_id . "&ope1=' + this.value + '&ope2=' + document.getElementById('cmb_ope2').value + '&ope3=' + document.getElementById('cmb_ope3').value + '&ope4=' + document.getElementById('cmb_ope4').value, 'capa_materiales');"); ?>
          </div>
        </div>
        <div class="form-group bloque-reducido">
          <label for="horas_normales1" class="col-xs-3 col-xs-offset-1">Normales:</label>
          <div class="col-xs-3">
            <input type="number" class="form-control decimal" id="txt_hnor1" name="txt_hnor1" value="<?php echo $par_ope[0]['pd_nor'];?>" />
          </div>
          <label for="horas_extras1" class="col-xs-2">Extras:</label>
          <div class="col-xs-3">
            <input type="number" class="form-control decimal" id="txt_hext1" name="txt_hext1" value="<?php echo $par_ope[0]['pd_ext'];?>"  />
          </div>
        </div>
      </div>

      <div class="bloque">
        <div class="form-group bloque-reducido">
          <label for="operario2" class="col-lg-2">Operario2:</label>
          <div class="col-lg-10">
            <?php echo DB_COMBOBOX("LIST_OPERARIOS","Codigo","Nombre","","Nombre","cmb_ope2","cmb_ope2","form-control",$par_ope[1]['pd_ope'],"","","javascript: cargar('nuevoparte_materiales.php?par_id=" . $par_id . "&ope1=' + document.getElementById('cmb_ope1').value + '&ope2=' + this.value + '&ope3=' + document.getElementById('cmb_ope3').value + '&ope4=' + document.getElementById('cmb_ope4').value, 'capa_materiales');"); ?>
          </div>
        </div>
        <div class="form-group bloque-reducido">
          <label for="horas_normales2" class="col-xs-3 col-xs-offset-1">Normales:</label>
          <div class="col-xs-3">
            <input type="number" class="form-control decimal" id="txt_hnor2" name="txt_hnor2" value="<?php echo $par_ope[1]['pd_nor'];?>" />
          </div>
          <label for="horas_extras2" class="col-xs-2">Extras:</label>
          <div class="col-xs-3">
            <input type="number" class="form-control decimal" id="txt_hext2" name="txt_hext2" value="<?php echo $par_ope[1]['pd_ext'];?>" />
          </div>
        </div>
      </div>

      <div class="bloque">
        <div class="form-group bloque-reducido">
          <label for="operario3" class="col-lg-2">Operario3:</label>
          <div class="col-lg-10">
            <?php echo DB_COMBOBOX("LIST_OPERARIOS","Codigo","Nombre","","Nombre","cmb_ope3","cmb_ope3","form-control",$par_ope[2]['pd_ope'],"","","javascript: cargar('nuevoparte_materiales.php?par_id=" . $par_id . "&ope1=' + document.getElementById('cmb_ope1').value + '&ope2=' + document.getElementById('cmb_ope2').value + '&ope3=' + this.value + '&ope4=' + document.getElementById('cmb_ope4').value, 'capa_materiales');"); ?>
          </div>
        </div>
        <div class="form-group bloque-reducido">
          <label for="horas_normales3" class="col-xs-3 col-xs-offset-1">Normales:</label>
          <div class="col-xs-3">
            <input type="number" class="form-control decimal" id="txt_hnor3" name="txt_hnor3" value="<?php echo $par_ope[2]['pd_nor'];?>" />
          </div>
          <label for="horas_extras3" class="col-xs-2">Extras:</label>
          <div class="col-xs-3">
            <input type="number" class="form-control decimal" id="txt_hext3" name="txt_hext3" value="<?php echo $par_ope[2]['pd_ext'];?>" />
          </div>
        </div>
      </div>

      <div class="bloque">
        <div class="form-group bloque-reducido">
          <label for="operario4" class="col-lg-2">Operario4:</label>
          <div class="col-lg-10">
            <?php echo DB_COMBOBOX("LIST_OPERARIOS","Codigo","Nombre","","Nombre","cmb_ope4","cmb_ope4","form-control",$par_ope[3]['pd_ope'],"","","javascript: cargar('nuevoparte_materiales.php?par_id=" . $par_id . "&ope1=' + document.getElementById('cmb_ope1').value + '&ope2=' + document.getElementById('cmb_ope2').value + '&ope3=' + document.getElementById('cmb_ope3').value + '&ope4=' + this.value, 'capa_materiales');"); ?>
          </div>
        </div>
        <div class="form-group bloque-reducido">
          <label for="horas_normales4" class="col-xs-3 col-xs-offset-1">Normales:</label>
          <div class="col-xs-3">
            <input type="number" class="form-control decimal" id="txt_hnor4" name="txt_hnor4" value="<?php echo $par_ope[3]['pd_nor'];?>" />
          </div>
          <label for="horas_extras4" class="col-xs-2">Extras:</label>
          <div class="col-xs-3">
            <input type="number" class="form-control decimal" id="txt_hext4" name="txt_hext4" value="<?php echo $par_ope[3]['pd_ext'];?>" />
          </div>
        </div>
      </div>
    </div>    
    <!-- Fin Nuevo Parte 1/7 -->

    <!-- Nuevo Parte 2/7 -->
    <div id="nuevoparte2" style="display:none">
      <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
              <span class="navbar-left"><a href="javascript: habilitar_capa('nuevoparte1');" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
              <span class="navbar-right"><a href="javascript: habilitar_capa('nuevoparte3');" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
              <p class="titulo">Nuevo Parte 2/7</span>
      </nav>

      <h4>Datos de la Aver&iacute;a</h4>
      <div class="bloque">
        <div class="form-group">
          <label for="num_incidencia" class="col-lg-2">N&deg; de Incidencia:</label>
          <div class="col-lg-10">
            <input type="number" class="form-control entero" name="txt_ninc" value="<?php echo $par_ninc;?>"></input>
          </div>
        </div>

        <div class="form-group">
          <label for="fecha" class="col-lg-2">Fecha:</label>
          <div class="col-lg-10">
            <input type="date" class="form-control" name="txt_fec" value="<?php echo $par_fec;?>"></input>
        
          </div>
        </div>
        
        <div class="form-group">
          <label for="tipo_averia" class="col-lg-2">Tipo de Aver&iacute;a:</label>
          <div class="col-lg-10">
            <?php //echo DB_COMBOBOX("LIST_TIPOS_AVERIAS","Codigo","Nombre","","Nombre","cmb_tip_ave","cmb_tip_ave","form-control",$par_tip_ave,"","","javascript: cargar('nuevoparte_materiales.php?par_id=" . $id . "&par_tip_ave=' + this.value, 'capa_materiales');");
                echo DB_COMBOBOX("LIST_TIPOS_AVERIAS","Codigo","Nombre","","Nombre","cmb_tip_ave","cmb_tip_ave","form-control",$par_tip_ave,"","",""); ?>
          </div>
        </div>
      
        <label class="radio-inline">
          <?php if ($par_fin == 1) {$checked="checked";} else {$checked="";}?>
          <input type="radio" name="chk_fin" value="1" <?php echo $checked;?>> Finalizado
        </label>
        <label class="radio-inline">
          <?php if ($par_fin == 0) {$checked="checked";} else {$checked="";}?>
          <input type="radio" name="chk_fin" value="0" <?php echo $checked;?>> Pendiente
        </label>
    
      </div>

      <h4>Datos de Localizaci&oacute;n</h4>
      
      <div class="form-group">
        <label for="uot" class="col-lg-2">UOT:</label>
        <div class="col-lg-10">
          <?php echo DB_COMBOBOX("LIST_UOT","Codigo","Nombre","","Nombre","cmb_uot","cmb_uot","form-control",$par_uot,"","","javascript: cargar('combo_poblacion.php?id_uot=' + this.value ,'combo_poblacion');"); ?>
        </div>
      </div>    
      
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
          <?php echo DB_COMBOBOX("LIST_POBLACIONES","Codigo","Nombre","UOT=" . $par_uot,"Nombre","cmb_pob","cmb_pob","form-control",$par_pob,"","",""); ?>
        </div>
      </div>

      <div class="form-group bloque-reducido" style="display:none;">
        <label for="latitud" class="col-lg-2">Latitud:</label>
        <div class="col-lg-10">
          <input type="text" class="form-control" name="latitud" />
        </div>
      </div>
      <div class="form-group bloque-reducido" style="display:none;">
        <label for="longitud" class="col-lg-2">Longitud:</label>
        <div class="col-lg-10">
          <input type="text" class="form-control" name="longitud" />
        </div>
      </div>
    </div>
    <!-- Fin Nuevo Parte2/7 -->

    <!-- Nuevo Parte 3/7 -->
    <div id="nuevoparte3" style="display:none">
      <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
              <span class="navbar-left"><a href="javascript: habilitar_capa('nuevoparte2');" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
              <span class="navbar-right"><a href="javascript: habilitar_capa('nuevoparte4');" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
              <p class="titulo">Nuevo Parte 3/7</span>
      </nav>

      <h4>Trabajo Realizado</h4>
      <?php echo DB_LIST_CHECK("LIST_TRABAJOS_AVERIAS","Codigo","Nombre","","Nombre","chk_tip_tra[]","", $par_tra);?>
    </div>      
    <!-- Fin Nuevo Parte 3/7 -->

    <!-- Inicio Nuevo Parte 4/7 -->
    <div id="nuevoparte4" style="display:none">
      <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
              <span class="navbar-left"><a href="javascript: habilitar_capa('nuevoparte3');" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
              <span class="navbar-right"><a href="javascript: habilitar_capa('nuevoparte5');" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
              <p class="titulo">Nuevo Parte 4/7</span>
      </nav>

      <div id="capa_materiales"></div>
    </div>
    <!-- Fin Nuevo Parte 4/7 -->

    <!-- Nuevo Parte 5/7 -->
    <div id="nuevoparte5" style="display:none">
      <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
              <span class="navbar-left"><a href="javascript: habilitar_capa('nuevoparte4');" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
              <span class="navbar-right"><a href="javascript: habilitar_capa('nuevoparte6');" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
              <p class="titulo">Nuevo Parte 5/7</span>
      </nav>

      <h4>Descripci&oacute;n del Trabajo</h4>
      
      <div class="form-group bloque-reducido">
        <label for="vehiculo" class="col-lg-2">Veh&iacute;culo:</label>
        <div class="col-lg-10">
          <?php echo DB_COMBOBOX("LIST_VEHICULOS","Codigo","Nombre","","Nombre","cmb_veh","cmb_veh","form-control",$par_veh,"","",""); ?>
        </div>
      </div>
      <div class="form-group bloque_reducido">
        <label for="km" class="col-xs-2 col-xs-offset-1">KM:</label>
        <div class="col-xs-3">
          <input type="number" class="form-control decimal" name="txt_km" value="<?php echo $par_km;?>" />
        </div>
        <label for="desplazamiento" class="col-xs-2">Horas:</label>
          <div class="col-xs-3">
            <input type="number" class="form-control decimal" id="txt_km_hor" name="txt_km_hor" value="<?php echo $par_km_hor;?>"  />
          </div>
      </div>
      
      <div class="form-group">
        <label for="realizado" class="col-lg-2">Realizado:</label>
        <div class="col-lg-10">
          <textarea class="form-control" rows="4" name="txt_rea"><?php echo $par_rea;?></textarea>
        </div>
      </div>

      <div class="form-group">
        <label for="observaciones" class="col-lg-2">Observaciones:</label>
        <div class="col-lg-10">
          <textarea class="form-control" rows="4" name="txt_obs"><?php echo $par_obs;?></textarea>
        </div>
      </div>
    </div>
    <!-- Fin Nuevo Parte 5/7 -->

    <!-- Inicio Nuevo Parte 6/7 -->
    <div id="nuevoparte6" style="display:none">
      <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
              <span class="navbar-left"><a href="javascript: habilitar_capa('nuevoparte5');" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
              <span class="navbar-right"><a href="javascript: habilitar_capa('nuevoparte7');" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
              <p class="titulo">Nuevo Parte 6/7</span>
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
          $imagen_preliminar = "img/ico_camera.png";
        } else {
          $imagen_preliminar = "partes_imagenes/" .  $par_img[0];
        }?>
        <img src="<?php echo $imagen_preliminar;?>" width="75" class="img_miniatura" id="img_foto1" name="img_foto1"/>
      </div>
      <div class="col-xs-3">
        <?php if ($par_img[1] == '') {
          $imagen_preliminar = "img/ico_camera.png";
        } else {
          $imagen_preliminar = "partes_imagenes/" .  $par_img[1];
        }?>
        <img src="<?php echo $imagen_preliminar;?>" width="75" class="img_miniatura" id="img_foto2" name="img_foto2"/>
      </div>
      <div class="col-xs-3">
        <?php if ($par_img[2] == '') {
          $imagen_preliminar = "img/ico_camera.png";
        } else {
          $imagen_preliminar = "partes_imagenes/" . $par_img[2];
        }?>
        <img src="<?php echo $imagen_preliminar;?>" width="75" class="img_miniatura" id="img_foto3" name="img_foto3"/>
      </div>
      <div class="col-xs-3">
        <?php if ($par_img[3] == '') {
          $imagen_preliminar = "img/ico_camera.png";
        } else {
          $imagen_preliminar = "partes_imagenes/" .  $par_img[3];
        }?>
        <img src="<?php echo $imagen_preliminar;?>" width="75" class="img_miniatura" id="img_foto4" name="img_foto4"/>
      </div>
    
    </div>
    <!-- Fin Nuevo Parte 6/7 -->

    <!-- Inicio Nuevo Parte 7/7 -->
    <div id="nuevoparte7" style="display:none">
      <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
              <span class="navbar-left"><a href="javascript: habilitar_capa('nuevoparte6');" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
              <span class="navbar-right" style="visibility:hidden;"><a href="javascript: habilitar_capa('nuevoparte6');" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
              <p class="titulo">Nuevo Parte 7/7</span>
      </nav>

      <div id="cargando"></div>
      <button type="button" id="btn_guardar" class="btn btn-danger btn-block" onclick="javascript: validar_formulario();">Guardar</button>
    </div>
    <!-- Fin Nuevo Parte 7/7 -->

    <!-- Muestra Id del registro guardado -->
    <div id="nuevoparte_fin"></div>
    <!-- Fin nuevoparte_fin -->

    </form>
  
  </div> <!-- Fin .container -->
  <script type="text/javascript" src="bootstrap-3.2.0/js/bootstrap.min.js"></script>
</body>

</html>
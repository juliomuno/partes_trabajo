<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1">
    
    <script type="text/javascript" src="../js/jquery-1.11.0.min.js"></script>
    <script type="text/javascript" src="../js/ajax.js"></script>
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
      //setlocale(LC_ALL,”es_ES”);
      include "../comun/funciones.php";
      include "../comun/db_con.php";
      
      session_start();
      
      if (!isset($_SESSION['GLB_USR_ID'])) {
        CERRAR_SESION();
      }

      $op = $_REQUEST['op'];
      $id = $_REQUEST['id'];
      $num_trabajadores = 30;

      if ($op == "C") {
        $par_pre = $_REQUEST['pre_id'];
        $par_tip = 3; // Parte de Planificación
        $par_fec = str_html_fecha(getdate());
        $totales = 20;
        $pee_id = DB_LEE_CAMPO("LIST_PLANIFICACION", "PEE_ID", "Presupuesto=" . $par_pre . " AND Operario=" . $_SESSION['GLB_USR_ID'] . " AND Fecha=CURDATE()");
        
        $sentencia_ope = DB_CONSULTA("SELECT Operario, Horas FROM LIST_PLANIFICACION WHERE PEE_ID=" . $pee_id);
        $i = 0;
        $par_ope = array($num_trabajadores);
          
        while ($row = mysql_fetch_assoc($sentencia_ope)) {
            $par_ope[$i] = array(
                'pd_ope' => $row['Operario'],
                'pd_nor' => $row['Horas'],
                'pd_ext' => ''
            );
            $i++;
        }

        $sentencia = DB_CONSULTA("SELECT USR_VEH_HAB FROM USR_WEB WHERE USR_ID=" . $_SESSION['GLB_USR_ID']);
        if (mysql_num_rows($sentencia) == 1) {
          $row = mysql_fetch_assoc($sentencia);
          $par_veh = $row['USR_VEH_HAB'];
        }

        $sentencia = DB_CONSULTA("SELECT PRE_ID, Descripción, Cliente, Cliente_Nombre, Dirección, Población, Población_Nombre FROM LIST_PRESUPUESTOS WHERE PRE_ID=" . $par_pre);
        
        if (mysql_num_rows($sentencia) == 1) {
          $row = mysql_fetch_assoc($sentencia);
          $pre_des = $row['Descripción'];
          $cli_id = $row['Cliente'];
          $cli_nom = $row['Cliente_Nombre'];
          $par_dir = $row['Dirección'];
          $par_pob = $row['Población'];
          $pob_nom = $row['Población_Nombre'];
        }

      } else {
        $sentencia = DB_CONSULTA("SELECT * FROM PAR WHERE PAR_ID=" . $id);
        if (mysql_num_rows($sentencia) == 1) {
          $row = mysql_fetch_assoc($sentencia);
          $par_id = $row['PAR_ID'];
          $par_pre = $row['PAR_PRE'];
          $par_fec = date("Y-m-d", strtotime($row['PAR_FEC']));
          $pee_id = DB_LEE_CAMPO("PLA_ENC_ENC", "PEE_ID", "PEE_PAR=" . $id);
          //$par_dir = $row['PAR_DIR'];
          //$par_pob = $row['PAR_POB'];
          $par_rea = $row['PAR_REA'];
          $par_obs = $row['PAR_OBS'];
          $par_veh = $row['PAR_VEH'];
          $par_km = $row['PAR_KM'];
          $par_km_hor = STR_hora($row['PAR_KM_HOR']);

          $sentencia = DB_CONSULTA("SELECT PRE_ID, Descripción, Cliente, Cliente_Nombre, Dirección, Población, Población_Nombre FROM LIST_PRESUPUESTOS WHERE PRE_ID=" . $par_pre);
          if (mysql_num_rows($sentencia) == 1) {
            $row = mysql_fetch_assoc($sentencia);
            $pre_des = $row['Descripción'];
            $par_cli = $row['Cliente'];
            $cli_nom = $row['Cliente_Nombre'];
            $par_dir = $row['Dirección'];
            $par_pob = $row['Población'];
            $pob_nom = $row['Población_Nombre'];
          }

          $sentencia_ope = DB_CONSULTA("SELECT * FROM PAR_DET WHERE PD_PAR=" . $id);
          $i = 0;
          $par_ope = array($num_trabajadores);
          
          while ($row = mysql_fetch_assoc($sentencia_ope)) {
            $par_ope[$i] = array(
              'pd_ope' => $row['PD_OPE'],
              'pd_nor' => $row['PD_NOR'],
              'pd_ext' => $row['PD_EXT']
            );
            $i++;
          }
                    
          $sentencia_img = DB_CONSULTA("SELECT * FROM PAR_IMG WHERE PI_PAR=" . $id);
          $par_img = array();
          while ($row = mysql_fetch_assoc($sentencia_img)) {
            $par_img[] = $row['PI_IMG'];
          }
        }
      }
      
      // Necesario para seleccionar el encargado por defecto al crear. Al modificar no seleccinar nada para mostrar todo lo ejecutado
      $par_enc = ($op == "C") ? DB_LEE_CAMPO("LIST_PLANIFICACION", "Encargado", "PEE_ID=" . $pee_id) : 0;
    ?>    

    <script type="text/javascript">

      var myXhr = $.ajaxSettings.xhr(); // definimos global para poder cancelar el progreso

      // Muy importante. Necesaria para validar entradas en campos input de tipo numérico
      window.onload = function() {
          validar_campos_input();

          // Cargar Página de Materiales de Operarios
          cargar("nuevoparte_materiales.php?par_id=<?php echo $par_id; ?>&ope1=" + document.getElementById("cmb_ope1").value + "&ope2=" + document.getElementById("cmb_ope2").value + "&ope3=" + document.getElementById("cmb_ope3").value + "&ope4=" + document.getElementById("cmb_ope4").value + "&ope5=" + document.getElementById("cmb_ope5").value + "&ope6=" + document.getElementById("cmb_ope6").value + "&ope7=" + document.getElementById("cmb_ope7").value + "&ope8=" + document.getElementById("cmb_ope8").value, "capa_materiales");
          
          // Indicar Encargado de la planificación
          document.getElementById("cmb_enc_pre").value = <?php echo $par_enc ?>;
          mostrar_partidas_encargados(<?php echo $par_enc?>);

          marcar_conductores();
      }
      
      
      function habilita_planificado(bplanificado) {
        var lencargado;
        
        lencargado = document.getElementById("cmb_enc_pre").value;
        
        if (bplanificado) {
          document.getElementById("li_planificado").className = "active";
          document.getElementById("li_presupuestado").className = "";
          mostrar_partidas_encargados(lencargado);
        } else {
          document.getElementById("li_planificado").className = "";
          document.getElementById("li_presupuestado").className = "active";
          mostrar_partidas_encargados(lencargado);
        }
      }

       function marcar_conductores() {
          var ope_sel="";
          for(var i = 1; i < 9; i++) {
            ope_sel=document.getElementById("cmb_ope" + i)[document.getElementById("cmb_ope" + i).selectedIndex].innerHTML;
            document.getElementById("txt_ope_sel" + i).value=ope_sel;
          }
        }
      function mostrar_partidas_encargados(lencargado) {
        var filas;
        var bplanificacion;
        var splanificado;
      
        if (document.getElementById("li_planificado").className == 'active') {
          bplanificacion = true;
          splanificado = " planificado";
        } else {
          bplanificacion = false;
          splanificado = "";
        }
      
        filas = document.getElementsByClassName("encargado");

        for (var i = 0; i < filas.length; i++) {
          if (bplanificacion) {
            if (lencargado == '') {
              if (filas[i].classList.contains('planificado')) {
                filas[i].style.display = "";
              } else {
                filas[i].style.display = "none";
              }
            } else {
              //if (filas[i].classList.contains("planificado") && filas[i].classList.contains(lencargado)) {
              // Con tal de que esté planificado ya se muestra para no liar demasiado
              if (filas[i].classList.contains("planificado")) {
                filas[i].style.display = "";
              } else {
                filas[i].style.display = "none";
              }
            }
          } else {
            if (lencargado == '') {
              filas[i].style.display = "";
            } else {
              if (filas[i].classList.contains(lencargado)) { 
                filas[i].style.display = "";
              } else {
                filas[i].style.display = "none";
              }
            }
          }
        }
      }


      // Funciones necesarias para la capa de Materiales

      function valida_enter(e) {
        var tecla;
      
        tecla=(document.all) ? e.keyCode : e.which; 
        if (tecla == 13) {
          mostrar_material_almacen(true);
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
      // Fin Funciones necesarias capa Materiales


      function validar_formulario(){ 
        var msg;
        
        msg = "";
        
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

        if (document.formulario.txt_fec.value.length==0) {
          msg = msg + "<p>Es necesario indicar una Fecha.</p>";
        } else if (!fecha_valida(document.formulario.txt_fec.value)) {
          msg = msg + "<p>La Fecha indicada no tiene un formato v&aacute;lido. DD/MM/YYYY</p>";
        }

        if (document.formulario.cmb_veh.value.length == 0) {
          msg += "<p>Es necesario indicar un Veh&iacute;culo.</p>";
        } else if (isNaN(document.formulario.cmb_veh.value.replace(",","."))) {
          msg += "<p>Es necesario indicar un Veh&iacute;culo v&aacute;lido.</p>";
        }

        if (document.formulario.txt_km.value.length == 0) {
          msg += "<p>Es necesario indicar los Kil&oacute;metros.</p>";
        } else if (isNaN(document.formulario.txt_km.value.replace(",","."))) {
          msg += "<p>Es necesario indicar los Kil&oacute;metros v&aacute;lidos.</p>";
        }

        if (document.formulario.txt_km_hor.value.length == 0) {
          msg += "<p>Es necesario indicar el tiempo de Desplazamiento.</p>";
        }

        if (document.formulario.txt_img1.length == 0) {
          msg += "<p>Es necesario adjuntar, al menos, una foto del trabajo.</p>";
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
          uploadAjax("exe_parte_planificacion.php");
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

      $('#txt_img5').change(function(){
        mostrar_miniatura(this, 'img_foto5');
      });

      $('#txt_img6').change(function(){
        mostrar_miniatura(this, 'img_foto6');
      });

      $('#txt_img7').change(function(){
        mostrar_miniatura(this, 'img_foto7');
      });

      $('#txt_img8').change(function(){
        mostrar_miniatura(this, 'img_foto8');
      });

      $('#txt_img9').change(function(){
        mostrar_miniatura(this, 'img_foto9');
      });

      $('#txt_img10').change(function(){
        mostrar_miniatura(this, 'img_foto10');
      });
      
    });
    
    </script>

</head>
<body class="framework">
    <div class="container">
    <form name="formulario" id="formulario" class="form-horizontal" role="form" method="POST" action="exe_parte_planificacion.php" enctype="multipart/form-data" onsubmit="return false;">
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
    <div id="nuevoparte1" style="margin: 0 15px;">
      <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
              <span class="navbar-left"><a href="javascript: window.history.back();" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
              <span class="navbar-right"><a href="javascript: habilitar_capa('nuevoparte2');" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
              <p class="titulo">Nuevo Parte 1/7</span>
      </nav>
      
      <h4>Datos del Trabajo</h4>

      <div class="form-group">
        <label>Presupuesto:</label>
        <input type="text" class="form-control" id="txt_pre_id" name="txt_pre_id" readonly value="<?php echo $par_pre;?>" />
      </div>
      
      <div class="form-group">
        <label>Descripci&oacute;n:</label>
        <textarea class="form-control" rows="4" id="txt_pre_des" name="txt_pre_des"><?php echo $pre_des;?></textarea>
      </div>

      <input type="hidden" name="txt_cli" value="<?php echo $par_cli;?>" />
      <input type="hidden" name="txt_fec" value="<?php echo $par_fec;?>"></input>
      
      <div class="form-group">
        <label>Cliente:</label>
        <input type="text" class="form-control" id="txt_cli_nom" name="txt_cli_nom" readonly value="<?php echo $cli_nom;?>" />
      </div>      

      <h4>Datos de Localizaci&oacute;n</h4>
      <div class="form-group">
        <label>Direcci&oacute;n:</label>
        <input type="text" class="form-control" id="txt_dir" name="txt_dir" readonly value="<?php echo $par_dir;?>" />
      </div>

      <input type="hidden" name="txt_pob" value="<?php echo $par_pob;?>" />
      <div class="form-group">
        <label>Poblaci&oacute;n:</label>
        <input type="text" class="form-control" id="txt_pob_nom" name="txt_pob_nom" readonly value="<?php echo $pob_nom;?>" />
      </div>
    
    </div>
    <!-- Fin Nuevo Parte 1/7 -->

    
    <!-- Inicio Nuevo Parte 2/7 -->
    <div id="nuevoparte2" style="display:none;">
      <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
        <span class="navbar-left"><a href="javascript: habilitar_capa('nuevoparte1');" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
        <span class="navbar-right"><a href="javascript: habilitar_capa('nuevoparte3');" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
        <p class="titulo">Nuevo Parte 2/7</span>
      </nav>
      
      <h4>Datos de Trabajadores</h4>
      <!-- Bloque de Trabajadores -->
      <?php for($i=1;$i<=$num_trabajadores;$i++) { ?>
      <div class="bloque">
        <div class="form-group bloque-reducido">
          <label for="operario<?php echo $i?>" class="col-lg-2">Operario<?php echo $i?>:</label>
          <div class="col-lg-10">
            <?php 
              $cadena_onchange = "javascript: marcar_conductores(); cargar('nuevoparte_materiales.php?par_id=" . $par_id . "'";
              for ($j=1;$j<=$num_trabajadores;$j++) {
                if ($j == $i) {
                  $cadena_onchange .= "+'&ope" . $j . "=' + this.value";
                } else {
                  $cadena_onchange .= "+'&ope" . $j . "=' + document.getElementById('cmb_ope" . $j . "').value";
                }
              }

              $cadena_onchange .= ",'capa_materiales');";
              echo DB_COMBOBOX("LIST_OPERARIOS","Codigo","Nombre","","Nombre","cmb_ope" . $i,"cmb_ope" . $i,"form-control",$par_ope[($i-1)]['pd_ope'],"","",$cadena_onchange);
              echo '<input type="hidden" name="txt_ope_sel' . $i . '" id="txt_ope_sel' . $i . '">';
               ?>
          </div>
        </div>
        <div class="form-group bloque-reducido">
          <label for="horas_normales<?php echo $i?>" class="col-xs-3 col-xs-offset-1">Normales:</label>
          <div class="col-xs-3">
            <input type="number" class="form-control decimal" id="txt_hnor<?php echo $i?>" name="txt_hnor<?php echo $i?>" value="<?php echo $par_ope[$i-1]['pd_nor'];?>" />
          </div>
          <label for="horas_extras<?php echo $i ?>" class="col-xs-2">Extras:</label>
          <div class="col-xs-3">
            <input type="number" class="form-control decimal" id="txt_hext<?php echo $i ?>" name="txt_hext<?php echo $i ?>" value="<?php echo $par_ope[$i-1]['pd_ext'];?>" />
          </div>
        </div>
      </div>
      <?php } ?>


    </div>    
    <!-- Fin Nuevo Parte 2/7 -->
    
    <!-- Inicio Nuevo Parte 3/7 -->
    <div id="nuevoparte3" style="display:none">
      <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
        <span class="navbar-left"><a href="javascript: habilitar_capa('nuevoparte2');" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
        <span class="navbar-right"><a href="javascript: habilitar_capa('nuevoparte4');" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
        <p class="titulo">Nuevo Parte 3/7</span>
      </nav>

      <ul class="nav nav-pills" style="margin-bottom:.5em;">
        <?php $etiqueta_boton = ($op == "C") ? "Planificado" : "Ejecutado"; ?>
        <li id="li_planificado" class="active"><a href="#" onclick="javascript:habilita_planificado(true);"><?php echo $etiqueta_boton;?></a></li>
        <li id="li_presupuestado"><a href="#" onclick="javascript: habilita_planificado(false);">Presupuestado</a></li>
      </ul>
    
      <div class="form-group bloque-reducido">
        <label for="encargado" class="col-xs-3">Encargado:</label>
        <div class="col-xs-9">
          <?php echo DB_COMBOBOX("LIST_PRESUPUESTOS_ENCARGADOS","Código","Nombre","PRE_ID=" . $par_pre,"Nombre","cmb_enc_pre","cmb_enc_pre","form-control","","","","javascript:mostrar_partidas_encargados(this.value);"); ?>
        </div>
      </div>
      <?php echo DB_PARTE_DETALLE($id, $pee_id); ?>
    </div>
    <!-- Fin Nuevo Parte 3/7 -->    


    <!-- Nuevo Parte 4/7 -->
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
        <label for="km" class="col-xs-2">KM:</label>
        <div class="col-xs-4">
          <input type="number" class="form-control decimal" name="txt_km" value="<?php echo $par_km;?>" />
        </div>
        
        <label for="desplazamiento" class="col-xs-2">Horas:</label>
        <div class="col-xs-4">
          <input type="time" class="form-control hora" id="txt_km_hor" name="txt_km_hor" value="<?php echo STR_hora($par_km_hor);?>"  />
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

      <div class="col-xs-12">
        <div class="input-group form-group">
          <span class="input-group-btn">
              <span class="btn btn-primary btn-file">
                Seleccionar&hellip; <input type="file" id="txt_img5" name="txt_img5" accept=".jpg,.jpeg">
              </span>
          </span>
          <input type="text" class="form-control" readonly id="lbl_img5" name="lbl_img5" value="<?php echo $par_img[4];?>">
          <span class="input-group-btn">
            <span class="btn btn-default" onclick="javascript: document.formulario.lbl_img5.value='';document.formulario.txt_img5.value='';$('#img_foto5').attr('src','img/ico_camera.png');"><i class="fa fa-times"></i></span>
          </span>
        </div>
      </div>

      <div class="col-xs-12">
        <div class="input-group form-group">
          <span class="input-group-btn">
              <span class="btn btn-primary btn-file">
                Seleccionar&hellip; <input type="file" id="txt_img6" name="txt_img6" accept=".jpg,.jpeg">
              </span>
          </span>
          <input type="text" class="form-control" readonly id="lbl_img6" name="lbl_img6" value="<?php echo $par_img[5];?>">
          <span class="input-group-btn">
            <span class="btn btn-default" onclick="javascript: document.formulario.lbl_img6.value='';document.formulario.txt_img6.value='';$('#img_foto6').attr('src','img/ico_camera.png');"><i class="fa fa-times"></i></span>
          </span>
        </div>
      </div>

      <div class="col-xs-12">
        <div class="input-group form-group">
          <span class="input-group-btn">
              <span class="btn btn-primary btn-file">
                Seleccionar&hellip; <input type="file" id="txt_img7" name="txt_img7" accept=".jpg,.jpeg">
              </span>
          </span>
          <input type="text" class="form-control" readonly id="lbl_img7" name="lbl_img7" value="<?php echo $par_img[6];?>">
          <span class="input-group-btn">
            <span class="btn btn-default" onclick="javascript: document.formulario.lbl_img7.value='';document.formulario.txt_img7.value='';$('#img_foto7').attr('src','img/ico_camera.png');"><i class="fa fa-times"></i></span>
          </span>
        </div>
      </div>

      <div class="col-xs-12">
        <div class="input-group form-group">
          <span class="input-group-btn">
              <span class="btn btn-primary btn-file">
                Seleccionar&hellip; <input type="file" id="txt_img8" name="txt_img8" accept=".jpg,.jpeg">
              </span>
          </span>
          <input type="text" class="form-control" readonly id="lbl_img8" name="lbl_img8" value="<?php echo $par_img[7];?>">
          <span class="input-group-btn">
            <span class="btn btn-default" onclick="javascript: document.formulario.lbl_img8.value='';document.formulario.txt_img8.value='';$('#img_foto8').attr('src','img/ico_camera.png');"><i class="fa fa-times"></i></span>
          </span>
        </div>
      </div>

      <div class="col-xs-12">
        <div class="input-group form-group">
          <span class="input-group-btn">
              <span class="btn btn-primary btn-file">
                Seleccionar&hellip; <input type="file" id="txt_img9" name="txt_img9" accept=".jpg,.jpeg">
              </span>
          </span>
          <input type="text" class="form-control" readonly id="lbl_img9" name="lbl_img9" value="<?php echo $par_img[8];?>">
          <span class="input-group-btn">
            <span class="btn btn-default" onclick="javascript: document.formulario.lbl_img9.value='';document.formulario.txt_img9.value='';$('#img_foto9').attr('src','img/ico_camera.png');"><i class="fa fa-times"></i></span>
          </span>
        </div>
      </div>
      <div class="col-xs-12">
        <div class="input-group form-group">
          <span class="input-group-btn">
              <span class="btn btn-primary btn-file">
                Seleccionar&hellip; <input type="file" id="txt_img10" name="txt_img10" accept=".jpg,.jpeg">
              </span>
          </span>
          <input type="text" class="form-control" readonly id="lbl_img10" name="lbl_img10" value="<?php echo $par_img[9];?>">
          <span class="input-group-btn">
            <span class="btn btn-default" onclick="javascript: document.formulario.lbl_img10.value='';document.formulario.txt_img10.value='';$('#img_foto10').attr('src','img/ico_camera.png');"><i class="fa fa-times"></i></span>
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
      <div class="col-xs-3">
        <?php if ($par_img[4] == '') {
          $imagen_preliminar = "../img/ico_camera.png";
        } else {
          $imagen_preliminar = "../partes_imagenes/" .  $par_img[4];
        }?>
        <img src="<?php echo $imagen_preliminar;?>" width="75" class="img_miniatura" id="img_foto5" name="img_foto5"/>
      </div>
      <div class="col-xs-3">
        <?php if ($par_img[5] == '') {
          $imagen_preliminar = "../img/ico_camera.png";
        } else {
          $imagen_preliminar = "../partes_imagenes/" .  $par_img[5];
        }?>
        <img src="<?php echo $imagen_preliminar;?>" width="75" class="img_miniatura" id="img_foto6" name="img_foto6"/>
      </div>
      <div class="col-xs-3">
        <?php if ($par_img[6] == '') {
          $imagen_preliminar = "../img/ico_camera.png";
        } else {
          $imagen_preliminar = "../partes_imagenes/" .  $par_img[6];
        }?>
        <img src="<?php echo $imagen_preliminar;?>" width="75" class="img_miniatura" id="img_foto7" name="img_foto7"/>
      </div>
      <div class="col-xs-3">
        <?php if ($par_img[7] == '') {
          $imagen_preliminar = "../img/ico_camera.png";
        } else {
          $imagen_preliminar = "../partes_imagenes/" .  $par_img[7];
        }?>
        <img src="<?php echo $imagen_preliminar;?>" width="75" class="img_miniatura" id="img_foto8" name="img_foto8"/>
      </div>
      <div class="col-xs-3">
        <?php if ($par_img[8] == '') {
          $imagen_preliminar = "../img/ico_camera.png";
        } else {
          $imagen_preliminar = "../partes_imagenes/" .  $par_img[8];
        }?>
        <img src="<?php echo $imagen_preliminar;?>" width="75" class="img_miniatura" id="img_foto9" name="img_foto9"/>
      </div>
      <div class="col-xs-3">
        <?php if ($par_img[9] == '') {
          $imagen_preliminar = "../img/ico_camera.png";
        } else {
          $imagen_preliminar = "../partes_imagenes/" .  $par_img[9];
        }?>
        <img src="<?php echo $imagen_preliminar;?>" width="75" class="img_miniatura" id="img_foto10" name="img_foto10"/>
      </div>

    </div>
    <!-- Fin Nuevo Parte 6/7 -->

    <!-- Inicio Nuevo Parte 7/7 -->
    <div id="nuevoparte7" style="display:none">
      <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
              <span class="navbar-left"><a href="javascript: habilitar_capa('nuevoparte6');" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
              <span class="navbar-right" style="visibility:hidden;"><a href="javascript: habilitar_capa('nuevoparte7');" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
              <p class="titulo">Nuevo Parte 7/7</span>
      </nav>

      <div id="cargando"></div>
      <button type="button" id="btn_guardar" class="btn btn-danger btn-block" onclick="javascript: validar_formulario();">Guardar</button>
    </div>
    <!-- Fin Nuevo Parte 7/7 -->
    
    <!-- Muestra Id del registro guardado -->
    <div id="nuevoparte_fin"></div>
    <!-- Fin nuevoparte_fin -->

    </div> <!-- Container -->
    <script type="text/javascript" src="../bootstrap-3.2.0/js/bootstrap.min.js"></script>
</body>
</html>
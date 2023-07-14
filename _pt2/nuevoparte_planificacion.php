<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1">
    
    <script type="text/javascript" src="js/jquery-1.11.0.min.js"></script>
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
      //setlocale(LC_ALL,”es_ES”);
      include "comun/funciones.php";
      include "comun/db_con.php";
      
      session_start();
      
      if (!isset($_SESSION['GLB_USR_ID'])) {
        CERRAR_SESION();
      }

      $op = $_REQUEST['op'];
      $id = $_REQUEST['id'];
      $num_trabajadores = 1;
      $img_ctd = 0;

      // capturar el tiempo de desplazamiento
      $par_jor_ult_fec=hora_traslado_inicio($_SESSION['GLB_USR_ID']);
      $par_ini_fec=ultima_hora_inicio_parte($_SESSION['GLB_USR_ID']);
      if ($par_jor_ult_fec!=0){
        $des_hor=calcular_tiempo_trasnc($par_jor_ult_fec,$par_ini_fec);
      }

      if ($op == "C") {
        $par_pre = $_REQUEST['pre_id'];
        $par_tip = 3; // Parte de Planificación
        $par_fec = str_html_fecha(getdate());
        $hor_dif = calcular_tiempo_trasnc_initra($_SESSION['GLB_USR_ID']);
        $par_hini = STR_hora2(ultima_hora_inicio_parte($_SESSION['GLB_USR_ID']));
        //$par_hfin = date("H:i:00");
        $par_hfin = substr(str_replace("'", "", STR_fechor_esc15("FIN_TRA")), 11);
        $totales = 20;

        if ($par_pre==""){
          $fecha_hora_actual = date("Y-m-d H:i:s");
          $fecha_dia_anterior = fecha_dia_anterior(date("Y-m-d"));
          // capturar pee_id del trabajo en curso
          $pee_id = DB_LEE_CAMPO("USR_JOR","UJ_PEE","UJ_USU=" . $_SESSION['GLB_USR_ID'] . " AND UJ_JOR=0 AND UJ_TIP_STOP=1 AND UJ_FEC_INI>=" . STR_formato_cadena($fecha_dia_anterior) . " AND UJ_FEC_INI<=" . STR_formato_cadena($fecha_hora_actual) . " AND UJ_FEC_FIN IS NULL");

          //$par_pre = DB_LEE_CAMPO("LIST_PLANIFICACION", "Presupuesto", "PEE_ID=" . $pee_id . " AND Operario=" . $_SESSION['GLB_USR_ID']);
          $par_pre = DB_LEE_CAMPO("PLA_ENC_ENC", "PEE_PRE", "PEE_ID=" . $pee_id);
        } else {
          $pee_id = DB_LEE_CAMPO("LIST_PLANIFICACION", "PEE_ID", "Presupuesto=" . $par_pre . " AND Operario=" . $_SESSION['GLB_USR_ID'] . " AND Fecha=CURDATE()");
        }
        
        /*
        $sentencia_ope = DB_CONSULTA("SELECT Operario, Horas FROM LIST_PLANIFICACION WHERE PEE_ID=" . $pee_id);
        $i = 0;
        $par_ope = array($num_trabajadores);  
        while ($row = mysql_fetch_assoc($sentencia_ope)) {
            $par_ope[$i] = array(
                'pd_ope' => $row['Operario'],
                //'pd_nor' => $row['Horas'],
                'pd_nor' => $hor_dif,
                'pd_ext' => ''
            );
            $i++;
        }
        */

        $par_ope[0] = array(
          'pd_ope' => $_SESSION['GLB_USR_ID'],
          'pd_nor' => $hor_dif,
          'pd_ext' => ""
        );

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
          $par_fin = $row['PAR_FIN'];
          $par_km = $row['PAR_KM'];
          $par_km_hor = STR_hora($row['PAR_KM_HOR']);
          $par_hini = STR_Hora($row['PAR_HINI']);
          $par_hfin = STR_Hora($row['PAR_HFIN']);

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

          //$sentencia_ope = DB_CONSULTA("SELECT * FROM PAR_DET WHERE PD_PAR=" . $id . " AND PD_OPE=" . $_SESSION['GLB_USR_ID']);
          $sentencia_ope = DB_CONSULTA("SELECT * FROM PAR_DET WHERE PD_PAR=" . $id);
          $i = 0;
          $par_ope = array($num_trabajadores);
          
          while ($row = mysql_fetch_assoc($sentencia_ope)) {
            $par_ope[$i] = array(
              'pd_ope' => $row['PD_OPE'],
              'pd_nor' => $row['PD_NOR'],
              'pd_ext' => $row['PD_EXT']
            );
            $hor_dif=$row['PD_NOR']+$row['PD_EXT'];
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
          //cargar("nuevoparte_materiales.php?par_id=<?php echo $par_id; ?>&ope1=" + document.getElementById("cmb_ope1").value + "&ope2=" + document.getElementById("cmb_ope2").value + "&ope3=" + document.getElementById("cmb_ope3").value + "&ope4=" + document.getElementById("cmb_ope4").value + "&ope5=" + document.getElementById("cmb_ope5").value + "&ope6=" + document.getElementById("cmb_ope6").value + "&ope7=" + document.getElementById("cmb_ope7").value + "&ope8=" + document.getElementById("cmb_ope8").value, "capa_materiales");
          cargar("nuevoparte_materiales.php?par_id=<?php echo $par_id; ?>&ope1=" + document.getElementById("cmb_ope1").value + "&ope2=&ope3=&ope4=&ope5=&ope6=&ope7=&ope8=", "capa_materiales");
          
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
          for(var i = 1; i < 1; i++) {
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


      function registrar_seleccion_opes(cod_ope,checked){
        var str=document.getElementById('txt_pla_ope_sel').value;
        var res=str.replace(cod_ope+",","");
        if (checked==1){
          document.getElementById('txt_pla_ope_sel').value = res + cod_ope + ',';
        } else {
          document.getElementById('txt_pla_ope_sel').value = res;
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
          //document.getElementById("modal_text").innerHTML = msg;
          //$("#myModal").modal("show");
          //return 0;
        }
        document.formulario.txt_hnor1.value=aux.toFixed(2);
        document.formulario.txt_hnor_1.value=document.formulario.txt_hnor1.value;

        //solicitar confirmación de selección de operarios
        var ope_ctd=document.getElementById('txt_pla_ope_sel').value;
        var ope_array=ope_ctd.split(",");
        var msg="";
        <?php
        if ($op == "C") {
          ?>
          if (ope_array.length==1){
            msg="Confirme que quiere FINALIZAR trabajo<b> Solo.</b><br>No tiene seleccionado a ningún operario."
          } else {
            msg="Confirme que quiere FINALIZAR trabajo<br><b>Incluyendo "+(ope_array.length-1)+" operarios.</b>"
          }
          document.getElementById("modal_title_sn").innerHTML = "FINALIZAR TRABAJO";
          document.getElementById("modal_text_sn").innerHTML = msg;
          $("#myModal_sn").modal("show");
          return 0;
        <?php 
        } else {
          ?>
          habilitar_capa('nuevoparte1');
          <?php 
        }
        ?>
      }


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

        //if (document.formulario.cmb_veh.value.length == 0) {
        //  msg += "<p>Es necesario indicar un Veh&iacute;culo.</p>";
        //} else if (isNaN(document.formulario.cmb_veh.value.replace(",","."))) {
        //  msg += "<p>Es necesario indicar un Veh&iacute;culo v&aacute;lido.</p>";
        //}

        /*
        if (document.formulario.txt_img1.length == 0) {
          msg += "<p>Es necesario adjuntar, al menos, una foto del trabajo.</p>";
        }
        */

        // Verificar el nuevo "añadir fotos"
        if (document.formulario.lbl_img_jpg.value.length == 0) {
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
                  
        habilitar_capa("nuevoparte2");
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
    <input type="hidden" name="txt_des_hor" id="txt_des_hor" value="<?php echo $des_hor; ?>">
    <input type="hidden" id="txt_hini" name="txt_hini" value="<?php echo $par_hini;?>" />
    <input type="hidden" id="txt_hfin" name="txt_hfin" value="<?php echo $par_hfin;?>"  />
    
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
            <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="javascript: habilitar_capa('nuevoparte2');">Cerrar</button>
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
            <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="javascript: habilitar_capa('nuevoparte1');">Aceptar</button>
          </div>
        </div>
      </div>
    </div>



    <!-- Inicio Nuevo Parte 2/7 -->
    <div id="nuevoparte2" style="margin: 0 15px;">
      <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
        <span class="navbar-left"><a href="javascript: window.history.back();" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
        <span class="navbar-right"><a href="javascript: horas_normales();" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
        <p class="titulo">Nuevo Parte 1/7</span>
      </nav>
      
      <h4>Datos de Trabajadores</h4>
      <!-- Bloque de Trabajadores -->
      <?php for($i=1;$i<=$num_trabajadores;$i++) { ?>
      <div class="bloque">
        <div class="form-group bloque-reducido">
          <label for="operario<?php echo $i?>" class="col-lg-2">Operario:</label>
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
              //echo DB_COMBOBOX("LIST_OPERARIOS","Codigo","Nombre","","Nombre","cmb_ope" . $i,"cmb_ope" . $i,"form-control",$par_ope[($i-1)]['pd_ope'],"","1",$cadena_onchange);
              //echo '<input type="hidden" name="txt_ope_sel' . $i . '" id="txt_ope_sel' . $i . '" value="' . $par_ope[($i-1)]['pd_ope'] . '">';
              
              echo DB_COMBOBOX("LIST_OPERARIOS","Codigo","Nombre","","Nombre","cmb_ope" . $i,"cmb_ope" . $i,"form-control",$par_ope[$i-1]['pd_ope'],"","1",$cadena_onchange);
              echo '<input type="hidden" name="txt_ope_sel' . $i . '" id="txt_ope_sel' . $i . '" value="' . $par_ope[$i-1]['pd_ope'] . '">';

              $horas_disabled="";
              if ($op == "M"){
                $horas_disabled="disabled='disabled'";
              }
            ?>
          </div>

          <div class="col-xs-6 bloque">
            <label for="horas_normales1" class="">Normales (máx: <?php echo $hor_dif; ?>h):</label>
            <input type="hidden" id="txt_dif_hor" name="txt_dif_hor" value="<?php echo $hor_dif ?>">
            <input type="number" class="form-control decimal" id="txt_hnor<?php echo $i?>" disabled="disabled" name="txt_hnor<?php echo $i?>" value="<?php echo $par_ope[$i-1]['pd_nor'];?>" />
            <input type="hidden" id="txt_hnor_1" name="txt_hnor_1" value="<?php echo $par_ope[$i-1]['pd_nor'];?>" />
          </div>

          <div class="col-xs-6 bloque">
            <label for="horas_extras<?php echo $i ?>" class="">Extras:</label>
              <input type="number" class="form-control decimal" id="txt_hext1" name="txt_hext1" <?php echo $horas_disabled;?> onchange="javascript:horas_normales();" value="<?php echo $par_ope[$i-1]['pd_ext'];?>" />
          </div>
        </div>
        
        

        <?php
        if ($pee_id!="" && $pee_id!=-1){
          ?>
          <div class="col-lg-10">
            <?php 
            if ($op=="C"){
              ?>
            <label for="ope_sel<?php echo $i ?>" class="">Finalizar trabajo a:</label>
            <input type="hidden" name="txt_pla_ope_sel" id="txt_pla_ope_sel" >
            <?php 
              echo DB_LIST_CHECK_PARTES("UJ_PLA_JEF=0 AND UJ_FEC_FIN IS NULL AND UJ_FEC_INI>=CURDATE()-1 AND UJ_PEE=".$pee_id,"","","onchange=javascript:registrar_seleccion_opes(this.value,this.checked);","",$op);
            } else {
              ?>
            <label for="ope_sel<?php echo $i ?>" class="">Miembros del equipo:</label>
            <input type="hidden" name="txt_pla_ope_sel" id="txt_pla_ope_sel" >
              <?php
              echo DB_LIST_CHECK_PARTES("UJ_PLA_JEF=0 AND UJ_PEE=".$pee_id,"disabled","","onchange=javascript:registrar_seleccion_opes(this.value,this.checked);",$op);
            }
            ?>
          </div>
          <?php
          }
          ?>
      </div>
      <?php } ?>


    </div>    
    <!-- Fin Nuevo Parte 2/7 -->

    <!-- Inicio Nuevo Parte 1/7 -->
    <div id="nuevoparte1" style="display:none;">
      <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
              <span class="navbar-left"><a href="javascript: habilitar_capa('nuevoparte2');" class="btn btn-primary"><i class="fa fa-arrow-left"> </i> Ant.</a></span>
              <span class="navbar-right"><a href="javascript: habilitar_capa('nuevoparte3');" class="btn btn-primary"><i class="fa fa-arrow-right"></i> Sig.</a></span>
              <p class="titulo">Nuevo Parte 2/7</span>
      </nav>
      
      <h4>Datos del Trabajo</h4>

      <div class="form-group">
        <label>Presupuesto:</label>
        <input type="text" class="form-control" id="txt_pre_id" name="txt_pre_id" readonly value="<?php echo $par_pre;?>" />
      </div>
      
      <div class="form-group">
        <label class="radio-inline">
          <?php if ($par_fin == 1) {$checked="checked";} else {$checked="";}?>
          <input type="radio" name="chk_fin" value="1" <?php echo $checked;?>> Finalizado (totalmente terminado)
        </label>
        <label class="radio-inline">
          <?php if ($par_fin == 0) {$checked="checked";} else {$checked="";}?>
          <input type="radio" name="chk_fin" value="0" <?php echo $checked;?>> Pendiente
        </label>
      </div>

      <div class="form-group">
        <label>Descripci&oacute;n:</label>
        <textarea class="form-control" rows="4" id="txt_pre_des" name="txt_pre_des"><?php echo $pre_des;?></textarea>
      </div>

      <input type="hidden" name="txt_cli" value="<?php echo $par_cli;?>" />
      <input type="hidden" name="txt_fec" value="<?php echo $par_fec;?>">
      
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
                Selecci&oacute; Múlt.&hellip; <input type="file" id="select_image" name="txt_img[]" value="txt_img[]" accept=".jpg,.jpeg" multiple="multiple">
              </span>
          </span>
          <input type="text" class="form-control" readonly id="lbl_img<?php echo $i;?>" name="lbl_img<?php echo $i;?>" value="<?php echo $par_img[$i-1];?>">
          <span class="input-group-btn">
            <span class="btn btn-default" onclick="javascript: document.formulario.lbl_img<?php echo $i;?>.value='';document.formulario.txt_img<?php echo $i;?>.value='';$('#img_foto<?php echo $i;?>').attr('src','img/ico_camera.png');"><i class="fa fa-times"></i></span>
          </span>
        </div>
      </div>

      <?php
      for($i=1; $i<=$img_ctd; $i++){
        ?>
        <div class="col-xs-12">
        <div class="input-group form-group">
          <span class="input-group-btn">
            <span class="btn btn-primary btn-file">
              Selecci&oacute; Múlt.&hellip; <input type="file" id="select_image" name="txt_img" value="txt_img[]" accept=".jpg,.jpeg" multiple="multiple">
            </span>
          </span>
          <input type="text" class="form-control" readonly id="lbl_img<?php echo $i ?>" name="lbl_img<?php echo $i ?>" value="<?php echo $par_img[$i-1];?>">
          <span class="input-group-btn">
            <span class="btn btn-default" onclick="javascript: document.formulario.lbl_img<?php echo $i ?>.value='';document.formulario.txt_img<?php echo $i ?>.value='';$('#img_foto<?php echo $i ?>').attr('src','img/ico_camera.png');"><i class="fa fa-times"></i></span>
          </span>
        </div>
      </div>
        <?php
      }
      ?>
      

      <h4>Vista previa de Im&aacute;genes</h4>
      <?php
      for($i=1; $i<=$img_ctd; $i++){
        ?>
      <div class="col-xs-3">
        <?php if ($par_img[$i-1] == '') {
          $imagen_preliminar = "img/ico_camera.png";
        } else {
          $imagen_preliminar = "../partes_imagenes/" .  $par_img[$i-1];
        }?>
        <img src="<?php echo $imagen_preliminar;?>" width="75" class="img_miniatura" id="img_foto<?php echo $i ?>" name="img_foto<?php echo $i ?>"/>
      </div>
        <?php
      }
      ?>

      <!-- Establecida la opción de añadir imágen -->
      <?php
        $imagen = DB_CONSULTA("SELECT * FROM PAR_IMG WHERE PI_PAR=" . $id);
        $par_img_jpg = array();
        while ($row = mysql_fetch_assoc($imagen)) {
          $par_img_jpg[] = $row['PI_IMG'];
        }
      ?>

      <!-- Añadir múltiples imágenes -->
      <label for="descripcion" style="margin-top: 6%;">Designación de RP, JT y asignación parejas Buddy Partner / Charla diaria de SYS:</label>

      <div class="col-xs-12 mb-lg-4 mt-lg-4">
        <div class="input-group form-group">
          <span class="input-group-btn">
              <span class="btn btn-primary btn-file">
                <i class="fa fa-picture-o" aria-hidden="true"></i>
                Añadir Fotos <input type="file" id="fil_image" name="archivo[]" value="archivo[]" accept=".jpg" multiple="multiple">
              </span>
          </span>
          <input type="text" class="form-control" readonly id="lbl_img_jpg" name="lbl_img_jpg<?php echo $i;?>" value="<?php echo $par_img_jpg[$i-1];?>">
          <span class="input-group-btn">
            <span class="btn btn-default" onclick="javascript: document.formulario.lbl_img_jpg<?php echo $i;?>.value='';document.formulario.txt_img_jpg<?php echo $i;?>.value='';$('#img_foto<?php echo $i;?>').attr('src','img/ico_camera.png');"><i class="fa fa-times"></i></span>
          </span>
        </div>
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
    <script type="text/javascript" src="bootstrap-3.2.0/js/bootstrap.min.js"></script>
</body>
</html>
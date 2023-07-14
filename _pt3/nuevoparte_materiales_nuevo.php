<?php
  include "comun/funciones.php";
  include "comun/db_con.php";

  $vope[] = array();
  $vope[0] = $_REQUEST['ope1'];
  $vope[1] = $_REQUEST['ope2'];
  $vope[2] = $_REQUEST['ope3'];
  $vope[3] = $_REQUEST['ope4'];
  $par_id = $_REQUEST['par_id'];

  echo "Valor3=" . $_REQUEST['ope3'] . ", Valor4=" . $_REQUEST['ope4'];

  $condicion = "";
  for ($i=0; $i<=3; $i++) {
    if ($vope[$i] == '') {
      $vope[$i] = 0;
    } else {
      $condicion .= " Codigo=" . $vope[$i] . " OR ";
    }
  }
  
  if (strlen($condicion) > 0) {
    $condicion = substr($condicion, 0, strlen($condicion)-4);
  }
  
  echo '<ul class="nav nav-pills" style="margin-bottom:.5em;">';
  echo '<li id="li_material_todos" class="active"><a href="#" onclick="javascript:habilita_material_seleccionado(false);">Todos</a></li>';
  echo '<li id="li_material_seleccionado"><a href="#" onclick="javascript: habilita_material_seleccionado(true);">Seleccionado</a></li>';
  echo '</ul>';
      
  echo '<h4>Materiales</h4>';

  echo '<div class="bloque" id="capa_filtros_materiales">';
    echo '<div class="form-group bloque-reducido">';
      echo '<label for="almacen_operarios" class="col-lg-2">Operario:</label>';
          echo '<div class="col-lg-10">';
            echo DB_COMBOBOX("LIST_OPERARIOS","Codigo","Nombre",$condicion,"Nombre","cmb_almacen_operarios","cmb_almacen_operarios","form-control","","","","javascript: mostrar_operario_seleccionado(this.value);");
          echo '</div>';
    echo '</div>';
    
    echo '<div class="input-group bloque-reducido">';
        echo '<input type="text" class="form-control" name="txt_buscar_articulo" id="txt_buscar_articulo" placeholder="Buscar Art&iacute;culo" onkeypress="valida_enter(event);">';
        echo '<span class="input-group-btn">';
          echo '<span class="btn btn-default" onclick="javascript: document.formulario.txt_buscar_articulo.value=\'\'; document.formulario.txt_buscar_articulo.focus();mostrar_material_almacen(true);"><i class="fa fa-times"></i></span>';
        echo '</span>';
        echo '<span class="input-group-btn">';
          echo '<button class="btn btn-primary" type="button" id="cmd_buscar_articulos" onclick="javascript: mostrar_material_almacen(true);">Buscar</button>';
        echo '</span>';
      echo '</div>';
  echo '</div>';

  echo DB_MATERIALES($par_id, $vope);

?>
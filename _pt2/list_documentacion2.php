
  <?php
      include "comun/funciones.php";
      include "comun/db_con.php";

      $criterio = $_REQUEST['criterio'];

      $sentencia = DB_CONSULTA("SELECT * FROM DOC_PREV WHERE DOC_NOM LIKE '%" . $criterio . "%' OR DOC_NUM LIKE '%" . $criterio . "%' ORDER BY DOC_TIP");
      $categoria = "";
      echo '<div class="panel-group" id="accordion">';

      while ($row = mysql_fetch_assoc($sentencia)) {
        if ($row["DOC_TIP"] != $categoria) {
          if ($categoria != '') {
                    echo '</ul>';
                echo '</div>';
              echo '</div>';
          } 
          
          $categoria = $row["DOC_TIP"];
          
            echo '<div class="panel panel-primary">';
              echo '<div class="panel-heading">';
                echo '<h5 class="panel-title">';
                echo '<a class="accordion-toggle" style="display:block" data-toggle="collapse" data-parent="#accordion" href="#' . $row["DOC_TIP"] . '">' . $row["DOC_TIP"] . '</a>';
                //echo '<a class="panel-title" style="display: block" href="#' . $row['DOC_TIP'] . '" data-toggle="collapse"><h5>' . $row["DOC_TIP"] . '<span class="glyphicon glyphicon-chevron-right pull-right"></span></h5></a>';
                echo '</h5>';
              echo '</div>';
              if ($criterio != '') {
                $in = 'in';
              } else {
                $in = '';
              }

              echo '<div id="' . $row["DOC_TIP"] . '" class="panel-collapse collapse ' . $in . '">';
                  echo '<ul class="list-group">';
        }
                    echo '<li onclick="open_window(\'' . $GLB_APP_PATH . 'doc_prevencion/' . $row["DOC_TIP"] . '/' . $row["DOC_NUM"] . '.pdf\')" class="list-group-item item_detalle">';
                      echo '<p class="text-primary">' . $row["DOC_NUM"] . '</p>';
                      echo '<p class="text-info">' . $row["DOC_NOM"] . '</p>';
                    echo '</li>';
      }

                  echo '</ul>';
              echo '</div>';
            echo '</div>';
        echo '</div>';
      ?>
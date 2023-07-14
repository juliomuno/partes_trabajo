  <?php
      include "comun/funciones.php";
      include "comun/db_con.php";

      $criterio = $_REQUEST['criterio'];
      $usuario = $_REQUEST['usr'];
      $doc = $_REQUEST['doc'];
      $fec_rea = STR_formato_cadena(date("Y-m-d H:i:s"));

      // marcar como leido y abrir documento
      if ($doc!=0){
        // marcar como leido
        $sSQL = "INSERT INTO DOC_USR_LOG (DUL_USR, DUL_FEC, DUL_DOC, DUL_MOD) ";
        $sSQL .= "VALUES (" . $usuario . ", " . $fec_rea . ", " . $doc . ",1)";
        $resultado = DB_EJECUTA($sSQL);
        
        if (!$resultado) {
          //$respuesta->error = true;
          //$respuesta->mensaje = "Error al registrar lectura de documento.";
        } else {
          // abrir documento
          $sSQL = "SELECT * FROM DOC_PREV WHERE DOC_ID=" . $doc;
          $sentencia1 = DB_CONSULTA($sSQL);
          while ($row1 = mysql_fetch_assoc($sentencia1)) {
            echo "<script type='text/javascript'>open_window(\'../doc_prevencion/" . $row1["DOC_TIP"] . "/" . $row1["DOC_NUM"] . ".pdf\')</script>";
          }
        }
        
      }

      //documentos parametrizados para hoy para el operario
      $sSQL = "SELECT DOC_PREV.*, DOC_USR_LOG.DUL_IDE FROM DOC_USR_CFG LEFT JOIN DOC_USR_LOG ON (DUL_FEC>=DUC_FEC_INI AND DUL_FEC<=DUC_FEC_FIN AND DUL_FEC>=CURDATE() AND DUL_FEC<DATE_ADD(CURDATE(),INTERVAL 1 DAY) AND (DUL_USR=DUC_USR OR DUL_USR=" . $usuario . ") AND DUL_DOC=DUC_DOC) INNER JOIN DOC_PREV ON DOC_ID=DUC_DOC WHERE (DUC_USR=" . $usuario . " OR DUC_USR=-1) AND (DUC_FEC_INI<=CURDATE() AND DUC_FEC_FIN>=CURDATE())";
      $sentencia = DB_CONSULTA($sSQL);
      $categoria = "";
      
      echo '<div class="panel-group" id="accordion">';

      $bexi2=0;
      while ($row = mysql_fetch_assoc($sentencia)) {
        if ($row["DUL_IDE"]==''){
          $bexi2=1;
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
                  echo '<a class="accordion-toggle" style="display:block" data-toggle="collapse" data-parent="#accordion" href="#' . $row["DOC_TIP"] . '">' . str_replace('_',' ',$row['DOC_TIP']) . '</a>';
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
                      //echo '<li class="list-group-item item_detalle">';
                      echo '<li onclick="open_window(\'../doc_prevencion/' . $row["DOC_TIP"] . '/' . $row["DOC_NUM"] . '.pdf\')" class="list-group-item item_detalle">';
                        echo '<a href="documentacion_diaria.php?doc=' . $row["DOC_ID"] . '">';
                        echo '<p class="text-primary">' . $row["DOC_NUM"] . '</p>';
                        echo '<p class="text-info">' . $row["DOC_NOM"] . '</p>';
                        echo '</a>';
                      echo '</li>';
        }
      }

                  echo '</ul>';
              echo '</div>';
            echo '</div>';
        echo '</div>';

        if ($bexi2==0){
          php_redirect("principal.php");
        }
      ?>
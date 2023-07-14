
  <?php
      include "comun/funciones.php";
      include "comun/db_con.php";
      session_start();

      $usuario = $_SESSION['GLB_USR_ID'];
      $usu_nom = DB_LEE_CAMPO("USR_WEB", "CONCAT_WS(' ',USR_NOM, USR_APE)","USR_ID=" .$usuario);
      echo '<p>Planificación de: '.$usu_nom.'</p>';

      $pla_id=DB_LEE_CAMPO("PLA_ENC","PE_ID","PE_FEC=CURDATE()");

      $sentencia = DB_CONSULTA("SELECT PEE_ID, PEE_PRE, PRE_DES, CONCAT_WS('',USR_NOM,USR_APE) AS ENCARGADO, PEE_PAR FROM PLA_ENC_ENC INNER JOIN PRE ON PEE_PRE=PRE_ID INNER JOIN PLA_ENC_DET ON PEE_PE=PED_PE AND PEE_ID=PED_PEE INNER JOIN USR_WEB ON PEE_ENC=USR_ID WHERE PEE_PE=".$pla_id." AND PED_OPE=".$usuario." AND PED_JEF=1");
      
      $presupuesto = "";
      echo '<b>PLANIFICADO COMO JEFE DE EQUIPO:</b>';
      echo '<div class="panel-group" id="accordion">';

      while ($row = mysql_fetch_assoc($sentencia)) {
        if ($row["PEE_PRE"] != $presupuesto) {
          if ($presupuesto != '') {
                    echo '</ul>';
                echo '</div>';
              echo '</div>';
          }
          $presupuesto = $row["PEE_PRE"];

          //miembros del equipo
          $equipo_miembros="";
          $equipo_ctd=0;
          $sentencia_det = DB_CONSULTA("SELECT PEE_ID, PED_OPE, CONCAT_WS(' ',USR_NOM,USR_APE) AS OPERARIO, PED_HOR, PEE_PAR FROM PLA_ENC_ENC INNER JOIN PRE ON PEE_PRE=PRE_ID INNER JOIN PLA_ENC_DET ON PEE_PE=PED_PE AND PEE_ID=PED_PEE INNER JOIN USR_WEB ON PED_OPE=USR_ID WHERE PEE_PE=".$pla_id." AND PED_OPE<>".$usuario." AND PED_JEF=0 AND PEE_ID=".$row['PEE_ID'].";");
            while ($row_det = mysql_fetch_assoc($sentencia_det)) {
              $equipo_ctd = $equipo_ctd+1;
              $equipo_miembros .= '<li onclick="" class="list-group-item item_detalle">';
              $equipo_miembros .= '<p class="text-primary">'. $row_det['OPERARIO'] .'</p>';
                //detalle de tiempos ejecutados
                $sentencia_det2 = DB_CONSULTA("SELECT * FROM USR_JOR WHERE UJ_USU=".$row_det['PED_OPE']." AND UJ_PEE=".$row_det['PEE_ID']." ORDER BY UJ_FEC_INI, UJ_FEC_FIN;");
                while ($row_det2 = mysql_fetch_assoc($sentencia_det2)) {
                  $equipo_miembros .= '<p class="text-info">Inicio: '. STR_hora2($row_det2['UJ_FEC_INI']);
                  if ($row_det2['UJ_FEC_FIN']!=''){
                    $equipo_miembros .= ' - Fin: '. STR_hora2($row_det2['UJ_FEC_FIN']) . ' - <b>Acabado</b>';
                  } else {
                    $equipo_miembros .= ' - <b>Pendiete de finalizar trabajo</b>';
                  }
                  $equipo_miembros .= '</p>';
                }
              $equipo_miembros .= '</li>';
            }
            if ($equipo_ctd==0){
              $equipo_ctd_txt="Solo, sin operarios";
            } else {
              $equipo_ctd_txt="Equipo: " . $equipo_ctd . " operarios";
            }
            //**********************************

            echo '<div class="panel panel-primary">';
              echo '<div class="panel-heading">';
                echo '<h5 class="panel-title">';
                echo '<a class="accordion-toggle" style="display:block" data-toggle="collapse" data-parent="#accordion" href="#' . $row["PEE_PRE"] . '">';
                echo '<table width=100%><tr><td> Presupuesto: ' . $row['PEE_PRE'] . '</td><td align="right">'. $equipo_ctd_txt . '</td></tr><tr><td colspan="2"><br>'. $row['PRE_DES'] . '</td></tr></table></a>';
                echo '</h5>';
              echo '</div>';

              //echo '<div id="' . $row["PEE_PRE"] . '" class="panel-collapse collapse">';
              echo '<div id="' . $row["PEE_PRE"] . '" class="panel-collapse collapse in">';
                  echo '<ul class="list-group">';
        }
        //miembros del equipo
        echo $equipo_miembros;
      } 

                  echo '</ul>';
              echo '</div>';
            echo '</div>';
        echo '</div>';


      echo '<b>PLANIFICADO COMO MIEMBRO DEL EQUIPO:</b>';
      $sentencia3 = DB_CONSULTA("SELECT PEE_ID, PEE_PRE, PRE_DES, CONCAT_WS('',USR_NOM,USR_APE) AS ENCARGADO, PEE_PAR, OPERARIO FROM PLA_ENC_ENC INNER JOIN PRE ON PEE_PRE=PRE_ID INNER JOIN PLA_ENC_DET ON PEE_PE=PED_PE AND PEE_ID=PED_PEE INNER JOIN USR_WEB ON PEE_ENC=USR_ID INNER JOIN LIST_PLANIFICACION_JEFE ON LIST_PLANIFICACION_JEFE.PED_PEE=PEE_ID WHERE PEE_PE=".$pla_id." AND PED_OPE=".$usuario." AND PED_JEF=0");

      while ($row3 = mysql_fetch_assoc($sentencia3)) {
        echo '<div class="panel panel-primary">';
          echo '<div class="panel-heading">';
            echo '<h5 class="panel-title">';
            echo '<a class="accordion-toggle" style="display:block" data-toggle="collapse" data-parent="#accordion">';
            echo '<table width=100%>';
            echo '<tr><td> Jefe: <b>'.$row3['OPERARIO'].'</b></td><td align="right"></td></tr>';
            echo '<tr><td> Presupuesto: '.$row3['PEE_PRE'].'</td><td align="right">Encargado: '.$row3['ENCARGADO'].'</td></tr>';
            echo '<tr><td colspan="2"><br>'.$row3['PRE_DES'].'</td></tr>';
            echo '</table></a>';
            echo '</h5>';
          echo '</div>';
        echo '</div>';
      }


      echo '<br><br><b>PLANIFICADO SIN JEFE DE EQUIPO:</b><br>Contacte con el t&eacute;cnico en oficina.';
      $sentencia4 = DB_CONSULTA("SELECT PEE_ID, PEE_PRE, PRE_DES, PEE_PAR, OPERARIO FROM PLA_ENC_ENC INNER JOIN PRE ON PEE_PRE=PRE_ID INNER JOIN PLA_ENC_DET ON PEE_PE=PED_PE AND PEE_ID=PED_PEE LEFT JOIN LIST_PLANIFICACION_JEFE ON LIST_PLANIFICACION_JEFE.PED_PEE=PEE_ID WHERE PEE_PE=".$pla_id." AND PED_OPE=".$usuario." AND LIST_PLANIFICACION_JEFE.PED_PEE IS NULL");

      while ($row4 = mysql_fetch_assoc($sentencia4)) {
        echo '<div class="panel panel-primary">';
          echo '<div class="panel-heading">';
            echo '<h5 class="panel-title">';
            echo '<a class="accordion-toggle" style="display:block" data-toggle="collapse" data-parent="#accordion">';
            echo '<table width=100%>';
            echo '<tr><td> Presupuesto: '.$row4['PEE_PRE'].'</td><td align="right"></td></tr>';
            echo '<tr><td colspan="2"><br>'.$row4['PRE_DES'].'</td></tr>';
            echo '</table></a>';
            echo '</h5>';
          echo '</div>';
        echo '</div>';
      }
      ?>
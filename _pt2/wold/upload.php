<?php

//datos del arhivo 
$nombre_archivo = $_FILES['foto1']['name'];
$tipo_archivo = $_FILES['foto1']['type']; 
$tamano_archivo = $_FILES['foto1']['size']; 

print('<br>');
print($nombre_archivo);
print("<br>");
print($tipo_archivo);
print("<br>");
print($tamano_archivo/1000);
print("<br>");

//compruebo si las características del archivo son las que deseo 
if (!((strpos($tipo_archivo, "gif") || strpos($tipo_archivo, "jpeg")) && ($tamano_archivo < 10000000))) { 
   	echo "La extensión o el tamaño de los archivos no es correcta. <br><br><table><tr><td><li>Se permiten archivos .gif o .jpg<br><li>se permiten archivos de 10000 Kb máximo.</td></tr></table>"; 
}else{ 
   	if (move_uploaded_file($_FILES['foto1']['tmp_name'], "../img/" . $nombre_archivo)){ 
      	echo "El archivo ha sido cargado correctamente."; 
   	}else{ 
      	echo "Ocurrió algún error al subir el fichero. No pudo guardarse."; 
   	} 
} 
?>
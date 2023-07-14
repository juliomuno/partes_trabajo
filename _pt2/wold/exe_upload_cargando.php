<?php
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
	$respuesta = new stdClass();
	
	$nombre = $_REQUEST['txt_nombre'];
	$file = $_FILES['txt_img1']['name'];
	$respuesta->error = false;

	if (move_uploaded_file($_FILES['txt_img1']['tmp_name'],"../partes_imagenes_pruebas/".$file)) {
       $respuesta->mensaje=$file;
    } else {	
    	$respuesta->mensaje="No se pudo copiar el archivo " . $file;
    	$respuesta->error = true;
    }

    $file = $_FILES['txt_img2']['name'];
	if (move_uploaded_file($_FILES['txt_img2']['tmp_name'],"../partes_imagenes_pruebas/".$file)) {
       $respuesta->mensaje .= " " . $file;
    } else {
    	$respuesta->mensaje = "No se pudo copiar el archivo " . $file;
    	$respuesta->error = true;
    }
    $respuesta->mensaje .= " " . $nombre;
	$respuesta->error = true;
	echo json_encode($respuesta);
} else {
	throw new Exception("Error Processing Request", 1);   
}
?>
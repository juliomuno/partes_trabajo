<?php
$para = "amallou@moneleg.es";
$titulo = "Contacto DroneBahiadeCadiz.com";
$header = "From: info@moneleg.es";
$msjCorreo = "Prueba de mensaje\n";
if (mail($para, $titulo, $msjCorreo, $header)) {
	echo "Resultado correcto";
} else {
	echo "Resultado incorrecto";
}

?>
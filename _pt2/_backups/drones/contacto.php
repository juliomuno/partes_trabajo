<?php
echo "<!DOCTYPE html>";

echo "<html>";
echo "<head>";
echo '<link rel="stylesheet" type="text/css" href="./css/validacion.css" media="screen">';
echo "</head>";

echo "<body>";

// Procesar los datos del formulario
$nombre = $_POST["nombre"];
$telefono = $_POST["telefono"];
$email = $_POST["email"];
$mensaje = $_POST["mensaje"];
$para = "amallou@moneleg.es";
$titulo = "Contacto DroneBahiadeCadiz.com";
$header = "From: " . $email;
$msjCorreo = "Nombre: $nombre\n Telefono: $telefono\n E-Mail: $email\n Mensaje:\n $mensaje";

if (isset($_POST['submit'])) {
	if (mail($para, $titulo, $msjCorreo, $header)) {
		echo "<p>El mensaje se envi&oacute; correctamente.</p>";
		echo '<input type="button" value="Aceptar" onclick="javascript: window.location.assign(\'http://www.dronebahiadecadiz.com\');"/>';
	} else {
		echo "El mensaje no se envi&oacute;. Por favor, vuelva a intentarlo.";
		echo '<input type="button" value="Aceptar" onclick="javascript: window.location.assign(\'http://www.dronebahiadecadiz.com\');"/>';
	}
}

echo "</body>";
echo "</html>";
?>

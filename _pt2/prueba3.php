<!DOCTYPE html>

<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <title>MediaRecorder examples - Record live audio</title>
  
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="./audio_styles.css" type="text/css">
  <script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
  <script src="./record-live-audio2.js"></script>
</head>

<body>
    <form name="formulario" id="formulario" enctype="multipart/form-data" method="POST" action="./exe_prueba.php">
    <p>Live Sound</p>
  
    <p><input type="button" id="record" value="Grabar Audio"></input> <input type="button" id="stop" disabled="" value="Stop"></input></p>
    <p><audio id="audio" controls=""></audio></p>
  
  <p><input type="text" name="txt_blob" rows="10" cols="50" id="txt_blob" /></p>
  <input type="submit" name="boton" id="boton" value="Enviar" />
  </form>

</body>
</html>
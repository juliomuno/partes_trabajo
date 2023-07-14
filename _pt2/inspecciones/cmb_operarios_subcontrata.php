<?php
  include "../comun/funciones.php";
  include "../comun/db_con.php";
      
  session_start();

	$indice = $_REQUEST['indice'];
	$subcontrata = $_REQUEST['subcontrata'];

?>
<html>
	<head>
		
	</head>
	<body>
		<?php echo DB_COMBOBOX("LIST_INSPECCIONES_SUBCONTRATAS_OPERARIOS","Codigo","Nombre","Subcontrata=" . $subcontrata,"Nombre","cmb_ope_subc[" . $indice . "]","cmb_ope_subc[" . $indice . "]","form-control","","","","");?>
	</body>
</html>
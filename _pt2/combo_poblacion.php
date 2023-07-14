<?php
	include "comun/db_con.php";
	
	$id_uot = $_REQUEST['id_uot'];
	if ($id_uot == '') $id_uot=0;
	
	echo DB_COMBOBOX("LIST_POBLACIONES","Codigo","Nombre","UOT=" . $id_uot,"Nombre","cmb_pob","cmb_pob","form-control","","","","");
?>
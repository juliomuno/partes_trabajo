<?php
	include "comun/db_con.php";
	
	session_start();
	
	unset($_SESSION['GLB_USR_ID']);
    unset($_SESSION['GLB_USR_NOM']);
    php_redirect("../_pt3/index.php");
?>
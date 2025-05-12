<?php
	$con= mysqli_connect("localhost","root","","alimentos");
	if($con->connect_errno){
		die("La conexion fallo".$con->connect_errno);
	}
?>
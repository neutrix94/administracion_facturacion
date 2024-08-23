<?php
	include( '../include/conexion.php' );
	$rfc = $_POST['dato']; 
	$key = $_POST['current_key'];
	$sql = "SELECT id_cliente FROM clientes WHERE nombre = '{$rfc}' AND id_cliente != '{$key}'";
	$eje = mysql_query( $sql ) or die( "Error al consultar si el rfc existe : " . mysql_error() );
	if ( mysql_num_rows( $eje ) <= 0 ){
		die('ok');
	}else{
		die( "El RFC que intenta ingresar ya esta registrado, verifique y vuelva a intentar!");
	}
?>
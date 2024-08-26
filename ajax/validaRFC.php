<?php
	include('../include/db.php');
	$db = new db();
	$link = $db->conectDB();
	$rfc = $_POST['dato']; 
	$key = $_POST['current_key'];
	$sql = "SELECT id_cliente FROM clientes WHERE nombre = '{$rfc}' AND id_cliente != '{$key}'";
	$eje = $link->query( $sql ) or die( "Error al consultar si el rfc existe : {$sql}" );
	if ( $eje->rowCount() <= 0 ){
		die('ok');
	}else{
		die( "El RFC que intenta ingresar ya esta registrado, verifique y vuelva a intentar!");
	}
?>
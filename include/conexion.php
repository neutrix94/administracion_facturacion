<?php
	session_start();
	$hostLocal="www.sistemageneralcasa.com";
	$userLocal="wwsist_oscar23";
	$passLocal="wwsist_oscar23_23";
	$nombreLocal="wwsist_administracion_facturacion";//cdelasluces
	$local=@mysql_connect($hostLocal, $userLocal, $passLocal);

//comprobamos conexion local
	if(!$local){	//si no hay conexion
		echo 'no hay conexion local';//finaliza programa
	}else{
		//echo'conexion local'.$nombreLocal;
	}
	$dblocal=@mysql_select_db($nombreLocal);
	if(!$dblocal){
		echo 'BD local no encontrada';
	}else{
		//echo '<br> bd local encontrada';
	}	
	mysql_set_charset("utf8");
?>

<?php
	include("../include/db.php");
	$db = new db();
	$link = $db->conectDB();
//recibe las variables
	$usr=$_POST['u'];
	$pss=md5($_POST['p']);
//comprueba si el usuario existe
	$sql="SELECT id_usuario FROM usuarios WHERE login='$usr' AND contrasena='$pss'";
	$eje=$link->query($sql) or die("Error al verificar datos del usuario : {$sql}");
	if($eje->rowCount() == 1){
		die('ok|');
	}else{
		die('No se encontró el usuario o la contraseña es incorrecta!!!');
	}
?>
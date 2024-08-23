<?php
	include("../include/conexion.php");
	//recibimos las variables
		$usr=$_POST['u'];
		$pss=md5($_POST['p']);
	//comprobamos si el usuario existe
		$sql="SELECT id_usuario FROM usuarios WHERE login='$usr' AND contrasena='$pss'";
		$eje=mysql_query($sql) or die("Error al verificar datos del usuario!!!\n\n".mysql_error());
		if(mysql_num_rows($eje)==1){
			die('ok|');
		}else{
			die('No se encontró el usuario o la contraseña es incorrecta!!!');
		}
?>
<?php
	include('../include/conexion.php');
//recibimos las variables
	$fl=$_POST['flag'];
	$usuario_id=$_POST['id_registro_user'];//id de usuario
	$nombre=$_POST['nom_usr'];//nombre de usuario
	$login=$_POST['log'];//login de usuario
	$contrasena_usr=$_POST['pass'];
	$id_perfil=$_POST['id_prf'];
	$observaciones=$_POST['obs'];
	$estado=$_POST['estado'];
	$alta=$_POST['fecha_alta'];

//si es obtener datos del combo
	if($fl==5){
		$sql="SELECT id_perfil,nombre FROM perfiles WHERE activo=1";
		$eje=mysql_query($sql)or die("Error al consultar los perfiles");
		echo '<select class="entrada_txt" id="tipo_perfil">';
		while($r=mysql_fetch_row($eje)){
			echo '<option value="'.$r[0].'">'.$r[1].'</option>';
		}//fin de while
		die('</select>');
	}//fin de proceso para consultar datos del combo

//insertar
	if($fl==1){ $sql="INSERT INTO usuarios ";$accion="agregó";}
//actualizar
	if($fl==2){ $sql="UPDATE usuarios ";$accion="modificó";}
//eliminar
	if($fl==3){ $sql="DELETE FROM usuarios ";$accion="eliminó";}

	if($fl==1 || $fl==2){//actualizar o eliminar
		$sql.=" SET ";
		if($fl==1){//si es insertar
			/*1*$sql.="id_usuario=null,";*/
		}
		
		/*2*/$sql.="nombre='".$nombre."',";
		/*3*/$sql.="login='".$login."',";
		if( $fl==1 || ($fl==2 && strlen($contrasena_usr)>0) ){//si es insertar o es diferente de vacío
			/*4*/$sql.="contrasena='".md5($contrasena_usr)."',";
		}
		/*5*/$sql.="id_perfil='".$id_perfil."',";
		/*6*/$sql.="observaciones='".$observaciones."',";
		/*7*/$sql.="activo='".$estado."',";
		/*8*/$sql.="fecha_alta='".$alta."'";
	}
//si es eliminar
	if($fl==2||$fl==3){
		$sql.=" WHERE id_usuario=".$usuario_id;
	}

	if($fl==4){
		$sql="SELECT * from usuarios WHERE id_usuario=$usuario_id";

	}
//ejecutamos la consulta
	$eje=mysql_query($sql)or die("Error al ejecutar consulta!!!\n\n".$sql."\n\n".mysql_error());
//regresamos datos
	if($fl==4){
		$r=mysql_fetch_row($eje);
		echo 'ok|'.$r[0].'|'.$r[1].'|'.$r[2].'|'.$r[3].'|';
	//armamos el combo
		$sql="SELECT id_perfil,nombre FROM perfiles WHERE id_perfil=$r[4]";
		$eje_combo=mysql_query($sql)or die("Error al consultar perfil actual");
		$re=mysql_fetch_row($eje_combo);
		echo '<select class="entrada_txt" id="tipo_perfil">';
			echo '<option value="'.$re[0].'">'.$re[1].'</option>';
		$sql="SELECT id_perfil,nombre FROM perfiles WHERE id_perfil!=$r[4] AND activo=1";
		$eje_combo=mysql_query($sql)or die("Error al consultar perfil actual");
		while($re=mysql_fetch_row($eje_combo)){
			echo '<option value="'.$re[0].'">'.$re[1].'</option>';
		}
		echo '</select>';

		echo '|'.$r[5].'|'.$r[6].'|'.$r[7];
	}else{
		echo 'ok|Se '.$accion.' el usuario exitosamente!!!';
	}


?>
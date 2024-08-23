<?php
	include('../include/conexion.php');
//recibimos las variables
	$fl=$_POST['flag'];
	$perfil_id=$_POST['id_registro_perfil'];
	$nombre=$_POST['nomb'];
	$adm=$_POST['admin'];
	$status=$_POST['act'];
	$obs=$_POST['observaciones'];
	//die($obs);

//insertar
	if($fl==1){ $sql="INSERT INTO perfiles ";$accion="agregó";}
//actualizar
	if($fl==2){ $sql="UPDATE perfiles ";$accion="modificó";}
//eliminar
	if($fl==3){ $sql="DELETE FROM perfiles ";$accion="eliminó";}

	if($fl==1 || $fl==2){//actualizar o eliminar
		$sql.=" SET ";
		if($fl==1){//si es insertar
			/*1*$sql.="id_usuario=null,";*/
		}
		
		/*2*/$sql.="nombre='".$nombre."',";
		/*3*/$sql.="es_admin='".$adm."',";
		/*4*/$sql.="activo='".$status."',";
		/*5*/$sql.="observaciones='".$obs."',";
	//si es insertar
		if($fl==1){
			/*6*/$sql.="alta=now(),";
			/*7*/$sql.="ultima_modificacion='0000-00-00 00:00:00'";
		}
	//si es modificar
		if($fl==2){
			/*7*/$sql.="ultima_modificacion=now()";
		}
	}
//si es eliminar
	if($fl==2||$fl==3){
		$sql.=" WHERE id_perfil=".$perfil_id;
	}
//echo ($sql);
	if($fl==4){
		$sql="SELECT * from perfiles WHERE id_perfil=$perfil_id";

	}
//ejecutamos la consulta
	$eje=mysql_query($sql)or die("Error al ejecutar consulta!!!\n\n".$sql."\n\n".mysql_error());
//regresamos datos
	if($fl==4){
		$r=mysql_fetch_row($eje);
		echo 'ok|'.$r[0].'|'.$r[1].'|'.$r[2].'|'.$r[3].'|'.$r[4].'|'.$r[5].'|'.$r[6];
	}else{
		echo 'ok|Se '.$accion.' el perfil exitosamente!!!';
	}


?>
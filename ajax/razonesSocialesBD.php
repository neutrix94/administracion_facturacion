<?php
	include('../include/conexion.php');
//recibimos las variables
	$fl=$_POST['fl'];
	//die('flag:'.$fl);
	
	$id_rz=$_POST['id_reg'];
	$nombre=$_POST['nom_rs'];
	$host=$_POST['server'];
	$nom_db=$_POST['nombre_base_datos'];
	$rfc=$_POST['rfc'];
	$ruta_link=$_POST['enlace'];
	$orden=$_POST['ord'];
	$pss_db=$_POST['pass_base_datos'];
	$user_db=$_POST['usuario_base_datos'];
	$estado=$_POST['activo'];
	$obs=$_POST['observ'];

//insertar
	if($fl==1){ $sql="INSERT INTO razones_sociales ";$accion="agregó";}
//actualizar
	if($fl==2){ $sql="UPDATE razones_sociales ";$accion="modificó";}
//eliminar
	if($fl==3){ $sql="DELETE FROM razones_sociales ";$accion="eliminó";}

	if($fl==1 || $fl==2){//actualizar o eliminar
		$sql.=" SET ";

		/*2*/$sql.="nombre='".$nombre."',";
		/*3*/$sql.="link='".$host."',";
		/*4*/$sql.="usuario_db='".$user_db."',";
		/*5*/$sql.="contrasena_db='".$pss_db."',";
		/*6*/$sql.="nombre_db='".$nom_db."',";
		/*7*/$sql.="ruta='".$ruta_link."',";
		/*8*/$sql.="RFC='".$rfc."',";
		/*9*/$sql.="observaciones='".$obs."',";
		/*10*/$sql.="activo='".$estado."',";
		/*11*/$sql.="orden='".$orden."'";

		if($fl==1){//si es insertar
			/*13*/$sql.=",alta=now(),ultima_modificacion='00-00-00 00:00:00'";
		}
	}

//si es eliminar
	if($fl==2||$fl==3){
	//die($id_rg);
		$sql.=" WHERE id_razon_social=".$id_rg;
	}

	if($fl==4){
		$sql="SELECT * from razones_sociales";
	}
	$eje=mysql_query($sql)or die("Error al ejecutar consulta!!!\n\n".$sql."\n\n".mysql_error());

	if($fl==4){
		$r=mysql_fetch_row($eje);
		echo 'ok|'.$r[0].'|'.$r[1].'|'.$r[2].'|'.$r[3].'|'.$r[4].'|'.$r[5].'|'.$r[6].'|'.$r[7].'|'.$r[8].'|'.$r[9].'|'.$r[10].'|'.$r[11].'|'.$r[12];
		echo '|'.$r[13].'|'.$r[14].'|'.$r[15].'|'.$r[16].'|'.$r[17].'|'.$r[18].'|'.$r[19];
	}else{
		echo 'ok|Se '.$accion.' la razón social exitosamente!!!';
	}
?>
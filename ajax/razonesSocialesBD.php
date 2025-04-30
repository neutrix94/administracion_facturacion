<?php
	include('../include/db.php');
	$db = new db();
	$link = $db->conectDB();
//recibimos las variables
	$fl = $_POST['fl'];
	//die('flag:'.$fl);
	
	$id_rz=$_POST['id_reg'];
	if( isset( $_POST['nombre'] ) ){
		$id_razon_social = $_POST['id_razon_social']; 
		$nombre = $_POST['nombre'];
		$rfc = $_POST['rfc']; 
		$link = $_POST['link']; 
		$orden = $_POST['orden']; 
		$maximo_compras = $_POST['maximo_compras']; 
		$compras_actuales = $_POST['compras_actuales']; 
		$inv_precio_compra = $_POST['inv_precio_compra']; 
		$color = $_POST['color']; 
		$url_api = $_POST['url_api']; 
		$enviar_venta_a_rs = $_POST['enviar_venta_a_rs'];
		$host_db = $_POST['host_db']; 
		$usuario_db = $_POST['usuario_db']; 
		$nombre_db = $_POST['nombre_db']; 
		$activo = $_POST['activo'];
		$contrasena_db = $_POST['contrasena_db'];
		$maximo_ventas = $_POST['maximo_ventas'];
		$ventas_actuales = $_POST['ventas_actuales'];
		$inventario_precio_compra = $_POST['inventario_precio_compra'];
		$inventario_precio_venta = $_POST['inventario_precio_venta'];
		$observaciones = $_POST['observaciones']; 
		$id_equivalente = $_POST['id_equivalente']; 
		$limite_registros_barrido_ventas = $_POST['limite_registros_barrido_ventas'];	
	}

//insertar
	if($fl==1){ $sql="INSERT INTO razones_sociales ";$accion="agregó";}
//actualizar
	if($fl==2){ $sql="UPDATE razones_sociales ";$accion="modificó";}
//eliminar
	if($fl==3){ $sql="DELETE FROM razones_sociales ";$accion="eliminó";}

	if($fl==1 || $fl==2){//actualizar o eliminar
		$sql.=" SET ";
		/*2*/$sql.="nombre = '{$nombre}',
				link = '{$link}',
				usuario_db = '{$usuario_db}',
				contrasena_db = '{$contrasena_db}',
				nombre_db = '{$nombre_db}',
				host_db = '{$host_db}',
				RFC = '{$rfc}',
				observaciones = '{$observaciones}',
				activo = '{$activo}',
				orden = '{$orden}',
				maximo_compras = '{$maximo_compras}',
				compras_actuales = '{$compras_actuales}',
				maximo_ventas = '{$maximo_ventas}',
				ventas_actuales = '{$ventas_actuales}',
				inventario_precio_compra = '{$inventario_precio_compra}',
				inventario_precio_venta ='{$inventario_precio_venta}',
				color ='{$color}',
				url_api ='{$url_api}',
				id_equivalente ='{$id_equivalente}',
				enviar_venta_a_rs ='{$enviar_venta_a_rs}',
				limite_registros_barrido_ventas ='{$limite_registros_barrido_ventas}'";
		if( $fl == 1 ){//si es insertar
			/*13*/$sql .= ",alta=now(),ultima_modificacion='00-00-00 00:00:00'";
		}else{//actualizacion
			/*13*/$sql .= ",ultima_modificacion = NOW()";
		}
	}

//si es eliminar
	if($fl==2||$fl==3){
	//die($id_rg);
		$sql.=" WHERE id_razon_social = {$id_rz}";
	}

	if($fl==4){
		$sql="SELECT
				id_razon_social,
				nombre,
				link,
				usuario_db,
				contrasena_db,
				nombre_db,
				host_db,
				RFC,
				observaciones,
				activo,
				orden,
				maximo_compras,
				compras_actuales,
				maximo_ventas,
				ventas_actuales,
				inventario_precio_compra,
				inventario_precio_venta,
				color,
				url_api,
				id_equivalente,
				enviar_venta_a_rs,
				limite_registros_barrido_ventas,
				alta,
				ultima_modificacion
			FROM razones_sociales
			WHERE id_razon_social = {$id_rz}";
	}
	$eje = $link->query( $sql )or die( "Error al ejecutar consulta : {$sql}" );

	if($fl==4){
		$rS = $eje->fetch(PDO::FETCH_ASSOC);
		include( '../include/forms/formularioRazonSocial.php' );
		//echo 'ok|'.$r[0].'|'.$r[1].'|'.$r[2].'|'.$r[3].'|'.$r[4].'|'.$r[5].'|'.$r[6].'|'.$r[7].'|'.$r[8].'|'.$r[9].'|'.$r[10].'|'.$r[11].'|'.$r[12];
		//echo '|'.$r[13].'|'.$r[14].'|'.$r[15].'|'.$r[16].'|'.$r[17].'|'.$r[18].'|'.$r[19];
	}else{
		echo 'ok|Se '.$accion.' la razón social exitosamente.';
	}
?>
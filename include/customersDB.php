<?php
	include( 'db.php' );
	$values = explode('~', $_POST['data'] );
	//die( $_POST['data'] );
//instancia de la conexión a administración de Facturación
    $conexion_administracion = new Db( 'www.sistemageneralcasa.com','wwsist_oscar23','wwsist_oscar23_23','wwlaca_lacasade_adminFact' );
//obtiene los parámetros de conexión a las bases de datos de los sistemas de facturación
    $sql = "SELECT host_db, user_db, password_db FROM multiple_connection WHERE connection_id = 1";
    $params_tmp = $conexion_administracion->execQuery( $sql, "", "SELECT" );
//listar las bases de datos de los sistemas de facturacion
    $sql = "SELECT nombre_db FROM razones_sociales WHERE activo = 1";
    $databases = $conexion_administracion->execQuery( $sql, "", "SELECT" );

	switch ( $_POST['fl'] ) {
		case 0://insert
			$sql = "INSERT INTO clientes ( nombre, telefono, movil, email, idTipoPersona, EntregaConsSitFiscal, UltimaActualizacion )
							VALUES ( '{$values[1]}', '{$values[2]}', '{$values[3]}', '{$values[4]}','{$values[16]}','{$values[17]}','{$values[18]}' )";
			$insert = $conexion_administracion->execQuery( $sql, "", "INSERT" );
			if ( $insert[0] != 'ok' ){
				die( "Error al insertar el cliente en la BD centralizada : " . $insert[0] );
			}else{
				$sql = "INSERT INTO clientes_razones_sociales ( id_cliente, razon_social, calle, no_int, no_ext,
					colonia, del_municipio, cp, localidad, estado, pais) VALUES ( '{$insert[1]}',
					'{$values[6]}', '{$values[7]}', '{$values[8]}', '{$values[9]}',
					'{$values[10]}', '{$values[11]}', '{$values[12]}',
					'{$values[13]}', '{$values[14]}', '{$values[15]}' )";
				$insert2 = $conexion_administracion->execQuery( $sql, "", "INSERT" );
				if ( $insert2[0] != 'ok' ){
					die( "Error : {$insert2[0]}" );
				}
			}
			$eq = $insert[1];
		//iteración de las bases de datos
		    foreach ( $databases AS $key => $database ) {
		    	$conexion_tmp = new Db( $params_tmp[0]['host_db'], $params_tmp[0]['user_db'], $params_tmp[0]['password_db'], $database['nombre_db'] );
		    //conexion a cada una de las bases de datos de Facturación
		    	$sql = "INSERT INTO ec_clientes ( nombre, telefono, movil, email,idTipoPersona, EntregaConsSitFiscal, UltimaActualizacion, id_sucursal, id_equivalente )
						VALUES ( '{$values[1]}', '{$values[2]}', '{$values[3]}', '{$values[4]}', '{$values[16]}','{$values[17]}','{$values[18]}', 1, '{$eq}' )";
				$insert = $conexion_tmp->execQuery( $sql, "", "INSERT" );
				if ( $insert[0] != 'ok' ){
					die( "Error al insertar el cliente en la BD centralizada : " . $insert[0] );
				}else{
					$sql = "INSERT INTO ec_clientes_razones_sociales ( id_cliente, rfc, razon_social, calle, no_int, no_ext,
						colonia, del_municipio, cp, localidad, estado, pais) VALUES ( '{$insert[1]}', '{$values[1]}',
					'{$values[6]}', '{$values[7]}', '{$values[8]}', '{$values[9]}',
					'{$values[10]}', '{$values[11]}', '{$values[12]}',
					'{$values[13]}', '{$values[14]}', '{$values[15]}' )";
					$insert2 = $conexion_tmp->execQuery( $sql, "", "INSERT" );
					if ( $insert2[0] != 'ok' ){
						die( "Error al insertar el detalle de la razon social en sistema de Facturación : {$insert2[0]}" );
					}
				}
				$conexion_tmp->desconectar();
		    }
		    die( 'ok|El cliente fue insertado correctamente!' );

		break;
		case 1:

		break;
		case 2://update
			$sql = "UPDATE clientes c
						LEFT JOIN clientes_razones_sociales crs ON c.id_cliente = crs.id_cliente
						SET
							c.nombre = '{$values[1]}',
							c.telefono = '{$values[2]}',
							c.movil = '{$values[3]}',
							c.email = '{$values[4]}',
							crs.razon_social = '{$values[6]}',
							crs.calle = '{$values[7]}',
							crs.no_int = '{$values[8]}',
							crs.no_ext = '{$values[9]}',
							crs.colonia = '{$values[10]}',
							crs.del_municipio = '{$values[11]}',
							crs.cp = '{$values[12]}',
							crs.localidad = '{$values[13]}',
							crs.estado = '{$values[14]}',
							crs.pais = '{$values[15]}',
							c.idTipoPersona = '{$values[16]}',
							c.EntregaConsSitFiscal = '{$values[17]}',
							c.UltimaActualizacion = '{$values[18]}'
						WHERE c.id_cliente = '{$values[0]}'";
			$update = $conexion_administracion->execQuery( $sql, "", "UPDATE" );
			foreach ( $databases as $key => $database ) {
				//die( $params_tmp[0]['host_db'] . ',' . $params_tmp[0]['user_db'] . ',' . $params_tmp[0]['password_db'] . ',' . $database['nombre_db'] );
				$conexion_tmp = new Db( $params_tmp[0]['host_db'], $params_tmp[0]['user_db'], $params_tmp[0]['password_db'], $database['nombre_db'] );
				$sql = "UPDATE ec_clientes c
						LEFT JOIN ec_clientes_razones_sociales crs ON c.id_cliente = crs.id_cliente
						SET
							c.nombre = '{$values[1]}',
							c.telefono = '{$values[2]}',
							c.movil = '{$values[3]}',
							c.email = '{$values[4]}',
							crs.rfc = '{$values[1]}',
							crs.razon_social = '{$values[6]}',
							crs.calle = '{$values[7]}',
							crs.no_int = '{$values[8]}',
							crs.no_ext = '{$values[9]}',
							crs.colonia = '{$values[10]}',
							crs.del_municipio = '{$values[11]}',
							crs.cp = '{$values[12]}',
							crs.localidad = '{$values[13]}',
							crs.estado = '{$values[14]}',
							crs.pais = '{$values[15]}',
							c.idTipoPersona = '{$values[16]}',
							c.EntregaConsSitFiscal = '{$values[17]}',
							c.UltimaActualizacion = '{$values[18]}'
						WHERE c.id_equivalente = '{$values[0]}'";
				$update = $conexion_tmp->execQuery( $sql, "", "UPDATE" );
				$conexion_tmp->desconectar();
			}
			die( 'ok|Registro Guardado exitosamente!' );
		break;
		case 4://delete

		break;

		case 5 :
			include( 'customers_centralization.php' );
		break;

		default:
			return 'no action available!';
		break;
	}
?>

<?php
	//instancia de la conexión a administración de Facturación
    $conexion_administracion = new Db( 'www.lacasadelasluces.com','wwlaca_production2022','ZI6&knjM1**#','wwlaca_lacasade_adminFact' );
//obtiene los parámetros de conexión a las bases de datos de los sistemas de facturación
    $sql = "SELECT host_db, user_db, password_db FROM multiple_connection WHERE connection_id = 1";
    $params_tmp = $conexion_administracion->execQuery( $sql, "", "SELECT" );
//listar las bases de datos de los sistemas de facturacion
    $sql = "SELECT nombre_db FROM razones_sociales WHERE activo = 1";
    $databases = $conexion_administracion->execQuery( $sql, "", "SELECT" );

//eliminar clientes repetidos

    foreach ($databases as $key => $database) {
    //conexion a cada una de las bases de datos de Facturación
    	$conexion_tmp = new Db( $params_tmp[0]['host_db'], $params_tmp[0]['user_db'], $params_tmp[0]['password_db'], $database['nombre_db'] );
    	//die( $params_tmp[0]['host_db'] . ',' . $params_tmp[0]['user_db'] . ',' . $params_tmp[0]['password_db'] . ',' . $database['nombre_db'] );
    	$current_customers = "";
    	$sql = "SELECT COUNT(*), GROUP_CONCAT(nombre), GROUP_CONCAT( id_cliente SEPARATOR '~' ) AS ids FROM ec_clientes GROUP BY nombre HAVING COUNT(*) > 1";/**/
		$customers_list_duplicated = $conexion_tmp->execQuery( $sql, "", "SELECT" );
		foreach ( $customers_list_duplicated as $key => $customer ) {
			$customers_to_update = '';
			$customer_final = '';
			$customers_ids = explode( '~', $customer['ids'] );
			$counter_2 = 0;
			foreach ( $customers_ids as $key_2 => $id ) {
				if ( $counter_2 == ( sizeof( $customers_ids ) - 1 ) ) {
					$customer_final = $id ;
				}else{
					$customers_to_update .= ( $counter_2 > 0 ? ',' : null ) . $id;
				}
				$counter_2 ++;
			}
			$sql = "UPDATE ec_ventas SET id_cliente = '{$customer_final}' WHERE id_cliente IN( {$customers_to_update} )";
			$customers_list_duplicated = $conexion_tmp->execQuery( $sql, "", "UPDATE" );

			/*$sql = "DELETE FROM ec_clientes_razones_sociales WHERE id_cliente IN( {$customers_to_update} )";
			$customers_list_duplicated = $conexion_tmp->execQuery( $sql, "", "INSERT" );*/
			$sql = "DELETE FROM ec_clientes WHERE id_cliente IN( {$customers_to_update} )";
			$customers_list_duplicated = $conexion_tmp->execQuery( $sql, "", "INSERT" );
			//echo $sql;
		}
    }

//iteración de las bases de datos
    foreach ($databases as $key => $database) {
    	$current_customers = "";
    //consulta clientes existentes
    	$sql = "SELECT nombre FROM clientes WHERE id_cliente > 0";
		$current_customers_list = $conexion_administracion->execQuery( $sql, "", "SELECT" );
    //conexion a cada una de las bases de datos de Facturación
    	$conexion_tmp = new Db( $params_tmp[0]['host_db'], $params_tmp[0]['user_db'], $params_tmp[0]['password_db'], $database['nombre_db'] );
		$current_customers_count = 0;
		foreach ( $current_customers_list as $key2 => $customer ) {
			$current_customers .= ( $current_customers_count > 0 ? ",'" : "'" ) . "{$customer['nombre']}'";
			$current_customers_count ++;
		}
		$current_customers = ( $current_customers_count == 0 ? "'xxx'" : $current_customers );
		$sql = "SELECT
					c.nombre,
					c.telefono,
					c.movil,
					c.email,
					crs.razon_social,
					crs.calle,
					crs.no_int,
					crs.no_ext,
					crs.colonia,
					crs.del_municipio,
					crs.cp,
					crs.localidad,
					crs.estado,
					crs.pais,
  				c.idTipoPersona,
  				c.EntregaConsSitFiscal,
  				c.UltimaActualizacion
				FROM ec_clientes c
				LEFT JOIN ec_clientes_razones_sociales crs
				ON c.id_cliente = crs.id_cliente
				WHERE c.id_cliente > 0
				AND c.nombre NOT IN( {$current_customers} )";
		$new_customers = $conexion_tmp->execQuery( $sql, "", "SELECT" );
//inserta los clientes en la administración de la facturación
		foreach ( $new_customers as $key => $new_customer ) {
			$sql = "INSERT INTO clientes ( nombre, telefono, movil, email, idTipoPersona, EntregaConsSitFiscal, UltimaActualizacion )
					VALUES ( '{$new_customer['nombre']}', '{$new_customer['telefono']}', '{$new_customer['movil']}', '{$new_customer['email']}', '{$new_customer['idTipoPersona']}', '{$new_customer['EntregaConsSitFiscal']}', '{$new_customer['UltimaActualizacion']}' )";
			$insert = $conexion_administracion->execQuery( $sql, "", "INSERT" );
			if ( $insert[0] != 'ok' ){
				die( "Error al insertar el cliente en la BD centralizada : " . $insert[0] );
			}else{
				$sql = "INSERT INTO clientes_razones_sociales ( id_cliente, razon_social, calle, no_int, no_ext,
					colonia, del_municipio, cp, localidad, estado, pais) VALUES ( '{$insert[1]}',
					'{$new_customer['razon_social']}', '{$new_customer['calle']}', '{$new_customer['no_int']}', '{$new_customer['no_ext']}',
					'{$new_customer['colonia']}', '{$new_customer['del_municipio']}', '{$new_customer['cp']}',
					'{$new_customer['localidad']}', '{$new_customer['estado']}', '{$new_customer['pais']}' )";
				$insert2 = $conexion_administracion->execQuery( $sql, "", "INSERT" );
				if ( $insert2[0] != 'ok' ){
					die( "Error : {$insert2[0]}" );
				}
			}
		}
		$conexion_tmp->desconectar();
    }
//echo 'Clientes extraidos exitosamente.';

//inserta clientes que no existen en cada base de datos
	foreach ( $databases as $key => $database ) {
		//die( $params_tmp[0]['host_db'] . ',' . $params_tmp[0]['user_db'] . ',' . $params_tmp[0]['password_db'] . ',' . $database['nombre_db'] );
		$conexion_tmp = new Db( $params_tmp[0]['host_db'], $params_tmp[0]['user_db'], $params_tmp[0]['password_db'], $database['nombre_db'] );
	//consulta todos los clientes de la razon social de facturacion
    $sql = "SELECT
					c.id_cliente,
					c.nombre,
					c.telefono,
          c.movil,
					c.email,
					crs.razon_social,
					crs.calle,
					crs.no_int,
					crs.no_ext,
					crs.colonia,
					crs.del_municipio,
					crs.cp,
					crs.localidad,
					crs.estado,
					crs.pais,
          c.idTipoPersona,
  				c.EntregaConsSitFiscal,
  				c.UltimaActualizacion
				FROM clientes c
				LEFT JOIN clientes_razones_sociales crs
				ON c.id_cliente = crs.id_cliente
				WHERE c.id_cliente > 0
				/*AND c.nombre NOT IN( {$current_customers} )*/";
		$all_customers = $conexion_administracion->execQuery( $sql, "", "SELECT" );
		foreach ( $all_customers as $key => $customer ) {
			$sql = "SELECT id_cliente FROM ec_clientes WHERE nombre IN ('{$customer['nombre']}')";
			//die( $sql );
			$tmp_customer = $conexion_tmp->execQuery( $sql, "", "SELECT" );
		//si no existe se inserta
			if( $tmp_customer == null ){
				$sql = "INSERT INTO ec_clientes ( nombre, telefono, movil, email, id_sucursal, id_equivalente, idTipoPersona, EntregaConsSitFiscal, UltimaActualizacion )
						VALUES ( '{$customer['nombre']}', '{$customer['telefono']}', '{$customer['movil']}', '{$customer['email']}', 1,
						'{$tmp_customer[0]['id_cliente']}', '{$new_customer['idTipoPersona']}', '{$new_customer['EntregaConsSitFiscal']}', '{$new_customer['UltimaActualizacion']}' )";
				$insert = $conexion_tmp->execQuery( $sql, "", "INSERT" );
				if ( $insert[0] != 'ok' ){
					die( "Error al insertar el cliente en la BD centralizada : " . $insert[0] );
				}else{
					$sql = "INSERT INTO ec_clientes_razones_sociales ( id_cliente, rfc, razon_social, calle, no_int, no_ext,
						colonia, del_municipio, cp, localidad, estado, pais) VALUES ( '{$insert[1]}', '{$customer['nombre']}',
						'{$customer['razon_social']}', '{$customer['calle']}', '{$customer['no_int']}', '{$customer['no_ext']}',
						'{$customer['colonia']}', '{$customer['del_municipio']}', '{$customer['cp']}',
						'{$customer['localidad']}', '{$customer['estado']}', '{$customer['pais']}' )";
					$insert2 = $conexion_tmp->execQuery( $sql, "", "INSERT" );
					if ( $insert2[0] != 'ok' ){
						die( "Error al insertar el detalle de la razon social en sistema de Facturación : {$insert2[0]}" );
					}
				}
		//si existe se actualiza
			}else{
				$sql = "UPDATE ec_clientes c
						LEFT JOIN ec_clientes_razones_sociales crs ON c.id_cliente = crs.id_cliente
						SET
							c.id_equivalente = '{$customer['id_cliente']}',
							c.nombre = '{$customer['nombre']}',
							c.telefono = '{$customer['telefono']}',
							c.movil = '{$customer['movil']}',
							c.email = '{$customer['email']}',
/*							crs.id_cliente = '{$customer['id_cliente']}',*/
							crs.rfc = '{$customer['rfc']}',
							crs.razon_social = '{$customer['razon_social']}',
							crs.calle = '{$customer['calle']}',
							crs.no_int = '{$customer['no_int']}',
							crs.no_ext = '{$customer['no_ext']}',
							crs.colonia = '{$customer['colonia']}',
							crs.del_municipio = '{$customer['del_municipio']}',
							crs.cp = '{$customer['cp']}',
							crs.localidad = '{$customer['localidad']}',
							crs.estado = '{$customer['estado']}',
							crs.pais = '{$customer['pais']}',
              c.idTipoPersona = '{$customer['idTipoPersona']}',
							c.EntregaConsSitFiscal = '{$customer['EntregaConsSitFiscal']}',
							c.UltimaActualizacion = '{$customer['UltimaActualizacion']}'
						WHERE c.nombre = '{$customer['nombre']}'";
				$update = $conexion_tmp->execQuery( $sql, "", "UPDATE" );
			}
		}
		$conexion_tmp->desconectar();
	}
	echo "ok|Clientes distribuidos exitosamente!";
?>

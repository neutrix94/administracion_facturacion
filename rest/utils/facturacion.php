<?php
/*
	* Version Oscar 2024-11-22 para loguear error de clientes con datos undefinied en contactos
*/
	class Bill
	{
		private $link;	
		private $store_id;
		private $store_prefix;
		function __construct( $connection, $store_id, $store_prefix ){
			$this->link = $connection;
			$this->store_id = $store_id;
			$this->store_prefix = $store_prefix;
  			//$this->link->set_charset("utf8mb4");
		}

/*Generacion de sincronizacion de clietenes ( temporal )*/
		public function getTemporalCostumer(  ){
			$resp = array();
			$sql = "SELECT 
						id_cliente_facturacion_tmp,
						rfc,
						razon_social,
						id_tipo_persona,
						entrega_cedula_fiscal,
						url_cedula_fiscal,
						calle,
						no_int,
						no_ext,
						colonia,
						del_municipio,
						cp,
						estado,
						pais,
						regimen_fiscal,
						productos_especificos,
						id_cliente_facturacion,
						datos_alta
	          FROM vf_clientes_razones_sociales_tmp
	          WHERE folio_unico IS NULL 
	          OR folio_unico = ''";
	  		$stm = $this->link->query( $sql ) or die( "Error al consultar clientes pendientes de sincronizar : {$sql}" );
			
			//$this->link->autocommit( false );//comienza transaccion
			$this->link->beginTransaction();
			while( $row = $stm->fetch() ){
				$row['folio_unico'] = $this->update_unique_code( 'vf_clientes_razones_sociales_tmp', 'id_cliente_facturacion_tmp', 'CL', $row['id_cliente_facturacion_tmp'] );
				$detail = $this->getTemporalCostumerDetail( $row['id_cliente_facturacion_tmp'] );
				if( sizeof( $detail ) > 0 ){
					$row['detail'] = $detail;
				}
				$json = json_encode( $row, JSON_UNESCAPED_UNICODE );
			//die( $json );
				$sql = "INSERT INTO sys_sincronizacion_registros_facturacion ( id_sincronizacion_registro, sucursal_de_cambio, 
	  			id_sucursal_destino, datos_json, fecha, tipo, status_sincronizacion )
				VALUES( NULL, {$this->store_id}, -1, '{$json}', NOW(), 'envia_cliente.php', 1 )";
				//die( $sql );
				$stm2 = $this->link->query( $sql ) or die( "Error al insertar registro de sincronizacion : {$sql}" );
				$resp[] = $row;
			}
			//$this->link->autocommit( true );//autoriza transaccion
			$this->link->commit();//autoriza transaccion
			return $resp;
		}

		public function getTemporalCostumerDetail( $costumer_id ){
			$resp = array();
			$sql = "SELECT 
						id_cliente_contacto_tmp,
						id_cliente_facturacion_tmp,
						nombre,
						telefono,
						celular,
						correo,
						uso_cfdi,
						id_cliente_contacto,
						id_cliente_facturacion
		  			FROM vf_clientes_contacto_tmp
		  			WHERE id_cliente_facturacion_tmp = {$costumer_id}
		  			AND ( folio_unico IS NULL OR folio_unico = '' )"; //die( $sql );
		  	$stm = $this->link->query( $sql ) or die( "Error al consultar razones sociales pendientes de sincronizar : {$sql}" );
			while( $row = $stm->fetch() ){
				$row['folio_unico'] = $this->update_unique_code( 'vf_clientes_contacto_tmp', 'id_cliente_contacto_tmp', 'CLRZ', $row['id_cliente_contacto_tmp'] );
				$resp[] = $row;
				//die( 'HERE : ' . $row['folio_unico'] );
				//$detail = $this->getTemporalCostumerDetail( $row['id_cliente_tmp'] );
				//if( sizeof( $detail ) > 0 ){
					//$row['detail'] = $detail;
				//}
				//var_dump( $row );
			}
			return $resp;
			//var_dump( $row );
		}

		public function update_unique_code( $table, $keyname, $prefix, $id ){
		//genera el folio unico 
			$sql = "UPDATE {$table} 
						SET folio_unico = '{$this->store_prefix}_{$prefix}_{$id}' 
					WHERE {$keyname} = {$id}";
			$stm = $this->link->query( $sql ) or die( "Error al actualizar folio unico en {$table} : {$sql}" );
			return "{$this->store_prefix}_{$prefix}_{$id}";
		}

/*Insercion de clientes en linea*/
		public function insertCostumers( $costumers ){
			$rows = "";
			//$this->link->autocommit( false );
			$this->link->beginTransaction();
			foreach ( $costumers as $key => $costumer ) {
//var_dump( $costumer );die("stop");
				//var_dump( $costumer['id_cliente_facturacion_tmp'] );
				$insert = $this->insertLineCostumer( $costumer );
				if( $insert != "ok" ){
					die( "Error en objeto insertCostumers : {$insert}" );
				}
			//inserta los registros de sincronizacion de clientes en los sistemas de facturacion
				
				$rows .= ( $rows == "" ? "" : "," );
				if( isset( $costumer['detail'][0]['synchronization_row_id'] ) ){
					$rows .= $costumer['detail'][0]['synchronization_row_id'];
				}else{
					
				}
			}
		//autoriza transaccion
			//$this->link->autocommit( true );
			$this->link->commit();
			//die( "Rows : {$rows}" );
			return $rows;
		}

		public function insertLocalCostumers( $costumers ){
			$rows = "";
			//$this->link->autocommit( false );
			$this->link->beginTransaction();
			foreach ( $costumers as $key => $costumer ) {
				//var_dump( $costumer['id_cliente_facturacion_tmp'] );
				$insert = $this->insertLocalCostumer( $costumer );
				if( $insert != "ok" ){
					die( "Error en objeto insertCostumers : {$insert}" );
				}
			//inserta los registros de sincronizacion de clientes en los sistemas de facturacion
				
				$rows .= ( $rows == "" ? "" : "," );
				$rows .= $costumer['detail'][0]['synchronization_row_id'];
			}
		//autoriza transaccion
			//$this->link->autocommit( true );
			$this->link->commit();
			//die( "Rows : {$rows}" );
			return $rows;
		}

/*Insercion de clientes en local*/
		public function insertCostumersLocal( $costumer ){
//foreach ( $costumers as $key => $costumer ) {
				//$this->link->autocommit( false );
				$this->link->beginTransaction();
				//consulta si el cliente ya existe
				$sql = "SELECT id_cliente_facturacion, folio_unico FROM vf_clientes_razones_sociales WHERE rfc = '{$costumer->rfc}'";
				$stm_check = $this->link->query( $sql ) or die( "Error al consultar si el cliente existe : {$sql}" );
				if( $stm_check->rowCount() > 0 ){
					$costumer_row = $stm_check->fetch();
				//actualiza cabecera
					$sql = "UPDATE vf_clientes_razones_sociales SET 
							/*3*/razon_social = '{$costumer->razon_social}', 
							/*4*/id_tipo_persona = '{$costumer->id_tipo_persona}',
							/*5*/entrega_cedula_fiscal = '{$costumer->entrega_cedula_fiscal}', 
							/*6*/url_cedula_fiscal = '{$costumer->url_cedula_fiscal}', 
							/*7*/calle = '{$costumer->calle}', 
							/*8*/no_int = '{$costumer->no_int}', 
							/*9*/no_ext = '{$costumer->no_ext}', 
							/*10*/colonia = '{$costumer->colonia}', 
							/*11*/del_municipio = '{$costumer->del_municipio}', 
							/*12*/cp = '{$costumer->cp}', 
							/*13*/estado = '{$costumer->estado}', 
							/*14*/pais = '{$costumer->pais}', 
							/*15*/regimen_fiscal = '{$costumer->regimen_fiscal}', 
							/*16*/productos_especificos = '{$costumer->productos_especificos}', 
							/*17*/fecha_alta = '{$costumer->fecha_alta}', 
							/*18*/sincronizar = '1',
							/*18*/datos_alta = CONCAT( datos_alta, ' : {$costumer->datos_alta}' )
							WHERE folio_unico = '{$costumer_row['folio_unico']}'";
					$stm = $this->link->query( $sql ) or die( "Error al actualizar cliente de facturacion en local : {$sql}" );
				}else{
				//inserta cabecera 
					$sql = "INSERT INTO vf_clientes_razones_sociales ( /*1*/id_cliente_facturacion, /*2*/rfc, /*3*/razon_social, /*4*/id_tipo_persona,
							/*5*/entrega_cedula_fiscal, /*6*/url_cedula_fiscal, /*7*/calle, /*8*/no_int, /*9*/no_ext, /*10*/colonia, /*11*/del_municipio, 
							/*12*/cp, /*13*/estado, /*14*/pais, /*15*/regimen_fiscal, /*16*/productos_especificos, /*17*/fecha_alta, /*18*/sincronizar, datos_alta, folio_unico )
							VALUES( /*1*/{$costumer->id_cliente_facturacion}, /*2*/'{$costumer->rfc}', /*3*/'{$costumer->razon_social}', 
							/*4*/'{$costumer->id_tipo_persona}', /*5*/'{$costumer->entrega_cedula_fiscal}', /*6*/'{$costumer->url_cedula_fiscal}',
							/*7*/'{$costumer->calle}', /*8*/'{$costumer->no_int}', /*9*/'{$costumer->no_ext}', /*10*/'{$costumer->colonia}', 
							/*11*/'{$costumer->del_municipio}', /*12*/'{$costumer->cp}', /*13*/'{$costumer->estado}', /*14*/'{$costumer->pais}', 
							/*15*/'{$costumer->regimen_fiscal}', /*16*/'{$costumer->productos_especificos}', /*17*/NOW(), /*18*/1, '{$costumer->datos_alta}', '{$costumer->folio_unico}' )";
					$stm = $this->link->query( $sql ) or die( "Error al insertar cliente de facturacion en local : {$sql}" );
				}
			//obtiene el id insertado
				$costumer_id = $this->link->insert_id;
				//$this->link->autocommit( true );
				$this->link->commit();
			return 'ok';
		}

/*Insercion de contactos en local*/
		public function insertCostumerContactLocal( $contact ){
				//$this->link->autocommit( false );
				$this->link->beginTransaction();
				//consulta si el cliente ya existe
				$sql = "SELECT id_cliente_contacto FROM vf_clientes_contacto WHERE folio_unico = '{$contact->folio_unico}'";
				$stm_check = $this->link->query( $sql ) or die( "Error al consultar si el contacto existe : {$sql}" );
				if( $stm_check->rowCount() > 0 ){
					$costumer_row = $stm_check->fetch();
				//actualiza contacto
					$sql = "UPDATE vf_clientes_contacto SET 
							/*1*/nombre = '{$contact->nombre}', 
							/*2*/telefono = '{$contact->telefono}',
							/*3*/celular = '{$contact->celular}', 
							/*4*/correo = '{$contact->correo}', 
							/*5*/uso_cfdi = '{$contact->uso_cfdi}', 
							/*6*/fecha_ultima_actualizacion = NOW(), 
							/*7*/sincronizar = '1'
							WHERE folio_unico = {$costumer_row['folio_unico']}";
					$stm = $this->link->query( $sql ) or die( "Error al actualizar contacto de facturacion en local : {$sql}" );
				}else{
				//inserta contacto 
					$contact->id_cliente_contacto = str_replace('CONTACTO_', '', $contact->id_cliente_contacto );
					$sql = "INSERT INTO vf_clientes_contacto ( /*1*/id_cliente_contacto, /*2*/id_cliente_facturacion, /*3*/nombre, /*4*/telefono, /*5*/celular, /*6*/correo,
						/*7*/uso_cfdi, /*8*/fecha_alta, /*9*/fecha_ultima_actualizacion, /*10*/folio_unico, /*11*/sincronizar )
						VALUES( /*1*/{$contact->id_cliente_contacto}, /*2*/( SELECT id_cliente_facturacion FROM vf_clientes_razones_sociales WHERE folio_unico = '{$contact->id_cliente_facturacion}' LIMIT 1 ), 
							/*3*/'{$contact->nombre}', /*4*/'{$contact->telefono}', /*5*/'{$contact->celular}', 
							/*6*/'{$contact->correo}', /*7*/'{$contact->uso_cfdi}', /*8*/NOW(), /*9*/'0000/00/00', 
							/*10*/'{$contact->folio_unico}', /*11*/1 )";
					$stm = $this->link->query( $sql ) or die( "Error al insertar contacto de facturacion en local 1 : " . var_dump( $contact ) . "{$sql}" );
				}
				//$this->link->autocommit( true );
				$this->link->commit();
			die( 'ok' );
		}
		public function insertLineCostumer( $costumer ){
$file = fopen("log_inserta_cliente.txt", "a");
fwrite($file, "Facturacion LOG : " . PHP_EOL);
fclose($file);
//var_dump($costumer);
			$action = "";
		//verifica si el cliente existe en relacion al RFC
			$sql = "SELECT id_cliente_facturacion FROM vf_clientes_razones_sociales WHERE rfc = '{$costumer['rfc']}'";
			$check_stm = $this->link->query( $sql ) or die( "Error al consultar si el cliente existe en linea por RFC : {$sql}" );
$file = fopen("log_inserta_cliente.txt", "a");
fwrite($file, "Busca Cliente : {$sql}" . PHP_EOL);
fclose($file);
			if( $check_stm->rowCount() > 0 ){
				$aux_row = $check_stm->fetch( PDO::FETCH_ASSOC );
				$costumer['id_cliente_facturacion'] = "{$aux_row['id_cliente_facturacion']}";
			}
			//$costumer_id = "";
			$sql = ( $costumer['id_cliente_facturacion'] == "" || $costumer['id_cliente_facturacion'] == 0 ? "INSERT INTO" : "UPDATE" );
			$sql .= " vf_clientes_razones_sociales SET
						rfc = '{$costumer['rfc']}', 
						razon_social = '{$costumer['razon_social']}', 
						id_tipo_persona = '{$costumer['id_tipo_persona']}',
						entrega_cedula_fiscal = '{$costumer['entrega_cedula_fiscal']}', 
						url_cedula_fiscal = '{$costumer['url_cedula_fiscal']}', 
						calle = '{$costumer['calle']}', 
						no_int = '{$costumer['no_int']}', 
						no_ext = '{$costumer['no_ext']}', 
						colonia = '{$costumer['colonia']}', 
						del_municipio = '{$costumer['del_municipio']}', 
						cp = '{$costumer['cp']}', 
						estado = '{$costumer['estado']}', 
						pais = '{$costumer['pais']}', 
						regimen_fiscal = '{$costumer['regimen_fiscal']}', 
						productos_especificos = '{$costumer['productos_especificos']}', 
						fecha_alta = NOW(), 
						localidad = '',
						referencia = '',
						fecha_ultima_actualizacion = NOW(),
						datos_alta = '{$costumer['datos_alta']}',
						sincronizar = 1";
			if ( $costumer['id_cliente_facturacion'] == "" || $costumer['id_cliente_facturacion'] == 0 ){
				$action = "INSERTAR";
				$stm = $this->link->query( $sql ) or die( "Error al insertar el nuevo cliente : {$sql}" );
			//consulta el id insertado
				$sql = "SELECT LAST_INSERT_ID() AS last_id";
				$stm2 = $this->link->query( $sql ) or die( "Error al consultar id de nuevo cliente : {$sql}" );
				$row_insert = $stm2->fetch( PDO::FETCH_ASSOC );//die( "{$row_insert['last_id']}" );
				$costumer['id_cliente_facturacion'] = $row_insert['last_id'];
				$costumer['folio_unico'] = "CLIENTE_{$costumer['id_cliente_facturacion']}";
			//actualiza el folio unico
				$sql = "UPDATE vf_clientes_razones_sociales 
							SET folio_unico = '{$costumer['folio_unico']}' 
						WHERE id_cliente_facturacion = {$costumer['id_cliente_facturacion']}";//die($sql);
				$stm = $this->link->query( $sql ) or die( "Error al actualizar el folio unico del nuevo cliente : {$sql}" );
			}else{
				$action = "ACTUALIZAR";
				$costumer['folio_unico'] = "CLIENTE_{$costumer['id_cliente_facturacion']}";
				$sql .= " WHERE id_cliente_facturacion = {$costumer['id_cliente_facturacion']}";
				$stm = $this->link->query( $sql ) or die( "Error al actualizar el cliente : {$sql}" );
			}
$file = fopen("log_inserta_cliente.txt", "a");
fwrite($file, "Cabecera cliente : {$sql}" . PHP_EOL);
fclose($file);
		//procesa el detalle
			foreach ( $costumer['detail'] as $key => $contact ) {
				$sql = ( $costumer['detail'][$key]['id_cliente_contacto'] == "" || $costumer['detail'][$key]['id_cliente_contacto'] == "0" ? "INSERT INTO" : "UPDATE" );
				$costumer['detail'][$key]['id_cliente_facturacion'] = $costumer['id_cliente_facturacion'];
				$sql .= " vf_clientes_contacto SET 
							id_cliente_facturacion = '{$costumer['detail'][$key]['id_cliente_facturacion']}',
							nombre = '{$costumer['detail'][$key]['nombre']}', 
							telefono = '{$costumer['detail'][$key]['telefono']}',
							celular = '{$costumer['detail'][$key]['celular']}', 
							correo = '{$costumer['detail'][$key]['correo']}', 
							uso_cfdi = '{$costumer['detail'][$key]['uso_cfdi']}', 
							fecha_ultima_actualizacion = NOW(), 
							sincronizar = '1'";
				if( $costumer['detail'][$key]['id_cliente_contacto'] == "" || $costumer['detail'][$key]['id_cliente_contacto'] == "0" ){
						
						$stm = $this->link->query( $sql ) or die( "Error al insertar el nuevo contacto : {$sql}" );
						$sql = "SELECT LAST_INSERT_ID() AS last_id";
						$stm2 = $this->link->query( $sql ) or die( "Error al consultar id de nuevo cliente : {$sql}" );
						$row_insert = $stm2->fetch( PDO::FETCH_ASSOC );//die( "{$row_insert['last_id']}" );
						$costumer['detail'][$key]['id_cliente_contacto'] = $row_insert['last_id'];
						$costumer['detail'][$key]['folio_unico'] = "CONTACTO_{$costumer['detail'][$key]['id_cliente_contacto']}";
					//actualiza el folio unico
						$sql = "UPDATE vf_clientes_contacto 
									SET folio_unico = '{$costumer['detail'][$key]['folio_unico']}' 
								WHERE id_cliente_contacto = {$costumer['detail'][$key]['id_cliente_contacto']}";//die($sql);
						$stm = $this->link->query( $sql ) or die( "Error al actualizar el folio unico del nuevo cliente : {$sql}" );
				}else{
					$costumer['detail'][$key]['folio_unico'] = "CONTACTO_{$costumer['detail'][$key]['id_cliente_contacto']}";
					$sql .= " WHERE id_cliente_contacto = {$costumer['detail'][$key]['id_cliente_contacto']}";
					$stm = $this->link->query( $sql ) or die( "Error al actualizar el contacto : {$sql}" );
				}
$file = fopen("log_inserta_cliente.txt", "a");
fwrite($file, "Detalle contactos cliente : {$sql}" . PHP_EOL);
fclose($file);
			}
		//inserta el registro de sincronizacion para sucursales locales
			$costumer_json = json_encode( $costumer, JSON_UNESCAPED_UNICODE );
			$sql = "INSERT INTO sys_sincronizacion_registros_facturacion ( id_sincronizacion_registro, sucursal_de_cambio,
					id_sucursal_destino, datos_json, fecha, tipo, tabla, registro_llave, status_sincronizacion )
					SELECT
						NULL,
						-1,
						id_sucursal,
						'{$costumer_json}',
						NOW(),
						'/rest/utils/facturacion.php',
						'vf_clientes_razones_sociales',
						'{$costumer['rfc']}',
						1
					FROM sys_sucursales 
					WHERE id_sucursal >= -1";
			$stm = $this->link->query( $sql ) or die( "Error al insertar registros de sincronizacion de cliente para equipos locales: {$sql}" );
$file = fopen("log_inserta_cliente.txt", "a");
fwrite($file, "Insercion registros sincronizacion : {$sql}" . PHP_EOL);
fclose($file);
		//inserta el registro de sincronizacion para sistemas de facturacion
			$costumer_json = json_encode( $costumer, JSON_UNESCAPED_UNICODE );
			$sql = "INSERT INTO sys_sincronizacion_registros_facturacion ( id_sincronizacion_registro, sucursal_de_cambio,
					id_sucursal_destino, datos_json, fecha, tipo, tabla, registro_llave, status_sincronizacion )
					VALUES ( NULL, -1, -2, '{$costumer_json}', NOW(), '/rest/utils/facturacion.php', 'vf_clientes_razones_sociales',
						'{$costumer['rfc']}', 1 )";
			$stm = $this->link->query( $sql ) or die( "Error al insertar registros de sincronizacion de cliente para sistemas de facturacion: {$sql}" );
$file = fopen("log_inserta_cliente.txt", "a");
fwrite($file, "Insercion registros sincronizacion para RS facturacion : {$sql}\n\n" . PHP_EOL);
fclose($file);
			return 'ok';
		}

		public function insertLocalCostumer( $costumer ){
			$action = "";
			$costumer_exists = false;
		//consulta si el cliente existe
			$sql = "SELECT id_cliente_facturacion FROM vf_clientes_razones_sociales WHERE id_cliente_facturacion = {$costumer['id_cliente_facturacion']}";
			$stm = $this->link->query( $sql );
			if( $stm->rowCount() > 0 ){
				$costumer_exists = true;
			}
			$sql = ( $costumer_exists == false ? "INSERT INTO" : "UPDATE" );
			$sql .= " vf_clientes_razones_sociales SET ";
			if( $costumer_exists == false ){
				$sql .= " id_cliente_facturacion = {$costumer['id_cliente_facturacion']}, ";
			}
			$sql .= "rfc = '{$costumer['rfc']}', 
						razon_social = '{$costumer['razon_social']}', 
						id_tipo_persona = '{$costumer['id_tipo_persona']}',
						entrega_cedula_fiscal = '{$costumer['entrega_cedula_fiscal']}', 
						url_cedula_fiscal = '{$costumer['url_cedula_fiscal']}', 
						calle = '{$costumer['calle']}', 
						no_int = '{$costumer['no_int']}', 
						no_ext = '{$costumer['no_ext']}', 
						colonia = '{$costumer['colonia']}', 
						del_municipio = '{$costumer['del_municipio']}', 
						cp = '{$costumer['cp']}', 
						estado = '{$costumer['estado']}', 
						pais = '{$costumer['pais']}', 
						regimen_fiscal = '{$costumer['regimen_fiscal']}', 
						productos_especificos = '{$costumer['productos_especificos']}', 
						fecha_alta = NOW(), 
						datos_alta = '{$costumer['datos_alta']}',
						folio_unico = '{$costumer['folio_unico']}',
						sincronizar = 1";
			if ( $costumer_exists == false ){
				$action = "INSERTAR";
				$stm = $this->link->query( $sql ) or die( "Error al insertar el nuevo cliente : {$sql}" );
			}else{
				$action = "ACTUALIZAR";
				$sql .= " WHERE id_cliente_facturacion = {$costumer['id_cliente_facturacion']}";
				$stm = $this->link->query( $sql ) or die( "Error al actualizar el cliente : {$sql}" );
			}
		//procesa el detalle
			foreach ( $costumer['detail'] as $key => $contact ) {
				$contact_exists = false;
			//consulta si el contacto existe
				$sql = "SELECT id_cliente_contacto FROM vf_clientes_contacto WHERE id_cliente_contacto = {$costumer['detail'][$key]['id_cliente_contacto']}";
				$stm = $this->link->query( $sql );
				if( $stm->rowCount() > 0 ){
					$contact_exists = true;
				}
				$sql = ( $contact_exists == false ? "INSERT INTO" : "UPDATE" );
				$costumer['detail'][$key]['id_cliente_facturacion'] = $costumer['id_cliente_facturacion'];
				$sql .= " vf_clientes_contacto SET ";
				if( $contact_exists == false ){
					$sql .= "id_cliente_contacto = {$costumer['detail'][$key]['id_cliente_contacto']}, ";
				}
				$sql .= "id_cliente_facturacion = '{$costumer['detail'][$key]['id_cliente_facturacion']}',
							nombre = '{$costumer['detail'][$key]['nombre']}', 
							telefono = '{$costumer['detail'][$key]['telefono']}',
							celular = '{$costumer['detail'][$key]['celular']}', 
							correo = '{$costumer['detail'][$key]['correo']}', 
							uso_cfdi = '{$costumer['detail'][$key]['uso_cfdi']}', 
							fecha_ultima_actualizacion = NOW(), 
							sincronizar = '1',
							folio_unico = '{$costumer['detail'][$key]['folio_unico']}'";
				if( $contact_exists == false  ){
					$stm = $this->link->query( $sql ) or die( "Error al insertar el nuevo contacto : {$sql}" );
				}else{
					$sql .= " WHERE id_cliente_contacto = {$costumer['detail'][$key]['id_cliente_contacto']}";
					$stm = $this->link->query( $sql ) or die( "Error al actualizar el contacto : {$sql}" );
				}
			}
			return 'ok';
		}
	}
?>
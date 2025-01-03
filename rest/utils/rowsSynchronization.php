<?php
//ok 2023/11/25
	class rowsSynchronization
	{
		private $link;
		function __construct( $connection ){
			$this->link = $connection;
		}
		
//hacer / obtener jsons de registros de sincronziacion
		public function getSynchronizationRows( $system_store, $destinity_store, $limit, $table ){
			$resp = array();
			$sql = "SELECT 
						id_sincronizacion_registro AS synchronization_row_id,
						REPLACE( datos_json, '\r\n', ' ' ) AS data
					FROM {$table}
					WHERE status_sincronizacion IN( 1, 2 )
					AND sucursal_de_cambio = {$system_store}
					AND id_sucursal_destino = {$destinity_store}
					LIMIT {$limit}";
		//die( $sql );
			$stm = $this->link->query( $sql ) or die( "Error al consultar los datos de jsons de registros de sincronizacion on {$table} : {$sql}" );
			$movements_counter = 0;
			//forma arreglo
			while ( $row = $stm->fetch( PDO::FETCH_ASSOC ) ) {
				if( $row['data'] != '' && $row['data'] != null && $row['data'] != 'null' ){
					//reemplaza saltos de linea y caracteres especiales
					$row['data'] = str_replace( "\n", " ", $row['data'] );
					$row['data'] = str_replace( "\r\n", " ", $row['data'] );
					$row['data'] = preg_replace("/[\r\n|\n|\r|\r\n]+/", PHP_EOL, $row['data'] );
					//$row['data'] = str_replace('Ñ', 'N', $row['data'] );
					$row['data'] = trim( $row['data'] );


					$row['data'] = str_replace('"}', '", "synchronization_row_id" : "' . $row['synchronization_row_id'] . '" }', $row['data'] );
			//actualiza el status de sincronizacion a 2 los registros de sincronizacion
					$sql = "UPDATE sys_sincronizacion_registros_facturacion 
								SET status_sincronizacion = 2 
							WHERE id_sincronizacion_registro = {$row['synchronization_row_id']}";
					$stm = $this->link->query( $sql ) or die( "Error al poner registro de sincronizacion en status de enviado (comprobacion) : {$sql} : {$this->link->error}" );
					array_push( $resp, json_decode($row['data'] ) );//decodifica el JSON
					$movements_counter ++;
				}
			}
			//var_dump( $resp );
			return $resp;
		}
//inserción de movimientos
		public function insertRows( $rows ){
  			$resp = array();
			$resp["ok_rows"] = '';
			$resp["error_rows"] = '';

			$resp["tmp_ok"] = "";
			$resp["tmp_no"] = "";
			$queries = array();
			//$this->link->autocommit( false );
			foreach ($rows as $key => $row) {
				//$tmp = json_decode($row);
				//echo $tmp['action_type'];
				$ok = true;
				$sql = "";
				$condition = "";
				if( isset( $row['primary_key'] ) && isset( $row['primary_key_value'] ) ){
					$condition .= "WHERE {$row['primary_key']} = '{$row['primary_key_value']}'";
				}
				if( isset( $row['secondary_key'] ) && isset( $row['secondary_key_value'] ) ){
					$condition .= " AND {$row['secondary_key']} = '{$row['secondary_key_value']}'";
				}

				$condition = str_replace( "'(", "(", $condition );
				$condition = str_replace( ")'", ")", $condition );
				switch ( $row['action_type'] ) {
					case 'insert' :
						$sql = "INSERT INTO {$row['table_name']} ( ";
						$fields = "";
						$values   = "";
						foreach ($row as $key2 => $value) {
							if( $key2 != 'table_name' && $key2 != 'action_type' && $key2 != 'primary_key' 
								&& $key2 != 'primary_key_value' && $key2 != 'secondary_key' 
								&& $key2 != 'secondary_key_value' && $key2 != 'synchronization_row_id' ){
								$fields .= ( $fields == "" ? "" : ", " );
								$fields .= "{$key2}";
								$values .= ( $values == "" ? "" : ", " );
								$values .= "'{$value}'";
							}
						}
						$fields .= " )";
						$sql .=  "{$fields} VALUES ( {$values} )";
						array_push( $queries, $sql );
						if( $row['table_name'] != 'ec_pedidos' && $row['table_name'] != 'ec_pedidos_detalle' ){
							array_push( $queries, "UPDATE {$row['table_name']} SET sincronizar = 0 {$condition}" );
						}
						$resp["ok_rows"] .= ( $resp["ok_rows"] == '' ? '' : ',' ) . "'{$row['synchronization_row_id']}'";
					break;
					case 'update' :
						$sql = "UPDATE {$row['table_name']} SET ";
						$fields = "";
						foreach ($row as $key2 => $value) {
							if( $key2 != 'table_name' && $key2 != 'action_type' && $key2 != 'primary_key' 
								&& $key2 != 'primary_key_value' && $key2 != 'secondary_key' 
								&& $key2 != 'secondary_key_value' && $key2 != 'synchronization_row_id' ){
								$fields .= ( $fields == "" ? "" : ", " );
								$fields .= "{$key2} = '{$value}'";
							}
						}
						$sql .= "{$fields} {$condition}";
						array_push( $queries, $sql );
						$resp["ok_rows"] .= ( $resp["ok_rows"] == '' ? '' : ',' ) . "'{$row['synchronization_row_id']}'";
						//$sql .= ( $row['action_type'] == 'update' ? " WHERE {$row['primary_key']} = '{$row['primary_key_value']}'" : "" );
					break;
					case 'delete' :
						$sql = "DELETE FROM {$row['table_name']} {$condition}";
						$resp["ok_rows"] .= ( $resp["ok_rows"] == '' ? '' : ',' ) . "'{$row['synchronization_row_id']}'";
						array_push( $queries, $sql );
						$resp["ok_rows"] .= ( $resp["ok_rows"] == '' ? '' : ',' ) . "'{$row['synchronization_row_id']}'";
						array_push( $queries, $sql );
					break;

					case 'sql_instruction' : 
						$sql = $row['sql'];
						$resp["ok_rows"] .= ( $resp["ok_rows"] == '' ? '' : ',' ) . "'{$row['synchronization_row_id']}'";
						array_push( $queries, $sql );
					break;
					
					default:
						//var_dump($row);
						die( "JSON incorrecto : {$row['action_type']}" );
					break;
				}
			}
			//$this->link->autocommit(false);
			$this->link->beginTransaction();
			foreach ($queries as $key => $query) {
//die( "here : {$sql}" );
				$query = str_replace( "'(", "(", $query );
				$query = str_replace( ")'", ")", $query );
				//echo $query.'<br>';
				$stm = $this->link->query( $query ) or die( "Error al ejecutar consulta : {$query}" );
				if( !$stm ){
					return array( "error"=>"Error al ejecutar consulta : {$query}" );
				}
			}
			//die( 'here' );
			//$this->link->autocommit(true);
			$this->link->commit();
		    //$this->link->autocommit( true );
			return $resp;
			//return $resp;
		}

//actualizacion de registros de sincronizacion
		public function updateRowSynchronization( $rows, $petition_unique_folio, $table, $status = null, $sum = false ){
			$sql = "";
			if( $status != null ){//actualiza status y folio unico de peticion
				$sql = "UPDATE {$table} 
	              SET status_sincronizacion = '{$status}',
	              	folio_unico_peticion = '{$petition_unique_folio}' 
	            WHERE id_sincronizacion_registro IN( {$rows} )";

	   		}
	   	 	$stm = $this->link->query( $sql ) or die( "Error al actualizar registros de sincronización exitosos : {$sql}" );
		
		}
	}
?>
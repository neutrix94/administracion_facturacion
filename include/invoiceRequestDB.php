<?php
    if( isset( $_POST['action_fl'] ) || isset( $_GET['action_fl'] ) ){
        include( "./db.php" );
	    $db = new db();
	    $link = $db->conectDB();
        $InvoiceRequestDB = new InvoiceRequestDB( $link );
        $action = ( isset( $_POST['action_fl'] ) ? $_POST['action_fl'] : $_GET['action_fl'] );
        switch ($action) {
            case 'getInvoiceRequests':
                $seeker_text = ( isset( $_POST['seeker_text'] ) ? $_POST['seeker_text'] : ( isset( $_GET['seeker_text'] ) ? $_GET['seeker_text'] : NULL ) );
                $store_filter = ( isset( $_POST['store_filter'] ) ? $_POST['store_filter'] : ( isset( $_GET['store_filter'] ) ? $_GET['store_filter'] : -1 ) );
                $social_reason_filter = ( isset( $_POST['social_reason_filter'] ) ? $_POST['social_reason_filter'] : ( isset( $_GET['social_reason_filter'] ) ? $_GET['social_reason_filter'] : -1 ) );
                $status = ( isset( $_POST['status'] ) ? $_POST['status'] : ( isset( $_GET['status'] ) ? $_GET['status'] : -1 ) );
                $limit = ( isset( $_POST['limit'] ) ? $_POST['limit'] : ( isset( $_GET['limit'] ) ? $_GET['limit'] : 50 ) );                
                $page_since = ( isset( $_POST['page_since'] ) ? $_POST['page_since'] : ( isset( $_GET['page_since'] ) ? $_GET['page_since'] : NULL ) );
                $page_to = ( isset( $_POST['page_to'] ) ? $_POST['page_to'] : ( isset( $_GET['page_to'] ) ? $_GET['page_to'] : NULL ) );
                echo $InvoiceRequestDB->getInvoiceRequests( $seeker_text, $store_filter, $social_reason_filter, $status, $limit, $page_since, $page_to );
            break;

            case "getRowsCounter":
                $factor = ( isset( $_POST['factor'] ) ? $_POST['factor'] : ( isset( $_GET['factor'] ) ? $_GET['factor'] : 50 ) );
                echo json_encode( $InvoiceRequestDB->getRowsCounter( $factor ) );
            break;
            
            case 'sendBillPetition':
                $sale_id = ( isset( $_GET['sale_id'] ) ? $_GET['sale_id'] : $_POST['sale_id'] );
                echo $InvoiceRequestDB->sendBillPetition( $sale_id );
            break;

            case 'showBillPetitionDetail':
                $sale_id = ( isset( $_GET['sale_id'] ) ? $_GET['sale_id'] : $_POST['sale_id'] );
                echo json_encode( $InvoiceRequestDB->showBillPetitionDetail( $sale_id ) );
            break;

            default :
                die( "Permission denied on '{$action}'." );
            break;
        }
    }
    class InvoiceRequestDB{
        private $link;
        public function __construct($connection) {
            $this->link = $connection;
        }

        public function showBillPetitionDetail( $sale_id ){
            $resp = array();
        //consulta datos de la nota de venta
            $sql = "SELECT 
                        folio_nv
                    FROM ec_pedidos
                    WHERE id_pedido = {$sale_id}";
            $stm = $this->link->query( $sql ) or die( "Error al consultar el folio" );
            $row = $stm->fetch( PDO::FETCH_ASSOC );
            $sale_folio = $row['folio_nv'];
        //consulta cabeceras de solicitudes de factura
            $sql = "SELECT 
                        id_solicitud_factura, 
                        id_razon_social, 
                        fecha_alta 
                    FROM solicitudes_factura
                    WHERE folio_venta = '{$sale_folio}'";
            $stm = $this->link->query( $sql ) or die( "Error al consultar la cabecera de solicitud de factura : {$sql} : {$this->link->error}" );
            while( $row = $stm->fetch( PDO::FETCH_ASSOC ) ){
                $row['detail'] = array();
            //consulta detalles de solicitudes de factura
                $sql = "SELECT 
                            id_peticion_solicitud_factura,
                            respuesta,
                            detalle_respuesta,
                            fecha_alta 
                        FROM peticiones_solicitud_factura 
                        WHERE id_solicitud_factura = {$row['id_solicitud_factura']}";
                $stm_2 = $this->link->query( $sql ) or die( "Error al consultar detalle de solicitud de factura : {$sql} : {$this->link->error}" );
                while( $row2 = $stm->fetch( PDO::FETCH_ASSOC ) ){
                    array_push( $row['detail'], $row2 );
                }
                array_push( $resp, $row );
            }
            return $resp;
        }

        public function sendBillPetition( $sale_id ){
        //consulta datos de la nota de venta
            $sql = "SELECT 
                        folio_nv,
                        uso_cfdi,
                        id_razon_factura,
                        ( SELECT `value` FROM `api_config` WHERE `key` = 'facturacion' ) AS api_url
                    FROM ec_pedidos
                    WHERE id_pedido = {$sale_id}";
            $stm = $this->link->query( $sql ) or die( "Error al consultar el folio de venta : {$sql} : {$this->link->error}" );
        //
            $row = $stm->fetch( PDO::FETCH_ASSOC );
            $sale_folio = $row['folio_nv'];
            $cfdi_use = $row['uso_cfdi'];
            $sale_costumer = $row['id_razon_factura'];
            $url = "{$row['api_url']}/rest/solicitud_factura";
            $payment_type = 7;
        //forma peticion
            $post_data = json_encode( array( "sale_folio"=>$sale_folio, "cfdi_use"=>$cfdi_use,
            "sale_costumer"=>$sale_costumer, "payment_type"=>$payment_type ) );
//echo( $post_data . " : " . $url );
        //consume api
            $resp = $this->sendPetition( $url, $post_data );
            $resp_json = json_decode( $resp );
            if( trim( $resp_json->message ) == 'La nota de venta ya habia sido facturada.' ){
                $sql = "UPDATE ec_pedidos SET id_status_facturacion = IF( id_status_facturacion <= 8, 8, id_status_facturacion ) WHERE folio_nv = '{$sale_folio}'";
                $stm = $this->link->query( $sql ) or die( "Error al actualizar status de la venta en administracion de facturacion : {$sql}" );
            }
            return $resp;
        }
        public function sendPetition( $url, $post_data, $token = "" ){
			$resp = "";
			$crl = curl_init( $url );
			curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($crl, CURLINFO_HEADER_OUT, true);
			curl_setopt($crl, CURLOPT_POST, true);
			curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data);
			//curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
		    curl_setopt($crl, CURLOPT_TIMEOUT, 60000);
			curl_setopt($crl, CURLOPT_HTTPHEADER, array(
			  'Content-Type: application/json',
			  'token: ' . $token)
			);
			$resp = curl_exec($crl);//envia peticion
			curl_close($crl);
			return $resp;
        }
        public function getRowsCounter( $factor, $seeker_text = null, $store_filter = -1, $social_reason_filter = -1, $status = -1 ){
            $sql = "SELECT
                        COUNT( p.id_pedido ) AS counter_rows
                    FROM ec_pedidos p
                    LEFT JOIN sys_sucursales s
                    ON s.id_sucursal = p.id_sucursal
                    LEFT JOIN razones_sociales rs
                    ON rs.id_razon_social = p.id_razon_social
                    LEFT JOIN vf_clientes_razones_sociales crs
                    ON crs.id_cliente_facturacion = p.id_razon_factura
                    LEFT JOIN ec_status_facturacion st
                    ON st.id_status_facturacion = p.id_status_facturacion
                    WHERE 1";
            $stm = $this->link->query( $sql );
            $row = $stm->fetch( PDO::FETCH_ASSOC );
            $row['pages_number'] = ceil( $row['counter_rows'] / $factor );
            return $row;
        }

        public function getInvoiceRequests( $seeker_text = null, $store_filter = -1, $social_reason_filter = -1, $status = -1, $limit = 50, $page_since = null, $page_to = null ){
            $resp = array();
            $sql = "SELECT
                p.id_pedido AS sale_id,
                p.folio_nv AS sale_folio,
                s.nombre AS store_name,
                rs.nombre AS reason_name,
                crs.rfc AS costumer_rfc,
                p.total AS sale_ammount,
                p.fecha_alta AS sale_date_time,
                st.nombre_status AS status_name
            FROM solicitudes_factura sf
            LEFT JOIN ec_pedidos p
            ON p.folio_nv = sf.folio_venta
            LEFT JOIN sys_sucursales s
            ON s.id_sucursal = p.id_sucursal
            LEFT JOIN razones_sociales rs
            ON rs.id_razon_social = p.id_razon_social
            LEFT JOIN vf_clientes_razones_sociales crs
            ON crs.id_cliente_facturacion = p.id_razon_factura
            LEFT JOIN ec_status_facturacion st
            ON st.id_status_facturacion = p.id_status_facturacion
            WHERE 1";
        //filtro de sucursal
            if( $store_filter != -1 ){
                $sql .= " AND p.id_sucursal = {$store_filter}";
            }
        //filtro de razon social
            if( $social_reason_filter != -1 ){
                $sql .= " AND p.id_razon_social = {$social_reason_filter}";
            }
        //filtro de status
            if( $status != -1 ){
                $sql .= " AND p.id_status_facturacion = {$status}";
            }
            if( $seeker_text != null ){
                $sql .= " AND ( p.folio_nv LIKE '%{$seeker_text}%'";
                $sql .= " OR crs.rfc LIKE '%{$seeker_text}%'";
                $sql .= " OR rs.nombre LIKE '%{$seeker_text}%' )";
            }
        //paginador (desde, limite)
            if( $page_since != null){ //&& $page_to != null 
                $sql .= " LIMIT {$page_since}, {$limit}";
            }
            
            $stm = $this->link->query( $sql ) or die( "Error al consultar las solicitudes de factura : {$sql} : {$this->link->error}" );
            $resp = $this->buildTableRows( $stm );
            return $resp;
            //while( $row = $stm->fetch( PDO::FETCH_ASSOC ) ){
             //   $resp[] = $row;
            //}
            //return json_encode( $resp );
        }

        public function buildTableRows( $stm ){
            $resp = "";
            $c=0;//iniciamos el contador en cero
			while($r = $stm->fetch(PDO::FETCH_ASSOC) ){
				$c++;//incrementamos contador
				$resp .= "<tr tabindex=\"{$c}\">
						<td>{$r['sale_folio']}</td>
						<td>{$r['store_name']}</td>
						<td>{$r['reason_name']}</td>
						<td>{$r['costumer_rfc']}</td>
						<td>{$r['sale_ammount']}</td>
						<td>{$r['sale_date_time']}</td>
						<td>{$r['status_name']}</td>
						<td align=\"center\">
							<button 
								type=\"button\"
								class=\"btn\"
								onclick=\"show_bill_petition_detail( {$r['sale_id']} );\"
							>
								<i class=\"icon-list\"></i>
							</button>
						</td>
						<td align=\"center\">
							<button 
								type=\"button\"
								class=\"btn\"
								onclick=\"bill_petition( {$r['sale_id']} );\"
							>
								<i class=\"icon-bell-5\"></i>
							</button>
						</td>
						<td align=\"center\">
							<button 
								type=\"button\"
								class=\"btn\"
								onclick=\"muestra_datos_RS( {$r['sale_id']}, 1 );\"
							>
								<i class=\"icon-print\"></i>
							</button>
						</td>
						<td align=\"center\">
							<button 
								type=\"button\"
								class=\"btn\"
								onclick=\"muestra_datos_RS( {$r['sale_folio']}, 2 );\"
							>
								<i class=\"icon-email\"></i>
							</button>
						</td>
					</tr>"; 
			}
            return $resp;
        }

        public function getStores(){
            $stores = "";
            $sql = "SELECT
                        id_sucursal,
                        nombre
                    FROM sys_sucursales
                    WHERE id_sucursal >0";
            $stm = $this->link->query( $sql );
            while( $row = $stm->fetch(PDO::FETCH_ASSOC) ){
                $stores .= "<option value=\"{$row['id_sucursal']}\">{$row['nombre']}</option>";
            }
            return $stores;
        }

        public function getSocialReasons(){
            $rss = "";
            $sql = "SELECT
                id_razon_social,
                nombre
            FROM razones_sociales
            WHERE id_razon_social >0";
            $stm = $this->link->query( $sql );
            while( $row = $stm->fetch(PDO::FETCH_ASSOC) ){
                $rss .= "<option value=\"{$row['id_razon_social']}\">{$row['nombre']}</option>";
            }
            return $rss;
        }
        public function getStatus(){
            $status = "";
            $sql = "SELECT
                        id_status_facturacion,
                        nombre_status
                    FROM ec_status_facturacion
                    WHERE id_status_facturacion >0";
            $stm = $this->link->query( $sql );
            while( $row = $stm->fetch(PDO::FETCH_ASSOC) ){
                $status .= "<option value=\"{$row['id_status_facturacion']}\">{$row['nombre_status']}</option>";
            }
            return $status;
        }
    }
?>
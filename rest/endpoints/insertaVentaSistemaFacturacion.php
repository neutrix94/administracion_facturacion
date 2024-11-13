<?php
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    $app->post('/inserta_venta_sistema_facturacion', function (Request $request, Response $response, $args) {
        include( '../include/db.php' );
        $db = new db();
        $link = $db->conectDB();
        //include include( '../php/routes.php' );
        //$Routes = new Routes();
       /*if( ! include( 'utils/SynchronizationManagmentLog.php' ) ){
            die( "No se incluyó : SynchronizationManagmentLog.php" );
        }
        if( ! include( 'utils/facturacion.php' ) ){
            die( "No se incluyó : facturacion.php" );
        }*/
        $body = $request->getBody();
        $req = json_decode($body, true);
        $sale_folio = $req['sale_folio'];
/*Estos se tiene que actualizar al solicitar la factura*/
//$sale_costumer = $req['sale_costumer'];
//$cfdi_use = $req['cfdi_use'];
/**/
    //consulta el status de la venta
        $sql = "SELECT 
                    p.id_status_facturacion, 
                    rs.url_api,
                    p.id_razon_social 
                FROM ec_pedidos p 
                LEFT JOIN razones_sociales rs
                ON rs.id_equivalente = p.id_razon_social
                WHERE p.folio_nv = '{$sale_folio}'";
        $stm = $link->query( $sql ) or die( "Error al consultar el status de facturacion de la venta : {$sql}" );
        $row = $stm->fetch( PDO::FETCH_ASSOC );
        if( $row['id_status_facturacion'] >= 8 ){
        //consulta id de venta y url de facturacion
                
            die( json_encode( array( "status"=>200, "message"=>"La nota de venta ya fue facturada. {$row['url_api']}" ) ) );
        }
//actualiza el cfdi en el sistema de administracion de facturacion {$cfdi_use}
        $sql = "UPDATE ec_pedidos SET uso_cfdi = 2, id_status_facturacion = IF( id_status_facturacion = 3, 4, id_status_facturacion ) 
                WHERE folio_nv = '{$sale_folio}'";
        $stm = $link->query( $sql ) or die( "Error al actualizar el uso de cfdi : {$sql}" );
//consulta los datos de la venta
        $sql = "SELECT 
                    id_pedido, 
                    folio_nv, 
                    id_cliente, 
                    fecha_alta, 
                    subtotal, 
                    iva, 
                    total, 
                    id_sucursal, 
                    id_usuario, 
                    descuento, 
                    id_razon_social,  
                    tipo_pedido, 
                    id_cajero, 
                    folio_unico, 
                    id_sesion_caja, 
                    tipo_sistema, 
                    id_status_facturacion,
                    2 AS cfdi
                FROM ec_pedidos 
                WHERE folio_nv = '{$sale_folio}'
                LIMIT 1";
        $stm = $link->query( $sql ) or die( "Error al consultar cabecera de la nota de venta : {$sql}" );
        $sale_header = $stm->fetch(PDO::FETCH_ASSOC);
//consulta el detalle de venta
        $sql = "SELECT 
                    id_producto, 
                    cantidad, 
                    precio, 
                    monto,
                    folio_unico 
                FROM ec_pedidos_detalle 
                WHERE id_pedido = {$sale_header['id_pedido']}";
        $stm = $link->query( $sql ) or die( "Error al consultar detalle de la nota de venta : {$sql}" );
        $sale_products = array();
        while( $row = $stm->fetch(PDO::FETCH_ASSOC) ){
            $sale_products[] = $row; 
        }
//consulta el detalle de pagos de la venta
        $sql = "SELECT 
                    id_sucursal, 
                    id_cajero, 
                    id_sesion_caja, 
                    id_afiliacion, 
                    id_terminal, 
                    id_banco, 
                    monto, 
                    fecha, 
                    hora, 
                    folio_unico, 
                    id_forma_pago 
                FROM ec_cajero_cobros 
                WHERE id_pedido = {$sale_header['id_pedido']}";
        $stm = $link->query( $sql ) or die( "Error al consultar detalle de la nota de venta : {$sql}" );
        $sale_payments = array();
        while( $row = $stm->fetch(PDO::FETCH_ASSOC) ){
            $sale_payments[] = $row;
        }
    //consulta el apath del api de acuerdo a la razon social
        $sql = "SELECT 
                    url_api
                FROM razones_sociales
                WHERE id_equivalente = {$sale_header['id_razon_social']}";
        //die($sql);
        $stm = $link->query( $sql )or die( "Error al consultar api de sistema destino de facturacion : {$sql}" );//die($sql);
        $row = $stm->fetch(PDO::FETCH_ASSOC);
        $api_path = $row['url_api'];
        $post_data = json_encode( array( 
            "sale_header"=>$sale_header, 
            "sale_products"=>$sale_products, 
            "sale_payments"=>$sale_payments,
            "costumer_rfc"=>$sale_costumer
        ) );
        //echo "{$api_path}/inserta_venta";
        //public function sendPetition( $url, $post_data ){
			$resp = "";
			$crl = curl_init( "{$api_path}/inserta_venta" );
			curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($crl, CURLINFO_HEADER_OUT, true);
			curl_setopt($crl, CURLOPT_POST, true);
			curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data);
			//curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
		    curl_setopt($crl, CURLOPT_TIMEOUT, 60000);
			curl_setopt($crl, CURLOPT_HTTPHEADER, array(
			  'Content-Type: application/json' )
			);
			$resp = curl_exec($crl);//envia peticion
			curl_close($crl);
            //var_dump($resp);
            //die('here : ' . " {$url} " . $resp);
            //$resp = json_decode( $Routes->sendPetition( $api_path, "inserta_venta", $post_data ) );
			//return $resp;
		//}
       // var_dump( $resp );
        $response->getBody()->write( $resp );
        return $response;

        /*$response->getBody()->write(
            json_encode( 
                array( 
                    "respuesta"=>$resp 
                ) 
            )
        );
        return $response;*/
    });
?>
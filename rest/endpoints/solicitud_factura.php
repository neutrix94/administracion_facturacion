<?php
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    $app->post('/solicitud_factura', function (Request $request, Response $response, $args) {
        include( '../include/db.php' );
        $db = new db();
        $link = $db->conectDB();
        $body = $request->getBody();
        $req = json_decode($body, true);
        $sale_folio = $req['sale_folio']; 
        $cfdi_use = $req['cfdi_use'];
        $sale_costumer = $req['sale_costumer'];
    //consulta informacion de nota de venta
        $sql = "SELECT 
                    p.id_status_facturacion, 
                    rs.url_api,
                    p.id_razon_social,
                    p.id_pedido 
                FROM ec_pedidos p 
                LEFT JOIN razones_sociales rs
                ON rs.id_equivalente = p.id_razon_social
                WHERE p.folio_nv = '{$sale_folio}'";
        $stm = $link->query( $sql ) or die( "Error al consultar cabecera de venta : {$sql}" );
        $sale_header = $stm->fetch( PDO::FETCH_ASSOC );
    //consulta si ya tiene una solicitud de factura 
        $id_solicitud_factura = null;
        $id_intento_solicitud_factura = null;
        try{
            $sql = "SELECT
                        id_solicitud_factura
                    FROM solicitudes_factura
                    WHERE folio_venta = '{$sale_folio}'";
            $stm = $link->query( $sql );
            if( $link->rowCount() <= 0 ){
            //inserta el registro de solicitud de factura
                try{
                //actualiza la cabecera de la nota de venta
                    try{
                        $sql = "UPDATE ec_pedidos 
                                SET uso_cfdi = {$cfdi_use}, 
                                id_razon_factura = (SELECT id_cliente_facturacion FROM vf_clientes_razones_sociales WHERE rfc = '{$sale_costumer}' LIMIT 1)
                                WHERE id_pedido = {$sale_header['id_pedido']}";
                        $stm_2 = $link->query( $sql );
                    }catch(PDOException $e){
                        error_log( "Error al actualizar cabecera de nota de venta {$sale_folio} : {$sql} : {$e}" );
                        die( "Error al actualizar cabecera de nota de venta {$sale_folio} : {$sql} : {$e}" );
                    }
                    $sql = "INSERT INTO solicitudes_factura ( id_solicitud_factura, id_razon_social, folio_venta, fecha_alta )
                        VALUES( NULL, {$sale_header['id_razon_social']}, '{$sale_folio}', NOW() )";
                    $stm_insert = $link->query( $sql );
                //recupera el id insertado
                    $id_solicitud_factura = $link->lastInsertId();
                }catch (PDOException $e) {
                    error_log( "Error al insertar solicitud de factura para la nota {$sale_folio} : {$sql}" );
                    die( "Error al insertar solicitud de factura para la nota {$sale_folio} : {$sql}" );            
                }
            }else{
                $row = $stm->fetch( PDO::FETCH_ASSOC );
                $id_solicitud_factura = $row['id_solicitud_factura'];
            }
        }catch (PDOException $e) {
            error_log( "Error al consultar si ya hay una solicitud de factura para la nota : {$sql} : {$e}" );
            die( "Error al consultar si ya hay una dsolicitud de factura para la nota : {$sql} : {$e}" );            
        }
    //Valida que haya una solicitud de factura para la nota de venta
        if( $id_solicitud_factura == null || $id_solicitud_factura == '' || $id_solicitud_factura == 0 ){
            error_log( "No se encontró unsa solicitud de factura valida." );
            die( "No se encontró unsa solicitud de factura valida." );   
        }
    //inserta el intento de solicitud de factura
        try{
            $sql = "INSERT INTO peticiones_solicitud_factura ( id_peticion_solicitud_factura, id_solicitud_factura, respuesta, detalle_respuesta, fecha_alta )
            VALUES( NULL, {$id_solicitud_factura}, '', '', NOW() )";
            $stm_3 = $link->query( $sql );
            $id_intento_solicitud_factura = $link->lastInsertId();
        }catch (PDOException $e) {
            error_log( "Error al insertar intento de solicitud de factura : {$sql} : {$e}" );
            die( "Error al insertar intento de solicitud de factura : {$sql} : {$e}" );
        }
        
    //consulta el path del api de acuerdo a la razon social
        $sql = "SELECT 
                    url_api
                FROM razones_sociales
                WHERE id_equivalente = {$sale_header['id_razon_social']}";
        //die($sql);
        $stm = $link->query( $sql )or die( "Error al consultar api de sistema destino de facturacion : {$sql}" );//die($sql);
        $row = $stm->fetch(PDO::FETCH_ASSOC);
        $api_path = $row['url_api'];
        $post_data = json_encode( array( 
            "sale_folio"=>$sale_folio, 
            "costumer_rfc"=>$sale_costumer,
            "cfdi_use"=>$cfdi_use
        ) );
        //echo "{$api_path}/inserta_venta";
        //public function sendPetition( $url, $post_data ){
			$resp = "";
			$crl = curl_init( "{$api_path}/api/facturacion/genera_factura" );
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
        //actualiza el registro de intento de facturacion
            try{
                $sql = "UPDATE peticiones_solicitud_factura SET respuesta = ''{$resp}'', detalle_respuesta = '{$resp}' WHERE id_peticion_solicitud_factura = {$id_intento_solicitud_factura}";
                $stm = $link->query( $sql ) or die( "Error al" );
            }catch(PDOException $e){
                error_log( "Error al actualizar intento de solicitud de factura : {$sql} : {$e}" );
                die( "Error al actualizar intento de solicitud de factura : {$sql} : {$e}" );
            }
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
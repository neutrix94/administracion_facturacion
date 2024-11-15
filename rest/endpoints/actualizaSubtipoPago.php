<?php
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    $app->post('/actualiza_subtipo_pago', function (Request $request, Response $response, $args) {
        include( '../include/db.php' );
        $db = new db();
        $link = $db->conectDB();
        $body = $request->getBody();
        $req = json_decode($body, true);
        if( ! isset( $req['payment_id'] ) ){
            $response->getBody()->write(json_encode( array( "status"=>"400", "message"=>"Error : No llego ningun id de pago" ) ));
            return $response;
        }
        if( ! isset( $req['payment_subtype'] ) ){
            $response->getBody()->write(json_encode( array( "status"=>"400", "message"=>"Error : No llego ningunasubtipo de pago." ) ));
            return $response;
        }
        $sql = "UPDATE ec_cajero_cobros SET id_forma_pago = {$req['payment_subtype']} WHERE id_cajero_cobro = {$req['payment_id']}";
        $link->query( $sql ) or die( "Error al actualizar la forma de pago : {$sql}" );
    //consume servicio en la razon social correspondiente para ir a actualizar el tipo de pago
        $sql = "SELECT
                    rs.url_api
                FROM ec_cajero_cobros cc
                LEFT JOIN ec_pedidos p
                ON p.id_pedido = cc.id_pedido
                LEFT JOIN razones_sociales rs
                ON rs.id_equivalente = p.id_razon_social
                WHERE cc.id_cajero_cobro = {$req['payment_id']}";
        $rs_api_path = null;
        try{
            $stm = $link->query( $sql );
            if( $stm->rowCount() <= 0 ){
                $response->getBody()->write(json_encode( array( "status"=>"300", "message"=>"No se econtro path para actualizar tipo pago en RS." ) ));
                return $response;
            }
            $row = $stm->fetch( PDO::FETCH_ASSOC );
            $rs_api_path = $row['url_api'];
        }catch(PDOException $e){
            error_log( "Error al consultar endpoint de API para actualizar el API en RS : {$sql} : {$e}" );
            $response->getBody()->write(json_encode( array( "status"=>"300", "message"=>"Error al consultar endpoint de API para actualizar el API en RS : {$sql} : {$e}" ) ));
            return $response;
        }
        if( $rs_api_path = '' || $rs_api_path == null ){
            $response->getBody()->write(json_encode( array( "status"=>"300", "message"=>"No se econtro path para actualizar tipo pago en RS." ) ));
            return $response;
        }else{
    //consume servicio en la razon social correspondiente para ir a actualizar el tipo de pago
            $post_data = json_encode( array( "payment_id"=>$req['payment_id'], "payment_type"=>$req['payment_id']) );
            $resp = "";
			$crl = curl_init( "{$rs_api_path}/actualiza_tipo_pago_razon_social" );
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
            $resp_json = json_decode( $resp, true );
            if( $resp_json['status'] != 200 ){
                $response->getBody()->write( json_encode( array( "status"=>"400", "message"=>$resp_json['message'] ) ) );
                return $response;
            }
        }
        $response->getBody()->write(json_encode( array( "status"=>"200", "message"=>"Forma de pago actualizada exitosamente." ) ));
        return $response;
    });
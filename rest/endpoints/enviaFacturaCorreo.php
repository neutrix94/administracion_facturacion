<?php
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    $app->post('/envia_factura_correo', function (Request $request, Response $response, $args) {
        include( '../include/db.php' );
        $db = new db();
        $link = $db->conectDB();
        $body = $request->getBody();
        $req = json_decode($body, true);
        $sale_folio = $req['sale_folio'];
    //consulta el apath del api de acuerdo a la razon social
        $sql = "SELECT 
                    rs.url_api
                FROM razones_sociales rs
                LEFT JOIN ec_pedidos p
                ON p.id_razon_social = rs.id_equivalente
                WHERE p.folio_nv = '{$sale_folio}'";
        $stm = $link->query( $sql )or die( "Error al consultar api de sistema destino de facturacion : {$sql}" );//die($sql);
        if( $stm->rowCount() <= 0 ){
            die( "No se encontro la venta en el sistema de administración de facturación." );
        }
        $row = $stm->fetch(PDO::FETCH_ASSOC);
        $api_path = $row['url_api'];
        $post_data = json_encode( array( "sale_folio"=>$sale_folio ) );

        //public function sendPetition( $url, $post_data ){
			$resp = "";
			$crl = curl_init( "{$api_path}/envia_correo_factura" );
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
            //die('here : ' . " {$url} " . $resp);
            //$resp = json_decode( $Routes->sendPetition( $api_path, "inserta_venta", $post_data ) );
			//return $resp;
		//}
        //var_dump( $resp );
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
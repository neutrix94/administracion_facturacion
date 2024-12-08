<?php

    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    $app->post('/barrido_ventas_facturacion_por_lote', function (Request $request, Response $response, $args) {
       // die( "here" );
        include( '../include/db.php' );
        $db = new db();
        $link = $db->conectDB();
        $body = $request->getBody();
        $responseFacturacion = array();
        $responseFacturacion["exitosos"] = array();
        $responseFacturacion["fallidos"] = array();
        $date_since = "";
        $date_to = "";
        $rs_id = "-1";
        $sql_condition = "";
        $requestRS = array();


        $enviar_facturacion_directo = false;//variable para indicar que la venta se tiene que enviar.
        $req = json_decode($body, true);
        /*if( ! isset( $req['sales'] ) ){
            $response->getBody()->write(json_encode( array( "status"=>"error", "message"=>"Error : No llego ninguna venta; Se necesita una venta para continuar." ) ));
            return $response;
        }*/
        //$sales = $req['sales'];
/*Implementacion Oscar 2024-12-03 para recibir filtros de RS, fecha desde, fecha hasta*/
        if( isset( $req['date_since'] ) ){
            $date_since = $req['date_since'];
        }
        if( isset( $req['date_to'] ) ){
            $date_to = $req['date_to'];
        }
        if( $date_since != '' && $date_to != '' ){
            $sql_condition .= "AND ( p.fecha_alta BETWEEN '{$date_since} 00:00:01' AND '{$date_to} 23:59:59' )";
        }
        if( isset( $req['rs_id'] ) && $req['rs_id'] != -1 ){
            $rs_id = $req['rs_id'];
            $sql_condition .= ( $sql_condition == "" ? "" : " " );
            $sql_condition .= "AND p.id_razon_social = {$rs_id}";
        }else{
            $sql_condition .= ( $sql_condition == "" ? "" : " " );
            $sql_condition .= "AND p.id_razon_social > 0";
        }
//consulta ventas pendientes de mandar a Razones Sociales (status 4 o menor)
        $sql = "SELECT 
                    DISTINCT( p.id_razon_social ) AS id_razon_social,
                    rs.url_api AS api_url,
                    rs.limite_registros_barrido_ventas AS rows_limit
                FROM ec_pedidos p
                LEFT JOIN razones_sociales rs
                ON p.id_razon_social = rs.id_equivalente
                WHERE p.id_status_facturacion <= 4 
                AND p.id_razon_social > 0 
                {$sql_condition}";//die($sql);
        $stm = $link->query( $sql );// or die( "Error al consultar las ventas pendientes de enviar a Razones sociales : {$sql} : {$link->error}" );
        if( $stm->rowCount() <= 0 ){
            $response->getBody()->write(json_encode( array( "status"=>"200", "message"=>"No hay ventas por enviar con los parametros seleccionados." ) ));
            return $response;
        }else{
        //itera las razones sociales
            while( $RS_row = $stm->fetch( PDO::FETCH_ASSOC ) ){
                //$response->getBody()->write(json_encode( array( "status"=>"200", "message"=>json_encode( $RS_row ) ) ));
                //return $response;
                //return json_encode( $RS_row );
                $sales = array();//arreglo de ventas
            //consulta cabeceras
                $sqlGetVenta = "SELECT 
                                p.id_pedido, 
                                p.folio_nv, 
                                p.id_cliente, 
                                p.fecha_alta, 
                                p.subtotal, 
                                p.iva, 
                                p.total, 
                                p.id_sucursal, 
                                p.id_usuario, 
                                p.descuento, 
                                p.id_razon_social,  
                                p.tipo_pedido, 
                                p.id_cajero, 
                                p.folio_unico, 
                                p.id_sesion_caja, 
                                p.tipo_sistema, 
                                p.id_status_facturacion,
                                2 AS cfdi
                        FROM ec_pedidos p 
                        WHERE p.id_status_facturacion <= 4 
                        AND p.id_razon_social = '{$RS_row['id_razon_social']}'
                        {$sql_condition}
                        LIMIT {$RS_row['rows_limit']}";
                $resultGetVenta = $link->query( $sqlGetVenta );
                //$sale_header = $resultGetVenta->fetch(PDO::FETCH_ASSOC);
            //itera resultados de las ventas
                while( $sale_header = $resultGetVenta->fetch(PDO::FETCH_ASSOC) ){
                //consulta detalle
                    $sqlDetalleVenta = "SELECT 
                            id_producto, 
                            cantidad, 
                            precio, 
                            monto,
                            folio_unico 
                        FROM ec_pedidos_detalle 
                        WHERE id_pedido = {$sale_header['id_pedido']}";
                    $resultDetalleVenta = $link->query( $sqlDetalleVenta );
                    $sale_products = array();
                    while( $row = $resultDetalleVenta->fetch(PDO::FETCH_ASSOC) ){
                        $sale_products[] = $row; 
                    }
                //consulta pagos
                    $sqlDetallePagosVenta = "SELECT 
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
                        IF( id_forma_pago = 1, 1, 14 ) AS id_forma_pago,
                        id_cajero_cobro 
                    FROM ec_cajero_cobros 
                    WHERE id_pedido = {$sale_header['id_pedido']}";
                    $resultDetallePagosVenta = $link->query( $sqlDetallePagosVenta );
                    $sale_payments = array();
                    while( $row = $resultDetallePagosVenta->fetch(PDO::FETCH_ASSOC) ){
                        $sale_payments[] = $row;
                    }
                    $sale = array( 
                            "sale_header"=>$sale_header, 
                            "sale_products"=>$sale_products, 
                            "sale_payments"=>$sale_payments
                    );
                    $sales[] = $sale;//agrega venta al arreglo que se va a enviar la razon social
                    $post_data = json_encode( array( "sales"=>$sales ) );//forma JSON
                }
                if( sizeof( $sales ) > 0 ){
                //envia datos a servicio de la razon social
                    $crl = curl_init( "{$RS_row['api_url']}/api/facturacion/inserta_ventas_por_lote" );
                    curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($crl, CURLINFO_HEADER_OUT, true);
                    curl_setopt($crl, CURLOPT_POST, true);
                    curl_setopt($crl, CURLOPT_POSTFIELDS, json_encode($arrayVentasParaRazonesSociales));
                    //curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
                    curl_setopt($crl, CURLOPT_TIMEOUT, 60000);
                    curl_setopt($crl, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json' )
                    );
                    $resp = curl_exec($crl);//envia peticion
                    curl_close($crl);

                    $responseRazonSocial = json_decode(trim($resp), true);
                //actualiza los registros exitosos
                    
                        foreach ($responseRazonSocial['exitosos'] as $key => $folio ) {
                            try{
                                $sql = "UPDATE ec_pedidos SET id_status_facturacion = 5 WHERE folio_nv = '{$folio}'";
                                $update_stm = $link->query( $sql );
                            }catch( PDOException $error ){
                                $response->getBody()->write(json_encode( array( "status"=>"400", "message"=>"Error al actualizar registro exitoso : {$sql} : {$error}" ) ));
                                return $response;
                            }
                        }
                }
                $response->getBody()->write(json_encode( array( "status"=>"200", "message"=>array( "rs"=>$RS_row, "sales"=>$sales ) ) ));
                return $response;
            }

        }

        $response_mssg = array(
            "status" => "200",
            "exitosos" => $responseFacturacion['exitosos'],
            "fallidos" => $responseFacturacion['fallidos'],
            //"razones" => $requestRS
        );
    

        //$response->getBody()->write(json_encode( $response_mssg ));
        $response->getBody()->write(json_encode( $response_mssg ));
        //$rs->successMessage($request->getParsedBody(),$response, $response_mssg);
        return $response;
    });
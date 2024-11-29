<?php
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    $app->post('/inserta_venta_facturacion', function (Request $request, Response $response, $args) {
        include( '../include/db.php' );
        $db = new db();
        $link = $db->conectDB();
        $body = $request->getBody();
        $responseFacturacion = array();
        $responseFacturacion["exitosos"] = array();
        $responseFacturacion["fallidos"] = array();
        $requestRS = array();

        $enviar_facturacion_directo = false;//variable para indicar que la venta se tiene que enviar.
        $req = json_decode($body, true);
        if( ! isset( $req['sales'] ) ){
            $response->getBody()->write(json_encode( array( "status"=>"error", "message"=>"Error : No llego ninguna venta; Se necesita una venta para continuar." ) ));
            return $response;
        }

        $sales = $req['sales'];

        if( count($sales) == 0 ){
            $response->getBody()->write(json_encode( array( "status"=>"error", "message"=>"Error : Al menos debe existir una venta." ) ));
            return $response;
        }

        //Recorremos el arreglo de ventas pendientes
        for ($i=0; $i < count( $sales ) ; $i++) {
            //$link->beginTransaction();
            if( $sales[$i]['venta']['id_razon_social'] !== '-1' ){

                $id_razon_social = $sales[$i]['venta']['id_razon_social'];
            }
            $folio_nv = $sales[$i]['venta']['folio_nv'];

            if (!isset($requestRS['razonesSociales'][$id_razon_social])) {
                $requestRS['razonesSociales'][$id_razon_social] = [];
            }

            $requestRS['razonesSociales'][$id_razon_social][] = ["folio_nv" => $folio_nv];

            $venta = $sales[$i]["venta"];

            $arrayFolios = array();
            array_push( $arrayFolios,  $sales[$i]["venta"]['folio_nv'] );

            $sqlInsertPedidos = "INSERT INTO ec_pedidos ( `folio_pedido`, `folio_nv`, `folio_factura`, `folio_cotizacion`, `id_cliente`, `id_estatus`, `id_moneda`, 
                    `fecha_alta`, `id_razon_social`, `subtotal`, `iva`, `ieps`, `total`, 
                    `pagado`, `surtido`, `enviado`, `id_sucursal`, `id_usuario`, `fue_cot`, `facturado`, `id_tipo_envio`, 
                    `descuento`,  `folio_abono`, `correo`, `facebook`, `modificado`, 
                    `ultima_modificacion`, `tipo_pedido`, `id_status_agrupacion`, `id_cajero`, `id_devoluciones`, `venta_validada`, 
                    `folio_unico`, `id_sesion_caja`, `tipo_sistema`, `monto_pago_inicial`, `cobro_finalizado`, id_status_facturacion )
                VALUES( '{$venta['folio_pedido']}', '{$venta['folio_nv']}', '{$venta['folio_factura']}', '{$venta['folio_cotizacion']}', 
                    '{$venta['id_cliente']}', '{$venta['id_estatus']}', '{$venta['id_moneda']}', '{$venta['fecha_alta']}', 
                    '{$venta['id_razon_social']}', 
                    '{$venta['subtotal']}', '{$venta['iva']}', '{$venta['ieps']}', '{$venta['total']}',  
                    '{$venta['pagado']}', '{$venta['surtido']}', '{$venta['enviado']}', '{$venta['id_sucursal']}', '{$venta['id_usuario']}', 
                    '{$venta['fue_cot']}', '{$venta['facturado']}', '{$venta['id_tipo_envio']}', '{$venta['descuento']}', 
                    '{$venta['folio_abono']}', '{$venta['correo']}', '{$venta['facebook']}', 
                    '{$venta['modificado']}', '{$venta['ultima_modificacion']}', '{$venta['tipo_pedido']}', 
                    '{$venta['id_status_agrupacion']}', '{$venta['id_cajero']}', '{$venta['id_devoluciones']}', '{$venta['venta_validada']}', 
                    '{$venta['folio_unico']}', '{$venta['id_sesion_caja']}', '{$venta['tipo_sistema']}', '{$venta['monto_pago_inicial']}', 
                    '{$venta['cobro_finalizado']}', 3 )";

            //error_log("SQL INSERT ec_pedidos");
            //error_log( $sqlInsertPedidos );
            $resultInsertPedidos = $link->query($sqlInsertPedidos);
            //$link->query( $sql ) or die( "Error al insertar cabecera de movimiento de almacen : {$sql}" );
            //ToDo: Agregar $resultInsertPedidos
            if( !$resultInsertPedidos ){
                //Si hay fallo agregamos a arreglo de fallos
                $arrPedidoFallo = array( "folio_pedido" => $venta['id_pedido'], "errorMessage" => "Error al insertar cabecera de movimiento de almacen" );

                array_push( $responseFacturacion['fallidos'], $arrPedidoFallo );
            }else{
                
                $sqlGetLastId = "SELECT LAST_INSERT_ID() AS last_id";
                $stm = $link->query( $sqlGetLastId );
                $row = $stm->fetch();
                $sale_id = $row['last_id'];

                //Se inserta detalle de la venta
                $detalles =  $sales[$i]["venta_detalle"];

                
                //array_push( $responseFacturacion['exitosos'],  $venta['id_pedido'] );
                foreach ($detalles as $key => $detalle) {
                    $sqlInsertPedidosDetalle = "INSERT INTO ec_pedidos_detalle ( `id_pedido`, `id_producto`, `cantidad`, `precio`, `monto`, `iva`, `ieps`, `cantidad_surtida`, 
                            `descuento`, `modificado`, `es_externo`, `id_precio`, `folio_unico` )
                        VALUES ( {$sale_id}, '{$detalle['id_producto']}', '{$detalle['cantidad']}', '{$detalle['precio']}', '{$detalle['monto']}', '{$detalle['iva']}', 
                        '{$detalle['ieps']}', '{$detalle['cantidad_surtida']}', '{$detalle['descuento']}', '{$detalle['modificado']}', '{$detalle['es_externo']}', 
                        '{$detalle['id_precio']}', '{$detalle['folio_unico']}' )";

                    //error_log("SQL INSERT ec_pedidos_detalle del pedido ".$venta['id_pedido']);
                    //error_log( $sqlInsertPedidosDetalle );

                    $resultInsertDetalle = $link->query( $sqlInsertPedidosDetalle );
                    //$resultInsertDetalle = $link->query( $sql ) or die( "Error al insertar detalle de venta : {$sql}" );

                    //!$resultInsertDetalle
                    if( !$resultInsertDetalle ){
                        $arrPedidoDetalleFallo = array( "folio_pedido" => $venta['id_pedido'], "errorMessage" => "Error al insertar detalle de venta id_pedido_detalle ".$detalle['id_pedido_detalle'] );

                        array_push( $responseFacturacion['fallidos'], $arrPedidoDetalleFallo );
                    }
                }

                //Inserta cobros de la venta
                $cobros = $sales[$i]['cobros'];
                foreach ($cobros as $key => $cobro) {
                    $sqlInsertCobros = "INSERT INTO ec_cajero_cobros ( `id_sucursal`, `id_pedido`, `id_devolucion`, `id_cajero`, `id_sesion_caja`, `id_afiliacion`, 
                                `id_terminal`, `id_banco`, `id_tipo_pago`, `monto`, `fecha`, `hora`, `observaciones`, `cobro_cancelado`, `folio_unico`, 
                                `id_forma_pago`, `sincronizar` )
                        VALUES ( '{$cobro['id_sucursal']}', {$sale_id}, '{$cobro['id_devolucion']}', '{$cobro['id_cajero']}', '{$cobro['id_sesion_caja']}', 
                        '{$cobro['id_afiliacion']}', '{$cobro['id_terminal']}', '{$cobro['id_banco']}', '{$cobro['id_tipo_pago']}', '{$cobro['monto']}', 
                        '{$cobro['fecha']}', '{$cobro['hora']}', '{$cobro['observaciones']}', '{$cobro['cobro_cancelado']}', '{$cobro['folio_unico']}', 
                        IF( '{$cobro['id_forma_pago']}' = '1', 1, 14 ), 1 )";

                        //error_log("SQL INSERT ec_cajero_cobros del pedido ".$venta['id_pedido']);
                        //error_log( $sqlInsertCobros );
                    
                    $resultInsertCobros = $link->query( $sqlInsertCobros );

                    if( $cobro['id_tipo_pago'] == 7 ){//si encuentra pago con tarjeta, se envía a razón social
                        $enviar_facturacion_directo = true;


                        if( $venta['id_razon_social'] !== '-1' ){
                            
                            //Agregamos al paquete de arreglos que se envían a las razones sociales
                            //$requestRS[$venta['id_razon_social']][]= $arrayFolios;
                            //$requestRS['razonesSociales'][$id_razon_social][] = ["folio_nv" => $folio_nv];
                            $folios_actuales = array_column($requestRS['razonesSociales'][$id_razon_social], 'folio_nv');
                            if (!in_array($folio_nv, $folios_actuales)) {
                                $output['razonesSociales'][$id_razon_social][] = ["folio_nv" => $folio_nv];
                            }
                        }

                    }
                    if( !$resultInsertCobros ){

                        $arrPedidoCobroFallo = array( "folio_pedido" => $venta['id_pedido'], "errorMessage" => "Error al insertar cobro de la venta id_cajero_cobro ".$cobro['id_cajero_cobro'] );

                        array_push( $responseFacturacion['fallidos'], $arrPedidoCobroFallo );

                    }
                }

                //Inserta pagos de la venta
                $pagos = $sales[$i]['pagos'];
                foreach ($pagos as $key => $pago) {
                    $sqlInsertPagos = "INSERT INTO ec_pedido_pagos ( `id_pedido`, `id_cajero_cobro`, `id_tipo_pago`, `fecha`, `hora`, `monto`, `referencia`, 
                                `id_moneda`, `tipo_cambio`, `id_nota_credito`, `id_cxc`, `exportado`, `es_externo`, `id_cajero`, `folio_unico`, 
                                `sincronizar`, `id_sesion_caja`, `pago_cancelado` )
                        VALUES ( {$sale_id}, '{$pago['id_cajero_cobro']}', '{$pago['id_tipo_pago']}', '{$pago['fecha']}', '{$pago['hora']}', '{$pago['monto']}',
                        '{$pago['referencia']}', '{$pago['id_moneda']}', '{$pago['tipo_cambio']}', '{$pago['id_nota_credito']}', '{$pago['id_cxc']}', 
                        '{$pago['exportado']}', '{$pago['es_externo']}', '{$pago['id_cajero']}', '{$pago['folio_unico']}', '1', '{$pago['id_sesion_caja']}', 
                        '{$pago['pago_cancelado']}' )";
                    //$stm = $link->query( $sql ) or die( "Error al insertar pagos de venta : {$sql}" );

                    $resultInsertPagos = $link->query( $sqlInsertPagos );
                    //error_log("SQL INSERT ec_pedido_pagos del pedido ".$venta['id_pedido']);
                    //error_log( $sqlInsertPagos );
                }

                array_push( $responseFacturacion['exitosos'],  $venta['folio_nv'] );
            }
        }

        //Una vez insertados los registros en el sistema, se procede a generar los paquetes para enviar a las respectivas Razones sociales
        //error_log( print_r($requestRS,true) );
        //error_log( "Se armaron: ". count($requestRS["razonesSociales"]) );

        if( count($requestRS["razonesSociales"]) > 0 ){

            $arrayRazonesSocialesRequest = array();
            foreach ($requestRS["razonesSociales"] as $idRazonSocial => $folios) {
                //error_log( print_r( $folios,true ) );
                
                $arrayVentasParaRazonesSociales = array();
                $arrayVentasParaRazonesSociales['sales'] = array();
                if( count($folios) > 0 ){
                    for ($i=0; $i < count($folios); $i++) {
                        $folio = $folios[$i]["folio_nv"];

                        
                        $sqlStatusVenta =  "SELECT 
                            p.id_status_facturacion, 
                            rs.url_api,
                            p.id_razon_social 
                        FROM ec_pedidos p 
                        LEFT JOIN razones_sociales rs
                        ON rs.id_equivalente = p.id_razon_social
                        WHERE p.folio_nv = '{$folio}'";

                        $resultStatusVenta = $link->query( $sqlStatusVenta );

                        $rowStatusVenta = $resultStatusVenta->fetch( PDO::FETCH_ASSOC );

                        if( $rowStatusVenta['id_status_facturacion'] >= 8 ){
                            error_log( "La nota ya fue facturada" );
                            $arrPedidoFacturado = array( "folio_pedido" => $folio, "errorMessage" => "Error al insertar registro, la nota ya fue facturada" );

                            array_push( $responseFacturacion['fallidos'], $arrPedidoFacturado );
                        }
                        
                        
                        //error_log( "SQL_STATUS_VENTA" );
                        //error_log( $sqlStatusVenta );
                        //Actualizamos a enviado (status = 4)
                        $sqlUpdateCFDI = "UPDATE ec_pedidos SET uso_cfdi = 2, id_status_facturacion = IF( id_status_facturacion = 3, 4, id_status_facturacion ) 
                        WHERE folio_nv = '{$folio}'";
                        $stm = $link->query( $sqlUpdateCFDI );
                        
                        //Consulta datos de la venta (header)
                        
                        $sqlGetVenta = "SELECT 
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
                            WHERE folio_nv = '{$folio}'
                            LIMIT 1";

                        $resultGetVenta = $link->query( $sqlGetVenta );

                        $sale_header = $resultGetVenta->fetch(PDO::FETCH_ASSOC);

                        //consulta el detalle de venta
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
                        
                        //consulta el detalle de pagos de la venta
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

                        $post_data = array( 
                                "sale_header"=>$sale_header, 
                                "sale_products"=>$sale_products, 
                                "sale_payments"=>$sale_payments,
                        );

                        array_push( $arrayVentasParaRazonesSociales['sales'], $post_data );
                        
                    }//Fin for recorrido de ventas
                    
                    //Obtenemos la url a donde se enviará
                    $sqlGetApiPath = "SELECT 
                            url_api
                            FROM razones_sociales
                            WHERE id_equivalente = {$idRazonSocial}";
                    $resultApiPath = $link->query( $sqlGetApiPath );
                    $row = $resultApiPath->fetch(PDO::FETCH_ASSOC);
                    $api_path = $row['url_api'];
                    error_log( "Enviamos petición a razones sociales" );
                    error_log( "{$api_path}/api/facturacion/inserta_ventas_por_lote" );
                    error_log( print_r($arrayVentasParaRazonesSociales,true) );

                    
                    $crl = curl_init( "{$api_path}/api/facturacion/inserta_ventas_por_lote" );
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
                    error_log( "Respuesta de la razón social" );
                    //error_log( print_r( $resp,true ) );
                    //error_log( print_r( $responseRazonSocial,true ) );
                    error_log( "Respuesta de la razón json_decode" );
                    //error_log( print_r( $responseRazonSocial ) );
                    
                    if( $responseRazonSocial !== null ){
                        if( $responseRazonSocial['status'] == '200' ){
                        //únicamente actualizamos los registros exitosos
                        $exitosos = $responseRazonSocial['exitosos'];

                            if( count( $exitosos ) > 0 ){
                            
                                for ($i=0; $i < count($exitosos) ; $i++) { 
    
                                    $folio_exitoso = $exitosos[$i];
                                    $sqlUpdateInsertadoEnRazonSocial = "UPDATE ec_pedidos SET id_status_facturacion = 5 WHERE folio_nv = '{$folio_exitoso}'";
    
                                    error_log( "Actualizamos ec_pedidos indicando inserción en Razón Social" );
                                    error_log( $sqlUpdateInsertadoEnRazonSocial );
    
                                    $resultUpdateInsertadoEnRazonSocial = $link->query( $sqlUpdateInsertadoEnRazonSocial );
                                }

                            }
                        }    
                    }else{
                        error_log("NO SE CONVIRTIÓ JSON_DECODE");
                        error_log($resp['status']);
                    }
                    

                    
                    

                }//fin de los folios


            }//fin foreach de las razones sociales

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
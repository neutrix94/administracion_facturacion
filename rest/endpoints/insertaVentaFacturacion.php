<?php
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    $app->post('/inserta_venta_facturacion', function (Request $request, Response $response, $args) {
        include( '../include/db.php' );
        $db = new db();
        $link = $db->conectDB();
        $body = $request->getBody();
        $enviar_facturacion_directo = false;//variable para indicar que la venta se tiene que enviar.
        $req = json_decode($body, true);
        if( ! isset( $req['venta'] ) ){
            $response->getBody()->write(json_encode( array( "status"=>"400", "message"=>"Error : No llego ninguna venta; Se necesita una venta para continuar." ) ));
            return $response;
        }
        //var_dump( $req );//die('');//ode($body, true);
        $link->beginTransaction();
        $venta = $req['venta'];//`dias_proximo`'{$venta['dias_proximo']}',`id_razon_factura`,'{$venta['id_razon_factura']}',
    //inserta la cabecera del movimiento de almacen `fecha_factura`,'{$venta['fecha_factura']}',  `ultima_sincronizacion`, '{$venta['ultima_sincronizacion']}',
        //`id_direccion`,'{$venta['id_direccion']}',  `direccion`,'{$venta['direccion']}',
        $sql = "INSERT INTO ec_pedidos ( `folio_pedido`, `folio_nv`, `folio_factura`, `folio_cotizacion`, `id_cliente`, `id_estatus`, `id_moneda`, 
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
        $link->query( $sql ) or die( "Error al insertar cabecera de movimiento de almacen : {$sql}" );
    //recupera id inserado
        $sql = "SELECT LAST_INSERT_ID() AS last_id";
        $stm = $link->query( $sql ) or die( "Error al consultar id de cabecera de venta insertada : {$sql}" );
        $row = $stm->fetch();
        $sale_id = $row['last_id'];
    //inserta el detalle de la venta
        $detalles = $req['venta_detalle'];
        foreach ($detalles as $key => $detalle) {
            $sql = "INSERT INTO ec_pedidos_detalle ( `id_pedido`, `id_producto`, `cantidad`, `precio`, `monto`, `iva`, `ieps`, `cantidad_surtida`, 
					`descuento`, `modificado`, `es_externo`, `id_precio`, `folio_unico` )
                VALUES ( {$sale_id}, '{$detalle['id_producto']}', '{$detalle['cantidad']}', '{$detalle['precio']}', '{$detalle['monto']}', '{$detalle['iva']}', 
                '{$detalle['ieps']}', '{$detalle['cantidad_surtida']}', '{$detalle['descuento']}', '{$detalle['modificado']}', '{$detalle['es_externo']}', 
                '{$detalle['id_precio']}', '{$detalle['folio_unico']}' )";
            $stm = $link->query( $sql ) or die( "Error al insertar detalle de venta : {$sql}" );
        }
    //inserta cobros de la venta
        $cobros = $req['cobros'];
        foreach ($cobros as $key => $cobro) {
            $sql = "INSERT INTO ec_cajero_cobros ( `id_sucursal`, `id_pedido`, `id_devolucion`, `id_cajero`, `id_sesion_caja`, `id_afiliacion`, 
						`id_terminal`, `id_banco`, `id_tipo_pago`, `monto`, `fecha`, `hora`, `observaciones`, `cobro_cancelado`, `folio_unico`, 
						`id_forma_pago`, `sincronizar` )
                VALUES ( '{$cobro['id_sucursal']}', {$sale_id}, '{$cobro['id_devolucion']}', '{$cobro['id_cajero']}', '{$cobro['id_sesion_caja']}', 
                '{$cobro['id_afiliacion']}', '{$cobro['id_terminal']}', '{$cobro['id_banco']}', '{$cobro['id_tipo_pago']}', '{$cobro['monto']}', 
                '{$cobro['fecha']}', '{$cobro['hora']}', '{$cobro['observaciones']}', '{$cobro['cobro_cancelado']}', '{$cobro['folio_unico']}', 
                IF( '{$cobro['id_forma_pago']}' = '1', 1, 14 ), 1 )";
            $stm = $link->query( $sql ) or die( "Error al insertar cobro de venta : {$sql}" );
            if( $cobro['id_tipo_pago'] == 7 ){//si encuentra pago con tarjeta
                $enviar_facturacion_directo = true;
            }
        }
    //inserta pagos de la venta
        $pagos = $req['pagos'];
        foreach ($pagos as $key => $pago) {
            $sql = "INSERT INTO ec_pedido_pagos ( `id_pedido`, `id_cajero_cobro`, `id_tipo_pago`, `fecha`, `hora`, `monto`, `referencia`, 
						`id_moneda`, `tipo_cambio`, `id_nota_credito`, `id_cxc`, `exportado`, `es_externo`, `id_cajero`, `folio_unico`, 
						`sincronizar`, `id_sesion_caja`, `pago_cancelado` )
                VALUES ( {$sale_id}, '{$pago['id_cajero_cobro']}', '{$pago['id_tipo_pago']}', '{$pago['fecha']}', '{$pago['hora']}', '{$pago['monto']}',
                '{$pago['referencia']}', '{$pago['id_moneda']}', '{$pago['tipo_cambio']}', '{$pago['id_nota_credito']}', '{$pago['id_cxc']}', 
                '{$pago['exportado']}', '{$pago['es_externo']}', '{$pago['id_cajero']}', '{$pago['folio_unico']}', '1', '{$pago['id_sesion_caja']}', 
                '{$pago['pago_cancelado']}' )";
            $stm = $link->query( $sql ) or die( "Error al insertar pagos de venta : {$sql}" );
        }
        $link->commit();
    //envia la nota de venta a la razon social para su facturacion
        if( $enviar_facturacion_directo == true ){
            $bill_api_path = "http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
            $bill_api_path = str_replace( '/inserta_venta_facturacion', '', $bill_api_path );     
            $resp = "";
            $post_data = json_encode( array( "sale_folio"=>$venta['folio_nv'] ) );
            $crl = curl_init( "{$bill_api_path}/inserta_venta_sistema_facturacion" );
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
        }
        $response->getBody()->write(json_encode( array( "status"=>"200" ) ));
        return $response;
    });
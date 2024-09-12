<?php
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    $app->post('/busca_ventas_por_folio', function (Request $request, Response $response, $args) {
        include( '../include/db.php' );
        $db = new db();
        $link = $db->conectDB();
        $body = $request->getBody();
        $req = json_decode($body, true);
        $sale_folio = $req['folio'];
    //busca venta por folio
        $sql = "SELECT 
                    id_pedido AS sale_id, 
                    folio_nv AS folio, 
                    total AS sale_ammount, 
                    id_sucursal AS store_id,
                    folio_unico AS unique_folio 
                FROM ec_pedidos 
                WHERE folio_nv = '{$sale_folio}'";
        $stm = $link->query( $sql ) or die( "Error al consultar si la venta existe : {$sql}" );
        if( $stm->rowCount() <= 0 ){
            $response->getBody()->write(json_encode( array( "status"=>"200", "was_found"=>"no", "message"=>"La venta con el folio '{$sale_folio}' no fue encontrada." ) ));
            return $response;
        }
        $sale = array();
        $sale_tmp = $stm->fetch();
        foreach ($sale_tmp as $key => $value) {
            if( ! is_numeric( $key ) ){
                $sale[$key] = $value;
            }
        }
    //consulta los detalle de venta
        $sql = "SELECT
                    id_producto AS product_id,
                    cantidad AS quantity,
                    precio AS price,
                    monto AS ammount,
                    es_externo AS is_external,
                    id_precio AS price_id,
                    folio_unico AS unique_folio
                FROM ec_pedidos_detalle
                WHERE id_pedido = {$sale['sale_id']}";
        $stm = $link->query( $sql ) or die( "Error al consultar los detalle de productos de la nota de venta : {$sql}" );
        $sale_products = array();
        while ( $products_tmp = $stm->fetch() ) {
            $products = array();
            foreach ($products_tmp as $key => $value) {
                if( ! is_numeric( $key ) ){
                    $payment[$key] = $value;
                }
            }
            $sale_products[] = $payment;
        }
    //consulta los cobros de cajero
        $sql = "SELECT
                    cc.id_cajero_cobro AS payment_id,
                    cc.id_cajero AS user_id,
                    cc.id_sesion_caja AS session_id,
                    cc.id_afiliacion AS afiliation_id,
                    cc.id_terminal AS terminal_id,
                    cc.id_tipo_pago AS payment_type_id,
                    tp.nombre AS payment_type_name,
                    cc.monto AS ammount,
                    cc.fecha AS payment_date,
                    cc.hora AS payment_time,
                    cc.folio_unico AS unique_folio,
                    cc.id_forma_pago AS payment_subtype
                FROM ec_cajero_cobros cc
                LEFT JOIN ec_tipos_pago tp
                ON tp.id_tipo_pago = cc.id_tipo_pago
                WHERE cc.id_pedido = {$sale['sale_id']}";
        $stm = $link->query( $sql ) or die( "Error al consultar los cobros de la nota de venta : {$sql}" );
        $sale_payments = array();
        while ( $payments_tmp = $stm->fetch() ) {
            $payment = array();
            foreach ($payments_tmp as $key => $value) {
                if( ! is_numeric( $key ) ){
                    $payment[$key] = $value;
                }
            }
            $sale_payments[] = $payment;
        }
        $response->getBody()->write( json_encode( array( "status"=>"200", "was_found"=>"yes", "sale"=>$sale, "sale_products"=>$sale_products, "sale_payments"=>$sale_payments ) ) );
        return $response;
    });
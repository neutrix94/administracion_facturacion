<?php

//require 'vendor/autoload.php';

//use Slim\Factory\AppFactory;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

//$app = AppFactory::create();

// Ruta para recibir parámetros id y fecha
$app->post('/devolucion', function (Request $request, Response $response) {
    include( '../include/db.php' );
    $db = new db();
    $link = $db->conectDB();
//libreria de Log de Sincronizacion
    if( ! include( 'utils/SynchronizationManagmentLog.php' ) ){
        die( "No se incluyó : SynchronizationManagmentLog.php" );
    }
    $SynchronizationManagmentLog = new SynchronizationManagmentLog( $link );//instancia clase de Peticiones Log
//recibe parametros
    $body = $request->getBody();
    $req = json_decode($body, true);
    $folio = (isset($req["folio_nv"]) ? $req["folio_nv"] : null);
    $subtotal = (isset($req["subtotal"]) ? $req["subtotal"] : null);
    $total = (isset($req["total"]) ? $req["total"] : null);
    $descuento = (isset($req["descuento"]) ? $req["descuento"] : null);
    $detalle = (isset($req["detail"]) ? $req["detail"] : array());
    if($folio == "" || $folio == null){
        $payload = json_encode(array("status"=>400,"Message"=>"Faltan atributos para la devolucion en Administracion de facturacion."));        
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
//consulta que la nota de venta no haya sido facturada
    $stm = null;
    $sql = "SELECT id_status_facturacion, id_pedido FROM ec_pedidos WHERE folio_nv = '{$folio}'";
    try{
        $stm = $link->query($sql);
    }catch(PDOException $error){
        $payload = json_encode(array("status"=>400, "message"=>"Error al consultar status de la venta en facturacion.", "sql"=>"{$sql}", "error_detail"=>"{$error->getMessage()}"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
    if($stm->rowCount() <= 0){
        $payload = json_encode(array("status"=>400, "message"=>"La venta {$folio} no fue encontrada en Administracion de Facturación."));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }else{
        $row = $stm->fetch(PDO::FETCH_ASSOC);
        $sale_id = $row['id_pedido'];
        if($row['id_status_facturacion'] == 8){
            $payload = json_encode(array("status"=>200, "message"=>"La venta {$folio} ya fue facturada en la Razón Social."));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        }else{
            $link->beginTransaction();
        //actualiza la venta en administracion de facturacion
            $sql = "UPDATE ec_pedidos SET subtotal = {$subtotal}, total = {$total}, descuento = {$descuento} WHERE folio_nv = '{$folio}'";
            try{
                $link->query($sql);
            }catch(PDOException $error){
                $link->rollBack();
                $payload = json_encode(array("status"=>400, "message"=>"Error al actualizar cabecera de la venta en administracion facturacion.", "sql"=>"{$sql}", "error_detail"=>"{$error->getMessage()}"));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
        /*elimina el detalle anterior
            $sql = "DELETE FROM ec_pedidos_detalle WHERE id_pedido = {$sale_id}";
            try{
                $link->query($sql);
            }catch(PDOException $error){
                $link->rollBack();
                $payload = json_encode(array("status"=>400, "message"=>"Error al eliminar detalle anterior de la venta en administracion facturacion.", "sql"=>"{$sql}", "error_detail"=>"{$error->getMessage()}"));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
        */
        //actualiza el detalle de la venta anterior
            foreach ($detalle as $key => $detalle_venta) {
                /*$sql = "INSERT INTO `ec_pedidos_detalle`(`id_pedido`, `id_producto`, `cantidad`, `precio`, `monto`, `precio_facturacion`, `monto_facturacion`, `iva`, `ieps`, `cantidad_surtida`, `descuento`, `modificado`, `es_externo`, `id_precio`, `folio_unico`) 
                VALUES ({$sale_id}, {$detalle_venta['id_producto']}, {$detalle_venta['cantidad']}, {$detalle_venta['precio']}, {$detalle_venta['monto']}, {$detalle_venta['precio_facturacion']}, {$detalle_venta['monto_facturacion']}, 0, 0, 0, 0, 0, 
                {$detalle_venta['es_externo']}, {$detalle_venta['id_precio']}, '{$detalle_venta['folio_unico']}')";*/
                $sql = "UPDATE ec_pedidos_detalle SET cantidad = {$detalle_venta['cantidad']}, precio = {$detalle_venta['precio']}, monto = {$detalle_venta['monto']}, 
                        precio_facturacion = {$detalle_venta['precio_facturacion']}, monto_facturacion = {$detalle_venta['monto_facturacion']} 
                        WHERE folio_facturacion = '{$detalle_venta['folio_facturacion']}'";
                try{
                    $link->query($sql);
                }catch(PDOException $error){
                    $link->rollBack();
                    $payload = json_encode(array("status"=>400, "message"=>"Error al actualizar detalle de la venta en administracion facturacion.", "sql"=>"{$sql}", "error_detail"=>"{$error->getMessage()}"));
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
                }
            }
            $link->commit();
        //verifica si la venta ya fue enviada a la Razon Social
            if( $row['id_status_facturacion'] == 4 || $row['id_status_facturacion'] == 5 ){
            //consulta el url api de la razon social
                $sql = "SELECT
                        rs.url_api
                    FROM ec_pedidos p 
                    LEFT JOIN razones_sociales rs
                    ON rs.id_equivalente = p.id_razon_social
                    WHERE p.folio_nv = '{$folio}'";
                $stm = $link->query( $sql ) or die( "Error al consultar el url de api de facturacion RS : {$sql}" );
                $api_row = $stm->fetch( PDO::FETCH_ASSOC );
            //consume el servicio para actualizar la nota de venta en la razon social correspondiente
                $post_data = json_decode($req);
                $update_RS = $SynchronizationManagmentLog->sendPetition("{$api_row['url_api']}/api/facturacion/devolucion", $post_data);
            
                $payload = json_encode(array("status"=>200, "message"=>"Venta actualizada en administracion de facturacion y Razon Social exitosamente."));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            }else{
                $payload = json_encode(array("status"=>200, "message"=>"Venta actualizada en administracion de facturacion exitosamente."));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            }
        }
    }
});
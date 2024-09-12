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
        $response->getBody()->write(json_encode( array( "status"=>"200", "message"=>"Forma de pago actualizada exitosamente." ) ));
        return $response;
    });
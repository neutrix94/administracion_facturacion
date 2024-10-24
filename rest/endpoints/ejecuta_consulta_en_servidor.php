<?php
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/*
* Endpoint: 
* Path: /
* Método: GET
* Descripción: Actualizacion de peticion de servidor a cliente
*/
$app->post('/', function (Request $request, Response $response){
    include( '../include/db.php' );
    $db = new db();
    $link = $db->conectDB();
    $body = $request->getBody();
    $req = json_decode($body, true);
    $sql = $req["QUERY"];
    if( trim($sql) == "" ){
        $resp['status'] = 400;
        $resp['message'] = "La consulta SQL no puede ser vacia.";
        $response->getBody()->write(json_encode($resp ));
        return $response;
    }
    $resp =array();
    try{
        $stm = $link->query( $sql ) or die( "Error : {$sql}" );
        //die( 'here' );
        while ( $row = $stm->fetch(PDO::FETCH_ASSOC) ){ 
            array_push( $resp, $row );
        }
    }catch( Exception $e ){
        $resp['status'] = 400;
        $resp['error'] = $e;
        $resp['query'] = $sql;
        $response->getBody()->write(json_encode($resp ));
        return $response;
    }
    $resp['status'] = 200;
    $response->getBody()->write(json_encode($resp ));
    return $response;
//    return json_encode( $resp );
});

?>

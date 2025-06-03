<?php
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    $app->post('/obtener_clientes_pendientes', function (Request $request, Response $response, $args) {
        include( '../include/db.php' );
        $body = $request->getBody();
        $req = json_decode($body, true);
        $clientes = array();
        $contactos = array();
        $clientes_rs = "";
        $contactos_rs = "";
        if( ! isset( $req['clientes'] ) ){
            $response->getBody()->write(json_encode( array( "status"=>"303", "message"=>"No llegaron clientes, el atributo clientes es requerido." ) ));
            return $response;
        }else{
            $clientes_rs = $req['clientes'];
        }
        if( ! isset( $req['contactos'] ) ){
            $response->getBody()->write(json_encode( array( "status"=>"303", "message"=>"No llegaron contactos, el atributo contacto es requerido." ) ));
            return $response;
        }else{
            $contactos_rs = $req['contactos'];
        }
        $db = new db();
        $link = $db->conectDB();
    //consulta clientes faltantes
        try{
            $sql = "SELECT
                        *
                    FROM vf_clientes_razones_sociales
                    WHERE id_cliente_facturacion >= 10000
                    AND id_cliente_facturacion NOT IN({$clientes_rs})";
            $stm = $link->query($sql);
            while($row = $stm->fetch(PDO::FETCH_ASSOC)){
                $clientes[] = $row;
            }
        }catch(PDOException $error){
            $payload = json_encode(array("status"=>"302", "message"=>"Error al consultar clientes faltantes en adminsitracion de facturacion : {$sql}", "error_detail"=>"{$error->getMessage()}"));   
            $response->getBody()->write($payload);
            return $response;
        }
    //consulta contactos faltantes
        try{
            $sql = "SELECT
                        *
                    FROM vf_clientes_contacto
                    WHERE id_cliente_contacto NOT IN({$contactos_rs})";
            $stm = $link->query($sql);
            while($row = $stm->fetch(PDO::FETCH_ASSOC)){
                $contactos[] = $row;
            }
        }catch(PDOException $error){
            $payload = json_encode(array("status"=>"302", "message"=>"Error al consultar contactos faltantes en adminsitracion de facturacion : {$sql}", "error_detail"=>"{$error->getMessage()}"));   
            $response->getBody()->write($payload);
            return $response;
        }
        
        $response->getBody()->write(json_encode( array( "status"=>"200", "clientes"=>$clientes, "contactos"=>$contactos ) ));
        return $response;
    });
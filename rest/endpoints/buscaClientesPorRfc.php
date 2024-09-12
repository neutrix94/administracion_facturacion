<?php
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    $app->post('/busca_clientes_por_rfc', function (Request $request, Response $response, $args) {
        include( '../include/db.php' );
        $db = new db();
        $link = $db->conectDB();
        $body = $request->getBody();
        $req = json_decode($body, true);
        $rfc = $req['rfc'];
    //busca cliente
        $sql = "SELECT 
                    id_cliente_facturacion AS costumer_id, 
                    rfc AS costumer_rfc,
                    razon_social AS costumer_name
                FROM vf_clientes_razones_sociales 
                WHERE rfc = '{$rfc}'";
        $stm = $link->query( $sql ) or die( "Error al consultar si el RFC esta registrado : {$sql}" );
        if( $stm->rowCount() <= 0 ){
            $response->getBody()->write( json_encode( array( "status"=>"200", "was_found"=>"no", "message"=>"El cliente con el RFC : '{$rfc}' no fue encontrado." ) ) );
            return $response;
        }
        $costumer = array();
        $costumer_tmp = $stm->fetch();
        foreach ($costumer_tmp as $key => $value) {
            if( ! is_numeric( $key ) ){
                $costumer[$key] = $value;
            }
        }
    //consulta contactos del cliente
        $sql = "SELECT
                    cc.id_cliente_contacto AS contact_costumer_id,
                    cc.nombre AS contact_name,
                    cc.correo AS contact_email,
                    cc.folio_unico AS contact_unique_folio,
                    c.id_cfdi AS cfdi_use_id,
                    c.nombre AS cfdi_use_name
                FROM vf_clientes_contacto cc
                LEFT JOIN vf_cfdi c
                ON cc.uso_cfdi = c.clave
                WHERE cc.id_cliente_facturacion = {$costumer['costumer_id']}";
        $stm = $link->query( $sql ) or die( "Error al consultar los contactos del cliente : {$sql}" );
        $costumer_contacts = array();
        while ( $contacts_tmp = $stm->fetch() ) {
            $contacts = array();
            foreach ($contacts_tmp as $key => $value) {
                if( ! is_numeric( $key ) ){
                    $contacts[$key] = $value;
                }
            }
            $costumer_contacts[] = $contacts;
        }
        $response->getBody()->write( json_encode( array( "status"=>"200", "was_found"=>"yes", "costumer"=>$costumer, "contacts"=>$costumer_contacts ) ) );
        return $response;
    });
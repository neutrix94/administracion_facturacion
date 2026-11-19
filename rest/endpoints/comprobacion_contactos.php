<?php
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    $app->post('/comprobacion_contactos', function (Request $request, Response $response, $args) {
        include( '../include/db.php' );
        $db = new db();
        $link = $db->conectDB();
        $contactos = array();
        $bases_datos = array();
    //consulta las bases de datos de facturacion
        try{
            $sql = "SELECT
                        nombre_db
                    FROM razones_sociales
                    WHERE activo = 1";
            $stm = $link->query($sql);
            while($row = $stm->fetch(PDO::FETCH_ASSOC)){
                $row['no_existen'] = array();
                $row['tienen_diferencias'] = array();
                $bases_datos[] = $row;
            }
        }catch(PDOException $error){
            $payload = json_encode( array( "status"=>"302", "message"=>"Error al consultar contactos en admin fact", "query"=>"{$sql}", "error_detail"=>"{$error->getMessage()}" ) );
            $response->getBody()->write($payload);
            return $response;
        }
    //consulta todos los usuarios
        try{
            $sql = "SELECT
                        id_cliente_contacto,
                        id_cliente_facturacion,
                        nombre,
                        telefono,
                        celular,
                        correo,
                        uso_cfdi,
                        fecha_alta,
                        fecha_ultima_actualizacion,
                        folio_unico
                    FROM vf_clientes_contacto";
            $stm = $link->query($sql);
            while($row = $stm->fetch(PDO::FETCH_ASSOC)){
                $contactos[] = $row;
            }
        }catch(PDOException $error){
            $payload = json_encode( array( "status"=>"302", "message"=>"Error al consultar los clientes en admin fact", "query"=>"{$sql}", "error_detail"=>"{$error->getMessage()}" ) );
            $response->getBody()->write($payload);
            return $response;
        }
    //compara clientes
        foreach ($contactos as $key => $contacto) {
            foreach($bases_datos as $key2 => $base){
                try{
                    $sql = "SELECT
                                `id_cliente_contacto`,
                                `id_cliente_facturacion`,
                                `nombre`,
                                `telefono`,
                                `celular`,
                                `correo`,
                                `uso_cfdi`,
                                `fecha_alta`,
                                `fecha_ultima_actualizacion`,
                                `folio_unico`,
                                `sincronizar`
                            FROM {$base['nombre_db']}.`ec_clientes_contacto`
                            WHERE id_cliente_contacto = {$contacto['id_cliente_contacto']}";
                    $stm = $link->query($sql);
                    if($stm->rowCount() <= 0){
                        array_push($bases_datos[$key2]['no_existen'], $contacto);
                    }else{
                //realiza comparacion 
                        $row = $stm->fetch(PDO::FETCH_ASSOC);
                        if($row['nombre'] != $contacto['nombre']){
                            $diferencia = array("razon_social"=>$row, "administracion_facturacion"=>$contacto);
                            array_push($bases_datos[$key2]['tienen_diferencias'], $diferencia);
                        }
                    }
                }catch(PDOException $error){
                    $payload = json_encode( array( "status"=>"302", "message"=>"Error al consultar contactos en RS", "query"=>"{$sql}", "error_detail"=>"{$error->getMessage()}" ) );
                    $response->getBody()->write($payload);
                    return $response;
                }
            }
        }
        $response->getBody()->write(json_encode( array( "status"=>"200", "registros"=>$bases_datos ) ));
        return $response;
    }); 
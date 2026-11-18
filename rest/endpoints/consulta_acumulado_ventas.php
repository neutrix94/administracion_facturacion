<?php
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    $app->post('/consulta_acumulado_ventas', function (Request $request, Response $response, $args) {
        include( '../include/db.php' );
        $db = new db();
        $link = $db->conectDB();
        $bases_datos = array();
        $emisores = array();
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
            $payload = json_encode( array( "status"=>"302", "message"=>"Error al consultar los clientes en admin fact", "query"=>"{$sql}", "error_detail"=>"{$error->getMessage()}" ) );
            $response->getBody()->write($payload);
            return $response;
        }
        foreach($bases_datos as $key2 => $base){
            $sql = "SELECT
                        e.razon_social,
                        lrs.monto_ventas_actual
                    FROM {$base['nombre_db']}.`ec_emisor` e
                    LEFT JOIN {$base['nombre_db']}.`ec_limites_razon_social` lrs
                    ON e.id_emisor = lrs.id_emisor
                    WHERE lrs.periodo = (SELECT DATE_FORMAT(NOW(), '%Y'))";
            $stm = $link->query($sql);
            $row = $stm->fetch(PDO::FETCH_ASSOC);
            $emisores[] = $row;
        }

        $response->getBody()->write(json_encode( array( "status"=>"200", "registros"=>$emisores ) ));
        return $response;
    }); 
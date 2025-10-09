<?php
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    $app->post('/barrido_comprobacion_envios_rs', function (Request $request, Response $response, $args) {
        include( '../include/db.php' );
        $db = new db();
        $link = $db->conectDB();
        $body = $request->getBody();
        $pending_sales = array();
        $current_year = "";
        $api_path = "";
    //consulta el año actual
        try{
            $sql = "SELECT
                    `value` AS api_path,
                    (SELECT DATE_FORMAT(NOW(), '%Y')) AS current_year
                FROM api_config WHERE `name` = 'path_facturacion'";
            $stm = $link->query($sql);
            $row = $stm->fetch(PDO::FETCH_ASSOC);
            $current_year = $row['current_year'];
            $api_path = $row['api_path'];
        }catch(PDOException $error){
            $resp = array();
            $resp['status'] = "302";
            $resp['message'] = "Error al consultar año actual.";
            $resp['query'] = "{$sql}";
            $resp['error_detail'] = "{$error->getMessage()}";
            $payload = json_encode($resp);
            $response->getBody()->write($payload);
            return $response;
        }
    //consulta los folios de la venta que estan es status de enviada a RS
        $pending_sales = array();
        $ok_sales = array();
        try{
            $sql = "SELECT
                        folio_nv
                    FROM ec_pedidos p
                    WHERE id_status_facturacion IN(4)
                    AND fecha_alta LIKE '%{$current_year}%'";
            $stm = $link->query($sql);
            while($row = $stm->fetch(PDO::FETCH_ASSOC)){
                $post_data = json_encode( array( "sale_folio"=>$row['folio_nv'] ) );
                $url = "{$api_path}/rest/inserta_venta_sistema_facturacion";
                $petition_rs = sendPetition($url, $post_data);//$pending_sales[] = $row;

                $resp_decode = json_decode( $petition_rs, true );
                if( isset($resp_decode['status']) && $resp_decode['status'] == 200 ){//si la insercion es exitosa actualiza a status 5 la nota de venta
                    $status_update = 5;//insertado en RS
                    $pending_sales[] = $row;
                }else{
                    $status_update = 4;//enviado a RS pero no se inserta
                    $ok_sales[] = $row;
                }
                try{
                    $sql = "UPDATE ec_pedidos SET id_status_facturacion = {$status_update} WHERE folio_nv = '{$row['folio_nv']}'";
                    $link->query($sql);
                }catch(PDOException $error){
                    $resp = array();
                    $resp['status'] = "302";
                    $resp['message'] = "Error al actualizar status de facturacion de venta.";
                    $resp['query'] = "{$sql}";
                    $resp['error_detail'] = "{$error->getMessage()}";
                    $payload = json_encode($resp);
                    $response->getBody()->write($payload);
                    return $response;
                }
            }
        }catch(PDOException $error){
            $resp = array();
            $resp['status'] = "302";
            $resp['message'] = "Error al consultar las ventas pendientes de subir.";
            $resp['query'] = "{$sql}";
            $resp['error_detail'] = "{$error->getMessage()}";
            $payload = json_encode($resp);
            $response->getBody()->write($payload);
            return $response;
        }
        $resp = array("ok_sales"=>$ok_sales, "pending_sales"=>"{$pending_sales}");
        $payload = json_encode($pending_sales);
        $response->getBody()->write($payload);
        return $response;
    });

    function sendPetition($url, $post_data){
        $crl = curl_init( "{$url}" );
        curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($crl, CURLINFO_HEADER_OUT, true);
        curl_setopt($crl, CURLOPT_POST, true);
        curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data);
        //curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
        curl_setopt($crl, CURLOPT_TIMEOUT, 10);
        curl_setopt($crl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json' )
        );
        $resp = curl_exec($crl);//envia peticion
        curl_close($crl);
$myfile = fopen("barrido_comprobacion_envios_rs.txt", "a") or die("Unable to open file!");
$txt = "\nURL : {$url}\nPOST_DATA : {$post_data}\nRESPONSE : {$resp}";
fwrite($myfile, $txt);
fclose($myfile);
        return $resp;
    }
?>
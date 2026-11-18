<?php
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    $app->post('/comprobacion_clientes', function (Request $request, Response $response, $args) {
        include( '../include/db.php' );
        $db = new db();
        $link = $db->conectDB();
        $clientes = array();
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
            $payload = json_encode( array( "status"=>"302", "message"=>"Error al consultar los clientes en admin fact", "query"=>"{$sql}", "error_detail"=>"{$error->getMessage()}" ) );
            $response->getBody()->write($payload);
            return $response;
        }
    //consulta todos los usuarios 
        try{
            $sql = "SELECT
                        `id_cliente_facturacion`,
                        `rfc`,
                        `razon_social`,
                        `id_tipo_persona`,
                        `entrega_cedula_fiscal`,
                        `url_cedula_fiscal`,
                        `calle`,
                        `no_int`,
                        `no_ext`,
                        `colonia`,
                        `del_municipio`,
                        `cp`,
                        `localidad`,
                        `referencia`,
                        `estado`,
                        `pais`,
                        `regimen_fiscal`,
                        `productos_especificos`,
                        `fecha_alta`,
                        `datos_alta`,
                        `fecha_ultima_actualizacion`,
                        `folio_unico`,
                        `sincronizar`
                    FROM vf_clientes_razones_sociales
                    WHERE id_cliente_facturacion >= 10000";
            $stm = $link->query($sql);
            while($row = $stm->fetch(PDO::FETCH_ASSOC)){
                $clientes[] = $row;
            }
        }catch(PDOException $error){
            $payload = json_encode( array( "status"=>"302", "message"=>"Error al consultar los clientes en admin fact", "query"=>"{$sql}", "error_detail"=>"{$error->getMessage()}" ) );
            $response->getBody()->write($payload);
            return $response;
        }

    //compara clientes
        foreach ($clientes as $key => $cliente) {
            foreach($bases_datos as $key2 => $base){
                try{
                    $sql = "SELECT
                            `id_cliente`,
                            `nombre`,
                            `telefono`,
                            `telefono_2`,
                            `movil`,
                            `contacto`,
                            `email`,
                            `dias_credito`,
                            `maximo_adeudo`,
                            `es_cliente`,
                            `id_sucursal`,
                            `monto_desc`,
                            `porc_desc`,
                            `min_compra_desc`,
                            `id_equivalente`,
                            `idTipoPersona`,
                            `EntregaConsSitFiscal`,
                            `UltimaActualizacion`,
                            `regimenFiscal`,
                            `datos_alta`,
                            `folio_unico`
                        FROM
                            {$base['nombre_db']}.`ec_clientes`
                        WHERE id_cliente = {$cliente['id_cliente_facturacion']}";
                    $stm = $link->query($sql);
                    if($stm->rowCount() > 0){
                        $base['no_estan'][] = $cliente;
                    }else{
                //realiza comparacion 

                    }
                }catch(PDOException $error){
                    $payload = json_encode( array( "status"=>"302", "message"=>"Error al consultar los clientes en admin fact", "query"=>"{$sql}", "error_detail"=>"{$error->getMessage()}" ) );
                    $response->getBody()->write($payload);
                    return $response;
                }
            }
        }
        $response->getBody()->write(json_encode( array( "status"=>"200", "registros"=>$bases_datos ) ));
        return $response;
    }); 
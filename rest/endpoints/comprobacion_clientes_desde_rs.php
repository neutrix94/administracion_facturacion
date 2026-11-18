<?php
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    $app->post('/comprobacion_clientes_desde_rs', function (Request $request, Response $response, $args) {
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
                $row['no_existen_en_admin_fact'] = array();
                $row['tienen_diferencias'] = array();
                $bases_datos[] = $row;
            }
        }catch(PDOException $error){
            $payload = json_encode( array( "status"=>"302", "message"=>"Error al consultar los clientes en admin fact", "query"=>"{$sql}", "error_detail"=>"{$error->getMessage()}" ) );
            $response->getBody()->write($payload);
            return $response;
        }
        foreach($bases_datos as $key2 => $base){
            $clientes = array();
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
                    WHERE id_cliente >= 10000";
                $stm = $link->query($sql);
                while($row = $stm->fetch(PDO::FETCH_ASSOC)){
                    $clientes[] = $row;
                //consulta de usuarios 
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
                                WHERE id_cliente_facturacion = {$cliente['id_cliente']}";
                        $stm2 = $link->query($sql);
                //compara cliente
                        if($stm2->rowCount() <= 0){
                            array_push($bases_datos[$key2]['no_existen_en_admin_fact'], $row);
                        }else{
                    //realiza comparacion 
                            $row2 = $stm2->fetch(PDO::FETCH_ASSOC);
                            if($row['nombre'] != $row2['rfc']){
                                $diferencia = array("razon_social"=>$row, "administracion_facturacion"=>$row2);
                                array_push($bases_datos[$key2]['tienen_diferencias'], $diferencia);
                            }
                        }
                    }catch(PDOException $error){
                        $payload = json_encode( array( "status"=>"302", "message"=>"Error al consultar los clientes en admin fact", "query"=>"{$sql}", "error_detail"=>"{$error->getMessage()}" ) );
                        $response->getBody()->write($payload);
                        return $response;
                    }
                }
            }catch(PDOException $error){
                $payload = json_encode( array( "status"=>"302", "message"=>"Error al consultar los clientes en admin fact", "query"=>"{$sql}", "error_detail"=>"{$error->getMessage()}" ) );
                $response->getBody()->write($payload);
                return $response;
            }
        }
        $response->getBody()->write(json_encode( array( "status"=>"200", "registros"=>$bases_datos ) ));
        return $response;
    }); 
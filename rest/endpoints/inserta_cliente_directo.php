<?php
//ok 2023/11/25
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
/*
* Endpoint: inserta_cliente
* Path: /inserta_clientes
* Método: POST
* Descripción: Insercion de clientes directo desde la pantalla de clientes
* Version Oscar 2024-11-07 para corregir error de ciclado de clientes
* Version Oscar 2024-11-07 para corregir error de ciclado de clientes al insertar en General Linea
*/
//$log = $req["log"];
$app->post('/inserta_cliente_directo', function (Request $request, Response $response){
    //die( 'here' );
    include( '../include/db.php' );
    $db = new db();
    $link = $db->conectDB();//conexion a mysql

    $body = $request->getBody();
    $req = json_decode($body, true);
    $costumers = $req["rows"];//recibe los cientes
    //var_dump($costumers);die('');
    $resp = array();
    $resp["ok_rows"] = '';
    $resp["error_rows"] = '';
    $resp["rows_download"] = array();
    $resp["log_download"] = array();
    if( ! include( 'utils/SynchronizationManagmentLog.php' ) ){
        die( "No se incluyó : SynchronizationManagmentLog.php" );
    }
    if( ! include( 'utils/facturacion.php' ) ){
        die( "No se incluyó : facturacion.php" );
    }
    $Bill = new Bill( $link, -1, "24LNA" );//$system_store, $store_prefix
  
    $SynchronizationManagmentLog = new SynchronizationManagmentLog( $link );//instancia clase de Peticiones Log

    if( ! include( 'utils/rowsSynchronization.php' ) ){
        die( "No se incluyó : rowsSynchronization.php" );
    }
    $rowsSynchronization = new rowsSynchronization( $link );

    $resp = array();
    $resp["ok_rows"] = '';
    $resp["error_rows"] = '';
    $resp["rows_download"] = array();
    $resp["log_download"] = array();
    $costumer_rfc ="";

    $tmp_ok = "";
    $tmp_no = "";
//inserta request
    $request_initial_time = $SynchronizationManagmentLog->getCurrentTime();
    //$resp["log"] = $SynchronizationManagmentLog->insertResponse( $log, $request_initial_time );
    if( sizeof( $costumers ) > 0 ){
        $costumer_rfc = $costumers[0]['rfc'];
        $insert_costumers = $Bill->insertCostumers( $costumers );
        //var_dump( $insert_returns );//die('');
        $resp["ok_rows"] = $insert_costumers;//$insert_returns["ok_rows"];
    //return json_encode( $insert_returns );
        if( isset( $insert_costumers["error"] ) ){//$insert_returns["error"] != '' && $insert_returns["error"] != null
        //inserta error si es el caso
        //$resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $insert_costumers["error"], $resp["log"]["unique_folio"] );
        }else{
            //$insert_returns["error"] = '';
            $resp["ok_rows"] = $insert_costumers;//$insert_returns["ok_rows"];
        //inserta respuesta exitosa
            //$resp["log"] = $SynchronizationManagmentLog->updateResponseLog( "{$resp["ok_rows"]} | ", $resp["log"]["unique_folio"] );//{$insert_returns["error_rows"]}
        }
    }/*else{
    //inserta excepcion controlada
        $response_string = "No llegaron clientes, posiblemente tengas que bajar el limite de registros de sincronizacion de facturacion!";
        $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $response_string, $resp["log"]["unique_folio"] );
    }*/
//consulta si tiene registros por comprobar
    $resp["download"] = $rowsSynchronization->getSynchronizationRows( -1, -2, 50, 'sys_sincronizacion_registros_facturacion' );
//var_dump($resp);
//return '';
//consulta las clientes que se tiene que descargar 
    //$costumers_limit = 1000;
    //$resp["download"] = $rowsSynchronization->getSynchronizationRows( -1, $log['origin_store'], $costumers_limit, 'sys_sincronizacion_registros_facturacion' );

//consume el webservice para insertar cliente en los sistemas de facturacion
    $sql = "SELECT value FROM api_config WHERE `name` = 'path_facturacion' LIMIT 1";
    $stm = $link->query( $sql ) or die( "Error al consultar el path del api : {$link->error}" );
    $row = $stm->fetch();
    $api_path = $row['value'];

    $post_data = json_encode( array( "costumers"=>$resp["download"] ), JSON_UNESCAPED_UNICODE );//json_encode( $costumers ); //json_encode( array( "costumers"=>$resp["download"] ), JSON_UNESCAPED_UNICODE );  
    $result_1 = $SynchronizationManagmentLog->sendPetition( "{$api_path}/rest/clientes/envia_cliente_facturacion", $post_data );
    $result_json = json_decode($result_1, true);
//  echo $post_data;return '';
//var_dump( $result_1 );//die("{$api_path}/rest/clientes/envia_cliente_facturacion");
    if( $result_json['status'] != 200 ){//&& trim( $result_json['status'] ) != '200'trim($result_1) != 'ok'
        die( "Error al insertar registros en facturacion : {$result_1}" );
    }else{
    //actualiza el status de sincronizacion del registros de razones sociales 
        /*$sql = "UPDATE sys_sincronizacion_registros_facturacion 
                    SET status_sincronizacion = 3
                WHERE tabla = 'vf_clientes_razones_sociales'
                AND registro_llave IN( {$result_json['ok_rows']} )";
        $stm = $link->query($sql) or die("Error al consultar los registros de sincronizacion : {$sql} : {$link->error}");*/
    }
//consume el webservice para insertar cliente en sistema general linea
    $sql = "SELECT value FROM api_config WHERE `name` = 'path' LIMIT 1";
    $stm = $link->query( $sql ) or die( "Error al consultar el path del api sistema general: {$link->error}" );
    $row = $stm->fetch();
    $general_api_path = $row['value'];
//inserta cliente en sistema general de facturacion
    $costumers_to_send = $rowsSynchronization->getSynchronizationRows( -1, -1, 50, 'sys_sincronizacion_registros_facturacion' );
    $post_data = json_encode( array(  "rows"=>$costumers_to_send ), JSON_UNESCAPED_UNICODE ); //"log"=>$log,
    $result_1 = $SynchronizationManagmentLog->sendPetition( "{$general_api_path}/rest/facturacion/inserta_cliente_directo_general_linea", $post_data );
    $result_json = json_decode( $result_1, true );
    //die( "{$general_api_path}/rest/facturacion/inserta_cliente_directo_general_linea" );
    if( $result_json['status'] != 200 ){//trim( $result_1 ) != 'ok'
        var_dump( $result_1 );
        die( "Error al insertar registros en sistema General Linea : $result_1" );
    }else{
    //actualiza el status de registro sincronizacion de General Linea
        foreach ( $result_json['ok_rows'] as $key => $value ) {
            $sql = "UPDATE sys_sincronizacion_registros_facturacion SET status_sincronizacion = 3 WHERE id_sincronizacion_registro = {$value}";
            $link->query( $sql ) or die( "Error al actualizar registros de facturacion : {$sql} : {$link->error}" );        
        }
       // var_dump( $result_1 );die('here');
    }
    if( $costumer_rfc != '' ){
    //deshabilitado Oscar 2024-11-07 para que no elimine los jsons de sincronizacion de clientes RS y GeneralLinea
        //$sql = "DELETE FROM sys_sincronizacion_registros_facturacion WHERE id_sucursal_destino IN ( -2, -1 ) 
        //AND datos_json LIKE '%{$costumer_rfc}%'";
        //$stm = $link->query($sql) or die( "Error al eliminar registros de sincronizacion en tabla sys_sincronizacion_registros_facturacion : {$sql} : {$link->error}" );
    //die('ok');
    }else{
        die( "Error al recuperar rfc del cliente." );
    }
    die('ok');
    //$response->getBody()->write(json_encode( $resp ));
    //return $response;
});

?>
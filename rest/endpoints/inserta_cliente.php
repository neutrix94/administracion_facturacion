<?php
//ok 2023/11/25
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
/*
* Endpoint: inserta_cliente
* Path: /inserta_clientes
* Método: POST
* Descripción: Insercion de clientes
*/

$app->post('/inserta_cliente', function (Request $request, Response $response){
    //die( 'here' );
    include( '../include/db.php' );
    $db = new db();
    $link = $db->conectDB();
    $body = $request->getBody();
    $req = json_decode($body, true);
    $log = $req["log"];
    $costumers = $req["rows"];
  //$link->set_charset("utf8mb4");
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

  $tmp_ok = "";
  $tmp_no = "";
  //inserta request
  $request_initial_time = $SynchronizationManagmentLog->getCurrentTime();
  $resp["log"] = $SynchronizationManagmentLog->insertResponse( $log, $request_initial_time );
    if( sizeof( $costumers ) > 0 ){
        $insert_returns = $Bill->insertCostumers( $costumers );
       //var_dump( $insert_returns );//die('');
        $resp["ok_rows"] = $insert_returns;//$insert_returns["ok_rows"];
//return json_encode( $insert_returns );
        if( isset( $insert_returns["error"] ) ){//$insert_returns["error"] != '' && $insert_returns["error"] != null
        //inserta error si es el caso
        $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $insert_returns["error"], $resp["log"]["unique_folio"] );
        }else{
            //$insert_returns["error"] = '';
            $resp["ok_rows"] = $insert_returns;//$insert_returns["ok_rows"];
        //inserta respuesta exitosa
            $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( "{$resp["ok_rows"]} | ", $resp["log"]["unique_folio"] );//{$insert_returns["error_rows"]}
        }
    }else{
    //inserta excepcion controlada
        $response_string = "No llegaron clientes, posiblemente tengas que bajar el limite de registros de sincronizacion de facturacion!";
        $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $response_string, $resp["log"]["unique_folio"] );
    }
//consulta las cliemtes que se tiene que descargar 
    $costumers_limit = 1000;
    $resp["download"] = $rowsSynchronization->getSynchronizationRows( -1, $log['origin_store'], $costumers_limit, 'sys_sincronizacion_registros_facturacion' );

//consume el webservice para insertar cliente en los sistemas de facturacion
    $sql = "SELECT value FROM api_config WHERE `name` = 'path_facturacion' LIMIT 1";
    $stm = $link->query( $sql ) or die( "Error al consultar el path del api : {$link->error}" );
    $row = $stm->fetch();
    $api_path = $row['value'];

    $post_data = json_encode( array( "costumers"=>$resp["download"] ), JSON_UNESCAPED_UNICODE );  
    $result_1 = $SynchronizationManagmentLog->sendPetition( "{$api_path}/rest/clientes/nuevoCliente", $post_data );
    if( trim( $result_1 ) != 'ok' ){
        die( "Error al insertar registros en facturacion : $result_1" );
    }
//consume el webservice para insertar cliente en sistema general linea
    $sql = "SELECT value FROM api_config WHERE `name` = 'path' LIMIT 1";
    $stm = $link->query( $sql ) or die( "Error al consultar el path del api sistema general: {$link->error}" );
    $row = $stm->fetch();
    $general_api_path = $row['value'];
    
    $post_data = json_encode( array( "costumers"=>$resp["download"] ), JSON_UNESCAPED_UNICODE );  
    $result_1 = $SynchronizationManagmentLog->sendPetition( "{$general_api_path}/rest/facturacion/inserta_cliente", $post_data );
    if( trim( $result_1 ) != 'ok' ){
        die( "Error al insertar registros en sisytema General Linea : $result_1" );
    }
    $response->getBody()->write(json_encode( $resp ));
    return $response;
});

?>
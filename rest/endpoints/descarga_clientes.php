<?php
//ok 2023/11/25
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
/*
* Endpoint: descarga_clientes
* Path: /descarga_clientes
* Método: POST
* Descripción: Descarga de clientes
*/

$app->post('/descarga_clientes', function (Request $request, Response $response){
    include( '../include/db.php' );
    $db = new db();
    $link = $db->conectDB();
    $body = $request->getBody();
    $req = json_decode($body, true);
    //$log = $req["log"];
    //$costumers = $req["rows"];
    $store_id = $req["store_id"];
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
//$resp["log"] = $SynchronizationManagmentLog->insertResponse( $log, $request_initial_time );
//consulta las clientes que se tiene que descargar 
    $costumers_limit = 1000;
    $resp["download"] = $rowsSynchronization->getSynchronizationRows( -1, $store_id, $costumers_limit, 'sys_sincronizacion_registros_facturacion' );//$log['origin_store']
    $response->getBody()->write(json_encode( $resp ));
    return $response;
});

?>
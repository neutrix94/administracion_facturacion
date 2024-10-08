<?php
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    $app->get('/inserta_cliente_facturacion', function (Request $request, Response $response, $args) {die( 'here' );
        include( '../include/db.php' );
        $db = new db();
        $link = $db->conectDB();
        if( ! include( 'utils/SynchronizationManagmentLog.php' ) ){
            die( "No se incluyó : SynchronizationManagmentLog.php" );
        }
        if( ! include( 'utils/facturacion.php' ) ){
            die( "No se incluyó : facturacion.php" );
        }
        $Bill = new Bill( $link, -1, 'LNA' );
        $SynchronizationManagmentLog = new SynchronizationManagmentLog( $link );//instancia clase de Peticiones Log
         // $returnsSynchronization = new returnsSynchronization( $link );//instancia clase de sincronizacion de movimientos
          
        
          if( ! include( 'utils/rowsSynchronization.php' ) ){
            die( "No se incluyó : rowsSynchronization.php" );
          }//die( 'here' );
          $rowsSynchronization = new rowsSynchronization( $link );
        
          $resp = array();
          $resp["ok_rows"] = '';
          $resp["error_rows"] = '';
          $resp["rows_download"] = array();
          $resp["log_download"] = array();
        
          $tmp_ok = "";
          $tmp_no = "";

          $body = $request->getBody();
          $req = json_decode($body, true);
          //var_dump( $req );die('');//ode($body, true);
          $log = $req["log"];
          $costumers = $req["rows"];
          //var_dump( $costumers );die('');
          //inserta request
          $request_initial_time = $SynchronizationManagmentLog->getCurrentTime();
          $resp["log"] = $SynchronizationManagmentLog->insertResponse( $log, $request_initial_time );
          if( sizeof( $costumers ) > 0 ){
            $insert_returns = $Bill->insertCostumers( $costumers );
              $resp["ok_rows"] = $insert_returns;//$insert_returns["ok_rows"];
        //return json_encode( $insert_returns );
            if( $insert_returns["error"] != '' && $insert_returns["error"] != null  ){
            //inserta error si es el caso
              $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $insert_returns["error"], $resp["log"]["unique_folio"] );
            }else{
              $resp["ok_rows"] = $insert_returns;//$insert_returns["ok_rows"];
           // die( "ok_rows : {$insert_returns}" );
              
              //$resp["error_rows"] = $insert_returns["error_rows"];
              //$tmp_ok = $insert_returns->tmp_ok;
              //$tmp_no = $insert_returns->tmp_no;
            //inserta respuesta exitosa
              $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( "{$resp["ok_rows"]} | {$insert_returns["error_rows"]}", $resp["log"]["unique_folio"] );
            }
          }else{
          //inserta excepcion controlada
            $response_string = "No llegaron clientes, posiblemente tengas que bajar el limite de registros de sincronizacion de facturacion!";
            $resp["log"] = $SynchronizationManagmentLog->updateResponseLog( $response_string, $resp["log"]["unique_folio"] );
          }
        //consulta las cliemtes que se tiene que descargar 
          $costumers_limit = 1000;
        
          $resp["download"] = $rowsSynchronization->getSynchronizationRows( -1, $log['origin_store'], $costumers_limit, 'sys_sincronizacion_registros_facturacion' );
        
        //consume el webservice para insertar cliente en los sistemas de factureacion
          $sql = "SELECT value FROM api_config WHERE name = 'path' LIMIT 1";
          $stm = $link->query( $sql ) or die( "Error al consultar el path del api : {$sql}" );
          $row = $stm->fetch();
          $api_path = $row['value'];
        
          $post_data = json_encode( array( "costumers"=>$resp["download"] ), JSON_UNESCAPED_UNICODE );  
          die( "here" );
          $result_1 = $SynchronizationManagmentLog->sendPetition( "{$api_path}/rest/facturacion/clientes/nuevoCliente", $post_data );
          if( trim( $result_1 ) != 'ok' ){
            die( "Error al insertar registros en facuracion : $result_1" );
          }
          //die( 'here' );
          return json_encode($resp, JSON_UNESCAPED_UNICODE);
          //die( "api_path : {$api_path}" );




        
        $response->getBody()->write(json_encode($row));
        return $response;
      //  return json_encode( array( "test"=>"1" ) );
    });
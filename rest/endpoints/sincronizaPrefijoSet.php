<?php
//ok 2023/11/25
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

/*
* Endpoint: productos
* Path: /productos/nuevoFact
* Método: POST
* Descripción: Servicio para registrar nuev producto en BDs facturación
* Version Oscar 2024-11-07 para corregir error de ciclado de clientes
* Version Oscar 2024-12-09 para no enviar clientes nullos
*/
$app->post('/sets/prefijos', function (Request $request, Response $response){
    include( '../include/db.php' );
//consulta datos de conexion a bases de facturacion
    $db = new db();
    $link = $db->conectDB();
    $sql = "SELECT 
                TRIM(host_db) AS host, 
                TRIM(usuario_db) AS user, 
                TRIM(contrasena_db) As pass, 
                TRIM(nombre_db) AS db_name 
            FROM razones_sociales 
            WHERE activo = 1 
            LIMIT 1";
    $db_stm = $link->query( $sql ) or die( "Error al consultar los parametros de bases de datos de facturacion : {$sql}" );
    $db_row = $db_stm->fetch( PDO::FETCH_ASSOC );
    $linkFact = mysqli_connect("{$db_row['host']}", "{$db_row['user']}", "{$db_row['pass']}", "{$db_row['db_name']}");
//recibe parametros del request
    $body = $request->getBody();
    $req = json_decode($body, true);
    $set_prefix = $req['set_prefix'];
    if( $linkFact->connect_error ){
        die( "Error al conectar con Bases de Datos de Facturación : {$linkFact->connect_error}");
    }
    $linkFact->set_charset("utf8mb4");
//bases de datos destino de facturacion
    $bd_facturacion=[];
    $sql="SELECT id_razon_social AS id, nombre_db FROM razones_sociales WHERE activo = 1";
    $stm = $link->query( $sql );// or die( "Error al consultar las bases de datos de facturacion : {$link->error}" );
    while( $row = $stm->fetch( PDO::FETCH_ASSOC ) ) {
    //consulta si existe
        try{
            $sql = "SELECT id_prefijo_set FROM {$row['nombre_db']}.ec_prefijos_sets WHERE id_prefijo_set = {$set_prefix['id_prefijo_set']}";
            $stm2 = $linkFact->query($sql);
        }catch(PDOException $error){
            $resp = array( "status"=>"302", "message"=>"Error al consultar si el prefijo ya existe.", "query"=>"{$sql}", "error_detail"=>"{$error->getMessage()}" );
            $response->getBody()->write(json_encode( $resp ));
            return $response;
        }
            $sql = "";
            if($stm2->num_rows > 0){
                $sql = "UPDATE {$row['nombre_db']}.ec_prefijos_sets 
                            SET nombre = '{$set_prefix['nombre']}', habilitado = '{$set_prefix['habilitado']}' 
                        WHERE id_prefijo_set = '{$set_prefix['id_prefijo_set']}'";
            }else{
                $sql = "INSERT INTO {$row['nombre_db']}.ec_prefijos_sets (id_prefijo_set, nombre, habilitado, fecha_alta) 
                        VALUES ('{$set_prefix['id_prefijo_set']}', '{$set_prefix['nombre']}', '{$set_prefix['habilitado']}', NOW())";
            }
            $linkFact->query($sql);
    }
    $resp = array( "status"=>"200", "message"=>"ok" );
    $response->getBody()->write(json_encode( $resp ));
    return $response;
});

?>

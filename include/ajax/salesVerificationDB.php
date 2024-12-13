<?php
    include( "../db.php" );
	$db = new db();
	$link = $db->conectDB();
//die( "Datos : {$_POST['date_since']} : {$_POST['date_to']} : {$_POST['rs_id']}" );
//consulta el url del api de facturacion
    $sql = "SELECT `value` FROM api_config WHERE `name` = 'path_facturacion'";
    $stm = $link->query( $sql );
    $row = $stm->fetch( PDO::FETCH_ASSOC );
    $billing_path = "{$row['value']}/rest/inserta_venta_facturacion_por_lote";
//recibe parametros
    $date_since = ( isset( $_GET['date_since'] ) ? $_GET['date_since'] : $_POST['date_since'] );
    $date_to = ( isset( $_GET['date_to'] ) ? $_GET['date_to'] : $_POST['date_to'] );
    $rs_id = ( isset( $_GET['rs_id'] ) ? $_GET['rs_id'] : $_POST['rs_id'] );
//consume servicio
    //die( "{$billing_path} | {$date_since} | {$date_to} |{$rs_id}");
    $token = "";
    $post_data = json_encode( array( "date_since"=>$date_since, "date_to"=>$date_to, "rs_id"=>$rs_id, "url"=>$billing_path ) );
die( "POST DATA : {$post_data}" );
    $crl = curl_init( "{$billing_path}" );
    curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($crl, CURLINFO_HEADER_OUT, true);
    curl_setopt($crl, CURLOPT_POST, true);
    curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($crl, CURLOPT_TIMEOUT, 60000);
    curl_setopt($crl, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'token: ' . "{$token}" )
    );
    $resp = curl_exec($crl);//envia peticion
    curl_close($crl);
    


?>
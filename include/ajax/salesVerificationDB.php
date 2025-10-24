<?php
    include( "../db.php" );
	$db = new db();
	$link = $db->conectDB();

    if(  isset( $_POST['fl'] ) || isset( $_GET['fl'] ) ){
        $salesVerificationDB = new salesVerificationDB( $link );
        $action = ( isset( $_POST['fl'] ) ? $_POST['fl'] : $_GET['fl'] );
        switch ($action) {
            case 'makePrevious':
            //recibe parametros
                $date_since = ( isset( $_GET['date_since'] ) ? $_GET['date_since'] : $_POST['date_since'] );
                $date_to = ( isset( $_GET['date_to'] ) ? $_GET['date_to'] : $_POST['date_to'] );
                $rs_id = "";//( isset( $_GET['rs_id'] ) ? $_GET['rs_id'] : $_POST['rs_id'] );
                echo $salesVerificationDB->getPrevious( $date_since, $date_to, $rs_id );
            break;
            case 'send':
            //recibe parametros
                $date_since = ( isset( $_GET['date_since'] ) ? $_GET['date_since'] : $_POST['date_since'] );
                $date_to = ( isset( $_GET['date_to'] ) ? $_GET['date_to'] : $_POST['date_to'] );
                $rs_id = ( isset( $_GET['rs_id'] ) ? $_GET['rs_id'] : $_POST['rs_id'] );
                echo $salesVerificationDB->sendSales( $date_since, $date_to, $rs_id );
            break;
            case 'sales_sweep' :
                $date_since = (isset($_POST['date_since']) ? $_POST['date_since'] : (isset($_GET['date_since']) ? $_GET['date_since'] : null));
                if($date_since == null){
                    die(json_encode(array("status"=>"302", "message"=>"La fecha desde no puede enviarse vacia.")));
                }
                $date_to = (isset($_POST['date_to']) ? $_POST['date_to'] : (isset($_GET['date_to']) ? $_GET['date_to'] : null));
                if($date_to == null){
                    die(json_encode(array("status"=>"302", "message"=>"La fecha hasta no puede enviarse vacia.")));
                }
                echo $salesVerificationDB->salesSweep($date_since, $date_to);
            break;
            default :
                die( "Permission denied on : '{$action}'" );
            break;
        }
    }
//die( "Datos : {$_POST['date_since']} : {$_POST['date_to']} : {$_POST['rs_id']}" );
    final class salesVerificationDB{
        private $link;
        public function __construct( $connection ) {
            $this->link = $connection;
        }
    //barrido de ventas
        public function salesSweep($date_since, $date_to){
            $sql = "SELECT `value` FROM api_config WHERE `name` = 'path_facturacion'";
            $stm = $this->link->query( $sql );
            $row = $stm->fetch( PDO::FETCH_ASSOC );
            $billing_path = "{$row['value']}";
            $date_since = (isset($_POST['date_since']) ? $_POST['date_since'] : (isset($_GET['date_since']) ? $_GET['date_since'] : null));
            if($date_since == null){
                die(json_encode(array("status"=>"302", "message"=>"La fecha desde no puede enviarse vacia.")));
            }
            $date_to = (isset($_POST['date_to']) ? $_POST['date_to'] : (isset($_GET['date_to']) ? $_GET['date_to'] : null));
            if($date_to == null){
                die(json_encode(array("status"=>"302", "message"=>"La fecha hasta no puede enviarse vacia.")));
            }
            $post_data = json_encode( array( "fecha_desde"=>$date_since, "fecha_hasta"=>$date_to ) );
            $resp = $this->sendPetition( "{$billing_path}/rest/barrido_comprobacion_envios_rs", $post_data, "" );
            return $resp;
            //die( $resp );
        }
    //previo
        public function getPrevious( $date_since, $date_to, $rs_id, $token = "" ){
            $sales = array();
            try{
                $sql = "SELECT
                            ax.folio_nv,
                            ax.total,
                            ax.fecha_alta,
                            ax.total_piezas
                        FROM(
                            SELECT
                                p.folio_nv,
                                p.fecha_alta,
                                p.total,
                                SUM(pd.cantidad) AS total_piezas
                            FROM ec_pedidos p
                            LEFT JOIN ec_pedidos_detalle pd
                            ON pd.id_pedido = p.id_pedido
                            WHERE p.id_status_facturacion IN(4)
                            AND ( p.fecha_alta BETWEEN '{$date_since} 00:00:01' AND '{$date_to} 23:59:59' )
                            GROUP BY p.id_pedido
                        )ax
                        WHERE ax.total_piezas > 0";
                $stm = $this->link->query($sql);
                if($stm->rowCount() <= 0){
                    die(json_encode(array("status"=>"200", "message"=>"No hay ventas con el rango de fecha seleccionado.")));
                }
                while($row = $stm->fetch(PDO::FETCH_ASSOC)){
                    $sales[] = $row; 
                }
                return json_encode(array("status"=>"200", "sales"=>$sales));
            }catch(PDOException $error){
                die(json_encode(array("status"=>"302", "message"=>"Error al consultar ventas pendientes", "query"=>"{$sql}", "error_detail"=>"{$error->getMessage()}")));
            }
            
            /*$sql = "SELECT `value` FROM api_config WHERE `name` = 'path_facturacion'";
            $stm = $this->link->query( $sql );
            $row = $stm->fetch( PDO::FETCH_ASSOC );
            $billing_path = "{$row['value']}";
            $post_data = json_encode( array( "date_since"=>$date_since, "date_to"=>$date_to, "rs_id"=>$rs_id, "url"=>$billing_path ) );
            $resp = $this->sendPetition( "{$billing_path}/rest/barrido_ventas_previo", $post_data, $token );
            return $resp;*/
            //die( $resp );
        }
    //previo
        public function sendSales( $date_since, $date_to, $rs_id, $token = "" ){
            $sql = "SELECT `value` FROM api_config WHERE `name` = 'path_facturacion'";
            $stm = $this->link->query( $sql );
            $row = $stm->fetch( PDO::FETCH_ASSOC );
            $billing_path = "{$row['value']}";
            $post_data = json_encode( array( "date_since"=>$date_since, "date_to"=>$date_to, "rs_id"=>$rs_id, "url"=>$billing_path ) );
            //die( "{$billing_path}/rest/barrido_ventas_facturacion_por_lote : {$post_data}" );
            $resp = $this->sendPetition( "{$billing_path}/rest/barrido_ventas_facturacion_por_lote", $post_data, $token );
            return $resp;
            //die( $resp );
        }
    //metodo para mandar peticion
        function sendPetition( $url, $post_data, $token = '' ){
            $crl = curl_init( "{$url}" );
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
            return $resp;
        }
    }
?>
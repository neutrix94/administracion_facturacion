<?php
    if( isset( $_POST['fl'] ) || isset( $_GET['fl'] ) ){
        $action = ( isset( $_POST['fl'] ) ? $_POST['fl'] : $_GET['fl'] );
        include( '../include/db.php' );
        $db = new db();
        $link = $db->conectDB();
        $SalesDB = new SalesDB( $link );
        switch ( $action ) {
            case 'seekSaleByFolio':
                $folio = ( isset( $_POST['folio'] ) ? $_POST['folio'] : $_GET['folio'] );
                echo $SalesDB->getSales( $folio );
            break;

            case 'getPagesSalesErrors':
                $limit = ( isset( $_POST['limit'] ) ? $_POST['limit'] : (isset($_GET['limit']) ? $_GET['limit'] : 20) );
                $current_page = ( isset( $_POST['current_page'] ) ? $_POST['current_page'] : (isset($_GET['current_page']) ? $_GET['current_page'] : 0) );
                $store = ( isset( $_POST['store'] ) ? $_POST['store'] : (isset($_GET['store']) ? $_GET['store'] : 0) );
                $status = ( isset( $_POST['status'] ) ? $_POST['status'] : (isset($_GET['status']) ? $_GET['status'] : 0) );
                echo json_encode($SalesDB->getPagesSalesErrors($limit, $current_page, $store, $status));
            break;

            case 'getSpecificSale' :
                $sale_id = ( isset( $_POST['sale_id'] ) ? $_POST['sale_id'] : $_GET['sale_id'] );
                echo $SalesDB->getSpecificSale($sale_id);
            break;

            case 'retrySendingSale' :
                $sale_id = ( isset( $_POST['sale_id'] ) ? $_POST['sale_id'] : $_GET['sale_id'] );
                echo $SalesDB->retrySendingSale($sale_id);
            break;
            
            default:
                die( "Permission denied on : '{$action}'." );
            break;
        }
    }
    final class SalesDB{
        private $link;
        public function __construct( $connection ) {
            $this->link = $connection;
        }

        public function getPagesInfo($limit, $current_page){
            $paginator = array();
            try{
                $sql = "SELECT
                        COUNT(*) AS rows_counter
                    FROM(
                            SELECT
                            COUNT(*) AS rows_counter
                        FROM ec_pedidos_error_envio_rs
                        WHERE id_pedido > 0
                        GROUP BY id_pedido
                    )ax";
                $eje = $this->link->query( $sql );
                $row = $eje->fetch( PDO::FETCH_ASSOC );
                $pages_counter = CEIL( $row['rows_counter'] / $limit );
                $paginator['rows_counter'] = $row['rows_counter'];
                $paginator['pages_counter'] = $pages_counter;
                $paginator['limit'] = $limit;
                $paginator['current_page'] = $current_page;
                return $paginator;
            }catch(PDOException $error){
                return array("status"=>"302", "message"=>"Error al calcular paginador.", "query"=>"{$sql}", "error_detail"=>"{$error->getMessage()}");
            }
        }
        
        public function getStores(){
            $stores = array();
            try{
                $sql = "SELECT
                            id_sucursal,
                            nombre
                        FROM sucursales
                        WHERE id_sucursal > 0";
                $stm = $this->link->query($sql);
                while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
                    $stores[] = $row;
                }
                return $stores;
            }catch(PDOException $error){
                die(json_encode(array("status"=>"302", "message"=>"Error al consultar sucursales.", "query"=>"{$sql}", "error_detail"=>"{$error->getMessage()}")));
            }
        }

        public function getStatus(){
            $status = array();
            try{
                $sql = "SELECT
                            id_status_facturacion,
                            nombre_status
                        FROM ec_status_facturacion
                        WHERE 1";
                $stm = $this->link->query($sql);
                while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
                    $status[] = $row;
                }
                return $status;
            }catch(PDOException $error){
                die(json_encode(array("status"=>"302", "message"=>"Error al consultar status.", "query"=>"{$sql}", "error_detail"=>"{$error->getMessage()}")));
            }
        }
        
        public function getPagesSalesErrors( $limit = 20, $current_page = 1, $store = 0, $status = 0){
            $sales = array();
            $paginator = $this->getPagesInfo($limit, $current_page);
            try{
                $sql = "SELECT 
                        p.id_pedido, 
                        s.nombre AS store_name,
                        p.folio_nv,
                        p.total,
                        GROUP_CONCAT(peer.contenido_respuesta SEPARATOR '\n') AS contenido_respuesta,
                        peer.omitir,
                        p.id_status_facturacion
                    FROM ec_pedidos_error_envio_rs peer
                    LEFT JOIN ec_pedidos p
                    ON peer.id_pedido = p.id_pedido
                    LEFT JOIN sys_sucursales s
                    ON p.id_sucursal = s.id_sucursal
                    WHERE 1";
                if($store != 0 ){
                    $sql .= " AND p.id_sucursal = {$store}";
                }
                if($status != 0 ){
                    $sql .= " AND p.id_status_facturacion = {$status}";
                }
                $sql .= " GROUP BY p.id_pedido
                    ORDER BY p.id_pedido DESC";
                $offset = ($current_page - 1) * $limit;
                $sql .= " LIMIT {$offset}, $limit";
//die($sql);
                $stm = $this->link->query($sql);
                while($row = $stm->fetch(PDO::FETCH_ASSOC)){
                    $sales[] = $row;
                }
                return array("paginator"=>$paginator,"errors"=>$sales);
            }catch(PDOException $error){
                return array("status"=>"302", "message"=>"Error al consultar las notas de venta.", "query"=>"{$sql}", "error_detail"=>"{$error->getMessage()}");
            }
        }

        public function retrySendingSale($sale_id){
            $folio = "";
            $api_path = "";
        //consulta folio y url de API
            try{
                $sql = "SELECT 
                            folio_nv,
                            (SELECT `value` FROM api_config WHERE `name` = 'path_facturacion' LIMIT 1) AS api_path
                        FROM ec_pedidos WHERE id_pedido = {$sale_id}";
                $stm = $this->link->query($sql);
                $row = $stm->fetch(PDO::FETCH_ASSOC);
                $folio = $row['folio_nv'];
                $api_path = $row['api_path'];
            }catch(PDOException $error){
                die(json_encode(array("status"=>"302", "message"=>"Error al consultar folio y url del api : {$sql}", "error_detail"=>$error->getMessage())));
            }
            if($folio == ""){
                die(json_encode(array("status"=>"303", "message"=>"No se encontro el folio de la venta : {$sale_id}")));
            }
            if($folio == ""){
                die(json_encode(array("status"=>"303", "message"=>"No se encontro el path del API.")));
            }
        //consume servicio para enviar venta de nuevo
/**/                 
            $resp = "";
            $post_data = json_encode( array( "sale_folio"=>$folio ) );
            $crl = curl_init( "{$api_path}/rest/inserta_venta_sistema_facturacion" );
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
            $resp_decode = json_decode( $resp, true );
            $status_update = "";
            if( isset($resp_decode['status']) && $resp_decode['status'] == 200 ){//si la insercion es exitosa actualiza a status 5 la nota de venta
                $status_update = 5;//insertado en RS
            }else{
                $status_update = 4;//enviado a RS pero no se inserta
            }
//actualiza el status de la nota de venta
            try{
                $sql = "UPDATE ec_pedidos SET id_status_facturacion = {$status_update} WHERE folio_nv = '{$folio}'";
                $stm = $this->link->query( $sql );
            }catch( PDOException $e ){
                die( json_encode( array( "status"=>400, "Message"=>"Error al actualizar status de venta en sistema de administracion_facturacion : {$sql} : ", "error_detail"=>"{$e->getMessage()}" ) ) );
            }
            return $resp;
/**/
        }
        public function getSales( $folio = '', $start = 0, $limit = 30, $order_by = 'ASC' ){
            $resp = "";
            $sql = "SELECT 
                        p.id_pedido, 
                        s.nombre AS store_name,
                        p.folio_nv,
                        p.total,
                        GROUP_CONCAT(peer.contenido_respuesta SEPARATOR '\n') AS contenido_respuesta,
                        peer.omitir 
                    FROM ec_pedidos_error_envio_rs peer
                    LEFT JOIN ec_pedidos p
                    ON peer.id_pedido = p.id_pedido
                    LEFT JOIN sys_sucursales s
                    ON p.id_sucursal = s.id_sucursal
                    WHERE 1";
            $sql .= ( $folio == '' ? "" : " AND p.folio_nv LIKE '%{$folio}%'" );
            if( $start != 0 ){
            //    $sql .= " LIMIT {$start}, $limit";
            }else{
            //    $sql .= " LIMIT $limit";
            }
            $sql .= " GROUP BY p.id_pedido";
            $sql .= " ORDER BY p.id_pedido ";
            //die("SQL : {$sql}");
            try{
                $stm = $this->link->query( $sql ) or die( "Error al consultar la venta  : {$sql} : {$this->link->error}" );
            }catch( PDOException $e ){
                die( "Error al consultar las notas de venta : {$sql} : {$e}" );
            }
            $c = 0;
            if( $stm->rowCount() <= 0 ){
                return "<tr><td colspan=\"10\" class=\"text-center text-danger\">Sin resultados.</td></tr>";
            }
            while( $r = $stm->fetch( PDO::FETCH_ASSOC ) ){
                $c ++;
                $resp .= $this->build_row_ceil( $r, $c );
            }
            return $resp;
        }

        public function build_row_ceil( $r, $c ){
            //$c++;//incrementamos contador        
            $resp = '<tr id="fila_'.$c.'" tabindex="'.$c.'" class="row_item">';
            $resp .= '<td class="text-center">'.$r['id_pedido'].'</td>';
            $resp .= '<td class="text-center">'.$r['store_name'].'</td>';
            $resp .= '<td class="text-center">'.$r['folio_nv'].'</td>';
            $resp .= '<td class="text-center">'.$r['total'].'</td>';
            $resp .= "<td id=\"error_detail_{$c}\" style=\"display:none;\">{$r['contenido_respuesta']}</td>";
            $resp .= "<td class=\"text-center\"><input type=\"checkbox\" " . ($r['omitir'] == 1 ? 'checked' : '') . " disabled></td>";
            $resp .= "<td class=\"text-center\" value=\"\">
                    <button
                        type=\"button\"
                        class=\"btn\"
                        onclick=\"showErrorDetail( {$c}, {$r['id_pedido']} );\"
                    >
                        <i class=\"icon-eye\"></i>
                    </button>
                </td>
            </tr>"; 
            return $resp;
        }
    //consulta detalle de una venta en especifico
        public function getSpecificSale($sale_id){
            $sale = array();
            $detail_smt = null;
            try{
                $sql = "SELECT
                            p.id_pedido,
                            p.folio_nv,
                            IF(p.id_razon_factura < 10000, 'Sin Asignar', CONCAT(crs.rfc, ' - ', crs.razon_social) ) AS id_cliente,
                            p.fecha_alta,
                            p.subtotal,
                            p.total,
                            vc.nombre AS uso_cfdi,
                            esf.nombre_status AS id_status_facturacion,
                            rs.nombre AS id_razon_social
                        FROM ec_pedidos p
                        LEFT JOIN vf_clientes_razones_sociales crs
                        ON crs.id_cliente_facturacion = p.id_razon_factura
                        LEFT JOIN vf_cfdi vc
                        ON vc.id_cfdi = p.uso_cfdi
                        LEFT JOIN ec_status_facturacion esf
                        ON esf.id_status_facturacion = p.id_status_facturacion
                        LEFT JOIN razones_sociales rs
                        ON rs.id_equivalente = p.id_razon_social
                        WHERE p.id_pedido = {$sale_id}";
                $stm = $this->link->query($sql);
                if($stm->rowCount() > 0){
                    $sale = $stm->fetch(PDO::FETCH_ASSOC);
                }
                $sql = "SELECT
                            id_producto,
                            cantidad,
                            precio,
                            monto,
                            folio_unico
                        FROM ec_pedidos_detalle
                        WHERE id_pedido = {$sale_id}";
                $detail_smt = $this->link->query($sql);
            }catch(PDOException $error){
                die("Error al consultar información de la venta : {$sql} : {$error}");
            }
            include( '../include/forms/formularioVentas.php' );
        }
    }
?>
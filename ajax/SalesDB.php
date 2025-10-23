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

            case 'getPagesSales' : 
                $limit = ( isset( $_POST['limit'] ) ? $_POST['limit'] : (isset($_GET['limit']) ? $_GET['limit'] : 20) );
                $current_page = ( isset( $_POST['current_page'] ) ? $_POST['current_page'] : (isset($_GET['current_page']) ? $_GET['current_page'] : 0) );
                echo json_encode($SalesDB->getPagesSales( $limit, $current_page));
            break;

            case 'getSales':
                $start = ( isset( $_POST['start'] ) ? $_POST['start'] : $_GET['start'] );
                $folio = ( isset( $_POST['limit'] ) ? $_POST['limit'] : $_GET['limit'] );
                echo $SalesDB->getSales( '', $start, $limit, 'DESC' );//fl=&folio=${text}&start=${start_position}&limit=${limit}
            break;

            case 'getSpecificSale' :
                $sale_id = ( isset( $_POST['sale_id'] ) ? $_POST['sale_id'] : $_GET['sale_id'] );
                echo $SalesDB->getSpecificSale($sale_id);
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
                FROM ec_pedidos
                WHERE id_pedido > 0";
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

        public function getPagesSales( $limit = 20, $current_page = 1){
            $sales = array();
            $paginator = $this->getPagesInfo($limit, $current_page);
            try{
                $sql = "SELECT 
                    p.id_pedido, 
                    s.nombre AS store_name,
                    p.folio_nv, 
                    IF(p.id_razon_factura < 10000, 'Sin Asignar', crs.rfc ) AS id_cliente,
                    p.total
                FROM ec_pedidos p
                LEFT JOIN sys_sucursales s
                ON p.id_sucursal = s.id_sucursal
                LEFT JOIN vf_clientes_razones_sociales crs
                ON p.id_razon_factura = crs.id_cliente_facturacion
                WHERE 1 
                ORDER BY id_pedido DESC";
                $offset = ($current_page - 1) * $limit;
                $sql .= " LIMIT {$offset}, $limit";
                $stm = $this->link->query($sql);
                while($row = $stm->fetch(PDO::FETCH_ASSOC)){
                    $sales[] = $row;
                }
                return array("paginator"=>$paginator,"sales"=>$sales);
            }catch(PDOException $error){
                return array("status"=>"302", "message"=>"Error al consultar las notas de venta.", "query"=>"{$sql}", "error_detail"=>"{$error->getMessage()}");
            }
        }
        public function getSales( $folio = '', $start = 0, $limit = 20, $order_by = 'ASC' ){
            $resp = "";
            $sql = "SELECT 
                        p.id_pedido, 
                        s.nombre AS store_name,
                        p.folio_nv, 
                        p.id_cliente,
                        p.total
                    FROM ec_pedidos p
                    LEFT JOIN sys_sucursales s
                    ON p.id_sucursal = s.id_sucursal
                    WHERE 1";
            $sql .= ( $folio == '' ? "" : " AND p.folio_nv LIKE '%{$folio}%'" );
            $sql .= " ORDER BY p.id_pedido ";
            if( $start != 0 ){
                $sql .= " LIMIT {$start}, $limit";
            }else{
                $sql .= " LIMIT $limit";
            }
            try{
                $stm = $this->link->query( $sql ) or die( "Error al consultar la venta  : {$sql} : {$this->link->error}" );
            }catch( PDOException $e ){
                die( "Error al consultar las notas de venta : {$sql} : {$e}" );
            }
            $c = 0;
            if( $stm->rowCount() <= 0 ){
                return "<tr><td colspan=\"10\" class=\"text-center\">Sin resultados.</td></tr>";
            }
            while( $r = $stm->fetch( PDO::FETCH_ASSOC ) ){
                $c ++;
                $resp .= $this->build_row_ceil( $r, $c );
            }
            return $resp;
        }

        public function build_row_ceil( $r, $c ){
            //$c++;//incrementamos contador
            $resp =  '<tr id="fila_'.$c.'" tabindex="'.$c.'" onfocus="resalta('.$c.');" onclick="resalta('.$c.');" onblur="quita_resaltado('.$c.');">';
                $resp .= '<td>'.$r['id_pedido'].'</td>';
                $resp .= '<td>'.$r['store_name'].'</td>';
                $resp .= '<td>'.$r['folio_nv'].'</td>';
                $resp .= '<td class="text-center">'.$r['id_cliente'].'</td>';
                $resp .= '<td>'.$r['total'].'</td>';
                $resp .= "<td class=\"text-center\">
                    <button
						type=\"button\"
						class=\"btn\"
						onclick=\"saleDetail( {$r['id_pedido']} , 0 );\"
					>
						<i class=\"icon-eye\"></i>
					</button>
                </td>";
            $resp .= '</tr>'; 
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
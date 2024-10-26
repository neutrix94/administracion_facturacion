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

            case 'getSales':
                $start = ( isset( $_POST['start'] ) ? $_POST['start'] : $_GET['start'] );
                $folio = ( isset( $_POST['limit'] ) ? $_POST['limit'] : $_GET['limit'] );
                echo $SalesDB->getSales( '', $start, $limit, 'DESC' );//fl=&folio=${text}&start=${start_position}&limit=${limit}
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
        public function getSales( $folio = '', $start = 0, $limit = 30, $order_by = 'ASC' ){
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
            if( $start != 0 ){
                $sql .= " LIMIT {$start}, $limit";
            }else{
                $sql .= " LIMIT $limit";
            }
            $sql .= "ORDER BY p.id_pedido ";
            $stm = $this->link->query( $sql ) or die( "Error al consultar la venta  : {$sql} : {$this->link->error}" );
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
                        onclick=\"muestra_datos_RS( {$r['id_pedido']} , 0 );\"
                    >
                        <i class=\"icon-eye\"></i>
                    </button>
                </td>
                <td class=\"text-center\">
                    <button
                        type=\"button\"
                        class=\"btn\"
                        onclick=\"muestra_datos_RS( {$r['id_pedido']} , 2 );\"
                    >
                        <i class=\"icon-pencil\"></i>
                    </button>
                </td>
                <td class=\"text-center\">
                    <button
                        type=\"button\"
                        class=\"btn\"
                        onclick=\"muestra_datos_RS( {$r['id_pedido']} , 3 );\"
                    >
                        <i class=\"icon-cancel\"></i>
                    </button>
                </td>";
            $resp .= '</tr>'; 
            return $resp;
        }
    }
    
?>
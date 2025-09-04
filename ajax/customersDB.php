<?php
    if( isset( $_POST['fl'] ) || isset( $_GET['fl'] ) ){
        $action = ( isset( $_POST['fl'] ) ? $_POST['fl'] : $_GET['fl'] );
        include( '../include/db.php' );
        $db = new db();
        $link = $db->conectDB();
        $CustomersDB = new CustomersDB( $link );
        switch ( $action ) {
            case 'seekSaleByFolio':
                $folio = ( isset( $_POST['folio'] ) ? $_POST['folio'] : $_GET['folio'] );
                echo $CustomersDB->getSales( $folio );
            break;

            case 'getSales':
                $start = ( isset( $_POST['start'] ) ? $_POST['start'] : $_GET['start'] );
                $folio = ( isset( $_POST['limit'] ) ? $_POST['limit'] : $_GET['limit'] );
                echo $CustomersDB->getSales( '', $start, $limit, 'DESC' );//fl=&folio=${text}&start=${start_position}&limit=${limit}
            break;

            case 'getSpecificCustomer' :
                $search = (isset($_GET['text']) ? $_GET['text'] : $_POST['text']);
                echo $CustomersDB->seekCustomers($search);
            break;
            
            default:
                die( "Permission denied on : '{$action}'." );
            break;
        }
    }
    final class CustomersDB{
        private $link;
        public function __construct( $connection ) {
            $this->link = $connection;
        }
        public function getCustomers( $folio = '', $start = 0, $limit = 30, $order_by = 'ASC', $search = "" ){
            $resp = array();
            try{
                $sql = "SELECT 
                        id_cliente_facturacion, 
                        rfc, 
                        razon_social, 
                        cp 
                    FROM vf_clientes_razones_sociales
                    WHERE id_cliente_facturacion >= 10000 
                    ";
                if($search != '' && $search != null){
                    $sql .= "AND (rfc LIKE '%$search%' ";
                //busqueda por nombre
                    $sql .= "OR (";
                    $name_array = explode(" ", $search);
                    foreach ($name_array as $key => $word) {
                        $sql .= $key > 0 ? " AND " : "";
                        $sql .= "razon_social LIKE '%{$word}%'";
                    }
                    $sql .= "))";
                    
                }
                $sql .= " ORDER BY id_cliente_facturacion DESC";//LIMIT 20
                $stm = $this->link->query( $sql );
                while($row = $stm->fetch(PDO::FETCH_ASSOC)){
                    $resp[] = $row;
                }
            }catch(PDOException $error){
                die(json_encode( array("status"=>302, "message"=>"Error al listar las razones sociales.", "query"=>$sql, "error_detail"=>"{$error->getMessage()}")));
            }
            return $resp;
        }

        public function build_row_ceil( $r, $c ){
            $resp = '<tr id="fila_'.$c.'" tabindex="'.$c.'" onfocus="resalta('.$c.');" onclick="resalta('.$c.');" onblur="quita_resaltado('.$c.');">';
                $resp .= '<td>'.$r['id_cliente_facturacion'].'</td>';
                $resp .= '<td>'.$r['rfc'].'</td>';
                $resp .= '<td>'.$r['razon_social'].'</td>';
                $resp .= '<td class="text-center">'.$r['cp'].'</td>';
            return $resp;
        }
    //consulta detalle de una venta en especifico
        public function seekCustomers($search){
            $results = $this->getCustomers('', 0, 30, "ASC", $search);//echo json_encode($CustomersDB->getCustomers('', 0, 30, "ASC", $search));
            $resp = "";
            $c = 0;
            foreach ($results as $key => $r) {
                $c ++;
                $resp .= $this->build_row_ceil($r, $c);   
            }
            return $resp;
        }
    }
?>
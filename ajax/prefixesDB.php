<?php
    if( isset( $_POST['fl'] ) || isset( $_GET['fl'] ) ){
        $action = ( isset( $_POST['fl'] ) ? $_POST['fl'] : $_GET['fl'] );
        include( '../include/db.php' );
        $db = new db();
        $link = $db->conectDB();
        $SetsPrefixesDB = new SetsPrefixesDB( $link );
        switch ( $action ) {
            case 'seekSaleByFolio':
                $folio = ( isset( $_POST['folio'] ) ? $_POST['folio'] : $_GET['folio'] );
                echo $SetsPrefixesDB->getSales( $folio );
            break;

            case 'getSales':
                $start = ( isset( $_POST['start'] ) ? $_POST['start'] : $_GET['start'] );
                $folio = ( isset( $_POST['limit'] ) ? $_POST['limit'] : $_GET['limit'] );
                echo $SetsPrefixesDB->getSales( '', $start, $limit, 'DESC' );//fl=&folio=${text}&start=${start_position}&limit=${limit}
            break;

            case 'getSpecificCustomer' :
                $search = (isset($_GET['text']) ? $_GET['text'] : $_POST['text']);
                echo $SetsPrefixesDB->seekCustomers($search);
            break;

            case 'getSetPrefix' :
                $prefix_id = ( isset( $_POST['id'] ) ? $_POST['id'] : $_GET['id'] );
                $type = ( isset( $_POST['type'] ) ? $_POST['type'] : $_GET['type'] );
                echo $SetsPrefixesDB->getSetPrefix($prefix_id, $type);
            break;

            case 'saveSetPrefix' :
                $set_prefix_id = (isset($_GET['set_prefix_id']) ? $_GET['set_prefix_id'] : $_POST['set_prefix_id']);
                $set_prefix_name = (isset($_GET['set_prefix_name']) ? $_GET['set_prefix_name'] : $_POST['set_prefix_name']);
                $set_prefix_status = (isset($_GET['set_prefix_status']) ? $_GET['set_prefix_status'] : $_POST['set_prefix_status']);
                //$set_prefix_date = (isset($_GET['set_prefix_date']) ? $_GET['set_prefix_date'] : $_POST['set_prefix_date']);
                echo json_encode( $SetsPrefixesDB->saveSetPrefix($set_prefix_id, $set_prefix_name, $set_prefix_status) );
            break;
            
            default:
                die( "Permission denied on : '{$action}'." );
            break;
        }
    }
    final class SetsPrefixesDB{
        private $link;
        public function __construct( $connection ) {
            $this->link = $connection;
        }
        public function getSetsPrefixes( $folio = '', $start = 0, $limit = 30, $order_by = 'ASC', $search = "" ){
            $resp = array();
            try{
                $sql = "SELECT 
                        id_prefijo_set, 
                        nombre, 
                        habilitado, 
                        fecha_alta 
                    FROM prefijos_sets
                    WHERE 1";
                if($search != '' && $search != null){
                //busqueda por nombre
                    $sql .= "AND (";
                    $name_array = explode(" ", $search);
                    foreach ($name_array as $key => $word) {
                        $sql .= $key > 0 ? " AND " : "";
                        $sql .= "razon_social LIKE '%{$word}%'";
                    }
                    $sql .= ")";//)
                }
                $sql .= " ORDER BY id_prefijo_set ASC";//LIMIT 20
                $stm = $this->link->query( $sql );
                while($row = $stm->fetch(PDO::FETCH_ASSOC)){
                    $resp[] = $row;
                }
            }catch(PDOException $error){
                die(json_encode( array("status"=>302, "message"=>"Error al consultar prefijos.", "query"=>$sql, "error_detail"=>"{$error->getMessage()}")));
            }
            return $resp;
        }

        function saveSetPrefix($set_prefix_id, $set_prefix_name, $set_prefix_status){
            $sql = "";
            $action = "";
            if( $set_prefix_id != '' && $set_prefix_id != null && $set_prefix_id != NULL){
                $action = "actualizado";
                $sql = "UPDATE prefijos_sets SET nombre = '{$set_prefix_name}', habilitado = '{$set_prefix_status}', fecha_alta = NOW() WHERE id_prefijo_set = {$set_prefix_id}";
            }else{
                $action = "insertado";
                $sql = "INSERT INTO prefijos_sets (nombre, habilitado, fecha_alta) VALUES ('{$set_prefix_name}', '{$set_prefix_status}', NOW())";
            }
            try{
                $this->link->query($sql);
                if($action == "insertado"){
                    try{
                        $sql = "SELECT LAST_INSERT_ID() AS last_id";
                        $stm = $this->link->query($sql);
                        $row = $stm->fetch(PDO::FETCH_ASSOC);
                        $set_prefix_id = $row['last_id'];
                    }catch(PDOException $error){
                        die(json_encode(array("status"=>"302", "message"=>"Error al recuperar el id del prefijo de set", "query"=>"{$sql}", "error_detail"=>"{$error->getMessage()}")));
                    }
                }
                $post_data = array("set_prefix"=>array("id_prefijo_set"=>"{$set_prefix_id}", "nombre"=>"{$set_prefix_name}", "habilitado"=>"{$set_prefix_status}" ));
                $set_synchronization = $this->sets_prefixes_sincronization($post_data);
            }catch(PDOException $error){
                die(json_encode(array("status"=>"302", "message"=>"Error al insertar / actualizar prefijo de set", "query"=>"{$sql}", "error_detail"=>"{$error->getMessage()}")));
            }
            return array("status"=>"200","action"=>"{$action}", "message"=>"Set {$action} exitosamente.", "id"=>"{$set_prefix_id}");
        }

        public function build_row_ceil( $r, $c ){
            //$c++;//incrementamos contador
            $resp = "";
				//$c++;//incrementamos contador
            $resp .= '<tr id="fila_'.$c.'" tabindex="'.$c.'" onfocus="resalta('.$c.');" onclick="resalta('.$c.');" onblur="quita_resaltado('.$c.');">';
                $resp .= '<td>'.$r['id_prefijo_set'].'</td>';
                $resp .= '<td>'.$r['nombre'].'</td>';
                $resp .= '<td class="text-center"><input type="checkbox" '.($r['habilitado'] == 1 ? 'checked' : '').'></td>';
                $resp .= '<td class="text-center">'.$r['fecha_alta'].'</td>';
                $resp .= "<td class=\"text-center\">
                    <button
                        type=\"button\"
                        class=\"btn\"
                        onclick=\"show_set_prefixes_form( {$r['id_prefijo_set']} , 0 );\"
                    >
                        <i class=\"icon-eye\"></i>
                    </button>
                </td>
                <td class=\"text-center\">
                    <button
                        type=\"button\"
                        class=\"btn\"
                        onclick=\"show_set_prefixes_form( {$r['id_prefijo_set']} , 2 );\"
                    >
                        <i class=\"icon-pencil\"></i>
                    </button>
                </td>
                <td class=\"text-center\">
                    <button
                        type=\"button\"
                        class=\"btn\"
                        onclick=\"show_set_prefixes_form( {$r['id_prefijo_set']} , 3 );\"
                    >
                        <i class=\"icon-cancel\"></i>
                    </button>
                </td>";
            $resp .= '</tr>'; 
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

        function getSetPrefix( $prefix_id, $type){
            try{
                $sql = "SELECT
                            id_prefijo_set,
                            nombre,
                            habilitado,
                            fecha_alta
                        FROM prefijos_sets
                        WHERE id_prefijo_set = {$prefix_id}";
                $stm = $this->link->query($sql);
                $prefix_row = $stm->fetch(PDO::FETCH_ASSOC);
                include( '../include/forms/setsPrefixesForm.php' );
            }catch(PDOException $error){
                die(json_encode(array("status"=>"302", "message"=>"Error al consultar prefijo de set", "query"=>"{$sql}", "error_detail"=>"{$error->getMessage()}")));
            }
            
        }
//consume servicio para insertar / actualizar prefijos en las razones sociales
        function sets_prefixes_sincronization($post_data){
        //consulta URL de sistema de administracion facturacion
            $url = "";
			$resp = "";
            try{
                $sql = "SELECT `value` AS api_path FROM api_config WHERE `name` = 'path_facturacion'";
                $stm = $this->link->query($sql);
                $row = $stm->fetch(PDO::FETCH_ASSOC);
                $url = "{$row['api_path']}/rest/sets/prefijos";
            }catch(PDOException $error){
                die("Error al consultar URL de api del set : {$sql} : {$error->getMessage()}");
            }
			$crl = curl_init( $url );
			curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($crl, CURLINFO_HEADER_OUT, true);
			curl_setopt($crl, CURLOPT_POST, true);
			curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data);
		    curl_setopt($crl, CURLOPT_TIMEOUT, 10000);
			curl_setopt($crl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			$resp = curl_exec($crl);//envia peticion
			curl_close($crl);
			return $resp;
        }
    }
    
?>
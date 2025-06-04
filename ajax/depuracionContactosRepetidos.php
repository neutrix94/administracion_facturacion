<?php
    include('../include/db.php');
    $db = new db();
    $link = $db->conectDB();
    try{
        $sql = "SELECT 
                    GROUP_CONCAT( id_cliente_contacto SEPARATOR ',') AS contacts_ids,
                    nombre,
                    telefono,
                    celular,
                    correo,
                    uso_cfdi,
                    COUNT(*)
                FROM vf_clientes_contacto
                GROUP BY nombre, telefono, celular, correo, uso_cfdi
                HAVING COUNT(*) > 1";
        $stm = $link->query($sql);
        $link->beginTransaction();
        $counter = 0;
        while($row = $stm->fetch(PDO::FETCH_ASSOC)){
            $ids = $row['contacts_ids'];
            $contacts_ids = explode(",", $ids);
            foreach ($contacts_ids as $key => $contact) {
                if($key > 0){
                    try{
                        $sql = "DELETE FROM vf_clientes_contacto WHERE id_cliente_contacto = {$contact}";
                        $link->query($sql);
                    }catch(PDOException $error){
                        $link->rollBack();
                        die(json_encode( array( "status"=>302,"message"=>"Error al eliminar cliente repetido : ", "query"=>"{$sql}", "error_detail"=>"{$error->getMessage()}")));
                    }
                    $counter ++;
                }
            }
        }
    }catch(PDOException $error){
        die(json_encode( array( "status"=>302,"message"=>"Error al consultar contactos de clientes repetidos : ", "query"=>"{$sql}", "error_detail"=>"{$error->getMessage()}")));
    }
    $link->commit();
    die("Fueron eliminados {$counter} contactos.");
?>
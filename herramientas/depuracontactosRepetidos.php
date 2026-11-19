<?php
    include( '../include/db.php' );
        $db = new db();
        $link = $db->conectDB();
        /*$sql = "SELECT 
                    COUNT(*), 
                    `id_cliente_facturacion`, 
                    GROUP_CONCAT(id_cliente_contacto) AS id_cliente_contacto, 
                    nombre, 
                    telefono,
                    celular, 
                    correo, 
                    uso_cfdi, 
                    GROUP_CONCAT(folio_unico)
                FROM vf_clientes_contacto 
                GROUP BY nombre, telefono, correo
                HAVING COUNT(*) > 1 
                ORDER BY `vf_clientes_contacto`.`id_cliente_facturacion` ASC";*/
    //
    /*$clientes = array();
    $repetidos = array();
    try{
        $sql = "SELECT id_cliente_facturacion FROM vf_clientes_razones_sociales WHERE id_cliente_facturacion >= 10000";
        $stm = $link->query($sql);
        while($row = $stm->fetch(PDO::FETCH_ASSOC)){
            $clientes[] = $row;
        }
    }catch(PDOException $error){
        die("Error al consultar clientes");
    }
//consulta los contactos de cada cliente
    foreach ($clientes as $key => $cliente) {
        $tmp = null;
        try{
            $sql = "SELECT
                        nombre,
                        telefono,
                        celular,
                        correo,
                        uso_cfdi
                    FROM vf_clientes_contacto
                    WHERE id_cliente_facturacion = {$cliente['id_cliente_facturacion']}";
            $stm = $link->query($sql);
            while($row = $stm->fetch(PDO::FETCH_ASSOC)){
                if($tmp == null){
                    $tmp = $row;
                }else{
                    if($tmp['nombre'] == $row['nombre'] && $tmp['telefono'] == $row['telefono'] && $tmp['celular'] == $row['celular'] && $tmp['correo'] == $row['correo'] && $tmp['uso_cfdi'] == $row['uso_cfdi']){
                        $repetidos[] = $row;
                    }else{
                        $tmp = $row;
                    }
                }
            }
        }catch(PDOException $error){
            die("Error al consultar contactos de clientes");
        }
    }*/

    try{
        $sql = "SELECT b.*, 
                    a.id_cliente_contacto AS id_original
                FROM vf_clientes_contacto a
                JOIN vf_clientes_contacto b
                ON  a.id_cliente_facturacion = b.id_cliente_facturacion
                AND a.nombre   <=> b.nombre
                AND a.telefono <=> b.telefono
                AND a.celular  <=> b.celular
                AND a.correo   <=> b.correo
                AND a.uso_cfdi <=> b.uso_cfdi
                AND a.id_cliente_contacto < b.id_cliente_contacto
                WHERE b.id_cliente_facturacion >= 10000
                AND NOT EXISTS (                       -- para que id_original sea el primero
                    SELECT 1 FROM vf_clientes_contacto x
                    WHERE x.id_cliente_facturacion = a.id_cliente_facturacion
                        AND x.nombre <=> a.nombre AND x.telefono <=> a.telefono
                        AND x.celular <=> a.celular AND x.correo <=> a.correo
                        AND x.uso_cfdi <=> a.uso_cfdi
                        AND x.id_cliente_contacto < a.id_cliente_contacto)";
        $stm = $link->query($sql);
        while($row = $stm->fetch(PDO::FETCH_ASSOC)){
            $sql = "DELETE FROM vf_clientes_contacto WHERE id_cliente_contacto = {$row['id_cliente_contacto']}";
            $link->query($sql);
            echo $sql . "<br>";
        }
    }catch(PDOException $error){
        die("Error : {$error->getMessage()}");
    }
?>
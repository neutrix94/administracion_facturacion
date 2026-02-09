<?php
    if(isset($_POST['config_flag']) || isset($_GET['config_flag'])){
        //var_dump($_POST);
        $action = (isset($_POST['config_flag']) ? $_POST['config_flag'] : $_GET['config_flag']);
        include('../../../db.php');
        $db = new db();
        $link = $db->conectDB();
        $ConfiguracionAmbiente = new ConfiguracionAmbiente($link);
        switch ($action) {
            case 'seekTables':
                //die("HERE : {$action}");
                $text = (isset($_POST['text']) ? isset($_POST['text']) : $_GET['text']);
                echo $ConfiguracionAmbiente->seekTables($text);
            break;

            case 'getTableColumns' :
                $table_name = (isset($_POST['table_name']) ? $_POST['table_name'] : $_GET['table_name']);
                echo $ConfiguracionAmbiente->getTableColumns($table_name);
            break;

            case 'saveTableModule':
                //die("Here");
                //datos de cabecera
                $table_name = (isset($_POST['table_name']) ? $_POST['table_name'] : $_GET['table_name']);
                $config_module_id = (isset($_POST['config_module_id']) ? $_POST['config_module_id'] : $_GET['config_module_id']);
                $where_table_module = (isset($_POST['where_table_module']) ? $_POST['where_table_module'] : $_GET['where_table_module']);
                $title_field = (isset($_POST['title_field']) ? $_POST['title_field'] : $_GET['title_field']);
                $line_check = (isset($_POST['line_check']) ? $_POST['line_check'] : $_GET['line_check']);
                $local_check = (isset($_POST['local_check']) ? $_POST['local_check'] : $_GET['local_check']);
                $details = json_decode(isset($_POST['detail_json']) ? $_POST['detail_json'] : $_GET['detail_json']);
/*faltan las imagenes
    //$table_name = (isset($_POST['table_name']) ? isset($_POST['table_name']) : $_GET['table_name']);
    //$table_name = (isset($_POST['table_name']) ? isset($_POST['table_name']) : $_GET['table_name']);
*/
                echo $ConfiguracionAmbiente->saveTableModule($table_name, $config_module_id, $where_table_module, $title_field, $line_check, $local_check, $details);//
            break;

            case 'saveFileModule' ://config_flag
                
                $file_name = (isset($_POST['file_name']) ? $_POST['file_name'] : $_GET['file_name']);
                $file_route = (isset($_POST['file_route']) ? $_POST['file_route'] : $_GET['file_route']);
                $file_title = (isset($_POST['file_title']) ? $_POST['file_title'] : $_GET['file_title']);
                $line_check = (isset($_POST['line_check']) ? $_POST['line_check'] : $_GET['line_check']);
                $local_check = (isset($_POST['local_check']) ? $_POST['local_check'] : $_GET['local_check']);
                $module_vars = json_decode(isset($_POST['module_vars']) ? $_POST['module_vars'] : $_GET['module_vars']);
                echo json_encode($ConfiguracionAmbiente->saveFileModule($file_name, $file_route, $file_title, $line_check, $local_check, $module_vars));
                //saveFileModule();
            break;

            case 'getConfigModules':
                echo json_encode($ConfiguracionAmbiente->getConfigModules());
            break;

            case 'getConfigFiles' : 
                echo json_encode($ConfiguracionAmbiente->getConfigFiles());
            break;
        //guardar todas las configuraciones
            case 'saveConfigurations':
                $json_data = json_decode(isset($_POST['json_data']) ? $_POST['json_data'] : $_GET['json_data']);
                $files_data = json_decode(isset($_POST['files_data']) ? $_POST['files_data'] : $_GET['files_data']);
                echo json_encode($ConfiguracionAmbiente->saveConfigurations($json_data, $files_data), true);
            break;
            
            default:
                die(json_encode(array("status"=>"400", "message"=>"permission denied on '{$action}'.")));
            break;
        }
    }
    class ConfiguracionAmbiente{
        private $link;
        public function __construct($connection) {
            $this->link = $connection;
        }

        function getCurrentBranch(){
            $output = [];
            $return_var = 0;
            exec('git branch --show-current', $output, $return_var);//git branch -a
            return $output[0];
        }

        function seekTables($text){
            include('../../../../config.inc.php');
            $tables = array();
            try{
                $sql = "SELECT
                            TABLE_NAME AS table_name
                        FROM information_schema.TABLES
                        WHERE TABLE_SCHEMA = '{$dbName}'
                        AND TABLE_NAME LIKE '%{$text}%'";
                $stm = $this->link->query($sql);
                if($stm->rowCount() <= 0){
                    return json_encode(array("status"=>"302", "message"=>"Sin coincidencias : {$sql}"));
                }else{
                    while($row = $stm->fetch(PDO::FETCH_ASSOC)){
                        $tables[] = $row;
                    }
                    return json_encode(array("status"=>"200", "tables"=>$tables));
                }
            }catch(PDOException $error){
                die(json_encode(array("status"=>"302", "message"=>"Error al consultar coincidencias de nombres de tablas.", "query"=>"{$sql}", "error_detail"=>"{$error->getMessage()}")));
            }
        }

        function getTableColumns($table_name){
            include('../../../../config.inc.php');
            $columns = array();
            try{
                $sql = "SELECT
                            ORDINAL_POSITION AS column_order,
                            COLUMN_NAME AS column_name,
                            DATA_TYPE AS column_data_type,
                            COLUMN_KEY AS column_key
                        FROM information_schema.`COLUMNS`
                        WHERE `TABLE_SCHEMA` = '{$dbName}' 
                        AND `TABLE_NAME` = '{$table_name}'";
                $stm = $this->link->query($sql);
                while($row = $stm->fetch(PDO::FETCH_ASSOC)){
                    $columns[] = $row;
                }
                if($stm->rowCount() <= 0){
                    return json_encode(array("status"=>"302", "message"=>"Sin coincidencias"));
                }else{
                    return json_encode(array("status"=>"200", "columns"=>$columns));
                }
            }catch(PDOException $error){
                die(json_encode(array("status"=>"302", "message"=>"Error al consultar campos de la tabla.", "query"=>"{$sql}", "error_detail"=>"{$error->getMessage()}")));
            }
        }

        function saveTableModule($table_name, $config_module_id, $where_table_module, $title_field, $line_check, $local_check, $details){
            $header_id = null;
        //inserta cabecera
            try{
                $sql = "INSERT INTO `sys_configuracion_modulos_tablas`(`id_configuracion_modulo`, `nombre_tabla`, `condicion`, `titulo`, `linea`, `local`) 
                        VALUES ('{$config_module_id}','{$table_name}','{$where_table_module}','{$title_field}','{$line_check}','{$local_check}')";
                $this->link->query($sql);
                $header_id = $this->link->lastInsertId();
            }catch(PDOException $error){
                die(json_encode(array("status"=>"302", "message"=>"Error al insertar modulo de tabla.", "query"=>"{$sql}", "error_detail"=>"{$error->getMessage()}")));
            }
        //inserta detalle
            foreach ($details as $key => $detail) {
                try{
                    $sql = "INSERT INTO `sys_configuracion_modulos_tablas_campos`(`id_configuracion_modulo_tabla`, `nombre_campo`, `obligatorio`, `texto_ayuda`, `tipo_campo`) 
                            VALUES ('{$header_id}','{$detail->field_name}','{$detail->required}','{$detail->help_text}','{$detail->data_type}')";
                    $this->link->query($sql);
                }catch(PDOException $error){
                    die(json_encode(array("status"=>"302", "message"=>"Error al insertar detalle de  modulo de tabla.", "query"=>"{$sql}", "error_detail"=>"{$error->getMessage()}")));
                }
            }
            return json_encode(array("status"=>"200", "message"=>"Modulo insertado exitosamente."));
        }
        function saveFileModule($file_name, $file_route, $file_title, $line_check, $local_check, $module_vars){
            $header_id = null;
            $this->link->beginTransaction();
        //inserta cabecera
            try{
                $sql = "INSERT INTO `sys_configuracion_modulos_archivos`(`ruta_archivo`, `nombre_modulo`, `nombre_archivo`, `linea`, `local`) 
                VALUES ('{$file_route}', '{$file_title}', '{$file_name}','{$line_check}','{$local_check}')";
                $this->link->query($sql);
                $header_id = $this->link->lastInsertId();
            }catch(PDOException $error){
                $this->link->rollBack();
                die(json_encode(array("status"=>"302", "message"=>"Error al insertar modulo de archivo.", "query"=>"{$sql}", "error_detail"=>"{$error->getMessage()}")));
            }
        //inserta detalle
            foreach ($module_vars as $key => $var) {
                try{
                    $sql = "INSERT INTO `sys_configuracion_modulos_archivos_variables`(`id_configuracion_modulo_archivo`, `nombre_variable`, `tipo_variable` ) 
                    VALUES ('{$header_id}', '{$var->var_name}', '{$var->var_type}')";
                    $this->link->query($sql);
                }catch(PDOException $error){
                    $this->link->rollBack();
                    die(json_encode(array("status"=>"302", "message"=>"Error al insertar detalle de  modulo de archivo.", "query"=>"{$sql}", "error_detail"=>"{$error->getMessage()}")));
                }
            }
            $this->link->commit();
            return json_encode(array("status"=>"200", "message"=>"Modulo insertado exitosamente."));
        }
//obtener los modulos de bases de datos
        function getConfigModules(){
            $modules = array();
            try{
                $sql = "SELECT
                        id_configuracion_modulo AS module_config_id,
                        nombre_modulo AS module_name
                    FROM sys_configuracion_modulos";
                $stm = $this->link->query($sql);
                while($row = $stm->fetch(PDO::FETCH_ASSOC)){
                    $row['module_tables'] = $this->getModulesTables($row['module_config_id']);
                    $modules[] = $row;
                }
                return array("status"=>"200", "modules"=>$modules);
            }catch(PDOException $error){
                die(json_encode(array("status"=>"302", "message"=>"Error al consultar modulos de tablas.", "query"=>"{$sql}", "error_detail"=>"{$error->getMessage()}")));
            }
        }

        function getConfigFiles(){
            $modules = array();
            try{
                $sql = "SELECT
                        id_configuracion_modulo_archivo AS file_module_config_id,
                        nombre_modulo AS file_module_name,
                        ruta_archivo AS file_directory,
                        nombre_archivo AS file_name
                    FROM sys_configuracion_modulos_archivos";
                $stm = $this->link->query($sql);
                while($row = $stm->fetch(PDO::FETCH_ASSOC)){
                    $row['file_vars'] = $this->getFileVars($row['file_module_config_id']);
                    $modules[] = $row;
                }
                return array("status"=>"200", "modules"=>$modules);
            }catch(PDOException $error){
                die(json_encode(array("status"=>"302", "message"=>"Error al consultar modulos de archivos.", "query"=>"{$sql}", "error_detail"=>"{$error->getMessage()}")));
            }
        }

//obtener las tablas por modulo
        function getModulesTables($module_config_id){
            $module_tables = array();
            try{
                $sql = "SELECT
                            id_configuracion_modulo_tabla AS module_table_config_id,
                            nombre_tabla AS table_name,
                            condicion AS query_condition,
                            titulo AS table_title_field
                        FROM sys_configuracion_modulos_tablas
                        WHERE id_configuracion_modulo = {$module_config_id}";
                $stm = $this->link->query($sql);
                while($row = $stm->fetch(PDO::FETCH_ASSOC)){
                    $row['table_fields'] = $this->getModulesTablesFields($row['module_table_config_id']);
                    $row['values'] = $this->getModulesTablesValues($row);
                    $module_tables[] = $row;
                }
                return $module_tables;
            }catch(PDOException $error){
                die(json_encode(array("status"=>"302", "message"=>"Error al consultar tablas de modulos.", "query"=>"{$sql}", "error_detail"=>"{$error->getMessage()}")));
            }
        }

//obtener variables por archivo
        function getFileVars($file_module_config_id){
            $fileVars = array();
            try{
                $sql = "SELECT
                            id_configuracion_modulo_archivo_variable AS file_module_var_id,
                            nombre_variable AS var_name,
                            tipo_variable AS var_type,
                            valor AS var_value
                        FROM sys_configuracion_modulos_archivos_variables
                        WHERE id_configuracion_modulo_archivo = {$file_module_config_id}";
                $stm = $this->link->query($sql);
                while($row = $stm->fetch(PDO::FETCH_ASSOC)){
                    $fileVars[] = $row;
                }
                return $fileVars;
            }catch(PDOException $error){
                die(json_encode(array("status"=>"302", "message"=>"Error al consultar variables de archivo.", "query"=>"{$sql}", "error_detail"=>"{$error->getMessage()}")));
            }
        }

//obtener los campos de tablas por modulo
        function getModulesTablesFields($module_table_config_id){
            $module_table_fields = array();
            try{
                $sql = "SELECT
                        nombre_campo AS field_name,
                        obligatorio AS is_required,
                        texto_ayuda AS help_text,
                        ruta_imagen AS img_url,
                        tipo_campo AS data_type
                    FROM sys_configuracion_modulos_tablas_campos
                    WHERE id_configuracion_modulo_tabla = {$module_table_config_id}";
                $stm = $this->link->query($sql);
                while($row = $stm->fetch(PDO::FETCH_ASSOC)){
                    $module_table_fields[] = $row;
                }
                return $module_table_fields;//json_encode(array("status"=>"200", "module_table_fields"=>$module_table_fields));
            }catch(PDOException $error){
                die(json_encode(array("status"=>"302", "message"=>"Error al consultar campos de tablas.", "query"=>"{$sql}", "error_detail"=>"{$error->getMessage()}")));
            }
        }

//consultar
        function getModulesTablesValues($structure){
            $query_values = array();
            $fields = "";
            $condition = "";
            $sql = "SELECT ";
        //campos
            foreach ($structure['table_fields'] as $key => $field) {
                $fields .= ($fields == "" ? "" : ", ");
                $fields .= $field['field_name'];
            }
            $sql .= "{$fields} FROM {$structure['table_name']} {$structure['query_condition']}";
            try{
                $stm = $this->link->query($sql);
                while($row = $stm->fetch(PDO::FETCH_ASSOC)){
                    $query_values[] = $row;
                }
                return $query_values;
                //die(json_encode($query_values));
            }catch(PDOException $error){
                die(json_encode(array("status"=>"302", "message"=>"Error al consultar valores para frontend.", "query"=>"{$sql}", "error_detail"=>"{$error->getMessage()}")));
            }
        }

        function saveConfigurations($json_data, $files_data){
            $queries = array();
        //tablas de modulos
            foreach ($json_data as $key1 => $module) {//<MODULOS>
                foreach ($module as $key2 => $table) {//<TABLAS>
                    $sql = "UPDATE `{$table->table_name_data}` SET";
                    $pk_value = null;
                    foreach ($table as $key3 => $field) {//<CAMPOS>
                        if($key3 == $table->table_primary_key){
                            $pk_value = $field;
                        }else{
                            if($key3 != 'table_primary_key' && $key3 != 'table_name_data'){
                                $sql .= " `{$key3}` = '{$field}',";
                            }
                        }
                    }//</CAMPOS>
                    $sql .= "WHERE {$table->table_primary_key} = {$pk_value}";
                    $sql = str_replace(",WHERE", " WHERE", $sql);
                    $queries[] = $sql;
                }//</TABLAS>
            }//</MODULOS>
        //variables de tablas
        //var_dump($files_data);return '';
            foreach ($files_data as $key => $file) {
                foreach ($file->vars as $key1 => $data) {
                    $data->file_var_value = str_replace("'", "\'", $data->file_var_value);
                    $sql = "UPDATE sys_configuracion_modulos_archivos_variables SET valor = '{$data->file_var_value}' WHERE id_configuracion_modulo_archivo_variable = {$data->file_var_primary_key}";
                    $queries[] = $sql;
                }
            }
        //itera y ejecuta consultas
            $this->link->beginTransaction();
            foreach ($queries as $key => $sql) {
                try{
                    $this->link->query($sql);
                }catch(PDOException $error){
                    $this->link->rollBack();
                    die(json_encode(array("status"=>"302", "message"=>"Error al actualizar los valores en saveConfigurations.", "query"=>"{$sql}", "error_detail"=>"{$error->getMessage()}")));
                }
            }
            $this->link->commit();
            return array("status"=>"200", "message"=>"Datos actualizados exitosamente.");
        }
    }
    
?>
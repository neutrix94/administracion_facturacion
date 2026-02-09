<?php
    include('../../../db.php');
    $db = new db();
    $link = $db->conectDB();
    $modules = "<select id=\"config_module_id\" class=\"form-select\">
        <option value=\"0\">--SELECCIONAR--</option>";
    try{
        $sql = "SELECT
                    id_configuracion_modulo AS module_config_id,
                    nombre_modulo AS module_name
                FROM sys_configuracion_modulos
                WHERE tipo_configuracion_modulo = 'BASE DATOS'";
        $stm = $link->query($sql);
        while($row = $stm->fetch(PDO::FETCH_ASSOC)){
            $modules .= "<option value=\"{$row['module_config_id']}\">{$row['module_name']}</option>";
        }
    }catch(PDOException $error){
        
    }
    $modules .= "</select>";
?>
<div class="row p-3">
    <h3>Configuración de Tabla</h3>
    <div class="col-1 p-1 text-center">
            ID :
    </div>
    <div class="col-5 p-1 text-center">
        <input type="text" id="table_module_id" class="form-control" value="" readonly>
    </div>

    <div class="col-1 p-1 text-center">
        TABLA :
    </div>
    <div class="col-5">
        <div class="input-group">
            <input type="text" class="form-control" id="table_seeker_input" onkeyup="seekTable(event);">
            <button
                type="button"
                class="btn btn-primary"
                id="table_seeker_btn"
                onclick="seekTable(event);"
            >
                <i class="icon-search"></i>
            </button>
        </div>
        <div id="table_seeker_response"></div>
    </div>

    <div class="col-1 p-1 text-center">
        MÓDULO :
    </div>
    <div class="col-5 p-1">
        <?php
            echo "{$modules}";
        ?>
    </div>

    <div class="col-1 p-1 text-center">
        CONDICIÓN :
    </div>
    <div class="col-5 p-1">
        <textarea id="where_table_module" class="form-control"></textarea>
    </div>
    
    <!--div class="col-1 p-1">
        ORDENAMIENTO :
    </div>
    <div class="col-5 p-1">
        <input type="text" class="form-control">
    </div-->

    <div class="col-1 p-1 text-center">
        TÍTULO :
    </div>
    <div class="col-5 p-1">
        <select class="form-select" id="title_field">
        </select>
        <!--input type="text" class="form-control"-->
    </div>

    <div class="col-1 p-1 text-center">
        LINEA :
    </div>
    <div class="col-2 p-1 text-start">
        <input type="checkbox" id="line_check" style="transform:scale(2)" checked>
    </div>
    <div class="col-1 p-1 text-center">
        LOCAL :
    </div>
    <div class="col-2 p-1 text-start">
        <input type="checkbox" id="local_check" style="transform:scale(2)" checked>
    </div>

    <div class="col-6 p-1 text-start">
        <button
            type="button"
            id="save_table_btn"
            class="btn btn-success form-control"
            onclick="saveTableModule();"
        >
            <i class="icon-floppy-1">Guardar</i>
        </button>
    </div>
</div>

<div class="row p-3">
    <h3>Campos</h3>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th></th>
                <th class="text-center">#</th>
                <th class="text-center">Nombre Campo</th>
                <th class="text-center">Tipo Campo</th>
                <th class="text-center">Requerido</th>
                <th class="text-center">Texto ayuda</th>
                <th class="text-center">Ruta imagen</th>
            </tr>
        </thead>
        <tbody id="fields_content">
        </tbody>
    </table>
</div>
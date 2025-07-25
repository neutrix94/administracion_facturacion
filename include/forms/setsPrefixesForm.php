<br><br>
<div class="text-end">
    <button
        type="button"
    >
        X
    </button>
</div>
<h3 class="text-center">Prefijos de SET</h3>
<div class="row p-3">
    <div class="col-6">
        <div class="row p-1">
            <div class="col-4">ID</div>
            <div class="col-8">
                <input type="text" id="set_prefix_id" class="form-control bg-light" value="<?php echo( isset( $prefix_row['id_prefijo_set'] ) ? $prefix_row['id_prefijo_set'] : '' )?>" readonly>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="row p-1">
            <div class="col-4">Nombre</div>
            <div class="col-8">
                <input type="text" id="set_prefix_name" class="form-control bg-light" value="<?php echo( isset( $prefix_row['nombre'] ) ? $prefix_row['nombre'] : '' )?>">
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="row p-1">
            <div class="col-4">Habilitado</div>
            <div class="col-8">
                <input type="checkbox" id="set_prefix_status" <?php echo( isset( $prefix_row['habilitado'] ) ? ($prefix_row['habilitado'] == 1 ? 'checked' : '') : 'checked' )?>>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="row p-1">
            <div class="col-4">Fecha de alta</div>
            <div class="col-8">
                <input type="text" id="set_prefix_date" class="form-control bg-light" value="<?php echo( isset( $prefix_row['fecha_alta'] ) ? $prefix_row['fecha_alta'] : '' )?>" readonly>
            </div>
        </div>
    </div>
    <div class="col-2"></div>
    <div class="col-8 text-center p-2">
        <br>
    <?php
        if(isset($type) && $type == "3"){
    ?>
        <button class="btn btn-danger form-control" onclick="delete_set_prefix();" id="">
            <i class="icon-ok-circled">Eliminar</i>
        </button>  
    <?php
        }else{
    ?> 
        <button class="btn btn-success form-control" onclick="save_set_prefix();" id="">
            <i class="icon-ok-circled">Guardar</i>
        </button>  
    <?php
        }
    ?>
    </div>
</div>
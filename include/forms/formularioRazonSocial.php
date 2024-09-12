<div class="row">
    <div class="col-6">
        <div class="row p-1">
            <div class="col-4">ID</div>
            <div class="col-8">
                <input type="text" id="id_razon_social" class="form-control" value="<?php echo( isset( $rS['id_razon_social'] ) ? $rS['id_razon_social'] : '' )?>" disabled>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="row p-1">
            <div class="col-4">Host</div>
            <div class="col-8">
                <input type="text" id="host_db" class="form-control" value="<?php echo( isset( $rS['host_db'] ) ? $rS['host_db'] : '' )?>">
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="row p-1">
            <div class="col-4">Nombre</div>
            <div class="col-8">
                <input type="text" id="nombre" class="form-control" value="<?php echo( isset( $rS['nombre'] ) ? $rS['nombre'] : '' )?>">
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="row p-1">
            <div class="col-4">Usuario de BD</div>
            <div class="col-8">
                <input type="text" id="usuario_db" class="form-control" value="<?php echo( isset( $rS['usuario_db'] ) ? $rS['usuario_db'] : '' )?>">
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="row p-1">
            <div class="col-4">RFC</div>
            <div class="col-8">
                <input type="text" id="rfc" class="form-control" value="<?php echo( isset( $rS['RFC'] ) ? $rS['RFC'] : '' )?>">
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="row p-1">
            <div class="col-4">Nombre Base Datos</div>
            <div class="col-8">
                <input type="text" id="nombre_db" class="form-control" value="<?php echo( isset( $rS['nombre_db'] ) ? $rS['nombre_db'] : '' )?>">
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="row p-1">
            <div class="col-4">Ruta de enlace</div>
            <div class="col-8">
                <input type="text" id="link" class="form-control" value="<?php echo( isset( $rS['link'] ) ? $rS['link'] : '' )?>">
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="row p-1">
            <div class="col-4">Activo</div>
            <div class="col-8 text-center">
                <input type="checkbox" id="activo" class="" <?php echo( isset( $rS['activo'] ) ? 'checked' : '' )?>>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="row p-1">
            <div class="col-4">Orden</div>
            <div class="col-8">
                <input type="text" id="orden" class="form-control" value="<?php echo( isset( $rS['orden'] ) ? $rS['orden'] : '' )?>">
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="row p-1">
            <div class="col-4">Password Base de datos</div>
            <div class="col-8 input group">
                <input type="password" id="contrasena_db" class="form-control" value="<?php echo( isset( $rS['contrasena_db'] ) ? $rS['contrasena_db'] : '' )?>">
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="row p-1">
            <div class="col-4">Máximo de compras</div>
            <div class="col-8">
                <input type="text" id="maximo_compras" class="form-control" value="<?php echo( isset( $rS['maximo_compras'] ) ? $rS['maximo_compras'] : '' )?>">
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="row p-1">
            <div class="col-4">Máximo de Ventas</div>
            <div class="col-8">
                <input type="text" id="maximo_ventas" class="form-control" value="<?php echo( isset( $rS['maximo_ventas'] ) ? $rS['maximo_ventas'] : '' )?>">
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="row p-1">
            <div class="col-4">Compras Actuales</div>
            <div class="col-8">
                <input type="text" id="compras_actuales" class="form-control" value="<?php echo( isset( $rS['compras_actuales'] ) ? $rS['compras_actuales'] : '' )?>">
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="row p-1">
            <div class="col-4">Ventas Actuales</div>
            <div class="col-8">
                <input type="text" id="ventas_actuales" class="form-control" value="<?php echo( isset( $rS['ventas_actuales'] ) ? $rS['ventas_actuales'] : '' )?>">
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="row p-1">
            <div class="col-4">Inventario Precio Compra</div>
            <div class="col-8">
                <input type="text" id="inv_precio_compra" class="form-control" value="<?php echo( isset( $rS['inventario_precio_compra'] ) ? $rS['inventario_precio_compra'] : '' )?>">
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="row p-1">
            <div class="col-4">Inventario Precio Venta</div>
            <div class="col-8">
                <input type="text" id="inv_precio_venta" class="form-control" value="<?php echo( isset( $rS['inventario_precio_venta'] ) ? $rS['inventario_precio_venta'] : '' )?>">
            </div>
        </div>
    </div>

    <div class="col-6">
        <div class="row p-1">
            <div class="col-4">Color</div>
            <div class="col-8">
                <input type="color" id="color" class="form-control" value="<?php echo( isset( $rS['color'] ) ? $rS['color'] : '' )?>">
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="row p-1">
            <div class="col-4">Observaciones</div>
            <div class="col-8">
                <input type="text" id="observaciones" class="form-control" value="<?php echo( isset( $rS['observaciones'] ) ? $rS['observaciones'] : '' )?>">
            </div>
        </div>
    </div>

    <div class="col-6">
        <div class="row p-1">
            <div class="col-4">Ultima actualización</div>
            <div class="col-8">
                <input type="text" id="alta" class="form-control" value="<?php echo( isset( $rS['alta'] ) ? $rS['alta'] : '' )?>">
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="row p-1">
            <div class="col-4">Ultima Modificación</div>
            <div class="col-8">
                <input type="text" id="ultima_modificacion" class="form-control" value="<?php echo( isset( $rS['ultima_modificacion'] ) ? $rS['ultima_modificacion'] : '' )?>">
            </div>
        </div>
    </div>
    <div class="col-12 text-center p-2">
        <br>
        <button class="btn btn-success form-control" onclick="guarda_RS();" id="guardar_rs">
            <i class="icon-ok-circled">Guardar</i>
        </button> 
        <br><br>

        <button class="btn btn-danger form-control" onclick="close_alert();" id="">
            <i class="icon-ok-circled">Cancelar y cerrar</i>
        </button>   
    </div>
</div>
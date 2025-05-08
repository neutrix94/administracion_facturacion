<div class="row">
    <div class="col-6">
        <div class="row p-1">
            <div class="col-4">ID</div>
            <div class="col-8">
                <input type="text" id="id_pedido" class="form-control bg-light" value="<?php echo( isset( $sale['id_pedido'] ) ? $sale['id_pedido'] : '' )?>" readonly>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="row p-1">
            <div class="col-4">Folio</div>
            <div class="col-8">
                <input type="text" id="folio_nv" class="form-control bg-light" value="<?php echo( isset( $sale['folio_nv'] ) ? $sale['folio_nv'] : '' )?>" readonly>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="row p-1">
            <div class="col-4">Cliente</div>
            <div class="col-8">
                <input type="text" id="id_cliente" class="form-control bg-light" value="<?php echo( isset( $sale['id_cliente'] ) ? $sale['id_cliente'] : '' )?>" readonly>
            </div>
        </div>
    </div>
    <!--div class="col-6">
        <div class="row p-1">
            <div class="col-4">Status</div>
            <div class="col-8">
                <input type="text" id="id_estatus" class="form-control bg-light" value="<?php echo( isset( $sale['id_estatus'] ) ? $sale['id_estatus'] : '' )?>" readonly>
            </div>
        </div>
    </div-->
    <div class="col-6">
        <div class="row p-1">
            <div class="col-4">Fecha de alta</div>
            <div class="col-8">
                <input type="text" id="fecha_alta" class="form-control bg-light" value="<?php echo( isset( $sale['fecha_alta'] ) ? $sale['fecha_alta'] : '' )?>" readonly>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="row p-1">
            <div class="col-4">R.S. Emisor</div>
            <div class="col-8">
                <input type="text" id="id_razon_social" class="form-control bg-light" value="<?php echo( isset( $sale['id_razon_social'] ) ? $sale['id_razon_social'] : '' )?>" readonly>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="row p-1">
            <div class="col-4">Subtotal</div>
            <div class="col-8">
                <input type="number" id="subtotal" class="form-control bg-light" value="<?php echo( isset( $sale['subtotal'] ) ? $sale['subtotal'] : '' )?>" readonly>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="row p-1">
            <div class="col-4">Descuento</div>
            <div class="col-8">
                <input type="number" id="subtotal" class="form-control bg-light" value="<?php echo( isset( $sale['subtotal'] ) ? ($sale['subtotal']-$sale['total']) : 0 )?>" readonly>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="row p-1">
            <div class="col-4">Total</div>
            <div class="col-8 text-center">
                <input type="number" id="total" class="form-control bg-light" value="<?php echo( isset( $sale['total'] ) ? $sale['total'] : '' )?>"  readonly>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="row p-1">
            <div class="col-4">USO CFDI</div>
            <div class="col-8">
                <input type="text" id="uso_cfdi" class="form-control bg-light" value="<?php echo( isset( $sale['uso_cfdi'] ) ? $sale['uso_cfdi'] : '' )?>" readonly>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="row p-1">
            <div class="col-4">Status Facturacion</div>
            <div class="col-8 input group">
                <input type="text" id="id_status_facturacion" class="form-control bg-light" value="<?php echo( isset( $sale['id_status_facturacion'] ) ? $sale['id_status_facturacion'] : '' )?>" readonly>
            </div>
        </div>
    </div>
    <div class="col-12">
        <br>
        <h3 class="text-center">Detalle de la venta</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th class="text-center">Id Producto</th>
                    <th class="text-center">Cantidad</th>
                    <th class="text-center">Precio</th>
                    <th class="text-center">Monto</th>
                    <th class="text-center">Folio Único</th>
                </tr>
            </thead>
            <tbody>
    <?php
        $total = 0;
        while($row_detail = $detail_smt->fetch(PDO::FETCH_ASSOC)){
            echo "<tr>
                    <td class=\"text-center\">{$row_detail['id_producto']}</td>
                    <td class=\"text-center\">{$row_detail['cantidad']}</td>
                    <td class=\"text-center\">$ {$row_detail['precio']}</td>
                    <td class=\"text-center\">$ {$row_detail['monto']}</td>
                    <td>{$row_detail['folio_unico']}</td>
                </tr>";
                $total += $row_detail['monto'];
        }
    ?>
            </tbody>
            <tfoot>
                <tr>
                    <th></th>
                    <th></th>
                    <th class="text-center">Total : </th>
                    <th class="text-center">$ <?php echo $total;?></th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="col-2"></div>
    <div class="col-8 text-center p-2">
        <br>
        <button class="btn btn-success form-control" onclick="close_alert();" id="">
            <i class="icon-ok-circled">Aceptar y cerrar</i>
        </button>   
    </div>
</div>
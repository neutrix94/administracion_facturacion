<div>
    <h3 class="text-center pt-3">Bases datos Facturación</h3>
</div>
<?php
    for($i = 0; $i <= 14; $i++){
?>
    <div class="row">
        <h5>RS _ <?php echo $i;?></h5>
        <div class="col-2 p-1 text-center">
            NOMBRE :
        </div>
        <div class="col-4 p-1 text-center">
            <input type="text" class="form-control" readonly>
        </div>
        <div class="col-6 p-1 text-center">
            <input type="text" class="form-control" placeholder="Escribe Nuevo Nombre">
        </div>

        <div class="col-2 p-1 text-center">
            HOST :
        </div>
        <div class="col-4 p-1 text-center">
            <input type="text" class="form-control" readonly>
        </div>
        <div class="col-6 p-1 text-center">
            <input type="text" class="form-control" placeholder="Escribe Nuevo Host">
        </div>

        <div class="col-2 p-1 text-center">
            NOMBRE DB :
        </div>
        <div class="col-4 p-1 text-center">
            <input type="text" class="form-control" readonly>
        </div>
        <div class="col-6 p-1 text-center">
            <input type="text" class="form-control" placeholder="Escribe Nuevo Nombre DB">
        </div>
        
        <div class="col-2 p-1 text-center">
            URL_API :
        </div>
        <div class="col-4 p-1 text-center">
            <input type="text" class="form-control" readonly>
        </div>
        <div class="col-6 p-1 text-center">
            <input type="text" class="form-control" placeholder="Escribe Nueva URL API">
        </div>
    </div>

<?php
    }
?>
<hr>
<div>
    <hr>
    <h3 class="text-center pt-3">Bancos</h3>
</div>
<?php
    for($i = 0; $i <= 2; $i++){
?>
    <div class="row">
        <h5>BANCO _ <?php echo $i;?></h5>
        <div class="col-2 p-1 text-center">
            NOMBRE :
        </div>
        <div class="col-4 p-1 text-center">
            <input type="text" class="form-control" readonly>
        </div>
        <div class="col-6 p-1 text-center">
            <input type="text" class="form-control" placeholder="Escribe Nuevo Nombre">
        </div>

        <div class="col-2 p-1 text-center">
            URL VENTA :
        </div>
        <div class="col-4 p-1 text-center">
            <input type="text" class="form-control" readonly>
        </div>
        <div class="col-6 p-1 text-center">
            <input type="text" class="form-control" placeholder="Escribe Nueva URL Venta">
        </div>

        <div class="col-2 p-1 text-center">
            URL IMPRESION :
        </div>
        <div class="col-4 p-1 text-center">
            <input type="text" class="form-control" readonly>
        </div>
        <div class="col-6 p-1 text-center">
            <input type="text" class="form-control" placeholder="Escribe Nueva URL Impresion">
        </div>
        
        <div class="col-2 p-1 text-center">
            URL CANCELACIÓN :
        </div>
        <div class="col-4 p-1 text-center">
            <input type="text" class="form-control" readonly>
        </div>
        <div class="col-6 p-1 text-center">
            <input type="text" class="form-control" placeholder="Escribe Nueva URL Cancelacion">
        </div>
    </div>

<?php
    }
?>
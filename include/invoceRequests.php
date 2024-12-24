<?php
	session_start();
	include('./db.php');
	$db = new db();
	$link = $db->conectDB();
//instancia clase de solicitudes de factura
    include( './invoiceRequestDB.php' );
    $InvoiceRequestDB = new InvoiceRequestDB( $link );
    //$pages_limit = $InvoiceRequestDB->getPagesLimit();
    $pages_limit = 50;
	$_SESSION['current_view'] = $_POST['action'];
//consulta las sucursales para los filtros
    $stores = $InvoiceRequestDB->getStores();
//consulta las razones sociales para los filtros
    $rss = $InvoiceRequestDB->getSocialReasons();
//consulta las status para los filtros
    $status = $InvoiceRequestDB->getStatus();
?>
	<div style="width:90%;height:500px;">
		<br>
		<b>
			<p class="subtitulo" align="left">Solicitudes de Factura</p>
					
		</b>
        <div class="row">
            <div class="col-4">
                <label for="store_filter">Sucursal : </label>
                <br>
                <select class="form-control" id="store_filter" onchange="filter();">
                    <option value="-1">Todas</option>
                    <?php echo $stores;?>
                </select>
                <br>
            </div>
            <div class="col-4">
                <label for="rss_filter">Razon Social : </label>
                <br>
                <select class="form-control" id="rss_filter" onchange="filter();">
                    <option value="-1">Todas</option>
                    <?php echo $rss;?>
                </select>
            </div>
            <div class="col-4 text-end">
                <label for="status_filter">Status : </label>
                <br>
                <select class="form-control" id="status_filter" onchange="filter();">
                    <option value="-1">Todos</option>
                    <?php echo $status;?>
                </select>
            </div>
        </div>
        <div>
            <div class="input-group">
                <input type="text" id="seeker_input" class="form-control" onkeyup="filter();" 
                placeholder="Buscar por RFC, folio nota">
                <button
                    type="button"
                    class="btn btn-success"
                    onclick="filter();"
                >
                    <i class="icon-search"></i>
                </button>
            </div>
            <br>
        </div>
		<div class="row" style="max-height : 100%; overflow: auto; position : relative;">
			<table width="100%" id="listaRS" class="table table-striped table-bordered">
				<thead class="bg-primary text-light" style="position : sticky; top :0;">
					<tr>
						<th class="text-center" width="10%">Folio Nota</th>
						<!--th width="20%">Link acceso</th-->
						<th class="text-center" width="10%">Sucursal</th>
						<th class="text-center" width="10%">Razon Social Emisor</th>
						<th class="text-center" width="10%">RFC Cliente</th>
						<th class="text-center" width="7.5%">Monto</th>
						<th class="text-center" width="7.5%">Fecha</th>
						<th class="text-center" width="10%">Status</th>
						<th class="text-center" width="5%">Facturar</th>
						<th class="text-center" width="5%">Imprimir</th>
						<th class="text-center" width="5%">Correo</th>
					</tr>
				</thead>
				<tbody id="invoiceRequestList">
			<?php
                echo $InvoiceRequestDB->getInvoiceRequests( null,  -1, -1, -1, 50 );
			?>
				</tbody>
			</table>
		</div>

        <table class="table">
            <tfoot>
                <tr>
                    <th class="text-center">
                        <button
                            class="btn btn-primary"
                            style="box-shadow : 1px 10px 10px rgba( 0,0,0,.4 );"
                            onclick="move_page( -1 );"
                        >
                            <i class="icon-left-open"></i>
                        </button>
                    </th>
                    <th class="text-center">
                        Página <input type="number" id="current_page" value="1" class="paginator_input" onkeyup="filter();"> de 
                        <input type="number" id="pages_stop" value="<?php echo $pages_limit;?>" class="paginator_input" disabled>
                        <br>
                        <b class="rows_per_page_text">Registros por página : </b>
                        <input type="number" id="pages_limit" value="<?php echo $pages_limit;?>" onblur="change_rows_per_page();" class="paginator_input rows_per_page_text">
                    </th>
                    <th class="text-center">
                        <button
                            class="btn btn-primary"
                            style="box-shadow : 1px 10px 10px rgba( 0,0,0,.4 );"
                            onclick="move_page( 1 );"
                        >
                            <i class="icon-right-open"></i>
                        </button>
                    </th>
                </tr>
            </tfoot>
        </table>
	</div>

<script>
    function filter( type = null ){
//recolecta informacion de los filtros
       // var store_filter = $( '#store_filter' ).val();  
       // var rs_filter = $( '#rss_filter' ).val();  
       // var status_filter = $( '#status_filter' ).val();  
        var url, seeker_text, store_filter, social_reason_filter;
        var status, limit, page_since, status_filter;//, page_to;
        
        url = `./include/invoiceRequestDB.php?action_fl=getInvoiceRequests`;
        limit = parseInt( $( "#pages_limit" ).val().trim() );
        var page = parseInt( $( '#current_page' ).val().trim() );
        if( page > 1 ){
            page_since = ( page * limit ) -2;
        }else{
            page_since = 0;
        }
        url += `&page_since=${page_since}&limit=${limit}`;
        url+= `&store_filter=` + $('#store_filter').val();
        url+= `&social_reason_filter=` + $('#rss_filter').val();
        url+= `&status_filter=` + $('#status_filter').val();
        if( $( '#seeker_input' ).val().trim().length > 0 ){
            url += "&seeker_text=" + $( '#seeker_input' ).val().trim();
        }
        var resp = ajaxR( url );//alert(url);
        $( '#invoiceRequestList' ).empty();
        $( '#invoiceRequestList' ).html(resp);
    }

    function change_rows_per_page(){
        var factor = parseInt( $( '#pages_limit' ).val().trim() );
        if( factor <= 0 ){
            alert( "El mínimo de registros por pagina es 1." );
            $( '#pages_limit' ).val(1);
            $( '#pages_limit' ).select();
            return false;
        }
        var url = `include/invoiceRequestDB.php?action_fl=getRowsCounter&factor=${factor}`;
        var resp = ajaxR( url );
        var json = JSON.parse( resp );
        $( '#pages_stop' ).val( json.pages_number );
        $( '#current_page' ).val( 1 );
        setTimeout( function(){
            filter();
        }, 300 );
        //alert( json.pages_number + " " + json.counter_rows );
    }

    function move_page( type ){
        var next_page = parseInt( $('#current_page').val().trim() );
        next_page += parseInt( type );
        if( next_page <=0 ){
            return false;
        }else{
            $('#current_page').val( next_page );
        }
        //filter();
    }

    function bill_petition( sale_id ){
    //consume api de facturacion
        var url = `include/invoiceRequestDB.php?action_fl=sendBillPetition&sale_id=${sale_id}`;
        var resp = ajaxR( url );
        var json = JSON.parse( resp );
        var content = `<h2 style="font-size : 300%;">${json.message}</h2>
            <div class="text-center">
                <br>
                <button
                    type="button"
                    class="btn btn-success"
                    onclick="close_emergent();"
                >
                    <i>Aceptar y cerrar</i>
                </button>
            </div>`;
        $( '#contenido_emergente' ).html( content );
        $( '#emergente' ).css( "display", "block" );
        alert(resp);
    }
</script>

<style>
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
    -webkit-appearance: none; 
    margin: 0; 
    }

input[type=number] { -moz-appearance:textfield; }
    .paginator_input{
        width: 50px;
        text-align: center;
        border:none;
        background-color: white;
        color : black;
    }
    .rows_per_page_text{
        font-size: 80%;
        color :blue;
    }
</style>
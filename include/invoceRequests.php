<?php
	session_start();
	include('./db.php');
	$db = new db();
	$link = $db->conectDB();
    
    include( './invoiceRequestDB.php' );
    $InvoiceRequestDB = new InvoiceRequestDB( $link );//instancia clase de solicitudes de factura
    $pages_limit = 20;
	$_SESSION['current_view'] = $_POST['action'];
    
    $stores = $InvoiceRequestDB->getStores();//consulta las razones sociales para los filtros
    $rss = $InvoiceRequestDB->getSocialReasons();//consulta las status para los filtros
    $status = $InvoiceRequestDB->getStatus();
	$paginator = $InvoiceRequestDB->getPagesInfo(20, 1);
?>
<!-- libreria para dar formato a jsons -->
	<script src="./js/highlight/highlight.min.js"></script>
	<link rel="stylesheet" href="./js/highlight/styles/default.min.css">
    <script>hljs.highlightAll();</script>
	<div style="width:90%;height:500px;">
		<br>
		<b>
			<p class="subtitulo" align="left">Solicitudes de Factura</p>
					
		</b>
        <div class="row">
            <div class="col-4">
                <label for="store_filter">Sucursal : </label>
                <br>
                <select class="form-control" id="store_filter" onchange="getInvoiceRequests();">
                    <option value="0">Todas</option>
                    <?php echo $stores;?>
                </select>
                <br>
            </div>
            <div class="col-4">
                <label for="rss_filter">Razon Social : </label>
                <br>
                <select class="form-control" id="rss_filter" onchange="getInvoiceRequests();">
                    <option value="0">Todas</option>
                    <?php echo $rss;?>
                </select>
            </div>
            <div class="col-4 text-end">
                <label for="status_filter">Status : </label>
                <br>
                <select class="form-control" id="status_filter" onchange="getInvoiceRequests();">
                    <option value="0">Todos</option>
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
						<th class="text-center" width="10%">Sucursal</th>
						<th class="text-center" width="10%">Razon Social Emisor</th>
						<th class="text-center" width="10%">RFC Cliente</th>
						<th class="text-center" width="5%">Monto</th>
						<th class="text-center" width="5%">Fecha</th>
						<th class="text-center" width="10%">Status</th>
						<th class="text-center" width="5%">Detalle</th>
						<th class="text-center" width="5%">Facturar</th>
						<th class="text-center" width="5%">Imprimir</th>
						<th class="text-center" width="5%">Correo</th>
					</tr>
				</thead>
				<tbody id="invoiceRequestList">
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
                            onclick="paginator(-1);"
                        >
                            <i class="icon-left-open"></i>
                        </button>
                    </th>
                    <th class="text-center">
                        Página <input type="number" id="current_page" value="<?php echo $paginator['current_page'];?>" class="paginator_input" onkeyup="filter();"> de 
                        <input type="number" id="pages_limit" value="<?php echo $paginator['pages_counter'];?>" class="paginator_input" disabled>
                        <br>
                    </th>
                    <th class="text-center">
                        <button
                            class="btn btn-primary"
                            style="box-shadow : 1px 10px 10px rgba( 0,0,0,.4 );"
                            onclick="paginator(1);"
                        >
                            <i class="icon-right-open"></i>
                        </button>
                    </th>
                </tr>
            </tfoot>
        </table>
	</div>
<script>
    function getInvoiceRequests( start_position = 0, limit = 20, page = 1 ){//buscar venta
        var url = `include/invoiceRequestDB.php?action_fl=getInvoiceRequests&limit=${limit}&current_page=${page}`;//&folio=${text}&start=${start_position}
        var store_filter = $('#store_filter').val();
        if( store_filter != 0){
            url += `&store=${store_filter}`;
        }
        var status_filter = $('#status_filter').val();
        if( status_filter != 0){
            url += `&status=${status_filter}`;
        }//alert(url);

        var rs_id = $('#rss_filter').val();
        if( rs_id != 0){
            url += `&rs_id=${rs_id}`;
        }//alert(url);
        var resp = ajaxR( url );//alert(resp);
		var json = JSON.parse(resp);
//console.log(json.errors);
		var content = buildInvoiceRequest(json.invoiceRequests);
        $( '#invoiceRequestList' ).html( resp );
		
		$('#invoiceRequestList').empty();
		$('#invoiceRequestList').html(content);
		$('#current_page').val(json.paginator.current_page);
		$('#pages_limit').val(json.paginator.pages_counter);
	}

    function buildInvoiceRequest(json){//alert(json);
        var content = ``;
        var c = 0;
        for (const key in json) {
            var row_class = "bg-danger text-white";
            if(json[key]['status_name'] == "Facturada"){
                row_class = "";
            }
            c++;//incrementamos contador
            content += `<tr tabindex="${c}" class="${row_class}">
                    <td>${json[key]['sale_folio']}</td>
                    <td>${json[key]['store_name']}</td>
                    <td>${json[key]['reason_name']}</td>
                    <td>${json[key]['costumer_rfc']}</td>
                    <td>${json[key]['sale_ammount']}</td>
                    <td>${json[key]['sale_date_time']}</td>
                    <td>${json[key]['status_name']}</td>
                    <td align="center">
                        <button 
                            type="button"
                            class="btn"
                            onclick="show_bill_petition_detail(${json[key]['sale_id']});"
                        >
                            <i class="icon-list"></i>
                        </button>
                    </td>
                    <td align="center">
                        <button 
                            type="button"
                            class="btn"
                            onclick="bill_petition( ${json[key]['sale_id']} );"
                        >
                            <i class="icon-bell-5"></i>
                        </button>
                    </td>
                    <td align="center">
                        <button 
                            type="button"
                            class="btn"
                            onclick="muestra_datos_RS( ${json[key]['sale_id']}, 1 );"
                        >
                            <i class="icon-print"></i>
                        </button>
                    </td>
                    <td align="center">
                        <button 
                            type="button"
                            class="btn"
                            onclick="muestra_datos_RS( ${json[key]['sale_folio']}, 2 );"
                        >
                            <i class="icon-email"></i>
                        </button>
                    </td>
                </tr>`; 
        }
        return content;
    }
    
    function paginator(action){
	//consulta pagina actual
		var current_page = parseInt($('#current_page').val().trim());
		var pages_limit = parseInt($('#pages_limit').val().trim());//alert(pages_limit);
		current_page += parseInt(action);
		if(current_page < 1){
			alert("No hay mas paginas hacia atras.");
			return false;
		}else if(current_page > pages_limit){
			alert("No hay mas paginas hacia adelante.");
			return false;
		}
		getInvoiceRequests( 0, 20, current_page );
	}

    function bill_petition( sale_id ){
    //consume api de facturacion
        var url = `include/invoiceRequestDB.php?action_fl=sendBillPetition&sale_id=${sale_id}`;
        var resp = ajaxR( url );
        var json = JSON.parse( resp );
        var content = `<br><br><br>
        <h2 style="font-size : 300%;" class="text-center">${json.message}</h2>
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
    }

    function show_bill_petition_detail( sale_id ){
        var url = `include/invoiceRequestDB.php?action_fl=showBillPetitionDetail&sale_id=${sale_id}`;
        var resp = ajaxR( url );
        var content = ``;
        var json = JSON.parse( resp );
        content += `<table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Razon Social</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>`;
        for (var i in json) {        
            content += `<tr>
                <td>${json[i].nombre}</td>
                <td>${json[i].fecha_alta}</td>
            </tr>
            <tr>
                <td colspan="2">
                    <table class="table">
                    <thead>
                        <tr>
                            <th class="col-2">Fecha</th>
                            <th class="col-5">Respuesta</th>
                            <th class="col-5">Detalle Respuesta</th>
                        </tr>
                    </thead>
                    <tbody>`;
            for (var j in json[i].detail ) {
                json[i].detail[j].respuesta = json[i].detail[j].respuesta.replaceAll(`\r\n\t\t\t\t\t`, `\n`);
                json[i].detail[j].respuesta = json[i].detail[j].respuesta.replaceAll(`\r\n\t\t\t\t`, `\n`);
                json[i].detail[j].respuesta = json[i].detail[j].respuesta.replaceAll(`\r\n\t\t\t`, `\n`);
                json[i].detail[j].respuesta = json[i].detail[j].respuesta.replaceAll(`\\t`, `    `);
                json[i].detail[j].respuesta = json[i].detail[j].respuesta.replaceAll(`\\r\\n`, `\n`);
                json[i].detail[j].respuesta = json[i].detail[j].respuesta.replaceAll(`,"`, `,\n"`);
                json[i].detail[j].respuesta = json[i].detail[j].respuesta.replaceAll(`,{`, `,\n{`);

                json[i].detail[j].detalle_respuesta = json[i].detail[j].detalle_respuesta.replaceAll(`\r\n\t\t\t\t\t`, `\n`);
                json[i].detail[j].detalle_respuesta = json[i].detail[j].detalle_respuesta.replaceAll(`\r\n\t\t\t\t`, `\n`);
                json[i].detail[j].detalle_respuesta = json[i].detail[j].detalle_respuesta.replaceAll(`\r\n\t\t\t`, `\n`);
                json[i].detail[j].detalle_respuesta = json[i].detail[j].detalle_respuesta.replaceAll(`\\t`, `    `);
                json[i].detail[j].detalle_respuesta = json[i].detail[j].detalle_respuesta.replaceAll(`\\r\\n`, `\n`);
                json[i].detail[j].detalle_respuesta = json[i].detail[j].detalle_respuesta.replaceAll(`,"`, `,\n"`);
                json[i].detail[j].detalle_respuesta = json[i].detail[j].detalle_respuesta.replaceAll(`,{`, `,\n{`);
                content += `<tr>
                    <td>${json[i].detail[j].fecha_alta}</td>
                    <td><pre><code class="json">${json[i].detail[j].respuesta}</code></pre></td>
                    <td><pre><code class="json">${json[i].detail[j].detalle_respuesta}</code></pre></td>
                </tr>`;  
            }
            content += `</tbody>
                    </table>
                </td>
            </tr>`;
        }
        content += `</tbody>
        </table>
        <br><br>
        <div class="text-center">
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
        hljs.highlightAll();
    }

    function close_emergent(){
        $( '#contenido_emergente' ).html( '' );
        $( '#emergente' ).css( "display", "none" );
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

<script>
    //getInvoiceRequests();
</script>
<?php
	session_start();
	$_SESSION['current_view'] = $_POST['action'];
	include('./db.php');
	$db = new db();
	$link = $db->conectDB();
    include('ajax/salesVerificationDB.php');
    $salesVerificationDB = new salesVerificationDB($link);
    $stores = $salesVerificationDB->getStores();//consulta las sucursales
    $stores_options = "";
    foreach ($stores as $key => $store) {
        $stores_options .= "<option value=\"{$store['id_sucursal']}\">{$store['nombre']}</option>";
    }
    $rss = $salesVerificationDB->getSocialReasons();//consulta las razones sociales
    $rs_options = "";
    foreach ($rss as $key => $rs) {
        $rs_options .= "<option value=\"{$rs['id_razon_social']}\">{$rs['nombre']}</option>";
    }
?>

<div id="emergent">
    <div id="emergent_content">
        <h2 class="text-center"><br><br>Cargando ...</h2>
        <div class="text-center">
            <img src="img/load.gif" alt="">
        </div>
    </div>
</div>

<div class="row" style="width:97% !important;">
    <div class="col-6">
        <h4 class="text-center">Fecha desde : </h4>
        <input type="date" id="date_since" class="form-control" onchange="change_button_type();" value="2025-11-22">
    </div>
    <div class="col-6">
        <h4 class="text-center">Fecha hasta : </h4>
        <input type="date" id="date_to" class="form-control" onchange="change_button_type();" value="2025-11-22">
    </div>
    <div class="col-6">
        <h4 class="text-center">Razon Social : </h4>
        <select class="form-control" id="rs_id" onchange="change_button_type();">
            <option value="-1">Todas</option>
        <?php
            echo $rs_options;
        ?>
        </select>
    </div>
    <div class="col-6">
        <h4 class="text-center">Buscar : </h4>
        <div class="input-group">
            <input type="text" onkeyup="seekSale(event);" id="sales_seeker" class="form-control">
            <button onclick="seekSale('intro');" class="btn btn-primary icon-search"></button>
        </div>
    </div>
</div>

<div id="table_content">
</div>
<br><br>

<div class="row" style="width:97% !important;">
    <button
        type="button"
        class="form-control btn btn-info"
        id="previous_btn"
        onclick="salesVerification(false);"
    >
        Ver Previo
    </button>
    <button
        type="button"
        class="form-control btn btn-success hidden"
        id="send_btn"
        onclick="enviarVentas();"
        disabled
    >
        Enviar Ventas
    </button>
</div>

<script>
    function enviarVentas(){
        var sales = ``;
        sales = $('#previous_list td input[type="checkbox"]:checked').map(function () {
            return $(this).val();
        }).get();
//console.log(sales);
//alert(sales);
        $.post('include/ajax/cashSalesVerificationDB.php', { fl : 'sendCashSales', sales : sales }, function (resp) {
            alert(resp);
            console.log(resp);
        });
    }
    
    function salesVerification( send = false ){
        var flag = "makePrevious";//( ( send == true ) ? "send" : "makePrevious" );
        var rs_id, store_id;
        rs_id = $('#rs_id').val();
        store_id = $('#store_id').val();
        var date_since = $( "#date_since" ).val();
        if( date_since == '' ){
            alert("La fecha desde no puede ir vacia.");
            $( "#date_since" ).focus();
            return false;
        }
        var date_to = $( "#date_to" ).val();
        if( date_to == '' ){
            alert("La fecha hasta no puede ir vacia.");
            $( "#date_to" ).focus();
            return false;
        }
        $( '#emergent' ).css( "display", "block" );
	//enviamos datos por ajax
		$.ajax({
			type : 'post',
			url : 'include/ajax/cashSalesVerificationDB.php',
			cache : false,
			data : { fl : flag, date_since : date_since, date_to : date_to, rs_id : rs_id },
		    success:function(dat){
                $( '#emergent' ).css( "display", "none" );
                if( send == false ){
                    var json_data = JSON.parse(dat);
                    build_previous_table( json_data );
                    $( '#previous_btn' ).addClass( 'hidden' );
                    $( '#send_btn' ).removeClass( 'hidden' );
                    //console.log(json_data);
                }else{
                    console.log( dat );
                    alert(dat);
                }
                //alert(dat);
			}
		});
        //var resp = ajaxR( url );
    }

    function seekSale( e ){
        if(e != 'intro' && e.keyCode != 13){
            return false;
        }
        var txt = $(`#sales_seeker`).val();
        var flag = "makePreviousSeeker";//( ( send == true ) ? "send" : "makePrevious" );
        $( '#emergent' ).css( "display", "block" );
	//enviamos datos por ajax
		$.ajax({
			type : 'post',
			url : 'include/ajax/cashSalesVerificationDB.php',
			cache : false,
			data : { fl : flag, text : txt },
		    success:function(dat){//alert(dat);
                $( '#emergent' ).css( "display", "none" );
                var json_data = JSON.parse(dat);
                build_previous_table( json_data );
                $( '#previous_btn' ).addClass( 'hidden' );
                $( '#send_btn' ).removeClass( 'hidden' );
			}
		});
    }

    function build_previous_table( json ){//#
        var content = `<h2 class="text-center">Previo : </h2>
        <table class="table table-bordered table-striped">
            <thead style="position : sticky; top : 5px; background-color : white; z-index : 5;">
                <tr>
                    <th class="text-center">
                        <input type="checkbox" onclick="selectAllChecks(this);"> 
                    </th>
                    <th class="text-center">Folio</th>
                    <th class="text-center">Total</th>
                    <th class="text-center">Razon Social</th>
                    <th class="text-center">Sucursal</th>
                    <th class="text-center">Fecha</th>
                </tr>
            </thead>
            <tbody id="previous_list">`;
        var sales = json.sales;
        var counter = 1;
        //for (const key in RS ) {
            for (const key in sales ) {//${counter}
                content += `<tr>
                    <td class="text-center">
                        <input type="checkbox" onclick="enabledOrdDisabledSendButton();" value="${sales[key].folio_nv}">  
                    </td>
                    <td class="text-center">${sales[key].folio_nv}</td>
                    <td class="text-center">${sales[key].total}</td>
                    <td class="text-center">${sales[key].nombre}</td>
                    <td class="text-center">${sales[key].nombre_sucursal}</td>
                    <td class="text-center">${sales[key].fecha_alta}</td>
                </tr>`;
                counter ++;
            }
        //}
        content += `</tbody>
            </table>`;
        $( '#table_content' ).html( content );
    }

    function selectAllChecks(obj){
        var status = $(obj).prop('checked');
        if(status){
            $('#previous_list td input[type="checkbox"]').prop('checked', true);
        }else{
            $('#previous_list td input[type="checkbox"]').removeAttr('checked');
        }
        enabledOrdDisabledSendButton();
    }

    function enabledOrdDisabledSendButton(){
        var marcados = 0;
        $('#previous_list tr').each(function (index) {
            marcados += $(this).find('input[type="checkbox"]:checked').length;
        });
        //alert(marcados);
        if(marcados == 0){
            $( '#send_btn' ).attr( 'disabled', 'true' );
        }else{
            $( '#send_btn' ).removeAttr( 'disabled' );
        }
    }

    function change_button_type(){
        $( '#send_btn' ).addClass( 'hidden' );
        $( '#previous_btn' ).removeClass( 'hidden' );
    }

</script>

<style>
    #emergent{
        position : fixed;
        top : 0;
        left : 0;
        width: 100%;
        height: 100%;
        background-color: rgba( 0,0,0,.5 );
        z-index:10;
        display : none;
    }

    #emergent_content{
        position : fixed;
        top : 10%;
        left : 10%;
        width: 80%;
        min-height: 30%;
        max-height: 70%;
        background-color: white;
        box-shadow: 1px 1px 10px rgba( 0,0,0,.5 );
    }
    .hidden{
        display : none;
    }
    #table_content{
        max-height: 500px !important;
        overflow: auto;
    }
</style>
<?php
	session_start();
	$_SESSION['current_view'] = $_POST['action'];
	//$_SESSION[$_SESSION['current_view']]['salesPagination'] = array();//$_POST['action'];
    //$_SESSION[$_SESSION['current_view']]['salesPagination']['since'] = ( isset( $_SESSION['salesPagination']['since'] ) ? $_SESSION['salesPagination']['since'] : 1 );
    //$_SESSION[$_SESSION['current_view']]['salesPagination']['to'] = ( isset( $_SESSION['salesPagination']['to'] ) ? $_SESSION['salesPagination']['to'] : 1 );
    //$_SESSION['salesPagination'][''] =
	//include('conexion.php');
	include('./db.php');
	include('../ajax/SalesDB.php');
	$db = new db();
	$link = $db->conectDB();
	$SalesDB = new SalesDB(connection: $link);
	$paginator = $SalesDB->getPagesInfo(20, 1);
//consulta el numero de registros entre el numero de pagina
    $sql = "SELECT
                COUNT(*) AS pages_limit
            FROM ec_pedidos
            WHERE id_pedido > 0";
	$eje=$link->query( $sql )or die("Error al listar las razones sociales : {$sql}");
    $row = $eje->fetch( PDO::FETCH_ASSOC );
    $pages_limit = ROUND( $row['pages_limit'] / 20 );
?>
	<div style="width:90%;heigth:450px;">
		<br>
        <div class="row">
            <div class="col-6">
                <b>
                    <p class="subtitulo" align="left" style="position : sticky; top: 0; background-color : white;">
                        <i class="icon-money-1">Administración de Ventas 2025</i> 
                    </p>
                </b>
            </div>
            <div class="col-6">
                <div class="input-group">
                    <input type="text" id="sale_seeker" class="form-control" placeholder="Buscar por Folio" onkeyup="seekSale( event );">
                    <button
                        type="button"
                        class="btn btn-primary"
                        onclick="seekSale( 'intro' );"
                    >
                        <i class="icon-search"></i>
                    </button>
                </div>
            </div>
        </div>
		<div style="max-height:500px; overflow : auto;">
			<table width="100%" class="table table-striped table-bordered">
				<thead class="bg-primary text-light" style="position : sticky; top: 0;">
					<tr>
						<th width="20%" class="text-center">Id</th>
						<th width="20%" class="text-center">Sucursal 
                            <button
                                tye="button"
                                class="btn text-light"
                            >
                                <i class="icon-filter"></i></th>
                            </button>
						<th width="15%" class="text-center">Folio</th>
						<th width="15%" class="text-center">Cliente</th>
						<th width="15%" class="text-center">Monto</th>
						<th width="10%" class="text-center">Ver</th>
					</tr>
				</thead>
				<tbody style="max-height : 200px; overflow:auto;" id="SalesListContent">
				</tbody>
			</table>
	</div>
    
    <table class="table" id="paginator_table">
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
                    Página <b id="current_page"><?php echo $paginator['current_page'];?></b> de <b id="pages_limit"><?php echo $paginator['pages_counter'];?></b>
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

<script>
var id_rg,nombre,ruta,nom_db,rfc,ruta_link,orden,pss_db,host,user_db,nom_db,estado,obs;
	function saleDetail(id,flag){
	//envia datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/SalesDB.php',
			cache:false,
			data:{fl : 'getSpecificSale', sale_id : id},
			success:function(dat){//alert(dat);
				$( '#alert_content' ).html( dat );
				$( '#alert' ).css( 'display', 'block' );
			}
		});//fin de ajax
	}//fin de funcion que carga datos

	function paginator(action){
	//consulta pagina actual
		var current_page = parseInt($('#current_page').html().trim());
		var pages_limit = parseInt($('#pages_limit').html().trim());
		current_page += parseInt(action);
		if(current_page < 1){
			alert("No hay mas paginas hacia atras.");
			return false;
		}else if(current_page > pages_limit){
			alert("No hay mas paginas hacia adelante.");
			return false;
		}
		getSales( 0, 20, current_page );
	}

var resaltada=0;
	function resalta(num){
		/*if(resaltada!=0){
			quita_resaltado(resaltada);
		}
		$("#fila_"+num).css("background","rgba(0,225,0,0.5)");*/
	}

	function quita_resaltado(num){
		//$("#fila_"+num).css("background","white");
	}
    function getSales( start_position = 0, limit = 20, page = 1){//buscar venta
        var url = `ajax/SalesDB.php?fl=getPagesSales&limit=${limit}&current_page=${page}`;//&folio=${text}&start=${start_position}
        var resp = ajaxR( url );//alert(resp);
		var json = JSON.parse(resp);
		var content = buildSaleRows(json.sales);
        $( '#SalesListContent' ).html( resp );
		
		$('#SalesListContent').empty();
		$('#SalesListContent').html(content);
		$('#current_page').html(json.paginator.current_page);
	}
	
    function seekSale( e ){
        var keycode = e.keyCode;
        if( e != 'intro' && keycode != 13 ){
            return false;
        }
    //
        var text = $( "#sale_seeker" ).val();
        if( text.length <= 0 ){
            alert( "Debes ingresar un folio de venta para buscar." );
            $( "#sale_seeker" ).focus();
            return false;
        }
    //manda a buscar venta
        var url = `ajax/SalesDB.php?fl=seekSaleByFolio&folio=${text}`;
        var resp = ajaxR( url );
        $( '#SalesListContent' ).html( resp );

    }
	function buildSaleRows(json){
		content = ``;
		for (const key in json) {
			content += `<tr id="fila_${key}" tabindex="${key}" onfocus="resalta(${key});" onclick="resalta(${key});" onblur="quita_resaltado(${key});" class="row_item">
				<td>${json[key]['id_pedido']}</td>
				<td>${json[key]['store_name']}</td>
				<td>${json[key]['folio_nv']}</td>
				<td class="text-center">${json[key]['id_cliente']}</td>
				<td>${json[key]['total']}</td>
				<td class="text-center">
					<button
						type="button"
						class="btn"
						onclick="saleDetail( ${json[key]['id_pedido']} , 0 );"
					>
						<i class="icon-eye"></i>
					</button>
				</td>
			</tr>`;
		}
		return content;
	}
</script>
<style>
	.row_item:hover{
		background-color: rgba(225,0,0,.5) !important;
	}
</style>

<script>
	getSales( 1, 20 );
</script>
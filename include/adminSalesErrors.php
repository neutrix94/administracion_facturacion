<?php
	session_start();
	$_SESSION['current_view'] = $_POST['action'];
	$_SESSION['salesPagination'] = array();//$_POST['action'];
    $_SESSION['salesPagination']['since'] = ( isset( $_SESSION['salesPagination']['since'] ) ? $_SESSION['salesPagination']['since'] : 1 );
    //$_SESSION['salesPagination'][''] =
	//include('conexion.php');
	include('./db.php');
	$db = new db();
	$link = $db->conectDB();
    include('../ajax/SalesErrorsDB.php');
    $SalesDB = new SalesDB($link);
//consulta el numero de registros entre el numero de pagina
    $sql = "SELECT
                COUNT(*) AS pages_limit
            FROM ec_pedidos_error_envio_rs
            WHERE 1";
	$eje=$link->query( $sql )or die("Error al listar las razones sociales : {$sql}");
    $row = $eje->fetch( PDO::FETCH_ASSOC );
    $pages_limit = ceil( $row['pages_limit'] / 20 );
    //$pages_limit //die("PAGES : {$pages_limit}  : {$row['pages_limit']} / 20");
	$sql = "SELECT 
            p.id_pedido, 
            s.nombre AS store_name,
            p.folio_nv,
            p.total,
            GROUP_CONCAT(peer.contenido_respuesta SEPARATOR '\n') AS contenido_respuesta,
            peer.omitir,
            p.id_status_facturacion
        FROM ec_pedidos_error_envio_rs peer
        LEFT JOIN ec_pedidos p
        ON peer.id_pedido = p.id_pedido
        LEFT JOIN sys_sucursales s
        ON p.id_sucursal = s.id_sucursal
        WHERE 1 
        GROUP BY p.id_pedido
        ORDER BY id_pedido DESC/*LIMIT 20*/";
	$eje = $link->query( $sql )or die("Error al listar las razones sociales : {$sql}");
    $stores = $SalesDB->getStores();
    $status = $SalesDB->getStatus();
	$paginator = $SalesDB->getPagesInfo(20, 1);
    //var_dump($stores);
    
?>
    <script src="./js/highlight/highlight.min.js"></script>
	<link rel="stylesheet" href="./js/highlight/styles/default.min.css">
    <script>hljs.highlightAll();</script>
	<div style="width:90%;heigth:450px;">
		<br>
        <div class="row">
            <div class="col-6">
                <b>
                    <p class="subtitulo" align="left" style="position : sticky; top: 0; background-color : white;" class="">
                        <i class="icon-cancel-circled text-danger">Ventas con error al enviar a Razon Social</i> 
                    </p>
                </b>
                <div class="row">
                    <div class="col-6">
                        Sucursal
                        <select class="form-control" name="" id="store_filter">
                            <option value="0">--Todas--</option>
                    <?php
                        foreach ($stores as $key => $store) {
                            echo "<option {$store['id_sucursal']}>{$store['nombre']}</option>";
                        }
                    ?>
                        </select>
                    </div>
                    <div class="col-6">
                        Status
                        <select class="form-control" name="" id="status_filter">
                            <option value="0">--Todas--</option>
                    <?php
                        foreach ($status as $key => $st) {
                            echo "<option {$st['id_status_facturacion']}>{$st['nombre_status']}</option>";
                        }
                    ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="input-group">
                    <input type="text" id="sale_seeker" class="form-control border border-danger" placeholder="Buscar por Folio" onkeyup="seekSale( event );">
                    <button
                        type="button"
                        class="btn btn-danger"
                        onclick="seekSale( 'intro' );"
                    >
                        <i class="icon-search"></i>
                    </button>
                </div>
            </div>
        </div>
		<div style="max-height:500px; overflow : auto;">
			<table width="100%" class="table table-striped table-bordered">
				<thead class="bg-danger text-light" style="position : sticky; top: 0;">
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
						<th width="15%" class="text-center">Monto</th>
						<th width="15%" class="text-center">Omitido</th>
						<th width="10%" class="text-center">Ver Detalle</th>
					</tr>
				</thead>
				<tbody style="max-height : 200px; overflow:auto;" id="SalesListContent">
			<?php
			/*$c=0;//inicaimos el contador en cero
			while( $r = $eje->fetch( PDO::FETCH_ASSOC ) ){
                $row_class = "";
                if($r['id_status_facturacion'] >= 5){
                    $row_class = "bg-success";
                }
				$c++;//incrementamos contador
				echo '<tr id="fila_'.$c.'" tabindex="'.$c.'" class="row_item '. $row_class . '">';
					echo '<td class="text-center">'.$r['id_pedido'].'</td>';
					echo '<td class="text-center">'.$r['store_name'].'</td>';
					echo '<td class="text-center">'.$r['folio_nv'].'</td>';
					echo '<td class="text-center">'.$r['total'].'</td>';
                    echo "<td id=\"error_detail_{$c}\" style=\"display:none;\">{$r['contenido_respuesta']}</td>";
                    echo "<td class=\"text-center\"><input type=\"checkbox\" " . ($r['omitir'] == 1 ? 'checked' : '') . " disabled></td>";
					echo "<td class=\"text-center\" value=\"\">
						<button
							type=\"button\"
							class=\"btn\"
							onclick=\"showErrorDetail( {$c}, {$r['id_pedido']} );\"
						>
							<i class=\"icon-eye\"></i>
						</button>
					</td>";
				echo '</tr>';
			}//fin de while*/
			?>
				</tbody>
			</table>
	</div>
    
    <table class="table">
        <tfoot>
            <tr>
                <th class="text-center">
                    <button
                        class="btn btn-danger"
                        style="box-shadow : 1px 10px 10px rgba( 0,0,0,.4 );"
                        onclick="paginator(-1);"
                    >
                        <i class="icon-left-open"></i>
                    </button>
                </th>
                <th class="text-center text-danger">
                    Página <b id="current_page"><?php echo $paginator['current_page'];?></b> de <b id="pages_limit"><?php echo $paginator['pages_counter'];?></b>
                </th>
                <th class="text-center">
                    <button
                        class="btn btn-danger"
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
    function showErrorDetail( counter, sale_id ){
        var error = $("#error_detail_" + counter).html().trim();
        var content = `<h3 class="text-center text-danger">Detalle de Error : </h3>
            <br>
            <pre><code class="json text-start">${error}</code></pre>
            <br>
            <br>
            <div class="row">
                <div class="col-4"></div>
                <div class="col-4 text-center">
                    <button
                        class="btn btn-danger form-control"
                        onclick="close_alert();"
                    >
                        <i class="icon-ok-circled">Aceptar y cerrar</i>
                    </button>
                    <br>
                    <br>
                    <button
                        class="btn btn-warning form-control"
                        onclick="try_again('${sale_id}');"
                    >
                        <i class="icon-ccw">Reintentar</i>
                    </button>
                </div>
            </div>`;    
        $( '#alert_content' ).html( content );
        $( '#alert' ).css( 'display', 'block' );
        hljs.highlightAll();
    }
    
    function getSalesErrors( start_position = 0, limit = 20, page = 1 ){//buscar venta
        var url = `ajax/SalesErrorsDB.php?fl=getPagesSalesErrors&limit=${limit}&current_page=${page}`;//&folio=${text}&start=${start_position}
        var resp = ajaxR( url );//alert(resp);
		var json = JSON.parse(resp);
        console.log(json.errors);
		var content = buildSaleRows(json.errors);
        $( '#SalesListContent' ).html( resp );
		
		$('#SalesListContent').empty();
		$('#SalesListContent').html(content);
		$('#current_page').html(json.paginator.current_page);
	}
    

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
		getSalesErrors( 0, 20, current_page );
	}

    function buildSaleRows(json){
        var content = ``;
        var c = 0;
        for (const key in json) {
            var row_class = "";
            if(json[key]['id_status_facturacion'] >= 5){
                row_class = "bg-success";
            }
            content += `<tr id="fila_${c}" tabindex="${c}" class="row_item ${row_class}">
                <td class="text-center">${json[key]['id_pedido']}</td>
                <td class="text-centeer">${json[key]['store_name']}</td>
                <td class="text-center">${json[key]['folio_nv']}</td>
                <td class="text-center">${json[key]['total']}</td>
                <td id=\"error_detail_${c}\" style=\"display:none;\">${json[key]['contenido_respuesta']}</td>
                <td class=\"text-center\"><input type=\"checkbox\" " . (${json[key]['omitir']} == 1 ? 'checked' : '') . " disabled></td>
                <td class=\"text-center\" value=\"\">
                    <button
                        type=\"button\"
                        class=\"btn\"
                        onclick=\"showErrorDetail( ${c}, ${json[key]['id_pedido']} );\"
                    >
                        <i class=\"icon-eye\"></i>
                    </button>
                    </td>
                echo '</tr>`;
            c ++;
        }
		return content;
    }


    function seekSale( e ){
        var keycode = e.keyCode;
        if( e != 'intro' && keycode != 13 ){
            return false;
        }
        var text = $( "#sale_seeker" ).val();
        if( text.length <= 0 ){
            alert( "Debes ingresar un folio de venta pra buscar." );
            $( "#sale_seeker" ).focus();
            return false;
        }
        var url = `ajax/SalesErrorsDB.php?fl=seekSaleByFolio&folio=${text}`;
        var resp = ajaxR( url );
        $( '#SalesListContent' ).html( resp );
    }

    function try_again(sale_id){
        var url = `ajax/SalesErrorsDB.php?fl=retrySendingSale&sale_id=${sale_id}`;
        var resp = ajaxR( url );
        var json_content = JSON.parse(resp);
        var content = `<div><h2 class="text-ecnter">Respuesta : </h2></div>
        <div>
            <h3 class="text-center">${json_content.message}</h3>
        </div>
        <div class="text-center">
            <button
                class="btn btn-success"
                onclick="close_alert();"
            >
                <i class="icon-ok-circle">Aceptar y cerrar</i>
            </button>
        </div>`;
        $( '#alert_content' ).html( content );
        $( '#alert' ).css( 'display', 'block' );
    }
</script>

<style>
	.row_item:hover{
		background-color: rgba(225,225,0,.5) !important;
	}
</style>

<script>
	getSalesErrors( 1, 20 );
</script>
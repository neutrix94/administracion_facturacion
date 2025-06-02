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
            peer.contenido_respuesta,
            peer.omitir
        FROM ec_pedidos_error_envio_rs peer
        LEFT JOIN ec_pedidos p
        ON peer.id_pedido = p.id_pedido
        LEFT JOIN sys_sucursales s
        ON p.id_sucursal = s.id_sucursal
        WHERE 1 
        ORDER BY id_pedido DESC/*LIMIT 20*/";
	$eje = $link->query( $sql )or die("Error al listar las razones sociales : {$sql}");
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
						<!--th width="15%" class="text-center">Cliente</th-->
						<th width="15%" class="text-center">Monto</th>
						<th width="15%" class="text-center">Omitido</th>
						<th width="10%" class="text-center">Ver Detalle</th>
						<!--th width="10%" class="text-center">Editar</th>
						<th width="10%" class="text-center">Eliminar</th-->
					</tr>
				</thead>
				<tbody style="max-height : 200px; overflow:auto;" id="SalesListContent">
			<?php
			$c=0;//inicaimos el contador en cero
			while( $r = $eje->fetch( PDO::FETCH_ASSOC ) ){
				$c++;//incrementamos contador
				echo '<tr id="fila_'.$c.'" tabindex="'.$c.'" class="row_item">';
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
			}//fin de while
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
                    >
                        <i class="icon-left-open"></i>
                    </button>
                </th>
                <th class="text-center text-danger">
                    Página <b id="current_page">1</b> de <b id="pages_limit"><?php echo $pages_limit;?></b>
                </th>
                <th class="text-center">
                    <button
                        class="btn btn-danger"
                        style="box-shadow : 1px 10px 10px rgba( 0,0,0,.4 );"
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
        /*error = error.replaceAll(`\r\n\t\t\t\t\t`, `\n`);
        error = error.replaceAll(`\r\n\t\t\t\t`, `\n`);
        error = error.replaceAll(`\r\n\t\t\t`, `\n`);
        error = error.replaceAll(`\\t`, `    `);
        error = error.replaceAll(`\\r\\n`, `\n`);
        error = error.replaceAll(`,"`, `,\n"`);
        error = error.replaceAll(`,{`, `,\n{`);
        error = error.replaceAll(`\r\n\t\t\t\t\t`, `\n`);
        error = error.replaceAll(`\r\n\t\t\t\t`, `\n`);
        error = error.replaceAll(`\r\n\t\t\t`, `\n`);
        error = error.replaceAll(`\\t`, `    `);
        error = error.replaceAll(`\\r\\n`, `\n`);
        error = error.replaceAll(`,"`, `,\n"`);
        error = error.replaceAll(`,{`, `,\n{`);*/
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
        $( '#SalesListContent' ).html( resp );
        var content = `<div><h2 class="text-ecnter">Respuesta : </h2></div>
        <div>
            <h3 class="text-center">${resp}</h3>
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
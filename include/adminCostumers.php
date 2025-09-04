<?php
	session_start();
	$_SESSION['current_view'] = $_POST['action'];
	include('./db.php');
	$db = new db();
	$link = $db->conectDB();
	include('../ajax/customersDB.php');
	$CustomersDB = new CustomersDB( $link );
//consulta el numero de registros entre el numero de pagina
    $sql = "SELECT
                COUNT(*) AS pages_limit
            FROM vf_clientes_razones_sociales
            WHERE id_cliente_facturacion >= 10000";
	$eje=$link->query( $sql )or die("Error al listar las razones sociales : {$sql}");
    $row = $eje->fetch( PDO::FETCH_ASSOC );
    $pages_limit = ROUND( $row['pages_limit'] / 20 );

	$customers = $CustomersDB->getCustomers();
?>
	<div style="width:90%;heigth:450px;">
		<br>
        <div class="row">
            <div class="col-6">
                <b>
                    <p class="subtitulo" align="left" style="position : sticky; top: 0; background-color : white;">
                        Administración de Clientes 2025
                    </p>
                </b>
            </div>
            <div class="col-6 input-group">
                <input type="text" class="form-control" placeholder="Buscar por RFC" onkeyup="customerSeeker(event);" id="customer_seeker">
                <button
                    type="button"
                    class="btn btn-primary"
					onclick="customerSeeker('intro');"
                >
                    <i class="icon-search"></i>
                </button>
            </div>
        </div>
		<div style="max-height:500px; overflow : auto;">
			<table width="100%" class="table table-striped">
				<thead class="bg-primary text-light" style="position : sticky; top: 0;">
					<tr>
						<th width="20%" class="text-center">Id</th>
						<th width="20%" class="text-center">RFC</th>
						<th width="15%" class="text-center">Razon Social</th>
						<th width="15%" class="text-center">C.P.</th>
					</tr>
				</thead>
				<tbody style="max-height : 200px; overflow:auto;" id="customersList">
			<?php
			$c=0;
			foreach ($customers as $key => $r) {
				$c++;
				echo $CustomersDB->build_row_ceil( $r, $c );
			}
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
                    >
                        <i><-</i>
                    </button>
                </th>
                <th class="text-center">
                    Pagina <b id="current_page">1</b> de <b id="pages_limit"><?php echo $pages_limit;?></b>
                </th>
                <th class="text-center">
                    <button
                        class="btn btn-primary"
                    >
                        <i>-></i>
                    </button>
                </th>
            </tr>
        </tfoot>
    </table>

	<div class="form_emergente" id="emergente_RS" style="display:none;">
		<div style="position:absolute;top:10%;width:80%;left:10%;">
			<button class="cierra_emergente" onclick="cierra_emergente('emergente_RS');">X</button>
				<table width="100%" border="0" cellspacing="10px" cellpadding="10px;" style="background:#B0C4DE;border-radius:15px;">
					<tr>
						<td align="right" width="25%"><b class="desc_campo">ID:</b></td>
						<td align="center" width="25%"><input type="text" id="id_user" class="entrada_txt" disabled></td>

						<td align="right" width="25%"><b class="desc_campo">Activo:</b></td>
						<td align="center" width="25%"><input type="checkbox" id="estado"></td>					
					</tr>
					<tr>
						<td align="right" width="25%"><b class="desc_campo">Nombre:</b></td>
						<td align="center" width="25%"><input type="text" id="nombre" class="entrada_txt"></td>

						<td align="right" width="25%"><b class="desc_campo">Login:</b></td>
						<td align="center" width="25%"><input type="text" id="login" class="entrada_txt"><br></td>
					</tr>
					<tr>
						<td align="right" width="25%"><b class="desc_campo">Password:</b></td>
						<td align="center" width="25%"><input type="password" id="pass" class="entrada_txt"></td>

						<td align="right" width="25%"><b class="desc_campo">Fecha alta:</b></td>
						<td align="center" width="25%"><input type="text" id="fecha_alta" class="entrada_txt"></td>
					</tr>
					<tr>
						<td align="right" width="25%"><b class="desc_campo">Tipo de Perfil:</b></td>
						<td align="center" width="25%"><input type="text" id="perfil" class="entrada_txt"></td>

						<td align="right" width="25%"><b class="desc_campo">Observaciones:</b></td>
						<td align="center" width="25%"><textarea id="observaciones" class="entrada_txt"></textarea></td>
						<td></td><td></td>
					</tr>
					<tr>
						<td colspan="4" align="center">
						<button class="btn_med" onclick="guarda_user();" id="guardar_user">
								Guardar
						</button>
						</td>
					</tr>
				</table>

		</div>
	</div>

<script>
var id_rg,nombre,ruta,nom_db,rfc,ruta_link,orden,pss_db,host,user_db,nom_db,estado,obs;
var resaltada = 0;

	function customerSeeker(e){
		if(e.keyCode != 13 && e != 'intro'){
			return false;
		}
		var txt = $('#customer_seeker').val();
		var url = `ajax/customersDB.php?fl=getSpecificCustomer&text=${txt}`;
		var resp = ajaxR(url);
		$('#customersList').empty();
		$('#customersList').html(resp);
	}

	function resalta(num){
		if(resaltada!=0){
			quita_resaltado(resaltada);
		}
		$("#fila_"+num).css("background","rgba(0,225,0,0.5)");
	}

	function quita_resaltado(num){
		$("#fila_"+num).css("background","white");		
	}

</script>
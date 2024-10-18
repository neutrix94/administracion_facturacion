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
            FROM ec_pedidos
            WHERE id_pedido > 0";
	$eje=$link->query( $sql )or die("Error al listar las razones sociales : {$sql}");
    $row = $eje->fetch( PDO::FETCH_ASSOC );
    $pages_limit = ROUND( $row['pages_limit'] / 20 );
	$sql="SELECT 
            p.id_pedido, 
            s.nombre AS store_name,
            p.folio_nv, 
            p.id_cliente,
            p.total
        FROM ec_pedidos p
        LEFT JOIN sys_sucursales s
        ON p.id_sucursal = s.id_sucursal
        WHERE 1 
        ORDER BY id_pedido DESC
        LIMIT 20";
	$eje=$link->query( $sql )or die("Error al listar las razones sociales : {$sql}");
?>
	<div style="width:90%;heigth:450px;">
		<br>
        <div class="row">
            <div class="col-6">
                <b>
                    <p class="subtitulo" align="left" style="position : sticky; top: 0; background-color : white;">
                        <i class="icon-money-1">Administración de Ventas 2024</i> 
                    </p>
                </b>
            </div>
            <div class="col-6">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Buscar por Folio">
                    <button
                        type="button"
                        class="btn btn-primary"
                    >
                        <i class="icon-search"></i>
                    </button>
                </div>
            </div>
        </div>
		<div style="max-height:500px; overflow : auto;">
			<table width="100%" class="table table-striped">
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
						<th width="10%" class="text-center">Editar</th>
						<th width="10%" class="text-center">Eliminar</th>
					</tr>
				</thead>
				<tbody style="max-height : 200px; overflow:auto;">
			<?php
			$c=0;//inicaimos el contador en cero
			while( $r = $eje->fetch( PDO::FETCH_ASSOC ) ){
				$c++;//incrementamos contador
				echo '<tr id="fila_'.$c.'" tabindex="'.$c.'" onfocus="resalta('.$c.');" onclick="resalta('.$c.');" onblur="quita_resaltado('.$c.');">';
					echo '<td>'.$r['id_pedido'].'</td>';
					echo '<td>'.$r['store_name'].'</td>';
					echo '<td>'.$r['folio_nv'].'</td>';
					echo '<td class="text-center">'.$r['id_cliente'].'</td>';
					echo '<td>'.$r['total'].'</td>';
					echo "<td class=\"text-center\">
						<button
							type=\"button\"
							class=\"btn\"
							onclick=\"muestra_datos_RS( {$r['id_pedido']} , 0 );\"
						>
							<i class=\"icon-eye\"></i>
						</button>
					</td>
					<td class=\"text-center\">
						<button
							type=\"button\"
							class=\"btn\"
							onclick=\"muestra_datos_RS( {$r['id_pedido']} , 2 );\"
						>
							<i class=\"icon-pencil\"></i>
						</button>
					</td>
					<td class=\"text-center\">
						<button
							type=\"button\"
							class=\"btn\"
							onclick=\"muestra_datos_RS( {$r['id_pedido']} , 3 );\"
						>
							<i class=\"icon-cancel\"></i>
						</button>
					</td>";
				echo '</tr>'; 
					//echo '<td align="center"><a href="javascript:muestra_datos_RS('.$r[0].',1);"><img src="img/ver.png" width="30px"></a></td>';
					//echo '<td align="center"><a href="javascript:muestra_datos_RS('.$r[0].',2);"><img src="img/editar.png" width="30px"></a></td>';
					//echo '<td align="center"><a href="javascript:muestra_datos_RS('.$r[0].',3);"><img src="img/eliminar.png" width="30px"></a></td>';
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
                        class="btn btn-primary"
                        style="box-shadow : 1px 10px 10px rgba( 0,0,0,.4 );"
                    >
                        <i class="icon-left-open"></i>
                    </button>
                </th>
                <th class="text-center">
                    Página <b id="current_page">1</b> de <b id="pages_limit"><?php echo $pages_limit;?></b>
                </th>
                <th class="text-center">
                    <button
                        class="btn btn-primary"
                        style="box-shadow : 1px 10px 10px rgba( 0,0,0,.4 );"
                    >
                        <i class="icon-right-open"></i>
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
	function guarda_RS(id_reg,flag){/*/
		if(id_reg==null||id_reg==''){
			$("#guardar_rs").attr('onclick',);
		}*/
	//extraemos datos del formulario
		id_rg=$("#id_razon_social").val();
		nombre=$("#nombre").val();
		ruta=$("#ruta").val();
		nom_db=$("#usuario_db").val();
		rfc=$("#rfc").val();
		ruta_link=$("#link").val();
		orden=$("#orden").val();
		pss_db=$("#contrasena_db").val();
		user_db=$("#usuario_db").val();
		obs=$("#observaciones").val();
		estado=$("#activo").val();
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/rS.php',
			cache:false,
			data:{
					flag:flag,
					id_registro:id_rg,
					nom_rs:nombre,
					server:host,
					nombre_base_datos:nom_db,
					rfc:rfc,
					enlace:ruta_link,
					ord:orden,
					pass_base_datos:pss_db,
					usuario_base_datos:user_db,
					activo:estado,
					observ:obs
				},
				success:function(dat){
					var aux=dat.split("|");
					if(aux[0]!='ok'){
						alert("Error!!!\n\n"+dat);
						return false;
					}else{
						alert(aux[1]);
						return true;
				}
			}
		});
	} 

	function muestra_datos_RS(id,flag){
        alert();
        $("#emergente_RS").css("display","block");
		if(flag==0){
			$("#guardar_rs").attr('onclick','guarda_RS(0,'+flag+')');
		}else{
		//enviamos datos por ajax
			$.ajax({
				type:'post',
				url:'ajax/rS.php',
				cache:false,
				data:{fl:flag,id_reg:id},
				success:function(dat){
					alert('dat:'+dat);
					var aux=dat.split("|");
					if(aux[0]!='ok'){
						alert("Error!!!"+dat);
					}else{
						$("#id_razon_social").val(aux[1]);
						$("#nombre").val(aux[2]);
						$("#ruta").val(aux[3]);
						$("#usuario_db").val(aux[4]);
						$("#contrasena_db").val(aux[5]);
						$("#rfc").val(aux[6]);
						$("#link").val(aux[7]);
						$("#orden").val(aux[8]);
						$("#usuario_db").val(aux[9]);
						$("#observaciones").val(aux[10]);
						$("#activo").val(aux[11]);
					}//fin de else
				}
			});//fin de ajax
			$("#emergente_RS").css("display","block");
		}//fin de else
	}//fin de funcion que carga datos

var resaltada=0;
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
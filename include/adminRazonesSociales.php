<?php
	session_start();
	include('./db.php');
	$db = new db();
	$link = $db->conectDB();
	$_SESSION['current_view'] = $_POST['action'];
	$sql="SELECT id_razon_social,nombre,link,rfc,observaciones,activo FROM razones_sociales WHERE 1 ORDER BY orden ASC";
	$eje = $link->query( $sql )or die("Error al listar las razones sociales : {$sql}");
?>
	<div style="width:90%;height:500px;">
		<br>
		<b>
			<p class="subtitulo" align="left">Administración de Razones Sociales
				<button 
					class="btn btn-info" 
					onclick="muestra_datos_RS(0,1);"

				>
					<i class="icon-plus">Nuevo</i>
				</button>
			</p>
					
		</b>
		<div class="row" style="max-height : 100%; overflow: auto; position : relative;">
			<table width="100%" id="listaRS" class="table table-striped table-bordered">
				<thead class="bg-primary text-light" style="position : sticky; top :0;">
					<tr>
						<th class="text-center" width="20%">Nombre</th>
						<!--th width="20%">Link acceso</th-->
						<th class="text-center" width="10%">RFC</th>
						<th class="text-center" width="15%">observaciones</th>
						<th class="text-center" width="10%">Activo</th>
						<th class="text-center" width="5%">Ver</th>
						<th class="text-center" width="10%">Editar</th>
						<th class="text-center" width="10%">Eliminar</th>
					</tr>
				</thead>
				<tbody>
			<?php
			$c=0;//iniciamos el contador en cero
			while($r = $eje->fetch() ){
				$c++;//incrementamos contador
				echo '<tr tabindex="'.$c.'">';
					echo '<td>'.$r[1].'</td>';
					//echo '<td>'.$r[2].'</td>';
					echo '<td>'.$r[3].'</td>';
					echo '<td>'.$r[4].'</td>';
					echo '<td>'.$r[5].'</td>';
					//echo '<td align="center"><a href="javascript:muestra_datos_RS('.$r[0].',1);"><img src="img/ver.png" width="30px"></a></td>';
					echo "<td align=\"center\">
							<button 
								type=\"button\"
								class=\"btn\"
								onclick=\"muestra_datos_RS( {$r[0]}, 1 );\"
							>
								<i class=\"icon-eye\"></i>
							</button>
						</td>";
					echo "<td align=\"center\">
							<button 
								type=\"button\"
								class=\"btn\"
								onclick=\"muestra_datos_RS( {$r[0]}, 2 );\"
							>
								<i class=\"icon-pencil\"></i>
							</button>
						</td>";
					//echo '<td align="center"><a href="javascript:muestra_datos_RS('.$r[0].',2);"><img src="img/editar.png" width="30px"></a></td>';
					echo "<td align=\"center\">
							<button 
								type=\"button\"
								class=\"btn\"
								onclick=\"muestra_datos_RS( {$r[0]}, 3 );\"
							>
								<i class=\"icon-cancel\"></i>
							</button>
						</td>";
					//echo '<td align="center"><a href="javascript:muestra_datos_RS('.$r[0].',3);"><img src="img/eliminar.png" width="30px"></a></td>';
				echo '</tr>'; 
			}
			?>
				</tbody>
			</table>
		</div>
	</div>

	<div class="" id="emergente_RS" style="display:none;overflow:auto;">
		<div style="position:absolute;top:2%;width:80%;left:10%;">
			<button class="cierra_emergente" onclick="cierra_emergente('emergente_RS');">X</button>
				<table width="100%" border="0" cellspacing="10px" cellpadding="10px;" style="border-radius:15px;">
					<tr>
						<td align="right" width="25%"><b class="desc_campo">ID:</b></td>
						<td align="center" width="25%"><input type="text" id="id_razon_social" class="entrada_txt" disabled></td>

						<td align="right" width="25%"><b class="desc_campo">Host:</b></td>
						<td align="center" width="25%"><input type="text" id="host_db" class="entrada_txt"></td>					
					</tr>
					<tr>
						<td align="right" width="25%"><b class="desc_campo">Nombre:</b></td>
						<td align="center" width="25%"><input type="text" id="nombre" class="entrada_txt"></td>

						<td align="right" width="25%"><b class="desc_campo">Usuario de BD</b></td>
						<td align="center" width="25%"><input type="text" id="usuario_db" class="entrada_txt"><br></td>
					</tr>
					<tr>
						<td align="right" width="25%"><b class="desc_campo">RFC:</b></td>
						<td align="center" width="25%"><input type="text" id="rfc" class="entrada_txt"></td>

						<td align="right" width="25%"><b class="desc_campo">Nombre de Base de Datos:</b></td>
						<td align="center" width="25%"><input type="text" id="nombre_db" class="entrada_txt"></td>
					</tr>
					<tr>	
						<td align="right" width="25%"><b class="desc_campo">Ruta de enlace:</b></td>
						<td align="center" width="25%"><input type="text" id="link" class="entrada_txt"></td>	
						
						<td align="right" width="25%"><b class="desc_campo">Activo:</b></td>
						<td align="center" width="25%"><input type="checkbox" id="activo" class="entrada_txt"></td>
					</tr>
					<tr>
						<td align="right" width="25%"><b class="desc_campo">Orden:</b></td>
						<td align="center" width="25%"><input type="text" id="orden" class="entrada_txt"></td>
						
						<td align="right" width="25%"><b class="desc_campo">Password Base de datos:</b></td>
						<td align="center" width="25%"><input type="text" id="contrasena_db" class="entrada_txt"><br></td>
					</tr>
					<tr>
						<td align="right" width="25%"><b class="desc_campo">Máximo de compras:</b></td>
						<td align="center" width="25%"><input type="text" id="maximo_compras" class="entrada_txt"></td>

						<td align="right" width="25%"><b class="desc_campo">Máximo de Ventas:</b></td>
						<td align="center" width="25%"><input type="text" id="maximo_ventas" class="entrada_txt"></td>
					</tr>
					<tr>
						<td align="right" width="25%"><b class="desc_campo">Compras Actuales:</b></td>
						<td align="center" width="25%"><input type="text" id="compras_actuales" class="entrada_txt"></td>

						<td align="right" width="25%"><b class="desc_campo">Ventas Actuales:</b></td>
						<td align="center" width="25%"><input type="text" id="ventas_actuales" class="entrada_txt"></td>
					</tr>
					<tr>
						<td align="right" width="25%"><b class="desc_campo">Inventario Precio Compra:</b></td>
						<td align="center" width="25%"><input type="text" id="inv_precio_compra" class="entrada_txt"></td>

						<td align="right" width="25%"><b class="desc_campo">Inventario Precio Venta:</b></td>
						<td align="center" width="25%"><input type="text" id="inv_precio_venta" class="entrada_txt"></td>
					</tr>
					<tr>
						<td align="right" width="25%"><b class="desc_campo">Color:</b></td>
						<td align="center" width="25%"><input type="text" id="color" class="entrada_txt"></td>

						<td align="right" width="25%"><b class="desc_campo">Observaciones:</b></td>
						<td align="center" width="25%"><textarea id="observaciones" class="entrada_txt"></textarea></td>
					</tr>
					<tr>
						<td align="right" width="25%"><b class="desc_campo">Ultima actualización:</b></td>
						<td align="center" width="25%"><input type="text" id="alta" class="entrada_txt" disabled></td>
						
						<td align="right" width="25%"><b class="desc_campo">Ultima Modificación:</b></td>
						<td align="center" width="25%"><input type="text" id="ultima_modificacion" class="entrada_txt" disabled><br><br></td>
					</tr>
					<tr>
						<td colspan="4" align="center">
						<button class="btn_med" onclick="guarda_RS();" id="guardar_rs">
								Guardar
						</button>
						</td>
					</tr>
				</table>

		</div>
	</div>

<script>
var id_rs,nombre_rs,link,user_db,pass_db,nombre_db,host_db,rfc,observaciones,status,orden,max_comp,comp_act,max_vtas,vtas_act,inv_prec_comp,inv_prec_vta,color;
	function guarda_RS(id_reg,flag){	//extraemos datos del formulario
		id_rs=$("#id_razon_social").val();
		nombre=$("#nombre").val();
		link=$("#link").val();
		user_db=$("#usuario_db").val();
		pass_db=$("#contrasena_db").val();
		nombre_db=$("#nombre_db").val();
		host_db=$("#host_db").val();
		rfc=$("#rfc").val();
		observaciones=$("#observaciones").val();
		status=$("#activo").val();
		orden=$("#orden").val();
		max_comp=$("#maximo_compras").val();
		comp_act=$("#compras_actuales").val();
		max_vtas=$("#maximo_ventas").val();
		vtas_act=$("#ventas_actuales").val();
		inv_prec_comp=$("#inv_precio_compra").val();
		inv_prec_vta=$("#inv_precio_venta").val();
		color=$("#color").val();
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
		url = ( id <= 0 ? './include/forms/formularioRazonSocial.php' : 'ajax/razonesSocialesBD.php' );
		if(flag==0){
			$("#guardar_rs").attr('onclick','guarda_RS(0,'+flag+')');
		}else{
		//enviamos datos por ajax
			$.ajax({
				type:'post',
				url: `${url}`,
				cache:false,
				data:{fl:4,id_reg:id},
				success:function(dat){
					$( '#alert_content' ).html( dat );
					$( '#alert' ).css( 'display', 'block' );
					return false;
					//alert('dat:'+dat);
					var aux=dat.split("|");
					if(aux[0]!='ok'){
						alert("Error!!!"+dat);
					}else{
						/*1*/$("#id_razon_social").val(aux[1]);
						/*2*/$("#nombre").val(aux[2]);
						/*3*/$("#link").val(aux[3]);
						/*4*/$("#usuario_db").val(aux[4]);
						/*5*/$("#contrasena_db").val(aux[5]);
						/*6*/$("#nombre_db").val(aux[6]);
						/*7*/$("#host_db").val(aux[7]);
						/*8*/$("#rfc").val(aux[8]);
						/*9*/$("#observaciones").val(aux[9]);

						/*10*/document.getElementById("activo").checked=true;
						if(aux[10]==0){
							/*10*/document.getElementById("activo").checked=false;
						}
						/*11*/$("#orden").val(aux[11]);
						/*12*/$("#maximo_compras").val(aux[12]);
						/*13*/$("#compras_actuales").val(aux[13]);
						/*14*/$("#maximo_ventas").val(aux[14]);
						/*15*/$("#ventas_actuales").val(aux[15]);
						/*16*/$("#inv_precio_compra").val(aux[16]);
						/*17*/$("#inv_precio_venta").val(aux[17]);
						/*18*/$("#color").val(aux[18]);
						/*19*/$("#alta").val(aux[19]);
						/*20*/$("#ultima_modificacion").val(aux[20]);
					}//fin de else
				}
			});//fin de ajax

	//		$("#emergente_RS").css("display","block");
		}//fin de else
	}//fin de funcion que carga datos

</script>
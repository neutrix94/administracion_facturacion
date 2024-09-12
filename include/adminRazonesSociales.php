<?php
	session_start();
	include('./db.php');
	$db = new db();
	$link = $db->conectDB();
	$_SESSION['current_view'] = $_POST['action'];
	$sql="SELECT id_razon_social,nombre,link,rfc,observaciones,activo, color FROM razones_sociales WHERE 1 ORDER BY orden ASC";
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
				echo "<tr tabindex=\"{$c}\" style=\"color : {$r[6]};\">
						<td>{$r[1]}</td>
						<td>{$r[3]}</td>
						<td>{$r[4]}</td>
						<td>{$r[5]}</td>
						<td align=\"center\">
							<button 
								type=\"button\"
								class=\"btn\"
								onclick=\"muestra_datos_RS( {$r[0]}, 1 );\"
							>
								<i class=\"icon-eye\" style=\"color : {$r[6]};\"></i>
							</button>
						</td>
						<td align=\"center\">
							<button 
								type=\"button\"
								class=\"btn\"
								onclick=\"muestra_datos_RS( {$r[0]}, 2 );\"
							>
								<i class=\"icon-pencil\" style=\"color : {$r[6]};\"></i>
							</button>
						</td>
						<td align=\"center\">
							<button 
								type=\"button\"
								class=\"btn\"
								onclick=\"muestra_datos_RS( {$r[0]}, 3 );\"
							>
								<i class=\"icon-cancel\" style=\"color : {$r[6]};\"></i>
							</button>
						</td>
					</tr>"; 
			}
			?>
				</tbody>
			</table>
		</div>
	</div>

<script>
var id_rs,nombre_rs,link,user_db,pass_db,nombre_db,host_db,rfc,observaciones,status,orden,max_comp,comp_act,max_vtas,vtas_act,inv_prec_comp,inv_prec_vta,color;
	function guarda_RS(id_reg,flag){	//extraemos datos del formulario
		id_rs=$("#id_razon_social").val();
		nombre_rs=$("#nombre").val();
		host_db=$("#host_db").val();
		link=$("#link").val();
		user_db=$("#usuario_db").val();
		pass_db=$("#contrasena_db").val();
		nombre_db=$("#nombre_db").val();
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
					flag : flag,
					id_registro : id_rs,
					nom_rs : nombre_rs,
					server : host_db,
					nombre_base_datos : nombre_db,
					rfc : rfc,
					enlace : link,
					ord : orden,
					pass_base_datos : pass_db,
					usuario_base_datos : user_db,
					activo : status,
					observ : observaciones
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
				type : 'post',
				url : `${url}`,
				cache : false,
				data : { fl : 4, id_reg : id },
				success : function( dat ){
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
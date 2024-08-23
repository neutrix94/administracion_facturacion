<?php
	session_start();
	$_SESSION['current_view'] = $_POST['action'];
	include('conexion.php');
	$sql="SELECT id_usuario,nombre,login,observaciones,activo FROM usuarios WHERE 1 ORDER BY id_usuario ASC";
	$eje=mysql_query($sql)or die("Error al listar las razones sociales!!!\n\n".mysql_error());
?>
	<style type="text/css">
		#listaRS{background: white;color:black;}
		#listaRS th{background: rgba(225,0,0,.5);height: 35px;}
	</style>
	<div style="width:90%;heigth:450px;">
		<br><b><p class="subtitulo" align="left">Administración de Timbres</p></b>
		<button class="bot_nvo" onclick="muestra_datos_user(0,1);">
			<img src="img/nuevo.png" width="40px"><br>Nuevo
		</button>		
		<center>
			<table width="100%" id="listaRS">
				<tr>
					<th width="20%">Nombre</th>
					<th width="20%">Login</th>
					<th width="15%">observaciones</th>
					<th width="15%">Activo</th>
					<th width="10%">Ver</th>
					<th width="10%">Editar</th>
					<th width="10%">Eliminar</th>
				</tr>
			<?php
			$c=0;//inicaimos el contador en cero
			while($r=mysql_fetch_row($eje)){
				$c++;//incrementamos contador
				echo '<tr id="fila_'.$c.'" tabindex="'.$c.'" onfocus="resalta('.$c.');" onclick="resalta('.$c.');" onblur="quita_resaltado('.$c.');">';
					echo '<td>'.$r[1].'</td>';
					echo '<td>'.$r[2].'</td>';
					echo '<td>'.$r[3].'</td>';
					echo '<td>'.$r[4].'</td>';
					echo '<td align="center"><a href="javascript:muestra_datos_RS('.$r[0].',1);"><img src="img/ver.png" width="30px"></a></td>';
					echo '<td align="center"><a href="javascript:muestra_datos_RS('.$r[0].',2);"><img src="img/editar.png" width="30px"></a></td>';
					echo '<td align="center"><a href="javascript:muestra_datos_RS('.$r[0].',3);"><img src="img/eliminar.png" width="30px"></a></td>';
				echo '<tr>'; 
			}//fin de while
			?>
			</table>
		</center>
	</div>

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

	function muestra_datos_user(id,flag){
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
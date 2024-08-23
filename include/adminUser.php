<?php
	session_start();
	$_SESSION['current_view'] = $_POST['action'];
	include('conexion.php');
	$sql="SELECT id_usuario,nombre,login,observaciones,activo FROM usuarios WHERE 1 ORDER BY id_usuario ASC";
	$eje=mysql_query($sql)or die("Error al listar las razones sociales!!!\n\n".mysql_error());
?>
	<style type="text/css">
		#listaRS{background: white;color:black;}
		#listaRS th{background: rgba(225,0,0,.5);height: 35px; color : white;}
		.row.content{
			background-color : white;
			padding : 40px;
			border-radius : 20px;
		}
		.row.content div{
			padding-top : 20px; 
		}
		.desc_campo_usr{
			color : black; 
		}
		.cierra_emergente.usr{
			right: -1.4% !important;
			border-radius: 50%;
			top : -2%;
		}
		.bot_nvo{
			position: fixed;
			top : 85%;
			right: 8%;
		}
	</style>
	<div style="width:90%;heigth:450px;">
		<br><b><p class="subtitulo" align="left"><img src="img/usuarios_icono.png" height="50px" style="position:absolute;left:10px;top:8%;">
		Administración de Usuarios</p></b>
		<button class="bot_nvo" onclick="muestra_datos_user(0,1);">
			<img src="img/nuevo.png" width="40px"><br>Nuevo
		</button>		
		<center>
			<table class="table table-striped" width="100%" id="listaRS">
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
				$estado="";
				if($r[4]==1){
					$estado='checked';
				}
				echo '<tr id="fila_'.$c.'" tabindex="'.$c.'" onfocus="resalta('.$c.');" onclick="resalta('.$c.');" onblur="quita_resaltado('.$c.');">';
					echo '<td>'.$r[1].'</td>';
					echo '<td>'.$r[2].'</td>';
					echo '<td>'.$r[3].'</td>';
					echo '<td align="center"><input type="checkbox" '.$estado.' disabled></td>';
					echo '<td align="center"><a href="javascript:muestra_datos_user('.$r[0].',0);"><img src="img/ver.png" width="30px"></a></td>';
					echo '<td align="center"><a href="javascript:muestra_datos_user('.$r[0].',2);"><img src="img/editar.png" width="30px"></a></td>';
					echo '<td align="center"><a href="javascript:muestra_datos_user('.$r[0].',3);"><img src="img/eliminar.png" width="30px"></a></td>';
				echo '<tr>'; 
			}//fin de while
			?>
			</table>
		</center>
	</div>

	<div class="form_emergente" id="emergente_users" style="display:none;">
		<div style="position:absolute;top:10%;width:80%;left:10%;">
			<button class="cierra_emergente usr" onclick="cierra_emergente('emergente_users');">X</button>
				<!--table width="100%" border="0" cellspacing="10px" cellpadding="10px;" class="table table-striped"><!-- style="background:#B0C4DE;border-radius:15px;" -->
					<!--tr-->
				<div class="row content">
					<div class="col-2">
						<b class="desc_campo_usr">ID:</b>
					</div>
					<div class="col-4">
						<input type="text" id="id_user" class="form-control" disabled>
					</div>
				<!--td align="right" width="25%"><b class="desc_campo">ID:</b></td>
				<td align="center" width="25%"><input type="text" id="id_user" class="entrada_txt" disabled></td-->
					<div class="col-2">
						<b class="desc_campo_usr">Activo:</b>
					</div>
					<div class="col-4">
						<input type="checkbox" id="estado">
					</div>
				<!--td align="right" width="25%"><b class="desc_campo">Activo:</b></td>
				<td align="center" width="25%"><input type="checkbox" id="estado"></td>					
				</tr>
				<tr-->
					<div class="col-2">
						<b class="desc_campo_usr">Nombre:</b>
					</div>
					<div class="col-4">
						<input type="text" id="nombre" class="form-control" >
					</div>
				<!--td align="right" width="25%"><b class="desc_campo">Nombre:</b></td>
				<td align="center" width="25%"><input type="text" id="nombre" class="entrada_txt"></td-->
					<div class="col-2">
						<b class="desc_campo_usr">Login:</b>
					</div>
					<div class="col-4">
						<input type="text" id="login"  class="form-control" >
					</div>
				<!--td align="right" width="25%"><b class="desc_campo">Login:</b></td>
				<td align="center" width="25%"><input type="text" id="login" class="entrada_txt"><br></td>
					</tr>
					<tr-->
					<div class="col-2">
						<b class="desc_campo_usr">Password:</b>
					</div>
					<div class="col-4">
						<input type="password" id="pass"  class="form-control" style="background:rgba(0,225,225,0.3);" title="ingrese la nueva contraseña!!!!">
					</div>
				<!--td align="right" width="25%"><b class="desc_campo">Password:</b></td>
				<td align="center" width="25%">
					<input type="password" id="pass" class="entrada_txt" style="background:rgba(225,0,0,0.3);" title="ingrese la nueva contraseña!!!!">
				</td-->
					<div class="col-2">
						<b class="desc_campo_usr">Fecha alta:</b>
					</div>
					<div class="col-4">
						<input type="text" id="fecha_alta"  class="form-control" disabled>
					</div>
				<!--td align="right" width="25%"><b class="desc_campo">Fecha alta:</b></td>
				<td align="center" width="25%"><input type="text" id="fecha_alta" class="entrada_txt" disabled></td>
				</tr>
				<tr-->
					<div class="col-2">
						<b class="desc_campo_usr">Tipo de Perfil:</b>
					</div>
					<div class="col-4" id="celda_combo_perfil">
						<select  class="form-control"  id="tipo_perfil">
							<option>--Seleccionar--</option>
						</select>
					</div>

						<!--td align="right" width="25%"><b class="desc_campo">Tipo de Perfil:</b></td>
						<td align="center" width="25%" id="celda_combo_perfil">
							<select class="entrada_txt" id="tipo_perfil">
								<option>--Seleccionar--</option>
							</select>
						</td-->
					<div class="col-2">
						<b class="desc_campo_usr">Observaciones:</b>
					</div>
					<div class="col-4" id="celda_combo_perfil">
						<textarea id="observaciones"  class="form-control" ></textarea>
					</div>

				<!--td align="right" width="25%"><b class="desc_campo">Observaciones:</b></td>
				<td align="center" width="25%"><textarea id="observaciones" class="entrada_txt"></textarea></td>
				<td></td><td></td>
				</tr-->

					<div class="col-3"></div>
					<div class="col-6">
						<button class="btn btn-success form-control" onclick="guarda_user();" id="guardar_user">
								Guardar
						</button>
					</div>
					<div class="col-3"></div>
					<!--tr>
						<td colspan="4" align="center">
						<button class="btn_med" onclick="guarda_user();" id="guardar_user">
								Guardar
						</button>
						</td>
					</tr-->
				</div>
				<!--/table-->

		</div>
	</div>

<script>
	
	var id_rg='',nombre='',login='',contrasena_usr='',id_perfil='',observaciones='',activo='',fecha_alta='';
	
	function guarda_user(id_reg,flag){
	//extraemos datos del formulario
		id_rg=$("#id_user").val();
		nombre=$("#nombre").val();
		if(nombre.length<=0){alert("El campo nombre no puede ir vacío!!!");$("#nombre").focus();return false;}
		
		login=$("#login").val();
		if(login.length<=0){alert("El campo login no puede ir vacío!!!");$("#login").focus();return false;}
		
		if(($("#pass").val()).length>0){
			contrasena_usr=$("#pass").val();
			if(contrasena_usr.length<=0 && flag==1){alert("La contraseña no puede ir vacía!!!");$("#pass").focus();return false;}
		}
		

		id_perfil=$("#tipo_perfil").val();
		if(id_perfil=='-1'){alert("Debe seleccionar un tipo de perfil!!!");$("#tipo_perfil").focus();return false;}

		observaciones=$("#observaciones").val();
	//activado/desactivado
		activo=1;
		if(document.getElementById("estado").checked==false){
			activo=0;
		}//
			//alert(activo);

		fecha_alta=$("#fecha_alta").val();
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/userBD.php',
			cache:false,
			data:{
					flag:flag,
					id_registro_user:id_rg,
					nom_usr:nombre,
					log:login,
					pass:contrasena_usr,
					id_prf:id_perfil,
					obs:observaciones,
					estado:activo,
					alta:fecha_alta
				},
				success:function(dat){
					var aux=dat.split("|");
					if(aux[0]!='ok'){
						alert("Error!!!\n\n"+dat);
						return false;
					}else{
						alert(aux[1]);
						carga_pantalla('adminUser');
				}
			}
		});
	} 

	function muestra_datos_user(id,flag){
		//alert(id);
		$("#guardar_user").attr('onclick','guarda_user('+id+','+flag+');');
		if(flag==1){
			$.ajax({
				type:'post',
				url:'ajax/userBD.php',
				cache:false,
				data:{flag:5},
				success:function(dat){
					$("#celda_combo_perfil").html(dat);$("#id_user").val('');	
					$("#nombre").prop('disabled',false);$("#nombre").val('');	
					$("#login").prop('disabled',false);$("#login").val('');
					$("#pass").prop('disabled',false);$("#pass").val('');
					$("#observaciones").prop('disabled',false);$("#observaciones").val('');
					$("#estado").prop('disabled',false);$("#estado").val('');
				}
			});//fin de ajax
		}else{
		//enviamos datos por ajax
			$.ajax({
				type:'post',
				url:'ajax/userBD.php',
				cache:false,
				data:{flag:4,id_registro_user:id},
				success:function(dat){
					//alert('dat:'+dat);
					var aux=dat.split("|");
					if(aux[0]!='ok'){
						alert("Error!!!"+dat);
					}else{
						$("#id_user").val(aux[1]);
						$("#nombre").val(aux[2]);
						$("#login").val(aux[3]);
						//$("#pass").val(aux[4]);
						$("#celda_combo_perfil").html(aux[5]);
						$("#observaciones").val(aux[6]);
					//activado/desactivado
						document.getElementById("estado").checked=true;
						if(aux[7]==0){
							document.getElementById("estado").checked=false;
						}
						$("#fecha_alta").val(aux[8]);
						if(flag==1){
							$("#nombre").prop('disabled',true);
							$("#login").prop('disabled',true);
							$("#tipo_perfil").prop('disabled',true);
							$("#pass").prop('disabled',true);
							$("#observaciones").prop('disabled',true);
							$("#estado").prop('disabled',true);
						}else{
							$("#nombre").prop('disabled',false);
							$("#login").prop('disabled',false);
							$("#tipo_perfil").prop('disabled',false);
							$("#pass").prop('disabled',false);
							$("#observaciones").prop('disabled',false);
							$("#estado").prop('disabled',false);
						}
					}//fin de else
				}
			});//fin de ajax
		}//fin de else
		//deshabilitamos la edición de campos
		if(flag==0){
			$("#guardar_user").css("display","none");
		}else{
			$("#guardar_user").css("display","block");			
		}

		$("#emergente_users").css("display","block");
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

	function habilitar_combos(){

	}
	function deshabilitar_combos(){

	}


</script>
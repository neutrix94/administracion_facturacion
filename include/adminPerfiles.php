<?php
	session_start();
	$_SESSION['current_view'] = $_POST['action'];
	//include('conexion.php');
	include('./db.php');
	$db = new db();
	$link = $db->conectDB();
	$sql="SELECT id_perfil,nombre,es_admin,observaciones,activo FROM perfiles WHERE 1 ORDER BY id_perfil ASC";
	$eje = $link->query( $sql )or die( "Error al listar las razones sociales : {$sql}" );
?>
	<style type="text/css">
		/*#listaRS{background: white;color:black;}
		#listaRS th{background: rgba(225,0,0,.5);height: 35px;}*/
	</style>
	<div style="width:90%;heigth:450px;">
		<br><b><p class="subtitulo" align="left">Listado de Perfiles<button class="btn btn-info" onclick="muestra_datos_perfil(0,1);">
			<i class="icon-plus">Nuevo</i>
		</button></p></b>
				
		<div>
			<table width="100%" id="listaRS" class="table table-striped table-bordered">
				<thead class="bg-primary text-light">
					<tr>
						<th width="30%" class="text-center">Nombre</th>
						<th width="15%" class="text-center">Es Admin</th>
						<th width="25%" class="text-center">observaciones</th>
						<th width="15%" class="text-center">Activo</th>
						<th width="10%" class="text-center">Ver</th>
						<th width="10%" class="text-center">Editar</th>
						<th width="10%" class="text-center">Eliminar</th>
					</tr>
				</thead>
				<tbody>
			<?php
			$c=0;//inicaimos el contador en cero
			while( $r = $eje->fetch() ){
				$c++;//incrementamos contador
				$estado="";$es_admin="";
				if($r[4]==1){
					$estado='checked';
				}
				if($r[2]==1){
					$es_admin="checked";
				}
				echo '<tr id="fila_'.$c.'" tabindex="'.$c.'" onfocus="resalta('.$c.');" onclick="resalta('.$c.');" onblur="quita_resaltado('.$c.');">';
					echo '<td>'.$r[1].'</td>';
					echo '<td align="center"><input type="checkbox" '.$es_admin.' disabled></td>';
					echo '<td>'.$r[3].'</td>';
					echo '<td align="center"><input type="checkbox" '.$estado.' disabled></td>';
					echo "<td class=\"text-center\">
					<button
						type=\"button\"
						class=\"btn\"
						onclick=\"muestra_datos_perfil( {$r[0]} , 0 );\"
					>
						<i class=\"icon-eye\"></i>
					</button>
				</td>
				<td class=\"text-center\">
					<button
						type=\"button\"
						class=\"btn\"
						onclick=\"muestra_datos_perfil( {$r[0]} , 2 );\"
					>
						<i class=\"icon-pencil\"></i>
					</button>
				</td>
				<td class=\"text-center\">
					<button
						type=\"button\"
						class=\"btn\"
						onclick=\"muestra_datos_perfil( {$r[0]} , 3 );\"
					>
						<i class=\"icon-cancel\"></i>
					</button>
				</td>";
				echo '</tr>'; 
			}//fin de while
			?>
				</tbody>
			</table>
		</div>
	</div>

	<div class="form_emergente" id="emergente_perfiles" style="display:none;">
		<div style="position:absolute;top:10%;width:80%;left:10%;">
			<button class="cierra_emergente" onclick="cierra_emergente('emergente_perfiles');">X</button>
				<table width="100%" border="0" cellspacing="10px" cellpadding="10px;" style="background:#B0C4DE;border-radius:15px;">
					<tr>
						<td align="right" width="25%"><b class="desc_campo">ID:</b></td>
						<td align="center" width="25%"><input type="text" id="id_perfil" class="entrada_txt" disabled></td>

						<td align="right" width="25%"><b class="desc_campo">Es Admin:</b></td>
						<td align="center" width="25%"><input type="checkbox" id="es_admin"></td>					
					</tr>
					<tr>
						<td align="right" width="25%"><b class="desc_campo">Nombre:</b></td>
						<td align="center" width="25%"><input type="text" id="nombre" class="entrada_txt"></td>

						<td align="right" width="25%"><b class="desc_campo">Observaciones:</b></td>
						<td align="center" width="25%"><textarea id="observaciones" class="entrada_txt"></textarea></td>
					</tr>
					<tr>
						<td align="right" width="25%"><b class="desc_campo">Ultima modificación</b></td>
						<td align="center" width="25%">
							<input type="text" id="ultima_modificacion" class="entrada_txt" disabled="">
						</td>

						<td align="right" width="25%"><b class="desc_campo">Fecha alta:</b></td>
						<td align="center" width="25%"><input type="text" id="fecha_alta" class="entrada_txt" disabled></td>
					</tr>
					<tr>
						<td align="right" width="25%"><b class="desc_campo">Activo:</b></td>
						<td align="center" width="25%"><input type="checkbox" id="estado"></td>	
						<td></td><td></td>
					</tr>
					<tr>
						<td colspan="4" align="center">
						<button class="btn_med" onclick="guarda_perfil();" id="guardar_perfil">
								Guardar
						</button>
						</td>
					</tr>
				</table>

		</div>
	</div>

<script>
	
	var id_rg='',nombre='',es_adm='',activo='',observaciones='';
	
	function guarda_perfil(id_reg,flag){
	//extraemos datos del formulario
		/*id_rg=$("#id_pefil").val();
		alert($("#id_pefil").val());
		return false;*/
		nombre=$("#nombre").val();
		if(nombre.length<=0){alert("El campo nombre no puede ir vacío!!!");$("#nombre").focus();return false;}
	//es admin/ no es admin
		es_adm=1;
		if(document.getElementById("es_admin").checked==false){
			es_adm=0;
		}
	//activado/desactivado
		activo=1;
		if(document.getElementById("estado").checked==false){
			activo=0;
		}
		observaciones=$("#observaciones").val();
		//alert(observaciones);return false;
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/perfilesBD.php',
			cache:false,
			data:{
					flag:flag,
					id_registro_perfil:id_reg,
					nomb:nombre,
					admin:es_adm,
					act:activo,
					observaciones:observaciones,
				},
				success:function(dat){
					var aux=dat.split("|");
					if(aux[0]!='ok'){
						alert("Error!!!\n\n"+dat);
						return false;
					}else{
						alert(aux[1]);
						carga_pantalla('adminPerfiles');
				}
			}
		});
	} 

	function muestra_datos_perfil(id,flag){
		//alert(id);
		$("#guardar_perfil").attr('onclick','guarda_perfil('+id+','+flag+');');
		if(flag==1){
			$.ajax({
				type:'post',
				url:'ajax/perfilesBD.php',
				cache:false,
				data:{flag:5},
				success:function(dat){
					$("#id_perfil").val('');	
					$("#nombre").prop('disabled',false);$("#nombre").val('');	
					$("#es_admin").prop('disabled',false);$("#es_admin").prop("checked",false);
					$("#observaciones").prop('disabled',false);$("#observaciones").val('');
					$("#ultima_modificacion").val('');
					$("#fecha_alta").prop('disabled',false);$("#fecha_alta").val('');
					$("#estado").prop('disabled',false);$("#estado").prop("checked",true);
				}
			});//fin de ajax
		}else{
		//enviamos datos por ajax
			$.ajax({
				type:'post',
				url:'ajax/perfilesBD.php',
				cache:false,
				data:{flag:4,id_registro_perfil:id},
				success:function(dat){
					var aux=dat.split("|");
					if(aux[0]!='ok'){
						alert("Error!!!"+dat);
					}else{
						$("#id_perfil").val(aux[1]);
						$("#nombre").val(aux[2]);
					//admin/no admin
						document.getElementById("es_admin").checked=true;
						if(aux[3]==0){
							document.getElementById("es_admin").checked=false;
						}
					//activado/desactivado
						document.getElementById("estado").checked=true;
						if(aux[4]==0){
							document.getElementById("estado").checked=false;
						}
						$("#observaciones").val(aux[5]);
						$("#fecha_alta").val(aux[6]);
						$("#ultima_modificacion").val(aux[7]);

						if(flag==0){
							$("#nombre").prop('disabled',true);
							$("#es_admin").prop('disabled',true);
							$("#estado").prop('disabled',true);
							$("#observaciones").prop('disabled',true);
						}else{
							$("#nombre").prop('disabled',false);
							$("#es_admin").prop('disabled',false);
							$("#estado").prop('disabled',false);
							$("#observaciones").prop('disabled',false);
						}
					}//fin de else
				}
			});//fin de ajax
		}//fin de else
		//deshabilitamos la edición de campos
		if(flag==0){
			$("#guardar_perfil").css("display","none");
		}else{
			$("#guardar_perfil").css("display","block");			
		}

		$("#emergente_perfiles").css("display","block");
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
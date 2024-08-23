<?php
	session_start();
	$_SESSION['current_view'] = $_POST['action'];
	include('conexion.php');
	$sql="SELECT id_perfil,nombre FROM perfiles WHERE 1 ORDER BY id_perfil ASC";
	$eje=mysql_query($sql)or die("Error al listar las razones sociales!!!\n\n".mysql_error());
?>
	<style type="text/css">
		#listaRS{background: white;color:black;}
		#listaRS th{background: rgba(225,0,0,.5);height: 35px;}
		#permisos_prfil th{background:rgba(225,0,0,.5);height: 35px;color:white;}
		.celda_contenido{background: white;width: 100%;height: 350px;overflow: scroll;top:0;}
		#permisos_contenido{top:0;width: 103%;border: 3px solid;position:relative;}
		#listPermisos{}
	</style>
	<div style="width:90%;heigth:450px;">
		<br><b><p class="subtitulo" align="left"><img src="img/permisos.png" height="50px" style="position:absolute;left:10px;top:8%;">
		Permisos de perfil</p></b>
			<table width="60%" id="listaRS">
				<tr>
					<th width="50%">Pefil</th>
					<th width="25%">Ver<br>Permisos</th>
					<th width="25%">Editar<br>Permisos</th>
				</tr>
		<?php
			$c=0;//inicaimos el contador en cero
			while($r=mysql_fetch_row($eje)){
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
					echo '<td align="center"><a href="javascript:muestra_permisos('.$r[0].',0);"><img src="img/ver.png" width="30px"></a></td>';
					echo '<td align="center"><a href="javascript:muestra_permisos('.$r[0].',2);"><img src="img/editar.png" width="30px"></a></td>';
				echo '<tr>'; 
			}//fin de while
		?>
			</table>
		</center>
	</div>

	<div class="form_emergente" id="emergente_perfiles" style="display:none;">
		<div style="position:absolute;top:10%;width:80%;left:10%;">
			<button class="cierra_emergente" onclick="cierra_emergente('emergente_perfiles');">X</button>
				<table width="90%" id="permisos_prfil" border="0" cellspacing="0px" cellpadding="10px;" style="background:#B0C4DE;border-radius:15px;">
					<tr>
						<th width="40%">Nombre de<br>menú</th>
						<th width="20%">Ver</th>
						<th width="20%">Modificar</th>
						<th width="20%">Eliminar</th>		
					</tr>
					<tr>
				<!--listado de permisos-->
						<td colspan="4" class="celda_contenido"  style="border:1px solid;">
							<div id="permisos_contenido"></div>
						</td>
				<!--fin de listado de permisos-->
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
	var num_permisos = 0;
	
	function guarda_permisos(id_reg,flag){
		var datos = '';
		var permisos = get_permisos();
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/permisosBD.php',
			cache:false,
			data:{
					flag : 2,
					id_registro_perfil : id_reg,
					values : permisos
				},
			success : function(dat){
				var aux=dat.split("|");
				if(aux[0]!='ok'){
					alert("Error!!!\n\n"+dat);
					return false;
				}else{
					alert(aux[1]);
					carga_pantalla('adminPermisos');
				}
			}
		});
	}

	function get_permisos(){
		var resp = '';
		for( var i = 1; i <= num_permisos; i++ ){
			resp += $("#1_" + i).html() + '~';
			resp += ( $("#3_" + i).prop("checked") == true ? '1' : 0 ) + '~';
			resp += ( $("#4_" + i).prop("checked") == true ? '1' : 0 ) + '~';
			resp += ( $("#5_" + i).prop("checked") == true ? '1' : 0 ) + '~';
		}//fin de for i
		return resp;
	}

	function muestra_permisos(id,flag){
		//alert(id);
		$("#guardar_perfil").attr('onclick','guarda_permisos('+id+','+flag+');');
		//enviamos datos por ajax
		$.ajax({
				type:'post',
				url:'ajax/permisosBD.php',
				cache:false,
				data:{flag:4,id_registro_perfil:id},
				success:function(dat){
				//alert(dat);
					var aux=dat.split("|");
					if(aux[0]!='ok'){
						alert("Error!!!"+dat);
					}else{
						$("#permisos_contenido").html(aux[1]);
						num_permisos = aux[2];

						if(flag==0){//deshabilitamos los check
							for(var i=1;i<=aux[2];i++){
								$("#3_"+i).prop("disabled",true);
								$("#4_"+i).prop("disabled",true);
								$("#5_"+i).prop("disabled",true);
							}//fin de for i
						}else{
							for(var i=1;i<=aux[2];i++){
								$("#3_"+i).prop("disabled",false);
								$("#4_"+i).prop("disabled",false);
								$("#5_"+i).prop("disabled",false);
							}//fin de for i
						}
					}//fin de else
				}
			});//fin de ajax
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
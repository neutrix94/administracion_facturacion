<?php
//1. Abre el archivo /conexion_inicial.txt
	$path = "conf.json";
	$conf_loc = array();
    $file = 'conf.json';  // Ruta al archivo JSON
    $config = array();
	if(file_exists($file)){
    	$json_data = file_get_contents($file);  // Lee el contenido del archivo
		if ($json_data === false) {
			echo "Error al leer el archivo JSON.";
		} else {
			$config = json_decode($json_data, true);
		}
	}
?>
<!DOCTYPE html>
<!-- 2. Estilos CSS -->
<style type="text/css">
	#global{width:100%;height: 100%;position: absolute; top:0;left:0; /*background-image: url("img/backgrounds/general.jpg");*/}
	.entrada{padding: 12px;border-radius: 15px;width: 105%;}
	.descripcion{color:black;font-size: 120%;}
	#impresoras{background: white;}
	th{background: red;padding: 10px; color: white;}
	#emergente{position: fixed; background: rgba(0,0,0,.5);display: none; width: 100%;height: 100%;top:0; left: 0;}
	*{
		font-size: 95%;
	}
</style>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>Configuración Inicial</title>

	<script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>
	<link rel="stylesheet" type="text/css" href="css/bootstrap/css/bootstrap.css">
	<!--link rel="stylesheet" type="text/css" href="css/icons/css/fontello.css"-->
	<!--script type="text/javascript" src="js/jquery-ui.js"></script-->
	<script type="text/javascript" src="css/bootstrap/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<!-- 3. Formulario de configuracion -->
	<div id="global">
        <h3 class="text-center">Configuracion de Sistema de Facturación</h3>
		<div class="accordion" id="accordionExample" style="background-color : transparent !important ;">
			<div class="accordion-item"> <!--  style="background-color : transparent !important ;" -->
				<h2 class="accordion-header" id="heading_1_0">
					<button 
						class="accordion-button" 
						type="button" 
                        data-bs-toggle="collapse" 
						data-bs-target="#collapse_1_0" 
						aria-expanded="true" 
						aria-controls="collapse_1_0" 
						onclick=""
						id="herramienta_1_0">
						<i class="icon-database" style="font-size : 120%;">Configuracion de conexiones</i>
					</button>
				</h2>
				<div 
					id="collapse_1_0" 
					class="accordion-collapse description" 
					aria-labelledby="heading_1_0" 
					data-bs-parent="#accordionExample">
					<div class="accordion-body">
						<div class="row">
							<div class="col-12" id="local_host_container">
								<table class="table"><!--  style="position:absolute;top:10%;left:1%;" -->
									<tr>
										<td align="left"><b class="descripcion">Host Local:</b>
											<input type="text" id="host_loc" class="form-control" value="<?php echo ( isset($config['system_host']) ? base64_decode($config['system_host']) : '' );?>" placeholder="localhost/ www.dominio...">
										</td>
									</tr>
									<tr>
										<td align="left"><b class="descripcion">Ruta Local:</b>
											<input type="text" id="ruta_loc" class="form-control" value="<?php echo ( isset($config['system_path']) ? base64_decode($config['system_path']) : '' );?>" placeholder="carpeta(s) del sistema">
										</td>
									</tr>
									<tr>
										<td align="left"><b class="descripcion">Nombre BD Local:</b>
											<input type="text" id="nombre_bd_loc" value="<?php echo ( isset($config['system_database_name']) ? base64_decode($config['system_database_name']) : '');?>" class="form-control">
										</td>
									</tr>
									<tr>
										<td align="left"><b class="descripcion">Usuario BD Local:</b>
											<input type="text" id="usuario_bd_loc" value="<?php echo ( isset($config['system_database_user']) ? base64_decode($config['system_database_user']) : '');?>" class="form-control">
										</td>
									</tr>
									<tr>
										<td align="left"><b class="descripcion">Password BD Local:</b>
											<input type="text" id="pass_bd_loc" value="<?php echo ( isset($config['system_database_password']) ? base64_decode($config['system_database_password']) : '' );?>" class="form-control">
										</td>
									</tr>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>		
	</div>

	<button 
		class="form-control btn btn-success"
		onclick="genera_config();"
		style="position:absolute;top:50%;right:5%; width : 90%;padding:10px;"
	><!--  -->
		<b>
			Crear Configuracion
		</b>
	</button>
	
	<div id="emergente"></div>
</body>
</html>
<!-- 4. Funciones JavaScript -->
<script type="text/javascript">
	function genera_config(){
        var system_host, system_path, system_database_name, system_database_user, system_database_password;
		system_host = $("#host_loc").val();
		if( system_host.length <= 0 ){
			alert("El campo de Host local no puede ir vacío.");
			$("#host_loc").focus();
			return false;
		}
		system_path = $("#ruta_loc").val();
		if( system_path.length <= 0 ){
			alert("El campo de Ruta Local no puede ir vacío.");
			$("#ruta_loc").focus();
			return false;
		}
		system_database_name = $("#nombre_bd_loc").val();
		if( system_database_name.length <= 0 ){
			alert("El campo de Nombre de Base de datos no puede ir vacío.");
			$("#nombre_bd_loc").focus();
			return false;
		}
		system_database_user = $("#usuario_bd_loc").val();
		if( system_database_user.length <= 0 ){
			alert("El usuario de Base de Datos no puede ir vacío.");
			$("#usuario_bd_loc").focus();
			return false;
		}
		var system_database_password = $("#pass_bd_loc").val();
	//envia datos por ajax
		$.ajax({
			type:'post',
			url:'include/ajax/setSystemInitialConfiguration.php',
			cache:false,
			data : {
				host : system_host,
				path : system_path,
				db_name : system_database_name,
				db_user : system_database_user,
				db_password : system_database_password
			},
			success:function(dat){
				if(dat!='ok'){
					alert(`Error, actualiza la pantalla y vuelve a intentar.\n${dat}`);
					return false;
				}else{
					alert("La configuración fue guardada exitosamente.");
					location.href = 'index.php?';
				}
			}
		});
	}

	function close_emergent(){
		$("#emergente").html( '' );
		$("#emergente").css("display", "none");
	}
</script>

<style type="text/css">
	@media only screen and (max-width: 600px) {
		* {
			font-size : 50% !important;
		}
	}
</style>
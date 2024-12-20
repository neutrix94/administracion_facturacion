<?php
	session_start();
	$_SESSION['current_view'] = $_POST['action'];
	include('../../db.php');
	$db = new db();
	$link = $db->conectDB();
	include('ajax/makeList.php');
?>
<div>
	<link rel="stylesheet" href="include/specials/special_queries/css/estilos.css">
	<script type="text/javascript" src="css/bootstrap/js/bootstrap.bundle.min.js"></script>
	<script type="text/javascript" src="include/specials/special_queries/js/utils.js"></script>
	<div class="row" style="height : 700px !important; overflow:auto;">
		<div id="izquierda_" class="col-2 bg-primary">
			<table width="99%">
				<tr class="encabezado">
					<th class="bg-primary">
						<input type="text" class="form-control" 
						onkeyup="search_menu(this, event);" placeholder="Buscador..."/>
					</th>
					<th class="bg-primary">
						<button onclick="carga_form(0);" class="btn btn-success" 
						title="Click para agregar nueva Herramienta"><b>+</b></button>
					</th>
				</tr>
			</table>
	<!-- Listado de Consultas -->
			<div class="queries_list">
				<h5>Consultas</h5>
			<?php
				echo build_accordeon($link, 'Consulta', 1 );
			?>
			</div>	  

	<!-- Listado de Herramientas -->
			<div class="queries_list">
				<h5>Herramientas</h5>
			<?php
				echo build_accordeon( $link, 'Herramienta', 2 );
			?>
			</div>

		</div>
		<div id="derecha_" class="col-10">
		<!--Filtros-->
			<div id="filtros" class="row bg-primary">
				<b class="filter">Filtros</b>
			</div>
		<!--contenido-->
			<div class="row">
				<div id="resultados" class="col-10 bg-light">
				</div>
				<div id="info_consulta" class="col-2 bg-primary">
			<!--caja de texto donde se muestran las consultas-->
					<textarea class="consulta" id="txt_consulta" disabled onclick="habilitar_txt_consulta();"></textarea>
			<!--botones para modificar y agregar consultas-->
					<div class="row text-center" style="margin-top : 40px;">
						<button id="edita_consulta" onclick="carga_form(-1);" class="btn btn-primary form-control"><!-- btn_opc -->
							Editar
						</button>
						<button class="btn btn-danger form-control">
							Cancelar
						</button>
						<button class="btn btn-success form-control">
							Guardar Nuevo
						</button>
						<button class="btn btn-warning form-control" onclick="home_redirect();">
							Ir al Panel
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>

	<form id="TheForm" method="post" action="include/specials/special_queries/ajax/genera_consulta.php" target="TheWindow">
		<input type="hidden" name="fl" value="1" />
		<input type="hidden" id="datos" name="datos" value=""/>
	</form>
</div>
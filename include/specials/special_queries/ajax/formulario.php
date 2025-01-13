<!--head>
	<link rel="stylesheet" href="css/estilos.css">
</head-->

<?php
	
/*implementación Oscar 2021 para ejecutar consultas con MYSQLI*/
	//include('../../../../config.inc.php');
	//$link = mysqli_connect($dbHost, $dbUser, $dbPassword, $dbName);
	//$link->set_charset("utf8");
	include( '../../../db.php' );
	$db = new db();
	$link = $db->conectDB();
	$id_h=$_POST['id'];
	$result="";
	$r = array();
	if($id_h!=0){
		$sql="SELECT 
				$id_h,/*0*/
				titulo,/*1*/
				consulta,/*2*/
				descripcion,/*3*/
				campo_filtro_sucursal,/*4*/
				campo_filtro_fecha1,/*5*/
				campo_filtro_fecha2,/*6*/
				campo_filtro_familia,/*7*/
				campo_filtro_tipo,/*8*/
				campo_filtro_subtipo,/*9*/
				campo_filtro_color,/*10*/
				campo_filtro_almacen,/*11*/
				campo_filtro_es_externo,/*12*/
				tipo_herramienta/*13*/
			FROM sys_herramientas 
			WHERE id_herramienta=$id_h";
		$eje= $link->query($sql)or die("Error al consultar los datos de la herramienta!!!\n" . $link->error);
		//$r=mysql_fetch_row($eje);
		$r = $eje->fetch();
	}else{//if($r[0]=='' || $r[0]==null)
		$r[0]="(Automático)";
	}

	$placeholder='placeholder="$CARACTER_REEMPLAZAR|campo_comparacion|tipo_elemento|consulta_combo/Formato fecha|onchange|id_elemento_html|titulo"';
/*implementacion Oscar 2021 para poner el tipo de consulta*/
	$combo_tipos = "<select id=\"query_type\" class=\"form-control\" display:inline;\">"
			. "<option value=\"Consulta\" " . ( isset($r[13]) && $r[13]== 'Consulta' ? 'selected' :null) . ">Consulta</option>"
			. "<option value=\"Herramienta\"" . ( isset($r[13]) && $r[13]== 'Herramienta' ? 'selected' :null) . ">Herramienta</option>"
		. "</select>";

	echo '<button type="button" title="Cerrar" onclick="document.getElementById(\'emergente\').style.display=\'none\';" class="btn_cerrar btn btn-danger">X</button>';
	echo '<form>';
		echo '<table class="tabla_formulario">';
				echo '<tr>';
					echo '<td class="titulo" width="20%">ID: </td>';
					echo '<td width="80%" align="left"><input type="text" id="id_herramienta" value="'.( isset($r[0]) ? $r[0] : '' ).'" class="form-control" disabled></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td class="titulo" width="20%">Tipo : </td>';
					echo '<td width="80%" align="left">' . $combo_tipos . '</td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td class="titulo">Título:</td>';
					echo '<td><textarea id="titulo" class="form-control">'. ( isset($r[1]) ? $r[1] : '' ) .'</textarea></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td class="titulo">Consulta:</td>';
					echo '<td><textarea id="consulta" class="form-control">'. ( isset($r[2]) ? $r[2] : '' ) .'</textarea></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td class="titulo">Descripción:</td>';
					echo '<td><textarea id="descripcion" class="form-control">'. ( isset($r[3]) ? $r[3] : '' ) .'</textarea></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td class="titulo">Filtro Sucursal:</td>';
					echo '<td><textarea id="campo_filtro_sucursal" class="form-control" '.$placeholder.'>'. ( isset($r[4]) ? $r[4] : '' ) .'</textarea></td>';
				echo '</tr>';

				echo '<tr>';
					echo '<td class="titulo">Filtro Fecha 1:</td>';
					echo '<td><textarea id="campo_filtro_fecha_1" class="form-control" '.$placeholder.'>'. ( isset($r[5]) ? $r[5] : '' ) .'</textarea></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td class="titulo">Filtro Fecha 2:</td>';
					echo '<td><textarea id="campo_filtro_fecha_2" class="form-control" '.$placeholder.'>'. ( isset($r[6]) ? $r[6] : '' ) .'</textarea></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td class="titulo">Filtro Familia:</td>';
					echo '<td><textarea id="campo_filtro_familia" class="form-control" '.$placeholder.'>'. ( isset($r[7]) ? $r[7] : '' ) .'</textarea></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td class="titulo">Filtro Tipo:</td>';
					echo '<td><textarea id="campo_filtro_tipo" class="form-control" '.$placeholder.'>'. ( isset($r[8]) ? $r[8] : '' ) .'</textarea></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td class="titulo">Filtro Subtipo:</td>';
					echo '<td><textarea id="campo_filtro_subtipo" class="form-control" '.$placeholder.'>'. ( isset($r[9]) ? $r[9] : '' ) .'</textarea></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td class="titulo">Filtro Color:</td>';
					echo '<td><textarea id="campo_filtro_color" class="form-control" '.$placeholder.'>'. ( isset($r[10]) ? $r[10] : '' ) .'</textarea></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td class="titulo">Filtro Almacen</td>';
					echo '<td><textarea id="campo_filtro_almacen" class="form-control" '.$placeholder.'>'. ( isset($r[11]) ? $r[11] : '' ) .'</textarea></td>';
				echo '</tr>';
				echo '<tr>';
					echo '<td class="titulo">Filtro es Externo</td>';
					echo '<td><textarea id="campo_filtro_es_externo" class="form-control" '.$placeholder.'>'. ( isset($r[12]) ? $r[12] : '' ) .'</textarea></td>';
				echo '</tr>';
			/*botones*/
				echo '<tr>';
					echo '<td colspan="2" align="center">';
						echo '<table>';
							echo '<tr>';
									echo '<td><button class="btn btn-success" type="button" onclick="guarda();">Guardar</button></td>';
									echo '<td><button class="btn btn-warning" type="button" onclick="guarda(0);">Guardar Nuevo</button></td>';
									echo '<td><button class="btn btn-danger" type="button">Cancelar</button></td>';
							echo '</tr>';
						echo '</table>';
					echo '</td>';
				echo '</tr>';
		echo '</table>';
	echo '</form>';
?>
<?php
	include('../include/db.php');
	$db = new db();
	$link = $db->conectDB();
//recibimos las variables
	$fl = $_POST['flag'];
	$perfil_id = $_POST['id_registro_perfil'];
	//die('prfil: '.$perfil_id);

//actualizar
	if ( $fl == 2 ){
		$sql = "DELETE FROM permisos_perfil WHERE id_perfil = '{$perfil_id}'";
		$eje = $link->query( $sql ) or die( "Error al eliminar los permisos actuales para sobreescribirlos : {$sql}" );
		$permisos = explode( '|', $_POST['values'] );
		foreach ( $permisos as $key => $permiso ) {
			$perm = explode( '~', $permiso );
			if ( $perm[1] != 0 || $perm[2] != 0 || $perm[3] != 0 ) {
				$sql = "INSERT INTO permisos_perfil ( id_menu, id_perfil, ver, modificar, eliminar, activo )
						VALUES ( '{$perm[0]}', '{$perfil_id}', '{$perm[1]}', '{$perm[2]}', '{$perm[3]}', 1)";
		//die('here : ' . $sql);
				$eje_perm = $link->query( $sql ) or die( "Error al insertar permisos : {$sql}" );
				//echo $sql . ' - ';
			}
		}
		die('ok|Los permisos fueron actualizados exitosamente!!!');
	}
//echo ($sql);*/
	if($fl==4){
		$sql="SELECT 
				/*0*/m.id_menu,
				/*1*/{$perfil_id},
				/*2*/m.display,
				/*3*/IF( p.ver IS NULL, 0, p.ver ),
				/*4*/IF( p.modificar IS NULL, 0, p.modificar ),
				/*5*/IF( p.eliminar IS NULL, 0, p.eliminar )
			FROM menus m
			LEFT JOIN permisos_perfil p ON p.id_menu = m.id_menu
			AND p.id_perfil = '{$perfil_id}'
			WHERE 1";
//die($sql);
	}
//ejecutamos la consulta
	$eje = $link->query( $sql )or die( "Error al ejecutar consulta : {$sql}" );
//regresamos datos
	if($fl==4){
		echo 'ok|<table width="98%" border="1" style="color:black;" id="listPermisos">';
		$c = 0;//declaramos el contador en ceros
		while( $r = $eje->fetch() ){
		$c++;//incrementamos el contador
		//creamos permisos
			$ver="";
			if($r[3]==1){$ver="checked";}
			$modificar="";
			if($r[4]==1){$modificar="checked";}
			$eliminar="";
			if($r[5]==1){$eliminar="checked";}
		//formamos fila
			echo '<tr>';
				echo '<td style="display:none;" id="1_'.$c.'">'.$r[0].'</td>';
				echo '<td style="display:none;" id="2_'.$c.'">'.$r[1].'</td>';
				echo '<td width="40%">'.$r[2].'</td>';
				echo '<td width="20%" align="center"><input type="checkbox" id="3_'.$c.'" '.$ver.'></td>';
				echo '<td width="20%" align="center"><input type="checkbox" id="4_'.$c.'" '.$modificar.'></td>';
				echo '<td width="20%" align="center"><input type="checkbox" id="5_'.$c.'" '.$eliminar.'></td>';
			echo '</tr>';
		}//fin de while

		die('</table>|'.$c);//fin de tabla <input type="hidden" id="filas_totales" value="'.$c.'">
	}


?>
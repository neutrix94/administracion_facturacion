<?php
	//session_start();
	$_SESSION['current_view'] = $_POST['action'];
	include("db.php");
	$db = new db();
	$link = $db->conectDB();
	$sql="SELECT id_razon_social,nombre,link,color FROM razones_sociales WHERE activo=1 ORDER BY orden ASC";
	$eje=$link->query($sql)or die("Error al consultar sistemas de facturación : {$sql}");
	echo '<br><br><br><table style="border:1px solid;" width="80%"><tr>';
	$ancho=(100/($eje->rowCount()));
	
	$c=0;//declaramos contador en cero
	echo "<div class=\"row\">";
	while($r=$eje->fetch()){
		//echo '<td id="opc_mnu_rs_'.$c.'" align="center" width="'.$ancho.'" height="450px" style="background:'.$r[3].';" class="sld"';
		//echo 'onclick="carga_link(\''.$r[2].'\');" onmouseover="resalta('.$c.');" onmouseout="regresa_col('.$c.',\''.$r[3].'\');">';
		//	echo '<img src="img/usr.png" width="150px"><br>';
		//	echo '<b class="opc_rs">'.$r[1].'</b>';
		//echo '</td>'; 
		echo "<div class=\"col-lg-3\" style=\"padding : 5px;\"  onclick=\"carga_link('{$r[2]}');\">
			<div style=\"background:{$r[3]};\" class=\"text-center text-light\" height=\"450px\">
				<img src=\"img/usr.png\" width=\"150px\">
				<br>
				<b class=\"\">{$r[1]}</b>
			</div>
		</div>";
		$c++;//incrementamos contador
	}
	//echo '</tr></table>';
	echo '</div>'
?>
<style type="text/css">
	.opc_rs{font-size: 30px;}
	.sld{padding: 0;border-radius: 15px;}
	.sld:hover{background: gray;padding: 0px 50px 0px 50px;color:black;border:5px solid white;position:relative;}
</style>

<script type="text/javascript">
	function carga_link(link){
		location.href="http://"+link;
	}
	function resalta(num){
		$("#opc_mnu_rs_"+num).css("background","rgba(0,0,0,.7)");
	}
	function regresa_col(num,color){
		$("#opc_mnu_rs_"+num).css("background",color);
	}
</script>
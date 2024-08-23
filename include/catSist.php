<?php
	session_start();
	$_SESSION['current_view'] = $_POST['action'];
	include("conexion.php");
	$sql="SELECT id_razon_social,nombre,link,color FROM razones_sociales WHERE activo=1 ORDER BY orden ASC";
	$eje=mysql_query($sql)or die("Error al consultar sistemas de facturación!!!\n\n".mysql_error());
	echo '<br><br><br><table style="border:1px solid;" width="80%"><tr>';
	$ancho=(100/mysql_num_rows($eje));
	/*$color=array(
				'0' =>'#1E90FF',
				'1'=>'#DAA520',
				'2'=>'#B22222',
				'3'=>'#6B8E23'
				);*/
	$c=0;//declaramos contador en cero
	while($r=mysql_fetch_row($eje)){
		echo '<td id="opc_mnu_rs_'.$c.'" align="center" width="'.$ancho.'" height="450px" style="background:'.$r[3].';" class="sld"';
		echo 'onclick="carga_link(\''.$r[2].'\');" onmouseover="resalta('.$c.');" onmouseout="regresa_col('.$c.',\''.$r[3].'\');">';
			echo '<img src="img/usr.png" width="150px"><br>';
			echo '<b class="opc_rs">'.$r[1].'</b>';
		echo '</td>'; 
		$c++;//incrementamos contador
	}
	echo '</tr></table>';
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
<?php
if( $log > 0  ){// &&isset($log)
    $sql="SELECT 
        mnu.id_menu,
        mnu.display 
    FROM menus mnu 
    LEFT JOIN permisos_perfil pp ON mnu.id_menu=pp.id_menu
    LEFT JOIN usuarios u ON pp.id_perfil=u.id_perfil
    WHERE mnu.es_principal=1 
    AND mnu.activo=1 
    AND u.id_usuario=$log
    ORDER BY orden";
    $eje_mnu = $link->query($sql) or die( "Error al consultar menus principales : {$sql}" );
?>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <!--a class="navbar-brand" href="#">Navbar</a-->
  <a  class="navbar-brand"href="./"><img src="img/logocasadelasluces-easy.png" width="20%"><span class="titulo"></span></a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item active">
        <a class="nav-link" href="javascript:carga_pantalla('catSist');">Sistemas Facturación <span class="sr-only">(RS's)</span></a>
      </li>
      <!--li class="nav-item">
        <a class="nav-link" href="#">Link</a>
      </li-->
<?php
    while($mnu_princ = $eje_mnu->fetch()){//var_dump( $mnu_princ );
        echo "<li class=\"nav-item dropdown\">
        <a class=\"nav-link dropdown-toggle\" href=\"#\" role=\"button\" data-toggle=\"dropdown\" aria-expanded=\"false\">
          {$mnu_princ['display']}
        </a>
        <div class=\"dropdown-menu\">";
        $sq_sub="SELECT display,enlace FROM menus WHERE menu_principal='{$mnu_princ['id_menu']}' AND es_principal=0";
        $eje_sbnu = $link->query($sq_sub)or die("Error al consultar submenus : {$sql}");
        while($reg_sbnu = $eje_sbnu->fetch() ){
           // echo '<br><a href="javascript:carga_pantalla(\''.$reg_sbnu['enlace'].'\');" class="opc_submnu">'.$reg_sbnu['display'].'</a>';
            echo "<div class=\"dropdown-divider\"></div>
                <a class=\"dropdown-item\" href=\"javascript:carga_pantalla( '{$reg_sbnu['enlace']}' );\">{$reg_sbnu['display']}</a>
                ";
        }
          //"<a class=\"dropdown-item\" href=\"#\">Action</a>
        //  <div class=\"dropdown-divider\"></div>
        //  <a class=\"dropdown-item\" href=\"#\">Another action</a>
        //  <div class=\"dropdown-divider\"></div>
        //  <a class=\"dropdown-item\" href=\"#\">Something else here</a>";
        echo "</div>
      </li>";
    }
?>
      <!--li class="nav-item">
        <a class="nav-link disabled">Disabled</a>
      </li-->
    </ul>
    <form class="form-inline my-2 my-lg-0">
      <!--input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search"-->
      <!--button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button-->
      <button class="btn btn-danger my-2 my-sm-0" onclick="logout();">Cerrar Sesión</button>
    </form>
  </div>
</nav>

<?php
}
/*if($log>0){//menú
		//$log = 1;
		
		//$eje_mnu=mysql_query($sql)or die("Error al consultar las cabeceras de menus!!!\n\n".mysql_error());
	//tabla
		echo '<table><tr>'; 
		while($mnu_princ = $eje_mnu->fetch()){//var_dump( $mnu_princ );
			$sq_sub="SELECT display,enlace FROM menus WHERE menu_principal='{$mnu_princ['id_menu']}' AND es_principal=0";
			$eje_sbnu = $link->query($sq_sub)or die("Error al consultar submenus : {$sql}");

			echo '<td class="opc" width="20%" onmouseover="muestra('.$mnu_princ['id_menu'].');" onmouseout="oculta('.$mnu_princ['id_menu'].');">'. $mnu_princ['display'];
				echo '<br><div class="subemnu bg-primary" id="sbmnu_'.$mnu_princ['id_menu'].'">';
				while($reg_sbnu = $eje_sbnu->fetch() ){
					echo '<br><a href="javascript:carga_pantalla(\''.$reg_sbnu['enlace'].'\');" class="opc_submnu">'.$reg_sbnu['display'].'</a>';
				}
				echo '<br>';
				echo '</div>';
			echo '</td>';
		}//fin de while
	?>*/

?>
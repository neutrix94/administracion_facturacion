<?php
if( $log > 0 ){// &&isset($log)
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
  <a  class="navbar-brand"href="./"><img src="img/logocasadelasluces-easy.png" width="20%"><span class="titulo"></span></a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item active">
        <a class="nav-link" href="javascript:carga_pantalla('catSist');">Sistemas Facturación <span class="sr-only">(RS's)</span></a>
      </li>
<?php
    while($mnu_princ = $eje_mnu->fetch()){
        echo "<li class=\"nav-item dropdown\">
        <a class=\"nav-link dropdown-toggle\" href=\"#\" role=\"button\" data-toggle=\"dropdown\" aria-expanded=\"false\">
          {$mnu_princ['display']}
        </a>
        <div class=\"dropdown-menu\">";
        $sq_sub="SELECT display,enlace FROM menus WHERE menu_principal='{$mnu_princ['id_menu']}' AND es_principal=0";
        $eje_sbnu = $link->query($sq_sub)or die("Error al consultar submenus : {$sql}");
        while($reg_sbnu = $eje_sbnu->fetch() ){
            echo "<div class=\"dropdown-divider\"></div>
                <a class=\"dropdown-item\" href=\"javascript:carga_pantalla( '{$reg_sbnu['enlace']}' );\">{$reg_sbnu['display']}</a>";
        }
        echo "</div>
      </li>";
    }
?>
    </ul>
    <form class="form-inline my-2 my-lg-0">
      <button class="btn btn-light my-2 my-sm-0" onclick="logout();">
        <i class="icon-cancel-circled"></i>
      </button>
    </form>
  </div>
</nav>

<?php
}else{
  $_SESSION['current_view'] = '';
  session_destroy();
  $log = '';
}
?>
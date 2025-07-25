<?php
	session_start();
	$_SESSION['current_view'] = $_POST['action'];
	//include('conexion.php');
	include('./db.php');
	$db = new db();
	$link = $db->conectDB();
	include('../ajax/prefixesDB.php');
	$SetsPrefixesDB = new SetsPrefixesDB( $link );
//consulta el numero de registros entre el numero de pagina
    $sql = "SELECT
                COUNT(*) AS pages_limit
            FROM prefijos_sets
            WHERE id_prefijo_set > 0";
	$eje=$link->query( $sql )or die("Error consultar numeros de prefijos de sets : {$sql}");
    $row = $eje->fetch( PDO::FETCH_ASSOC );
    $pages_limit = ROUND( $row['pages_limit'] / 20 );

	$setsPrefixes = $SetsPrefixesDB->getSetsPrefixes();
?>
	<div style="width:90%;heigth:450px;">
		<br>
        <div class="row">
            <div class="col-6">
                <b>
                    <p class="subtitulo" align="left" style="position : sticky; top: 0; background-color : white;">
                        Administración de Prefijos de SETS 2025
                        <button
                            type="button"
                            class="btn btn-primary icon-plus"
                            onclick="show_set_prefixes_form();"
                        >

                        </button>
                    </p>
                </b>
            </div>
            <div class="col-6 input-group">
                <input type="text" class="form-control" placeholder="Buscar por Nombre" onkeyup="prefixesSeeker(event);" id="prefixes_seeker">
                <button
                    type="button"
                    class="btn btn-primary"
					onclick="prefixesSeeker(event);"
                >
                    <i class="icon-search"></i>
                </button>
            </div>
        </div>
		<div style="max-height:500px; overflow : auto;">
			<table width="100%" class="table table-striped">
				<thead class="bg-primary text-light" style="position : sticky; top: 0;">
					<tr>
						<th width="20%" class="text-center">Id</th>
						<th width="20%" class="text-center">Nombre</th>
						<th width="15%" class="text-center">Habilitado</th>
						<th width="15%" class="text-center">Alta</th>
						<th width="15%" class="text-center" colspan="3">Acciones</th>
					</tr>
				</thead>
				<tbody style="max-height : 200px; overflow:auto;" id="prefixesList">
			<?php
			$c=0;//inicaimos el contador en cero
			foreach ($setsPrefixes as $key => $r) {
				$c++;//incrementamos contador
				echo $SetsPrefixesDB->build_row_ceil( $r, $c );
			}
			?>
				</tbody>
			</table>
	</div>
    
    <table class="table">
        <tfoot>
            <tr>
                <th class="text-center">
                    <button
                        class="btn btn-primary"
                    >
                        <i><-</i>
                    </button>
                </th>
                <th class="text-center">
                    Pagina <b id="current_page">1</b> de <b id="pages_limit"><?php echo $pages_limit;?></b>
                </th>
                <th class="text-center">
                    <button
                        class="btn btn-primary"
                    >
                        <i>-></i>
                    </button>
                </th>
            </tr>
        </tfoot>
    </table>

	<div class="form_emergente" id="emergente_RS" style="display:none;">
		<div style="position:absolute;top:10%;width:80%;left:10%;">
			<button class="cierra_emergente" onclick="cierra_emergente('emergente_RS');">X</button>
		</div>
        <div class=""></div>
	</div>

<script>
    var id_rg,nombre,ruta,nom_db,rfc,ruta_link,orden,pss_db,host,user_db,nom_db,estado,obs;
   
    function prefixesSeeker(e){
        if(e.keyCode != 13 && e != 'intro'){
            return false;
        }
        var txt = $('#customer_seeker').val();
        /*if(txt.length <= 0 ){
            alert("El buscador no puede ir vacio.");
            return false;
        }else{//envia peticion a la busqueda*/
            var url = `ajax/prefixesDB.php?fl=getSpecificSetPrefix&text=${txt}`;
            var resp = ajaxR(url);
            $('#customersList').empty();
            $('#customersList').html(resp);
        //}
    }

    function show_set_prefixes_form(id = null, type){
        var url = ``;
        if(id == null){
            url = `./include/forms/setsPrefixesForm.php?`;
        }else{
            url = `ajax/prefixesDB.php?fl=getSetPrefix&id=${id}&type=${type}`;
        }
        var resp = ajaxR(url);
        $('#contenido_emergente').html(resp);
        $('#emergente').css('display', 'block');
    }

    function save_set_prefix(){
        var set_prefix_id = $('#set_prefix_id').val();
        var set_prefix_name = $('#set_prefix_name').val();
        var set_prefix_status = ($('#set_prefix_status').prop('checked') ? '1' : '0');
        var set_prefix_date = $('#set_prefix_date').val();
        var url = `ajax/prefixesDB.php?fl=getSpecificSetPrefix&id=${set_prefix_id}&set_prefix_name=${set_prefix_name}&set_prefix_status=${set_prefix_status}&set_prefix_date=${set_prefix_date}`;
        var resp = ajaxR(url);
        alert(resp);
    }

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

</script>
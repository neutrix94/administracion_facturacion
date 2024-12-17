<?php
	session_start();
	$_SESSION['current_view'] = $_POST['action'];
	include('./db.php');
	$db = new db();
	$link = $db->conectDB();
//consulta las razones sociales
    $sql = "SELECT id_razon_social, nombre FROM razones_sociales";
    try{
        $stm = $link->query( $sql );
    }catch( PDOException $e ){
        die( "Error al consultar las razones sociales : {$sql} : {$e}" );
    }
    $rs_options = "";
    while( $row = $stm->fetch(PDO::FETCH_ASSOC) ){
        $rs_options .= "<option value=\"{$row['id_razon_social']}\">{$row['nombre']}</option>";
    }
?>

<div id="emergent">
    <div id="emergent_content">
        <h2 class="text-center"><br><br>Cargando ...</h2>
    </div>
</div>

<div class="row" style="width:97% !important;">
    <div class="col-4">
        <p>Razon Social :</p>
        <select class="form-control" id="rs_id">
            <option value="-1">Todas</option>
            <?php echo "{$rs_options}";?>
        </select>
    </div>
    <div class="col-4">
        <p>Fecha desde :</p>
        <input type="date" class="form-control" id="date_since">
    </div>
    <div class="col-4">
        <p>Fecha hasta :</p>
        <input type="date" class="form-control" id="date_to">
    </div>
    <br>
</div>
<br><br>
<div class="row" style="width:97% !important;">
    <button
        type="button"
        class="form-control btn btn-success"
        onclick="salesVerification();"
    >
        Comprobar Ventas
    </button>
</div>

<script>
    function salesVerification(){
        var date_since = $( "#date_since" ).val();
        if( date_since == '' ){
            alert("La fecha desde no puede ir vacia.");
            $( "#date_since" ).focus();
            return false;
        }
        var date_to = $( "#date_to" ).val();
        if( date_to == '' ){
            alert("La fecha hasta no puede ir vacia.");
            $( "#date_to" ).focus();
            return false;
        }
        var rs_id = $( "#rs_id" ).val();
	//enviamos datos por ajax
		$.ajax({
			type : 'post',
			url : 'include/ajax/salesVerificationDB.php',
			cache : false,
			data : { date_since : date_since, date_to : date_to, rs_id : rs_id },
		    success:function(dat){
                alert(dat);
			}
		});
        //var resp = ajaxR( url );
    }

</script>

<style>
    #emergent{
        position : fixed;
        top : 0;
        left : 0;
        width: 100%;
        height: 100%;
        background-color: rgba( 0,0,0,.5 );
        z-index:10;
        display : none;
    }

    #emergent_content{
        position : fixed;
        top : 10%;
        left : 10%;
        width: 80%;
        min-height: 30%;
        max-height: 70%;
        background-color: white;
        box-shadow: 1px 1px 10px rgba( 0,0,0,.5 );
    }
</style>
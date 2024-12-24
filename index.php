<?php
	session_start();
	if( !isset($_SESSION['current_view']) ){
		$_SESSION['current_view'] = '';
	}
	//include('include/conexion.php');
	include( 'include/db.php' );
	$db = new db();
	$link = $db->conectDB();
	//var_dump($link);
	if ( isset($_GET['usrlg']) && $_GET['usrlg'] != null ){
		$_SESSION['log'] = base64_decode($_GET['usrlg']);
		echo "<script>location.href=\"./#{$_SESSION['current_view']}\";</script>";
		//die('here_1');
	}
	if ( isset($_GET['logout']) && $_GET['logout'] != null ){
		session_destroy();
		echo "<script>location.href=\"./#{$_SESSION['current_view']}\";</script>";
		//die('here_2');
	}	
	$log = ( isset($_SESSION['log']) ? $_SESSION['log'] : '' );
	//die( $_SESSION['current_view'] );	
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; utf-8">
	<script type="text/javascript" src="js/jquery-1.10.2.min.js"></script><!--JQuery-->
	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.css">
	<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>
	<script type="text/javascript" charset="utf8" src="./js/utils.js"></script>
	<title>Facturación</title>

	<!--link rel="stylesheet" type="text/css" href="css/bootstrap/css/bootstrap.css"-->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
	<!--script type="text/javascript" src="css/bootstrap/js/bootstrap.bundle.min.js"></script-->
	<!--script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script-->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
	
	<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
	<link rel="preconnect" href="https://fonts.googleapis.com">	
	<link href="css/icons/css/fontello.css" rel="stylesheet" type="text/css"  media="all" />
	
	<style type="text/css">
		#global{position: absolute;padding: 0;top:0;left:0;width: 100%;height: 100%; min-width: 80% !important;}/*background-image: url('img/bg8.jpg');*/
		.titulo{font-size: 30px;top:10%;position: absolute;color: black;}
		#mnu{background:#556B2F; width: 97%;left:1%;position: relative;padding: 0px;color: white;height: 38px;}
		.opc{text-decoration: none;padding: 0;height:35px;text-align: center;font-size: 20px;}
		.opc:hover{background: gray;}
		.fct{position: absolute;top:3%;text-decoration: none;border-radius:5px;width:10%;text-align: center;right:2%;color:black;font-size: 20px;}
		.fct:hover{background:rgba(0,0,0,0.5);color: white;}
		.entrada_txt{padding: 8px;}
		.subemnu{width:15%;border:1px solid: white;background:#556B2F;position: fixed;top:150;z-index: 3;display: none;}
		.opc_submnu{text-decoration: none;color:white;font-size: 18px;width: 100%;width: 100%;}
		.opc_submnu:hover{background: green;}
		#cont_carga{position: absolute;z-index:2;width:100%;left : 5%;}
		.subtitulo{font-size: 25px;color: black;padding: 0;}
		.form_emergente{position: fixed;z-index:4;width: 100%;height: 100%;background: rgba(0,0,0,.8);top:0;left: 0;}
		.cierra_emergente{position: absolute;top:0px;right:-10%;color: white;background: rgba(225,0,0,0.7);padding: 15px;font-size: 20px;}
		.btn_med{padding:8px;}
		.bot_nvo{position: absolute;top:15px;background: transparent;border-radius: 5px;}
		input[type=checkbox]{-ms-transform: scale(2); /* IE */
			-moz-transform: scale(2); /* FF */
			-webkit-transform: scale(2); /* Safari and Chrome */
			-o-transform: scale(2); /* Opera */
			padding: 10px;
		}
		/*.table td{
			background-color: white;
		}*/
		.emergente{
			width: 100%;
			height: 100%;
			display: block;
			position: fixed;
			background-color: rgba( 0,0,0, .6);
			top : 0;
			left: 0;
			z-index: 100;
		}
		
	</style>
</head>
<body>
    <div id="alert">
        <div id="alert_content"></div>
    </div>
	
	<div id="emergente">
		<div id="contenido_emergente">
		</div>
	</div>

	<div id="global">
	<!--Menu-->
		<div>
			<?php
				include( './views/menu.php' );
			?>
		</div>
		<?php
			//}
			echo '<div id="cont_carga">';
			if($log == ''){//login
				include('include/login.php');
			}
			echo '</div>';
		?>
	</div>

<!-- Button trigger modal 
	<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal" style="display : none;"
	id="btn_trigger_modal" id="btn_modal_trigger" >
	  Launch demo modal
	</button>
	<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	  	<div class="modal-dialog">
	    	<div class="modal-content">
	     		<div class="modal-header">
	        		<h5 class="modal-title" id="globalModalLabel"></h5>
	        		<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
	      		</div>
		      	<div class="global-modal-body">
		      		<img src="">
			    </div>
		      	<div class="modal-footer">
			        <button type="button" id="global_modal_close" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
			        <button type="button" id="global_modal_save" class="btn btn-primary">Guardar</button>
		      	</div>
	    	</div>
	  	</div>
	</div>
	<div class="emergente">
		<div class="contenido_emergente"></div>
	</div>-->
</body>
</html>
<style>
	#alert{
        position : fixed;
        top : 0;
        height: 100%;
        left : 0;
        width: 100%;
        background : rgba( 0, 0, 0, .5 );
        z-index: 100;
        display : none;
    }
    #alert_content{
        position: relative;
        width : 95%;
        left : 2.5%;
        min-height: 30%;
        max-height: 80%;
		overflow: auto;
        top : 10%;
        background : white;
        box-shadow: 3px 3px 15px rgba( 0, 0, 0, .5 );
        padding: 20px;
    }
	#emergente{
		position: fixed;
		z-index: 200;
		width: 100%;
		height: 100%;
		top:0;left:0; 
		background: rgba(0,0,0,.4);
		display: none;
	}
	#contenido_emergente{
		position: absolute;
		width: 95%;
		left:2.5%;
		/*border:2px solid white;*/
		top:10%;
		height:80%;
		/*border-radius: 2%;*/
		overflow:auto;
		background : white;
		/*background: rgba(0,0,0,.5);*/
		z-index: 200;
		box-shadow: 4px 4px 15px rgba( 0,0,0,.5 );
	}
</style>
<script type="text/javascript">

	function carga_pantalla ( flag = null ) {
		$( '.emergente' ).css( 'display', 'block' );
		$( ".contenido_emergente" ).html( '<p align="center" style="font-size : 30px; color : white; position : absolute; top : 30%; width : 100%;">Cargando ...</p>' 
			+ '<p align="center" style="font-size : 30px; color : white; position : absolute; top : 40%; width : 100%;">'
			+ '<img src="https://img1.picmix.com/output/stamp/normal/8/5/2/9/509258_fb107.gif"></p>' );
		$.ajax({
			type : 'post',
			data : { action : (flag != null ? flag : "<?php echo $_SESSION['current_view']; ?>" )},
			url : "include/" + (flag != null ? flag : "<?php echo $_SESSION['current_view']; ?>" ) + ".php",
			cache : false,
			success : function ( dat ) {
				$( "#cont_carga" ).html( dat );
				setTimeout( function () { $( '.emergente' ).css( 'display', 'none' ); }, '1000');
		
			}
		});
		//
	}
	function ajaxR(url){
		if(window.ActiveXObject){       
			var httpObj = new ActiveXObject("Microsoft.XMLHTTP");
		}
		else if (window.XMLHttpRequest)
		{       
			var httpObj = new XMLHttpRequest(); 
		}
		httpObj.open("POST", url , false, "", "");
		httpObj.send(null);
		return httpObj.responseText;
	}        
</script>

<?php
//carga pantalla
	if ( isset( $_SESSION['current_view'] ) && $_SESSION['current_view'] != '' && $_SESSION['current_view'] != null ){
		echo '<script>carga_pantalla();</script>';
		echo '<script>$( \'.emergente\' ).css( \'display\', \'none\' );</script>';
	}else{
		echo '<script>$( \'.emergente\' ).css( \'display\', \'none\' );</script>';
	}
?>
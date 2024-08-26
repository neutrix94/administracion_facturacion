<i?php
	//session_start();
	//$_SESSION['current_view'] = $_POST['action'];
?>
<style type="text/css">
	#form_log{width:30%;left:0;position:relative;top:70%;border:3px;border-radius:15px;padding: 10px;font-size: 20px;border:3px solid gray;}
</style>
<br><br><br><br><br><br>
<div id="form_log" class="bg-primary"><br><br>
	<b class="text-light">Inicio de sesión</b><br><br>
	<div>
		<input type="text" id="user" class="form-control" placeholder="Usuario" tabindex="1">
	</div>
	<br>
	<div class="input-group">
		<input type="password" id="pss"  class="form-control" placeholder="** Password **" tabindex="2">
		<button 
			type="button"
			class="btn btn-warning"
			onclick="show_password(this);" 
		>
			<i class="icon-eye-1" id="icon_pass"></i>
		</button>
	</div>
	<br>
	<div class="text-center">
		<button onclick="valida_log();" tabindex="3" class="btn btn-success">
			Acceder
		</button>
	</div>
	<br>
</div>

<script type="text/javascript">
	function show_password ( obj ) {
		var field_type = $( '#pss' ).attr('type');
		$( '#pss' ).attr( 'type', ( field_type == 'password' ? 'text' : 'password' ) );
		$( '#icon_pass' ).attr( 'class',( field_type == 'password' ? 'icon-eye-off' : 'icon-eye-1' ) );
	}
	function valida_log(){
	//sacamos el valor de los input 
		var usuario,pass;
		usuario=$("#user").val();
		if( usuario.length <= 0 ){
			alert("El usuario no puede ir vacío");
			$("#user").focus();
			return false;
		}

		pass=$("#pss").val();
		if(pass.length<=0){
			alert("La contraseña no puede ir vacía");
			$("#pss").focus();
			return false;
		}
	//enviamos datos por ajax
		$.ajax({
			type:'post',
			url:'ajax/valida_log.php',
			cache:false,
			data:{u:usuario,p:pass},
			success:function(dat){
				var ax=dat.split("|");
				if(ax[0]!='ok'){
					alert("Error!!!\n"+dat);
				}else{
					location.href='index.php?usrlg=MQ==';//MA==
				}
			}
		});
	}
</script>

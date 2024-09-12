
	function muestra ( num ) {
		$("#sbmnu_"+num).css("display", "block");
	}

	function oculta ( num ) {
		$("#sbmnu_"+num).css("display", "none");
	}
	function cierra_emergente ( obj ) {
		if(!confirm("Desea salir sin guardar cambios???")){
			return true;
		}
		$("#"+obj).css("display", "none");
	}
	function logout(){
		location.href= "index.php?logout=1";
	}

	function close_alert(){
		$( '#alert_content' ).html( '' );
		$( '#alert' ).css( 'display', 'none' );
	}
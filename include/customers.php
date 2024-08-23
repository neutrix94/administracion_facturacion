<?php
	session_start();
	$_SESSION['current_view'] = $_POST['action'];
?>
<style type="text/css">
	.container_list{
		height: 400px !important;
		max-height: 400px !important;
		overflow: auto;
	}
	.form-control{
		margin-top : 5px;
	}
	.required{
		color  :red;
	}
	.table_header{
		position : sticky;
		top : 0;
		text-align : center;
		color : white;
		background-color : rgba( 225,0,0,.5);
	}
</style>
<?php
	include( 'db.php' );
	$sql = "SELECT
				c.id_cliente,
				c.nombre,
				c.telefono,
				c.movil,
				c.email,
                tp.TipoPersona as idTipoPersona,
				CASE
					WHEN c.EntregaConsSitFiscal = 1 THEN 'Sí'
					ELSE 'No'
				END EntregaConsSitFiscal,
				c.UltimaActualizacion
			FROM clientes c
            left join cat_Tipo_Persona tp on tp.idTipoPersona = c.idTipoPersona
			WHERE c.id_cliente > 0";
	//var_dump( $conexion->execQuery( $sql, "", "SELECT" ) );
	echo '<br><div class="container_list">' . build_list( $conexion->execQuery( $sql, "", "SELECT" ) ) . '</div>';
	echo '<br><div class="row">'
			. '<div class="col-8"></div>'
			. '<div class="col-2"><button type="button" class="btn btn-info" onclick="save_customer(5,null)">Centralizar Clientes</button></div>'
			. '<div class="col-2"><button class="btn btn-warning" onclick="show_form( 0 )">Agregar Nuevo</button></div>'
		. '</div>';

//	echo '<div class="row" style="padding : 20px;"></div>';
	/*echo '<script>$(document).ready( function () {
    				$("#rows_list").DataTable();
				} );</script>';*/
	function build_list( $data ){
		$resp = '<table class="table table-striped" id="rows_list">';
			$resp .= '<thead class="table_header">';
				$resp .= '<tr>';
					$resp .= '<th>RFC</th>';
					$resp .= '<th>TELÉFONO</th>';
					$resp .= '<th>CELULAR</th>';
					$resp .= '<th>CORREO</th>';
					$resp .= '<th>TIPO PERSONA</th>';
					$resp .= '<th>ENTREGÓ CSF</th>';
					$resp .= '<th>ACTUALIZADO</th>';
					$resp .= '<th>ACCIONES</th>';
				$resp .= '</tr>';
			$resp .= '</thead>';
		$resp .= '<tbody>';
			$resp .= content_table( $data );
		$resp .= '</tbody>';
		$resp .= '</table>';
		return $resp;
	}

	function content_table( $data ){
		$resp = '';
		$primary = 0;
		foreach ($data as $key => $row) {
			$resp .= '<tr>';
			$c = 0;
			foreach ($row as $key2 => $value) {
				if( $c == 0 ){
					$primary = $value;
				}
				$resp .= '<td' . ( $c == 0 ? ' style="display : none;" ' : '' ) . '>' . $value . '</td>';
				$c ++;
			}
			$resp .= '<td align="center">';
				$resp .= '<button type="button" class="btn btn-info" onclick="show_form(1, ' . $primary . ')"><i class="icon-eye-1"></i></button>';
				$resp .= '<button type="button" class="btn btn-warning" onclick="show_form(2, ' . $primary . ')"><i class="icon-pencil-neg"></i></button>';
				$resp .= '<button type="button" class="btn btn-danger" onclick="show_form(3, ' . $primary . ')"><i class="icon-trash"></i></button>';
			$resp .= '</td>';
			$resp .= '</tr>';
		}
		return $resp;
	}

?>
<script type="text/javascript">

	var rfc_validado = 1;

	function verifica_rfc( obj ){
		$.ajax({
			type : 'post',
			url : 'ajax/validaRFC.php',
			data : { dato : $( obj ).val().trim(), current_key : $( '#customer_id' ).val() },
			cache : false,
			success : function ( dat ){
				if( dat != 'ok' ){
					$( obj ).css( 'border', '1px solid red' );
					$( '#rfc_validation' ).html( 'x' );
					$( '#rfc_validation' ).css( 'color', 'red' );
					alert( dat );
					$( obj ).select();
					rfc_validado = 0;
				}else{
					$( obj ).css( 'border', '1px solid green' );
					$( '#rfc_validation' ).html( '✓' );
					$( '#rfc_validation' ).css( 'color', 'green' );
					rfc_validado = 1;
				}
			}
		});
	}

	function show_form ( flag, id = null ){
		$.ajax({
			type : 'post',
			url : 'include/customers_form.php',
			data : { fl : flag, row_id : id },
			success : function( dat ){
				$( '#globalModalLabel' ).html( 'Cliente' );
				$( '.global-modal-body' ).html( dat );
				$( '#btn_trigger_modal' ).click();
				if ( flag != 1 ) {
					$( '#global_modal_save' ).attr( 'onclick', 'save_customer(' + flag + ',' + id + ')' );
				}
				$( '#rfc' ).focus();
			}
		});
	}

	function save_customer ( flag, id = null ){
		if( rfc_validado == 0 ){
			alert( "El rfc es incorrecto o ya existe, verifique y vuelva a intentar!" );
			$( '#rfc' ).select();
			return false;
		}
		$( '.emergente' ).css( 'display', 'block' );
		$( ".contenido_emergente" ).html( '<p align="center" style="font-size : 50px; color : white; position : absolute; top : 30%; width : 100%;">Guardando ...</p>'
			+ '<p align="center" style="font-size : 30px; color : white; position : absolute; top : 40%; width : 100%;">'
			+ '<img src="https://img1.picmix.com/output/stamp/normal/8/5/2/9/509258_fb107.gif"></p>' );
		if ( flag != 5  ) {
			var values = get_values();
			if ( values == false ){
				return false;
			}
		}
	//envia datos por ajax
		$.ajax({
			type : 'post',
			url : 'include/customersDB.php',
			cache : false,
			data : { fl : flag, data : values, primary_key : id },
			success : function ( dat ){
				dats = dat.split( '|' );
				alert( dat );
				rfc_validado = 0;
				$( '.emergente' ).css( 'display', 'none' );
				location.reload();
			}
		});
	}

	function get_values (  ){
		var arr = '';
	//cliente
		arr += $( '#customer_id' ).val() + '~';
	//rfc
		if ( $( '#rfc' ).val().trim().length <= 0 ) {
			alert( "El rfc no puede ir vacio!" );
			$( '#rfc' ).focus();
			return false;
		}else{
			arr += $( '#rfc' ).val() + '~';
		}
	//telefono
		if ( $( '#tel_1' ).val().trim().length <= 0 ) {
			alert( "El telefono no puede ir vacio!" );
			$( '#tel_1' ).focus();
			return false;
		}else{
			arr += $( '#tel_1' ).val() + '~';
		}
	//celular
		if ( $( '#celular' ).val().trim().length <= 0 ) {
			alert( "El celular no puede ir vacio!" );
			$( '#celular' ).focus();
			return false;
		}else{
			arr += $( '#celular' ).val() + '~';
		}
	//correo
		if ( $( '#correo' ).val().trim().length <= 0 ) {
			alert( "El correo no puede ir vacio!" );
			$( '#correo' ).focus();
			return false;
		}else{
			arr += $( '#correo' ).val() + '~';
		}
	//datos fiscales
		arr += $( '#id_razon_social' ).val() + '~';
		arr += $( '#razon_social' ).val() + '~';
		arr += $( '#calle' ).val() + '~';
		arr += $( '#no_interior' ).val() + '~';
		arr += $( '#no_exterior' ).val() + '~';
		arr += $( '#colonia' ).val() + '~';
		arr += $( '#delegacion' ).val() + '~';
		arr += $( '#c_p' ).val() + '~';
		arr += $( '#localidad' ).val() + '~';
		arr += $( '#estado' ).val() + '~';
		arr += $( '#pais' ).val() + '~';
		//Nuevos campos de control
		arr += $( '#tipoPersona' ).val() + '~';
		let current = new Date();
		let cDate = current.getFullYear() + '-' + (current.getMonth() + 1) + '-' + current.getDate();
		let cTime = current.getHours() + ":" + current.getMinutes() + ":" + current.getSeconds();
		const csfVal = document.querySelector('#csf');
		var csf = (csfVal.checked) ? 1 : 0;
		arr += csf + '~';
		arr += cDate + ' ' + cTime + '~';
		console.log(arr);
		return arr;
	}
</script>

<?php
	include( 'db.php' );
	$primary_key = $_POST['row_id'];
	$tipo = $_POST['fl'];
	$sql = "SELECT
				c.id_cliente,
				c.nombre,
				c.telefono,
				c.movil,
				c.email,
				crs.id_cliente_rs,
				crs.razon_social,
				crs.calle,
				crs.no_int,
				crs.no_ext,
				crs.colonia,
				crs.del_municipio,
				crs.cp,
				crs.localidad,
				crs.estado,
				crs.pais,
				c.idTipoPersona,
				c.EntregaConsSitFiscal,
				c.UltimaActualizacion
			FROM clientes c
			LEFT JOIN clientes_razones_sociales crs ON crs.id_cliente = c.id_cliente
			WHERE c.id_cliente = '{$primary_key}'";
	$customer = $conexion->execQuery( $sql, "", "SELECT" );

	$sqlRF = "SELECT * from cat_Tipo_Persona;";
	$rfValues = $conexion->execQuery( $sqlRF, "", "SELECT" );
?>

<div class="row form">
	<div class="col-1">
	</div>
	<div class="col-10">
		<form>
			<input type="hidden" id="customer_id" value="<?php  echo $customer[0]['id_cliente']; ?>" class="form-control"/>
			<div class="row">
				<div class="col-3">
					<label for="rfc">RFC <span class="required" id="rfc_validation">*</span></label>
				</div>
				<div class="col-9">
					<input type="text" id="rfc" class="form-control" value="<?php  echo $customer[0]['nombre']; ?>"
						onchange="verifica_rfc( this );" maxlength="13"
						required/>
				</div>
			</div>
			<!--Fila 2: Tipo de persona & Entregó Comprobante de situación fiscal-->
			<div class="row">
				<div class="col-3">
					<label for="tipoPersona">TIPO PERSONA<span class="required">*</span></label>
				</div>
				<div class="col-4">
					<!-- <input type="number" id="tipoPersona" class="form-control"
					value="<?php  echo $customer[0]['idTipoPersona']; ?>" maxlength="10"
					required/> -->
					<select name="tipoPersonaName" id="tipoPersona" class="form-control">
						<?php
							foreach ($rfValues as $item) {
								if($item['idTipoPersona'] == $customer[0]['idTipoPersona']){
										echo "<option value='" . $item['idTipoPersona'] ."' selected>".$item['TipoPersona']."</option>";
								}else{
										echo "<option value='" . $item['idTipoPersona'] ."'>".$item['TipoPersona']."</option>";
								}
							}
						?>
				  </select>
				</div>
				<div class="col-2">
					<label for="csf">ENTREGÓ CSF</label>
				</div>
				<div class="col-2">
					<!-- <input type="number" id=csf class="form-control"
					maxlength="10" value="<?php  echo $customer[0]['EntregaConsSitFiscal']; ?>"/> -->
					<?php
						if($customer[0]['EntregaConsSitFiscal'] == 1){
								echo "<input type='checkbox' id='csf' value='csf' checked>";
						}else{
								echo "<input type='checkbox' id='csf' value='csf'>";
						}
					?>
				</div>
			</div>
			<div class="row">
				<div class="col-3">
					<label for="tel_1">TELEFONO <span class="required">*</span></label>
				</div>
				<div class="col-4">
					<input type="number" id="tel_1" class="form-control"
					value="<?php  echo $customer[0]['telefono']; ?>" maxlength="10"
					required/>
				</div>

				<div class="col-1">
					<label for="celular">CEL <span class="required">*</span></label>
				</div>
				<div class="col-4">
					<input type="number" id="celular" class="form-control"
					maxlength="10" value="<?php  echo $customer[0]['movil']; ?>"/>
				</div>
			</div>
			<div class="row">
				<div class="col-3">
					<label for="correo">CORREO <span class="required">*</span></label>
				</div>
				<div class="col-9">
					<input type="email" id="correo" class="form-control" value="<?php  echo $customer[0]['email']; ?>" required/>
				</div>
			</div>
			<hr>
			<h3 align="center">Datos Fiscales</h3>
			<hr>
			<input type="hidden" id="id_razon_social">
			<div class="row">
				<div class="col-3">
					<label for="razon_social">RAZON SOCIAL <span class="required">*</span></label>
				</div>
				<div class="col-9">
					<input type="text" id="razon_social" class="form-control" value="<?php  echo $customer[0]['razon_social']; ?>" required>
				</div>
			</div>
			<div class="row">
				<div class="col-3">
					<label for="calle">CALLE <span class="required">*</span></label>
				</div>
				<div class="col-9">
					<input type="text" id="calle" class="form-control" value="<?php  echo $customer[0]['calle']; ?>" required>
				</div>
			</div>

			<div class="row">
				<div class="col-3">
					<label for="no_interior">NO. INTERIOR <span class="required">*</span></label>
				</div>
				<div class="col-4">
					<input type="text" id="no_interior" class="form-control" value="<?php  echo $customer[0]['no_int']; ?>" required>
				</div>
				<div class="col-1">
					<label for="no_exterior">NO. EXT <span class="required">*</span></label>
				</div>
				<div class="col-4">
					<input type="text" id="no_exterior" class="form-control" value="<?php  echo $customer[0]['no_ext']; ?>" required>
				</div>
			</div>

			<div class="row">
				<div class="col-3">
					<label for="colonia">COLONIA <span class="required">*</span></label>
				</div>
				<div class="col-9">
					<input type="text" id="colonia" class="form-control" value="<?php  echo $customer[0]['colonia']; ?>" required>
				</div>
			</div>

			<div class="row">
				<div class="col-3">
					<label for="delegacion">DELEG / MUNIC <span class="required">*</span></label>
				</div>
				<div class="col-9">
					<input type="text" id="delegacion" class="form-control" value="<?php  echo $customer[0]['del_municipio']; ?>" required>
				</div>
			</div>

			<div class="row">
				<div class="col-3">
					<label for="c_p">CÓDIGO POSTAL <span class="required">*</span></label>
				</div>
				<div class="col-4">
					<input type="text" id="c_p" class="form-control" value="<?php  echo $customer[0]['cp']; ?>" required>
				</div>
				<div class="col-2">
					<label for="localidad">LOCALIDAD <span class="required">*</span></label>
				</div>
				<div class="col-3">
					<input type="text" id="localidad" class="form-control" value="<?php  echo $customer[0]['localidad']; ?>" required>
				</div>
			</div>

			<div class="row">
				<div class="col-3">
					<label for="estado">ESTADO <span class="required">*</span></label>
				</div>
				<div class="col-4">
					<input type="text" id="estado" class="form-control" value="<?php  echo $customer[0]['estado']; ?>" required>
				</div>
				<div class="col-1">
					<label for="pais">PAÍS <span class="required">*</span></label>
				</div>
				<div class="col-4">
					<input type="text" id="pais" class="form-control" value="<?php  echo $customer[0]['pais']; ?>" required>
				</div>
			</div>
		</form>
	</div>
	<div class="col-1">
	</div>
</div>

<style type="text/css">
	.form{
		/*border : 1px solid green;*/
		max-width: 100%;
		padding-top: 20px;
		padding-bottom: 50px;
		color: black;
		text-align: left;
		font-family: "Times New Roman", Times, serif;
	}
	.modal-content{
		position: relative;
		width: 200%;
		right: 50% !important;
	}
	label{
		vertical-align: middle;
	}
</style>

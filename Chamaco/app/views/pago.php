<script>
	var fecha = '<?php echo date('d/m/Y h:i a'); ?>';
	app.ini(function(){

	});
</script>

<script type="text/x-kendo-template" id="plantillaListaOrdenes">
	<li class="btn btn-primary" data-id="#= id #">
		<div class="listaProducto">
			Orden N&deg; #= id # #= fecha # <br/>
			<div id="listaUbicacion"><u>Ubicaci&oacute;n:</u> #= ubicacion_venta # / #= seccion_venta # </div>
		</div>
	</li>
</script>

<script type="text/x-kendo-template" id="plantillaLista">
	<li data-precio="#= precio #" data-cantidad="#= cantidad #">
		<div class="listaProducto">#= producto #
			# if (id != 'parallevar') { #
				(Bsf #= precio #)
			# } #
		</div>
		# if (id != 'parallevar') { #
		<div>X</div>
		<div class="cantidad"> #= cantidad # </div>
		# } #
	</li>
</script>

<script type="text/x-kendo-template" id="plantillaBusqueda">
	<table class="table">
		<tr>
			<td style="width: 100px;">Fecha</td>
			<td style="width: 100px;">Estatus</td>
			<td style="width: 100px;">Pago</td>
			<td style="width: 100px;" data-toggle="tooltip" data-placement="top" title="Pendiente por Pagar">PPP</td>
			<td style="width: 100px;">Cedula</td>
			<td style="width: 230px;">Nombre</td>
			<td style="width: 125px;">&nbsp;</td>
		</tr>
		# for(i = 0; i < registros.length; i++){ #
			<tr>
				<td>#= registros[i].fecha #</td>
				<td>#= registros[i].estatusTexto #</td>
				<td>#= registros[i].pago #</td>
				<td>#= registros[i].pendiente #</td>
				<td>#= registros[i].cedula #</td>
				<td>#= registros[i].nombre # #= registros[i].apellido #</td>
				<td>
					<button type="button" class="btn btn-info" data='#= registros[i].data #' data-accion="informacion" title="Informacion"><i class="fa fa-info"></i></button>
					# if (registros[i].estatus != 'a' && registros[i].estatus != 'ee' && registros[i].estatus != 'nc') { #
					<button type="button" class="btn btn-danger" data='#= registros[i].data #' data-accion="anular" title="Anular"><i class="fa fa-close"></i></button>
						# if (registros[i].pendiente != 0) { #
							<button type="button" class="btn btn-success" data='#= registros[i].data #' data-accion="pagar" data-pendiente="#= registros[i].pendiente #" title="Pagar y Entregar"><i class="fa fa-usd "></i></button>
						# }else if (registros[i].estatus != 'ee' && registros[i].estatus != 'pc' && registros[i].estatus != 'p'){ #
							<button type="button" class="btn btn-success" data='#= registros[i].data #' data-accion="entregar" title="Entregar"><i class="fa fa-check"></i></button>
						# } #
					# } #
				</td>
			</tr>
		# } #
	</table>
</script>

<style>
.btn-purple {
	color: #fff;
	background-color: #551A8B;
	border-color: #551A8B;
}
.btn-purple:hover,
.btn-purple:focus,
.btn-purple:active,
.btn-purple.active {
	color: #fff;
	background-color: #481676;
	border-color: #3b1260;
}
.btn-purple.disabled:hover,
.btn-purple.disabled:focus,
.btn-purple.disabled:active,
.btn-purple.disabled.active,
.btn-purple[disabled]:hover,
.btn-purple[disabled]:focus,
.btn-purple[disabled]:active,
.btn-purple[disabled].active,
fieldset[disabled] .btn-purple:hover,
fieldset[disabled] .btn-purple:focus,
fieldset[disabled] .btn-purple:active,
fieldset[disabled] .btn-purple.active {
	color: #fff;
	background-color: #551A8B;
	border-color: #551A8B;
}

	#listadoOrden{
		float: left;
		width: 360px;
		min-height: 400px;
	}

	#listadoOrden ul{
		padding: 10px 0;
		margin: 0;
		overflow: hidden;
		width: 360px;
	}

	#listadoOrden li{
		list-style: none;
		margin-left: 10px;
		overflow: hidden;
		text-align: left;
		margin: 3px 0;
		width: 360px;
	}
	#listadoOrden li div{
		float: left;
		min-height: 36px;
		/*line-height: 2.3em;*/
		font-weight: bold;
		font-size: 17px;
	}

	#listaUbicacion{
		font-size: 14px !important;
		min-height: 20px !important;
	}

	#detalleOrden, #detalleOrdenInfo{
		float: left;
		margin-top: 12px;
		width: 580px;
		display: none;
		margin-left: 25px;

		position: fixed;
		/*right: 213px;*/
		top: 225px;
	}

	#detalleOrden ul, #detalleOrdenInfo ul{
		padding: 10px 0;
		margin: 0;
		overflow: hidden;
		width: 580px;
	}

	#detalleOrden li, #detalleOrdenInfo li{
		list-style: none;
		overflow: hidden;
		text-align: left;
		margin: 3px 10px;
		border-bottom: 1px solid;
	}
	#detalleOrden li div, #detalleOrdenInfo li div{
		float: left;
	}

	#detalleOrden .listaProducto, #detalleOrdenInfo .listaProducto{
		width: 512px;
	}

	#detalleOrden .cantidad, #detalleOrdenInfo .cantidad{
		margin: 0 3px;
	}

	#detalleOrden .total, #detalleOrdenInfo .total{
		font-weight: bold;
		font-size: 30px;
		text-align: right;
		padding: 5px;
	}

	#detalleOrden .btn, #detalleOrdenInfo .btn{
		font-weight: bold;
		font-size: 30px;
		margin-top: 20px;
		margin-left: 10px;
		float: right;
	}

	#detalleOrdenInfo{
		display: block;
		float: none;
		position: relative;
		top: auto;
		width: 100%;
		margin: 0;
	}

	#detalleOrdenInfo ul{
		width: 100%;
	}

	#detalleOrdenInfo .listaProducto{
		width: 800px;
	}

	#dialogoPago .k-button{
		float: left;
		width: 128px;
		height: 128px;
		font-size: 18px;
		font-weight: bold;
		margin: 5px;
	}
	.modal-body .form-control{
		margin: 10px 0;
	}

	#dialogoPago .modal-body .btn-pago{
		width: 160px;
		height: 160px;
		font-size: 26px;
		margin: 5px 10px;
	}

	#correlativo{
		font-size: 30px;
		height: 85px;
		padding: 20px;
		position: absolute;
		right: 10px;
		text-align: center;
		top: 10px;
		width: 100px;
		display: none;
	}

	.campoNotaCredito{
		float: left;
		width: 100px;
		margin-left: 10px !important;
		margin-top: 2px !important;
		margin-bottom: 2px !important;
	}

	#agregarDetalleNotaCredito{
		padding: 3px 8px;
	}

	.eliminarDetalleNotaCredito{
		margin-left: 10px;
	}

	#dialogBusqueda table .btn{
		float: left;
		height: 34px;
		margin-left: 2px;
		width: 34px;
	}

	#listadoReportes{
		padding: 3px;
		float: right;
		margin-right:0 10px 0 10px;
	}

	#listadoReportes .ui-icon{
		float: left;
	}

	#listadoReportes h4 {
		padding: 3px;
		margin: 0 0 5px;
	}
</style>

<div id="correlativo" class="btn btn-info">01</div>

<div class="input-group" style="float: left; margin-left: 10px; width: 200px;">
  <input id="buscar" class="form-control objControl" type="text" placeholder="Buscar Orden" />
  <span class="input-group-btn">
    <button id="btn-buscar" class="btn btn-default" type="button"><i class="fa fa-search"></i></button>
  </span>
</div>

<div id="listadoReportes" class="ui-widget-content ui-corner-all">
	<h4 class="ui-widget ui-state-default ui-corner-all">
        <span class="ui-icon ui-icon-note">Listado de Reportes</span>Listado de Reportes
    </h4>

    <div style="padding: 15px;">
    	<div id="reportex" class="btn btn-warning">Generar Reporte <strong>X</strong></div>
		<div id="cerrarCajas" class="btn btn-purple"><i class="fa fa-close"></i> Cerrar Cajas</div>
    	<div id="reportez" class="btn btn-danger">Generar Reporte <strong>Z</strong></div>
    	<div id="reportezmensual" class="btn btn-danger" data-toggle="modal" data-target="#dialogreportezmensual">Reporte <strong>Z</strong> Mensual</div>
    	<div class="barra" style="height: 10px;"></div>

    	<div id="nota_credito" class="btn btn-primary" data-toggle="modal" data-target="#dialogNotaCredito">Generar Nota de Credito</div>
    	<div id="imprimirUltimaFactura" class="btn btn-primary">Imprimir Ultima Factura</div>
    </div>
</div>

<div class="barra" style="height:20px;"></div>

<div style="width:360px; float: left;">
    <h4 class="ui-widget" style="padding: 3px; margin: 0 0 5px;">
        <span class="ui-icon ui-icon-info" style="float: left;">Ordenes por Procesar</span>&nbsp;Ordenes por Procesar
    </h4>
    <div class="ui-widget ui-state-default ui-corner-all" style="width:360px; height: 5px;"></div>
</div>

<div style="width:575px; float: left; margin-left: 25px;">
    <h4 class="ui-widget" style="padding: 3px; margin: 0 0 5px;">
        <span class="ui-icon ui-icon-contact" style="float: left;">Detalles de la Orden</span>&nbsp;Detalles de la Orden
    </h4>
    <div class="ui-widget ui-state-default ui-corner-all" style="width:575px; height: 5px;"></div>
</div>

<div id="listadoOrden">
	<ul></ul>
</div>

<div id="detalleOrden">
	<ul class="k-button"></ul>

	<div id="procesar" class="btn btn-success" data-toggle="modal" data-target="#dialogoPago" style="margin-right: 20px;"><i class="fa fa-check"></i> Procesar</div>
	<div id="anular" class="btn btn-danger"><i class="fa fa-close"></i> Anular</div>
	<div id="encargo" class="btn btn-warning" data-toggle="modal" data-target="#datosEncargo"><i class="fa fa-truck"></i> Encargo</div>
</div>

<div class="modal" id="dialogoPago" tabindex="-1" role="dialog" aria-labelledby="datosEncargoLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
		<div class="modal-header">
			<h4 class="modal-title" id="datosEncargoLabel">Forma de Pago</h4>
		</div>
		<div class="modal-body">
			<label><input id="facturapersonalizadacheck" type="checkbox" value="si" /> Factura Personalizada.</label>

			<label style="float: right;"><input id="pagodetalladocheck" type="checkbox" value="si" /> Pago Detallado.</label>

			<div class="barra"></div>

			<div id="divDatosCliente" style="display: none;">
				<input id="cedula" name="cedula" class="form-control objControl" type="text" placeholder="Cedula o RIF" required="" style="margin-top: 0;" />
				<input id="nombre" name="nombre" class="form-control objControl" type="text" placeholder="Nombre" required="" />
				<input id="apellido" name="apellido" class="form-control objControl" type="text" placeholder="Apellido" />
				<input id="direccion" name="direccion" class="form-control objControl" type="text" placeholder="Direccion" />
				<input id="telefono" name="telefono" class="form-control objControl" type="text" placeholder="Telefono" />
			</div>

			<div id="divFormaPago">
				<?php
					$ci->db->seleccionar('forma_pagos', '*');
					foreach($ci->db->rs as $forma){
						echo '<button type="button" class="btn btn-pago btn-success" data-valor="' . $forma['id'] . '">' . $forma['forma'] . '</button>';
					}
				?>
			</div>
			<div id="divFormaPagoDetalle" style="display: none;">
				<?php
					$ci->db->seleccionar('forma_pagos', '*');
					foreach($ci->db->rs as $forma){
						echo '
						<div class="input-group">
							<input type="text" class="form-control objControl" data-valor="' . $forma['id'] . '" placeholder="' . $forma['forma'] . '" />
							<span class="input-group-btn"><button class="btn btn-default btn-calcular" type="button">Calcular!</button></span>
						</div>';
					}
				?>
				<div id="divTotalPagarDetalle" style="text-align: right; margin: 15px 0;"></div>
				<div style="text-align: right;">
					<button id="btnPagoDetallado" class="btn btn-success" type="button" disabled="disabled"><i class="fa fa-check"></i> Procesar</button>
				</div>
			</div>
		</div>
		<!--
		<div class="modal-footer">
			<button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
		</div>
		-->
		</div>
	</div>
</div>

<div class="modal" id="datosEncargo" tabindex="-1" role="dialog" aria-labelledby="datosEncargoLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Cerrar</span></button>
				<h4 class="modal-title" id="datosEncargoLabel">Datos del Encargo</h4>
			</div>
			<div class="modal-body">
				<form id="formEncargo">
					<input id="cedula" name="cedula" class="form-control objControl" type="text" placeholder="Cedula o RIF" required="" style="margin-top: 0;" />

					<input id="nombre" name="nombre" class="form-control objControl" type="text" placeholder="Nombre" required="" />
					<input id="apellido" name="apellido" class="form-control objControl" type="text" placeholder="Apellido" />
					<input id="direccion" name="direccion" class="form-control objControl" type="text" placeholder="Direccion" />
					<input id="telefono" name="telefono" class="form-control objControl" type="text" placeholder="Telefono" />

					<input id="fecha_entrega" name="fecha_entrega" class="form-control objControl" type="text" placeholder="Fecha Entrega" required="" />

					<label for="pago">Pago del Cliente:</label>
					<input id="pago" name="pago" class="objControl" type="text" placeholder="Pago" required="" /><br />

					<label for="extra">Cobro Extra:</label>
					<input id="extra" name="extra" class="objControl" type="text" placeholder="Pago Extra" required="" /><br />

					<div id="total"></div>

					<label for="observacion" style="margin-top: 10px;">Observaci&oacute;n:</label>
					<textarea id="observacion" name="observacion" class="form-control objControl" placeholder=""></textarea>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
				<button id="guardarEncargo" type="button" class="btn btn-primary" data-toggle="modal">Guardar</button>
			</div>
		</div>
	</div>
</div>


<div class="modal" id="dialogNotaCredito" tabindex="-1" role="dialog" aria-labelledby="dialogNotaCreditoLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Cerrar</span></button>
				<h4 class="modal-title" id="dialogNotaCreditoLabel">Nota de Credito</h4>
			</div>
			<div class="modal-body">
				<form id="formNotaCredito">
					<input id="cedula" name="cedula" class="form-control objControl" type="text" placeholder="Cedula o RIF" required="" style="margin-top: 0;" />

					<input id="nombre" name="nombre" class="form-control objControl" type="text" placeholder="Nombre" required="" />
					<input id="apellido" name="apellido" class="form-control objControl" type="text" placeholder="Apellido" />
					<input id="direccion" name="direccion" class="form-control objControl" type="text" placeholder="Direccion" />
					<input id="telefono" name="telefono" class="form-control objControl" type="text" placeholder="Telefono" />

					<div><strong>Detalle. </strong><button id="agregarDetalleNotaCredito" type="button" class="btn btn-primary"><i class="fa fa-plus"></i></button></div>
					<div class="barra" style="height: 10px;"></div>

					<div id="contenedorDetalleNotaCredito"></div>
					<div id="totalNotaCredito"></div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-close"></i> Cerrar</button>
				<button id="guardarNotaCredito" type="button" class="btn btn-success" data-toggle="modal"><i class="fa fa-check"></i> Generar</button>
			</div>
		</div>
	</div>
</div>

<div id="clonDetalleNotaCredito" class="clonDetalleNotaCredito" style="display: none;">
	<input name="precio[]" class="form-control objControl campoNotaCredito nc_precio" type="text" placeholder="Precio" required="" style="margin-left: 0 !important;" />
	<input name="cantidad[]" class="form-control objControl campoNotaCredito nc_cantidad" type="text" placeholder="Cantidad" required="" />
	<input name="texto[]" class="form-control objControl campoNotaCredito nc_texto" type="text" placeholder="Descripci&oacute;n" required="" style="width: 299px;" />
	<button type="button" class="btn btn-danger eliminarDetalleNotaCredito"><i class="fa fa-minus"></i></button>
	<div class="barra"></div>
</div>


<div class="modal" id="dialogBusqueda" tabindex="-1" role="dialog" aria-labelledby="dialogBusquedaLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Cerrar</span></button>
				<h4 class="modal-title" id="dialogBusquedaLabel"><i class="fa fa-search"></i> Resultado de la Busqueda</h4>
			</div>
			<div class="modal-body" style="height: 400px; width: 100%; overflow: auto;">

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-close"></i> Cerrar</button>
			</div>
		</div>
	</div>
</div>

<div class="modal" id="dialogBusquedaInformacion" tabindex="-1" role="dialog" aria-labelledby="dialogBusquedaInformacionLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Cerrar</span></button>
				<h4 class="modal-title" id="dialogBusquedaInformacionLabel"><i class="fa fa-search"></i> Detalle de la Orden</h4>
			</div>
			<div class="modal-body">
				<div id="detalleOrdenInfo">
					<ul class="k-button"></ul>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-close"></i> Cerrar</button>
			</div>
		</div>
	</div>
</div>

<div class="modal" id="dialogreportezmensual" tabindex="-1" role="dialog" aria-labelledby="dialogreportezmensualLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Cerrar</span></button>
				<h4 class="modal-title" id="dialogreportezmensualLabel"><i class="fa fa-calendar"></i> Seleccione la Fecha</h4>
			</div>
			<div class="modal-body">
				<select id="mes" class="form-control objControl">
					<?php
					$meses = array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');
					$mesSeleccion = date('m') - 1;
					//if ($mesSeleccion < 0) $mesSeleccion = 11;
					foreach($meses as $i => $mes){
						echo "<option value='$i'" . (($i == $mesSeleccion) ? 'selected=""' : '') . ">$mes</option>";
					}
					?>
				</select>
				<input id="anno" type="text" class="form-control objControl" value="<?php echo date('Y'); ?>" />
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-close"></i> Cerrar</button>
				<button id="generarReporteZMensual" type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-newspaper-o"></i> Generar Reporte <strong>Z</strong></button>
			</div>
		</div>
	</div>
</div>
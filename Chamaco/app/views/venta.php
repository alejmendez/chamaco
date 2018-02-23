<script>
var $ip = '<?php echo $ci->getIpCliente(); ?>';
</script>
<script type="text/x-kendo-template" id="plantillaLista">
<li data-id="#= id #" data-producto="#= producto #" data-precio="#= precio #" data-cantidad="#= cantidad #" data-tipo-producto="#= tipo_producto #">
	<div class="contListaProducto">
		<div class="listaProducto">#= producto # 
			# if (id != 'parallevar') { #
				(Bsf #= precio #)
			# } #
		</div>
		# if (id != 'parallevar') { #
		<div>X</div>
		<div class="cantidad"> #= cantidad # </div>
		# } #
	</div>
	<div class="eliminar"></div>
</li>
</script>
<style>
#contPagina{
	width: 100%;
}
#contenedorListadoProductos{
	width: 500px; 
	float: left; 
	margin-left: 10px;
}
#vender{
	cursor: pointer;
    font-size: 40px;
    font-weight: bold;
    height: 128px;
    margin: 5px 0;
    text-align: center;
    width: 100%;
    text-shadow: 1px 2px 3px #666;
}

#listadoVenta{
	width: 550px;
	position: absolute;
	right: 10px;
	z-index: 2;
}

#listadoVenta ul{
	padding: 10px 0;
	margin: 0;
	overflow: hidden;
	width: 550px;
}

#listadoVenta li{
	list-style: none;
	margin-left: 10px;
	overflow: hidden;
	text-align: left;
}
#listadoVenta li div{
	float: left;
	min-height: 36px;
	line-height: 2.3em;
}

#listadoVenta .contListaProducto{
	border-bottom: solid 1px;
}

#listadoVenta .listaProducto{
	width: 360px;
	font-size: 14px;
	/* */
	essential 
	text-overflow: ellipsis;
	white-space: nowrap;
	overflow: hidden;
}

#listadoVenta .cantidad{
	padding: 0 3px;
}

#listadoVenta .eliminar{
	width: 36px;
	height: 36px;
	background: url('<?php echo base_url('img/eliminar.png'); ?>');
	float: right;
	cursor: pointer;
	margin-right: 5px;
}
#listaProductos{
	min-height: 410px;
}

#listaProductos,
#menuAccionVenta{
	padding: 0;
	margin: 0;
}

#listaProductos li,
#menuAccionVenta li{
	list-style: none;
	float: left;
	margin: 2px;
	cursor: pointer;
	width: 128px;
	height: 128px;
	text-align: center;
	padding: 0;
}
#menuAccionVenta li{
	
	font-size: 20px;
	padding: 5px;
	width: 146px;
	/*
	color: white;
	text-shadow: 1px 2px 3px #666;
	line-height: 1em;
	*/
	
}
#ubicacion{
	min-height: 26px;
	font-size: 20px;
}
#ubicacion h2{
	margin: 0;
}
#dialogoPedido #cantidad_pedido{
	float: left;
	text-align: center;
	height: 48px;
	margin-top: 1px;
	font-size: 30px;
    width: 160px;
}
#dialogoPedido #cantidad_menos,
#dialogoPedido #cantidad_mas{
	width: 48px;
	height: 48px;
	float: left;
	cursor: pointer;
}
#dialogoPedido #cantidad_menos{
	background: url('<?php echo base_url('img/menos.png'); ?>');
}
#dialogoPedido #cantidad_mas{
	background: url('<?php echo base_url('img/mas.png'); ?>');
}
#totalVenta{
	font-size: 20px;
	text-align: right;
	margin: 5px 5px 0 0;
}

#dialogoNumerico .k-button{
	float: left;
	width: 128px;
	height: 128px;
	font-size: 72px;
	font-weight: bold;
}
.btn-accion{
	width: 109px;
	height: 109px;
	font-size: 20px;
	padding: 0;
}

.btn-tipoCompra{
	width: 260px;
	height: 150px;
	font-size: 25px;
	padding: 0;
    margin-right: 20px;
}

.btn{
	white-space: normal;
}

/***************************************/
#detalleOrden{
	float: left;
	margin-top: 12px;
	width: 580px;
	display: none;
	margin-left: 10px;
	
	position: fixed;
    /*right: 213px;*/
    top: 109px;
}

#detalleOrden ul{
	padding: 10px 0;
	margin: 0;
	overflow: hidden;
	width: 580px;
}

#detalleOrden li{
	list-style: none;
	overflow: hidden;
	text-align: left;
	margin: 3px 10px;
	border-bottom: 1px solid;
}
#detalleOrden li div{
	float: left;
}

#detalleOrden .listaProducto{
	width: 512px;
}

#detalleOrden .cantidad{
	margin: 0 3px;
}

#detalleOrden .total{
	font-weight: bold;
	font-size: 30px;
	text-align: right;
	padding: 5px;
}

#detalleOrden .btn{
	font-weight: bold;
	font-size: 30px;
	margin-top: 20px;
	margin-left: 10px;
	float: right;
}

#dialogoUbicacion .selectpicker .filter-option, #dialogoUbicacion div.dropdown-menu ul li{
    font-size:20px !important;
}

#dialogoUbicacion div.bootstrap-select{
    width:350px !important;
    margin-left: 100px;
}

/*** DIALOGO DE PAGO ***/
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
    width: 130px;
    display: none;
    z-index: 1000;
}

.listaAgregarOrden{
    width: 250px;
    margin: 0 10px 15px 0;
}

#dialogoTipoSeccion .modal-body button{
    font-size: 26px;
    height: 120px;
    margin: 10px 20px 20px 10px;
    width: 150px;
}

#dialogoUbicacion .modal-body button{
    font-size: 22px;
    height: 100px;
    margin: 15px;
    width: 150px;
}

#dialogoUbicacion .modal-body button.btn-danger{
    text-decoration:line-through;
}
</style>
<div id="correlativo" class="btn btn-info">01</div>

<div class="modal bs-example-modal-sm" id="dialogoPedido" tabindex="-1" role="dialog" aria-labelledby="datosEncargoLabel" aria-hidden="true">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="datosEncargoLabel">Cantidad</h4>
			</div>
			<div class="modal-body" style="height: 180px;">
				<div id="cantidad_menos" class="btn btn-default"></div>
				<input id="cantidad_pedido" name="cantidad_pedido" class="btn btn-default" placeholder="Cantidad" />
				<div id="cantidad_mas" class="btn btn-default"></div>
				
				<button id="btnPedido" type="button" class="btn btn-primary" data-dismiss="modal" style="font-size: 34px; float: right; margin-top: 30px;">Listo!</button>
			</div>
		</div>
	</div>
</div>

<div id="dialogoNumerico" title="" style="display: none;">
	<input id="codigo" name="codigo" class="k-textbox" type="password" placeholder="Codigo" style="width: 510px;" />
	<div class="barra"></div>
	<div class="k-button" data-accion="asignar" data-valor="1">1</div>
	<div class="k-button" data-accion="asignar" data-valor="2">2</div>
	<div class="k-button" data-accion="asignar" data-valor="3">3</div>
	<div class="k-button" data-accion="borrar" style="font-size: 30px;color: #BB2929;">Borrar</div>
	<div class="barra"></div>
	<div class="k-button" data-accion="asignar" data-valor="4">4</div>
	<div class="k-button" data-accion="asignar" data-valor="5">5</div>
	<div class="k-button" data-accion="asignar" data-valor="6">6</div>
	<div class="k-button" data-accion="limpiar" style="font-size: 26px;color: #293BBB;">Limpiar</div>
	<div class="barra"></div>
	<div class="k-button" data-accion="asignar" data-valor="7">7</div>
	<div class="k-button" data-accion="asignar" data-valor="8">8</div>
	<div class="k-button" data-accion="asignar" data-valor="9">9</div>
	<div class="k-button" data-accion="cancelar" style="color: #B70015;font-size: 19px;">CANCELAR</div>
	<div class="barra"></div>
	<div class="k-button" data-accion="asignar" data-valor="0" style="margin-left: 129px;">0</div>
	<div class="k-button" data-accion="ok" style="color: #29BB2F;margin-left: 127px;">OK</div>
</div>

<div id="contenedorListadoProductos">
	<div id="ubicacion" style="width: 100%;"></div>
	<ul id="listaProductos">
		<?php
		$ci->db->seleccionar('productos', '*', array(
			'o' => 'orden'
		));
		
		$oculto = ' style="display:none;"';
		
		foreach($ci->db->rs as $prod){
			$prod['texto'] = ($prod['texto']);
			
			echo '
			<li class="btn btn-primary" 
				data-id="' . $prod['id'] . '"
				data-comando="producto"
				data-texto="' . $prod['texto'] . '"  
				data-precio="' . $prod['precio'] . '"
				data-ventaporkilo="' . $prod['ventaporkilo'] . '"  
				data-padre="' . $prod['padre'] . '" ' . ($prod['padre'] == 0 ? '' : $oculto) . ' >
				' . (trim($prod['imagen']) !== '' ? '<img src="' . base_url('img/venta/' . $prod['imagen']) . '" />' : '<br /><br />' . $prod['texto']) . '
			</li>
			';
		}
		
		/**/
		$ci->db->seleccionar('extras', '*');
		
		echo '
			<li class="btn btn-primary" 
				data-id="extras"
				data-comando="extras"
				data-texto="extras"  
				data-precio="0" 
				data-padre="0" ' . $oculto . '>
				<br /><br />Extras
			</li>
			';
		
		foreach($ci->db->rs as $prod){
			echo '
			<li class="btn btn-primary" 
				data-id="' . $prod['id'] . '"
				data-comando="extras"
				data-texto="' . $prod['nombre'] . '"  
				data-precio="' . $prod['precio'] . '"
				data-ventaporkilo="' . $prod['ventaporkilo'] . '"  
				data-padre="' . $prod['producto'] . '" ' . $oculto . '>
				<br /><br />' . $prod['nombre'] . '
			</li>
			';
		}
		?>
	</ul>
</div>

<div id="listadoVenta" class="">
	<ul id="listadoProductosVendidos" class="k-button"></ul>
	<div id="totalVenta">
		Total: <span>Bs. F. 0,00</span>
	</div>
</div>

<div style="position: fixed; bottom: 10px; right: 10px; width: 561px;">
	<button id="vender" class="btn btn-success" data-toggle="modal" >Vender!</button>
	
	<button id="venta_reiniciar" class="btn btn-primary btn-accion">Reiniciar Pedido</button>
	<button id="venta_reiniciar_venta" class="btn btn-warning btn-accion">Pedido Nuevo</button>
	<button id="pedido_para_llevar" class="btn btn-info btn-accion">Pedido Para Llevar</button>
    <button id="agregar_orden" class="btn btn-info btn-accion" style="background-color: #c51e15;">Agregar a Orden</button>
	<button id="pantalla_completa" class="btn btn-primary btn-accion" style="background-color: #a72ab7;">Pantalla Completa</button>
	
	<!-- <li id="pedido_para_llevar" class="k-button" style="background-color: #009313;">
		<br />Pedido Para Llevar
	</li>
	<li id="imprimir_ultima_orden" class="k-button" style="background-color: #0008CF;">
		<br />Imprimir Ultima Orden
	</li>
	<li id="reporte_diario" class="k-button" style="background-color: #12B700;">
		<br />Reporte Diario
	</li>
	<li id="cerrar_terminal" class="k-button" style="background-color: #FF7A00;">
		<br />Cerrar Caja
	</li> -->
</div>

<div class="modal" id="dialogoTipoCompra" tabindex="-1" role="dialog" aria-labelledby="datosTipoCompraLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Cerrar</span></button>
				<h4 class="modal-title" id="dialogTipoCompraLabel">Tipo de Compra</h4>
			</div>
			<div class="modal-body" style="height: 180px;">
                <button id="PagoPendiente" class="btn btn-primary btn-tipoCompra">Pendiente por Pagar<br />(Asignar Ubicaci&oacute;n)</button>
                <button id="PagoProcesar" type="button" class="btn btn-tipoCompra btn-success">Pagar</button>
			</div>
            <div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-close"></i>Cerrar</button>
			</div>
		</div>
	</div>
</div>

<div class="modal" id="dialogoUbicacion" tabindex="-1" role="dialog" aria-labelledby="datosUbicacion" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Cerrar</span></button>
				<h4 class="modal-title" id="dialogTipoCompraLabel">Asignar Ubicacion</h4>
			</div>
			<div class="modal-body" style="height: 300px; overflow:auto;"></div>
            <div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-close"></i>Cerrar</button>
			</div>
		</div>
	</div>
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
		</div>
	</div>
</div>

<div class="modal" id="dialogoAgregarOrden" tabindex="-1" role="dialog" aria-labelledby="datosUbicacion" aria-hidden="true">
	<div class="modal-dialog" style="width:840px !important;">
		<div class="modal-content">
			<div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Cerrar</span></button>
				<h4 class="modal-title" id="dialogTipoCompraLabel">Agregar a Orden</h4>
			</div>
			<div class="modal-body" style="height:220px; overflow: auto;"></div>
            <div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-close"></i>Cerrar</button>
			</div>
		</div>
	</div>
</div>

<div class="modal" id="dialogoTipoSeccion" tabindex="-1" role="dialog" aria-labelledby="datosTipoSeccionLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Cerrar</span></button>
				<h4 class="modal-title" id="dialogTipoCompraLabel">Secci&oacute;n</h4>
			</div>
			<div class="modal-body" style="height: 180px; overflow:auto;">
                <?php
					$ci->db->seleccionar('secciones', '*');
                    
					foreach($ci->db->rs as $seccion){
						echo '
                            <button id="seccion_'.$seccion['id'].'" data-valor="' . $seccion['id'] . '" type="button" class="btn btn-seccion btn-primary">' . $seccion['nombre'] . '</button>';
					}						
				?>
			</div>
            <div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-close"></i>Cerrar</button>
			</div>
		</div>
	</div>
</div>

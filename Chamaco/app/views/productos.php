<script>
var $formObj = "#<?php echo $ci->productos->id(); ?>",
dataArbol = <?php echo json_encode($ci->arbol(true)); ?>;
app.ini(function(){
	<?php $ci->productos->sJQuery(); ?>
});
</script>
<style>
#contenedorproductosArbol{
	padding: 5px;
	z-index: 1;
	padding-bottom: 10px;
}

#nombre_nodo, #descripcion_nodo,
#nombre_nuevo_nodo, #descripcion_nuevo_nodo{
	width: 300px;
}

.btn-group input{
	position: absolute;
	z-index: -1;
	opacity: 0;
}

.btn-group .btn.btn-primary.active{
	background-color: #449d44;
    border-color: #398439;
    color: #fff;
}
</style>
<div class="grid_4">
	<form <?php echo $ci->productos; ?>>
		<div id="arbol"></div>
	</form>
</div>
<div class="grid_8">
	<div id="contenedorproductosArbol" class="k-content">
		<form id="fProductosArbol" name="fProductosArbol">
			<button id="nuevo" class="k-button">Nuevo</button>
			<button id="guardar_arbol" class="k-button">Guardar</button>
			<button id="eliminar_nodo" class="k-button">Eliminar</button>
			<div class="barra" style="height: 10px;"></div>
		</form>

		<div class="barra" style="height: 35px;"></div>
        
        <h4 class="ui-widget" style="padding: 3px 3px 5px 0; margin: 0 0 5px; font-weight:bold;">
            <span class="ui-icon ui-icon-pencil" style="float: left;">Modificar Producto</span>&nbsp;Modificar Producto
        </h4>
    
		<form id="fProductosArbolElemento" name="fProductosArbolElemento">
			<input id="id_nodo" name="id_nodo" type="hidden" />
			
			<label>
				<input id="ventaporkilo" name="ventaporkilo" type="checkbox" value="venta" /> Vender Producto por Peso (Kgr)
			</label><br /><br />
			
			<input id="nombre_nodo" name="nombre_nodo" class="k-textbox" placeholder="Nombre Producto" type="text" />
			<input id="descripcion_nodo" name="descripcion_nodo" class="k-textbox" placeholder="Descripci&oacute; del Producto" type="text" /><br /><br />
			
			
			
			<input id="precio_nodo" name="precio_nodo" placeholder="Precio del Producto" type="text" style="width: 300px;" />
			<button id="guardar_propiedad_nodo" class="k-button">Guardar</button>
		</form>
	</div>
</div>

<div id="dialogo_nuevo_nodo" title="Nuevo Producto" style="display: none;">
	<input id="nombre_nuevo_nodo" name="nombre_nuevo_nodo" class="k-textbox" placeholder="Nombre del Producto" type="text" /><br /><br />
	<input id="descripcion_nuevo_nodo" name="descripcion_nuevo_nodo" class="k-textbox" placeholder="Descripci&oacute;n del Producto" type="text" /><br /><br />
	
	<input id="precio_nuevo_nodo" name="precio_nuevo_nodo" placeholder="Precio del Producto" type="text" style="width: 300px;" /><br /><br />
	
	<label>
		<input id="ventaporkilo_nuevo" name="ventaporkilo" type="checkbox" value="venta" /> Vender Producto por Peso (Kgr)
	</label>
</div>
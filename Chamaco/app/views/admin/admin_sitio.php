<script>
var $formObj = "#<?php echo $ci->admin_sitio->id(); ?>";
app.ini(function(){
	<?php $ci->admin_sitio->sJQuery(); ?>
	crear_arbol();
});
</script>
<style>
#contenedoradmin_sitioArbol{
	padding: 5px;
	z-index: 1;
	padding-bottom: 10px;
}
</style>
<div class="grid_6">
	<form <?php echo $ci->admin_sitio; ?>>
		<div id="arbol"></div>
	</form>
</div>
<div class="grid_6">
	<div id="contenedoradmin_sitioArbol" class="k-content">
		<form id="fadmin_sitioArbol" name="fadmin_sitioArbol">
			<button id="deseleccionar" class="k-button">Deseleccionar</button>
			<button id="guardar_arbol" class="k-button">Guardar Arbol</button>
			<button id="eliminar_nodo" class="k-button">Eliminar Nodo</button>
			<div class="barra" style="height: 10px;"></div>

			<span class="k-textbox k-space-right">
				<input id="nombre_nuevo_nodo" class="k-textbox" placeholder="Nombre del Nodo" type="text" />
                <a id="agregar_nuevo_nodo" href="#" class="k-icon k-i-plus">&nbsp;</a>
            </span>

            <input id="codigo_nuevo_nodo" class="k-textbox" placeholder="Codigo del Nodo" type="text" />
		</form>

		<div class="barra" style="height: 20px;"></div>
		<form id="fadmin_sitioArbolElemento" name="fadmin_sitioArbolElemento">
			<input id="id_nodo" name="id_nodo" type="hidden" />
			<input id="nombre_nodo" name="nombre_nodo" class="k-textbox" placeholder="Nombre del Nodo" type="text" />
			<input id="codigo_nodo" name="codigo_nodo" class="k-textbox" placeholder="Codigo del Nodo" type="text" />
			
			<button id="guardar_propiedad_nodo" class="k-button">Guardar</button>
		</form>
	</div>
</div>
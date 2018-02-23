<script>
var $lista, listadoVenta;

app.ini(function(){
	$lista = kendo.template($("#plantillaLista").html());
	listadoVenta = $("ul", "#listadoVenta");
	
	$("#cabecera").remove();
	$("#contenedorPrincipal").removeClass('container_12 clearfix');
	
	reiniciar();
	
	document.title = 'El Chamaco: Modulo de Venta';
});

function agregar_elemento_lista(datos){
	var productoListaExistente = listadoVenta.find("[data-id='" + datos.id + "'][data-tipo-producto='" + datos.tipo_producto + "']");

	if (productoListaExistente.length){
		var cantidadExistente = parseFloat(productoListaExistente.find('.cantidad').html());
		productoListaExistente.find('.cantidad').html(datos.cantidad);
	}else{
		listadoVenta.append($lista(datos));
	}
}

function total(valor){
	$("#total").html(valor);
}

function reiniciar(){
	listadoVenta.html('');
	total('Bs. F. 0,00');
}
</script>
<script type="text/x-kendo-template" id="plantillaLista">
<li data-id="#= id #" data-producto="#= producto #" data-precio="#= precio #" data-cantidad="#= cantidad #" data-tipo-producto="#= tipo_producto #">
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
<style>
#contPagina{
	padding: 0;
}
#listadoVenta{
	width: 400px;
	font-size: 12px;
    margin: 5px;
}

#listadoVenta ul{
	padding: 4px 0;
	margin: 0;
	overflow: hidden;
	width: 370px;
}

#listadoVenta li{
	list-style: none;
	margin-left: 4px;
	overflow: hidden;
	text-align: left;
}
#listadoVenta li div{
	float: left;
}
#listadoVenta .listaProducto{
	width: 320px;
}

#listadoVenta .cantidad{
	margin: 0 3px;
}

#listadoVenta .listaProducto{
	width: 280px;
	/* essential */
	text-overflow: ellipsis;
	white-space: nowrap;
	overflow: hidden;
}
</style>
<div id="listadoVenta">
	<ul class="k-button"></ul>
	
    <h4 class="ui-widget" style="padding: 3px; margin: 0 0 5px; font-weight: bold;">
        <span class="ui-icon ui-icon-cart" style="float: left;">Total</span>&nbsp;Total: <span id="total">Bs. F. 0,00</span>
    </h4>
</div>
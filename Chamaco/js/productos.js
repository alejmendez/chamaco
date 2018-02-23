var $id = 0,
ap,
tw,
twds;

app.ini(function(){
	/*$("#buscar", "#menuAccion").unbind("click").remove();
	$("#eliminar", "#menuAccion").unbind("click").remove();
	$("#guardar", "#menuAccion").unbind("click").remove();*/
	
	crear_arbol(dataArbol);
	
	$("#ventaporkilo, #ventaporkilo_nuevo").change(function(){
		
	});
	
	$("#dialogo_nuevo_nodo").dialog({
    	width : 350,
    	height : 300
    });

    $("#dialogo_nuevo_nodo").dialog();
    $("#dialogo_nuevo_nodo").dialog("option", "buttons", [
		{
			text: "Cancelar",
			click: function(){
				$(this).dialog("close");
			}
		}, {
			text: "Guardar",
			click: function(){
				agregar_nodo();
				$(this).dialog("close");
			}
		}
	]);

	$("#fProductos").removeAttr("title");
	$(".k-button").kendoButton();
	$("#precio_nodo, #precio_nuevo_nodo").kendoNumericTextBox({
		step: 1,
		format: "BsF #.00",
		min: 0
	});

	resetForm();

	//$("#contenedorArbol").resizable("destroy").resizable({ maxWidth: $("#contenedorArbol").width(), minWidth: $("#contenedorArbol").width() });

    $("#nuevo", "#fProductosArbol").click(function(){
    	$("#dialogo_nuevo_nodo").dialog('open');
		resetForm();
		return false;
	});

	$("#fProductosArbolElemento").submit(function(){
		return false;
	});

	$("#guardar_propiedad_nodo", "#fProductosArbolElemento").click(function(){
		var $datos = {
    		id 	: $("#id_nodo", "#fProductosArbolElemento").val(),
    		texto : $("#nombre_nodo", "#fProductosArbolElemento").val(),
    		descripcion : $("#descripcion_nodo", "#fProductosArbolElemento").val(),
    		precio : $("#precio_nodo", "#fProductosArbolElemento").data("kendoNumericTextBox").value(),
    		ventaporkilo : $("#ventaporkilo", "#fProductosArbolElemento").is(':checked')
    	};

		$.ajax(app.url + 'guardarNodo', {
        	data:$datos,
			success : function(r){
				aviso(r);
				if (r.s === 's'){
					$resultado = twds.get($datos.id);
					$resultado.text = $datos.texto;
					$resultado.descripcion = $datos.descripcion;
					$resultado.precio = $datos.precio;
					$resultado.ventaporkilo = $datos.ventaporkilo;
					
					twds.sync();

					tw.select(null);
					resetForm();
				}
			}
		});

		return false;
	});

	$("#nombre_nodo, #descripcion_nodo", "#fProductosArbolElemento").val('');
	$("#precio_nodo", "#fProductosArbolElemento").data("kendoNumericTextBox").value(0);

	$("#nombre_nodo", "#fProductosArbolElemento").on("keyup", function(e){
		$("#descripcion_nodo", "#fProductosArbolElemento").val(ucwords(this.value));
	});

	$("#guardar_arbol", "#fProductosArbol").click(function(e){
		e.preventDefault();
		guardar_arbor();
    });

    $("#eliminar_nodo", "#fProductosArbol").click(function(e){
    	e.preventDefault();
    	alertify.confirm("Esta Seguro que Desea Eliminar Este Producto?", function (e) {
			if (e){
		    	var arbol_data = [];

		    	data_arbol(twds.view(), arbol_data, 0, tw.select().find("input:first").val());
		    	//var $data = twds.get(tw.select().find("input:first").val());

		    	$.ajax(app.url + 'eliminarNodo', {
		        	data:{
		        		arbol : arbol_data
		        	},
					success : function(r){
						aviso(r);

						if (r.s === 's'){
							//crear_arbol(r.arbol);
							tw.remove(tw.select());
							tw.select(null);
							resetForm();
						}
					}
				});
			}
		});
    });

    //arbol();
});

function resetForm(){
	//tw.select(null);
	$("#nombre_nodo, #descripcion_nodo", "#fProductosArbolElemento").val('');
	$("#precio_nodo", "#fProductosArbolElemento").data("kendoNumericTextBox").value(0);
	$("#ventaporkilo").prop('checked', false).triggerHandler('change');
}

function ucwords(str){
	return (str + '').replace(/^([a-z\u00E0-\u00FC])|\s+([a-z\u00E0-\u00FC])/g, function ($1) {
		return $1.toUpperCase();
	});
}

function guardar_arbor(){
	var arbol_data = [];
	data_arbol(twds.view(), arbol_data, 0);

    $.ajax(app.url + 'guardarArbol', {
    	data:{
    		arbol : arbol_data
    	},
		success : function(r){
			aviso(r);
			refrescarArbol();

			tw.select(null);
			resetForm();
		}
	});
}

function data_arbol(nodes, arbol_data, padre, busqueda){
	if (typeof(busqueda) === 'undefined'){
		busqueda = true;
	}

    for (var i = 0; i < nodes.length; i++){
    	if (busqueda === true || (typeof(busqueda) === 'string' && busqueda === nodes[i].id)){
    		console.log(nodes[i].cambio);
			arbol_data.push({
	  			"id" : nodes[i].id,
				"text":nodes[i].text,
				"descripcion":nodes[i].descripcion,
				"precio":nodes[i].precio,
				"ventaporkilo" : nodes[i].ventaporkilo,
				"padre" : padre,
				"posicion" : i,
				"db" : nodes[i].db
	  		});

	  		if (nodes[i].hasChildren){
	  			data_arbol(nodes[i].children.view(), arbol_data, nodes[i].id, true);
	  		}
		}else{
			if (nodes[i].hasChildren){
	  			data_arbol(nodes[i].children.view(), arbol_data, nodes[i].id, busqueda);
	  		}
		}
    }
}

function agregar_nodo(){
	var selectedNode = tw.select(),
	$nombre_nodo = $("#nombre_nuevo_nodo"),
	$descripcion_nodo = $("#descripcion_nuevo_nodo"),
	$descripcion_nodo = $("#descripcion_nuevo_nodo"),
	$precio_nodo = $("#precio_nuevo_nodo"),
	texto = $.trim($nombre_nodo.val());

	if (texto === ''){
		return false;
	}
	console.log(selectedNode);
	if (selectedNode.length == 0) {
		selectedNode = null;
	}
	//"icon-fodt", "icon-sitemap", "icon-page-white-php", "icon-page-white-code"

	tw.append({
		"id":"pro_" + rand(),
		"text":texto,
		"descripcion":$descripcion_nodo.val(),
		"precio":$precio_nodo.data("kendoNumericTextBox").value(),
		"ventaporkilo" : $("#ventaporkilo_nuevo").is(':checked'),
		//"spriteCssClass": "icon-sitemap",
		"cambio" : true,
		"db" : 0
	}, selectedNode);

	tw.select(selectedNode);

	$nombre_nodo.val("");
	$descripcion_nodo.val("");
	$precio_nodo.data("kendoNumericTextBox").value(0);
	$("#ventaporkilo_nuevo").prop('checked', false).triggerHandler('change');

	refrescarArbol();
}

function arbol(){
	$.ajax(app.url + 'arbol', {
		success : function(r){
			crear_arbol(r);
		}
	});
}

function crear_arbol(r){
	ap = $("#arbol");
	tw = ap.data("kendoTreeView");

	if (tw !== undefined){
		tw.destroy();
		ap.removeAttr('class data-role role aria-activedescendant').html('');
	}

	ap
    .kendoTreeView({
    	template: ' #: item.text # <input name="productos[]" type="hidden" value="#= item.id #" />',
    	dragAndDrop: true,
        dataSource: {
        	data : r,
        	requestEnd : function(){

		    }
		},
        animation: {
            expand: {
                effects: 'expand:vertical fadeIn'
            }
        },
        select: function(e) {
        	var $data = twds.get(e.sender._current.find("input:first").val());

			if ($data.id === 'productos_ini'){
				resetForm();
	    		return;
	    	}

	    	$("#id_nodo").val($data.id);
	    	$("#nombre_nodo").val($data.text).trigger('keyup');
	    	$("#descripcion_nodo").val($data.descripcion);
	    	$("#precio_nodo").data("kendoNumericTextBox").value($data.precio);
	    	$("#ventaporkilo").prop('checked', $data.ventaporkilo).triggerHandler('change');
	    	
	    	console.log($data);
		},
		dragend: function(e) {
			//refrescarArbol();
			
			//e.sourceNode
			
			//guardar_arbor();
		}
    });

	tw = ap.data("kendoTreeView");
	twds = tw.dataSource;
}

function refrescarArbol(){
	//verificarNodosArbol(twds.view());
}

function verificarNodosArbol(nodes, iteracion){
	iteracion = iteracion || 0;
	iteracion++;
    for (var i = 0; i < nodes.length; i++) {
    	var $ele = tw.findByUid(nodes[i].uid),
		$eleSprite = $ele.find(".k-sprite:first").addClass("icon-sitemap"),
		$eleInput = $ele.find("input:first");


    	if (nodes[i].hasChildren) {
			verificarNodosArbol(nodes[i].children.view(), iteracion);
		}

        $eleInput.val(nodes[i].id);
        $ele.find(".k-in:first").attr('title', nodes[i].text + ' (' + nodes[i].precio + ')');
    }
}
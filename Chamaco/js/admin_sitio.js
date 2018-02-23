var $id = 0, ap, tw, twds;

app.ini(function(){
	$('#buscar', '#menuAccion').unbind('click').remove();
	$('#eliminar', '#menuAccion').unbind('click').remove();
	$('#guardar', '#menuAccion').unbind('click').remove();
	
	//permisos
	$('#fadmin_sitio').removeAttr('title');
	$('.k-button').kendoButton();
	//$('#contenedorArbol').resizable('destroy').resizable({ maxWidth: $('#contenedorArbol').width(), minWidth: $('#contenedorArbol').width() });
    
    $('#deseleccionar', '#fadmin_sitioArbol').click(function(){
		$('#arbol').data('kendoTreeView').select(null);
		$('#nombre_nodo, #codigo_nodo').val('');
		return false;
	});
	
	$('#fadmin_sitioArbolElemento').submit(function(){
		return false;
	});
	
	$('#guardar_propiedad_nodo', '#fadmin_sitioArbolElemento').click(function(){
		var $datos = {
    		id 	: $('#id_nodo', '#fadmin_sitioArbolElemento').val(),
    		texto : $('#nombre_nodo', '#fadmin_sitioArbolElemento').val(),
    		codigo : $('#codigo_nodo', '#fadmin_sitioArbolElemento').val()
    	};
    	
		$.ajax(app.url + 'guardarNodo', {
        	data:$datos,
			success : function(r){
				aviso(r);
				if (r.s === 's'){
					$resultado = twds.get($datos.id);
					$resultado.text = $datos.texto;
					$resultado.codigo = $datos.codigo;
					twds.sync();
				}
			}
		});
		
		return false;
	});
	
	
	$('#nombre_nuevo_nodo, #codigo_nuevo_nodo', '#fadmin_sitioArbol').val('').on('keypress', function(e){
		if (e.which === kendo.keys.ENTER) {
			e.preventDefault();
			agregar_nodo();
        }
	});
	
	$('#nombre_nuevo_nodo', '#fadmin_sitioArbol').on('keyup', function(e){
		$('#codigo_nuevo_nodo', '#fadmin_sitioArbol').val(procesarTexto(this.value));
		this.value = ucwords(this.value);
	});
	
	$('#nombre_nodo, #codigo_nodo', '#fadmin_sitioArbolElemento').val('');
	$('#nombre_nodo', '#fadmin_sitioArbolElemento').on('keyup', function(e){
		$('#codigo_nodo', '#fadmin_sitioArbolElemento').val(procesarTexto(this.value));
		this.value = ucwords(this.value);
		return false;
	});
	
	$('#agregar_nuevo_nodo').on('click', function(e){
		e.preventDefault();
		agregar_nodo();
	});
	
	$('#guardar_arbol', '#fadmin_sitioArbol').click(function(e){
		e.preventDefault();
		
		var arbol_data = [];
		
		data_arbol(twds.view(), arbol_data, 0);
        
        $.ajax(app.url + 'guardarArbol', {
        	data:{
        		arbol : arbol_data
        	},
			success : function(r){
				aviso(r);
				refrescarArbol();
			}
		});
    });
    
    $('#eliminar_nodo', '#fadmin_sitioArbol').click(function(e){
    	e.preventDefault();
    	var arbol_data = [];
    	
    	data_arbol(twds.view(), arbol_data, 0, tw.select().find('input:first').val());
    	//var $data = twds.get(tw.select().find('input:first').val());
    	
    	$.ajax(app.url + 'eliminarNodo', {
        	data:{
        		arbol : arbol_data
        	},
			success : function(r){
				//crear_arbol(r.arbol);
				tw.remove(tw.select());
			}
		});
    });
    
    //arbol();
});

function procesarTexto(texto){
	return stripVowelAccent(texto).replace(/\s+/g, '_').toLowerCase();
}

function ucwords(str){
	return (str + '').replace(/^([a-z\u00E0-\u00FC])|\s+([a-z\u00E0-\u00FC])/g, function ($1) {
		return $1.toUpperCase();
	});
}

function data_arbol(nodes, arbol_data, padre, busqueda){
	refrescarArbol();
	_data_arbol(nodes, arbol_data, padre, busqueda);
}

function _data_arbol(nodes, arbol_data, padre, busqueda){
	if (typeof(busqueda) === 'undefined'){
		busqueda = true;
	}
	
    for (var i = 0; i < nodes.length; i++){
    	if (busqueda === true || (typeof(busqueda) === 'string' && busqueda === nodes[i].id)){
    		var tipo = padre == 0 ? 'S' : 'C'; // S = Sistema | C = Controlador
    		if (padre != 0){
	    		if (nodes[i].hasChildren){
		  			var nodo_hijo = nodes[i].children.view(), directorio = false;
		  			
		  			for (var j = 0; j < nodo_hijo.length; j++){
		  				if (nodo_hijo[j].hasChildren){
		  					directorio = true;
		  					break;
		  				}
		  			}
		  			
		  			if (directorio){
		  				tipo = 'D'; //Directorio
		  			}
		  		}else{
		  			tipo = 'M'; // Metodo...
		  		}
    		}
    		
			arbol_data.push({
	  			'id' : nodes[i].id,
				'text':nodes[i].text,
				'codigo':nodes[i].codigo,
				'padre' : padre,
				'posicion' : i,
				'tiene_hijos' : nodes[i].hasChildren,
				'tipo' : tipo,
				'db' : nodes[i].db
	  		});
	  		
	  		if (nodes[i].hasChildren){
	  			_data_arbol(nodes[i].children.view(), arbol_data, nodes[i].id, true);
	  		}
		}else{
			if (nodes[i].hasChildren){
	  			_data_arbol(nodes[i].children.view(), arbol_data, nodes[i].id, busqueda);
	  		}
		}
    }
}

function agregar_nodo(){
	var selectedNode = tw.select(),
	$nombre_nodo = $('#nombre_nuevo_nodo', '#fadmin_sitioArbol'),
	$codigo_nodo = $('#codigo_nuevo_nodo', '#fadmin_sitioArbol'),
	texto = $.trim($nombre_nodo.val());
	
	if (texto === ''){
		return false;
	}
	//console.log(selectedNode);
	if (selectedNode.length == 0) {
		selectedNode = null;
	}
	//'icon-fodt', 'icon-sitemap', 'icon-page-white-php', 'icon-page-white-code'
	
	tw.append({
		'id':'M_' + rand(),
		'text':texto,
		'codigo':$codigo_nodo.val(),
		'spriteCssClass': selectedNode === null ? 'icon-sitemap' : 'icon-fodt',
		'db' : 0  
	}, selectedNode);
	
	tw.select(selectedNode);
	
	$nombre_nodo.val('');
	$codigo_nodo.val('');
	
	refrescarArbol();
}

function crear_arbol(r){
	ap = $('#arbol').kendoTreeView({
    	dataSource: {
	        transport: {
	            read: app.url + 'arbol'
	        },
	        schema: {
	            model: {
	                id: 'id',
	                hasChildren: 'tiene_hijos',
	                children: 'hijos'
	            }
	        }
	    },
    	template: ' #: item.text # <input name="admin_sitio[]" type="hidden" value="#= item.id #" />',
    	dragAndDrop: true,
        animation: {
            expand: {
                effects: 'expand:vertical fadeIn'
            }
        },
        select: function(e) {
        	var $data = this.dataItem(e.node); //twds.get(e.sender._current.find('input:first').val());
	    	
	    	$('#id_nodo').val($data.id);
	    	$('#codigo_nodo').val($data.codigo);
	    	$('#nombre_nodo').val($data.text).trigger('keyup');
		},
		dragend: function(e) {
			refrescarArbol();
		}
    });
    
	tw = ap.data('kendoTreeView');
	twds = tw.dataSource;
}

function refrescarArbol(){
	_verificarNodosArbol(twds.view());
	verificarNodosArbol();
}

function verificarNodosArbol(){
	var $padre;
	$('.k-sprite', ap).removeClass('icon-fodt icon-page-white-php icon-page-white-code').addClass('icon-fodt'),
	
	$('#arbol > ul > li > div .k-sprite').addClass('icon-sitemap');
	
	$('li', ap).each(function(i){
		if (!$('ul', this).length){
			//console.log(this);
			$('.k-sprite', this).addClass('icon-page-white-code');
			
			$padre = $(this).parent().parent();
			$('> div .k-sprite', $padre).addClass('icon-page-white-php');
		}
	});
}

function _verificarNodosArbol(nodes, iteracion){
    for (var i = 0; i < nodes.length; i++) {
		nodes[i].load();
		
		if (nodes[i].hasChildren){
			_verificarNodosArbol(nodes[i].children.view());
		}
    }
}
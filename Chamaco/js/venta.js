var plantillaListadoVenta,
$lista,
listadoVenta,
listaProductos,
ubicacion,
idProducto,
precioProducto,
tipo_producto = '',
ventaporkilo = false,
ultimaOrden = 0,
$forma_pago = 0,
$clave_segudidad,
nombreProducto = '', 
$terminalDesactivado = false,
ventanaPedido,
pantallaSegundaria,
ventanaTipoCompra;

var ventanaFormaPago,
orden,
$forma_pago_pago,
idUltimaOrden = 0,
total = 0,
$procesado = false,
$comanda = false,
ordenActual = 0,
fecha_actual = new Date();

var listaAgregarOrden = $('<div/>', {'class': 'listaAgregarOrden btn btn-primary'});

if ($ip !== '127.0.0.1'){
	pantallaSegundaria = window.open(app.base + 'venta_s', 'Palmerinni: Modulo de Venta', 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, copyhistory=no, width=380, height=220, top=-10, left=1000');
}

$.ajaxSetup({
	error: function(x, t, m) {
		if ($terminalDesactivado === true){
			$("#vender").removeAttr('disabled');
		}else{
			$("#vender").attr('disabled', 'disabled');
		}
		
		if (t == 'timeout'){
			aviso('No se Puedo Realizar la Venta, El Servidor Tardo Mucho en Responder', false);
		}else{
			aviso('Se Genero un Advertencia');
		}
	}
});



/*
https://developer.mozilla.org/en-US/docs/Web/API/Window.open

Mozilla and Firefox users can force new windows to always render the menubar by setting 
dom.disable_window_open_feature.menubar to true in about:config or in their user.js file.
*/

app.ini(function(){
	$("#cabecera").remove();
	$("#contPagina").unwrap();
	$(window).resize(function(){
		$("#contenedorListadoProductos").css('width', $(window).width() - $("#listadoVenta").width() - 30);
	})
	.resize()
	.unload(function(){
		if (pantallaSegundaria !== undefined){
			pantallaSegundaria.close(); // para cerrar la ventana
		}
	});
	
	ventanaPedido = $('#dialogoPedido').clone();
    ventanaTipoCompra = $('#dialogoTipoCompra').clone();
    ventanaUbicacion = $('#dialogoUbicacion').clone();
    ventanaSecciones = $('#dialogoTipoSeccion').clone();
    ventanaFormaPago = $('#dialogoPago').clone();
    ventanaAgregarOrden = $("#dialogoAgregarOrden").clone();
    
    
    
	$('#dialogoPedido, #dialogoTipoCompra, #dialogoUbicacion, #dialogoPago, #dialogoAgregarOrden, #dialogoTipoSeccion').remove();
	$('body')
    .append(ventanaPedido)
    .append(ventanaTipoCompra)
    .append(ventanaUbicacion)
    .append(ventanaSecciones)
    .append(ventanaFormaPago)
    .append(ventanaAgregarOrden);
    
    //$("#ubicacionVenta").selectpicker();

	//plantillaListadoVenta= kendo.template($("#plantillaListadoVenta").html());
	listadoVenta = $("#listadoProductosVendidos", "#listadoVenta");
	listaProductos = $('#listaProductos');
	ubicacion = $('#ubicacion');
	$lista = kendo.template($("#plantillaLista").html());
	
	$("#cantidad_pedido").val(1);
	//$("#totalVenta span").html(kendo.toString(0, "c", "es-VE"));
    
	$('li', listaProductos).click(function(){
		if ($terminalDesactivado === true){
			return false;
		}
		
		var t = $(this),
		id = t.attr('data-id'),
		padre = t.attr('data-padre'),
		texto = t.attr('data-texto'),
		precio = t.attr('data-precio'),
		comando = t.attr('data-comando'),
		hijos = $('li[data-padre=' + id + '][data-comando="producto"]', listaProductos);
		
		ventaporkilo = parseInt(t.attr('data-ventaporkilo')) === 1 ? true : false;
		
		idProducto = id;
		precioProducto = precio;
		tipo_producto = comando;
		
		if (texto !== 'Comida'){
			nombreProducto += texto + " ";
		}
		
		texto = ubicacion.text() + ' / ' + texto;
		texto = texto.replace(/^\s\/\s+|\s\/\s+$/g,"");
		
		ubicacion.text(texto);
		// data-comando="producto"
		// data-comando="extras"
		
		if (comando === 'producto'){
			if (hijos.length){
				$('li', listaProductos).css('display', 'none');
				$extras = $('li[data-padre="' + id + '"][data-comando="extras"]', listaProductos);
				if ($extras.length){
					$('li[data-id="extras"][data-comando="extras"]', listaProductos)
						.css('display', 'block')
						.data('id_producto', id);
				}
				
				hijos.css('display', 'block');
			}else{
				$("#dialogoPedido").modal('show');
			}
		}else if (comando === 'extras'){
			if (id === 'extras'){
				nombreProducto = "> Extas ";
				
				$('li', listaProductos).css('display', 'none');
				$extras = $('li[data-padre=' + t.data('id_producto') + '][data-comando="extras"]', listaProductos);
				if ($extras.length){
					$extras.css('display', 'block');
				}
				
				hijos.css('display', 'block');
			}else{
				$("#dialogoPedido").modal('show');
			}
		}
	});
	
	listadoVenta.on('click', '.eliminar', function(){
		$(this).parent().remove();
		calcularTotal();
	});
	
	$("#dialogoPedido").on('show.bs.modal', function(e){
		if (ventaporkilo){
			$("#cantidad_pedido").val('1.0 Kgr');
		}
	}).find('#btnPedido').click(function(){
		agregar_elemento_lista({
			'id': idProducto,
			'producto': nombreProducto,
			'cantidad': $("#cantidad_pedido").val(),
			'precio':precioProducto,
			'tipo_producto': tipo_producto
		});
	});
	
	// <div class="k-button" data-accion="asignar" data-valor="9">9</div>
	
	$("div.k-button", "#dialogoNumerico").click(function(){
		var t = $(this),
		$accion = t.attr('data-accion'),
		$valor = t.attr('data-valor'),
		$campo = $("#codigo", "#dialogoNumerico"),
		$cv = $campo.val();
		
		if ($accion == 'asignar'){
			$campo.val($cv + $valor);
		}else if($accion == 'borrar'){
			$campo.val($cv.substring(0, $cv.length - 1));
		}else if($accion == 'limpiar'){
			$campo.val('');
		}else if($accion == 'cancelar'){
			$("#dialogoNumerico").data('valor', '').dialog('close');
			$campo.val('');
		}else if($accion == 'ok'){
			$("#dialogoNumerico").data('valor', $cv).dialog('close');
			$campo.val('');
		}
	});
	
	$("#dialogoNumerico").parent().find('.ui-dialog-titlebar').find('button').remove();

	
	$("#cantidad_menos").click(function(){
		cambioUnidad("#cantidad_pedido", false);
	});
	
	$("#cantidad_mas").click(function(){
		cambioUnidad("#cantidad_pedido", true);
	});
	
	$("#venta_reiniciar").click(function(){
		reiniciar_pedido();
	});
	
	$("#venta_reiniciar_venta").click(function(){
		reiniciar_venta();
	});
	
	$("#imprimir_ultima_orden").click(function(){
		seguridad('imprimirUltimaOrden', function(){
			imprimirUltimaOrden();
		});
	});
	
	$("#reporte_diario").click(function(){
		seguridad('reporteDiario', function(){
			$.ajax(app.url + 'reporteDiario',{
				success : function(r){
					aviso(r);
					if (r.s == 'n'){
						return;
					}
				}
			});
		});
	});
	
	$("#cerrar_terminal").click(function(){
		if ($terminalDesactivado === true) return; 
		
		seguridad('cerrarTerminal', function(){
			$.ajax(app.url + 'cerrarTerminal',{
				success : function(r){
					aviso(r);
					if (r.s == 'n'){
						return;
					}
					
					_verificarTerminal(false);
				}
			});
			
		});
	});
	
	$("#pedido_para_llevar").click(function(){
		if (!listadoVenta.find("[data-id='parallevar']").length){
			listadoVenta.append($lista({
				'id': 'parallevar',
				'producto': 'PARA LLEVAR',
				'cantidad': 0,
				'precio':0,
				'tipo_producto': ''
			}));
		}
	});
	
	setTimeout(function(){
		if (!$.fullscreen.isFullScreen()) {
			$('html').fullscreen();
		}
	}, 2000);
	
	$("#pantalla_completa").click(function(){
		if ($.fullscreen.isFullScreen()) {
			$(this).html('Pantalla Completa');
			$.fullscreen.exit();
		}else{
			$(this).html('Salir de Pantalla Completa');
			$('html').fullscreen();
		}
	});
	
	$("#vender").live('click', function(){
        if ($("li", listadoVenta).length == 0){
        	return false;
        }
        
        $("#dialogoTipoCompra").modal('show');
	});
    
    $("#PagoPendiente").live('click', function(){
		$("#dialogoTipoSeccion").modal('show');
	});
    
    $(".btn-seccion", "#dialogoTipoSeccion").live('click', function(){
        var idSeccion = $(this).attr('data-valor');
		var textoSeccion = $(this).text();
        
        buscarUbicacion(idSeccion, textoSeccion);
	});
    
    $("#PagoProcesar").live('click', function(){
        $("#dialogoTipoCompra").modal('hide');
		$("#dialogoPago").modal('show');
	});
    
    /* SIN ACCIONES DESPUES DE VENTA */
    $(".btn-ubicacion", "#dialogoUbicacion").live('click', function(){
        var idUbic = $(this).attr('data-valor');
        
        if($(this).hasClass('btn-danger')){
            if(!confirm('Esta Totalmente Seguro que Desea Asignar esta Ubicacion? No se encuentra disponible.')){
                return;
            }
        }
        
		vender(idUbic, false);
	});
    
    /*
    Con acciones despues de venta
    $(".btn-ubicacion", "#dialogoUbicacion").live('click', function(){
        var idUbic = $(this).attr('data-valor');
        
        if($(this).hasClass('btn-danger')){
            if(!confirm('Esta Totalmente Seguro que Desea Asignar esta Ubicacion? No se encuentra disponible.')){
                return;
            }
        }
        
		vender(idUbic);
        
        setTimeout(function(){
    		imprimirPequenna(ordenActual);
    	}, 1000);
	});
    */
    
    /*
    Con Combo
    $("#asignarUbicacion").live('click', function(){
        if($('#ubicacionVenta').val() == ''){
            aviso('Debe Asignar la Ubicacion de la Orden', false);
            return false;
        }
        
		vender($('#ubicacionVenta').val());
        
        setTimeout(function(){
    		imprimirPequenna(ordenActual);
    	}, 1000);
	});
    */
    
    $("#agregar_orden").live('click', function(){
        buscarOrdenesPendientes();
	});
    
	setTimeout(function(){
		verificarTerminal();
	}, 5000); // 2 segundos
	
	//verificarTerminal();
	
	//$("body").disableSelection();
	
	setInterval(function(){
		verificarTerminal();
	}, 60000); // 10 minutos
	
	$("#vender").removeAttr('disabled');
    
    /*** VENTANA FORMA DE PAGO ***/
    $("#dialogoPago").parent().find('.ui-dialog-titlebar').find('button').remove();
    
    $(".modal-body .btn-pago", ventanaFormaPago).click(function(){
		var $_forma_pago = $(this).attr('data-valor');
		
		$forma_pago_pago = {};
		
		if ($_forma_pago === 'cancelar'){
			return;
		}
		
		$forma_pago_pago[$_forma_pago] = total;
		
		if ($procesado === false){
			$procesado = true;
			vender(0, $forma_pago_pago);
            
			/*setTimeout(function(){
				procesar(ordenActual, $forma_pago_pago);
				reiniciar();
			}, 3000);*/
		}
	}).dblclick(function(){
		if (!$("#facturapersonalizadacheck").prop('checked') && $forma_pago_pago[1] !== undefined){
			$comanda = true;
		}
		
		$("#dialogoPago").modal('hide');
	});
    
    ventanaFormaPago.on('show.bs.modal', function(e){
		var $_total = total;
		
		$("#facturapersonalizadacheck").prop('checked', false).triggerHandler('click');
		$("#facturapersonalizadacheck").parent().css('display', 'inline');
		
		$("#cedula, #nombre, #apellido, #direccion, #telefono", ventanaFormaPago).val('');
		
		$("#pagodetalladocheck").prop('checked', false).triggerHandler('click');
		$(".objControl", "#divFormaPagoDetalle").val('');
		
		$("#btnPagoDetallado").attr('disabled', 'disabled');
		$("#divTotalPagarDetalle")
        .removeClass('text-success text-danger')
        .addClass('text-danger')
		.html("Pago: 0<br />Factura: " + $_total);
	});
    
    $("#facturapersonalizadacheck").click(function(){
		if ($(this).prop('checked')){
			$("#divDatosCliente").css('display', 'block');
		}else{
			$("#divDatosCliente").css('display', 'none');
		}
	});
	
	$("#pagodetalladocheck").click(function(){
		if ($(this).prop('checked')){
			$("#divFormaPago").css('display', 'none');
			$("#divFormaPagoDetalle").css('display', 'block');
		}else{
			$("#divFormaPago").css('display', 'block');
			$("#divFormaPagoDetalle").css('display', 'none');
		}
	});
    
    $("#cedula", ventanaFormaPago).blur(function(){
		$(this).val($(this).val().toUpperCase());
		
		$.ajax(app.url + 'buscarCliente/' + $(this).val(), {
			success: function(r){
				if (r.s === 's'){
					$("#nombre", ventanaFormaPago).val(r.nombre);
					$("#apellido", ventanaFormaPago).val(r.apellido);
					$("#direccion", ventanaFormaPago).val(r.direccion);
					$("#telefono", ventanaFormaPago).val(r.telefono);
				}
			}
		});
	}).keypress(function(e){
		if (e.which == 13) $(this).trigger('blur');
	});
    
    $(".btn-calcular", "#divFormaPagoDetalle").click(function(){
		var total_formas_pago = 0,
		$input = $(this).parent().parent().find('input'),
		$_total = total;
		
		$input.val('');
		
		$(".objControl", "#divFormaPagoDetalle").each(function(){
			var t = $(this), v = parseFloat(t.val().replace(/\,+/g, '.'));
			
			if (isNaN(v)){
				v = 0;
			}
			
			total_formas_pago += v;
		});
		
		var totalInput = round($_total - total_formas_pago, 2);
		
		if (totalInput < 0){
			totalInput = 0;
		}
		
		$input.val(totalInput).trigger('keyup');
	});
	
	$(".objControl", "#divFormaPagoDetalle").blur(function(){
		var t = $(this), v = parseFloat(t.val().replace(/\,+/g, '.'));
		t.val(isNaN(v) ? '' : round(v, 2));
		t.trigger('keyup');
	}).keyup(function(){
		var total_formas_pago = 0,
		$formas = {},
		$_total = total;
		
		$(".objControl", "#divFormaPagoDetalle").each(function(){
			var t = $(this), v = parseFloat(t.val().replace(/\,+/g, '.'));
			
			if (isNaN(v)){
				v = 0;
			}
			
			$formas[t.attr('data-valor')] = v;
			total_formas_pago += v;
		});
		
		var formulaPago = "Pago: " + total_formas_pago + "<br />Factura: " + $_total;
		
		if (total_formas_pago == $_total){
			$("#btnPagoDetallado").removeAttr('disabled');
			$("#divTotalPagarDetalle").removeClass('text-success text-danger').addClass('text-success').html(formulaPago);
		}else{
			$("#btnPagoDetallado").attr('disabled', 'disabled');
			$("#divTotalPagarDetalle").removeClass('text-success text-danger').addClass('text-danger').html(formulaPago);
		}
	}).numeric({
		allow : ',.' 
	});
	
	$("#btnPagoDetallado").click(function(){
		var total_formas_pago = 0,
		$formas = {},
		$_total = total;
		
		$(".objControl", "#divFormaPagoDetalle").each(function(){
			var t = $(this), v = parseFloat(t.val().replace(/\,+/g, '.'));
			
			if (isNaN(v)){
				v = 0;
			}
			
			$formas[t.attr('data-valor')] = v;
			total_formas_pago += v;
		});
		
		if (total_formas_pago != $_total){
			alert('La Suma de los Pagos no Coinciden con el Total');
			return;
		}
		
		vender(0, $formas);
            
		/*setTimeout(function(){
			procesar(ordenActual, $formas);
			reiniciar();
		}, 1000);*/
        
		/*procesar(ordenActual, $formas);
		reiniciar();*/
	});
    
    $('.listaAgregarOrden').live('click', function(){
        agregarProductosOrden($(this).attr('orden'));        
    });
});

function cambioUnidad($campo, $incremento){
	var $campo = $($campo), $cantidad, valor;
	
	if (ventaporkilo){
		$cantidad = 0.5;
		valor = parseFloat($("#cantidad_pedido").val());
	}else{
		$cantidad = 1;
		valor = parseInt($("#cantidad_pedido").val());
	}
	
	if ($incremento){
		valor += $cantidad;
	}else{
		valor -= $cantidad;
	}
	
	//console.log(valor);
	
	if (valor <= 0){
		valor = 0.25;
	}
	
	if (valor === 0.75){
		valor = 0.5;
	}
	
	valor = round(valor, 2);
	
	if (valor === parseInt(valor) && ventaporkilo){
		valor += '.0';
	}
	
	$("#cantidad_pedido").val(valor + (ventaporkilo ? " Kgr" : ''));
}

function verificarTerminal(){
	$.ajax(app.url + 'verificarTerminal', {
		success : function(r){
			_verificarTerminal(r);
		}
	});
}

function _verificarTerminal(r){
	if (r === false){
		if ($terminalDesactivado === true){
			return;
		}
		
		reiniciar_venta();
		//$('li', listaProductos).unbind("click");
		$("#ubicacion").html("<h2>CAJA CERRADA...</h2>");
		
		$("#vender").attr('disabled', 'disabled');
		$terminalDesactivado = true;
		
		if (pantallaSegundaria !== undefined){
			pantallaSegundaria.reiniciar(); // para cerrar la ventana
		}
	}else{
		if ($terminalDesactivado === false){
			return;
		}
		
		$("#ubicacion").html("");
		$("#vender").removeAttr('disabled');
		$terminalDesactivado = false;
	}
}

function vender(valorUbicacion, tipoFormaPago){
	if (!($("li", listadoVenta).length)){
		return;
	}
	
	$("#vender").attr('disabled', 'disabled');
	
	var datos = [], parallevar = 0;
	$("li", listadoVenta).each(function(i){
		var t = $(this), d = {
			'id_producto'	: t.attr("data-id"),
			'producto'		: t.attr("data-producto"),
			'cantidad'		: t.attr("data-cantidad"),
			'precio'		: t.attr("data-precio")
		};
		
		if (d.id_producto == 'parallevar'){
			parallevar = 1;
		}else{
			datos.push(d);
		}
	});
	
	$.ajax(app.url + 'guardar',{
	//$.ajax("http://localhost/palmerinnirrrr/pago/" + 'guardar',{
		data : {
			'ventas' 		: kendo.stringify(datos),
			'parallevar'	: parallevar,
            'ubicacion'     : valorUbicacion
		},
		success : function(r){
            aviso(r);
    			
			if (r.caja_cerrada){
				_verificarTerminal(false);
				return;
			}
			
			if (r.s == 'n'){
				return;
			}
			
            reiniciar_venta();
			ultimaOrden = r.orden;
            ordenActual = r.orden;
            $("#dialogoTipoCompra, #dialogoUbicacion").modal('hide');
            
            if(valorUbicacion != 0){
                setTimeout(function(){
            		imprimirPequenna(ordenActual);
            	}, 1000);
                
            }else{
                setTimeout(function(){
        			procesar(ordenActual, tipoFormaPago);
        			reiniciar();
        		}, 1000);
            }
		}
	});
}

function agregar_elemento_lista(datos){
	var productoListaExistente = listadoVenta.find("[data-id='" + datos.id + "'][data-tipo-producto='" + datos.tipo_producto + "']");

	datos.cantidad = ventaporkilo ? parseFloat(datos.cantidad) : parseInt(datos.cantidad);
	console.log(datos);
	if (productoListaExistente.length){
		var cantidadExistente = parseFloat(productoListaExistente.find('.cantidad').html());
		datos.cantidad = round(datos.cantidad + cantidadExistente, 2);
		
		productoListaExistente.find('.cantidad').html(datos.cantidad);
		productoListaExistente.attr('data-cantidad', datos.cantidad);
	}else{
		listadoVenta.append($lista(datos));
	}
	
	if (pantallaSegundaria !== undefined){
		pantallaSegundaria.agregar_elemento_lista(datos);
	}
	
	reiniciar_pedido();
}

function imprimirUltimaOrden(){
	if ($terminalDesactivado === true) return;
	//console.log('imprimirUltimaOrden');
	$.ajax(app.url + 'imprimir/' + ultimaOrden, {
		success : function(r){
			
		}
	});
	//abrirVentana(app.url + 'imprimir/' + ultimaOrden);
}

function reiniciar_pedido(){
	if ($terminalDesactivado === true) return;
	ubicacion.text('');
	
	$('li', listaProductos).css('display', 'none');
	$('li[data-padre=0][data-comando="producto"]', listaProductos).css('display', 'block');
	
	$("#cantidad_pedido").val('1');
	
	idProducto = 0;
	nombreProducto = '';
	$forma_pago = 0;
	
	total = 0;
	
	ventaporkilo = false;
	
	calcularTotal();
}

function reiniciar_venta(){
	if ($terminalDesactivado === true) return;
	
	reiniciar_pedido();
	$("#totalVenta span").html(kendo.toString(0, "c", "es-VE"));
	listadoVenta.find('li').remove();
	
	$("#vender").removeAttr('disabled');
	
	if (pantallaSegundaria !== undefined){
		pantallaSegundaria.reiniciar();
	}
}

function calcularTotal(){
	total = 0;
	listadoVenta.find('li').each(function(){
		total += parseFloat($(this).attr('data-precio')) * parseFloat($(this).attr('data-cantidad'));
	});
	
	$("#totalVenta span").html(kendo.toString(total, "c", "es-VE"));
	if (pantallaSegundaria !== undefined){
		pantallaSegundaria.total(kendo.toString(total, "c", "es-VE"));
	}
}

function seguridad(concepto, $callback){
	$("#dialogoNumerico").dialog('option', 'close', function(){
		if ($(this).data('valor') === '') return;
		
		$.ajax(app.url + 'seguridad',{
			data:{
				'concepto' : concepto,
				'clave' : $("#dialogoNumerico").data('valor')
			},
			success : function(r){
				if (r.s == 'n'){
					aviso(r);
					return;
				}
				
				$callback(r);
			}
		});
	}).dialog('open');
}

function abrirVentana(url){
	var w = window.open(url, "ventana" + rand(), "");
	if (w && !w.closed)
		w.focus();
	else
		aviso("No Se Pudo Abrir la Nueva Ventana, deshabilite \"bloquear ventana\"", false);
}

/*** PROCESAR PAGO ***/
function procesar(id, $forma_pago_pago){
	console.log($forma_pago_pago);
	//return false;
	if ($("#facturapersonalizadacheck").prop('checked')){
		if ($("#cedula", ventanaFormaPago).val().trim() === ''){
			alert('El campo Cedula no puede estar vacio');
			return false;
		}
		
		if ($("#nombre", ventanaFormaPago).val().trim() === ''){
			alert('El campo Nombre no puede estar vacio');
			return false;
		}
	}

    $.ajax(app.base + "pago/procesarComandaFiscal/" + id, {
		data:{
			//'parallevar'	: parallevar,
			'forma_pago' 	: $forma_pago_pago,
			'comanda' 		: $comanda,
			'cedula' 		: $("#cedula", ventanaFormaPago).val(), 
			'nombre' 		: $("#nombre", ventanaFormaPago).val(),
			'apellido' 		: $("#apellido", ventanaFormaPago).val(),
			'direccion' 	: $("#direccion", ventanaFormaPago).val(),
			'telefono' 		: $("#telefono", ventanaFormaPago).val(),
			'total' 		: total
		},
		success: function(r){
			$procesado = false;
            
			if (r.s === 's'){
				$("#correlativo")
				.css({
					'opacity' : 0,
					'display' : 'block'
				})
				.animate({
					'opacity' : 1
				}, 500, function() {
				    setTimeout(function(){
            			$("#correlativo")
    				    .css({
        					'opacity' : 0,
        					'display' : 'none'
        				})
    				    .animate({
    					   'opacity' : 0
    				    }, 3000);
            		}, 1000);
                }).html(r.correlativo);
				
				$("#btnPagoDetallado").attr('disabled', 'disabled');
				
				ventanaFormaPago.modal('hide');
				idUltimaOrden = id;
			}
			
			aviso(r);
		}
	});
}

function reiniciar(){
	$comanda = false;
	total = 0;
	extra = 0;
	
}

function buscarOrdenesPendientes(){
    if (!($("li", listadoVenta).length)){
		return;
	}
    
    $.ajax(app.url + 'buscarOrdenesPendientes', {
		data : { },
		success : function(r){
            aviso(r);
			
			if (r.s == 'n'){
				return;
			}
			
            ventanaAgregarOrden.find('.modal-body').html('');
            var divOrden = '';
            
            for(i = 0; i < r.length; i++){
				divOrden = listaAgregarOrden.clone().attr('orden', r[i].id).html('<b>Orden N&deg; ' + r[i].id + '</b><br/><u>Ubicaci&oacute;n:</u> ' + r[i].seccion_venta + ' / ' + r[i].ubicacion_venta);
                ventanaAgregarOrden.find('.modal-body').append(divOrden);
			}
            
            ventanaAgregarOrden.modal('show');
		}
	});
}

function agregarProductosOrden(idOrden){
    if (!idOrden){
		return;
	}
    
    $("#vender").attr('disabled', 'disabled');
	
	var datos = [], parallevar = 0;
    
	$("li", listadoVenta).each(function(i){
		var t = $(this), d = {
			'id_producto'	: t.attr("data-id"),
			'producto'		: t.attr("data-producto"),
			'cantidad'		: t.attr("data-cantidad"),
			'precio'		: t.attr("data-precio")
		};
		
		if (d.id_producto == 'parallevar'){
			parallevar = 1;
		}else{
			datos.push(d);
		}
	});
	
	$.ajax(app.url + 'agregarProductosOrden',{
        data : {
			'ventas' 		: kendo.stringify(datos),
			'parallevar'	: parallevar,
            'idOrden'       : idOrden
		},
		success : function(r){
            aviso(r);
    			
			if (r.caja_cerrada){
				_verificarTerminal(false);
				return;
			}
			
			if (r.s == 'n'){
				return;
			}
			
            reiniciar_venta();
			ultimaOrden = r.orden;
            ordenActual = r.orden;
            ventanaAgregarOrden.modal('hide');
            //$('#ubicacionVenta').val('');
            
			imprimirPequennaAgregar(r.orden);
		}
	});
}

function imprimirPequenna(id){
    if(!id)
        return false;
        
    $.ajax(app.base + "pago/imprimirPequenna/" + id, {
		data:{
			'forma_pago' 	: $forma_pago,
			'comanda' 		: $comanda,
			'cedula' 		: $("#cedula", ventanaFormaPago).val(), 
			'nombre' 		: $("#nombre", ventanaFormaPago).val(),
			'apellido' 		: $("#apellido", ventanaFormaPago).val(),
			'direccion' 	: $("#direccion", ventanaFormaPago).val(),
			'telefono' 		: $("#telefono", ventanaFormaPago).val(),
			'total' 		: total
		},
		success: function(r){
			$procesado = false;
            
			if (r.s === 's'){
				
				$("#correlativo")
				.css({
					'opacity' : 0,
					'display' : 'block'
				})
				.animate({
					'opacity' : 1
				}, 500, function() {
				    setTimeout(function(){
            			$("#correlativo")
    				    .css({
        					'opacity' : 0,
        					'display' : 'none'
        				})
    				    .animate({
    					   'opacity' : 0
    				    }, 3000);
            		}, 1000);
                }).html(r.correlativo);
				
				$("#btnPagoDetallado").attr('disabled', 'disabled');
				
				ventanaFormaPago.modal('hide');
                ventanaFormaPago.modal('hide');
				idUltimaOrden = id;
			}
			
			aviso(r);
		}
	});
}

function imprimirPequennaAgregar(id){
    if(!id)
        return false;
        
    $.ajax(app.base + "pago/imprimirPequennaAgregar/" + id, {
		data:{
			'forma_pago' 	: $forma_pago,
			'comanda' 		: $comanda,
			'cedula' 		: $("#cedula", ventanaFormaPago).val(), 
			'nombre' 		: $("#nombre", ventanaFormaPago).val(),
			'apellido' 		: $("#apellido", ventanaFormaPago).val(),
			'direccion' 	: $("#direccion", ventanaFormaPago).val(),
			'telefono' 		: $("#telefono", ventanaFormaPago).val(),
			'total' 		: total
		},
		success: function(r){
			$procesado = false;
            
			if (r.s === 's'){
				
				$("#correlativo")
				.css({
					'opacity' : 0,
					'display' : 'block'
				})
				.animate({
					'opacity' : 1
				}, 500, function() {
				    setTimeout(function(){
            			$("#correlativo")
    				    .css({
        					'opacity' : 0,
        					'display' : 'none'
        				})
    				    .animate({
    					   'opacity' : 0
    				    }, 3000);
            		}, 1000);
                }).html(r.correlativo);
				
				$("#btnPagoDetallado").attr('disabled', 'disabled');
				
				ventanaFormaPago.modal('hide');
				idUltimaOrden = id;
			}
			
			aviso(r);
		}
	});
}

function buscarUbicacion(idSec, textSec){
    if (idSec == 0){
		return;
	}
    
    $.ajax(app.url + 'buscarUbicacion', {
		data : { id_seccion: idSec},
		success : function(r){
            aviso(r.msj);
			
			if (r.s == 'n'){
				return;
			}
			
            $("#dialogoUbicacion")
                .find('.modal-body')
                .html('<div style="float:left; width:100%; font-size:20px; font-weight: bold; color: #2d722d;"><u>Secci&oacute;n:</u> '+textSec+'</div>')
                .append(r.ubicaciones);
                
            $("#dialogoTipoSeccion").modal('hide');
            $("#dialogoUbicacion").modal('show');
		}
	});
}
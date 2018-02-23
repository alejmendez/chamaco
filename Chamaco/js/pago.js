var tiempoConsulta = 5000,
$lista,
$listaOrdenes,
listadoOrden,
$plantillaBusqueda,
ordenActual = 0,
total = 0,
extra = 0,
ordenes = {},
$procesado = false,
$comanda = false,
$notaCredito = false,
$pago_encargo = false,

ventanaEncargo, ventanaFormaPago,
orden,
$encargo = false,
$forma_pago,
idUltimaOrden = 0,
fecha_actual = new Date();

$.ajaxSetup({
	error: function(x, t, m) {
		if (t == 'timeout'){
			aviso('No se Puedo Realizar la Venta, El Servidor Tardo Mucho en Responder', false);
		}else{
			aviso('Se Genero un Advertencia');
		}
	}
});

app.ini(function(){
	$("#cargando").remove();
	listado = $("ul", "#detalleOrden");
	listadoOrden = $("ul", "#listadoOrden");

	$lista = kendo.template($("#plantillaLista").html());
	$listaOrdenes = kendo.template($("#plantillaListaOrdenes").html());
	$plantillaBusqueda = kendo.template($("#plantillaBusqueda").html());


	ventanaEncargo = $('#datosEncargo').clone();
	ventanaFormaPago = $('#dialogoPago').clone();
	ventanaNotaCredito = $('#dialogNotaCredito').clone();
	ventanaBusqueda = $('#dialogBusqueda').clone();
	ventanaBusquedaInformacion = $('#dialogBusquedaInformacion').clone();
	vemtanareportezmensual = $("#dialogreportezmensual").clone();


	$('#datosEncargo, #dialogoPago, #dialogNotaCredito, #dialogBusquedaInformacion, #dialogreportezmensual').remove();
	$('body')
	.append(ventanaEncargo)
	.append(ventanaNotaCredito)
	.append(ventanaBusqueda)
	.append(ventanaBusquedaInformacion)
	.append(vemtanareportezmensual)
	.append(ventanaFormaPago);

	$(window).resize(function(){
		var $pos = $("#contPagina").position();
		$("#detalleOrden").css('left', $(this).width() - $("#detalleOrden").width() - $pos.left - 20);
		/*position: fixed;
	    right: 213px;
	    top: 64px;*/
	}).resize();

	$("#procesar").click(function(){

	});

	$("#anular").click(function(){
		anular(ordenActual);
	});

	listadoOrden.on('click', 'li', function(){
		$('li', listadoOrden).removeClass('btn-success').removeClass('btn-danger');

		orden = $(this).attr('data-id');
		consultaOrden(orden);
	});

	$(".modal-body .btn-pago", ventanaFormaPago).click(function(){
		var $_forma_pago = $(this).attr('data-valor');

		$forma_pago = {};

		if ($_forma_pago === 'cancelar'){
			return;
		}

		$forma_pago[$_forma_pago] = total;

		if ($encargo){
			$forma_pago[$_forma_pago] = $("#pago").data('handler').value();

			encargo(ordenActual, $forma_pago);
			reiniciar();
			return;
		}

		if ($notaCredito){
			notaCredito($forma_pago);
			reiniciar();
			return;
		}

		if ($pago_encargo){
			pagar($forma_pago);
			reiniciar();
			return;
		}

		if ($procesado === false){
			$procesado = true;

			setTimeout(function(){
				procesar(ordenActual, $forma_pago);
				reiniciar();
			}, 500);
		}
	}).dblclick(function(){
		if (!$("#facturapersonalizadacheck").prop('checked') && $forma_pago[1] !== undefined){
			$comanda = true;
		}

		$("#dialogoPago").modal('hide');
	});

	$("#dialogoPago").parent().find('.ui-dialog-titlebar').find('button').remove();

	ventanaFormaPago.on('show.bs.modal', function(e){
		var $_total = $encargo == false ? total : $("#pago", ventanaEncargo).data('handler').value();

		$("#facturapersonalizadacheck").prop('checked', false).triggerHandler('click');
		$("#facturapersonalizadacheck").parent().css('display', 'inline');

		$("#cedula", ventanaFormaPago).val('');
		$("#nombre", ventanaFormaPago).val('');
		$("#apellido", ventanaFormaPago).val('');
		$("#direccion", ventanaFormaPago).val('');
		$("#telefono", ventanaFormaPago).val('');

		$("#pagodetalladocheck").prop('checked', false).triggerHandler('click');
		$(".objControl", "#divFormaPagoDetalle").val('');

		$("#btnPagoDetallado").attr('disabled', 'disabled');
		$("#divTotalPagarDetalle").removeClass('text-success text-danger').addClass('text-danger')
		.html("Pago: 0<br />Factura: " + $_total);
	});

	ventanaEncargo.on('show.bs.modal', function(e){
		var modal = $('.modal-body', ventanaEncargo);

		$('input', modal).val('');
		$('#pago', modal).data('handler').value(total);
		$('#extra', modal).data('handler').value(0);
		$('#observacion', modal).val('');

		$("#fecha_entrega", ventanaEncargo).val(fecha);

		var valor = 0;

		$("#total", ventanaEncargo).html('Total Factura: ' + total + '<br />' +
			'Total [Total Factura + Extra]: ' + (total + valor) + ' (' + total + ' + ' + valor + ')<br />' +
			'Cuenta por Cobrar: ' + (total - valor) +  '<br />' +
			'Total a Cobrar: <b>' + (total + valor) + '</b>');
	});

	ventanaNotaCredito.on('show.bs.modal', function(e){
		$("#contenedorDetalleNotaCredito").html('');
		$("#agregarDetalleNotaCredito").trigger('click');
	});

	$("#agregarDetalleNotaCredito").click(function(){
		var $detalle = $("#clonDetalleNotaCredito").clone(true).css('display', 'block').removeAttr('id');
		$("#contenedorDetalleNotaCredito").append($detalle);

		$detalle.find('.nc_precio, .nc_cantidad').numeric({
			allow : ',.'
		}).blur(function(){
			var v = floatString($(this).val()),
			totalnc = 0;
			$(this).val(v);

			$(".nc_precio", "#contenedorDetalleNotaCredito").each(function(){
				var t = $(this),
				v = floatString(t.val()),
				c = floatString(t.parent().find('.nc_cantidad').val());

				totalnc += precio(v, c);
			});

			totalnc *= 1.12;
			total = round(totalnc, 2);

			$("#totalNotaCredito").html('Total de la Nota de Credito:' + total);
		});
	});

	$("#formEncargo").validate();

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

	$("#guardarEncargo", ventanaEncargo).click(function(){
		$validado = $("#formEncargo").valid();
		if ($validado === false){
			return;
		}

		$encargo = true;
		extra = parseFloat($("#extra", ventanaEncargo).val());
		$("#dialogoPago").modal('show');

		$("#facturapersonalizadacheck").parent().css('display', 'none');
	});

	$("#buscar").keypress(function(e){
		if (e.which == 13) $("#btn-buscar").trigger('click');
	});

	$("#btn-buscar").click(function(){
		ventanaBusqueda.find('.modal-body').html('<center><h2>Buscando <i class="fa fa-refresh fa-spin"></i></h2></center>');
		ventanaBusqueda.modal('show');

		$.ajax(app.url + 'buscar/' + $("#buscar").val(), {
			success: function(r){
				if (r.s == 'n'){
					ventanaBusqueda.find('.modal-body').html('<center><h2>' + r.msj + '</h2></center>');
					return;
				}

				for(i = 0; i < r.registros.length; i++){
					r.registros[i].data = JSON.stringify(r.registros[i]);
				}

				ventanaBusqueda.find('.modal-body').html($plantillaBusqueda(r));
			}
		});
	});

	ventanaBusqueda.on('click', '.modal-body button', function(){
		var t = $(this),
		accion = t.attr('data-accion'),
		data = JSON.parse(t.attr('data'));

		$("table tr", ventanaBusqueda).removeClass('info');

		t.parent().parent().addClass('info');

		if (accion === 'anular'){
			anular(data.id);
		}else if (accion === 'pagar'){
			//pagar(data.id, data.pendiente);
			ordenActual = data.id;
			total = data.pendiente;

			$pago_encargo = true;

			$("#facturapersonalizadacheck").parent().css('display', 'none');
			ventanaFormaPago.modal('show');
		}else if (accion === 'entregar'){
			entregar(data.id);
		}else if (accion === 'informacion'){
			informacion(data.id);
		}
	});

	$("#cedula", ventanaEncargo).blur(function(){
		$(this).val($(this).val().toUpperCase());

		$.ajax(app.url + 'buscarCliente/' + $(this).val(), {
			success: function(r){
				if (r.s === 's'){
					$("#nombre", ventanaEncargo).val(r.nombre);
					$("#apellido", ventanaEncargo).val(r.apellido);
					$("#direccion", ventanaEncargo).val(r.direccion);
					$("#telefono", ventanaEncargo).val(r.telefono);

					$("#idCliente", ventanaEncargo).val(r.id);
				}
			}
		});
	}).keypress(function(e){
		if (e.which == 13) $(this).trigger('blur');
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

	$("#cedula", ventanaNotaCredito).blur(function(){
		$(this).val($(this).val().toUpperCase());

		$.ajax(app.url + 'buscarCliente/' + $(this).val(), {
			success: function(r){
				if (r.s === 's'){
					$("#nombre", ventanaNotaCredito).val(r.nombre);
					$("#apellido", ventanaNotaCredito).val(r.apellido);
					$("#direccion", ventanaNotaCredito).val(r.direccion);
					$("#telefono", ventanaNotaCredito).val(r.telefono);
				}
			}
		});
	}).keypress(function(e){
		if (e.which == 13) $(this).trigger('blur');
	});

	var $allowTimes = [], i, j;

	for (i = 8; i <= 24; i++){
		for (j = 0; j <= 3; j++){
			$allowTimes.push(i + ':' + (j * 15));
		}
	}

	//console.log($allowTimes);

	$("#fecha_entrega", ventanaEncargo).datetimepicker({
		value: fecha,
		format: 'd/m/Y h:i a',
		minDate: fecha_actual,
		//minTime: '08:00',
		allowTimes:[
			"08:00", "08:15", "08:30", "08:45", "09:00", "09:15", "09:30", "09:45",
			"10:00", "10:15", "10:30", "10:45", "11:00", "11:15", "11:30", "11:45",
			"12:00", "12:15", "12:30", "12:45", "13:00", "13:15", "13:30", "13:45",
			"14:00", "14:15", "14:30", "14:45", "15:00", "15:15", "15:30", "15:45",
			"16:00", "16:15", "16:30", "16:45", "17:00", "17:15", "17:30", "17:45",
			"18:00", "18:15", "18:30", "18:45", "19:00", "19:15", "19:30", "19:45",
			"20:00", "20:15", "20:30", "20:45", "21:00", "21:15", "21:30", "21:45",
			"22:00", "22:15", "22:30", "22:45", "23:00", "23:15", "23:30", "23:45",
			"24:00", "24:15", "24:30", "24:45"
		]
	});

	$("#nombre, #apellido", ventanaEncargo).blur(function(){
		$(this).val(ucwords($.trim($(this).val()).toLowerCase()));
	});

	$("#pago", ventanaEncargo).css({
		'margin': 0,
		'width' : 569
	}).kendoNumericTextBox({
		min: 0,
		change : function(){
			var valor = $("#extra", ventanaEncargo).data('handler').value(),
			pago = this.value();

			$("#total", ventanaEncargo).html('Total Factura: ' + total + '<br />' +
			'Total [Total Factura + Extra]: ' + (total + valor) + ' (' + total + ' + ' + valor + ')<br />' +
			'Cuenta por Cobrar: ' + (total - pago + valor) +  '<br />' +
			'Total a Cobrar: <b>' + (pago) + '</b>');
		}
	});

	$("#extra", ventanaEncargo).css({
		'margin': 0,
		'width' : 569
	}).kendoNumericTextBox({
		min: 0,
		change : function(){
			var valor = this.value(),
			pago = $("#pago", ventanaEncargo).data('handler').value();

			if (valor > 0){
				$("#observacion").attr('required', '');
			}else{
				$("#observacion").removeAttr('required');
			}

			$("#total", ventanaEncargo).html('Total Factura: ' + total + '<br />' +
			'Total [Total Factura + Extra]: ' + (total + valor) + ' (' + total + ' + ' + valor + ')<br />' +
			'Cuenta por Cobrar: ' + (total - pago + valor) +  '<br />' +
			'Total a Cobrar: ' + (pago));
		}
	});

	$("#reportex").click(function(){
		$.ajax(app.url + 'reportex', {
			success: function(r){
				aviso(r);
			}
		});
	});

	$("#reportez").click(function(){
		var c = confirm("Esta Totalmente Seguro que Desea Generar el Reporte Z y Cerrar Todos los Terminales?");
		if (!c){
			return;
		}

		/**/
		$.ajax(app.url + 'reportez', {
			success: function(r){
				aviso(r);
			}
		});
	});

	$("#cerrarCajas").click(function(){
		var c = confirm("Esta Totalmente Seguro que Desea Cerrar todas las cajas?");
		if (!c){
			return;
		}

		/**/
		$.ajax(app.url + 'cerrarcajas', {
			success: function(r){
				aviso(r);
			}
		});
	});

	$(".btn-calcular", "#divFormaPagoDetalle").click(function(){
		var total_formas_pago = 0,
		$input = $(this).parent().parent().find('input'),
		$_total = $encargo == false ? total : $("#pago", ventanaEncargo).data('handler').value();

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
		$_total = $encargo == false ? total : $("#pago", ventanaEncargo).data('handler').value();

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
		$_total = $encargo == false ? total : $("#pago", ventanaEncargo).data('handler').value();

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

		if ($encargo){
			encargo(ordenActual, $formas);
		}else if ($notaCredito){
			notaCredito($formas);
		}else if ($pago_encargo){
			pagar($formas);
		}else{
			procesar(ordenActual, $formas);
		}

		reiniciar();
	});

	$("#imprimirUltimaFactura").click(function(){
		var id = 0;
		$.ajax(app.url + 'getUltimaOrden/', {
			success: function(r){
				id = r.id;

				if (!confirm('Esta Totalmente Seguro que Desea Imprimir La Factura "' + id + '" de total? Esto no se Registrara en el Sistema.')){
					return;
				}

				$.ajax(app.url + 'imprimirOrden/' + id, {
					success: function(r){
						aviso(r);
					}
				});
			}
		});
	});

	$("#guardarNotaCredito").click(function(){
		$validado = $("#formNotaCredito").valid();
		if ($validado === false){
			return;
		}

		$notaCredito = true;
		ventanaFormaPago.modal('show');
	});

	$("#contenedorDetalleNotaCredito").on('click', '.eliminarDetalleNotaCredito', function(){
		console.log($(".clonDetalleNotaCredito", "#contenedorDetalleNotaCredito").length);
		if ($(".clonDetalleNotaCredito", "#contenedorDetalleNotaCredito").length > 1)
			$(this).parent().remove();
	});

	$("#generarReporteZMensual").click(function(){
		$.ajax(app.url + 'reportezmensual/' + $("#mes").val() + '/' + $("#anno").val(), {
			success: function(r){
				if (r.s === 's'){
					vemtanareportezmensual.modal('hide');
				}

				aviso(r);
			}
		});
	});

	setInterval(function(){
		consulta();
	}, tiempoConsulta);

	consulta();
});

function reiniciar(){
	$comanda = false;
	$encargo = false;
	$notaCredito = false;
	$pago_encargo = false;
	total = 0;
	extra = 0;

	//$("#facturapersonalizadacheck").parent().css('display', 'inline');
}

function consulta(){
	$.ajax(app.url + 'consulta', {
		success: function(r){
			for(var i in r){
				if (!$("[data-id=" + r[i].id + "]", listadoOrden).length){
					ordenes[r[i].id] = r[i];
					listadoOrden.append($listaOrdenes(r[i]));
				}
			}

			$("li", listadoOrden).each(function(){
				var t = $(this), id = parseInt(t.attr('data-id')), b = false;


				for(var i in r){
					if (parseInt(r[i].id) == id){
						b = true;
					}
				}

				if (!b){
					eliminarOrden(id);
				}
			});
		}
	});
}

function consultaOrden(id){
	//listado.html('');
	ordenActual = id;
	$("#detalleOrden").css('display', 'block');

	$.ajax(app.url + 'consultaOrden/' + id, {
		success: function(r){
			var html = '', detalle = r.detalle, ordenConsulta = r.orden;

			reiniciar();

			if (ordenConsulta.bloqueo != 0){
				$("#listadoOrden li[data-id='" + ordenActual + "']").removeClass('btn-success').addClass('btn-danger');
				listado.html('Esta Orden ya esta en consulta, intente mas tarde');

				$("#procesar, #anular, #encargo").css('display', 'none');

				return;
			}

			$("#listadoOrden li[data-id='" + ordenActual + "']").removeClass('btn-danger').addClass('btn-success');
			$("#procesar, #anular, #encargo").css('display', 'block');

			for(var i in detalle){
				html += $lista(detalle[i]);
				total += precio(detalle[i].precio, detalle[i].cantidad);
			}

			total = round(total * 1.12, 2);

			html += "<li class='total'>Total: " + number_format(total, 2, ',', '.') + " Bsf</li>";

			listado.html(html);
		}
	});
}

function procesar(id, $forma_pago){
	if ($("#facturapersonalizadacheck").prop('checked')){
		if ($("#cedula", ventanaFormaPago).val().trim() === ''){
			alert('El campo Cedula no puede estar vacio');
			return false;
		}

		if ($("#nombre", ventanaFormaPago).val().trim() === ''){
			alert('El campo Cedula no puede estar vacio');
			return false;
		}
	}

	//$.ajax(app.url + 'procesar/' + id, {
    $.ajax(app.url + 'procesarFiscalCom/' + id, {
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
				eliminarOrden(id);

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

				//ventanaEncargo.modal('hide');
				ventanaFormaPago.modal('hide');
				idUltimaOrden = id;
			}

			aviso(r);
		}
	});
}

function notaCredito($formas){
	ventanaNotaCredito.modal('hide');

	var datosDetalle = [];

	$(".nc_precio", "#contenedorDetalleNotaCredito").each(function(){
		var t = $(this),
		v = floatString(t.val()),
		c = floatString(t.parent().find('.nc_cantidad').val());

		datosDetalle.push({
			'precio' : v,
			'cantidad' : c,
			'texto' : t.parent().find('.nc_texto').val()
		});
	});

	$.ajax(app.url + 'notaCredito', {
		data : {
			'cedula' 		: $("#cedula", ventanaNotaCredito).val(),
			'nombre' 		: $("#nombre", ventanaNotaCredito).val(),
			'apellido' 		: $("#apellido", ventanaNotaCredito).val(),
			'direccion' 	: $("#direccion", ventanaNotaCredito).val(),
			'telefono' 		: $("#telefono", ventanaNotaCredito).val(),
			'detalle' 		: datosDetalle,
			'forma_pago' 	: $formas,
		},
		success: function(r){
			$procesado = false;
			if (r.s === 's'){
				ventanaNotaCredito.modal('hide');
				ventanaFormaPago.modal('hide');
			}

			aviso(r);
		}
	});
}

function encargo(id, $forma_pago){
	$.ajax(app.url + 'encargo', {
		data : {
			'orden'			: id,

			'cedula' 		: $("#cedula", ventanaEncargo).val(),
			'nombre' 		: $("#nombre", ventanaEncargo).val(),
			'apellido' 		: $("#apellido", ventanaEncargo).val(),
			'direccion' 	: $("#direccion", ventanaEncargo).val(),
			'telefono' 		: $("#telefono", ventanaEncargo).val(),

			'fecha_entrega' : $("#fecha_entrega", ventanaEncargo).val(),
			'pago' 			: $("#pago", ventanaEncargo).val(),
			'extra' 		: extra,
			'observacion' 	: $("#observacion", ventanaEncargo).val(),
			'forma_pago' 	: $forma_pago,
			'total' 		: total
		},
		success: function(r){
			$procesado = false;
			if (r.s === 's'){
				ventanaEncargo.modal('hide');
				ventanaFormaPago.modal('hide');

				eliminarOrden(id);
			}

			aviso(r);
		}
	});
}

function anular(id){
	if (!confirm('Esta Seguro que Desea Anular la Orden "' + id + '"?')){
		return;
	}

	$.ajax(app.url + 'anular/' + id, {
		success: function(r){
			if (r.s === 's'){
				eliminarOrden(id);
				ventanaBusqueda.modal('hide');
			}

			r.s = 'n';
			aviso(r);
		}
	});
}

function pagar($forma_pago){
	if (!confirm('Esta Seguro que Desea Pagar y Entregar la Orden "' + ordenActual + '"?')){
		return;
	}

	$.ajax(app.url + 'pagar/' + ordenActual, {
		data: {
			'forma_pago' 	: $forma_pago,
			'total' 		: total
		},
		success: function(r){
			if (r.s === 's'){
				ventanaBusqueda.modal('hide');
				ventanaFormaPago.modal('hide');
			}

			aviso(r);
		}
	});
}

function entregar(id){
	if (!confirm('Esta Seguro que Desea Entregar el Encargo "' + id + '"?')){
		return;
	}

	$.ajax(app.url + 'entregar/' + id, {
		success: function(r){
			aviso(r);

			ventanaBusqueda.modal('hide');
		}
	});
}

function informacion(id){
	$.ajax(app.url + 'informacion/' + id, {
		success: function(r){
			var html = '', detalle = r.detalle, ordenConsulta = r.orden, totalInfo = 0;

			$("#listadoOrden li[data-id='" + ordenActual + "']").removeClass('btn-danger').addClass('btn-success');
			$("#procesar, #anular, #encargo").css('display', 'block');

			for(var i in detalle){
				html += $lista(detalle[i]);
				totalInfo += precio(detalle[i].precio, detalle[i].cantidad);
			}

			totalInfo = round(totalInfo * 1.12, 2);

			html += "<li class='total'>Total: " + number_format(totalInfo, 2, ',', '.') + " Bsf</li>";

			$("#detalleOrdenInfo ul", ventanaBusquedaInformacion).html(html);
			ventanaBusquedaInformacion.modal('show');
		}
	});
}

function eliminarOrden(id){
	$("[data-id=" + id + "]", listadoOrden).remove();
	$("#detalleOrden").css('display', 'none');
	$("#procesar, #anular, #encargo").css('display', 'block');
}

function ucwords(str) {
	return (str + '')
	.replace(/^([a-z\u00E0-\u00FC])|\s+([a-z\u00E0-\u00FC])/g, function($1) {
		return $1.toUpperCase();
	});
}

function obFecha(){
	var d = fecha_actual.getDate(),
	m = (fecha_actual.getMonth() + 1),
	a = fecha_actual.getFullYear();

	if (d < 10){
		d = '0' + d;
	}

	if (m < 10){
		m = '0' + m;
	}

	return d + '/' + m + '/' + a;
}

function validar($form){
	$form = $($form);

	var t = $(this), h = t.data('handler'), v = h ? h.value() : t.val();
}

function floatString(v){
	var v = parseFloat(v.replace(/\,+/g, '.'));
	return isNaN(v) ? 0 : v;
}
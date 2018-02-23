var ajaxsetupvar = {
	dataType: "json",
	complete:function(x,e,o){
		$("#cargando").animate({opacity:0}, {queue:false, complete:function(){
			$(this).css({display: 'none'});
		}});
	},
	timeout: 15000,
	cache: false,
	type: "POST"
};

$.ajaxSetup(ajaxsetupvar);

$(document)
.ajaxStart(function(){
	$("#cargando").css({display: 'block', opacity: 0.6});
})
.ajaxStop(function(){
	$("#cargando").animate({opacity:0}, {queue:false, complete:function(){
		$(this).css({display: 'none'});
	}});
});

app.ini(function(){
	$("#cargando").css("opacity", 0.6);
	
	$('a[href="#"]', "#menuPrincipal").click(function(){return false;});

	$("#menuPrincipal ul").each(function(){
		var $this = $(this);
		if ($this.html() == "")
			$this.parent("li").detach();
	});
	
	$("#menuPrincipal").kendoMenu();

	$("#dialogAlerta").dialog(dialogVar);
});
function alerta(msj){
	$("#dialogAlerta #dialog_txt").html(msj);
	$("#dialogAlerta").dialog("open");
}

alertify.set({labels: {
    'ok'     : "Aceptar",
    'cancel' : "Cancelar"
}});

function aviso(msj, t){
	var _msj = msj;
	if (typeof(msj) === 'object'){
		_msj = msj.msj || '';
		t = msj.s === 's';
	}
	
	if (_msj === undefined || _msj === ''){
		console.log("llamada a 'aviso' sin msj");
		return;
	}
	
	if (t === undefined){
		alertify.log(_msj);
	}else if (t === true){
		alertify.success(_msj);
	}else if (t === false){
		alertify.error(_msj);
	}
}

$.validator.setDefaults({
	//onsubmit: false,
	onkeyup: false,
	onclick: false,
	focusInvalid: false,
	focusCleanup: true,
	
	ignore: [],
	/*highlight: function (element, errorClass) {
		console.log(element);
		$(element).wrap('<div class="has-error" />');
	},
	unhighlight: function (element, errorClass) {
		$(element).unwrap();
	},

	highlight: function(input) {
		$(input).addClass("ui-state-highlight");
	},
	unhighlight: function(input) {
		$(input).removeClass("ui-state-highlight");
	},*/
	errorElement: "div",
	wrapper: "label",  // a wrapper around the error message
	errorPlacement: function(error, element){
		if (!element.is(':visible'))
			return;

		var textoError = $(".error", error).html();

		//alert("id: " + element.attr("id") + ", val: " + element.html());
		offset = element.offset();
		error
		.appendTo("body")
		.addClass('message')  // add a class to the wrapper
		.css({
			position 		: 'absolute',
			opacity 		: '0',
			fontWeight 		: 'bold'
		})
		//.css('left', (offset.left + (element.outerWidth() / 2) - (error.outerWidth() / 2)))
		//.css('top', (offset.top - error.outerHeight() - 25))
		.position({
			of: element,
			my: "center bottom",
			at: "center top",
			offset: "0 -25",
		}).css({
			top 	: '-=25',
		}).animate({
			top 	: '+=25',
			opacity	: 1
		}, {duration: 500, complete:function(){
			error.mousemove(function(){
				$(this).animate({
				top 	: '-=25',
				opacity	: 0
				}, 500, function() {
					$(error).detach();
				});
			});

			setTimeout(function(){
				error.trigger("mousemove");
			}, 2500);
		}});
	},
	showErrors: function(errorMap, errorList) {
		$numeroErrores = this.numberOfInvalids();
		if ($numeroErrores > 0)
			aviso("Su formulario contiene " + $numeroErrores + " error" + ($numeroErrores > 1 ? "es." : "."));

		this.defaultShowErrors();
	},

	debug: false
});

var oTable = {};
function iniTablaObj(id, op){
	/*
		{
			"url" : url,
			"datos" : {},
			"funcionBuscar" : function(id){},
			"funcion": function(r){},
		}
	*/
    if (typeof op == "string")
        op = {"url" : op};

    id.data("datos", {});
    if (typeof op.datos != "undefined")
    	id.data("datos", op.datos);

	if (typeof op.funcionBuscar != "undefined")
    	id.data("funcionBuscar", op.funcionBuscar);

    var datatableDefecto = {
		//bJQueryUI: true,
		sPaginationType: "full_numbers",
		bAutoWidth: false,
		bProcessing: false,
		bServerSide: true,

		sAjaxSource: op.url,
		fnServerData: function ( sSource, aoData, fnCallback ) {
			var $datatableObj = $(this);
			var $datosTabla = $datatableObj.data("datos");

			$.each($datosTabla, function(k, v){
				aoData.push({ "name" : k, "value" : v });
			});

			$.ajax({
				dataType: "json",
				type: "POST",
				url: sSource,
				data: aoData,
				success: function(r){
		   			if (typeof r.__iniciarsession != "undefined"){
		   				if (r.__iniciarsession == 1){
		   					setTimeout(function(){ location.href = "index.php"; },2500);
		   					return false;
		   				}
			    	}

				    fnCallback(r);

                    if (typeof op.funcion == "function")
                        op.funcion(r);
					
					if (typeof op.funcionBuscar == "function"){
                   		$("tbody tr", $datatableObj).click(function(){
                   			op.funcionBuscar(parseInt($(this).attr("idReg")));
                   		});
					}else if(typeof window.buscar == 'function'){
                   		$("tbody tr", $datatableObj).click(function(){
                   			buscar(parseInt($(this).attr("idReg")));
                   		});
	   				}else if(typeof app.buscar == 'function'){
                   		$("tbody tr", $datatableObj).click(function(){
                   			app.buscar(parseInt($(this).attr("idReg")));
                   		});
	   				}

                    if (typeof r.jQuery != "undefined"){
	                    if ($.isArray(r.jQuery)){
	                    	$("tbody tr", $datatableObj).each(function(i){
	                    		$(this).attr("datatable", r.jQuery[i]);
	                    	});
						}
                   	}

                   	if (typeof r.idReg != "undefined"){
	                    if ($.isArray(r.idReg)){
	                    	$("tbody tr", $datatableObj).each(function(i){
	                    		$(this).attr("idReg", r.idReg[i]);
	                    	});
						}
                   	}
                }
			});
		}
	};

	var datatableDefectop = datatableDefecto;
	if (typeof op.json != "undefined")
		var datatableDefectop = $.extend(datatableDefecto, op.json);

	oTable["#" + id.attr('id')] = id.addClass('table table-striped table-bordered').dataTable(datatableDefectop);
}

function cargarForm(r, $contenedor){
	if (typeof $contenedor == "undefined")
    	$contenedor = $objForm.element;

    if (typeof r.s != "undefined")
    	if (r.s != "s"){
    		if (typeof r.msj != "undefined")
    			aviso(r.msj);

   			if (typeof r.__iniciarsession != "undefined")
   				if (r.__iniciarsession == 1)
   					setTimeout(function(){ location.href = "index.php"; },2500);

    		return false;
    	}

    if (typeof r.msj != "undefined")
		aviso(r.msj);

	$.each(r, function(key, value){
		if (key == "msj" || key == "s")
			return true;

		var $objid = "#" + key;
		var $obj = $($objid, $contenedor);

		if ($obj.length == 0)
   				return;

		var $tag = $obj.get(0).tagName;

		if ($obj.hasClass("puntuacion") == true){
			$('input', $obj).rating('select',value);
			return;
		}

		if ($tag == "DIV")
			$obj.html($.trim(value) == "" ? "&nbsp;" : value);
		else if ($tag == "INPUT")
			if ($obj.attr("type") == "checkbox")
				if ($obj.val() == value) $obj.attr("checked", "checked");
			else
				$obj.val(value);

		else
			$obj.val(value);

	});

	return true;
}

function bloquearMenu($bloqueo){
	if ($("#menuAccion").length == 0)
		return true;

	if (typeof $bloqueo == "undefined")
		$bloqueo = true;

	if ($bloqueo === true){
		$("#menuAccion").block({
			message: null,
			overlayCSS:  {
				"backgroundColor": '#000',
				"opacity":         0
			}
		});
	}else{
		$("#menuAccion").unblock();
	}
}

function bloquear($ele, $bloqueo, $valor){
	$bloqueo = typeof $bloqueo == "undefined" ? true : $bloqueo;
	$valor = typeof $valor == "undefined" ? "" : $valor;

	if ($ele.data("requerido") == undefined)
		$ele.data("requerido", $ele.hasClass("required"));

	if ($bloqueo){
		$ele.val("")
		.addClass("ui-state-disabled")
		.removeClass("ui-state-highlight")
		.attr("readonly", "readonly");

		if ($ele.data("requerido")){
			$ele.removeClass("required").rules("remove");
		}
	}else{
		$ele
		.removeClass("ui-state-disabled")
		.removeAttr("readonly")
		.focus();

		if ($ele.data("requerido")){
			$ele.addClass("required")
			.rules("add", {
				"required": true
			});
		}
	}
}

function rand(n){
	return Math.round((new Date()).getTime() / 1000);
    return (Math.floor(Math.random() * ((n == undefined) ? 99999 : parseInt(n)) + 1));
}

function trim(stringToTrim) {
	return stringToTrim.replace(/^\s+|\s+$/g,"");
}
function ltrim(stringToTrim) {
	return stringToTrim.replace(/^\s+/,"");
}
function rtrim(stringToTrim) {
	return stringToTrim.replace(/\s+$/,"");
}

function number_format(number, decimals, dec_point, thousands_sep) {
    number = (number+'').replace(',', '').replace(' ', '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function (n, prec) {
            var k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;        };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }    return s.join(dec);
}

function stripVowelAccent(str){
	var rExps=[
		{re:/[\xC0-\xC6]/g, ch:'A'},
		{re:/[\xE0-\xE6]/g, ch:'a'},
		{re:/[\xC8-\xCB]/g, ch:'E'},
		{re:/[\xE8-\xEB]/g, ch:'e'},
		{re:/[\xCC-\xCF]/g, ch:'I'},
		{re:/[\xEC-\xEF]/g, ch:'i'},
		{re:/[\xD2-\xD6]/g, ch:'O'},
		{re:/[\xF2-\xF6]/g, ch:'o'},
		{re:/[\xD9-\xDC]/g, ch:'U'},
		{re:/[\xF9-\xFC]/g, ch:'u'},
		{re:/[\xD1]/g, ch:'N'},
		{re:/[\xF1]/g, ch:'n'}
	];
	
	for(var i=0, len=rExps.length; i<len; i++)
		str=str.replace(rExps[i].re, rExps[i].ch);
	
	return str;
}

/*
String.prototype.replaceAll = function(strTarget, strSubString){
	var strText = this,
	intIndexOfMatch = strText.indexOf(strTarget);
	while (intIndexOfMatch != -1){
		strText = strText.replace(strTarget, strSubString);
		intIndexOfMatch = strText.indexOf( strTarget );
	}
	return strText;
}
*/

(function($){
    $.fn.delayEvent = function (evento, iDelay, callback) {
	    var iDelay = iDelay || 250;
	 
	    if (evento === undefined){
	    	console.log("no se puedo cargar el evento");
	        return;
	    }
		
	    return this.each(function(){
	        var t = $(this), 
				oTimerId = null;
	          
	        t.unbind(evento).bind(evento, function(e){
	            window.clearTimeout(oTimerId);
	            oTimerId = window.setTimeout(function(){
	                callback.call(t);
	            }, iDelay);
	        });
	          
	        return this;
	    });
	}
	
	$.fn.sortElements = function(){
		var sort = [].sort;
		return function(comparator, getSortable){
			getSortable = getSortable || function(){return this;};
			var placements = this.map(function(){
				var sortElement = getSortable.call(this),
				parentNode = sortElement.parentNode,
				nextSibling = parentNode.insertBefore(
					document.createTextNode(''),
					sortElement.nextSibling
				);
	
				return function(){
					if (parentNode === this){
						throw new Error("You can't sort elements if any one is a descendant of another.");
					}
	
					// Insert before flag:
					parentNode.insertBefore(this, nextSibling);
					// Remove flag:
					parentNode.removeChild(nextSibling);
				};
			});
	
			return sort.call(this, comparator).each(function(i){
				placements[i].call(getSortable.call(this));
			});
		};
	}
}(jQuery));

var tinymceOpciones = {
	// Location of TinyMCE script
	script_url : 'js/tiny_mce/tiny_mce.js',

	// General options
	theme : "advanced",
    skin : "o2k7",
    skin_variant : "silver",
	plugins : "safari,pagebreak,style,layer,table,advhr,advimage,advlink,inlinepopups,insertdatetime,preview,media,searchreplace,print,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

	// Theme options
	theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,formatselect,fontsizeselect,|,forecolor,backcolor,|,sub,sup,|,charmap,emotions,iespell,advhr,|,search,replace,|,bullist,numlist,|,undo,redo,|,link,unlink,cleanup,code,|,insertdate,inserttime,preview,|,fullscreen",
	theme_advanced_buttons2 : "",
	theme_advanced_buttons3 : "",
	theme_advanced_buttons4 : "",

	paste_use_dialog : false,
	paste_auto_cleanup_on_paste : true,
	paste_convert_headers_to_strong : false,
	paste_strip_class_attributes : "all",
	paste_remove_spans : true,
	paste_remove_styles : true,
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",
	theme_advanced_statusbar_location : "bottom",
	theme_advanced_resizing : false	,

	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",
	theme_advanced_statusbar_location : "bottom",
	theme_advanced_resizing : false	,

	// Drop lists for link/image/media/template dialogs
	template_external_list_url : "lists/template_list.js",
	external_link_list_url : "lists/link_list.js",
	external_image_list_url : "lists/image_list.js",
	media_external_list_url : "lists/media_list.js"
};

var datePickerVar = {
    showOtherMonths: true,
	selectOtherMonths: true,
	changeMonth: true,
	changeYear: true,

	closeText: 'Cerrar',
	prevText: '&#x3c;Ant',
	nextText: 'Sig&#x3e;',
	currentText: 'Hoy',
	monthNames: ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
	monthNamesShort: ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'],
	dayNames: ['Domingo','Lunes','Martes','Mi&eacute;rcoles','Jueves','Viernes','S&aacute;bado'],
	dayNamesShort: ['Dom','Lun','Mar','Mi&eacute;','Juv','Vie','S&aacute;b'],
	dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','S&aacute;'],
	weekHeader: 'Sm',
	dateFormat: 'dd/mm/yy',
	firstDay: 1,
	isRTL: false,
	showMonthAfterYear: false,
	yearSuffix: ''
};

$.datepicker.setDefaults(datePickerVar);

var dialogVar = {
	autoOpen: false,
	bgiframe: true,
	modal: true,
	resizable: true,
	width: 380,
	height: 250,
	show: "fade",
	hide: "fade",
	draggable: true,
	buttons: {
		Aceptar: function() {
			$(this).dialog("close");
		}
	}
};

$.extend($.ui.dialog.prototype.options, dialogVar);


function round(value, precision, mode) {
	var m, f, isHalf, sgn; // helper variables
	precision |= 0; // making sure precision is integer
	m = Math.pow(10, precision);
	value *= m;
	sgn = (value > 0) | -(value < 0); // sign of the number
	isHalf = value % 1 === 0.5 * sgn;
	f = Math.floor(value);
	
	if (isHalf) {
		switch (mode) {
			case 'PHP_ROUND_HALF_DOWN':
				value = f + (sgn < 0); // rounds .5 toward zero
				break;
			case 'PHP_ROUND_HALF_EVEN':
				value = f + (f % 2 * sgn); // rouds .5 towards the next even integer
				break;
			case 'PHP_ROUND_HALF_ODD':
				value = f + !(f % 2); // rounds .5 towards the next odd integer
				break;
			default:
				value = f + (sgn > 0); // rounds .5 away from zero
		}
	}

	return (isHalf ? value : Math.round(value)) / m;
}

function coe_iva($iva){
	$iva = $iva || 12;
	return 1 / (($iva / 100) + 1);
}

function precio($valor, $camtidad){
	return round(($valor * coe_iva() * $camtidad), 2);
}
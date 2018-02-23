$(function(){
	$(".botoneraMenu").tooltip({
		"track": true,
		"delay": 0,
		"showURL": false,
		"showBody": " - ",
		"extraClass": "ui-state-default",
		"fade": 250
	}).hover(
		function() { $(this).addClass('ui-state-hover'); },
		function() { $(this).removeClass('ui-state-hover'); }
	)
	.mousedown(function() { $(this).addClass('ui-state-active'); })
	.mouseup(function() { $(this).removeClass('ui-state-active'); });
	
	$.Shortcuts.add({
	    "type": 'down',
	    "mask": 'Ctrl+g,Ctrl+s',
	    "enableInInput": true,
	    "list": 'principal',
	    "handler": function() {
	        $("#guardar").triggerHandler("click"); return false;
	    }
	}).add({
	    "type": 'down',
	    "mask": 'Ctrl+f,Ctrl+b',
	    "enableInInput": true,
	    "list": 'principal',
	    "handler": function() {
	        $("#buscar").triggerHandler("click"); return false;
	    }
	}).add({
	    "type": 'down',
	    "mask": 'Ctrl+d,Ctrl+e',
	    "enableInInput": true,
	    "list": 'principal',
	    "handler": function() {
	        $("#eliminar").triggerHandler("click"); return false;
	    }
	}).add({
	    "type": 'down',
	    "mask": 'Ctrl+n',
	    "enableInInput": true,
	    "list": 'principal',
	    "handler": function() {
	        $("#nuevo").triggerHandler("click"); return false;
	    }
	}).start("principal");
	
	if ($("#idm").length){
		$("#idm").val(0).bind("keydown", function(e){
			var idmval = $(this).val();
			if (e.which == 13){
				$(this).blur();
				if(typeof window.buscar == 'function')
					buscar(idmval);
				else
					$($formObj).objForm("buscar", idmval);
			}
		}).click(function(){
			$(this).select();
		});
	}
	
	var dialogVarp = $.extend(dialogVar, {width: 500, height: 350});
	$("#dialogAyudaForm").dialog(dialogVarp);
	
	$("#ayuda", "#menuAccion").bind("click", function() {
		$("#dialogAyudaForm").dialog("open");
	});
	
	$("#nuevo", "#menuAccion").bind("click", function() {
		$($formObj).objForm("reset");
	});
	
	$("#buscar", "#menuAccion").bind("click", function() {
	    if ($("#dialogBuscar").length){
			if (!$("#dialogBuscar").dialog("isOpen")){
	            if (oTable["#tabla"] != undefined)
				    oTable["#tabla"].fnDraw();
				$("#dialogBuscar").dialog("open");
			}
		}
	});
	
	$("#guardar", "#menuAccion").bind("click", function(){
		$($formObj).objForm($($formObj).objForm("get", "idReg") == 0 ? "incluir" : "modificar");
	});
	
	$("#eliminar", "#menuAccion").bind("click", function() {
		if ($($formObj).objForm("get", "idReg") != 0)
			$.blockUI({ "message": $('#eliminarForm'), "css": { "width": 390 } });
	});
	
	$("#eliminarSI").button().bind("click", function() {
	    $($formObj).objForm("eliminar");
		$.unblockUI();
	});
	$("#eliminarNO").button().bind("click", function() {
		$.unblockUI();
	});
	
	$("#bloquear", "#menuAccion").toggle(function(){
		this.tooltipText = "Desbloquear Formulario";
	
		$("span", this).addClass('ui-icon-unlocked').removeClass('ui-icon-locked');
		$($formObj).block({
			"message": null,
			"overlayCSS":  {
				"backgroundColor": '#000',
				"opacity":         0
			}
		});
	}, function(){
		this.tooltipText = "Bloquear Formulario";
	
		$("span", this).addClass('ui-icon-locked').removeClass('ui-icon-unlocked');
		$($formObj).unblock();
	});
});
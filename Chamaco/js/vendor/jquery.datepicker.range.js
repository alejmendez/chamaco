/*
 * jQuery UI objForm 0.0.1
 *
 * Copyright 2011, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 *
 * Depends:
 *   jquery.ui.core.js
 *   jquery.ui.widget.js
 *
 *   jQuery objForm plug-in 0.0.1
 *
 */
(function($, undefined){

$.widget("ui.datepickerrange",{
	"options": {
		
	},
    
	"_create": function() {
		var $ele = this.element, $tag = $ele.get(0).tagName;
		
		if ($tag != "INPUT"){
			alert("No se Puede Inicializar este Tipo de Objetos");
			this._destroy();
			return false;
		}
		
		var $contenedor = $("<div />")
		.attr("id", "ui-datepickerrange-div" + this.rand())
		.attr("class", "ui-widget ui-datepickerrange-div ui-widget-content ui-helper-clearfix ui-corner-all")
		.css({position: "absolute", zIndex: 2000, top:-99999, left:-99999, display: "block", padding: "2px"})
		.appendTo("body");
		
		for(var i=0;i<=1;i++)
			var $datepicker = $("<div />")
			.attr("id", this.rand())
			.attr("datepicker", i)
			.css({"float":"left", "margin-left" : i * 2})
			.datepicker({
				onSelect: function( selectedDate ) {
					//$ele.datepicker("getDate");
					
					var $datepicker = $(".hasDatepicker", $contenedor),
						option = $(this).attr("datepicker") == "0" ? "minDate" : "maxDate",
						instance = $(this).data("datepicker"),
						formato = $(this).datepicker("option", "dateFormat") || $.datepicker._defaults.dateFormat
						date = $.datepicker.parseDate(instance.settings.dateFormat ||
							$.datepicker._defaults.dateFormat,
							selectedDate, instance.settings);
							
					$datepicker.not(this).datepicker("option", option, date);
					
					$ele.val("");
					$datepicker.each(function(i){
						var fecha = $(this).datepicker("getDate"), fechaConFormato = $.datepicker.formatDate(formato, fecha);
						if (fecha == null)
							return true;
							
						var valor = i == 0 ? fechaConFormato : $ele.val() + " - " + fechaConFormato
						$ele.val(valor);
					});
					$ele.triggerHandler("change");
				}
			})
			.appendTo($contenedor);
			
		$contenedor.css({"overflow" : "hidden", display: "block", opacity:0})
		
		$ele.data("options", {
			"contenedor": $contenedor,
		}).click(function(e){
			e.stopPropagation();
			
			$(".ui-datepickerrange-div").css({opacity:0, top:-99999, left:-99999});
			
			var $this = $(this), $objele = $this.data("options").contenedor;
			if ($objele.css("opacity") != 0){
				e.stopImmediatePropagation();
				return true;
			}
			
			$objele.position({
				of: $ele,
				my: "left top",
				at: "left bottom",
				offset: 0,
				//using: using,
				collision: "flip flip"
			})
			.animate({opacity:1}, {duration:300, queue:false});
			
			$(document).not($contenedor).one("click", function(e){
				$objele.animate({opacity:0}, {duration:300, queue:false, complete:function(){
					$(this).css({top:-99999, left:-99999});
				}});
				//e.stopImmediatePropagation();
			});
		});
		
		$contenedor.click(function(e){
			e.stopPropagation();
		});
		
	},

	"_destroy": function() {
		this.element.data("options", {});
		$.Widget.prototype.destroy.apply(this, arguments);
	},
	
	"rand": function(){
		do{
			var $nombre = "datepicker" + Math.round((new Date()).getTime() / 1000);
		}while($("#" + $nombre).length > 0);
		
		return $nombre;
	},
    
    
    "get": function(key){
        switch (key) {
			case "idForm":
				return this.element.data("options").idForm;
        }
  		return false;
    },
    
	"_setOption": function(key, valor){
	   switch (key) {
			case "idForm":
				if (typeof valor == "undefined")
					return this.get("idForm");
					
				this.element.data("options").idForm = valor;
                break;
        }

        return this;
	}
});

$.extend( $.ui.datepickerrange, {
	version: "0.0.1"
});

})( jQuery );
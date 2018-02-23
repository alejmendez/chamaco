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

$.widget("ui.objForm",{
	'bloqueado' : false,
	"options": {
		"log"					: true,
		"idForm"              : "",
        "objForm"             : "",
        "url"                 : "",
        
        "validacion"          : "",
        "accion"              : "",
        "idReg"               : 0,
        "rAjax"               : {},
        
        "antes"               : "",
        "despues"             : "",
        "despuesCargaDatos"   : "",
        "reset"               : "",
        "usaContenedor"			: false
	},

    "_log" : function($t){
    	if (!!this.options.log) console.log("(" + this.element.attr("id") + "): " + $t);
    },
    
	"_create": function() {
		var $this = this.element, $tag = $this.get(0).tagName;
		this._log("Iniciar Formulario");
		
		if ($tag !== "FORM"){
			alert("No se Puede Inicializar este Tipo de Objetos");
			this._destroy();
			return false;
		}
		//<fieldset class="ui-widget ui-widget-content ui-corner-all formfieldset">
		//<legend align="left" class="ui-widget ui-widget-header ui-corner-all">Proyectos</legend>
		
		if (this.options.usaContenedor !== false){
			$this.wrap(function() {
				return '<fieldset class="ui-widget ui-widget-content ui-corner-all formfieldset" />';
			});
			
			if ($this.attr("title") !== ""){
				$("<legend></legend>")
				.addClass("ui-widget ui-state-default ui-corner-all")
				.attr("align", "left")
				.html($this.attr("title"))
				.insertBefore($this);
			}
		}
		
		$this.removeAttr("title")
		.data("options", {
			"idForm": $this.attr("id"),
			"url": $this.attr("action"),
			
			"validacion": this.validar(),
			"accion": "",
			"idReg": 0,
			"rAjax": {},
			
			"reset": this.options.reset
		});
	},

	"_destroy": function() {
		this.element.data("options", {});
		$.Widget.prototype.destroy.apply(this, arguments);
	},
	
	"validar": function() {
		return this.element.validate();
	},
    
    "buscar" : function(options){
        var $this = this, opi = $this.options, id = 0;
        
        $this._setOption("rAjax", {});
        
        if (!$.isPlainObject(options))
            id = parseInt(options);
        else if (options.id !== undefined)
            id = parseInt(options.id);
        else
        	return false;
       	
       	this._log("Accion: buscar(" + id + ")");
       	$this._idReg(id);
       	
       	if ($this.get("idReg") === undefined)
       		return false;
       	
        var defaults = {
            "id"                  : 0,
			"antes"               : "",
            "despues"             : "",
            "despuesCargaDatos"   : "",
            "datos"               : {
    			"n"             : $this._rand(),
    			"id"            : $this.get("idReg"),
                "formulario"    : $this.get("idForm"),
                "accion"        : "buscar"
    		},
            "accion"              : "buscar"
		};
        defaults.datos.accion = defaults.accion;
        
        var o = $.extend({}, defaults, options);
        var obj = {"r" : {}, "objForm" : $this.element, "accion" : options.accion};
        
        if(typeof o.antes === "function")
            o.antes(obj);
        
        var funcion = $this._esFuncion(opi.antes, o.accion);
        if (typeof funcion === "function")
            funcion(obj);
        
        $.ajax({
      		"url" : $this.element.attr('action') + "/" + o.accion,
      		"type" : 'POST',
    		"data": o.datos,
    		"error" : function(){
    			$this.bloquear(false);
    		},
    		"success": function(r){
                $this._setOption("rAjax", r);
                obj.r = r;
                
                if (r.s === "n")
					$this._idReg(0);
                else
    		    	$(".required", $this.element).removeClass('ui-state-highlight');
                
                if (typeof r.id !== "undefined")
                    $this._idReg(parseInt(r.id));
                    
                var funcion = $this._esFuncion(opi.despues, o.accion);
                if (typeof funcion === "function") funcion(obj);
                if (typeof o.despues === "function") o.despues(obj);
                if (!$this._cargarForm()) return false;
                
                var funcion = $this._esFuncion(opi.despuesCargaDatos, o.accion);
                if (typeof funcion === "function") funcion(obj);
                if (typeof o.despuesCargaDatos === "function") o.despuesCargaDatos(obj);
    		},
            dataType: "json"
    	});
        return this;
    },
    "bloquear": function($b){
    	if ($b === undefined || $b === true){
    		this.bloqueado = true;
    		this.element.block({
				"message": null,
				"overlayCSS":  {
					"backgroundColor": '#000',
					"opacity":         0
				}
			});
    	}else{
    		this.bloqueado = false;
    		this.element.unblock();
    	}
    },
    "incluir": function(options){
    	if (this.bloqueado) return;
    	
    	this.bloquear();
        var s = this.accion($.extend(options, {"accion" : "incluir"}));
        
        return s;
    },
    "modificar": function(options){
    	if (this.bloqueado) return;
    	
    	this.bloquear();
        var s = this.accion($.extend(options, {"accion" : "modificar"}));
        
        return s;
    },
    
    "eliminar": function(options){
    	if (this.bloqueado) return;
    	
    	this.bloquear();
        var s = this.accion($.extend(options, {"accion" : "eliminar", "validar" : false}));
        
        return s;
    },
    
    "accion": function(options){
        var $this = this;
        var opi = this.options;
        
        if (!$.isPlainObject(options))
            options = {"accion" : options};
        
        this._log("Accion: " + options.accion);
		
        this._setOption("rAjax", {});
        var $idRegObj = parseInt($("#id", $this.element).val());
        
        if (isNaN($idRegObj))
        	$idRegObj = $this.get("idReg");
        
		$this._idReg($idRegObj);
        
        if ($idRegObj === 0){
            var $datos = {
    			"n" : $this._rand(),
                "formulario" : $this.get("idForm"),
                "accion" : options.accion
	        };
        }else{
	        var $datos = {
    			"n" : $this._rand(),
                "formulario" : $this.get("idForm"),
                "id" : $this.get("idReg"),
                "accion" : options.accion
	        };
        }
        
        var defaults = {
			"antes"               : "",
            "despues"             : "",
            "despuesCargaDatos"   : "",
            "opcionesAjaxSubmit"  : {},
            "datos"               : {},
            "accion"              : "",
            "validar" 			  : true
		};
		
        options.datos = (typeof options.datos !== "undefined") ? $.extend({}, $datos, options.datos) : $datos;
        	
		var o = $.extend({}, defaults, options),
        validarObjForm = o.validar === true ? $this.element.valid() : true,
		obj = {"r" : {}, "objForm" : $this.element, "accion" : o.accion, "validacion" : validarObjForm};
        
        if(typeof o.antes === "function")
            var salidaFuncion = o.antes(obj);
        
        if (salidaFuncion === false){
        	$this.bloquear(false);
        	return false;
        }
        
        var funcion = this._esFuncion(opi.antes, o.accion);
        if (typeof funcion === "function")
            var salidaFuncion = funcion(obj);
        
        if (salidaFuncion === false){
        	$this.bloquear(false);
        	return false;
        }
        
        var opcionesAjaxSubmit = {
        	"url" : $this.element.attr('action') + o.accion,
            "data" : o.datos,
            "dataType": "json",
    		"type" : 'POST',
    		"error" : function(){
    			$this.bloquear(false);
    		},
    		"success": function(r, statusText, xhr, $form) {
    			$this.bloquear(false);
    			
                $this._setOption("rAjax", r);
                obj.r = r;
                
                if (typeof r.id !== "undefined")
                	if (!isNaN(parseInt(r.id)))
                    	$this._idReg(parseInt(r.id));
                
                $(".required", $this.element).removeClass('ui-state-highlight');
                
                var funcion = $this._esFuncion(opi.despues, o.accion);
                if (typeof funcion === "function") var salidaFuncion = funcion(obj);
                if (salidaFuncion === false) return false;
                
                if (typeof o.despues === "function") var salidaFuncion = o.despues(obj);
                if (salidaFuncion === false) return false;
                
                if (!$this._cargarForm()) return false;
                
                var funcion = $this._esFuncion(opi.despuesCargaDatos, o.accion);
                if (typeof funcion === "function") var salidaFuncion = funcion(obj);
                if (salidaFuncion === false) return false;
                
                if (typeof o.despuesCargaDatos === "function") var salidaFuncion = o.despuesCargaDatos(obj);
                if (salidaFuncion === false) return false;
                
                $this._reset(obj);
    		}
        };
            
        var opcionesAjaxSubmit = $.extend({}, opcionesAjaxSubmit, defaults.opcionesAjaxSubmit);
		if (validarObjForm){
    		$this.element.ajaxSubmit(opcionesAjaxSubmit);
    	}else{
    		$this.bloquear(false);
    	}
    	
        return this;
    },
    
    "reset" : function(opciones){
        return this._reset({"r" : {}, "objForm" : this.element, "accion" : "reset", options : "opciones"});
    },
    
    "_reset" : function(obj){
    	this._log("Accion: reset");
        var $this = this;
        
        if (obj.options === undefined)
      		obj.options = {};
      		
    	if (obj.options.campos !== undefined)
			$(obj.options.campos, $this.element).clearFields();
        else{
        	$(".required", $this.element).removeClass('ui-state-highlight');
	        $this._idReg(0);
	        //$($this.element).resetForm();
	        $('*:not([data-role])', $this.element).clearFields();
	        
	        $kddl = $("[data-role]", $this.element);
	        if ($kddl.length){
	        	$kddl.each(function(i){
	        		var t = $(this),
	        		rol = t.attr('data-role'),
					$obj = t.data('handler');
					
					switch (rol){
						case 'dropdownlist':
							$obj.value('');
							break;
						case 'multiselect':
							$obj.value([]);
							break;
					}
	        	});
			}
			
			$("select", obj.objForm).each(function(){
				if ($(this).data('selectpicker')){
					$(this).selectpicker('deselectAll');
				}
			}); 
			//$('.selectpicker').selectpicker('deselectAll');
        }
        
        if ($("#idm").length)
        	$("#idm").val(0);
        	
        if (typeof $this.get("reset") === "function")
            $this.get("reset")(obj);
        
        return $this;
    },
    
    "_esFuncion" : function(arr, accion){
        var salida = false;
        
        if (typeof arr === "function")
            return arr;
            
        if (typeof arr !== "object")
        	return false;
        
        $.each(arr, function(llave, valor){
        	if (llave.lastIndexOf(",") !== -1)
                var array = llave.split(",");
            else
                var array = [llave];
            
            for(i in array){
            	array[i] = $.trim(array[i]);
                if ((array[i].lastIndexOf("!") === 0 && array[i].substring(1) !== accion) || array[i] === accion)
                   if (typeof valor === "function") salida = valor;
            }
        });  
        return salida;
    },
    
    "cargarForm": function($rAjax, $contenedor){
    	this._cargarForm($.extend({}, $rAjax, {"s": "s"}), $contenedor);
    },
    
    "_cargarForm": function($rAjax, $contenedor){
        var $objForm = this;
        
   		if ($contenedor === undefined)
        	$contenedor = $objForm.element;
        	
		if ($rAjax === undefined)
			var $rAjax = this.element.data("options").rAjax;
        
        if ($rAjax.s !== undefined){
        	if ($rAjax.s !== "s"){
        		if ($rAjax.msj !== undefined){
        			aviso($rAjax.msj, false);
       			}
       			
       			if (typeof $rAjax.__iniciarsession !== "undefined")
       				if ($rAjax.__iniciarsession === 1)
       					setTimeout(function(){ location.href = "/"; },300);
       				
        		return false;
        	}
		}
         
        if ($rAjax.msj !== undefined)
    	   aviso($rAjax.msj, true);
    	
    	$.each($rAjax, function(key, valor){
    		if (key === "msj" || key === "s")
    			return true;
   			
   			if (key === ""){
   				console.log("No se puede asignar valor (" + valor + ") al siguiente elemento: '" + key + "'");
   				return true;
   			}
   			
    		var $objid = "#" + key, $obj = $($objid, $contenedor);
    		
   			if ($obj.length === 0)
   				return true;
			
    		var $tag = $obj.get(0).tagName;
    		
    		if (typeof valor === "object" && $tag !== "SELECT"){
   				console.log("Se Salto la Asignacion de \"" + key + "\" ya que su Valor es un Object");
   				return true;
			}
			
			var rol = $obj.attr('data-role');
			if (rol !== undefined){
				var k = $obj.data("handler");
				
				switch (rol){
					case 'dropdownlist':
					case 'combobox':
						if (!$.isArray(valor)){
							k.value(valor);
							return;
						}
						
						k.dataSource.data({ value: '', text: '' });
						k.text("");
						k.value("");
						
						for (var i in valor){
							if (valor[i][0] !== undefined && valor[i][1] !== undefined){
								k.dataSource.add({ value: valor[i][0], text: valor[i][1] });
							}else{
								k.value(valor[i]);
							}
						}
						return;
						
					default:
						k.value(valor);
						break;
				}
    		}
    		
    		if ($obj.hasClass("puntuacion") === true){
    			$('input', $obj).rating('select',valor);
    			return true;
    		}
    		
    		if ($tag === "DIV"){
    			$obj.html($.trim(valor) === "" ? "&nbsp;" : valor);
    		}else if ($tag === "INPUT"){
    			if ($obj.attr("type") === "checkbox"){
  					$obj.prop("checked", ($obj.val() === valor));
    			}else{
    				$obj.val(valor);
				}
			}else if ($tag === "SELECT"){
				$objForm._comboBox($obj, valor);
    		}else{
    			$obj.val(valor);
    		}
    	});
    	
    	return true;
    },
    "comboBox" : function($obj, valor){
    	this._comboBox($obj, valor);
    },
    "_comboBox" : function($obj, valor){
    	if ($obj.get(0).tagName !== "SELECT" || typeof $obj.attr("multiple") !== "undefined" || !$.isArray(valor)){
    		$obj.val(valor);
    		return true;
   		}
		
		for (var i in valor)
			if (!$.isArray(valor[i])){
				$obj.val(valor);
				return true;
			}
			
		$("option[value!='']", $obj).detach();
		for (var i in valor)
			if (typeof valor[i][0] !== "undefined" && typeof valor[i][1] !== "undefined")
				$("<option />").attr("value", valor[i][0]).html(valor[i][1]).appendTo($obj);
			else
				$obj.val(valor[i]);
		
		return true;
    },
    
    "_rand" : function(n){
    	return Math.round((new Date()).getTime() / 1000);
    	//return (Math.floor(Math.random() * ((n == undefined) ? 99999 : parseInt(n)) + 1));
    },
    "_idReg" : function(id){
        this._setOption("idReg", parseInt(id));
    },
    
    "get": function(key){
        switch (key) {
			case "idForm":
				return this.element.data("options").idForm;
            case "url":
				return this.element.data("options").url;
            case "validacion":
				return this.element.data("options").validacion;
            case "accion":
				return this.element.data("options").accion;
            case "idReg":
				return this.element.data("options").idReg;
            case "rAjax":
				return this.element.data("options").rAjax;
            case "reset":
				return this.element.data("options").reset;
        }
  		return false;
    },
    
	"_setOption": function(key, valor){
	   switch (key) {
			case "idForm":
				if (typeof valor === "undefined")
					return this.get("idForm");
					
				this.element.data("options").idForm = valor;
                break;
            case "url":
				if (typeof valor === "undefined")
					return this.get("url");
					
				this.element.data("options").url = valor;
                break;
            case "validacion":
				if (typeof valor === "undefined")
					return this.get("validacion");
					
				this.element.data("options").validacion = valor;
                break;
            case "accion":
				if (typeof valor === "undefined")
					return this.get("accion");
					
				this.element.data("options").accion = valor;
                break;
            case "idReg":
				if (typeof valor === "undefined")
					return this.get("idReg");
					
				this.element.data("options").idReg = parseInt(valor);
				if ($("#idm").length)
					$("#idm").val(parseInt(valor));
					
                break;
            case "rAjax":
				if (typeof valor === "undefined")
					return this.get("rAjax");
					
				this.element.data("options").rAjax = valor;
                break;
            case "reset":
				if (typeof valor === "undefined")
					return this.get("reset");
					
				this.element.data("options").reset = valor;
                break;
        }

        return this;
	}
});
$.extend($.ui.objForm, {
	version: "0.0.1"
});
})(jQuery);
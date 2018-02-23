var app = {},
$basejs = 'js/',
$basecss = 'css/',
descargarPagina = function(){},
appcore = {
	js : [],
 	jstemp : [],
 	css : [],
 	execappini : false,
 	velocidad : 300,
 	url : "",
 	nombreArchivo : "",
 	appCargados : [],

 	log : function(msg){
 		console.log("appCore: " + msg);
 		return this;
 	},

 	menu : function(accion, velocidad){
 		accion = accion.toLowerCase() == "m";
		$("#menu")
		.css("display", (accion ? "block" : "none"))
		.animate({opacity:(accion ? 1 : 0)}, typeof(velocidad) == "undefined" ? appcore.velocidad : velocidad);
		
		return this;
 	},

 	menuAccion : function(accion, velocidad){
 		accion = accion.toLowerCase() == "m";
		$("#menuAccion")
		.css("display", (accion ? "block" : "none"))
		.animate({opacity:(accion ? 1 : 0)}, typeof(velocidad) == "undefined" ? appcore.velocidad : velocidad);

 		if (accion){
			$.Shortcuts.start("appcore");
 		}else{
 			$.Shortcuts.stop();
 		}
 		return this;
 	},

 	ini : function(){
 		appcore.log("appcore ini");
 		if (!$html.menu){
 			appcore.menu("o");
 		}
 		if (!$html.menuAccion){
 			appcore.menuAccion("o");
 		}

 		var $basejsRExp = new RegExp($basejs, "g"),
		$basecssRExp = new RegExp($basecss, "g");

		$("script[src]").each(function(){
	 		appcore.js.push($(this).attr("src").replace($basejsRExp,''));
	 	});

	 	$("link").each(function(){
	 		appcore.css.push($(this).attr("href").replace($basecssRExp, ''));
	 	});

	 	appcore.iniciarapp();
	 	appcore.cargarPagina($.bbq.getState('a'));
	 	
	 	return this;
 	},

	cargarPagina : function(options){
		if (typeof(options) === 'undefined') return false;

		appcore._descargarPagina();
		if ($.type(options) === "string"){
			options = {url:options};
		}

		var defaults = {
			url:"index.php",
			datos: {},
			contenedor: "#contenedor"
		};
		
		options = $.extend({}, defaults, options);
		
		$url = options.url;
		ajaxsetupvar.url = $url;
		
		appcore.url = $url;
		appcore.nombreArchivo = $url.split(".");
		appcore.nombreArchivo.pop();
		appcore.nombreArchivo = appcore.nombreArchivo.join(".").toString();
		appcore.options = options;
		appcore.execappini = false;
		
		appcore.log($url);
		
		$.ajaxSetup(ajaxsetupvar);

		$(options.contenedor).animate({opacity:0}, appcore.velocidad);

		$.bbq.removeState();

		if ($url == "index.php"){
			return false;
		}

		$.bbq.pushState({'a' : $url});
		
		appcore.iniciarapp();
		$.ajax({
		    data 		: options.datos,
		    dataType 	: "html",
		    success 	: function(r){
				$(options.contenedor).html(r);
		        if (typeof(app) != "undefined"){
		        	var $jsCarga = [],
					$archivoJsActual = appcore.nombreArchivo.toLowerCase() + ".js",
					i = 0,
					iniFc = function(){
						appcore.mostrarPagina();
						$(appcore.options.contenedor).animate({opacity:1}, appcore.velocidad);
						
						if (typeof(appcore.appCargados[appcore.nombreArchivo]) === 'undefined'){
							appcore.appCargados[appcore.nombreArchivo] = app;
						}else{
							app = appcore.appCargados[appcore.nombreArchivo];
						}
						
						app.ini();
					};

		        	appcore.jstemp = typeof(app.js) != "undefined" ? ($.type(app.js) === "array" ? app.js.length : 1) : 0;
					appcore.include(app.css);

	        		for(i in app.js){
	        			if ($.inArray(app.js[i], appcore.js) == -1){
							appcore.js.push(app.js[i]);
							$jsCarga.push(app.js[i])
						}
	        		}
	        		
	        		if (!$jsCarga.length){
	        			iniFc();
	        		}else{
	        			Modernizr.load({
						   load: $jsCarga,
						   complete: function () {
								iniFc();
						   }
						});
	        		}
		        }
			}
		});

		return this;
	},

	mostrarPagina : function($html){
		if (typeof(app.html) == "undefined" && typeof($html) == "undefined") return this;

		var h = typeof(app.html) == "undefined" ? $html : app.html,
		$cabecera = $("#cabecera"),
		$pies = $("#pies");
		
		if (typeof(h.titulo) !== "undefined")
			document.title = h.titulo;

		if (typeof ($cabecera.data("d")) == "undefined"){
			$cabecera.data("d", [$cabecera.width(), $cabecera.height()]);
			$pies.data("d", [$pies.width(), $pies.height()]);
		}

		if (h.cabeza){
			$cabecera.width($cabecera.data("d")[0]).height($cabecera.data("d")[1]).animate({opacity:1}, appcore.velocidad);
		}else{
			$cabecera.width(0).height(0).animate({opacity:0}, appcore.velocidad);
		}

		if (h.pies){
			$pies.width($pies.data("d")[0]).height($pies.data("d")[1]).animate({opacity:1}, appcore.velocidad);
		}else{
			$pies.width(0).height(0).animate({opacity:0}, appcore.velocidad);
		}

		$basejs = h.js;
		$basecss = h.css;

		appcore.menu(h.menu ? "m" : "o", appcore.velocidad);
		appcore.menuAccion(h.menuFormulario ? "m" : "o", appcore.velocidad);

		$('select', "#contenedor").each(function(i, e){
			var $this =	$(this);
			$this.width($this.width() + (($this.outerWidth() - $this.width()) * 2));
		});

		$(".obj", "#contenedor").each(function(){
			$(this).width($("input[type!='hidden'], select", this).outerWidth(true))
		});
		
		return this;
	},

	isjs : function(f){
		return /\.(js)$/i.test(f);
	},

	iscss : function(f){
		return /\.(css)$/i.test(f);
	},

	include : function ($a, $callback){
		var $css = $a;
		
		if ($.type($a) === "undefined"){
			return this;
		}
		
		if ($.type($a) === "array"){
			$css = [];
			for(var i in $a){
				if (appcore.iscss($a[i]) && $.inArray($a[i], appcore.css) == -1){
					$css.push($a[i]);
					appcore.css.push($a[i]);
				}
			}
		}else{
			appcore.css.push($css);
			$css = [$css];
		}

		Modernizr.load({
		   load: $css,
		   complete: function(){
				appcore.log($css.join(', '));
		   }
		});

		return this;
	},

	_descargarPagina : function(){
		appcore.jstemp = [];
		try{
		    $("#contenedor").children().remove();

		    descargarPagina();
		    //appcore.deletevar("descargarPagina,$formObj,buscar");

		    if (typeof(app) != "undefined" && typeof(app.descargarPagina) == "function")
				app.descargarPagina();

		    appcore.iniciarapp();
		    appcore.consoleClear();
		}catch(err){
			appcore.log("Error al Descargar la Pagina.");
		}

		descargarPagina = function(){};
		return this;
	},

	iniciarapp : function(){
		app = appcore.app;
		return this;
	},

	deletevar : function(arr){
		if ($.type(arr) === "string"){
			arr = arr.split(",");
		}

		var i = 0, $ejecutar = "";
		for (i in arr){
			$ejecutar += arr[i] + " = null; delete " + arr[i] + "; ";
		}
		$ejecutar += "if (typeof($ejecutar) == 'undefined') return; $ejecutar = null; delete $ejecutar;";
		//$.globalEval($ejecutar);
		return this;
	},
	
	app : {
		_funcionesIni : [],
		_funcionesDescarga : [],
		
		html 	: {},
		js 		: [],
		css 	: [],
		formObj : '',
		
		ini : function(f){
	 		if (typeof(f) == "undefined"){
	 			for(var i in this._funcionesIni){
	 				appcore.log("ini " + i);
	 				this._funcionesIni[i]();
	 			}
	 			return this;
	 		}

	 		if (typeof(f) != "function") return false;
	 		this._funcionesIni.push(f);
	 		return this;
	 	},
	 	descargarPagina : function(f){
	 		if (typeof(f) == "undefined"){
	 			for(var i in this._funcionesDescarga){
	 				this._funcionesDescarga[i]();
	 			}
	 			return this;
	 		}

	 		if (typeof(f) != "function") return false;
	 		this._funcionesDescarga.push(f);
	 		return this;
	 	}
	},
	consoleClear: function(){
		if (typeof console.clear != "undefined") console.clear();
		else if (typeof clear != "undefined") clear();
		return this;
	}
};
var app = {
	jsIni : [],
	url : '',
	base : '',

	__funcionesIni : [],

	inicio : function(){
		ajaxsetupvar.url = app.url;
		$.ajaxSetup(ajaxsetupvar);

		app.ini();
	},
	_ini : function(){
		for(var i in app.jsIni){
			app.jsIni[i] = app.base + app.jsIni[i];
		}

		Modernizr.load({
		   load: app.jsIni,
		   complete: function () {
		      app.inicio();
		   }
		});
	},
	ini : function(f){
		if (typeof(f) === 'function'){
			app.__funcionesIni.push(f);
			return;
		}

		for(var i in app.__funcionesIni){
			app.__funcionesIni[i]();
		}

		console.log("app ini");
	}
};
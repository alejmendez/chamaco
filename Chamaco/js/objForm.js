(function($, k){
	k.obj = k.Class.extend({
		propiedades: {
			id:"",
			tipo:"",
			nombre:"",
			clase:"",
			clase_ui:"",
			estilos:"",
			valor:"",
			type:"hidden",
			titulo:"",
			usaContenedor:true,
			placeholder:"",
			min:'',
			max:'',
			readonly:false,
			deshabilitar:false,
			html:"",
			_data:"",
			requerido:true,
			sin:[],
			data:{
				
			}
		},
		plantilla : k.template("<input id='#= id #' name='#= nombre #' class='#= clase #' style='#= estilos #' value='#= valor #' title='#= titulo #' type='#= type #' placeholder='#= placeholder #' minlength='#= min #' maxlength='#= max #' readonly='#= readonly #' disabled='#= deshabilitar #' #= _data # #= html # />"),
		
		init: function(o){
			if (o.nombre === undefined) o.nombre = o.id;
			this.propiedades = $.extend({}, this.propiedades, o);
		},
		propiedad: function(p, v){
			if (typeof(p) === "object"){
				$.extend(this.propiedades, p);
				return this;
			}
			
			if (v === undefined){
				return this.propiedades[p];
			}
			
			this.propiedades[p] = v;
			return this;
		},
		inf_data:function(){
			var t = this, p = t.propiedades, data = '';
			
			$.each(p.data, function(ll, v){
				data += 'data-' + ll + '=\'' + v + '\' ';
			});
			
			this.propiedades._data = data; 
		},
		generar: function(){
			this.inf_data();
			
			var p = this.propiedades;
			p.readonly = p.readonly === false ? '' : 'readonly';
			p.deshabilitar = p.deshabilitar === false ? '' : 'disabled';
			
			if (p.sin.length > 0){
				for(var i in p.sin){
					p[p.sin] = '';
				}
			}
			
			return this.plantilla(p).replace(/([a-z\-]+)=''/ig, '').replace(/\s+/ig, ' ');
		},
		id: function(){
			return this.propiedad('id');
		},
		val: function(valor){
			return this.propiedad('valor', valor);
		}
	});
	
	k.objs = {
		div : k.obj.extend({
			propiedades: {
				clase_ui:""
			},
			plantilla : k.template("<div id='#= id #' class='#= clase #' style='#= estilos #' title='#= titulo #' #= _data # #= html # >#= valor #</div>"),
		}),
		label : k.obj.extend({
			propiedades: {
				clase_ui:"",
				clase:"",
				para:"",
				usaContenedor:false,
			},
			plantilla : k.template("<label class='#= clase #' style='#= estilos #' title='#= titulo #' for='#= para #' #= _data # #= html # >#= valor #</label>")
		}),
		texto : k.obj.extend({
			propiedades: {
				clase_ui:"k-textbox",
				type:"text"
			}
		}),
		combo : k.obj.extend({
			propiedades: {
				type:"text"
			}
		}),
		oculto : k.obj.extend({
			propiedades: {
				usaContenedor:false
			}
		})
	};
	
	k.objs = $.extend({}, k.objs, {
		password : k.objs.texto.extend({
			propiedades: {
				type:"password"
			}
		}),
		cedula : k.objs.texto.extend({
			propiedades: {
				//type:"password"
			}
		}),
		telefono : k.objs.texto.extend({
			propiedades: {
				//type:"password"
			}
		}),
		email : k.objs.texto.extend({
			propiedades: {
				//type:"password"
			}
		}),
		spinner : k.objs.texto.extend({
			propiedades: {
				data : {
					role:"numerictextbox",
					format:"c",
					min:"0",
					max:"100",
					bind:""
				},
			}
		})
	});
	
	k.formulario = k.Class.extend({
		objs : {},
		init:function(data){
			for(var i in data){
				var o = data[i];
				if (o.tipo === undefined || o.id === undefined){
					continue;
				}
				//console.log(o.id);
				this.objs[o.id] = new k.objs[o.tipo](o);
			}
			
			this.objs['|'] = new k.objs.div({
				id:'|',
				clase:'barra',
				sin: ['id']
			});
			
			this.objs['barra'] = this.objs['|'];
			
			this.objs.contenedorObj = new k.objs.div({
				id:'contenedorObj',
				clase:'obj',
				sin: ['id']
			});
			
			this.objs.labelObj = new k.objs.label({
				id:'labelObj',
				clase:'labelObj',
				sin: ['id']
			});
		},
		generar: function(objs){
			var label, html = '', o = this.objs, ob;
			for(var i in objs){
				ob = o[objs[i]];
				if (ob === undefined){
					continue;
				}
				
				label = '';
				pro = ob.propiedades,
				id = pro.id,
				pro.texto = $.trim(pro.texto);
				
				if (pro.texto !== ''){
					label = o.labelObj
						.propiedad('para', id)
						.val(pro.texto)
						.generar() + "<br />";	
				}
				
				html = label + ob.generar();
				
				if (pro.usaContenedor){
					html = o.contenedorObj.val(html).generar();
				}
				
				console.log(html);
			}
		}
	});
})(jQuery, kendo);
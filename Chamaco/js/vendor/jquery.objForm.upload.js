/*
 * jQuery UI objForm.upload 0.0.1
 *
 * Copyright 2011, AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 *
 * Depends:
 *   fileuploader.js
 *
 *   jQuery objFormUpload plug-in 0.0.1
 *   
 *   dependencias:
 *   tmpl.min.js, load-image.min.js, canvas-to-blob.min.js, jquery.iframe-transport.js, 
 *   jquery.fileupload.js, jquery.fileupload-fp.js, jquery.fileupload-ui.js, jquery.fileupload-jui.js
 */
(function($, undefined){

$.widget('ui.objFormUpload',{
	'options': {
        'url' : 'upload.php',
        'accion' : 'subir',
        
		'autoUpload' : true,
    	'acceptFileTypes' : /(\.|\/)(gif|jpe?g|png)$/i,
    	'previewFileTypes' : /^image\/(gif|jpe?g|png)$/,
    	'formData' : [],
    	
		'fileupload': {}
	},

	"_create": function(){
		var $elemento = this.element
		o = this.options;
		
		/*
		if (!$(".dropzone", $elemento).length){
			$("<div />").attr({
				'class' : 'dropzone fade well'
			})
			.css({opacity:0})
			.text("Soltar los archivos aqui!")
			.appendTo($(".fileupload-content", $elemento));
		}
		
		if (!$("#template-upload").length){
			$("<script id=\"template-upload\" type=\"text/x-jquery-tmpl\"></script>")
			.text('{% for (var i=0, file; file=o.files[i]; i++) { %}<tr class="template-upload fade"><td class="preview"><span class="fade"></span></td><td class="name"><span>{%=file.name%}</span></td><td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>{% if (file.error) { %}<td class="error" colspan="2"><span class="label label-important">Error</span> {%=file.error%}</td>{% } else if (o.files.valid && !i) { %}<td><div class="progress progress-success progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="bar" style="width:0%;"></div></div></td><td class="start">{% if (!o.options.autoUpload) { %}<button class="btn btn-primary"><i class="icon-upload icon-white"></i><span>Start</span></button>{% } %}</td>{% } else { %}<td colspan="2"></td>{% } %}<td class="cancel">{% if (!i) { %}<button class="btn btn-warning"><i class="icon-ban-circle icon-white"></i><span>Cancel</span></button>{% } %}</td></tr>{% } %}')
			.appendTo('body');
		}
		
		if (!$("#template-download").length){	
			$("<script id=\"template-download\" type=\"text/x-jquery-tmpl\"></script>")
			.text('{% for (var i=0, file; file=o.files[i]; i++) { %}<tr class="template-download fade" name="{%=file.name%}" nameo="{%=file.nameo%}" fecha="{%=file.fecha%}" idreg="{%=file.idreg%}" >{% if (file.error) { %}<td></td><td class="name"><span>{%=file.nameo%}</span></td><td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td><td class="error" colspan="2"><span class="label label-important">Error</span> {%=file.error%}</td>{% } else { %}<td class="preview">{% if (file.thumbnail_url) { %}<a href="{%=file.url%}" title="{%=file.nameo%}" rel="gallery" download="{%=file.name%}"><img src="{%=file.thumbnail_url%}"></a>{% } %}</td><td class="name"><a href="{%=file.url%}" title="{%=file.nameo%}" rel="{%=file.thumbnail_url&&\'gallery\'%}" download="{%=file.name%}">{%=file.nameo%}</a></td><td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td><td colspan="2"></td>{% } %}<td class="delete"><button class="btn btn-danger" data-type="{%=file.delete_type%}" data-url="{%=file.delete_url%}"{% if (file.delete_with_credentials) { %} data-xhr-fields=\'{"withCredentials":true}\'{% } %}><i class="icon-trash icon-white"></i><span>Delete</span></button><input type="checkbox" name="delete" value="1"></td></tr>{% } %}')
			.appendTo('body');
		}
		*/
		var $formData = [];
		
		$formData.push({ "name" : "accion", "value" : o.accion });
		
		if (typeof o.formData == 'object'){
			$.each(o.formData, function(k, v){
				$formData.push({ "name" : k, "value" : v });
			});
		}
		
		o.formData = $formData;
		
	    $fileupload = $.extend({}, o.fileupload, {
	    	url: o.url,
	    	autoUpload : o.autoUpload,
	    	acceptFileTypes : o.acceptFileTypes,
	    	previewFileTypes : o.previewFileTypes,
	    	formData : o.formData,
	    	//dropZone: $('.dropzone', $elemento)
	    	dataType: 'json',

			uploadTemplateId: null,
			downloadTemplateId: null,
			
			uploadTemplate : function(o){
				return tmpl('{% for (var i=0, file; file=o.files[i]; i++) { %}<tr class="template-upload fade"><td class="preview"><span class="fade"></span></td><td class="name"><span>{%=file.name%}</span></td><td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>{% if (file.error) { %}<td class="error" colspan="2"><span class="label label-important">Error</span> {%=file.error%}</td>{% } else if (o.files.valid && !i) { %}<td><div class="progress progress-success progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="bar" style="width:0%;"></div></div></td><td class="start">{% if (!o.options.autoUpload) { %}<button class="btn btn-primary"><i class="icon-upload icon-white"></i><span>Start</span></button>{% } %}</td>{% } else { %}<td colspan="2"></td>{% } %}<td class="cancel">{% if (!i) { %}<button class="btn btn-warning"><i class="icon-ban-circle icon-white"></i><span>Cancel</span></button>{% } %}</td></tr>{% } %}', o);
			},
			downloadTemplate : function(o){
				return tmpl('{% for (var i=0, file; file=o.files[i]; i++) { %}<tr class="template-download fade" name="{%=file.name%}" nameo="{%=file.nameo%}" fecha="{%=file.fecha%}" idreg="{%=file.idreg%}" >{% if (file.error) { %}<td></td><td class="name"><span>{%=file.nameo%}</span></td><td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td><td class="error" colspan="2"><span class="label label-important">Error</span> {%=file.error%}</td>{% } else { %}<td class="preview">{% if (file.thumbnail_url) { %}<a href="{%=file.url%}" title="{%=file.nameo%}" rel="gallery" download="{%=file.name%}"><img src="{%=file.thumbnail_url%}"></a>{% } %}</td><td class="name"><a href="{%=file.url%}" title="{%=file.nameo%}" rel="{%=file.thumbnail_url&&\'gallery\'%}" download="{%=file.name%}">{%=file.nameo%}</a></td><td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td><td colspan="2"></td>{% } %}<td class="delete"><button class="btn btn-danger" data-type="{%=file.delete_type%}" data-url="{%=file.delete_url%}"{% if (file.delete_with_credentials) { %} data-xhr-fields=\'{"withCredentials":true}\'{% } %}><i class="icon-trash icon-white"></i><span>Delete</span></button><input type="checkbox" name="delete" value="1"></td></tr>{% } %}', o);
			}/*,
			done: function (e, data){
				$.each(data.result, function (index, file){
					$attr = {
						src: file.thumbnail_url,
						name: file.name,
						nameo: file.nameo,
						fecha: file.fecha,
						idreg: 0
					};
					
					if (typeof file.idreg != 'undefined')
						$attr.idreg = file.idreg;
						
					$img.attr($attr);
				});
			}*/
	    });
	    
	    $elemento
		.fileupload($fileupload)
		.bind('fileuploadsubmit', function (e, data) {
			$datos = $(this).data("formData");
			if (typeof $datos != 'object')
				return true;
				
			var $formData = [];
		
			if (typeof $datos == 'object'){
				$.each($datos, function(k, v){
					$formData.push({ "name" : k, "value" : v });
				});
			}
			
			data.formData = $formData;
			data.formData = $datos;
			//$(this).data("formData", null);
		})
		.delegate(
	        'a:not([target^=_blank])',
	        'click',
	        function(e){
	        	console.log("clock");
	            e.preventDefault();
	            $('<iframe style="display:none;"></iframe>').prop('src', this.href).appendTo('body');
	        }
	    );
	    /*
		$(document).bind('dragover', function (e) {
			var dropZone = $('.dropzone', $elemento).animate({opacity:1}, {queue:false}),
				timeout = window.dropZoneTimeout;
				
			if (!timeout)
				dropZone.addClass('in');
			else{
				clearTimeout(timeout);
			}
			
			if (e.target === dropZone[0])
				dropZone.addClass('hover');
			else
				dropZone.removeClass('hover');
			
			window.dropZoneTimeout = setTimeout(function () {
				window.dropZoneTimeout = null;
				dropZone.removeClass('in hover');
			}, 100);
		}).bind('drop dragenter', function (e) {
			$('.dropzone', $elemento).animate({opacity:0}, {queue:false});
		})
		.bind('drop dragover', function (e) {
			e.preventDefault();
		});*/
	},
	"reset" : function(){
		this.element.removeData("formData");
		$(".files", this.element).html('');
	},
	"agregarDato" : function($datos){
		this.element.data("formData", $datos);
	},
	"obtenerArchivos" : function(){
		var $archivos = new Array();
		$(".template-download:not(.ui-state-error)", this.element).each(function(){
			$archivos.push({
				"idreg" : $(this).attr("idreg"),
				"fecha" : $(this).attr("fecha"),
				"nameo" : $(this).attr("nameo"),
				"name"  : $(this).attr("name")
			});
		});
		return $.toJSON($archivos);
	},
	"cargar": function(files){
		var fu = this.element.data('fileupload');
		fu._adjustMaxNumberOfFiles(-files.length);
		fu._renderDownload(files)
			.appendTo($('.files', this.element))
			.fadeIn(function () {
				// Fix for IE7 and lower:
				$(this).show();
			});	
	},
	"_destroy": function(){
		this.element.fileupload("destroy");
		$.Widget.prototype.destroy.apply(this, arguments);
	},

	"set": function(key, valor){
	   switch (key.toLowerCase()) {
			case "params":
				if (typeof valor != "object")
					break;

				this.element.data("uploader").setParams(valor);
                break;

        }

        return this;
	}
});

$.extend( $.ui.objFormUpload, {
	version: "0.0.1"
});

})( jQuery );
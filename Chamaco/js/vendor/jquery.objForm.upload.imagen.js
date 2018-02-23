/*
 * jQuery UI objForm.uploadImagen 0.0.1
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
 */
(function($, undefined){

$.widget("ui.objFormUploadImagen",{
	"options": {
        "url" : "upload.php",
        "accion" : "subir",
        
		'autoUpload' : true,
    	'acceptFileTypes' : /(\.|\/)(gif|jpe?g|png)$/i,
    	'previewFileTypes' : /^image\/(gif|jpe?g|png)$/,
    	'formData' : [],
    	
		"fileupload": {}
	},

	"_create": function(){
		var $elemento = this.element
		o = this.options;
		
		if (typeof $url != 'undefined')
			o.url = $url;
		
		var $formData = [];
		
		if (typeof o.formData == 'object'){
			$.each(o.formData, function(k, v){
				$formData.push({ "name" : k, "value" : v });
			});
		}
		
		o.formData = $formData;
		$img = $('img', $elemento);
		
	    $fileupload = $.extend({}, o.fileupload, {
	    	url: o.url + "?accion=" + o.accion,
	    	autoUpload : o.autoUpload,
	    	acceptFileTypes : o.acceptFileTypes,
	    	previewFileTypes : o.previewFileTypes,
	    	formData : o.formData,
	    	dataType: 'json',
	    	
	    	uploadTemplateId: null,
    		downloadTemplateId: null,
    
	    	uploadTemplate : function(o){
	    		return '';
			},
	        downloadTemplate : function(o){
	        	return '';
			},
	        done: function (e, data) {
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
	        }
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
		});
		
		$('.fileupload-content', $elemento).css({
			padding: 4,
			position: 'relative'
		})
		$('.fileupload-progressbar', $elemento).css({
		    width: $img.width() - 20,
		    height: 12,
		    position: 'absolute',
		    top: ($img.height() / 2) - 6,
		    left: 10,
		});
	},
	"reset" : function(){
		this.element.removeData("formData");
		$(".files", this.element).html('');
	},
	"agregarDato" : function($datos){
		this.element.data("formData", $datos);
	},
	"obtenerArchivos" : function(){
		var $archivos = new Array()
		$img = $("img", this.element);
		$archivos.push({
			"idreg" : $img.attr("idreg"),
			"fecha" : $img.attr("fecha"),
			"nameo" : $img.attr("nameo"),
			"name"  : $img.attr("name")
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

$.extend($.ui.objFormUpload, {
	version: "0.0.1"
});
})(jQuery);
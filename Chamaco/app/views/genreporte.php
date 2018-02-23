<?php
	$css = array();
	$js = array('tiny_mce/tiny_mce.js', 'tiny_mce/jquery.tinymce.js');
	
	include_once("includes/includes.php");
	include_once('includes/tcpdf/include/tcpdf_static.php');
	
	$html = array_merge($htmlConf, isset($html) ? $html : array());
	
	
	$tam_paginas = array(
		'carta' => TCPDF_STATIC::getPageSizeFromFormat("LETTER")
	);
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" >
	<head>
		<title><?php echo htmlentities($html["titulo"]); ?></title>

		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="title" content="Gobernacion del Estado Bolivar" />
		<meta name="description" content="Gobernacion del Estado Bolivar" />
		<meta name="keywords" content="Gobernacion del Estado Bolivar" />
		<meta name="language" content="es" />
		<meta name="subject" content="Gobernacion del Estado Bolivar" />
		<meta name="robots" content="All" />
		<meta name="copyright" content="GEB" />
		<meta name="abstract" content="GEB" />

		<link rel="icon" href="images/favicon.png" type="image/x-icon" />
		<link rel="shortcut icon" href="images/favicon.png" type="image/x-icon" />

<?php
	$css = array_unique(array_merge($cssConf, $css));
	array_walk($css, 'array_sufijo', '.css');

	foreach($css as $llave => $valor)
		echo "\t\t" . '<link rel="stylesheet" type="text/css" href="' . $html["css"] . $valor. '" />' . "\n";

	echo "\n";

	$js = array_unique(array_merge($jsConf, $js));
	array_walk($js, 'array_sufijo', '.js');

	foreach($js as $llave => $valor)
		echo "\t\t" . '<script type="text/javascript" src="' . $html["js"] . $valor . '"></script>' . "\n";
?>
<style type="text/css">
#contenedo_pagina{
	height: auto;
    overflow: hidden;
}
.pagina{
	border: #EDEDED solid 1px;
	margin: 10px auto;
    overflow: hidden;
    padding: 0;
    position: relative;
}

.elementos{
	border: #EDEDED solid 1px;
}
.elementos .elementosCabeza{
	position: absolute;
	height : 20px;
	width : 100%;
	opacity : 0
}
.elementos .elementosCabeza .ui-icon{
	cursor: pointer;
	float: right;
}
</style>

<script type="text/javascript">
	ajaxsetupvar.url = "<?php echo $url; ?>";
	$url = ajaxsetupvar.url;
	$.ajaxSetup(ajaxsetupvar);
	
	var $idRevista = 0,
	$idPagina = 0,
	$spinnerAncho,
	$spinnerAlto,
	$spinnerArriba,
	$spinnerIzquierda,
	$anchoPagina,
	$altoPagina;

	
	var rep = {
		tam_paginas : <?php echo json_encode($tam_paginas); ?>,
		tipo_pagina : 'carta',
		barra : $("<div class='barra' />"),
		celdaSel : null,
		celda : $("<div />").attr({
			"class" : "elementos",
			"idreg" : 0
		})
		.css({ height : 200, width : 200 })
		.append(
			$("<div />").attr({
				"class" : "elementosCabeza ui-helper-clearfix"
			})
			.append(
				$("<div />").attr({
					"class" : "ui-icon ui-icon-close"
				})
			)
		)
		.append(
			$("<div />").attr({
				"class" : "contenido"
			})
		)
		.hover(function(){
			$('.elementosCabeza', this).animate({'opacity' : 1}, {duration : 400, queue : false});
		}, function(){
			$('.elementosCabeza', this).animate({'opacity' : 0}, {duration : 400, queue : false});
		})
		.mousedown(function(){
			$('.elementos').css("z-index", 1);
			if ($.trim($("#contenido").html()) != '')
				$(".contenido", rep.celdaSel).html($("#contenido").html());
			
			rep.celdaSel = $(this).css("z-index", 2);
			
			$spinnerAncho.val(rep.celdaSel.width());
			$spinnerAlto.val(rep.celdaSel.height());
			
			$spinnerArriba.val(parseInt(rep.celdaSel.css('top')));
			$spinnerIzquierda.val(parseInt(rep.celdaSel.css('left')));
			
			$("#contenido").html($(".contenido", rep.celdaSel).html());
		}),
		agregarPagina : function($n){
			if ($n === undefined){
				$n = 1;
			}
			
			for (var i = 1; i <= parseInt($n); i++) {
				$("<div class='pagina' />").css({
					width : (this.tam_paginas[this.tipo_pagina][0] / 72) + 'in',
					height : (this.tam_paginas[this.tipo_pagina][1] / 72) + 'in'
				}).on('click', function(){
					$(".pagina").attr('activo', 0);
					$(this).attr('activo', 1);
				}).appendTo("#contenedo_pagina");
				
				this.barra.clone().appendTo("#contenedo_pagina");
			}
		},
		
		agregarCelda: function(propiedades){
			if (propiedades == undefined)
				propiedades = {};
			
			propiedades = $.extend({}, {
				ancho:200,
				alto:200,
				arriba:0,
				izquierda:0,
				contenido:'',
				id:0
			}, propiedades);
				
			this.celdaSel = this.celda.clone(true).draggable({
				containment : ".pagina",
				cancel: ".ui-icon",
				//handle : ".elementosCabeza",
				snap: true,
				drag : function(e, ui){
					$spinnerArriba.val(ui.position.top);
					$spinnerIzquierda.val(ui.position.left);
				}
			})
			.resizable({
				//maxHeight: 250,
				//maxWidth: 350,
				minHeight: 20,
				minWidth: 50,
				containment: ".pagina",
				resize: function(event, ui){
				$spinnerAncho.val(ui.size.width);
					$spinnerAlto.val(ui.size.height);
				}
			})
			.attr('idreg', propiedades.id)
			.appendTo($(".pagina[activo=1]"))
			.css({
				"z-index": 2,
				top:parseInt(propiedades.arriba),
				left:parseInt(propiedades.izquierda),
				width:parseInt(propiedades.ancho),
				height:parseInt(propiedades.alto)
			});
			
			$('.elementos').css("z-index", 1);
			
			$spinnerAncho.val(propiedades.ancho);
			$spinnerAlto.val(propiedades.alto);
			
			$spinnerArriba.val(parseInt(propiedades.arriba));
			$spinnerIzquierda.val(parseInt(propiedades.izquierda));
			
			$(".contenido", this.celdaSel).html(propiedades.contenido);
			$("#contenido").html(propiedades.contenido);
			
			$(".ui-icon-close", this.celdaSel).click(function(){
				eliminarElemento($(this).parent().parent());
			});

		}
	};
	
	$(function(){
		$("#contenedorFormPagina")
		.dialog({
			autoOpen: true,
			modal: false,
			//resizable: true,
			width: 330,
			//height: $(window).height() - 2,
			height: 663,
			position: "right top"
		});
		
		$("#contenido").tinymce($.extend({}, tinymceOpciones, {
			onchange_callback : "contenidoChange",
			force_br_newlines : true,
			force_p_newlines : false,
			forced_root_block : '',
			theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,formatselect",
			theme_advanced_buttons2 : "fontsizeselect,|,forecolor,backcolor,|,sub,sup,|,charmap,emotions,iespell,advhr",
			theme_advanced_buttons3 : "bullist,numlist,|,undo,redo,|,link,unlink,cleanup,code,|,fullscreen",
			theme_advanced_buttons4 : "",
		}));
		
		$("#arriba, #izquierda").spinner({"max":9999,"min":0});
		
		$spinnerAncho = $("#ancho").spinner({"max":9999,"min":50})
		.change(function(){
			if (rep.celdaSel !== null){
				rep.celdaSel.width($spinnerAncho.val());
			}
		});
		
		$spinnerAlto = $("#alto").spinner({"max":9999,"min":20})
		.change(function(){
			if (rep.celdaSel !== null){
				rep.celdaSel.height($spinnerAlto.val());
			}
		});
		
		
		
		$spinnerArriba = $("#arriba").change(function(){
			if (rep.celdaSel !== null){
				rep.celdaSel.css('top', $spinnerArriba.val());
			}
		});
		
		$spinnerIzquierda = $("#izquierda").change(function(){
			if (rep.celdaSel !== null){
				rep.celdaSel.css('left', $spinnerIzquierda.val());
			}
		});
		
	    rep.agregarPagina(2);
	    $(".pagina:first").attr('activo', 1);
	    
	    rep.agregarCelda();
	});
	
	
	
</script>
	</head>
	<body>
		<div id="cargando">
			<span>Cargando... </span>
		</div>
		<!-- contenido -->
		<div id="contenedo_pagina"></div>
		
		<div id="contenedorFormPagina" title="Propiedades">
			<form action="rd_gestionarPaginas.php" enctype="application/x-www-form-urlencoded" id="revistaDigitalContenido" method="post" name="revistaDigitalContenido" title="revistaDigitalContenido">
				<div class="obj">
					<label class="labelObj" for="alto">Dimensiones:</label>
					<div class="barra">&nbsp;</div>
					<div class="obj" style="width: 50px;">
						<div class="contenedorObj">
							<input class="required" id="ancho" name="ancho" style="width: 34px;" type="text" value="50" />
						</div>
					</div>
		
					<div class="obj" style="width: 50px;">
						<div class="contenedorObj">
							<input class="required" id="alto" name="alto" style="width: 34px;" type="text" value="20" />
						</div>
					</div>
				</div>
		
				<div class="obj">
					<label class="labelObj" for="arriba">Posici&oacute;n:</label>
					<div class="barra">&nbsp;</div>
					<div class="obj" style="width: 50px;">
						<div class="contenedorObj">
							<input class="required" id="arriba" name="arriba" style="width: 34px;" type="text" value="0" />
						</div>
					</div>
		
					<div class="obj" style="width: 50px;">
						<div class="contenedorObj">
							<input class="required" id="izquierda" name="izquierda" style="width: 34px;" type="text" value="0" />
						</div>
					</div>
				</div>
				
				<div class="obj" style="width: 300px;" >
					<label for="contenido" class="labelObj">Contenido: </label>
					<div class="contenedorObj">
						<textarea id="contenido" name="contenido" class="required ui-widget ui-widget-content ui-corner-all" style="width: 300px; height: 300px;" cols="3" rows="3" ></textarea>
					</div>
				</div>
				<div class="barra" >&nbsp;</div>
				
			</form>
		</div>
		
				
		<!-- fin del contenido -->
	</body>
</html>
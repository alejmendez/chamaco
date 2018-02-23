<script type="text/javascript">
	app.ini(function(){
		$(".botoneraMenu")
		/*.tooltip({
			"track": true,
			"delay": 0,
			"showURL": false,
			"showBody": " - ",
			"extraClass": "ui-state-default",
			"fade": 250
		})*/
		.hover(
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

		var dialogVarp = $.extend(dialogVar, {width: 540, height: 400});
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
			if ($($formObj).objForm("get", "idReg") === 0) return;
			
			alertify.confirm("Esta Seguro que Desea Eliminar Este Registro?", function (e) {
				if (e){
					$($formObj).objForm("eliminar");
				}				
			});
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
</script>
<style type="text/css">
.botoneraMenu {
	cursor:pointer;
	float:left;
	list-style:none outside none;
	margin:2px;
	padding:4px;
	position:relative;
}
</style>
<div id="dialogAyudaForm" style="display:none;" title="Ayuda">
	Ayuda.<br /><br /><br />

	Teclas de Acceso Rapido: <br /><br />

	Nuevo Formulario (Limpiar Formulario) <strong>[Ctrl+n]</strong><br />
	Guardar datos del formulario <strong>[Ctrl+g, Ctrl+s]</strong><br />
	Buscar en la Base de Datos <strong>[Ctrl+f, Ctrl+b]</strong><br />
	Eliminar Registro Actual (Se Pedir&aacute; Confirmacion) <strong>[Ctrl+d, Ctrl+e]</strong><br /><br />

	Usted Podra Buscar en Cualquier Momento en la Base de Datos, si el Campo "<strong>id</strong>" es Igual a "<strong>0</strong>"
	al Guardar se Guardar&aacute; un Registro Nuevo de lo Contrario sera Modificado el Ultimo Registro Seleccionado.
</div>
<?php
	$menuAccionDefecto = array(
		array("id" => "nuevo", 		"titulo" => "Limpiar Formulario", 			"imagen" => "img/menuAccion/nuevo.png"),
		array("id" => "guardar", 	"titulo" => "Guardar en la Base de Datos", 	"imagen" => "img/menuAccion/guardar.png"),
		array("id" => "buscar", 	"titulo" => "Buscar en la Base de Datos", 	"imagen" => "img/menuAccion/buscar.png"),
		array("id" => "eliminar", 	"titulo" => "Eliminar Registro Actual", 	"imagen" => "img/menuAccion/eliminar.png"),
		array("id" => "bloquear", 	"titulo" => "Bloquear Formulario", 			"imagen" => "img/menuAccion/bloquear.png"),
		array("id" => "ayuda", 		"titulo" => "&iquest;Necesita Ayuda?", 		"imagen" => "img/menuAccion/ayuda.png")
	);

	$menuAccion = isset($menuAccion) ? array_merge($menuAccionDefecto, $menuAccion) : $menuAccionDefecto;
?>

<div id="menuAccion" class="grid_12 ui-widget ui-widget-content ui-corner-all" >
	<div class="ui-widget ui-helper-clearfix" style="float:left;">
		<?php
			foreach($menuAccion as $elementoMenu){
		?>
			<div id="<?php echo $elementoMenu["id"]; ?>" class="ui-state-default ui-corner-all botoneraMenu" title="<?php echo $elementoMenu["titulo"]; ?>">
				<?php
					$icono = '<div class="ui-icon '.$elementoMenu["imagen"].'"></div>';
					if (preg_match('/\.(jpe?g|png|gif)$/i', $elementoMenu["imagen"])){
						$icono = '<img src="'.site_url($elementoMenu["imagen"]).'" />';
					}
					
					echo $icono;
				?>
	        </div>
		<?php } ?>
	</div>
</div>

<div class="barra"></div>
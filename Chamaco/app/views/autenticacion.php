<script>
var $formObj;
app.ini(function(){
	<?php $ci->modelo->sJQuery(); ?>
	
	$("#nombre, #contrasenna").val('');
	
	$formObj = $("#<?php echo $ci->modelo->id(); ?>").objForm({
		"antes":function(){
			$formObj.data("deshabilitado", true);
			$("#boton").prop("disable", true);
		},
		"despues":{
			"buscarUsuario": function(obj){
				if (obj.r.s === "n"){
					$formObj.data("deshabilitado", false);
					aviso(obj.r);
					return false;
				}
				
				location.href = "<?php echo site_url($this->session->userdata('redireccionar')); ?>";
				return false;
			}
		}, 
		'reset' : function(obj){
			$("#nombre, #contrasenna").val('');
			return false;
		}
	}).submit(function(e){
		if ($formObj.data("deshabilitado") == true){
			e.stopImmediatePropagation();
			return false;
		}
	}).data("deshabilitado", false);
	
	//------------------------------------------------------------------------------
	
	if ($("#nombre").val() === ''){
		$("#nombre").focus();
	}else{
		$("#contrasenna").focus();
	}
	
	$("#boton").click(function(){
		$formObj.objForm("accion", "buscarUsuario");
	});
	
	$(".form-control", $formObj).keypress(function(e){
		if(e.which == 13){
			if ($("#nombre").val() === ''){
				$("#nombre").focus();
			}else if ($("#contrasenna").val() === ''){
				$("#contrasenna").focus();
			}else{
				$formObj.objForm("accion", "buscarUsuario");
			}
		}
	});
});
</script>
	
<div class="row">
    <div class="col-md-4 col-md-offset-4">
        <div class="login-panel panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Autenticaci&oacute;n de Usuario</h3>
            </div>
            <div class="panel-body">
                <form id="autenticacion" action="<?php echo $ci->url; ?>" autocomplete="off" role="form">
                    <fieldset>
                        <div class="form-group">
                            <input id="nombre" name="nombre" class="form-control" placeholder="Usuario" type="text" autofocus>
                        </div>
                        <div class="form-group">
                            <input id="contrasenna" name="contrasenna" class="form-control" placeholder="Contrase&ntilde;a" type="password">
                        </div>
                        <button id="boton" class="btn btn-lg btn-success btn-block">Aceptar</button>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>
</div>
var $id = 0;

app.ini(function(){
	//Usuarios
	$($formObj).objForm({
		"despues" : {
			"buscarUsuario":function(obj){
				$(obj.objForm).objForm("reset", "[id!='usuario']");
			}
		},
		"despuesCargaDatos":{
			"buscarUsuario":function(obj){
				return false;
			},
			"buscar":function(obj){
				$id = $("#id", $formObj).val();
	            
	        	$("#textoUsuario", $formObj).html($("#nombre", $formObj).val());
	            $("#pass", $formObj).val("");
			},
			"incluir,modificar" : function(){
				$("#fPermisos").objForm('accion', 'buscarUsuarios');
			}
		},
        "reset": function(obj){
        	$id = 0;
            
            $("#textoUsuario", obj.objForm).html("");
            oTable["#tablaUsuarios"].fnDraw();
        }
    }).objForm("reset");
	
	//$("input", $formObj).val("");
	
	$("#usuario", $formObj).keyup(function(e){
		if (e.which == 13){
			$($formObj).objForm('accion', {
				accion : 'buscarUsuario',
				validar : false
			});
		}
	});
});

function buscar(id){
	$($formObj).objForm("buscar", id);
}
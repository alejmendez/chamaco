var $id = 0, ap, tw, twds;

app.ini(function(){
	$("#buscar").unbind("click").remove();
	//$("#eliminar").unbind("click").remove();
	
	//perfiles
	$("#fperfiles").objForm({
    	"despues" : {
    		"guardar" : function(){
				//$("#fperfiles").objForm('reset');
				return false;
			},
			"buscar" : function(obj){
				var r = obj.r;
				if (r.s == 'n'){
    				//$("#fperfiles").objForm('reset');
    				aviso(r.msj, false);
    				return false;
    			}
    			
				cargarNodosArbol();
    			
    			$("input:checkbox", "#arbol").prop("checked", false);
				if (r.permisos.length > 0){
					//checkNodos(r.permisos);
					for(elementos in r.permisos){
						$("input[value='" + r.permisos[elementos] + "']", "#arbol").prop("checked", true);
					}
				}
				
				tw.updateIndeterminate();
			}
    	},
		"despuesCargaDatos" : {
			"buscarUsuarios" : function(){
				return false;
			}
		},
	 	"reset" : function(obj){
	 		//$("#usuario", "#fperfiles").data('kendoDropDownList').value('');
	 		$("input:checkbox", "#arbol").prop("checked", false);
	 		tw.updateIndeterminate();
	 		oTable["#tabla"].fnDraw();
	 	}
    });
    
    $("#perfil").val('');
    
    crear_arbol();
    
	$("#arbol").on("change", ":checkbox", function(e) {
		var node = $(e.currentTarget).closest(".k-item");
		
		twds.getByUid(node.attr("data-uid")).trigger("change", { field: "checked" });
   });
});

function buscar(id){
	//$("#fperfiles").objForm('accion', 'buscarperfiles', id);
	$("#fperfiles").objForm("buscar", id);
}

function crear_arbol(){
	ap = $("#arbol").kendoTreeView({
    	dataSource: {
	        transport: {
	            read: app.url + 'arbol'
	        },
	        schema: {
	            model: {
	                id: 'id',
	                hasChildren: 'tiene_hijos',
	                children: 'hijos'
	            }
	        },
	        requestEnd: function(e){
	        	setTimeout(function(){
	        		cargarNodosArbol();
	        	}, 500);
	        }
	    },
    	checkboxes: {
            checkChildren: true,
            template: "<input type='checkbox' name='permisos[]' value='#= item.id.substr(1) #' />" // apc = arbol permiso Checked
        },
        animation: {
            expand: {
                effects: 'expand:vertical fadeIn'
            }
        }
    });
    
    tw = ap.data("kendoTreeView");
	twds = tw.dataSource;
}

function checkNodos(valores, nodes){
	nodes = nodes || twds.view();
    for (var i = 0; i < nodes.length; i++) {
    	if ($.inArray(nodes[i].id.substr(1), valores) != -1){
    		nodes[i].checked = true;
    	}
		
		if (nodes[i].hasChildren){
			checkNodos(valores, nodes[i].children.view());
		}
    }
}

function cargarNodosArbol(nodes){
	nodes = nodes || twds.view();
    for (var i = 0; i < nodes.length; i++) {
		nodes[i].load();
		
		if (nodes[i].hasChildren){
			cargarNodosArbol(nodes[i].children.view());
		}
    }
}
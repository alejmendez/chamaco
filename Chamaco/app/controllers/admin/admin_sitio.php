<?php
class admin_sitio extends control_base{
	protected $css = array('arbol_admin');
	public function __construct(){
		parent::__construct();
		$this->load->model('admin/admin_sitio_modelo', 'admin_sitio');
	}
	
	public function index(){
		$rsUsuarios	= $this->db->seleccionar($this->conf['tablas']['usuarios'], 'id, nombre', '', 'nombre', true);
		$this->pag('admin/admin_sitio');
	}
	
	public function guardarArbol(){
		$arbol = array();
		foreach($_POST["arbol"] as $nodo){
			$arbol[$nodo['id']] = $nodo;
		}
		
		$this->admin_sitio->guardar($arbol);
		
		$this->salida(array('s' => 's', 'msj' => 'Registros Guardados Satisfactoriamente.'));
	}
	
	public function guardarNodo(){
		//guardar text y codigo del nodo, verificar id enviando todo el nodo, agregar el nodo a una variable al boton
		$id = intval(substr($this->get('id', true), 1));
		
		$texto = $this->get('texto', true);
		$codigo = $this->get('codigo', true);
		
		$datos = array(
			'texto' => $texto,
			'codigo' => $codigo
		);
		
		$this->db->seleccionar($this->conf['tablas']['app_estructura'], 'count(*) as c', array(
			'w' => "codigo = '$codigo' and id!=$id and padre = (select padre from " . $this->conf['tablas']['app_estructura'] . " where id=$id)"
		));
		
		if (intval($this->db->ur('c')) >= 1){
			$this->salida(array('s' => 'n', 'msj' => 'Ya Existe un Elemento con el Codigo "' . $codigo . '" Dentro del Nodo.'));
		}
		
		$this->db->actualizar($this->conf['tablas']['app_estructura'], $datos, false, 'id = ' . $id);
		
		$this->salida(array('s' => 's', 'msj' => 'Registros Guardados Satisfactoriamente.', 'arbol' => $this->arbol(true)));
	}
	
	public function eliminarNodo(){
		$ids = array();
		
		foreach($_POST["arbol"] as $nodo){
			if (strpos('_', $nodo["id"]) === false){
				$ids[] = intval(substr($nodo["id"], 1));
			}
		}
		
		if (!empty($ids)){
			$this->db->eliminar($this->conf['tablas']['app_estructura'], 'id in (' . implode(", ", $ids) . ')');
		}
		
		$this->salida(array('s' => 's', 'msj' => 'Registros Guardados Satisfactoriamente.', 'arbol' => $this->arbol(true)));
	}
	
	public function arbol($retornar = false){
		$arbol = $this->admin_sitio->arbol();
		
		if ($retornar){
			return $arbol;
		}
		
		echo json_encode($arbol);
	}
}
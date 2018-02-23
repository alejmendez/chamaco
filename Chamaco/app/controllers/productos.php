<?php
class productos extends control_base{
	//protected $css = array('arbol_productos');
	protected $tabla = '';
	
	public function __construct(){
		parent::__construct();
		$this->conf['menuFormulario'] = false;
		
		$this->load->model('productos_modelo', 'productos');
		$this->tabla = $this->productos->tabla();
	}
	
	public function index(){
		$this->pag('productos');
	}
	
	protected function procesar_id($id = false){
		return intval(substr(($id === false ? $this->get('id', true) : $id), 3));
	}
	
	public function guardarArbol(){
		$arbol = array();
		foreach($_POST["arbol"] as $nodo){
			$arbol[$nodo['id']] = $nodo;
		}
		
		$this->_guardar_padre($arbol);
		
		$this->salida(array('s' => 's', 'msj' => 'Registros Guardados Satisfactoriamente.'));
	}
	
	protected function _guardar_padre(&$arbol, $id_guardar = 0){
		foreach($arbol as &$nodo){
			if (($id_guardar !== 0 && $nodo["id"] !== $id_guardar) || isset($nodo["guardado"])){
				continue;
			}
			
			if (substr($nodo["padre"], 3, 1) === '_'){ //si el id del padre contiene '_' el padre no existe 
				$this->_guardar_padre($arbol, $nodo["padre"]);
			}
			
			$datos = array(
				'texto' => $nodo["text"],
				'descripcion' => $nodo["descripcion"],
				'precio' => $nodo["precio"],
				'ventaporkilo' => $nodo["ventaporkilo"] === 'true' ? 1 : 0,
				//'imagen' => '',
				'orden' => (int) $nodo["posicion"],
				'padre' => (int) isset($arbol[$nodo["padre"]]['iddb']) ? $this->procesar_id($arbol[$nodo["padre"]]['iddb']) : 0,
				
			);
			
			if ($nodo["db"] == 0){
				$this->db->guardar($this->tabla, $datos, false, 'id');
				$nodo["iddb"] = $this->db->uid;
				$nodo["db"] = 1;
			}else{
				$this->db->actualizar($this->tabla, $datos, false, 'id = ' . $this->procesar_id($nodo["id"]));
				$nodo["iddb"] = $nodo["id"];
			}
			
			$nodo["guardado"] = true;
			
		}
	}
	
	public function guardarNodo(){
		//guardar text y codigo del nodo, verificar id enviando todo el nodo, agregar el nodo a una variable al boton
		$id = $this->procesar_id();
		
		$texto = $this->get('texto', true);
		$descripcion = $this->get('descripcion', true);
		$precio = $this->get('precio', true);
		$ventaporkilo = $this->get('ventaporkilo', true);
		
		$datos = array(
			'texto' => $texto,
			'descripcion' => $descripcion,
			'precio' => $precio,
			'ventaporkilo' => $ventaporkilo === 'true' ? 1 : 0
			//'imagen' => ''
		);
		
		$this->db->actualizar($this->tabla, $datos, false, 'id = ' . $id);
		//$this->db->uq();
		$arbol = array();
		$this->_arbol($arbol);
		
		$this->salida(array('s' => 's', 'msj' => 'Registros Guardados Satisfactoriamente.', 'arbol' => $arbol, 'id' => $id));
	}
	
	public function eliminarNodo(){
		$ids = array();
		
		foreach($_POST["arbol"] as $nodo){
			if (strpos('_', $nodo["id"]) === false){
				$ids[] = $this->procesar_id($nodo["id"]);
			}
		}
		
		if (!empty($ids)){
			$this->db->eliminar($this->tabla, 'id in (' . implode(", ", $ids) . ')');
		}
		
		$this->salida(array('s' => 's', 'msj' => 'Registros Eliminado' . (count($ids) > 1 ? 's' : '') . ' Satisfactoriamente.'));
	}
	
	public function arbol($retornar = false){
		$arbol = array(array(
			'id' => 'productos_ini',
			'text' => 'Productos',
			'descripcion' => '',
			'precio' => 0,
			'expanded' => 1,
			'ventaporkilo' => 0,
			//'spriteCssClass' => 'icon-sitemap',
			'db' => 1,
			'items' => array()
		));
		$this->_arbol($arbol[0]['items']);

		if ($retornar){
			return $arbol;
		}
		
		exit(json_encode($arbol));
	}
	
	protected function _arbol(&$arbol, $padre = 0, $iteracion = 0){
		$i = -1;
		$iteracion++;
		
		$clase = array(
			'S' => 'icon-sitemap',
			'C' => 'icon-page-white-php',
			'D' => 'icon-fodt',
			'M' => 'icon-page-white-code',
		);
		
		$this->db->seleccionar($this->tabla, '*', array('w' => 'padre = ' . $padre, 'o' => 'orden'));
		foreach($this->db->rs as $nodo){
			$arbol[++$i] = array(
				'id' => 'pro' . $nodo['id'],
				'text' => utf8_encode($nodo['texto']),
				'descripcion' => utf8_encode($nodo['descripcion']),
				'precio' => (float) $nodo['precio'],
				'expanded' => 0,
				'ventaporkilo' => $nodo['ventaporkilo'] == 1 ? true : false,
				//'spriteCssClass' => 'icon-sitemap',
				'db' => 1,
				'items' => array()
			);
			
			$this->_arbol($arbol[$i]['items'], $nodo['id'], $iteracion);
			if (empty($arbol[$i]['items'])){
				unset($arbol[$i]['items']);
			}
		}
		
		return $arbol;
	}
}
<?php
class admin_sitio_modelo extends modelo_form{
	protected $id = 'fadmin_sitio'; //id del formulario
	protected $titulo = 'admin_sitio'; //titulo (attr title) del formulario
	protected $tabla = 'app_estructura';
	//protected $url = 'admind/admin_sitio';
	
	protected $forzarSalida = false;
	
	protected $clase_arbol = array(
		'S' => 'icon-sitemap',
		'C' => 'icon-page-white-php',
		'D' => 'icon-fodt',
		'M' => 'icon-page-white-code',
	);
	
	public $campos = array(
		array(
			'id' => 'admin_sitio',
			'nombreDB' => 'admin_sitio[]'
		),
	);
	
	public function __construct(){
	    parent::__construct();
	}
	
	public function arbol($padre = 0, $expanded = true){
		$arbol = array();
		$i = -1;
		
		$this->db->seleccionar($this->tabla, '*', array('w' => 'padre = ' . $padre, 'o' => 'posicion'));
		foreach($this->db->rs as $nodo){
			$i++;
			$arbol[] = array(
				'id' => $nodo['tipo'] . $nodo['id'],
				'text' => $nodo['texto'],
				'codigo' => $nodo['codigo'],
				'expanded' => $expanded,
				'spriteCssClass' => $this->clase_arbol[$nodo['tipo']],
				'db' => 1,
				'tiene_hijos' => true,
				'hijos' => $this->arbol($nodo['id'], false)
			);
			
			if (empty($arbol[$i]['hijos'])){
				unset($arbol[$i]['hijos']);
				$arbol[$i]['tiene_hijos'] = false;
			}
		}
		
		return $arbol;
	}
	
	public function guardar($arbol, $id_guardar = 0){
		foreach($arbol as $nodo){
			$na = $arbol[$nodo['id']]; // na == N.A. == Nodo Actual...
			
			if (($id_guardar !== 0 && $na['id'] !== $id_guardar) || isset($na['guardado'])){
				continue;
			}
			
			if (substr($na['padre'], 1, 1) === '_'){ //si el id del padre contiene '_' el padre no existe 
				$this->guardar($arbol, $na['padre']);
			}
			
			$datos = array(
				'texto' 	=> $na['text'],
				'codigo' 	=> $na['codigo'],
				'posicion' 	=> (int) $na['posicion'],
				'padre' 	=> isset($arbol[$na['padre']]['iddb']) ? intval(substr($arbol[$na['padre']]['iddb'], 1)) : 0,
				'tipo' 		=> $na['tipo']
			);
			
			if ($na['db'] == 0){
				$this->db->guardar($this->tabla, $datos, false, 'id');
				$arbol[$na['id']]['iddb'] = $na['tipo'] . $this->db->uid;
				$arbol[$na['id']]['db'] = 1;
			}else{
				$this->db->actualizar($this->tabla, $datos, false, 'id = ' . intval(substr($nodo['id'], 1)));
				$arbol[$na['id']]['iddb'] = $na['id'];
			}
			
			$arbol[$na['id']]['guardado'] = true;
		}
	}
}
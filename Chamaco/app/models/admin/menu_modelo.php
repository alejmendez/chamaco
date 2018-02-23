<?php
class menu_modelo extends modelo_form{
	protected $id = 'fMenu'; //id del formulario
	protected $titulo = 'Menu'; //titulo (attr title) del formulario
	protected $tabla = 'app_menu';
	//protected $url = 'admind/menu';
	
	protected $forzarSalida = false;
	
	public $campos = array(
		array('id' => 'id', 'nombreDB' => 'id:int', 'campoPrincipal' => true),
		
	);
	
	public function __construct(){
	    parent::__construct();
	    
	}
}
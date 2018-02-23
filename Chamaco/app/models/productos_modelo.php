<?php
class productos_modelo extends modelo_form{
	protected $id = 'fProductos'; //id del formulario
	protected $titulo = 'Productos'; //titulo (attr title) del formulario
	protected $tabla = 'productos';
	//protected $url = 'admind/menu';
	
	protected $forzarSalida = false;
	
	public $campos = array(
		array(
			'id' => 'productos',
			'nombreDB' => 'productos[]'
		),
	);
	
	public function __construct(){
	    parent::__construct();
	}
}
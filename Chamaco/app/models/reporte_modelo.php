<?php
class reporte_modelo extends modelo_form{
	protected $id = 'ventas'; //id del formulario
	protected $titulo = 'Ventas'; //titulo (attr title) del formulario
	//protected $tabla = 'venta';
	//protected $url = 'autenticacion';
	
	//protected $forzarSalida = false;
	//protected $metodo = 'JSON';
	
	public $campos = array(
		
	);
	
	public function __construct(){
	    parent::__construct();
	}
	
}
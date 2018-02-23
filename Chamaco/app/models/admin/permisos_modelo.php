<?php
class permisos_modelo extends modelo_form{
	protected $id = 'fPermisos'; //id del formulario
	protected $titulo = 'Permisos'; //titulo (attr title) del formulario
	protected $tabla = 'app_permisos_usuarios';
	//protected $url = 'admind/permisos';
	
	protected $forzarSalida = false;
	
	public $campos = array(
		//array('id' => 'id', 'nombreDB' => 'id:int', 'campoPrincipal' => true),
		array(
			'id' => 'usuario',
			'tipo' => 'combo',
			'texto' => 'Usuario',
			'ancho' => 200,
			'nombreDB' => 'usuario:int',
			'campoPrincipal' => true
		),
		array(
			'id' => 'permisos',
			'nombreDB' => 'estructura[]:int'
		),
	);
	
	public function __construct(){
	    parent::__construct();
	    
		$this->db->seleccionar('usuarios', 'id, nombre', array('o' => 'nombre'));
		
	    $this->validar('*', 'permisos')
		->val('usuario', $this->db->rs, false, false);
	}
}
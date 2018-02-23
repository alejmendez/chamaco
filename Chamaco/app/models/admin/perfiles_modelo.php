<?php
class perfiles_modelo extends modelo_form{
	protected $id = 'fperfiles'; //id del formulario
	protected $titulo = 'perfiles'; //titulo (attr title) del formulario
	protected $tabla = 'app_perfiles_usuarios';
	//protected $url = 'admind/perfiles';
	
	protected $forzarSalida = false;
	
	public $campos = array(
		//array('id' => 'id', 'nombreDB' => 'id:int', 'campoPrincipal' => true),
		array('id' => 'id', 'nombreDB' => 'id:int', 'campoPrincipal' => true),
		array(
			'id' => 'tabla', 
			'tipo' => 'tabla',
			//'url' => 'usuarios/datatable',
			//'funcionBuscar' => 'buscar',
			'valor' => array('Perfil'=>'100%'), 
			//'datos' => array('accion' => 'dataTable', 'formulario' => 'fperfiles')
		),
		
		array(
			'id' => 'perfil',
			'tipo' => 'texto',
			'texto' => 'Perfil',
			'ancho' => 200,
			'nombreDB' => 'perfil'
		),
		array(
			'id' => 'permisos',
			'nombreDB' => 'estructura[]:int'
		),
	);
	
	public function __construct(){
	    parent::__construct();
	    $this->validar('perfil');
	}
	
	public function datatable(){
		$this->desactivar('*')->activar('perfil');
		
		$dtt = new datatable($this);
		$dtt->hacer();
	}
}
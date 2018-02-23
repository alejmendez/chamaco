<?php
class usuarios_modelo extends modelo_form{
	protected $id = 'fUsuarios'; //id del formulario
	protected $titulo = 'Usuarios'; //titulo (attr title) del formulario
	protected $tabla = 'usuarios';
	//protected $url = 'usuarios';
	
	protected $forzarSalida = false;
	
	public $campos = array(
		array('id' => 'id', 'nombreDB' => 'id:int', 'campoPrincipal' => true),
		array(
			'id' => 'tablaUsuarios', 
			'tipo' => 'tabla',
			//'url' => 'usuarios/datatable',
			//'funcionBuscar' => 'buscar',
			'valor' => array('Nombre'=>'39%', 'Cedula'=>'15%', 'Email'=>'28%', 'Autenticación'=>'18%'), 
			'datos' => array('accion' => 'dataTable', 'formulario' => 'fUsuarios')
		),
		
		array(
			'id' => 'textoUsuario', 
			'tipo' => 'div',
			'texto' => 'Usuario',
			'alto' => 22,
		),
		array(
			'id' => 'nombre', 
			'tipo' => 'texto', 
			'texto' => 'Nombre',
			'nombreDB' => 'nombre'
		),
		array(
			'id' => 'usuario', 
			'tipo' => 'texto', 
			'texto' => 'Usuario',
			'nombreDB' => 'usuario'
		),
		array(
			'id' => 'pass', 
			'tipo' => 'password', 
			'texto' => 'Clave',
			'nombreDB' => 'contrasenna:encry'
		),
		array(
			'id' => 'cedula', 
			'tipo' => 'cedula', 
			'texto' => 'Cedula',
			'nombreDB' => 'cedula'
		),
		array(
			'id' => 'telefono', 
			'tipo' => 'telefono', 
			'texto' => 'Telefono',
			'nombreDB' => 'telefono'
		),
		array(
			'id' => 'email', 
			'tipo' => 'email', 
			'texto' => 'E-mail',
			'nombreDB' => 'email'
		),
		array(
			'id' => 'autenticacion', 
			'tipo' => 'combo', 
			'texto' => 'Autenticacion',
			'valor' => array('Base de Datos', 'Directorio Activo'),
			'valorFuente' => array('Base de Datos', 'Directorio Activo'),
			'nombreDB' => 'autenticacion'
		),
		array(
			'id' => 'perfil', 
			'tipo' => 'combo',
			'multiple' => true,
			'nombre' => 'perfil[]',  
			'texto' => 'Perfil',
			'nombreDB' => 'perfil[]:int'
		)
	);
	
	public function __construct(){
	    parent::__construct();
	    
		$this->db->seleccionar('app_perfiles_usuarios', 'id, perfil as nombre', array('o' => 'perfil'));
	    $rs = $this->db->rs;
	    
	    $this->val('perfil', $rs, false, false);
	    
		$this->validar('*', 'pass,email,telefono')
    	->cambiarPropiedad('nombre,usuario,pass,cedula,telefono,email,ejecutor, autenticacion, perfil', array('ancho' => 306))
    	//->cambiarPropiedad('perfil', array('valorFuente' => $rs))
		->sArchivos();
	}
	
	public function datatable(){
		$this->desactivar('*')->activar('nombre, cedula, email, ejecutor, autenticacion');
		
		$dtt = new datatable($this);
		$dtt->hacer();
	}
}
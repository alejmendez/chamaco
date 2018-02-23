<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class autenticacion extends control_base{
	protected $sinPermiso = true;
	
	protected $css = array();
	protected $js  = array();
	
	public function __construct(){
		parent::__construct();
		
		$this->load->model('autenticacion_modelo', 'modelo');
		$this->conf['marco'] = false;
		$this->conf['menu'] = false;
		$this->conf['menuFormulario'] = false;
		$this->conf['pies'] = false;
	}
	
	public function index(){
		$this->pag('autenticacion');
	}
	
	public function buscarUsuario(){
		$this->salida($this->modelo->autenticar() ? 
			array('s' => 's', 'msj' => '') : // Correcto! rediceccionando al sistema
			array('s' => 'n', 'msj' => 'Usuario o Password Invalido.') //Incorrecto! algo salio mal!
		);
	}
	
	public function cerrar(){
		$this->modelo->cerrarSession();
		redirect("autenticacion");
	}
}
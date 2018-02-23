<?php
class permisos extends control_base{
	protected $css = array('arbol_admin');
	public function __construct(){
		parent::__construct();
		$this->load->model('admin/permisos_modelo', 'permisos');
	}
	
	public function index(){
		$this->pag('admin/permisos');
	}
	
	public function buscarPermisos(){
		$this->permisos->buscar(array('usuario'));
		$this->permisos->dbSalida();
	}
	
	public function incluir(){
		$permisos = $this->get("permisos", true);
		
		if (!is_array($permisos)){
			$_POST['permisos'] = array();
		}
		
		$this->permisos->actualizacionAutomatica();
	}
	
	public function arbol(){
		$this->load->controller('admin/admin_sitio');
		$this->admin_sitio->arbol();
	}
}
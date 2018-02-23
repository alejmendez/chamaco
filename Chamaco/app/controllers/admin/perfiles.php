<?php
class perfiles extends control_base{
	protected $css = array('arbol_admin');
	public function __construct(){
		parent::__construct();
		$this->load->model('admin/perfiles_modelo', 'perfiles');
	}
	
	public function index(){
		$this->pag('admin/perfiles');
	}
	
	public function buscar(){
		$this->perfiles->buscar();
		$this->perfiles->dbSalida();
	}
	
	public function incluir(){
		$permisos = $this->get("permisos", true);
		
		if (!is_array($permisos)){
			//$this->salida(array("s" => "n", "msj" => "Error de Variables"));
			$_POST['permisos'] = array();
		}
		$this->perfiles->actualizacionAutomatica();
	}
	
	public function modificar(){
		return $this->incluir();
	}
	
	public function eliminar(){
		$this->perfiles->eliminar();
		$this->perfiles->dbSalida();
	}
	
	public function arbol(){
		$this->load->controller('admin/admin_sitio');
		$this->admin_sitio->arbol();
	}
	
	public function dataTable(){
		$this->perfiles->datatable();
	}
}
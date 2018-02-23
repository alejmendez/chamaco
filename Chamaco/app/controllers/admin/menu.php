<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class menu extends control_base{
	public function __construct(){
		parent::__construct();
		$this->load->model('admin/menu_modelo', 'menu');
	}
	
	public function index(){
		$this->pag('admin/menu');
	}
	
	public function arbol(){
		$this->load->controller('admin/admin_sitio');
		$this->admin_sitio->arbol();
	}
	
}
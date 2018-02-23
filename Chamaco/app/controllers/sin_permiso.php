<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class sin_permiso extends control_base{
	protected $sinPermiso = true;
	
	public function index(){
		$this->conf['menuFormulario'] = false;
		$this->pag('sin_permiso');
	}
}
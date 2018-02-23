<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class err404 extends control_base{
	protected $sinPermiso = true;
	
	public function index(){
		$this->conf['menuFormulario'] = false;
		$this->pag('err404');
	}
}
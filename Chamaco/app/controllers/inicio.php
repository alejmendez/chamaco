<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class inicio extends control_base{
	protected $sinPermiso = true;
	
	protected $css = array();
	protected $js  = array();
	
	public function __construct(){
		parent::__construct();
		$this->conf['menuFormulario'] = false;
	}
	
	public function index(){
		$this->pag('inicio');
	}
}
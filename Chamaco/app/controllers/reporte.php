<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class reporte extends control_base{
	protected $css = array();
	protected $js  = array();
	
	public function __construct(){
		parent::__construct();
		$this->conf['menuFormulario'] = false;
		$this->conf['menu'] = false;
		$this->conf['pies'] = false;
		$this->conf['titulo'] = $this->conf['compania'] . ': Modulo de Venta';
		
		$this->load->model('reporte_modelo', 'modelo');
	}
	
	public function index(){
		$this->pag('reporte');
	}
}
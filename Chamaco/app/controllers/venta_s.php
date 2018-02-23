<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class venta_s extends control_base{
	protected $css = array();
	protected $js  = array('jquery.fullscreen-0.4.1.min.js');
	protected $sinPermiso = true;
	protected $iva = 0.12;
	protected $coe_iva = 0.8929;
	
	protected $forma_pago;
	
	protected $idTerminal;
	protected $nombreTerminal;
	
	public function __construct(){
		parent::__construct();
		$this->conf['menuFormulario'] = false;
		$this->conf['menu'] = false;
		$this->conf['pies'] = false;
		$this->conf['titulo'] = $this->conf['compania'] . ': Resumen de Venta';
		
		$this->load->model('venta_modelo', 'modelo');
		$this->terminal();
		
		$this->terminalActivo = $this->_verificarTerminal();
	}
	
	public function index(){
		$this->pag('venta_s');
	}
	
	protected function _verificarTerminal(){
		$this->db->seleccionar('cierre_terminal', 'count(*) as c', "terminal = {$this->idTerminal} and fecha = '{$this->fecha}'");
		$cantidad = (int) $this->db->ur('c');
		
		return $cantidad <= 0;
	}
	
	public function verificarTerminal(){
		exit(json_encode($this->terminalActivo));
	}
	
	protected function terminal(){
		$af = $this->db->seleccionar('terminales', 'id, nombre', array(
			'w' => 'ip = \'' . $this->getIpCliente() . '\''
		));
		
		if ($af <= 0){
			$this->idTerminal = 0;
			$this->nombreTerminal = '';
			return false;
		}
		
		$this->idTerminal = (int) $this->db->ur('id');
		$this->nombreTerminal = $this->db->ur('nombre');
		
		return true;
	}
}

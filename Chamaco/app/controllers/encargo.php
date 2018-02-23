<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class encargo extends control_base{
	protected $css = array('fullcalendar.min.css');
	protected $js  = array('moment.min.js', 'fullcalendar.min.js', 'calendar/es.js');
	
	public function __construct(){
		parent::__construct();
		$this->conf['menuFormulario'] = false;
		
		$this->load->model('encargo_modelo', 'modelo');
		
		/**/
		//$this->load->model('reporte_modelo', 'modelo');
		
		$this->modelo->eliminarElemento('cliente, extra, observacion');
		
		$this->modelo->cargarObjetos(array(
			array(
				"id" => "cedula",
				"tipo" => 'cedula',
				"texto" => 'Cedula',
				"nombreDB" => "cli.cedula:int"
			),
			array(
				"id" => "nombre",
				"tipo" => 'texto',
				"texto" => 'Nombre',
				"nombreDB" => "nombre"
			),
			array(
				"id" => "estatus",
				"tipo" => 'combo',
				"texto" => 'Estatus',
				"valor" => array(
					'e' => 'Encargo',
					'd' => 'Despachado'
				),
				"nombreDB" => "ord.estatus"
			)
		))
		//->cambiarPropiedad('fecha_entrega', array('nombreDB' => 'enc.fecha_entrega:date'))
		->cambiarPropiedad('orden', array('nombreDB' => 'enc.orden:int'));
		
	}
	
	public function index(){
		$this->pag('encargo');
	}
	
	public function obPedidos(){
		$ini = $this->tratarFechas($this->get('start'), 'Y-m-d');
		$fin = $this->tratarFechas($this->get('end'), 'Y-m-d');
		
		exit(json_encode($this->modelo->obPedidos($ini, $fin)));
	}
	
	public function imprimir($id = 0){
		$this->idEncargo = intval($id);
		
		$this->load->library('Html_pdf', array(
			'marges' => array(5, 3, 5, 3)
		));
		
		$this->html_pdf->generar('plantillaspdf/encargo');
	}
	
	protected function condicion(){
		$condicion = $ci->modelo->hacerCondicion('*', true);
		return $condicion;
	}
}
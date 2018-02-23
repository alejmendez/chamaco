<?php
class reporte_diario_modelo extends modelo_form{
	protected $id = 'reporte_diario'; //id del formulario
	protected $titulo = 'Reporte Diario'; //titulo (attr title) del formulario
	//protected $tabla = 'venta';
	protected $url = 'reporte_diario/reporte';
	
	public $campos = array(
		array(
			"id" => "fecha",
			"tipo" => 'fechaRango',
			//'formatoDB' => 'Y-m-d',
			'texto' => 'Fecha',
			'nombreDB' => 'fecha'
		),
		array(
			"id" => "terminal",
			"tipo" => 'combo',
			'texto' => 'Computadora',
			'nombreDB' => 'ter.id:int'
		),
		array(
			"id" => "reporteResumido",
			"tipo" => 'check',
			'texto' => 'Reporte Resumido',
			'valor' => 'si'
		),
		array(
			"id" => "reporteComanda",
			"tipo" => 'check',
			'texto' => 'Reporte Comanda',
			'valor' => 'si'
		),
		array(
			"id" => "boton",
			"tipo" => 'submit',
			"valor" => 'Reporte'
		)
	);
	
	public function __construct(){
	    parent::__construct();
		
		$arr = array('' => 'Seleccione...');
	    $this->db->seleccionar('terminales', 'id, nombre', array('o' => 'nombre'));
		
		foreach($this->db->rs as $row){
			$arr[$row['id']] = $row['nombre'];
		}
		
	    $this
		->val('terminal', $arr, false, false)
		//->valor('fecha', date('d/m/Y - d/m/Y'), false, false)
		->cambiarPropiedad('*, !boton', array('ancho' => 300))
		->contenedor('boton', array('estilo' => 'float: right;'));
	}
}
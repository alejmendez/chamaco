<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class reporte_diario extends control_base{
	protected $css = array();
	protected $js  = array();
	
	public function __construct(){
		parent::__construct();
		$this->conf['menuFormulario'] = false;
		//$this->conf['menu'] = false;
		//$this->conf['pies'] = false;
		$this->conf['titulo'] = $this->conf['compania'] . ': Reporte Diario';
		$this->load->model('reporte_diario_modelo', 'modelo');
	}
	
	public function index(){
		$this->pag('reporte_diario');
	}
	
	protected function condicion(){
		return array(
			str_replace('fecha', 'ord.fecha::date', $this->modelo->hacerCondicion('fecha')),
			$this->modelo->hacerCondicion('*, !fecha'),
			//'ord.estatus != \'e\' and ord.estatus != \'ee\''
			'trim(ven.producto) != \'Encargo\''
		);
	}
	
	public function reporte(){
		$condicion = $condicionEncargo = $condicionTotal = $this->condicion();
		
		foreach($condicion as $k => $v){
			if (trim($v) === ''){
				unset($condicion[$k]);
			}
		}
		
		foreach($condicionEncargo as $k => $v){
			if (trim($v) === ''){
				unset($condicionEncargo[$k]);
			}
		}
		
		foreach($condicionTotal as $k => $v){
			if (trim($v) === ''){
				unset($condicionTotal[$k]);
			}
		}
		
		$fecha = $this->getdate("fecha");
		if (!is_array($fecha)){
			exit("Por Favor, Seleccione una fecha Valida...");
		}
		
		$sufijo = '';
		if (isset($_POST['reporteComanda'])){
			$sufijo = '_c';
		}
		
		$total = array(
			'' => array(
				'fecha' => '',
				'forma_pago1' => 0,
				'forma_pago2' => 0,
				'forma_pago3' => 0,
				'total' => 0,
			)
		);
		
		$totalNotaCredito = array(
			'' => array(
				'fecha' => '',
				'total' => 0,
			)
		);
		
		$totalEncargos = array(
			'' => array(
				'fecha' => '',
				'cantidad' => 0,
				'total' => 0,
			)
		);
		
		if (isset($_POST['reporteResumido'])){
			unset($condicionTotal[2]);
			
			$condicion = implode(' and ', $condicion);
			$condicionTotal = implode(' and ', $condicionTotal);
			
			$this->db->query("
				SELECT
					ord.fecha::date, 
					sum(ord.forma_pago1) as forma_pago1,
					sum(ord.forma_pago2) as forma_pago2,
					sum(ord.forma_pago3) as forma_pago3,
					(sum(ord.forma_pago1) + sum(ord.forma_pago2) + sum(ord.forma_pago3)) as total
				FROM orden" . $sufijo . " as ord
				" . ($condicionTotal === '' ? '' : 'WHERE ' . $condicionTotal) . "
				group by ord.fecha::date
			");
			//$this->db->uq();
			foreach($this->db->rs as $totales){
				$total[''] = $totales;
			}
			
			$this->db->query("
				SELECT
					ord.fecha::date,
					(sum(ord.forma_pago1) + sum(ord.forma_pago2) + sum(ord.forma_pago3)) as total
				FROM nota_credito as ord
				" . ($condicionTotal === '' ? '' : 'WHERE ' . $condicionTotal) . "
				group by ord.fecha::date
			");
			
			foreach($this->db->rs as $totales){
				$totalNotaCredito[''] = $totales;
			}
			
			$condicionEncargo[2] = "(ord.estatus = 'e' or ord.estatus = 'ee')";
			$condicionEncargo = implode(' and ', $condicionEncargo);
			
			$this->db->query("
				SELECT
					ord.fecha::date,
					count(*) as cantidad,
					(sum(ord.forma_pago1) + sum(ord.forma_pago2) + sum(ord.forma_pago3)) as total
				FROM orden" . $sufijo . " as ord
				" . ($condicionEncargo === '' ? '' : 'WHERE ' . $condicionEncargo) . "
				group by ord.fecha::date
			");
			//$this->db->uq();
			foreach($this->db->rs as $totales){
				$totalEncargos[''] = $totales;
			}
			
			$this->db->seleccionar('venta' . $sufijo . ' as ven
			LEFT JOIN orden' . $sufijo . ' as ord on ord.id = ven.orden
			LEFT JOIN terminales as ter on ord.ip = ter.ip', 
			'	
				\'\' as fecha,
				 
				trim(ven.producto) as producto, 
				ven.precio,
				sum(ven.cantidad) as cantidad, 
				(sum(ven.cantidad) * ven.precio) as total', 
			array(
				'w' => $condicion,
				'g' => 'trim(ven.producto), ven.precio',
				'o' => 'trim(ven.producto)'
			));
		}else{
			unset($condicionTotal[2]);
			
			$condicion = implode(' and ', $condicion);
			$condicionTotal = implode(' and ', $condicionTotal);
			
			$this->db->query("
				SELECT
					ord.fecha::date, 
					sum(ord.forma_pago1) as forma_pago1,
					sum(ord.forma_pago2) as forma_pago2,
					sum(ord.forma_pago3) as forma_pago3,
					(sum(ord.forma_pago1) + sum(ord.forma_pago2) + sum(ord.forma_pago3)) as total
				FROM orden" . $sufijo . " as ord
				" . ($condicionTotal === '' ? '' : 'WHERE ' . $condicionTotal) . "
				group by ord.fecha::date
			");
			//$this->db->uq();
			foreach($this->db->rs as $totales){
				$total[$totales['fecha']] = $totales;
			}
			
			$this->db->query("
				SELECT
					ord.fecha::date,
					(sum(ord.forma_pago1) + sum(ord.forma_pago2) + sum(ord.forma_pago3)) as total
				FROM nota_credito as ord
				" . ($condicionTotal === '' ? '' : 'WHERE ' . $condicionTotal) . "
				group by ord.fecha::date
			");
			
			foreach($this->db->rs as $totales){
				$totalNotaCredito[$totales['fecha']] = $totales;
			}
			
			$condicionEncargo[2] = "(ord.estatus = 'e' or ord.estatus = 'ee')";
			$condicionEncargo = implode(' and ', $condicionEncargo);
			
			$this->db->query("
				SELECT
					ord.fecha::date,
					count(*) as cantidad,
					(sum(ord.forma_pago1) + sum(ord.forma_pago2) + sum(ord.forma_pago3)) as total
				FROM orden" . $sufijo . " as ord
				" . ($condicionEncargo === '' ? '' : 'WHERE ' . $condicionEncargo) . "
				group by ord.fecha::date
			");
			
			foreach($this->db->rs as $totales){
				$totalEncargos[$totales['fecha']] = $totales;
			}
			
			$this->db->query("
				SELECT 
					ord.fecha::date,
					ven.producto, 
					ven.precio, 
					sum(ven.cantidad) AS cantidad, 
					sum(ven.cantidad) * ven.precio AS total
				FROM venta" . $sufijo . " as ven 
					LEFT JOIN orden" . $sufijo . " as ord on ord.id = ven.orden 
					LEFT JOIN terminales as ter on ord.ip = ter.ip
				" . ($condicion === '' ? '' : 'WHERE ' . $condicion) . " 
				GROUP BY 
					ord.fecha::date, ven.producto, ven.precio
				ORDER BY ord.fecha::date, ven.producto
			");
		}
		
		//$this->db->uq();
		if ($this->db->af <= 0){
			exit('No se Encontraron Registros.');
		}
		
		$this->load->library('html_pdf', array(
			'orientacion' => 'P',
			//'formato' => array(100,70)
		));
		
		$this->html_pdf->generar('plantillaspdf/reporte_diario', array(
			'comanda' => isset($_POST['reporteComanda']),
			'totalDetallado' => $total,
			'totalEncargos' => $totalEncargos,
			'totalNotaCredito' => $totalNotaCredito,
			'productos' => array(
				'precio' => '110',
				'cantidad' => '2',
				'producto' => 'pizza'
			)
		));
	}
}
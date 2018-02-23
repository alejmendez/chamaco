<?php
class venta_modelo extends modelo_form{
	protected $id = 'ventas'; //id del formulario
	protected $titulo = 'Ventas'; //titulo (attr title) del formulario
	protected $tabla = 'venta';
	//protected $url = 'autenticacion';
	
	protected $forzarSalida = false;
	protected $metodo = 'JSON';
	
	public $campos = array(
		array("id" => "id", "nombreDB" => "id:int"),
		array(
			"id" => "orden",
			"nombreDB" => "orden:int"
		),
		array(
			"id" => "id_producto",
			"nombreDB" => "id_producto:int"
		),
		array(
			"id" => "producto",
			"nombreDB" => "producto"
		),
		array(
			"id" => "cantidad",
			"nombreDB" => "cantidad:float"
		),
		array(
			"id" => "precio",
			"nombreDB" => "precio:float"
		),
		array(
			"id" => "fecha",
			"nombreDB" => "fecha:date"
		)
	);
	
	public function __construct(){
	    parent::__construct();
	}
	
	public function guardar(){
		parent::guardar();
	}
	
	public function reportePorDias($dia1 = '', $dia2 = ''){
		$dia1 = $dia1 === '' ? time() : $this->tratarFechas($dia1);
		$dia2 = $dia2 === '' ? $dia1 : $this->tratarFechas($dia2);
		
		if ($dia1 === '' || $dia2 === ''){
			return false;
		}
		
		if ($dia1 > $dia2){
			$aux = $dia1;
			$dia1 = $dia2;
			$dia2 = $aux;
		}
		
		$dia1 = date('Y-m-d', $dia1);
		$dia2 = date('Y-m-d', $dia2);
		
		$this->db->query("
		SELECT
			fp.forma,
			sum(cantidad * precio) as total
		FROM orden
		left join venta as ven on ven.orden = orden.id
		left join forma_pagos as fp on fp.id = orden.forma_pago
		where orden.fecha between '" . $dia1 . " 00:00:00' and '" . $dia2 . " 23:59:59'
		and trim(orden.ip) = '" . trim($this->ci->getIpCliente()) . "'
		Group by fp.forma");
		
		if ($this->db->af <= 0){
			return 0;
		}
	}
}
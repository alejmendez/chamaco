<?php
class encargo_modelo extends modelo_form{
	protected $id = 'encargo'; //id del formulario
	protected $titulo = 'Encargo'; //titulo (attr title) del formulario
	protected $tabla = 'encargo';
	protected $url = 'encargo/imprimir';
	
	protected $forzarSalida = false;
	//protected $metodo = 'JSON';
	
	public $campos = array(
		array(
			"id" => "id",
			"nombreDB" => "id:int"
		),
		array(
			"id" => "orden",
			"tipo" => 'numerico',
			"texto" => 'Orden',
			"nombreDB" => "orden:int"
		),
		array(
			"id" => "fecha_entrega",
			"texto" => 'Fecha de Entrega',
			"tipo" => "fechaRango",
			"nombreDB" => "fecha_entrega:date"
		),
		array(
			"id" => "observacion",
			"texto" => 'Observacion',
			"tipo" => 'textoArea',
			"nombreDB" => "observacion"
		),
		array(
			"id" => "extra",
			"tipo" => 'numerico',
			"texto" => 'Extra',
			"nombreDB" => "extra:decimal"
		),
		array(
			"id" => "pago",
			"tipo" => 'numerico',
			"texto" => 'Pago',
			"nombreDB" => "pago:decimal"
		),
		array(
			"id" => "cliente",
			"tipo" => 'numerico',
			"texto" => 'Cliente',
			"nombreDB" => "cliente:int"
		)
	);
	
	public function obPedidos($ini = false, $fin = false){
		$condicion = '';
		
		if ($ini !== false && $fin !== false){
			$condicion = ' AND enc.fecha_entrega between \'' . $ini . '\' and \'' . $fin . '\'';
		}
		
		$arr = $this->db->seleccionarArray(
			'encargo as enc, orden_tmp as ord, cliente as cli', 
			'ord.id, (cli.nombre || \' \' || cli.apellido) as title, enc.fecha_entrega as start', 
			array(
				'w' => '
					ord.id = enc.orden 
					AND enc.cliente = cli.id
				' . $condicion
			));
		//$this->db->uq();
		return $arr;
	} 
}
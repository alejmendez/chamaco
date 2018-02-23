<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class pago extends control_base{
	protected $css = array('jquery.datetimepicker.css');
	protected $js  = array('jquery.alphanumeric.js', 'jquery.datetimepicker.js');

	protected $idTerminal = 0;
	protected $nombreTerminal = '';
	protected $terminal_orden = 0;

	protected $stablas = '_tmp';
	protected $comanda = false;
	protected $correlativo = 0;

	protected $idforma_pago = 0;
	protected $forma_pago = array();
	protected $idCliente = 0;

	public function __construct(){
		parent::__construct();

		$this->conf['menuFormulario'] = false;
		$this->conf['menu'] = false;
		//$this->conf['pies'] = false;
		$this->conf['titulo'] = $this->conf['compania'] . ': Modulo de Pago';
		$this->load->model('encargo_modelo');
		$this->load->model('cliente_modelo', 'cliente');

		$this->idforma_pago = intval($this->get('forma_pago'));

		$this->terminal();

		$this->forma_pago = $this->get('forma_pago');

		if ($this->idTerminal === 0){
			$this->salida(array('s' => 'n', 'msj' => 'Este Terminal no esta Autorizado para El modulo de Pago<br />Contacte al Administrador del Sistema'));
		}
	}

	public function index(){
		$this->db->actualizar('orden' . $this->stablas, array('bloqueo' => 0), false, 'bloqueo = ' . $this->idTerminal);
		$this->pag('pago');
	}

	protected function terminal(){
		$af = $this->db->seleccionar('terminales', 'id, nombre, terminal_orden', array(
			'w' => 'ip = \'' . $this->getIpCliente() . '\''
		));

		if ($af <= 0){
			$this->idTerminal = 0;
			$this->nombreTerminal = '';
			return false;
		}

		$this->idTerminal = (int) $this->db->ur('id');
		$this->nombreTerminal = $this->db->ur('nombre');
		$this->terminal_orden = $this->db->ur('terminal_orden');


        /*$this->idTerminal = 5;
		$this->nombreTerminal = "Dorela";
		$this->terminal_orden = 5;

		$this->idTerminal = 4;
		$this->nombreTerminal = "Dorela";
		$this->terminal_orden = 4;*/

		return true;
	}

	public function btnreportez(){
		set_time_limit(0);

		$this->load->library('Impresora');
		$this->impresora->conf('fiscal');
		$impresion = $this->impresora->reportez();
		exit;
	}

	public function prueba(){
		//$this->load->library('Impresora');
		//$this->impresora->conf('fiscal');
		//$impresion = $this->impresora->reportez();


		$id = intval(2993);
		$this->load->library('Impresora');

		$this->correlativo = 1;

		$cantImpresion = 1;
		$this->impresora->conf('comanda2');
		$this->impresora->plantilla('factura');

		$impresion = $this->imprimirComandaPequenna($id);

		exit;
		$cliente = array();
		$dataFactura = array();

		$this->load->library('Impresora');
		$this->impresora->conf('fiscal');
		$this->impresora->plantilla('factura');

		$imp = $this->impresora->imp;

		//$imp->prueba = true;
		/*
		$ini = '0008180';
		$fin = '0008608';
		$imp->cmd('RF' . $ini . $fin);

		exit;
		*/
		/*
		$cliente = array(
			'nombre' => 'ASC C.I.E.P Uncion de Dios',
			'cedula' => 'J-31394228-6',
			'direccion' => 'Av. España, Nro 212',
			'telefono' => '0285-6510312'
		);
		*/
		$dataFactura = array(
			'orden' => str_pad(5555, 8, "0", STR_PAD_LEFT),
			'fecha' => date("d-m-Y"),
			'hora' => date("h:i:s a"),
			'cajero' => 'cajero 1',
			'correlativo' => '55',
			'parallevar' => 0,
			'forma_pago' => array(
				// 1 = efectivo | 2 = debito | 3 = credito
				1 => 2000 //no importa la cantidad si es un solo medio de pago
			),
			//'idforma_pago' => 1,
		);

		$this->impresora->datosFactura(array_merge($cliente, $dataFactura));

		//$fecha = '160914';
		//$imp->cmd('I2X' . $fecha . $fecha);
		/*
		$fecha = '160914';
		$ini = '0000021';
		$fin = '0000050';
		$imp->cmd('RF' . $ini . $fin);
		*/
		/**/

		/*$imp->cmd('i01Nombre: Farmatodo C.A');
		$imp->cmd('i02RIF: J-00020200-1');
		$imp->cmd('i03Direccion: Ciudad Bolivar');
		$imp->cmd('i04Telefono: 0414-9892627');
		$imp->cmd('@Numero de Pedido: 55');
		*/
		$arrventa = array(
			array(1000, 1, 'TORTA GRANDE CHOCOLATE', 12),
			//array(10, 1, 'Agua qna2', 12),
			//array(10, 1, 'Agua qna3', 12),
		);

		$imp->imprimir($arrventa);

		//$imp->notaCredito($arrventa);

		$imp->totalizar();


	}

	public function reparar_registros(){
		$this->db->query('
		select
			ord.id, ord.forma_pago, sum(ven.cantidad * ven.precio) as total
		from orden_tmp as ord
		left join venta_tmp as ven on ord.id = ven.orden
		where (forma_pago1 + forma_pago2 + forma_pago3) = 0
		group by ord.id, ord.forma_pago');

		foreach($this->db->rs as $reg){
			$this->db->query('update orden_tmp set forma_pago' . $reg['forma_pago'] . ' = ' . $reg['total'] . ' where id = ' . $reg['id']);
			$this->db->query('update orden_c set forma_pago' . $reg['forma_pago'] . ' = ' . $reg['total'] . ' where tmp = ' . $reg['id']);
			$this->db->query('update orden set forma_pago' . $reg['forma_pago'] . ' = ' . $reg['total'] . ' where tmp = ' . $reg['id']);
		}

		$this->db->query('select orden, cliente from encargo');

		foreach($this->db->rs as $reg){
			$this->db->query('update orden_tmp set cliente = ' . $reg['cliente'] . ' where id = ' . $reg['orden']);
			$this->db->query('update orden_c set cliente = ' . $reg['cliente'] . ' where tmp = ' . $reg['orden']);
			$this->db->query('update orden set cliente = ' . $reg['cliente'] . ' where tmp = ' . $reg['orden']);
		}

		exit;
	}

	public function buscar($id){
		$idn = intval($id);

		$cedula = trim($id);

		$condicion = array('trim(cli.cedula) = \'' . $cedula . '\'');

		if ($idn != 0){
			$condicion[] = 'ord.id = ' . $idn;
			$condicion[] = 'ord.nro_factura = ' . $idn;
		}

		$this->db->query('
		select
			ord.id, fecha, estatus, (forma_pago1 + forma_pago2 + forma_pago3) as pago, pendiente,
			cedula, nombre, apellido, direccion, telefono, comanda
		from orden_tmp as ord
		left join cliente as cli on ord.cliente = cli.id
		where ' . implode(' or ', $condicion));

		//$this->db->uq();

		$resultado = $this->db->rsAMatriz($this->db->rs, false);
		if (empty($resultado)){
			$this->salida(array('s' => 'n', 'msj' => 'No se Encontraron Registros'));
		}

		$estatus = array(
			'e' => 'Encargo',
			'ee' => 'Encargo Entregado',
			'nc' => 'Nota de Credito',
			'p' => 'Pagado',
			'pc' => 'Pendiente por Cobrar',
			'a' => 'Anulado'
		);

		foreach($resultado as $k => $v){
			$resultado[$k]['estatusTexto'] = $estatus[trim($resultado[$k]['estatus'])];

			foreach($resultado[$k] as $kk => $vv){
				if (is_null($resultado[$k][$kk])){
					$resultado[$k][$kk] = '';
				}
			}
		}

		$this->salida(array('s' => 's', 'msj' => '', 'registros' => $resultado));
	}

	public function notaCredito($id = 0){
		$id = intval($id);

		$arrventa = array();

		if ($id != 0){
			$this->db->seleccionar('orden_tmp' . $this->stablas, '*', 'id = ' . $id);
			$orden = $this->db->ur();

			$this->forma_pago = array();
			if (floatval($orden['forma_pago1']) > 0){
				$this->forma_pago[1] = floatval($orden['forma_pago1']);
			}

			if (floatval($orden['forma_pago2']) > 0){
				$this->forma_pago[2] = floatval($orden['forma_pago2']);
			}

			if (floatval($orden['forma_pago3']) > 0){
				$this->forma_pago[3] = floatval($orden['forma_pago3']);
			}

			$detalle = array();
			if ($orden['estatus'] == 'e'){
				$detalle[] = array(
					'precio' => array_sum($this->forma_pago),
					'cantidad' => 1,
					'texto' => 'Encargo'
				);
			}else{
				$this->db->seleccionar('venta' . $this->stablas, 'id, precio, cantidad, producto as texto', 'orden = ' . $id, false, true);

				foreach($this->db->rs as $row){
					$detalle[] = $row;
				}
			}


			/*$this->db->seleccionar('encargo', '*', 'orden = ' . $id);
			$encargo = $this->db->ur();*/


			$this->cliente->buscar('id = \'' . $orden['cliente'] . '\'');
			$cliente = $this->cliente->dbSalida;
		}else{
			$this->idCliente = $this->guardarCliente();
			$cliente = $this->buscarCliente($this->get('cedula'), true);
			$detalle = $this->get('detalle');
		}

		$this->load->library('Impresora');
		$this->impresora->conf('fiscal');
		$this->impresora->plantilla('factura');

		$dataFactura = array(
			'forma_pago' => $this->forma_pago
		);

		$this->impresora->datosFactura(array_merge($cliente, $dataFactura));

		foreach($detalle as $det){
			$arrventa[] = array($det['precio'], $det['cantidad'], $det['texto'], 12);
		}

		$this->impresora->notaCredito($arrventa);

		$datos = array(
			'fecha' => $this->fechaHora,
			'estatus' => 'nc', // nota de credito
			'cliente' => $this->idCliente,
			'ip' => $this->getIpCliente(),
			'terminal_venta' => $this->idTerminal,
			'terminal_orden' => $this->idTerminal
		);

		foreach($this->forma_pago as $k => $v){
			$this->forma_pago[$k] = floatval($v);
			$datos['forma_pago' . $k] = floatval($v);
		}

		$this->db->guardar('nota_credito', $datos, false, 'id');

		$nota_credito = (int) $this->db->uid;

		foreach($detalle as $det){
			$datos = array(
				'nota_credito' => $nota_credito,
				'fecha' => $this->fechaHora,
				'producto' => $det['texto'],
				'cantidad' => $det['cantidad'],
				'precio' => $det['precio']
			);

			$this->db->guardar('nota_credito_detalle', $datos);
		}

		$salida = array('s' => 's', 'msj' => 'Nota de Credito Creada');

		if ($id != 0){
			return $salida;
		}

		$this->salida($salida);
	}

	/*public function consulta(){
		$orden = $this->db->seleccionarArray('orden' . $this->stablas, '*', array(
			'w' => 'estatus = \'pc\' and terminal_orden = ' . $this->idTerminal,
			'o' => 'fecha'
		));

		foreach($orden as $ll => $v){
			$orden[$ll]['fecha'] = $this->tratarFechas($orden[$ll]['fecha'], 'd/m/Y h:i:s a');
		}

		exit(json_encode($orden));
	}*/

    public function consulta(){
        $orden = $this->db->seleccionarArray('orden' . $this->stablas, 'orden' . $this->stablas.'.*, ubicacion.nombre as ubicacion_venta, secciones.nombre as seccion_venta', array(
			'w' => 'orden' . $this->stablas.'.estatus = \'pc\' and orden' . $this->stablas.'.terminal_orden = ' . $this->idTerminal,
            'lj' => 'ubicacion ON ubicacion.id = orden' . $this->stablas.'.ubicacion LEFT JOIN secciones ON secciones.id = ubicacion.id_seccion',
			'o' => 'orden' . $this->stablas . '.fecha'
		));

		foreach($orden as $ll => $v){
			$orden[$ll]['fecha'] = $this->tratarFechas($orden[$ll]['fecha'], 'd/m/Y h:i:s a');

            if($orden[$ll]['ubicacion_venta'] == false)
                $orden[$ll]['ubicacion_venta'] = 'Sin Ubicacion';

			if($orden[$ll]['seccion_venta'] == false)
                $orden[$ll]['seccion_venta'] = 'Sin Seccion';
		}

		exit(json_encode($orden));
	}

	public function consultaOrden($id){
		$id = intval($id);
		$detalle = $this->db->seleccionarArray('venta' . $this->stablas, '*', array(
			'w' => 'orden = ' . $id
		));

		$detalleOrden = $this->db->seleccionarArray('orden' . $this->stablas, '*', array(
			'w' => 'id = ' . $id
		));

		$detalleOrden = $detalleOrden[0];
		$detalleOrden['bloqueo'] = intval($detalleOrden['bloqueo']);

		if ($detalleOrden['bloqueo'] == $this->idTerminal){
			$detalleOrden['bloqueo'] = 0;
		}

		$salida = array(
			'orden' => $detalleOrden,
			'detalle' => $detalle
		);

		$this->db->actualizar('orden' . $this->stablas, array('bloqueo' => 0), false, 'bloqueo = ' . $this->idTerminal);

		if ($detalleOrden['bloqueo'] == 0){
			$this->db->actualizar('orden' . $this->stablas, array('bloqueo' => $this->idTerminal), false, 'id = ' . $id);
		}

		exit(json_encode($salida));
	}

	public function informacion($id){
		$id = intval($id);
		$detalle = $this->db->seleccionarArray('venta' . $this->stablas, '*', array(
			'w' => 'orden = ' . $id
		));

		$detalleOrden = $this->db->seleccionarArray('orden' . $this->stablas, '*', array(
			'w' => 'id = ' . $id
		));

		$detalleOrden = $detalleOrden[0];

		$salida = array(
			'orden' => $detalleOrden,
			'detalle' => $detalle
		);

		exit(json_encode($salida));
	}

	public function entregar($id){
		$id = intval($id);
		$datos = array(
			'estatus' => 'ee'
		);

		$this->db->actualizar('orden_tmp', $datos, false, 'id = ' . $id);
		$this->db->actualizar('orden', $datos, false, 'tmp = ' . $id);

		$this->salida(array('s' => 's', 'msj' => 'Registro Modificado Satisfactoriamente'));
	}

	public function imprimirEncargo($idOrden = 0){
		$idOrden = (int) $idOrden;

		$this->load->library('Impresora');
		$this->impresora->conf('comanda1');
		$this->impresora->plantilla('encargo');

		if ($idOrden === 0){
			return false;
		}else{
			$orden = $this->db->seleccionarArray('orden' . $this->stablas, '*', 'id = ' . $idOrden);
		}

		if (empty($orden)){
			exit('La Orden "' . $idOrden . '" No Existe.');
		}

		$orden = $orden[0];

		$formas_pagos = $this->db->seleccionarEnMatriz('forma_pagos', 'id, forma', 'id');

		if (empty($ventas)){
			$ventas = $this->db->seleccionarArray('venta' . $this->stablas, 'producto, precio, cantidad', 'orden = ' . $orden['id']);
		}

		$this->impresora->datosFactura(array(
			'orden' => str_pad($orden['id'], 8, "0", STR_PAD_LEFT),
			'fecha' => date("d-m-Y"),
			'hora' => date("h:i:s a"),
			'cajero' => $this->nombreTerminal,
			'forma_pago' => $this->forma_pago,
			'idforma_pago' => $this->idforma_pago,
			'formas_pagos' => $formas_pagos,
			'correlativo' => str_pad(intval(substr($this->correlativo, -2)), 2, '0', STR_PAD_LEFT)
		));

		$encargo = $this->db->seleccionarArray('encargo as enc', 'enc.extra, enc.cliente, enc.fecha_entrega, enc.pago, enc.observacion, cli.cedula, cli.nombre, cli.apellido, (cli.nombre || \' \' || cli.apellido) nombre_apellido, cli.direccion, cli.telefono', array(
			'w' => 'orden = ' . $idOrden,
			'lj' => 'cliente as cli on enc.cliente = cli.id'
		));

		$encargo = $encargo[0];
		//var_dump($encargo);
		$encargo['fecha_entrega'] = $this->tratarFechas($encargo['fecha_entrega'], 'd/m/Y h:i:s a');

		$encargo['cedula'] = strtoupper(trim($encargo['cedula']));
		$encargo['cedula'] = preg_match('/^\s*\d/i', $encargo['cedula']) ? ('CEDULA: ' . number_format($encargo['cedula'], 0, '', '.')) : ('RIF: '  . $encargo['cedula']);

		$arrventa = array();
		$total = 0;
		foreach($ventas as $venta){
			$venta['producto'] = strtoupper($venta['producto']);

			$arrventa[] = array($venta['precio'], $venta['cantidad'], $venta['producto'], 12);

			$total += ($venta['precio'] * $venta['cantidad']);
		}

		$encargo['pendiente'] = number_format((($total + $encargo['extra']) - $encargo['pago']), 2, ',', '.');
		$encargo['total_encargo'] = number_format($total + $encargo['extra'], 2, ',', '.');

		$pago = $encargo['pago'];
		$encargo['pago'] = number_format($pago, 2, ',', '.');
		$encargo['extra'] = number_format($encargo['extra'], 2, ',', '.');

		$this->impresora->datosFactura($encargo);

		$salida = $this->impresora->imprimir($arrventa);

		//var_dump($encargo);
		// imprimir por la fiscal
		$this->impresora->conf('fiscal');
		$this->impresora->plantilla('encargo');

		$arrventa = array(
			array($pago, 1, 'Encargo', 12)
		);

		$this->impresora->imprimir($arrventa);

		return $salida;
	}

	public function getUltimaOrden(){
		//$this->db->seleccionar('orden' . $this->stablas, 'max(id) as id', 'terminal_venta = ' . $this->idTerminal);

        $this->db->seleccionar('orden' . $this->stablas, 'id', array(
				'w' => 'fecha_procesado IS NOT NULL AND estatus = \'p\' AND terminal_venta = ' . $this->idTerminal,
				'o' => 'fecha_procesado desc',
				'l' => array(1, -1)
            )
        );

		$this->salida(array(
			's' => 's',
			'msj' => 'id encontrado',
			'id' => intval($this->db->ur('id'))
		));
	}

	public function imprimirOrden($id = 0){
		$id = intval($id);

		$this->db->seleccionar('orden' . $this->stablas, 'comanda', 'id = ' . $id);
		$comanda = intval($this->db->ur('comanda'));

		$this->load->library('Impresora');
		$this->impresora->conf($comanda == 1 ? 'comanda1' : 'fiscal');

		$this->impresora->plantilla('factura');

		$salida = $this->imprimir($id);

		if (!$salida){
			$this->salida(array('s' => 'n', 'msj' => 'Error al Imprimir la Factura'));
		}

		$this->salida(array('s' => 's', 'msj' => 'Se Imprimio la orden'));
	}

	public function imprimir($idOrden = 0, $ventas = array()){
		$idOrden = (int) $idOrden;
		if ($idOrden === 0){
			$orden = $this->db->seleccionarArray('orden' . $this->stablas, '*', array(
				//'w' => 'ip = \'' . $this->getIpCliente() . '\'',
				'o' => 'fecha desc',
				'l' => array(1, -1)
			));
		}else{
			$orden = $this->db->seleccionarArray('orden' . $this->stablas, '*', 'id = ' . $idOrden);
		}

		if (empty($orden)){
			exit('La Orden "' . $idOrden . '" No Existe.');
		}

		$orden = $orden[0];

		$formas_pagos = $this->db->seleccionarEnMatriz('forma_pagos', 'id, forma', 'id');

		if (empty($ventas)){
			$ventas = $this->db->seleccionarArray('venta' . $this->stablas, 'producto, precio, cantidad', 'orden = ' . $orden['id']);
		}

		$total = 0;

		if ($this->forma_pago === false){
			$this->forma_pago = array(
				1 => floatval($orden['forma_pago1']),
				2 => floatval($orden['forma_pago2']),
				3 => floatval($orden['forma_pago3'])
			);

			foreach($this->forma_pago as $k => $v){
				if ($v <= 0){
					unset($this->forma_pago[$k]);
				}else{
					$total += $v;
				}
			}
		}

		if ($orden['estatus'] == 'e' || $orden['estatus'] == 'ee'){
			$ventas = array(
				array(
					'producto' => 'Encargo',
					'precio' => $total,
					'cantidad' => 1
				)
			);
		}

		$cliente = $this->buscarCliente($this->get('cedula'), true);

		$datosFactura = array(
			'orden' => str_pad($orden['id'], 8, "0", STR_PAD_LEFT),
			'fecha' => date("d-m-Y"),
			'hora' => date("h:i:s a"),
			'cajero' => $this->nombreTerminal,
			'correlativo' => str_pad(intval(substr($this->correlativo, -2)), 2, '0', STR_PAD_LEFT),
			'forma_pago' => $this->forma_pago,
			'formas_pagos' => $formas_pagos,
			'parallevar' => $orden['parallevar']
		);

		if ($cliente !== false && !empty($cliente)){
			$datosFactura = array_merge($datosFactura, $cliente);
		}

		$this->impresora->datosFactura($datosFactura);

		$arrventa = array();
		foreach($ventas as $venta){
			//$venta['precio'] = round($venta['precio'] * $venta['cantidad'] * $this->coe_iva, 2);
			//$venta['precio'] = $venta['precio'];
			$venta['producto'] = strtoupper($venta['producto']);

			$arrventa[] = array($venta['precio'], $venta['cantidad'], $venta['producto'], 12);
		}

		$salida = $this->impresora->imprimir($arrventa);

		return $salida;
	}

	public function imprimirComandaPequenna($idOrden = 0, $ventas = array()){
		$idOrden = (int) $idOrden;
		if ($idOrden === 0){
			$orden = $this->db->seleccionarArray('orden' . $this->stablas, '*', array(
				//'w' => 'ip = \'' . $this->getIpCliente() . '\'',
				'o' => 'fecha desc',
				'l' => array(1, -1)
			));
		}else{
			$orden = $this->db->seleccionarArray('orden' . $this->stablas, '*', 'id = ' . $idOrden);
		}

		if (empty($orden)){
			exit('La Orden "' . $idOrden . '" No Existe.');
		}

		$orden = $orden[0];

		$formas_pagos = $this->db->seleccionarEnMatriz('forma_pagos', 'id, forma', 'id');

		if (empty($ventas)){
			$ventas = $this->db->seleccionarArray('venta' . $this->stablas, 'producto, precio, cantidad', 'orden = ' . $orden['id']);
		}

		$total = 0;

		if ($this->forma_pago === false){
			$this->forma_pago = array(
				1 => floatval($orden['forma_pago1']),
				2 => floatval($orden['forma_pago2']),
				3 => floatval($orden['forma_pago3'])
			);

			foreach($this->forma_pago as $k => $v){
				if ($v <= 0){
					unset($this->forma_pago[$k]);
				}else{
					$total += $v;
				}
			}
		}

		if ($orden['estatus'] == 'e' || $orden['estatus'] == 'ee'){
			$ventas = array(
				array(
					'producto' => 'Encargo',
					'precio' => $total,
					'cantidad' => 1
				)
			);
		}

        $ubicacion = 'Sin Ubicacion';

        if (isset($orden['ubicacion'])){
            if ($orden['ubicacion'] != false){
                $ubicacion = $this->db->seleccionarArray('ubicacion', 'nombre', 'id = ' . $orden['ubicacion']);
                $ubicacion = $ubicacion[0]['nombre'];
            }
		}

		$cliente = $this->buscarCliente($this->get('cedula'), true);

		$datosFactura = array(
			'orden' => str_pad($orden['id'], 8, "0", STR_PAD_LEFT),
			'fecha' => date("d-m-Y"),
			'hora' => date("h:i:s a"),
			'cajero' => $this->nombreTerminal,
			'correlativo' => str_pad(intval(substr($this->correlativo, -2)), 2, '0', STR_PAD_LEFT),
			'forma_pago' => $this->forma_pago,
			'formas_pagos' => $formas_pagos,
			'parallevar' => $orden['parallevar'],
            'ubicacion' => $ubicacion
		);

		if ($cliente !== false && !empty($cliente)){
			$datosFactura = array_merge($datosFactura, $cliente);
		}

		$this->impresora->datosFactura($datosFactura);

		$arrventa = array();
		foreach($ventas as $venta){
			//$venta['precio'] = round($venta['precio'] * $venta['cantidad'] * $this->coe_iva, 2);
			//$venta['precio'] = $venta['precio'];
			$venta['producto'] = strtoupper($venta['producto']);

			$arrventa[] = array($venta['precio'], $venta['cantidad'], $venta['producto'], 12);
		}

		$salida = $this->impresora->imprimir($arrventa);

		return $salida;
	}

    public function imprimirAgregarPedido($idOrden = 0, $ventas = array()){
		$idOrden = (int) $idOrden;

		if ($idOrden === 0){
			$orden = $this->db->seleccionarArray('orden' . $this->stablas, '*', array(
				'o' => 'fecha desc',
				'l' => array(1, -1)
			));
		}else{
			$orden = $this->db->seleccionarArray('orden' . $this->stablas, '*', 'id = ' . $idOrden);
		}

		if (empty($orden)){
			exit('La Orden "' . $idOrden . '" No Existe.');
		}

		$orden = $orden[0];

		$formas_pagos = $this->db->seleccionarEnMatriz('forma_pagos', 'id, forma', 'id');

		if (empty($ventas)){
			$ventas = $this->db->seleccionarArray('venta_agregar', 'producto, precio, cantidad', 'orden = ' . $orden['id']);
		}

		$total = 0;

		if ($this->forma_pago === false){
			$this->forma_pago = array(
				1 => floatval($orden['forma_pago1']),
				2 => floatval($orden['forma_pago2']),
				3 => floatval($orden['forma_pago3'])
			);

			foreach($this->forma_pago as $k => $v){
				if ($v <= 0){
					unset($this->forma_pago[$k]);
				}else{
					$total += $v;
				}
			}
		}

		if ($orden['estatus'] == 'e' || $orden['estatus'] == 'ee'){
			$ventas = array(
				array(
					'producto' => 'Encargo',
					'precio' => $total,
					'cantidad' => 1
				)
			);
		}

		$cliente = $this->buscarCliente($this->get('cedula'), true);

		$datosFactura = array(
			'orden' => str_pad($orden['id'], 8, "0", STR_PAD_LEFT),
			'fecha' => date("d-m-Y"),
			'hora' => date("h:i:s a"),
			'cajero' => $this->nombreTerminal,
			'correlativo' => str_pad(intval(substr($this->correlativo, -2)), 2, '0', STR_PAD_LEFT),
			'forma_pago' => $this->forma_pago,
			'formas_pagos' => $formas_pagos,
			'parallevar' => $orden['parallevar']
		);

		if ($cliente !== false && !empty($cliente)){
			$datosFactura = array_merge($datosFactura, $cliente);
		}

		$this->impresora->datosFactura($datosFactura);

		$arrventa = array();
		foreach($ventas as $venta){
			$venta['producto'] = strtoupper($venta['producto']);

			$arrventa[] = array($venta['precio'], $venta['cantidad'], $venta['producto'], 12);
		}

        $this->db->eliminar('venta_agregar', 'orden = ' . $orden['id']);
		$salida = $this->impresora->imprimir($arrventa);

		return $salida;
	}

	public function cerrarTerminal(){
		$this->db->seleccionar('terminales', 'id');

		foreach($this->db->rs as $terminal){
			$datosTerminal = array(
				'terminal' => intval($terminal['id']),
				'fecha' => date('Y-m-d') //es necesario verificar el estado de la caja al dia, hay q eliminar el boton de venta y bloquear los metodos de venta para mayor seguridad, realizar un metodo q compruebe el estatus del terminal
			);

			$this->db->guardar('cierre_terminal', $datosTerminal);
		}
	}

	protected function _procesar($id, $retornar = false){
		$this->db->seleccionar('orden' . $this->stablas, 'max(nro_factura) as nro_factura');

		$datos = array(
			//'forma_pago' => $this->get('forma_pago'),
			'estatus' => ($retornar ? 'e' : 'p'), // procesada
			'cliente' => $this->idCliente,
			'nro_orden' => $this->correlativo,
			'terminal_venta' => $this->idTerminal,
			'comanda' => $this->comanda ? 1 : 0,
			'total' => $this->get('total'),
			'nro_factura' => $this->comanda ? 0 : intval($this->db->ur('nro_factura')) + 1,
            'fecha_procesado' => date('Y-m-d h:i:s')
		);

		foreach($this->forma_pago as $k => $v){
			$this->forma_pago[$k] = floatval($v);
			$datos['forma_pago' . $k] = floatval($v);
		}

		$this->db->actualizar('orden' . $this->stablas, $datos, false, 'id = ' . $id);

		$this->correlativo = str_pad(intval(substr($this->correlativo, -2)), 2, '0', STR_PAD_LEFT);

		return $this->duplicar($id);
	}

	public function procesar($id, $retornar = false){
		$id = intval($id);
		$this->load->library('Impresora');

		$this->comanda = $this->get('comanda') === 'true';
		$this->idCliente = $this->guardarCliente();

		$this->correlativo = ($retornar === false) ? ($this->correlativo() + 1) : 0;

		$cantImpresion = 1;
		$this->impresora->conf($this->comanda || $retornar ? 'comanda1' : 'fiscal');
		$this->impresora->plantilla('factura');

		$impresion = $this->imprimir($id);

		while($impresion === false && $cantImpresion++ < 1){
			sleep(1);
			$impresion = $this->imprimir($id);
		}

		if ($impresion === false){
			$this->salida(array('s' => 'n', 'msj' => 'Error al Imprimir Factura Nro: ' . $id));
		}

		/**/
		$cantImpresion = 1;
		$this->impresora->conf('comanda2');
		$this->impresora->plantilla('factura');

		$impresion = $this->imprimir($id);

		while($impresion === false && $cantImpresion++ < 1){
			sleep(1);
			$impresion = $this->imprimir($id);
		}


		if ($impresion === false){
			$this->salida(array('s' => 'n', 'msj' => 'Error al Imprimir Factura Nro: ' . $id));
		}

		/**/
		$this->_procesar($id, $retornar);

		if ($retornar){
			return array('s' => 's', 'msj' => 'Orden Procesada', 'correlativo' => $this->correlativo);
		}

		$this->salida(array('s' => 's', 'msj' => 'Orden Procesada', 'correlativo' => $this->correlativo));
	}

	public function anular($id){
		$id = intval($id);

		$this->db->seleccionar('orden' . $this->stablas, '*', 'id = ' . $id);
		$orden = $this->db->ur();

		/*
		'e' => 'Encargo',
		'ee' => 'Encargo Entregado',
		'p' => 'Pagado',
		'pc' => 'Pendiente por Cobrar',
		'a' => 'Anulado'
		*/
		$estatus = 'a';

		$fecha = $this->tratarFechas($orden['fecha']);

		if ($orden['comanda'] == 0){
			$this->stablas = '';
			if ($orden['estatus'] === 'p' || $orden['estatus'] === 'e' || $orden['estatus'] === 'ee'){
				$this->notaCredito($orden['id']);
				$estatus = 'nc';
			}
		}else{
			$this->stablas = '_c';
		}

		$this->db->actualizar('orden_tmp', array(
			'estatus' => $estatus // anulada
		), false, 'id = ' . $id);

		$this->db->actualizar('orden' . $this->stablas, array(
			'estatus' => $estatus // anulada
		), false, 'tmp = ' . $id);

		$this->salida(array('s' => 's', 'msj' => 'Orden Anulada'));
	}

	public function pagar($id){
		$id = intval($id);

		$this->db->seleccionar('orden' . $this->stablas, '*', 'id = ' . $id);
		$orden = $this->db->ur();

		$this->db->seleccionar('cliente', '*', 'id = ' . $orden['cliente']);
		$cliente = $this->db->ur();

		unset($orden['id'], $orden['forma_pago1'], $orden['forma_pago2'], $orden['forma_pago3']);

		$total = 0;
		foreach($this->forma_pago as $fp => $v){
			$orden['forma_pago' . $fp] = $v;
		}

		$pendiente = $orden['pendiente'];

		$orden['fecha'] = $this->fechaHora;
		//$orden['total'] = $pendiente;
		$orden['pendiente'] = 0;
		$orden['estatus'] = 'ee';

		$arr = array('forma_pago', 'parallevar', 'nro_orden', 'cliente', 'bloqueo', 'terminal_venta', 'terminal_orden', 'nro_factura', 'comanda');

		foreach($arr as $v){
			$orden[$v] = intval($orden[$v]);
		}

		$this->load->library('Impresora');
		$this->impresora->conf('fiscal');
		$this->impresora->plantilla('encargo');

		$this->impresora->datosFactura(array_merge($cliente, array(
			'forma_pago' => $this->forma_pago,
		)));

		$arrventa = array(
			array($pendiente, 1, 'Encargo', 12)
		);

		$salida = $this->impresora->imprimir($arrventa);

		if ($salida === false){
			$this->salida(array('s' => 'n', 'msj' => 'Error al Imprimir la Factura'));
		}

		//$this->db->actualizar('orden_tmp', array('pendiente' => 0), false, 'id = ' . $id);

		$this->db->seleccionar('orden_tmp', 'max(nro_factura) as nro_factura');
		$orden['nro_factura'] = intval($this->db->ur('nro_factura')) + 1;
		$orden['orden_relacion_pago'] = $id;

		$this->db->guardar('orden_tmp', $orden, false, 'id');
		$idOrdenNueva = $this->db->uid;

		$this->db->actualizar('orden_tmp', array(
			'estatus' => 'ee',
			'pendiente' => 0,
			'orden_relacion_pago' => $idOrdenNueva
		), false, 'id = ' . $id);

		$orden['tmp'] = intval($idOrdenNueva);
		$orden['id'] = intval($idOrdenNueva);

		$this->db->guardar('orden', $orden);

		$venta = array(
			'orden' => $idOrdenNueva,
			'id_producto' => 0,
			'producto' => 'Encargo',
			'cantidad' => 1,
			'precio' => $orden['total'],
			'fecha' => $this->fechaHora
		);

		$this->db->guardar('venta_tmp', $venta);
		$this->db->guardar('venta', $venta);

		$this->salida(array('s' => 's', 'msj' => 'Se Pago y Entrego el Encargo'));
	}

	public function reportex(){
		$this->load->library('Impresora');
		$this->impresora->conf('fiscal');
		$impresion = $this->impresora->reportex();

		$this->salida(array('s' => 's', 'msj' => 'Reporte X Generado'));
	}

	public function reportez(){
		$this->load->library('Impresora');
		$this->impresora->conf('fiscal');
		$impresion = $this->impresora->reportez();

		//$this->cerrarTerminal();
		$this->salida(array('s' => 's', 'msj' => 'Reporte Z Generado'));
	}

	public function cerrarcajas(){
		$this->cerrarTerminal();
		$this->salida(array('s' => 's', 'msj' => 'Cajas Cerradas'));
	}

	public function reportezmensual($mes, $anno){
		$mes = intval($mes) + 1;
		$anno = intval($anno);
		$dia = date("d", mktime(0,0,0, $mes+1, 0, $anno));

		$fecini = mktime(0,0,0, $mes, 1, $anno);
		$fecfin = mktime(0,0,0, $mes, $dia, $anno);

		$ini = date('dm', $fecini) . substr(date('Y', $fecini), -2);
		$fin = date('dm', $fecfin) . substr(date('Y', $fecfin), -2);

		$this->load->library('Impresora');
		$this->impresora->conf('fiscal');

		$imp = $this->impresora->imp;
		//$salida = $imp->cmd('I2Z' . $ini . $fin);
		$salida = $imp->cmd('I2S' . $ini . $fin);

		$imp->log();

		if ($salida === false){
			$this->salida(array('s' => 'n', 'msj' => 'Se Genero un Problema al Imprimir el Reporte'));
		}

		$this->salida(array('s' => 's', 'msj' => 'Se Imprimio el Reporte'));
	}

	public function encargo(){
		$this->idCliente = $this->guardarCliente();
		$orden = $this->guardarEncargo($this->idCliente);

		$impresion = $this->imprimirEncargo($orden);


		if ($impresion === false){
			$this->salida(array('s' => 'n', 'msj' => 'Error al Imprimir Factura Nro: ' . $id));
		}

		$this->_procesar($orden, true);
		$this->salida(array('s' => 's', 'msj' => 'Orden Procesada'));
	}

	protected function correlativo(){
		$this->db->seleccionar('orden_tmp', 'max(id) as correlativo');

		//return str_pad(intval(substr($this->db->ur('nro_orden'), -2)), 2, '0', STR_PAD_LEFT);
		return $this->correlativo = intval($this->db->ur('correlativo'));
	}

    /*
    protected function correlativo(){
		$this->db->seleccionar('orden_tmp', 'max(nro_orden) as correlativo');

		//return str_pad(intval(substr($this->db->ur('nro_orden'), -2)), 2, '0', STR_PAD_LEFT);
		return $this->correlativo = intval($this->db->ur('correlativo'));
	}
    */

	protected function guardarCliente(){
		$cedula = trim($this->cliente->val('cedula'));
		$cliente = $this->buscarCliente($cedula, true);

		if ((preg_match('/^\d/i', $cedula) && $cedula == 0) || $cedula == ''){
			return 0;
		}

		$this->cliente->val('cedula', $cedula, true);

		if ($cliente['s'] === 's'){
			$this->cliente->desactivar('id')->modificar('cedula');
		}else{
			$this->cliente->guardar();
			$cliente = $this->buscarCliente($cedula, true);

			//var_dump(cliente);
		}

		return intval($cliente['id']);
	}

	protected function guardarEncargo($idCliente){
		$id = (int) $this->encargo_modelo->val('orden');
		$this->encargo_modelo->actualizarValores()->val('cliente', $idCliente, true);

		$this->encargo_modelo->buscar('orden = ' . $id);

		if ($this->encargo_modelo->dbSalida('s') === 's'){
			$this->encargo_modelo
			->desactivar('id')
			->val('orden', $this->encargo_modelo->dbSalida('orden'))
			->modificar('orden');
		}else{
			$this->encargo_modelo->guardar();
		}

		$this->db->actualizar('orden_tmp', array(
			'cliente' => $idCliente,
			'pendiente' => floatval($this->get('total')) + floatval($this->encargo_modelo->val('extra')) - floatval($this->encargo_modelo->val('pago'))
		), false, 'id = ' . $id);

		return $id;
	}

	protected function buscarEncargo($orden){
		$orden = intval($orden);
		$this->cliente->buscar('orden = ' . $this->encargo_modelo->val('orden'));

		return $this->cliente->dbSalida;
	}

	public function buscarCliente($cedula = 0, $retornar = false){
		$cedula = trim(str_replace('.', '', $cedula));

		$this->cliente->buscar('cedula = \'' . $cedula . '\'');

		if ($retornar){
			return $this->cliente->dbSalida;
		}

		$this->cliente->dbSalida();
	}

	protected function stablas($s){
		$this->stablas = $s;
		$this->cliente->tabla = 'cliente' . $s;
		$this->encargo_modelo->tabla = 'encargo' . $s;
	}

	protected function duplicar($id){
		$sufijo = $this->comanda ? '_c' : '';
		$tabla = 'orden' . $sufijo;

		$this->db->seleccionar('orden_tmp', '*', array(
			'w' => 'id = ' . $id
		));

		$datos = $this->db->rs->fields;
		$this->db->seleccionar($tabla, '*', array(
			'w' => 'tmp = ' . $id
		));

		$af = $this->db->af;

		$datos['forma_pago'] = intval($datos['forma_pago']);
		$datos['parallevar'] = intval($datos['parallevar']);
		$datos['cliente'] = intval($datos['cliente']);
		$datos['tmp'] = intval($id);

		if ($af > 0){
			$this->db->actualizar($tabla, $datos, false, 'tmp = ' . $id);
		}else{
			$this->db->guardar($tabla, $datos, false, 'id');
			$id_orden = intval($this->db->uid);

			$this->db->seleccionar('venta_tmp', '*', array(
				'w' => 'orden = ' . $id
			));

			$tablaVenta = 'venta' . $sufijo;

			foreach($this->db->rs as $venta){
				unset($venta['id']);
				$venta['orden'] = $id_orden;

				$this->db->guardar($tablaVenta, $venta);
			}
		}
	}

    /******* IMPRIMIR *******/

    public function imprimirPequenna($id, $retornar = false){
		$id = intval($id);
		$this->load->library('Impresora');

		$this->correlativo = ($retornar === false) ? ($this->correlativo() + 1) : 0;

		$cantImpresion = 1;
		$this->impresora->conf('comanda2');
		$this->impresora->plantilla('factura');

		$impresion = $this->imprimirComandaPequenna($id);

		while($impresion === false && $cantImpresion++ < 1){
			sleep(1);
			$impresion = $this->imprimirComandaPequenna($id);
		}

		if ($impresion === false){
			$this->salida(array('s' => 'n', 'msj' => 'Error al Imprimir Factura Nro: ' . $id));
		}else{
            $this->correlativo = str_pad(intval(substr($this->correlativo, -2)), 2, '0', STR_PAD_LEFT);
            $this->salida(array('s' => 's', 'msj' => 'Orden Procesada', 'correlativo' => $this->correlativo));
		}
	}

    public function imprimirPequennaAgregar($id, $retornar = false){
		$id = intval($id);
		$this->load->library('Impresora');

		$this->correlativo = ($retornar === false) ? ($this->correlativo() + 1) : 0;

		$cantImpresion = 1;
		$this->impresora->conf('comanda2');
		$this->impresora->plantilla('factura');

		$impresion = $this->imprimirAgregarPedido($id);

		while($impresion === false && $cantImpresion++ < 1){
			sleep(1);
			$impresion = $this->imprimirAgregarPedido($id);
		}

		if ($impresion === false){
			$this->salida(array('s' => 'n', 'msj' => 'Error al Imprimir Factura Nro: ' . $id));
		}else{
            $this->correlativo = str_pad(intval(substr($this->correlativo, -2)), 2, '0', STR_PAD_LEFT);
            $this->salida(array('s' => 's', 'msj' => 'Orden Procesada', 'correlativo' => $this->correlativo));
		}
	}

    public function procesarComandaFiscal($id, $retornar = false){
		$id = intval($id);
		$this->load->library('Impresora');

		$this->comanda = $this->get('comanda') === 'true';
		$this->idCliente = $this->guardarCliente();

		$this->correlativo = ($retornar === false) ? ($this->correlativo() + 1) : 0;

		$cantImpresion = 1;
		$this->impresora->conf($this->comanda || $retornar ? 'comanda1' : 'fiscal');
		$this->impresora->plantilla('factura');

		$impresion = $this->imprimir($id);

		while($impresion === false && $cantImpresion++ < 1){
			sleep(1);
			$impresion = $this->imprimir($id);
		}

		if ($impresion === false){
			$this->salida(array('s' => 'n', 'msj' => 'Error al Imprimir Factura Nro: ' . $id));
		}

		/**/
		$cantImpresion = 1;
		$this->impresora->conf('comanda2');
		$this->impresora->plantilla('factura');

		$impresion = $this->imprimir($id);

		while($impresion === false && $cantImpresion++ < 1){
			sleep(1);
			$impresion = $this->imprimir($id);
		}


		if ($impresion === false){
			$this->salida(array('s' => 'n', 'msj' => 'Error al Imprimir Factura Nro: ' . $id));
		}


		/**/
		$this->_procesar($id, $retornar);

		if ($retornar){
			return array('s' => 's', 'msj' => 'Orden Procesada', 'correlativo' => $this->correlativo);
		}

		$this->salida(array('s' => 's', 'msj' => 'Orden Procesada', 'correlativo' => $this->correlativo));
	}

	public function procesarFiscalCom($id, $retornar = false){
		$id = intval($id);
		$this->load->library('Impresora');

		$this->comanda = $this->get('comanda') === 'true';
		$this->idCliente = $this->guardarCliente();

		$this->correlativo = ($retornar === false) ? ($this->correlativo() + 1) : 0;

		$cantImpresion = 1;
		$this->impresora->conf($this->comanda || $retornar ? 'comanda1' : 'fiscal');
		$this->impresora->plantilla('factura');

		$impresion = $this->imprimir($id);

		while($impresion === false && $cantImpresion++ < 1){
			sleep(1);
			$impresion = $this->imprimir($id);
		}

		if ($impresion === false){
			$this->salida(array('s' => 'n', 'msj' => 'Error al Imprimir Factura Nro: ' . $id));
		}

		/**/
		$this->_procesar($id, $retornar);

		if ($retornar){
			return array('s' => 's', 'msj' => 'Orden Procesada', 'correlativo' => $this->correlativo);
		}

		$this->salida(array('s' => 's', 'msj' => 'Orden Procesada', 'correlativo' => $this->correlativo));
	}
}
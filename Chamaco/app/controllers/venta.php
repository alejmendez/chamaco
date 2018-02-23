<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class venta extends control_base{
	protected $css = array('bootstrap/bootstrap-select.min.css', 'jquery.datetimepicker.css');
	protected $js  = array('jquery.fullscreen-0.4.1.min.js', 'jquery.maskedinput.min.js', 'bootstrap/bootstrap-select.min.js', 'jquery.alphanumeric.js', 'jquery.datetimepicker.js');
	protected $sinPermiso = true;
	protected $iva = 0;
	protected $coe_iva = 0;

	protected $forma_pago;

	protected $idTerminal;
	protected $nombreTerminal;
	protected $terminal_orden = 0;

    /* VARIABLES DE PAGO */
	protected $stablas = '_tmp';
	protected $comanda = false;
	protected $correlativo = 0;
	protected $idforma_pago = 0;
	//protected $forma_pago = array();
	protected $idCliente = 0;

	public function __construct(){
		parent::__construct();
		$this->conf['menuFormulario'] = false;
		$this->conf['menu'] = false;
		$this->conf['pies'] = false;
		$this->conf['titulo'] = $this->conf['compania'] . ': Modulo de Venta';

		$this->iva = $this->conf['iva'];
		$this->coe_iva = round(1 / ($this->iva + 1), 4);

		$this->load->model('venta_modelo', 'modelo');
        $this->load->model('cliente_modelo', 'cliente');

		$this->terminal();

		if ($this->isajax && $this->idTerminal === 0){
			$this->salida(array('s' => 'n', 'msj' => 'Este Terminal no esta Autorizado para Vender<br />Contacte al Administrador del Sistema'));
		}

		$this->modelo->tabla('venta_tmp');

		$this->terminalActivo = $this->_verificarTerminal();
	}

	public function index(){
		$this->pag($this->idTerminal === 0 ? 'venta_sin_permiso' : 'venta');
	}

	public function fix($padre = 0){
		exit;
		$padre = intval($padre);
		$this->db->seleccionar('productos', '*', array(
			'w' => 'padre = ' . $padre
		));
//		$this->db->uq();
		foreach($this->db->rs as $rw){
			echo "
			INSERT INTO productos (id, texto, descripcion, padre, precio, imagen, orden) VALUES (" . $rw['id'] . ", '" . $rw['texto'] . "', '" . $rw['descripcion'] . "', " . $rw['padre'] . ", " . $rw['precio'] . ", '" . $rw['imagen'] . "', " . $rw['orden'] . ");";

			$this->fix($rw['id']);
		}
	}

	public function guardar(){
		if (!$this->terminalActivo){
			$this->salida(array('s' => 'n', 'caja_cerrada' => true, 'msj' => 'Caja Cerrada.'));
		}

		$ventas = json_decode($_POST['ventas'], true);

		$this->db->guardar('orden_tmp', array(
			'fecha' => $this->fechaHora,
			//'forma_pago' => $this->getint('forma_pago'),
			'parallevar' => $this->getint('parallevar'),
			'ip' => $this->getIpCliente(),
			'terminal_orden' => $this->terminal_orden,
			'estatus' => 'pc', // pendiente por cobrar
            'ubicacion' => $_POST['ubicacion'],
		), false, 'id');

		$idOrden = $this->db->uid;

		if ($idOrden == 0){
			$this->salida(array('s' => 'n', 'msj' => 'Error al Generar Orden'));
		}

		$this->modelo
			->actualizarValores()
			->val('orden', $idOrden, true)
			->val('fecha', $this->fechaHora, true)
            ->desactivar('ubicacion')
			->guardar();

		if ($this->modelo->dbSalida('s') == 'n'){
			$this->salida(array('s' => 'n', 'msj' => 'Error al Generar El Pedido, factura Nro: ' . $idOrden));
		}

		//$this->salida(array('s' => 's', 'msj' => 'Factura Generada Bajo el Codigo: ' . $idOrden, 'orden' => $idOrden));

		$this->salida(array('s' => 's', 'msj' => 'Venta Satisfactoria, Nro de Factura: ' . $idOrden, 'orden' => $idOrden));
	}

	public function _reporteDiario($dia = ''){
		$resultado = $this->modelo->reportePorDias($dia);

		if ($resultado === false){
			$this->salida(array('s' => 'n', 'msj' => 'Error, el Parametro no es una Fecha'));
		}

		if ($resultado === 0){
			$this->salida(array('s' => 'n', 'msj' => 'No Tiene Ventas el dia ' . $dia . ' en el terminal ' . $this->getIpCliente()));
		}

		$this->impresora->plantilla("
			|J-295924011|
			|EL RINCON DEL DULCE PALMERINI, J.M C.A|
			|CALLE BRASIL QTA JOSMAR N 32 SECTOR|
			|LA MARIQUITA PQUITA VTA HERMOSA|
			|MCPIO HERES CD BOLIVAR|

			{linea}
			FECHA: {fecha_actual}
			{linea}
		", array(
			'fecha_actual' => date('d/m/Y')
		));

		$total = 0;
		foreach($this->db->rs as $row){
			$row['forma'] = strtoupper($row['forma']);
			$total += $row['total'];
			$this->impresora->plantilla("
				{forma}|{total}
			", $row);
		}

		$this->impresora->saltoLinea()->plantilla("
			Total:|{total}

			|Fin del Reporte Diario|
			{linea}
		", array('total' => $total));

		return $this->impresora->imprimir();
	}

	public function reporteDiario($dia = ''){
		$impresion = $this->_reporteDiario($dia);

		if ($impresion === false){
			$this->salida(array('s' => 'n', 'msj' => 'Error al Imprimir Reporte.'));
		}

		$this->salida(array('s' => 's', 'msj' => 'Reporte Impreso'));
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

	public function seguridad(){
		$this->db->seleccionar('seguridad', 'count(*) as c', array(
			'w' => '
				clave = \'' . $this->encriptar($this->get('clave', true)) . '\'
				and concepto = \'' . $this->get('concepto', true) . '\'
				'
		));

		$af = intval($this->db->ur('c'));

		if ($af === 1){
			$this->salida(array('s' => 's', 'msj' => 'Clave Valida'));
		}

		$this->salida(array('s' => 'n', 'msj' => 'La Clave Ingresada es Incorrecta'));
	}


    /******* FUNCIONES PARA  PAGO *******/
    public function buscarCliente($cedula = 0, $retornar = false){
		$cedula = trim(str_replace('.', '', $cedula));

		$this->cliente->buscar('cedula = \'' . $cedula . '\'');

		if ($retornar){
			return $this->cliente->dbSalida;
		}

		$this->cliente->dbSalida();
	}

    public function buscarOrdenesPendientes(){
        $orden = $this->db->seleccionarArray('orden' . $this->stablas, 'orden' . $this->stablas.'.id, ubicacion.nombre as ubicacion_venta, secciones.nombre as seccion_venta', array(
			//'w' => 'orden' . $this->stablas.'.estatus = \'pc\' and orden' . $this->stablas.'.terminal_orden = ' . $this->idTerminal,
            'w' => 'orden' . $this->stablas.'.estatus = \'pc\' AND date(orden' . $this->stablas.'.fecha) = \''.date('Y-m-d').'\'',
            'lj' => 'ubicacion ON ubicacion.id = orden' . $this->stablas.'.ubicacion
                    LEFT JOIN secciones ON secciones.id = ubicacion.id_seccion',
			'o' => 'orden' . $this->stablas . '.fecha'
		));
		//$this->db->uq();
		foreach($orden as $ll => $v){
            if($orden[$ll]['ubicacion_venta'] == false)
                $orden[$ll]['ubicacion_venta'] = 'Sin Ubicacion';

            if($orden[$ll]['seccion_venta'] == false)
                $orden[$ll]['seccion_venta'] = 'Sin Seccion';
		}

		exit(json_encode($orden));
        //exit(json_encode(array('ordenes' => $orden)));
	}

    public function agregarProductosOrden(){
        $ventas = json_decode($_POST['ventas'], true);
        $parallevar = $this->getint('parallevar');
        $idOrden = $this->getint('idOrden');

        if (!$this->terminalActivo){
			$this->salida(array('s' => 'n', 'caja_cerrada' => true, 'msj' => 'Caja Cerrada.'));
		}

        $af = $this->db->seleccionar('venta_tmp', 'id', 'orden = '.$idOrden);

        if($af == 0)
            $this->salida(array('s' => 'n', 'msj' => 'No Existe la Orden.'));

        $this->db->actualizar('orden_tmp', array('parallevar' => $parallevar), false, 'id = ' . $idOrden);

        foreach($ventas as $v){
            $v['fecha'] = $this->fechaHora;
            $v['orden'] = $idOrden;

            $this->db->guardar('venta_agregar', $v, false, 'id');

            $af = $this->db->seleccionar('venta_tmp', 'id', 'orden = '.$idOrden . ' AND id_producto = '.$v['id_producto']);

            if($af == 0)
                $this->db->guardar('venta_tmp', $v, false, 'id');
            else
                $this->db->query('UPDATE venta_tmp SET cantidad = cantidad + '. $v['cantidad'] . ' WHERE orden = '.$idOrden.' AND id_producto = '. $v['id_producto']);
        }

        $this->salida(array('s' => 's', 'msj' => 'Orden Actualizada, Nro de Factura: ' . $idOrden, 'orden' => $idOrden));
	}

    public function buscarUbicacion(){
        $ubicacionUsada = $disponibles = array();
        $condicion = $listaUbic = '';
        $idSeccion = $this->getint('id_seccion');

        $af = $this->db->seleccionar('orden' . $this->stablas, 'ubicacion', array(
			//'w' => "estatus = 'pc'",
            'w' => "estatus = 'pc' AND fecha::date = '".date('Y-m-d')."'",
            'g' => 'ubicacion'
		));

        foreach($this->db->rs as $ubiu){
			$ubicacionUsada[] = $ubiu['ubicacion'];
		}

        if(!empty($ubicacionUsada))
            $condicion = 'NOT IN('.implode(',', $ubicacionUsada).')';

        $af = $this->db->seleccionarArray('ubicacion', 'id, nombre', array(
			'w' => "id_seccion = $idSeccion" . ($condicion != '' ? (' AND id ' . $condicion) : ''),
            'o' => 'nombre'
		));
         //$this->db->uq();
        foreach($this->db->rs as $ubid){
			$disponibles[$ubid['id']] = $ubid['nombre'];
		}
        //var_dump($disponibles);
        $todas = $this->db->seleccionarArray('ubicacion', 'id, nombre', array(
			'w' => "id_seccion = $idSeccion",
            'o' => 'nombre'
		));

        foreach($todas as $t){
            $clase = array_key_exists($t['id'], $disponibles) ? 'btn-success' : 'btn-danger';

            $listaUbic .= '
                <button id="ubicacion_'.$t['id'].'" data-valor="' . $t['id'] . '" type="button" class="btn btn-ubicacion '.$clase.'">' . $t['nombre'] . '</button>';
		}

        //$this->db->uq();

        if(!empty($disponibles))
            $this->salida(array('s' => 's', 'msj' => 'Ubicacion Disponible!', 'ubicaciones' => $listaUbic));
        else
            $this->salida(array('s' => 'n', 'msj' => 'No existe Ubicacion Disponible!'));

	}
}

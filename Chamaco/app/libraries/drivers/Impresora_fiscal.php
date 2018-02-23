<?php
defined("BASEPATH") OR exit("El acceso directo al script está prohibido!");

class Impresora_fiscal{
	public $ip = "localhost";
	public $puerto = null;
	public $array_rep = null;
	
	public $limiteIntentos = 1;
	public $tiempoEspera = 500000; // medio segundo
	
	protected $socket = false;
	protected $modo = 'puerto';
	
	protected $comandos = array();
	
	public $prueba = false;
	
	public $usoComentarios = true;
	
	protected $errorConeccion = false; 
	
	protected $iDatosCliente = 1;
	
	public $datosFactura = array();
	
	public function __construct($opciones = array()){
		error_reporting(E_ALL);
		set_time_limit(0);
		
		$ci = &get_instance();
		
		if (is_string($opciones)){
			$opciones = $ci->conf['impresoras'][$opciones];
		}
		
		if (isset($opciones['ip'])){
			$this->ip  = $opciones['ip'];//$_SERVER['REMOTE_ADDR'];  // Solo aplica para servidores Apache, de lo contrario tiene que establecercela
		}
		
		if (isset($opciones['puerto'])){
			$this->puerto = $opciones['puerto'];
		}else{
			$this->puerto = getservbyname('www', 'tcp'); //Puerto 80 por default
		}
		
		if (isset($opciones['modo'])){
			$this->modo = $opciones['modo'];
		}
		
		//$this->conectar();
	}
	
	public function plantilla($plantilla){
		return;
	}
	
	public function datosFactura($datos = false){
		if ($datos === false){
			return $this->datosFactura;
		}
		
		$this->datosFactura = array_merge($this->datosFactura, $datos);
		
		//var_dump($this->datosFactura);
		
		return true;
	}
	
	public function imprimir($arr){
		$this->datosCliente();
		
		$out = $this->_imprimir($arr);
		$this->totalizar(); // totalizar y cerrar la conexion
		
		return $out;
	}
	
	public function log(){
		//$this->comandos;
		$ruta = 'archivos/log/fiscal/' . date('Y') . '/' . date('m');
		
		if (!is_dir($ruta)){
			mkdir($ruta, 0777, true);
		}
		
		$fp = fopen($ruta . '/' . date('d-m-Y') . '.txt', 'a');
		
		fwrite($fp, date('d/m/Y h:i:s a') . ': ' . PHP_EOL);
		foreach($this->comandos as $cmd){
			fwrite($fp, $cmd . PHP_EOL);
		}
		
		fwrite($fp, PHP_EOL);
		fclose($fp);
	}
	
	public function datosCliente(){
		if (isset($this->datosFactura['nombre']) && $this->datosFactura['nombre'] != ''){
			$this->_datosClientes('Nombre: ' . $this->datosFactura['nombre']);
		}
		
		if (isset($this->datosFactura['apellido']) && $this->datosFactura['apellido'] != ''){
			$this->_datosClientes('Apellido: ' . $this->datosFactura['apellido']);
		}
		
		if (isset($this->datosFactura['cedula']) && $this->datosFactura['cedula'] != ''){
			$cedula = strtoupper(trim($this->datosFactura['cedula']));
			
			$cmd = preg_match('/^\s*\d/i', $cedula) ? ('Cedula: ' . number_format($cedula, 0, '', '.')) : ('Rif: '  . $cedula);
			
			$this->_datosClientes($cmd);
		}
		
		if (isset($this->datosFactura['direccion']) && $this->datosFactura['direccion'] != ''){
			$this->_datosClientes('Direccion: ' . $this->datosFactura['direccion']);
		}
		
		if (isset($this->datosFactura['telefono']) && $this->datosFactura['telefono'] != ''){
			$this->_datosClientes('Telefono: ' . $this->datosFactura['telefono']);
		}
		
		//comentarios de la factura
		
		if ($this->usoComentarios == true){
			if (isset($this->datosFactura['correlativo'])){
				$this->cmd('@' . str_pad('-Numero de Pedido: ' . $this->datosFactura['correlativo'], 38, ' ', STR_PAD_RIGHT) . '-');
			}
			
			if (isset($this->datosFactura['parallevar']) && $this->datosFactura['parallevar'] == 1){
				$this->cmd(str_pad('@Pedido Para llevar.', 40, ' ', STR_PAD_RIGHT));
			}
			
			if ($this->prueba === true){
				$this->cmd(str_pad('@Mode de prueba.', 40, ' ', STR_PAD_RIGHT));
			}
		}
	}
	
	protected function _datosClientes($dato){
		$this->cmd('i0' . $this->iDatosCliente++ . substr($dato, 0, 40));
	}
	
	public function formato_numero($nro, $cant = 8, $dec = 2){
		$nro = (float) $nro;
		$nro = (string) round($nro, $dec);
		
		$pos = strpos($nro, '.');
		if ($pos === false){
			$nro = str_pad($nro, $cant, "0", STR_PAD_LEFT) . str_repeat('0', $dec);
		}else{
			$nro = str_pad(substr($nro, 0, $pos), $cant, "0", STR_PAD_LEFT) . str_pad(substr($nro, $pos + 1, 2), $dec, "0", STR_PAD_RIGHT);
		}
		
		return $nro;
	}
	
	public function _imprimir($arr, $cantidad = false, $producto = false, $iva = 12){ 
		if (is_array($arr)){
			$out = true;
			foreach($arr as $producto){
				if (isset($producto[0]) && isset($producto[1]) && isset($producto[2]) && isset($producto[3])){
					$out = $this->_imprimir($producto[0], $producto[1], $producto[2], $producto[3]);					
				}
			}
			
			return $out;
		}
		
		$precio = round($arr * $this->coe_iva($iva), 2);
		
		//descomentar para hacer pruebas
		if ($this->prueba === true){
			$precio = round(50, 500) / 100;
		}
		
		$cmd = $this->formato($precio, $cantidad, $producto, $iva);
		
		if ($cmd === false){
			return false;
		}
		
		$out = $this->cmd($cmd);
		
		return $out;
	}
	
	public function notaCredito($arr){
		$this->usoComentarios = false;
		$this->datosCliente();
		
		$out = $this->_notaCredito($arr);
		$this->totalizarNotaCredito(); // totalizar y cerrar la conexion
		
		return $out;
	}
	
	public function _notaCredito($arr, $cantidad = false, $producto = false, $iva = 12){
		$out = true;
		if (is_array($arr)){
			foreach($arr as $producto){
				if (isset($producto[0]) && isset($producto[1]) && isset($producto[2]) && isset($producto[3])){
					$out = $this->_notaCredito($producto[0], $producto[1], $producto[2], $producto[3]);					
				}
			}
			
			return $out;
		}
		
		$precio = round($arr * $this->coe_iva($iva), 2);
		
		//descomentar para hacer pruebas
		if ($this->prueba === true){
			$precio = round(50, 500) / 100;
		}
		
		$cmd = $this->formatoNota($precio, $cantidad, $producto, $iva);
		
		if ($cmd === false){
			return false;
		}
		
		$out = $this->cmd($cmd);
		
		return $out;
	}
	
	protected function coe_iva($iva){
		//return round(1 / (($iva / 100) + 1), 4);
		return (1 / (($iva / 100) + 1));
	}
	
	public function cabeza(){
		return true;
	}
	
	public function pies(){
		return true;
	}
	
	public function totalizar(){
		$formaPago = $this->datosFactura['forma_pago'];
		
		$formaPagoArr = array(
			1 => '01',
			2 => '09',
			3 => '10'
		);
		
		foreach($formaPago as $k => $v){
			if ($v == 0){
				unset($formaPago[$k]);
			}
		}
		
		$out = true;
		if (count($formaPago) == 1){
			foreach($formaPago as $k => $v){
				$out = $this->cmd("1" . $formaPagoArr[$k]); // totalizar
			}
		}else{
			foreach($formaPago as $k => $v){
				$precio = $this->formato_numero($v, 10, 2);
				$out = $this->cmd("2" . $formaPagoArr[$k] . $precio); // totalizar
			}
		}
		
		$this->log();
		
		return $out;
	}
	
	public function totalizarNotaCredito(){
		$formaPago = $this->datosFactura['forma_pago'];
		
		$formaPagoArr = array(
			1 => '01',
			2 => '09',
			3 => '10'
		);
		
		foreach($formaPago as $k => $v){
			if ($v == 0){
				unset($formaPago[$k]);
			}
		}
		
		foreach($formaPago as $k => $v){
			$precio = $this->formato_numero($v, 10, 2);
			//$precio = '';
			$out = $this->cmd("f" . $formaPagoArr[$k] . $precio); // totalizar
		}
		
		$this->log();
		
		return $out;
	}
	
	public function ejecutar(){
		return $this->cerrar();
	}
	
	public function formato($precio, $cantidad, $producto, $iva){
		$cantidad *= 1000;
		
		$ivas = array(8 => '"', 12 => '!', 22 => '#'); 
		
		$iva = $iva * (isset($ivas[$iva]) ? 1 : 100);
		
		if (!isset($ivas[$iva])){
			$iva = 12;
		}
		
		$_iva = $ivas[$iva];
		
		$precio = $this->formato_numero($precio, 8, 2);
		
		//$precio = substr($precio[0], -8) . substr($precio[1], -2);
		
		$cantidad = $this->formato_numero($cantidad, 8, 0);
		
		$producto = trim(substr($producto, 0, 40));
		
		$precio_cantidad = $precio . $cantidad;
		
		if (!preg_match('/^[0-9]+$/', $precio_cantidad)){
			return false;
		}
			
		return ($_iva . $precio_cantidad . $producto);
	}
	
	public function formatoNota($precio, $cantidad, $producto, $iva){
		$cantidad *= 1000;
		
		$ivas = array(
			8 => '2', 
			12 => '1', 
			22 => '3'
		); 
		
		$iva = $iva * (isset($ivas[$iva]) ? 1 : 100);
		
		if (!isset($ivas[$iva])){
			$iva = 12;
		}
		
		$_iva = $ivas[$iva];
		
		$precio = $this->formato_numero($precio, 8, 2);
		
		//$precio = substr($precio[0], -8) . substr($precio[1], -2);
		
		$cantidad = $this->formato_numero($cantidad, 8, 0);
		
		$producto = trim(substr($producto, 0, 40));
		
		$precio_cantidad = $precio . $cantidad;
		
		if (!preg_match('/^[0-9]+$/', $precio_cantidad)){
			return false;
		}
			
		return ('d' . $_iva . $precio_cantidad . $producto);
	}
	
	//Funcion que envia un comando por protococolo TCP/IP y retorna un arreglo de  palabras de repuestas
	public function cmd($cmd){
		if ($this->errorConeccion !== false) return false;
		
		if ($this->modo == 'prueba'){
			$this->comandos[] = $cmd;
			return true;
		}
		
		$i = 0;
		$salida = false;
		while($salida === false && ++$i <= $this->limiteIntentos){
			$this->array_rep = array();
			$out = '';
			
			if (!$this->conectar()){
				return false;
			}
			
			if ($this->escribir($cmd) === false){
				return false;
			}
			
			while ($out = @socket_read($this->socket, 2048)) {
				$this->array_rep[] = $out;
			}
			
			//var_dump($this->array_rep);
			
			$salida = true;
			foreach($this->array_rep as $respuesta){
				if ($respuesta == 'NAK'){ // error de impresora
					$salida = false;
				}
			}
			
			$this->cerrar();
		}
		
		$this->comandos[] = $cmd;
		
		return $this->array_rep;
	}
	
	protected function escribir($cmd){
		/*Enviamos el comando o peticion al Fiscal printer por via TCP*/
		$escritura = @socket_write($this->socket, $cmd, strlen($cmd));
		
		/*$i = 1;
		
		while($escritura === false && ++$i <= $this->limiteIntentos){
			usleep($this->tiempoEspera); //espera medio segundo para volver a intentar
			$escritura = @socket_write($this->socket, $cmd, strlen($cmd));
		}*/
		
		return $escritura;
	}
	
	protected function conectar(){
		$con = $this->_conectar();
		
		$i = 1;
		
		while($con === false && ++$i <= $this->limiteIntentos){
			usleep($this->tiempoEspera); //espera medio segundo para volver a intentar
			$con = $this->_conectar();
		}
		
		return $con;
	}
	
	protected function _conectar(){
		$connection = @fsockopen(gethostbyname($this->ip), $this->puerto);
		//$connection = @fsockopen(gethostbyname($this->ip), $this->puerto, $errno, $errstr, 30);
	    if (@is_resource($connection)){
	        @fclose($connection);
	    }else{
	        return false;
	    }
		
		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		//stream_set_timeout($this->socket, 3); 
		
		if ($this->socket === false) {
			$this->errorConeccion = 1;
			return false;
		}
		
		$result = socket_connect($this->socket, gethostbyname($this->ip), $this->puerto);
		
		if ($result === false) {
			$this->errorConeccion = 2;
			$this->cerrar();
			return false;
		}
		
		return true;
	}
	
	public function errorConeccion(){
		if ($this->errorConeccion === false) {
			return '';
		}elseif ($this->errorConeccion === 1){
			echo "socket_create() falló: razón: " . socket_strerror(socket_last_error()) . "<br>";
		}elseif ($this->errorConeccion === 2){
			echo "socket_connect() falló.\nRazón: ($result) " . socket_strerror(socket_last_error($this->socket)) . "<br>";
		}
	}
	
	protected function cerrar(){
		return @socket_close($this->socket);
	}
	
	public function reportex(){
		return $this->cmd('I0X'); //U0X
	}
	
	public function reportez(){
		return $this->cmd('I0Z'); //U0Z
	}
}
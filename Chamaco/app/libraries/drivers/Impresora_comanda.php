<?php
defined("BASEPATH") OR exit("El acceso directo al script está prohibido!");

class Impresora_comanda{
	public $con = false;
	public $puerto = false;
	public $modo = false;
	public $archivo = false;
	public $archivoBat = false;
	public $cabeza = false;
	public $cuerpo = false;
	public $pies = false;
	
	public $caracteresPorLinea = 64;
	public $caracteresPorLineaNegrita = false;
	
	protected $_caracteresPorLinea = 64;
	
	protected $caracterLinea = '-';
	protected $letra = 8;
	protected $letraNegrita = 8;
	protected $saltoAutomatico = true;
	
	protected $log = '';
	protected $depurar = false;
	protected $rutatmp = '.';
	
	protected $cache = '';
	
	public $impresion = false;
	protected $total = array();
	public $datosFactura = array();
	
	public $conf = array(
		'puerto' => 'PRN', // PRN - [COM1-COM5]
		'modo' => 'archivo', // [archivo|puerto|prueba]
		'archivo' => '',
		'archivoBat' => '',
		'rutatmp' => 'archivos/tmp/'
	);
	
	public function __construct($conf = array()){
		$this->ci = &get_instance();
		
		$this->conf($conf);
		$this->iniciar();
	}
	
	public function plantilla($plantilla){
		if (isset($this->conf['plantillas'][$plantilla])){
			$c = $this->conf['plantillas'][$plantilla];
			
			$this->cabeza = $c['cabeza'];
			$this->cuerpo = $c['cuerpo'];
			$this->pies = $c['pies'];
			
			return true;
		}else{
			return false;
		}
	}
	
	public function datosFactura($datos = array()){
		if ($datos === false){
			return $this->datosFactura;
		}
		
		$this->datosFactura = array_merge($this->datosFactura, $datos);
		return true;
	}
	
	public function notaCredito($arr){
		return true;
	}
	
	public function imprimir($arr){
		$this->total = array();
		$this->impresion = $arr;
		$out = $this->_imprimir($arr);
		$this->totalizar(); // totalizar y cerrar la conexion
		return $out;
	}
	
	public function _imprimir($arr, $cantidad = false, $producto = false, $iva = 12){ 
		if (is_array($arr)){
			foreach($arr as $producto){
				if (isset($producto[0]) && isset($producto[1]) && isset($producto[2]) && isset($producto[3])){
					$out = $this->_imprimir($producto[0], $producto[1], $producto[2], $producto[3]);					
				}
			}
			
			return $out;
		}
		
		$precio = round($arr * $this->coe_iva($iva), 2);
		$total = $precio * $cantidad;
		/*if (!isset($total[$iva])){
			$total[$iva] = 0;
		}*/
		
		@$this->total[$iva] += $total;
		$this->tmpl($this->cuerpo, array(
			'precio' => $precio,
			'cantidad' => $cantidad,
			'producto' => $producto,
			'total' => $total
		));
		
		return true;
	}
	
	protected function coe_iva($iva){
		return round(1 / (($iva / 100) + 1), 4);
	}
	
	protected function totalizar(){
		return true;
	}
	
	public function cabeza(){
		$arr = $this->datosFactura;
		if (isset($arr['parallevar'])){
			$arr['parallevar'] = $arr['parallevar'] == 1 ? 'Pedido Para llevar.' : '';
		}
		
		return $this->tmpl($this->cabeza, $arr);
	}
	
	public function pies(){
		foreach($this->total as $iva => $total){
			$iva /= 100;
			$_datosFactura = array_merge($this->datosFactura, array(
				'total' 		=> $total,
				'porcentaje' 	=> number_format(($iva * 100), 2, ',', '.'),
				'iva' 			=> round($total * $iva, 2),
				'totalneto' 	=> round($total * (1 + $iva), 2)
			));
			
			if (is_array($_datosFactura['forma_pago'])){
				foreach($_datosFactura['forma_pago'] as $k => $v){
					$_datosFactura['forma_pago'] = $_datosFactura['formas_pagos'][$k]['forma'];
				}
			}
			
			$this->tmpl($this->pies, $_datosFactura);
		}
		
		return true;
	}
	
	public function conf($conf = array()){
		if (is_string($conf)){
			$conf = $ci->conf['impresoras'][$conf];
		}
		
		$this->conf = array_merge($this->conf, $conf);
		
		
		foreach($this->conf as $ll => $v){
			if (isset($this->$ll)){
				$this->$ll = $v;
			}
		}
		
		$this->modo = strtoupper($this->modo);
		
		if ($this->archivo === ''){
			$this->archivo = 'imp' . rand(1,999999) . '.txt';
		}
		
		if ($this->archivoBat === ''){
			$this->archivoBat = 'imp' . rand(1,999999) . '.bat';
		}
		
		if (!is_dir($this->rutatmp)){
			mkdir($this->rutatmp, 0777);
		}
		
		$this->archivo = $this->rutatmp . $this->archivo;
		$this->archivoBat = $this->rutatmp . $this->archivoBat;
		
		$this->_caracteresPorLinea = $this->caracteresPorLinea;
		
		if ($this->caracteresPorLineaNegrita === false){
			$this->caracteresPorLineaNegrita = $this->caracteresPorLinea;
		}
	}
	
	public function iniciar($puerto = false){
		if ($puerto !== false){
			$this->puerto = $puerto;
			$this->conf['puerto'] = $puerto;
		}
		
		//$this->saltoLinea(10);
		$this->cerrar();
		
		$this->cache = '';
		
		$this->log("[inicio[modo={$this->modo}]]");
		
		$this->reinicio();
	}
	
	public function log_archivo(){
		//$this->comandos;
		$ruta = 'archivos/log/comanda/' . date('Y') . '/' . date('m');
		
		if (!is_dir($ruta)){
			mkdir($ruta, 0777, true);
		}
		
		$fp = fopen($ruta . '/' . date('d-m-Y') . '.txt', 'a');
		fwrite($fp, date('d/m/Y h:i:s a') . ': ' . PHP_EOL . $this->cache . PHP_EOL . PHP_EOL);
		fclose($fp);
	}
	
	public function log($c = false){
		if ($this->depurar === false){
			return;
		}
		
		if ($c === false){
			echo "<pre>" . $this->log . "</pre>";
			return;
		}
		
		$this->log .= '*' . $c . "*\n";
	}
	
	protected function _escribir($cod = ''){
		$this->cache .= $cod;
		return $this;
	}
	
	public function escribir($cod = '', $formato = ''){
		$formato = strtoupper($formato);
		/*if (stripos($formato, 'C')){
			$this->centrado($cod);
		}elseif (stripos($formato, 'D')){
			$this->derecha($cod);
		}else{
			$this->izquierda($cod);
		}*/
		
		if (stripos($formato, 'N')){
			$this->negrita();
		}
		
		$this->log($cod);
		$this->_escribir($cod);
		
		$this->saltoLinea();
		
		if (stripos($formato, 'L')){
			$this->saltoLinea();
		}
		
		//$this->reinicio();
		
		return $this;
	}
	
	public function tmpl($plantilla = '', $datos = ''){
		$plantilla = trim($plantilla);
		$plantilla = trim($plantilla, "\r");
		$plantilla = trim($plantilla, "\n");
		
		$datos['linea'] = str_repeat($this->caracterLinea, $this->caracteresPorLinea);
		$plantilla = $this->ci->tmpl($plantilla, $datos);
		$plantilla = explode("\n", trim($plantilla));
		
		foreach($plantilla as $linea){
			$linea = trim($linea);
			if ($linea === ''){
				$this->saltoLinea();
			}
			$caracteresPorLinea = $this->caracteresPorLinea;
			
			if (strpos($linea, '[n]') !== false || strpos($linea, '[N]') !== false){
				$this->negrita();
				$linea = str_replace(array('[n]', '[N]'), '', $linea);
			}
			
			
			if (strlen($linea) > $caracteresPorLinea){
				$lineas = array();
				
				while($linea != ''){
					$lineas[] = substr($linea, 0, $caracteresPorLinea);
					$linea = substr($linea, $caracteresPorLinea);
				}
			}else{
				$lineas = array($linea);
			}
			
			//$linea = substr($linea, 0, $caracteresPorLinea);
			
			foreach($lineas as $linea){
				if (strpos($linea, '|') === false){
					$linea .= str_repeat(' ', $caracteresPorLinea - strlen($linea));
				}elseif(substr($linea, 0, 1) === '|' && substr($linea, -1) === '|'){
					$linea = trim($linea, '|');
					$this->centrado();
					
					/*$espacios = $caracteresPorLinea - strlen($linea);
					
					if ($espacios > 0){
						if ($espacios % 2 == 0){
							$espacios = $espacios / 2;
							$linea = str_repeat(' ', $espacios) . $linea . str_repeat(' ', $espacios);
						}else{
							$espacios = floor($espacios / 2);
							$linea = str_repeat(' ', $espacios + 1) . $linea . str_repeat(' ', $espacios);
						}
					}*/
				}else{
					$linea = 
						substr($linea, 0, strpos($linea, '|')) .
						str_repeat(' ', $caracteresPorLinea - strlen($linea) + 1) .
						substr($linea, strpos($linea, '|') + 1);
				}
				
				//$linea = substr($linea, 0, $caracteresPorLinea);
				
				//var_dump($linea);
				
				$this->escribir($linea);
				//$this->saltoLinea();	
			}
		}
		
		return $this;
	}
	
	public function reinicio(){
		$this->log("[reinicio]");
		$this->caracteresPorLinea = $this->_caracteresPorLinea;
		$this->_escribir(chr(27) . chr(64)); //reinicio
		return $this->formato($this->letra);
	}
	
	public function formato($i){
		$i = (int) $i;
		$this->ultimoFormato = $i;
		return $this->_escribir(chr(27) . chr(33) . chr($i));
	}
	
	public function abrirCaja(){
		$this->log("[abrirCaja]");
		return $this->_escribir(chr(27) . chr(112) . chr(48)); //ABRIR EL CAJON
	}
	
	public function saltoLinea($vacio = 1){
		$this->log("[saltoLinea $vacio]");
		//return $this->_escribir(str_repeat("\n", intval($vacio))); //salto de linea VACIO
		
		if ($this->saltoAutomatico){
			return $this->_escribir("\n");
		}
		
		return $this->_escribir(chr(27) . chr(100) . chr(intval($vacio))); //salto de linea VACIO
	}
	
	public function negrita(){
		//$this->caracteresPorLinea = 32;
		$this->log("[negrita]");
		//return $this->_escribir(chr(27) . chr(33) . chr(16)); //negrita
		$this->caracteresPorLinea = $this->caracteresPorLineaNegrita;
		
		return $this->formato($this->letraNegrita);
	}
	
	public function izquierda(&$cod, $caracter = " "){
		$this->log("[izquierda]");
		return $this->_escribir(chr(27). chr(97). chr(0)); //izquierda
		//$cod = str_pad($cod, $this->caracteresPorLinea, $caracter);
		//return $cod;
	}
	
	public function centrado(){
		$this->log("[centrado]");
		/*
		$espacios = $this->caracteresPorLinea - strlen($cod);
				
		if ($espacios > 0){
			if ($espacios % 2 == 0){
				$espacios = $espacios / 2;
				$cod = str_repeat(' ', $espacios) . $cod . str_repeat(' ', $espacios);
			}else{
				$espacios = floor($espacios / 2);
				$cod = str_repeat(' ', $espacios + 1) . $cod . str_repeat(' ', $espacios);
			}
		}
		return $cod;*/
		return $this->_escribir(chr(27). chr(97). chr(1)); //centrado
	}
	
	public function derecha(&$cod, $caracter = " "){
		$this->log("[derecha]");
		//$cod = str_pad($cod, $this->caracteresPorLinea, $caracter, STR_PAD_LEFT);
		//return $cod;
		return $this->_escribir(chr(27). chr(97). chr(2)); //derecha
	}
	public function cortarPapel(){
		$this->escribir(' ');
		$this->escribir(' ');
		$this->escribir(' ');
		
		$this->log("[cortarPapel]");
		//return $this->_escribir(chr(29) . chr(86) . chr(49)); //CORTA PAPEL
		
		return $this->_escribir(chr(29) . chr(86) . chr(1) . chr(0)); //CORTA PAPEL completo
		return $this->_escribir(chr(10).chr(10).chr(10).chr(10).chr(10).chr(10).chr(29).chr(86).chr(49).chr(12)); //Corte de papel
	}
	public function linea($cantiadLineas = 1){
		$this->log("[linea[caracter=" . $this->caracterLinea . "][lineas=$cantiadLineas]]");
		$salida = false;
		for($i = 0; $i < $cantiadLineas; $i++){
			$salida = $this->_escribir(str_repeat($this->caracterLinea, $this->caracteresPorLinea));
		}
		return $salida;
	}
	
	public function cerrar(){
		$this->log("[cerrar]");
		$this->cortarPapel();
		if ($this->con !== false)
			fclose($this->con); // cierra el fichero PRN
		$this->con = false;
		return $this;
	}
	
	public function probarFuente(){
		for($i = 0; $i <= 200; $i++){
			$this->formato($i);
			$this->escribir('('.$i.')ABCDEFGHIJKLMNOPQRSTUVWXYZ-123456789');
		}
		
		$this->ejecutar();
	}
	
	public function ejecutar(){
		$this->cerrar();
		
		$salida = 'N/A';
		if ($this->modo === 'PUERTO'){
			//echo 'lpr ' . $this->puerto . ' 2>&1 ';
			$this->con = @fopen($this->puerto, 'w+');
			
			if($this->con === false){
			    //exit('No se puedo Imprimir, Verifique su conexion con el Terminal, Puerto: ' . $this->puerto);
			    $salida = false;
			}else{
				fwrite($this->con, $this->cache);
				
				$salida = shell_exec($this->puerto . ' 2>&1 ');
				//var_dump($this->cache, $this->puerto . ' 2>&1 ', $salida);
			}
		}elseif ($this->modo === 'ARCHIVO'){
			$this->con = fopen($this->archivo, 'w+');
			if($this->con === false){
			    //exit('No se puedo Imprimir, Verifique su conexion con el Terminal, Puerto: ' . $this->puerto);
			    $salida = false;
			}else{
				@fwrite($this->con, $this->cache);
			
				if (is_file($this->archivo)){
					$comando = 'type "' . trim($this->archivo) . '" > "' . $this->puerto . '" 2>&1 ';
					$salida = shell_exec($comando);
				}else{
					$salida = false;
				}
			}
		}elseif ($this->modo === 'PRUEBA'){
			//$this->con = fopen($this->archivo, 'w+');
			//@fwrite($this->con, $this->cache);
			$salida = '';
		}else{
			$salida = false;
		}
		
		if ($salida != false){
			//var_dump(trim($salida));
			$salida = trim($salida) == '';
		}
		
		if ($this->modo !== 'PRUEBA'){
			//sleep(1);
		}
		
		//@unlink($this->archivo);
		//@unlink($this->archivoBat);
		
		$this->log("[imprimir]");
		$this->log();
		
		$this->log_archivo();
		
		return true;
	}
}
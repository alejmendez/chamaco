<?php
class impresora{
	public $con = false;
	public $puerto;
	public $modo;
	public $archivo;
	public $archivoBat;
	public $ultimoFormato = 0;
	public $caracteresPorLinea = 40;
	
	public $conf = array(
		'puerto' => 'PRN', // PRN - [COM1-COM5]
		'modo' => 'archivo', // [archivo|puerto|prueba]
		'archivo' => '',
		'archivoBat' => ''
	);
	
	public function __construct($conf = array()){
		$this->ci = &get_instance();
		$this->conf = array_merge($this->conf, $conf);
		$this->modo = strtoupper($this->conf['modo']);
		
		$this->archivo = $this->conf['archivo'];
		$this->archivoBat = $this->conf['archivoBat'];
		
		if ($this->archivo === ''){
			$this->archivo = 'imp' . rand(1,999999) . '.txt';
		}
		
		if ($this->archivoBat === ''){
			$this->archivoBat = 'imp' . rand(1,999999) . '.bat';
		}
		
		//$this->archivo = 'archivos/tmp/' . $this->archivo;
		//$this->archivoBat = 'archivos/tmp/' . $this->archivoBat;
		
		$this->puerto = $this->conf['puerto'];
		
		$this->iniciar();
	}
	
	public function iniciar($puerto = false){
		//$puerto = strtoupper($puerto);
		if ($puerto !== false){
			$this->puerto = $puerto;
			$this->conf['puerto'] = $puerto;
		}
		
		$this->cerrar();
		//$this->con = @fopen($this->puerto, 'w+');
		if ($this->modo === 'PUERTO'){
			$this->con = fopen($this->puerto, 'w');
		}elseif ($this->modo === 'ARCHIVO'){
			$this->con = fopen($this->archivo, 'w');
		}elseif($this->modo === 'PRUEBA'){
			$this->con = fopen($this->archivo, 'w');
		}
		
		if($this->con === false){
			//var_dump($this->puerto);
		    exit('No se puedo Imprimir, Verifique su conexion con el Terminal, Puerto: ' . $this->puerto);
		}
		
		$this->reinicio();
	}
	
	public function _escribir($cod = ''){
		@fwrite($this->con, $cod);
		return $this;
	}
	
	public function escribir($cod = '', $formato = ''){
		$formato = strtoupper($formato);
		if (stripos($formato, 'C')){
			$this->centrado($cod);
		}elseif (stripos($formato, 'D')){
			$this->derecha($cod);
		}else{
			$this->izquierda($cod);
		}
		
		if (stripos($formato, 'N')){
			$this->negrita();
		}
		
		$this->_escribir($cod);
		$this->saltoLinea();
		
		if (stripos($formato, 'L')){
			$this->saltoLinea();
		}
		
		$this->reinicio();
		
		return $this;
	}
	
	public function plantilla($plantilla = '', $datos = ''){
		$plantilla = trim($plantilla);
		$plantilla = trim($plantilla, "\r");
		$plantilla = trim($plantilla, "\n");
		
		
		$datos['linea'] = str_repeat('-', $this->caracteresPorLinea);
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
			
			if ($this->ultimoFormato == 14){
				$caracteresPorLinea = 33;
			}elseif ($this->ultimoFormato != 3){
				$caracteresPorLinea = 32;
			}
			
			$linea = substr($linea, 0, $caracteresPorLinea);
			
			if (strpos($linea, '|') === false){
				$linea .= str_repeat(' ', $caracteresPorLinea - strlen($linea));
			}elseif(substr($linea, 0, 1) === '|' && substr($linea, -1) === '|'){
				$linea = trim($linea, '|');
				$espacios = $caracteresPorLinea - strlen($linea);
				
				if ($espacios > 0){
					if ($espacios % 2 == 0){
						$espacios = $espacios / 2;
						$linea = str_repeat(' ', $espacios) . $linea . str_repeat(' ', $espacios);
					}else{
						$espacios = floor($espacios / 2);
						$linea = str_repeat(' ', $espacios + 1) . $linea . str_repeat(' ', $espacios);
					}
				}
			}else{
				$linea = 
					substr($linea, 0, strpos($linea, '|')) .
					str_repeat(' ', $caracteresPorLinea - strlen($linea) + 1) .
					substr($linea, strpos($linea, '|') + 1);
			}
			
			$linea = substr($linea, 0, $caracteresPorLinea);
			
			//var_dump($linea);
			
			$this->escribir($linea);
			//$this->saltoLinea();
		}
		
		return $this;
	}
	
	public function reinicio(){
		$this->_escribir(chr(27) . chr(64)); //reinicio
		return $this->formato(3);
	}
	
	public function formato($i){
		$this->ultimoFormato = $i;
		return $this->_escribir(chr(27) . chr(33) . chr($i));
	}
	
	public function abrirCaja(){
		return $this->_escribir(chr(27) . chr(112) . chr(48)); //ABRIR EL CAJON
	}
	
	public function saltoLinea($vacio = 1){
		//return $this->_escribir(str_repeat("\n", intval($vacio))); //salto de linea VACIO
		return $this->_escribir(chr(27) . chr(100) . chr(intval($vacio))); //salto de linea VACIO
	}
	
	public function negrita(){
		return $this->formato(8); //negrita
	}
	
	public function izquierda(&$cod, $caracter = " "){
		//return $this->_escribir(chr(27). chr(97). chr(1)); //izquierda
		$cod = str_pad($cod, $this->caracteresPorLinea, $caracter);
		return $cod;
	}
	
	public function centrado(){
		return $this->_escribir(chr(27). chr(97). chr(1)); //centrado
	}
	
	public function derecha(&$cod, $caracter = " "){
		$cod = str_pad($cod, $this->caracteresPorLinea, $caracter, STR_PAD_LEFT);
		return $cod;
		//return $this->_escribir(chr(27). chr(97). chr(2)); //derecha
	}
	public function cortarPapel(){
		//return $this->_escribir(chr(29) . chr(86) . chr(49)); //CORTA PAPEL
		return $this->_escribir(chr(29) . chr(86) . chr(50)); //CORTA PAPEL completo
		return $this->_escribir(chr(10).chr(10).chr(10).chr(10).chr(10).chr(10).chr(29).chr(86).chr(49).chr(12)); //Corte de papel
	}
	public function linea($caracter = '-', $cantiadLineas = 1){
		$salida = false;
		for($i = 0; $i < $cantiadLineas; $i++){
			$salida = $this->_escribir(str_repeat($caracter, $this->caracteresPorLinea));
		}
		return $salida;
	}
	
	public function cerrar(){
		$this->cortarPapel();
		if ($this->con !== false)
			fclose($this->con); // cierra el fichero PRN
		$this->con = false;
		return $this;
	}
	
	public function imprimir(){
		$this->saltoLinea(9);
		$this->cerrar();
		
		$salida = 'N/A';
		
		if ($this->modo === 'PUERTO'){
			//echo 'lpr ' . $this->puerto . ' 2>&1 ';
			$salida = shell_exec('lpr ' . $this->puerto . ' 2>&1 ');
		}elseif ($this->modo === 'ARCHIVO'){
			if (is_file($this->archivo)){
				$comando = 'type "' . trim($this->archivo) . '" > "' . $this->puerto . '" 2>&1 ';
				$salida = shell_exec($comando);
			}else{
				$salida = false;
			}
		}elseif ($this->modo === 'PRUEBA'){
			
		}else{
			$salida = false;
		}
		
		if ($salida != false){
			//var_dump(trim($salida));
			$salida = trim($salida) == '';
		}
		
		if ($salida === false){
			$this->iniciar();
		}else{
			sleep(1);
			//@unlink($this->archivo);
			//@unlink($this->archivoBat);
		}
		
		return $salida;
	}
}
<?php
defined("BASEPATH") OR exit("El acceso directo al script está prohibido!");

class Impresora{
	//array generado en la clase padre que contiene los hijos
    public $valid_drivers;
    //aquí guardamos una instancia de ci al no poder hacer uso de $this
    public $ci;
    public $driver = '';
    
    public $imp = null;
	
	public function __construct($conf = ''){
		$this->ci = &get_instance();
		
        $this->ci->config->load("impresoras", TRUE);
 
        $this->valid_drivers = $this->ci->config->item("drivers_impresora", "impresoras");
        $this->configuraciones = $this->ci->config->item("impresoras", "impresoras");
	}
	
	public function conf($conf){
		if (isset($this->configuraciones[$conf])){
			$this->conf = $conf;
			$this->driver($this->configuraciones[$conf]['driver']);
		}
	}
	
	public function driver($driver){
		$this->driver = $driver;
		$clase = 'Impresora_' . $this->driver;
		
		$arch = APPPATH . 'libraries/drivers/' . $clase . EXT;
		if (is_file($arch)){
			include_once($arch);
			
			$datos = array();
			if (!is_null($this->imp)){
				$datos = $this->imp->datosFactura;
			}
			
			$this->imp = new $clase($this->configuraciones[$this->conf]);
			
			$this->imp->datosFactura($datos);
		}else{
			exit('error al cargar driver ' . $arch);
		}
	}
	
	public function cabeza($datosFactura){
		return $this->imp->cabeza($datosFactura);
	}
	
	public function pies($datosFactura){
		return $this->imp->pies($datosFactura);
	}
	
	public function parallevar(){
		return true;
	}
	
	public function imprimir($arr){
		$this->imp->cabeza();
		$salida = $this->imp->imprimir($arr);
		$this->imp->pies();
		
		$this->imp->ejecutar();
		
		return $salida;
	}
	
	public function notaCredito($arr){
		$salida = $this->imp->notaCredito($arr);
		return $salida;
	}
	
	public function datosFactura($datos = false){
		return $this->imp->datosFactura($datos);
	}
	
	public function plantilla($plantilla){
		return $this->imp->plantilla($plantilla);
	}
	
	public function reportex(){
		return $this->imp->reportex();
	}
	
	public function reportez(){
		return $this->imp->reportez();
	}
}
<?php
defined("BASEPATH") OR exit("El acceso directo al script está prohibido!");

$arch = APPPATH . 'libraries/drivers/Impresora_comanda' . EXT;
if (is_file($arch)){
	include_once($arch);
}else{
	exit("error al cargar driver");
}

class Impresora_comanda_oficina extends Impresora_comanda{
	//protected $depurar = true;
	
	public function cortarPapel(){
		$this->escribir(' ');
		$this->escribir(' ');
		$this->escribir(' ');
		$this->escribir(' ');
		
		parent::cortarPapel();
	}
}
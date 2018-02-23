<?php
class modelo_form extends modelo_form_base{
	public function __construct(){
		//asignamos el objeto de base de datos automaticamente al modelo.
		$this->ci = &get_instance();
		$this->db = $this->ci->db;
		
		parent::__construct();
	
		//asignamos la url automaticamente dependiendo del controlador.
		if ($this->url === ''){
			$this->url = $this->ci->url;
		}else{
			$this->url = base_url() . $this->url;
		}
	}
	
	public function buscar($condicion = '', $campos = false, $ordenar = false){
		parent::buscar($condicion, $campos, $ordenar);
	}
	public function actualizacionAutomatica($salida = true){
		parent::actualizacionAutomatica($salida);
	}
	public function guardar(){
		parent::guardar();
	}
	public function modificar($condicion = '', $condicionGuardar = false, $guardarNuevoRegistro = false){
		parent::modificar($condicion, $condicionGuardar, $guardarNuevoRegistro);
	}
	public function eliminar($condicion = ''){
		parent::eliminar($condicion);
	}
}
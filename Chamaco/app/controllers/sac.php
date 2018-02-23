<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class sac extends control_base{
	protected $css = array();
	protected $js  = array();
	protected $sinPermiso = true;
	
	public function __construct(){
		parent::__construct();
		$this->conf['menuFormulario'] = false;
		$this->conf['menu'] = false;
		$this->conf['pies'] = false;
		
		$this->adodb->ini('sac', 'sac');
	}
	
	public function index(){
		//$this->pag($this->idTerminal === 0 ? 'venta_sin_permiso' : 'venta');
		$datos = array();
		
		$rango = array(61, 99);
		$edad = 'edad' . $rango[0] . '-' . $rango[1];
		
		$this->sac->query('
			select 
				par.id as parroquia,
				par.nombre as nombre_parroquia,
				count(*) as "' . $edad . '"
			from personas_naturales as per
			left join def_urbanizaciones as urb on per.id_urbanizacion = urb.id
			left join def_parroquias as par on urb.id_parroquia = par.id
			left join def_municipios as mun on par.id_municipio = mun.id
			where 
				mun.id = 5
				and date_part(\'year\', age(per.fecha_nacimiento)) between ' . $rango[0] . ' and 2000
			group by par.id, par.nombre
		');
		
		foreach($this->sac->rs as $row){
			$row['parroquia'] = intval($row['parroquia']);
			$row['"' . $edad . '"'] = intval($row[$edad]);
			unset($row[$edad]);
			$this->data($row);
		}
		
	}
	
	public function data($data){
		$this->sac->seleccionar('gis_resumen', 'parroquia', array(
			'w' => 'parroquia = ' . $data['parroquia']
		));
		
		if ($this->sac->af > 0){
			$this->sac->actualizar('gis_resumen', $data, false, 'parroquia = ' . $data['parroquia']);
		}else{
			$this->sac->guardar('gis_resumen', $data);
		}
		
	}
}

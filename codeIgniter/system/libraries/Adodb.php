<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Adodb{
	public function __construct($conf = array('', '')){
		global $ADODB_COUNTRECS, $ADODB_LANG, $ADODB_CACHE_DIR;
		$ADODB_CACHE_DIR = 'tmp/';
		$ADODB_LANG = 'es';
		$ADODB_COUNTRECS = true;
		
		if (!class_exists('ADONewConnection')){
			if (file_exists($file = BASEPATH . 'libraries/adodb/adodb.inc' . EXT)){
				require_once($file);
			}elseif (file_exists($file = APPPATH . 'libraries/adodb/adodb.inc' . EXT)){
				require_once($file);
			}else{
				exit('Error al Cargar la Libreria adodb');
			}
			
			if (file_exists($file = BASEPATH . 'libraries/objDb' . EXT)){
				require_once($file);
			}elseif (file_exists($file = APPPATH . 'libraries/objDb' . EXT)){
				require_once($file);
			}else{
				exit('Error al Cargar la Clase de Base de Datos');
			}
		}
		
		$nombre = $conf[0] === '' ? 'db' : $conf[0];
		$grupo = $conf[1] === '' ? '' : $conf[1];

		$this->ini($nombre, $grupo);
	}

	public function ini($nombre, $grupo){
		$CI = &get_instance();

		include (APPPATH . 'config/database' . EXT);

		$grupo = (!empty($grupo)) ? $grupo : $active_group;
		
		if (!isset($db[$grupo])){
			if (!isset($CI->$nombre)){
				$CI->$nombre = false;
			}
			return false;
		}
		
		$cfg = $db[$grupo];

		if (isset($cfg['dbdriver'])){
			$CI->$nombre = new base_datos($cfg['dbdriver'], $cfg['hostname'], $cfg['username'], $cfg['password'], $cfg['database'], $cfg['pconnect']);
			$CI->$nombre->db->debug = $cfg['db_debug'];
			
			if (isset($cfg['schema']) && !empty($cfg['schema'])){
				$CI->$nombre->setSchema($cfg['schema']);
				//echo $CI->$nombre->schema;
			}
			
			if (isset($cfg['dbprefix']) && !empty($cfg['dbprefix'])){
				$CI->$nombre->tbPrefijo = $cfg['dbprefix'];
			}
		}else{
			exit("la configuracion de base de datos no esta establecida");
		}
	}
}
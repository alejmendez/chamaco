<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class control_base extends control_base_app{
	protected $sinPermiso = false;
	protected $acceso = false;
	
	public function __construct($sinPermiso = false){
		parent::__construct();
		
		//var_dump($sinPermiso);
		if ($this->sinPermiso === true || $sinPermiso === true){
			return $this;
		}
		
		if ($this->session->userdata('autenticado') === false && $this->uri->segment(1) !== 'autenticacion'){
			$this->session->set_userdata(array_merge($this->conf['variables_session'], array(
				'autenticado' => false,
				'idUsuario' => 0,
				'nombre' => 'Invitado',
				'usuario' => '',
				'redireccionar' => $this->uri->uri_string
			)));
			
			if ($this->isajax){
				$this->salidaPrograma('La Session Caduco', true);
			}else{
				redirect('autenticacion');
			}
			
			exit;
		}
		
		
		
		$accion = end($this->uri->segments);
		if ($accion !== 'agregarEstructura'){
			return;
			
			$r = $this->permisologia();
			
			if (!$r){
				if ($this->isajax){
					$this->salida(array('s' => 'n', 'msj' => 'El usuario "' . $this->session->userdata('nombre') . '" No tiene Permiso para "' . $accion . '"'));
				}else{
					redirect('sin_permiso');
				}
				exit;
			}
		}
	}
	
	//falta crear un metodo para crear la estructura a partir de la estructura de directorios dentro de controllers
	public function agregarEstructura($clase = false, $ruta = false){
		$this->load->helper('inflector');
		
		if ($clase === false || !is_object($clase)){
			$clase = $this;
		}
		
		$metodos = array_diff(get_class_methods(get_class($clase)), 
			array(
				'__construct', 'agregarEstructura', 'permisologia', 'css',
				'js', 'pag', 'ccss', 'cjs', 'archivosCabecera', 'archivosCabeceraCss',
				'archivosCabeceraJs', 'isajax', 'tratarFechas', '_comillas', 'get', 'id',
				'salida', 'getvar', 'getpost', 'getint', 'getfloat', 'getdate', 'getjson', 'getget',
				'tmpl', 'getIpCliente', 'salidaPrograma', 'encriptar', '_iniCargador', 'get_instance'
			));
		
		natsort($metodos);
		
		if ($ruta === false){
			$ruta = $this->uri->segments;
			unset($ruta[count($ruta)]);
		}else{
			$ruta = explode('/', $ruta);
		}
		
		$cruta = count($ruta);
		
		$this->db->seleccionar('app_estructura', 'id', 'padre = 0');
		$id = $this->db->ur('id');
		
		if ($id === false){
			$this->db->guardar('app_estructura', array(
				'texto' => 'Todos',
				'padre' => 0,
				'posicion' => 0,
				'codigo' => 'todos',
				'tipo' => 'S',
			), false, 'id');
			
			$id = $this->db->uid;
		}
		
		$id = (int) $id;
		
		foreach($ruta as $ll => $seg){
			$this->db->seleccionar('app_estructura', '(max(posicion) + 1) as pos', 'padre = ' . $id);
			$pos = $this->db->ur('pos');
			$pos = $pos === false ? 0 : intval($pos);
			
			$data = array(
				'texto' => humanize($seg),
				'padre' => $id,
				'posicion' => $pos,
				'codigo' => $seg,
				'tipo' => ($cruta === $ll ? 'C' : 'D'),
			);
			
			$this->db->seleccionar('app_estructura', 'id', 'padre = ' . $id . ' and codigo = \'' . $seg . '\'');
			$id_ex = $this->db->ur('id');
			
			if ($id_ex === false){
				$this->db->guardar('app_estructura', $data, false, 'id');
				$id = $this->db->uid;
			}else{
				$this->db->actualizar('app_estructura', $data, false, 'id = ' . $id_ex);
				$id = $id_ex;
			}
		}
		
		foreach($metodos as $metodo){
			$this->db->seleccionar('app_estructura', '(max(posicion) + 1) as pos', 'padre = ' . $id);
			$pos = $this->db->ur('pos');
			$pos = $pos === false ? 0 : intval($pos);
			
			$data = array(
				'texto' => humanize($metodo),
				'padre' => $id,
				'posicion' => $pos,
				'codigo' => $metodo,
				'tipo' => 'M',
			);
			
			$this->db->seleccionar('app_estructura', 'id', 'padre = ' . $id . ' and codigo = \'' . $metodo . '\'');
			$id_ex = $this->db->ur('id');
			
			if ($id_ex === false){
				$this->db->guardar('app_estructura', $data, false, 'id');
			}else{
				$this->db->actualizar('app_estructura', $data, false, 'id = ' . $id_ex);
			}
		}
	}
	
	protected function permisologia(){
		//echo md5(md5(php_uname() . PHP_OS . $_SERVER['PATH_TRANSLATED'] . $_SERVER['SERVER_ADMIN']));
		//$this->uri->segments
		
		if(empty($this->uri->segments) && $this->acceso !== false){ // estarian entrando en inicio
			return true;
		}
		
		$this->db->seleccionar('app_estructura', 'id', 'padre = 0');
		$id = $this->db->ur('id');
			
		// se realiza consultas para saber si la estructura existe y obtener el id del control o metodo
		foreach($this->uri->segments as $seg){
			$this->db->seleccionar('app_estructura', 'id', 'codigo = \'' . $seg . '\' and padre = ' . $id);
			$id = $this->db->ur('id');
			
			if ($id === false){
				return $this->acceso = false;
			}else{
				$this->acceso[] = (int) $id;
			}
		}
		
		// falta revisar la permisolocia del usuario
		$permisos = $this->session->userdata('permisos');
		
		if (!in_array($id, $permisos)){
			return false;
		}
		
		
		return true;
	}
}
<?php
class control_base_app extends CI_Controller{
	public $conf;
	public $url = '';
	
	protected $css = array();
	protected $js  = array();
	
	protected $rutascss = array('css', 'css/less', 'css/ui', 'plantillas');
	protected $rutasjs  = array('js', 'js/vendor');
	
	protected $rutascsscount = 0;
	protected $rutasjscount  = 0;
	
	protected $isajax;
	protected $modulo = '';
	
	protected $metodoForm = '';
	protected $metodosForm = array(
		'GET' => 'getget',
		'POST' => 'getpost',
		'HEAD' => 'getget',
		'PUT' => 'getget'
	);
	
	public function __construct(){
		if (!defined('BASEPATH')) exit('No se Puede Acceder Directamente al Script');
		
		parent::__construct();
		
		$this->isajax = $this->input->is_ajax_request();
		$this->conf = &$this->config->config;
		
		$this->fecha = date('Y-m-d');
		$this->fechaHora = date('Y-m-d H:i:s');
		
		$this->metodoForm = $_SERVER['REQUEST_METHOD'];
		
		$this->rutascss = $this->conf['rutascss'];
		$this->rutasjs 	= $this->conf['rutasjs'];
		
		$this->rutascsscount = count($this->rutascss);
		$this->rutasjscount  = count($this->rutasjs);
		
		//$this->load->ci =& get_instance();
		
		$segmento = end($this->uri->segments); //$this->uri->segment(1);
		$segmento = $segmento === false ? $this->conf['modulo'] : $segmento;
		
		//$this->router->routes->default_controller;
		
		// archivos de css necesarios para el inicio de la aplicacion
		
		$this->url = trim(current_url(), '/') . '/';
		
		$css = array();
		if(!empty($this->css)){
			$css = $this->css;
			$this->css = array();
		}
		
		if (!$this->isajax){
			$this->css($this->conf['cssConf']);
		}
		
		$this->css(array(
			($this->modulo === '' ? $this->conf['modulo'] : $this->modulo) . '/estilo.css',
			$segmento . '.css',
			$segmento . '.less',
			$css
		));
		
		// archivos de js necesarios para el inicio de la aplicacion
		$js = array();
		
		if(!empty($this->js)){
			$js = $this->js;
			$this->js = array();
		}
		
		if (!$this->isajax){
			$this->js($this->conf['jsConf']);
		}
		
		$this->js(array(
			$js,
			$segmento . '.js'
		));
		
		$this->js();
	}
	
	public function css($a = false){
		if ($a === false){
			return $this->css = array_values(array_unique($this->css));
		}
		
		if (is_array($a)){
			foreach($a as $aa){
				$this->css($aa);
			}
			return true;
		}
		
		if ($this->ccss($a)){
			$this->css[] = $a;
			return true;
		}
		return false;
	}
	
	public function js($a = false){
		if ($a === false){
			return $this->js = array_values(array_unique($this->js));
		}
		
		if (is_array($a)){
			foreach($a as $aa){
				$this->js($aa);
			}
			return true;
		}
		
		if ($this->cjs($a)){
			$this->js[] = $a;
			return true;
		}
		return false;
	}
	
	public function conf($prop, $val = null){
		if (!isset($this->conf[$prop])) return '';
		if (!is_null($val)) $this->conf[$prop] = $val;
		
		return $this->conf[$prop];
	}
	
	public function propag($json = true){
		$arr = array(
			'js' => $this->js(),
			'css' => $this->css(),
			'html' => array(
				'cabeza' => $this->conf('cabeza'),
				'banner' => $this->conf('banner'),
				'menu' => $this->conf('menu'),
				'menuFormulario' => $this->conf('menuFormulario'),
				'marco' => $this->conf('marco'),
				'contenedor' => $this->conf('contenedor'),
				'imagenPies' => $this->conf('imagenPies'),
				'pies' => $this->conf('pies'),
				'titulo' => $this->conf('titulo')
			)
		);
		
		return $json ? json_encode($arr) : $arr;
	}
	
	protected function pag($pagina, $parametros = array(), $salida = false){
		$ci = array('ci' => $this);
		if (!$this->isajax){
			$this->load->view('plantillas/cabeza', $ci);
			$this->load->view($pagina, array_merge($ci, $parametros));
			$this->load->view('plantillas/pies', $ci);	
		}else{
			header('Content-Type: text/html; charset=utf-8');
			header('Pragma: no-cache');
			header('Cache-Control: private, no-store, no-cache');
			
			$this->load->view($pagina, array_merge($ci, $parametros));
		}
	}
	
	protected function ccss(&$archivo){ // comprobar archivo css, verifica si existe un archivo en el directorio en css con extension "css" o "less"
		if (!preg_match("/\.(css)$/i", $archivo)){
			$archivo .= '.css';
		}
		
		for($i = 0; $i < $this->rutascsscount; $i++){
			// 
			if (is_file($this->rutascss[$i] . '/' . $archivo)){
				$archivo = $this->rutascss[$i] . '/' . $archivo;
				return true;
			}
		}
		
		return false;
	}
	
	protected function cjs (&$archivo){ // comprobar archivo javascripts, verifica si existe un archivo en el directorio en js o js/vendor con extension "js"
		if (!preg_match("/\.(js)$/i", $archivo)){
			$archivo .= '.js';
		}
		
		for($i = 0; $i < $this->rutasjscount; $i++){
			if (is_file($this->rutasjs[$i] . '/' . $archivo)){
				//$archivo = base_url() . $this->rutasjs[$i] . '/' . $archivo;
				$archivo = $this->rutasjs[$i] . '/' . $archivo;
				return true;
			}
		}
		
		return false;
	}
	
	public function archivosCabecera($mod = true){
		if ($this->isajax){
			if ($mod === true){
				return $this->archivosCabeceraCss() + $this->archivosCabeceraJs();
			}elseif ($mod === "css"){
				return $this->archivosCabeceraCss();
			}elseif ($mod === "js"){
				return $this->archivosCabeceraJs();
			}
		}else{
			if ($mod === true){
				echo $this->archivosCabeceraCss(), "\n", $this->archivosCabeceraJs();
			}elseif ($mod === "css"){
				echo $this->archivosCabeceraCss();
			}elseif ($mod === "js"){
				echo $this->archivosCabeceraJs();
			}
		}
		
		
		return true;
	}
	
	public function archivosCabeceraCss(){
		$sm = array();
		$s = '';
		
		foreach($this->css() as $valor){
			if ($this->isajax){
				$sm[] = $valor;
			}else{
				$s .= "\t\t" . $this->tmpl('<link rel="{rel}" type="text/css" href="{src}" />', array(
					"src" => base_url() . $valor,
					"rel" => 'stylesheet' . (substr($valor, -5) === '.less' ? '/less' : '')
				)) . "\n";
			}
		}
		
		return $this->isajax ? $sm : $s;
	}
	
	public function archivosCabeceraJs(){
		$sm = array();
		$s = '';

		foreach($this->js as $valor){
			if ($this->isajax){
				$sm[] = $valor;
			}else{
				$s .= "\t\t" . $this->tmpl('<script type="text/javascript" src="{src}"></script>', array("src" => $valor)) . "\n";
			}
		}
		
		return $this->isajax ? $sm : $s;
	}
	
	public function isajax(){
		return $this->isajax;
	}
	
	public function tratarFechas($fecha, $formato = false){
		if (!is_string($formato) && $formato !== false)
			return false;
		
		$fecha = trim($fecha);
		
		$formatos = array(
			'/^([0-9]{4})[-\/\.]?([0-9]{1,2})[-\/\.]?([0-9]{1,2})/',
			'/^([0-9]{4})[-\/\.]?([0-9]{1,2})[-\/\.]?([0-9]{1,2})[ ,-]*(([0-9]{1,2}):?([0-9]{1,2}):?([0-9\.]{1,4}))?/',
	
			'/^([0-9]{1,2})[-\/\.]?([0-9]{1,2})[-\/\.]?([0-9]{4})/',
			'/^([0-9]{1,2})[-\/\.]?([0-9]{1,2})[-\/\.]?([0-9]{4})[ ,-]*(([0-9]{1,2}):?([0-9]{1,2}):?([0-9\.]{1,4}))?/',
		);
	
		$rmatriz = false;
		$resultado = false;
	
		foreach($formatos as $ll => $v){
			if (preg_match($v, $fecha, $rr)){
				$resultado = $ll;
				$rmatriz = $rr;
			}
		}
	
		if ($resultado === false)
			return ''; //si retorna false posiblemente se genere un bucle infinito actualizarValores()
	
		if ($resultado < 2){
			$aux = $rmatriz[3];
			$rmatriz[3] = $rmatriz[1];
			$rmatriz[1] = $aux;
		}
	
		if (count($rmatriz) <= 4)
			$rmatriz = array_pad($rmatriz, 8, 0);
	
		$fechaunix = mktime($rmatriz[5], $rmatriz[6], $rmatriz[7], $rmatriz[2], $rmatriz[1], $rmatriz[3]);
	
		if ($formato === false)
			return $fechaunix;
		
		return date($formato, $fechaunix);
	}
	
	protected function _comillas($name = NULL, $xss = false){
		return $xss ? str_replace('\'', $this->db->db->replaceQuote, $name) : $name; 
	}
	
	public function getvar($name = NULL, $xss = false){
		return $this->_comillas($this->input->get_post($name, $xss), $xss);
	}
	
	public function getpost($name = NULL, $xss = false){
		return $this->_comillas($this->input->post($name, $xss), $xss);
	}
	
	public function get($name = NULL, $xss = false){
		return $this->{$this->metodosForm[$this->metodoForm]}($name, $xss);
	}
	
	public function getint($name = NULL){
		return (int) $this->get($name);
	}
	
	public function getfloat($name = NULL){
		return (float) $this->get($name);
	}
	
	public function getdate($name = NULL, $formato = 'Y-m-d'){
		$fecha = trim($this->get($name));
		
		if (strpos($fecha, ' - ') !== false){
			$fecha = explode(' - ', $fecha);
			foreach ($fecha as &$value) {
				$value = $this->tratarFechas($value, $formato);
			}
			return $fecha;
		}else{
			return $this->tratarFechas($fecha, $formato);
		}
	}

	public function getjson($name = NULL){
		return json_decode($this->get($name), true);
	}
	
	public function getget($name = NULL, $xss = false){
		return $this->_comillas($this->input->get($name, $xss), $xss);
	}
	
	public function id(){
		return $this->getint('id');
	}
	
	protected function salida($array = array()){
		header('content-type: application/json; charset=utf-8');
		header('Pragma: no-cache');
		header('Cache-Control: private, no-store, no-cache');
		
		//var_dump($this->output);
		if (!is_array($array) || empty($array)){
			$array = array('s' => 'n', 'msj' => '<strong>Error Interno:</strong> El argumento de la funcion salida es invalido.');
		}
		
		if (!array_key_exists('s', $array)){
			$array = array('s' => 'n', 'msj' => '<strong>Error Interno:</strong> El valor del argumento no es valido.');
		}
		
		if (!array_key_exists('msj', $array)){
			$array['msj'] = '';
		}
		
		if(!$this->isajax){
			exit(isset($array['msj']) ? $array['msj'] : '');
		}
		
		exit(json_encode($array));
	}
	
	public function tmpl($p, $arr){
		if (!is_array($arr)) return false;
	    
		$p = str_replace("' . $ . '", '{}', preg_replace('#\{([a-z0-9\-_]*?)\}#is', "' . $\\1 . '", str_replace('\'', '\\\'', trim($p))));
		
		foreach($arr as $key => $val){
			$key = trim($key);
			
			if ($key !== '' && $key !== 'p' && $key !== 'arr'){
				$$key = $val;
			}
		}
		
		@eval("\$p = '$p';");
		
		foreach($arr as $key => $val) unset($$key);
		return str_replace('\\\'', '\'', $p);
	}
	
	public function getIpCliente() {
		if (!empty($_SERVER['HTTP_CLIENT_IP']))
			return $_SERVER['HTTP_CLIENT_IP'];
			
		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
			
		return $_SERVER['REMOTE_ADDR'];
	}
	
	protected function salidaPrograma($msj, $iniciaSession = false){
		
		$salida = array('s' => 'n', 'msj' => $msj);
		if ($iniciaSession === true){
			$salida['__iniciarsession'] = 1;
		}
		
		if($this->isajax){
			$this->salida($salida);
		}
		
		$this->pag('error_autenticacion', $salida);
		
		echo $this->output->get_output();
		exit;
	}
	
	public function encriptar($c = ''){
		return $c === '' ? '' : sha1(md5(md5(str_rot13($c))));
	}
}
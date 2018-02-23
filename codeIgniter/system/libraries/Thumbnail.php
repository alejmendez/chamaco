<?php
// thumbnail.php?origen=origen.jpg&destino=destino.jpg&ancho=200&alto=200&relacion=true
// thumbnail.php?origen=origen.jpg&ancho=200&alto=200
// thumbnail.php?origen=origen.jpg
// ejemplo real:
//		http://localhost/Gobernacion/thumbnail.php?origen=images/foto_bio_gobernador.JPG&ancho=510&alto=200&relacion=true

//$origen='origen.png';

// ini_set('display_errors', 1);
// error_reporting(E_ALL);
// ini_set('memory_limit','500M');

//include_once('includes/funciones.php');

class Thumbnail{
	protected $extensiones_permitidas = array('jpg', 'jpeg', 'gif', 'png', 'bmp');
	
	protected $origen;
	protected $origen_ext;
	
	protected $img = null;
	protected $nombreImagen = null;
	
	protected $nombreThumbnail;
	protected $thumbnail;
	
	protected $directorio;
	protected $directorioThumbnail;
	protected $nombreArchivo;
	
	protected $destino;
	protected $destino_ext;
	
	protected $ancho = 0;
	protected $alto = 0;
	
	protected $aspecRadio = 2.36;
	protected $recortar = false;
	
	protected $relacion = 'inside';
	protected $prioridad = 'any';
	
	protected $gris = 'img/gris.gif';
	
	
	function __construct($salida = true){
		if (file_exists($file = BASEPATH . 'libraries/wideimage/WideImage' . EXT)){
			include_once($file);
		}elseif (file_exists($file = APPPATH . 'libraries/wideimage/WideImage' . EXT)){
			include_once($file);
		}
		$this->gris = site_url($this->gris);
		
		if ($this->init()){
			$this->hacerImagen();
			$this->redimencionarImagen($salida);
		}
	}
	
	function origen($v = false){
		$this->origen = $v === false ? utf8_decode($this->get('origen', '')) : $v; // archivo de origen
		$this->origen = str_replace('../', '', $this->origen); //se puede comentar la linea
		$this->origen_ext = $this->ext($this->origen);
		
		if ($this->origen === '' || array_search($this->origen_ext, $this->extensiones_permitidas) === false || !file_exists($this->origen)){
			return false;
		}
		
		$ruta = explode('/', $this->origen);
		$this->nombreArchivo = $this->nombreArchivo(array_pop($ruta));
		
		$this->directorio = implode('/',$ruta);
		$this->directorioThumbnail = $this->directorio . '/thumbnail';
		
		$this->nombreThumbnail = $this->nombreArchivo . '-' . $this->ancho . 'x'. $this->alto . '.'. $this->origen_ext;
		$this->thumbnail = $this->directorioThumbnail . '/' . $this->nombreThumbnail;
		
		if (!is_null($this->directorioThumbnail) && !is_dir($this->directorioThumbnail)){
			mkdir($this->directorioThumbnail, 0777);
		}
		
		return true;
	}
	
	function init(){
		$this->ancho 		= $this->getint('ancho');
		$this->alto 		= $this->getint('alto');
		
		$this->destino 		= $this->get('destino', ''); // archivo de destino
		$this->destino_ext 	= $this->ext($this->destino);
		
		$this->aspecRadio 	= $this->getfloat('aspecRadio', 2.36); //relacion ancho, alto
		$this->recortar 	= $this->getbool('recortar');
		
		$this->relacion 	= $this->get('relacion', array('inside', 'outside', 'fill'));
		$this->prioridad 	= $this->get('prioridad', array('any', 'down', 'up'));
		
		$o = $this->origen();
		
		return $o;
	}
	
	protected function get($v, $vd = null){
		if (is_array($vd)){
			return isset($_GET[$v]) ? (array_search($_GET[$v], $vd) === false ? $vd[0] : $_GET[$v]) : $vd[0];
		}
		
		return isset($_GET[$v]) ? $_GET[$v] : $vd;
	}
	
	protected function getint($v, $vd = null){
		return (int) $this->get($v, $vd);
	}
	
	protected function getfloat($v, $vd = null){
		return (float) $this->get($v, $vd);
	}
	
	protected function getbool($v){
		return (bool) isset($_GET[$v]);
	}
	
	protected function ext($v){
		return strtolower(substr($v, strrpos($v, '.') + 1));
	}
	
	protected function nombreArchivo($v){
		return strtolower(substr($v, 0, strrpos($v, '.')));
	}
	
	protected function _imagen($v){
		if (!is_file($v)){
			return false;
		}
		
		if ($this->nombreImagen === $v){
			return $this->img;
		}
		
		$this->nombreImagen = $v;
		
		$this->img = WideImage::load($v);
		
		return $this->img;
	}
	
	public function imagenHtml($v, $w = false, $h = false, $html=''){
		if ($v === '' || is_null($v)){
			return '<img alt="" src="' . $this->gris . '" width="' . $w . '" height="' . $h . '" class="imgc" ' . $html . ' />';
		}
		
		$this->ancho = $w === false ? 0 : $w;
		$this->alto  = $h === false ? 0 : $h;
		
		$this->origen($v);
		if (is_file($this->thumbnail)){
			$this->_imagen($this->thumbnail);
		}else{
			$this->redimencionarImagen(false);
		}
		
		$this->thumbnail = site_url($this->thumbnail);
		
		return '<img alt="" src="' . $this->gris . '" data-src="' . $this->thumbnail . '" width="' . $this->img->getWidth() . '" height="' . $this->img->getHeight() . '" class="imgc" ' . $html . ' />';
	}
	
	public function imagenDiv($v, $w = false, $h = false, $html=''){
		if ($v === '' || is_null($v)){
			return '<div style="width: ' . $this->img->getWidth() . 'px; height: ' . $this->img->getHeight() . 'px;" ' . $html . '></div>';
		}
		
		$this->ancho = $w === false ? 0 : $w;
		$this->alto  = $h === false ? 0 : $h;
		
		$this->origen($v);
		if (is_file($this->thumbnail)){
			$this->_imagen($this->thumbnail);
		}else{
			$this->redimencionarImagen(false);
		}
		
		$this->thumbnail = site_url($this->thumbnail);
		
		return '<div data-src="' . $this->thumbnail . '" style="width: ' . $this->img->getWidth() . 'px; height: ' . $this->img->getHeight() . 'px;" class="imgc" ' . $html . '></div>';
	}
	
	public function imagen($v, $w = false, $h = false){
		if ($v === '' || is_null($v)){
			return $this->gris;
		}
		
		$this->ancho = $w === false ? 0 : $w;
		$this->alto  = $h === false ? 0 : $h;
			
		$this->origen($v);
		if (is_file($this->thumbnail)){
			$this->_imagen($this->thumbnail);
		}else{
			$this->redimencionarImagen(false);
		}
		
		$this->thumbnail = site_url($this->thumbnail);
		
		return array($this->thumbnail, $this->img->getWidth(), $this->img->getHeight());
	}
	
	public function recortar($v){
		$this->recortar = (bool) $v;
	}
	
	protected function redimencionarImagen($salida = true){
		if (@is_file($this->thumbnail)){
			if ($salida){
				$this->_imagen($this->thumbnail);
				$this->salidaImagen($this->thumbnail);
				exit;
			}
			
			return false;
		}
		
		$this->_imagen($this->origen);
		
		if ($this->recortar){
			$w = $this->img->getWidth();
			$h = $this->img->getHeight();
			
			$top = $w > $h ? 'center' : ($w * 0.135);
			$hap = $w / $this->aspecRadio; //$h = $this->img->getHeight();
			
			$this->img = $this->img->crop(0, $top, $w, $hap);
		}
		
		$this->img = $this->img->resize(($this->ancho === 0 ? null : $this->ancho), ($this->alto === 0 ? null : $this->alto), $this->relacion, $this->prioridad);
		$this->img->saveToFile($this->thumbnail);
		
		if ($salida){
			$this->salidaImagen($this->thumbnail);
			exit;
		}
		
		return true;
	}
	
	protected function salidaImagen($v){
		$ext = $this->ext($v);
		$q = null;
		
		if ($ext === 'jpg' || $ext === 'jpeg'){
			$q = 80;
		}elseif ($ext === 'png'){
			$q = 8;
		}
		
		$this->img->output($ext, $q);
	}
	
	protected function hacerImagen(){
		if (!is_file($this->thumbnail)){
			return false;
	    }
	    
	    $this->_imagen($this->thumbnail);
	    $this->salidaImagen($this->thumbnail);
		exit;
	}
}
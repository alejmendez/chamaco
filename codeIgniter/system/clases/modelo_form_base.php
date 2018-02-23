<?php
/**
 * GEB.
 *
 * version 1.1 - 08/07/2011
 * Se dio un salto al modificar la estructura y agregando el archivo de "plantillas", ahora los elementos de
 * los formularios se manejan por medio de herencia de objetos para un mejor control, esto hace que se ahorre
 * tiempo y lineas de codigo, entre los cambios esta el metodo sArchivos (muchas menos lineas de codigo)
 *
 * version 1.0.1
 * Se agregaron nuevos elementos y metodos para facilitar la programacion, los metodos de base de datos se
 * modificaron para evitar algunos errores encontrados.
 *
 * version 1.0
 * Inicio del objeto formulario (form), maneja una cantidad considerable de elementos que interactual entre
 * si para simular el funcionamiento de un formulario integral.
 *
 */

include_once(BASEPATH . 'clases/modelo_form_base_plantillas' . EXT);

class modelo_form_base extends CI_Model{
	protected $db;
    protected $version = '2.0';
    public $esObjForm = true;

	protected $objTipos = array();
	protected $objTiposChk = array();
	protected $objs = array();

	public $debug = false;
    public $debugdb = false;
	protected $tLog = '';
	public $usaUI = true;
	protected $id = '';
	protected $idFormulario = '';
	protected $titulo = '';

	protected $url = '';
	protected $tabla = '';
	protected $metodo = 'POST';
	public $actualizacion = 0;
	protected $accionjs;
    protected $usarContexto = true;

    public $ultimoIdInsertado = 0;

    protected $validacion = 1;

    protected $seleccionados = array();
    protected $forzarSalida = true;

    protected $get_magic_quotes_gpc = false;

    protected $EOF = true;
    protected $relaciones = array();
    protected $rs = false;
    protected $json = array();
    protected $dbSalidaQuery;

    protected $logValidacion = array();
	
	protected $contenedorObj = array(
		'id' => 'obj',
		'clase' => 'obj form-group',
		'dbActivo' => true,
		'contenedorObjs' => '',
		'contenedor' => '',
		'usaUI' => false,
		'sin' => 'id'
	);

    protected $parametros = array('id', 'url', 'db', 'tabla', 'titulo', 'forzarSalida', 'metodo');
	/**
	 * se inicializa la salida de base de datos, en caso de no haber realizado ninguna accion.
	 */
    public $dbSalida = array('s' => 'n', 'msj' => 'No se ha Realizado Ninguna Accion');

    public $campos = array();
    protected $ci;

	public function __construct($objetos = NULL){
		$this->ci = &get_instance();
		$this->get_magic_quotes_gpc = function_exists("get_magic_quotes_gpc") ? get_magic_quotes_gpc() : false;
		
		$this->contenedorObj = new objdiv($this->contenedorObj);

		if (!empty($this->campos)){
			$this->cargarObjetos($this->campos);
		}

		if ($objetos !== NULL && !empty($objetos)){
			$this->cargarObjetos($objetos);
		}
	}
	
	public function verificarLicencia(){
		$this->ci->load->library('Mac');
		$macs = $this->ci->mac->GetMacAddr();
		$clave = hash('sha512', hash('sha512', php_uname() . PHP_OS . $_SERVER['SERVER_ADMIN'] . implode($macs)));
		if (!is_file(APPPATH . '/core/licencia.phar')){
			$this->errorLicencia();
		}else{
			include_once(APPPATH . '/core/licencia.phar');
			
			$this->ci->load->library('mac');
			$clave = hash('sha512', hash('sha512', php_uname() . PHP_OS . $_SERVER['SERVER_ADMIN']));
		}
	}
	
	public function errorLicencia(){
		exit('error de licencia');
	}
	
	public function __destruct(){
		foreach($this->objs as $ll => $v){
			$this->objs[$ll] = null;
			unset($this->objs[$ll]);
		}
	}

	public function cargarObjetos($objetos){
		if (!is_array($objetos)){
			echo 'Error: el parametro no es array.';
			return false;
		}

		foreach ($objetos as $index => $valor){
			$this->agregarObj($valor, $index);
		}
		
		if ($this->get('barra') === false){
			$this->agregarObj(array(
				'id' => 'barra',
				'tipo' => 'div',
				'clase' => 'barra',
				'dbActivo' => true,
				'valorDeFuente' => false,
				'contenedorObjs' => '',
				'contenedorObjsClase' => '',
				'contenedor' => '',
				'usaUI' => false,
				'sin' => 'id, validar'
			))->clonar('barra', '|');
		}

		$this->sArchivos();

		$this->db($this->db());
		$this->tabla($this->tabla());

        return $this;
	}

	public function agregarObj($obj, $index = null){
		if (in_array($index, $this->parametros) && method_exists($this, $index)){
			if ($index == 'db'){
				if (is_object($obj) && isset($obj->esObjDB) && $obj->esObjDB === true){
    				$this->db = $obj;
    				return $this;
				}
			}

			$this->$index($obj);
			return $this;
		}

		if (is_object($obj)){
			return $this->agregarForm($obj);
		}elseif (!is_array($obj)){
			echo 'Error: el parametro no es array.';
			return false;
		}elseif (!array_key_exists('id', $obj)){
			$this->sLog('<b>Adventencia:</b> No se definio la propiedad "id" del objeto. El objeto no fue creado.');
			return $this;
		}
		
		$id = $obj['id'];
		$tipo = $obj['tipo'] = array_key_exists('tipo', $obj) ? trim($obj['tipo']) : 'oculto';

		if (strpos($tipo, 'obj') === false)
			$tipo = 'obj' . $tipo;

	    if (!class_exists($tipo)){
        	$this->sLog('<b>Error:</b> No Existe la Clase ' . $tipo . '.');
			return false;
		}

		if ($this->objs($id) !== false){
			$this->sLog('eliminado ' . $id . ' para ser de tipo ' . $tipo);
			$this->eliminarElemento($id);
		}
		
		if (!array_key_exists('contenedor', $obj)){
			$obj['contenedor'] = $this->contenedorObj;
		}

		$obj = $this->objs($id, new $tipo($obj));
		if ($obj->propiedad('usaUI') === true){
			$obj->cambiarPropiedad(array('usaUI' => $this->usaUI));
		}

		if ($id === 'id'){
			$this->campoClave('id');
		}

		$this->objTiposChk[$tipo] = true;

		$this->dblista($id);

		return $this;
	}

	protected function _esObjForm($obj){
		if (is_string($obj) && isset($this->objs[$obj]))
			$obj = $this->objs[$obj];

		return is_object($obj) && isset($obj->esObjForm) && $obj->esObjForm === true;
	}

	protected function agregarForm($obj){
		if (!$this->_esObjForm($obj)){
			echo 'Error: el parametro no es Obj Form.';
			return $this;
		}

		$this->forms($obj->idFormulario(), $obj);
		return $this;
	}

	public function sLog($t = false){
		if ($t === false)
			return $this->tLog;

		$t .= "<br />\n";
		$this->tLog .= $t;

		if ($this->debug == true) echo $t;

		return $this;
	}

	public function id($id = false){
		if ($id === false)
			return $this->id;

    	$id = (string) $id;

        $this->id = $this->idFormulario = $id;
        if ($this->titulo == '')
        	$this->titulo = $id;

        return $this;
	}

    public function idFormulario($id = false){
    	return $this->id($id);
    }

	public function titulo($titulo = false){
		if ($titulo === false)
			return $this->titulo;

        $this->titulo = (string) $titulo;
        return $this;
    }

    public function url($url = false){
    	if ($url === false)
			return $this->url;

        $this->url = (string) $url;
        return $this;
    }

    public function db($DB = false){
    	if (!(is_object($DB) && isset($DB->esObjDB) && $DB->esObjDB === true))
    		return $this->db;

     	$this->db = $DB;
        foreach($this->objs() as $v){
	        if ($v->propiedad('fuente') != ''){
				$this->db->query($v->propiedad('fuente'));
				$v->cambiarPropiedad(array('valorFuente' => $this->db->rs));

				if (method_exists($v, 'valorFuente')){
					$v->valorFuente($this->db->rs);
				}
			}

			if (method_exists($v, 'form')){
				$v->form($this);
			}
        }

        return $this;
    }

    public function tabla($tabla = false){
    	if ($tabla === false)
    		return $this->tabla;

        $this->tabla = (string) $tabla;
        $this->tabla = trim($this->tabla);

        if ($this->tabla !== '' && !$this->existeTabla($this->tabla())){
        	echo "<pre>\nLa Tabla \"$tabla\" no existe, sql para crearla: \n\n";
			$sql = $this->crearTablarDB();
			foreach($sql as $s){
				echo $s . ";\n\n";
			}
			echo "</pre>";
			exit;
		}

        return $this;
    }

    public function contenedor($id, $propiedades, $valor = false){
   	 	$id = $this->selector($id);
    	if(is_string($propiedades) && $valor !== false){
    		$propiedades = array($propiedades => $valor);
    	}elseif (!is_array($propiedades) && $propiedades !== false && $valor === false){
    		return $this;
    	}

    	foreach ($id as $objs){
    		if (!is_object($objs->contenedor))
    			continue;

			if($propiedades === false){
	    		$objs->propiedad('contenedor', '');
	    		continue;
	    	}

			foreach ($propiedades as $llp => $vp){
				$objs->contenedor->propiedad($llp, $vp);
			}
    	}

    	return $this;
    }

    public function usarContexto($contexto = true){
    	if (is_string($contexto) || is_bool($contexto))
    		$this->usarContexto = $contexto;

   		return $this;
    }

    protected function _esObj($obj){
    	if (is_string($obj) && isset($this->objs[$obj]))
    		$obj = $this->objs[$obj];

   		return is_object($obj) && $obj->propiedad('esObj') === true;
    }

    public function objs($id = false, $valor = false){
    	if (is_object($id) && isset($id->esObj)){
    		return $id;
    	}

    	if (is_string($id) && !$this->_esObjForm($id)){
    		if (isset($this->objs[$id]) && $valor === false){
    			return $this->objs[$id];
    		}elseif ($this->_esObj($valor)){
    			$this->objs[$id] = $valor;
    			return $this->objs[$id];
    		}

    		return false;
		}

    	$r = array();
    	foreach($this->objs as $ll => $v){
    		if ($this->_esObjForm($this->objs[$ll])) continue;
			$r[$ll] = $v;
		}
		return $r;
    }

    public function forms($id = false, $valor = false){
    	if ($id !== false){
    		if ($this->_esObjForm($id)){
    			return $this->objs[$id];
    		}elseif ($this->_esObjForm($valor)){
    			$this->objs[$id] = $valor;
    			return $this->objs[$id];
    		}

    		return false;
		}

    	$r = array();
    	foreach($this->objs as $ll => $v){
    		if ($this->_esObjForm($v))
				$r[$ll] = $v;
		}
		return $r;
    }

    public function selector($selector, $devolverObjs = true){
    	if (!is_array($selector) && !is_string($selector)){
    		return array();
    	}

		$selector = empty($selector) ? array('*') : (is_string($selector) ? explode(',', str_replace(array("\n", "\r", "\t"), '', $selector)) : $selector);
        $selectoresArray = array();
        $negadosArray = array();

        foreach ($selector as $ll => $id) {
        	if (!is_string($id)){
        		continue;
			}

            $id = trim($id);
            $objs = $this->objs();

            if($id == '*'){
    			foreach($objs as $ll => $v){
    				if ($ll === 'barra' || $ll === '|') continue;
    				$selectoresArray[] = $ll;
				}
    		}else{
                $negado = false;
                $primerCaracter = substr($id,0,1);

                if ($primerCaracter == '!'){
                    $id = substr($id,1);
                    $negado = true;
                    $primerCaracter = substr($id,0,1);
                }

				if ($primerCaracter == '.'){
                	$id = substr($id,1);
                	$arr = array();
   					foreach ($objs as $ll => $v) {
   						if (strpos($v->clase, $id) !== false)
   							$arr[] = $ll;
   					}

   					if ($negado){
   						$negadosArray = array_merge($negadosArray, $arr);
   					}else{
   						$selectoresArray = array_merge($selectoresArray, $arr);
   					}

   					continue;
				}


                if ($this->objs($id) !== false){
                    if ($negado){
    				    $negadosArray[] = $id;
                    }else{
                        $selectoresArray[] = $id;
					}
    			}else{
    				$this->sLog("El objeto $id no existe\n<br>");
				}
    		}
        }

        $selectoresArray = array_diff($selectoresArray, $negadosArray);

        $this->seleccionados = $selectoresArray;
        $selectoresArray = array();

        if ($devolverObjs === false){
        	return $this->seleccionados;
        }

        foreach($this->seleccionados as $ll){
        	$selectoresArray[] = $this->objs($ll);
        }

        return $selectoresArray;
    }

	public function formulario($imprimir = false){
		$plantilla = 'id="{idFormulario}" name="{idFormulario}" action="{url}" title="{titulo}" method="{metodo}" enctype="{enctype}" encoding="{encoding}"';
		$plantilla = $this->ci->tmpl($plantilla, array(
			'idFormulario' 	=> $this->id,
			'url' 			=> $this->url,
			'titulo' 		=> $this->titulo,
			'metodo' 		=> 'POST',
			'enctype'		=> $this->enctype(),
			'encoding'		=> $this->encoding()
		));

		$plantilla = preg_replace('/([a-z]+)=""/i', '', $plantilla); // elimina propiedades vacias
		$plantilla = preg_replace('/\s{2,}/', ' ', $plantilla); // elimina espacios en blancos repetidos

        if ($imprimir === true)
            echo $plantilla;
        else
            return $plantilla;

        return $this;
	}

	public function clonar($a, $b){
		if (!is_array($b))
			$b = array((string) $b);

		foreach($b as $v){
			$v = (string) $v;
			if ($this->objs($v) === false){
				if ($v === '')
					continue;

				$this->objs($v, clone $this->objs($a))->cambiarPropiedad(array('id' => $v));
			}
		}

		return $this;
	}

	public function eliminarElemento($id){
		foreach($this->selector($id, false) as $obj){
        	unset($this->objs[$obj]);
		}

		return $this;
	}

	public function validar($id = '*', $idDesvalidar = NULL){
	    foreach($this->selector($id) as $obj)
        	$obj->cambiarPropiedad(array('validar' => true));

		if (!is_null($idDesvalidar))
			$this->desValidar($idDesvalidar);

		return $this;
	}

	public function desValidar($id = '*'){
        foreach($this->selector($id) as $obj)
        	$obj->cambiarPropiedad(array('validar' => false));

		return $this;
	}

	public function get($obj){
		return $this->objs((string) $obj);
	}

	public function hacer($id = '*', $salida = false, $saliaTexto = true){
        $s = '';

        foreach($this->selector($id) as $obj){
			$so = $obj->hacer($salida, $saliaTexto);
			if ($so !== true)
				$s .= $so;
		}

        return $s;
	}

	public function contenedorObjs($cont){
		$cont = (string) $cont;
		foreach($this->objs() as $v)
			$v->cambiarPropiedad(array('contenedorObjs' => (string) $cont));

        return $this;
	}

	public function getNombre($id, $salida = true){
	    if ($this->objs($id) === false)
            echo '<strong>Error:</strong> No existe el objeto ' . $id;
        else
			return $this->objs($id)->getNombre($salida);

        return false;
	}

	function desactivar($id = '*'){
		foreach($this->selector($id) as $obj)
			$obj->desactivar();

        return $this;
	}

	public function activar($id = '*'){
		foreach($this->selector($id) as $obj)
        	$obj->activar();

        return $this;
	}

	public function buscarObj($arr, $retornarArray = false){
		if (!is_array($arr)) return false;

		$salida = array();
		foreach($this->objs() as $v){
			foreach($arr as $llp => $vp){
				$negado = false;
				if (substr($llp, 0, 1) === '!'){
					$negado = true;
					$llp = substr($llp, 1);
				}

				if ((!is_array($vp) && strpos($vp, '|') && array_search($v->propiedad($llp), explode('|', $vp)) !== false) ||
					($v->propiedad($llp) == $vp && $negado === false) ||
					($v->propiedad($llp) != $vp && $negado === true)){
	 				$salida[] = $v->id();
				}
			}
		}

		$salida = array_unique($salida);
		return ($retornarArray === false) ? implode(', ', $salida) : $salida;
	}

	public function metodo($metodo = false){
		if ($metodo === false)
			return strtoupper($this->metodo);

		$this->metodo = (string) $metodo;
		return $this;
	}

	protected function getvars($id, $xss = false){
		if ($this->metodo() === 'POST'){
			return $this->ci->getpost($id, $xss);
        }elseif ($this->metodo() === 'GET'){
        	return $this->ci->getget($id, $xss);
		}elseif ($this->metodo() === 'JSON'){
			if (empty($this->json)){
				$this->jsonData($this->id());
			}

			$valor = isset($this->json[$id]) ? (!is_array($this->json[$id]) ? trim($this->json[$id]) : $this->json[$id]) : '';

			return $xss === true ? $this->security->xss_clean($valor) : $valor;
		}else{
			parse_str($this->valores, $salida);
			$valor = isset($salida[$id]) ? $salida[$id] : '';
			return $xss === true ? $this->security->xss_clean($valor) : $valor;
		}
	}

	public function jsonData($data = null){
		if (is_null($data)){
			return $this->json;
		}elseif (is_string($data)){
			$data = isset($_POST[$data]) ? $_POST[$data] : (isset($_GET[$data]) ? $_GET[$data] : $data);
			$data = str_replace('\"', '"', $data);
			$data = json_decode($data, true);

			if ($data === false || is_null($data)){
				$this->json = array();
				return false;
			}

			$this->json = $data;
		}elseif (is_array($data)){
			$this->json = $data;
			$this->actualizarValores();
			return $this;
		}

		if (count($this->json) > 1){
			$valor = array();
			foreach($this->json as $v){
				foreach($v as $llv => $vv){
					$valor[$llv][] = $vv;
				}
			}

			$this->json = $valor;
		}elseif (is_array($this->json) && !empty($this->json)){
			$this->json = $this->json[0];
		}

		//$this->actualizarValores();

		return $this;
	}

	public function forzarSalida($f = true){
		$this->forzarSalida = (bool) $f;
		return $this;
	}

	public function propiedad($id, $propiedad, $valor = null){
		if (!is_string($id) || !is_string($propiedad))
			return false;

		if ($valor !== null)
			return $this->cambiarPropiedad($id, array($propiedad => $valor));

		$obj = $this->objs($id);
		return $obj === false ? false : $obj->propiedad($propiedad);
	}

	public function cambiarPropiedad($id, $propiedad, $valor = ''){
        $id = $this->selector($id);

		if (!is_array($propiedad))
			$propiedad = array($propiedad => $valor);

        foreach ($id as $obj){
        	if (array_key_exists('id', $propiedad)){
        		$this->clonar($obj->id(), $propiedad['id'])->eliminarElemento($obj->id());
        		$obj = $this->objs($propiedad['id']);

				unset($propiedad['id']);
			}

        	$obj->cambiarPropiedad($propiedad);

       		if (array_key_exists('tipo', $propiedad)){
       			$propiedadesObj = get_object_vars($obj);

				$contenedor = $obj->propiedad('contenedor');

       			$propiedadesEliminar = array('jQuery', 'archJs', 'archCss', 'type', 'plantilla', 'file', 'borde');
       			foreach($propiedadesEliminar as $pv){
       				unset($propiedadesObj[$pv]);
       			}

       			$tipo = $propiedad['tipo'];
       			if (strpos($tipo, 'obj') === false)
       				$tipo = 'obj' . $propiedad['tipo'];

				$obj = $this->objs($obj->id(), new $tipo($propiedadesObj));
				$obj->propiedad('contenedor', $contenedor);
				$this->sArchivos();
       		}
		}

		return $this;
	}

	public function validacion($validacion = NULL){
		// validacion === 0 (no validar)
		// validacion === 1 (validar, generará una salida del script)
		// validacion === 2 (validar, aunque si el resultado es falso solo salta la iteracion actual)
		if (!is_null($validacion)){
			$validacion = (int) $validacion;
			if ($validacion >= 0 && $validacion <= 2)
				$this->validacion = $validacion;
		}else{
			return $this->validacion;
		}

		return $this;
	}

	protected function _validacion($valores){
		if ($this->validacion === 0){
			return true;
		}

		$salida = true;
		foreach($this->objs() as $v){
			$id = $v->id(); 
			$relacion = $v->propiedad('relacion');
			$validar = $v->propiedad('validar');

			list($DBNombre, $DBTipo) = $this->dblista($id);

			if (!($v->propiedad('dbActivo') === true && $v->propiedad('nombreDB') != '' && is_null($relacion)) ||
				$validar === false || $salida === false ||
				$v->propiedad('campoPrincipal') === true){
				continue;
			}

			if (!array_key_exists($id, $valores)){
				$this->logValidacion[] = "El Valor del Campo '$id' no Esta Definido.";
				$salida = false;
				continue;
			}

			$valor = $valores[$id];

			if ($DBTipo !== false && array_search($DBTipo, array('bool', 'int', 'cedula', 'porcentaje', 'decimal', 'currency')) === false){
				
				$valor = trim($valor);
			}else{
				switch($DBTipo){
					case 'bool':
						break;

					case 'int':
						if (intval($valor) === 0){
							$this->logValidacion[] = "El Valor del Campo '$id' No puede ser Cero (0).";
							$salida = false;
						}

						break;

					case 'cedula':
						if (intval(str_replace(array(',', '.'), '', $valor)) === 0){
							$this->logValidacion[] = "El Valor del Campo '$id' No Tiene el Formato de Cedula.";
							$salida = false;
						}
						break;

					case 'porcentaje':
						$valor = trim($valor, '%');
					case 'decimal':
					case 'currency':
						if (floatval($valor) == 0){
							$this->logValidacion[] = "El Valor del Campo '$id' No puede ser Cero (0).";
							$salida = false;
						}

						break;
				}

				continue;
				
			}

			if ($validar === 'email' && !preg_match('|^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]{2,})+$|i', $valor)){
				$this->logValidacion[] = "El Valor del Campo '$id' No es un correo.";
				$salida = false;

				continue;
			}

			if ($valor == ''){
				$this->logValidacion[] = "El Valor del Campo '$id' No Puede estar Vacio.";
				$salida = false;
			}
		}

		return $salida;
	}

	public function val($id, $valor = NULL, $valDb = false, $conFormato = true){
		$obj = $this->objs($id);

		if ($obj === false)
			return '';

        if ($valor !== NULL){
        	if ($conFormato === true)
				$valor = $this->formato($id, $valor);

			$campoMatrizDb = (bool) $obj->propiedad('campoMatrizDb');
        	if ($campoMatrizDb && !is_array($valor) && !is_object($valor)){
        		$valor = array();
        	}

			$obj->valor($valor);

			if ($valDb === true)
                $this->valdb($id, $obj->valor());

            return $this;
        }

		if ($this->actualizacion === 0 && !$this->actualizarValores()){
			return false;
		}

		return $obj->valor();
	}

	public function valdb($id, $valDb = false){
		if (!is_string($id))
			return $this;

		$obj = $this->objs($id);

		if ($obj === false)
			return '';

        if ($valDb !== false){
        	list($DBNombre, $DBTipo) = $this->dblista($id);

			$campoMatrizDb = (bool) $obj->propiedad('campoMatrizDb');
        	if ($campoMatrizDb && !is_array($valDb)){
        		$valDb = array();
        	}

			$valor = $this->__procesarVariableVal($valDb, $DBTipo, $campoMatrizDb);

	    	$obj->dbvalor($valor);
            return $this;
        }

		if ($this->actualizacion === 0 && !$this->actualizarValores()){
			return false;
		}

		return $obj->dbvalor();
	}

	protected function __procesarVariableVal($var, $db = false, $campoMatriz = false){
		//$this->to_pg_array($valor)
		$campoMatriz = (bool) $campoMatriz;
		if (is_array($var)){
			foreach($var as $ll => $v)
        		$var[$ll] = $this->__procesarVariableVal($v, $db, $campoMatriz);

 			return $campoMatriz ? $this->to_pg_array($var) : $var;
		}

		if ($db !== false){
			if ($db === 'date' && is_null($var)){
				$var = 'null';
			}elseif (array_search($db, array('bool', 'int', 'cedula', 'porcentaje', 'decimal', 'currency')) === false){
				$var = $this->db->db->qstr($var, $this->get_magic_quotes_gpc);
				if ($campoMatriz){
					$var = substr($var, 1, strlen($var) - 2);
				}
			}
		}

		return $var;
	}

	public function valor($id, $valor = false, $valDb = false){
		if (!is_string($id))
			return $this;

		$obj = $this->objs($id);

		if ($obj === false)
			return '';

		if ($valor !== false){
        	if ($valDb === true){
        		list($DBNombre, $DBTipo) = $this->dblista($id);
                $obj->dbvalor($this->__procesarVariableVal($valor, $DBTipo));
			}

			$obj->cambiarPropiedad(array('valor' => $valor));
            return $this;
		}

		if ($this->actualizacion === 0 && !$this->actualizarValores()){
			return false;
		}

		return $this->getvars($id, $obj->propiedad('limpiarXss'));
	}

	public function valarr($id = '*'){
		$arr = array();
		foreach($this->selector('*') as $obj){
			$arr[$obj->id()] = $obj->valor();
		}

		return $arr;
	}

	public function valorFuente($id = '*'){
		$id = $this->selector($id);

        foreach ($id as $obj){
        	if ($obj->propiedad('valorFuente') === '')
       			continue;

			$valorFuente = $obj->propiedad('valorFuente');
        	$objm = is_object($obj->propiedad('valorFuente')) ? $this->db->rsAMatriz($valorFuente) : $valorFuente;

			//var_dump(debug_backtrace());
        	if (isset($objm[$obj->valor()])){
        		$ele = &$objm[$obj->valor()];
        		$obj->valor(is_array($ele) ? $ele['nombre'] : $ele);
			}
		}

		return $this;
	}

	public function ejecutarSalida($id = '*'){
		$id = $this->selector($id);

		foreach($this->dbSalida as $ll => $v){
			if (isset($this->objs[$ll]) && in_array($ll, $id))
				$this->dbSalida[$ll] = $this->objs[$ll]->valor;
		}
		return $this;
	}

	public function formato($id, $val){
		$DBTipo = $this->tipoDato($id);

		if (is_array($val)){
			foreach($val as $ll => $v){
				$val[$ll] = $this->formato($id, $v);
			}
			return $val;
		}

		if ($this->objs($id) === false)
			return $val;

		switch ($DBTipo){
			case 'str':
			case 'file':
				break;
			case 'int':
				$val = (int) $val;
				break;
			case 'tiempo':
			case 'date':
				$val = $this->tratarFechas($val, $this->objs($id)->propiedad('formatoDB') !== false ? $this->objs($id)->propiedad('formatoDB') : 'Y-m-d H:i:s');
				break;
			case 'porcentaje':
				$val = trim($val, '%');
				break;
			case 'currency':
			case 'decimal':
				$val = str_replace(array('.', ','), '.', $val);
				break;
			case 'encry':
				$val = $this->encriptar($val);
				break;
			case 'bool':
				$val = ($val == '' || $val == 'false' || intval($val) == 0) ? 0 : 1;
				break;
			case 'cedula':
				$val = (int) str_replace(array('.', ','), '', $val);
				break;
			default:
				break;
		}

		return $val;
	}

	public function formatoDb($val, $id = null){
		if (is_array($val) && $id === null){
			foreach($val as $ll => $v){
				$val[$ll] = $this->formatoDb($v, $ll);
			}
			return $val;
		}

		$obj = $this->objs($id);
		if ($obj === false){
			return $val;
		}elseif(is_array($obj)){
			// revisar
			foreach($obj as $ll => $v){
                   list($DBNombre, $DBTipo, $obj) = $this->dblista($ll);
                   
                   if ($obj != $ll){
                           $val[$obj] = $val[$ll];
                           unset($val[$ll]);
                   }
           }
           //print_r($salid);
           return $val;
			//falta hacer el codigo, cuando buscar retorna mas de un resultado
		}

		if ($obj->propiedad('campoMatrizDb')){
			$val = $this->db->str_getcsv(trim($val, '{}'));
		}

		$DBTipo = $this->tipoDato($id);

		$val = $this->__formatoDb($val, $DBTipo,$id);

		return $val;
	}

	protected function __formatoDb($val, $DBTipo,$id=""){
		if (is_array($val)){
			foreach($val as $ll => $v){
				$val[$ll] = $this->__formatoDb($v, $DBTipo);
			}
			return $val;
		}
//var_dump($val);
		switch ($DBTipo){
			case 'str':
			case 'encry':
			case 'file':
				break;
			case 'int':
				$val = $val == '' ? 0 : (int) $val;
				break;
			case 'tiempo':
			case 'date':
				$val = $this->tratarFechas($val, $this->objs($id)->propiedad('formato') !== false ? $this->objs($id)->propiedad('formato') : 'd/m/Y H:i:s');
				//$val = !fechaNula($val) ? fecha_mysql_n($val) : '';
                break;
			case 'porcentaje':
				$val = trim($val, '%') . '%';
				break;
			case 'currency':
				$val = number_format($val, 2, ',', '.');
				break;
			case 'decimal':
				$val = str_replace('.', ',', $val);
				break;
			case 'bool':
				$val = $val == '' ? 0 : 1;
				break;

			default:
				break;
		}

		return $val;
	}

	public function actualizarValores($objs = ''){
		$this->actualizacion++;

		foreach($this->forms() as $v){
			if ($v->actualizacion === 0){
				$v->actualizarValores();
			}
		}

		if ($objs == ''){
			$objs = $this->objs();
		}elseif (!is_array($objs)){
			echo('error, no paso array');
			return false;
		}else{
			$objs = array_flip($objs);
		}

		foreach ($objs as $ll => $v){
			if ($v->propiedad('dbActivo') !== true){
				continue;
			}

			if ($v->propiedad('actualizable') === true){
				list($DBNombre, $DBTipo) = $this->dblista($ll);

				if ($DBTipo == 'file'){
					$file = $v->propiedad('file');
					$salida = $this->guardar_archivo($v->propiedad('nombre'), $file['ruta'], $file['ext'], $file['tm'], $file['nomorg']);

					if ($salida === false){
						$this->sLog('Error al Subir el Archivo ' . $v->propiedad('nombre'));
						return false;
					}
				}else{
					$salida = $this->getvars($ll, $v->propiedad('limpiarXss'));
				}

				if (!is_object($this->val($ll)) && $this->val($ll) != $this->formato($ll, $salida)){
					$this->objs($ll)->propiedad('enviado', true);
				}

				$this->val($ll, $salida, true);
			}else{
				$v->dbvalor($v->valor());
			}
		}

		return $this;
	}

	public function dblista($id){
		$obj = $this->objs($id);
		if ($obj === false)
			return false;

		if (is_object($id)){
			$obj = $id;
		}

		$nombreDB = $obj->propiedad('nombreDB');

		$posicion = strrpos($nombreDB, ':');
		if ($posicion === false){
			$DBNombre = trim($nombreDB);
			$DBTipo = 'str';
		}else{
			$DBNombre = trim(substr($nombreDB, 0, $posicion));
			$DBTipo = trim(strtolower(substr($nombreDB, $posicion + 1)));
		}

		$obj->cambiarPropiedad(array('nombreCampo' => $DBNombre));
		$obj->cambiarPropiedad(array('tipoCampo' => $DBTipo));

		return array($DBNombre, $DBTipo, $id);
	}

	public function tipoDato($id){
		$lista = $this->dblista($id);
		return $lista === false ? false : $lista[1];
	}

	protected function variablesDB(){
		if ($this->actualizacion === 0 && !$this->actualizarValores())
			return false;

		$camposValidacion = $campos  = array();
		$_isArray = 1;

		foreach ($this->objs() as $ll => $v){
			$relacion = $v->propiedad('relacion');
			if ($v->propiedad('dbActivo') === true && $v->propiedad('nombreDB') != '' && (is_null($relacion) || is_string($relacion))){
				list($DBNombre) = $this->dblista($ll);
				$s = $this->val($ll);

				if (!$v->propiedad('campoMatrizDb') && is_array($s) && count($s) > $_isArray)
					$_isArray = count($s);
			}
		}

		foreach ($this->objs() as $ll => $v){
			$relacion = $v->propiedad('relacion');
			if ($v->propiedad('dbActivo') === true && $v->propiedad('nombreDB') != '' && (is_null($relacion) || is_string($relacion))){
				list($DBNombre, $DBTipo) = $this->dblista($ll);

				$s = $this->valdb($ll);
				$sv = $this->val($ll);
				
				if ($s !== false){
					for($j = 0; $j < $_isArray; $j++){
						if (is_array($s)){
							$camposValidacion[$j][$ll] = $sv[$j];
							$campos[$j][$DBNombre] = $s[$j];
						}else{
							
							$camposValidacion[$j][$ll] = $sv;
							$campos[$j][$DBNombre] = $s;
						}
					}
				}
			}
		}

		for($j = 0; $j < $_isArray; $j++){
			if (!$this->_validacion($camposValidacion[$j])){
				//var_dump($camposValidacion[$j]);
				unset($camposValidacion[$j], $campos[$j]);
			}
		}
//print_r($camposValidacion);
		if (empty($campos)){
			foreach($this->logValidacion as &$v){
				$v = "($this->id): " . $v;
			}

			$this->dbSalida = array('s' => 'n', 'msj' => "Inconvenientes con la Validacion de los Datos.\n" . implode("\n", $this->logValidacion));

			if ($this->forzarSalida === true)
				$this->dbSalida();

			return false;
		}

		return $campos;
	}

	function to_pg_array($set) {
	    settype($set, 'array'); // can be called with a scalar or array
	    $result = array();
	    foreach ($set as $t) {
	        if (is_array($t)) {
	            $result[] = $this->to_pg_array($t);
	        } else {
	            $t = str_replace('"', '\\"', $t); // escape double quote
	            if (!is_numeric($t)) // quote only non-numeric values
	                $t = '"' . $t . '"';
	            $result[] = $t;
	        }
	    }
	    return '\'{' . implode(",", $result) . '}\''; // format
	}

	// el campo clave debe ser definido de forma explicita
	public function campoClave($id = NULL){
		if (is_null($id)){
			foreach($this->objs() as $v){
				if ($v->esCampoPrincipal())
					return $v->id();
			}
			return false;
		}

		if ($this->objs($id) === false)
			return $this;

		foreach($this->objs() as $v)
			$v->campoPrincipal(false);

		$this->objs($id)->campoPrincipal();

		return $this;
	}

	public function incluir(){//alias de guardar
		return $this->guardar();
	}

	// se mantiene la propiedad $campo_clave por compatibilidad con otras versiones del obj, en un futuro seró eliminado
	public function guardar(){ //Guardar todos los objetos seleccionados ()
		$this->ultimoIdInsertado = 0;

		$idCampoClave = $this->campoClave();
		$DBNombreCampoClave = false;
		if ($idCampoClave !== false)
			list($DBNombreCampoClave) = $this->dblista($idCampoClave);

		if ($idCampoClave !== false){
			$dbActivo = (bool) $this->objs($idCampoClave)->propiedad('dbActivo');
			$this->desactivar($idCampoClave);
		}

		$this->camposProcesoBD = $this->variablesDB();

		if ($idCampoClave !== false && $dbActivo){
			$this->objs($idCampoClave)->activar();
		}

		if (empty($this->camposProcesoBD)){
			$this->dbSalida = array('s' => 'n', 'msj' => 'Error de Validaci&oacute;n: No Se Puedo Guardar el Nuevo Registro.');

			if ($this->forzarSalida === true && $this->validacion === 1)
				$this->dbSalida();

			return $this->dbSalida;
		}


		foreach($this->camposProcesoBD as $v){
			$this->dbSalidaQuery = $this->db->guardar($this->tabla, $v, false, $DBNombreCampoClave);
			$this->ultimoIdInsertado = $this->db->uid;

			if ($idCampoClave !== false){
				$this->val($idCampoClave, $this->db->uid, true);
			}

			if ($this->dbSalidaQuery === false){
				$this->dbSalida = array('s' => 'n', 'msj' => 'Error: No Se Puedo Guardar el Nuevo Registro.');
				if ($this->debugdb === true)
            		$this->dbSalida['sql'] = $this->db->uq;

				if ($this->forzarSalida === true)
					$this->dbSalida();

				return $this->dbSalida;
			}

			foreach($this->forms() as $v){
				if ($v->actualizacion === 0)
					$v->actualizarValores();

				foreach($v->objs() as $objs){
					$relacion = $objs->propiedad('relacion');
					if (is_string($relacion) && $relacion !== ''){
						$v->val($objs->id(), $this->val($relacion), true);
					}
				}
				$v->actualizacionAutomatica(false);
			}
		}

		foreach($this->objs() as $v){
			if ($v->propiedad('dbActivo') === true && $v->propiedad('nombreDB') != '' && (!is_string($v->propiedad('relacion')) && !is_null($v->propiedad('relacion')))){
				$this->dbSalida = $this->relacionUnicaGuardar($v);
			}
		}


		$this->dbSalida = array('s' => 's', 'msj' => 'Registro Guardado Satisfactoriamente.');

        if ($this->debugdb === true)
            $this->dbSalida['sql'] = $this->db->uq;

		return $this->dbSalida;
	}

	public function actualizacionAutomatica($salida = true){
		if ($this->actualizacion === 0 && !$this->actualizarValores())
			return false;

		$idCampoClave = $this->campoClave();

		$DBNombreCampoClave = false;
		if ($idCampoClave === false){
			$this->dbSalida = array('s' => 'n', 'msj' => 'No se Cuenta con un Campo Principal Para Ejecutar la Acci&oacute;n.');

			if ($this->forzarSalida === true)
				$this->dbSalida();

			return false;
		}

		//$campoClave = $this->dblista($idCampoClave);

		$condicionGuardar = $idCampoClave . ";==;" . $this->formato($idCampoClave, $this->objs($idCampoClave)->propiedad('valorInicial'));
		$salidaMetodo = $this->actualizar(array($idCampoClave), $condicionGuardar);

		if ($salida === true){
			$this->dbSalida();
		}else{
			return $salidaMetodo;
		}
	}

	public function modificar($condicion = '', $condicionGuardar = false, $guardarNuevoRegistro = false){//alias de actualizar
		return $this->actualizar($condicion, $condicionGuardar, $guardarNuevoRegistro);
	}

	/**
	 * sintaxis: cada condicion se separa por ":", al inicio de cada condicion
	 * se evalua el primer caracter comparandolo con "&" siendo la condicion "and"
	 * de lo contrario se entiende como "or", cada condicion a su vez se divide en
	 * tres parametros, (campo;operador;campo) si no existe el campo se considera un
	 * valor estatico.
	 *
	 * ejemplo: "id;=;0:|id;=;'0'" => "id == 0 or id == '0'"
	 * la intension es que si uno de los elementos concuerda con algun objeto sera
	 * reemplazado por su valor
	 */

	public function actualizar($condicion = '', $condicionGuardar = false, $guardarNuevoRegistro = false){ //actualiza todos los objetos seleccionados ()
		$idCampoClave = $this->campoClave();
		$DBNombreCampoClave = false;
		if ($idCampoClave !== false)
			list($DBNombreCampoClave) = $this->dblista($idCampoClave);

		$this->camposProcesoBD = $this->variablesDB();
		$guardar = false;

		if ($this->camposProcesoBD === false){
			return true; // en caso de se llamado por actualizacionAutomatica genera el msj de error enviado por la validacion
		}

		foreach($this->camposProcesoBD as $ll => $v){
			$condicionSql = $this->construirCondicion($condicion, $ll);
			$condicionGuardarScript = $this->script($condicionGuardar, $ll);

			$af = 0;
			$guardar = false;

			if ($condicionGuardarScript === true){
				if (array_key_exists($DBNombreCampoClave, $v))
					unset($v[$DBNombreCampoClave]);

				$this->dbSalidaQuery = $this->db->guardar($this->tabla, $v, false, $DBNombreCampoClave);
				$guardar = true;
			}else{
				$af = $this->dbSalidaQuery = $this->db->actualizar($this->tabla, $v, false, $condicionSql);
			}

			if ($af === 0 && $guardarNuevoRegistro !== false && $condicionGuardarScript === false){
				$this->dbSalidaQuery = $this->db->guardar($this->tabla, $v, false, $DBNombreCampoClave);
				$guardar = true;
			}

			if ($guardar === true && $idCampoClave !== false){
				$this->ultimoIdInsertado = $this->db->uid;
				$this->val($idCampoClave, $this->db->uid, true);
			}

			foreach($this->objs() as $v){
				if ($v->propiedad('dbActivo') === true && $v->propiedad('nombreDB') != '' && (!is_string($v->propiedad('relacion')) && !is_null($v->propiedad('relacion')))){
					$this->dbSalidaQuery = $this->relacionUnicaGuardar($v);
				}
			}

			foreach($this->forms() as $form){
				if ($form->actualizacion === 0)
					$form->actualizarValores();

				foreach($form->objs() as $objs){
					$relacion = $objs->propiedad('relacion');

					if (is_string($relacion) && $relacion !== ''){
						$form->val($objs->id(), $this->val($relacion), true);
					}
				}
				$form->actualizacionAutomatica(false);
			}

			if ($this->dbSalidaQuery === false){
				$this->dbSalida = array('s' => 'n', 'msj' => 'Error: No Se Puedo Actualizar el Registro.');
				if ($this->debugdb === true)
            		$this->dbSalida['sql'] = $this->db->uq;

				if ($this->forzarSalida === true)
					$this->dbSalida();

				return $this->dbSalidaQuery;
			}
		}

		$msj = $guardar === true ? 'Registro Guardado Satisfactoriamente.' : 'Registro Actualizado Satisfactoriamente.';

		$this->dbSalida = array('s' => 's', 'msj' => $msj);

        if ($this->debugdb === true)
            $this->dbSalida['sql'] = $this->db->uq;

		return $this->dbSalidaQuery;
	}

	protected function script($condicionGuardar, $iteracion = 0){
		$condicionGuardarS = '';

		if($condicionGuardar !== false){
			$condicionGuardarM = explode(':', $condicionGuardar);

			foreach($condicionGuardarM as $cg){
				if (strpos($cg, ';') === false)
					continue;

				$cgM = explode(';', $cg);

				if (count($cgM) > 3)
					continue;

				$c = $cgM[0]{0};

				if ($c == '&' || $c == '|')
					$cgM[0] = substr($cgM[0],1);

				$cc = $c == '&' ? ' && ' : ($c == '|' ? ' || ' : '');

				$cgM[1] = trim($cgM[1]);

				if (strpos('===!=<=>=', $cgM[1]) !== false){
					if ($cgM[1] == '=')
						$cgM[1] = '=='; // intenta corregir abreviaturas
					elseif ($cgM[1] == '!')
						$cgM[1] = '!='; // intenta corregir abreviaturas
				}else
					$cgM[1] = '=='; // por defecto se define como '=='

				if ($this->objs($cgM[0]) !== false){
					$campo = $this->dblista($cgM[0]);
					$cgM[0] = $this->camposProcesoBD[$iteracion][$campo[0]];
					if (is_array($cgM[0])){
						$cgM[0] = $cgM[0][$iteracion];
					}
				}

				if ($this->objs($cgM[2]) !== false)
					$cgM[2] = $this->camposProcesoBD[$iteracion][$cgM[2]];

				$condicionGuardarS .= ' ' . $cc . ' (' . implode(' ', $cgM) . ')';
			}
			//echo $condicionGuardarS;
			$condicionGuardarS = trim($condicionGuardarS);
			$condicionGuardarS = trim($condicionGuardarS, 'OR ');
			$condicionGuardarS = trim($condicionGuardarS, 'AND ');
			$condicionGuardarS = 'return (' . $condicionGuardarS . ');';

			return $condicionGuardarS == '' ? false : eval($condicionGuardarS);
		}
	}

	public function eliminar($condicion = ''){ //actualiza todos los objetos seleccionados ()
		if ($this->actualizacion === 0 && !$this->actualizarValores())
			return false;

		if ($condicion === ''){
			$idCampoClave = $this->campoClave();
			$condicion = $idCampoClave === false ? '' : array($idCampoClave);
		}

		$condicion = $this->construirCondicion($condicion);

		$this->dbSalidaQuery = $this->db->eliminar($this->tabla, $condicion);
		if ($this->dbSalidaQuery === false)
			$this->dbSalida = array('s' => 'n', 'msj' => 'Error: No Elimino el Registro.');
		else
			$this->dbSalida = array('s' => 's', 'msj' => 'Registro Eliminado.');

        if ($this->debugdb === true)
            $this->dbSalida['sql'] = $this->db->uq;

        if ($this->forzarSalida === true && $this->dbSalidaQuery === false)
			$this->dbSalida();

		return $this->dbSalidaQuery;
	}

	protected function llenarObjs($row){
		$this->formatoDb($row);

		foreach($row as $ll => $v){
			$obj = $this->objs($ll);

			if ($obj !== false){
				if (!isset($obj->fuenteArray)){
					$obj->fuenteArray = array();
				}

				if (!isset($obj->valorArray)){
					$obj->valorArray = array();
				}

				$fuenteArray = &$obj->fuenteArray;
				$valorArray = &$obj->valorArray;
				unset($valores);
				$valores = false;

				if (!empty($valorArray)){
					$valores = &$valorArray;
				}elseif (!empty($fuenteArray)){
					if (is_object($fuenteArray) && strrpos(get_class($fuenteArray), 'ADORecordSet') !== false){
						$obj->fuenteArray = $this->rsAMatriz($fuenteArray);
					}
					$valores = &$fuenteArray;
				}

				if ($valores !== false){
					$v = isset($valores[$v]) ? $valores[$v] : $v;
				}

				$this->valor($ll, $v, true);
			}
		}
	}

	public function recorrerBusqueda($rs = false, $valorVista = false){
		if ($rs !== false)
			$this->rs = $rs;

		if ($this->rs === false){
			return false;
		}

		$this->actualizacion = 1;
		$row = $this->rs->FetchRow();

		if ($row === false) return false;

		$this->llenarObjs($row, $valorVista);

		return true;
	}

	protected function rsAMatriz(&$rs){
		$arr = array();
		$rs->MoveFirst();
		$ur = $rs->FetchRow();
		$rs->MoveFirst();

		if (count($ur) >= 2){
			$campos = array_keys($ur);

			$id = current($campos);
			next($campos);
			$campo = current($campos);
		}else{
			return array();
		}

		foreach($rs as $row)
			$arr[$row[$id]] = $row[$campo];

		return $arr;
	}

	public function buscarObjConRs(&$rs){
		$ur = $rs->fields;

		foreach($ur as $ll => $v){
			$objs[] = $ll;
		}

		$objs = implode('|', $objs);

		return $this->buscarObj(array('nombreCampo' => $objs), true);
	}

	protected function __matrizObj($m = false){
		if ($m === false){
			$m = $this->selector('*', false);
		}else{
			if (is_string($m))
				$m = array($m);
			elseif(!is_array($m))
				return $m;

			foreach ($m as $ll){
				if ($this->objs($ll) !== false){
					unset($m[$ll]);
				}
			}
		}

		return $m;
	}

	public function buscar($condicion = '', $campos = false, $ordenar = false){ //actualiza todos los objetos seleccionados ()
		if ($this->actualizacion === 0 && !$this->actualizarValores()){
			return false;
		}

		if ($condicion === ''){
			$idCampoClave = $this->campoClave();
			$condicion = $idCampoClave === false ? '' : array($idCampoClave);
		}

		$condicion = $this->construirCondicion($condicion);

		$campossql = array();
		$campos = $this->__matrizObj($campos);

		foreach ($campos as $ll){
			$v = $this->objs($ll);

			$relacion = $v->propiedad('relacion');
			if ($v->propiedad('dbActivo') === true && $v->propiedad('nombreDB') != '' && is_null($relacion)){
				list($DBNombre, $DBTipo) = $this->dblista($ll);
				$campossql[] = $DBNombre == $v->id() ? $DBNombre : array($DBNombre, $v->id());
			}
		}

		$campos = empty($campossql) ? '*' : $campossql;
		unset($campossql);

		$this->dbSalidaQuery = $this->db->seleccionar($this->tabla, $campos, $condicion, $ordenar);
		//$this->db->uq();
		$this->rs = $this->db->rs;

		$this->dbSalida = array('s' => 'n', 'msj' => 'No se Encontro el Registro.');
		if ($this->dbSalidaQuery > 0){
			$this->dbSalida = array('s' => 's', 'msj' => 'Registro Encontrado.');

			if ($this->dbSalidaQuery > 1 || $this->metodo() === 'JSON'){
				$salidaM = $this->formatoDb($this->db->rs->GetArray());
				$this->dbSalida = array_merge($this->dbSalida, array($this->idFormulario() => $salidaM));
			}else{
				$salidaM = $this->formatoDb($this->db->ur());
				$this->dbSalida = array_merge($this->dbSalida, $salidaM);
			}

			$this->db->rs->MoveFirst();

			foreach($this->dbSalida as $ll => $v){
				if ($ll === 's' || $ll === 'msj'){
					continue;
				}

				$this->valor($ll, $v);
			}

			foreach($this->forms() as $form){
				if ($form->actualizacion === 0)
					$form->actualizarValores();

				$relacionM = array();

				foreach($form->objs() as $objs){
					$relacion = $objs->propiedad('relacion');

					if (is_string($relacion) && $relacion !== ''){
						$relacionM[] = $objs->id();
						$form->val($objs->id(), $this->val($relacion), true);
					}
				}

				if (empty($relacionM))
					$relacionM = '';

				$form->forzarSalida(false)->buscar($relacionM);
				$this->dbSalida[$form->idFormulario()] = $form->dbSalida(false);
				$form->forzarSalida();
			}

			$objsValorDeFuente = $this->buscarObj(array('valorDeFuente' => true, 'campoMatrizDb' => false), true);
			$this->valorFuente($objsValorDeFuente);

			$objsValorDeFuente = $this->buscarObj(array('valorDeFuente' => true), true);
			foreach($objsValorDeFuente as $v){
				$this->dbSalida($v, $this->val($v));
			}

			$objsRelacion = array();
			foreach($this->buscarObj(array('!relacion' => ''), true) as $v){
				$r = $this->relacionUnicaBuscar($this->objs($v));
				if ($r !== false)
					$objsRelacion[$v] = $r;
			}

			//$this->dbSalida = array_merge($this->dbSalida, $this->formatoDb($objsRelacion));
			$this->dbSalida = array_merge($this->dbSalida, $objsRelacion);
		}

        if ($this->debugdb === true)
            $this->dbSalida['sql'] = $this->db->uq;

        if ($this->forzarSalida === true && $this->dbSalidaQuery == false)
			$this->dbSalida();

		return $this->dbSalidaQuery;
	}

	//fase de prueba
	protected function relacionUnicaGuardar($obj){
		if (!is_null($obj->relacion) && count($obj->relacion) < 3)
			return false;

		//tabla, nombre en el objform, nombre en base de datos
		list($tabla, $campoRelacion, $nombreCampoRelacion) = $obj->relacion;
		$objRelacion = $this->objs($campoRelacion);
		if ($objRelacion === false)
			return false;

		$dbvalor = $obj->dbvalor;
		list($nombreCampo) = $this->dblista($obj->id());
		$condicion = $nombreCampoRelacion . ' = ' . $objRelacion->dbvalor;

		$actipublic = false;
		$this->db->seleccionar($tabla, $nombreCampo, $condicion);
		$registros = array();

		if ($this->db->af == count($dbvalor)){
			foreach($this->db->rs as $v){
				$registros[] = $v[$nombreCampo];
			}

			foreach($registros as $v){
				if (array_search($v, $dbvalor) === false){
					$actipublic == true;
					break;
				}
			}
		}

		if ($actipublic == false && !empty($registros))
			return $this->dbSalidaQuery;

		$this->db->eliminar($tabla, $condicion);
		$elementos = array($nombreCampoRelacion => $objRelacion->dbvalor);

		foreach($dbvalor as $v){
			$elementos[$nombreCampo] = $v;
			$this->dbSalidaQuery = $this->db->guardar($tabla, $elementos);

			if ($this->dbSalidaQuery === false){
				$this->dbSalida = array('s' => 'n', 'msj' => 'Error: No Se Puedo Guardar el Nuevo Registro.');
				if ($this->debugdb === true)
            		$this->dbSalida['sql'] = $this->db->uq;

				if ($this->forzarSalida === true)
					$this->dbSalida();

				return $this->dbSalidaQuery;
			}
		}

		return $this->dbSalidaQuery;
	}

	protected function relacionUnicaBuscar($obj){
		if (!is_null($obj->relacion) && count($obj->relacion) < 3)
			return false;

		//tabla, nombre en el objform, nombre en la tabla relacionada ($tabla)
		list($tabla, $campoRelacion, $nombreCampoRelacion) = $obj->relacion;
		$objRelacion = $this->get($campoRelacion);
		if ($objRelacion === false)
			return false;

		$dbvalor = $obj->dbvalor;
		list($nombreCampo) = $this->dblista($obj->id());

		$condicion = $nombreCampoRelacion . ' = ' . $objRelacion->dbvalor;

		$campoSql = $nombreCampo;
		/*if ($nombreCampo != $obj->id()){
			$campoSql = array($nombreCampo, $obj->id);
		}*/

		$this->db->seleccionar($tabla, $campoSql, $condicion);

		$elementos = array();
		foreach($this->db->rs as $v){
			$elementos[] = $v[$nombreCampo];
		}

		return $elementos;
	}

	protected function buscarRelacion(){

	}

	protected function buscarRelacionSig(){

	}

	protected function buscarRelacionAnt(){

	}

	/**
	 * Intenta dar salida o "asignar" valores a los resultados de una consulta del objeto.
	 * Por defecto imprime la salida en json y termina con la ejecución del script.
	 */
	public function dbSalida($p = true, $v = false){
		//echo $this->idFormulario() . "\n";
		//var_dump(debug_backtrace());

		if ($p === true){ // Si $p == true forza la salida de base de datos del objeto en json, culminando la ejecución del script, valor por defecto.
			exit (json_encode($this->dbSalida));
		}elseif ($p === false){ // De lo contrario siendo $p == false retorna la salida de base de datos del objeto en json.
			return $this->dbSalida;
		}elseif (is_array($p)){ // Siendo $p un array asigna a la salida de la base de datos dicha matriz, ademas, si el segundo parametro es verdadero da salida de la variable.
			$this->dbSalida = $p;
			if ($v === true)
				exit (json_encode($this->dbSalida));
		}elseif (isset($this->dbSalida[$p])){
			if ($v !== false){
				$this->dbSalida[$p] = $v;
				return $this;
			}

			return $this->dbSalida[$p];
		}elseif ($v !== false){
			$this->dbSalida[$p] = $v;
		}else{
			return '';
		}
		return $this;
	}

	/**
	 * Intenta construir una condición simple dependiendo de $condicion.
	 * Si $condicion es una matriz intenta construir una condición con los parómetros pasados en ella,
	 * si los elementos existen construye una igualdad por cada elemento valido.
	 */

	public function construirCondicion($condicion, $correlativo = false){
		$condicionOriginal = $condicion;
		if (is_string($condicion))
			$condicion = $this->selector($condicion);

		if (!is_array($condicion)){
			return $condicion;
		}

		if (empty($condicion)){
			return $condicionOriginal;
		}

		$salidaCondicion = array();
		foreach($condicion as $v){
			if ($this->objs($v) === false){
				if (is_string($v))
					$salidaCondicion[] = $v;

				continue;
			}

			list($DBNombre, $DBTipo) = $this->dblista($v);
			$valor = $correlativo === false ? $this->valdb($v) : $this->camposProcesoBD[$correlativo][$DBNombre];

			if (is_array($valor) && array_key_exists($correlativo, $valor))
				$valor = $valor[$correlativo];

			$salidaCondicion[] = $DBNombre . ' = ' . $valor;
		}

		return (count($salidaCondicion) > 0) ? implode(' AND ', $salidaCondicion) : '';
	}

	public function hacerCondicion($id = false, $retornarMatriz = false){
		if ($this->actualizacion === 0 && !$this->actualizarValores()){
			return false;
		}

		$id = $this->selector($id);
        $condiciones = array();

		foreach($id as $obj){
			list($DBNombre, $DBTipo) = $this->dblista($obj->id());
			$valor = $obj->valor;

			if ($valor === '' || $valor === 0 || $valor == $obj->valorInicial)
				continue;

			if (isset($obj->fecha)){
				if (is_array($valor)){
					$fecha = array($this->tratarFechas($valor[0], $obj->formatoDB), $this->tratarFechas($valor[1], $obj->formatoDB));
					$condicion = $DBNombre . ' BETWEEN \'' . $fecha[0] . '\' AND \'' . $fecha[1] . '\'';
				}else{
					$fecha = $this->tratarFechas($valor, $obj->formatoDB);
					$condicion = $DBNombre . ' = \'' . $fecha . '\'';
				}
			}elseif (array_search($DBTipo, array('bool', 'int', 'cedula', 'porcentaje', 'decimal', 'currency')) === false){
				$condicion = "lower($DBNombre) like '%" . strtolower($valor) . "%'";
			}elseif (is_array($valor)){
				if ($valor[0] != $valor[1])
					$condicion = "($DBNombre >= " . $valor[0] . " AND $DBNombre < " . $valor[1] . ")";
				elseif ($valor[0] === '' || $valor[0] === 0 || $valor[0] == $obj->valorInicial)
					continue;

				$condicion = $DBNombre . ' = ' . $valor[0];
			}else{
				$condicion = $DBNombre . ' = ' . $valor;
			}

			$condiciones[] = $condicion;
		}

		return $retornarMatriz ? $condiciones : implode(' AND ', $condiciones);
	}

	protected function tratarFechas($fecha, $formato = false){
		if (!is_string($formato) && $formato !== false)
			return false;

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

	protected function guardar_archivo($nombre_campo_file, $ruta, $extensiones = array('*'), $tamanno_maximo = 10, $var_nom_org = false){
		if (!isset($_FILES[$nombre_campo_file]))
			return '';

		$nombre_archivo  	= elimina_acentos(htmlspecialchars($_FILES[$nombre_campo_file]['name']));
		$tamanno_archivo 	= $_FILES[$nombre_campo_file]['size'];
		$tiemporal_archivo 	= $_FILES[$nombre_campo_file]['tmp_name'];
		$error_archivo  	= $_FILES[$nombre_campo_file]['error'];

		$extension_archivo = end(explode('.', $nombre_archivo));

		foreach($extensiones as $ll => $v){
			$extensiones[$ll] = trim($extensiones[$ll]);
		}

		if(!empty($nombre_archivo)){
			if (!is_dir($ruta)){
				//$this->sLog('Error: el Directorio no Existe');
				if (!mkdir($ruta, 0777)){
					$this->sLog('Error: No se Pudo Crear el Directorio.');
					return false;
				}
			}

			if($tamanno_archivo > ($tamanno_maximo * 1048576)) {
				$this->sLog('El Tama&ntilde;o del Archivo es Demasiado Grande');
				return false;
			}

			$extension_archivo = strtolower($extension_archivo);

			if(array_search($extension_archivo, $extensiones) === false && array_search('*', $extensiones) === false){
				$this->sLog('El Archivo no es ' . implode(', ', $extensiones));
				return false;
			}

			$error_count = count($error_archivo);

			if($error_count > 1) {
				$this->sLog('Error al subir el archivo:');
				for($i = 0; $i <= $error_count; ++$i){
					$this->sLog($_FILES[$nombre_campo_file]['error'][$i] . '<br />');
				}
				return false;
			}else{
				if ($var_nom_org === false){
					$pos = strrpos($nombre_archivo, ".");
					$pos = $pos === false ? "" : substr($nombre_archivo, $pos + 1);

					do{
						$nombre_archivo_final = md5(uniqid(rand(), true)) . ($pos === "" ? "" : "." . $pos);
			        }while (is_file($ruta . $nombre_archivo_final));
				}else
					$nombre_archivo_final = $nombre_archivo;


				return move_uploaded_file($tiemporal_archivo, $ruta . $nombre_archivo_final) ? $nombre_archivo_final : false;
			}
		}

		return false;
	}

	public function enctype(){ //obtener el enctype del formulario.
		foreach($this->objs() as $v)
			if ($v->propiedad('file') !== false)
				return $this->objTiposChk['objarchivo'] ? 'multipart/form-data' : 'application/x-www-form-urlencoded';

		return 'application/x-www-form-urlencoded';
	}

	public function encoding(){ //obtener el enctype del formulario.
		foreach($this->objs() as $v)
			if ($v->propiedad('file') !== false)
				return $this->objTiposChk['objarchivo'] ? 'multipart/form-data' : '';

		return '';
	}

	protected function encriptar($c = ''){
		return $this->ci->encriptar($c);
	}

	public function sArchivos(){ //obtener javascripts (salida del javascripts)
		foreach($this->objs() as $v){
			$css = $v->archCss();
			if (!empty($css)){
				$this->ci->css($css);
			}

			$js = $v->archJs();
			if (!empty($js)){
				$this->ci->js($js);
			}
		}

		return $this;
	}

	public function sJQuery($id = '*', $retornar = false){ //obtener jQuery (salida del jQuery)
		$selectores = array();
		$id = trim($id);

		$select = $this->selector(($id !== '' ? $id . ", " : '') . "!barra, !|");

		foreach ($select as $obj){ // repasar todos los archivos
			if (strpos($obj->sin, 'jquery') !== false){
				continue;
			}

			$jsOpciones = $obj->jsOpciones();
			$plantilla = $obj->jQuery;
			$plantillaDestruir = $obj->jQueryDestruir;

			if (method_exists($obj, 'jQuery')){
				$plantilla = $obj->jQuery();
			}

			if (is_array($obj->js)){
				$js = $obj->js[0];
				$jsOpciones = $obj->js[1];
			}else
				$js = trim($obj->js) == '' ? 'id' : trim($obj->js);

			$select = $js;
			switch ($js){
				case 'id':
					$select = '#' . trim($obj->id);
					break;
				case 'clase':
					$select = '.' . trim($obj->clase);
					break;
			}

			$i=0;
			$contenedor = ($this->usarContexto === true && $obj->contenerorJquery === true) ? '#' . $this->id() : (is_string($this->usarContexto) ? trim($this->usarContexto) : '');

			if (array_key_exists($obj->tipo, $selectores)){
				if ($jsOpciones != ''){
					foreach($selectores[$obj->tipo] as $ll => $v){
						if ($v['opciones'] == $jsOpciones && $contenedor == $v['contenedor']){
							$i++;
							break;
						}
					}
				}
			}

			if ($i == 0){
				$selectores[$obj->tipo][] = array(
					'selector' 		=> array($select),
					'opciones' 		=> $jsOpciones,
					'plantilla' 	=> $plantilla,
					'plantillaDestruir' => $plantillaDestruir,
					'contenedor'	=> $contenedor
				);
			}else{
				$selectores[$obj->tipo][$ll]['selector'][] = $select;
			}
		}

		foreach($selectores as $ll => $v){
			foreach($v as $ll2 => $v2){
				$ele = &$selectores[$ll][$ll2];
				$ele['selectorOrigen'] = $ele['selector'];

				$contenedor = $ele['contenedor'] === '' ? '' : '", "' . $ele['contenedor'];
				$ele['selector'] = implode(', ', array_unique($ele['selector'])) . $contenedor;
			}
		}

		if ($retornar === true)
			return $selectores;

        echo "\n";
		foreach ($selectores as $valor){
			foreach ($valor as $v){
				if ($v['plantilla'] == '')
					continue;
					//({});
				$v['plantilla'] = str_replace('({})', '()', $v['plantilla']);
				echo "\t\t" . $this->ci->tmpl($v['plantilla'], $v) . "\n\n";
			}
		}

		foreach($this->forms() as $form){
			$form->sJQuery($id, $retornar);
		}

		return $this;
	}

	public function sJQueryDestruir($id = '*', $retornar = false){
		$selectores = $this->sJQuery($id, true);
		$elementos = array(); //$selector["plantillaDestruir"]
		foreach($selectores as $selector){
			foreach ($selector as $s){
				$s['selector'] = implode(", ", $s['selectorOrigen']);
				if ($s['plantillaDestruir'] != ''){
					echo "\t\t" . $this->ci->tmpl($s['plantillaDestruir'], $s) . "\n\n";
				}

				$elementos[$s['contenedor']][] = $s['selector'];
			}
		}

		foreach($elementos as $contenedor => $elemento){
			echo "\n\t\t$(\"" . implode(", ", $elemento) . "\"" . ($contenedor == '' ? '' : ', "' . $contenedor . "\"") . ").unbind().remove();";
		}

		foreach($this->forms() as $form){
			$form->sJQueryDestruir($id, $retornar);
		}
	}

	function existeTabla($tabla){
		if (is_null($this->db)){
			return true;
		}

		if ($this->db->driver === 'postgres'){
			$sql = "select count(*) as c from pg_tables where schemaname='" . $this->db->schema . "' and tablename='" . $tabla . "';";
		}elseif ($this->db->driver === 'mysql'){
			$sql = "SELECT COUNT(*) AS c FROM information_schema.tables WHERE table_schema = '" . $this->db->db->database . "' AND table_name = '" . $tabla . "'";
		}else{
			return true;
		}

		$this->db->query($sql);
		return intval($this->db->ur('c')) > 0;
	}

	public function crearTablarDB($ejecutar = false, $camposIndex = 'id'){
		$dict = NewDataDictionary($this->db()->db);

		$tiposCampos = array(
			'str' => array('C', '50', 'NOTNULL'),
			'int' => array('I', 11, 'NOTNULL'),
			'date' => array('D', 'DEFDATE', 'NOTNULL'),
			'porcentaje' => array('N', '3.2', 'NOTNULL'),
			'currency' => array('N', '12.2', 'NOTNULL'),
			'decimal' => array('N', '12.2', 'NOTNULL'),
			'encry' => array('C', '50', 'NOTNULL'),
			'bool' => array('I', 11, 'NOTNULL'),
			'file' => array('C', '50', 'NOTNULL'),
			'' => array('C', '50', 'NOTNULL')
		);

		$campos = array();
		foreach($this->objs() as $v){
			if($v->dbActivo !== true || $v->nombreDB == '')
				continue;

			list($DBNombre, $DBTipo) = $this->dblista($v->id);

			$propiedades = $tiposCampos[$DBTipo];

			if (isset($v->max))
				if ($v->max > 0)
					$propiedades[1] = $v->max;

			if ($v->tipo === 'objmceTexto' || $v->tipo === 'objeditor')
				$propiedades[0] = 'X';

			if ($v->id() === 'id'){
				$propiedades[3] = 'AUTO';
				$propiedades[4] = 'KEY';
			}

			$campos[$v->id()] = array_merge(array($DBNombre), $propiedades);
		}

  		$sqlarray = $dict->CreateTableSQL($this->tabla, $campos);

		if ($ejecutar === true){
			$dict->ExecuteSQLArray($sqlarray);
		}

		$sqlarrayIndex = $dict->CreateIndexSQL($campos, $this->tabla, $camposIndex);
		$sqlarray = array_merge($sqlarray, $sqlarrayIndex);

		//print_r($sqlarray);
		if ($ejecutar === true){
			$dict->ExecuteSQLArray($sqlarray);
		}

		return $sqlarray;
	}

	static function generarFormulario($tabla, $nombre = null, $baseDatos = null, $retornar = false){
		global $db;

		$baseDatos = is_null($baseDatos) ? $db->db : (isset($baseDatos->db) ? $baseDatos->db : $baseDatos);

		$dict = NewDataDictionary($baseDatos);

		if (!in_array($tabla, $dict->MetaTables()))
			return array();

		if (is_null($nombre))
			$nombre = $tabla;

		$tipos = array(
			"C" => "str",
			"X" => "str",
			"D" => "date",
			"T" => "date",
			"L" => "bool",
			"I" => "int",
			"I1" => "int",
			"I2" => "int",
			"I4" => "int",
			"I8" => "int",
			"R" => "int",
			"F" => "decimal",
			"N" => "decimal",
		);

		$tiposdecampos = array(
			"str" => "texto",
			"date" => "fecha",
			"bool" => "spinner",
			"int" => "spinner",
			"decimal" => "spinner"
		);

		$primaryKeys = $dict->MetaPrimaryKeys($tabla);
		$primaryKeys = $primaryKeys === false ? array() : $primaryKeys;
		//$MetaIndexes = $dict->MetaIndexes($tabla);

		$meta = $dict->MetaColumns($tabla);
		$formulario = array();
		$validar = array();
		foreach($meta as $campos){
			$tipo = $dict->MetaType($campos->type);
			$arr = array(
				"id" => $campos->name,
				"tipo" => $tiposdecampos[$tipos[$tipo]],
				"texto" => ucfirst($campos->name) . ": ",
				"max" => $campos->max_length,
				"nombreDB" => $campos->name . ($tipos[$tipo] === "str" ? "" : ":" . $tipos[$tipo]),
			);

			if ($campos->not_null){
				$validar[] = $campos->name;
			}

			if ($arr["tipo"] == "oculto"){
				unset($arr["texto"]);
			}

			if (isset($campos->default_value)){
				$arr["valor"] = $campos->default_value;
			}

			if (in_array($tipos[$tipo], array("int", "bool", "date"))){
				unset($arr["max"]);
			}

			if ($arr["id"] == "id"){
				unset($arr["valor"]);
				$arr["campoPrincipal"] = true;
			}

			if (in_array($arr["id"], $primaryKeys)){
				unset($arr["tipo"], $arr["max"], $arr["validar"], $arr["texto"]);
				$arr["campoPrincipal"] = true;
			}

			$formulario[] = $arr;
		}

		$formulario[] = array(
	        "id" => "tabla",
	        "tipo" => "tabla",
	        "campos" => implode(", ", $validar)
	    );
		$formulario[] = array("id" => "dialogBuscar", "tipo" => "dialog");

		if ($retornar === true){
			return array($formulario, $validar);
		}else{
			echo "<pre>";
			echo "\$" . strtolower($nombre) . " = new form(" . form::imprimirMatriz($formulario, true) . ");";
			echo "\n\n";
			echo "\$" . strtolower($nombre) . "
	->id('$nombre')->titulo('$nombre')->url(\$url)->db(\$db)->tabla('$tabla')
	->validar(" . (count($validar) == (count($formulario) - 2) ? "" : "'" . implode(", ", $validar) . "'") . ")
	->cambiarPropiedad('" . implode(", ", $validar) . "', array('ancho' => 287))
	->val('dialogBuscar', \$" . strtolower($nombre) . "->hacer('tabla', true));";
			//var_export($formulario);
			echo "</pre>";
			exit;
		}
	}

	static public function imprimirMatriz($arr, $retornar = false, $iteracion = 0){
		$iniciaCero = false;
		$salida = "array(\n";
		foreach($arr as $ll => $v){
			$salida .= str_repeat("\t", $iteracion + 1);

			if (is_array($v)){
				$salida .= form::imprimirMatriz($v, $retornar, $iteracion+1) . ",\n";
				continue;
			}elseif (is_bool($v)){
				$v = $v ? "true" : "false";
			}elseif (!is_numeric($v) && substr($v, 0, 1) != '$'){
				$v = "'$v'";
			}

			if ($ll === 0){
				$iniciaCero = true;
				$salida .= "$v,";
			}elseif($iniciaCero === true && is_int($ll)){
				$salida .= "$v,";
			}else{
				$salida .= "'$ll' => $v,";
			}
			$salida .= "\n";
		}

		$salida = substr($salida, 0, -2) . "\n" . str_repeat("\t", $iteracion) . ")";
		if ($iteracion == 0 && $retornar === false)
			echo $salida;
		else
			return $salida;
	}

	public function __clone(){
		foreach($this->objs() as $v){
			$this->objs[$v->id()] = clone $v;
		}
	}

	public function __toString(){
		return $this->formulario();
	}
}

class objs{
	protected $esObj				= true;
	public $id 						= '';
	public $tipo 					= '';
	public $nombre 					= '';
	public $clase 					= '';

	public $ancho 					= '';
	public $alto 					= '';
	public $estilo 					= '';

	public $valor 					= '';
	public $texto 					= '';
	public $js 						= '';
	public $css 					= '';
	public $titulo 					= '';
	public $type 					= '';
	public $html 					= '';
	public $placeholder 			= '';

	public $limpiarXss 				= true;

	public $tabla 					= false;
	public $grid 					= false;
	public $valorDeFuente 			= false;

	public $borde					= '';
    public $usarContexto 			= false;
	public $validar					= false;
	public $cambioValidacion		= true;

	public $campoPrincipal 			= false;
	public $campoRelacion	 		= false;

	public $usaContenedor 			= true;
	public $contenedor 				= false;
	public $contenedorObjs 			= 'div';
	public $contenedorObjsClase 	= 'col-lg-4';

    public $bloqueado 				= false;

	public $nombreDB 				= '';
	public $nombreCampo				= '';
	public $tipoCampo				= '';
	public $campoMatrizDb			= false;

	public $dbvalor					= '';
	public $dbActivo 				= false;
	public $actualizable 			= true;
	public $relacion 				= NULL;

	public $enviado					= false;

	public $cambiarTamano			= true;

	public $usaUI 					= true;
	public $claseUi					= 'form-control';

    public $plantilla 				= '<input id="{id}" name="{nombre}" class="{clase}" style="{estilo}" value="{valor}" type="hidden" {html} />';
    public $jQuery 					= '';
	public $jQueryDestruir			= '';

    public $fuente 					= '';
    public $valorFuente 			= '';
    public $valorInicial 			= false;
	public $fuenteArray 			= false;

    public $archCss 				= array();
    public $archJs 					= array();

    public $sin 					= '';
    public $sinEtiqueta				= false;
    public $contenerorJquery 		= true;

    public $ci;

    public function __construct($propiedades){
    	if (!array_key_exists('id', $propiedades))
    		$propiedades['id'] = '';

    	$this->nombre = $this->id = trim($propiedades['id']);
		$this->tipo = get_class($this);

		if (is_array($propiedades)){
			if (!array_key_exists('contenedor', $propiedades)){
				$propiedades['contenedor'] = new objdiv(array(
					'id' => 'obj',
					'clase' => 'obj ',
					'dbActivo' => true,
					'contenedorObjs' => '',
					'contenedor' => '',
					'usaUI' => false,
					'sin' => 'id'
				));
				$propiedades['contenedor']->activar();
			}else{
				if (is_object($propiedades['contenedor']) && isset($this->contenedor->esObj)){
					$this->contenedor = $propiedades['contenedor'];
				}else
					unset($propiedades['contenedor']);
			}

			if (array_key_exists('plantilla', $propiedades))
				unset($propiedades['plantilla']);

			if (array_key_exists('id', $propiedades))
				unset($propiedades['id']);

			if (array_key_exists('tipo', $propiedades))
				unset($propiedades['tipo']);

			foreach($propiedades as $ll => $v){
				if (method_exists($this, $ll)){
					$this->$ll($v);
				}elseif (property_exists($this, $ll)){
					$this->$ll = $v;
				}
			}
		}

		if (isset($_POST[$this->id]) || isset($_GET[$this->id])){
			$this->enviado = true;
		}

		if ($this->valorInicial === false){
			$this->valorInicial = $this->valor;
		}

		if ($this->nombreDB !== '' && !array_key_exists('dbActivo', $propiedades)){
			$this->dbActivo = true;
		}

		$this->ci = &get_instance();
    }

    public function id(){
    	return $this->id;
    }

    public function archCss(){
    	return count($this->archCss) !== 1 ? $this->archCss : $this->archCss[0];
    }

    public function archJs(){
    	return count($this->archJs) !== 1 ? $this->archJs : $this->archJs[0];
    }

    public function activar(){
    	$this->dbActivo = true;
    }

	public function desactivar(){
    	$this->dbActivo = false;
    }

    public function campoPrincipal($esCampoPrincipal = true){
    	$this->campoPrincipal = (boolean) $esCampoPrincipal;
    }

    public function esCampoPrincipal(){
    	return (boolean) $this->campoPrincipal;
    }

	public function jsOpciones(){
    	return '{}';
    }

    public function valor($valor = false, $usadb = false){
    	if ($valor === false)
    		return $this->valor;

    	$this->valor = $valor;
    	if ($usadb === true)
    		$this->dbvalor($valor);

    	return $this;
    }

    public function dbvalor($valor = false){
    	if ($valor === false)
    		return $this->dbvalor;

		$this->dbvalor = $valor;

    	return $this;
    }

    public function nombreDB($v = false){
    	if ($v === false){
    		return $this->nombreDB;
    	}

    	$this->nombreDB = $v;
    	if ($this->nombreDB === ''){
			$this->dbActivo = false;
		}else{
			if (strpos($this->nombreDB, ':') === false){
				$this->nombreCampo = $this->nombreDB;
				$this->tipoCampo = 'str';
			}else{
				list($this->nombreCampo, $this->tipoCampo) = explode(':', $this->nombreDB);
			}

			if (substr($this->nombreCampo, -2) === '[]'){
				$this->nombreCampo = str_replace('[]', '', $this->nombreCampo);
				$this->nombreDB = $this->nombreCampo . ':' . $this->tipoCampo;
				$this->campoMatrizDb = true;
			}
		}

		return $this;
    }

    public function jQuery(){
    	return $this->jQuery;
    }

	public function hacer($salidabool = false, $saliaTexto = true, $parametros = false){
		global $url;
		$style = '';

		if ($this->cambiarTamano === true){
			$style =
			(($this->ancho == '')  ? '' : ('width: '  . (is_int($this->ancho) ? ($this->ancho . 'px') : $this->ancho) . '; ')) .
			(($this->alto  == '')  ? '' : ('height: ' . (is_int($this->alto)  ? ($this->alto . 'px')  : $this->alto)  . '; ')) .
			(($this->estilo == '') ? '' : $this->estilo);
		}

		$clase = $this->clase;
		if (strpos($this->sin, 'validar') === false){
			if ($this->validar === true){
				$clase = 'required ' . $clase;
			}elseif (is_string($this->validar)){
				$clase = 'required ' . $this->validar . ' ' . $clase;
			}
		}

		$clase = $this->usaUI ? $clase . ' ' . $this->claseUi . ' ' . ($this->borde == false ? '' : $this->borde) : $clase;

		$array = array(
			'id' 		=> trim($this->id),
			'nombre' 	=> trim($this->nombre),
			'clase' 	=> trim($clase),
			'estilo' 	=> trim($style),
			'valor' 	=> trim($this->valor),
			'placeholder' => htmlentities(trim($this->placeholder)),
			'type' 		=> trim($this->type),
			'titulo' 	=> trim($this->titulo),
			'texto' 	=> trim($this->texto),
			'html' 		=> trim($this->html),
			'url' 		=> trim($url)
		);

		if (is_array($parametros))
			$array = array_merge($array, $parametros);

		if ($this->sin != ''){
			$sinProp = explode(',', $this->sin);

			foreach($sinProp as $v){
				$v = trim($v);
				if (array_key_exists($v, $array))
					$array[$v] = '';
			}
		}

		$salidaObj = $this->ci->tmpl($this->plantilla, $array);
		$salida = ($saliaTexto == true ? $this->getNombre($saliaTexto) : '') .(
			$this->contenedorObjs != '' ?
			"\n<".$this->contenedorObjs." class=\"contenedorObj\">\n" . $salidaObj . "\n</".$this->contenedorObjs.">" :
			"\n" . $salidaObj . "\n"
		);

		if ($this->contenedorObjsClase != ''){
			$salida = "\n<div class=\"".$this->contenedorObjsClase."\">\n" . $salida . "\n</div>";
		}

		$salida = preg_replace('/([a-z]+)=""/i', '', $salida); // elimina propiedades vacias
		$salida = preg_replace('/\s{2,}/', ' ', $salida); // elimina espacios en blancos repetidos

		if (is_object($this->contenedor) && isset($this->contenedor->esObj) && $this->usaContenedor !== false){
			if ($this->ancho > 0 && $this->ancho != '' && $this->contenedor->propiedad('ancho') === '' && $this->contenedor->propiedad('ancho') !== false){
				$this->contenedor->cambiarPropiedad(array('ancho' => $this->ancho));
			}

			$this->contenedor->valor = "\n" . $salida . "\n";
			$salida = $this->contenedor->hacer(true, false);
		}

		if ($salidabool == true)
			return $salida;
		else
			echo $salida;

		return true;
	}

	public function propiedad($propiedad, $valor = null){
		if (!is_string($propiedad))
			return false;

		if ($valor !== null)
			return $this->$propiedad = $valor;


		return property_exists($this, $propiedad) ? $this->$propiedad : false;
	}

	public function cambiarPropiedad($propiedad, $valor = ''){
        if (!is_array($propiedad))
			$propiedad = array($propiedad => $valor);

		foreach ($propiedad as $llp => $vp){
			//if ($this->dbActivo != true) continue;

			$concatenar = false;
			if (is_string($vp) && substr($vp,0,1) == '+'){
                $vp = substr($vp,1);
                $concatenar = true;
            }

			if ($llp === 'contenedor'){
				if (is_object($vp)){
					if (isset($vp->esObj) && $this->usaContenedor !== false){
						$propiedadesObj = get_object_vars($vp);
						$id = $vp->id;

						$tipo = $vp->tipo;
						if (strpos($tipo, 'obj') === false)
       						$tipo = 'obj' . $vp->tipo;

						$this->contenedor = new $tipo($propiedadesObj, $id, $tipo);
						$this->contenedor->dbActivo = true;
						continue;
					}
				}else{
					$this->contenedor = false;
					continue;
				}
			}

			if ($llp == 'tipo' || ($this->cambioValidacion === false && $llp == 'validar'))
				continue;

			if ($concatenar && strpos($this->$llp, $vp) === false){
				$this->$llp .= ' ' . $vp;
			}else{
				$this->$llp = $vp;
			}
		}

		return $this;
	}

	public function getNombre($salida = true){ //Salida del nombre del objeto
		$texto = trim($this->texto);
		$uc = substr($texto, -1);
		if ($this->sinEtiqueta === true || $uc === '@' || $texto === '') return '';
		if ($uc !== ':'){
			$texto .= ': ';
		}
		return $salida ? "\n<label for=\"".$this->id."\" class=\"labelObj\">\n" . htmlentities($texto) . "\n</label>" : $this->texto;
	}

	public function __toString(){
		return $this->valor();
	}

	public function __destruct(){
		$this->fuente = null;
		$this->valorFuente = null;
		$this->contenedor = null;
	}
}
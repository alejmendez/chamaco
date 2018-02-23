<?php
class base_datos{
	protected $conectado = false;
	
	public $esObjDB = true;
	public $db;
	public $driver;

	public $auto_historial = false;
	public $uq = '';
	public $ur;

	public $rs = false;
    public $af = 0;
    public $uid = 0;

	public $resultado = false;
	public $afectados = 0;
	public $ultimo_id_insertado = 0;

	public $tbPrefijo = '';

    public $usuario;
    public $conceptos = array('seleccionar', 'guardar', 'actualizar', 'eliminar', 'query', 'autenticacion');
    public $tblhistorico = 'historico';

    public $usaUTF8 = false;
    public $eof = true;
	public $schema = 'public';
	
	protected $camposUsaComillas = false;
	protected $magic_quotes_gpc = false;
	
	protected $ci;
	protected $entorno = 'production'; //production, testing, development
	/**
	 * base_datos::base_datos()
	 *
	 * metodo de conexion de la base de datos, prepara el objeto de adodb para su uso
	 *
	 * @param mixed $driver
	 * @param mixed $host
	 * @param mixed $user
	 * @param mixed $pass
	 * @param mixed $database
	 * @return
	 */
	function __construct($driver, $host, $user = '', $pass = '', $database = '', $pconnect = false){
		if (defined('ENVIRONMENT')){
    		$this->entorno = ENVIRONMENT;
    	}
    	
		if (function_exists("get_magic_quotes_gpc")){
			$this->magic_quotes_gpc = get_magic_quotes_gpc();
		}
		
		if (function_exists('get_instance')){
			$this->ci = &get_instance();
		}
		
		try{
			if (preg_match("/\.(mdb|accdb)$/i", $driver)){
				$pconnect = $host;
				$host = $driver;
				$driver = 'access';
			}elseif(preg_match("/\.(db)$/i", $driver)) {
				$pconnect = $host;
				$database = $host = $driver;
				$driver = 'sqlite3';
			}
			
			$this->driver = strtolower($driver);
			
			if ($driver == 'postgres'){
				$this->camposUsaComillas(true);
			}

			$this->db = ADONewConnection($this->driver);
			$tipoConexion = $pconnect === true ? 'PConnect' : 'NConnect';
			//$this->db->debug = true;
			
			if ($driver == 'oci8' || $driver == 'oci805'){
				putenv('NLS_LANG=SPANISH_SPAIN.WE8MSWIN1252');
				//putenv('NLS_LANG=AMERICAN_AMERICA.WE8ISO8859P1');
				//putenv('NLS_LANG=AMERICAN_AMERICA.WE8ISO8859P9');
				//putenv("NLS_LANG=AMERICAN_AMERICA.UTF8");
			}
			
			if ($user === '' && $pass === '' && $database === ''){
				$dsn = $host;

				if ($driver === 'access' && preg_match("/\.(mdb|accdb)$/i", $dsn)){
					$result = @$this->db->{$tipoConexion}("Driver={Microsoft Access Driver (*.mdb)};Dbq=$dsn;Uid=Admin;Pwd=;");
					if (!$result){
						$result = $this->db->{$tipoConexion}("Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$dsn;Uid=Admin;Pwd=;");
					}
				}else{
					$result = @$this->db->{$tipoConexion}($dsn);
				}
			}else{
				if ($driver === 'access' && preg_match("/\.(mdb|accdb)$/i", $dsn)){
					$result = @$this->db->{$tipoConexion}("Driver={Microsoft Access Driver (*.mdb)};Dbq=$dsn;Uid=$user;Pwd=$pass;");
					if (!$result){
						$result = $this->db->{$tipoConexion}("Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$dsn;Uid=$user;Pwd=$pass;");
					}
				}else{
					$result = $this->db->{$tipoConexion}($host, $user, $pass, $database);
				}
			}

            if ($result !== true){
            	if ($driver === 'ldap'){
            		return false;
           		}
           		
                exit("Error de Conexi&oacute;n. <br />" . $this->db->_errorMsg);
			}
			
			if ($driver == 'oci8'){
				$this->db->connectSID = true;
				$this->db->autoRollback = true; # default is false
				//putenv('NLS_LANG=SPANISH_SPAIN.WE8MSWIN1252');
			}
			
			$this->conectado = true;
			
			$this->db->SetFetchMode(ADODB_FETCH_ASSOC); // los resultados son de forma asociada
			$this->rv();
			
			//var_dump(pg_client_encoding($this->db->_connectionID));
			//exit;
			//$this->db->_Execute("SET NAMES 'UTF-8';");
			//pg_set_client_encoding($this->db->_connectionID, "UNICODE");
			
		}catch (exception $e){
			//var_dump($e);
		}
	}
	
	public function conectado(){
		return (bool) $this->conectado;
	}

	public function camposUsaComillas($c = NULL){
		if (!is_null($c))
			$this->camposUsaComillas = (bool) $c;

		return $this->camposUsaComillas;
	}
	
	public function setSchema($schema){
		if ($this->schema == $schema)
			return false;
		
		if ($this->driver === 'postgres'){
			$s = $this->db->Execute("SET search_path TO $schema,$this->schema;") === false ? false : true;
			if ($s)
				$this->schema = $schema;
		}elseif($this->driver == 'oci8' || $this->driver == 'oci805'){
			$s = $this->db->Execute("ALTER SESSION SET CURRENT_SCHEMA = $schema") === false ? false : true;
			if ($s){
				$this->schema = $schema;
			}
		}else{
			$s = false;
		}
		
		return $s;
	}
	
	/**
	 * Iniciar las transacion entre tablas,
	 **/
    public function begin(){
        $this->db->BeginTrans();
        return $this;
    }
    public function CommitTrans(){
        $this->db->CommitTrans();
        return $this;
    }
    public function RollbackTrans(){
        $this->db->RollbackTrans();
        return $this;
    }
    
	/**
	 * base_datos::ur()
	 *
	 * Devuelve un array asociativo con el primer registro del resultado de una sentencia,
	 * si el parametro $campo es indicado será devuelto el valor del campo si existe o
	 * forma parte del resultado.
	 *
	 * @param bool $campo
	 * @return array
	 */
	function ur($campo = false){
		if ($this->rs === false)
			return false;

		if ($this->ur == false){
			$this->ur = $this->rs->fields;
			//$this->rs->MoveFirst();
			//$this->ur = $this->rs->FetchRow();
			//$this->rs->MoveFirst();

			if ($this->ur === false)
				return false;
		}

		if ($campo != false)
			return array_key_exists($campo, $this->ur) ? $this->ur[$campo] : false;

		return $this->ur;
	}

	/**
	 * base_datos::uq()
	 *
	 * hace una salida de la ultima consulta (query) creada por el objeto.
	 *
	 * @return
	 */
	function uq(){
		echo $this->uq . "\n";
		return true;
	}

	/* reinicia las propiedades del objeto */

    /**
     * base_datos::rv()
     *
     * reinicia las propiedades asociadas con la ejecucion de cualquier consulta realizada
     * con el objeto.
     *
     * @return
     */
    private function rv(){
		$this->uq = '';
		
		//if ($this->is_rs($this->rs)) $this->rs->Close();
		
        $this->resultado = $this->rs = $this->ultimo_id_insertado = $this->uid = $this->ur = false;
		$this->afectados = $this->af = 0;
		
		return true;
    }
    
    public function rs(){
    	return clone($this->rs);
    }
    
    public function is_rs($rs){
    	return is_object($rs) && strrpos(get_class($rs), 'ADORecordSet') !== false;
    }
    
	public function is_recordset($rs){
		return $this->is_rs($rs);
	}
	
	public function db_array($val){
		return is_string($val) ? $this->str_getcsv(trim($val, '{}')) : $val;
	}
	
    /**
     * base_datos::procesarTabla()
     *
     * recibe como parametro el nombre de una tabla para prepararla para ser usada en una consulta,
     * si la propiedad tbPrefijo esta definido se antepone el valor de esta
     *
     * @param mixed $tabla
     * @return
     */
    private function procesarTabla($tabla){
		if (is_array($tabla)){
			$tablaS = '';
			foreach($tabla as $ll => $v)
				$tablaS .= ($this->tbPrefijo == '' ? '' : $this->tbPrefijo . '.') . $v . (is_int($ll) ? '' : $ll) . ', ';

			$tabla = substr_replace($tablaS, '', -2);
		}else
			$tabla = $this->tbPrefijo != '' ? $this->tbPrefijo . '.' . $tabla : $tabla;

		return $tabla;
    }
    
    public function qstr($var){
    	return $this->db->qstr($var, $this->magic_quotes_gpc);
    }

    /**
     * base_datos::migrarDatos()
     *
     * @param mixed $dbd
     * @param mixed $opciones
     * @return
     */
    function migrarDatos($dbd, $opciones = array()){
    	set_time_limit(0);
    	$limite = 100;
		$tipoDeCampos = array('tinyint', 'smallint', 'mediumint', 'int', 'integer', 'bigint', 'float', 'double', 'decimal', 'bit', 'bool', 'binary');

    	if (array_key_exists('limite', $opciones))
			$limite = $opciones['limite'];

		if (array_key_exists('tablas', $opciones))
			$tablas = $opciones['tablas'];
		else{
			$tablas = $this->db->MetaTables('TABLES');
			sort($tablas);
			reset($tablas);
		}

		foreach($tablas as $tabla){
			if (array_key_exists('vaciar', $opciones))
				$dbd->vaciarTabla($tabla, 1);

			$inicio = 0;
			while(true){
				$rs = $this->db->SelectLimit('SELECT * FROM ' . $tabla, $limite, $inicio);
				$this->salidaRS($rs);

				if ($this->af <= 0) {
					echo 'Migre los Datos de la Tabla <strong>' . $tabla . '</strong>.<br />';
					break;
				}

				$campos = $this->db->MetaColumns($tabla);
				foreach($this->rs as $row){
					foreach($campos as $elementoCampo){
						$row[$elementoCampo->name] = utf8_encode($row[$elementoCampo->name]);

						if (array_search($elementoCampo->type, $tipoDeCampos) === false)
							$row[$elementoCampo->name] = "'" . $row[$elementoCampo->name] . "'";
						else
							if ($row[$elementoCampo->name] == '')
								$row[$elementoCampo->name] = 0;

						if ($row[$elementoCampo->name] == null)
							$row[$elementoCampo->name] = 0;
					}

					$g = $dbd->guardar($tabla, $row); //si genera error lo muestra por el metodo interno de error

					if ($g === false)
						echo '<pre>' . print_r($row, true) . '</pre>';

				}

				usleep(100000);
				$inicio += $limite;
			}
		}

		return true;
    }

    /**
     * base_datos::ejecutar()
     *
     * @param mixed $sql
     * @return
     */
    public function ejecutar($sql, $concepto = 0){
    	$this->rv();
    	if (is_string($sql)){
    		$this->uq = $sql;
    		$this->rs = $this->db->Execute($this->uq);
    	}elseif ($this->is_rs($sql)){
    		$this->uq = $sql->sql;
    		$this->rs = $sql;
    	}
    	
		if($this->rs === false){
			$this->rs = array();
			return $this->error($sql);
		}
		
		$this->salidaRS($concepto);

        return true;
	}
	
	public function _ejecutar($sql, $concepto = 0){
    	$this->rv();
    	if (is_string($sql)){
    		$this->uq = $sql;
    		$this->db->_execute($this->uq);
    	}elseif ($this->is_rs($sql)){
    		$this->uq = $sql->sql;
    	}

        return true;
	}


	/**
	 * base_datos::salidaRS()
	 *
	 * @return
	 */
	function salidaRS($concepto){
		// $this->db->Affected_Rows() solo funciona para instrucciones update y delete
		// $this->rs->_numOfRows solo funciona si la variable $ADODB_COUNTRECS es igual a true
		// $this->rs->EOF funciona para sentencias select, en caso de insert, update y delete es igual a true
		$this->eof = &$this->rs->EOF;
		if ($concepto === 0){
			$this->af = $this->rs->_numOfRows;
		}elseif ($concepto === 4){
			$this->af = $this->rs->_numOfRows <= 0 ? $this->db->Affected_Rows() : $this->rs->_numOfRows;
		}else{
			$this->af = $this->db->Affected_Rows();
		}
		
		$this->resultado = &$this->rs;
		$this->afectados = &$this->af;
		return true;
    }

    /**
     * base_datos::error()
     *
     * @param mixed $error
     * @return
     */
    private function error($sql){
    	if ($this->entorno !== 'development'){
    		return false;
    	}
    	
    	if ($this->ci->isajax()){
    		echo "\nSQL: " . $sql . "\n\n";
    		if ($this->db->ErrorNo() != 0){
    			echo $this->db->ErrorNo() . ": " . ($this->db->ErrorMsg()) . "\n\n";
    		}
    	} else{
    		echo "\n\n<br />SQL: " . nl2br($sql) . "<br />\n\n";
    		if ($this->db->ErrorNo() != 0){
    			echo "<strong>" . $this->db->ErrorNo() . ": " . utf8_decode($this->db->ErrorMsg()) . "</strong><br /><br />\n";
    		}
    	}   

        return false;
    }

	/**
	 * base_datos::vaciarTabla()
	 *
	 * @param bool $tabla
	 * @param integer $op
	 * @return
	 */
	function vaciarTabla($tabla = false, $op = 0){
		if (is_array($tabla)){
			$tabla = implode(', ', $tabla);
		}

		$tabla = $this->procesarTabla($tabla);
		$p = 'TABLE';

		if ($op === 1){
			$op = 'CASCADE';
			$p = '';
		}elseif ($op === 2){
			$op = 'RESTRICT';
			$p = '';
		}else
			$op = '';

		if (!$this->ejecutar('TRUNCATE ' . $p . ' ' . $tabla . ' ' . $op, 0))
			return false;

		return $this->af;
    }

	/**
	 * base_datos::seleccionarEnMatriz()
	 *
	 * @param mixed $tabla
	 * @param string $campos
	 * @param string $id
	 * @param string $condicion
	 * @param string $ordenar
	 * @return
	 */
	function seleccionarEnMatriz($tabla, $campos = '*', $id = '', $condicion = false, $ordenar = false){
		$this->seleccionar($tabla, $campos, $condicion, $ordenar);

		if ($this->af <= 0)
			return array();

		return $this->rsAMatriz($this->rs, $id);
	}

	function rsAMatriz(&$rs, $id = ''){
		$arr = array();
		
		$rs->MoveFirst();
		$ur = $rs->FetchRow();
		$rs->MoveFirst();

		if (count($ur) >= 2 && $id === '')
			$id = current(array_keys($ur));

		if ($id === '' || $id === false){
			foreach($rs as $row){
				$arr[] = $row;
			}
		}else{
			foreach($rs as $row){
				$arr[$row[$id]] = $row;
			}
		}
		
		return $arr;
	}

    /**
     * base_datos::seleccionarArray()
     *
     * @param mixed $tabla
     * @param string $campos
     * @param string $condicion
     * @param string $ordenar
     * @return
     */
    function seleccionarArray($tabla, $campos = '*', $condicion = false, $ordenar = false, $sinIndice = false){
    	$this->seleccionar($tabla, $campos, $condicion, $ordenar);
    	if ($this->af <= 0)
    		return array();

		$salida = $this->rs->GetArray();
		if ($sinIndice === true){
    		$salidaux = $salida;
			$salida = array();
			$i = 0;
	    	foreach($salidaux as $v){
	    		foreach($v as $vv)
	    			$salida[$i][] = $vv;

				$i++;
			}
		}
		return $salida;
	}

	/* funcion seleccionar hace un query de Select */
	/**
	 * base_datos::seleccionar()
	 *
	 * @param mixed $tabla
	 * @param string $campos
	 * @param string $condicion
	 * @param string $ordenar
	 * @param bool $retornarRs
	 * @return
	 */

	private function trim($c){
		if (!is_array($c))
			return trim($c);

		foreach($c as $lc => $cc)
			$c[$lc] = $this->trim($cc);

		return $c;
	}

	function seleccionar($tabla, $campos = '*', $condicion = false, $ordenar = false, $retornarRs = false, $sqlExtra = '') {
	    $concepto = 0;

		$tabla = $this->procesarTabla($tabla);
		$campoSQL = array();

		if ($condicion === '')
			$condicion = false;

		if ($ordenar === '')
			$ordenar = false;
			
		if (!is_array($campos)){
			$campos = explode(',', $campos);
			foreach($campos as $v)
				$arr[] = $this->trim(preg_split('/ as /i', $v));

			$campos = $arr;
		}

		foreach($campos as $ll => $v){
			if (is_array($v))
				$campoSQL[] = $v[0] . (array_key_exists(1, $v) ? ' AS ' . $v[1] : '');
			else{
				if ($v === '')
					continue;

				$campoSQL[] = $v;
			}
		}
		
		$sql = 'SELECT ' . implode(', ', $campoSQL) . ' FROM ' . $tabla . ' ';
		
		if (is_array($condicion)){
			$limit = false;
			$join = array();
			$condiciones = array( //debe ir en el orden del SELECT sql
				'j' => '',
				'lj' => '',
				'rj' => '',
				
				'w' => '',
				'g' => '',
				'h' => '',
				'o' => '',
				'sql' => ''
			);
			foreach($condicion as $index => $valor){
				if ($valor == "")
					continue;
				
				switch(strtolower($index)){
					case "j": //JOIN
						$join['j'] = 'JOIN ' . $valor;
						unset($condiciones['j']);
						break;
					
					case "lj": //LEFT JOIN
						$join['lj'] = 'LEFT JOIN ' . $valor;
						unset($condiciones['lj']);
						break;
							
					case "rj": //RIGHT JOIN
						$join['rj'] = 'RIGHT JOIN ' . $valor;
						unset($condiciones['rj']);
						break;
												
					case "w": //Where
						$condiciones['w'] = 'WHERE ' . $valor;
						break;
					
					case "g": // Group By
						$condiciones['g'] =  'GROUP BY ' . $valor;
						break;
					
					case "h": // Having
						$condiciones['h'] =  'HAVING  ' . $valor;
						break;
								
					case "o": // Order By
						$condiciones['o'] =  'ORDER BY ' . $valor;
						break;
						
					case "l": // Limit
						if (is_array($valor) && count($valor) === 2)
							$limit = $valor;
						
						unset($condiciones['l']);
						break;
						
					case "r": // retornar rs
						$retornarRs = (bool) $valor;
						unset($condiciones['r']);
						break;
						
					case "sql": // sql
						$condiciones['sql'] =  $valor;
						break;
						
					default:
						break;
				}
			}
			
			$join = implode(' ', $join);
			
			$condicion = implode(' ', $condiciones); 
			
			
			$sql .= $join . ' ' . $condicion;
			
			if ($limit !== false){
				$sql = $this->db->SelectLimit($sql, $limit[0], $limit[1]);
			}	
			
			if (!$this->ejecutar($sql, $concepto))
				return false;
		}else{
			$sql .= ($condicion === false ? '' : 'WHERE ' . $condicion) . ' ' . ($ordenar === false ? '' : 'ORDER BY ' . $ordenar) . " " . $sqlExtra;

			if (!$this->ejecutar($sql, $concepto))
				return false;
		}

		$this->historico($condicion, $tabla, $concepto);
		return $retornarRs === false ? $this->af : $this->rs;
	}

	/**
	 * base_datos::procesarCampoGuardar()
	 *
	 * @param mixed $campos
	 * @param mixed $valores
	 * @return
	 */
	private function procesarCampoGuardar($campos, $valores){
		if ($valores === false){
			$valores = implode(', ', $this->procesarValores(array_values($campos)));
			$campos  = implode(', ', array_keys($campos));
        }elseif ((is_array($campos) && is_array($valores)) && count($campos) == count($valores)){
        	$valores = array_values(array_values($valores));
        	$campos  = implode(', ', $campos);
		}
		
		return array($campos, $valores);
	}

	private function procesarUTF8($var){
		if ($this->usaUTF8 === false)
			return $var;

		if (!is_array($var))
			return utf8_encode($var);

		//while(list($ll) = each($var))
		foreach($var as $ll => $v){
			$var[$ll] = utf8_encode($v);
		}

		return $var;
	}
	
	private function procesarValores($valores){
		if (is_null($valores)){
			return 'null';
		}
			
		if (is_string($valores)){
			if (substr($valores, 0, 1) === '\'' && substr($valores, -1) === '\''){
				return $valores;
			}
			
			$valores = stripslashes(str_replace('\\', '', trim($valores)));
			$valores = $this->db->qstr($valores, $this->magic_quotes_gpc);
			
			return $valores;
		}
		
		if (!is_array($valores)){
			return $valores;
		}
		
		foreach($valores as $ll => $v){
			$valores[$ll] = $this->procesarValores($v);
		}
		
		return $valores;
	}

	/**
	 * base_datos::procesarCampoActualizar()
	 *
	 * @param mixed $campos
	 * @param mixed $valores
	 * @return
	 */
	private function procesarCampoActualizar($campos, $valores){
		$actualizar = array();
		if ($valores === false){
			//$this->procesarValores(array_values($campos));
			$campos = $this->procesarValores($campos);
			foreach($campos as $llave => $valor){
				$actualizar[] = $llave . ' = ' . $valor;
			}
        }elseif ((is_array($campos) && is_array($valores)) && count($campos) == count($valores)){
        	$this->procesarValores($valores);
        	foreach($campos as $llave => $valor){
				$actualizar[] = $valor . ' = ' . $valores[$llave];
			}
		}else{
			echo '<br>Error de Variables.<br>';
			return false;
		}

		return implode(', ', $actualizar);
	}

	/**
	 * base_datos::guardarMatriz()
	 *
	 * para los casos de $estado se tienen los siguientes:
	 * 0: el objeto de base de datos guardara o actualizara el registro
	 * 1: el objeto de base de datos guardara el registro en caso de no existir
	 * 2: el objeto de base de datos actualizara el registro en caso de existir
	 *
	 * @param array $matriz
	 * @param string $tabla
	 * @param string $condicion
	 * @param int $estado
	 * @return int
	 */
	public function guardarMatriz($matriz, $tabla, $condicion = '', $estado = 0){
		$estado = (int) $estado;

		if ($estado < 0 || $estado > 2)
			$estado = 0;

		$i = 0;

		if (!is_array($condicion))
			$condicion = explode(',', $condicion);

		$matrizEle = current($matriz);

		foreach($condicion as $ll => $v)
			if (!array_key_exists($v, $matrizEle))
				unset($condicion[$ll]);

		foreach($matriz as $ll => $v){
			$i++;
			$af = 0;
			$condicionSql = array();

			if (!empty($condicion)){
				foreach($condicion as $vc){
					if (array_key_exists($vc, $v)){
						$condicionSql[] = ($this->camposUsaComillas ? '"'.$vc.'"' : $vc) . ' = ' . $v[$vc];
					}
				}
				
				$this->seleccionar($tabla, 'count(*) as count', implode(' AND ', $condicionSql));
				$af = (int) $this->ur('count');
			}

			if ($af > 0 && ($estado == 2 or $estado == 0))
				$this->actualizar($tabla, $v, false, implode(' AND ', $condicionSql));
			elseif ($af == 0 && $estado <= 1)
				$this->guardar($tabla, $v);
		}

		return $i;
	}

	/* funcion guardar hace un query de Insert into */
	/**
	 * base_datos::guardar()
	 *
	 * @param mixed $tabla
	 * @param mixed $campos
	 * @param bool $valores
	 * @param string $campo_clave
	 * @return
	 */
	function guardar($tabla, $campos, $valores = false, $campo_clave = false) {
        list($campos, $valores) = $this->procesarCampoGuardar($campos, $valores);
        
        if (is_array($campos) or is_array($valores))
        	return $this->error('Los Valores no Concuerdan.');

		if ($campo_clave === '')
			$campo_clave = false;
		
		$sql = 'INSERT INTO ' . $tabla . ' (' . $this->procesarUTF8($campos) . ') VALUES (' . $this->procesarUTF8($valores) . ') ';
		
		if ($this->driver == 'postgres'){
			if (!$this->ejecutar($sql . ($campo_clave === false ? '' : 'RETURNING ' . $campo_clave), 1))
				return false;
			
			$this->uid = $campo_clave != '' ? $this->ur($campo_clave) : $this->db->Insert_ID();
		}else{
			if (!$this->ejecutar($sql, 1))
				return false;
				
			$this->uid = $this->db->Insert_ID();
		}
		
		$this->ultimo_id_insertado = &$this->uid;

        $this->historico($this->uid, $tabla, 1);
		return true;
	}

	/* funcion actualizar hace un query de update */
	/**
	 * base_datos::actualizar()
	 *
	 * @param mixed $tabla
	 * @param mixed $campos
	 * @param bool $valores
	 * @param bool $condicion
	 * @return
	 */
	function actualizar($tabla, $campos, $valores = false, $condicion = false) {
		if (!$this->ejecutar('UPDATE ' . $tabla . ' SET ' . $this->procesarUTF8($this->procesarCampoActualizar($campos, $valores)) . ($condicion === false ? '' : ' WHERE ' . $condicion), 2))
			return false;

        $this->historico($condicion, $tabla, 2);

		return $this->af;
	}

	/* funcion eliminar hace un query de delete */
	/**
	 * base_datos::eliminar()
	 *
	 * @param mixed $tabla
	 * @param string $condicion
	 * @return
	 */
	function eliminar($tabla, $condicion = false){
		if (!is_string($tabla))
			$this->error("El Parametro \"tabla\" en el metodo eliminar debe ser de tipo \"string\".");
		
		if ($condicion !== false && !is_string($condicion))
			$this->error("El Parametro \"condicion\" en el metodo eliminar debe ser de tipo \"string\".");
		
		if (!$this->ejecutar('DELETE FROM ' . $tabla . ' ' . ($condicion === false ? '' : 'WHERE ' . $condicion), 3))
			return false;

        $this->historico($condicion, $tabla, 3);

		return $this->af;
	}

    /* funcion query hace un query en la base de datos */
	/**
	 * base_datos::query()
	 *
	 * @param mixed $sql
	 * @return
	 */
	function query($sql) {
		if (!$this->ejecutar($sql, 0))
			return false;

        $this->historico('query', 'sql_query', 4);

		return $this->af;
	}

    /**
     * base_datos::historico()
     *
     * @param mixed $id
     * @param mixed $tabla
     * @param mixed $concepto
     * @return
     */
    private function historico($id, $tabla, $concepto){
        if ($this->auto_historial !== false){
            if (array_search($concepto, $this->auto_historial) !== false){
                $sql = 'INSERT INTO '.$this->tblhistorico.' (idRegistro, concepto, tabla, usuario, fecha) VALUES (\''.addslashes($id).'\', \''.$this->conceptos[$concepto].'\', \''.$tabla.'\', \'' . $this->usuario . '\', \''.date('Y-m-d H:i:s').'\')';

                $this->rs = $this->db->Execute($sql);

                if($this->rs === false)
					return $this->error($sql);
            }
        }
        return true;
    }
    
    public function str_getcsv($input, $delimiter = ",", $enclosure = '"', $escape = "\\") {
    	if (function_exists('str_getcsv')){
    		return str_getcsv($input, $delimiter, $enclosure, $escape);
    	}
    	
        $fiveMBs = 5 * 1024 * 1024;
        $fp = fopen("php://temp/maxmemory:$fiveMBs", 'r+');
        fputs($fp, $input);
        rewind($fp);

        $data = fgetcsv($fp, 1000, $delimiter, $enclosure); //  $escape only got added in 5.3.0

        fclose($fp);
        return $data;
    }

    public function cerrar(){
    	if (is_object($this->rs))
    		$this->rs->Close();

    	$this->db->Close();
    }
    
    public function __clone(){
        $this->db = clone $this->db;
        $this->rv();
    }
}

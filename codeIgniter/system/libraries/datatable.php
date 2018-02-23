<?php
class datatable{
	public $columnToField = array(); 	// es necesario asingnarlo // nombre de los campos utilizados para el orden (order by)
	public $aColumns = array(); 		// es necesario asingnarlo // columnas para la sentencia where sql
	public $aColumnsSelect = array(); 	// es necesario asingnarlo // columnas a retornar
	public $aColumnsFormat = array(); 	// es necesario asingnarlo // formato de las columnas
	public $columnasForm = array();

	public $condicionOriginal = ''; 	// es necesario asingnarlo
	public $join = ''; 				// es necesario asingnarlo // anexo de sentencia join
	public $sIndexColumn = ''; 		// es necesario asingnarlo // nombre de la columna 'id'
	public $ordenar = ''; 				// es necesario asingnarlo // nombre del campo a ordenar segundario

	public $urlAccion = ''; 			// es necesario asingnarlo // accion javascripts // entre llaves el nombre del campo

	public $tabla = ''; 		// es necesario asingnarlo // tabla a consultar para la tabla
	public $db; 				// es necesario asingnarlo
    public $linkHtml = '';

	public $sSearch = '';

	public $sLimit = array();
	public $iDisplayStart;
	public $iDisplayLength;

	public $accion = '';

	public $iSortCol = array();
	public $sOrder = '';

	public $form;

	public $sWhere = '';

	public $sQuery = '';
	public $rResult = '';
	public $aResultFilterTotal = '';
	public $iFilteredTotal = '';
	public $iTotal = '';
	public $afectados = '';

	public $sEcho = '';
	public $sOutput = '';
	public $ci = '';

	function __construct($form = false){
		$this->ci = &get_instance();
		if ($form === false)
			return $this;
			
		return $this->form($form);
	}

	function db($db){
		$this->db = $db;
		return $this;
	}

	function tabla($tabla){
		$this->tabla = $tabla;
		return $this;
	}

	function sIndexColumn($sIndexColumn){
		$this->sIndexColumn = $sIndexColumn;
		return $this;
	}

	function ordenar($ordenar){
		$this->ordenar = $ordenar;
		return $this;
	}

	function urlAccion($urlAccion){
		$this->urlAccion = $urlAccion;
		return $this;
	}

	function condicionOriginal($condicionOriginal){
		$this->condicionOriginal = $condicionOriginal;
		return $this;
	}

	function accion($accion){
		$this->accion = $accion;
		return $this;
	}

	function form(&$form, $orden = false){
		$this->form = &$form;

		$id = $this->form->buscarObj(array('campoPrincipal' => true));
				
		list($campoId, $tipoId) = $this->form->dblista($id);
		
		$tabla = false;
		if ($orden === false){
			$tabla = $this->form->buscarObj(array('tipo' => "objtabla"), true);
			$tabla = isset($tabla[0]) ? $tabla[0] : false;
			if ($tabla !== false)
				$tabla = $this->form->objs($tabla);
		}else{
			$tabla = $this->form->objs($orden);
		}
		
		$id = ($tabla !== false && !empty($tabla->campos)) ? array_values($tabla->campos) : $this->form->selector($this->form->buscarObj(array('dbActivo' => true), true));
		
		$columnas = array();
		$columnasForm = array(); 

		foreach($id as $v){
			$ido = $v->id();
			if ($ido === '|' || $ido === 'barra') continue;
			
			list($campo, $tipo) = $this->form->dblista($ido);
			$columnas[$campo] = $tipo;
			$obj = &$v->valorFuente;
			
			//$v->valorArray
			
			if (!empty($v->valorArray)){
				$columnasForm[$campo] = $v->valorArray;
			}elseif (is_object($obj)){
				$columnasForm[$campo] = $this->form->db()->rsAMatriz($obj);
			}elseif(is_array($obj)){
				$columnasForm[$campo] = $obj;
			}
		}

		$this
		->db($this->form->db())
		->tabla($this->form->tabla())
		->sIndexColumn($campoId)
		->ordenar($campoId)
		->columnas($columnas)
		->columnasForm = $columnasForm;

		return $this;
	}

	function iniciacion(){
		$this->sSearch  = $this->get('sSearch');

		$this->iDisplayStart  = (int) $this->get('iDisplayStart');
		$this->iDisplayLength = (int) $this->get('iDisplayLength');

		$this->sEcho = (int) $this->get('sEcho');

		for ($i = 0; $i < $this->get('iSortingCols'); $i++)
			$this->iSortCol[$this->columnToField[$this->get('iSortCol_'.$i)]] = $this->get('sSortDir_'.$i);

	}

	function hacer($forzar = true){
		$this->iniciacion();
		$this->paginacion();
		$this->ordenando();
		$this->filtrando();
		$this->query();

		return $this->salida($forzar);
	}

	function get($id){
		return $this->ci->get($id);
	}

	function columnas($columnas){
		if (!is_array($columnas))
			return false;

		foreach($columnas as $ll => $v){
			$this->columnToField[] = $ll;
			$this->aColumnsFormat[] = $v;
		}
		
		$this->aColumns = $this->aColumnsSelect = $this->columnToField;

		return $this;
	}

	function query(){
		//$this->db->db->debug = true;
		
		$this->db->seleccionar($this->tabla, $this->tabla.'.'.$this->sIndexColumn.', ' . implode(', ', $this->aColumnsSelect), array(
			"sql" => $this->join . ' ' . $this->sWhere . ' ' . $this->sOrder,
			"l" => $this->sLimit
		));
		
		$this->sQuery = $this->db->uq;

		//echo "\n" . $this->sQuery . "\n";

		$this->rResult = $this->db->rs->GetRows();
		
		$this->db->query('SELECT COUNT(*) as count FROM ' . $this->tabla . ' ' . $this->join . ' ' . $this->sWhere);
		$this->iFilteredTotal = (int) $this->db->ur('count');

		$this->db->query('SELECT COUNT(*) as count FROM ' . $this->tabla);
		$this->iTotal = (int) $this->db->ur('count');
	}

	/* Paginacion */
	function paginacion(){
		if ($this->iDisplayStart !== '' && $this->iDisplayLength !== -1){
			$this->sLimit = array($this->iDisplayLength, $this->iDisplayStart);
			// LIMIT x, y
		}
	}

	/* Ordenando */
	function ordenando(){
		if (count($this->iSortCol) > 0){
			$this->sOrder = 'ORDER BY ';

			foreach($this->iSortCol as $ll => $v)
				$this->sOrder .= $ll . ' ' . $v .  ', ';

			$this->sOrder = substr_replace($this->sOrder, '', -2);
		}

		if ($this->ordenar != ''){
			if ($this->sOrder == '')
				$this->sOrder = 'ORDER BY ' . $this->ordenar . ' ';
			else
				$this->sOrder .= ', ' . $this->ordenar . ' ';
		}
		// ORDER BY campo [DENC , ANC]
	}

	/* Filtrando */
	function filtrando(){
		$operador = "LIKE";
		$this->sWhere = "";
		
		if ($this->sSearch != ""){
			$this->sWhere = array();
		
		foreach($this->aColumnsFiltro as $ll => $v){
			if($this->db->db->dataProvider == "postgres")
				$operador = "ILIKE";
			
			if (array_search($this->aColumnsFormat[$ll], array("date", "int")) !== false){
				if($this->aColumnsFormat[$ll] == "date")
					$v = "to_char($v ,'dd/mm/yyyy HH12:MI:SS')";        
				else{
					$v = "CAST($v as varchar(200))";
				}
			}                
			$this->sWhere[] = $v . " $operador '%".$this->sSearch."%'";        
		}
		
		if (count($this->sWhere) > 0)
			$this->sWhere = "WHERE (" . implode(" or ", $this->sWhere) . ")";
		}
	
		if ($this->sWhere != "" and $this->condicionOriginal != "")
			$this->sWhere .= " AND (".$this->condicionOriginal.")";
		elseif ($this->condicionOriginal != "")
			$this->sWhere = "WHERE (".$this->condicionOriginal.")";
	}

	/* salida */
	function salida($f = true){
		$this->sOutput = array(
			'sEcho' => $this->sEcho,
			'iTotalRecords' => $this->iTotal,
			'iTotalDisplayRecords' => $this->iFilteredTotal,
			'aaData' => array()
		);

		if ($this->iFilteredTotal == 0)
			$this->ssalida($f);

		$urlAccion = '';
		if ($this->urlAccion != '' || $this->linkHtml != '')
			$urlAccion = $this->urlAccion . ' ' . $this->linkHtml;

		foreach($this->rResult as $aRow){
			$srow = array();

			$accion = $urlAccion == '' ? '' : '<a href="#" ' . plantilla($urlAccion, $aRow) . '>';

			foreach($this->aColumns as $llave => $valores){
				$aRow[$valores] = trim(strip_tags($aRow[$valores]));
				
				if ($this->aColumnsFormat[$llave] == 'date'){
					$aRow[$valores] = $this->tratarFechas($aRow[$valores], 'd/m/Y');
				}/*elseif(strpos($this->aColumnsFormat[$llave], ':') !== false){
					$aColumnsFormatVal = explode(':', $this->aColumnsFormat[$llave]);
					var_dump($aColumnsFormatVal);
					if (count($aColumnsFormatVal) > 1)
						$aRow[$valores] = recortar_str($aRow[$valores], $aColumnsFormatVal[1]);
				}*/

				if (isset($this->columnasForm[$valores])){
					$id = $aRow[$valores];
					
					$aRow[$valores] = is_array($this->columnasForm[$valores][$id]) ? $this->columnasForm[$valores][$id]['nombre'] : $this->columnasForm[$valores][$id];
				}

				$srow[] = ($accion == '') ? $aRow[$valores] : $accion . $aRow[$valores] . '</a>';
			}

			$this->sOutput['idReg'][] = $aRow[$this->sIndexColumn];

			if ($this->accion != '')
				$this->sOutput['jQuery'][] = plantilla($this->accion, $aRow);

			$this->sOutput['aaData'][] = $srow;
		}

		$this->ssalida($f);
	}

	private function ssalida($f){
		if ($f === true)
			exit(json_encode($this->sOutput));

		return $this->sOutput;
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
}
?>
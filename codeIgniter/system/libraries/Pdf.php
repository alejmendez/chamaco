<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

include_once(BASEPATH . 'libraries/tcpdf/tcpdf' . EXT);

class pdf extends TCPDF{
	public $pdfConf = array(
		'orientacion' => 'P', //'L' o 'P' landscape or portrait orientation
		'unidad' => 'mm',
		'formato' => 'Letter', //A4, A5, Letter
		'unicode' => true,
		'encoding' => 'UTF-8',
		'cache' => false,
		'pdfa' => false,
		
		'language' => 'es', //es, fr, en, it
		'marges' => array(15, 15, 15, 10), //(left, top, right, bottom)
		
		'creator' => 'Gobernación de Bolívar',
		'author' => 'Gobernación de Bolívar',
		'title' => 'Reporte',
		'subject' => 'Reporte',
		'keywords' => 'Reporte',
	);
	
	protected $_color_cache = array();
	
	//public function __construct($orientation='P', $unit='mm', $format='Letter', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false) {
	public function __construct($conf = array()) {
		set_time_limit(0);
		
		$this->ci = &get_instance();
		$this->pdfConf($conf);
		
		parent::__construct($this->pdfConf['orientacion'], $this->pdfConf['unidad'], $this->pdfConf['formato'], $this->pdfConf['unicode'], $this->pdfConf['encoding'], $this->pdfConf['cache'], $this->pdfConf['pdfa']);
		
		$this->setFontSubsetting(false);
		
        $this->SetCreator($this->pdfConf['creator']);
		$this->SetAuthor($this->pdfConf['author']);
		$this->SetTitle($this->pdfConf['title']);
		$this->SetSubject($this->pdfConf['subject']);
		$this->SetKeywords($this->pdfConf['keywords']);
		
		$this->SetMargins($this->pdfConf['marges'][0], $this->pdfConf['marges'][1], $this->pdfConf['marges'][2]);
		
		$this->SetAutoPageBreak(TRUE, $this->pdfConf['marges'][3]);
		
		//$this->SetDisplayMode('fullpage');
		$this->SetDisplayMode('fullwidth', 'OneColumn');
		
		// set font
		$this->SetFont('times', '', 10);
		
		$this->dimensions = $this->getPageDimensions();
		$this->margenes = $this->getMargins();
		
    	$this->anchoInternoPagina = $this->dimensions['wk'] - $this->margenes['left'] - $this->margenes['right'];
    	$this->altoInternoPagina = $this->dimensions['hk'] - $this->margenes['top'] - $this->margenes['bottom'];
		
		// set cell padding
		//$this->setCellPaddings(1, 1, 1, 1);
		
		// set cell margins
		//$this->setCellMargins(1, 1, 1, 1);
		
		// set image scale factor
		$this->setImageScale(PDF_IMAGE_SCALE_RATIO);
		
		$this->AddPage();
	}
	
    public function Header(){
    	$ancho = $this->anchoInternoPagina / 3;
    	$this->MultiCell($ancho, 5, 'cabeza izq', 1, 'L', 0, 0, '', 5, true);
        $this->MultiCell($ancho, 5, 'cabeza izq', 1, 'L', 0, 0, '', '', true);
        $this->MultiCell($ancho, 5, 'cabeza izq', 1, 'L', 0, 1, '', '', true);
    }

    public function Footer(){
    	/*$ancho = $this->anchoInternoPagina / 3;
        $this->MultiCell($ancho, 5, '', 1, 'L', 0, 0, '', $this->dimensions['hk'] - 8, true);
        $this->MultiCell($ancho, 5, $this->PageNo() . ' / ' . $this->PageNoFormatted(), 1, 'C', 0, 0, '', '', true);
        $this->MultiCell($ancho, 5, '', 1, 'L', 0, 1, '', '', true);*/
        
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        //$this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Pagina '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
    
    protected function calcular_ancho($ancho, $anchoContenedor = false){
    	if(is_array($ancho)){
    		foreach ($ancho as $ll => $v) {
      			$ancho[$ll] = $this->calcular_ancho($v, $anchoContenedor);
			}
    	}elseif (is_string($ancho)){
			if (preg_match_all('/^(\d{1,3})\%$/', $ancho, $coincidencias)){
	    		$_ancho = floatval($coincidencias[1][0]);
				$ancho = $_ancho <= 0 || $_ancho > 100 ? $anchoContenedor : ($_ancho * $anchoContenedor / 100);
			}else{
				$ancho = $anchoContenedor;
			}
    	}
    	
    	return $ancho;
    }
    
    public function color($color, $tipo = 'fill'){ 
    	// se recomienda que el $color sea un array RGB para no tener q realizar el calculo de conversion
    	$tipos = array(
			1 => 'draw', // 1 => color de lineas (lineas, bordes)
			'fill', // 2 => color de relleno
			'text' // 3 => color de texto
		);
		
		if(isset($tipos[$tipo])){
			$tipo = $tipos[$tipo];
		}elseif(!in_array($tipo, $tipos)){
			return false;
		}
		
		if (is_string($color)){
			$color = $this->HexToRGB($color);
		}
		
    	return $this->setColorArray($tipo, $color);	
    }
    
    public function fuente(){//font o tipo de letra
    	
    }
    
    public function tabla($data, $parametros = array()){
    	$this->dimensions = $this->getPageDimensions();
		$this->hasBorder = false; //flag for fringe case
		
		$cellMargins = $this->getCellMargins();
		
		$altoPorLinea = ($this->GetFontSize() * $this->getCellHeightRatio()) + (2 * $cellMargins['T']);
		
		$usarParImpar = false;
		
    	//var_dump($this->dimensions);
    	$parametros_por_defecto = array(
			'ancho' => $this->anchoInternoPagina,
			'anchos' => array(),
			'par_impar' => array(),
			'cabecera' => array()
		);
		
		$parametros = array_merge($parametros_por_defecto, $parametros);
		$parametros['ancho'] = $this->calcular_ancho($parametros['ancho'], $this->anchoInternoPagina);
		$parametros['anchos'] = $this->calcular_ancho($parametros['anchos'], $parametros['ancho']);
		
		$parametros['usarParImpar'] = false;
		$parametros['altoPorLinea'] = ($this->GetFontSize() * $this->getCellHeightRatio()) + (2 * $cellMargins['T']);
		$parametros['cantidad_lineas'] = 0;
		
    	$CF = &$parametros['par_impar']; // color de fondo
    	
    	if (is_array($parametros['par_impar']) && count($parametros['par_impar']) > 1){
    		$CF[0] = $this->HexToRGB($CF[0]);
    		$CF[1] = $this->HexToRGB($CF[1]);
    		$parametros['usarParImpar'] = true;
    	}
    	
    	$suma_anchos = array_sum($parametros['anchos']);
    	if (ceil($suma_anchos) < ceil($parametros['ancho'])){
    		$ancho_ultima_columna = &$parametros['anchos'][count($parametros['anchos']) - 1];
			$ancho_ultima_columna = $ancho_ultima_columna + ($parametros['ancho'] - $suma_anchos);
    	}
    	
		foreach($data as $row){
			$this->lineaTabla($row, $parametros);
		}
		 
		$this->Cell($parametros['ancho'],0,'','T');  //last bottom border
    }
    
    public function lineaTabla($row, &$parametros, $cabecera = false){
    	$rowcount = $i = 0;
		$lineas = 1;
		$startY = $this->GetY();
		
		foreach($row as $valor){
			$lineas = $this->getNumLines($valor, $parametros['anchos'][$i++]);
			if ($lineas > $rowcount){
				$rowcount = $lineas; 
			}
		}
		
		if (($startY + $rowcount * ($parametros['altoPorLinea'] * 2)) + $this->dimensions['bm'] > ($this->dimensions['hk'])) {
			//this row will cause a page break, draw the bottom border on previous row and give this a top border
			//we could force a page break and rewrite grid headings here
			
			if ($this->hasBorder){
				$this->hasBorder = false;
			} else {
				$this->Cell($parametros['ancho'],0,'','T'); //draw bottom border on previous row
				$this->Ln();
				$this->AddPage();
				
				$this->lineaTabla($parametros['cabecera'], $parametros, true);
			}
			$borders = 'LTR';
		} elseif ((ceil($startY) + $rowcount * $parametros['altoPorLinea']) + $this->dimensions['bm'] == floor($this->dimensions['hk'])) {
			//fringe case where this cell will just reach the page break
			//draw the cell with a bottom border as we cannot draw it otherwise
			$borders = 'LRB';	
			$this->hasBorder = true; //stops the attempt to draw the bottom border on the next row
		} else {
			//normal cell
			//$borders = $parametros['cantidad_lineas'] === 0 ? 'LTR' : 'LR';
			$borders = $parametros['cantidad_lineas'] === 0 ? 'LTR' : 'LR';
			
			if ($parametros['cantidad_lineas'] === 0){
				$borders = 'LTR';
				if ($cabecera === false){
					$this->lineaTabla($parametros['cabecera'], $parametros, true);
				}
			}else{
				$borders = $cabecera === false ? 'LR' : 'LTR';
			}
		}
	 	
	 	if ($parametros['usarParImpar']){
	 		$CF = &$parametros['par_impar']; // color de fondo
	 		$fondo = $parametros['cantidad_lineas'] % 2;
	 		//$this->SetFillColorArray($CF[$fondo]);
	 		$this->color($CF[$fondo]);
	 	}
	 	
	 	
		//now draw it
		$alto = $rowcount * $parametros['altoPorLinea'];
		$i = 0;
		
		foreach($row as $valor){
			$this->MultiCell($parametros['anchos'][$i++], $alto, $valor, $borders, 'L', 1, 0);
		}
		$parametros['cantidad_lineas']++;
	 
		$this->Ln();
    }
    
    public function HexToRGB($hex) {
		if (!preg_match('/^#(?:(?:[a-f\d]{3}){1,2})$/i', $hex)) {
			return array();
		}
		
		if (isset($this->_color_cache[$hex]))
			return $this->_color_cache[$hex];
			
		$i = strlen($hex) === 4 ? 1 : 2;
		
		return $this->_color_cache[$hex] = array(
			hexdec(substr($hex, 1, $i)),
			hexdec(substr($hex, $i * 1 + 1, $i)),
			hexdec(substr($hex, $i * 2 + 1))
		);
	}
	
	public function RGBToHex($r, $g, $b) {
		return "#" . str_pad(dechex($r), 2, "0", STR_PAD_LEFT) . str_pad(dechex($g), 2, "0", STR_PAD_LEFT) . str_pad(dechex($b), 2, "0", STR_PAD_LEFT);
	}
    
    public function pdfConf($id, $val = null){
		if (is_array($id)){
			$this->pdfConf = array_merge($this->pdfConf, $id);
			return $this;
		}
		
		if (is_null($val)){
			return isset($this->pdfConf[$id]) ? $this->pdfConf[$id] : null;
		}
		
		$this->pdfConf[$id] = $val;
		
		return $this;
	}
	
	public function salida(){
		$this->lastPage();
		$this->Output('Reporte_' . $this->generarId() . '.pdf', 'I');
		return $this;
	}
	
	protected function generarId(){
		return md5(uniqid(rand(), true));
	}
	
	public function autoPrint($dialogo = false){
		//$this->IncludeJS('print(' . ($dialogo ? 'true' : 'false') . ');');
		$cod = 'var pp = this.getPrintParams();
		pp.interactive = pp.constants.interactionLevel.automatic;
		this.print(pp);
		window.close();';
		
		$this->IncludeJS($cod);
	}
	
	public function autoPrintToPrinter($servidor, $impresora, $dialogo = false){
		$cod = 'var pp = getPrintParams();
		pp.interactive = pp.constants.interactionLevel.' . ($dialogo ? 'full' : 'automatic') . ';
		pp.printerName = \'\\\\\\\\' . $servidor . '\\\\' . $impresora . '\';
		print(pp);
		';
		
		$this->IncludeJS($cod);
	}
}
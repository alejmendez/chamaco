<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

include_once(BASEPATH . 'libraries/html2pdf/html2pdf.class' . EXT);

class html_pdf extends HTML2PDF{
	public $pdfConf = array(
		'orientacion' => 'P', //'L' o 'P' landscape (horizontal) or portrait (vertical) orientation
		'unidad' => 'mm',
		'formato' => 'Letter', //A4, A5, Letter o array(ancho, alto)
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
	
	//public function __construct($orientation = 'P', $format = 'A4', $langue='fr', $unicode=true, $encoding='UTF-8', $marges = array(5, 5, 5, 8))
	public function __construct($conf = array()) {
		set_time_limit(0);
		
		$this->ci = &get_instance();
		$this->pdfConf($conf);
		
		parent::__construct($this->pdfConf['orientacion'], $this->pdfConf['formato'], $this->pdfConf['language'], $this->pdfConf['unicode'], $this->pdfConf['encoding'], $this->pdfConf['marges']);
		
		//$this->setFontSubsetting(false);
		
        $this->pdf->SetCreator($this->pdfConf['creator']);
		$this->pdf->SetAuthor($this->pdfConf['author']);
		$this->pdf->SetTitle($this->pdfConf['title']);
		$this->pdf->SetSubject($this->pdfConf['subject']);
		$this->pdf->SetKeywords($this->pdfConf['keywords']);
		
		//$this->pdf->SetDisplayMode('fullpage');
		$this->pdf->SetDisplayMode('fullwidth', 'OneColumn');
		
		//$this->setModeDebug();
        $this->setDefaultFont('Arial');
		
		//$this->dimensions = $this->getPageDimensions();
		//$this->margenes = $this->getMargins();
		
    	//$this->anchoInternoPagina = $this->dimensions['wk'] - $this->margenes['left'] - $this->margenes['right'];
    	//$this->altoInternoPagina = $this->dimensions['hk'] - $this->margenes['top'] - $this->margenes['bottom'];
	}
	
	function generar($archivo, $variables = array(), $salida = true){
		ob_start();
		ini_set('memory_limit', '512M');
		
		$ci = &get_instance();
		$variables = array_merge(array('ci' => $ci), $variables);
		$this->ci->load->view($archivo, $variables);
	
		$this->writeHTML(ob_get_clean());
		
		if ($salida)
			$this->salida();
		
		return $this;
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
	
	public function autoPrint($dialogo = false){
		//$this->IncludeJS('print(' . ($dialogo ? 'true' : 'false') . ');');
		$cod = 'var pp = this.getPrintParams();
		pp.interactive = pp.constants.interactionLevel.automatic;
		this.print(pp);
		window.close();';
		
		$this->pdf->IncludeJS($cod);
		return $this;
	}
	
	public function autoPrintToPrinter($servidor, $impresora, $dialogo = false){
		$cod = 'var pp = getPrintParams();
		pp.interactive = pp.constants.interactionLevel.' . ($dialogo ? 'full' : 'automatic') . ';
		pp.printerName = \'\\\\\\\\' . $servidor . '\\\\' . $impresora . '\';
		print(pp);
		';
		
		$this->pdf->IncludeJS($cod);
		return $this;
	}
	
	function salida(){
		$this->Output('Reporte_' . rand(0,99999) . '.pdf');
		exit;
	}
}
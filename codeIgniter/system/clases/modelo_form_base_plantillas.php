<?php
class objinput extends objs{
	public $plantilla = "<input id=\"{id}\" name=\"{nombre}\" class=\"{clase}\" style=\"{estilo}\" value=\"{valor}\" title=\"{titulo}\" type=\"{type}\" placeholder=\"{placeholder}\" {min} {max} {readonly} {deshabilitar} {html} />";
	public $min = 0;
	public $max = 0;

	public $readonly = false;
	public $deshabilitar = false;

	function hacer($salidabool = false, $salidaTexto = true, $array = array()){
		$array = array_merge(array(
			'min' 		=> ($this->min > 0 ? 'minlength="' . $this->min . '"' : ''),
			'max' 		=> ($this->max > 0 ? 'maxlength="' . $this->max . '"' : ''),
			'readonly' 	=> ($this->readonly !== false ? 'readonly="readonly"' : ''),
			'deshabilitar' 	=> ($this->deshabilitar !== false ? 'disabled="disabled"' : '')
		), $array);

		return parent::hacer($salidabool, $salidaTexto, $array);
	}
}

class objtexto extends objinput{
	public $type = 'text';
}

class objpassword extends objinput{
	public $type = 'password';
}

class objspinner extends objtexto{
	public $plantilla = "<input id=\"{id}\" name=\"{nombre}\" class=\"{clase}\" style=\"{estilo}\" value=\"{valor}\" title=\"{titulo}\" type=\"{type}\" {html} />";

	public $jQuery = '$("{selector}").kendoNumericTextBox({opciones});';
	public $jQueryDestruir	= '$("{selector}").kendoNumericTextBox("destroy");';

	public $valor = 0;
	public $max = false;
	public $min = 0;
	public $decimales = false;
	
	public $usaUI = false;
	
	function hacer($salidabool = false, $salidaTexto = true, $array = array()){
		if (intval($this->min) > intval($this->valor))
			$this->valor = (int) $this->min;

		return parent::hacer($salidabool, $salidaTexto, $array);
	}

	function jQuery(){
		return '$("{selector}").kendoNumericTextBox('.$this->jsOpciones().');';
	}

	function jsOpciones(){
		$opciones = array(
			'decimals' 	=> $this->decimales === false ? '' : $this->decimales,
			'max' 		=> $this->max === false ? '' : $this->max,
			'min' 		=> $this->min === false ? '' : $this->min
		);

		foreach($opciones as $ll => $v){
			if (is_string($v) && trim($v) == '')
				unset($opciones[$ll]);
		}

		return json_encode($opciones);
	}
}

class objoculto extends objinput{
	public $type = 'hidden';
	public $contenedorObjs = '';
	public $usaContenedor = false;
	public $cambiarTamano = false;
	public $usaUI = false;
}

class objnumeroAleatorio extends objoculto{
	public $valor = 0;

	function __construct($propiedades){
		parent::__construct($propiedades);

		$this->cambiarPropiedad('clase', '+numeroAleatorio');
	}
}

class objnumerico extends objtexto{
	public $jQuery = '$("{selector}").numeric({allow:"."});';
	public $archJs = array("jquery.alphanumeric.js");
	public $valor = 0;
}

class objcedula extends objtexto{
	public $jQuery = '
		$("{selector}").numeric()
		.blur(function(){
			var $cedvar = number_format($.trim($(this).val()).replace(/\.*/g, ""), 0, ",", ".");
			$(this).val(($cedvar == 0 ? "" : $cedvar));
		});';
	public $archJs = array("jquery.alphanumeric.js");
}

class objcorreo extends objtexto{
	function hacer($salidabool = false, $salidaTexto = true, $array = array()){
		$this->cambiarPropiedad("clase", "+email");

		return parent::hacer($salidabool, $salidaTexto, $array);
	}
}

class objtextoArea extends objs{
	public $plantilla = '<textarea id="{id}" name="{nombre}" class="{clase}" style="{estilo}" cols="3" rows="3" title="{titulo}" {min} {max} {readonly} {html} >{valor}</textarea>';

	public $min = 0;
	public $max = 65536; // 2 ^ 16 = tama�o de un campo tipo texto

	public $type = 'textarea';

	public $readonly = false;

	function hacer($salidabool = false, $salidaTexto = true, $array = array()){
		$array = array(
			'min' 		=> ($this->min > 0 ? 'minlength="' . $this->min . '"' : ''),
			'max' 		=> ($this->max > 0 ? 'maxlength="' . $this->max . '"' : ''),
			'readonly' 	=> ($this->readonly !== false ? 'readonly="readonly"' : '')
		);
		return parent::hacer($salidabool, $salidaTexto, $array);
	}
}

class objmceTexto extends objtextoArea{
	public $jQuery = '$("{selector}").tinymce(tinymceOpciones);';
	public $jQueryDestruir	= '$("{selector}").tinymce("remove");';
	public $archJs = array('tiny_mce/jquery.tinymce.min.js', 'tiny_mce/tinymce.min.js');
	public $limpiarXss = false;

	function __construct($propiedades){
		parent::__construct($propiedades);

		$this->cambiarPropiedad("clase", "+tinymce");
	}
}

class objeditor extends objtextoArea{
	public $jQuery = '$("{selector}").kendoEditor();';
	public $jQueryDestruir	= '$("{selector}").kendoEditor("remove");';
	public $limpiarXss = false;

	function __construct($propiedades){
		parent::__construct($propiedades);
	}
	
	public function valor($valor = false, $usadb = false){
		if ($valor !== false){
			$valor = html_entity_decode($valor);
		}
		
		return parent::valor($valor, $usadb);
    }
}


class objcombo extends objs{
	public $plantilla = '<select id="{id}" name="{nombre}" class="{clase}" style="{estilo}" title="{titulo}" {multiple} {html} >{valor}</select>';
	//public $borde = 'ui-corner-left';
	
	public $archJs = array('bootstrap/bootstrap-select.min.js');
	public $archCss = array('bootstrap/bootstrap-select.min.css');
	
	public $jQuery = '$("{selector}").selectpicker({opciones});';
	
	public $multiple = false;
	public $valor = 0;
	public $type = 'select';
	public $valorArray = array();
	public $valorSeleccione = true;
	public $titulo = 'Seleccione...';
	//public $claseUi = '';
	
	function __construct($propiedades){
		parent::__construct($propiedades);

		if ($this->multiple !== false){
			$this->nombre .= '[]'; //se agrega los corchetes de forma automatica.
		}

		//$this->valorInicial = "";
	}
	
	function jQuery(){
		return '$("{selector}").selectpicker('.$this->jsOpciones().');';
	}

	function jsOpciones(){
		$opciones = array(
			//'title' 	=> $this->optionLabel
		);

		foreach($opciones as $ll => $v){
			if (is_string($v) && trim($v) == '')
				unset($opciones[$ll]);
		}

		return json_encode($opciones);
	}

	function hacer($salidabool = false, $salidaTexto = true, $array = array()){
		$array = array(
			'multiple' 	=> ($this->multiple !== false ? 'multiple="multiple"' : ' ')
		);

		if (($this->valor === 0 || $this->valor === '') && (is_array($this->valorFuente) || is_object($this->valorFuente))){
			$this->valor($this->valorFuente);
		}

		$salida = str_replace('<option >', '<option value="">', parent::hacer(true, $salidaTexto, $array));

		if ($salidabool === false)
			echo $salida;
		else
			return $salida;

		return true;
	}

	function valor($valor = false, $usadb = false){
		if ($this->enviado === false && (is_array($valor) || is_object($valor))){
			$valorAnterior = $this->valorInicial;

    		$this->valor = $this->comboBox($valor);
    		if ($valorAnterior == 0 || $valorAnterior == ''){
    			$this->valorInicial = $this->valor;
    		}
   		}else{
   			return parent::valor($valor, $usadb);
		}

    	return true;
    }

    function comboBox($etiqueta, $seleccionado = '', $retornaArray = false){
    	$this->valorFuente($etiqueta);

		if ($retornaArray === true){
			return $this->valorArray;
		}

    	if (!empty($this->valorArray))
    		$etiqueta = $this->valorArray;
		
		$s = '';
		//$s = ($this->valorSeleccione !== false and $this->multiple === false) ? '<option value="">'.($this->valorSeleccione === true ? '- Seleccione' : '') . '</option>' : '';

		foreach($etiqueta as $ll => $v){
			$s .= '<option value="'.$ll.'"'.($ll === $seleccionado ? ' selected="selected"' : '').' >' . $v . '</option>';
		}
		
		if ($s === ''){
			$s = ($this->valorSeleccione !== false and $this->multiple === false) ? '<option value="">'.($this->valorSeleccione === true ? '- Seleccione' : '') . '</option>' : '';
		}

		return $s;
	}

	function valorFuente($rs){
		if (is_object($rs) && strrpos(get_class($rs), 'ADORecordSet') !== false){
			$rsObj = $rs;

			$rsObj->MoveFirst();

			$rs = $valores = array();
			$cantidadCampos = $rsObj->FieldCount() > 1 ? 1 : 0;

			foreach($rsObj as $row){
				$row = array_values($row);
				$rs[$row[0]] = $row[$cantidadCampos];
			}

			return $this->valorArray = $rs;
		}else{
			return $this->valorArray = (is_array($rs) ? $rs : array());
		}
	}
}
class objlista extends objcombo{
	public $multiple = true;
}

class objcomboAutoCompletado extends objcombo{
	public $jQuery = '$("{selector}").combobox({opciones});';
	public $jQueryDestruir	= '$("{selector}").combobox("destroy");';
	public $archJs = array('jquery.autocomplete.combobox.js');
	public $archCss = array('jquery.autocomplete.combobox.css');
}

class objuiCombo extends objcombo{
	public $jQuery = '$("{selector}").selectmenu({opciones});';
	public $jQueryDestruir	= '$("{selector}").selectmenu("destroy");';
	public $archJs = array('jquery.ui.selectmenu.js');
	public $archCss = array('jquery.ui.selectmenu.css');
}

class objselecta extends objcombo{
	public $jQuery = '$("{selector}").jqSelecta({opciones});';
	public $jQueryDestruir	= '$("{selector}").jqSelecta("destroy");';
	public $archJs = array('jqSelecta.js');

	public $multiple = true;
}

class objmultiselect extends objcombo{
	public $jQuery = '$("{selector}").kendoMultiSelect({opciones});';

	public $multiple = true;
	
	function jQuery(){
		return '$("{selector}").kendoMultiSelect('.$this->jsOpciones().');';
	}
}

class objarchivo extends objinput{
	public $file = array(
			'ruta' => 'archivos/',
			'ext' => array('*'),
			'tm' => 10,
			'nomorg' => false
		);
	public $type = 'file';

	function nombreDB($nombreDB){
		$this->nombreDB = $nombreDB;
		if (strrpos($this->nombreDB, ':') === false){
			$this->nombreDB .= ':file';
		}
	}
}

class objjupload extends objs{
	public $validar 			= false;
	public $cambioValidacion 	= false;
	public $contenedorObjs 	= '';
	public $dbActivo 			= false;
	public $usaContenedor 		= false;
	public $usarContexto		= false;
	public $usaUI 				= false;
	public $actualizable		= false;
	public $contenerorJquery	= false;
	public $sinEtiqueta		= true;
	
	public $jQuery 	= '$("{selector}").objFormUpload({opciones});';
	public $jQueryDestruir	= '$("{selector}").objFormUpload("destroy");';
	public $archCss 	= array('jquery.fileupload.css', 'jquery.fileupload-ui.css');
	public $archJs 	= array(
		'jquery.json-2.3.min.js', 
		'jquery.iframe-transport.js', 
		'load-image.min.js', 
		'canvas-to-blob.min.js', 
		'tmpl.min.js', 
		'jquery.fileupload.js', 
		'jquery.fileupload-process.js', 
		'jquery.fileupload-image.js', 
		'jquery.fileupload-audio.js', 
		'jquery.fileupload-video.js', 
		'jquery.fileupload-validate.js',
		
		'jquery.fileupload-ui.js', 
		'jquery.fileupload-jquery-ui.js', 
		'jquery.fileupload-validate.js',
		'jquery.objForm.upload.js'
	);

	public $plantilla = '
		<form id="{id}" action="{url}" method="POST" enctype="multipart/form-data" class="{clase}" style="{estilo}" title="{titulo}" {html} >
			<div class="fileupload-buttonbar">
		        <div class="fileupload-buttons">
		            <span class="fileinput-button">
		                <span>Agregar Archivos...</span>
		                <input type="file" name="files[]" multiple>
		            </span>
		            <button type="button" class="delete">Eliminar Archivos</button>
		            <input type="checkbox" class="toggle">
		            
					<span class="fileupload-loading"></span>
		        </div>
		        
				<div class="fileupload-progress fade" style="display:none">
		            <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100"></div>
		            <div class="progress-extended">&nbsp;</div>
		        </div>
		    </div>
		    		    
		    <table role="presentation"><tbody class="files"></tbody></table>
		</form>
	';
}

class objsubirimagen extends objjupload{
	public $usaContenedor 		= true;
	
	public $texto = 'Subir Imagen';
	public $imagen = 'images/user.png';

	public $jQuery 	= '$("{selector}").objFormUploadImagen({opciones});';
	public $jQueryDestruir	= '$("{selector}").objFormUploadImagen("destroy");';
	
	public $archCss 	= array('jquery.fileupload-ui.css', 'jquery.fileuploadImagen-ui.css');
	public $archJs 	= array('jquery.json-2.3.min.js', 'jquery.iframe-transport.js', 'load-image.min.js', 'canvas-to-blob.min.js', 'tmpl.min.js', 'jquery.fileupload.js', 'jquery.fileupload-fp.js', 'jquery.fileupload-ui.js', 'jquery.fileupload-jui.js', 'jquery.image-gallery.min.js', 'jquery.objForm.upload.imagen.js');

	public $plantilla = '
		<div class="fileupload-content {clase}" style="{estilo}">	
	    	<img id="{id}_imagen" src="{imagen}" width="{ancho}" height="{alto}" />
	    	<label class="fileinput-button ui-widget ui-state-default ui-corner-all">
	            <span>{texto}</span>
	            <input id="{id}" type="file" multiple="multiple" name="files" />
	        </label>
	        
	        <!-- <div class="fileupload-progress fade">
				<div class="progress progress-success progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
					<div class="bar" style="width:0%;"></div>
				</div>
			</div>
			
	        <div class="fileupload-loading"></div> -->
	    </div>';

	function hacer($salidabool = false, $salidaTexto = true, $array = array()){
		$array = array_merge(array(
			'imagen' 	=> $this->imagen,
			'ancho' 	=> $this->ancho,
			'alto' 		=> $this->alto
		), $array);

		return parent::hacer($salidabool, $salidaTexto, $array);
	}
}

class objsubmit extends objinput{
	public $type = 'submit';
	public $jQuery = '$("{selector}").button({opciones});';
	public $jQueryDestruir	= '$("{selector}").button("destroy");';
	public $cambioValidacion = false;
	public $actualizable = false;
	public $usaUI = true;
	public $claseUi = 'k-button';
}

class objboton extends objsubmit{
	public $type = 'button';
}

class objbutton extends objsubmit{
	public $plantilla = '<button id="{id}" name="{nombre}" class="{clase}" style="{estilo}" title="{titulo}" {html} >{valor}</button>';
}

class objcheck extends objinput{
	public $plantilla = '<input id="{id}" name="{nombre}" class="{clase}" style="{estilo}" value="{valor}" title="{titulo}" type="{type}" {check} {min} {max} {deshabilitar} {readonly} {html} />';
	public $type = 'checkbox';
	public $check = false;
	public $usaUI = false;
	public $cambiarTamano = false;

	function hacer($salidabool = false, $salidaTexto = true, $array = array()){
		$array = array(
			'check' => ($this->check !== false ? 'checked="checked"' : '')
		);

		return parent::hacer($salidabool, $salidaTexto, $array);
	}
	
	public function getNombre($salida = true){ //Salida del nombre del objeto
		if ($this->sinEtiqueta === true) return '';
		
		if (substr($this->texto, -1, 1) == '@') return '';
		if ($salida) return (trim($this->texto) != '' ? "\n<label for=\"".$this->id."\" class=\"labelObj\">\n" . $this->texto . "\n</label>" : '');

		return $this->texto;
	}
}

class objopcion extends objcheck{
	public $type = 'radio';
}

class objdiv extends objs{
	public $cambioValidacion = false;
	public $plantilla = '<div id="{id}" class="{clase}" style="{estilo}" title="{titulo}" {align} {html} >{valor}</div>';

	public $align = false;
	public $actualizable = false;
	public $valorDeFuente = false;

	function hacer($salidabool = false, $salidaTexto = true, $array = array()){
		$array = array(
			'align' => ($this->align !== false ? 'align="'.$this->align.'"' : '')
		);

		if (trim($this->valor) == '' || $this->valor === false)
			$this->valor = '&nbsp;';

		return parent::hacer($salidabool, $salidaTexto, $array);
	}
}

class objradioBoton extends objdiv{
	public $tipoElemento = 'objopcion';

	public $cambioValidacion = true;
	public $jQuery = '$("{selector}").buttonset({opciones});';
	public $jQueryDestruir	= '$("{selector}").buttonset("destroy");';
	public $usaUI = false;
	public $actualizable = true;
	
	public function __construct($propiedades){
		parent::__construct($propiedades);
		if (strpos($this->nombre, '[]') === false){
			$this->nombre .= '[]';
		}
	}
	
	function hacer($salidabool = false, $salidaTexto = true, $array = array()){
		$this->hacerElementos('objcheck');

		return parent::hacer($salidabool, $salidaTexto);
	}

	private function hacerElementos($tipo){
		if ($this->valorFuente != ''){
			$salida = '';
			$plantilla = new $this->tipoElemento(array(
				'id' => $this->id . '_' . md5(uniqid(rand(), true)),
				'tipo' => 'check',
				'nombre' => $this->nombre,
				'usaUI' => false,
				'contenedorObjsClase' => '',
				'contenedorObjs' => ''
			));

			$plantilla->dbActivo = true;

			foreach($this->valorFuente as $ll => $v){
				$v1 = $v2 = current($v);
				if (count($v) > 1)
					$v2 = next($v);

				$plantilla
				->valor($v1)
				->cambiarPropiedad(array(
					'id' => $this->id . '_' . md5(uniqid(rand(), true)),
					'texto' => utf8_decode($v2)
				));

				$salida .= $plantilla->hacer(true);
			}

			$this->valor = $salida;
		}
	}
}

class objcheckBoton extends objradioBoton{
	public $tipoElemento = 'objcheck';
}

class objdialog extends objdiv{
	//public $estilo = 'display:none; ';
	//public $jQuery = '$("{selector}").dialog({opciones});';
	public $jQueryDestruir	= '$("{selector}").dialog("destroy");';
	public $contenedorObjs = '';
	public $usaContenedor = false;
	public $cambiarTamano = false;
	public $actualizable = false;
	public $opciones = array();
	public $usaUI = false;

	public $ancho = 900;
	public $alto = 500;

	public $contenerorJquery = false;

	function jQuery(){
		return '$("{selector}").dialog('.$this->jsOpciones().');';
	}

	function jsOpciones(){
		$opciones = array(
			'width' 	=> $this->ancho,
			'height' 	=> $this->alto
		);

		return json_encode(array_merge($opciones, $this->opciones));
	}
}

class objpuntuacion extends objdiv{
	public $jQuery = '$("{selector}").rating({opciones});';

	public $actualizable = true;
	public $archCss = array('jquery.rating.css');
	public $archJs = array('jquery.rating.pack.js');
	public $usaUI = false;
	public $valorFuente = 10;
	
	function __construct($propiedades){
		parent::__construct($propiedades);

		$this->cambiarPropiedad('clase', '+puntuacion');
	}

	function valor($valor = false, $usadb = false){
		if(is_int($this->valorFuente)){
			$this->valor = "";
			$plantilla = new objopcion(array(
				"id" => "puntuacion1",
				"nombre" => $this->id,
				"tipo" => "opcion",
				"clase" => "puntuacionOpcion",
				"usaUI" => false,
				'contenedorObjs' => '',
				'contenedor' => '',
				"sin" => "id"
			));

			$plantilla->dbActivo = true;

			for($i = 1; $i <= $this->valorFuente; $i++){
				$this->valor .= $plantilla->valor($i)->hacer(true);
			}
			
			$this->js = ".puntuacionOpcion";
		}
		
		return parent::valor($this->valor, $usadb);
	}
}

class objtelefono extends objtexto{
	public $jQuery = '$("{selector}").kendoMaskedTextBox({ mask: "9999-999-9999" });';
	//public $archJs = array('jquery.maskedinput-1.2.2.min.js');
}

class objemail extends objtexto{
	public $validar = 'email';
}

class objfecha extends objtexto{
	public $fecha = true;
	public $readonly = true;

	public $formato = 'd/m/Y';
	public $formatoDB = 'Y-m-d';

	public $jQuery = '$("{selector}").datepicker({opciones});';
	public $jQueryDestruir	= '$("{selector}").datepicker("destroy");';
}

class objfechahora extends objtexto{
	public $fecha = true;
	public $readonly = true;

	public $formato = 'd/m/Y H:i:s';
	public $formatoDB = 'Y-m-d H:i:s';

	public $jQuery = '$("{selector}").datetimepicker({opciones});';
	public $jQueryDestruir	= '$("{selector}").datetimepicker("destroy");';
}

class objhora extends objtexto{
	public $jQuery = '$("{selector}").timepicker({opciones});';
	public $jQueryDestruir	= '$("{selector}").timepicker("destroy");';
}

class objfechaRango extends objfecha{
	public $readonly = false;
	public $jQuery = '$("{selector}").datepickerrange({opciones});';
	public $jQueryDestruir	= '$("{selector}").datepickerrange("destroy");';
	public $archJs = array('jquery.datepicker.range.js');
	public $separador = '-';

	function __construct($propiedades){
		parent::__construct($propiedades);

		$pos = strrpos($this->nombreDB, ":");
		if ($pos !== false )
			$this->propiedad('nombreDB', substr($this->propiedad('nombreDB'), 0, $pos));
	}

	function valor($valor = false, $usadb = false){
		$retornar = $valor === false ? true : false;
		parent::valor($valor, $usadb);

		$condicion = is_array($this->valor) ? false : (strpos($this->valor, $this->separador) !== false);

		if ($condicion){
			$this->valor = array_map("trim", explode($this->separador, $this->valor));

			if (count($this->valor) == 1 or $this->valor[0] === $this->valor[1])
				$this->valor = current($this->valor);
		}

		if ($retornar)
    		return $this->valor;

		return $this;
	}
}

class objfechaInput extends objtexto{
	public $jQuery = '$("{selector}").datepicker({opciones}).mask("99/99/9999");';
	public $jQueryDestruir	= '$("{selector}").datepicker("destroy").unbind().remove();';
}

class objimagen extends objs{
    public $src = '';
    public $ruta = '';
    public $actualizable = false;
	public $valorDeFuente = false;
    public $cambioValidacion = false;
    public $validar = false;
    public $usaUI = false;

	public $plantilla = "<img id=\"{id}\" src=\"{src}\" name=\"{nombre}\" class=\"{clase}\" style=\"{estilo}\" title=\"{titulo}\" {html} />";
    function hacer($salidabool = false, $salidaTexto = true, $array = array()){
        $array = array_merge(array(
			"src" 		=> trim($this->src)
		), $array);

		return parent::hacer($salidabool, $salidaTexto, $array);
	}

	function valor($valor = false, $usadb = false){
		$this->src = $valor = $this->ruta . $valor;
		parent::valor($valor, $usadb);

    	return $this;
    }
}

/*
 modo de uso:
	array(
		'id' => 'tabla',
		'tipo' => 'tabla',
		'valor' => array(
			'Instruccion' => '40%',
			'Nombre' => '13%',
			'Apellido' => '13%',
			'Cedula' => '8%',
			'Municipio' => '15%',
			'Fecha' => '11%'
		),
		'js' => array('id',  '"' . $url . '?' . accionForm . '=dataTable"')
	)
*/


class objtabla extends objs{
	public $plantilla = '
	<div class="ui-widget">
		<table id="{id}" class="{clase}" cellspacing="0" width="100%" border="0" title="{titulo}" {html} >
			<thead>
				<tr>
					{valor}
				</tr>
			</thead>
			<tbody>{valores}</tbody>
		</table>
	</div>';

	public $borde = '';
	public $cambioValidacion = false;
	public $actualizable = false;

	public $contenedorObjs = '';
	public $usaContenedor = false;
	public $cambiarTamano = false;

	public $usaUI = false;
	public $cuerpo = '';
	public $columnas = array();
	public $campos = array();
	public $anchos = array();
	public $valor = '';
	public $valores = '&nbsp;';
	public $jQuery = 'iniTablaObj($("{selector}"), {opciones});';
	public $jQueryDestruir	= 'oTable["{selector}"].fnDestroy();';

	public $archCss = array('datatable/dataTables.bootstrap.css');
	public $archJs = array('datatable/jquery.dataTables.min.js', 'datatable/dataTables.bootstrap.js');

	public $url = '';
	public $accion = 'dataTable';
	public $idFormulario = 'dataTable';
	public $datos = array();
	public $json = '';
	public $funcionBuscar = '';
	public $funcion = '';
	public $sinServ = false;

	public $opciones = array();

	public $contenerorJquery = false;
	public $contenedorObjsClase 	= 'col-lg-12';

	function hacer($salidabool = false, $salidaTexto = true, $array = array()){
		$v = false;
		$this->procesarValores($v);

        $array = array_merge(array(
			'valores' 		=> $this->valores
		), $array);

		return parent::hacer($salidabool, $salidaTexto, $array);
	}

	private function procesarValores(&$rs){
		if (!($rs === false)){
			$this->valores = $rs;
		}

		if (!(is_array($this->valores) || is_object($this->valores))){
			$this->valores = '&nbsp;';
			return false;
		}

		$this->valores = '';
		return true;
	}

	function campos($campos = null){
		if (!is_null($campos)){
			$this->campos = $campos;
			return $this;
		}

		return $this->campos;
	}

	function form($form){
		$this->columnas = array();
		$this->idFormulario = $form->id();

		if (empty($this->campos) && empty($this->valor)){
			foreach($form->objs() as $ll => $v){
				if ($v->propiedad('tabla') !== false){
					$objs[] = $ll;
				}
			}
		}elseif(!empty($this->campos)){
			$objs = $form->selector($this->campos);
		}elseif(is_array($this->valor)){
			$objs = array_keys($this->valor);
		}else{
			return false;
		}

		$this->campos = $objs;
		$valorTabla = array();
		$anchoTotal = 0;
		$i = 0;

		foreach($objs as $v){
			if ($v->id() == $this->id)
				continue;

			$nombre = trim(str_replace(':', '', $v->propiedad('texto')));
			$ancho = $v->propiedad('tabla') !== false ? (int) $v->propiedad('tabla') : 0;
			if ($ancho === 0 && isset($this->anchos[$i]))
				$ancho = (int) $this->anchos[$i];
			$anchoTotal += $ancho;

			$this->columnas[] = $nombre;
			$valorTabla[$nombre] = $ancho;

			$i++;
		}

		$anchoTotal = abs(100 - $anchoTotal);
		$cantidad = 0;

		foreach($valorTabla as $ll => $v){
			if ($v == 0) $cantidad++;
		}

		if ($cantidad > 0){
			$anchoTotal = floor($anchoTotal / $cantidad);
			foreach($valorTabla as $ll => $v){
				if ($v == 0)
					$valorTabla[$ll] = $anchoTotal;
			}
		}

		$this->valor($valorTabla);
	}

	function jQuery(){
		return 'iniTablaObj($("{selector}"), '.$this->jsOpciones().');';
	}

	function jsOpciones(){
		if ($this->sinServ === true){
			$opciones = array(
				'bJQueryUI' 	=> true,
			);

			return "oTable['#".$this->id."'] = $('{selector}').dataTable(".$this->json_encode(array_merge($opciones, $this->opciones)).");";
		}

		$opciones = array(
			'url' 			=> $this->url === '' ? ($this->ci->url . ($this->accion == '' ? $this->id() : $this->accion)) : $this->url,
			'json'			=> $this->json,
			'datos' 		=> array_merge($this->datos,
			array(
				'formulario' => $this->idFormulario
			)),
			'funcionBuscar'	=> $this->funcionBuscar,
			'funcion'		=> $this->funcion
		);

		foreach($opciones as $ll => $v){
			if (is_string($v) && trim($v) == '')
				unset($opciones[$ll]);
		}

		return $this->json_encode($opciones, array('funcionBuscar', 'funcion'));
	}

	function accion($accion){
		//echo "$this->accion --- $accion";
		$this->accion = (string) $accion;
		return $this;
	}

	function valor($valor = false, $usadb = false){
		if (is_array($valor))
    		$this->valor = $this->tablaValor($valor);
   		else
   			parent::valor($valor, $usadb);

    	return $this;
    }

    private function tablaValor($arr){
		$s = '';
		foreach($arr as $ll => $v)
			$s .= '<th width="' . ((int) $v) . '%">'.htmlentities($ll).'</th>';

		return $s;
	}

	function json_encode($arr, $sinComilla = array()){
		$value_arr = array();
		$replace_keys = array();

		foreach($arr as $key => &$value){
			if (is_string($value)){
				if(strpos(trim($value), 'function(') === 0 || array_search($key, $sinComilla) !== false){
					$value_arr[] = trim($value);
					$value = '%' . $key . '%';
					$replace_keys[] = '"' . $value . '"';
				}
			}
		}

		return str_replace($replace_keys, $value_arr, json_encode($arr));
    }
}

class objgrid extends objs{
	public $plantilla = '
	<table id="{id}" class="{clase}" title="{titulo}" {html} ></table>
	<div id="grid_pager_{id}"></div>
	';

	public $borde = '';
	public $cambioValidacion = false;
	public $actualizable = false;
	public $usaUI = false;

	//public $contenedorObjs = '';
	//public $usaContenedor = false;
	public $cambiarTamano = false;

	public $jQuery = '';
	public $opciones = array();
	public $navGrid = array(
		'edit' => false,
		'add' => false,
		'del' => false
	);

	public $url = false;

	public $colNames = array();
	public $colModel = array();

	public $campos = array();
	public $anchos = array();
	public $accion = 'grid';

	public $editable = false;

	public $archCss = array('ui.jqgrid.css');
	public $archJs = array('i18n/grid.locale-es.js', 'jquery.jqGrid.min.js');
	public $onSelectRow = 'buscar';

	public $contenerorJquery = false;

	public $jQueryDestruir	= '$("{selector}").jqGrid("GridDestroy");';

	function form(&$form){
		$this->colNames = $this->colModel = array();

		$tipos = array(
			'checkbox',
			'radio',
			'select',
			'textarea',
		);

		if (empty($this->campos)){
			foreach($form->objs() as $ll => $v){
				if ($v->propiedad('grid') !== false){
					$objs[] = $ll;
				}
			}
		}elseif (is_string($this->campos)){
			$objs = explode(',', $this->campos);
			$objs = array_map("trim", $objs);
		}elseif (is_array($this->campos)){
			$objs = $this->campos;
		}

		$this->campos = $objs;
		$js = '';
		$i = 0;

		foreach($objs as $v){
			$v = trim($v);
			
			$objEle = $form->objs($v);
			
			if ($v == $this->id || $objEle === false || $objEle->tipo === 'tabla')
				continue;

			$this->colNames[] = htmlentities(trim(str_replace(':', '', $objEle->propiedad('texto'))));

			$colModel = array(
				'name' => $objEle->id,
				'index' => $objEle->id,
				'width' => isset($this->anchos[$i]) ? $this->anchos[$i] : null,

				// nombre del campo, order del campo, ancho, align, sortable, editable,
				//editrules:{number:true,minValue:100,maxValue:350} , formatter
			);
			
			$i++;

			if (is_array($objEle->propiedad('grid')))
				$colModel = array_merge($colModel, $objEle->propiedad('grid'));

			if ($this->editable === true){
				$colModel = array_merge($colModel, array(
					'editable' => true,
					//'editoptions' => array('size'=>"20",'maxlength'=>"30") => input
					//editoptions:{value:"FE:FedEx;IN:InTime;TN:TNT;AR:ARAMEX"}} => select
					//editoptions:{rows:"2",cols:"10"} => textarea
					//editoptions: {value:"Yes:No"}} => checkbox
				));

				if (array_search($objEle->propiedad('type'), $tipos) !== false)
					$colModel['edittype'] = $objEle->propiedad('type');

				switch($objEle->propiedad('type')){
					case 'select':
						if ($objEle->valor() == '' && (is_array($objEle->propiedad('valorFuente')) || is_object($objEle->propiedad('valorFuente')))){
							$objEle->valor($objEle->valorFuente);
						}

						$colModel['editoptions'] = array('value' => $objEle->propiedad('valorArray'));
						break;
					case 'textarea':
						$colModel['editoptions'] = array('rows' => 2, 'cols' => 10);
						break;
					case 'checkbox':
						$colModel['editoptions'] = array('value' => '0:1');
						break;
					default:
						$colModel['editoptions']['size'] = 20;
						if ($objEle->max > 0)
							$colModel['editoptions']['maxlength'] = $objEle->propiedad('max');
						break;
				}
			}

			$this->colModel[] = $colModel;

			$jsProp = $objEle->propiedad('js');

			$js .= "\n\t\t" . plantilla(
				$objEle->jQuery(),
				array(
					'selector' => '#' . $objEle->id() . '_' . $this->id(),
					'opciones' => is_array($jsProp) ? $jsProp[1] : '{}'
				)
			) . "\n";
		}

		$onSelectRow = 'function(id){
			$grid = $(this);

			if(id && id!==$grid.data("ultimoSeleccionado")){
				$grid.data("ultimoSeleccionado", id);

				$grid.jqGrid("restoreRow",$grid.data("ultimoSeleccionado"));
				$grid.jqGrid("editRow",id,{
					"keys" : true,
					"successfunc" : function(idele){
						' . $js . '
					},
				    "extraparam" : {
		    	 		"' . accionForm . '" : "' . $this->accion . '"
				    }
				});
			}
		}';

		$this->onSelectRow = $onSelectRow;
	}

    function jQuery(){
    	$opciones = $this->jsOpciones(1);
    	//echo "//--------" . $opciones;
		$opcionesJson = $this->json_encode($opciones, array('onSelectRow'));

		$jQuery = '
		$("{selector}").jqGrid('.$opcionesJson.')
		' . (isset($opciones['pager']) ? '.navGrid("'.$opciones['pager'].'", '.json_encode($this->navGrid).')' : '').'
		.data("ultimoSeleccionado", 0);
		';

		if ($this->editable === true)
			$jQuery .= '';
		return $jQuery;
    }

    function jsOpciones($op = 0){
		global $url;
		$opcionesConstruc = array(
			'url' 			=> $this->url === false ? $url : $this->url,
			'datatype' 		=> 'json',
			'colNames' 		=> $this->colNames,
			'colModel' 		=> $this->colModel,
			'rowNum' 		=> 10,
			'rowList' 		=> array(10,20,30),
			'pager' 		=> '#grid_pager_' . $this->id,
			'sortname' 		=> 'id',
			'viewrecords' 	=> true,
			'sortorder' 	=> 'desc',
			'multiselect' 	=> false,
			'caption' 		=> $this->titulo,
			//'rownumbers'	=> true,
			'mtype' 		=> 'post',
			'postData' 		=> array(accionForm => $this->accion),
			'loadui' 		=> 'block'
		);
		
		//$("#grid").jqGrid('setGridParam',{postData:{a:'b', b:'a'}});
		//$("#grid").trigger("reloadGrid");

		if (strpos($this->sin, 'pager') !== false){
			unset($opcionesConstruc['pager']);
		}

		if ($opcionesConstruc['url'] == ''){
			unset($opcionesConstruc['url']);
			$opcionesConstruc['datatype'] = 'local';
		}

		if ($this->alto != ''){
			$opcionesConstruc['height'] = $this->alto;
		}
		
		if ($this->ancho != ''){
			$opcionesConstruc['width'] = $this->ancho;
		}
		
		if ($this->editable === true){
			$opcionesConstruc['editurl'] = $opcionesConstruc['url'];
			$opcionesConstruc['editParams'] = array(accionForm => $this->accion);
			$opcionesConstruc['addParams'] = array(accionForm => $this->accion);
			$opcionesConstruc['onSelectRow'] = $this->onSelectRow;
		}
		
		$opciones = array_merge($opcionesConstruc, $this->opciones);

		if($op != 0){
			return $opciones;
		}

		return $this->json_encode($opciones, array('onSelectRow'));
	}

    function json_encode($arr, $sinComilla = array()){
    	if (!is_array($arr)) return $arr;

		$value_arr = array();
		$replace_keys = array();

		foreach($arr as $key => &$value){
			if (is_string($value)){
				if(strpos(trim($value), 'function(')===0 || array_search($key, $sinComilla) !== false){
					$value_arr[] = trim($value);
					$value = '%' . $key . '%';
					$replace_keys[] = '"' . $value . '"';
				}
			}
		}

		return str_replace($replace_keys, $value_arr, json_encode($arr));
    }
}

class objkgrid extends objs{
	public $plantilla = '
	<div id="{id}" class="{clase}" title="{titulo}" {html} ></div>
	';

	public $borde = '';
	public $cambioValidacion = false;
	public $actualizable = false;
	public $usaUI = false;

	//public $contenedorObjs = '';
	//public $usaContenedor = false;
	public $cambiarTamano = false;

	public $jQuery = '';
	public $opciones = array();

	public $url = false;

	public $dataSource = array();
	public $columns = array();
	public $datos = array();

	public $campos = array();
	public $anchos = array();
	public $accion = 'grid';

	public $editable = false;

	//public $archCss = array('ui.jqgrid.css');
	//public $archJs = array('i18n/grid.locale-es.js', 'jquery.jqGrid.min.js');
	public $contenerorJquery = false;

	public $jQueryDestruir	= '$("{selector}").kendoGrid("GridDestroy");';

	function form(&$form){
		$this->columns = array();
		/*
		public crudServiceBaseUrl = "http://demos.kendoui.com/service",
            dataSource = new kendo.data.DataSource({
                transport: {
                    read:  {
                        url: crudServiceBaseUrl + "/Products",
                        dataType: "jsonp"
                    },
                    update: {
                        url: crudServiceBaseUrl + "/Products/Update",
                        dataType: "jsonp"
                    },
                    destroy: {
                        url: crudServiceBaseUrl + "/Products/Destroy",
                        dataType: "jsonp"
                    },
                    create: {
                        url: crudServiceBaseUrl + "/Products/Create",
                        dataType: "jsonp"
                    },
                    parameterMap: function(options, operation) {
                        if (operation !== "read" && options.models) {
                            return {models: kendo.stringify(options.models)};
                        }
                    }
                },
                batch: true,
                pageSize: 20,
                schema: {
                    model: {
                        id: "ProductID",
                        fields: {
                            ProductID: { editable: false, nullable: true },
                            ProductName: { validation: { required: true } },
                            UnitPrice: { type: "number", validation: { required: true, min: 1} },
                            Discontinued: { type: "boolean" },
                            UnitsInStock: { type: "number", validation: { min: 0, required: true } }
                        }
                    }
                }
            });

        $("#grid").kendoGrid({
            dataSource: dataSource,
            navigatable: true,
            pageable: true,
            height: 430,
            toolbar: ["create", "save", "cancel"],
            columns: [
                "ProductName",
                { field: "UnitPrice", title: "Unit Price", format: "{0:c}", width: 110 },
                { field: "UnitsInStock", title: "Units In Stock", width: 110 },
                { field: "Discontinued", width: 110 },
                { command: "destroy", title: "&nbsp;", width: 90 }],
            editable: true
        });
		*/

		if (empty($this->campos)){
			foreach($form->objs() as $ll => $v){
				if ($v->propiedad('grid') !== false){
					$objs[] = $ll;
				}
			}
		}elseif (is_string($this->campos)){
			$objs = explode(',', $this->campos);
			$objs = array_map("trim", $objs);
		}elseif (is_array($this->campos)){
			$objs = array_map("trim", $this->campos);
		}

		$this->campos = $objs;
		
		$js = '';
		$i = -1;
		
		$camposNoPermitidos = array("tabla");
		
		$tipos = array(
			'' => 'string',
			'str' => 'string',
			'int' => 'number',
			'date' => 'date',
			'bool' => 'boolean',
		);
			
		$dataSource = array(
			'data' => $this->datos,
			'batch' => true,
			'pageSize' => 20,
            'schema' => array(
				'model' => array(
					'id' => $form->campoClave(),
					'fields' =>  array(
						$form->campoClave() => array('editable' => false, 'nullable' => true)
					)
				)
			)
		);
		
		foreach($objs as $v){
			$i++;
			$objEle = $form->objs($v);
			
			if ($v == $this->id || $objEle === false || in_array($objEle->tipo, $camposNoPermitidos))
				continue;
			
			//$objEle->tipo;
			
			$DBTipo = $form->tipoDato($v);
			
			$columna = array(
				'field' => $objEle->id(),
				'title' => htmlentities(trim(str_replace(':', '', $objEle->propiedad('texto')))),
				'width' => isset($this->anchos[$i]) ? $this->anchos[$i] : null,
			);
			
			if ($DBTipo === 'date' || $DBTipo === 'tiempo'){
				$columna['format'] = "{0:dd/MM/yyyy}";
			}
			
			//valorArray
			
			$valorFuente = is_array($objEle->propiedad('valorFuente')) ? $objEle->propiedad('valorFuente') : $objEle->propiedad('valorArray');
			
			if (is_array($valorFuente)){
				$columna['values'] = $valorFuente;
				/*
				[{
                    "value": 1,
                    "text": "Beverages"
                },{
                    "value": 2,
                    "text": "Condiments"
                }]
				*/
			}
			
			$this->columns[] = $columna;
			
			$campo = array(
				//'editable' => false,
				//'nullable' => true,
				'type' => $tipos[$DBTipo], // number, boolean, date, string
			);
			
			$gridObj = $objEle->propiedad('grid');
			if (is_array($gridObj)){
				if (isset($gridObj['editable'])){
					$campo['editable'] = $gridObj['editable'];
				}
				
				if (isset($gridObj['nullable'])){
					$campo['nullable'] = $gridObj['nullable'];
				}
			}
			
			if ($objEle->propiedad('validar') !== false){
				$campo['validation']['required'] = true;
				
				$min = $objEle->propiedad('min');
				if ($min != 0) $campo['validation']['min'] = $min;
				
				$max = $objEle->propiedad('max');
				if ($max != 0) $campo['validation']['max'] = $max;
			}
			
			$dataSource['schema']['model']['fields'][$v] = $campo; 
		}
		
		$this->dataSource = $dataSource; 
	}

    function jQuery(){
    	$opciones = $this->jsOpciones();

		$jQuery = '
		$("{selector}").kendoGrid(' . $opciones . ');
		';
			
		return $jQuery;
    }

    function jsOpciones($op = 0){
		global $url;
		$opcionesConstruc = array(
			//'scrollable' 	=> true,
			'scrollable' 	=> array('virtual' => true),
	        'sortable'		=> true,
	        'filterable'	=> true,
	        'reorderable'	=> true,
	        'resizable' 	=> true,
	        'editable'		=> true,
	        'navigatable'	=> true,
	        'pageable'		=> array('input' => true, 'numeric' => false),
	        'toolbar'		=> array(),
	        'columns'		=> $this->columns,
	        'dataSource' 	=> $this->dataSource
		);
		
		if ($this->alto != ''){
			$opcionesConstruc['height'] = $this->alto;
		}
		
		if ($this->ancho != ''){
			$opcionesConstruc['width'] = $this->ancho;
		}
		
		
		$opciones = array_merge($opcionesConstruc, $this->opciones);
		
		if (empty($opciones['toolbar'])){
			unset($opciones['toolbar']);
		}
		
		return $this->json_encode($opciones);
	}

    function json_encode($arr, $sinComilla = array()){
    	if (!is_array($arr)) return $arr;

		$value_arr = array();
		$replace_keys = array();

		foreach($arr as $key => &$value){
			if (is_string($value)){
				if(strpos(trim($value), 'function(')===0 || array_search($key, $sinComilla) !== false){
					$value_arr[] = trim($value);
					$value = '%' . $key . '%';
					$replace_keys[] = '"' . $value . '"';
				}
			}
		}

		return str_replace($replace_keys, $value_arr, json_encode($arr));
    }
}
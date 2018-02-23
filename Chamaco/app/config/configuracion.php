<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//definiciones
date_default_timezone_set('America/Caracas');

//configuracion general
$config['modulo'] 			= 'inicio';
$config['compania'] 		= 'El Chamaco';
$config['titulo'] 			= 'El Chamaco';

$config['iva'] 				= 0.12;

$config['dircss'] 			= 'css/';
$config['dirjs'] 			= 'js/';

$config['cssConf'] 			= array(
	'estilos.css', 
	//'960/reset.css', '960/text.css', 
	'960/960.css', // grid 960 
	'normalize.css', 'main.css', //normalize
	'jquery.qtip.min.css', //tooltips
	'superfish.css', // menu superfish
	'ui/jquery-ui.min.css', //estilo jquery ui
	'alertify.core.css', 'alertify.default.css',
	'bootstrap/bootstrap.min.css',
	'fontawesome/font-awesome.min.css',
	'kendo/kendo.common.min.css', 'kendo/kendo.silver.min.css' //estilo kendo ui
);

$config['jsConf'] 			= array(
	//'underscore-min.js', 'backbone-min.js', //backbone
	//'kendo.web.min.js', 'cultures/kendo.culture.es-VE.min.js', //kendo ui
	
	'jquery.easing.1.3.js', //'tmpl.min.js', 
	'jquery.shortcuts.js',
	'alertify.min.js',
	'jquery.blockUI.js',
	'jquery.blockUI.js',
	//'jquery.bgiframe.pack.js', 'jquery.hoverIntent.minified.js', 'supersubs.js', 'superfish.js', //menu en jquery

	'jquery.validate.js', 'jquery.form.js', 'jquery.objForm.js', //plugins de formulario
	//'jquery.qtip.min.js', // 'jquery.lazy.min.js',
	'ini.js'
);

$config["rutascss"] 		= array("css", "css/less", "css/ui", "plantillas");
$config["rutasjs"] 			= array("js", "js/vendor");

$config['cabeza'] 			= true;
$config['banner'] 			= false;
$config['menu'] 			= true;
$config['menuFormulario'] 	= true;

$config['marco'] 			= true;
$config['contenedor'] 		= true;

$config['imagenPies'] 		= true;
$config['pies'] 			= true;

$config['fechaActualTime'] 	= time();
$config['fechaActual'] 		= date('Y-m-d H:i:s');

$config['autentucacion'] 	= 'ldap'; //ldap, bd

//definicion de tablas
$config['tablas'] = array(
	"menu" 		 	 		=> "menu",
	"perfiles" 	 	 		=> "perfiles",
	"permisos" 	 	 		=> "permisos",
	"usuarios" 	 	 		=> "usuarios",
	"procedimientos" 		=> "procedimientos",
	"permisosprocedimiento" => "permisosprocedimiento",
	"permisosarchivos" 		=> "permisosarchivos",
	"modulos" 		 		=> "modulos",
	"archivos" 		 		=> "archivos",
	"app_estructura"		=> "app_estructura"
);

//configuracion de session
$config['variables_session'] = array(
	
);
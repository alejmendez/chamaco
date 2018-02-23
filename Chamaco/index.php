<?php
define('ENVIRONMENT', 'development');
if (defined('ENVIRONMENT')){
	switch (ENVIRONMENT){
		case 'development':
			ini_set('display_errors', 1);
			error_reporting(E_ALL);
		break;

		case 'testing':
		case 'production':
			ini_set('display_errors', 0);
			error_reporting(0);
		break;

		default:
			exit('The application environment is not set correctly.');
	}
}

$system_path = '../codeIgniter/system';
$application_folder = 'app';

if (defined('STDIN')){
	chdir(dirname(__FILE__));
}

if (realpath($system_path) !== FALSE){
	$system_path = realpath($system_path).'/';
}

$system_path = rtrim($system_path, '/').'/';

if (!is_dir($system_path)){
	exit("Su ruta de la carpeta del sistema no parece estar configurado correctamente. Por favor, abra el siguiente archivo y corregir esta: ".pathinfo(__FILE__, PATHINFO_BASENAME));
}

define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));
define('EXT', '.php');
define('BASEPATH', str_replace("\\", "/", $system_path));
define('FCPATH', str_replace(SELF, '', __FILE__));
define('SYSDIR', trim(strrchr(trim(BASEPATH, '/'), '/'), '/'));

if (is_dir($application_folder)){
	define('APPPATH', $application_folder.'/');
}else{
	if (!is_dir(BASEPATH.$application_folder.'/')){
		exit("Su ruta de carpeta de aplicaci�n no parece estar configurado correctamente. Por favor, abra el siguiente archivo y corregir esta: ".SELF);
	}

	define('APPPATH', BASEPATH.$application_folder.'/');
}

require_once BASEPATH.'core/CodeIgniter.php';
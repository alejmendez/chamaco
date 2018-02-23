<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class MY_Loader extends CI_Loader{
	public function __construct(){
		parent::__construct();
	}
	
	public function controller($control, $class_name = false){
		$CI = & get_instance();
		
		$file_path = APPPATH . 'controllers/' . $control . EXT;
		
		if (($last_slash = strrpos($control, '/')) !== FALSE){
			$control = substr($control, $last_slash + 1);
		}
		
		$class_name = $class_name === false ? $control : $class_name;
		
		if(file_exists($file_path)){
			require $file_path;
			$CI->$class_name = new $control(true);
		}else{
			show_error("Unable to load the requested controller class: " . $class_name);
		}
	}
}
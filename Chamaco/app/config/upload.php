<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['upload_path'] 		= './archivos/' . date('Y') . '/' . date('m');
$config['allowed_types'] 	= 'gif|jpg|jpeg|png';
$config['file_name'] 		= 'archivos';
$config['max_size'] 		= (1 * 1024); // 1Mb
//$config['max_width'] 		= '1024';
//$config['max_height'] 		= '768';
$config['encrypt_name'] 	= true;
$config['xss_clean'] 		= true;

if (!is_dir($config['upload_path'])){
	mkdir($config['upload_path'], 0777, true);
}
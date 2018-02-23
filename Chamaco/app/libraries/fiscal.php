<?php
//Cliente TCP/IP
class fiscal{
	//Variables globales
	public $my_ip = null;
	public $my_port = null;
	public $array_rep = null;
	// Contructor
	public function __construct(){
		$this->my_ip  = "localhost";//$_SERVER['REMOTE_ADDR'];  // Solo aplica para servidores Apache, de lo contrario tiene que establecercela
		$this->my_port = getservbyname('www', 'tcp'); //Puerto 80 por default
	}
	//Funcion que envia un comando por protococolo TCP/IP y retorna un arreglo de  palabras de repuestas
	public function sendCmdTcp($cmd){
		$this->array_rep = null;

		error_reporting(E_ALL);

		/* Obtener el puerto para el servicio WWW. */
		$service_port =  $this->my_port;
		/* Obtener la dirección IP para el host objetivo. */
		$address = gethostbyname($this->my_ip);

		/* Crear un socket TCP/IP. */

		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if ($socket === false) {
			return "socket_create() falló: razón: " . socket_strerror(socket_last_error()) . "<br>";
		} 

		$result = socket_connect($socket, $address, $service_port);
		if ($result === false) {
			return "socket_connect() falló.\nRazón: ($result) " . socket_strerror(socket_last_error($socket)) . "<br>";
		} 
		$out = '';
		/*Enviamos el comando o peticion al Fiscal printer por via TCP*/
		socket_write($socket, $cmd, strlen($cmd));
		$ite = 0;
		while ($out = socket_read($socket, 2048)) {
			$this->array_rep[$ite] = $out;
			++$ite;
		}

		socket_close($socket);
		return $this->array_rep;
	}
}
?>
<?php
class cliente_modelo extends modelo_form{
	protected $id = 'cliente'; //id del formulario
	protected $titulo = 'Cliente'; //titulo (attr title) del formulario
	protected $tabla = 'cliente';
	//protected $url = 'autenticacion';
	
	protected $forzarSalida = false;
	//protected $metodo = 'JSON';
	
	public $campos = array(
		array(
			"id" => "id",
			"nombreDB" => "id:int"
		),
		array(
			"id" => "cedula",
			"nombreDB" => "cedula:str"
		),
		array(
			"id" => "nombre",
			"nombreDB" => "nombre",
			"max" => 100
		),
		array(
			"id" => "apellido",
			"nombreDB" => "apellido",
			"max" => 100
		),
		array(
			"id" => "direccion",
			"nombreDB" => "direccion",
			"max" => 300
		),
		array(
			"id" => "telefono",
			"nombreDB" => "telefono",
			"max" => 100
		)
	);
}
<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

//$rand = rand(1,999999);

//definicion de tablas
$modo = 'puerto';
if (function_exists('gethostname')){
	$modo = gethostname() === 'dggdisupdit01' ? 'prueba' : 'puerto'; // opcional, para generar pruebas	
}

//$modo = 'puerto';

$config["drivers_impresora"] = array("Impresora_comanda","Impresora_fiscal");

$config['impresoras'] = array(
	'fiscal' => array(
		'modo' => $modo, // puerto|prueba
		'driver' => 'fiscal',
		'ip' => $modo === 'puerto' ? '192.168.1.80' : 'localhost:8080',
		//'puerto' => '80'
	),
	
	'comanda1' => array(
		'driver' => 'comanda_oficina',
		'modo' => $modo, // puerto|archivo|prueba
		'puerto' => '\\\\127.0.0.1\\POS-80',
		//'puerto' => 'LPT1',
		'caracteresPorLinea' => 48,
		'caracteresPorLineaNegrita' => 48,
		'caracterLinea' => '-',
		'letra' => 1,
		'letraNegrita' => 8,
		'plantillas' => array(
			'factura' => array(
				'cabeza' => '
					[n]|SENIAT|
					|J-297077430|
					|"EL CHAMACO, C.A"|
					|AV. PASEO MENESES EDIF. EL CHAMACO|
					|PISO PB LOCAL EL CHAMACO|
                    |SECTOR PASEO MENESES|
                    |PARROQUIA CATEDRAL MUNICIPIO HERES|
					|CIUDAD BOLIVAR ESTADO BOLIVAR|
					
					||
					|FACTURA|
					FACTURA:|{orden}
					FECHA: {fecha}|HORA: {hora}
					{linea}
					Numero de Pedido: {correlativo}
					{parallevar}
				',
                /*
                'cabeza' => '
					[n]|SENIAT|
					|J-295924011|
					|EL RINCON DEL DULCE PALMERINI, J.M C.A|
					|CALLE BRASIL QTA JOSMAR N 32 SECTOR|
					|LA MARIQUITA PQUITA VTA HERMOSA|
					|MCPIO HERES CD BOLIVAR|
					
					||
					|FACTURA|
					FACTURA:|{orden}
					FECHA: {fecha}|HORA: {hora}
					{linea}
					Numero de Pedido: {correlativo}
					{parallevar}
				',
                */
				'cuerpo' => '
					{cantidad} x {precio}
					{producto}|Bs {total}',
				'pies' => '
					{linea}
					BI G ({porcentaje}%)|Bs {total}
					IVA G ({porcentaje}%)|Bs {iva}
					{linea}
					[n]TOTAL:|Bs {totalneto}
					{forma_pago}|Bs {totalneto}
					MH|DLA7004674
				'
			),
			'encargo' => array(
				'cabeza' => '
					[n]|"EL CHAMACO, C.A"|
					|AV. PASEO MENESES EDIF. EL CHAMACO|
					|PISO PB LOCAL EL CHAMACO|
                    |SECTOR PASEO MENESES|
                    |PARROQUIA CATEDRAL MUNICIPIO HERES|
					|CIUDAD BOLIVAR ESTADO BOLIVAR|
					||
					|ENCARGO|
					{linea}
					FACTURA:|{orden}
					FECHA: {fecha}|hora: {hora}
					FECHA ENTREGA: {fecha_entrega}
					{linea}
					{cedula}
					NOMBRE: {nombre_apellido}
					DIRECCION: {direccion}
					TELEFONO:  {telefono}
					{linea}
				',
				'cuerpo' => '
					{cantidad} x {precio}
					{producto}|Bs {total}',
				'pies' => '
					||
					{observacion}|Bs {extra}
					{linea}
					PAGADO:|Bs {pago}
					PENDIENTE:|Bs {pendiente}
					{linea}
					[n]TOTAL:|Bs {total_encargo}
				'
			)
		)
		//{cajero}
	),
	
	'comanda2' => array(
		'driver' => 'comanda',
		'modo' => $modo, // puerto|archivo|prueba
		'caracteresPorLinea' => 32,
		'saltoAutomatico' => false,
		//'letra' => 4,
		'caracterLinea' => '-',
		'puerto' => '\\\\127.0.0.1\\POS58',
		
		'plantillas' => array(
			'factura' => array(
				'cabeza' => '
                    ||
					NRO PEDIDO:|{correlativo}
                    UBICACION:|{ubicacion}
					FECHA: {fecha}|hora: {hora}
					||
				',
				'cuerpo' => '
					*{producto} x {cantidad}
					||',
				'pies' => '
					||
					{linea}
				'
			),
			'encargo' => array(
				'cabeza' => '',
				'cuerpo' => '',
				'pies' => ''
			)
		)
	)
);
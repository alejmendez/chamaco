<?php
$db = $ci->db;
$condicion = $ci->modelo->hacerCondicion('*');

if ($condicion !== ''){
	$condicion = ' AND ' . $condicion;
}

$condicion = str_replace('fecha_entrega', 'fecha_entrega::date', $condicion);

$condicionIdEncargo = $this->idEncargo === 0 ? '' : 'ord.id = ' . $this->idEncargo;

if ($condicionIdEncargo !== ''){
	$condicionIdEncargo = ' AND ' . $condicionIdEncargo;
}
?>
<style>
.barra{
	height: 10mm;
}

.do{
	
}

.cedula{ width: 40mm; }
.nombre{ width: 80mm; }
.fecha{ width: 68mm; }
.pagoRestante{ width: 40mm; }
.observacion{ width: 151mm; }
</style>
<page backtop="3mm" backbottom="3mm" backleft="3mm" backright="3mm" >
    <page_header>
        <div style="text-align: left">
            <?php 
				// <img src="echo base_url('img/venta/apple_drink.png');" class="logo" alt="Logo" style="margin-top: 5mm;" />
			?>
        </div>
    </page_header>
    
    <page_footer>
        <div style="text-align: center">
            <em>Pagina [[page_cu]]/[[page_nb]]</em>
        </div>
    </page_footer>
    
    <h1>Encargos.</h1>
    
			<?php
				$resumen = array();
				/*
				$productosCompleto = $db->seleccionarEnMatriz('productos', 'id, texto, padre');
				$productos = $db->seleccionarEnMatriz('productos as pro', 'id, texto, padre', 'id', '(select pro2.id from productos as pro2 where pro2.padre = pro.id limit 1) is null');
				
				foreach($productos as $id => $producto){
					$id = intval($id);
					$padre = intval($productos[$id]['padre']);
					
					while($padre != 0){
						$productos[$id]['texto'] = @$productosCompleto[$padre]['texto'] . ' ' . $productosCompleto[$id]['texto'];
						$padre = @intval($productosCompleto[$padre]['padre']);
					}
				}
				*/
				
				$db->seleccionar(
				'orden_tmp as ord, encargo as enc, cliente as cli',
				array(
					array('ord.id', 'id'),
					array('cli.cedula', 'cedula'),
					array('(cli.nombre || \' \' || cli.apellido)', 'nombre'),
					array('enc.fecha_entrega', 'fecha_entrega'),
					//array('((select sum(vtn_tmp.precio) from (select (vnt.precio * vnt.cantidad) precio from venta_tmp vnt where vnt.orden = ord.id) as vtn_tmp) + enc.extra - enc.pago)', 'restante'),
					array('ord.pendiente', 'restante'),
					array('enc.observacion', 'observacion')
				),
				array(
					'w' => '
						ord.id = enc.orden 
						AND enc.cliente = cli.id
					' . $condicion . $condicionIdEncargo,
					'o' => 'enc.fecha_entrega'
				));
				
				//$db->uq();
				
				foreach($db->rs as $encargo){
					echo '
					<table>
						<tr>
							<td class="do cedula">Cedula: ' . number_format($encargo['cedula'], 0, ',', '.') . '</td>
							<td class="do nombre">Nombre: ' . $encargo['nombre'] 			. '</td>
							<td class="do fecha">Fecha de Entrega: ' . $ci->tratarFechas($encargo['fecha_entrega'], 'd/m/Y h:i a') . '</td>
						</tr>
					</table>
					<table>
						<tr>
							<td class="do pagoRestante">Pago Restante:' . $encargo['restante'] 			. '</td>
							<td class="do observacion">Observación: ' . htmlentities($encargo['observacion']) 	. '</td>
						</tr>
					</table>
					';
					
					$detalle = $db->seleccionarArray('venta_tmp', 'id_producto as id, cantidad, producto', 
					array(
						'w' => 'orden = ' . $encargo['id']
					));
					
					
					echo '<table style="width: 100%;">
				    	<thead>
							<tr>
								<th style="width: 6%;">Cant.</th>
								<th style="width: 44%;">Encargo</th>
								
								<th style="width: 6%;">Cant.</th>
								<th style="width: 44%;">Encargo</th>
							</tr>
						</thead>
				    	<tbody>
				    		<tr>';
    		
					$i = 0;
					foreach($detalle as $producto){
						if ($i++ % 2 == 0){
							echo '</tr><tr>';
						}
						
						$producto['id'] = intval($producto['id']);
						$producto['producto'] = trim($producto['producto']);
						echo '
							<td>' . $producto['cantidad'] . '</td>
							<td>' . $producto['producto'] . '</td>
						';
						
						
						if (!isset($resumen[$producto['producto']])){
							$resumen[$producto['producto']] = array(0, $producto['producto']);
						}
						
						$resumen[$producto['producto']][0]++;
					}
					
					if ($i++ % 2 == 0){
						echo '
							<td></td>
							<td></td>
						';
					}
					
					echo "</tr>
						</tbody>
					</table>
					<div class='barra'></div>
					";
				}
			?>
</page>

<page backtop="3mm" backbottom="3mm" backleft="3mm" backright="3mm" >
    <page_header>
        <div style="text-align: left">
            <?php 
				// <img src="echo base_url('img/venta/apple_drink.png');" class="logo" alt="Logo" style="margin-top: 5mm;" />
			?>
        </div>
    </page_header>
    
    <page_footer>
        <div style="text-align: center">
            <em>Pagina [[page_cu]]/[[page_nb]]</em>
        </div>
    </page_footer>
	<h1>Resumen.</h1>
	
	<?php
		/*$tab = "<div style='margin-left: {distancia}px;'>{texto}</div>";
		$productos = $db->seleccionarEnMatriz('productos', 'id, texto, padre');
		$padres = array();
		
		echo $ci->tmpl($tab, array(
			'texto' => '',
			'distancia' => ($cant * 10),
		));
		
		foreach($resumen as $id => $producto){
			$id = intval($id);
			$padre = intval($productos[$id]['padre']);
			$_padre = array();
			
			while($padre != 0){
				$productos[$id]['texto'] = $productos[$padre]['texto'] . ' ' . $productos[$id]['texto'];
				$_padre = array();
				
				$padre = intval($productos[$padre]['padre']);
			}
			
			$producto[1] = $productos[$id]['texto'];
		}*/
	?>
	
	<table style="width: 100%;">
    	<thead>
			<tr>
				<th style="width: 6%;">Cant.</th>
				<th style="width: 44%;">Encargo</th>
				
				<th style="width: 6%;">Cant.</th>
				<th style="width: 44%;">Encargo</th>
			</tr>
		</thead>
    	<tbody>
    		<tr>
			<?php
				$i = 0;
				foreach($resumen as $id => $producto){
					if (strpos($producto[1], '> Extas') !== false){
						continue;
					}
					
					if ($i++ % 2 == 0){
						echo '</tr><tr>';
					}
					
					echo '
						<td>' . $producto[0] . '</td>
						<td>' . $producto[1] . '</td>
					';
				}
				
				if ($i++ % 2 == 0){
					echo '
						<td></td>
						<td></td>
					';
				}
			?>
			</tr>
		</tbody>
	</table>
</page>
<?php //exit(); ?>
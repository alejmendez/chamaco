<?php
$total = 0;
$db = $ci->db;

$dias = array("Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sábado");
$meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
?>
<style>
.titulo{
	text-align: center;
}
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
    
    
    <?php
		$fecha = false;
		$i = $total = $totalFinal = 0;
		 
		foreach($db->rs as $venta){
			$i++;
			
			$venta['fechaVenta'] = trim(substr($venta['fecha'], 0, 10));
			
			if ($venta['fechaVenta'] != ''){
				$venta['fechaVenta'] = $ci->tratarFechas($venta['fechaVenta']);
				
				$venta['fechaVenta'] = $dias[date('w', $venta['fechaVenta'])] . ' ' . date("d/m/Y", $venta['fechaVenta']);
			}
			
			$generaTabla = false;
				
			if ($fecha !== $venta['fechaVenta']){
				$fecha = $venta['fechaVenta'];
				$generaTabla = true;
			}
			
			if ($generaTabla){
				if ($i !== 1){
					echo "
				<tr style='font-weight: bold;'>
					<td colspan='3'>Total Efectivo:</td>
					<td style='text-align: right;'>" . number_format($totalDetallado[$venta['fecha']]['forma_pago1'], 2, ',', '.') . "</td>
				</tr>
				
				<tr style='font-weight: bold;'>
					<td colspan='3'>Total Debito:</td>
					<td style='text-align: right;'>" . number_format($totalDetallado[$venta['fecha']]['forma_pago2'], 2, ',', '.') . "</td>
				</tr>
				
				<tr style='font-weight: bold;'>
					<td colspan='3'>Total Credito:</td>
					<td style='text-align: right;'>" . number_format($totalDetallado[$venta['fecha']]['forma_pago3'], 2, ',', '.') . "</td>
				</tr>
				
				<tr style='font-weight: bold;'>
					<td colspan='3'>Total General:</td>
					<td style='text-align: right;'>" . number_format($totalDetallado[$venta['fecha']]['forma_pago1'] + $totalDetallado[$venta['fecha']]['forma_pago2'] + $totalDetallado[$venta['fecha']]['forma_pago3'], 2, ',', '.') . "</td>
				</tr>
			</tbody>
		</table><br /><br /><br />";
					$total = 0;
				}
				
				echo $fecha;
	?>
		<table style="width: 170mm; margin-top: 10cm;">
			<thead>
				<tr>
					<th style="width: 50%; text-align: center;">Producto</th>
					<th style="width: 10%; text-align: center;">Cantidad</th>
					<th style="width: 20%; text-align: center;">Precio p/u</th>
					<th style="width: 20%; text-align: center;">Precio</th>
				</tr>
			</thead>
			<tbody>
	<?php 
		}
		$totalVenta = $venta['cantidad'] * $venta['precio'];
		$total += $totalVenta;
		$totalFinal += $totalVenta;
		
		if ($venta['cantidad'] == 0)
			continue;
	?>
				<tr>
					<td style="width: 60%;"><?php echo ($venta['producto']); ?></td>
					<td style="width: 10%; text-align: right;"><?php echo $venta['cantidad']; ?></td>
					<td style="width: 15%; text-align: right;"><?php echo number_format($venta['precio'], 2, ',', '.'); ?></td>
					<td style="width: 15%; text-align: right;"><?php echo number_format($totalVenta, 2, ',', '.'); ?></td>
				</tr>
	<?php if ($generaTabla){ ?>
			
	<?php
			}
		}
	?>
			<?php if ($comanda === false) { ?>
			<tr>
				<td>Encargos</td>
				<td style=" text-align: right;"><?php echo @$totalEncargos[$venta['fecha']]['cantidad']; ?></td>
				<td style=" text-align: right;">&nbsp;</td>
				<td style=" text-align: right;"><?php echo @number_format($totalEncargos[$venta['fecha']]['total'], 2, ',', '.'); ?></td>
			</tr>
			<?php } ?>
			<tr style='font-weight: bold;'>
				<td colspan="3">Total Efectivo:</td>
				<td style='text-align: right;'><?php echo number_format($totalDetallado[$venta['fecha']]['forma_pago1'], 2, ',', '.'); ?></td>
			</tr>
			
			<tr style='font-weight: bold;'>
				<td colspan="3">Total Debito:</td>
				<td style='text-align: right;'><?php echo number_format($totalDetallado[$venta['fecha']]['forma_pago2'], 2, ',', '.'); ?></td>
			</tr>
			
			<tr style='font-weight: bold;'>
				<td colspan="3">Total Credito:</td>
				<td style='text-align: right;'><?php echo number_format($totalDetallado[$venta['fecha']]['forma_pago3'], 2, ',', '.'); ?></td>
			</tr>
			
			<tr style='font-weight: bold;'>
				<td colspan="3">Total Cuentas:</td>
				<td style='text-align: right;'><?php echo number_format($totalDetallado[$venta['fecha']]['total'], 2, ',', '.'); ?></td>
			</tr>
			
			<?php if ($comanda === false) { ?>
			<tr style='font-weight: bold; color: #BE162D;'>
				<td colspan="3">Notas de Credito:</td>
				<td style='text-align: right;'><?php echo @number_format($totalNotaCredito[$venta['fecha']]['total'], 2, ',', '.'); ?></td>
			</tr>
			
			<tr style='font-weight: bold;'>
				<td colspan="3">Total General:</td>
				<td style='text-align: right;'><?php echo @number_format(floatval($totalDetallado[$venta['fecha']]['total']) - floatval($totalNotaCredito[$venta['fecha']]['total']), 2, ',', '.'); ?></td>
			</tr>
			<?php } ?>
		</tbody>
	</table><br /><br /><br />
	
	<?php
		if (count($totalDetallado) > 1){
			$totales = array(0,0,0);
			foreach($totalDetallado as $total){
				$totales[0] += $total['forma_pago1'];
				$totales[1] += $total['forma_pago2'];
				$totales[2] += $total['forma_pago3'];
			}
	?>
	
	<table>
		<tr style='font-weight: bold;'>
			<td>Total Efectivo:</td>
			<td style='text-align: right;'><?php echo number_format($totales[0], 2, ',', '.'); ?></td>
		</tr>
		
		<tr style='font-weight: bold;'>
			<td>Total Debito:</td>
			<td style='text-align: right;'><?php echo number_format($totales[1], 2, ',', '.'); ?></td>
		</tr>
		
		<tr style='font-weight: bold;'>
			<td>Total Credito:</td>
			<td style='text-align: right;'><?php echo number_format($totales[2], 2, ',', '.'); ?></td>
		</tr>
		
		<tr style='font-weight: bold;'>
			<td>Total General:</td>
			<td style='text-align: right;'><?php echo number_format($totales[0] + $totales[1] + $totales[2], 2, ',', '.'); ?></td>
		</tr>
	</table>
	<?php } ?>
</page>
<?php //exit(); ?>
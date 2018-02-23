<?php
$total = 0;
?>
<style>
.titulo{
	text-align: center;
}
</style>
<page>
<table border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td colspan="2" style="text-align: center;">            
            SENIAT<br />
			J-297077430<br />
			"EL CHAMACO, C.A"<br />
			AV. PASEO MENESES EDIF. EL CHAMACO<br />
			PISO PB LOCAL EL CHAMACO<br />
            SECTOR PASEO MENESES<br />
            PARROQUIA CATEDRAL MUNICIPIO HERES<br />
			CIUDAD BOLIVAR ESTADO BOLIVAR<br />
		</td>
	</tr>
	<tr>
		<td colspan="2" >
			FACTURA #: NUMERO<br />
			PEDIDO # 123 - Venta Directa<br /><br />
			
			-------------------------------------------<br />
			*** SIN DERECHO A CREDITO FISCAL ***<br />
			PARA LLEVAR -------------------------------
		</td>
	</tr>
	<tr>
		<td colspan="2" style="text-align: center;">
			FACTURA
		</td>
	</tr>
	
	<tr>
		<td>FACTURA:</td>
		<td>00001287</td>
	</tr>
	<tr>
		<td>FECHA: <?php echo date('d-m-Y'); ?></td>
		<td>HORA: <?php echo date('H:i'); ?></td>
	</tr>
	<tr>
		<td colspan="2" >
			-------------------------------------------<br />
			PEDIDO
		</td>
	</tr>

	<?php
	foreach($productos as $producto){
		$total += floatval($producto['precio']) * intval($producto['cantidad']);
	?>
		<tr>
			<td style="width: 52mm; height: 3.3mm;"><?php echo $producto['producto']; ?></td>
			<td><?php echo $producto['cantidad']; ?> X <?php echo $producto['precio']; ?></td>
		</tr>
	<?php
	}
	?>
	
	<td colspan="2" >-------------------------------------------</td>
	<tr>
		<td>SUBTTL</td>
		<td>Bs <?php echo $total; ?></td>
	</tr>
	<td colspan="2" >-------------------------------------------</td>
	<tr>
		<td>BI G (12,00%)</td>
		<td>Bs. <?php echo $total; ?></td>
	</tr>
	<tr>
		<td>IVA G(12,00%</td>
		<td>Bs <?php echo $total; ?></td>
	</tr>
BI G (12,00%)				Bs. <?php echo $total; ?>
IVA G(12,00%				Bs. 51,56
<td colspan="2" >-------------------------------------------</td>
TOTAL 						Bs <?php echo $total; ?>
Efectivo 2					bs <?php echo $total; ?>
Cajero 4

</table>
</page>
<script>
app.ini(function(){
	<?php $ci->modelo->sJQuery(); ?>
	$("#fecha").val('<?php echo date('d/m/Y - d/m/Y'); ?>');
});
</script>
<style>
.barra{
	height: 20px;
}
#boton{
	font-size: 22px;
}
</style>
<div id="contenedor_formulario" class="grid_12">
	<form <?php echo $ci->modelo; ?> target="_blank">
		<?php $ci->modelo->hacer("fecha, terminal, reporteResumido, reporteComanda, |, |, |"); ?>
		<div class="col-lg-4" style="float: right;">
			<div class="contenedorObj">
				<input type="submit" value="Reporte" class="btn btn-success" style="font-size: 20px">
			</div>
		</div>
	</form>
</div>
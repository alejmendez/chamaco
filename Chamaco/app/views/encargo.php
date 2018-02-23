<script>
app.ini(function(){
	<?php $ci->modelo->sJQuery(); ?>
	$('#calendario').fullCalendar({
		header: {
			left: 'prev,next today',
			center: 'title',
			right: 'month,basicWeek,basicDay'
		},
		defaultDate: '<?php echo date('Y-m-d'); ?>',
		editable: true,
		eventLimit: true, // allow "more" link when too many events
		eventClick: function(calEvent, jsEvent, view) {
			//console.log(calEvent, jsEvent, view);
			//console.log(calEvent.id)
			$("#formImprimir").attr('action', app.url + 'imprimir/' + calEvent.id).submit();
	    },
		events: {
			url: app.url + 'obPedidos'
		}
	});
	
	
	
	//$('#calendario').fullCalendar('refetchEvents');
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
		<?php $ci->modelo->hacer('orden, |, fecha_entrega, cedula, nombre, estatus, |'); ?>
		<div style="margin: 15px; text-align: right;">
			<button type="submit" class="btn btn-primary">Imprimir</button>
		</div>
	</form>
	<div id="calendario"></div>
</div>

<form id="formImprimir" action="<?php echo $ci->url . 'imprimir'; ?>" target="_blank"></form>
<script>
app.ini(function(){
	//$()
	
	$("#archivos").kendoUpload({
		async: {
			saveUrl: app.url + "subirArchivo",
			removeUrl: app.url + "eliminarArchivo",
			autoUpload: true
		},
		template : kendo.template($('#archivosPlantilla').html()),
		success: function(e){
			aviso(e.response);
			
			e.files[0].archivo = e.response.archivo
			e.files[0].ruta_base = e.response.ruta_base
		},
		remove : function(e, data){
			e.data = {
				archivo: e.files[0].archivo,
				ruta_base: e.files[0].ruta_base
			};
		},
		error: function(e){
			aviso(e.XMLHttpRequest.responseText, false);
		}
	});
});
</script>
<script id="archivosPlantilla" type="text/x-kendo-template">
	<span class="k-progress" style="width: 100%;"></span>
	<span class="k-icon k-i-jpg">Cargado</span>
	<span title="#= name #" class="k-filename">#= name #</span>
	<strong class="k-upload-status">
		<span class="k-upload-pct">100%</span>
		<button class="k-button k-button-bare k-upload-action" type="button">
			<span title="Remove" class="k-icon k-i-close k-delete"></span>
		</button>
	</strong>
</script>
<style>

</style>

<form method="post" action="">
<input id="archivos" name="archivos" type="file" />
</form>
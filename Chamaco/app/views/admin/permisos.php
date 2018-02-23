<script>
var $formObj = "#<?php echo $ci->permisos->id(); ?>";
app.ini(function(){
	<?php $ci->permisos->sJQuery(); ?>
});
</script>
<div class="grid_12">
	<form <?php echo $ci->permisos; ?>>
		<?php $ci->permisos->hacer("usuario"); ?>
		<div id="arbol"></div>
	</form>
</div>
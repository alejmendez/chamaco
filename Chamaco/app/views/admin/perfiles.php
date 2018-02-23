<script>
var $formObj = "#<?php echo $ci->perfiles->id(); ?>";
app.ini(function(){
	<?php $ci->perfiles->sJQuery(); ?>
});
</script>
<style>
#arbol {
    min-height: 300px;
    width: 300px;
}
</style>
<div class="grid_4">
	<form <?php echo $ci->perfiles; ?>>
		<?php $ci->perfiles->hacer("perfil"); ?><br />
		<div id="arbol"></div>
	</form>
</div>
<div class="grid_8">	
	<div style="float:left; width:100%;">
		<?php $ci->perfiles->hacer("tabla"); ?>
	</div>
</div>
<script>
var $formObj = "#<?php echo $ci->usuarios->id(); ?>";
app.ini(function(){
	<?php
		$ci->usuarios->sJQuery();
	?>
	//$("#perfil").kendoMultiSelect();
});
</script>
<style>
</style>

<div class="grid_12">
	<form <?php echo $ci->usuarios; ?>>
		<?php $ci->usuarios->hacer('textoUsuario,|,usuario,nombre,pass,cedula,telefono,email,ejecutor,autenticacion, perfil,|'); ?>
		<strong>Nota: </strong> Al darle a la tecla &quot;Enter&quot; en el campo &quot;Usuario&quot; se busca al usuario en base de datos y en ldap.
	</form>
	<div style="float:left; width:100%;">
		<?php $ci->usuarios->hacer("tablaUsuarios"); ?>
	</div>
</div>
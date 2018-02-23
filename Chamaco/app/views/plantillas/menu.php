<div id="contenedorMenu" class="grid_12 ui-widget ui-widget-content ui-corner-all">
	<ul id="menuPrincipal" style="font-size:14px; margin:0px;">
		<li>
			<a href="<?php echo site_url("/"); ?>">Inicio</a>
		</li>
		<?php 
		if ($this->session->userdata('autenticado') !== false){
			//construirMenu();
		}
		?>
		<!--  --><li>
			<a href="#">Administrador</a>
			<ul>
				<li><a href="<?php echo site_url("admin/usuarios"); ?>">Usuarios</a></li>
				<li><a href="<?php echo site_url("admin/permisos"); ?>">Permisos</a></li>
				<li><a href="<?php echo site_url("admin/perfiles"); ?>">Perfiles</a></li>
				<li><a href="<?php echo site_url("admin/menu"); ?>">Sitio</a></li>
			</ul>
		</li>
		
		<li><a href="<?php echo site_url("productos"); ?>">Productos</a></li>
		<li><a href="<?php echo site_url("pago"); ?>">Modulo de Pago</a></li>
		<li><a href="<?php echo site_url("venta"); ?>">Modulo de Venta</a></li>
		
		<li>
			<a href="#">Reportes</a>
			<ul>
				<li><a href="<?php echo site_url("reporte_diario"); ?>">Por Fecha</a></li>
				<li><a href="<?php echo site_url("encargo"); ?>">Encargos</a></li>
			</ul>
		</li>
		
		<li style="float: right;">
			<a href="#"><?php echo $this->session->userdata('nombre'); ?></a>
			<ul>
				<li><a href="<?php echo site_url("cambio_clave"); ?>">Cambiar Contrase&ntilde;a</a></li>
				<li><a href="<?php echo site_url("autenticacion/cerrar"); ?>">Salir</a></li>
			</ul>
		</li>
	</ul>
</div>
<script>
var $ip = '<?php echo $ci->getIpCliente(); ?>';
    $("#menuCirculo li img").live('click', function(){
        window.location = $(this).attr('direccion');
    });
</script>

<ul style="font-size:14px; float: left; margin:50px 0; list-style: none;" id="menuCirculo">
	<li class="ui-widget" style="float: left; margin-right: 60px;">Productos
        <img title="Productos" direccion="<?php echo site_url('productos/'); ?>" src="<?php echo site_url('img/menu/productos.png'); ?>" style="width: 200px; cursor:pointer; height: 200px;" />
	</li>
		
    <li class="ui-widget" style="float: left; margin-right: 60px;">Pago
        <img title="Pago" direccion="<?php echo site_url('pago/'); ?>" src="<?php echo site_url('img/menu/pago.png'); ?>" style="width: 200px; cursor:pointer; height: 200px;" />
	</li>
    
    <li class="ui-widget" style="float: left;">Venta
        <img title="Venta" direccion="<?php echo site_url('venta/'); ?>" src="<?php echo site_url('img/menu/venta.png'); ?>" style="width: 200px; cursor:pointer; height: 200px;" />
	</li>
</ul>
<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="es"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" lang="es"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" lang="es"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="es"> <!--<![endif]-->
    <head>
        <meta charset="<?php echo config_item('charset');?>" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <title><?php echo htmlentities($this->conf["titulo"]); ?></title>
        <meta name="description" content="Gobernacion del estado Bolivar" />
        <meta name="viewport" content="width=device-width" />
		
        <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->
        
        <link rel="shortcut icon" href="<?php echo site_url('img/favion/favicon.ico'); ?>" />
	    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="<?php echo site_url('img/favion/apple-touch-icon-144-precomposed.png'); ?>" />
	    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?php echo site_url('img/favion/apple-touch-icon-114-precomposed.png'); ?>" />
	    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?php echo site_url('img/favion/apple-touch-icon-72-precomposed.png'); ?>" />
	    <link rel="apple-touch-icon-precomposed" href="<?php echo site_url('img/favion/apple-touch-icon-57-precomposed.png'); ?>" />
		
	    <?php
			$ci->archivosCabecera("css"); 
	    
        	$arrayJs = array(
				'modernizr-2.6.2.min.js', 
				'jquery-1.10.2.min.js', 'jquery-migrate-1.2.1.min.js', 'jquery-ui.min.js', //jquery
				'bootstrap/bootstrap.min.js',
				'kendoui/kendo.web.min.js', 'kendoui/cultures/kendo.culture.es-VE.min.js', //kendoui
				'app.js'
			);
        	
        	for($i = 0, $c = count($arrayJs); $i < $c; $i++){
        		echo '
				<script type="text/javascript" src="' . site_url('js/vendor/' . $arrayJs[$i]) . '"></script>';
        	}
		?>
    </head>
    <body>
        <!--[if lt IE 7]>
            <p class="chromeframe">Usted est&aacute; usando un navegador desactualizado. <a href="http://browsehappy.com/">Actualiza tu navegador hoy</a> &oacute; <a href="http://www.google.com/chromeframe/?redirect=true">instalar Google Chrome</a> a una mejor experiencia de este sitio.</p>
        <![endif]-->
        
        <div id="cargando" style="opacity: 0; display: none; ">Cargando...</div>
        <div class="fondo">
	        <div id="contenedorPrincipal" class="container_12 clearfix">
	        	<?php
				if ($this->conf["cabeza"] === true){
					$this->view('plantillas/cabecera');
				}
				
				if ($this->conf["banner"] === true){
					$this->view('plantillas/banner');
				}
				
				if ($this->conf["menu"] === true){
					$this->view('plantillas/menu');
				}
				
				if ($this->conf["menuFormulario"] === true){
					$this->view('plantillas/menuAccion');
				}
				if ($this->conf["marco"] === true){
					echo '<div id="contPagina">';
				}
				?>
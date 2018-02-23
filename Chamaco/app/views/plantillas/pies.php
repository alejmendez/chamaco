			<?php
				if ($this->conf['pies'] === true){
					$this->view('plantillas/pie');
				}

				if ($this->conf["marco"] === true){
					echo '</div>';
				}
			?>
			</div>
		</div>
	    <script>
	    	app.jsIni = <?php echo json_encode($ci->js()); ?>;
			app.url = '<?php echo $ci->url; ?>';
			app.base = '<?php echo site_url(); ?>';
			$(function() {
				app._ini();
			});
	    </script>
    </body>
</html>
<?php
class autenticacion_modelo extends modelo_form{
	protected $id = 'autenticacion'; //id del formulario
	protected $titulo = 'Autenticación'; //titulo (attr title) del formulario
	protected $tabla = 'usuarios';
	//protected $url = 'autenticacion';
	
	protected $autenticado = false;
	protected $forzarSalida = false;
	
	public $dbLdap = false;
	
	public $campos = array(
		array("id" => "id", "nombreDB" => "id:int"),
		array(
			"id" => "idUsuario",
			"actualizable" => false,
			"nombreDB" => "id:int"
		),
		array(
			"id" => "nombre",
			"tipo" => "texto",
			//"texto" => "Usuario: ",
			"placeholder" => "Usuario",
			"nombreDB" => "usuario"
		),
		array(
			"id" => "nombreCompleto",
			"nombreDB" => "nombre"
		),
		array(
			"id" => "contrasenna",
			"tipo" => "password",
			//"texto" => "Contraseña: ",
			"placeholder" => "Contraseña",
			"nombreDB" => "contrasenna:encry"
		),
		array(
			"id" => "perfil",
			"nombreDB" => "perfil[]:int"
		),
		array(
			"id" => "boton",
			"tipo" => "boton",
			"valor" => "Acceder"
		)
	);
	
	public function __construct(){
	    parent::__construct();
		//$this->ci->db->db->debug = true;
		$this->val('idUsuario', $this->ci->session->userdata('idUsuario'), true);
	    
	    $this->cambiarPropiedad("*, !boton", array("clase" => "loginCajaTexto", 'usaUI' => false))
		->cambiarPropiedad("boton", array("contenedorObjs" => ""))
		->validar()
		->sArchivos();
	}
	
	public function autenticado(){
		return $this->autenticado;
	}
	
	public function init_autenticacion(){
		global $LDAP_CONNECT_OPTIONS;
		
		$this->ci->conf['autentucacion'] = strtolower($this->ci->conf['autentucacion']);

		if ($this->actualizacion <= 0)
			$this->actualizarValores();

		if (!function_exists('ldap_connect')){
			$this->ci->conf['autentucacion'] = 'bd';
			return;
		}
		
		if ($this->ci->conf['autentucacion'] === 'ldap'){
			$LDAP_CONNECT_OPTIONS = array(
				array('OPTION_NAME' => LDAP_OPT_DEBUG_LEVEL, 		'OPTION_VALUE' => 7),
				array('OPTION_NAME' => LDAP_OPT_DEREF, 				'OPTION_VALUE' => 2),
				array('OPTION_NAME' => LDAP_OPT_SIZELIMIT, 			'OPTION_VALUE' => 100),
				array('OPTION_NAME' => LDAP_OPT_TIMELIMIT, 			'OPTION_VALUE' => 30),
				array('OPTION_NAME' => LDAP_OPT_PROTOCOL_VERSION, 	'OPTION_VALUE' => 3),
				array('OPTION_NAME' => LDAP_OPT_ERROR_NUMBER, 		'OPTION_VALUE' => 13),
				array('OPTION_NAME' => LDAP_OPT_REFERRALS, 			'OPTION_VALUE' => 0), // era false
				array('OPTION_NAME' => LDAP_OPT_RESTART, 			'OPTION_VALUE' => false)
			);
			
			$this->ci->adodb->ini('dbLdap', 'ldap');
			
			if (!$this->ci->dbLdap->conectado()){
				$this->ci->conf['autentucacion'] = 'bd';
				$this->ci->dbLdap = false;
			}
		}
	}

	public function autenticar($usuario = false, $clave = false){
		$this->init_autenticacion();
		
		if ($usuario === false || $clave === false){
			$this->usuario = $usuario = $this->val('nombre');
			$this->clave = $clave = $this->valor('contrasenna');
		}else{
			$this->usuario = $usuario;
			$this->clave = $clave;

			if ($usuario == '' || $clave == '')
				return false;

			$this->val('nombre', $this->usuario, true);
			$this->val('contrasenna', $this->clave, true);
		}
		
		$af = $this->db->seleccionar($this->tabla, 'autenticacion', 'usuario = \'' . $this->usuario . '\'');
		if ($af > 0){
			if ($this->db->ur('autenticacion') == 0){
				$this->ci->conf['autentucacion'] = 'bd';
			}
		}
		
		$conContrasena = true;
		$this->autenticado = false;

		if ($this->ci->conf['autentucacion'] === 'ldap'){
			$this->autenticado = $this->autenticarLdap();
			if ($this->autenticado){
				$this->guardarUsuario();
				$conContrasena = false;
			}else{
				return false;
			}
		}
		
		$this->autenticado = $this->autenticarDb($conContrasena);

		return $this->autenticado;
	}
	
	protected function autenticarLdap($usuario = false, $clave = false){
		if ($usuario !== false)
			$this->usuario = $usuario;

		if ($clave !== false)
			$this->clave = $clave;

		if (!$this->buscarUsuarioLdap($this->usuario))
			return false;

		$dn = $this->ci->dbLdap->ur('distinguishedName');

		return (@ldap_bind($this->ci->dbLdap->db->_connectionID, $dn, $this->clave));
	}
	
	public function buscarUsuarioLdap($usuario = ''){
		$usuario = trim($usuario);
		// si tiene algun caracter que no sean letras en mayuscula o minuscula no se hace la consulta, para evitar una injeccion en la busqueda
		if ($usuario === '' || preg_match('/([^a-zA-Z])+/', $usuario, $r)){
			return false;
		}
		
		$this->ci->dbLdap->query('sAMAccountName=' . $usuario);

		return ($this->ci->dbLdap->af <= 0) ? false : true;
	}
	
	protected function autenticarDb($conContrasena = true){
		if (!$this->buscarUsuarioDb($conContrasena)){
			return false;
		}
		
		$permisos = array();
		$perfiles = $this->dbSalida('perfil');
		
		if ($perfiles === false || empty($perfiles)){
			$perfiles = array();
		}else{
			$this->db->seleccionar('app_perfiles_usuarios', 'estructura', 'id in (' . implode(', ', $perfiles) . ')');
			
			foreach($this->db->rs as $permiso){
				foreach($this->db->db_array($permiso['estructura']) as $perm){
					$permisos[] = $perm;
				}
			}
		}
		
		$this->db->seleccionar('app_permisos_usuarios', 'estructura', 'usuario = ' . $this->dbSalida('id'));
		foreach($this->db->db_array($this->db->ur('estructura')) as $perm){
			$permisos[] = $perm;
		}
		
		$permisos = array_map('intval', $permisos);
		
		$this->ci->session->set_userdata(array(
			'autenticado' => true,
			'idUsuario' => $this->dbSalida('id'),
			'nombre' => $this->dbSalida('nombrecompleto'),
			'usuario' => $this->dbSalida('nombre'),
			'perfiles' => $perfiles,
			'permisos' => $permisos
		));
		
		return true;
	}
	
	protected function buscarUsuarioDb($conContrasena = true){
		$arrBuscar = array('nombre');
		if ($conContrasena === true){
			$arrBuscar[] = 'contrasenna';
		}
		
		$this->activar()->buscar($arrBuscar);
		
		return !$this->db()->eof;
	}

	protected function guardarUsuario(){
		if ($this->buscarUsuarioDb(false)){
			return false;
		}
		
		$this->db->guardar($this->tabla(), array(
			'usuario' 		=> '\'' . $this->ci->dbLdap->ur('sAMAccountName') . '\'',
			'nombre' 		=> '\'' . $this->ci->dbLdap->ur('displayName') . '\'',
			'contrasenna' 	=> '\'' . $this->val('contrasenna') . '\'',
			'cedula' 		=> '\'' . number_format(rand(1000000, 99999999), 0, '', '.') . '\'',
			'email' 		=> '\'' . $this->ci->dbLdap->ur('mail') . '\'',
			'telefono' 		=> '\'' . $this->ci->dbLdap->ur('telefono') . '\'',
			'autenticacion'	=> 1
		), false, 'id');

		return true;
	}
	
	public function cerrarSession(){
		$this->ci->session->set_userdata('autenticado', false);
	}

	public function cambiarClaveLdap($usuario = false, $clave = false){
		if ($clave === false){
			$clave = $usuario;
			$usuario = $this->usuario;
		}

		if (!$this->buscarUsuarioLdap($usuario))
			return false;

		$dn = $this->ci->dbLdap->ur('distinguishedName');
		//$clave = '{MD5}' . base64_encode(pack('H*',md5($clave)));
		//$clave = '{SHA}' . base64_encode(pack('H*', sha1($clave)));

		/*
		$nclave = '"' . $clave . '"';
		$len = strlen($nclave);
		$clave = '';

		for ($i = 0; $i < $len; $i++)
			$clave .= "{$nclave{$i}}\000";
		*/
		$clave = mb_convert_encoding($clave, 'UTF-8', 'ASCII');
		$modificar = array(
			//'displayName' => "Alejandro Mendez", //Alejandro Mendez
			//'unicodePwd'  => $clave,
			'userPassword' => $clave
		);
		var_dump($this->ci->dbLdap->db->_connectionID);
		//echo $modificar['unicodePwd'];
		return ldap_modify($this->ci->dbLdap->db->_connectionID, $dn, $modificar);
		//return ldap_mod_replace($this->ci->dbLdap->db->_connectionID, $dn, array('unicodePwd' => base64_encode($nclave)));
		//return ldap_mod_replace($this->ci->dbLdap->db->_connectionID, $dn, array('userpassword' => $clave));
	}

	public function cambiarClaveDb($usuario = false, $clave = false){
		return false; //falta realizar este metodo
	}
}
<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class usuarios extends control_base{
	public function __construct(){
		parent::__construct();
		$this->load->model('admin/usuarios_modelo', 'usuarios');
	}
	
	public function index(){
		$this->pag('admin/usuarios');
	}
	
	public function buscar(){
		//$this->usuarios->activar("id")->desactivar("pass")->buscar(array("id"));
		$this->usuarios->desactivar("pass")->buscar();
		$this->usuarios->dbSalida();
	}
	
	public function incluir(){
		if ($this->usuarios->val("pass") == "")
			$this->usuarios->desactivar("pass");
			
		$this->usuarios->actualizacionAutomatica();
	}
	
	public function modificar(){
		$this->incluir();
	}
	
	public function buscarUsuario(){
		$this->load->model('autenticacion_modelo', 'autenticacion');
		$this->autenticacion->init_autenticacion();
		
		$usuario = $this->get('usuario');
		
		$this->usuarios->forzarSalida(false);
		$this->usuarios->activar("id")->desactivar("pass")->buscar(array("usuario"));
		
		if ($this->usuarios->dbSalida('s') == 's'){// si el usuario esta en base de datos se dale y muestra el msj
			$this->salida(array('s' => 'n', 'msj' => 'Ya El Usuario Existe en Base de Datos.'));
		}elseif ($this->autenticacion->dbLdap === false){// si no se pudo conectar con el servidor de directorio activo termina la busqueda
			$this->salida(array('s' => 'n', 'msj' => 'No se Encontro el Usuario en la Base de Datos.'));
		}elseif(!$this->autenticacion->buscarUsuarioLdap($usuario)){// si el usuario no esta en base de datos ni en ldap muestra un msj de error
			$this->usuarios->dbSalida(); 
		}
		
		$salida = array(
			's' 			=> 's',
			'msj' 			=> 'Usuario Encontrado!',
			'usuario' 		=> $usuario,
			'nombre' 		=> $this->autenticacion->dbLdap->ur('displayName'),
			'email' 		=> $this->autenticacion->dbLdap->ur('mail'),
			'telefono' 		=> $this->autenticacion->dbLdap->ur('telefono'),
			'autenticacion' => 1
		);
		
		if($salida['telefono'] == false)
			$salida['telefono'] = '';
			
		$this->salida($salida);
	}
	
	public function eliminar(){
		$this->usuarios->eliminar(array("id"));
		$this->usuarios->dbSalida();
	}
	
	public function dataTable(){
		$this->usuarios->datatable();
	}
}
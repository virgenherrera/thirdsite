<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dom{
								//======================================================================================//
	//class properties 			//		Value types:
	protected $ci;				//	Codeigniter superobject
	protected $Inaccesibles = [
		'ci'
	];
	protected $domConfig;		//	info from cfg/dom.php
	protected $Layout;			//	info of the selected layout to render

	//head 								valores recibidos
	protected $title;			//	string
	protected $meta;			//	path,string,array
	protected $headLink;		//	path,
	protected $headScript;		//	path,
	protected $NoScript;		//	path,string,array

	//body
	protected $navbar;			//	lib,view,string,array
	protected $content;			//	lib,view,string,array
	protected $modal;			//	lib,view,string,array
	protected $postLink;		//	path,
	protected $postScript;		//	path,
								//======================================================================================//

	protected $errorLog;

	// Metodos magicos --------------------------------------------------------------------------------------------------//
	public function __construct(){
		//ir por una instancia de CI
		$this->ci =& get_instance();

		//cargar el helper necesario para trabajar
		$this->ci->load->helper('dom');

		//get/set class cfg
		$this->ci->config->load('dom');
		$this->domConfig = $this->ci->config->item('dom');

		//load default first layout's config
		self::loadDefaults();
	}

	//getter magico
	public function __get($propiedad){
		if( property_exists($this, $propiedad) AND !in_array($propiedad, $this->Inaccesibles) ){
			return $this->{$propiedad};
		}
	}

	//setter magico
	public function __set( $propiedad, $valor ){
		if( $propiedad === 'Layout' ){
			self::loadDefaults($valor);
		}
		elseif( $propiedad === 'title' ){
			$this->title = $valor;
		}
		elseif( in_array($propiedad, ['navbar','content','modal']) ){
			self::contentManager( $propiedad, $valor );
		}
		elseif( in_array($propiedad, ['headLink','headScript','postLink','postScript']) ){
			self::scriptStyleManager($propiedad,$valor);
		}
	}

	//to string magico
	public function __toString(){
		return $this->render(TRUE);
	}
	// -------------------------------------------------------------------------------------------------- Metodos magicos//

	// class Methods ----------------------------------------------------------------------------------------------------//
	public function loadDefaults($layout=NULL){
		$flag = (is_null($layout))?TRUE:FALSE;
		//si no se especifica el layout seleccionar al primero
		$layout = (!is_null($layout) AND is_string($layout)) ? $layout : array_keys($this->domConfig)[0];

		//setear valores x default al layout
		foreach($this->domConfig[$layout] as $key => $value) {
			self::__set( $key , $value );
		}

		//segun sea el caso sanitizar los atributos de la clase
		if( $flag ) self::reInit();

		//indicar el layout que esta siendo  usado
		$this->Layout = $layout;
	}

	protected function contentManager( $propiedad=NULL, $params=NULL ){
		// PRIMERO: prevenir ejecucion x default
		if( !is_null($propiedad) AND !is_null($params) ){
			//SEGUNDO: existe la propiedad y es accesible?
			if( property_exists($this, $propiedad) AND !in_array($propiedad, $this->Inaccesibles) ){
				//TERCERO: es string O...?
				if( is_string($params) ){
					$this->{$propiedad} .= $params . "\n";
				}
				//CUARTO: ...es array?
				elseif( is_array($params) ){
					//QUINTO: que clase de arreglo es?
					if( in_array(array_keys($params)[0], ['view','v','vista']) ){
						//SEXTO-A una view
						$this->{$propiedad} .= self::loadGetView( $params[array_keys($params)[0]] ) . "\n";
					}
					elseif( in_array(array_keys($params)[0], ['library','lib','l']) ){
						//SEXTO-B una libreria
						$this->{$propiedad} .= self::loadGetLibrary( $params[array_keys($params)[0]] ) . "\n";
					}
					elseif( in_array(array_keys($params)[0], ['string','cadena','str','s']) ){
						//SEXTO-C //varias cadenas
						$this->{$propiedad} .= implode(' ', $params[array_keys($params)[0]]) . "\n";
					}
				}
			}
		}
	} //fin contentManager

	protected function loadGetLibrary($params){
		//contar params y asignar valor
		if( count( $params ) === 3 ){
			//asignar valores
			$libreria	=	$params[0];
			$metodo		=	$params[1];
			$metParams	=	$params[2];
			//existe la lib?
			if( deveritas_archivo( APPPATH.'libraries/'.ucfirst($libreria).'.php' ) ){
				//cargar libreria
				$this->ci->load->library($libreria);
				//existe el metodo?
				if( method_exists($this->ci->{$libreria}, $metodo) ){
					//si existe el metodo ejecutalo llamando parametros
					return $this->ci->{$libreria}->{$metodo}($metParams);
				}
			}
		}
		//si solo son 2 params
		elseif( count( $params ) === 2 ){
			//asignar valores
			$libreria	=	$params[0];
			$metodo		=	$params[1];
			//existe la lib?
			if( deveritas_archivo( APPPATH.'libraries/'.ucfirst($libreria).'.php' ) ){
				//cargar libreria
				$this->ci->load->library($libreria);
				//existe el metodo?
				if( method_exists($this->ci->{$libreria}, $metodo) ){
					//si existe el metodo ejecutalo llamando parametros
					return $this->ci->{$libreria}->{$metodo}();
				}
			}
		}
	} //Fin loadGetLibrary

	protected function loadGetView($params){
		//contar params
		if( count( $params ) === 2 ){
			//asignar vars
			$path 		= 	$params[0];
			$viewParams =	$params[1];
			//existe la view?
			if(  deveritas_archivo( VIEWPATH . $path . '.php' ) ){
				//llamar-pasarparams-retornar
				return $this->ci->load->view($path,$viewParams,TRUE);
			}
		}
		elseif( count( $params ) === 1 ){
			//asignar vars
			$path 		= 	$params[0];
			//existe la view?
			if(  deveritas_archivo( VIEWPATH . $path . '.php' ) ){
				//llamar-pasarparams-retornar
				return $this->ci->load->view($path,[],TRUE);
			}
		}
	} //Fin LoadGetView

	protected function scriptStyleManager($propiedad, $param){
		$msg = '';
		if( is_array($param) ){
			foreach ($param as $key => $value) {
				if( is_string($key) AND function_exists($key)){
					//llamar a la funcion de un helper  cuando viene como ['funcion'=>'params']
					$msg .= $key($value);
				}
				elseif( is_integer($key) AND is_array($value) ){
					//sobre cargar esta funcion si viene en el formato anterior pero es  un array multidimensional
					$msg .= self::scriptStyleManager( $propiedad, $value);
				}
			}//fin ciclo
			$this->{$propiedad} .= $msg;
		}
	}

	protected function reInit(){
		$properties = [
			'Layout',
			'title',
			'meta',
			'headLink',
			'headScript',
			'NoScript',
			'navbar',
			'content',
			'modal',
			'postLink',
			'postScript',
		];

		foreach( $properties as $property ){
			$this->{$property} = NULL;
		}
	}//fin reInit

	public function render($notRender=FALSE){
		//validar que exista la view seleccionada
		if( isFile(VIEWPATH . 'layout/'. $this->Layout.'/'.$this->Layout .'.php') ){
			//setear valores que siempre deben llevar alguna info
			$this->title = (!is_null($this->title))?$this->title:base_url();

			//renderizar o regresar la view segun sea el caso
			return $this->ci->load->view('layout/'. $this->Layout.'/'.$this->Layout,[
				'public'		=> '/public/',

				'title'			=>	$this->title,
				'meta'			=>	$this->meta . "\n",
				'headLink'		=>	$this->headLink . "\n",
				'headScript'	=>	$this->headScript . "\n",
				'NoScript'		=>	$this->NoScript . "\n",
				'navbar'		=>	$this->navbar . "\n",
				'content'		=>	$this->content . "\n",
				'modal'			=>	$this->modal . "\n",
				'postLink'		=>	$this->postLink . "\n",
				'postScript'	=>	$this->postScript . "\n",
				'errorComit'	=>	$this->errorComit . "\n",
				'errorLog'		=>	$this->errorLog . "\n",
			],(bool)$notRender);
		}
		else{
			//sino tirar error fatal :/
			echo '<h1>ERROR fatal, no se encuentra el layout: '. $this->Layout .'</h1>';
			echo '<h2>reconfigura para que se encuentre en:</h2>';
			echo '<h3>VIEWPATH/layoyt/{nombreLayout}/{nombreLayout}.php</h3>';
			die();
		}
	}
	//------------------------------------------------------------------------------------------------- End class Methods//
}

/* End of file Dom.php */
/* Location: ./application/libraries/Dom.php */
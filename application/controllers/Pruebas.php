<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pruebas extends CI_Controller {
	/*
	public function index(){
		$this->load->library('dom');
			cargar string
			'el string aqui'
			
			cargar lib 
			[ 'library/lib/l' => ['libPath','method'] ]
			[ 'library/lib/l' => ['libPath','method','methodParams'] ]

			cargar vista
			[ 'view/v/vista' => ['viewPath'] ]
			[ 'view/v/vista' => ['viewPath','viewParams'] ]

			cargar varios string
			['string/cadena/str/s'=>['string1','string2',...,'stringN']]

			llamar una func helper para setear scripts styles
			['function'=>'params']
			[['function'=>'params'],['function'=>'params'],['function'=>'params']]
	}
	*/

	public function index(){

		$this->dom->content = 'hola mundo';
		echo $this->dom;

	}
}

/* End of file Pruebas.php */
/* Location: ./application/controllers/Pruebas.php */
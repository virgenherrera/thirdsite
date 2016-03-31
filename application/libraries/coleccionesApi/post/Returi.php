<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Returi
{
	protected $ci;

	public function __construct()
	{
        $this->ci =& get_instance();
	}

	public function index($postParams)
	{	
		$this->ci->load->model('omni_model','modelo');

		if( $this->ci->modelo->_insert(['tabla'=>'requests','set'=>['headers'=>serialize($this->ci->input->request_headers()), 'body'=>serialize($postParams)]]) ){
			return ['data correcltly saved, use get method to retrive the info'];
		} else {
			return ['we can not store your data properly'];
		}
	}

	

}

/* End of file Retuti.php */
/* Location: ./application/libraries/coleccionesApi/post/Retuti.php */

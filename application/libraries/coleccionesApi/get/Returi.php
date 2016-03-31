<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Returi
{
	protected $ci;

	public function __construct()
	{
        $this->ci =& get_instance();
	}

	public function index()
	{
		$this->ci->load->model('omni_model','modelo');
		$query = ['s'=>'headers, body, created_at', 'f'=>'requests'];
		$getParams =  $this->ci->uri->uri_to_assoc();
		if( $getParams && array_keys($getParams)[0] === 'request' && (int)array_values($getParams)[0] > 0 ){
			$query['w'] = ['idrequests'=> (int)array_values($getParams)[0]];
		}
		$query = $this->ci->modelo->_get($query) ;
		foreach ($query as $key => $value) {
			$query[$key]['headers'] = unserialize($value['headers']);
			$query[$key]['body'] = unserialize($value['body']);
		}
		return $query;
	}
}

/* End of file Returi.php */
/* Location: ./application/libraries/coleccionesApi/get/Returi.php */

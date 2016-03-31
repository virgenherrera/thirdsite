<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Email_to {
   var $CI;
   
    function __construct(){
	    $this->CI =& get_instance();
    }
   
	public function send($to,$cc,$cco,$subject,$html,$attach=NULL){
	   
	   
		if($to != "" && $html!= ""){
			$data['contenido'] .= $html; 
			
			$html = $this->CI->load->view('mail',$data,TRUE);
			
			$config['protocol'] = 'smtp';
			$config["smtp_user"] = 'info@fondea.org';
			$config["smtp_pass"] = 'Losfondos88'; 
			$config['smtp_host']  = "ssl://smtp.googlemail.com";
	        $config['smtp_port']  = "465";  
			$config['charset'] = 'utf-8';
			$config['wordwrap'] = TRUE;
			$config['validate'] = TRUE;
			$config['mailpath'] = '/usr/sbin/sendmail';
			$this->CI->load->library('email',$config);
			$this->CI->email->set_newline("\r\n");
			$this->CI->email->from('info@fondea.org', 'FONDEA');
			$this->CI->email->to($to, 'info');
			$this->CI->email->subject($subject);
			$this->CI->email->set_mailtype("html");
			$this->CI->email->message($html);

			if(!is_null($attach)){
				$this->CI->email->attach($attach);
			}
				
			if ($this->CI->email->send()) {
				return 1;
			}else{
				return 2;
			}
		}else{
			return 3;
		}
	}
}
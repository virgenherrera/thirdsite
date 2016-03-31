<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if(!function_exists('deveritas_archivo')){
	function deveritas_archivo($file_path=FALSE,$writable=FALSE){
		if(is_string($file_path)&&$writable===FALSE){
			$msg = (file_exists($file_path)&&is_file($file_path)&&!is_dir($file_path))?TRUE:FALSE;
		}
		elseif(is_string($file_path)&&$writable!==FALSE){
			$msg = (file_exists($file_path)&&is_writable($file_path)&&is_file($file_path)&&!is_dir($file_path))?TRUE:FALSE;
		}
		else{
			$msg = FALSE;
		}
		return $msg;
	}
}

if(!function_exists('deveritas_carpeta')){
	function deveritas_carpeta($folder_path=NULL){
		if(!is_null($folder_path)){
			return  (
						is_dir(str_replace('index.php','',$_SERVER['SCRIPT_FILENAME']).$folder_path) AND 
						!is_file(str_replace('index.php','',$_SERVER['SCRIPT_FILENAME']).$folder_path)
					)
					?TRUE:FALSE;
		}
		else FALSE;
	}
}

if(!function_exists('print_array')){
	function print_array($data,$die=false){
		echo "<pre>";
		var_dump($data);
		echo "</pre><br>";
		if($die){
			die();
		}
	}
}

if (!function_exists('no_acentos')) {
	function no_acentos($String){
		$String = str_replace(array('á','à','â','ã','ª','ä'),"a",$String);
		$String = str_replace(array('Á','À','Â','Ã','Ä'),"A",$String);
		$String = str_replace(array('Í','Ì','Î','Ï'),"I",$String);
		$String = str_replace(array('í','ì','î','ï'),"i",$String);
		$String = str_replace(array('é','è','ê','ë'),"e",$String);
		$String = str_replace(array('É','È','Ê','Ë'),"E",$String);
		$String = str_replace(array('ó','ò','ô','õ','ö','º'),"o",$String);
		$String = str_replace(array('Ó','Ò','Ô','Õ','Ö'),"O",$String);
		$String = str_replace(array('ú','ù','û','ü'),"u",$String);
		$String = str_replace(array('Ú','Ù','Û','Ü'),"U",$String);
		$String = str_replace(array('[','^','´','`','¨','~',']'),"",$String);
		$String = str_replace("ç","c",$String);
		$String = str_replace("Ç","C",$String);
		$String = str_replace("ñ","n",$String);
		$String = str_replace("Ñ","N",$String);
		$String = str_replace("Ý","Y",$String);
		$String = str_replace("ý","y",$String);
		 
		$String = str_replace("&aacute;","a",$String);
		$String = str_replace("&Aacute;","A",$String);
		$String = str_replace("&eacute;","e",$String);
		$String = str_replace("&Eacute;","E",$String);
		$String = str_replace("&iacute;","i",$String);
		$String = str_replace("&Iacute;","I",$String);
		$String = str_replace("&oacute;","o",$String);
		$String = str_replace("&Oacute;","O",$String);
		$String = str_replace("&uacute;","u",$String);
		$String = str_replace("&Uacute;","U",$String);
		return $String;
	}
}

if(!function_exists('pre_recompensas')){
	function pre_recompensas($Request,$where){
		if( $Request['init']['manega_recompensa'] == 1 ){
			if($where!==$Request['primer']&&is_array($Request['primer'])){
				$resultado['a'] = $Request['primer'][0]['a'];
				$resultado['b'] = $Request['primer'][0]['b'];
				$resultado['proyecto'] = $Request['primer'][0]['proyecto'];
				$resultado['owner'] = $Request['primer'][0]['owner'];
				$resultado['imagen'] = (!file_exists(base_url('imgs/proyecto').'/'.$Request['primer'][0]['imagen']))?NULL:base_url('imgs/proyecto').'/'.$Request['primer'][0]['imagen'].'.jpg';
				$resultado['descripcion'] = $Request['primer'][0]['descripcion'];
				$resultado['flag'] = TRUE;
				$resultado['recompensas'] = $Request['primer'];
			}
		}
		elseif ( $Request['init']['manega_recompensa'] == 2 ) {
			if(count($Request['init'])===1){
				$resultado['a'] = $Request['init']['a'];
				$resultado['b'] = $Request['init']['b'];
				$resultado['proyecto'] = $Request['init']['proyecto'];
				$resultado['imagen'] = (!file_exists(base_url('imgs/proyecto').'/'.$Request['init']['imagen']))?NULL:base_url('imgs/proyecto').'/'.$Request['init']['imagen'].'jpg';
				$resultado['descripcion'] = $Request['init']['descripcion'];
			}
		}
		return $resultado;
	}
}

if(!function_exists('diferenciaf')){
	function diferenciaf($fecha=FALSE){
		if($fecha===FALSE){
			$resultado = 'Necesito la fecha inicial y final en formato YYYY-MM-DD';
		}
		else{
			$fecha['actual'] = (isset($fecha['actual']))?$fecha['actual']:date('Y-m-d');	
			$fin = new DateTime($fecha['fin']);
			$actual = new DateTime($fecha['actual']);
			$intervalo = $actual->diff($fin);
			$resultado = $intervalo->format('%R%a'); //posible concatenar ' dias'
		}
		return $resultado;
	}
}

if(!function_exists('token')){
	function token()
    {
    	
        $token = md5(uniqid(rand(),true));
        return $token;
		
    }
}

if(!function_exists('generateToken')){
	function generateToken($longitud = 40){
		//un array perfecto para crear claves
		$chars	=
				[
					'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm',
					'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
					'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
					'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
					'0', '1', '2', '3', '4', '5', '6', '7', '8', '9','-','_',
				];
		//desordenamos el array chars
		shuffle(shuffle($chars));
		$num_chars = count($chars) - 1;
		$token = FALSE;
	 
		//creamos una ficha de 40 carácteres (x default)
		for ($i = 0; $i < $longitud; $i++){
			$token .= $chars[mt_rand(0, $num_chars)];
		}
		return $token;
	}
}

if(!function_exists('btn_edit_texto')){
	function btn_edit_texto($id)
    {
    	if($id != ""){
	    	$CI =& get_instance();
	    	$queryd = ['tabla'=>'seccion_textos',
	     		           'queryMods'=>[ 'where'=>"etiqueta = '".$id."'"]];
		    $row = $CI->omni_model->_get($queryd);
	    	$html = '';
	    	if($CI->session->userdata['idiom'] ==  'spanish'){
		    	$texto = $row[0]['texto_sp'];
	    	}else{
		    	$texto = $row[0]['texto_en'];
	    	}
	    	if( isset( $CI->session->userdata['Loggeado']['profile'] ) ){
		    	if($CI->session->userdata['Loggeado']['profile'] == "admin"){
			    	$html = $texto.'<a class="btn-round btn btn-success btn-bordered open-modal-text" data-id="'.$id.'"><i class="fa fa-pencil"></i></a>';
		    	}
	    	}else{
		    	$html = $texto;		    	
	    	}
    	}
    	
		return $html;
		
    }
}

if(!function_exists('generar_qr')){
	function generar_qr($data=FALSE,$logo=FALSE,$save_name=FALSE,$size=FALSE){
		// Obtiene un codigo QR desde Google Chart API y le sobrepone una imagen (logo)
		// http://code.google.com/apis/chart/infographics/docs/qr_codes.html
		if($data===FALSE){
			return FALSE;
		}
		elseif(is_string($data)){
			$data = (!empty($data))?$data:'No se recibieron datos para codificar';
			$logo = (deveritas_archivo($logo))?$logo:FALSE;
			$save_name = (is_string($save_name))?'./imgs/qr-codes/donaciones/'.$save_name:'./imgs/qr-codes/donaciones/temp_qr.png';
			switch ($size) {
				case 1 :
					$size = '100x100';
				break;
				case 2 :
					$size = '200x200';
				break;
				case 3 :
					$size = '300x300';
				break;
				case 4 :
					$size = '400x400';
				break;
				case 5 :
					$size = '500x500';
				break;
				
				default:
					$size = '300x300';
				break;
			}

			header('Content-type: image/png');
			$request = 'https://chart.googleapis.com/chart?cht=qr&chld=H|1&chs='.$size.'&chl='.urlencode($data);
			$QR = imagecreatefrompng($request);
			if($logo !== FALSE){
				$logo = imagecreatefromstring(file_get_contents($logo));
				$QR_width = imagesx($QR);
				$QR_height = imagesy($QR);
				
				$logo_width = imagesx($logo);
				$logo_height = imagesy($logo);
				
				// Scale logo to fit in the QR Code
				$logo_qr_width = $QR_width/3;
				$scale = $logo_width/$logo_qr_width;
				$logo_qr_height = $logo_height/$scale;
				
				imagecopyresampled($QR, $logo, $QR_width/3, $QR_height/3, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
			}
			//imagepng($QR,$save_name);
			imagedestroy($QR);
			return $save_name;
		}
	}
}

if( !function_exists('trepaFile') ){
	function trepaFile($parametros=NULL){
		if( !is_null( $parametros ) ){
			if( deveritas_carpeta( $parametros['upload_path'] )
					AND is_string( $parametros['file_name'] )
						AND !empty($parametros['userfile']) ) {
				/*	Validar/setear default config -------------------------------------------------------------------*/
				$config['upload_path']		=	str_replace('index.php','',$_SERVER['SCRIPT_FILENAME']).$parametros['upload_path'];
				$config['allowed_types']	=	$parametros['allowed_types'];
				$config['file_name']		=	$parametros['file_name'];
				$config['file_ext_tolower']			= (!empty($parametros['file_ext_tolower']) AND is_bool($parametros['file_ext_tolower']))	?	$parametros['file_ext_tolower']	:	FALSE;
				$config['allowed_types']			= (!empty($parametros['allowed_types']) AND is_string($parametros['allowed_types']))	?	$parametros['allowed_types']	:	'jpg|gif|png';
				$config['overwrite']				= (!empty($parametros['overwrite'])		AND is_bool($parametros['overwrite']))			?	$parametros['overwrite']		:	TRUE;
				$config['max_size']					= (!empty($parametros['max_size'])		AND is_integer($parametros['max_size']))		?	$parametros['max_size']		:	0;
				$config['max_width']				= (!empty($parametros['max_width'])		AND is_integer($parametros['max_width']))	?	$parametros['max_width']	:	0;
				$config['max_height']				= (!empty($parametros['max_height'])		AND is_integer($parametros['max_height']))	?	$parametros['max_height']	:	0;
				$config['min_width']				= (!empty($parametros['min_width'])		AND is_integer($parametros['min_width']))	?	$parametros['min_width']	:	0;
				$config['min_width']				= (!empty($parametros['min_width'])		AND is_integer($parametros['min_width']))	?	$parametros['min_width']	:	0;
				$config['min_height']				= (!empty($parametros['min_height'])		AND is_integer($parametros['min_height']))	?	$parametros['min_height']	:	0;
				$config['max_filename']				= (!empty($parametros['max_filename'])	AND is_integer($parametros['max_filename']))	?	$parametros['max_filename']	:	100;
				$config['max_filename_increment']	= (!empty($parametros['max_filename_increment'])		AND is_integer($parametros['max_filename_increment']))	?	$parametros['max_filename_increment']	:	0;
				$config['encrypt_name']				= (!empty($parametros['encrypt_name'])	AND is_bool($parametros['encrypt_name']))		?	$parametros['encrypt_name']	:	FALSE;
				$config['remove_spaces']			= (!empty($parametros['remove_spaces'])	AND is_bool($parametros['remove_spaces']))		?	$parametros['remove_spaces']:	TRUE;
				$config['detect_mime']				= (!empty($parametros['detect_mime'])	AND is_bool($parametros['detect_mime']))			?	$parametros['detect_mime']	:	TRUE;
				$config['mod_mime_fix']				= (!empty($parametros['mod_mime_fix'])	AND is_bool($parametros['mod_mime_fix']))		?	$parametros['mod_mime_fix']	:	TRUE;
				/*------------------------------------------------------------------- Validar/setear default config */

				//llamar una instancia del super objeto CI
				$CI =& get_instance();
				//cargar la libreria upload
				$CI->load->library('upload' , $config);

				//a procesar con do_upload y validar la respuesta
				if ( !$CI->upload->do_upload( $parametros['userfile'] ) ){
					//si no se logro la subida
					return 	[
								'status' => FALSE,
								'response' => 	[
													'msg' => $CI->upload->display_errors(),
													'data'	=>	$CI->upload->data(),
												],
							];
				}
				else{

					return	[
								'status' => TRUE,
								'response' => $CI->upload->data(),

							];
				}
			}//fin validaciones principales
			else{
				return	[
							'status' => FALSE,
							'response' => 	[
												'msg' => 'ERROR al correr las pruebas logicas, revisa x favor',
												'upload_path' => deveritas_carpeta( $parametros['upload_path'] ) ,
												'file_name'	=> is_string( $parametros['file_name'] ),
												'userfile'	=>	!empty($parametros['userfile']),
											],
						];
			}			
		}
		else {
			return	[
						'status'	=>	FALSE,
						'response'	=>	[
											'msg' => 'hey revisa tus parametos, no puedo hacer nada con parameteos nulos',
										],
					];
		}
	}//fin helper trepaFile
}
//Fin del HELPER Debugger
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if( !function_exists('head') ){
	function head($extraLines = NULL){
		$ci =& get_instance();
		$msg  = '';
		$msg .= '<title>' . $ci->dom->title. '</title>' . "\n";
		$msg .= $ci->dom->meta . "\n";
		$msg .= $ci->dom->headLink . "\n";
		$msg .= $ci->dom->headScript . "\n";
		$msg .= (is_string($extraLines))?$extraLines:'';
		$msg .= (!empty($ci->dom->NoScript)) ? '<noscript>' . "\n\t" . $ci->dom->NoScript . "\r\n\t" . '</noscript>' . "\n":'';
		return $msg;
	}
}

if(!function_exists('content')){
	function content($commits=TRUE){
		$ci =& get_instance();
		$msg = '';

		$msg .= ($commits)?'<!-- section navbar -->' . "\n":'';
		$msg .= $ci->dom->navbar . "\n";
		$msg .= ($commits)?'<!-- End section navbar -->' . "\n":'';

		$msg .= ($commits)?'<!-- section content -->' . "\n":'';
		$msg .= $ci->dom->content . "\n";
		$msg .= ($commits)?'<!-- End section content -->' . "\n":'';

		$msg .= ($commits)?'<!-- section modal -->' . "\n":'';
		$msg .= $ci->dom->modal . "\n";
		$msg .= ($commits)?'<!-- End section modal -->' . "\r\n":'';

		$msg .= ($commits)?'<!-- section postLink -->' . "\n":'';
		$msg .= $ci->dom->postLink . "\n";
		$msg .= ($commits)?'<!-- End section postLink -->' . "\n":'';

		$msg .= ($commits)?'<!-- section postScript -->' . "\n":'';
		$msg .= $ci->dom->postScript . "\n";
		$msg .= ($commits)?'<!-- End section postScript -->' . "\n":'';

		return $msg;
	}
}

if(!function_exists('errors')){
	function errors(){
		$ci =& get_instance();
		$msg = '';

		$msg .= '<!-- section errorLog ' . "\n";
		$msg .= $ci->dom->errorLog . "\n";
		$msg .= '<!-- End section errorLog -->' . "\n";

		return $msg;
	}
}

if(!function_exists('domImport')){
	/*
	** recibe el(los) path(s) de un archivo dentro de la carpeta public y devuelve un string para incluirlo en html
	//	devuelve string
	*/
	function domImport($filePath){
		//checar el tipo de dato
		if( is_string($filePath) ){
			//validar que el archivo exista
			if( is_string( asset($filePath) ) ){
				switch ( parseExt( asset($filePath) ) ) {
					case 'css':
						return  '<link rel="stylesheet" href="'. asset($filePath) .'" />' . "\n";
					break;

					case 'html':
						return  '<link rel="import" href="'. asset($filePath) .'" />' . "\n";
					break;

					case 'js':
						return  '<script src="'. asset($filePath) .'"></script>' . "\n";
					break;
				}
			} else { return  '<!-- No existe el archivo: '. $filePath .' en la carpeta public -->' . "\n"; }
		}
		elseif( is_array($filePath) ){
			$msg = '';
			foreach( $filePath as $key => $value ){
				$msg .= domImport($value);
			}
			return $msg;
		}
		else { return  '<!-- error: esta funcion solo acepta string o array -->' . "\n"; }
	} //fin domImport
}

if(!function_exists('asset')){
	/*
	//recibe el path de un archivo y si este existe dentro de la carpeta public devuelve /public/ . $filePath sino FALSE
	** devuelve string(publicFilePath) || FALSE
	*/
	function asset($filePath){
		if( isFile('./public/'.$filePath) ){
			return '/public/' . $filePath;
		} else { return FALSE; }
	} //fin asset
}

if(!function_exists('isFile')){
	/*
	** recibe la ruta a un archivo (string) e indica si es archivo
	//	devuelve TRUE || FALSE
	*/
	function isFile($file_path=FALSE,$writable=FALSE){
		if(is_string($file_path)&&$writable===FALSE){
			return (file_exists($file_path)&&is_file($file_path)&&!is_dir($file_path))?TRUE:FALSE;
		}
		elseif(is_string($file_path)&&$writable!==FALSE){
			return (file_exists($file_path)&&is_writable($file_path)&&is_file($file_path)&&!is_dir($file_path))?TRUE:FALSE;
		}
		else{
			return FALSE;
		}
	}

	if(!function_exists('parseExt')){
		/*
		** recibe un nombre de archivo (string) y devuelve su extension
		//	devuelve str ext || FALSE
		*/
		function parseExt($file=NULL){
			if(!is_null($file)){
				$arr = explode('.',$file);
				$size = count($arr) - 1;
				return $arr[$size];
			} else { return FALSE; }
		}
	}
}

/* End of file dom_helper.php */
/* Location: ./application/helpers/dom_helper.php */
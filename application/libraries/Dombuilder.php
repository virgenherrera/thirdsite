<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Dom Class
 * @author		virgenherrera
 */
class Dom {
//Atributos ------------------------------------------------------------------------------//
	private $CI;
	private $config;
	private $NL			= "\n";
	private $TAB		= "\t";
	private $Ugly		= FALSE;
	private $normal_tags= ['a','abbr','acronym','address','applet','article','aside','audio','b','basefont','bdi','bdo','big','blockquote','body','button','canvas','caption','center','cite','code','colgroup','datalist','dd','del','details','dfn','dialog','dir','div','dl','dt','em','fieldset','figcaption','figure','font','footer','form','frame','frameset','h1','h2','h3','h4','h5','h6','head','header','html','i','iframe','ins','kbd','label','legend','li','main','map','mark','menu','menuitem','meter','nav','noframes','noscript','object','ol','optgroup','option','output','p','pre','progress','q','rp','rt','ruby','s','samp','script','section','select','small','span','strike','strong','style','sub','summary','sup','table','tbody','td','textarea','tfoot','th','thead','time','title','tr','tt','u','ul','var','video'];
	private $self_closed= ['area','base','br','col','command','embed','hr','img','input','keygen','link','meta','param','source','track','wbr'];

	private $head;
	private $head_attribs;
	private $doctype;
	private $title; 	//======== Almacena string hasta el momento del build =========//
	private $favicon;	//======== Almacena BOOL hasta el momento del build ===========//
	private $metas;		//========	|
	private $links;		//			|
	private $scripts;	//			Almacenan array hasta el momento del build
	private $no_script; //			|											=======//

	private $body;
	private $body_attribs;
	private $body_request;
	private $post_links;
	private $post_scripts;

	private $HTML_atribs;
	public	$doc;
	public	$log; //alimentada por callback
//------------------------------------------------------------------------------ATRIBUTOS //

//Metodos ------------------------------------------------------------------------------//
	
	public function __construct(){
		//cargar superobjeto CodeIgniter en $this->CI
		$this->CI=&get_instance();
		//carga de helpers y config
		$this->CI->load->helper('html','url');
		$this->CI->config->load('dom');
		//guardar config
		$this->config = $this->CI->config->item('dom');
		//configurar la belleza del DOM
		$this->ugly_output();
		//setear la version del documento
		$this->set_doctype();
		//setear atributos HEAD & BODY
		$this->set_htmlheadbody_atributes();
		//preconstruir head
		$this->add_title();
		$this->set_favicon();
		$this->add_metatag();
		$this->add_link(FALSE,TRUE);
		$this->add_script(FALSE,TRUE);
		$this->add_no_script(FALSE);

		//Preconstruir BODY
		$this->initialize_body();
		$this->add_script(FALSE,FALSE);
		$this->add_link(FALSE,FALSE);

	 	//avisarle a codeigniter que se ha cargado esta clase
		log_message('info','Clase Dom Inicializada');
	}//fin de __construct

//METODOS del HEAD HTML ------------------------------------------------------------------------------//
	public function set_doctype($version='html5'){
		include(APPPATH.'config/doctypes.php');
		$this->doctype = NULL; //<=== x alguna razon tenia datos, solo x seguridad limpiar antes d usar
		$version = (array_key_exists($version, $_doctypes))?$version:'html5';
		$this->doctype = $_doctypes[$version];
		$this->dom_log( __METHOD__,$version );
	}//fin set_doctype

	private function set_htmlheadbody_atributes(){
		if( isset( $this->config['head_atribs'] ) ){
			$this->head_attribs = (is_string($this->config['head_atribs']))?$this->config['head_atribs']:FALSE;
		}
		if( isset( $this->config['body_atribs'] ) ){
			$this->body_attribs = (is_string($this->config['body_atribs']))?$this->config['body_atribs']:FALSE;
		}
		if( isset( $this->config['html_atribs'] ) ){
			$this->HTML_atribs = (is_string($this->config['html_atribs']))?$this->config['html_atribs']:FALSE;
		}
	}//fin set_htmlheadbody_atributes

	public function ugly_output($Format='FALSE'){
		if(isset($this->config['UglyOutput'])&&$Format==='FALSE'){
			$this->Ugly = (is_bool($this->config['UglyOutput']))?TRUE:FALSE;
		}
		elseif(is_bool($Format)){
			$this->Ugly = $Format;
		}
	}

	private function initialize_body(){
		if(isset($this->config['body_init'])){
			foreach ($this->config['body_init'] as $view) {
				if(is_string($view)){
					$this->import_view($view);
				}
			}
		}
	}

	public function add_title($title_text=FALSE){
		if($title_text===FALSE){
			if(isset($this->config['title'])&&is_string($this->config['title'])){
				$this->title .= $this->config['title'];
				$this->dom_log(__METHOD__,'titulo preconstruido por config');
			}
		}
		elseif (is_string($title_text)){
			$this->title .= $title_text;
			$this->dom_log(__METHOD__,$textarea);
		}
	}//fin title

	public function set_favicon($favicon=FALSE){
		if($favicon===FALSE){
			if(isset($this->config['favicon'])&&$this->config['favicon']===TRUE){
				$this->favicon = $this->config['favicon'];
				$this->dom_log(__METHOD__,'favicon preconstruido por config');
			}
		}
		elseif (is_bool($favicon)){
			$this->title = $favicon;
			$this->dom_log(__METHOD__,$textarea);
		}
	}//fin favicon

	public function add_metatag($meta_text=FALSE){
		if($meta_text===FALSE){
			if( is_array($this->config['meta_tags']) ){
				foreach ($this->config['meta_tags'] as $key => $value) {
					$meta['tag'] = 'meta';
					$meta['atr'] = $value;
					$this->dom_log(__METHOD__,'Metatexto agregado por config/dom.php');
					$this->metas[] = $meta;

				}
			}
			elseif(is_string($this->config['meta_tags'])){
				$meta['tag'] = 'meta';
				$meta['atr'] = $this->config['meta_tags'];
				$this->dom_log(__METHOD__,'Metatexto agregado por config/dom.php');
				$this->metas[] = $meta;
			}
		}//fin default
		elseif( is_string($meta_text) ){
			$meta['tag'] = 'meta';
			$meta['atr'] = $meta_text;
			$this->dom_log(__METHOD__,$meta_text);
			$this->metas[] = $meta;
		}
		elseif( is_array($meta_text) ){
			foreach ($meta_text as $key => $value) {
				$meta['tag'] = 'meta';
				$meta['atr'] = $value;
				$this->dom_log(__METHOD__,$value);
				$this->metas[] = $meta;
			}
		}
	}//fin metatag

	public function add_link($file_name=FALSE,$placementHead=TRUE){
		$linkspath = base_url('assets/css');
		if($file_name===FALSE&&$placementHead===TRUE){
			foreach ($this->config['links'] as $key => $value) {
				$this->html_include('link',$linkspath.$value,$placementHead);
			}
			$this->dom_log(__METHOD__,'autogenerado por config/dom');
		}//fin comportamient x default
		elseif($file_name===FALSE&&$placementHead!==TRUE){
			if(isset($this->config['post_links'])){
				foreach ($this->config['post_links'] as $key => $value) {
					$this->html_include('link',$linkspath.$value,FALSE);
				}
			}
			$this->dom_log(__METHOD__,'autogenerado por config/dom');
		}//fin POST_comportamiento x default
		elseif(is_string($file_name)&&$placementHead===TRUE){
			$this->html_include('link',$linkspath.$file_name,$placementHead);
			$this->dom_log(__METHOD__,'generado por el usuario');
		}//fin string>HEAD
		elseif(is_string($file_name)&&$placementHead!==TRUE){
			$this->html_include('link',$linkspath.$file_name,FALSE);
			$this->dom_log(__METHOD__,'generado por el usuario');
		}//fin String>body
		elseif(is_array($file_name)&&$placementHead===TRUE){
			foreach ($$file_name as $key => $value) {
				$this->html_include('link',$linkspath.$value,$placementHead);
			}
			$this->dom_log(__METHOD__,'generado por el usuario');
		}//fin Array>HEAD
		elseif(is_array($file_name)&&$placementHead!==TRUE){
			foreach ($$file_name as $key => $value) {
				$this->html_include('link',$linkspath.$value,$FALSE);
			}
			$this->dom_log(__METHOD__,'generado por el usuario');
		}//fin Array>HEAD	
	}//fin link

	public function add_script($file_name=FALSE,$placementHead=TRUE){
		$scripts_path = base_url('assets/js');
		if($file_name===FALSE&&$placementHead===TRUE){
			foreach ($this->config['scripts'] as $key => $value) {
				$this->html_include('script',$scripts_path.$value,$placementHead);
			}
			$this->dom_log(__METHOD__,'autogenerado por config/dom');
		}//fin comportamient x default
		elseif($file_name===FALSE&&$placementHead!==TRUE){
			foreach ($this->config['post_scripts'] as $key => $value) {
				$this->html_include('script',$scripts_path.$value,FALSE);
			}
			$this->dom_log(__METHOD__,'autogenerado por config/dom');
		}//fin POST_comportamiento x default
		elseif(is_string($file_name)&&$placementHead===TRUE){
			$this->html_include('script',$scripts_path.$file_name,$placementHead);
			$this->dom_log(__METHOD__,'generado por el usuario');
		}//fin string>HEAD
		elseif(is_string($file_name)&&$placementHead!==TRUE){
			$this->html_include('script',$scripts_path.$file_name,FALSE);
			$this->dom_log(__METHOD__,'generado por el usuario');
		}//fin String>body
		elseif(is_array($file_name)&&$placementHead===TRUE){
			foreach ($$file_name as $key => $value) {
				$this->html_include('script',$scripts_path.$value,$placementHead);
			}
			$this->dom_log(__METHOD__,'generado por el usuario');
		}//fin Array>HEAD
		elseif(is_array($file_name)&&$placementHead!==TRUE){
			foreach ($$file_name as $key => $value) {
				$this->html_include('script',$scripts_path.$value,$FALSE);
			}
			$this->dom_log(__METHOD__,'generado por el usuario');
		}//fin Array>HEAD	
	}//fin script

	public function add_no_script($noscript_content=FALSE){
		$noscript_path = base_url('assets/css');
		$result['tag'] = 'noscript';
		if($noscript_content===FALSE){
			foreach ($this->config['no_script'] as $key => $value) {
				$result['chl'] = array('tag'=>'link','atr'=>'rel="stylesheet" href="'.$noscript_path.$value);
			}
		}
		elseif(is_string($noscript_content)){
			$result['chl'] = array('tag'=>'link','atr'=>'rel="stylesheet" href="'.$noscript_path.$noscript_content);
		}
		elseif(is_array($noscript_content)){
			foreach ($noscript_content as $key => $value) {
				$result['chl'] = array('tag'=>'link','atr'=>'rel="stylesheet" href="'.$noscript_path.$value);
			}
		}
		$this->no_script = $result;
	}//fin no_sript

	private function html_include($type=FALSE,$file_path=FALSE,$placementHead=TRUE){
		$type		= (is_string($type))?$type:'';
		$file_path	= (is_string($file_path))?$file_path:'';
		if($placementHead===TRUE){
			if($type==='link'){
				$link['tag'] = 'link';
				$link['atr'] = 'rel="stylesheet" href="'.$file_path.'"';
				$this->links[] = $link;
			}
			elseif($type==='script'){
				$script['tag'] = 'script';
				$script['atr'] = 'src="'.$file_path.'"';
				$this->scripts[] = $script;
			}
		}
		elseif($placementHead!==TRUE){
			if($type==='link'){
				$link['tag'] = 'link';
				$link['atr'] = 'rel="stylesheet" href="'.$file_path.'"';
				$this->post_links[] = $link;
			}
			elseif($type==='script'){
				$script['tag'] = 'script';
				$script['atr'] = 'src="'.$file_path.'"';
				$this->post_scripts[] = $script;
			}
		}
	}//fin html_include

	public function view($view_name,$view_params){
		if(is_string($view_name)){
			$this->import_view($view_name,$view_params);
		} else { return FALSE; }
	}

	private function build_head(){
		//coonvertir en request los elementos faltantes
		$this->favicon 	= ($this->favicon)?array('tag'=>'link','atr'=>'rel="shortcut icon" type="image/x-icon" href="'.base_url().'favicon.ico"' ):[];
		$this->title 	= array('tag'=>'title','txt'=>$this->title);

		//Sanitizar contenedor, compilar, concatenar y liberar request
		$this->head	=	FALSE;
		$tags = ['title','favicon','metas','links','scripts','no_script'];
		foreach ($tags as $tag) { $this->tag_merchant($tag,'head'); }

		//encerrarlo dentro del tag 'head' y agregarle atribs y tab
		$this->head = $this->wrap_string($this->head,'head',$this->head_attribs,.1);
	}


//------------------------------------------------------------------------------METODOS del HEAD HTML //

//METODOS del BODY HTML ------------------------------------------------------------------------------//
	private function build_body(){
		//Sanitizar contenedor, compilar, concatenar y liberar request
		$this->body = FALSE;
		if(!is_null($this->body_request)){
			$this->tag_merchant('body_request','body');
		}
		$tags = [ 'post_links','post_scripts' ];
		foreach ($tags as $tag){ $this->tag_merchant($tag,'body'); }
		//Secciones al declarar body

		//envolverlo dentro del tag body y otorgarle sus atributos
		$this->body = $this->wrap_string($this->body,'body',$this->body_attribs,.1);
	}//fin_build_body
//------------------------------------------------------------------------------METODOS del BODY HTML //
//Constructores de DOM ------------------------------------------------------------------------------//

	private function import_view($view_name=FALSE,$view_params=NULL){
		//integra una view de CI dentro del tag body
		if( deveritas_archivo( VIEWPATH.$view_name.'.php' ) ){
			$this->body_request[] = $this->CI->load->view($view_name,$view_params,TRUE);
		}
	}

	private function tag_merchant($tag_name=FALSE,$Container_name=FALSE){
		//este metodo compila las secciones dentro de un container y libera la memoria del request
		if( property_exists( $this , $tag_name ) && property_exists( $this, $Container_name ) ){
			$this->{ $Container_name } .= $this->analizar_y( $this->{ $tag_name } );
			unset( $this->{$tag_name} );
		}
	}

	public function wrap_string($text=FALSE,$wrapper='section',$wrap_atribs='',$tab_index=1){
		$msg='';
		if(is_string($text)&&!empty($text)){
			$wrapper = (is_string($wrapper))?$wrapper:'';
			$wrap_atribs = (is_string($wrap_atribs))?$wrap_atribs:'';
			$msg = $this->analizar_y(array('tag'=>$wrapper,'atr'=>$wrap_atribs,'txt'=>$text),'','',$tab_index);
		}
		return $msg;
	}

	public function build_document($auto_render=TRUE){
		$this->build_head();
		$this->build_body();
		$this->HTML_atribs;
		$this->doc .= $this->doctype;
		$this->doc .= $this->wrap_string($this->head.$this->body,'html',$this->HTML_atribs,.1);
		if($auto_render===TRUE){
			$this->render_dom();
		} else { return ($auto_render!==TRUE)?TRUE:$this->render_dom(FALSE); }
	}

	public function render_dom($not_render=FALSE){
		$data['dom'] = $this->doc;
		if($not_render===FALSE){
			$this->CI->load->view('dom/document',$data);
		} else { return $this->CI->load->view('dom/document',$data,TRUE); }
	}
//---------------------------------------------------------------------------------Constructores de DOM//
//Constructores de HTML ------------------------------------------------------------------------------//
	private function sintactica($tag_name){
		//1er analisis: es un tag que existe
		$sintax['valid_tag'] = (array_search($tag_name, array_merge($this->normal_tags,$this->self_closed))!==FALSE)?TRUE:FALSE;
		//2do analisis: es normal o self-closed
		if($sintax['valid_tag']){
			$sintax['type'] = (array_search($tag_name, $this->normal_tags)!==FALSE)?'normal':'self-closed';
		}
		return $sintax;
	}//sintactica

	private function valid_wrapper($wrap_tag){
		return in_array($wrap_tag, $this->normal_tags);
	}

	private function porcion($tag_name=FALSE,$attrib=FALSE,$text=FALSE,$tab_cant=1){
		$nl = ($this->Ugly)?'':$this->NL;
		$tab = ($this->Ugly)?'':$this->TAB;
		$attrib	= ($attrib===FALSE)?'':$attrib;
		$text	= ($text===FALSE||$text==='')?'':$nl.str_repeat($tab,$tab_cant+1).$text.$nl.str_repeat($tab,$tab_cant);
		$fragmento = '';
		if(is_string($tag_name)&&$this->sintactica($tag_name)['valid_tag']){
			if($this->sintactica($tag_name)['type']==='normal'){
				$fragmento = str_repeat($tab,$tab_cant).'<'.$tag_name.' '.$attrib.'>'.$text.'</'.$tag_name.'>';
			}//tag normal
			elseif($this->sintactica($tag_name)['type']==='self-closed') {
				$fragmento = str_repeat($tab,$tab_cant). '<'.$tag_name.' '.$attrib.' />';
			}//tag self-closed
		}//fin es string y un tag valido
		return $fragmento;
	}//porcion

	private function analizar_x($request=FALSE,$wrapper=FALSE,$wrap_atribs='',$tab_index=1){
		//analizar en X
		$nl		= ($this->Ugly)?'':$this->NL;
		$tab	= ($this->Ugly)?'':$this->TAB;
		$tab_lv = ($wrapper===FALSE)?str_repeat($tab,$tab_index-1):str_repeat($tab,$tab_index);
		$close_w= ($wrapper===FALSE)?'':'</'.$wrapper.'>'.$nl;
		$parcial= ($wrapper===FALSE)?$tab_lv.$nl:$tab_lv.'<'.$wrapper.' '.$wrap_atribs.'>'.$nl;
		if($request===FALSE){
			$parse .= str_repeat($tab,++$tab_index).'<!-- No se puede construir una peticion vacia -->'.$nl;
			$flag = TRUE;
		}//fin default
		elseif(is_array($request)){
			//nueva forma request
			$tag	= (isset($request['tag']))?$request['tag']:'';
			$atr	= (isset($request['atr']))?$request['atr']:'';
			$txt	= (isset($request['txt']))?$request['txt']:'';
			$txb	= (isset($request['txb'])&&$request['txb']!==FALSE)?TRUE:FALSE;
			$child	= (isset($request['chl'])&&is_array($request['chl']))?$this::analizar_x($request['chl'],$wrapper,$wrap_atribs,$tab_index+1):'';
			if($txb===TRUE){
				$parcial	.= $this->porcion($tag,$atr,$txt.$child,$tab_index);
				$flag = TRUE;
			}//fin texto antes de child
			elseif($txb===FALSE){
				$parcial	.= $this->porcion($tag,$atr,$child.$txt,$tab_index);
				$flag = TRUE;
			}//fin texto despues de child
			else{
				$flag = FALSE;
			}
		}//fin es array
		$tabsyenter = $flag?$tab_lv.$close_w:'';
		return $parcial.$tabsyenter;
	}//fin analizar_x

	private function analizar_y($request=FALSE,$wrapper=FALSE,$wrap_atribs='',$tab_index=1){
		$html = '';
		$wrapper = ($this->valid_wrapper($wrapper))?$wrapper:FALSE;
		$tab_index = ($this->valid_wrapper($wrapper))?$tab_index+1:$tab_index;
		if(is_string($request)){
			$html = $request;
		}
		elseif(	array_key_exists('tag', $request) &&	(
				array_key_exists('atr', $request) ||
				array_key_exists('txt', $request) ||
				array_key_exists('txb', $request) ||
				array_key_exists('chl', $request) 		)	){
			$html .= $this->analizar_x($request,$wrapper,$wrap_atribs,$tab_index);
		}//fin Request simple
		elseif( isset($request[0]) ){
			$parcial = '';
			foreach($request as $grupo){
				$parcial .= $this->analizar_y($grupo,$wrapper,$wrap_atribs,$tab_index);
			}
			$html = $parcial;
		}//fin Request Compuesto
		return $html;
	}//fin analizar_y
//--------------------------------------------------------------------------Constructores de HTML//
	function dom_log( $action=__METHOD__,$value='nada PASÓ por aqui'){
		$this->log .= $action.' => "'.$value.'"'.$this->NL;
	}//fin dom_log
	
//------------------------------------------------------------------------------Metodos //
}//fin de la clase Dom
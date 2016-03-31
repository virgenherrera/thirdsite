<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Modelo_model extends CI_Model{

	/*
	Atribs============================================================================================================
	*/
	private $selectAlias;
	private $insertAlias;
	private $updateAlias;
	private $deleteAlias;
	private $dbTables;

	private $currentMethod;

	private $request;
	private $status;
	private $response;

	private $num_rows;
	private $num_fields;

	private $modelLog;
	/*
	Atribs============================================================================================================
	*/

	/*
	class MAGIC methods============================================================================================================
	*/
	public function __contstruct(){
		parent::__construct();
		$this->load->database();

		//guardar las tablas de la db, para usar con el getter magico
		$this->dbTables = $this->db->list_tables();

		//crear los alias
		self::setAliases();
	}//Fin __construct

	public function __get($atributo=NULL){
		if( in_array($atributo, ['dbTables','request','status','response','num_rows','_fields','modelLog',]) ){
			//geter magico solo para estos atribs
			return $this->{$atributo};
		}
		elseif( in_array($atributo, $this->dbTables) ){
			//geter veloz select * from
			self::_select(['f'=>$atributo])->_setSelectResults('result_array');
			return $this->response;
		}
	}//fin __get

	public function __set($atributo,$valor){
		
		//este metodo puede buscar una propiedad o una tabla de la db
		//nota agregar el buscador de tablas en el constructor magico
	}
	/*
	class MAGIC methods============================================================================================================
	*/

	/*
	class OLD methods============================================================================================================
	*/
	public function _insert($insertSet=NULL,$compiled=FALSE){
		if(!is_null($insertSet)){
			$entrega = ($compiled===TRUE)?'get_compiled_insert':'insert';
				if(isset($insertSet['set'][0])){
					$msg = FALSE;
					foreach ($insertSet['set'] as $set) {
						$ciclo = FALSE;
						$ciclo = $this->db->set( $set )->{$entrega}($insertSet['tabla']);
						if( $compiled === TRUE ){
							$msg[] = $ciclo;
						}
						elseif($this->db->affected_rows()===1){ $msg[] =	$this->db->insert_id(); }
						else{ $msg[] =	FALSE; }
					}//finforeach
					return $msg;
				}//fin multiupdate
				elseif(!isset($insertSet['set'][0])){
					$msg = FALSE;
					$msg = $this->db->set( $insertSet['set'] )->{$entrega}( $insertSet['tabla'] );
					if( $compiled === TRUE ){ return $msg; }
					elseif( $this->db->affected_rows() === 1 ){ return $this->db->insert_id(); }
					else{ return FALSE; }
				}//fin update simple
				else{ return FALSE; }
		}//fin no es nulo
		else{ return FALSE; }
	}//fin _insert

	public function _update($updateSet=NULL,$compiled=FALSE){
		if(!is_null($updateSet)){
			$entrega = ($compiled===TRUE)?'get_compiled_update':'update';
				if(isset($updateSet['set'][0])&&is_array($updateSet['set'][0]['id'])){
					$msg = FALSE;
					foreach ($updateSet['set'] as $set) {
						$ciclo = FALSE;
						$id = $set['id'];
						unset($set['id']);
						$ciclo = $this->db->set( $set )->where( $id )->{$entrega}($updateSet['tabla']);
						if( $compiled === TRUE ){
							$msg[] = $ciclo;
						}
						elseif($this->db->affected_rows()===1){ $msg[] =	TRUE; }
						else{ $msg[] =	FALSE; }
					}//finforeach
					return $msg;
				}//fin multiupdate
				elseif(!isset($updateSet['set'][0]) && is_array($updateSet['set']['id'])){
					$msg = FALSE;
					$id = $updateSet['set']['id'];
					unset($updateSet['set']['id']);
					$msg = $this->db->set( $updateSet['set'] )->where( $id )->{$entrega}( $updateSet['tabla'] );
					if( $compiled === TRUE ){ return $msg; }
					elseif( $this->db->affected_rows() === 1 ){ return TRUE; }
					else{ return FALSE; }
				}//fin update simple
				else{ return FALSE; }
		}//fin no es nulo
		else{ return FALSE; }
	}//fin update

	public function _delete($deleteSet=NULL,$compiled=FALSE){
		if(!is_null($deleteSet)){
			$entrega = ($compiled===TRUE)?'get_compiled_delete':'delete';
				if(isset($deleteSet['where'][0]['id'])){
					$msg = FALSE;
					foreach ($deleteSet['where'] as $where) {
						$ciclo = FALSE;
						$ciclo = $this->db->where( $where['id'] )->{$entrega}($deleteSet['tabla']);
						if( $compiled === TRUE ){
							$msg[] = $ciclo;
						}
						elseif($this->db->affected_rows()===1){ $msg[] =	TRUE; }
						else{ $msg[] =	FALSE; }
					}//finforeach
					return $msg;
				}//fin multidelete
				elseif(!isset($deleteSet['where'][0]['id'])){
					$msg = FALSE;
					$msg = $this->db->where( $deleteSet['where'] )->{$entrega}( $deleteSet['tabla'] );
					if( $compiled === TRUE ){ return $msg; }
					elseif( $this->db->affected_rows() === 1 ){ return TRUE; }
					else{ return FALSE; }
				}//fin delete simple
				else{ return FALSE; }
		}//fin no es nulo
		else{ return FALSE; }
	}//fin delete
	/*
	class OLD methods============================================================================================================
	*/

	/*
	class SELECT methods============================================================================================================
	*/
	protected function _select($query=NULL){
		//informar que estamos llamando a este metodo
		$this->currentMethod = 'select';

		//esta funcion setea la consulta
		if( is_string($query) AND strtoupper(substr($query, 0, 5)) === 'SELECT' ){
			//cuando query es string e inicia con SELECT
			$this->db->query( $query );
			$this->status = TRUE;
		}
		elseif( is_array($query) ){
			//analizar alias
			$newArray = [];
			foreach( $query as $key => $value ){
				$tempAnalisis = self::aliasParser( $key , 'selectAlias' );
				$newArray = ( $tempAnalisis['aliasExist']  ) ? $tempAnalisis['parsedName'] : NULL;
			}
			$query = $newArray;
			unset($newArray);

			//siempre, debe haber algo que seleccionar, sino setear en *
			$query['select'] = ( isset($query['select']) ) ? $query['select'] : '*';

			//construir la consulta si es que se solicito la tabla
			if( isset($query['select']) AND isset($query['from']) ){
				foreach ($variable as $Method => $Params){
					if( method_exists($this->db, $Method) ){
						$this->db->{$Method}( $Params );
					}
				}
				$this->status = TRUE;
			}
			else{
				$this->status = FALSE;
			}
		}
	}//fin _select()

	protected function aliasParser($nameToSearch=NULL,$attibHaystack=NULL){
		if( is_string($nameToSearch) AND is_string($attibHaystack) ){
			foreach ($this->{$attibHaystack} as $keyy => $y) {
				if( in_array($nameToSearch, $y) ){
					return [
						'aliasExist' => TRUE,
						'parsedName' => array_values($y)[0],
					];
				}
			}
			return [
				'aliasExist' => FALSE,
				'parsedName' => FALSE,
			];
		}//fin es cadena
		else{
			return [
				'aliasExist' => FALSE,
				'parsedName' => FALSE,
			];
		}
	}//fin aliasParser

	protected function _setSelectResults($desiredResultType=NULL){
		//analizar el resultado del constructor de consultas
		if( $this->status AND !is_null($desiredResultType)){
			//y segun sea el caso setear response, num_rows, num_fields
			if( $desiredResultType === 'get_compiled_select' ){
				self::get_compiled_();
			}
			elseif( $desiredResultType === 'unbuffered_row' ){
				self::doUnbuffered();
			}
			else{
				$query				=	$this->db->get();

				$this->response		=	$query;
				$this->num_rows		=	$query;
				$this->num_fields	=	$query;
				unset($query);

				$this->response		=	$this->response->{$desiredResultType}();
				$this->num_rows		=	$this->num_rows->num_rows();
				$this->num_fields	=	$this->num_fields->num_fields();
			}
		}
	}//fin _setSelectResults

	protected function doUnbuffered($format='array',$dataSeek=NULL){
		$query = $this->db->get();
		if( $dataSeek >0 )$query->data_seek( $dataSeek );
		$this->response		= $query->unbuffered_row( $format );
		$this->num_rows		=	$query->num_rows();
		$this->num_fields	=	0;
	}//fin doUnbuffered
	/*
	class SELECT methods============================================================================================================
	*/

	/*
	class helper methods============================================================================================================
	*/
	protected function cleanup(){
		if( !is_null($this->currentMethod) ){
			//alimentar al log
			$this->modelLog[] = [
				'request'		=>	$this->request,
				'status'		=>	$this->status,
				'num_rows'		=>	$this->num_rows,
				'num_fields'	=>	$this->num_fields,
			];
			//realizar limpieza
			$this->currentMethod	= NULL;
			$this->request			= NULL;
			$this->status			= NULL;
			$this->response			= NULL;
			$this->num_rows			= NULL;
			$this->num_fields		= NULL;
		}
	}//fin cleanup

	protected function setAliases(){
		$this->selectAlias		=	[
			['select',			's',		'sel',			'campos'		],
			['select_max',		'smx',		'tabla_max'						],
			['select_min',		'smn',		'tabla_min'						],
			['select_avg',		'svg',		'tabla_pro'						],
			['select_sum',		'sum',		'tabla_sum'						],
			['distinct',		'dis',		'distinto'						],
			['from',			'f',		'de',			'tabla'			],
			['join',			'j',		'union',		'juntar'		],
			['where',		 	'w',		'donde',						],
			['or_where',		'wo',		'donde_o'						],
			['where_in',		'wi',	 	'donde_en'						],
			['or_where_in',	 	'owi',		'o_donde_en'					],
			['where_not_in',	'wni',		'donde_no_en'					],
			['or_where_not_in', 'owni',		'o_donde_no_en'					],
			['like',			'l',		'como',			'parecido'		],
			['or_like',			'ol',		'o_como',		'o_parecido'	],
			['not_like',		'no',		'no_como',		'no_parecido'	],
			['or_not_like',		'onl',		'o_no_como',	'o_no_parecido'	],
			['group_by',		'gb',		'agrupar',		'grupo_por'		],
			['distinct',		'd',		'distinto',		'diferente'		],
			['having',			'h',		'teniendo'						],
			['or_having',	 	'oh',		'o_teniendo'					],
			['order_by',		'ob',		'oby',			'ordenado_por'	],
			['limit',			'li',		'lim',			'limite'		],
			['offset',			'of',		'off',			'rebase'		],
		];
		$this->insertAlias	=	[
			['set',		'se',	's'],
			['insert',	'ins', 	'i'],
		];
		$this->updateAlias	=	[
			['set',		'se',	's'		],
			['where', 	'w',	'donde',],
			['update','actullizar','a','u'],
			['replace','reemplaza','rep','r'],
		];
		$this->deleteAlias	=	[
			['where',	'w',		'donde',						],
			['delete','borra','d','b'],
		];
	}//fin setAliases

	protected function get_compiled_(){
		if( is_string( $this->currentMethod ) ){
			$this->response		=	$this->db->{__FUNCTION__ . $this->currentMethod}();
			$this->num_rows		=	0;
			$this->num_fields	=	0;
		}
	}//fin get_compiled_
	/*
	class helper methods============================================================================================================
	*/

}//fin de la clase Onmi_model
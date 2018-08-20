<?php
namespace tiFy\Plugins\WebService\Server;

use tiFy\Plugins\WebService\Server\Server;
use tiFy\Lib\File;

class Posts extends \WP_REST_Posts_Controller
{
	/* = CONSTRUCTEUR = */
	public function __construct( $post_type )
	{
		parent::__construct( $post_type );
		//$this->namespace = 'tiFyAPI/v1';
		add_action( 'rest_api_init', array( $this, '_rest_api_init' ) );	
	}
	
	/** == == **/
	final public function _rest_api_init()
	{
		register_rest_route( 
			Server::getOption( 'namespace' ), 
			'/'. $this->post_type, 
			array(
				'methods' 	=> \WP_REST_Server::READABLE,
				'callback' 	=> array( $this, 'get_items' )
			) 
		);
		
		register_rest_route( 
			Server::getOption( 'namespace' ), 
			'/'. $this->post_type . '/(?P<id>[\d]+)', 
			array(
				'methods' 	=> \WP_REST_Server::READABLE,
				'callback' 	=> array( $this, 'get_item' )
			) 
		);		
		
		register_rest_field( 
			$this->post_type,
	        'post_status',
	        array(
	            'get_callback'    => array( $this, 'getFieldPostStatus' ),
	            'update_callback' => null,
	            'schema'          => null,
	        )
	    );
		
		register_rest_field( 
			$this->post_type,
	        'author',
	        array(
	            'get_callback'    => array( $this, 'getFieldAuthor' ),
	            'update_callback' => null,
	            'schema'          => null,
	        )
	    );
	}
	
	/** == == **/
	public function get_items( $request )
	{
		if( ! isset( $request['orderby'] ) )
			$request->set_param( 'orderby', 'ID' );
		
		if( ! isset( $request['page'] ) )
			$request->set_param( 'page', 0 );
		
		if( ! isset( $request['per_page'] ) )
			$request->set_param( 'per_page', 100 );
						
		return parent::get_items( $request );
	}
		
	/* = CHAMPS PERSONNALISES PREDEFINIS = */
	/** == ATTRIBUTS == **/
	/*** === Extrait === ***/
	public function getFieldExcerpt( $object, $field_name, $request )
	{
		return get_the_excerpt( $object['id'] );
	}
	
	/*** === Status === ***/
	public function getFieldPostStatus( $object, $field_name, $request )
	{
		return get_post_status_object( get_post_status( $object['id'] ) )->label;
	}
	
	/*** === Auteur === ***/
	public function getFieldAuthor( $object, $field_name, $request )
	{
		$author_id =  get_post_field ( 'post_author', $object['id'] );
		
		return array(
			'id'	=> 	$author_id,
			'name'	=>	get_the_author_meta( 'display_name', $author_id )
		);
	}
	
	/** == MÉTADONNÉES == **/
	/*** === Métadonné === ***/
	public function getFieldPostMeta( $object, $field_name, $request )
	{
		return get_post_meta( $object['id'], $field_name, true );
	}
	
	/*** === Fichier attaché === ***/
	public function getFieldPostMetaAttachmentID( $object, $field_name, $request )
	{
		if( ! $attachment_id = (int) get_post_meta( $object['id'], $field_name, true ) )
			return false;
		
		return File::getAttachmentDatas( $attachment_id );
	}
	
	/*** === Fichier attaché === ***/
	public function getFieldPostMetaAttachmentIDs( $object, $field_name, $request )
	{
		if( ! $attachment_ids = (array) get_post_meta( $object['id'], $field_name, true ) )
			return false;
		
		$attachment_ids	 = array_map( 'intval', $attachment_ids );
			
		$response = array();
		foreach( (array) $attachment_ids as $attachment_id ) :	
			$response[] = File::getAttachmentDatas( $attachment_id );
		endforeach;
		
		return $response;
	}
	
	/** == TAXONOMIE == **/
	/*** === Récupération de la liste des termes d'un taxonomie === ***/
	public function getFieldTerms( $object, $field_name, $request )
	{
		$terms = wp_get_post_terms( $object['id'], $field_name );
		
		if( is_wp_error( $terms ) )
			return array();
		
		return $terms;
	}
}
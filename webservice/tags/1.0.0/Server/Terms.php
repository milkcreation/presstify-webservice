<?php 
namespace tiFy\Plugins\WebService\Server;

use tiFy\Plugins\WebService\Server\Server;

class Terms extends \WP_REST_Terms_Controller
{
	/* = CONSTRUCTEUR = */
	public function __construct( $taxonomy )
	{
		parent::__construct( $taxonomy );
		//$this->namespace = 'tiFyAPI/v1';
		add_action( 'rest_api_init', array( $this, '_rest_api_init' ) );	
	}
	
	/** == == **/
	final public function _rest_api_init()
	{
		register_rest_route( 
			Server::getOption( 'namespace' ), 
			'/'. $this->taxonomy, 
			array(
				'methods' 	=> \WP_REST_Server::READABLE,
				'callback' 	=> array( $this, 'get_items' )
			) 
		);
		
		register_rest_route( 
			Server::getOption( 'namespace' ), 
			'/'. $this->taxonomy . '/(?P<id>[\d]+)', 
			array(
				'methods' 	=> \WP_REST_Server::READABLE,
				'callback' 	=> array( $this, 'get_item' )
			) 
		);		
	}
	
	/** == == **/
	public function get_items( $request )
	{
		if( ! isset( $request['orderby'] ) )
			$request->set_param( 'orderby', 'name' );
		
		if( ! isset( $request['page'] ) )
			$request->set_param( 'page', 1 );
		
		if( ! isset( $request['per_page'] ) )
			$request->set_param( 'per_page', 100 );
			
		return parent::get_items( $request );
	}
}
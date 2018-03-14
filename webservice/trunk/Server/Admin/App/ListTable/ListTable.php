<?php
/**
 * @Overrideable 
 */
namespace tiFy\Plugins\WebService\Server\Admin\App\ListTable;

class ListTable extends \tiFy\Core\Admin\Model\ListTable\ListTable 
{
	/* = PARAMAETRAGE = */
	/** == Définition des actions sur un élément == **/
	public function set_row_actions()
	{
		return array( 'edit', 'delete' );
	}
	
	/* = DECLENCHEURS = */
	/** == Mise en file des scripts de l'interface d'administration == **/
	final public function admin_enqueue_scripts()
	{
		tify_control_enqueue( 'token' );
		wp_enqueue_style( 'tiFy_Webservice_Server_Admin_App_ListTable', self::tFyAppUrl() . '/ListTable.css' );
	}
	
	/* = AFFICHAGE = */
	/** == Colonne - Description == **/
	public function column_wsapp_desc( $item )
	{
		return wp_unslash( $item->wsapp_desc );	
	}
	
	/** == Colonne - Clé d'authentification == **/
	public function column_wsapp_key_hash( $item )
	{
		return tify_control_token(
			array(
				'name'			=> 'wsapp_key_hash',
				'keygen'		=> false,	
				'value'			=> $item->wsapp_key_hash,	
				'public_key'	=> $item->wsapp_public_key,
				'private_key'	=> $item->wsapp_private_key
			)
		);
	}
	
	/** == Activation == **/
	public function column_wsapp_active( $item )
	{
		if( $item->wsapp_active )
			return "<span style=\"color:green;\">". __( 'Ouvert', 'tify' ) ."</span>";
		else
			return "<span style=\"color:red;\">". __( 'Fermé', 'tify' ) ."</span>";
	}
}
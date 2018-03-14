<?php
/**
 * @Overrideable 
 */

/**
 * @see https://developer.wordpress.org/rest-api/reference/
 */
namespace tiFy\Plugins\WebService\Client\Admin\RestApi;

use tiFy\Plugins\WebService\Client\Client;

class ListTable extends \tiFy\Core\Templates\Admin\Model\Import\Import
{
    /**
     * Url de prefixe de l'url de requête
     */ 
    protected $RestUrl;
    
    /**
     * Espace de nom de l'url de requête
     */ 
    protected $Namespace;
    
    /**
     * Suffixe de l'url de requête
     */
    protected $Route;
    
    /**
     * Description de l'url de requête
     */
    protected $EndpointDescription          = '';
        
    /**
     * PARAMETRES
     */
    /** 
     * Définition de la cartographie des paramètres autorisés
     */
    public function set_params_map()
    {
        $params = parent::set_params_map();
        array_push( $params, 'RestUrl', 'Namespace','Route', 'EndpointDescription' ); 
        
        return $params;
    }    

    /**
     * Définition de l'url de prefixe de l'url de requête 
     */    
    public function set_rest_url()
    {
        return '';
    }

    /**
     * Définition de l'espace de nom de l'url de requête 
     */
    public function set_namespace()
    {
        return '';
    }
    
    /**
     * Définition du suffixe de l'url de requête 
     */
    public function set_route()
    {
        return '';
    }
    
    /**
     * Définition de la description de l'url de requête 
     */
    public function set_endpoint_description()
    {
        return '';
    }
    
    /**
     * Listes de données complémentaires porté par les requêtes Datatables
     */
    public function getDatatablesData()
    {
        return array(
            'endpoint' => isset( $_REQUEST['endpoint'] ) ? $_REQUEST['endpoint'] : 0
        );
    }
    
    /**
     * PARAMETRAGE
     */
    /**
     * Paramétrage de l'url de prefixe de l'url de requête
     */
    public function initParamRestUrl()
    {
        if( $rest_url = $this->set_rest_url() ) :
        elseif( ( $endpoint = Client::getOption( 'endpoint' ) ) && ! empty( $endpoint['rest_url'] ) ) :
            $rest_url = $endpoint['rest_url'];
        elseif( $rest_url = Client::getOption( 'rest_url' ) ) :            
        endif;
        
        if( $rest_url ) :
            $this->RestUrl = rtrim( $rest_url, '/' );
        endif;
    }
    
    /**
     * Paramétrage de l'espace de nom de l'url de requête
     */
    public function initParamNamespace()
    {
        if( $namespace = $this->set_namespace() ) :
        elseif( ( $endpoint = Client::getOption( 'endpoint' ) ) && ! empty( $endpoint['namespace'] ) ) :
            $namespace = $endpoint['namespace'];
        elseif( $namespace = Client::getOption( 'namespace' ) ) :
        endif;

        if( $namespace ) :
            $this->Namespace = trim( rtrim( $namespace, '/' ), '/' );
        endif;
    }
    
    /**
     * Paramétrage du suffixe de l'url de requête
     */
    public function initParamRoute()
    {
        if( isset( $_REQUEST['route'] ) ) :
            $route = $_REQUEST['route'];
        elseif( $route = $this->set_route() ) :
        elseif( ( $endpoint = Client::getOption( 'endpoint' ) ) && ! empty( $endpoint['route'] ) ) :
            $route = $endpoint['route'];
        elseif( $route = Client::getOption( 'route' ) ) :
        endif;
                
        if( $route ) :
            $this->Route = trim( rtrim( $route, '/' ), '/' );
        endif;
    }
    
    /**
     * Paramétrage de la description de l'url de requête
     */
    public function initParamEndpointDescription()
    {
        if( $description = $this->set_endpoint_description() ) :
        elseif( ( $endpoint = Client::getOption( 'endpoint' ) ) && ! empty( $endpoint['description'] ) ) :
            $description = $endpoint['description'];
        elseif( $description = Client::getOption( 'description' ) ) :
        endif;

        if( $description )
            $this->EndpointDescription = $description;
    }
                    
    /**
     * DECLENCHEURS
     */
    /**
     * Mise en file des scripts de l'interface d'administration
     * {@inheritDoc}
     * @see \tiFy\Core\Templates\Admin\Model\Table::_admin_enqueue_scripts()
     */
    public function admin_enqueue_scripts()
    { 
        parent::admin_enqueue_scripts();
        
        tify_control_enqueue( 'dropdown' );
        wp_enqueue_style( 'tiFyPluginsWebServiceClientRestApiListTable', self::tFyAppUrl( get_class() ) .'/ListTable.css', array( 'tify_control-dropdown' ), '161027' );     
    }
    
    /**
     * TRAITEMENT
     */
    /**
     * Vérification d'existance d'un élément
     * @param obj $item données de l'élément
     * 
     * @return bool false l'élément n'existe pas en base | true l'élément existe en base
     */
    public function item_exists( $item )
    {        
        return false;
    }
        
    /**
     * Récupération des données
     */
    protected function getResponse()
    {
        $endpoint = $this->RestUrl .'/'. $this->Namespace .'/'. $this->Route;

        // Traitement des paramètres de requête
        if( $this->current_item() ) :
            $item_id = current( $this->current_item() );
            $endpoint .= "/{$item_id}";
        else :
            $params     = $this->parse_query_args();
            $_params    = http_build_query( $params );
            $endpoint .= "/?{$_params}";
        endif;
        
        $response = wp_remote_get( $endpoint );
        $body = wp_remote_retrieve_body( $response );
        if( empty( json_decode( $body, true ) ) ) :
            return new \WP_Error( 'empty_body', __( 'Aucun résultat ne correspond à la requête', 'tify' ) );
        endif;        
        
        // Traitement de la réponse
        if( $this->current_item() ) :
            $results[] = json_decode( $body, true );
        else :    
            $results = json_decode( $body, true );
        endif;
        
        $this->TotalItems = wp_remote_retrieve_header( $response, 'x-wp-total' );
        $this->TotalPages = wp_remote_retrieve_header( $response, 'x-wp-totalpages' );       
                
        foreach( $results as $key => $attrs ) :
            $items[$key] = new \stdClass;
            foreach( $attrs as $prop => $value ) :
                $cn = ( $res = array_search ( $prop, $this->MapColumns ) ) ? $res : $prop;
                $items[$key]->{$cn} = $value;
            endforeach;
            if( $this->ItemIndex ) :
                $items[$key]->_tiFyTemplatesImport_import_index = isset( $attrs[$this->MapColumns[$this->ItemIndex]] ) ? $attrs[$this->MapColumns[$this->ItemIndex]] : $attrs[$this->ItemIndex];                  
            endif;
        endforeach;
    
        return $items;
    }

    /**
     * AFFICHAGE
     */        
    /**
     * Affichage du formulaire de modification requête de récupération de données
     */
    public function views()
    {
        $rest_url       = $this->RestUrl;
        $namespace      = $this->Namespace;
        $route          = $this->Route;
        $description    = $this->EndpointDescription;
?>
<div id="tiFy_WebService_Client_Request">
    <h3><?php _e( 'Url de requête', 'tify' );?> <small>(endpoint)</small></h3>
    <form method="get" action="">
        <input type="hidden" name="page" value="<?php echo $_REQUEST['page'];?>" />
        <input type="hidden" name="endpoint[rest_url]" value="<?php echo $rest_url;?>" />
        <input type="hidden" name="endpoint[namespace]" value="<?php echo $namespace;?>" />
        <input type="hidden" name="endpoint[route]" value="<?php echo $route;?>" />
        <ul>
            <li>
                <input type="text" value="<?php echo $rest_url;?>" disabled="disabled"/>
                <label><?php _e( 'rest url', 'tify' );?></label>
            </li>
            <li>
                <input type="text" value="<?php echo $namespace;?>" disabled="disabled"/>
                <label><?php _e( 'namespace', 'tify' );?></label>
            </li>
            <li>
                <?php 
                    $choices = array();
                    foreach( (array) Client::getOption( 'endpoints' ) as $key => $v ) :
                        $choices[$key] = $v['route'];    
                    endforeach;
                    tify_control_dropdown(
                        array(
                            'choices'             => $choices,
                            'selected'            => ! empty( $_REQUEST['endpoint'] ) ? $_REQUEST['endpoint'] : 0,
                            'show_option_none'    => false,
                            'name'                => 'endpoint'
                        )
                    );
                ?>
                <label><?php _e( 'route', 'tify' );?></label>
            </li>
            <li>
                <button type="submit" class="submit-action"><?php _e( 'Envoyer', 'tify' );?></button>
            </li>
        </ul>
    </form>
    <?php if( $description ) :?>
    <div><?php echo $description;?></div>
    <?php endif;?>
</div>    
<?php    
        parent::views();       
    }
}
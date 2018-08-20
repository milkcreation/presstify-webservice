<?php
namespace tiFy\Plugins\WebService\Client;

use tiFy\tiFy;
use tiFy\Plugins\WebService\Server\Server;
use tiFy\Plugins\WebService\WebService;

class Client extends \tiFy\Environment\Addon
{
    /**
     * Chemin vers la classe principale du plugin (requis)
     */
    protected static $PluginPath        = "\\tiFy\\Plugins\\WebService\\WebService";
    
    /**
     * Identifiant de l'addon (requis)
     */
    protected static $AddonID             = 'client';
    
    /**
     * Options de l'addon (requises)
     */
    protected static $Options            = array();

    /**
     * CONSTRUCTEUR
     */
    public function __construct( )
    {
        parent::__construct();

        // Définition des attributs généraux
        $allowed_attrs = array( 'rest_url', 'namespace', 'route', 'cb' );

        // Définition de l'API courante
        if( $endpoints = self::getOption( 'endpoints', array() ) ) :
            foreach( $endpoints as &$e ) :
                foreach( $allowed_attrs as $aa ) :
                    if( ! isset( $e[$aa] ) ) :
                        $e[$aa] = self::getOption( $aa );
                    endif;
                endforeach;
            endforeach;
        else :
            foreach( $allowed_attrs as $aa ) :
                $endpoints[0][$aa] = self::getOption( $aa );
            endforeach;
        endif;                

        self::setOption( 'endpoints', $endpoints );
        
        // Définition de l'API courante
        if( ! empty( $_GET['endpoint'] ) && isset( $endpoints[$_GET['endpoint']] ) ):
            $endpoint = $endpoints[$_GET['endpoint']];
        else :
            $endpoint = current( $endpoints );
        endif;        
        self::setOption( 'endpoint', $endpoint );            
        
        add_action( 'tify_templates_register', array( $this, 'tify_templates_register' ), 50 );
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Déclaration des templates
     */
    public function tify_templates_register()
    {
        // Traitement des options
        $endpoint = self::getOption( 'endpoint' );
        
        if( $View = \tiFy\Core\Templates\Templates::getAdmin( 'webServiceListTable' ) ) :
            $route = trim( $endpoint['route'], '/' );
            $route = trim( $endpoint['route'], '\\' );
            $route = self::sanitizeControllerName( $route ); 
            
            if( isset( $endpoint['cb'] ) )
                $path[] = $endpoint['cb'];
            
            $path[] = "\\". self::getOverrideNamespace() ."\\Plugins\\WebService\\Client\\Admin\\{$route}"; 
            $path[] = "\\tiFy\\Plugins\\WebService\\Client\\Admin\\{$route}";

            $cb = self::getOverride( '\tiFy\Plugins\WebService\Client\Admin\RestApi\ListTable', $path );
            $View->setAttr( 'cb', $cb );            
        endif;
    }

    /**
     * CONTROLEURS
     */
    /**
     * Options par défaut
     */
    public static function defaultOptions()
    {
        $rest_url       = ( $_rest_url      = Server::getOption( 'rest_url' )       ) ? $_rest_url       : WebService::$DefaultRestUrl;
        $namespace      = ( $_namespace     = Server::getOption( 'namespace' )      ) ? $_namespace      : WebService::$DefaultNamespace;
        $route          = ( $_route         = Server::getOption( 'route' )          ) ? $_route          : (array) WebService::$DefaultRoute;

        return array(
            'type'          => 'post_type',
            'rest_url'      => $rest_url,    
            'namespace'     => $namespace,
            'route'         => $route,
            'title'         => '',
            'description'   => ''
        );
    }
}
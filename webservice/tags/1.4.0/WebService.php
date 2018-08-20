<?php
/*
Plugin Name: Webservice
Plugin URI: http://presstify.com/plugins/webservice
Description: Gestion de webservice
Version: 1.0.0
Author: Milkcreation
Author URI: http://milkcreation.fr
*/
namespace tiFy\Plugins\WebService;

class WebService extends \tiFy\App\Plugin
{
    /**
     * Url de requête par défaut
     */
    public static $DefaultRestUrl     = null;
    
    /**
     * Espace de nom de requête par défaut
     */
    public static $DefaultNamespace = 'tiFyAPI/v1';
    
    /**
     * Route par défaut
     */
    public static $DefaultRoute     = 'page';
        
    /**
     * CONSTRUCTEUR
     */
    public function __construct()
    {
        parent::__construct();
        
        // Définition de l'url de requête par défaut
        self::$DefaultRestUrl     = \esc_url_raw( \rest_url() );
        
        require_once self::tFyAppDirname( get_class() ) .'/Helpers.php';

        // Chargement des contrôleurs
        if( self::tFyAppConfig('server') )
            new Server\Server;
        if( self::tFyAppConfig('client') )
            new Client\Client;
    }
    
    /**
     * CONTROLEURS
     */
    /**
     * Clé d'index d'une route
     */
    public static function RouteKey( $route )
    {
        return sanitize_key( trim( rtrim( $route, '/' ), '/' ) );
    }
}

<?php
/**
 * @see http://www.sitepoint.com/wordpress-json-rest-api/
 */

namespace tiFy\Plugins\WebService\Server;

use tiFy\Environment\Addon;

class Server extends Addon
{
    /* = ARGUMENTS = */
    // Chemin vers la classe principale du plugin (requis)
    protected static $PluginPath = "\\tiFy\\Plugins\\WebService\\WebService";

    // Identifiant de l'addon (requis)
    protected static $AddonID = 'server';

    // Options de l'addon (requise)
    protected static $Options = [];

    /**
     * CONSTRUCTEUR
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Déclaration des événements
        $this->appAddAction('init', null, 25);
    }

    /* = OPTIONS = */
    /** == Par défaut == **/
    public static function defaultOptions()
    {
        return [
            'rest_url'  => \tiFy\Plugins\WebService\WebService::$DefaultRestUrl,
            'namespace' => \tiFy\Plugins\WebService\WebService::$DefaultNamespace,
            'route'     => \tiFy\Plugins\WebService\WebService::$DefaultRoute,
            'post_type' => 'page',
            'taxonomy'  => 'category',
        ];
    }

    /* = DECLENCHEURS = */
    /** == Initialisation global == **/
    final public function init()
    {
        // Chargement des contrôleurs
        if (!class_exists('WP_REST_Controller')) :
            require_once WPINC . '/rest-api/endpoints/class-wp-rest-controller.php';
        endif;
        if (!class_exists('WP_REST_Posts_Controller')) :
            require_once WPINC . '/rest-api/endpoints/class-wp-rest-posts-controller.php';
        endif;
        if (!class_exists('WP_REST_Terms_Controller')) :
            require_once WPINC . '/rest-api/endpoints/class-wp-rest-terms-controller.php';
        endif;

        // Instanciation des contrôleurs
        /// Type de post
        foreach ((array)self::getOption('post_type') as $k => $v) :
            if (is_int($k)) :
                self::AllowPostType($v);
                new Posts($v);
            else :
                self::AllowPostType($k);
                if (class_exists($v)) :
                    new $v($k);
                else :
                    new Posts($k);
                endif;
            endif;
        endforeach;

        foreach ((array)self::getOption('taxonomy') as $k => $v) :
            if (is_int($k)) :
                self::AllowTaxonomy($v);
                new Terms($v);
            else :
                self::AllowTaxonomy($k);
                if (class_exists($v)) :
                    new $v($k);
                else :
                    new Terms($k);
                endif;
            endif;
        endforeach;
    }

    /* = CONTRÔLEURS = */
    /** == == **/
    private static function AllowPostType($post_type, $class = '\tiFy\Plugins\WebService\Server\Posts')
    {
        global $wp_post_types;

        if (isset($wp_post_types[$post_type])) :
            $wp_post_types[$post_type]->show_in_rest = true;
            $wp_post_types[$post_type]->rest_base = $post_type;
            $wp_post_types[$post_type]->rest_controller_class = $class;
        endif;
    }

    /** == == **/
    private static function AllowTaxonomy($taxonomy, $class = '\tiFy\Plugins\WebService\Server\Terms')
    {
        global $wp_taxonomies;

        if (isset($wp_taxonomies[$taxonomy])) :
            $wp_taxonomies[$taxonomy]->show_in_rest = true;
            $wp_taxonomies[$taxonomy]->rest_base = $taxonomy;
            $wp_taxonomies[$taxonomy]->rest_controller_class = $class;
        endif;
    }
}
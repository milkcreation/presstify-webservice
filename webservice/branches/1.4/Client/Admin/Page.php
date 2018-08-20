<?php
/**
 * @Overrideable
 */
namespace tiFy\Plugins\WebService\Client\Admin;

class Page extends \tiFy\Plugins\WebService\Client\Admin\RestApi\ListTable
{
    /**
     * PARAMETRES
     */
    /**
     * Définition de la liste des colonnes 
     */
    public function set_columns()
    {
        return array(
            'ID'            => __( 'ID', 'tify' ),
            '_title'        => __( 'Titre', 'tify' ),
            'author'        => __( 'Auteur', 'tify' ),
            'date_gmt'      => __( 'Date de création', 'tify' ),
            'modified_gmt'  => __( 'Dernière modification', 'tify' )
        );
    }
    
    /**
     * Définition de la table de correspondance des données entre l'identifiant de table et les données récupérées du serveur
     */
    public function set_columns_map()
    {
        return array(
            'ID'        => 'id',
            '_title'    => 'title'
        );
    }
    
    /**
     * Définition de la classe de l'importateur de données
     */ 
    public function set_importer()
    {
        return "\\tiFy\\Plugins\\WebService\\Client\Admin\\Importer\\Page";
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
        $exists_query = new \WP_Query;
        if( 
            $exists = $exists_query->query( 
                array(
                    'post_type'         => 'any',
                    'posts_per_page'    => 1,
                    'meta_query'        => array(
                        array(
                            'key'           => '_tify_ws_import_id',
                            'value'         => $item->ID                                
                        )
                    ),
                    'fields'            => 'ids'
                ) 
            ) 
        ) :
            return current( $exists );
        else :                
            return 0;
        endif;
    }
    
    /**
     * Traitement des arguments de requête
     */
    public function parse_query_args()
    {
        // Arguments par défaut
        $query_args = parent::parse_query_args();
        
        if( ! isset( $query_args['order'] ) )
            $query_args['order'] = 'ASC';
        if( ! isset( $query_args['orderby'] ) )
            $query_args['orderby'] = 'menu_order';
            
        return $query_args;
    }
    
    /**
     * AFFICHAGE
     */ 
    /**
     * Affichage de la colonne - Titre
     */
    public function column__title( $item )
    {        
        if( isset( $item->_title['rendered'] ) )
            return "<strong>{$item->_title['rendered']}</strong>";
    }
    
    /**
     * Affichage de la colonne - Auteur
     */
    public function column_author( $item )
    {        
        if( isset( $item->author['name'] ) )
            return $item->author['name'];
    }
        
    /**
     * Affichage de la colonne - Date de création
     */
    public function column_date_gmt( $item )
    {        
        $output = "";

        if( isset( $item->post_status ) )
            $output .= $item->post_status .'<br/>';
        
        $date = new \DateTime( $item->date_gmt );
        $output .= "<abbr title=\"". $date->format( __( 'Y/m/d g:i:s a' ) ) ."\">". $date->format( __( 'Y/m/d' ) ) ."</abbr>";        
            
        return $output;
    }
    
    /**
     * Affichage de la colonne - Date de modification
     */
    public function column_modified_gmt( $item )
    {        
        $date = new \DateTime( $item->modified_gmt );
        
        return "<abbr title=\"". $date->format( __( 'Y/m/d g:i:s a' ) ) ."\">". $date->format( __( 'Y/m/d' ) ) ."</abbr>";
    } 
}
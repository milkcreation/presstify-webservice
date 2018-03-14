<?php
namespace tiFy\Plugins\WebService\Client\Admin\Importer;

class Page extends \tiFy\Inherits\Importer\Post
{
    /**
     * Définition de la cartographie des données principales
     */
    public function setDataMap()
    {
        return array(
            'ID',
            'post_author',
            'post_date',
            'post_date_gmt',
            'post_content'      => 'content',
            'post_title'        => '_title',
            'post_excerpt'      => 'excerpt',            
            'post_status'       => 'status',
            'comment_status',
            'ping_status',
            //'post_password',
            //'post_name,
            //'to_ping',
            //'pinged',
            'post_modified'     => 'modified',
            'post_modified_gmt' => 'modified_gmt',
            //'post_content_filtered',
            'post_parent'       => 'parent',
            //'guid',
            'menu_order',
            'post_type'     => 'type',
            //'post_mime_type',
            //'comment_count'
        );
    } 
    
    /**
     * Définition du type de post
     */
    public function set_data_post_type()
    {
        return 'page';
    }
    
    /**
     * Définition de la metadonnée de l'id du post importé 
     */
    public function set_meta__tify_ws_import_id()
    {
        return $this->InputDatas['ID'];
    }
    
    /**
     * Définition de la metadonnée de la date d'import du post
     */
    public function set_meta__tify_ws_import_datetime()
    {
        return current_time( 'mysql' );
    }
        
    /**
     * Filtrage des données
     */
    public function filter_datas( $value, $key )
    {      
        switch( $key ) :
            case 'ID' :
                $exists_query = new \WP_Query;
                if( 
                    $exists = $exists_query->query( 
                        array(
                            'post_type'         => 'any',
                            'posts_per_page'    => 1,
                            'meta_query'        => array(
                                array(
                                    'key'           => '_tify_ws_import_id',
                                    'value'         => $value                                
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
                break;
                
            case 'post_author' :
                return get_current_user_id();
                break;
                
            case 'post_content' :
            case 'post_title' :
            case 'post_excerpt' :
                if( isset( $value['rendered'] ) )
                    return $value['rendered']; 
                break;
                
            case 'post_modified' :
            case 'post_modified_gmt' :
                $DateTime = new \DateTime( $value );        
                return $DateTime->format( 'Y-m-d H:i:s' );
                break;
                
            case 'post_parent' :
                $exists_query = new \WP_Query;
                if( 
                    $exists = $exists_query->query( 
                        array(
                            'post_type'         => 'any',
                            'posts_per_page'    => 1,
                            'meta_query'        => array(
                                array(
                                    'key'           => '_tify_ws_import_id',
                                    'value'         => $value                                
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
                break;
        endswitch;
        
        return $value;
    }
}
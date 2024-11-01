<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists( 'TwitchPress_Post_Type_Giveaways' ) ) :

/**
 * Methods for handling all post types including "post", "page" and 
 * registrating custom post types.
 *
 * @class     TwitchPress_Post_Type_Giveaways
 * @version   1.0.0
 * @package   TwitchPress/Giveaways
 */
class TwitchPress_Post_Type_Giveaways {

    /**
     * Hook in methods.
     */
    public static function init() {
        add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 5 );
        add_action( 'init', array( __CLASS__, 'register_post_type' ), 5 );
        add_action( 'init', array( __CLASS__, 'register_post_status' ), 9 );
        add_filter( 'rest_api_allowed_post_types', array( __CLASS__, 'rest_api_allowed_post_types' ) );  
        //add_action( 'post_submitbox_misc_actions', array( __CLASS__, 'post_submitbox' ) );
    }
    
    public static function register_taxonomies() {
        
        if ( ! is_blog_installed() ) {
            return;
        }

        $permalinks = twitchpress_get_permalink_structure();
        
        if ( !taxonomy_exists( 'giveaways_type' ) ) {
            
        }
        
        do_action( 'twitchpress_after_register_taxonomy' );        
    }

    /**
     * Register core post types.
     * 
     * @link https://developer.wordpress.org/reference/functions/register_post_type/   
     */
    public static function register_post_type() {
        if ( ! is_blog_installed() || post_type_exists( 'giveaways' ) ) {
            return;
        }

        $permalinks = twitchpress_get_permalink_structure();  
        
        register_post_type( 'giveaways',
            apply_filters( 'twitchpress_register_post_type_giveaways',
                array(
                    'labels'              => array(
                            'name'                  => __( 'Twitch Giveaways', 'twitchpress' ),
                            'singular_name'         => __( 'Twitch Giveaway', 'twitchpress' ),
                            'menu_name'             => _x( 'Giveaways', 'Admin menu name', 'twitchpress' ),
                            'add_new'               => __( 'Add a giveaway', 'twitchpress' ),
                            'add_new_item'          => __( 'Add New Giveaway', 'twitchpress' ),
                            'edit'                  => __( 'Edit', 'twitchpress' ),
                            'edit_item'             => __( 'Edit Giveaway', 'twitchpress' ),
                            'new_item'              => __( 'New giveaway', 'twitchpress' ),
                            'view'                  => __( 'View giveaway', 'twitchpress' ),
                            'view_item'             => __( 'View giveaway', 'twitchpress' ),
                            'search_items'          => __( 'Search giveaways', 'twitchpress' ),
                            'not_found'             => __( 'No giveways found', 'twitchpress' ),
                            'not_found_in_trash'    => __( 'No giveaways found in trash', 'twitchpress' ),
                            'parent'                => __( 'Parent giveaway', 'twitchpress' ),
                            'featured_image'        => __( 'Giveaway image', 'twitchpress' ),
                            'set_featured_image'    => __( 'Set giveaway image', 'twitchpress' ),
                            'remove_featured_image' => __( 'Remove giveaway image', 'twitchpress' ),
                            'use_featured_image'    => __( 'Use as giveaway image', 'twitchpress' ),
                            'insert_into_item'      => __( 'Insert into giveaway', 'twitchpress' ),
                            'uploaded_to_this_item' => __( 'Uploaded to this giveaway post', 'twitchpress' ),
                            'filter_items_list'     => __( 'Filter giveaways', 'twitchpress' ),
                            'items_list_navigation' => __( 'Twitch giveaway navigation', 'twitchpress' ),
                            'items_list'            => __( 'Giveaways list', 'twitchpress' ),
                        ),
                    'description'         => __( 'This is where you can add giveaway posts.', 'twitchpress' ),
                    'public'              => false,
                    'show_ui'             => true,
                    'publicly_queryable'  => false,
                    'exclude_from_search' => false,
                    'hierarchical'        => false, 
                    'rewrite'             => $permalinks[ 'giveaways_rewrite_slug'] ? array( 'slug' => $permalinks[ 'giveaways_rewrite_slug'], 'with_front' => false, 'giveaways' => true ) : false,
                    'query_var'           => true,
                    'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'comments', 'custom-fields', 'wpcom-markdown' ),
                    'has_archive'         => true,
                    'show_in_nav_menus'   => false,
                    'show_in_rest'        => true,
                    'show_in_menu'        => false,
                    'map_meta_cap'        => true,
                    'capability_type'     => 'post'
                )
            )
        );
    }

    /**
     * Register our custom post statuses, used for order status.
     * 
     * @version 1.0
     */
    public static function register_post_status() {

        $order_statuses = apply_filters( 'twitchpress_register_giveaways_post_statuses',
            array(
                'twitchpress-awaitingtrigger'    => array(
                    'label'                     => _x( 'Awaiting Trigger', 'Order status', 'twitchpress' ),
                    'public'                    => false,
                    'exclude_from_search'       => false,
                    'show_in_admin_all_list'    => true,
                    'show_in_admin_status_list' => true,
                    'label_count'               => _n_noop( 'Awaiting Trigger <span class="count">(%s)</span>', 'Awaiting Trigger <span class="count">(%s)</span>', 'twitchpress' ),
                )
            )
        );

        foreach ( $order_statuses as $order_status => $values ) {
            register_post_status( $order_status, $values );
        }
    }

    /**
     * Allow twitchfeed posts in API controlled by JetPack.
     *
     * @param  array $post_types
     * @return array
     */
    public static function rest_api_allowed_post_types( $post_types ) {

        return $post_types;
    }
    
    /**
    * Options for sharing post content to Twitch feed.
    * 
    * @param mixed $post
    * 
    * @version 1.0
    */
    public static function html_twitchpress_post_sharing_options($post) {
        
        /*
        ?>
        <label for="twitchpress_whentoshare"><?php _e( 'When should the content be shared?', 'twitchpress' ); ?></label>
        <select name="twitchpress_whentoshare" id="twitchpress_whentoshare" class="postbox">
            <option value="">Select something...</option>
            <option value="publishing">ASAP</option>
        </select>
        <?php
        */
        
    }
 
    /**
    * Saves and processes share to feed options.
    * 
    * @param mixed $post_id
    * 
    * @version 1.0
    */
    public static function save_twitchpress_post_sharing_options($post_id){
        
        /*
        if ( array_key_exists( 'twitchpress_whentoshare', $_POST ) ) {
            update_post_meta(
                $post_id,
                '_twitchpress_whentoshare',
                $_POST['twitchpress_whentoshare']
            );
        }
        */
        
        return $post_id;
    }  
    
    /**
    * Display custom fields in the publish box. 
    * 
    * @version 1.0
    */
    public static function post_submitbox() {
        global $post;
        
        /*
        // Display checkbox option to share post content to Twitch.
        $post_type = get_post_type($post);
     
        $twitch_share = get_option( 'twitchpress_shareable_posttype_' . $post_type );
        if ( $twitch_share == 'yes' ) { 
            echo '<div class="misc-pub-section misc-pub-section-last" style="border-top: 1px solid #eee;">';
            wp_nonce_field( plugin_basename(__FILE__), 'nonce_twitchpress_share_post_option' );
            echo '<input type="checkbox" name="twitchpress_share_post_option" id="twitchpress_share_post_option" value="share" /> <label for="twitchpress_share_post_option">Share to Twitch Feed</label><br />';
            echo '</div>';
        }  
        */
    }   
}

endif;
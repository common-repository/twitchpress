<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
         
/**
 * Register custom post type for TwitchPress webhooks...
 *
 * @version   1.0.0
 * @package   TwitchPress
 * @category  Class
 * @author    Ryan Bayne
 */
class TwitchPress_Post_Type_Webhooks {
    public static function init() {
        
        // Register
        add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 5 );
        add_action( 'init', array( __CLASS__, 'register_post_type' ), 5 );
        add_action( 'init', array( __CLASS__, 'register_post_status' ), 9 );
        
        // Customize
        add_action( 'add_meta_boxes_webhooks', array( __CLASS__, 'add_custom_boxes' ) );
        
        // Save Post
        add_action( 'save_post_webhooks', array( __CLASS__, 'save_twitchpress_webhooks_options' ) );          
        add_action( 'save_post_webhook', array( __CLASS__, 'save_twitchpress_webhooks_eventsub' ) ); 
                 
        // Update Post 
        add_action( 'update_post_webhook', array( __CLASS__, 'save_twitchpress_webhooks_options' ) );          
        add_action( 'update_post_webhook', array( __CLASS__, 'save_twitchpress_webhooks_eventsub' ) );          
    }
 
    public static function register_taxonomies() {

        /*
        if ( ! is_blog_installed() ) {
            return;
        }

        if ( !taxonomy_exists( 'twitchperks_type' ) ) {
            self::register_taxonomy_perks();
        }

        do_action( 'twitchpress_register_perks_taxonomy' );

        */
    }
    
    public static function register_post_type() {
        if ( ! is_blog_installed() || post_type_exists( 'webhooks' ) ) {
            return;
        }        
        
        $permalinks = twitchpress_get_permalink_structure();
        
        $capabilities = array(
            'edit_post'          => 'edit_webook', 
            'read_post'          => 'read_webhook', 
            'delete_post'        => 'delete_webhook', 
            'edit_posts'         => 'edit_webhooks', 
            'edit_others_posts'  => 'edit_others_webhookss', 
            'publish_posts'      => 'publish_webhooks',       
            'read_private_posts' => 'read_private_webhooks', 
            'create_posts'       => 'edit_webhooks',            
        );
        
        $labels = array(
            'name'                  => _x( 'Webhooks', 'Post Type General Name', 'twitchpress' ),
            'singular_name'         => _x( 'Webhook', 'Post Type Singular Name', 'twitchpress' ),
            'menu_name'             => __( 'Webhooks', 'twitchpress' ),
            'name_admin_bar'        => __( 'Webhook', 'twitchpress' ),
            'archives'              => __( 'Item Archives', 'twitchpress' ),
            'attributes'            => __( 'Item Attributes', 'twitchpress' ),
            'parent_item_colon'     => __( 'Parent Item:', 'twitchpress' ),
            'all_items'             => __( 'All Items', 'twitchpress' ),
            'add_new_item'          => __( 'Add New Item', 'twitchpress' ),
            'add_new'               => __( 'Add New', 'twitchpress' ),
            'new_item'              => __( 'New Item', 'twitchpress' ),
            'edit_item'             => __( 'Edit Item', 'twitchpress' ),
            'update_item'           => __( 'Update Item', 'twitchpress' ),
            'view_item'             => __( 'View Item', 'twitchpress' ),
            'view_items'            => __( 'View Items', 'twitchpress' ),
            'search_items'          => __( 'Search Item', 'twitchpress' ),
            'not_found'             => __( 'Not found', 'twitchpress' ),
            'not_found_in_trash'    => __( 'Not found in Trash', 'twitchpress' ),
            'featured_image'        => __( 'Featured Image', 'twitchpress' ),
            'set_featured_image'    => __( 'Set featured image', 'twitchpress' ),
            'remove_featured_image' => __( 'Remove featured image', 'twitchpress' ),
            'use_featured_image'    => __( 'Use as featured image', 'twitchpress' ),
            'insert_into_item'      => __( 'Insert into item', 'twitchpress' ),
            'uploaded_to_this_item' => __( 'Uploaded to this item', 'twitchpress' ),
            'items_list'            => __( 'Items list', 'twitchpress' ),
            'items_list_navigation' => __( 'Items list navigation', 'twitchpress' ),
            'filter_items_list'     => __( 'Filter items list', 'twitchpress' ),
        );
                            
        register_post_type( 'webhooks',
            apply_filters( 'twitchpress_register_post_type_webhooks',
                array(
                    'label'                 => __( 'Webhook', 'twitchpress' ),
                    'description'           => __( 'Twitch web subscriptions using TwitchPress', 'twitchpress' ),
                    'labels'                => $labels,
                    'supports'              => array( 'title' ),
                    'taxonomies'            => array(),
                    'hierarchical'          => false,
                    'public'                => false,
                    'show_ui'               => true,
                    'show_in_menu'          => false,
                    'menu_position'         => 10,
                    'menu_icon'             => 'dashicon-admin-posta',
                    'capability_type'       => 'post',
                    'show_in_admin_bar'     => true,
                    'show_in_nav_menus'     => false,
                    'can_export'            => false,
                    'has_archive'           => false,
                    'exclude_from_search'   => true,
                    'publicly_queryable'    => false,
                    'capabilities'          => array(),
                    'show_in_rest'          => false                   
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
        /*
        $order_statuses = apply_filters( 'twitchpress_register_twitchperk_post_statuses',
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
        */
    }
          
    /**
    * Add custom meta boxes to webhooks post type... 
    * 
    * @version 1.0
    */
    public static function add_custom_boxes() {         
        add_meta_box(
            'twitchpress_post_webhooks_options', // Unique ID
            __( 'Webhook Options', 'twitchpress' ),  
            array( __CLASS__, 'html_twitchpress_post_webhooks_options' )
        );        
    }
    
    public static function get_actions() {
        return apply_filters( 'twitchpress_webhooks_actions', array(
            'userupdate'     => __( 'Update WP User', 'twitchpress' ),
            'emailkeyholder' => __( 'Email Site Owner', 'twitchpress' ),
            'rewardpoints'   => __( 'Reward WP Points', 'twitchpress' ),
            'filedump'       => __( 'File Dump', 'twitchpress' ),
        ) );    
    }
    
    /**
    * Options for locking post content.
    * 
    * @param mixed $post
    * 
    * @version 1.0
    */
    public static function html_twitchpress_post_webhooks_options($post) {
        $type = get_post_meta( $post->ID, '_twitchpress_post_webhooks_type', true );
        $action_one = get_post_meta( $post->ID, '_twitchpress_post_webhooks_action_one', true );
        $action_two = get_post_meta( $post->ID, '_twitchpress_post_webhooks_action_two', true );
        $action_three = get_post_meta( $post->ID, '_twitchpress_post_webhooks_action_three', true );
        ?>
        <p><?php _e( 'Not all combinations of event and action will work (pending further development). Please test and seek advice if unsure.'); ?></p>
        <table class="form-table">
            <tbody>
                <tr>
                    <th><label for="_twitchpress_post_webhooks_type"><?php _e( 'Type of event this subscription will hook into...', 'twitchpress' ); ?></label></th>
                    <td>
                        <select name="_twitchpress_post_webhooks_type" id="_twitchpress_post_webhooks_type" class="postbox">
                            <?php 
                            foreach( twitchpress_eventsub_types() as $item ) { 
                                echo '<option value="' . $item[1] . '"' . selected( $type, $item[1] ) . '>' . $item[0] . '</option>';    
                            }
                            ?> 
                        </select>
                    </td>
                </tr>                
                <tr>
                    <th><label for="_twitchpress_post_webhooks_action_one"><?php _e( 'The primary action to take for this webhook...', 'twitchpress' ); ?></label></th>
                    <td>
                        <select name="_twitchpress_post_webhooks_action_one" id="_twitchpress_post_webhooks_action_one" class="postbox">
                            <?php 
                            foreach( self::get_actions() as $action_id => $action_label ) { 
                                echo '<option value="' . $action_id . '"' . selected( $action_one, $action_id ) . '>' . $action_label . '</option>';    
                            }
                            ?> 
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="_twitchpress_post_webhooks_action_two"><?php _e( 'Second action to take for this webhook...', 'twitchpress' ); ?></label></th>
                    <td>
                        <select name="_twitchpress_post_webhooks_action_two" id="_twitchpress_post_webhooks_action_two" class="postbox">
                            <option value="none" <?php selected( $action_two, 'none' ); ?>><?php _e( 'Not Required', 'twitchpress' ); ?></option>                            
                            <?php 
                            foreach( self::get_actions() as $action_id => $action_label ) { 
                                echo '<option value="' . $action_id . '"' . selected( $action_two, $action_id ) . '>' . $action_label . '</option>';    
                            }
                            ?> 
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="_twitchpress_post_webhooks_action_three"><?php _e( 'Third action to take for this webhook...', 'twitchpress' ); ?></label></th>
                    <td>
                        <select name="_twitchpress_post_webhooks_action_three" id="_twitchpress_post_webhooks_action_three" class="postbox">
                            <option value="none" <?php selected( $action_two, 'none' ); ?>><?php _e( 'Not Required', 'twitchpress' ); ?></option> 
                            <?php 
                            foreach( self::get_actions() as $action_id => $action_label ) { 
                                echo '<option value="' . $action_id . '"' . selected( $action_three, $action_id ) . '>' . $action_label . '</option>';    
                            }
                            ?> 
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php        
    }
            
    /**
    * Saves and processes webhook post options.
    * 
    * @param mixed $post_id
    * 
    * @version 1.0
    */
    public static function save_twitchpress_webhooks_options( $post_id ){
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Save the webhook type... 
        if ( array_key_exists( '_twitchpress_post_webhooks_type', $_POST ) ) {
            update_post_meta(
                $post_id,
                '_twitchpress_post_webhooks_type',
                $_POST['_twitchpress_post_webhooks_type']
            );
        }

        if ( array_key_exists( '_twitchpress_post_webhooks_action_one', $_POST ) ) {
            update_post_meta(
                $post_id,
                '_twitchpress_post_webhooks_action_one',
                $_POST['_twitchpress_post_webhooks_action_one']
            );
        }

        if ( array_key_exists( '_twitchpress_post_webhooks_action_two', $_POST ) ) {
            update_post_meta(
                $post_id,
                '_twitchpress_post_webhooks_action_two',
                $_POST['_twitchpress_post_webhooks_action_two']
            );
        }

        if ( array_key_exists( '_twitchpress_post_webhooks_action_three', $_POST ) ) {
            update_post_meta(
                $post_id,
                '_twitchpress_post_webhooks_action_three',
                $_POST['_twitchpress_post_webhooks_action_three']
            );
        }
        
        return $post_id;
    }    
    
    public static function save_twitchpress_webhooks_eventsub( $post_id ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }      

        // Establish the Twitch-API.php method name for the selected webhook type...
        $method_name = 'eventsub_' . str_replace( '.', '_', $_POST['_twitchpress_post_webhooks_type'] );  
        
        // Make our call to Twitch...
        $twitch_api = new TwitchPress_Twitch_API();
        $twitch_response = $twitch_api->$method_name( $post_id, twitchpress_get_main_channels_twitchid() );
        unset( $twitch_api );
        
        # Expected $twitch_response Example 
        /* {
            "data": [{
                "id": "f1c2a387-161a-49f9-a165-0f21d7a4e1c4",
                "status": "webhook_callback_verification_pending",
                "type": "channel.follow",
                "version": "1",
                "cost": 1,
                "condition": {
                    "broadcaster_user_id": "12826"
                },
                "transport": {
                    "method": "webhook",
                    "callback": "https://example.com/webhooks/callback"
                },
                "created_at": "2019-11-16T10:11:12.123Z"
            }],
            "total": 1,
            "total_cost": 1,
            "max_total_cost": 10000,
            "limit": 10000
        }*/ 
 
        # Current Response
 
        # TESTING ONLY REMOVE
        //var_dump_twitchpress( $this->curl_object->curl_request_body );
        //var_dump_twitchpress( $this->curl_object );

        //error_log( implode( ',', $result ) );
    }
}
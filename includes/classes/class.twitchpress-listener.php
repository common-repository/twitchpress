<?php
/**
 * TwitchPress Listener for $_GET requests...
 * 
 * @author   Ryan Bayne
 * @category Admin
 * @package  TwitchPress
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}  

if( !class_exists( 'TwitchPress_Listener' ) ) :

class TwitchPress_Listener {  
    public function __construct() {              
        add_action( 'wp_loaded', array( $this, 'GET_requests_listener' ) );
    }
         
    /**
    * Call methods for processing requests after all of the common
    * security checks have been done for the request your making.
    * 
    * @version 1.2
    */
    public function GET_requests_listener() {      
        if ( $_SERVER['REQUEST_METHOD'] !== 'GET' ) {
            return;
        }

        // One of these values must be set for it to be TwitchPress related...
        if( !isset( $_GET['twitchpressaction'] ) && !isset( $_GET['state'] ) ) {
            return;    
        }
        
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        
        if( defined( 'DOING_CRON' ) && DOING_CRON ) {
            return;    
        }        
        
        if( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            return;    
        }

        self::twitch_oauth_unlocksubcontent_return();
        
        if( !is_user_logged_in() ) {       
            return;
        }        
        
        if( !user_can( TWITCHPRESS_CURRENTUSERID, 'activate_plugins' ) ) {  
            return;    
        }

        if( !isset( $_REQUEST['_wpnonce'] ) ) {
            return;    
        }
        
        // Developer Toolbar Actions
        self::developertoolbar_admin_actions();         
    } 
    
    /**
    * Runs method called by a request made using the Developer Toolbar.
    * 
    * @version 1.0
    */
    private static function developertoolbar_admin_actions() {
        if( !isset( $_GET['twitchpressaction'] ) ) { 
            return; 
        }

        // Varify Nonce
        if( ! wp_verify_nonce( $_REQUEST['_wpnonce'], $_GET['twitchpressaction'] ) ) {
            return;    
        }

        switch ( $_GET['twitchpressaction'] ) {
            
           case 'twitchpressuninstalloptions':
                self::developertoolbar_uninstall_settings();
             break;
           case 'twitchpresssyncmainfeedtowp':
                //self::developertoolbar_sync_main_channel_feed_to_wp();
             break;
        }       
    }
    
    /**
    * Remove all settings from the Developer Toolbar.
    * 
    * @version 1.1
    */
    public static function developertoolbar_uninstall_settings() {
        // Security is done already but we need safeguards should the method be called elsewhere.
        if( !user_can( TWITCHPRESS_CURRENTUSERID, 'activate_plugins' ) ) {  
            return;    
        }
                               
        $nonce = $_REQUEST['_wpnonce'];
        if ( wp_verify_nonce( $nonce, 'twitchpressuninstalloptions' ) ) {
            //TwitchPress_Uninstall::uninstall_options(); 
   
            TwitchPress_Admin_Notices::add_wordpress_notice(
                'devtoolbaruninstallednotices',
                'success',
                true,
                __( 'Options Removed', 'twitchpress' ),
                __( 'TwitchPress options have been deleted and the plugin will need some configuration to begin using it.', 'twitchpress' ) 
            );
        }  
    }         
    /**
    * Listens for oAuth2 return and calls applicable functions to process 
    * the response from the Twitch API...
    * 
    * @version 1.0
    */
    public static function twitch_oauth_unlocksubcontent_return() {
        if( !isset( $_GET['state'] ) || !isset( $_GET['scope'] ) || !isset( $_GET['code'] ) ){
            return;    
        }
        
        // We require the local state value stored in transient. 
        if( !$transient_state = twitchpress_get_transient_oauth_state( $_GET['state'] ) ) { 
            return;
        }   
        
        // Ensure the return from Twitch.tv is a request to unlock subscriber content...
        if( !isset( $transient_state['purpose'] ) || $transient_state['purpose'] != 'twitchsubcontent' ) {
            return;
        }
           
        $twitch_api = new TwitchPress_Twitch_API();
        $auth = $twitch_api->request_user_access_token( $_GET['code'], __FUNCTION__ );  
        $user = $twitch_api->get_user_by_bearer_token( $auth->access_token );

        setcookie( 'twitchname', $user->display_name , 3600, COOKIEPATH, COOKIE_DOMAIN );
        setcookie( 'twitchid', $user->id , 3600, COOKIEPATH, COOKIE_DOMAIN );
        setcookie( 'twitchaccesstoken', $auth->access_token , 3600, COOKIEPATH, COOKIE_DOMAIN );
        setcookie( 'twitchrefresh_token', $auth->refresh_token, 86400, COOKIEPATH, COOKIE_DOMAIN );

        // Get subscription data for the main channel...
        $subs = $twitch_api->get_broadcaster_subscriptions( twitchpress_get_main_channels_twitchid(), $user->id, false );        
        if( isset( $subs->data['tier'] ) ) {
            setcookie( 'twitchsubtier', $subs->data['tier'], 86400, COOKIEPATH, COOKIE_DOMAIN );
        }

        // Redirect back to original page...
        twitchpress_redirect_tracking( get_page_uri( $transient_state['loginpageid'] ), __LINE__, __FUNCTION__ );
        exit;        
    }   
}   

endif;

return new TwitchPress_Listener();        
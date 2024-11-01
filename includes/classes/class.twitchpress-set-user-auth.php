<?php
/**
 * TwitchPress - Class sets the current users Twitch API oauth credentials.   
 * 
 * @author   Ryan Bayne
 * @category Scripts
 * @package  TwitchPress/Core
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists( 'TwitchPress_Set_User' ) ) :

class TwitchPress_Set_User {

    public $wp_user_id        = null;
    public $user_twitch_id    = null;
    public $user_bio          = null;        
    public $user_display_name = null;        
    public $user_twitch_name  = null;        
    public $user_auth_time    = null;        
    public $user_code         = null;        
    public $user_token        = null;        
    public $user_refresh      = null;        
    public $user_expires_in   = null;        
    public $user_scope        = null;                   

    function set( $wp_user_id ) {  
        $this->user_twitch_id    = get_user_meta( $wp_user_id, 'twitchpress_twitch_id', true );        
        $this->user_bio          = get_user_meta( $wp_user_id, 'twitch_bio', true );        
        $this->user_display_name = get_user_meta( $wp_user_id, 'twitchpress_twitch_display_name', true );        
        $this->user_twitch_name  = get_user_meta( $wp_user_id, 'twitchpress_twitch_name', true );        
        $this->user_auth_time    = get_user_meta( $wp_user_id, 'twitchpress_auth_time', true );        
        $this->user_code         = get_user_meta( $wp_user_id, 'twitchpress_code', true );        
        $this->user_token        = get_user_meta( $wp_user_id, 'twitchpress_token', true );        
        $this->user_refresh      = get_user_meta( $wp_user_id, 'twitchpress_token_refresh', true );        
        $this->user_expires_in   = get_user_meta( $wp_user_id, 'twitchpress_twitch_expires_in', true );        
        $this->user_scope        = get_user_meta( $wp_user_id, 'twitchpress_token_scope', true );                   
    }
    
    function get() {  
        return array(
            'twitch_id'     => $this->user_twitch_id,
            'bio'           => $this->user_bio,
            'display_name'  => $this->user_display_name,
            'twitch_name'   => $this->user_twitch_name,
            'auth_time'     => $this->user_auth_time,
            'code'          => $this->user_code,
            'token'         => $this->user_token,
            'refresh'       => $this->user_refresh,
            'expires_in'    => $this->user_expires_in,
            'scope'         => $this->user_scope     
        );
    }
}

endif;

/**
* Setup te current users oAuth2 credentials.
* 
* @version 2.0
*/
function twitchpress_init_current_user_twitch_oauth() {   
    if( !is_user_logged_in() ) { return; }
    $set_user = new TwitchPress_Set_User();
    $set_user->set( wp_get_current_user()->ID );
    
    TwitchPress_Object_Registry::add( 'currentusertwitch', $set_user );
    unset( $set_user );    
}

// Priority needs to put app setup before most other things in TwitchPress...
add_action( 'init', 'twitchpress_init_current_user_twitch_oauth', 2 );

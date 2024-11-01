<?php
/**
 * TwitchPress - Twitch API credentials for the main channel and it's owner are set here.  
 * 
 * @author   Ryan Bayne
 * @category Scripts
 * @package  TwitchPress/Core
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists( 'TwitchPress_Set_Main_Channel_Auth' ) ) :

class TwitchPress_Set_Main_Channel_Auth {
    
    public $main_channels_code = null;
    public $main_channels_wpowner_id = null;
    public $main_channels_token = null;
    public $main_channels_refresh = null; 
    public $main_channels_scopes = null;
    public $main_channels_name = null;
    public $main_channels_id = null;
    public $main_channels_postid = null;
    public $main_channels_authtime = null;
    public $main_channels_expiry = null;
    public string $main_channels_refresh_token = ''; 

    /**
    * Set main channel values straight from WordPress options...
    * 
    * @version 1.0
    */
    function set() {                                                    
        $this->main_channels_code        = get_option( 'twitchpress_main_channels_code', 0 );
        $this->main_channels_wpowner_id  = get_option( 'twitchpress_main_channels_wpowner_id', 0 );
        $this->main_channels_token       = get_option( 'twitchpress_main_channels_token', 0 );        
        $this->main_channels_refresh     = get_option( 'twitchpress_main_channels_refresh', 0 ); 
        $this->main_channels_scopes      = get_option( 'twitchpress_main_channels_scopes', 0 );
        $this->main_channels_name        = get_option( 'twitchpress_main_channels_name', 0 );
        $this->main_channels_id          = get_option( 'twitchpress_main_channels_id', 0 );        
        $this->main_channels_postid      = get_option( 'twitchpress_main_channels_postid', 0 );        
        $this->main_channels_authtime    = get_option( 'twitchpress_main_channels_authtime', 0 );        
        $this->main_channels_expiry      = get_option( 'twitchpress_main_channels_expiry', 0 );        
    }
    
    /**
    * Get all or a single value for the main channel...
    * 
    * @param mixed $value
    * @return mixed
    * 
    * @version 1.0
    */
    function get( $value = null ) {
        if( $value ) {
            return eval( '$this->main_channels_$value' );
        }
        return array(
            'code'      => $this->main_channels_code,
            'wpownerid' => $this->main_channels_wpowner_id,
            'token'     => $this->main_channels_token,
            'refresh'   => $this->main_channels_refresh,
            'scopes'    => $this->main_channels_scopes,
            'name'      => $this->main_channels_name,
            'id'        => $this->main_channels_id,
            'postid'    => $this->main_channels_postid,
            'authtime'  => $this->main_channels_authtime,
            'expiry'    => $this->main_channels_expiry
        );
    }

}

endif;

//TwitchPress_Object_Registry::add( 'mainchannelauth', new TwitchPress_Set_Main_Channel_Auth() );

function twitchpress_init_main_channel_twitch_oauth() {
    $main_channel = new TwitchPress_Set_Main_Channel_Auth();
    $main_channel->set();
    TwitchPress_Object_Registry::add( 'mainchannelauth', $main_channel );
    
    // Ensure token is still valid...     
    $twitchapi = new TwitchPress_Twitch_API();
    
    // Main channel and main user share the same token...
    $established_token = $twitchapi->establish_user_token( 1 );
    twitchpress_update_main_channels_token( $established_token );

    unset( $main_channel, $twitchapi );   
}

// Priority needs to put app setup before most other things in TwitchPress...
add_action( 'init', 'twitchpress_init_main_channel_twitch_oauth', 2 );

<?php
/**
 * TwitchPress - Deprecated functions from the entire TwitchPress system. 
 * 
 * Move extension functions here and avoid creating file like this in every extension.  
 * 
 * @author   Ryan Bayne
 * @category Scripts
 * @package  TwitchPress/Core
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
* @deprecated use twitchpress_get_main_channels_code() 
*/
function twitchpress_get_main_channel_code() {
    return get_option( 'twitchpress_main_code' );
}

/**
* @deprecated use twitchpress_get_main_channels_code() 
*/
function twitchpress_get_main_client_code() {
    return twitchpress_get_main_channel_code();
}

/**
* @deprecated use twitchpress_get_app_id()
*/
function twitchpress_get_main_client_id() {
    return get_option( 'twitchpress_main_client_id' );
}  

/**
* Stores the main application token and main application scopes
* as an option value.
* 
* @param mixed $token
* @param mixed $scopes
* 
* @version 2.0
* 
* @deprecated 2.3.0 Use object registry approach.
* @see TwitchPress_Object_Registry()
*/
function twitchpress_update_main_client_token( $token, $scopes ) {
    update_option( 'twitchpress_main_token', $token );
    update_option( 'twitchpress_main_token_scopes', $scopes );
}

/**
* Generates Twitch user logo attachment...
* 
* @deprecated to avoid legal issues by copying and storing logos
* 
* @param mixed $wp_user_id
* @param mixed $url
* @param mixed $display_name
* @param mixed $username
* @return WP_Error
*/
function twitchpress_save_twitch_logo( $wp_user_id, $url, $display_name, $username ) { 
    $attachment = array(
        'post_title'   => 'Logo Owned By ' . $display_name,
        'post_content' => 'Copied from Twitch.tv when owner registered on site using Twitch oAuth2.',
        'post_slug'    => 'twitch-logo-' . $username,
        'post_status'  => 'private'
    );
                                                
    $attachment_id = wp_insert_attachment( $attachment, $url, null, true );
    if( $attachment_id !== WP_Error ) {
        // Store new attachment ID in users meta...
        update_user_meta( $wp_user_id, 'twitchpress_twitch_logo_attachment_id', $attachment_id );  
        
        // Improve the attachments meta data...
        $attach_data = wp_generate_attachment_metadata( $attachment_id, $url );
        wp_update_attachment_metadata( $attachment_id, $attach_data );              
    } 
    
    return $attachment_id; 
}

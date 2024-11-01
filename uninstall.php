<?php
/**
 * Uninstall plugin.
 * 
 * The uninstall.php file is a standard approach to running an uninstall
 * procedure for a plugin. It should be as simple as possible.
 *
 * @author      Ryan Bayne
 * @category    Core
 * @package     TwitchPress/Uninstaller
 * @version     2.0
 */

// Ensure plugin uninstall is being run by WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    wp_die( __( 'Uninstallation file incorrectly requested for the TwitchPress plugin.', 'twitchpress' ) );
}
                                                             
if( 'yes' == get_option( 'twitchpress_remove_database_tables' ) ) { twitchpress_remove_database_tables(); }
if( 'yes' == get_option( 'twitchpress_remove_extensions' ) ) { twitchpress_remove_extensions(); }
if( 'yes' == get_option( 'twitchpress_remove_user_data' ) ) { twitchpress_remove_user_data(); }
if( 'yes' == get_option( 'twitchpress_remove_media' ) ) { twitchpress_remove_media(); }
if( 'yes' == get_option( 'twitchpress_remove_roles' ) ) { twitchpress_remove_roles(); }

// The plan is to offer different levels of uninstallation to make testing and re-configuration easier...
//if( 'yes' == get_option( 'twitchpress_remove_options' ) ) { twitchpress_remove_options_surgically(); }
if( 'yes' == get_option( 'twitchpress_remove_options' ) ) { twitchpress_remove_options(); }

/**
* Uninstall all of the plugins options with care! 
* 
* @version 2.0
*/
function twitchpress_remove_options() {          
    delete_option( 'twitchpress_admin_notices' );
    delete_option( 'twitchpress_admin_notice_missingvaluesofferwizard' );
    delete_option( 'twitchpress_allapi_id_streamlabs' );
    delete_option( 'twitchpress_allapi_redirect_uri_streamlabs' );
    delete_option( 'twitchpress_allapi_secret_streamlabs' );
    delete_option( 'twitchpress_allapi_streamlabs_default_key' );
    delete_option( 'twitchpress_allapi_streamlabs_default_secret' );
    delete_option( 'twitchpress_allapi_streamlabs_default_uri' );
    delete_option( 'twitchpress_automatic_registration' );
    delete_option( 'twitchpress_bugnet_cache_action_hooks' );
    delete_option( 'twitchpress_display_actions' );
    delete_option( 'twitchpress_display_filters' );
    delete_option( 'twitchpress_login_button' );
    delete_option( 'twitchpress_login_button_text' );
    delete_option( 'twitchpress_login_loggedin_page_id' );
    delete_option( 'twitchpress_login_loginpage_position' );
    delete_option( 'twitchpress_login_loginpage_type' );
    delete_option( 'twitchpress_login_mainform_page_id' );
    delete_option( 'twitchpress_login_redirect_to_custom' );
    delete_option( 'twitchpress_login_requiretwitch' );
    delete_option( 'twitchpress_main_channels_refresh_token' );
    delete_option( 'twitchpress_registration_button' );
    delete_option( 'twitchpress_registration_requirevalidemail' );
    delete_option( 'twitchpress_registration_twitchonly' );
    delete_option( 'twitchpress_remove_database_tables' );
    delete_option( 'twitchpress_remove_extensions' );
    delete_option( 'twitchpress_remove_media' );
    delete_option( 'twitchpress_remove_options' );
    delete_option( 'twitchpress_remove_roles' );
    delete_option( 'twitchpress_remove_user_data' );
    delete_option( 'twitchpress_scope_analytics_read_extensions' );
    delete_option( 'twitchpress_scope_analytics_read_games' );
    delete_option( 'twitchpress_scope_bits_read' );
    delete_option( 'twitchpress_scope_chat_edit' );
    delete_option( 'twitchpress_scope_chat_read' );
    delete_option( 'twitchpress_scope_clips_edit' );
    delete_option( 'twitchpress_scope_user_edit' );
    delete_option( 'twitchpress_scope_user_edit_broadcast' );
    delete_option( 'twitchpress_scope_user_read_broadcast' );
    delete_option( 'twitchpress_scope_user_read_email' );
    delete_option( 'twitchpress_sync_timing' );
    delete_option( 'twitchpress_twitchapi_call_count' );
    delete_option( 'twitchpress_twitchpress-embed-everything_settings' );
    delete_option( 'twitchpress_twitchpress-login-extension_settings' );
    delete_option( 'twitchpress_twitchpress-subscriber-management_settings' );
    delete_option( 'twitchpress_twitchpress-sync-extension_settings' );
    delete_option( 'twitchpress_twitchpress-um-extension_settings' );
    delete_option( 'twitchpress_visitor_scope_analytics_read_extensions' );
    delete_option( 'twitchpress_visitor_scope_analytics_read_games' );
    delete_option( 'twitchpress_visitor_scope_bits_read' );
    delete_option( 'twitchpress_visitor_scope_channel_check_subscription' );
    delete_option( 'twitchpress_visitor_scope_channel_commercial' );
    delete_option( 'twitchpress_visitor_scope_channel_editor' );
    delete_option( 'twitchpress_visitor_scope_channel_read' );
    delete_option( 'twitchpress_visitor_scope_channel_stream' );
    delete_option( 'twitchpress_visitor_scope_channel_subscriptions' );
    delete_option( 'twitchpress_visitor_scope_chat_edit' );
    delete_option( 'twitchpress_visitor_scope_chat_read' );
    delete_option( 'twitchpress_visitor_scope_clips_edit' );
    delete_option( 'twitchpress_visitor_scope_collections_edit' );
    delete_option( 'twitchpress_visitor_scope_communities_edit' );
    delete_option( 'twitchpress_visitor_scope_communities_moderate' );
    delete_option( 'twitchpress_visitor_scope_openid' );
    delete_option( 'twitchpress_visitor_scope_user_blocks_edit' );
    delete_option( 'twitchpress_visitor_scope_user_blocks_read' );
    delete_option( 'twitchpress_visitor_scope_user_edit' );
    delete_option( 'twitchpress_visitor_scope_user_edit_broadcast' );
    delete_option( 'twitchpress_visitor_scope_user_follows_edit' );
    delete_option( 'twitchpress_visitor_scope_user_read' );
    delete_option( 'twitchpress_visitor_scope_user_read_broadcast' );
    delete_option( 'twitchpress_visitor_scope_user_read_email' );
    delete_option( 'twitchpress_visitor_scope_user_subscriptions' );
    delete_option( 'twitchpress_visitor_scope_viewing_activity_read' );
    delete_option( 'twitchpress_buddypress_avatars_override' );
    delete_option( 'twitchpress_twitchpress-embed-everything_settings' );        
    delete_option( 'twitchpress_twitchpress-login-extension_settings' );        
    delete_option( 'twitchpress_twitchpress-sync-extension_settings' );        
    delete_option( 'twitchpress_twitchpress-um-extension_settings' );

    // BugNet   
    delete_option( 'bugnet_activate_events' );        
    delete_option( 'bugnet_activate_log' );        
    delete_option( 'bugnet_activate_tracing' );        
    delete_option( 'bugnet_levelswitch_emergency' );        
    delete_option( 'bugnet_levelswitch_alert' );        
    delete_option( 'bugnet_levelswitch_critical' );        
    delete_option( 'bugnet_levelswitch_error' );        
    delete_option( 'bugnet_levelswitch_warning' );        
    delete_option( 'bugnet_levelswitch_notice' );        
    delete_option( 'bugnet_handlerswitch_email' );        
    delete_option( 'bugnet_handlerswitch_logfiles' );        
    delete_option( 'bugnet_handlerswitch_restapi' );        
    delete_option( 'bugnet_handlerswitch_tracing' );        
    delete_option( 'bugnet_handlerswitch_wpdb' );        
    delete_option( 'bugnet_reportsswitch_dailysummary' );        
    delete_option( 'bugnet_reportsswitch_eventsnapshot' );        
    delete_option( 'bugnet_reportsswitch_tracecomplete' );        
    delete_option( 'bugnet_systemlogging_switch' );        
    delete_option( 'bugnet_error_dump_user_id' );

    // Deprecated
    delete_option( 'twitchress_sandbox_mode_falsereturns_switch' );
    delete_option( 'twitchress_sandbox_mode_generator_switch' );
    delete_option( 'twitchress_sandbox_mode_switch' );
    delete_option( 'twitchpress_main_client_secret' );
    delete_option( 'twitchpress_main_client_id' );
    delete_option( 'twitchpress_main_redirect_uri' );
    delete_option( 'twitchpress_main_channel_postid' );
    delete_option( 'twitchpress_main_channel_name' );
    delete_option( 'twitchpress_main_channel_id' );
    delete_option( 'twitchpress_main_token' );
    delete_option( 'twitchpress_main_token_scopes' );      
}    

/**
* Remove database tables created by the TwitchPress core.
* 
* @version 1.0 
*/
function twitchpress_remove_database_tables() {
    global $wpdb;
    
    $activity  = "{$wpdb->prefix}twitchpress_activity";
    $errors    = "{$wpdb->prefix}twitchpress_errors";
    $endpoints = "{$wpdb->prefix}twitchpress_endpoints";
    $meta      = "{$wpdb->prefix}twitchpress_meta";    
    
    $wpdb->query( "DROP TABLE IF EXISTS $activity" );
    $wpdb->query( "DROP TABLE IF EXISTS $errors" );
    $wpdb->query( "DROP TABLE IF EXISTS $endpoints" );
    $wpdb->query( "DROP TABLE IF EXISTS $meta" );
}

/**
* Remove all TwitchPress extensions. 
* 
* @version 1.0
*/
function twitchpress_remove_extensions() {      
    foreach( twitchpress_extensions_array() as $extensions_group_key => $extensions_group_array ) {
        foreach( $extensions_group_array as $extension_name => $extension_array ) {
            deactivate_plugins( $extension_name, true );
            uninstall_plugin( $extension_name );                                 
        }
    }     
}

/**
* Remove all user data created by the core plugin.
* 
* @version 1.0
*/
function twitchpress_remove_user_data() {
    delete_user_meta( 1, 'twitchpress_twitch_sub' );
    delete_user_meta( 1, 'twitchpress_avatar_url' );
    delete_user_meta( 1, 'twitchpress_twitch_logo_url' );
    delete_user_meta( 1, 'twitchpress_twitch_logo_attachment_id' );
    delete_user_meta( 1, 'twitchpress_code' );
    delete_user_meta( 1, 'twitchpress_token' );
    delete_user_meta( 1, 'twitchpress_twitch_id' );
    delete_user_meta( 1, 'twitchpress_twitch_bot_id' );
    delete_user_meta( 1, 'twitchpress_bot_code' );
    delete_user_meta( 1, 'twitchpress_auth_time' );
    delete_user_meta( 1, 'twitchpress_bot_token' );
    delete_user_meta( 1, 'twitchpress_token_scope' );
    delete_user_meta( 1, 'twitchpress_token_refresh' );
    delete_user_meta( 1, 'twitchpress_bot_token_refresh' );
    delete_user_meta( 1, 'twitchpress_twitch_expires_in' );
    delete_user_meta( 1, 'twitchpress_sync_time' );
    delete_user_meta( 1, 'twitchpress_twitch_bio' );
    delete_user_meta( 1, 'twitchpress_twitch_email' );
    delete_user_meta( 1, 'twitchpress_streamlabs_code' );
    delete_user_meta( 1, 'twitchpress_streamlabs_access_token' );
    delete_user_meta( 1, 'twitchpress_streamlabs_expires_in' );
    delete_user_meta( 1, 'twitchpress_streamlabs_refresh_token' );
    delete_user_meta( 1, 'twitchpress_streamlabs_scope' );
}

/**
* Remove media created by TwitchPress. 
* 
* @version 1.0
*/
function twitchpress_remove_media() {
    
}

/**
 * Remove all roles and all custom capabilities added to 
 * both custom roles and core roles.
 * 
 * @version 1.0
 */
function twitchpress_remove_roles() {
    global $wp_roles;

    if ( ! class_exists( 'WP_Roles' ) ) {
        return;
    }

    if ( ! isset( $wp_roles ) ) {
        $wp_roles = new WP_Roles();
    }

    $capabilities = twitchpress_get_core_capabilities();
    $capabilities = array_merge( $capabilities, twitchpress_get_developer_capabilities() );
    
    foreach ( $capabilities as $cap_group ) {
        foreach ( $cap_group as $cap ) {
            $wp_roles->remove_cap( 'twitchpressdeveloper', $cap );
        }
    }

    remove_role( 'twitchpressdeveloper' );
}

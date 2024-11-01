<?php
/**
 * Functions that directly integrate with the WP core and enhance WP 
 * most common UI features...
 *
 * @author   Ryan Bayne
 * @category Admin
 * @package  TwitchPress
 * @version  1.0
 */

function twitchpress_integration() {
    add_filter( 'plugin_action_links_' . TWITCHPRESS_PLUGIN_BASENAME, 'twitchpress_plugin_action_links' );
    add_filter( 'plugin_row_meta', 'twitchpress_plugin_row_meta', 10, 2 );
    
    // Register post types
    TwitchPress_Post_types::register_post_types();
    TwitchPress_Post_types::register_taxonomies();    
}

                       
/**
 * Show action links on the plugin screen.
 *
 * @param    mixed $links Plugin Action links
 * @return    array
 * 
 * @version 1.2
 */
function twitchpress_plugin_action_links( $links ) {
    $action_links = array(
        'settings' => '<a href="' . admin_url( 'admin.php?page=twitchpress' ) . '" title="' . esc_attr( __( 'View TwitchPress Settings', 'twitchpress' ) ) . '">' . __( 'Settings', 'twitchpress' ) . '</a>',
        'wizard' => '<a href="' . admin_url( 'index.php?page=twitchpress-setup' ) . '" title="' . esc_attr( __( 'Start TwitchPress Setup Wizard', 'twitchpress' ) ) . '">' . __( 'Setup Wizard', 'twitchpress' ) . '</a>',
    );

    return array_merge( $action_links, $links );
}

/**
 * Show row meta on the plugin screen.
 *
 * @param    mixed $links Plugin Row Meta
 * @param    mixed $file  Plugin Base file
 * @return    array
 * 
 * @version 1.0
 */
function twitchpress_plugin_row_meta( $links, $file ) {     
    if ( $file == TWITCHPRESS_PLUGIN_BASENAME ) {
        $row_meta = array(
            'discord' => '<a href="' . esc_url( apply_filters( 'twitchpress_support_url', 'https://discord.gg/ScrhXPE' ) ) . '" title="' . esc_attr( __( 'Visit Discord for support', 'twitchpress' ) ) . '">' . __( 'Discord', 'twitchpress' ) . '</a>',
            'github'  => '<a href="' . esc_url( apply_filters( 'twitchpress_github_url', 'https://github.com/RyanBayne/TwitchPress/issues' ) ) . '" title="' . esc_attr( __( 'Visit Project GitHub', 'twitchpress' ) ) . '">' . __( 'GitHub', 'twitchpress' ) . '</a>',
            'donate'  => '<a href="' . esc_url( apply_filters( 'twitchpress_donate_url', TWITCHPRESS_DONATE ) ) . '" title="' . esc_attr( __( 'Donate to Project', 'twitchpress' ) ) . '">' . __( 'Donate', 'twitchpress' ) . '</a>',
            'twitch'  => '<a href="https://twitch.tv/lolindark1" title="' . esc_attr( __( 'Donate to Project', 'twitchpress' ) ) . '">' . __( 'Twitch', 'twitchpress' ) . '</a>',
            'blog'    => '<a href="http://twitchpress.wordpress.com" title="' . esc_attr( __( 'Get project updates from the blog.', 'twitchpress' ) ) . '">' . __( 'Blog', 'twitchpress' ) . '</a>',
        );

        return array_merge( $links, $row_meta );
    }

    return (array) $links;
}

/**
* Adds a step to a BugNet trace. Does what
* function bugnet_add_trace_steps() does.
* 
* @param mixed $code
* @param mixed $description
*/
function twitchpress_bugnet_add_trace_steps( $code, $description ) {
    if( 'yes' !== get_option( 'bugnet_activate_tracing' ) ) { return; }
    global $wpdb;
    
    $back_trace = debug_backtrace( false, 1 );

    $wpdb->insert(
        $wpdb->prefix . "bugnet_tracing_steps",
        array(  
            'code'        => $code,
            'request'     => TWITCHPRESS_REQUEST_KEY,
            'description' => $description,
            'microtime'   => microtime( true ),
            'line'        => $back_trace[0]['line'],
            'function'    => $back_trace[0]['function']
        )
    );
}
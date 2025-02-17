<?php  
/**
 * TwitchPress Subscriber Manager UM Role Update Link Shortcode
 * 
 * Shortcode twitchpress_subman_update_um_role] outputs a link which will
 * update the Ultimate Member role for the current logged-in user. 
 *
 * @author   Ryan Bayne
 * @category Shortcodes
 * @package  TwitchPress/Core
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}               
         
// Output the shortcode. 
add_shortcode( 'twitchpress_update_um_role_button', 'twitchpress_update_um_role_button' );

// (admin_post) Listen for authenticated users clicking the link.  
add_action( 'admin_post_twitchpress_subman_um_role_sync', 'twitchpress_subman_um_role_sync' );

// (admin_post_nopriv) Listen for non-authenticated users clicking the link.
add_action( 'admin_post_nopriv_twitchpress_subman_um_role_sync', 'twitchpress_admin_post_nopriv_reject' );
   
/**
* Shortcode outputs a basic status for the giving channel. 
* 
* [twitchpress_subman_update_um_role]
* 
* @version 1.0
*/
function twitchpress_update_um_role_button( $atts ) {  
    $html_output = null;
                    
    $atts = shortcode_atts( array(             
       //'channel_id'   => null,
    ), $atts, 'twitchpress_update_um_role_button' );
         
    $href = admin_url( 'admin-post.php?action=twitchpress_subman_um_role_sync' ); 
    $html_output = '<a href="' . $href . '">' . __( 'Update Ultimate Member Role', 'twitchpress' ) . '</a>';
    
    return $html_output;
}
           
/**
* Manual sync processing.
* 
* @version 1.0
*/
function twitchpress_subman_um_role_sync() {
    // User must be authenticated. 
    if( !is_user_logged_in() ) {
        twitchpress_admin_post_nopriv_reject();
        exit;    
    }
    
    $wp_user_id = get_current_user_id();
    
    $twitch_channel_id = twitchpress_get_main_channels_twitchid();
    
    // Get the current users Twitch subscription plan. 
    $sub_plan = twitchpress_get_user_sub_tier( $wp_user_id );
        
    // Get possible current UM role. 
    $current_role = get_user_meta( $wp_user_id, 'role', true );
        
    if( !twitchpress_is_valid_sub_plan( $sub_plan ) ) 
    {
        // Get and apply default (none) UM role. 
        $next_role = get_option( 'twitchpress_um_subtorole_none', false );
        
        update_user_meta( $wp_user_id, 'role', $next_role );
                            
        twitchpress_shortcode_procedure_redirect( 0 );
        exit; 
    }
    else
    {
        // We have a valid plan, get the matching role. 
        $next_role = get_option( 'twitchpress_um_subtorole_' . $sub_plan, false );
        
        // Avoid processing the main account or administrators so they are never downgraded. 
        if( $wp_user_id === 1 || user_can( $wp_user_id, 'administrator' ) ) 
        { 
            twitchpress_shortcode_procedure_redirect( 1 );
            exit; 
        }
        
        // If the UM role setting isn't set or valid.       
        if( !$next_role || !is_string( $next_role ) )         
        {   
            // Get and apply default (none) UM role. 
            $next_role = get_option( 'twitchpress_um_subtorole_none', false );
            update_user_meta( $wp_user_id, 'role', $next_role );                   
            twitchpress_shortcode_procedure_redirect( 2, array( $sub_plan ) );
            exit;
        }
                
        // Get and apply default (none) UM role. 
        $next_role = get_option( 'twitchpress_um_subtorole_none', false );
        
        update_user_meta( $wp_user_id, 'role', $next_role );
                            
        twitchpress_shortcode_procedure_redirect( 3, array( $next_role, $sub_plan ) );
        exit;      
    }    
}

/**
* 
* 
*/
function twitchpress_admin_post_nopriv_reject() {
    wp_die( __( 'The action you requested requires you to be logged into this website.', 'twitchpress' ), __( 'Please Login First', 'twitchpress' ));
    exit;
}



    
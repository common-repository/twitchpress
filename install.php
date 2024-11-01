<?php
/**
 * Installation functions, excluding plugin updating and some optional installation
 * features that might relate to none active API or extension integration.
 * 
 * @author   Ryan Bayne
 * @category Admin
 * @package  TwitchPress/Core
 * @version  1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
* Installs the TwitchPress plugin on normal activation through the admin...
*         
* @version 2.0
*/
function twitchpress_activation_installation() {
    global $wpdb;

    if ( ! defined( 'TWITCHPRESS_INSTALLING' ) ) {
        define( 'TWITCHPRESS_INSTALLING', true );
    }

    // Additional file includes, version checks, conflict checks...
    twitchpress_installation_prepare();
    
    // Install/update core database tables...
    include_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'includes/classes/class.twitchpress-tables-installation.php' );
    $tables = new TwitchPress_Install_Tables();
    $tables->install(); 
    
    // Run individual installation functions...
    twitchpress_installation_add_developer_role();
    twitchpress_installation_roles_and_capabilities();
    twitchpress_installation_create_files();
    twitchpress_installation_create_options();
    twitchpress_installation_add_capabilities_keyholder();    
     
    // Run automatic updates. 
    twitchpress_installation_update();
    
    twitchpress_update_package_version();

    do_action( 'twitchpress_installed' );
}

/**
* Register core tables...
* 
* @version 1.0
*/
function twitchpress_register_tables() {
    global $wpdb;
    $wpdb->twitchpress_activity  = "{$wpdb->prefix}twitchpress_activity";
    $wpdb->twitchpress_errors    = "{$wpdb->prefix}twitchpress_errors";
    $wpdb->twitchpress_endpoints = "{$wpdb->prefix}twitchpress_endpoints";
    $wpdb->twitchpress_meta      = "{$wpdb->prefix}twitchpress_meta";    
}

function twitchpress_installation_prepare() {
    include_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'includes/admin/class.twitchpress-admin-notices.php' );
    
    // Flush old notices to avoid confusion during a new installation...
    TwitchPress_Admin_Notices::remove_all_notices(); 
    
    // Queue upgrades/setup wizard
    $current_installed_version = get_option( 'twitchpress_version', null );

    // No versions? This is a new install :)
    if ( is_null( $current_installed_version ) && apply_filters( 'twitchpress_enable_setup_wizard', true ) ) {  
        TwitchPress_Admin_Notices::add_notice( 'install' );
        delete_transient( '_twitchpress_activation_redirect' );
        set_transient( '_twitchpress_activation_redirect', 1, 30 );
    }                                  
}

function twitchpress_installation_roles_and_capabilities() {
   require_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'includes/admin/class.twitchpress-admin-roles.php' );
   $roles_obj = new TwitchPress_Roles_Capabilities_Installation();
   $roles_obj->add_roles_and_capabilities();
}

/**
* Automatic updater - runs when plugin is activated which happens during
* a standard WordPress plugin update.
* 
* @version 2.0
*/
function twitchpress_installation_update() {
              
}
    
/**
* If key values are missing we will offer the wizard. 
* 
* Does not apply when the setup wizard has not been complete. This is
* currently done by checking 
* 
* @version 3.1
*/
function twitchpress_offer_wizard() {
    $offer_wizard = false;
                                      
    if( !current_user_can( 'administrator' ) ) {        
        return;    
    }
    
    // Avoid registering notice during the Setup Wizard.
    if( isset( $_GET['page']) && $_GET['page'] == 'twitchpress-setup' ) {     
        return;    
    }
    
    // If already displaying the install notice, do not display.
    if( TwitchPress_Admin_Notices::has_notice( 'install' ) ) {        
        return;
    }

    $a = get_option( 'twitchpress_main_channels_name' );               
    $b = get_option( 'twitchpress_main_channels_id' );           
    $c = get_option( 'twitchpress_app_id' );                           
    $d = get_option( 'twitchpress_app_secret' );                       
    $e = get_option( 'twitchpress_main_channels_code' );               
    $f = get_option( 'twitchpress_main_channels_token' );             
    
    if( !$a ) { $offer_wizard = 'twitchpress_main_channels_name'; } 
    elseif( !$b ) { $offer_wizard = 'twitchpress_main_channels_id'; } 
    elseif( !$c ) { $offer_wizard = 'twitchpress_app_id'; } 
    elseif( !$d ) { $offer_wizard = 'twitchpress_app_secret'; } 
    elseif( !$e ) { $offer_wizard = 'twitchpress_main_channels_code'; } 
    elseif( !$f ) { $offer_wizard = 'twitchpress_main_channels_token'; }     
    
    if( $offer_wizard === false ) { return; }
    
    // Build a link to wizard...
    $wizard_link = '<p><a href="' . admin_url( 'index.php?page=twitchpress-setup' ) . '" class="button button-primary">' . __( 'Setup Wizard', 'twitchpress' ) . '</a></p>';
    
    // Add a new installation notice if it appears to be a fresh installation...
    if( !$a && !$b && !$c && !$d && !$e && !$f ) {
        
        TwitchPress_Admin_Notices::add_wordpress_notice(
            'noappvaluesofferwizard',
            'info',
            false,
            __( 'Setup Wizard', 'twitchpress' ),
            sprintf( __( 'TwitchPress includes a Setup Wizard to help you get the plugin configured, please complete it now. %s', 'twitchpress'), $wizard_link )    
        );

    } else {

        TwitchPress_Admin_Notices::add_wordpress_notice(
            'missingvaluesofferwizard',
            'info',
            false,
            __( 'Twitch API Credentials Missing', 'twitchpress' ),
            sprintf( __( 'TwitchPress is not ready because the %s option is missing. If you have already been using the plugin and this notice suddenly appears then it suggests important options have been deleted or renamed. You can go through the Setup Wizard again to correct this problem. You should also report it. %s', 'twitchpress'), $offer_wizard, $wizard_link )    
        );      
    }     
}
    
/**
* Update plugin version.
* 
* @version 1.0
*/
function twitchpress_update_package_version() {
    delete_option( 'twitchpress_version' );
    add_option( 'twitchpress_version', TWITCHPRESS_VERSION );
} 
        
/**
 * Update DB version to current.
 */
function twitchpress_update_db_version( $version = null ) {
    delete_option( 'twitchpress_db_version' );
    add_option( 'twitchpress_db_version', is_null( $version ) ? TWITCHPRESS_VERSION : $version );
} 
    
/**
* Very strict capabilities for professional developers only.
* 
* @version 1.0
*/
function twitchpress_get_developer_capabilities() {
    $capabilities = array();

    $capabilities['core'] = array(
        'twitchpress_developer',
        'code_twitchpress',
        'twitchpressdevelopertoolbar'
    );

    return $capabilities;        
}

/**
 * Add the special developer role. 
 * 
 * Function originally named "twitchpress_create_roles"
 * 
 * @version 2.0
 */
function twitchpress_installation_add_developer_role() {
    global $wp_roles;

    if ( ! class_exists( 'WP_Roles' ) ) {
        return;
    }

    if ( ! isset( $wp_roles ) ) {
        $wp_roles = new WP_Roles();
    }

    // TwitchPress Developer role
    add_role( 'twitchpressdeveloper', __( 'TwitchPress Developer', 'twitchpress' ), array(
        'level_9'                => true,
        'level_8'                => true,
        'level_7'                => true,
        'level_6'                => true,
        'level_5'                => true,
        'level_4'                => true,
        'level_3'                => true,
        'level_2'                => true,
        'level_1'                => true,
        'level_0'                => true,
        'read'                   => true,
        'read_private_pages'     => true,
        'read_private_posts'     => true,
        'edit_users'             => true,
        'edit_posts'             => true,
        'edit_pages'             => true,
        'edit_published_posts'   => true,
        'edit_published_pages'   => true,
        'edit_private_pages'     => true,
        'edit_private_posts'     => true,
        'edit_others_posts'      => true,
        'edit_others_pages'      => true,
        'publish_posts'          => true,
        'publish_pages'          => true,
        'delete_posts'           => true,
        'delete_pages'           => true,
        'delete_private_pages'   => true,
        'delete_private_posts'   => true,
        'delete_published_pages' => true,
        'delete_published_posts' => true,
        'delete_others_posts'    => true,
        'delete_others_pages'    => true,
        'manage_categories'      => true,
        'manage_links'           => true,
        'moderate_comments'      => true,
        'unfiltered_html'        => true,
        'upload_files'           => true,
        'export'                 => true,
        'import'                 => true,
        'list_users'             => true
    ) );

    // Add custom capabilities to our new TwitchPress Developers role. 
    $new_admin_capabilities = twitchpress_get_developer_capabilities();
    foreach ( $new_admin_capabilities as $cap_group ) {
        foreach ( $cap_group as $cap ) {
            $wp_roles->add_cap( 'twitchpressdeveloper', $cap );                
        }
    }        
    
}

/**
 * Create files/directories with .htaccess and index files added by default.
 * 
 * @version 1.0
 */
function twitchpress_installation_create_files() {
    // Install files and folders for uploading files and prevent hotlinking
    $upload_dir      = wp_upload_dir();
    $download_method = get_option( 'twitchpress_file_download_method', 'force' );
                                         
    $files = array(
        array(
            'base'         => $upload_dir['basedir'] . '/twitchpress_uploads',
            'file'         => 'index.html',
            'content'     => ''
        ),
        array(
            'base'         => TWITCHPRESS_LOG_DIR,
            'file'         => '.htaccess',
            'content'     => 'deny from all'
        ),
        array(
            'base'         => TWITCHPRESS_LOG_DIR,
            'file'         => 'index.html',
            'content'     => ''
        )
    );

    if ( 'redirect' !== $download_method ) {
        $files[] = array(
            'base'         => $upload_dir['basedir'] . '/twitchpress_uploads',
            'file'         => '.htaccess',
            'content'     => 'deny from all'
        );
    }

    foreach ( $files as $file ) {
        if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
            if ( $file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' ) ) {
                fwrite( $file_handle, $file['content'] );
                fclose( $file_handle );
            }
        }
    }
}
    
/**
 * Adds default options from settings files.
 * 
 * @version 1.0
 */
function twitchpress_installation_create_options() {
    // Include settings so that we can run through defaults
    include_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'includes/admin/class.twitchpress-admin-settings.php' );
    $settings = TwitchPress_Admin_Settings::get_settings_pages();

    foreach ( $settings as $section ) {
        if ( !method_exists( $section, 'get_settings' ) ) {
            continue;
        }
        
        $subsections = array_unique( array_merge( array( '' ), array_keys( $section->get_sections() ) ) );

        foreach ( $subsections as $subsection ) {
            foreach ( $section->get_settings( $subsection ) as $value ) {
                if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
                    $autoload = isset( $value['autoload'] ) ? (bool) $value['autoload'] : true;
                    add_option( $value['id'], $value['default'], '', ( $autoload ? 'yes' : 'no' ) );
                }
            }
        }
    }
}

/**
* Use to add default capabilities to the key holder.
* 
* @version 1.0
*/
function twitchpress_installation_add_capabilities_keyholder() {    
    $user = new WP_User( 1 );// Give the site owner permission to do everything a TwitchPress Developer would...
    foreach ( twitchpress_get_developer_capabilities() as $cap_group ) {
        foreach ( $cap_group as $cap ) {
            $user->add_cap( $cap );                 
        }
    }        
}
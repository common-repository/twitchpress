<?php
/**
 * Setup Wizard which completes installation of plugin. 
 *
 * @author      Ryan Bayne
 * @category    Admin
 * @package     TwitchPress/Admin
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists( 'TwitchPress_Admin_Setup_Wizard' ) ) :

/**
 * TwitchPress_Admin_Setup_Wizard Class 
 * 
 * Class originally created by ** Automattic ** and is the best approach to plugin
 * installation found if an author wants to treat the user and their site with
 * respect.
 *
 * @author      Ryan Bayne
 * @category    Admin
 * @package     TwitchPress/Admin
 * @version     2.0
*/
class TwitchPress_Admin_Setup_Wizard {

    /** @var string Current Step */
    private $step = '';

    /** @var array Steps for the setup wizard */
    private $steps = array();

    /** @var boolean Is the wizard optional or required? */
    private $optional = false;

    /**
     * Hook in tabs.
     */
    public function __construct() {
        
        if ( apply_filters( 'twitchpress_enable_setup_wizard', true ) && current_user_can( 'activate_plugins' ) ) {
            add_action( 'admin_menu', array( $this, 'admin_menus' ) );
            add_action( 'admin_init', array( $this, 'setup_wizard' ) );
        } 
    }

    /**
     * Add admin menus/screens.
     */
    public function admin_menus() {
        add_dashboard_page( '', '', 'manage_options', 'twitchpress-setup', '' );
    }

    /**
     * Show the setup wizard.
     * 
     * @version 1.0
     */
    public function setup_wizard() {
        if ( empty( $_GET['page'] ) || 'twitchpress-setup' !== $_GET['page'] ) {
            return;
        }
        
        // Ensure install related notices no longer show. 
        TwitchPress_Admin_Notices::remove_notice( 'install' );
        TwitchPress_Admin_Notices::remove_notice( 'missingvaluesofferwizard' );

        $this->steps = array(
            'introduction' => array(
                'name'    =>  __( 'Introduction', 'twitchpress' ),
                'view'    => array( $this, 'twitchpress_setup_introduction' ),
                'handler' => ''
            ),
            'application' => array(
                'name'    =>  __( 'Application', 'twitchpress' ),
                'view'    => array( $this, 'twitchpress_setup_application' ),
                'handler' => array( $this, 'twitchpress_setup_application_save' )
            ),
            'folders' => array(
                'name'    =>  __( 'Files', 'twitchpress' ),
                'view'    => array( $this, 'twitchpress_setup_folders' ),
                'handler' => array( $this, 'twitchpress_setup_folders_save' )
            ),
            'database' => array(
                'name'    =>  __( 'Database', 'twitchpress' ),
                'view'    => array( $this, 'twitchpress_setup_database' ),
                'handler' => array( $this, 'twitchpress_setup_database_save' ),
            ), 
            'services' => array(
                'name'    =>  __( 'Services', 'twitchpress' ),
                'view'    => array( $this, 'twitchpress_setup_services' ),
                'handler' => array( $this, 'twitchpress_setup_services_save' ),
            ),
            'extensions' => array(
                'name'    =>  __( 'Extensions', 'twitchpress' ),
                'view'    => array( $this, 'twitchpress_setup_extensions' ),
                'handler' => array( $this, 'twitchpress_setup_extensions_save' ),
            ),                                   
            'improvement' => array(
                'name'    =>  __( 'Options', 'twitchpress' ),
                'view'    => array( $this, 'twitchpress_setup_improvement' ),
                'handler' => array( $this, 'twitchpress_setup_improvement_save' ),
            ),
            'next_steps' => array(
                'name'    =>  __( 'Ready!', 'twitchpress' ),
                'view'    => array( $this, 'twitchpress_setup_ready' ),
                'handler' => ''
            )
        );
        
        $this->steps = apply_filters( 'twitchpress_wizard_menu', $this->steps );
        
        $this->step = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) );
        $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

        // Register scripts for the pretty extension presentation and selection.
        wp_register_script( 'jquery-blockui', TWITCHPRESS_PLUGIN_URL . '/assets/js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array( 'jquery' ), '2.70', true );
        wp_register_script( 'select2', TWITCHPRESS_PLUGIN_URL . '/assets/js/select2/select2' . $suffix . '.js', array( 'jquery' ), '3.5.2' );
        wp_register_script( 'twitchpress-enhanced-select', TWITCHPRESS_PLUGIN_URL . '/assets/js/admin/twitchpress-enhanced-select' . $suffix . '.js', array( 'jquery', 'select2' ), TWITCHPRESS_VERSION );
        
        // Queue CSS for the entire setup process.
        wp_enqueue_style( 'twitchpress_admin_styles', TWITCHPRESS_PLUGIN_URL . '/assets/css/admin.css', array(), TWITCHPRESS_VERSION );
        wp_enqueue_style( 'twitchpress-setup', TWITCHPRESS_PLUGIN_URL . '/assets/css/twitchpress-setup.css', array( 'dashicons', 'install' ), TWITCHPRESS_VERSION );
        wp_register_script( 'twitchpress-setup', TWITCHPRESS_PLUGIN_URL . '/assets/js/admin/twitchpress-setup.min.js', array( 'jquery', 'twitchpress-enhanced-select', 'jquery-blockui' ), TWITCHPRESS_VERSION );

        if ( ! empty( $_POST['save_step'] ) && isset( $this->steps[ $this->step ]['handler'] ) ) {
            call_user_func( $this->steps[ $this->step ]['handler'] );
        }
    
        ob_start();
        $this->setup_wizard_header();
        $this->setup_wizard_steps();
        $this->setup_wizard_content();
        $this->setup_wizard_footer();
        exit;
    }

    public function get_next_step_link() {
        $keys = array_keys( $this->steps );
        return add_query_arg( 'step', $keys[ array_search( $this->step, array_keys( $this->steps ) ) + 1 ] );
    }

    /**
     * Setup Wizard Header.
     */
    public function setup_wizard_header() {        
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta name="viewport" content="width=device-width" />
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title><?php _e( 'WordPress TwitchPress &rsaquo; Setup Wizard', 'twitchpress' ); ?></title>
            <?php wp_print_scripts( 'twitchpress-setup' ); ?>
            <?php do_action( 'admin_print_styles' ); ?>
            <?php do_action( 'admin_head' ); ?>
        </head>
        <body class="twitchpress-setup wp-core-ui">
            <h1 id="twitchpress-logo"><a href="<?php echo TWITCHPRESS_HOME;?>"><img src="<?php echo TWITCHPRESS_PLUGIN_URL; ?>/assets/images/twitchpress_logo.png" alt="TwitchPress" /></a></h1>
        <?php
    }

    /**
     * Setup Wizard Footer.
     */
    public function setup_wizard_footer() { 
        if ( 'next_steps' === $this->step ) : ?>
                <a class="twitchpress-return-to-dashboard" href="<?php echo esc_url( admin_url() ); ?>"><?php _e( 'Return to the WordPress Dashboard', 'twitchpress' ); ?></a>
            <?php endif; ?>
            </body>
        </html>
        <?php
    }

    /**
     * Output the steps.
     */
    public function setup_wizard_steps() {      
        $ouput_steps = $this->steps;
        array_shift( $ouput_steps );
        ?>
        <ol class="twitchpress-setup-steps">
            <?php foreach ( $ouput_steps as $step_key => $step ) : ?>
                <li class="<?php
                    if ( $step_key === $this->step ) {
                        echo 'active';
                    } elseif ( array_search( $this->step, array_keys( $this->steps ) ) > array_search( $step_key, array_keys( $this->steps ) ) ) {
                        echo 'done';
                    }
                ?>"><?php echo esc_html( $step['name'] ); ?></li>
            <?php endforeach; ?>
        </ol>
        <?php
    }

    /**
     * Output the content for the current step.
     */
    public function setup_wizard_content() {           
        echo '<div class="twitchpress-setup-content">'; 
        
        if( !isset( $this->steps[ $this->step ]['view'] ) ) {
            ?><h1><?php _e( 'Invalid Step!', 'twitchpress' ); ?></h1><p><?php _e( 'You have attempted to visit a setup step that does not exist. I would like to know how this happened so that I can improve the plugin. Please tell me what you did before this message appeared. If you were just messing around, then stop it you naughty hacker!', 'twitchpress' ); ?></p><?php 
        } elseif( !method_exists( $this, $this->steps[ $this->step ]['view'][1] ) ) {
            ?><h1><?php _e( 'Something Has Gone Very Wrong!', 'twitchpress' ); ?></h1><p><?php _e( 'You have attempted to visit a step in the setup process that may not be ready yet! This should not have happened. Please report it to me.', 'twitchpress' ); ?></p><?php             
        } elseif( !current_user_can( 'activate_plugins' ) ) {
            ?><h1><?php _e( 'Administrators Only', 'twitchpress' ); ?></h1><p><?php _e( 'Only administrators can complete the installation of TwitchPress.', 'twitchpress' ); ?></p><?php                         
        } else {
            TwitchPress_Admin_Notices::output_custom_notices();
            call_user_func( $this->steps[ $this->step ]['view'] );
        }
        
        echo '</div>';
    }

    /**
     * Introduction step.
     */
    public function twitchpress_setup_introduction() { ?>
        <h1><?php _e( 'Setup TwitchPress', 'twitchpress' ); ?></h1>
        
        <?php if( $this->optional ) { ?>
        
        <p><?php _e( 'Thank you for choosing TwitchPress! The setup wizard will walk you through some essential settings and explain the changes being made to your blog. <strong>It’s completely optional and shouldn’t take longer than five minutes.</strong>', 'twitchpress' ); ?></p>
        <p><?php _e( 'No time right now? If you don’t want to go through the wizard, you can skip and return to the WordPress dashboard. You will be able to use the plugin but you might miss some features!', 'twitchpress' ); ?></p>
        <p class="twitchpress-setup-actions step">
            <a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button-primary button button-large button-next"><?php _e( 'Let\'s Go!', 'twitchpress' ); ?></a>
            <a href="<?php echo esc_url( admin_url() ); ?>" class="button button-large"><?php _e( 'Not right now', 'twitchpress' ); ?></a>
        </p>
        
        <?php } else { ?> 
            
        <p><?php _e( 'Thank you for choosing TwitchPress! The setup wizard will walk you through some essential settings and explain the changes being made to your blog. <strong>It is required before you can use the plugin but it shouldn’t take longer than five minutes.</strong> You will be asked to enter your Twitch Application credentials. If you do not have time to do this right now. Please click on the "Not Right Now" button below.', 'twitchpress' ); ?></p>
                
        <h1><?php _e( 'Twitch Developer Services Agreement', 'twitchpress' ); ?></h1>

        <p><?php _e( 'By continuing to use the TwitchPress plugin you are agreeing to comply with the Twitch Developer Services Agreement and the GNU General Public License (version 3). You agree to be bound by them in the development of your WordPress site without any exceptions. If you do not agree to either licenses or understand them fully then please do not continue and seek advice.', 'twitchpress' ); ?></p>
        
        <form method="post">
            <p class="twitchpress-setup-actions step">
                <a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button-primary button button-large button-next"><?php _e( 'Accept Agreement', 'twitchpress' ); ?></a>
                <a href="https://www.twitch.tv/p/developer-agreement" class="button button-large" target="_blank"><?php _e( 'Read Agreement', 'twitchpress' ); ?></a>
                <a href="<?php echo esc_url( admin_url() ); ?>" class="button button-large"><?php _e( 'Not right now', 'twitchpress' ); ?></a>
                <?php wp_nonce_field( 'twitchpress-setup' ); ?>
            </p>
        </form>        
                    
        <?php }
    }

    /**
     * Step requesting the user to accept the Twitch Developer Services Agreement
     * or avoid using the plugin.
     * 
     * @version 6.0
     */
    public function twitchpress_setup_application() {?>
        <h1><?php _e( 'Enter Twitch Application Credentials', 'twitchpress' ); ?></h1>
        
        <p><?php _e( 'Although you\'re using a plugin I created. All responsibilities are yours. You will need to create a new Developer Application within your own Twitch account and enter it\'s credentials below. All use of the Twitch API by TwitchPress will be done through your own account. If you are acting on behalf of a business or team please take care when deciding which Twitch account to log into. You should use the Twitch account considered to be the official or main user.', 'twitchpress' ); ?></p>

        <h3><?php _e( 'Some support buttons, they will open new tabs...', 'twitchpress' ); ?></h3>
              
        <p class="twitchpress-setup-actions step">
            <a href="https://dev.twitch.tv/dashboard/apps" class="button button-large" target="_blank"><?php _e( 'Manage Twitch Apps', 'twitchpress' ); ?></a>                                                                
            <a href="https://discord.gg/ScrhXPE" class="button button-large" target="_blank"><?php _e( 'Live Chat Help (Discord)', 'twitchpress' ); ?></a>                
        </p>
                                                
        <form method="post">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="twitchpress_main_channels_name"><?php _e( 'Main Channels Name', 'twitchpress' ); ?></label></th>
                    <td>
                        <input type="text" id="twitchpress_main_channels_name" name="twitchpress_main_channels_name" class="input-text" value="<?php echo esc_html( get_option( 'twitchpress_main_channels_name' ) );?>" />
                        <label for="twitchpress_main_channels_name"><?php _e( 'example: LOLinDark1, StarCitizen, nookyyy', 'twitchpress' ); ?></label>
                    </td>
                </tr>               
                <tr>
                    <th scope="row"><label for="twitchpress_app_redirect"><?php _e( 'App Redirect URI', 'twitchpress' ); ?></label></th>
                    <td>
                        <input type="text" id="twitchpress_app_redirect" name="twitchpress_app_redirect" class="input-text" value="<?php echo get_option( 'twitchpress_app_redirect' );?>" />
                        <label for="twitchpress_app_redirect"><?php echo __( 'example: ', 'twitchpress' ) . get_site_url(); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="twitchpress_app_id"><?php _e( 'Client/App ID', 'twitchpress' ); ?></label></th>
                    <td>
                        <input type="text" id="twitchpress_app_id" name="twitchpress_app_id" class="input-text" value="<?php echo get_option( 'twitchpress_app_id' );?>" />
                        <label for="twitchpress_app_id"><?php _e( 'example: uo6dggojyb8d6soh92zknwmi5ej1q2', 'twitchpress' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="twitchpress_app_secret"><?php _e( 'Client/App Secret', 'twitchpress' ); ?></label></th>
                    <td>
                        <input type="password" id="twitchpress_app_secret" name="twitchpress_app_secret" class="input-text" value="<?php echo get_option( 'twitchpress_app_secret' );?>" />
                        <label for="twitchpress_app_secret"><?php _e( 'example: nyo51xcdrerl8z9m56w9w6wg', 'twitchpress' ); ?></label>
                    </td>
                </tr>                
            </table>
            
            <?php   
            if( TWITCHPRESS_DEV_MODE ) {
            ?>
            
            <p class="twitchpress-setup-actions step">
                <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Dev Mode: Apply All Scopes', 'twitchpress' ); ?>" name="save_step" />
            </p>
            
            <?php 
            }
            ?>
            
            <h3><?php _e( 'Scopes for Administrators Twitch Accounts', 'twitchpress' ); ?></h3>
            <p><?php _e( 'Setup permission for this website to access your own Twitch accounts. Select the scopes that apply to your operation and the features you will use in TwitchPress. If you plan to install extensions, those extensions might need specific scopes. If in doubt you can select all of them. Just remember to consider your main channels security when adding administrators to your site.', 'twitchpress' ); ?></p>
             
            <table class="form-table">                    
                
                <?php
                foreach( twitchpress_scopes() as $scope => $info ) {
                    $scope_edited = str_replace( ':', '_', $scope );
                ?>
                 <tr>
                    <th scope="row"><label for="twitchpress_scope_<?php echo $scope_edited; ?>"><?php echo $scope; ?></label></th>
                    <td>
                        <input type="checkbox" id="twitchpress_scope_<?php echo $scope_edited; ?>" name="twitchpress_scopes[]" class="input-checkbox" value="<?php echo $scope_edited; ?>" <?php checked( get_option( 'twitchpress_scope_' . $scope_edited ), 'yes', true ); ?> />
                        <label for="twitchpress_scope_<?php echo $scope_edited; ?>"><?php echo $info['label']; ?></label>
                    </td>
                </tr>                
                <?php 
                }
                ?>

            </table>  
                    
            <h3><?php _e( 'Scopes for Visitors Twitch Accounts', 'twitchpress' ); ?></h3>
            <p><?php _e( 'Ask your users to grant this website permission to access their Twitch data. Only select the scopes that will be needed for public features and the services your website will provide to Twitch users. Visitors will be shown your selected scopes during an oAuth2 request.', 'twitchpress' ); ?></p>
             
            <table class="form-table">  
            
                <?php
                foreach( twitchpress_scopes() as $scope => $info ) {
                    $scope_edited = str_replace( ':', '_', $scope );
                ?>
                 <tr>
                    <th scope="row"><label for="twitchpress_visitor_scope_<?php echo $scope_edited; ?>"><?php echo $scope; ?></label></th>
                    <td>
                        <input type="checkbox" id="twitchpress_visitor_scope_<?php echo $scope_edited; ?>" name="twitchpress_visitor_scopes[]" class="input-checkbox" value="<?php echo $scope_edited; ?>" <?php checked( get_option( 'twitchpress_visitor_scope_' . $scope_edited ), 'yes', true ); ?> />
                        <label for="twitchpress_scope_<?php echo $scope_edited; ?>"><?php echo $info['label']; ?></label>
                    </td>
                </tr>                
                <?php 
                }
                ?>
                   
            </table>   
                   
            <p class="twitchpress-setup-actions step">
                <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Continue', 'twitchpress' ); ?>" name="save_step" />
                <?php wp_nonce_field( 'twitchpress-setup' ); ?>
            </p>
        </form>
                
        <?php    
    }

    /**
     * Save application settings and then forwards user to Twitch oauth2.
     * 
     * @version 3.2
     */
    public function twitchpress_setup_application_save() {          
        check_admin_referer( 'twitchpress-setup' );
        
        // Sanitize $_POST values.
        $main_channel  = sanitize_text_field( $_POST['twitchpress_main_channels_name'] );
        $redirect_uri  = sanitize_text_field( $_POST['twitchpress_app_redirect'] );
        $app_id        = sanitize_text_field( $_POST['twitchpress_app_id'] );
        $app_secret    = sanitize_text_field( $_POST['twitchpress_app_secret'] );

        $all_scopes = twitchpress_scopes();
                                                                                          
        // Was dev mode button used for applying all scopes...
        if( TWITCHPRESS_DEV_MODE && isset( $_POST['save_step'] ) && $_POST['save_step'] == "Dev Mode: Apply All Scopes" ) {
            foreach( $all_scopes as $scope => $scope_info ) {    
                update_option( 'twitchpress_scope_' . str_replace( ':', '_', $scope ), 'yes' );
                update_option( 'twitchpress_visitor_scope_' . str_replace( ':', '_', $scope ), 'yes' );
                $new_app_scopes[] = $scope;
            }    
        } else {
            
            // Delete options for scopes that are not in $_POST (not checked) and add those that are.
            $new_app_scopes = array();
            if( isset( $_POST['twitchpress_scopes'] ) ) {
                foreach( $all_scopes as $scope => $scope_info ) {  
                    if( in_array( str_replace( ':', '_', $scope ), $_POST['twitchpress_scopes'] ) ) {   
                        update_option( 'twitchpress_scope_' . str_replace( ':', '_', $scope ), 'yes' ); 
                        $new_app_scopes[] = $scope;
                    } else {                         
                        delete_option( 'twitchpress_scope_' . str_replace( ':', '_', $scope ) );
                    }
                }
            }
            
            // Store the main scopes as app scopes for the servers own needs...
            twitchpress_update_app_token_scopes( $new_app_scopes );
            
            if( isset( $_POST['twitchpress_visitor_scopes'] ) ) {
                foreach( $all_scopes as $scope => $scope_info ) {  
                    if( in_array( str_replace( ':', '_', $scope ), $_POST['twitchpress_visitor_scopes'] ) ) {  
                        update_option( 'twitchpress_visitor_scope_' . str_replace( ':', '_', $scope ), 'yes' );
                    } else {                                       
                        delete_option( 'twitchpress_visitor_scope_' . str_replace( ':', '_', $scope ) );
                    }
                }
            }
        }
 
        // Store Main Channel
        if( empty( $main_channel ) ) {
            TwitchPress_Admin_Notices::add_custom_notice( 'wizardcredentialsincomplete', sprintf( __( 'Application credentails are missing. All four inputs need a value.'), esc_html( $main_channel ) ) );            
            return;
        } else {
            update_option( 'twitchpress_main_channels_name',  $main_channel,  true );            
        }
        
        // Store Redirect URI
        if( empty( $redirect_uri ) ) {
            TwitchPress_Admin_Notices::add_custom_notice( 'wizardcredentialsincomplete', sprintf( __( 'Application credentails are missing. All four inputs need a value.'), esc_html( $main_channel ) ) );            
            return;
        } else {
            twitchpress_update_app_redirect( trim( $redirect_uri ) );        
        }        
        
        // Store App ID
        if( empty( $app_id ) ) {
            TwitchPress_Admin_Notices::add_custom_notice( 'wizardcredentialsincomplete', sprintf( __( 'Application credentails are missing. All four inputs need a value.'), esc_html( $main_channel ) ) );            
            return;
        } else {
            twitchpress_update_app_id( $app_id );
        }        
        
        // Store App Secret
        if( empty( $app_secret ) ) {
            TwitchPress_Admin_Notices::add_custom_notice( 'wizardcredentialsincomplete', sprintf( __( 'Application credentails are missing. All four inputs need a value.'), esc_html( $main_channel ) ) );            
            return;
        } else {
            twitchpress_update_app_secret( $app_secret );
        }
       
        // Twitch API requires one or more permissions in the scope. 
        if( !isset( $_POST['twitchpress_scopes'] ) ) {
            TwitchPress_Admin_Notices::add_custom_notice( 'wizardchanneldoesnotexist', sprintf( __( 'Please select one or more scopes. Each will enable services and features using the permissions each scope gives. It is best practice to avoid requesting permissions your site does not need.'), esc_html( $main_channel ) ) );
            return;
        }
      
        ########################################################################
        #                                                                      #
        #                       REQUEST APP ACCESS TOKEN                       #
        #                                                                      #
        ########################################################################
                       
        // Request new app Access Token (replaces any existing token)
        $twitch_api_obj = new TWITCHPRESS_Twitch_API();
        $call_object = $twitch_api_obj->request_app_access_token( __FUNCTION__ );                                 

        // Error on no curl response code being found...
        if( !isset( $call_object->curl_reply['response']['code'] ) ) {
            TwitchPress_Admin_Notices::add_custom_notice( 'getapptokennoresponsecode', __( 'Requested application token but the reply does not include a response code.' ) );
            return false;    
        }        
        
        // Error on curl response code not being 200...
        if( $call_object->curl_reply['response']['code'] !== 200 ) {
            TwitchPress_Admin_Notices::add_custom_notice( 'getapptokenresponsecodenot200', sprintf( __( 'App token requested but the response code is %s and should be 200!'), $call_object->curl_reply['response']['code'] ) );
            return false;    
        }
        
        $token_outcome = $twitch_api_obj->app_access_token_process_call_reply( $call_object );    

        if( !$token_outcome ) {
            // Handle a failed call...
            TwitchPress_Admin_Notices::add_custom_notice( 'failedtogettokenduringwizard', sprintf( __( 'Something went wrong after requesting an access token from the Twitch API. Please post this message, screenshot of any errors and PHP logs in the plugins Discord.') ) );
            return false;   
        }
        
        ########################################################################
        #                                                                      #
        #                          VALIDATE CHANNEL                            #
        #                                                                      #
        ########################################################################
                 
        // Confirm the giving main channel is valid.   
        $twitch_api_obj = new TWITCHPRESS_Twitch_API();
        $user_objects = $twitch_api_obj->get_user_without_email_by_login_name( $main_channel );
    
        if( !isset( $user_objects->data[0]->id ) ) {
            TwitchPress_Admin_Notices::add_custom_notice( 'wizardchanneldoesnotexist', sprintf( __( 'The channel you entered was not found. You entered %s. Please check the spelling and try again.'), esc_html( $main_channel ) ) );
            return;                         
        }
        
        $twitch_user_id = $user_objects->data[0]->id;        
    

        // The current user has logged into what we will assume is their own personal account, for now.
        // So add the channel/user ID to their user meta.  
        //update_user_meta( get_current_user_id(), 'twitchpress_twitch_id', $twitch_user_id );
               
        // The user ID is the same as the channel ID on Twitch.tv just because.
        // We store the admins selected channel as the account that manages the app.
        twitchpress_update_main_channels_id( $twitch_user_id );

        // This might be a re-authorization and we cannot assume the same channel is being entered as
        // was initially entered. Check if the twitchchannel post already exists for the giving credentials.
        $existing_channelpost_id = twitchpress_get_channel_post( $twitch_user_id );
                
        // Insert a new twitchchannel post if one does not already exist.
        if( !$existing_channelpost_id ) 
        { 
            $post_id = twitchpress_insert_channel( 
                $twitch_user_id, 
                $main_channel, 
                true 
            );
                
            if( !$post_id ) {
                TwitchPress_Admin_Notices::add_custom_notice( 'mainpostfailedtoinsert', __( 'TwitchPress needs to create a custom post to hold your channel information, but could not. This is required to continue. Please try again and seek support if you see this notice again.' ) );      
                return;
            }
            
            // NEW            
            twitchpress_update_main_channels_postid( $post_id );
        } 
        else 
        {
            $post_id = $existing_channelpost_id;
        } 
              
        // Confirm storage of application and that oAuth2 is next.        
        TwitchPress_Admin_Notices::add_custom_notice( 'applicationcredentialssaved', __( 'Your application credentials have been stored and your WordPress site is ready to communicate with Twitch.' ) );
                      
        // Cleanup
        unset( $existing_channelpost_id, $twitch_api_obj, $state, $user_objects );
        
        check_admin_referer( 'twitchpress-setup' );
        wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
        exit;
    }

    /**
     * Folders and files step.
     * 
     * @version 1.3
     */
    public function twitchpress_setup_folders() { 

        $upload_dir = wp_upload_dir();?>
        
        <h1><?php _e( 'Folders &amp; Files', 'twitchpress' ); ?></h1>
        
        <p><?php _e( 'These are the folders and files that have been created. Please try to avoid removing the folders and files you see in the list above.', 'twitchpress' ); ?></p>
                    
        <form method="post">
            <table class="twitchpress-setup-extensions" cellspacing="0">
                <thead>
                    <tr>
                        <th class="extension-name"><?php _e( 'Type', 'twitchpress' ); ?></th>
                        <th class="extension-description"><?php _e( 'Path', 'twitchpress' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="access-name"><?php _e( 'Folder', 'twitchpress' ); ?></td>
                        <td><?php echo $upload_dir['basedir'] . '/twitchpress_uploads'; ?></td>
                    </tr>
                    <tr>
                        <td class="access-name"><?php _e( 'Folder', 'twitchpress' ); ?></td>
                        <td><?php echo TWITCHPRESS_LOG_DIR; ?></td>
                    </tr>
                    <tr>
                        <td class="access-name"><?php _e( 'File', 'twitchpress' ); ?></td>
                        <td><?php echo TWITCHPRESS_LOG_DIR . '.htaccess'; ?></td>
                    </tr>
                    <tr>
                        <td class="access-name"><?php _e( 'File', 'twitchpress' ); ?></td>
                        <td><?php echo TWITCHPRESS_LOG_DIR . 'index.html'; ?></td>
                    </tr>
                </tbody>
            </table>
            
            <p class="twitchpress-setup-actions step">
                <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Continue', 'twitchpress' ); ?>" name="save_step" />
                <?php wp_nonce_field( 'twitchpress-setup' ); ?>
            </p>
        </form>
        <?php
    }

    /**
     * Create folders and files.
     */
    public function twitchpress_setup_folders_save() {       
        check_admin_referer( 'twitchpress-setup' );
        wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
        exit;
    }

    /**
     * Database changes overview step.
     */
    public function twitchpress_setup_database() {    
        global $wpdb;
            
        $installation = 'standard';
        switch ($installation) {
           case 'standard':
                $message = __( 'New tables will be added to your WordPress database. This is a normal operation for a plugin.', 'twitchpress' );
             break;
           case 'minimal':
                $message = __( 'The plugin will not create or alter any database tables for this installation.', 'twitchpress' );
             break;
           default: 
                $message = __( 'New tables will be added to your WordPress database. This is a normal operation for WP plugins.', 'twitchpress' );      
             break;
        }
        
        ?>
        <h1><?php _e( 'Database Changes', 'twitchpress' ); ?></h1>
        <form method="post">
                        
            <p>
                <?php echo $message; ?>
            </p>
            
            <ul>
                <li><?php echo $wpdb->prefix; ?>_twitchpress_activity</li>
                <li><?php echo $wpdb->prefix; ?>_twitchpress_errors</li>
                <li><?php echo $wpdb->prefix; ?>_twitchpress_endpoints</li>
                <li><?php echo $wpdb->prefix; ?>_twitchpress_meta</li>
            </ul>
            
            <p><?php _e( 'The above tables will be installed when you click to continue...', 'twitchpress' ); ?></p>
            
            <?php 
            if( !get_option( 'bugnet_version' ) ) { 
                ?> 
                
                <h2><?php _e( 'Install BugNet', 'twitchpress' ); ?></h2>
                <p><?php _e( 'You can install tables for the built-in debugging and troubleshooting solution now or skip this and install it later if needed. This will also activate BugNet which is not recommended on live sites.', 'twitchpress' ); ?></p>                
                
                <ul>
                    <li><?php echo $wpdb->prefix; ?>_bugnet_issues</li>
                    <li><?php echo $wpdb->prefix; ?>_bugnet_issues_meta</li>
                    <li><?php echo $wpdb->prefix; ?>_bugnet_reports</li>
                    <li><?php echo $wpdb->prefix; ?>_bugnet_reports_meta</li>
                    <li><?php echo $wpdb->prefix; ?>_bugnet_wp_caches</li>
                </ul>                
                
                <table class="form-table">  
                     <tr>
                        <th scope="row"><label for="twitchpress_install_bugnet"><?php _e( 'Install BugNet Tables', 'twitchpress' ); ?></label></th>
                        <td>
                            <input type="checkbox" id="twitchpress_install_bugnet" name="twitchpress_install_bugnet" class="input-checkbox" value="1" />
                            <label for="twitchpress_install_bugnet"><?php _e( '', 'twitchpress' ); ?></label>
                        </td>
                    </tr>                
                </table>
                
                <?php 
            } else {
                ?> 
                
                <h2><?php _e( 'BugNet Ready', 'twitchpress' ); ?></h2>
                <p><?php _e( 'You have already installed the following tables for BugNet. No further changes will be made to the database.', 'twitchpress' ); ?></p>                
                
                <ul>
                    <li><?php echo $wpdb->prefix; ?>_bugnet_issues</li>
                    <li><?php echo $wpdb->prefix; ?>_bugnet_issues_meta</li>
                    <li><?php echo $wpdb->prefix; ?>_bugnet_reports</li>
                    <li><?php echo $wpdb->prefix; ?>_bugnet_reports_meta</li>
                    <li><?php echo $wpdb->prefix; ?>_bugnet_wp_caches</li>
                </ul>                

                <?php                 
            }
            ?>
    
            <p class="twitchpress-setup-actions step">
                <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Continue', 'twitchpress' ); ?>" name="save_step" />
                <?php wp_nonce_field( 'twitchpress-setup' ); ?>
            </p>
        </form>
        <?php
    }

    /**
     * Save shipping and tax options.
     * 
     * @version 1.0
     */
    public function twitchpress_setup_database_save() {           
        check_admin_referer( 'twitchpress-setup' );

        // Install/update core database tables...
        include_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'includes/classes/class.twitchpress-tables-installation.php' );
        $tables = new TwitchPress_Install_Tables();
        $tables->primary_tables();   
        
        if( isset( $_POST['twitchpress_install_bugnet'] ) ) {
            require_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'includes/libraries/bugnet/class.bugnet-install.php' );
            $install = new BugNet_Install();
            $install->installation_type = 'activation';
            $result = $install->install();            
        }
                
        wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
        exit;
    }

    /**
     * Array of official and endorsed extensions.
     * 
     * @return array
     * 
     * @version 1.3
     */
    protected function get_wizard_extensions() {       
        $gateways = array(   
            'twitchpress-um-extension' => array(
                'name'        => __( 'TwitchPress UM Extension (Bridge)', 'twitchpress' ),
                'description' => __( 'Requires the Ultimate Member plugin.', 'twitchpress' ),
                'repo-slug'   => 'twitchpress-um-extension',
                'source'        => 'remote'
            ),   
            
            /*      This code contains example options for each item and should be kept for reference...
            'stripe' => array(
                'name'        => __( 'Channel Solution for Twitch', 'twitchpress' ),
                'description' => __( 'A modern and robust wa.', 'twitchpress' ),
                'repo-slug'   => 'twitchpress',
                'source'        => 'remote',
            ),            
            'paypal' => array(
                'name'        => __( 'PayPal Standard', 'twitchpress' ),
                'description' => __( 'Accept payments via PayPal using account balance or credit card.', 'twitchpress' ),
                'settings'    => array(
                    'email' => array(
                        'label'       => __( 'PayPal email address', 'twitchpress' ),
                        'type'        => 'email',
                        'value'       => get_option( 'admin_email' ),
                        'placeholder' => __( 'PayPal email address', 'twitchpress' ),
                    ),
                ),
                'source'        => 'local'
            ),
            'cheque' => array(
                'name'        => _x( 'Check Payments', 'Check payment method', 'twitchpress' ),
                'description' => __( 'A simple offline gateway that lets you accept a check as method of payment.', 'twitchpress' ),
                'source'        => 'local'
            ),
            'bacs' => array(
                'name'        => __( 'Bank Transfer (BACS) Payments', 'twitchpress' ),
                'description' => __( 'A simple offline gateway that lets you accept BACS payment.', 'twitchpress' ),
                'source'        => 'local'
            ) */
        );

        return $gateways;
    }

    /**
     * Extensions selection step.
     * 
     * Both WordPress.org plugins and packaged plugins are offered.
     * 
     * @version 1.2
     */
    public function twitchpress_setup_extensions() {
        $gateways = $this->get_wizard_extensions();?>
        
        <h1><?php _e( 'Extensions', 'twitchpress' ); ?></h1>   
        <p><?php _e( 'Extensions are actually other WordPress plugins safely downloaded from 
        wordpress.org website and their primary purpose is to act as a bridge. Integrating this
        plugin with another.', 'twitchpress' ); ?></p>
         
        <form method="post" class="twitchpress-wizard-plugin-extensions-form">
            
            <ul class="twitchpress-wizard-plugin-extensions">
                <?php foreach ( $gateways as $gateway_id => $gateway ) : ?>
                    <li class="twitchpress-wizard-extension twitchpress-wizard-extension-<?php echo esc_attr( $gateway_id ); ?>">
                        <div class="twitchpress-wizard-extension-enable">
                            <input type="checkbox" name="twitchpress-wizard-extension-<?php echo esc_attr( $gateway_id ); ?>-enabled" class="input-checkbox" value="yes" />
                            <label>
                                <?php echo esc_html( $gateway['name'] ); ?>
                            </label>
                        </div>
                        <div class="twitchpress-wizard-extension-description">
                            <?php echo wp_kses_post( wpautop( $gateway['description'] ) ); ?>
                        </div>
                        <?php if ( ! empty( $gateway['settings'] ) ) : ?>
                            <table class="form-table twitchpress-wizard-extension-settings">
                                <?php foreach ( $gateway['settings'] as $setting_id => $setting ) : ?>
                                    <tr>
                                        <th scope="row"><label for="<?php echo esc_attr( $gateway_id ); ?>_<?php echo esc_attr( $setting_id ); ?>"><?php echo esc_html( $setting['label'] ); ?>:</label></th>
                                        <td>
                                            <input
                                                type="<?php echo esc_attr( $setting['type'] ); ?>"
                                                id="<?php echo esc_attr( $gateway_id ); ?>_<?php echo esc_attr( $setting_id ); ?>"
                                                name="<?php echo esc_attr( $gateway_id ); ?>_<?php echo esc_attr( $setting_id ); ?>"
                                                class="input-text"
                                                value="<?php echo esc_attr( $setting['value'] ); ?>"
                                                placeholder="<?php echo esc_attr( $setting['placeholder'] ); ?>"
                                                />
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
           
            <p class="twitchpress-setup-actions step">
                <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Continue', 'twitchpress' ); ?>" name="save_step" />
                <a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button button-large button-next"><?php _e( 'Skip this step', 'twitchpress' ); ?></a>
                <?php wp_nonce_field( 'twitchpress-setup' ); ?>
            </p>
        </form>
        <?php
    }

    /**
     * Extensions installation and activation.
     * 
     * Both mini-extensions (single files stored in wp-content) and plugin-extensions
     * (plugins downloaded from wordpress.org) are handled by this step.
     */
    public function twitchpress_setup_extensions_save() {                  
        check_admin_referer( 'twitchpress-setup' );

        $extensions = $this->get_wizard_extensions();

        foreach ( $extensions as $extension_id => $ext_array ) {
            // If repo-slug is defined, download and install plugin from .org.
            if ( ! empty( $ext_array['repo-slug'] ) && ! empty( $_POST[ 'twitchpress-wizard-extension-' . $extension_id . '-enabled' ] ) ) {
                wp_schedule_single_event( time() + 10, 'twitchpress_plugin_background_installer', array( $extension_id, $ext_array ) );
            }

            $settings_key        = 'twitchpress_' . $extension_id . '_settings';
            $settings            = array_filter( (array) get_option( $settings_key, array() ) );
            $settings['enabled'] = ! empty( $_POST[ 'twitchpress-wizard-extension-' . $extension_id . '-enabled' ] ) ? 'yes' : 'no';

            if ( ! empty( $ext_array['settings'] ) ) {
                foreach ( $ext_array['settings'] as $setting_id => $setting ) {
                    $settings[ $setting_id ] = twitchpress_clean( $_POST[ $extension_id . '_' . $setting_id ] );
                }
            }                    

            update_option( $settings_key, $settings );
        }

        wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
        exit;
    }
    
    /**
     * Array of official and endorsed extensions.
     * 
     * @return array
     * 
     * @version 1.3
     */
    protected function get_wizard_services() {       
        $gateways = array(
            'twitchpress-raid-manager' => array(
                'name'        => __( 'Raid Manager', 'twitchpress' ),
                'description' => __( 'Score credits by raiding other TwitchPress related channels..then be raided later!', 'twitchpress' ),
                'repo-slug'   => 'twitchpress-raid-manager',
                'source'        => 'remote'
            ),    
            'twitchpress-highlighted-streamer' => array(
                'name'        => __( 'Highlighted Streamer', 'twitchpress' ),
                'description' => __( 'Appear on the home-page on many other websites when your channel goes live!', 'twitchpress' ),
                'repo-slug'   => 'twitchpress-highlighted-streamer',
                'source'        => 'remote'
            ),  
            'twitchpress-ad-network' => array(
                'name'        => __( 'Ad Network', 'twitchpress' ),
                'description' => __( 'Display your ad on other sites and sponsor others for display on your own.', 'twitchpress' ),
                'repo-slug'   => 'twitchpress-ad-network',
                'source'        => 'remote'
            ),              
            'twitchpress-stream-summary-generator' => array(
                'name'        => __( 'Stream Summary Generator', 'twitchpress' ),
                'description' => __( 'Generate WP posts based on your most recent live stream - including viewer activity.', 'twitchpress' ),
                'repo-slug'   => 'twitchpress-stream-summary-generator',
                'source'        => 'remote'
            ),  
            'twitchpress-ego-service' => array(
                'name'        => __( 'EGO Features', 'twitchpress' ),
                'description' => __( 'Tap into Evolved Gamers Online and benefit from an interested gaming community.', 'twitchpress' ),
                'repo-slug'   => 'twitchpress-ego-service',
                'source'        => 'remote'
            ),  
            /*
            'stripe' => array(
                'name'        => __( 'Channel Solution for Twitch', 'twitchpress' ),
                'description' => __( 'A modern and robust wa.', 'twitchpress' ),
                'repo-slug'   => 'twitchpress',
                'source'        => 'remote',
            ),            
            'paypal' => array(
                'name'        => __( 'PayPal Standard', 'twitchpress' ),
                'description' => __( 'Accept payments via PayPal using account balance or credit card.', 'twitchpress' ),
                'settings'    => array(
                    'email' => array(
                        'label'       => __( 'PayPal email address', 'twitchpress' ),
                        'type'        => 'email',
                        'value'       => get_option( 'admin_email' ),
                        'placeholder' => __( 'PayPal email address', 'twitchpress' ),
                    ),
                ),
                'source'        => 'local'
            ),
            'cheque' => array(
                'name'        => _x( 'Check Payments', 'Check payment method', 'twitchpress' ),
                'description' => __( 'A simple offline gateway that lets you accept a check as method of payment.', 'twitchpress' ),
                'source'        => 'local'
            ),
            'bacs' => array(
                'name'        => __( 'Bank Transfer (BACS) Payments', 'twitchpress' ),
                'description' => __( 'A simple offline gateway that lets you accept BACS payment.', 'twitchpress' ),
                'source'        => 'local'
            ) */
        );

        return $gateways;
    }

    /**
     * Extensions selection step.
     * 
     * Both WordPress.org plugins and packaged plugins are offered.
     */
    public function twitchpress_setup_services() {
        $gateways = $this->get_wizard_services();?>
        
        <h1><?php _e( 'Services', 'twitchpress' ); ?></h1>   
        <p><?php _e( 'Activate premium services that are supported by cloud services. Currently limited to testers.', 'twitchpress' ); ?></p>
         
        <form method="post" class="twitchpress-wizard-plugin-extensions-form">
            
            <ul class="twitchpress-wizard-plugin-extensions">
                <?php foreach ( $gateways as $gateway_id => $gateway ) : ?>
                    <li class="twitchpress-wizard-extension twitchpress-wizard-extension-<?php echo esc_attr( $gateway_id ); ?>">
                        <div class="twitchpress-wizard-extension-enable">
                            <input type="checkbox" name="twitchpress-wizard-extension-<?php echo esc_attr( $gateway_id ); ?>-enabled" class="input-checkbox" value="yes" />
                            <label>
                                <?php echo esc_html( $gateway['name'] ); ?>
                            </label>
                        </div>
                        <div class="twitchpress-wizard-extension-description">
                            <?php echo wp_kses_post( wpautop( $gateway['description'] ) ); ?>
                        </div>
                        <?php if ( ! empty( $gateway['settings'] ) ) : ?>
                            <table class="form-table twitchpress-wizard-extension-settings">
                                <?php foreach ( $gateway['settings'] as $setting_id => $setting ) : ?>
                                    <tr>
                                        <th scope="row"><label for="<?php echo esc_attr( $gateway_id ); ?>_<?php echo esc_attr( $setting_id ); ?>"><?php echo esc_html( $setting['label'] ); ?>:</label></th>
                                        <td>
                                            <input
                                                type="<?php echo esc_attr( $setting['type'] ); ?>"
                                                id="<?php echo esc_attr( $gateway_id ); ?>_<?php echo esc_attr( $setting_id ); ?>"
                                                name="<?php echo esc_attr( $gateway_id ); ?>_<?php echo esc_attr( $setting_id ); ?>"
                                                class="input-text"
                                                value="<?php echo esc_attr( $setting['value'] ); ?>"
                                                placeholder="<?php echo esc_attr( $setting['placeholder'] ); ?>"
                                                />
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
           
            <p class="twitchpress-setup-actions step">
                <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Continue', 'twitchpress' ); ?>" name="save_step" />
                <a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button button-large button-next"><?php _e( 'Skip this step', 'twitchpress' ); ?></a>
                <?php wp_nonce_field( 'twitchpress-setup' ); ?>
            </p>
        </form>

        <?php
    }

    /**
     * Extensions installation and activation.
     * 
     * Both mini-extensions (single files stored in wp-content) and plugin-extensions
     * (plugins downloaded from wordpress.org) are handled by this step.
     */
    public function twitchpress_setup_services_save() {                  
        check_admin_referer( 'twitchpress-setup' );

        $services = $this->get_wizard_services();

        $services = apply_filters( 'twitchpress_wizard_services', $services );
        
        foreach ( $services as $extension_id => $ext_array ) {
            // If repo-slug is defined, download and install plugin from .org.
            if ( ! empty( $ext_array['repo-slug'] ) && ! empty( $_POST[ 'twitchpress-wizard-service-' . $extension_id . '-enabled' ] ) ) {
                wp_schedule_single_event( time() + 10, 'twitchpress_plugin_background_installer', array( $extension_id, $ext_array ) );
            }

            $settings_key        = 'twitchpress_' . $extension_id . '_settings';
            $settings            = array_filter( (array) get_option( $settings_key, array() ) );
            $settings['enabled'] = ! empty( $_POST[ 'twitchpress-wizard-service-' . $extension_id . '-enabled' ] ) ? 'yes' : 'no';

            if ( ! empty( $ext_array['settings'] ) ) {
                foreach ( $ext_array['settings'] as $setting_id => $setting ) {
                    $settings[ $setting_id ] = twitchpress_clean( $_POST[ $extension_id . '_' . $setting_id ] );
                }
            }                    

            update_option( $settings_key, $settings );
        }

        wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
        exit;
    }

    /**
     * Options and Main Account oAuth
     * 
     * When user submits this form they are redirected to authorize the main 
     * account and then return to the wizard. 
     * 
     * @version 1.0
     */
    public function twitchpress_setup_improvement() { ?>
        <h1><?php _e( 'Options', 'twitchpress' ); ?></h1>

        <form method="post">

            <h3><?php _e( 'Training', 'twitchpress' ); ?></h3>
            <p><?php _e( 'The following options are for new users of the plugin and are the quickest way to learn how to get the most out of it.', 'twitchpress' ); ?></p>
                    
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="twitchpress_install_samples"><?php _e( 'Do you want to install example pages?', 'twitchpress' ); ?></label></th>
                    <td>
                        <input type="checkbox" id="twitchpress_install_samples" name="twitchpress_install_samples" class="input-checkbox" value="yes" />
                        <label for="twitchpress_install_samples"><?php _e( 'Yes, install some examples.', 'twitchpress' ); ?></label>
                    </td>
                </tr>                
            </table>
 
            <h3><?php _e( 'Systems', 'twitchpress' ); ?></h3>
            <p><?php _e( 'Only activate (by checking the boxes) the systems you require because some system requirements 
            will lead to increased error logging if the required data or Twitch.tv access is not provided.', 'twitchpress' ); ?></p>
                    
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="twitchpress_subscription_data"><?php _e( 'Does your Twitch channel offer subscription plans?', 'twitchpress' ); ?></label></th>
                    <td>
                        <input type="checkbox" id="twitchpress_subscription_data" <?php checked( get_option( 'twitchpress_twitchsubscribers_switch', 'no' ), 'yes' ); ?> name="twitchpress_subscription_data" class="input-checkbox" value="yes" />
                        <label for="twitchpress_subscription_data"><?php _e( 'Yes, store sub plans in WP.', 'twitchpress' ); ?></label>
                    </td>
                </tr>
            </table>        
            
            <h3><?php _e( 'Improvement Program &amp; Feedback Options', 'twitchpress' ); ?></h3>
            <p><?php _e( 'Taking the time to provide constructive feedback and allowing 
            the plugin to send none-sensitive data to me can be as valuable as a donation.', 'twitchpress' ); ?></p>
                    
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="twitchpress_feedback_data"><?php _e( 'Allow none-sensitive information to be sent to Ryan Bayne?', 'twitchpress' ); ?></label></th>
                    <td>
                        <input type="checkbox" id="twitchpress_feedback_data" <?php checked( get_option( 'twitchpress_feedback_data', '' ) !== 'disabled', true ); ?> name="twitchpress_feedback_data" class="input-checkbox" value="1" />
                        <label for="twitchpress_feedback_data"><?php _e( 'Yes, send configuration and logs only.', 'twitchpress' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="twitchpress_feedback_prompt"><?php _e( 'Allow the plugin to prompt you for feedback in the future?', 'twitchpress' ); ?></label></th>
                    <td>
                        <input type="checkbox" <?php checked( get_option( 'twitchpress_feedback_prompt', 'no' ), 'yes' ); ?> id="twitchpress_feedback_prompt" name="twitchpress_feedback_prompt" class="input-checkbox" value="1" />
                        <label for="twitchpress_feedback_prompt"><?php _e( 'Yes, prompt me in a couple of months.', 'twitchpress' ); ?></label>
                    </td>
                </tr>
            </table>
            
            <h3><?php _e( 'Intended Use', 'twitchpress' ); ?></h3>
            <p><?php _e( 'Please indicate your planned uses for TwitchPress as this will
            help when developing new features and addressing our users as a community.', 'twitchpress' ); ?></p>
             
            <table class="form-table">
                <tr>
                    <td>
                        <input type="checkbox" id="twitchpress_purpose_casualstreamer" <?php checked( get_option( 'twitchpress_purpose_casualstreamer', '' ) !== 'disabled', true ); ?> name="twitchpress_purpose_casualstreamer" class="input-checkbox" value="1" />
                        <label for="twitchpress_purpose_casualstreamer"><?php _e( 'New Streamer', 'twitchpress' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" <?php checked( get_option( 'twitchpress_purpose_partneredstreamer', 'no' ), 'yes' ); ?> id="twitchpress_purpose_partneredstreamer" name="twitchpress_purpose_partneredstreamer" class="input-checkbox" value="1" />
                        <label for="twitchpress_purpose_partneredstreamer"><?php _e( 'Partnered Streamer', 'twitchpress' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" <?php checked( get_option( 'twitchpress_purpose_streamteam', 'no' ), 'yes' ); ?> id="twitchpress_purpose_streamteam" name="twitchpress_purpose_streamteam" class="input-checkbox" value="1" />
                        <label for="twitchpress_purpose_streamteam"><?php _e( 'Stream Team', 'twitchpress' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" <?php checked( get_option( 'twitchpress_purpose_publicservice', 'no' ), 'yes' ); ?> id="twitchpress_purpose_publicservice" name="twitchpress_purpose_publicservice" class="input-checkbox" value="1" />
                        <label for="twitchpress_purpose_publicservice"><?php _e( 'Public Service', 'twitchpress' ); ?></label>
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" <?php checked( get_option( 'twitchpress_purpose_business', 'no' ), 'yes' ); ?> id="twitchpress_purpose_business" name="twitchpress_purpose_business" class="input-checkbox" value="1" />
                        <label for="twitchpress_purpose_business"><?php _e( 'Business', 'twitchpress' ); ?></label>
                    </td>
                </tr>
                
                
                <tr>
                    <td>
                        <input type="checkbox" <?php checked( get_option( 'twitchpress_purpose_other', 'no' ), 'yes' ); ?> id="twitchpress_purpose_other" name="twitchpress_purpose_other" class="input-checkbox" value="1" />
                        <label for="twitchpress_purpose_other"><?php _e( 'Other', 'twitchpress' ); ?></label>
                    </td>
                </tr>                                                                                   

             </table>
                                         
            <h3><?php _e( 'Please Read: Main Account Authorisation', 'twitchpress' ); ?></h3>
            <p><?php _e( 'When you click on the Continue button you will be sent to Twitch.tv to
            authorize your Twitch account as the main channel for this website. This
            step is important for your TwitchPress system to run properly. If it fails
            you must seek support.', 'twitchpress' ); ?></p>
                        
            <p class="twitchpress-setup-actions step">
                <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Continue', 'twitchpress' ); ?>" name="save_step" />
                <a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button button-large button-next"><?php _e( 'Skip this step', 'twitchpress' ); ?></a>
                <?php wp_nonce_field( 'twitchpress-setup' ); ?>
            </p>
            
        </form>
        <?php
    }

    /**
     * Save options...
     * 
     * @version 1.0
     */
    public function twitchpress_setup_improvement_save() { 
 
        check_admin_referer( 'twitchpress-setup' );
        
        if( isset( $_POST['twitchpress_install_samples'] ) && $_POST['twitchpress_install_samples'] == 'yes' ) {
            require_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'includes/admin/class.twitchpress-admin-install-examples.php' );
            TwitchPress_Install_Examples::everything();       
        }  
              
        if( isset( $_POST['twitchpress_subscription_data'] ) && $_POST['twitchpress_subscription_data'] == 'yes' ) {
            update_option( 'twitchpress_twitchsubscribers_switch', 'yes' );    
        } else {
            update_option( 'twitchpress_twitchsubscribers_switch', 'no' );
        }
        
        if( isset( $_POST['twitchpress_feedback_data'] ) ) {
            update_option( 'twitchpress_feedback_data', 1 );
        } else {
            delete_option( 'twitchpress_feedback_data' );
        }
        
        if( isset( $_POST['twitchpress_feedback_prompt'] ) ) {
            update_option( 'twitchpress_feedback_prompt', 1 );
        } else {
            delete_option( 'twitchpress_feedback_prompt' );
        }
        
        // Send user to authorise their main Twitch channel.             
        $state = array( 'redirectto' => admin_url( 'index.php?page=twitchpress-setup&step=next_steps' ),
                        'userrole'   => 'administrator',
                        'outputtype' => 'admin',
                        'reason'     => 'mainchannelsetup',
                        'function'   => __FUNCTION__
        );

        // Generate the oAuth URL and forward the user to it. 
        wp_redirect( twitchpress_generate_authorization_url( twitchpress_get_global_accepted_scopes(), $state ) );
        exit; 
    }
    
    public function twitchpress_setup_ready_actions() {
        // Stop showing notice inviting user to start the setup wizard. 
        TwitchPress_Admin_Notices::remove_notice( 'install' );   
    }    
    
    /**
     * Final step.
     * 
     * @version 2.0
     */
    public function twitchpress_setup_ready() {
        
        $this->twitchpress_setup_ready_actions();?>

        <div class="twitchpress-setup-next-steps">
            <div class="twitchpress-setup-next-steps-first">
                <h2><?php _e( 'TwitchPress System Ready!', 'twitchpress' ); ?></h2>
                <ul>
                    <li class="setup-thing"><a class="button button-primary button-large" href="<?php echo esc_url( admin_url( 'admin.php?page=twitchpress' ) ); ?>"><?php _e( 'Go to Settings', 'twitchpress' ); ?></a></li>
                </ul>                                                                                                 
            </div>
            <div class="twitchpress-setup-next-steps-last">
            
                <h2><?php _e( 'Need some support?', 'twitchpress' ); ?></h2>
                                                           
                <a href="<?php echo TWITCHPRESS_GITHUB; ?>"><?php _e( 'GitHub', 'twitchpress' ); ?></a>
                <a href="<?php echo TWITCHPRESS_DISCORD; ?>"><?php _e( 'Discord', 'twitchpress' ); ?></a>
                <a href="<?php echo TWITCHPRESS_TWITTER; ?>"><?php _e( 'Twitter', 'twitchpress' ); ?></a>
                <a href="<?php echo TWITCHPRESS_HOME; ?>"><?php _e( 'Blog', 'twitchpress' ); ?></a>
                <a href="<?php echo TWITCHPRESS_AUTHOR_DONATE; ?>"><?php _e( 'Patreon', 'twitchpress' ); ?></a>
     
            </div>
        </div>
        <?php
    }
}

endif;

// This file is conditionally included...
new TwitchPress_Admin_Setup_Wizard();
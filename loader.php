<?php
/**
 * TwitchPress main class - includes, debugging, error output, object registry, constants.
 * 
 * @author   Ryan Bayne
 * @package  TwitchPress
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main TwitchPress Class.
 *
 * @class TwitchPress
 */
final class WordPressTwitchPress {
    
    /**
     * Minimum WP version.
     *
     * @var string
     */
    public $min_wp_version = '5.4';
    
    /**
     * The single instance of the class.
     *
     * @var TwitchPress
     * @since 2.1
     */
    protected static $_instance = null;

    /**
     * Session instance.
     *
     * @var TwitchPress_Session
     */
    public $session = null; 

    /**
    * Quick and dirty way to debug by adding values that are dumped in footer.
    * 
    * @var mixed
    */
    public $dump = array();
    
    public $subsman        = null;
    public $public_notices = null;
    public $blocks_core    = null;
    public $login          = null; 
    public $login_sc       = null;  
    public $gate           = null;
    public $admin_notices  = null;
    public $available_languages = null;
    public $bugnet = null;
    
    /**
     * Main TwitchPress Instance.
     *
     * Ensures only one instance of TwitchPress is loaded or can be loaded.
     *
     * @since 1.0
     * @static
     * @see WordPressSeed()
     * @return TwitchPress - Main instance.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }                    
        return self::$_instance;
    }

    /**
     * Cloning TwitchPress is forbidden.
     * @since 1.0
     */
    public function __clone() {
        _doing_it_wrong( __FUNCTION__, __( 'You\'re not allowed to do that!', 'twitchpress' ), '1.0' );
    }

    /**
     * Unserializing instances of this class is forbidden.
     * @since 1.0
     */
    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, __( 'You\'re not allowed to do that!', 'twitchpress' ), '1.0' );
    }
    
    /**
     * TwitchPress Constructor.
     */
    public function __construct() {      
        $this->define_hard_constants();        
        $this->includes();                     
        $this->init_hooks();                      
        
        $this->available_languages = array(
            //'en_US' => 'English (US)',
            //'fr_FR' => 'Français',
            //'de_DE' => 'Deutsch',
        );
                    
        do_action( 'twitchpress_loaded' );
    }

    /**
     * Define TwitchPress in-line constants...
     * 
     * @version 2.0      
     */
    private function define_hard_constants() {
        
        $upload_dir = wp_upload_dir();
    
        if ( ! defined( 'TWITCHPRESS_API_NAME' ) ) { define( 'TWITCHPRESS_API_NAME', 'helix' ); }
        if ( ! defined( 'TWITCHPRESS_API_VERSION' ) ){ define( 'TWITCHPRESS_API_VERSION', '6' );}        

        // Main (package) constants.
        if ( ! defined( 'TWITCHPRESS_MIN_WP_VERSION' ) ) {    define( 'TWITCHPRESS_MIN_WP_VERSION', $this->min_wp_version ); }
        if ( ! defined( 'TWITCHPRESS_UPLOADS_DIR' ) ) {       define( 'TWITCHPRESS_UPLOADS_DIR', $upload_dir['basedir'] . 'twitchpress-uploads/' ); }
        if ( ! defined( 'TWITCHPRESS_LOG_DIR' ) ) {           define( 'TWITCHPRESS_LOG_DIR', TWITCHPRESS_PLUGIN_DIR_PATH . 'twitchpress-logs/' ); }
        if ( ! defined( 'TWITCHPRESS_DEV_MODE' ) ) {          define( 'TWITCHPRESS_DEV_MODE', false ); }
        if ( ! defined( 'TWITCHPRESS_WORDPRESSORG_SLUG' ) ) { define( 'TWITCHPRESS_WORDPRESSORG_SLUG', false ); }
                    
        // Support (project) constants.
        if ( ! defined( 'TWITCHPRESS_HOME' ) ) {              define( 'TWITCHPRESS_HOME', 'https://twitchpress.wordpress.com' ); }
        if ( ! defined( 'TWITCHPRESS_FORUM' ) ) {             define( 'TWITCHPRESS_FORUM', 'https://wordpress.org/support/plugin/twitchpress' ); }
        if ( ! defined( 'TWITCHPRESS_TWITTER' ) ) {           define( 'TWITCHPRESS_TWITTER', 'https://twitter.com/twitchpress' ); }
        if ( ! defined( 'TWITCHPRESS_DONATE' ) ) {            define( 'TWITCHPRESS_DONATE', 'https://www.patreon.com/twitchpress' ); }
        if ( ! defined( 'TWITCHPRESS_GITHUB' ) ) {            define( 'TWITCHPRESS_GITHUB', 'https://github.com/ryanbayne/twitchpress' ); }
        if ( ! defined( 'TWITCHPRESS_DISCORD' ) ) {           define( 'TWITCHPRESS_DISCORD', 'https://discord.gg/ScrhXPE' ); }
       
        // Author (social) constants - can act as default when support constants are false.                                                                                                              
        if ( ! defined( 'TWITCHPRESS_AUTHOR_HOME' ) ) {       define( 'TWITCHPRESS_AUTHOR_HOME', 'https://ryanbayne.wordpress.com' ); }
        if ( ! defined( 'TWITCHPRESS_AUTHOR_TWITTER' ) ) {    define( 'TWITCHPRESS_AUTHOR_TWITTER', 'http://www.twitter.com/ryan_r_bayne' ); }
        if ( ! defined( 'TWITCHPRESS_AUTHOR_FACEBOOK' ) ) {   define( 'TWITCHPRESS_AUTHOR_FACEBOOK', 'https://www.facebook.com/ryanrbayne' ); }
        if ( ! defined( 'TWITCHPRESS_AUTHOR_DONATE' ) ) {     define( 'TWITCHPRESS_AUTHOR_DONATE', 'https://www.patreon.com/twitchpress' ); }
        if ( ! defined( 'TWITCHPRESS_AUTHOR_GITHUB' ) ) {     define( 'TWITCHPRESS_AUTHOR_GITHUB', 'https://github.com/RyanBayne' ); }
        if ( ! defined( 'TWITCHPRESS_AUTHOR_LINKEDIN' ) ) {   define( 'TWITCHPRESS_AUTHOR_LINKEDIN', 'https://www.linkedin.com/in/ryanrbayne/' ); }
        if ( ! defined( 'TWITCHPRESS_AUTHOR_DISCORD' ) ) {    define( 'TWITCHPRESS_AUTHOR_DISCORD', 'https://discord.gg/ScrhXPE' ); }

        // Twitch API
        if( ! defined( "TWITCHPRESS_KEY_NAME" ) ){               define( "TWITCHPRESS_KEY_NAME", 'name' );}
        if( ! defined( "TWITCHPRESS_DEFAULT_TIMEOUT" ) ){        define( "TWITCHPRESS_DEFAULT_TIMEOUT", 5 );}
        if( ! defined( "TWITCHPRESS_DEFAULT_RETURN_TIMEOUT" ) ){ define( "TWITCHPRESS_DEFAULT_RETURN_TIMEOUT", 20 );}
        if( ! defined( "TWITCHPRESS_TOKEN_SEND_METHOD" ) ){      define( "TWITCHPRESS_TOKEN_SEND_METHOD", 'HEADER' );}
        if( ! defined( "TWITCHPRESS_RETRY_CALL_LIMIT" ) ){       define( "TWITCHPRESS_RETRY_CALL_LIMIT", 5 );}
        if( ! defined( "TWITCHPRESS_CERT_PATH" ) ){              define( "TWITCHPRESS_CERT_PATH", '' );}
        if( ! defined( "TWITCHPRESS_CALL_LIMIT_DEFAULT" ) ){     define( "TWITCHPRESS_CALL_LIMIT_DEFAULT", '15' );}
        if( ! defined( "TWITCHPRESS_CALL_LIMIT_DOUBLE" ) ){      define( "TWITCHPRESS_CALL_LIMIT_DOUBLE", '30' );}
        if( ! defined( "TWITCHPRESS_CALL_LIMIT_MAX" ) ){         define( "TWITCHPRESS_CALL_LIMIT_MAX", '60' );}
        if( ! defined( "TWITCHPRESS_CALL_LIMIT_SETTING" ) ){     define( "TWITCHPRESS_CALL_LIMIT_SETTING", TWITCHPRESS_CALL_LIMIT_MAX );}     
    
        // Sample content defaults
        if( ! defined( "TWITCHPRESS_STREAM_TEAM" ) ){ define( "TWITCHPRESS_STREAM_TEAM", 'sutv' );}     
              
        // Library Integration
        if ( ! defined( 'BUGNET_LOG_DIR' ) ) { define( 'BUGNET_LOG_DIR', TWITCHPRESS_LOG_DIR ); }  
    }
    
    /**
    * Define constants that require the WP init for accessing core functions
    * and WP globals.
    * 
    * @version 1.0
    */
    private function define_wp_reliant_constants() {    
        if( !defined( "TWITCHPRESS_CURRENTUSERID" ) ){ define( "TWITCHPRESS_CURRENTUSERID", get_current_user_id() );}
        if( !defined( 'TWITCHPRESS_BETA' ) ) { define( 'TWITCHPRESS_BETA', get_option( 'twitchpress_beta_testing', 0 ) ); }    
    }
    
    /**
     * Include required core files.
     * 
     * @version 3.0
     */
    public function includes() {
                                         
        do_action( 'before_twitchpress_includes' );
               
        // SPL autoloader class...
        //require_once( 'includes/classes/class.twitchpress-autoloader.php' );

        require_once( plugin_basename( 'functions.php' ) ); 
        require_once( plugin_basename( 'hooks.php' ) ); 
        
        // Debugging and logging...
        require_once( plugin_basename( 'includes/libraries/bugnet/class.bugnet.php' ) ); 
        require_once( plugin_basename( 'includes/classes/class.twitchpress-api-logging.php' ) );
                        
        // Classes using TwitchPress_Object_Registry() to init for global access...
        require_once( plugin_basename( 'includes/classes/class.twitchpress-set-app.php' ) );
        require_once( plugin_basename( 'includes/classes/class.twitchpress-set-user-auth.php' ) );
        require_once( plugin_basename( 'includes/classes/class.twitchpress-set-main-channel-auth.php' ) );
        //require_once( plugin_basename( 'includes/classes/class.twitchpress-set-bot-channel-auth.php' ) );
                         
        // More classes...
        require_once( plugin_basename( 'systems/giveaways/class.twitchpress-giveaways.php' ) );        
        require_once( 'includes/admin/class.twitchpress-admin-notices.php' );        
        require_once( 'includes/classes/class.twitchpress-current-user.php' );
        require_once( 'includes/classes/class.twitchpress-posts-gate.php' );
        require_once( 'includes/blocks/class.twitchpress-blocks.php' ); 
        require_once( 'includes/libraries/class.async-request.php' );
        require_once( 'includes/libraries/class.background-process.php' );                           
        require_once( 'includes/classes/class.twitchpress-extension-installer.php' );
        require_once( 'includes/classes/class.twitchpress-ajax.php' );
        require_once( 'includes/libraries/twitch/' . TWITCHPRESS_API_NAME . '/functions.twitch-api-statuses.php' );
        require_once( 'includes/classes/class.twitchpress-extend-wp-http-curl.php' );
        require_once( 'includes/libraries/twitch/' . TWITCHPRESS_API_NAME . '/class.twitch-api.php' );                                                                        
        require_once( 'includes/classes/class.twitchpress-login.php' ); 
        require_once( 'includes/classes/class.twitchpress-login-by-shortcode.php' );   
        require_once( 'includes/toolbars/class.twitchpress-toolbars.php' );        
        require_once( 'includes/classes/class.twitchpress-curl.php' ); 
        require_once( 'includes/classes/class.twitchpress-listener.php' );
        require_once( 'includes/classes/class.twitchpress-subscriptions.php' ); 
        require_once( 'includes/shortcodes/shortcodes-main.php' );
        require_once( plugin_basename( 'requests.php' ) );
        require_once( 'includes/classes/class.twitchpress-listener-main-account-oauth.php' );
        require_once( plugin_basename( 'includes/classes/class.twitchpress-public-preset-notices.php' ) ); 
        require_once( 'includes/classes/class.twitchpress-custom-login-notices.php' );
        require_once( 'includes/posts/class.twitchpress-page-template-splitscreen.php' );
        
        // Include main API (all-api) class...
        include_once( 'includes/libraries/allapi/class.all-api.php' );

        // Discord Integration
        include_once( 'includes/libraries/allapi/discord/class.api-discord.php' );  
        include_once( 'includes/libraries/allapi/discord/class.api-discord-listener.php' );
        
        // Streamlabs Integration
        include_once( 'includes/libraries/allapi/streamlabs/functions.api-streamlabs.php' );   
        include_once( 'includes/libraries/allapi/streamlabs/class.api-streamlabs.php' );
        include_once( 'includes/libraries/allapi/streamlabs/class.api-streamlabs-listener.php' );
    
        // YouTube Integration
        include_once( 'includes/libraries/allapi/youtube/functions.api-youtube.php' );   
        
        // Administration-only files...     
        include_once( 'includes/admin/class.twitchpress-admin.php' );
        
        // Frontend only files...
        include_once( plugin_basename( 'includes/classes/class.twitchpress-frontend-scripts.php' ) );  
        include_once( plugin_basename( 'includes/functions/functions.twitchpress-frontend-notices.php' ) );                
 
        // Load Core Objects
        $this->load_core_objects();
        
        // Load Public Objects
        $this->load_public_objects();   

        do_action( 'after_twitchpress_includes' );              
    }
    
    /**
    * Load class objects required by this core plugin for any request (front or abck) 
    * or at all times by extensions. 
    * 
    * @version 2.0
    */
    private function load_core_objects() {
        // Objects not required...
        TwitchPress_Current_User_Setter::init(); /*adds values to $current_user*/
                          
        // Create objects core objects...
        if( class_exists( 'BugNet' ) ){ $this->bugnet = new BugNet(); }    
        $this->subsman        = new TwitchPress_Twitch_Subscription_Management();
        $this->public_notices = new TwitchPress_Public_PreSet_Notices();
        $this->blocks_core    = new TwitchPress_Blocks_Core();
        $this->login          = new TwitchPress_Login(); 
        $this->login_sc       = new TwitchPress_Login_by_Shortcode();  
        $this->gate           = new TwitchPress_Posts_Gate();
        $this->admin_notices  = new TwitchPress_Admin_Notices();

        # Hint: Go to function init_system() after adding new line here...        
    }
    
    /**
    * Load objects only required for a front-end request.
    * 
    * @version 1.0
    */
    private function load_public_objects() {
  
    }

    /**
     * Hook into actions and filters. 
     * 
     * Extensions hook into the init() before and after TwitchPress full init.
     * 
     * @version 1.0 
     */
    private function init_hooks() {        
        add_action( 'init', 'twitchpress_register_tables', 0 );       
        add_action( 'admin_init', array( $this, 'load_admin_dependencies' ) );
        add_action( 'admin_init', 'twitchpress_offer_wizard' );
        add_action( 'init', array( $this, 'init_system' ), 0 );
        add_action( 'init', array( $this, 'output_errors' ), 2 );
        add_action( 'init', array( $this, 'output_actions' ), 2 );            
        add_action( 'init', array( $this, 'output_filters' ), 2 );   
        add_filter( 'views_edit-plugins', array( $this, 'views_edit_plugins' ), 1 );
        add_action( 'wp_enqueue_scripts',    array( $this, 'enqueue_public_css' ), 10 );
        add_action( 'login_enqueue_scripts', array( $this, 'twitchpress_login_styles'), 1 );// Core login form connect button...
        add_action( 'plugins_loaded', array( $this, 'init_third_party_integration' ), 1 );// Load the 3rd party integration files...             
    }
    
    public function init_third_party_integration() {    
        if( function_exists( 'is_plugin_active' ) && is_plugin_active( 'ultimate-member/ultimate-member.php' ) ){       
            require_once( 'includes/integration/class.twitchpress-ultimate-member.php' ); 
            $this->UM = new TwitchPress_Ultimate_Member();
            $this->UM->init();
        }   
    }
        
    public function twitchpress_login_styles() {
        wp_enqueue_script('jquery');
        wp_register_style( 'twitchpress_login_extension_styles', self::plugin_url() . '/assets/css/twitchpress-login-form.css' );
        wp_enqueue_style( 'twitchpress_login_extension_styles' );
    }
            
    public function enqueue_public_css() {
        wp_register_style( 'twitchpress_shortcode_styles', self::plugin_url() . '/assets/css/twitchpress-shortcodes.css' );
        wp_enqueue_style( 'twitchpress_shortcode_styles' );
    }
            
    /**
    * Load admin only files...
    * 
    * @version 1.0
    */
    public function load_admin_dependencies() {
        include_once( 'includes/admin/class.twitchpress-admin-data-views.php' );
        include_once( 'includes/admin/class.twitchpress-admin-tools-views.php' );
        include_once( 'includes/admin/class.twitchpress-admin-deactivate.php' );              
    }

    public function views_edit_plugins( $views ) {       
        $screen = get_current_screen();
    }
        
    public function init_system() {
                   
        // Before init action...
        do_action( 'before_twitchpress_init' );    
         
        $this->define_wp_reliant_constants();
                                                       
        // Core classes that require initializing...
        if( get_option( 'bugnet_version' ) || class_exists( 'BugNet' ) ){ $this->bugnet->init(); }
        $this->subsman->init();
        $this->blocks_core->init();  
        $this->login->init();        
        $this->login_sc->init();   
        $this->gate->init();
        $this->admin_notices->init();
        
        if( isset( $this->webhooks ) ) { $this->webhooks->init(); }
        
        // Collect required scopes from extensions and establish system requirements. 
        global $system_scopes_status;
        $system_scopes_status = array();
        
        // Scopes for admin only or main account functionality that is always used. 
        $system_scopes_status['admin']['core']['required'] = array();
        
        // Scopes for admin only or main account features that may not be used.
        $system_scopes_status['admin']['core']['optional'] = array(); 
                    
        // Scopes for functionality that is always used. 
        $system_scopes_status['public']['core']['required'] = array();
        
        // Scopes for features that may not be used.
        $system_scopes_status['public']['core']['optional'] = array(); 
        
        $system_scopes_status = apply_filters( 'twitchpress_update_system_scopes_status', $system_scopes_status );  

        add_filter( 'plugin_action_links_' . TWITCHPRESS_PLUGIN_BASENAME, 'twitchpress_plugin_action_links' );
        add_filter( 'plugin_row_meta', 'twitchpress_plugin_row_meta', 10, 2 );

        // Load the various systems, includes custom post types within those systems...
        $this->load_systems();
        
        // Init action.
        do_action( 'twitchpress_init' );   
    }
    
    /**
    * Load systems, including registration of custom post types...
    * 
    * @version 2.1
    */
    public function load_systems() {
        
        // Channels - Core
        require_once( 'includes/posts/class.twitchpress-post-type-channels.php' );
        TwitchPress_Post_Type_Channels::init();

        // Giveaways System
        if( get_option( 'twitchpress_giveaways_switch' ) == 'yes' ) {
            require_once( 'includes/posts/class.twitchpress-post-type-giveaways.php' );
            TwitchPress_Post_Type_Giveaways::init();            
        }

        // Perks System
        if( get_option( 'twitchpress_perks_switch' ) == 'yes' ) {
            require_once( 'includes/posts/class.twitchpress-post-type-perks.php' );
            TwitchPress_Post_Type_Perks::init();  
        } 
        
        // Webhooks System
        if( get_option( 'twitchpress_webhooks_switch' ) == 'yes' ) {
            
            // Set the callback URL sent to Twitch.tv when subscribing to a webhook...
            $url = admin_url( 'admin-post.php?webhook=twitchpress_eventsub_notification' );
            if( !defined( 'TWITCHPRESS_WEBHOOK_CALLBACK' ) ) { define( 'TWITCHPRESS_WEBHOOK_CALLBACK', $url ); }
            unset( $url );
            
            require_once( 'includes/posts/class.twitchpress-post-type-webhooks.php' );             
            require_once( plugin_basename( 'systems/webhooks/class.twitchpress-webhooks-cache.php' ) );
            require_once( plugin_basename( 'systems/webhooks/class.twitchpress-webhooks-event-processing.php' ) );
            
            // Initiate the custom post-type...
            TwitchPress_Post_Type_Webhooks::init();
                     
            // Hook into init for single site, priority 0 = highest priority...
            add_action( 'init', 'twitchpress_integrate_wpdb_webhooksmeta', 0);

            // Hook in to switch blog to support multisite...
            add_action( 'switch_blog', 'twitchpress_integrate_wpdb_webhooksmeta', 0 );

            // Listen for Twitch.tv EventSub webhook notifications and store them in cache for processing later...
            add_action( 'admin_post_nopriv', 'twitchpress_webhooks_eventsub_listener', 1, 0 );
            
            // Background processing of webhook notifications that have been stored in cache...
            add_action( 'init', array( new TwitchPress_Webhooks_Event_Processing(), 'process_handler' ) );            
        }       
    }
    
    /**
    * Output errors with a plain dump.
    * 
    * Pre-BugNet measure. 
    *     
    * @version 1.0
    */
    public function output_errors() {          
        // Display Errors Tool            
        if( !twitchpress_are_errors_allowed() ) { return false; }
                             
        ini_set( 'display_errors', 1 );
        error_reporting(E_ALL);
        
        add_action( 'shutdown', array( $this, 'show_errors' ), 1 );
        add_action( 'shutdown', array( $this, 'print_errors' ), 1 );                    
    }
   
    public function output_actions() {
        if( 'yes' !== get_option( 'twitchpress_display_actions') ) { return; }
                                                                       
        add_action( 'shutdown', array( $this, 'show_actions' ), 1 );                                                               
    }
        
    public function output_filters() {
        if( 'yes' !== get_option( 'twitchpress_display_filters') ) { return; }
                                                                       
        add_action( 'shutdown', array( $this, 'show_filters' ), 1 );                                                               
    }

    public static function show_errors() {      
        global $wpdb;
        echo '<div id="bugnet-wperror-dump">';       
            _e( '<h1>BugNet: Possible Errors</h1>', 'twitchpress' );
            $wpdb->show_errors( true );
        echo '</div>';   
    }
    
    public static function print_errors() {     
        global $wpdb;       
        $wpdb->print_error();    
    }    
    
    public function show_actions() {
        global $wp_actions;

        echo '<div id="bugnet-wpactions-dump">';
        _e( '<h1>BugNet: WordPress Actions</h1>', 'twitchpress' );
        echo '<pre>';
        print_r( $wp_actions );
        echo '</pre>';
        echo '</div>';  
    }
 
    public function show_filters() {
        global $wp_filter;

        echo '<div id="bugnet-wpfilters-dump">';
        _e( '<h1>BugNet: WordPress Filters</h1>', 'twitchpress' );
        echo '<pre>';
        //print_r( $wp_filter['admin_bar_menu'] );
        print_r( $wp_filter );
        echo '</pre>';
        echo '</div>';   
    }    
        
    /**
     * Get the plugin url.
     * @return string
     */
    public function plugin_url() {                
        return untrailingslashit( plugins_url( '/', __FILE__ ) );
    }

    /**
     * Get the plugin path.
     * @return string
     */
    public function plugin_path() {              
        return untrailingslashit( plugin_dir_path( __FILE__ ) );
    }

    /**
     * Get Ajax URL (this is the URL to WordPress core ajax file).
     * @return string
     */
    public function ajax_url() {                
        return admin_url( 'admin-ajax.php', 'relative' );
    }       
}

if( !function_exists( 'TwitchPress' ) ) {
    /**
     * Main instance of TwitchPress.
     *
     * Returns the main instance of TwitchPress to prevent the need to use globals.
     *
     * @since  1.0
     * @return TwitchPress
     */
    function TwitchPress() {        
        return WordPressTwitchPress::instance();
    }

    // Global for backwards compatibility.
    global $GLOBALS;   
    $GLOBALS['twitchpress'] = TwitchPress(); 
}

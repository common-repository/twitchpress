<?php
/**
 * TwitchPress Quick Tools Class
 * 
 * Contains methods for each quick tool. 
 * 
 * Remember that the quicktools table might be displaying cached data.
 * 
 * @author      Ryan Bayne
 * @category    Admin
 * @package     TwitchPress/Admin
 * @version     1.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'TwitchPress_Tools' ) ) :

/**
 * TwitchPress_Tools.
 * 
 * When making changes please remember that the quicktools 
 * table might be displaying cached data.
 * 
 * Append tool methods with "tool_". 
 */
class TwitchPress_Tools {
    /**
    * Change to true and iterate through all methods for info.
    * 
    * @var mixed
    */
    public $return_tool_info = false;
                  
    public function url( $tool_name ) {
        $nonce = wp_create_nonce( 'tool_action' );        
        return admin_url( 'admin.php?page=twitchpress_tools&_wpnonce=' . $nonce . '&toolname=' . $tool_name );    
    }
    
    public function text_link_tools_view( $tool_name, $tool_title, $href ) {
        return '<a href="' . $this->url( $tool_name ) . '">' . $tool_title . '</a></p>';    
    }
        
    public function button_link_tools_view( $tool_name, $tool_title ) {
        return '<a href="' . $this->url( $tool_name ) . '" class="button button-primary">' . $tool_title . '</a></p>';    
    }
    
    /**
    * A template tool. Replace "templatetool_" in method name with "tool_".
    * 
    * @version 1.0 
    */
    public function templatetool_rename_this_function_but_keep_tool_at_beginning() {
        /**
        * Description of values.
        * 
        * title       - give the tool a name.
        * description - describe what the tool does.
        * version     - tools must be versioned to give users warning
        * author      - we have to know who to come to for help with a tool
        * url         - link to a tutorial or other documentation
        * category    - a way to group tools
        * capability  - apply security using a core or custom capability
        * option      - add option name if configuration required to use tool
        */
        $tool_info = array(
            'title'       => __( 'Tool Title', 'multitool' ),
            'description' => __( 'This is the tool description.', 'multitool' ),
            'version'     => '1.1',
            'author'      => 'Ryan Bayne',
            'url'         => '',
            'category'    => 'users',
            'capability'  => 'activate_plugins',
            'option'      => null,
            'function'    => __FUNCTION__,
            'plugin'      => 'TwitchPress',
        );
        
        if( $this->return_tool_info ){ return $tool_info; }     
        
        if( !current_user_can( $tool_info['capability'] ) ) { return; }

        $notices = new TwitchPress_Admin_Notices();
        
        $example = new TwitchPress_EXAMPLE_API();

        if( !$example->streamlabs_app_ready ) {
            $notices->error( __( 'EXAMPLE Application Not Ready', 'twitchpress' ), __( 'A message here.', 'twitchpress' ) ); 
            return;   
        }
                
        /*
            Your tools unique code goes here. Make it do something!
        */
    }
            
    /**
    * Listens for tools being used on the Quick Tools table view.
    * 
    * Hooked by "init" in the init() method.
    * 
    * If a tool needs to send the user elsewhere, handle it by forwarding
    * them using a method in this class. Ensuring a standard approach to
    * every tools security checks and validation.
    *
    * @version 1.1
    */
    public static function admin_request_listener() {    
        if( !isset( $_REQUEST['_wpnonce'] ) ) {        
            return;
        }     
        
        if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'tool_action' ) ) {   
            return;
        } 
        
        if( !isset( $_GET['toolname'] ) ) {  
            return;
        }
        $tool_name = twitchpress_clean( $_GET['toolname'] );
             
        if( !method_exists( __CLASS__, $tool_name ) ) {        
            return;
        }    
        
        // Ensure the request is attempting to use an actual tool!
        if( substr( $tool_name, 0, 5 ) !== "tool_" ) {     
            return; 
        }
                              
        $QuickTools = new TwitchPress_Tools();                 
        $QuickTools->return_tool_info = true;
        
        // Prepare an array for passing to the tool method.
        $tool_parameters_array = array();
        
        // Get the requested tools information for performing validation.
        eval( '$tool_info = $QuickTools->$tool_name( $tool_parameters_array );');
        
        if( !isset( $tool_info['capability'] ) ) {        
            return;
        }
        
        if( !current_user_can( $tool_info['capability'] ) ) {       
            return;
        }
        
        // Is this a tool with multiple possible actions? 
        if( isset( $tool_info['actions'] ) && is_array( $tool_info['actions'] ) ) {
            $action = twitchpress_clean( $_GET['action'] );
            if( !isset( $tool_info['actions'][ $action ] ) ) {     
                return false;
            }   
            
            // Pass the specific action to the tools method.
            $tool_parameters_array['action'] = $action;
        }

        $QuickTools->return_tool_info = false;
        $QuickTools->$tool_name( $tool_parameters_array );
    }
    
    /**
    * Determines a tool request has been made or not...
    * 
    * @version 1.0
    */
    static function validate_request() {
        $backtrace = debug_backtrace( null, 2 );    
        if( !isset( $_GET['toolname'] ) || $backtrace[1]['function'] !== $_GET['toolname'] ) { return false; }
        return true;
    }
    
    public function get_categories() {
        return $tool_categories = array( 'posts', 'users', 'comments', 'plugins', 'security', 'seo', 'social', 'integration' );    
    }

    /**
    * Install a group of example pages...
    * 
    * @version 1.0 
    */
    public function tool_install_example_pages() {
        $tool_info = array(
            'title'       => __( 'Install Example Pages', 'multitool' ),
            'description' => __( 'Installs pages that include shortcodes using your configuration. This includes an index page listing all other pages.', 'multitool' ),
            'version'     => '1.1',
            'author'      => 'Ryan Bayne',
            'url'         => '',
            'category'    => 'users',
            'capability'  => 'activate_plugins',
            'option'      => null,
            'function'    => __FUNCTION__,
            'plugin'      => 'TwitchPress',
        );
        
        if( $this->return_tool_info ){ return $tool_info; }     
        
        if( !current_user_can( $tool_info['capability'] ) ) { return; }

        $notices = new TwitchPress_Admin_Notices();

        require_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'includes/admin/class.twitchpress-admin-install-examples.php' );
        TwitchPress_Install_Examples::everything();
    }
    
    /**
    * Called by a button in the Help tab under Installation. 
    * 
    * This tool is to be run by the owner of the site and the main channel. 
    * The oAuth procedure will be complete and a user token generated.
    * The token and refresh token is stored as the main channel token for features.
    * 
    * The WP users ID is also stored to indicate a relationship between WP user and owner. 
    * 
    * @version 3.0
    */
    public function tool_authorize_main_channel() {
        /**
        * Description of values.
        * 
        * title       - give the tool a name.
        * description - describe what the tool does.
        * version     - tools must be versioned to give users warning
        * author      - we have to know who to come to for help with a tool
        * url         - link to a tutorial or other documentation
        * category    - a way to group tools
        * capability  - apply security using a core or custom capability
        * option      - add option name if configuration required to use tool
        */
        $tool_info = array(
            'title'       => __( 'Authorize Main Channel', 'multitool' ),
            'description' => __( 'Only the site owner and owner of the main Twitch channel should use this tool. This tool will add permissions for more features to run i.e. getting the main channels subscribers.', 'multitool' ),
            'version'     => '1.1',
            'author'      => 'Ryan Bayne',
            'url'         => '',
            'category'    => 'users',
            'capability'  => 'activate_plugins',
            'option'      => null,
            'function'    => __FUNCTION__,
            'plugin'      => 'TwitchPress',
        );
                
        if( $this->return_tool_info ){ return $tool_info; }     
        
        if( !current_user_can( $tool_info['capability'] ) ) { return; }

        // Create a Twitch API oAuth2 URL           
        $state = array( 'redirectto' => admin_url( 'admin.php?page=twitchpress' ),
                        'userrole'   => 'administrator',
                        'outputtype' => 'admin',
                        'reason'     => 'mainchannelsetup',
                        'function'   => __FUNCTION__
        );
        
        // Generate the oAuth URL and forward the user to it. 
        wp_redirect( twitchpress_generate_authorization_url( twitchpress_scopes( true ), $state ) );
        exit;
    }  
    
    /**
    * Called by a button in the Help tab under Installation. 
    * 
    * @version 3.0
    */
    public function tool_authorize_bot_channel() {
        /**
        * Description of values.
        * 
        * title       - give the tool a name.
        * description - describe what the tool does.
        * version     - tools must be versioned to give users warning
        * author      - we have to know who to come to for help with a tool
        * url         - link to a tutorial or other documentation
        * category    - a way to group tools
        * capability  - apply security using a core or custom capability
        * option      - add option name if configuration required to use tool
        */
        $tool_info = array(
            'title'       => __( 'Authorize Bot Channel (beta)', 'multitool' ),
            'description' => __( 'Only the site owner and owner of the bot channel should use this tool..', 'multitool' ),
            'version'     => '1.1',
            'author'      => 'Ryan Bayne',
            'url'         => '',
            'category'    => 'users',
            'capability'  => 'activate_plugins',
            'option'      => null,
            'function'    => __FUNCTION__,
            'plugin'      => 'TwitchPress',
        );
        
        if( $this->return_tool_info ){ return $tool_info; }     
        
        if( !current_user_can( $tool_info['capability'] ) ) { return; }
                                        
        // Create a Twitch API oAuth2 URL           
        $state = array( 'redirectto' => admin_url( 'admin-post.php?action=twitchpress_bot_auth' ),
                        'userrole'   => 'administrator',
                        'outputtype' => 'admin',
                        'reason'     => 'botchannelsetup',
                        'function'   => __FUNCTION__
        );
        
        // Generate the oAuth URL and forward the user to it. 
        wp_redirect( twitchpress_generate_authorization_url( twitchpress_scopes( true ), $state ) );
        exit;
    }  
    
    /**
    * Sends the user to the latest wp_post (post,page,custom post types).
    * 
    * @version 2.0 
    */
    public function tool_go_to_latest_publication() {
        $tool_info = array(
            'title'       => 'View Latest Authored Post',
            'description' => __( 'Display the latest authored post (pages inlcluded).', 'multitool' ),
            'version'     => '1.1',
            'author'      => 'Ryan Bayne',
            'url'         => '',
            'category'    => 'users',
            'capability'  => 'activate_plugins',
            'option'      => null,
            'function'    => __FUNCTION__
        );
        
        if( $this->return_tool_info ){ return $tool_info; }     
        
        if( !current_user_can( $tool_info['capability'] ) ) { return; }

        $args = array(
            'numberposts' => 1,
            'orderby' => 'post_date',
            'order' => 'DESC',
            'post_type' => array( 'post', 'page' ),
            'post_status' => 'draft, publish, future, pending, private',
            'suppress_filters' => true
        );

        $post = wp_get_recent_posts( $args, ARRAY_A ); 
        
        $notices = new TwitchPress_Admin_Notices();

        $content = '';
        $content .= '<h3>' . $post[0]['post_title'] . '<h3>';
        $content .= '<p>' . $post[0]['post_content'] . '<p>';
        $content .= '<p><a href="' . $post[0]['guid'] . '">View Post</a><p>';
        $content .= '<p><a href="' . get_edit_post_link( $post[0]['ID'] ) . '">' . __( 'Edit Post', 'twitchpress' ). '</a>';
        
        TwitchPress_Admin_Notices::add_custom_notice( 'toollatestpublication', $content );      
    } 
    
    /**
    * Enable/Disabled error display.
    * 
    * @version 2.0 
    */
    public function tool_plugin_displayerrors( $tool_parameters_array ) {

        $tool_info = array(
            'title'       => __( 'Display Errors', 'twitchpress' ),
            'description' => __( 'A tool for developers that will display errors.', 'twitchpress' ),
            'version'     => '1.0',
            'author'      => 'Ryan Bayne',
            'url'         => '',
            'category'    => 'developers',
            'capability'  => 'activate_plugins',
            'option'      => 'displayerrors_activate',
            'function'    => __FUNCTION__
        );
        
        // Display the correct switch option...
        if( get_option( 'twitchpress_displayerrors') == 'yes' ) {
            $tool_info['actions'] = array( 
                'hideerrors' => array( 'title' => __( 'Hide Errors', 'twitchpress' ) ),
            );    
        } else {
            $tool_info['actions'] = array( 
                'displayerrors' => array( 'title' => __( 'Display Errors', 'twitchpress' ) ),
            );            
        }
              
        if( $this->return_tool_info ){ return $tool_info; }     
              
        if( !current_user_can( $tool_info['capability'] ) ) { return; }
             
        if( !isset( $tool_parameters_array['action'] ) ) { return; }
                                        
        if( $tool_parameters_array['action'] == 'displayerrors' ) {     
            
            update_option( 'twitchpress_displayerrors', 'yes', true );
            TwitchPress_Admin_Notices::add_custom_notice( 'displayerrorsyes', 'Error display has been activated by the TwitchPress Display Errors tool. You can reverse this by going to the plugins menu, select Quick Tools, search for "Display Errors" and click on the Hide Errors button.', 'twitchpress' );
            twitchpress_redirect_tracking( admin_url( 'admin.php?page=twitchpress_tools' ), __LINE__, __FUNCTION__ );
            exit;
            
        } elseif( $tool_parameters_array['action'] == 'hideerrors' ) {                 
            
            delete_option( 'twitchpress_displayerrors' );
            TwitchPress_Admin_Notices::add_custom_notice( 'displayerrorsno', 'Error display has been disabled by the TwitchPress Display Errors tool.', 'twitchpress' );            
            twitchpress_redirect_tracking( admin_url( 'admin.php?page=twitchpress_tools' ), __LINE__, __FUNCTION__ );          
            exit;
            
        }
    } 
    
    /**
    * Delete all trace data created by BugNet
    * 
    * @version 1.0
    */
    public function tool_delete_all_trace_transients() {
        /**
        * Description of values.
        * 
        * title       - give the tool a name.
        * description - describe what the tool does.
        * version     - tools must be versioned to give users warning
        * author      - we have to know who to come to for help with a tool
        * url         - link to a tutorial or other documentation
        * category    - a way to group tools
        * capability  - apply security using a core or custom capability
        * option      - add option name if configuration required to use tool
        */
        $tool_info = array(
            'title'       => __( 'Delete Cached Trace Data', 'multitool' ),
            'description' => __( 'Deletes all trace data generated by BugNet and stored in WordPress transient caches.', 'multitool' ),
            'version'     => '1.1',
            'author'      => 'Ryan Bayne',
            'plugin'      => 'TwitchPress',
            'url'         => '',
            'category'    => 'bugnet',
            'capability'  => 'activate_plugins',
            'option'      => null,
            'function'    => __FUNCTION__
        );
        
        if( $this->return_tool_info ){ return $tool_info; }     
        
        if( !current_user_can( $tool_info['capability'] ) ) { return; }
                                                                               
        TwitchPress_Admin_Notices::add_custom_notice( 'deletealltracetransients', __( 'All BugNet Tracing data has been deleted from caches.', 'twitchpress' ), 'twitchpress' );            
    }                                                

    /**
    * Tool for syncing all users.
    * 
    * Originally in the Sync Extension before it was merged.
    * 
    * @param mixed $return_tool_info
    * 
    * @version 1.0
    */
    public function tool_sync_all_users() {
        global $GLOBALS;
        
        /**
        * Description of values.
        * 
        * title       - give the tool a name.
        * description - describe what the tool does.
        * version     - tools must be versioned to give users warning
        * author      - we have to know who to come to for help with a tool
        * url         - link to a tutorial or other documentation
        * category    - a way to group tools
        * capability  - apply security using a core or custom capability
        * option      - add option name if configuration required to use tool
        */
        $tool_info = array(
            'title'       => __( 'Sync All Users', 'multitool' ),
            'description' => __( 'Import all WP users Twitch user data if not already done recently.', 'twitchpress' ),
            'version'     => '1.2',
            'author'      => 'Ryan Bayne',
            'plugin'      => 'Sync Extension',      
            'url'         => '',
            'category'    => 'users',
            'capability'  => 'activate_plugins',
            'option'      => null,
            'function'    => __FUNCTION__
        );
        
        if( $this->return_tool_info ){ return $tool_info; }     
        
        if( !$this->validate_request() ) { return; }
        
        if( !current_user_can( $tool_info['capability'] ) ) { return; }   
      
        // WP_User_Query - get all users who have a Twitch auth setup. 
        $args = array(    
            'meta_query' => array(
                array(
                    'key'     => 'twitchpress_code',
                    'compare' => 'EXISTS'
                ),
                array(
                    'key'     => 'twitchpress_token',
                    'compare' => 'EXISTS'
                ),
                array(
                    'key'     => 'twitchpress_sync_time',
                    'value'   => time(),
                    'compare' => '<'
                ),                
            )
        );

        // Create the WP_User_Query object
        $wp_user_query = new WP_User_Query( $args ); 
        $twitchers = $wp_user_query->get_results();
        if ( ! empty( $twitchers ) ) {
            foreach ( $twitchers as $next_user ) {
                # $GLOBALS['twitchpress']->
            }
        }    
        
        $notices = new TwitchPress_Admin_Notices();
        $notices->success( __( 'User Sync Finished', 'twitchpress' ), __( 'Your request to import data from Twitch and update your WordPress users has been complete. Due to the technical level of this action it is not easy to generate a summary. Please see log entries for specifics.', 'twitchpress' ) );   
    }    

    /**
    * 
    * 
    * @version 1.0 
    */
    public function tool_streamlabs_display_owner() {
        $tool_info = array(
            'title'       => __( 'Streamlabs Test - Display Owner', 'multitool' ),
            'description' => __( 'Will display Streamlabs data for the creator of the Streamlabs app credentials.', 'multitool' ),
            'version'     => '1.0',
            'author'      => 'Ryan Bayne',
            'url'         => '',
            'category'    => 'streamlabs',
            'capability'  => 'activate_plugins',
            'option'      => null,
            'function'    => __FUNCTION__,
            'plugin'      => 'TwitchPress',
        );
        
        if( $this->return_tool_info ){ return $tool_info; }     
        
        if( !current_user_can( $tool_info['capability'] ) ) { return; }

        $notices = new TwitchPress_Admin_Notices();
        
        // Prepare Streamlabs Applications object...
        $streamlabs = new TwitchPress_Streamlabs_API();

        // Check if Streamlabs Application is ready...
        if( !$streamlabs->streamlabs_app_ready ) {
            $notices->error( __( 'Streamlabs Application Not Ready', 'twitchpress' ), __( 'TwitchPress could not establish all the required Streamlabs credentails to make a call to the Streamlabs API.', 'twitchpress' ) ); 
            return;   
        }

        $result = $streamlabs->api_get_user_owner();
        
        $content = '<ul>';
        $content .= '<li>Streamlabs ID: ' . $result->streamlabs->id. '</li>';
        $content .= '<li>Streamlabs Display Name: ' . $result->streamlabs->display_name . '</li>';
        $content .= '<li>Twitch ID: ' . $result->twitch->id . '</li>';
        $content .= '<li>Twitch Display Name: ' . $result->twitch->display_name . '</li>';
        $content .= '<li>YouTube ID: ' . $result->youtube->id . '</li>';
        $content .= '<li>YouTube Title: ' . $result->youtube->title . '</li>';
        $content .= '</ul>';
         
        // Output
        TwitchPress_Admin_Notices::add_custom_notice( 'toolstreamlabsdisplayowner', 'Error display has been disabled by the TwitchPress Display Errors tool.', 'twitchpress' );
        $notices->success( __( 'Streamlabs Test', 'twitchpress' ), $content );   
    }

    /**
    * Discord Test Tool
    * 
    * @version 1.0 
    */
    public function tool_discord_test() {  return;
        /**
        * Description of values.
        * 
        * title       - give the tool a name.
        * description - describe what the tool does.
        * version     - tools must be versioned to give users warning
        * author      - we have to know who to come to for help with a tool
        * url         - link to a tutorial or other documentation
        * category    - a way to group tools
        * capability  - apply security using a core or custom capability
        * option      - add option name if configuration required to use tool
        */
        $tool_info = array(
            'title'       => __( 'Discord Test', 'multitool' ),
            'description' => __( 'This is the tool description.', 'multitool' ),
            'version'     => '1.1',
            'author'      => 'Ryan Bayne',
            'url'         => '',
            'category'    => 'users',
            'capability'  => 'activate_plugins',
            'option'      => null,
            'function'    => __FUNCTION__,
            'plugin'      => 'TwitchPress',
        );
        
        if( $this->return_tool_info ){ return $tool_info; }     
        
        if( !current_user_can( $tool_info['capability'] ) ) { return; }
        
        $notices = new TwitchPress_Admin_Notices();
        
        // Prepare Applications object...
        $discord = new TwitchPress_Discord_API();

        
        //https://discord.com/api/oauth2/authorize?client_id=709914283835654184&redirect_uri=http%3A%2F%2Flocalhost%2Ftwitchpress%2Fbranches%2FFebruary2020%2F&response_type=code&scope=identify%20email%20connections%20guilds%20guilds.join%20gdm.join%20rpc%20rpc.notifications.read%20webhook.incoming%20messages.read%20applications.builds.upload%20applications.builds.read%20applications.store.update%20applications.entitlements%20activities.read%20activities.write%20relationships.read
        
        
        // Check if Streamlabs Application is ready...
        if( !$discord->discord_app_ready ) {
            $notices->error( __( 'Discord Application Not Ready', 'twitchpress' ), __( 'TwitchPress could not establish required Discord credentails to make a call to the Discord Service.', 'twitchpress' ) ); 
            return;   
        }

        $result = $discord->api_get_user_owner();
        
        /*
        $content = '<ul>';
        $content .= '<li>Streamlabs ID: ' . $result->streamlabs->id. '</li>';
        $content .= '<li>Streamlabs Display Name: ' . $result->streamlabs->display_name . '</li>';
        $content .= '<li>Twitch ID: ' . $result->twitch->id . '</li>';
        $content .= '<li>Twitch Display Name: ' . $result->twitch->display_name . '</li>';
        $content .= '<li>YouTube ID: ' . $result->youtube->id . '</li>';
        $content .= '<li>YouTube Title: ' . $result->youtube->title . '</li>';
        $content .= '</ul>';
        */
         
        // Output
        $notices->success( __( 'DISCORD TEST', 'twitchpress' ), $content ); 
    }    

    public function tool_google_api_test() {   
        /**
        * Description of values.
        * 
        * title       - give the tool a name.
        * description - describe what the tool does.
        * version     - tools must be versioned to give users warning
        * author      - we have to know who to come to for help with a tool
        * url         - link to a tutorial or other documentation
        * category    - a way to group tools
        * capability  - apply security using a core or custom capability
        * option      - add option name if configuration required to use tool
        */
        $tool_info = array(
            'title'       => __( 'YouTube Playlist Test', 'multitool' ),
            'description' => __( 'Displays a list of videos from the Squadron 42 playlist on the Star Citizen channel.', 'multitool' ),
            'version'     => '1.1',
            'author'      => 'Ryan Bayne',
            'url'         => '',
            'category'    => 'users',
            'capability'  => 'activate_plugins',
            'option'      => null,
            'function'    => __FUNCTION__,
            'plugin'      => 'TwitchPress',
        );
        
        if( $this->return_tool_info ){ return $tool_info; }     
        
        if( !current_user_can( $tool_info['capability'] ) ) { return; }
        
        require_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'includes/libraries/allapi/youtube/Google/autoload.php' );

        $google_api_key = get_option( 'twitchpress_allapi_youtube_default_id', null );      

        $client = new Google_Client();
        $client->setApplicationName("Client_Library_Examples");
        $client->setDeveloperKey( $google_api_key );
        
        $youtube = new Google_Service_YouTube($client);

        $nextPageToken = '';
        $htmlBody = '<ul>';

        do {
            $playlistItemsResponse = $youtube->playlistItems->listPlaylistItems('snippet', array(
            'playlistId' => 'PLVct2QDhDrB2-Edu0jm18lz0W9NRcXy3Y', // PLVct2QDhDrB2-Edu0jm18lz0W9NRcXy3Y    PLVct2QDhDrB0QRjv9oN02f8mGsml8tcK9
            'maxResults' => 50,
            'pageToken' => $nextPageToken));

            foreach ($playlistItemsResponse['items'] as $playlistItem) {

                $htmlBody .= sprintf('<li>%s (%s)</li>', $playlistItem['snippet']['title'], $playlistItem['snippet']['resourceId']['videoId']);
            }

            $nextPageToken = $playlistItemsResponse['nextPageToken'];
        } while ( $nextPageToken <> '' );

        $htmlBody .= '</ul>';

        // Output
        TwitchPress_Admin_Notices::add_custom_notice( 'googleapitest', $htmlBody, 'twitchpress' );        
    }   
    
    /**
    * Update plugins core database tables. This will also install missing tables...
    * 
    * @version 1.0 
    */
    public function tool_update_plugins_core_database_tables() {
        /**
        * Description of values.
        * 
        * title       - give the tool a name.
        * description - describe what the tool does.
        * version     - tools must be versioned to give users warning
        * author      - we have to know who to come to for help with a tool
        * url         - link to a tutorial or other documentation
        * category    - a way to group tools
        * capability  - apply security using a core or custom capability
        * option      - add option name if configuration required to use tool
        */
        $tool_info = array(
            'title'       => __( 'Update/Repair Database Tables', 'multitool' ),
            'description' => __( 'Use to manually repair TwitchPress core database tables - this does not include tables for individual systems.', 'multitool' ),
            'version'     => '1.1',
            'author'      => 'Ryan Bayne',
            'url'         => '',
            'category'    => 'database',
            'capability'  => 'activate_plugins',
            'option'      => null,
            'function'    => __FUNCTION__,
            'plugin'      => 'TwitchPress',
        );
        
        if( $this->return_tool_info ){ return $tool_info; }     
        
        if( !current_user_can( $tool_info['capability'] ) ) { return; }

        // Install/update core database tables...
        include_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'includes/classes/class.twitchpress-tables-installation.php' );
        $tables = new TwitchPress_Install_Tables();
        $tables->primary_tables();
        
        TwitchPress_Admin_Notices::add_custom_notice( 'googleapitest', __( 'Tables have been installed or updated.', 'twitchpress' ), 'twitchpress' );
    }    
    
    /**
    * Display a count of subscribers for the main channel...
    * 
    * @version 1.0 
    */
    public function tool_display_main_channel_subscribers_total() {
        /**
        * Description of values.
        * 
        * title       - give the tool a name.
        * description - describe what the tool does.
        * version     - tools must be versioned to give users warning
        * author      - we have to know who to come to for help with a tool
        * url         - link to a tutorial or other documentation
        * category    - a way to group tools
        * capability  - apply security using a core or custom capability
        * option      - add option name if configuration required to use tool
        */
        $tool_info = array(
            'title'       => __( 'Display Subscribers Total', 'multitool' ),
            'description' => __( 'Get and count the total subscribers for the main channel.', 'multitool' ),
            'version'     => '1.1',
            'author'      => 'Ryan Bayne',
            'url'         => '',
            'category'    => 'users',
            'capability'  => 'activate_plugins',
            'option'      => null,
            'function'    => __FUNCTION__,
            'plugin'      => 'TwitchPress',
        );
        
        if( $this->return_tool_info ){ return $tool_info; }     
        
        if( !current_user_can( $tool_info['capability'] ) ) { return; }

        // Start preparing new API request...
        $helix = new TwitchPress_Twitch_API();
        $subs = $helix->get_broadcaster_subscriptions( twitchpress_get_main_channels_twitchid() );

        $notices = new TwitchPress_Admin_Notices();

        if( !isset( $subs->data ) || empty( $subs->data ) )
        {
            $message = __( 'The main channel either has no subscribers or cannot offer subscriptions.', 'twitchpress' );
            
            $notices->info( __( 'No Subscribers', 'twitchpress' ), $message, false );
            
            TwitchPress_API_Logging::outcome( $helix->curl_object->loggingid, $message );
        }
        elseif( is_array( $subs->data ) && !empty( $subs->data ) )
        {
            $total = count( $subs->data );
            
            $message = __( 'The main channel has a total of ' . $total . ' subscribers.', 'twitchpress' );
            
            TwitchPress_API_Logging::outcome( $helix->curl_object->loggingid, $message );
            
            TwitchPress_Admin_Notices::add_custom_notice( 'mainchannelsubtotaltesttool', $message, 'twitchpress' );            
        }
    }
    
    /**
    * Display some subscribers for the main channel...
    * 
    * @version 1.0 
    */
    public function tool_display_main_channel_subscribers_test() {
        /**
        * Description of values.
        * 
        * title       - give the tool a name.
        * description - describe what the tool does.
        * version     - tools must be versioned to give users warning
        * author      - we have to know who to come to for help with a tool
        * url         - link to a tutorial or other documentation
        * category    - a way to group tools
        * capability  - apply security using a core or custom capability
        * option      - add option name if configuration required to use tool
        */
        $tool_info = array(
            'title'       => __( 'Display Subscribers: Test Tool', 'multitool' ),
            'description' => __( 'Test the plugins ability to get some subscribers for your main channel.', 'multitool' ),
            'version'     => '1.1',
            'author'      => 'Ryan Bayne',
            'url'         => '',
            'category'    => 'users',
            'capability'  => 'activate_plugins',
            'option'      => null,
            'function'    => __FUNCTION__,
            'plugin'      => 'TwitchPress',
        );
        
        if( $this->return_tool_info ){ return $tool_info; }     
        
        if( !current_user_can( $tool_info['capability'] ) ) { return; }

        $notices = new TwitchPress_Admin_Notices();
        
        $helix = new TWITCHPRESS_Twitch_API();
        $twitch_sub_response = $helix->get_broadcaster_subscriptions( twitchpress_get_main_channels_twitchid() );        
        
        if( isset( $twitch_sub_response->data ) && count( $twitch_sub_response->data ) == 0 ) {
            $notices->error( __( 'No Subscribers', 'twitchpress' ), __( 'If the test worked then there are no subscribers for the main channel.', 'twitchpress' ) ); 
            return;   
        }
        
        $limit = 3;
        $dumped = 0;
         
        foreach( $twitch_sub_response->data as $key => $subscriber ) {
            twitchpress_var_dump( $subscriber );  # DO NOT REMOVE # 
            ++$dumped;
            if( $dumped == $limit ) { break; }
        }
    }    

    /**
    * Test if the Subscribers system is ready, the main channel has subscribers...
    * 
    * @version 1.0 
    */
    public function tool_display_main_channel_subscribers_system_ready_test() {
        /**
        * Description of values.
        * 
        * title       - give the tool a name.
        * description - describe what the tool does.
        * version     - tools must be versioned to give users warning
        * author      - we have to know who to come to for help with a tool
        * url         - link to a tutorial or other documentation
        * category    - a way to group tools
        * capability  - apply security using a core or custom capability
        * option      - add option name if configuration required to use tool
        */
        $tool_info = array(
            'title'       => __( 'Subscribers System Ready Test', 'multitool' ),
            'description' => __( 'Determine if the subscribers system is ready with your main Twitch channel.', 'multitool' ),
            'version'     => '1.1',
            'author'      => 'Ryan Bayne',
            'url'         => '',
            'category'    => 'subscribers',
            'capability'  => 'activate_plugins',
            'option'      => null,
            'function'    => __FUNCTION__,
            'plugin'      => 'TwitchPress',
        );
        
        if( $this->return_tool_info ){ return $tool_info; }     
        
        if( !current_user_can( $tool_info['capability'] ) ) { return; }
        
        $notices = new TwitchPress_Admin_Notices();
        
        // Check broadcaster type to ensure main channel is part of subscription program...
        $helix = new TWITCHPRESS_Twitch_API();
        $user = $helix->get_user_by_id( twitchpress_get_main_channels_twitchid() );
        if( $user->data[0]->broadcaster_type !== 'partner' && $user->data[0]->broadcaster_type !== 'affiliate' ) {
            $notices->error( __( 'Subscription System Not Ready', 'twitchpress' ), __( 'The main channel is not part of the Twitch subscription program. A channel needs to have partner or affiliate status to offer subscriptions.', 'twitchpress' ) );         
            return;
        }
        
        // Confirm user read subscriptions scope has been checked for the main application...
        if( !twitchpress_confirm_scope( 'user_read_subscriptions', 'channel' ) ) {
            $notices->error( __( 'User Scope Issue', 'twitchpress' ), __( 'The user:read:subscriptions scope has not been checked in settings.', 'twitchpress' ) ); 
            return;               
        } 
        
        // Confirm channel red subscriptions scope has been checked for the main application...
        if( !twitchpress_confirm_scope( 'channel_read_subscriptions', 'channel' ) ) {
            $notices->error( __( 'Channel Scope Issue', 'twitchpress' ), __( 'The channel:read:subscriptions scope has not been checked in settings.', 'twitchpress' ) ); 
            return;               
        }        

        // Get the main channels subscribers...
        $subscribers = $helix->get_broadcaster_subscriptions( twitchpress_get_main_channels_twitchid(), null, null, 100 );
                  
        if( !isset( $subscribers->total ) ) {
            $notices->error( __( 'Problem Detected', 'twitchpress' ), __( 'TwitchPress was unable to get any subscribers for the main channel, please report this notice in the TwitchPress Discord.', 'twitchpress' ) );             
        } else {
            $notices->success( __( 'Subscription System Ready', 'twitchpress' ), sprintf( __( 'TwitchPress was able to confirm you have %d subscribers or more. The subscription system is ready.', 'twitchpress' ), $subscribers->total ) );    
        }        
    }    
    
    /**
    * Starts a commercial on the main channel.
    * 
    * @version 1.0 
    */
    public function tool_start_commercial() {
        $tool_info = array(
            'title'       => __( 'Start Commercial', 'multitool' ),
            'description' => __( 'Starts a commercial on the main channel.', 'multitool' ),
            'version'     => '1.1',
            'author'      => 'Ryan Bayne',
            'url'         => '',
            'category'    => 'moderation',
            'capability'  => 'activate_plugins',
            'option'      => null,
            'function'    => __FUNCTION__,
            'plugin'      => 'TwitchPress',
        );
        
        if( $this->return_tool_info ){ return $tool_info; }     
        
        if( !current_user_can( $tool_info['capability'] ) ) { return; }

        $notices = new TwitchPress_Admin_Notices();
        
        $helix = new TWITCHPRESS_Twitch_API();
        
        $main_channels_twitch_id = twitchpress_get_main_channels_twitchid();
         
        $reply = $helix->start_commercial( $main_channels_twitch_id, $length = 30 );   

        if( isset( $reply->status ) && $reply->status == '400' ) {
            $notices->error( __( 'Commercial Cannot Run', 'twitchpress' ), $reply->error . ', ' . $reply->message );    
        } else {
            $append = sprintf( 'The commercial will run for %d seconds and you can run another in %d seconds.', 
            $reply->data[0]->length, $reply->data[0]->retry_after );
            
            $notices->add_custom_notice( 'startcommercialtoolmainchannel', $reply->data[0]->message );            
        }
    }

    /**
    * 
    * 
    * @version 1.0 
    */
    public function tool_get_bits_leaderboard() {
        $tool_info = array(
            'title'       => __( 'Get Bits Leaderboard', 'multitool' ),
            'description' => __( 'Gets a ranked list of Bits leaderboard information for an authorized broadcaster.', 'multitool' ),
            'version'     => '1.1',
            'author'      => 'Ryan Bayne',
            'url'         => '',
            'category'    => 'moderation',
            'capability'  => 'activate_plugins',
            'option'      => null,
            'function'    => __FUNCTION__,
            'plugin'      => 'TwitchPress',
        );
        
        if( $this->return_tool_info ){ return $tool_info; }     
        
        if( !current_user_can( $tool_info['capability'] ) ) { return; }

        $notices = new TwitchPress_Admin_Notices();
        
        $helix = new TWITCHPRESS_Twitch_API();
        $reply = $helix->get_bits_leaderboard( 100 );
        
        twitchpress_var_dump_safer( $reply );
    }

    /**
    * Sync a giving WordPress users Twitch subscription data...
    * 
    * @version 1.0 
    * 
    * @uses function twitchpress_toolsform_syncsubscriber()
    */
    public function tool_syncsubscriber() {            
        /**
        * Description of values.
        * 
        * title       - give the tool a name.
        * description - describe what the tool does.
        * version     - tools must be versioned to give users warning
        * author      - we have to know who to come to for help with a tool
        * url         - link to a tutorial or other documentation
        * category    - a way to group tools
        * capability  - apply security using a core or custom capability
        * option      - add option name if configuration required to use tool
        */
        $tool_info = array(
            'title'       => __( 'Sync Subscriber', 'multitool' ),
            'description' => __( 'Enter a WordPress user ID to update their Twitch subscription data.', 'multitool' ),
            'version'     => '1.1',
            'author'      => 'Ryan Bayne',
            'url'         => '',
            'category'    => 'users',
            'capability'  => 'activate_plugins',
            'option'      => null,
            'function'    => __FUNCTION__,
            'plugin'      => 'TwitchPress',
        );        
        
        $tool_info['thickbox_link'] = '#TB_inline?&width=600&height=450&inlineId=twitchpress_toolsform_syncsubscriber';  
                        
        if( $this->return_tool_info ){ return $tool_info; }     
        
        if( !current_user_can( $tool_info['capability'] ) ) { return; }

        $notices = new TwitchPress_Admin_Notices();
        
        if( !isset( $_POST['wordpress_user_id'] ) || empty( $_POST['wordpress_user_id'] ) || !is_numeric( $_POST['wordpress_user_id'] ) ) {     
            $notices->error( __( 'WordPress User ID Missing', 'twitchpress' ), __( 'Please enter a valid WordPress users ID in the pop-up window.', 'twitchpress' ) );             
            return; 
        }
        if( !get_user_by( 'id', $_POST['wordpress_user_id'] ) ) {
            $notices->error( __( 'WordPress User ID Invalid', 'twitchpress' ), sprintf( __( 'WordPress ID %d does not match a user in this WordPress database.', 'twitchpress' ), $_POST['wordpress_user_id'] ) );                         
            return;
        } else {
            $wp_user_id = $_POST['wordpress_user_id'];
        } 

        // Display locally stored subscription data...
        $sub_array = twitchpress_get_user_meta_twitch_sub( $wp_user_id ); 
        if( !$sub_array ) {
            $notices->info( __( 'First Time Import', 'twitchpress' ), sprintf( __( 'User with ID %d does not have locally stored Twitch subscription data.', 'twitchpress' ), $wp_user_id ) );    
        } else {
            $notices->info( __( 'Previously Imported', 'twitchpress' ), sprintf( __( 'User with ID %d has locally stored Twitch subscription data.', 'twitchpress' ), $wp_user_id ) );                
        }
        
        // Display newly requested data from the Twitch API...
        $twitch_api = new TwitchPress_Twitch_API();    
        $twitch_user_id = twitchpress_get_user_twitchid_by_wpid( $_POST['wordpress_user_id'] );    
        $twitch_channel_id = twitchpress_get_main_channels_twitchid();
        $twitch_user_token = twitchpress_get_user_token( $wp_user_id );
        $local_sub_array = twitchpress_get_user_meta_twitch_sub( $wp_user_id );
        $twitch_sub_array = $twitch_api->get_broadcaster_subscriptions( $twitch_channel_id, $wp_user_id, false );

        // Cancelled
        if( $local_sub_array && !$twitch_sub_array ) {
            
            TwitchPress_Admin_Notices::add_wordpress_notice( 'usersubsyncnosubresponsetool', 'warning', false, 
            __( 'Subscription Ended', 'twitchpress' ), 
            __( 'The response from Twitch.tv indicates that a previous subscription to the sites main channel was discontinued. Subscriber perks on this website will also be discontinued.', 'twitchpress' ) );

            // API Logging outcome (helix only)...
            $outcome = sprintf( __( 'User with ID [%s] has stopped subscribing.','twitchpress'), $wp_user_id );
            TwitchPress_API_Logging::outcome( $twitch_api->curl_object->loggingid, $outcome );

            // Remove local subscription data to prevent perks access...
            twitchpress_delete_user_meta_twitch_sub( $wp_user_id ); 
        }

        // No recent subscription... 
        if( !$local_sub_array && !$twitch_sub_array || !isset( $twitch_sub_array->data[0]['tier'] ) ) { 
            TwitchPress_Admin_Notices::add_wordpress_notice( 'usersubsyncnosubresponsetool', 'info', false, 
            __( 'Not Subscribing', 'twitchpress' ), 
            __( 'The response from Twitch.tv indicates that the user is not currently subscribing to this sites main channel.', 'twitchpress' ) );
            
            // API Logging outcome...
            $outcome = sprintf( __( 'User with ID [%s] is not a Twitch.tv subscriber and no updates were required.','twitchpress'), $wp_user_id );
            TwitchPress_API_Logging::outcome( $twitch_api->curl_object->loggingid, $outcome );
        }                     
                        
        // First time subscription sync...
        if( !$local_sub_array && $twitch_sub_array && isset( $twitch_sub_array->data[0]['tier'] ) ) { 
            // Action - update the user meta with raw subscription data array...
            twitchpress_update_user_meta_twitch_sub( $wp_user_id, $twitch_sub_array->data[0] );
            
            TwitchPress_Admin_Notices::add_wordpress_notice( 'usersubsyncnosubresponsetool', 'success', false, 
            __( 'First Import', 'twitchpress' ), 
            __( 'A Twitch subscription has been imported for the first time for the user.', 'twitchpress' ) );

            // API Logging outcome...
            $outcome = sprintf( __( 'User with ID [%s] is a subscriber being synced for the first time.','twitchpress'), $wp_user_id );
            TwitchPress_API_Logging::outcome( $twitch_api->curl_object->loggingid, $outcome );
        }
                                
        // Sub plan changed...
        if( isset( $local_sub_array->data[0]['tier'] ) && isset( $twitch_sub_array->data[0]['tier'] ) && $local_sub_array->data[0]['tier'] !== $twitch_sub_array->data[0]['tier'] ) {
            twitchpress_update_user_meta_twitch_sub( $wp_user_id, $twitch_sub_array->data[0] );
            
            TwitchPress_Admin_Notices::add_wordpress_notice( 'usersubsyncnosubresponsetool', 'success', false, 
            __( 'Subscription Plan Changed', 'twitchpress' ), 
            __( 'Existing subscription data was updated due to a change in the plan.', 'twitchpress' ) );

            // API Logging outcome (helix only)...
            $outcome = sprintf( __( 'User with ID [%s] has changed their Twitch.tv subscription plan.','twitchpress'), $wp_user_id );
            TwitchPress_API_Logging::outcome( $twitch_api->curl_object->loggingid, $outcome );
        }

        // No change to plan...
        if( isset( $local_sub_array->data[0]['tier'] ) && isset( $twitch_sub_array->data[0]['tier'] ) && $local_sub_array->data[0]['tier'] == $twitch_sub_array->data[0]['tier'] ) {
            TwitchPress_Admin_Notices::add_wordpress_notice( 'usersubsyncnosubresponsetool', 'success', false, 
            __( 'Continuing Subscriber', 'twitchpress' ), 
            __( 'Existing subscription has been confirmed with no changes found, no update required.', 'twitchpress' ) );

            $outcome = sprintf( __( 'User with ID [%s] is subscribing on the same plan.','twitchpress'), $wp_user_id );
            TwitchPress_API_Logging::outcome( $twitch_api->curl_object->loggingid, $outcome );
        }   
        
        if( isset( $_POST['twitchpress_var_dump'] ) ) {
            twitchpress_var_dump_safer( $twitch_sub_array );
        }
        
        TwitchPress_Admin_Notices::add_wordpress_notice( 'subscriptionquerycompletetool', 'success', false, 
        __( 'Subscription Query Complete', 'twitchpress' ), 
        sprintf( __( 'This notice marks the end of the procedure for querying the Twitch.tv subscription for WordPress user ID %s and if information you require has not been displayed in additional notices please contact the plugin author with feedback.', 'twitchpress' ), $wp_user_id ) );                            
    }
    
    
    /**
    * This tool tests TwitchPress_Twitch_API::get_user_by_id() 
    * 
    * @version 1.0 
    */
    public function tool_get_user_by_id() {
        /**
        * Description of values.
        * 
        * title       - give the tool a name.
        * description - describe what the tool does.
        * version     - tools must be versioned to give users warning
        * author      - we have to know who to come to for help with a tool
        * url         - link to a tutorial or other documentation
        * category    - a way to group tools
        * capability  - apply security using a core or custom capability
        * option      - add option name if configuration required to use tool
        */
        $tool_info = array(
            'title'       => __( 'Function Test: TwitchPress_Twitch_API::get_user_by_id()', 'multitool' ),
            'description' => __( 'This function should return an array of data from Twitch.tv for the giving Twitch user.', 'multitool' ),
            'version'     => '1.1',
            'author'      => 'Ryan Bayne',
            'url'         => '',
            'category'    => 'users',
            'capability'  => 'activate_plugins',
            'option'      => null,
            'function'    => __FUNCTION__,
            'plugin'      => 'TwitchPress',
        );
        
        $tool_info['thickbox_link'] = '#TB_inline?&width=600&height=450&inlineId=twitchpress_toolsform_get_user_by_id';
        
        if( $this->return_tool_info ){ return $tool_info; }     
        
        if( !current_user_can( $tool_info['capability'] ) ) { return; }

        $notices = new TwitchPress_Admin_Notices();
        
        if( !isset( $_POST['wordpress_user_id2'] ) || empty( $_POST['wordpress_user_id2'] ) || !is_numeric( $_POST['wordpress_user_id2'] ) ) {     
            $notices->error( __( 'Twitch User ID Missing', 'twitchpress' ), __( 'Please enter a valid Twitch user ID in the pop-up window.', 'twitchpress' ) );             
            return; 
        }
        
        $twitch = new TwitchPress_Twitch_API();
        
        //twitchpress_var_dump( $_POST['wordpress_user_id2'] );
        
        $array = $twitch->get_user_by_id( $_POST['wordpress_user_id2'] );

        if( isset( $_POST['twitchpress_var_dump2'] ) ) {
            twitchpress_var_dump_safer( $array );
            return;
        }
        
        if( !isset( $array->data[0]->display_name ) ) {     
            $notices->error( __( 'User Not Found', 'twitchpress' ), sprintf( __( 'The giving ID %s does not match a Twitch user.', 'twitchpress' ), $_POST['wordpress_user_id2'] ) );             
            return; 
        }
                
        $display_name = $array->data[0]->display_name; 
        echo $notices->info( __( 'Getting User By Twitch ID', 'twitchpress' ), sprintf( __( 'Twitch user ID belongs too %s', 'twitchpress' ), $display_name ), true );
    }
}
   
endif;

$QuickTools = new TwitchPress_Tools();
add_action( 'init', array( $QuickTools, 'admin_request_listener' ), 5  );
unset($QuickTools);
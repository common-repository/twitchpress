<?php
/**
 * Twitch API Helix for WordPress
 *
 * Do not use this class unless you accept the Twitch Developer Services Agreement
 * @link https://www.twitch.tv/p/developer-agreement
 * 
 * @author   Ryan Bayne
 * @version 6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Make sure we meet our dependency requirements
if (!extension_loaded('curl')) trigger_error('cURL is not currently installed on your server, please install cURL if your wish to use Twitch services in TwitchPress.');
if (!extension_loaded('json')) trigger_error('PECL JSON or pear JSON is not installed, please install either PECL JSON or compile pear JSON if you wish to use Twitch services in TwitchPress.');

if( !class_exists( 'TwitchPress_Twitch_API' ) ) :

class TwitchPress_Twitch_API {
    public $data;
    public $twitch_channel_id;
    public $streamlabs_app_id;
    public $streamlabs_app_secret;
    public $streamlabs_app_uri;

    /**
    * Post-request boolean value for tracking the calls purpose
    * and ability to meet requirements. 
    * 
    * @var mixed
    */
    public $call_result = false; 
    
    /**
    * Managing logging information from WP core, PHP, Twitch API etc
    * 
    * @var mixed
    */
    public $logging = array();
    
    // Debugging variables.
    public $twitch_call_name = 'Unknown';

    // Public notice assistance (built outside of this class)...
    public $public_notice_title = null;
    public $public_notice_actions = array();
    
    // Administrator notice creation (built within this class)...
    public $admin_notice_title = null;      // Usually a string of text
    public $admin_notice_body = null;       // Usually just a string of text
    public $admin_notice_actions = array(); // Multiple actions may be offered 
    public $admin_user_request = false;     // true triggers output for the current admin user
    public $curl_version = array(); 
    public $curl_object = null;
    
    /**
    * Twitch API Scopes
    * 
    * @var mixed
    */
    public $twitch_scopes = array( 
            'channel_check_subscription',
            'channel_commercial',
            'channel_editor',
            'channel_read',
            'channel_stream',
            'channel_subscriptions',
            'collections_edit',
            'communities_edit',
            'communities_moderate',
            'user_blocks_edit',
            'user_blocks_read',
            'user_follows_edit',
            'user_read',
            'user_subscriptions',
            'analytics:read:extensions', // View analytics data for your extensions.
            'analytics:read:games',      // View analytics data for your games.
            'bits:read',                 // View Bits information for your channel.
            'clips:edit',                // Manage a clip object.
            'channel:edit:commercial',   // Run commercials on a channel.
            'channel:manage:extensions', // Manage a channel�s Extension configuration, including activating Extensions.
            'user:edit',                 // Manage a user object.
            'user:edit:broadcast',       // Edit your channels broadcast configuration, including extension configuration. (This scope implies user:read:broadcast capability.)
            'user:read:broadcast',       // View your broadcasting configuration, including extension configurations.
            'channel:manage:broadcast',  // Manage a channel�s broadcast configuration, including updating channel configuration and managing stream markers and stream tags.
            'user:read:email',           // Read authorized users email address. 
            'user:read:subscriptions',   // 
            'channel:read:subscriptions',// Get all of a broadcaster�s subscriptions.
            'chat:edit',                 
            'chat:read', 
            'channel:manage:polls',      // Manage a channel�s polls.   
            'channel:manage:predictions',// Manage a channel�s Channel Points Predictions 
            'channel:manage:redemptions', 
            'channel:manage:schedule',   // Manage a channel�s stream schedule.
            'channel:manage:videos',     // Manage a channel�s videos, including deleting videos.
            'channel:read:editors',      // View a list of users with the editor role for a channel.
            'channel:read:goals',        // View Creator Goals for a channel.
            'channel:read:hype_train',   // View Hype Train information for a channel.
            'channel:read:polls',        // View a channels polls
            'channel:read:predictions',  // View a channel�s Channel Points Predictions.
            'channel:read:redemptions',  // View Channel Points custom rewards and their redemptions on a channel.
            'channel:read:stream_key',   // View an authorized user�s stream key.
            'moderation:read',           // View a channel�s moderation data including Moderators, Bans, Timeouts, and Automod settings.
            'moderator:manage:banned_users', // Ban and unban users.
            'moderator:read:blocked_terms', // View a broadcaster�s list of blocked terms.
            'moderator:manage:blocked_terms', // Manage a broadcaster�s list of blocked terms.
            'moderator:manage:automod',   // Manage messages held for review by AutoMod in channels where you are a moderator.
            'moderator:read:automod_settings', // View a broadcaster�s AutoMod settings.
            'moderator:manage:automod_settings', // Manage a broadcaster�s AutoMod settings.
            'moderator:read:chat_settings', // View a broadcaster�s chat room settings.
            'moderator:manage:chat_settings', // Manage a broadcaster�s chat room settings.
            'user:manage:blocked_users', // Manage the block list of a user.
            'user:read:blocked_users', // View the block list of a user.
            'user:read:broadcast', // View a user�s broadcasting configuration, including Extension configurations. 
            'user:read:follows', // View the list of channels a user follows.
            'whispers:read', // View your whisper messages.
            'whispers:edit', // Send whisper messages.
    );
             
    /**
    * Array of endorsed channels, only partnered or official channels will be 
    * added here to reduce the risk of unwanted/nsfw sample content. 
    * 
    * @var mixed
    * 
    * @version 1.0
    */
    public $twitchchannels_endorsed = array(
        'lolindark1'  => array( 'display_name' => 'LOLinDark1' ),
        'nookyyy'     => array( 'display_name' => 'nookyyy' ),        
        'starcitizen' => array( 'display_name' => 'StarCitizen' ),
    );

    public function __construct(){                
        $curl_info = curl_version();
        $this->curl_version = $curl_info['version'];         
    } 
    
    /**
    * Creates a Curl object ($this->curl_object) using my extending class
    * TwitchPress_Curl() for WP_Http_Curl(). This does not execute the 
    * call. See $this->call() examples on my own common but flexible approach...
    *  
    * This method also adds additional information that helps the plugin
    * manage manage data and logging...
    * 
    * @version 3.0
    * 
    * @param string $file
    * @param string $function
    * @param string $line
    * @param string $type
    * @param string $endpoint
    * @param boolean $paginate - Pass true to allow many calls in one procedure
    */
    public function curl( $file, $function, $line, $endpoint, $type = 'get', $api = 'helix', $paginate = false, $token_type = 'app' ) { 
                          
        // Create a Curl object... 
        $this->curl_object = new TwitchPress_Curl();// Extends WP_Http_Curl()
        $this->curl_object->originating_file = $file;
        $this->curl_object->originating_function = $function;
        $this->curl_object->originating_line = $line;
        $this->curl_object->type = $type;
        $this->curl_object->endpoint = $endpoint;
        $this->curl_object->paginate = $paginate;
        $this->curl_object->service = 'twitch';
        $this->curl_object->api = $api;
                
        // Add none API related parameters to the object...
        $this->curl_object->call_params(  
            false, 
            0, 
            false, 
            null, 
            false, 
            false, 
            __FUNCTION__,
            __LINE__ 
        );

        if( $token_type == 'visitor' ) {
            $token = twitchpress_get_user_token( TWITCHPRESS_CURRENTUSERID );    
        } elseif( $token_type == 'mainchannel' ) {
            $token = twitchpress_get_main_channels_token();
        } else {                  
            $token = twitchpress_get_app_token();
        }
        
        // Add common/default headers...
        $this->curl_object->add_headers( array(
            'Client-ID' => twitchpress_get_app_id(),
            'Authorization' => 'Bearer ' . $token,
        ) );
    }   
    
    /**
    * Uses TwitchPress_Curl::do_call() then finishes the final 
    * logging within the API procedure.
    * 
    * After using $this->call() in your method use $this->curl_object->curl_reply_body()
    * @version 1.0
    */
    function call() {             
        $this->set_accept_header();
                    
        // Start + make the request to Twitch.tv API in one line... 
        $this->curl_object->do_call( 'twitch' );

        // $this->curl_object is populated with the do_call() results...
        if( isset( $this->curl_object->response_code ) && $this->curl_object->response_code == '200' ) {
            // This will tell us that we should expect our wanted data to exist in $call_object
            // and we can use $this->call_result to assume that any database insert/update has happened also
            $this->curl_object->call_result = true;
        } else {    
            $this->curl_object->call_result = false; 

            if( !isset( $this->curl_object->response_code ) ) {
                // __( 'Response code not returned! Call ID [%s]', 'twitchpress' ), $this->curl_object->get_call_id() ), array(), true, false );            
            }
       
            if( $this->curl_object->response_code !== '200' ) {   
                // __( 'Response code [%s] Call ID [%s]', 'twitchpress' ), $this->curl_object->response_code, $this->curl_object->get_call_id() ), array(), true, false );            
            }
        }
    }  

    /**
    * Get Streams
    * 
    * Gets information about active streams. Streams are returned sorted by 
    * number of current viewers, in descending order. Across multiple pages of 
    * results, there may be duplicate or missing streams, 
    * as viewers join and leave streams.
    * 
    * The response has a JSON payload with a data field containing an array of 
    * stream information elements and a pagination field containing information 
    * required to query for more streams.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-streams
    * 
    * @param mixed $after
    * @param mixed $before
    * @param mixed $community_id
    * @param mixed $first
    * @param mixed $game_id
    * @param mixed $language
    * @param array $user_id
    * @param mixed $user_login
    * 
    * @version 2.3
    */
    public function get_streams( $after = null, $before = null, $community_id = null, $first = 100, $game_id = null, $language = null, $user_id = array(), $user_login = array() ) {
        $endpoint = 'https://api.twitch.tv/helix/streams';

        $endpoint = add_query_arg( 'after', $after, $endpoint );
        $endpoint = add_query_arg( 'before', $before, $endpoint );
        $endpoint = add_query_arg( 'community_id', $community_id, $endpoint );

        // Encorce the limit of the API 
        if( $first > 100 ) { $first = 100; }
        $endpoint = add_query_arg( 'first', $first, $endpoint );

        $endpoint = add_query_arg( 'game_id', $game_id, $endpoint );
        $endpoint = add_query_arg( 'language', $language, $endpoint );
        $endpoint = add_query_arg( 'user_login', $user_login, $endpoint );

        // Handle a $user_id that may be a string or an array...
        if( $user_id ) 
        {
            if( is_array( $user_id ) ) 
            {    
                $user_id_chunks = array_chunk( $user_id, $first );

                // Temporarily going to use the first chunk only 
                $endpoint = add_query_arg( 'user_id', implode( '&user_id=', $user_id_chunks[0] ), $endpoint );
                
                /*
                foreach( $user_id_chunks as $chunk ) {
                        TO BE COMPLETE 
                }
                */
            }
            elseif( is_numeric( $user_id ) )
            {
                $endpoint = add_query_arg( 'user_id', $user_id, $endpoint );   
            }          
        }

        $this->curl( __FILE__, __FUNCTION__, __LINE__, $endpoint, 'GET' );    
        $this->call();
        return $this->curl_object->curl_reply_body;
    }
      
    /**
    * Checks if application credentials are set.
    * 
    * @returns boolean true if set else an array of all the missing credentials.
    * 
    * @version 1.0
    */
    public function is_app_set() {
        
        /*
            Incomplete - added temporarily to solve login error...

            The values being checked are not available in this class and
            so we probably need to access the object registry directly to
            perform this check-up
        */
        
        return true;
    }        
              
    public function set_accept_header() {
        if( !isset( $this->curl_object->headers['Accept:'] ) ) {
            $this->curl_object->add_headers( array(
                //'Accept:' => 'Accept: application/vnd.twitchtv.v6+json',
            ) );            
        }
    }
    
    /**
    *       NEWER APPROACH  ------ STILL DOESNT WORK 
    * 
    * Alternative approach to requests...
    * 
    * Create a new HTTP Curl object with default Twitch app credentials.
    * 
    * You can easily use the contents of this function to create a custom
    * function outside of this class.
    * 
    * @param mixed $type is GET,PUT,POST,DELETE
    * @param mixed $endpoint
    * @param mixed $headers
    * @param mixed $body
    * @param mixed $additional
    * 
    * @return TwitchPress_Extend_WP_Http_Curl
    * 
    * @version 2.0 - Renamed Twitch_Request from WP_HTTP_Curl() 
    */
    public function Twitch_Request( $type, $endpoint, $headers = array(), $body = array(), $additional = array() ) {
        
        /*  NEWER APPROACH  ------ STILL DOESNT WORK */
        
        // Create new curl object for performing an API call...
        $new_curl = new TwitchPress_Extend_WP_Http_Curl();
        $new_curl->start_new_request(
            twitchpress_get_app_id(),
            twitchpress_get_app_secret(),
            twitchpress_get_app_token(),
            $type,                                                                       
            $endpoint
        ); 
        
        // Add headers if the default does not add them in the current package...
        $new_curl->option_headers_additional( $headers );
        
        // Add body parameters if the package hasn't been designed to add them automatically...
        $new_curl->option_body_additional( $body ); 
        
        // Now add miscellanous values that will make up our curl request...
        $new_curl->option_other_additional( $additional );    
        $new_curl->final_prep();
        $new_curl->do_call();
        $new_curl->call_array['response']['body'] = $new_curl->call_array['response']['body'];
        //$new_curl->call_array['response']['body'] = json_decode( $new_curl->call_array['response']['body'] );
        //$new_curl->call_array['response']['body'] = http_build_query( $new_curl->call_array['response']['body'] );

        return $new_curl->call_array['response'];          
    }
        
    /**
     * Generate an App Access Token as part of OAuth Client Credentials Flow. 
     * 
     * This token is meant for authorizing the application and making API calls that are not channel-auth specific. 
     * 
     * @param $code - [string] String of auth code used to grant authorization
     * 
     * @return object entire TwitchPress_Curl() object for handling any way required.
     * 
     * @version 3.0
     */
    public function request_app_access_token( $requesting_function = null ){
        $this->curl_object = new TwitchPress_Curl();
        $this->curl_object->originating_file = __FILE__;
        $this->curl_object->originating_function = __FUNCTION__;
        $this->curl_object->originating_line = __LINE__;
        $this->curl_object->type = 'POST';
        //$this->curl_object->endpoint = 'https://id.twitch.tv/oauth2/token?client_id=' . twitchpress_get_app_id();
        $this->curl_object->endpoint = 'https://id.twitch.tv/oauth2/token';
     
        // Set none API related parameters i.e. cache and rate controls...
        $this->curl_object->call_params( 
            false, 
            0, 
            false, 
            null, 
            false, 
            false, 
            __FUNCTION__,
            __LINE__ 
        );
        
        // Use app credentials from my own registry for sensitive data...
        $twitch_app = TwitchPress_Object_Registry::get( 'twitchapp' );
        
        $this->curl_object->endpoint = add_query_arg( array(
            'client_id'        => $twitch_app->app_id,
            'client_secret'    => $twitch_app->app_secret,
            'redirect_uri'     => $twitch_app->app_redirect,
            'grant_type'       => 'client_credentials'        
        ), $this->curl_object->endpoint );

        /*
        $this->curl_object->set_curl_body( array(
            'client_id'        => $twitch_app->app_id,
            'client_secret'    => $twitch_app->app_secret,
            'redirect_uri'     => $twitch_app->app_redirect,
            'grant_type'       => 'client_credentials'
        ) );
        */
        
        /*
        $this->curl_object->body = array(
            'client_id'        => $twitch_app->app_id,
            'client_secret'    => $twitch_app->app_secret,
            'redirect_uri'     => $twitch_app->app_redirect,
            'grant_type'       => 'client_credentials'
        );
        */
        
        /*
        $this->curl_object->headers = array(
            'client_id'        => $twitch_app->app_id,
            'client_secret'    => $twitch_app->app_secret,
            'redirect_uri'     => $twitch_app->app_redirect,
            'grant_type'       => 'client_credentials'
        );
        */
        
        unset($twitch_app);

        // Start + make the request in one line... 
        $this->curl_object->do_call( 'twitch' );
        
        // This method returns $call_twitch->curl_response_body;
        return $this->curl_object;
    }
    
    /**
    * Process the object created by class TwitchPress_Curl(). 
    * 
    * Function request_app_access_token() is called first, it returns $call_object
    * so we can perform required validation and then we call this method.
    * 
    * @param mixed $call_object
    * 
    * @version 2.0
    */
    public function app_access_token_process_call_reply( $call_object ) {
        $options = array();
                              
        if( !isset( $call_object->curl_reply_body->access_token ) ) {
            return false;
        }
        
        if( !isset( $call_object->curl_reply_body->expires_in ) ) {
            return false;
        }
        
        // Update option record and object registry...            
        twitchpress_update_app_token( $call_object->curl_reply_body->access_token );
        twitchpress_update_app_token_expiry( $call_object->curl_reply_body->expires_in ); 

        return $call_object->curl_reply_body->access_token; 
    }
    
    /**
     * Generate a visitor/user access token. This also applies to the 
     * administrator who sets the main and bot accounts...  
     * 
     * @param $code - [string] String of auth code used to grant authorization
     * 
     * @return array $token - The generated token and the array of all scopes returned with the token, keyed.
     * 
     * @version 5.3
     */
    public function request_user_access_token( $code, $requesting_function = null ){
        $endpoint = add_query_arg( array( 'client_id' => twitchpress_get_app_id() ), 'https://id.twitch.tv/oauth2/token' );
       
        $request_array = array(
            "headers" =>
                array(
                  "Authorization" => 'Bearer ' . twitchpress_get_app_token(),
                  "Client-ID" => twitchpress_get_app_id(),
                ),
            "method" => "POST",
            "body" =>
                array(
                  "client_id"     => twitchpress_get_app_id(),
                  "client_secret" => twitchpress_get_app_secret(),
                  "code"          => $code,
                  "grant_type"    => "authorization_code",
                  "redirect_uri"  => twitchpress_get_app_redirect(),
                ),
            "user-agent" => "curl/" . $this->curl_version,
            "stream" => false,
            "filename" => false,
            "decompress" => false           
        );
        
        $WP_Http_Curl_Object = new WP_Http_Curl();
       
        $response = $WP_Http_Curl_Object->request( $endpoint, $request_array );       

        if( !is_wp_error( $response) && isset( $response['response']['code'] ) && $response['response']['code'] == 200 ) 
        {
            if( isset( $response['body'] ) ) {
                return json_decode( $response['body'] );
            }
        } 
        else
        {
            // Prepare meta data for investigation into the response...
            $decoded_body = json_decode( $response['body'] );
  
            $call_values = array(
                'date'             => $response['headers']['date'],
                'response_code'    => $response['response']['code'],
                'response_message' => $response['response']['message'],
                'body_status'      => $decoded_body->status,
                'body_message'     => $decoded_body->message,
                'cliend_id'        => $request_array['body']['client_id'],
                'code'             => $code,
                'redirect_uri'     => $request_array['body']['redirect_uri'],
                'function'         => $requesting_function
            );
            
            // Generate fault report...
            $actual_code = '';
            if( isset( $response['response']['code'] ) ){ $actual_code = $response['response']['code']; }
            
            if( class_exists( 'BugNet_API_Net' ) ) {
                $bugnet_api_net = new BugNet_API_Net();
                $bugnet_api_net->report_call( 
                    'twitch', 
                    false,
                    $endpoint, 
                    sprintf( __( 'Code was [%s] instead of 200', 'twitchpress' ), $actual_code ), 
                    __( 'Requesting User Access Token', 'twitchpress' ), 
                    $call_values 
                ); 
                   
                unset($bugnet_api_net);
            }
        }       
    }
                       
    /**
     * Checks a token for validity and access grants available.
     * 
     * @return array $result if token is still valid, else false.  
     * 
     * @version 5.2
     * 
     * @deprecated this has not been a great approach, new approach coming October 2018
     */    
    public function check_application_token(){                    
        $url = 'https://id.twitch.tv/oauth2/validate';
        $post = array( 
            'oauth_token' => $this->twitch_client_token, 
            'client_id'   => twitchpress_get_app_id(),          
        );

        $result = json_decode( $this->cURL_get( $url, $post, array(), false, __FUNCTION__ ), true );                   
        
        if ( isset( $result['token']['valid'] ) && $result['token']['valid'] )
        {      
            return $result;
        } 
        else 
        {
             return false;
        }
        
        return false;     
    }        
                   
    /**
     * Checks a user oAuth2 token for validity.
     * 
     * @param $authToken - [string] The token that you want to check
     * 
     * @return $authToken - [array] Either the provided token and the array of scopes if it was valid or false as the token and an empty array of scopes
     * 
     * @version 9.0
     */    
    public function validate_user_token( $wp_user_id ){
        // Get the giving users token. 
        $user_token = twitchpress_get_user_token( $wp_user_id );
                    
        if( !$user_token ){ return false;}
        
        $endpoint = 'https://id.twitch.tv/oauth2/validate';
               
        $this->curl( __FILE__, __FUNCTION__, __LINE__, $endpoint, 'GET' );
           
        $this->curl_object->add_headers( array(
            "Authorization" => 'OAuth ' . $user_token,        
        ) );
             
        $this->call();
                  
        $result = $this->curl_object->curl_reply;
        if( is_wp_error( $result ) ) { return false; }
        
        $token = array();

        if ( isset( $result['response']['code'] ) && $result['response']['code'] == 200 )
        {      
            $token['token'] = $user_token;
        } 
        else 
        {
            $token['token'] = false;
        }
             
        return $token;     
    }

    /**
    * Establish an application token.
    * 
    * This method will check the existing token.
    * Existing token invalid, it will request a new one. 
    * Various values can be replaced during this procedure to help
    * generate debugging information for users.  
    * 
    * @param mixed $old_token
    * 
    * @returns array $result if token valid, else returns the return from request_app_access_token(). 
    * 
    * @version 5.0
    * 
    * @deprecated a new approach that relies on the access token expiry
    */
    public function establish_application_token( $function ) {     
        $result = $this->check_application_token();  

        // check_application_token() makes a call and if token invalid the following values will not be returned by the API
        if ( !isset( $result['token']['valid'] ) || !$result['token']['valid'] ){
            return $this->request_app_access_token( $function . ' + ' . __FUNCTION__ );
        }
                                  
        return $result;
    }
    
    /**
    * Establish current user token or token on behalf of a user who has
    * giving permission for extended sessions.
    * 
    * Step 1: Validate existing token string. 
    * Step 2: Refresh an invalid token string using existing refresh key. 
    * Step 3: Attempt to generate a new token without a refresh key. 
    * Step 4: Update users Twitch credentials.
    * Step 5: When user is keyholder update update main channel credentails. 
    * 
    * @returns array $result if token valid, else returns the return from request_app_access_token(). 
    * 
    * @version 3.0
    */
    public function establish_user_token( $wp_user_id ) {     
    
        // Validate the existing user token... 
        $result = $this->validate_user_token( $wp_user_id );  
                                   
        // ...token was returned (rather than false) because it is valid...
        if( isset( $result['token'] ) && $result['token'] !== false )
        {      
            return $result['token'];    
        }
        else // ...false was returned so we refresh the token...
        {                             
            $new_access_credentials = $this->refresh_token_by_userid( $wp_user_id );
            $code = twitchpress_get_user_code( $wp_user_id );              
            $authtime = time(); 
                         
            if( !$new_access_credentials )
            {
                // Refresh failed - attempt to request a new token.
                $code = twitchpress_get_user_code( $wp_user_id ); 

                $new_access_credentials = $this->request_user_access_token( $code, __FUNCTION__ );
                
                if( !$new_access_credentials ) { return false; }
                
                $access_token  = $new_access_credentials['access_token'];
                $refresh_token = $new_access_credentials['refresh_token'];
                $expires_in    = $new_access_credentials['expires_in'];
                $scope         = $new_access_credentials['scope']; 
            }
            else
            {
                $access_token  = $new_access_credentials->access_token;
                $refresh_token = $new_access_credentials->refresh_token;
                $expires_in    = $new_access_credentials->expires_in;
                $scope         = $new_access_credentials->scope;                
            }
            
            // Update the keyholders own channel user-data...
            twitchpress_update_users_twitch_data( $wp_user_id, array(
                'code'          => $code,
                'access_token'  => $access_token,
                'refresh_token' => $refresh_token,
                'expires_in'    => $expires_in,
                'authtime'      => $authtime,
                'scope'         => $scope
            ) );
       
            // Also update the main channels credentials if user is keyholder...
            if( $wp_user_id == 1 ) { 
                twitchpress_update_main_channels_token( $access_token ); 
                twitchpress_update_main_channels_refresh_token( $refresh_token );
                twitchpress_update_main_channels_scopes( $scope );                    
                twitchpress_update_main_channels_authtime( $authtime );                    
                twitchpress_update_main_channels_expires_in( $expires_in );                                        
            }
            
            return $new_access_credentials->access_token;
        }
    }
    
    /**
    * Refreshes an existing token to extend a session. 
    * 
    * @link https://dev.twitch.tv/docs/authentication#refreshing-access-tokens
    * 
    * @version 3.0
    * 
    * @param integer $wp_user_id
    */
    public function refresh_token_by_userid( $wp_user_id ) {          
        $token_refresh = twitchpress_get_user_token_refresh( $wp_user_id );
        if( !$token_refresh ) { return false; }
        
        $endpoint = 'https://id.twitch.tv/oauth2/token';
        
        $endpoint = add_query_arg( array( 
            'client_secret' => twitchpress_get_app_secret(),
            'grant_type'    => 'refresh_token',
            'refresh_token' => $token_refresh 
        ), $endpoint );

        $this->curl( __FILE__, __FUNCTION__, __LINE__, $endpoint, 'POST' );
        $this->curl_object->client_secret = twitchpress_get_app_secret();
        $this->curl_object->scope = twitchpress_prepare_scopes( twitchpress_get_visitor_scopes() );
        $this->call();

        if( $this->curl_object->call_result == true )
        {      
            return $this->curl_object->curl_reply_body;
        }
        else
        {          
            return false;    
        }
    } 

    /**
    * A method for administration accounts (not visitors). Call this when
    * all credentails are presumed ready in options table. Can pass $account
    * value to change which credentials are applied.
    * 
    * Recommended for admin requests as it generates notices.  
    * 
    * @author Ryan Bayne
    * @version 1.2
    */
    public function start_twitch_session_admin( $account = 'main' ) {
        // Can change from the default "main" credentails. 
        if( $account !== 'main' ) {
            //self::set_application_credentials( $app = 'main' );
        }

        // The plugin will bring the user to their original admin view using the redirectto value.
        $state = array( 'redirectto' => admin_url( '/admin.php?page=twitchpress&tab=twitch&amp;' . 'section=entermaincredentials' ),
                        'userrole'   => 'administrator',
                        'outputtype' => 'admin' 
        );

        wp_redirect( twitchpress_generate_authorization_url( twitchpress_get_global_accepted_scopes(), $state ) );
        exit;                       
    }      
    
    public function get_main_streamlabs_user() {
        $url = 'https://streamlabs.com/api/v1.0/user?access_token=' . $this->get_main_access_token();
     
        // Call Parameters
        $request_body = array(
            'client_id'        => $this->streamlabs_app_id,
            'client_secret'    => $this->streamlabs_app_secret,
            'redirect_uri'     => $this->streamlabs_app_uri,
        );                           

        $curl = new WP_Http_Curl();

        $response = $curl->request( $url, 
            array( 
                'method'     => 'GET', 
                'body'       => $request_body,
                'user-agent' => 'curl/' . $this->curl_version,
                'stream'     => false,
                'filename'   => false,
                'decompress' => false 
            ) 
        );

        if( isset( $response['response']['code'] ) && $response['response']['code'] == 200 ) {
            if( isset( $response['body'] ) ) {
                $response_body = json_decode( $response['body'] );
                return $response_body;
            }
        }
         
        return false;  
    }
    
    /**
     * Gets the channel object that belongs to the giving token.
     * 
     * @param $token - [string] Authentication key used for the session
     * @param $code - [string] Code used to generate an Authentication key
     * 
     * @return $object - [array] Keyed array of all channel data
     * 
     * @version 5.3
     */ 
    public function get_tokens_channel( $token ){        
                                 
        // Ensure required scope is permitted else we return the WP_Error confirm_scope() generates.
        $confirm_scope = twitchpress_confirm_scope( 'channel_read', 'channel', __FUNCTION__ );
        if( is_wp_error( $confirm_scope) ) { return $confirm_scope; }
        
        $endpoint = 'https://api.twitch.tv/helix/channel';
        $this->curl( __FILE__, __FUNCTION__, __LINE__, $endpoint, 'GET' );
        $this->call();
        
        return $this->curl_object->curl_reply_response;
    }  
    
    /**
    * Uses a users own Twitch code and token to get their subscription
    * status for the sites main/default channel.
    * 
    * @param mixed $user_id
    * 
    * @version 4.0
    */
    public function is_user_subscribed_to_main_channel( $user_id ) {
        if( !$credentials = twitchpress_get_user_twitch_credentials( $user_id ) ) {
            return null;    
        }        
     
        return $this->get_broadcaster_subscriptions( 
            twitchpress_get_main_channels_twitchid(),  
            twitchpress_get_user_twitchid_by_wpid($user_id)
        );    
    } 
    
    /**
    * Get Bits Leaderboard 
    * 
    * Gets a ranked list of Bits leaderboard information 
    * for an authorized broadcaster.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-bits-leaderboard
    * @version 1.0 
    * 
    * @param mixed $count
    * @param mixed $period
    * @param mixed $started_at
    * @param mixed $user_id
    * 
    * @version 1.0
    */
    public function get_bits_leaderboard( $count = null, $period = null, $started_at = null, $user_id = null ) {

        $call_authentication = 'scope';
        
        $scope = 'bits:read';

        $endpoint = 'https://api.twitch.tv/helix/bits/leaderboard';
        
        // Establishes headers...
        $this->curl( __FILE__, __FUNCTION__, __LINE__, $endpoint, 'GET' ); 
        
        // Initiates call and does some initial response processing...
        $this->call(); 
           
        return $this->curl_object->curl_reply_body;        
    }
    
    /**
    * Get Clips
    * 
    * Gets clip information by clip ID (one or more), broadcaster ID (one only), 
    * or game ID (one only).
    * 
    * The response has a JSON payload with a data field containing an array 
    * of clip information elements and a pagination field containing 
    * information required to query for more streams.
    * 
    * @param mixed $broadcaster_id
    * @param mixed $game_id
    * @param mixed $id
    * @param mixed $after
    * @param mixed $before
    * @param mixed $ended_at
    * @param mixed $first
    * @param mixed $started_at
    * 
    * @version 1.0
    */
    public function get_clips( $broadcaster_id, $game_id, $id, $after = null, $before = null, $ended_at = null, $first = null, $started_at = null ) {

        $call_authentication = 'none';

        $endpoint = 'https://api.twitch.tv/helix/clips'; 
        
        if( $broadcaster_id ) { $endpoint = add_query_arg( 'broadcaster_id', $broadcaster_id, $endpoint ); }
        if( $game_id ) { $endpoint = add_query_arg( 'game_id', $game_id, $endpoint ); }
        if( $id ) { $endpoint = add_query_arg( 'id', $id, $endpoint ); }
                         
        // Establishes headers...
        $this->curl( __FILE__, __FUNCTION__, __LINE__, $endpoint, 'GET' ); 
        
        // Initiates call and does some initial response processing...
        $this->call(); 
           
        return $this->curl_object->curl_reply_body;          
    }
         
    /**
    * Get Games
    * 
    * Gets game information by game ID or name. The response has a JSON 
    * payload with a data field containing an array of games elements.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-games
    * 
    * @param mixed $id
    * @param mixed $name
    * @param mixed $box_art_url
    * 
    * @version 1.0
    */
    public function get_games( $id, $name ) {

        $call_authentication = 'none';

        $endpoint = 'https://api.twitch.tv/helix/games';  
        
        if( $id ) { $endpoint = add_query_arg( 'id', $id, $endpoint ); }
        if( $name ) { $endpoint = add_query_arg( 'name', str_replace( ' ', '%20', $name ), $endpoint ); }
        
        $this->curl( __FILE__, __FUNCTION__, __LINE__, $endpoint, 'GET' );    
        
        $this->call();

        return $this->curl_object->curl_reply_body;                 
    }
         
    /**
    * Get Top (viewed) Games
    * 
    * Gets games sorted by number of current viewers on Twitch, most popular first.
    * 
    * The response has a JSON payload with a data field containing an array 
    * of games information elements and a pagination field containing 
    * information required to query for more streams.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-top-games
    * 
    * @param mixed $after
    * @param mixed $before
    * @param mixed $first
    * 
    * @version 1.0
    */
    public function get_top_games( $after = null, $before = null, $first = 10 ) {
        $call_authentication = 'none';

        $endpoint = 'https://api.twitch.tv/helix/games/top?first=' . $first;    

        $this->curl( __FILE__, __FUNCTION__, __LINE__, $endpoint, 'GET' ); 
        
        $this->call( 'GET', $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' ); 
           
        return $this->curl_object->curl_reply_body;
    }
    
    /**
    * Get a single stream using Twitch user ID or channel ID.
    * 
    * @uses get_streams()
    * 
    * @param mixed $twitch_user_id
    * 
    * @version 1.0
    */
    public function get_stream_by_userid( $twitch_user_id ) {
        $result = $this->get_streams( null, null, null, null, null, null, $twitch_user_id, null );     
    
        if( isset( $result->data[0] ) && !empty( $result->data[0] ) ) {
            return $result->data[0];
        }
        
        return false;
    }    
    
    /**
    * Get two or more streams using an array of Twitch user ID or channel ID.
    * 
    * @uses get_streams()
    * 
    * @param mixed $twitch_user_id
    * 
    * @version 2.0 Now splits array $twitch_user_id into chunks of 100 and performs multiple calls (due to API limit)
    */
    public function get_streams_by_userid( $twitch_user_id = array() ) {
        
        $streams_array = array();
        
        $chunks = array_chunk( $twitch_user_id, 100, false );
        
        foreach( $chunks as $group ) {
        
            $result = $this->get_streams( null, null, null, null, null, null, $group, null );     

            if( isset( $result->data[0] ) && !empty( $result->data[0] ) ) {
                $streams_array = array_merge( $streams_array, $result->data );
            }            
        }
        
        if( $streams_array ) { return $streams_array; }
        return false;
    }
    
    /**
    * Get all of the giving broadcasters subscribers... 
    * 
    * (this is the original method that matches the documented endpoint)
    * (see additional methods below this one for stricter queries)
    * 
    * The current user is determined by the OAuth token provided in the Authorization header.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-broadcaster-subscriptions
    * 
    * @version 4.0
    * 
    * @param mixed $broadcaster_id
    * @param mixed $subscribers_twitch_id
    * @param mixed $after
    * @param mixed $first
    */
    public function get_broadcaster_subscriptions( $broadcaster_id, $subscribers_twitch_id = null, $after = null, $first = 100 ) {
        $scope = 'channel:read:subscriptions';
        $endpoint = 'https://api.twitch.tv/helix/subscriptions'; 
        $endpoint = add_query_arg( array( 'broadcaster_id' => $broadcaster_id ), $endpoint );

        if( $subscribers_twitch_id ) { $endpoint = add_query_arg( array( 'user_id' => $subscribers_twitch_id ), $endpoint ); }
        if( $after ) { $endpoint = add_query_arg( array( 'after' => $after ), $endpoint ); }
        if( $first ) { $endpoint = add_query_arg( array( 'first' => $first ), $endpoint ); }
        
        $this->curl( __FILE__, __FUNCTION__, __LINE__, 'GET', $endpoint, 'helix', true, 'mainchannel' ); 
        $this->call(); 

        return $this->curl_object->curl_reply_body;           
    }
    
    public function get_user( $login_name, $plus_email = false ) {
        if( $plus_email ) {
            return $this->get_user_plus_email_by_login_name( $login_name );
        }
        
        return $this->get_user_without_email_by_login_name( $login_name );    
    }    
    
    public function get_user_by_id( $twitch_user_id ) {
        $endpoint = 'https://api.twitch.tv/helix/users?id=' . $twitch_user_id;
        $this->curl( __FILE__, __FUNCTION__, __LINE__, $endpoint, 'GET' );
        $this->call();
        return $this->curl_object->curl_reply_body;    
    }   
    
    public function get_channel_id_by_name( $channel_name ) {
        $response = $this->get_user_without_email_by_login_name( $channel_name );  
        if( isset( $response->data[0]->id ) ) { return $response->data[0]->id; }
        return false;      
    }
    
    public function get_user_by_bearer_token( $bearer_token ) {
   
        $call_authentication = 'scope';
        
        $endpoint = 'https://api.twitch.tv/helix/users';
        
        $this->curl( __FILE__, __FUNCTION__, __LINE__, $endpoint, 'GET' ); 

        $this->curl_object->scope = 'user:read:email';

        // Bearer token header "Authorization" is usually already set...
        unset( $this->curl_object->headers['Authorization']);
        
        $this->curl_object->add_headers( array(
            'Authorization' => 'Bearer ' . $bearer_token,
        ) );

        $this->call();    
                    
        return $this->curl_object->curl_reply_body->data[0];
    }
      
    /**
    * Get a user using login name without using scope. Using scope would get the
    * users email address also. 
    * 
    * Gets information about one or more specified Twitch users. 
    * Users are identified by optional user IDs and/or login name. 
    * If neither a user ID nor a login name is specified, the user is 
    * looked up by Bearer token.
    * 
    * The response has a JSON payload with a data field containing an array 
    * of user-information elements. If this is provided, the response 
    * includes the user’s email address.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-users
    * 
    * @param mixed $id
    * @param mixed $login
    * 
    * @version 6.0
    */
    public function get_user_without_email_by_login_name( $login_name ) {
 
        $endpoint = 'https://api.twitch.tv/helix/users?login=' . $login_name . '&login=' . $login_name;
        
        $this->curl( __FILE__, __FUNCTION__, __LINE__, $endpoint, 'GET' ); 
        
        $this->call();
  
        return $this->curl_object->curl_reply_body;          
    }
    
    /**
    * Get a user using login name without using scope. Using scope would get the
    * users email address also. 
    * 
    * Gets information about one or more specified Twitch users. 
    * Users are identified by optional user IDs and/or login name. 
    * If neither a user ID nor a login name is specified, the user is 
    * looked up by Bearer token.
    * 
    * The response has a JSON payload with a data field containing an array 
    * of user-information elements. If this is provided, the response 
    * includes the user’s email address.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-users
    * 
    * @param mixed $id
    * @param mixed $login
    * 
    * @version 6.0
    */
    public function get_user_plus_email_by_login_name( $login_name ) {
 
        $call_authentication = 'scope';
        
        $endpoint = 'https://api.twitch.tv/helix/users?login=' . $login_name . '&login=' . $login_name;
        
        $this->curl( __FILE__, __FUNCTION__, __LINE__, $endpoint, 'GET' ); 
        
        $this->curl_object->scope = 'user:read:email';
        
        return $this->call();          
    }

    /**
    * Get Users Follows [from giving ID]
    * 
    * Gets information on follow relationships between two Twitch users. 
    * Information returned is sorted in order, most recent follow first. 
    * This can return information like “who is lirik following,�?, 
    * “who is following lirik,�? or “is user X following user Y.�?
    * 
    * The response has a JSON payload with a data field containing an array 
    * of follow relationship elements and a pagination field containing 
    * information required to query for more follow information.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-users-follows
    * 
    * @param mixed $after
    * @param mixed $first Maximum number of objects to return. Maximum: 100. Default: 20.
    * @param mixed $from_id User ID. The request returns information about users who are being followed by the from_id user.
    * @param mixed $to_id User ID. The request returns information about users who are following the to_id user.
    * 
    * @version 1.0
    */
    public function get_users_follows( $after = null, $first = null, $from_id = null, $to_id = null ) {
        $endpoint = 'https://api.twitch.tv/helix/users/follows';  
        
        if( $after ) { $endpoint = add_query_arg( array( 'after' => $after ), $endpoint ); }
        if( $first ) { $endpoint = add_query_arg( array( 'first' => $first ), $endpoint ); }
        if( $from_id ) { $endpoint = add_query_arg( array( 'from_id' => $from_id ), $endpoint ); }
        if( $to_id ) { $endpoint = add_query_arg( array( 'to_id' => $to_id ), $endpoint ); }

        $this->curl( __FILE__, __FUNCTION__, __LINE__, $endpoint, 'GET' ); 

        $this->call( 'GET', $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' ); 
                             
        return $this->curl_object->curl_reply_body;        
    }
                   
    /**
    * The request returns information about users who are being followed by the from_id user.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-users-follows
    * 
    * @param mixed $after
    * @param mixed $first
    * @param mixed $from_id
    * 
    * @version 1.0
    */
    public function get_users_follows_from_id( $after = null, $first = null, $from_id = null ) {
        $endpoint = 'https://api.twitch.tv/helix/users/follows';  
        
        if( $after ) { $endpoint = add_query_arg( array( 'after' => $after ), $endpoint ); }
        if( $first ) { $endpoint = add_query_arg( array( 'first' => $first ), $endpoint ); }
        if( $from_id ) { $endpoint = add_query_arg( array( 'from_id' => $from_id ), $endpoint ); }
 
        $this->curl( __FILE__, __FUNCTION__, __LINE__, $endpoint, 'GET' ); 

        $this->call( 'GET', $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' ); 
                             
        return $this->curl_object->curl_reply_body;         
    }
            
    /**
    * The request returns information about users who are following the to_id user.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-users-follows
    * 
    * @param mixed $after
    * @param mixed $first
    * @param mixed $to_id
    * 
    * @version 2.0
    */
    public function get_users_follows_to_id( $after = null, $first = null, $to_id = null ) {
        $endpoint = 'https://api.twitch.tv/helix/users/follows';  
                                     
        if( $after ) { $endpoint = add_query_arg( array( 'after' => $after ), $endpoint ); }
        if( $first ) { $endpoint = add_query_arg( array( 'first' => $first ), $endpoint ); }
        if( $to_id ) { $endpoint = add_query_arg( array( 'to_id' => $to_id ), $endpoint ); }

        $this->curl( __FILE__, __FUNCTION__, __LINE__, $endpoint, 'GET' ); 

        $this->call( 'GET', $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' ); 
                      
        return $this->curl_object->curl_reply_body;         
    }

    /**
     * Gets a list of users that follow the specified broadcaster. You can also use this endpoint to see whether a specific user follows the broadcaster.
     *
     * Endpoint: https://dev.twitch.tv/docs/api/reference/#get-channel-followers
     * 
     * @param integer|null $after
     * @param integer|null $first
     * @param integer|null $user_id 
     * 
     * @return object curl body 
     * 
     * @version 1.0.0
     */
    public function get_channels_followers( int $broadcaster_id, int $after = null, int $first = null, int $user_id = null ) {
        $endpoint = 'https://api.twitch.tv/helix/channels/followers';  
                                     
        if( $after ) { $endpoint = add_query_arg( array( 'broadcaster_id' => $broadcaster_id ), $endpoint ); }
        if( $after ) { $endpoint = add_query_arg( array( 'after' => $after ), $endpoint ); }
        if( $first ) { $endpoint = add_query_arg( array( 'first' => $first ), $endpoint ); }
        if( $user_id ) { $endpoint = add_query_arg( array( 'user_id' => $user_id ), $endpoint ); }

        $this->curl( __FILE__, __FUNCTION__, __LINE__, $endpoint, 'GET' ); 

        $this->call( 'GET', $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' ); 
                      
        return $this->curl_object->curl_reply_body;         
    }
         
    /**
    * Get Videos
    * 
    * Gets video information by video ID (one or more), user ID (one only), 
    * or game ID (one only).
    * 
    * The response has a JSON payload with a data field containing an array 
    * of video elements. For lookup by user or game, pagination is available, 
    * along with several filters that can be specified as query string parameters.
    * 
    * @link https://dev.twitch.tv/docs/api/reference/#get-videos
    * 
    * @param mixed $id
    * @param mixed $user_id
    * @param mixed $game_id
    * @param mixed $after
    * @param mixed $before
    * @param mixed $first
    * @param mixed $language
    * @param mixed $period
    * @param mixed $sort
    * @param mixed $type
    * 
    * @version 1.0
    */
    public function get_videos( $id = null, $user_id = null, $game_id = null, $after = null, $before = null, $first = null, $language = null, $period = null, $sort = null, $type = null ) {
  
        $call_authentication = 'none';

        $endpoint = 'https://api.twitch.tv/helix/videos';          
        
        if( $id ) { $endpoint = add_query_arg( array( 'id' => $id ), $endpoint ); }
        if( $user_id ) { $endpoint = add_query_arg( array( 'user_id' => $user_id ), $endpoint ); }
        if( $game_id ) { $endpoint = add_query_arg( array( 'game_id' => $game_id ), $endpoint ); }
        if( $after ) { $endpoint = add_query_arg( array( 'after' => $after ), $endpoint ); }
        if( $before ) { $endpoint = add_query_arg( array( 'before' => $before ), $endpoint ); }
        if( $first ) { $endpoint = add_query_arg( array( 'first' => $first ), $endpoint ); }
        if( $language ) { $endpoint = add_query_arg( array( 'language' => $language ), $endpoint ); }
        if( $period ) { $endpoint = add_query_arg( array( 'period' => $period ), $endpoint ); }
        if( $sort ) { $endpoint = add_query_arg( array( 'sort' => $sort ), $endpoint ); }
        if( $type ) { $endpoint = add_query_arg( array( 'type' => $type ), $endpoint ); }
     
        $this->curl( __FILE__, __FUNCTION__, __LINE__, $endpoint, 'GET' ); 

        $this->call( 'GET', $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' ); 
                             
        return $this->curl_object->curl_reply_body;
    }
    
    public function webhook_new( $headers, $callback, $mode, $topic, $lease_seconds = null, $secret = null ) {
        
        $call_authentication = 'none';

        $endpoint = 'https://api.twitch.tv/helix/webhooks/hub';     
        
        $this->curl( __FILE__, __FUNCTION__, __LINE__, 'POST', $endpoint );    

        $this->curl_object->add_headers( array(
            'hub.callback'      => $callback,
            'hub.mode'          => $mode,
            'hub.topic'         => $topic,
            'hub.lease_seconds' => $lease_seconds,
            'hub.secret'        => $secret,
        ) );
        
        $this->curl_object->set_curl_body( $headers );
                       
        $this->call();
                     
        return $this->curl_object->curl_reply_body; 
    } 
    
    public function webhook_new_user_follows( $first, $callback, $mode, $topic, $from_id = null, $to_id = null, $lease_seconds = null, $secret = null ) {
        
        $headers = array(
            'first'   => $first,
            'from_id' => $from_id,
            'to_id'   => $to_id 
        );
        
        return $this->webhook_new( $headers, $callback, $mode, $topic, $lease_seconds = null, $secret = null );    
    } 

    /**
    * Get a team and team members...
    * 
    * @param mixed $team_name
    * 
    * @version 2.0
    */
    public function get_team( $team_name ) {
        $endpoint = 'https://api.twitch.tv/helix/teams';
        $endpoint = add_query_arg( array( 'name' => $team_name ), $endpoint );            
        $this->curl( __FILE__, __FUNCTION__, __LINE__, $endpoint, 'GET' ); 
        $this->call( 'GET', $endpoint, __FILE__, __FUNCTION__, __LINE__, 'automatic' );               
        return $this->curl_object->curl_reply_body;
    }
    
    /**
    * Starts a commercial on a specified channel.
    * 
    * @link https://dev.twitch.tv/docs/api/reference#start-commercial
    * 
    * @param mixed $broadcaster_id
    * @param mixed $length
    * 
    * @version 1.0
    */
    public function start_commercial( $broadcaster_id, $length = 30 ) {

        $endpoint = 'https://api.twitch.tv/helix/channels/commercial';
        $endpoint = add_query_arg( array( 
            'broadcaster_id' => $broadcaster_id,
            'length'         => $length, 
        ), $endpoint );

        $this->curl( __FILE__, __FUNCTION__, __LINE__, 'POST', $endpoint );
        
        $token = twitchpress_get_main_channels_token();
        
        $this->curl_object->add_headers( array(
            "Authorization" => 'Bearer ' . $token,        
        ) );
        
        $this->call();

        return json_decode( $this->curl_object->curl_reply['body'] );        
    }
    
    /**
    * put your comment there...
    * 
    * @param mixed $broadcaster_user_id
    */
    public function eventsub_channel_update( $wp_post_id, $broadcaster_user_id ) { 
        $scope = 'none';
        $endpoint = 'https://api.twitch.tv/helix/eventsub/subscriptions';
        $method = 'POST';
        $type = 'channel.update';  

        $this->curl( __FILE__, __FUNCTION__, __LINE__, $method, $endpoint ); 
        
        $this->curl_object->add_headers( array(
            'Client-ID'     => twitchpress_get_app_id(),
            'Authorization' => 'Bearer ' . twitchpress_get_app_token(),
        ) );
        
        $this->curl_object->curl_request_body = array(
            'type'      => $type,
            'version'   => '1',
            'condition' => array(
                'broadcaster_user_id' => twitchpress_get_main_channels_twitchid(),
            ),
            'transport' => array(
                'method'   => 'webhook',
                'callback' => TWITCHPRESS_WEBHOOK_CALLBACK,
                'secret'   => $wp_post_id . '_' . str_replace( '.', '_', $type ) . '_' . twitchpress_random14()
            )
        );
    
        $this->call(); 
                                    
        return $this->curl_object->curl_reply_body; 
        
        # Channel Update Notification Example
        /* {
            "subscription": {
                "id": "f1c2a387-161a-49f9-a165-0f21d7a4e1c4",
                "type": "channel.update",
                "version": "1",
                "status": "enabled",
                "cost": 0,
                "condition": {
                   "broadcaster_user_id": "1337"
                },
                 "transport": {
                    "method": "webhook",
                    "callback": "https://example.com/webhooks/callback"
                },
                "created_at": "2019-11-16T10:11:12.123Z"
            },
            "event": {
                "broadcaster_user_id": "1337",
                "broadcaster_user_login": "cool_user",
                "broadcaster_user_name": "Cool_User",
                "title": "Best Stream Ever",
                "language": "en",
                "category_id": "21779",
                "category_name": "Fortnite",
                "is_mature": false
            }
        } */                                          
    }
    
    public function eventsub_channel_follow() {
        $call_authentication = 'none';
        $endpoint = 'https://api.twitch.tv/helix/eventsub/subscriptions';
        $method = 'POST';
        $type = 'channel.follow';  
        
        /*
                    Channel Follow Request Body
                    Name    Type    Required?    Description
                    type    string    yes    The subscription type name: channel.follow.
                    version    string    yes    The subscription type version: 1.
                    condition     condition     yes    Subscription-specific parameters.
                    transport     transport     yes    Transport-specific parameters.
                    Channel Follow Webhook Example
                    {
                        "type": "channel.follow",
                        "version": "1",
                        "condition": {
                            "broadcaster_user_id": "1337"
                        },
                        "transport": {
                            "method": "webhook",
                            "callback": "https://example.com/webhooks/callback",
                            "secret": "s3cRe7"
                        }
                    }
                    Channel Follow Notification Payload
                    Name    Type    Description
                    subscription     subscription     Metadata about the subscription.
                    event     event     The event information. Contains the user ID and user name of the follower and the broadcaster user ID and broadcaster user name.
                    Channel Follow Webhook Notification Example
                    {
                        "subscription": {
                            "id": "f1c2a387-161a-49f9-a165-0f21d7a4e1c4",
                            "type": "channel.follow",
                            "version": "1",
                            "status": "enabled",
                            "cost": 0,
                            "condition": {
                               "broadcaster_user_id": "1337"
                            },
                             "transport": {
                                "method": "webhook",
                                "callback": "https://example.com/webhooks/callback"
                            },
                            "created_at": "2019-11-16T10:11:12.123Z"
                        },
                        "event": {
                            "user_id": "1234",
                            "user_login": "cool_user",
                            "user_name": "Cool_User",
                            "broadcaster_user_id": "1337",
                            "broadcaster_user_login": "cooler_user",
                            "broadcaster_user_name": "Cooler_User",
                            "followed_at": "2020-07-15T18:16:11.17106713Z"
                        }
                    }  */            
    }
    
    public function eventsub_channel_subscribe() {
        $call_authentication = 'channel:read:subscriptions';
        $endpoint = 'https://api.twitch.tv/helix/eventsub/subscriptions';
        $method = 'POST';
        $type = 'channel.subscribe';  
        
        
        /*
                            Channel Subscribe Request Body
                    Name    Type    Required?    Description
                    type    string    yes    The subscription type name: channel.subscribe.
                    version    string    yes    The subscription type version: 1.
                    condition     condition     yes    Subscription-specific parameters.
                    transport     transport     yes    Transport-specific parameters.
                    Channel Subscribe Webhook Example
                    {
                        "type": "channel.subscribe",
                        "version": "1",
                        "condition": {
                            "broadcaster_user_id": "1337"
                        },
                        "transport": {
                            "method": "webhook",
                            "callback": "https://example.com/webhooks/callback",
                            "secret": "s3cRe7"
                        }
                    }
                    Channel Subscribe Notification Payload
                    Name    Type    Description
                    subscription     subscription     Metadata about the subscription.
                    event     event     The event information. Contains the user ID and user name of the subscriber, the broadcaster user ID and broadcaster user name, and whether the subscription is a gift.
                    Channel Subscribe Webhook Notification Example
                    {
                        "subscription": {
                            "id": "f1c2a387-161a-49f9-a165-0f21d7a4e1c4",
                            "type": "channel.subscribe",
                            "version": "1",
                            "status": "enabled",
                            "cost": 0,
                            "condition": {
                               "broadcaster_user_id": "1337"
                            },
                             "transport": {
                                "method": "webhook",
                                "callback": "https://example.com/webhooks/callback"
                            },
                            "created_at": "2019-11-16T10:11:12.123Z"
                        },
                        "event": {
                            "user_id": "1234",
                            "user_login": "cool_user",
                            "user_name": "Cool_User",
                            "broadcaster_user_id": "1337",
                            "broadcaster_user_login": "cooler_user",
                            "broadcaster_user_name": "Cooler_User",
                            "tier": "1000",
                            "is_gift": false
                        }
                    }  
                    
                    */    
    }
    
    public function eventsub_channel_cheer() {
        $call_authentication = 'bits:read';
        $endpoint = 'https://api.twitch.tv/helix/eventsub/subscriptions';
        $method = 'POST';
        $type = 'channel.cheer';   
        
        
        
        /*
                    Channel Cheer Request Body
                    Name    Type    Required?    Description
                    type    string    yes    The subscription type name: channel.cheer.
                    version    string    yes    The subscription type version: 1.
                    condition     condition     yes    Subscription-specific parameters. Pass in the broadcaster user ID for the channel you want to receive cheer notifications for.
                    transport     transport     yes    Transport-specific parameters.
                    Channel Cheer Webhook Example
                    {
                        "type": "channel.cheer",
                        "version": "1",
                        "condition": {
                            "broadcaster_user_id": "1337"
                        },
                        "transport": {
                            "method": "webhook",
                            "callback": "https://example.com/webhooks/callback",
                            "secret": "s3cRe7"
                        }
                    }
                    Channel Cheer Notification Payload
                    Name    Type    Description
                    subscription     subscription     Metadata about the subscription.
                    event     event     The event information. Contains the user ID and user name of the cheering user along with the broadcaster user id and broadcaster user name.
                    Channel Cheer Notification Example
                    {
                        "subscription": {
                            "id": "f1c2a387-161a-49f9-a165-0f21d7a4e1c4",
                            "type": "channel.cheer",
                            "version": "1",
                            "status": "enabled",
                            "cost": 0,
                            "condition": {
                                "broadcaster_user_id": "1337"
                            },
                             "transport": {
                                "method": "webhook",
                                "callback": "https://example.com/webhooks/callback"
                            },
                            "created_at": "2019-11-16T10:11:12.123Z"
                        },
                        "event": {
                            "is_anonymous": false,
                            "user_id": "1234",          // null if is_anonymous=true
                            "user_login": "cool_user",  // null if is_anonymous=true
                            "user_name": "Cool_User",   // null if is_anonymous=true
                            "broadcaster_user_id": "1337",
                            "broadcaster_user_login": "cooler_user",
                            "broadcaster_user_name": "Cooler_User",
                            "message": "pogchamp",
                            "bits": 1000
                        }
                    } 
                    
                    
                    */    
    }
    
    public function eventsub_channel_raid() {
        $call_authentication = 'none';
        $endpoint = 'https://api.twitch.tv/helix/eventsub/subscriptions';
        $method = 'POST';
        $type = 'channel.raid';   
        
        
        /*
                    Channel Raid Request Body
                    Name    Type    Required?    Description
                    type    string    yes    The subscription type name: channel.raid.
                    version    string    yes    The subscription type version: 1.
                    condition     condition     yes    Subscription-specific parameters. Pass in either from_broadcaster_user_id or to_broadcaster_user_id. If you pass in both parameters you will receive an error.
                    transport     transport     yes    Transport-specific parameters.
                    Channel Raid Webhook Example
                    {
                        "type": "channel.raid",
                        "version": "1",
                        "condition": {
                            "to_broadcaster_user_id": "1337" // could provide from_broadcaster_user_id instead
                        },
                        "transport": {
                            "method": "webhook",
                            "callback": "https://example.com/webhooks/callback",
                            "secret": "s3cRe7"
                        }
                    }
                    Channel Raid Notification Payload
                    Name    Type    Description
                    subscription     subscription     Metadata about the subscription.
                    event     event     The event information. Contains the from and to broadcaster information along with the number of viewers in the raid. Will only notify for raids that appear in chat.
                    Channel Raid Notification Example
                    {
                        "subscription": {
                            "id": "f1c2a387-161a-49f9-a165-0f21d7a4e1c4",
                            "type": "channel.raid",
                            "version": "1",
                            "status": "enabled",
                            "cost": 0,
                            "condition": {
                                "to_broadcaster_user_id": "1337"
                            },
                             "transport": {
                                "method": "webhook",
                                "callback": "https://example.com/webhooks/callback"
                            },
                            "created_at": "2019-11-16T10:11:12.123Z"
                        },
                        "event": {
                            "from_broadcaster_user_id": "1234",
                            "from_broadcaster_user_login": "cool_user",
                            "from_broadcaster_user_name": "Cool_User",
                            "to_broadcaster_user_id": "1337",
                            "to_broadcaster_user_login": "cooler_user",
                            "to_broadcaster_user_name": "Cooler_User",
                            "viewers": 9001
                        }
                    }*/     
    }
    
    public function eventsub_channel_ban() {
        $call_authentication = 'channel:moderate';
        $endpoint = 'https://api.twitch.tv/helix/eventsub/subscriptions';
        $method = 'POST';
        $type = 'channel.ban';    
                    /*         
                    Channel Ban Request Body
                    Name    Type    Required?    Description
                    type    string    yes    The subscription type name: channel.ban.
                    version    string    yes    The subscription type version: 1.
                    condition     condition     yes    Subscription-specific parameters. Pass in the broadcaster user ID for the channel you want to receive ban notifications for.
                    transport     transport     yes    Transport-specific parameters.
                    Channel Ban Webhook Example
                    {
                        "type": "channel.ban",
                        "version": "1",
                        "condition": {
                            "broadcaster_user_id": "1337"
                        },
                        "transport": {
                            "method": "webhook",
                            "callback": "https://example.com/webhooks/callback",
                            "secret": "s3cRe7"
                        }
                    }
                    Channel Ban Notification Payload
                    Name    Type    Description
                    subscription     subscription     Metadata about the subscription.
                    event     event     The event information. Will notify on timeouts as well as bans. Contains the user ID and user name of the banned user, the broadcaster user ID and broadcaster user name, the user ID and user name of the moderator who issued the ban/timeout, the reason, and expiration time if it is a timeout.
                    Channel Ban Notification Example
                    {
                        "subscription": {
                            "id": "f1c2a387-161a-49f9-a165-0f21d7a4e1c4",
                            "type": "channel.ban",
                            "version": "1",
                            "status": "enabled",
                            "cost": 0,
                            "condition": {
                                "broadcaster_user_id": "1337"
                            },
                             "transport": {
                                "method": "webhook",
                                "callback": "https://example.com/webhooks/callback"
                            },
                            "created_at": "2019-11-16T10:11:12.123Z"
                        },
                        "event": {
                            "user_id": "1234",
                            "user_login": "cool_user",
                            "user_name": "Cool_User",
                            "broadcaster_user_id": "1337",
                            "broadcaster_user_login": "cooler_user",
                            "broadcaster_user_name": "Cooler_User",
                            "moderator_user_id": "1339",
                            "moderator_user_login": "mod_user",
                            "moderator_user_name": "Mod_User",
                            "reason": "Offensive language",
                            "ends_at": "2020-07-15T18:16:11.17106713Z",
                            "is_permanent": false
                        }
                    }   */         
    }
    
    public function eventsub_channel_unban() {
        $call_authentication = 'channel:moderate';
        $endpoint = 'https://api.twitch.tv/helix/eventsub/subscriptions';
        $method = 'POST';
        $type = 'channel.unban';    
        
                   /*
                    Channel Unban Request Body
                    Name    Type    Required?    Description
                    type    string    yes    The subscription type name: channel.unban.
                    version    string    yes    The subscription type version: 1.
                    condition     condition     yes    Subscription-specific parameters. Pass in the broadcaster user ID for the channel you want to receive unban notifications for.
                    transport     transport     yes    Transport-specific parameters.
                    Channel Unban Webhook Example
                    {
                        "type": "channel.unban",
                        "version": "1",
                        "condition": {
                            "broadcaster_user_id": "1337"
                        },
                        "transport": {
                            "method": "webhook",
                            "callback": "https://example.com/webhooks/callback",
                            "secret": "s3cRe7"
                        }
                    }
                    Channel Unban Notification Payload
                    Name    Type    Description
                    subscription     subscription     Metadata about the subscription.
                    event     event     The event information. Contains the user ID and user name of the unbanned user, the broadcaster user id and broadcaster user name, as well as the user ID and user name of the moderator who issued the unban.
                    Channel Unban Notification Example
                    {
                        "subscription": {
                            "id": "f1c2a387-161a-49f9-a165-0f21d7a4e1c4",
                            "type": "channel.unban",
                            "version": "1",
                            "status": "enabled",
                            "cost": 0,
                            "condition": {
                                "broadcaster_user_id": "1337"
                            },
                             "transport": {
                                "method": "webhook",
                                "callback": "https://example.com/webhooks/callback"
                            },
                            "created_at": "2019-11-16T10:11:12.123Z"
                        },
                        "event": {
                            "user_id": "1234",
                            "user_login": "cool_user",
                            "user_name": "Cool_User",
                            "broadcaster_user_id": "1337",
                            "broadcaster_user_login": "cooler_user",
                            "broadcaster_user_name": "Cooler_User",
                             "moderator_user_id": "1339",
                            "moderator_user_login": "mod_user",
                            "moderator_user_name": "Mod_User"
                        }
                    }   */         
    }
    
    public function eventsub_channel_moderator_add() {
        $call_authentication = 'moderation:read';
        $endpoint = 'https://api.twitch.tv/helix/eventsub/subscriptions';
        $method = 'POST';
        $type = 'channel.moderator.add';   
        
                    /* 
                    Channel Moderator Add Request Body
                    Name    Type    Required?    Description
                    type    string    yes    The subscription type name: channel.moderator.add.
                    version    string    yes    The subscription type version: 1.
                    condition     condition     yes    Subscription-specific parameters. Pass in the broadcaster user ID for the channel you want to receive moderator addition notifications for.
                    transport     transport     yes    Transport-specific parameters.
                    Channel Moderator Add Example
                    {
                        "type": "channel.moderator.add",
                        "version": "1",
                        "condition": {
                            "broadcaster_user_id": "1337"
                        },
                        "transport": {
                            "method": "webhook",
                            "callback": "https://example.com/webhooks/callback",
                            "secret": "s3cRe7"
                        }
                    }
                    Channel Moderator Add Notification Payload
                    Name    Type    Description
                    subscription     subscription     Metadata about the subscription.
                    event     event     The event information. Contains user information of the new moderator as well as broadcaster information of the channel the event occurred on.
                    Channel Moderator Add Notification Example
                    {
                        "subscription": {
                            "id": "f1c2a387-161a-49f9-a165-0f21d7a4e1c4",
                            "type": "channel.moderator.add",
                            "version": "1",
                            "status": "enabled",
                            "cost": 0,
                            "condition": {
                                "broadcaster_user_id": "1337"
                            },
                             "transport": {
                                "method": "webhook",
                                "callback": "https://example.com/webhooks/callback"
                            },
                            "created_at": "2019-11-16T10:11:12.123Z"
                        },
                        "event": {
                            "user_id": "1234",
                            "user_login": "mod_user",
                            "user_name": "Mod_User",
                            "broadcaster_user_id": "1337",
                            "broadcaster_user_login": "cooler_user",
                            "broadcaster_user_name": "Cooler_User"
                        }
                    }  */      
             
    }
    
    public function eventsub_channel_moderator_remove() {
        $call_authentication = 'moderation:read';
        $endpoint = 'https://api.twitch.tv/helix/eventsub/subscriptions';
        $method = 'POST';
        $type = 'channel.moderator.remove';  
        
                    /* 
                    Channel Moderator Remove Request Body
                    Name    Type    Required?    Description
                    type    string    yes    The subscription type name: channel.moderator.remove.
                    version    string    yes    The subscription type version: 1.
                    condition     condition     yes    Subscription-specific parameters. Pass in the broadcaster user ID for the channel you want to receive moderator removal notifications for.
                    transport     transport     yes    Transport-specific parameters.
                    Channel Moderator Remove Example
                    {
                        "type": "channel.moderator.remove",
                        "version": "1",
                        "condition": {
                            "broadcaster_user_id": "1337"
                        },
                        "transport": {
                            "method": "webhook",
                            "callback": "https://example.com/webhooks/callback",
                            "secret": "s3cRe7"
                        }
                    }
                    Channel Moderator Remove Notification Payload
                    Name    Type    Description
                    subscription     subscription     Metadata about the subscription.
                    event     event     The event information. Contains user information of the old moderator as well as broadcaster information of the channel the event occurred on.
                    Channel Moderator Remove Notification Example
                    {
                        "subscription": {
                            "id": "f1c2a387-161a-49f9-a165-0f21d7a4e1c4",
                            "type": "channel.moderator.remove",
                            "version": "1",
                            "status": "enabled",
                            "cost": 0,
                            "condition": {
                                "broadcaster_user_id": "1337"
                            },
                             "transport": {
                                "method": "webhook",
                                "callback": "https://example.com/webhooks/callback"
                            },
                            "created_at": "2019-11-16T10:11:12.123Z"
                        },
                        "event": {
                            "user_id": "1234",
                            "user_login": "not_mod_user",
                            "user_name": "Not_Mod_User",
                            "broadcaster_user_id": "1337",
                            "broadcaster_user_login": "cooler_user",
                            "broadcaster_user_name": "Cooler_User"
                        }
                    }   */           
    }
    
    public function eventsub_channel_points_custom_reward_add() {
        $call_authentication = 'channel:read:redemptions'; // OR channel:manage:redemptions
        $endpoint = 'https://api.twitch.tv/helix/eventsub/subscriptions';
        $method = 'POST';
        $type = 'channel.channel_points_custom_reward.add'; 
        
                    /* 
                    Channel Points Custom Reward Add Request Body
                    Name    Type    Required?    Description
                    type    string    yes    The subscription type name: channel.channel_points_custom_reward.add.
                    version    string    yes    The subscription type version: 1.
                    condition     condition     yes    Subscription-specific parameters. Pass in the broadcaster user ID for the channel you want to receive channel points customer reward add notifications for.
                    transport     transport     yes    Transport-specific parameters.
                    Channel Points Custom Reward Add Webhook Example
                    {
                        "type": "channel.channel_points_custom_reward.add",
                        "version": "1",
                        "condition": {
                            "broadcaster_user_id": "1337"
                        },
                        "transport": {
                            "method": "webhook",
                            "callback": "https://example.com/webhooks/callback",
                            "secret": "s3cRe7"
                        }
                    }
                    Channel Points Custom Reward Add Notification Payload
                    Name    Type    Description
                    subscription     subscription     Metadata about the subscription.
                    event     event     The event information. Contains data about the custom reward added to the broadcaster�s channel.
                    Channel Points Custom Reward Add Notification Example
                    {
                        "subscription": {
                            "id": "f1c2a387-161a-49f9-a165-0f21d7a4e1c4",
                            "type": "channel.channel_points_custom_reward.add",
                            "version": "1",
                            "status": "enabled",
                            "cost": 0,
                            "condition": {
                                "broadcaster_user_id": "1337"
                            },
                             "transport": {
                                "method": "webhook",
                                "callback": "https://example.com/webhooks/callback"
                            },
                            "created_at": "2019-11-16T10:11:12.123Z"
                        },
                        "event": {
                            "id": "9001",
                            "broadcaster_user_id": "1337",
                            "broadcaster_user_login": "cool_user",
                            "broadcaster_user_name": "Cool_User",
                            "is_enabled": true,
                            "is_paused": false,
                            "is_in_stock": true,
                            "title": "Cool Reward",
                            "cost": 100,
                            "prompt": "reward prompt",
                            "is_user_input_required": true,
                            "should_redemptions_skip_request_queue": false,
                            "cooldown_expires_at": null,
                            "redemptions_redeemed_current_stream": null,
                            "max_per_stream": {
                                "is_enabled": true,
                                "value": 1000
                            },
                            "max_per_user_per_stream": {
                                "is_enabled": true,
                                "value": 1000
                            },
                            "global_cooldown": {
                                "is_enabled": true,
                                "seconds": 1000
                            },
                            "background_color": "#FA1ED2",
                            "image": {
                                "url_1x": "https://static-cdn.jtvnw.net/image-1.png",
                                "url_2x": "https://static-cdn.jtvnw.net/image-2.png",
                                "url_4x": "https://static-cdn.jtvnw.net/image-4.png"
                            },
                            "default_image": {
                                "url_1x": "https://static-cdn.jtvnw.net/default-1.png",
                                "url_2x": "https://static-cdn.jtvnw.net/default-2.png",
                                "url_4x": "https://static-cdn.jtvnw.net/default-4.png"
                            }
                        }
                    }   */            
    }
    
    public function eventsub_channel_points_custom_reward_update() {
        $call_authentication = 'channel:read:redemptions'; // OR channel:manage:redemptions
        $endpoint = 'https://api.twitch.tv/helix/eventsub/subscriptions';
        $method = 'POST';
        $type = 'channel.channel_points_custom_reward.update';    
        
        
                /* 
                Channel Points Custom Reward Update Request Body
                Name    Type    Required?    Description
                type    string    yes    The subscription type name: channel.channel_points_custom_reward.update.
                version    string    yes    The subscription type version: 1.
                condition     condition     yes    Subscription-specific parameters. Pass in the broadcaster user ID for the channel you want to receive channel points custom reward update notifications for. You can optionally pass in a reward id to only receive notifications for a specific reward.
                transport     transport     yes    Transport-specific parameters.
                Channel Points Custom Reward Update Webhook Example
                {
                    "type": "channel.channel_points_custom_reward.update",
                    "version": "1",
                    "condition": {
                        "broadcaster_user_id": "1337",
                        "reward_id": "9001" // optional to only get notifications for a specific reward
                    },
                    "transport": {
                        "method": "webhook",
                        "callback": "https://example.com/webhooks/callback",
                        "secret": "s3cRe7"
                    }
                }
                Channel Points Custom Reward Update Notification Payload
                Name    Type    Description
                subscription     subscription     Metadata about the subscription.
                event     event     The event information. Contains data about the custom reward updated on the broadcaster�s channel.
                Channel Points Custom Reward Update Notification Example
                {
                    "subscription": {
                        "id": "f1c2a387-161a-49f9-a165-0f21d7a4e1c4",
                        "type": "channel.channel_points_custom_reward.update",
                        "version": "1",
                        "status": "enabled",
                        "cost": 0,
                        "condition": {
                            "broadcaster_user_id": "1337"

                        },
                         "transport": {
                            "method": "webhook",
                            "callback": "https://example.com/webhooks/callback"
                        },
                        "created_at": "2019-11-16T10:11:12.123Z"
                    },
                    "event": {
                        "id": "9001",
                        "broadcaster_user_id": "1337",
                        "broadcaster_user_login": "cool_user",
                        "broadcaster_user_name": "Cool_User",
                        "is_enabled": true,
                        "is_paused": false,
                        "is_in_stock": true,
                        "title": "Cool Reward",
                        "cost": 100,
                        "prompt": "reward prompt",
                        "is_user_input_required": true,
                        "should_redemptions_skip_request_queue": false,
                        "cooldown_expires_at": "2019-11-16T10:11:12.123Z",
                        "redemptions_redeemed_current_stream": 123,
                        "max_per_stream": {
                            "is_enabled": true,
                            "value": 1000
                        },
                        "max_per_user_per_stream": {
                            "is_enabled": true,
                            "value": 1000
                        },
                        "global_cooldown": {
                            "is_enabled": true,
                            "seconds": 1000
                        },
                        "background_color": "#FA1ED2",
                        "image": {
                            "url_1x": "https://static-cdn.jtvnw.net/image-1.png",
                            "url_2x": "https://static-cdn.jtvnw.net/image-2.png",
                            "url_4x": "https://static-cdn.jtvnw.net/image-4.png"
                        },
                        "default_image": {
                            "url_1x": "https://static-cdn.jtvnw.net/default-1.png",
                            "url_2x": "https://static-cdn.jtvnw.net/default-2.png",
                            "url_4x": "https://static-cdn.jtvnw.net/default-4.png"
                        }
                    }
                }     */
            
    }
        
    public function eventsub_channel_points_custom_reward_remove() {
        $call_authentication = 'channel:read:redemptions'; // OR channel:manage:redemptions
        $endpoint = 'https://api.twitch.tv/helix/eventsub/subscriptions';
        $method = 'POST';
        $type = 'channel.channel_points_custom_reward.remove'; 
        
                   /* 
                    Channel Points Custom Reward Remove Request Body
                    Name    Type    Required?    Description
                    type    string    yes    The subscription type name: channel.channel_points_custom_reward.remove.
                    version    string    yes    The subscription type version: 1.
                    condition     condition     yes    Subscription-specific parameters. Pass in the broadcaster user ID for the channel you want to receive channel points custom reward remove notifications for. You can optionally pass in a reward ID to only receive notifications for a specific reward.
                    transport     transport     yes    Transport-specific parameters.
                    Channel Points Custom Reward Remove Webhook Example
                    {
                        "type": "channel.channel_points_custom_reward.remove",
                        "version": "1",
                        "condition": {
                            "broadcaster_user_id": "1337",
                            "reward_id": "9001" // optional to only get notifications for a specific reward
                        },
                        "transport": {
                            "method": "webhook",
                            "callback": "https://example.com/webhooks/callback",
                            "secret": "s3cRe7"
                        }
                    }
                    Channel Points Custom Reward Remove Notification Payload
                    Name    Type    Description
                    subscription     subscription     Subscription information.
                    event     event     The event information. Contains data about the custom reward removed from the broadcaster�s channel.
                    Channel Points Custom Reward Remove Notification Example
                    {
                        "subscription": {
                            "id": "f1c2a387-161a-49f9-a165-0f21d7a4e1c4",
                            "type": "channel.channel_points_custom_reward.remove",
                            "version": "1",
                            "status": "enabled",
                            "cost": 0,
                            "condition": {
                                "broadcaster_user_id": "1337",
                                "reward_id": 12345
                            },
                             "transport": {
                                "method": "webhook",
                                "callback": "https://example.com/webhooks/callback"
                            },
                            "created_at": "2019-11-16T10:11:12.123Z"
                        },
                        "event": {
                            "id": "9001",
                            "broadcaster_user_id": "1337",
                            "broadcaster_user_login": "cool_user",
                            "broadcaster_user_name": "Cool_User",
                            "is_enabled": true,
                            "is_paused": false,
                            "is_in_stock": true,
                            "title": "Cool Reward",
                            "cost": 100,
                            "prompt": "reward prompt",
                            "is_user_input_required": true,
                            "should_redemptions_skip_request_queue": false,
                            "cooldown_expires_at": "2019-11-16T10:11:12.123Z",
                            "redemptions_redeemed_current_stream": 123,
                            "max_per_stream": {
                                "is_enabled": true,
                                "value": 1000
                            },
                            "max_per_user_per_stream": {
                                "is_enabled": true,
                                "value": 1000
                            },
                            "global_cooldown": {
                                "is_enabled": true,
                                "seconds": 1000
                            },
                            "background_color": "#FA1ED2",
                            "image": {
                                "url_1x": "https://static-cdn.jtvnw.net/image-1.png",
                                "url_2x": "https://static-cdn.jtvnw.net/image-2.png",
                                "url_4x": "https://static-cdn.jtvnw.net/image-4.png"
                            },
                            "default_image": {
                                "url_1x": "https://static-cdn.jtvnw.net/default-1.png",
                                "url_2x": "https://static-cdn.jtvnw.net/default-2.png",
                                "url_4x": "https://static-cdn.jtvnw.net/default-4.png"
                            }
                        }
                    }   */     
        
               
    }
    
    public function eventsub_channel_points_custom_reward_redemption_add() {
        $call_authentication = 'channel:read:redemptions'; // OR channel:manage:redemptions
        $endpoint = 'https://api.twitch.tv/helix/eventsub/subscriptions';
        $method = 'POST';
        $type = 'channel.channel_points_custom_reward_redemption.add';        
        
                 /*        
                Channel Points Custom Reward Redemption Add Request Body
                Name    Type    Required?    Description
                type    string    yes    The subscription type name: channel.channel_points_custom_reward_redemption.add.
                version    string    yes    The subscription type version: 1.
                condition     condition     yes    Subscription-specific parameters. Pass in the broadcaster user ID for the channel you want to receive channel points custom reward redemption notifications for. You can optionally pass in a reward id to only receive notifications for a specific reward.
                transport     transport     yes    Transport-specific parameters.
                Channel Points Custom Reward Redemption Add Webhook Example
                {
                    "type": "channel.channel_points_custom_reward_redemption.add",
                    "version": "1",
                    "condition": {
                        "broadcaster_user_id": "1337",
                        "reward_id": "9001" // optional to only get notifications for a specific reward
                    },
                    "transport": {
                        "method": "webhook",
                        "callback": "https://example.com/webhooks/callback",
                        "secret": "s3cRe7"
                    }
                }
                Channel Points Custom Reward Redemption Add Notification Payload
                Name    Type    Description
                subscription     subscription     Subscription information.
                event     event     The event information. Contains data about the redemption of the custom reward on the broadcaster�s channel.
                Channel Points Custom Reward Redemption Add Notification Example
                {
                    "subscription": {
                        "id": "f1c2a387-161a-49f9-a165-0f21d7a4e1c4",
                        "type": "channel.channel_points_custom_reward_redemption.add",
                        "version": "1",
                        "status": "enabled",
                        "cost": 0,
                        "condition": {
                            "broadcaster_user_id": "1337"
                        },
                         "transport": {
                            "method": "webhook",
                            "callback": "https://example.com/webhooks/callback"
                        },
                        "created_at": "2019-11-16T10:11:12.123Z"
                    },
                    "event": {
                        "id": "1234",
                        "broadcaster_user_id": "1337",
                        "broadcaster_user_login": "cool_user",
                        "broadcaster_user_name": "Cool_User",
                        "user_id": "9001",
                        "user_login": "cooler_user",
                        "user_name": "Cooler_User",
                        "user_input": "pogchamp",
                        "status": "unfulfilled",
                        "reward": {
                            "id": "9001",
                            "title": "title",
                            "cost": 100,
                            "prompt": "reward prompt"
                        },
                        "redeemed_at": "2020-07-15T17:16:03.17106713Z"
                    }
                }   */     
        
    }
    
    public function eventsub_channel_points_custom_reward_redemption_update() {
        $call_authentication = 'channel:read:redemptions'; // OR channel:manage:redemptions
        $endpoint = 'https://api.twitch.tv/helix/eventsub/subscriptions';
        $method = 'POST';
        $type = 'channel.channel_points_custom_reward_redemption.update';    
                   /* 
                            
                    Channel Points Custom Reward Redemption Update Request Body
                    Name    Type    Required?    Description
                    type    string    yes    The subscription type name: channel.channel_points_custom_reward_redemption.update.
                    version    string    yes    The subscription type version: 1.
                    condition     condition     yes    Subscription-specific parameters. Pass in the broadcaster user ID for the channel you want to receive channel points custom reward redemption update notifications for. You can optionally pass in a reward id to only receive notifications for a specific reward.
                    transport     transport     yes    Transport-specific parameters.
                    Channel Points Custom Reward Redemption Update Webhook Example
                    {
                        "type": "channel.channel_points_custom_reward_redemption.update",
                        "version": "1",
                        "condition": {
                            "broadcaster_user_id": "1337",
                            "reward_id": "9001" // optional to only get notifications for a specific reward
                        },
                        "transport": {
                            "method": "webhook",
                            "callback": "https://example.com/webhooks/callback",
                            "secret": "s3cRe7"
                        }
                    }
                    Channel Points Custom Reward Redemption Update Notification Payload
                    Name    Type    Description
                    subscription     subscription     Subscription information.
                    event     event     The event information. Contains data about the custom reward redemption update from the broadcaster�s channel.
                    Channel Points Custom Reward Redemption Update Notification Example
                    {
                        "subscription": {
                            "id": "f1c2a387-161a-49f9-a165-0f21d7a4e1c4",
                            "type": "channel.channel_points_custom_reward_redemption.update",
                            "version": "1",
                            "status": "enabled",
                            "cost": 0,
                            "condition": {
                                "broadcaster_user_id": "1337"
                            },
                             "transport": {
                                "method": "webhook",
                                "callback": "https://example.com/webhooks/callback"
                            },
                            "created_at": "2019-11-16T10:11:12.123Z"
                        },
                        "event": {
                            "id": "1234",
                            "broadcaster_user_id": "1337",
                            "broadcaster_user_login": "cool_user",
                            "broadcaster_user_name": "Cool_User",
                            "user_id": "9001",
                            "user_login": "cooler_user",
                            "user_name": "Cooler_User",
                            "user_input": "pogchamp",
                            "status": "fulfilled",  // Either fulfilled or cancelled
                            "reward": {
                                "id": "9001",
                                "title": "title",
                                "cost": 100,
                                "prompt": "reward prompt"
                            },
                            "redeemed_at": "2020-07-15T17:16:03.17106713Z"
                        }
                    }     */   
            
    }
    
    public function eventsub_hype_train_begin() {
        $call_authentication = 'channel:read:hype_train';
        $endpoint = 'https://api.twitch.tv/helix/eventsub/subscriptions';
        $method = 'POST';
        $type = 'channel.hype_train.begin';     
        
                 /* 
                Channel Hype Train Begin Request Body
                Name    Type    Required?    Description
                type    string    yes    The subscription type name: channel.hype_train.begin.
                version    string    yes    The subscription type version: 1.
                condition     condition     yes    Subscription-specific parameters. Pass in the broadcaster user ID for the channel you want to hype train begin notifications for.
                transport     transport     yes    Transport-specific parameters.
                Channel Hype Train Begin Webhook Example
                {
                    "type": "channel.hype_train.begin",
                    "version": "1",
                    "condition": {
                        "broadcaster_user_id": "1337"
                    },
                    "transport": {
                        "method": "webhook",
                        "callback": "https://example.com/webhooks/callback",
                        "secret": "s3cRe7"
                    }
                }
                Channel Hype Train Begin Notification Payload
                Name    Type    Description
                subscription     subscription     Subscription information.
                event     event     Event information. Contains hype train information like the level, goal, top contributors, start time, and expiration time.
                Channel Hype Train Notification Example
                {
                    "subscription": {
                        "id": "f1c2a387-161a-49f9-a165-0f21d7a4e1c4",
                        "type": "channel.hype_train.begin",
                        "version": "1",
                        "status": "enabled",
                        "cost": 0,
                        "condition": {
                            "broadcaster_user_id": "1337"
                        },
                         "transport": {
                            "method": "webhook",
                            "callback": "https://example.com/webhooks/callback"
                        },
                        "created_at": "2019-11-16T10:11:12.123Z"
                    },
                    "event": {
                        "broadcaster_user_id": "1337",
                        "broadcaster_user_login": "cool_user",
                        "broadcaster_user_name": "Cool_User",
                        "total": 137,
                        "progress": 137,
                        "goal": 500,
                        "top_contributions": [
                            { "user_id": "123", "user_login": "pogchamp", "user_name": "PogChamp", "type": "bits", "total": 50 },
                            { "user_id": "456", "user_login": "kappa", "user_name": "Kappa", "type": "subscription", "total": 45 }
                        ],
                        "last_contribution": { "user_id": "123", "user_login": "pogchamp", "user_name": "PogChamp", "type": "bits", "total": 50 },
                        "started_at": "2020-07-15T17:16:03.17106713Z",
                        "expires_at": "2020-07-15T17:16:11.17106713Z"
                    }
                }   */        
    }
    
    public function eventsub_hype_train_progress() {
        $call_authentication = 'channel:read:hype_train';
        $endpoint = 'https://api.twitch.tv/helix/eventsub/subscriptions';
        $method = 'POST';
        $type = 'channel.hype_train.progress';      
        
                     /*            
                        Channel Hype Train Progress Request Body
                        Name    Type    Required?    Description
                        type    string    yes    The subscription type name: channel.hype_train.progress.
                        version    string    yes    The subscription type version: 1.
                        condition     condition     yes    Subscription-specific parameters. Pass in the broadcaster user ID for the channel you want to hype train progress notifications for.
                        transport     transport     yes    Transport-specific parameters.
                        Channel Hype Train Progress Webhook Example
                        {
                            "type": "channel.hype_train.progress",
                            "version": "1",
                            "condition": {
                                "broadcaster_user_id": "1337"
                            },
                            "transport": {
                                "method": "webhook",
                                "callback": "https://example.com/webhooks/callback",
                                "secret": "s3cRe7"
                            }
                        }
                        Channel Hype Train Progress Notification Payload
                        Name    Type    Description
                        subscription     subscription     Subscription information.
                        event     event     Event information. Contains hype train information like the level, goal, top contributors, last contribution, start time, and expiration time.
                        Channel Hype Train Progress Notification Example
                        {
                            "subscription": {
                                "id": "f1c2a387-161a-49f9-a165-0f21d7a4e1c4",
                                "type": "channel.hype_train.progress",
                                "version": "1",
                                "status": "enabled",
                                "cost": 0,
                                "condition": {
                                    "broadcaster_user_id": "1337"
                                },
                                 "transport": {
                                    "method": "webhook",
                                    "callback": "https://example.com/webhooks/callback"
                                },
                                "created_at": "2019-11-16T10:11:12.123Z"
                            },
                            "event": {
                                "broadcaster_user_id": "1337",
                                "broadcaster_user_login": "cool_user",
                                "broadcaster_user_name": "Cool_User",
                                "level": 2,
                                "total": 700,
                                "progress": 200,
                                "goal": 1000,
                                "top_contributions": [
                                    { "user_id": "123", "user_login": "pogchamp", "user_name": "PogChamp", "type": "bits", "total": 50 },
                                    { "user_id": "456", "user_login": "kappa", "user_name": "Kappa", "type": "subscription", "total": 45 }
                                ],
                                "last_contribution": { "user_id": "123", "user_login": "pogchamp", "user_name": "PogChamp", "type": "bits", "total": 50 },
                                "started_at": "2020-07-15T17:16:03.17106713Z",
                                "expires_at": "2020-07-15T17:16:11.17106713Z"
                            }
                        }   */     
                                  
    }
    
    public function eventsub_hype_train_end() {
        $call_authentication = 'channel:read:hype_train';
        $endpoint = 'https://api.twitch.tv/helix/eventsub/subscriptions';
        $method = 'POST';
        $type = 'channel.hype_train.end';      
        
        
               /* 
                Channel Hype Train End Request Body
                Name    Type    Required?    Description
                type    string    yes    The subscription type name: channel.hype_train.end.
                version    string    yes    The subscription type version: 1.
                condition     condition     yes    Subscription-specific parameters. Pass in the broadcaster user ID for the channel you want to hype train end notifications for.
                transport     transport     yes    Transport-specific parameters.
                Channel Hype Train End Webhook Example
                {
                    "type": "channel.hype_train.end",
                    "version": "1",
                    "condition": {
                        "broadcaster_user_id": "1337"
                    },
                    "transport": {
                        "method": "webhook",
                        "callback": "https://example.com/webhooks/callback",
                        "secret": "s3cRe7"
                    }
                }
                Channel Hype Train End Notification Payload
                Name    Type    Description
                subscription     subscription     Subscription information.
                event     event     Event information. Contains hype train information like the level, top contributors, start time, end time, and cooldown end time.
                Channel Hype Train End Notification Example
                {
                    "subscription": {
                        "id": "f1c2a387-161a-49f9-a165-0f21d7a4e1c4",
                        "type": "channel.hype_train.end",
                        "version": "1",
                        "status": "enabled",
                        "cost": 0,
                        "condition": {
                            "broadcaster_user_id": "1337"
                        },
                         "transport": {
                            "method": "webhook",
                            "callback": "https://example.com/webhooks/callback"
                        },
                        "created_at": "2019-11-16T10:11:12.123Z"
                    },
                    "event": {
                        "broadcaster_user_id": "1337",
                        "broadcaster_user_login": "cool_user",
                        "broadcaster_user_name": "Cool_User",
                        "level": 2,
                        "total": 137,
                        "top_contributions": [
                            { "user_id": "123", "user_login": "pogchamp", "user_name": "PogChamp", "type": "bits", "total": 50 },
                            { "user_id": "456", "user_login": "kappa", "user_name": "Kappa", "type": "subscription", "total": 45 }
                        ],
                        "started_at": "2020-07-15T17:16:03.17106713Z",
                        "ended_at": "2020-07-15T17:16:11.17106713Z",
                        "cooldown_ends_at": "2020-07-15T18:16:11.17106713Z"
                    }
                }   */     
          
    }
    
    public function eventsub_stream_online() {
        $call_authentication = 'none';
        $endpoint = 'https://api.twitch.tv/helix/eventsub/subscriptions';
        $method = 'POST';
        $type = 'stream.online';     
        
        
                        /*
                        Stream Online Request Body
                        Name    Type    Required?    Description
                        type    string    yes    The subscription type name: stream.online.
                        version    string    yes    The subscription type version: 1.
                        condition     condition     yes    Subscription-specific parameters. Pass in the broadcaster user ID for the channel you want to get updates for.
                        transport     transport     yes    Transport-specific parameters.
                        Stream Online Webhook Example
                        {
                            "type": "stream.online",
                            "version": "1",
                            "condition": {
                                "broadcaster_user_id": "1337"
                            },
                            "transport": {
                                "method": "webhook",
                                "callback": "https://example.com/webhooks/callback",
                                "secret": "s3cRe7"
                            }
                        }
                        Stream Online Notification Payload
                        Name    Type    Description
                        subscription     subscription     Subscription information.
                        event     event     Event information. Contains the stream ID, broadcaster user ID, broadcaster user name, and the stream type.
                        Stream Online Notification Example
                        {
                            "subscription": {
                                "id": "f1c2a387-161a-49f9-a165-0f21d7a4e1c4",
                                "type": "stream.online",
                                "version": "1",
                                "status": "enabled",
                                "cost": 0,
                                "condition": {
                                    "broadcaster_user_id": "1337"
                                },
                                 "transport": {
                                    "method": "webhook",
                                    "callback": "https://example.com/webhooks/callback"
                                },
                                "created_at": "2019-11-16T10:11:12.123Z"
                            },
                            "event": {
                                "id": "9001",
                                "broadcaster_user_id": "1337",
                                "broadcaster_user_login": "cool_user",
                                "broadcaster_user_name": "Cool_User",
                                "type": "live",
                                "started_at": "2020-10-11T10:11:12.123Z"
                            }
                        } */

           
    }
    
    public function eventsub_stream_offline() {
        $call_authentication = 'none';
        $endpoint = 'https://api.twitch.tv/helix/eventsub/subscriptions';
        $method = 'POST';
        $type = 'stream.offline';
        
            /* 
                    Stream Offline Request Body
            Name    Type    Required?    Description
            type    string    yes    The subscription type name: stream.offline.
            version    string    yes    The subscription type version: 1.
            condition     condition     yes    Subscription-specific parameters. Pass in the broadcaster user ID for the channel you want to get updates for.
            transport     transport     yes    Transport-specific parameters.
            Stream Offline Webhook Example
            {
                "type": "stream.offline",
                "version": "1",
                "condition": {
                    "broadcaster_user_id": "1337"
                },
                "transport": {
                    "method": "webhook",
                    "callback": "https://example.com/webhooks/callback",
                    "secret": "s3cRe7"
                }
            }
            Stream Offline Notification Payload
            Name    Type    Description
            subscription     subscription     Subscription information.
            event     event     Event information. Contains the broadcaster user ID and broadcaster user name.
            Stream Offline Notification Example
            {
                "subscription": {
                    "id": "f1c2a387-161a-49f9-a165-0f21d7a4e1c4",
                    "type": "stream.offline",
                    "version": "1",
                    "status": "enabled",
                    "cost": 0,
                    "condition": {
                        "broadcaster_user_id": "1337"
                    },
                    "created_at": "2019-11-16T10:11:12.123Z",
                     "transport": {
                        "method": "webhook",
                        "callback": "https://example.com/webhooks/callback"
                    }
                },
                "event": {
                    "broadcaster_user_id": "1337",
                    "broadcaster_user_login": "cool_user",
                    "broadcaster_user_name": "Cool_User"
                }
            }     */   
    }
    
    public function eventsub_user_authorization_revoke() {
        $call_authentication = 'none'; // Provided client_id must match the client id in the application access token.
        $endpoint = 'https://api.twitch.tv/helix/eventsub/subscriptions';
        $method = 'POST';
        $type = 'user.authorization.revoke';  
        
        
        /* 
                User Authorization Revoke Request Body
                Name    Type    Required?    Description
                type    string    yes    The subscription type name: user.authorization.revoke.
                version    string    yes    The subscription type version: 1.
                condition     condition     yes    Subscription-specific parameters. Pass in the client ID of the application you want to get user authorization revoke notifications for.
                transport     transport     yes    Transport-specific parameters.
                User Authorization Revoke Webhook Example
                {
                    "type": "user.authorization.revoke",
                    "version": "1",
                    "condition": {
                        "client_id": "1337"
                    },
                    "transport": {
                        "method": "webhook",
                        "callback": "https://example.com/webhooks/callback",
                        "secret": "s3cRe7"
                    }
                }
                User Authorization Revoke Notification Payload
                Name    Type    Description
                subscription     subscription     Subscription information.
                event     event     Event information. Contains your application�s client ID and the user ID of the user who revoked authorization for your application.
                User Authorization Revoke Notification Example
                {
                    "subscription": {
                        "id": "f1c2a387-161a-49f9-a165-0f21d7a4e1c4",
                        "type": "user.authorization.revoke",
                        "version": "1",
                        "status": "enabled",
                        "cost": 0,
                        "condition": {
                            "client_id": "crq72vsaoijkc83xx42hz6i37"
                        },
                         "transport": {
                            "method": "webhook",
                            "callback": "https://example.com/webhooks/callback"
                        },
                        "created_at": "2019-11-16T10:11:12.123Z"
                    },
                    "event": {
                        "client_id": "crq72vsaoijkc83xx42hz6i37",
                        "user_id": "1337",
                        "user_login": "cool_user",  // Null if the user no longer exists
                        "user_name": "Cool_User"    // Null if the user no longer exists
                    }
                }  */            
    }
    
    public function eventsub_user_update() {
        $call_authentication = 'none'; // When using user:read:email scope, the notification will include email field.
        $endpoint = 'https://api.twitch.tv/helix/eventsub/subscriptions';
        $method = 'POST';
        $type = 'user.update';    
        
        
        
        /* 
                User Update Request Body
                Name    Type    Required?    Description
                type    string    yes    The subscription type name: user.update.
                version    string    yes    The subscription type version: 1.
                condition     condition     yes    Subscription-specific parameters. Pass in the user ID for the user you want update notifications for.
                transport     transport     yes    Transport-specific parameters.
                User Update Webhook Example
                {
                    "type": "user.update",
                    "version": "1",
                    "condition": {
                        "user_id": "1337"
                    },
                    "transport": {
                        "method": "webhook",
                        "callback": "https://example.com/webhooks/callback",
                        "secret": "s3cRe7"
                    }
                }
                User Update Notification Payload
                Name    Type    Description
                subscription     subscription     Subscription information.
                event     event     Event information. Contains the user ID, user name, and description. The user�s email is included if you have the user:read:emailscope for the user.
                User Update Notification Example
                {
                    "subscription": {
                        "id": "f1c2a387-161a-49f9-a165-0f21d7a4e1c4",
                        "type": "user.update",
                        "version": "1",
                        "status": "enabled",
                        "cost": 0,
                        "condition": {
                           "user_id": "1337"
                        },
                         "transport": {
                            "method": "webhook",
                            "callback": "https://example.com/webhooks/callback"
                        },
                        "created_at": "2019-11-16T10:11:12.123Z"
                    },
                    "event": {
                        "user_id": "1337",
                        "user_login": "cool_user",
                        "user_name": "Cool_User",
                        "email": "user@email.com",  // Requires user:read:email scope
                        "description": "cool description"
                    }
                }        
        */    
    }
}

endif;                         
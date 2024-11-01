<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if( !class_exists( 'TwitchPress_Systematic_Syncing' ) ) :

/**
 * TwitchPress Class for systematically syncing Twitch.tv data to WP.
 * 
 * @class    TwitchPress_Feeds
 * @author   Ryan Bayne
 * @category Admin
 * @package  TwitchPress/Core
 * @version  1.0.0
 */
class TwitchPress_Systematic_Syncing {
    var $tracing_obj = null;
    
    /**
    * @var integer - user sync flood delay.        
    */
    public $sync_user_flood_delay = 60;// seconds
    
    /**
    * Early sync uses stored calls to repeat calls
    * and keep content updated prior to being requested. 
    * 
    * This variable is an on/off switch for that service.
    * 
    * @var mixed
    */
    public $early_sync_on = false;

    public function init() {
        // WP core action hooks.
        add_action( 'wp_login', array( $this, 'sync_user_on_login' ), 1, 2 );
        add_action( 'profile_personal_options', array( $this, 'sync_user_on_viewing_profile' ) );
               
        // Systematic syncing will perform constant data updating with delays to prevent flooding.
        add_action( 'wp_loaded', array( $this, 'systematic_syncing' ), 1 );
   
        // User Profile
        add_action( 'show_user_profile',        array( $this, 'twitch_subscription_status_show' ), 1 );
        add_action( 'edit_user_profile',        array( $this, 'twitch_subscription_status_edit' ), 1 );
        add_action( 'personal_options_update',  array( $this, 'twitch_subscription_status_save' ), 1 );
        add_action( 'edit_user_profile_update', array( $this, 'twitch_subscription_status_save' ), 1 );
            
        do_action( 'twitchpress_sync_loaded' );    
    }
    
    /**
    * A main channel sync method.
    * 
    * Do hook when you need to ensure that a current logged in users
    * Twitch subscription data has been updated else update it. 
    * 
    * @version 2.0
    */
    public function twitchpress_sync_currentusers_twitchsub_mainchannel() {    
        // Hook should only be called within a method that involves a
        // logged in user but we will make sure. 
        if( !is_user_logged_in() ) { return; } 
                                     
        // Avoid processing the owner of the main channel (might not be admin with ID 1)
        if( twitchpress_is_current_user_main_channel_owner() ) { return; }
                   
        $this->sync_user( get_current_user_id(), false, false, 'user' );  
    }
    
    /**
    * Using load balancing limits and caching we trigger automated
    * processing constantly. This is a temporary approach until
    * background processing with reliance on WP CRON is established. 
    * 
    * @version 1.0
    */
    public function systematic_syncing() {          
        // Apply a short delay on all syncing activity. 
        if( !twitchpress_is_sync_due( __LINE__, __FUNCTION__, __LINE__, 120 ) )
        {
            return;        
        }
          
        // Run sync on all of the main channel subscribers, with offset to manage larger numbers.
        add_action( 'shutdown', array( $this, 'sync_channel_subscribers' ), 10 );    
        
        // Sync the current logged in visitors twitch sub data for the main channel. 
        add_action( 'shutdown', array( $this, 'twitchpress_sync_currentusers_twitchsub_mainchannel' ), 10 );    
        
        // Run repeat calls - these update prior to being requested...
        add_action( 'shutdown', array( $this, 'repeat_calls_run') );
    }
    
    /**
    * Syncs the main channels subscribers systematically. 
    * 
    * 1. Gets the main channels subscribers.
    * 2. Creates a channel post for each subscriber. 
    * 3. Post meta includes all possible information.
    * 4. We pair channel post with user when they login using Twitch. 
    * 
    * @version 2.0
    */
    public function sync_channel_subscribers() {

        if( get_option( 'twitchpress_sync_switch_channel_subscribers' ) !== 'yes' ) {return;}
        
        // This option is only generated here. 
        $option = get_option( 'twitchpress_sync_job_channel_subscribers' );
        if( !is_array( $option ) ) 
        { 
            $option = array(
                'delay' => 1800,
                'last_time' => 666,// 666 has no importance, placeholder only
                'subscribers' => 0,
                'subs_limit'  => 5, 
            ); 
        }
                                                                          
        $helix = new TwitchPress_Twitch_API();    
        $id = twitchpress_get_main_channels_twitchid();// Right now everything defaults to the main channel.
        $earliest_time = $option['last_time'] + $option['delay'];
        
        if( $earliest_time > time() ) { return false; } else { $option['last_time'] = time(); }
             
        // Establish an offset within a continuing job. 
        if( !isset( $option['offset'] ) || !is_numeric( $option['offset'] ) ) { $option['offset'] = 0; }
        
        // Update the jobs option early to ensure no error later stops timers being stored. 
        update_option( 'twitchpress_sync_job_channel_subscribers', $option, false );
    
        $token = twitchpress_get_main_channels_token();
        $code = twitchpress_get_main_channels_code(); 
        $broadcaster_id = twitchpress_get_main_channels_twitchid();

        $subscribers = $helix->get_broadcaster_subscriptions( $broadcaster_id );

        if( $subscribers !== null && isset( $subscribers['subscriptions'] ) )
        {
            if( isset( $subscribers['_total'] ) && $subscribers['_total'] < $option['subs_limit'] ) 
            {
                // Then we have reached the last subscriber for the channel, requiring the job to be restarted. 
                $option['offset'] = 0;
                $option['jobs'] = $option['jobs'] + 1;// Counts how many times we do the entire process.
                
                // Store last count to help any troubleshooting that needs done in future. 
                $option['lasttotal'] = isset( $subscribers['_total'] );
            }

            if( is_array($subscribers['subscriptions']  ) )
            {
                $new_subscribers = 0;
                
                // Get or create a channel post based on this subscriber. 
                foreach( $subscribers['subscriptions'] as $key => $sub ) 
                {
                    // Subscribers are stored as channel posts, subs can get their own page on the site.
                    if( $this->twitchpress_save_subscribers_post( $sub ) )
                    {  
                        ++$new_subscribers;               
                    }   
                    else
                    {
                        // False indicates existing post was updated.
                    }    
                }   
                
                $option['subscribers'] = $option['subscribers'] + $new_subscribers;
            }
        }
        
        // Update the jobs option. 
        update_option( 'twitchpress_sync_job_channel_subscribers', $option, false );
        
        unset( $helix );               
    } 

    /**
    * Add scopes information (usually from extensions) to the 
    * system scopes status which is used to tell us what scopes are
    * required for the current system.
    * 
    * @param mixed $new_array
    * 
    * @version 2.0
    */
    public function update_system_scopes_status( $filtered_array ) {
        
        $scopes = array();
        
        /*
           Not used because sync extension is to be merged with the core. 
           Sync extension technically does not have its own required roles.
           Only the services that require sync have required roles. 
        */
        
        // Scopes for admin only or main account functionality that is always used. 
        $scopes['admin']['twitchpress-sync-extension']['required'] = array();
        
        // Scopes for admin only or main account features that may not be used.
        $scopes['admin']['twitchpress-sync-extension']['optional'] = array(); 
                    
        // Scopes for functionality that is always used. 
        $scopes['public']['twitchpress-sync-extension']['required'] = array();
        
        // Scopes for features that may not be used.
        $scopes['public']['twitchpress-sync-extension']['optional'] = array(); 
                    
        return array_merge( $filtered_array, $scopes );      
    }
    
    /**
    * Syncronize the visitors Twitch data when they login.  
    * 
    * @version 1.2
    */
    public function sync_user_on_login( $user_login, $user ) {  
        $this->sync_user( $user->data->ID, false, false );    
    }

    /**
    * Hooked by profile_personal_options and syncs user data. 
    * 
    * @version 1.2
    */
    public function sync_user_on_viewing_profile( $user ) {    
        $this->sync_user( $user->ID, false, false );    
    }         
    
    /**
    * Syncronize the visitors Twitch data...
    * 
    * Hooked to login and profile visit. 
    * Also called in a custom action for running the user sync when it matters. 
    * 
    * @link https://twitchpress.wordpress.com/2020/10/13/sync-trigger-visitor-opening-wp-profile-view/  
    * 
    * @version 2.1
    */
    public function sync_user( $wp_user_id, $ignore_delay = false, $output_notice = false, $side = 'user' ) {  
                          
        // Ensure the giving user is due a sync.   
        if( false === $ignore_delay ) {                  
            if( !$this->is_users_sync_due( $wp_user_id ) ) { 

                if( $output_notice ) 
                {
                    TwitchPress_Admin_Notices::add_wordpress_notice( 'twitchpresssyncusernotdue', 'info', false, __( 'TwitchPress', 'twitchpress' ), __( 'Your subscription data was requested not long ago. Please wait a few minutes before trying again.', 'twitchpress' ) );
                }
                
                return false; 
            }
        }
                    
        update_user_meta( $wp_user_id, 'twitchpress_sync_time', time() );
        
        // Subscription Syncing - We can choose to sync from user side or channel side. 
        if( $side == 'user' )
        {                            
            // Twitch Subscription Sync by a visitor who has giving permission...
            $this->user_sub_sync( $wp_user_id, $output_notice );    
        }
        elseif( $side == 'channel' )
        {
            $this->main_channel_sub_sync_helix( $wp_user_id, $output_notice );    
        }           
        
        // Follower syncing...
        $this->user_follower_sync( $wp_user_id );
    }

    /**
    * Checks if the giving user is due a full sync.
    * 
    * @param mixed $user_id
    * 
    * @version 1.0
    */
    public function is_users_sync_due( $wp_user_id ) {
        $time = get_user_meta( $wp_user_id, 'twitchpress_sync_time', true );
        if( !$time ) { $time = 0; }         
        $earliest_time = $this->sync_user_flood_delay + $time;
        if( $earliest_time > time() ) {  return false; }
        return true;
    }
    
    /**
    * User side request to Twitch API for subscription data.
    * 
    * @param mixed $wp_user_id
    * @param mixed $notice_output
    * 
    * @version 2.0
    */
    public function user_sub_sync( $wp_user_id, $output_notice = false ){       
        twitchpress_bugnet_add_trace_steps( 'twitchsubonreg', __( 'Started syncing Twitch subscription.', 'twitchpress' ) );        
        
        $helix = new TwitchPress_Twitch_API();    

        $twitch_user_id = twitchpress_get_user_twitchid_by_wpid( $wp_user_id );    
        $twitch_channel_id = twitchpress_get_main_channels_twitchid();
        $twitch_user_token = twitchpress_get_user_token( $wp_user_id );
        
        // Get the full subscription object.
        $twitch_sub_response = $helix->getUserSubscription( $twitch_user_id, $twitch_channel_id, $twitch_user_token );    

        // Get possible existing sub plan from an earlier sub sync...
        $local_sub_plan = get_user_meta( $wp_user_id, 'twitchpress_sub_plan_' . $twitch_channel_id, true  );
        
        bugnet_add_trace_meta( 'twitchsubonreg', 'step_local_plan', $local_sub_plan );
        
        if( isset( $twitch_sub_response['error'] ) || $twitch_sub_response === null ) 
        {      
            bugnet_add_trace_meta( 'twitchsubonreg', 'step_api_response', $twitch_sub_response );

            // No sub exists so complete removal if the local sub value is recognized...
            if( twitchpress_is_valid_sub_plan( $local_sub_plan ) ) 
            {   
                twitchpress_bugnet_add_trace_steps( 'twitchsubonreg', __( 'User stopped subscribing to Twitch channel.', 'twitchpress' ) );
                
                bugnet_add_trace_meta( 'twitchsubonreg', 'step_delete_plan', 'removed users locally stored plan' );
                   
                // Remove the sub plan value to ensure there is no mistake when it comes to user access.
                delete_user_meta( $wp_user_id, 'twitchpress_sub_plan_' . $twitch_channel_id );  
                delete_user_meta( $wp_user_id, 'twitchpress_sub_plan_name_' . $twitch_channel_id );  

                if( $output_notice ) 
                {
                    TwitchPress_Admin_Notices::add_wordpress_notice( 'usersubsyncnosubresponse', 'warning', false, 
                    __( 'Subscription Ended', 'twitchpress' ), 
                    __( 'The response from Twitch.tv indicates that a previous subscription to the sites main channel was discontinued. Subscriber perks on this website will also be discontinued.', 'twitchpress' ) );
                }
                
                // API Logging outcome (helix only)...
                if( isset( $helix->curl_object->loggingid ) ) {
                    $outcome = sprintf( __( 'User with ID [%s] has stopped subscribing.','twitchpress'), $wp_user_id );
                    TwitchPress_API_Logging::outcome( $helix->curl_object->loggingid, $outcome );
                }
                            
                return;
            }
            else
            {
                twitchpress_bugnet_add_trace_steps( 'twitchsubonreg', __( 'User is not subscribing to the main channel.', 'twitchpress' ) );
            }       

            if( $output_notice ) 
            {
                TwitchPress_Admin_Notices::add_wordpress_notice( 'usersubsyncnosubresponse', 'info', false, 
                __( 'Not Subscribing', 'twitchpress' ), 
                __( 'The response from Twitch.tv indicates that you are not currently subscribing to this sites main channel.', 'twitchpress' ) );
                
                // API Logging outcome (helix only)...
                if( isset( $helix->curl_object->loggingid ) ) {
                    $outcome = sprintf( __( 'User with ID [%s] is not a Twitch.tv subscriber and no updates were required.','twitchpress'), $wp_user_id );
                    TwitchPress_API_Logging::outcome( $helix->curl_object->loggingid, $outcome );
                }                
            }
                                
            return;
        }
        elseif( isset( $twitch_sub_response['sub_plan'] ) )
        {
            bugnet_add_trace_meta( 'twitchsubonreg', 'step_sub_plan', $twitch_sub_response['sub_plan'] );
            
            // The visitor is a subscriber to the main channel... (status is boolean only)
            update_user_meta( $wp_user_id, 'twitchpress_substatus_mainchannel', true );
        
            if( !twitchpress_is_valid_sub_plan( $local_sub_plan ) ) 
            {   
                twitchpress_bugnet_add_trace_steps( 'twitchsubonreg', __( 'Users Twitch sub plan syncing for first time.', 'twitchpress' ) );   
                
                // User is being registered as a Twitch sub for the first time.
                update_user_meta( $wp_user_id, 'twitchpress_sub_plan_' . $twitch_channel_id, $twitch_sub_response['sub_plan'] );
                update_user_meta( $wp_user_id, 'twitchpress_sub_plan_name_' . $twitch_channel_id, $twitch_sub_response['sub_plan_name'] );
                                    
                if( $output_notice ) 
                {
                    TwitchPress_Admin_Notices::add_wordpress_notice( 'usersubsyncnosubresponse', 'success', false, 
                    __( 'New Subscriber', 'twitchpress' ), 
                    __( 'You\'re subscription has been confirmed and your support is greatly appreciated. You now have access to subscriber perks on this site.', 'twitchpress' ) );
                }
                
                // API Logging outcome (helix only)...
                if( isset( $helix->curl_object->loggingid ) ) {
                    $outcome = sprintf( __( 'User with ID [%s] is a subscriber being synced for the first time.','twitchpress'), $wp_user_id );
                    TwitchPress_API_Logging::outcome( $helix->curl_object->loggingid, $outcome );
                } 
                               
                return;
            } 
            elseif( twitchpress_is_valid_sub_plan( $local_sub_plan ) ) 
            {  
                if( $twitch_sub_response['sub_plan'] !== $local_sub_plan )
                {   
                    twitchpress_bugnet_add_trace_steps( 'twitchsubonreg', __( 'Visitor is a continuing subscriber but changed their plan.', 'twitchpress' ) );
                  
                    // User has changed their subscription plan and are still subscribing.
                    update_user_meta( $wp_user_id, 'twitchpress_sub_plan_' . $twitch_channel_id, $twitch_sub_response['sub_plan'] );                        
                    update_user_meta( $wp_user_id, 'twitchpress_sub_plan_name_' . $twitch_channel_id, $twitch_sub_response['sub_plan'] );        
                     
                    if( $output_notice ) 
                    {
                        TwitchPress_Admin_Notices::add_wordpress_notice( 'usersubsyncnosubresponse', 'success', false, 
                        __( 'Subscription Updated', 'twitchpress' ), 
                        __( 'Your existing subscription has been updated due to a change in your plan. You\'re continued support is greatly appreciated.', 'twitchpress' ) );
                    }  
                    
                    // API Logging outcome (helix only)...
                    if( isset( $helix->curl_object->loggingid ) ) {
                        $outcome = sprintf( __( 'User with ID [%s] has changed their Twitch.tv subscription plan.','twitchpress'), $wp_user_id );
                        TwitchPress_API_Logging::outcome( $helix->curl_object->loggingid, $outcome );
                    }                                                                            
                }
                else
                {  
                    twitchpress_bugnet_add_trace_steps( 'twitchsubonreg', __( 'Visitor is a continuing subscriber with no change to the plan.', 'twitchpress' ) );
                    
                    if( $output_notice ) 
                    {
                        TwitchPress_Admin_Notices::add_wordpress_notice( 'usersubsyncnosubresponse', 'success', false, 
                        __( 'Continuing Subscriber', 'twitchpress' ), 
                        __( 'Your existing subscription has been confirmed as unchanged and your continued support is greatly appreciated.', 'twitchpress' ) );
                    }
                    
                    // API Logging outcome (helix only)...
                    if( isset( $helix->curl_object->loggingid ) ) {
                        $outcome = sprintf( __( 'User with ID [%s] is subscribing on the same plan.','twitchpress'), $wp_user_id );
                        TwitchPress_API_Logging::outcome( $helix->curl_object->loggingid, $outcome );
                    }                    
                }

                return;
            } 
        }     

        twitchpress_bugnet_add_trace_steps( 'twitchsubonreg', __( 'Finished syncing Twitch subscription with an unexpected state being reached.', 'twitchpress' ) );

        // API Logging outcome (helix only)...
        if( isset( $twitch_sub_response->curl_object->loggingid ) ) {
            $outcome = sprintf( __( 'Syncing subscription for user ID [%s] reached a bad state.','twitchpress'), $wp_user_id );
            TwitchPress_API_Logging::outcome( $twitch_sub_response->curl_object->loggingid, $outcome );
        }
                    
        do_action( 'twitchpress_user_sub_sync_finished', $wp_user_id ); 
    }   
    
    public function user_follower_sync( $wp_user_id ) { 

        $helix = new TwitchPress_Twitch_API();    

        $twitch_user_id = twitchpress_get_user_twitchid_by_wpid( $wp_user_id );    
        $twitch_channel_id = twitchpress_get_main_channels_twitchid();
        $twitch_user_token = twitchpress_get_user_token( $wp_user_id );
                
        $followed = $helix->get_users_follows( null, null, $twitch_user_id, $twitch_channel_id );
        
        unset( $helix );
        
        if( isset( $followed->total ) && $followed->total == 1 ) {
            update_user_option( $wp_user_id, 'twitchpress_following_main', true );  
            do_action( 'twitchpress_new_follower', $wp_user_id );      
        } else {
            $status = get_user_option( 'twitchpress_following_main', $wp_user_id );
            if( $status ) { 
                update_user_option( $wp_user_id, 'twitchpress_following_main', false );
                do_action( 'twitchpress_stopped_following', $wp_user_id );
            } 
        }    
    }   
      
    /**
    * Channel side request for subscription data.
    * 
    * @returns true if a change is made and false if no change to sub status has been made.
    * 
    * @version 1.0
    */
    private function main_channel_sub_sync_helix( $user_id, $output_notice = false ) {
        $helix = new TWITCHPRESS_Twitch_API();  
            
        $channel_id = twitchpress_get_main_channels_twitchid();
        $channel_token = twitchpress_get_main_channels_token();

        // Setup a call name for tracing. 
        $helix->twitch_call_name = __( 'Sync Users Subscription', 'twitchpress' );
    
        $users_twitch_id = get_user_meta( $user_id, 'twitchpress_twitch_id', true );

        // Check channel subscription from channel side (does not require scope permission)
        $sub_response = $helix->get_broadcaster_subscriptions( $channel_id, $users_twitch_id );     

        if( !isset( $sub_response['data'] ) ) {
            TwitchPress_API_Logging::error( 
                $helix->curl_object->loggingid, 
                'twitchsubrequest', 
                __( '', 'twitchpress' ), 
                $meta 
            );
                
            // API Logging outcome...
            $outcome = sprintf( __( 'User with ID [%s] has incomplete subscription state in WP .','twitchpress'), $user_id );
            TwitchPress_API_Logging::outcome( $helix->curl_object->loggingid, $outcome );
        }

        // Get possible existing sub plan from an earlier sub sync...
        $local_sub_plan = get_user_meta( $user_id, 'twitchpress_sub_plan_' . $channel_id, true  );
        
        // No subscription - update WP to match Twitch.tv sub state...
        if( isset( $sub_response['error'] ) || $sub_response === null ) 
        {               
            // Avoid the do_action() if subplan isn't actually valid...
            if( twitchpress_is_valid_sub_plan( $local_sub_plan ) ) 
            {      
                // Remove the sub plan value to ensure there is no mistake when it comes to user access.
                delete_user_meta( $user_id, 'twitchpress_sub_plan_' . $channel_id );  
                delete_user_meta( $user_id, 'twitchpress_sub_plan_name_' . $channel_id );  
                
                do_action( 'twitchpress_sync_discontinued_twitch_subscriber', $user_id, $channel_id );

                // API Logging outcome...
                $outcome = sprintf( __( 'User with ID [%s] appears to have cancelled their Twitch.tv subscription.','twitchpress'), $user_id );
                TwitchPress_API_Logging::outcome( $helix->curl_object->loggingid, $outcome );
                                
                return;
            }       

            // Arriving here means no active Twitch sub and no local history of a sub.
            do_action( 'twitchpress_sync_never_a_twitch_subscriber', $user_id, $channel_id );

            // API Logging outcome...
            $outcome = sprintf( __( 'User with ID [%s] does not subscribe, update not required either.','twitchpress'), $user_id );
            TwitchPress_API_Logging::outcome( $helix->curl_object->loggingid, $outcome );
                        
            return;
        }
        elseif( isset( $sub_response->tier ) )
        {
            // The visitor is a subscriber to the main channel. 
            // The sub status is boolean only.
            update_user_meta( $user_id, 'twitchpress_substatus_mainchannel', true );
            
            // Actions should rely on the twitchpress_substatus_mainchannel option only as others are updated later.
            do_action( 'twitchpress_sync_user_subscribes_to_channel', array( $users_twitch_id, $channel_id ) );    
             
            if( !twitchpress_is_valid_sub_plan( $local_sub_plan ) ) 
            {      
                // User is being registered as a Twitch sub for the first time.
                update_user_meta( $user_id, 'twitchpress_sub_plan_' . $channel_id, $sub_response->tier );
                update_user_meta( $user_id, 'twitchpress_sub_plan_name_' . $channel_id, $sub_response->plan_name );

                do_action( 'twitchpress_sync_new_twitch_subscriber', $user_id, $channel_id, $sub_response->tier );                
            
                // API Logging outcome...
                $outcome = sprintf( __( 'User with ID [%s] is subscribing on Twitch and being synced for the first time.','twitchpress'), $user_id );
                TwitchPress_API_Logging::outcome( $helix->curl_object->loggingid, $outcome );
                            
                return;
            } 
            elseif( twitchpress_is_valid_sub_plan( $local_sub_plan ) ) 
            {  
                // User is not a newely detected subscriber and has sub history stored in WP, check for sub plan change. 
     
                if( $sub_response->tier !== $local_sub_plan )
                { 
                    // User has changed their subscription plan and are still subscribing.
                    update_user_meta( $user_id, 'twitchpress_sub_plan_' . $channel_id, $sub_response->tier );                        
                    update_user_meta( $user_id, 'twitchpress_sub_plan_name_' . $channel_id, $sub_response->tier );
                    
                    // API Logging outcome...
                    $outcome = sprintf( __( 'User with ID [%s] has changed their Twitch.tv sub plan.','twitchpress'), $user_id );
                    TwitchPress_API_Logging::outcome( $helix->curl_object->loggingid, $outcome );                                            
                }

                do_action( 'twitchpress_sync_continuing_twitch_subscriber', $user_id, $channel_id );

                return;
            } 
        }      
        
        // API Logging outcome...
        $outcome = sprintf( __( 'User with ID [%s] has incomplete subscription state in WP .','twitchpress'), $user_id );
        TwitchPress_API_Logging::outcome( $helix->curl_object->loggingid, $outcome );
        
        return TwitchPress_API_Logging::error( $helix->curl_object->loggingid, 'twitchsubsyncprocedurebadstate', sprintf( __( 'Bad state reached when syncing Twitch subscription for user ID [%s]', 'twitchpress' ), $user_id ) );                 
    }                                   

    /**
    * Adds subscription information to user profile: /wp-admin/profile.php 
    * 
    * @param mixed $user
    * 
    * @version 1.0
    */
    public function twitch_subscription_status_show( $user ) {
        ?>
        <h2><?php _e('Twitch Details','twitchpress') ?></h2>
        <p><?php _e('This information is being added by the TwitchPress system.','twitchpress') ?></p>
        <table class="form-table">
            <tbody>
                <tr>
                    <th>
                        <label for="something"><?php _e( 'Twitch ID', 'twitchpress'); ?></label>
                    </th>
                    <td>
                        <?php echo get_user_meta( $user->ID, 'twitchpress_twitch_id', true ); ?>
                    </td>
                </tr>                
                <tr>
                    <th>
                        <label for="something"><?php _e( 'Subscription Status', 'twitchpress'); ?></label>
                    </th>
                    <td>
                        <?php $this->display_users_subscription_status( $user->ID ); ?>
                    </td>
                </tr>                                        
                <tr>
                    <th>
                        <label for="something"><?php _e( 'Subscription Name', 'twitchpress'); ?></label>
                    </th>
                    <td>
                        <?php $this->display_users_subscription_plan_name( $user->ID ); ?>
                    </td>
                </tr>                    
                <tr>
                    <th>
                        <label for="something"><?php _e( 'Subscription Plan', 'twitchpress'); ?></label>
                    </th>
                    <td>
                        <?php $this->display_users_subscription_plan( $user->ID ); ?>
                    </td>
                </tr>                    
                <tr>
                    <th>
                        <label for="something"><?php _e( 'Update Date', 'twitchpress'); ?></label>
                    </th>
                    <td>                
                        <?php $this->display_users_last_twitch_to_wp_sync_date( $user->ID ); ?>
                    </td>
                </tr>                    
                <tr>
                    <th>
                        <label for="something"><?php _e( 'Update Time', 'twitchpress'); ?></label>
                    </th>
                    <td>                
                        <?php $this->display_users_last_twitch_to_wp_sync_date( $user->ID, true ); ?>
                    </td>
                </tr>    
                <tr>
                    <th>
                        <label for="something"><?php _e( 'Twitch oAuth2 Status', 'twitchpress'); ?></label>
                    </th>
                    <td>                
                        <?php $this->display_users_twitch_authorisation_status( $user->ID ); ?>
                    </td>
                </tr>   
                <tr>
                    <th>
                        <label for="something"><?php _e( 'Code', 'twitchpress'); ?></label>
                    </th>
                    <td>                
                        <?php if( get_user_meta( $user->ID, 'twitchpress_code', true ) ) { _e( 'Code Set', 'twitchpress' ); } ?>
                    </td>
                </tr>   
                <tr>
                    <th>
                        <label for="something"><?php _e( 'Token', 'twitchpress'); ?></label>
                    </th>
                    <td>                
                        <?php if( get_user_meta( $user->ID, 'twitchpress_token', true ) ) { _e( 'Token Is Saved', 'twitchpress' ); }else{ _e( 'No User Token', 'twitchpress' ); } ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php             
    }
    
    /**
    * Displays fields on wp-admin/user-edit.php?user_id=1
    * 
    * @param mixed $user
    * 
    * @version 1.2
    */
    public function twitch_subscription_status_edit( $user ) {
        ?>
        <h2><?php _e('Twitch Information','twitchpress') ?></h2>
        <p><?php _e('This information is being displayed by TwitchPress.','twitchpress') ?></p>
        <table class="form-table">
            <tbody>
                <tr>
                    <th>
                        <label for="something"><?php _e( 'Twitch ID', 'twitchpress'); ?></label>
                    </th>
                    <td>
                        <?php echo get_user_meta( $user->ID, 'twitchpress_twitch_id', true ); ?>
                    </td>
                </tr>                
                <tr>
                    <th>
                        <label for="something"><?php _e( 'Subscription Status', 'twitchpress'); ?></label>
                    </th>
                    <td>
                        <?php $this->display_users_subscription_status( $user->ID ); ?>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="something"><?php _e( 'Subscription Name', 'twitchpress'); ?></label>
                    </th>
                    <td>
                        <?php $this->display_users_subscription_plan_name( $user->ID ); ?>
                    </td>
                </tr>                                        
                <tr>
                    <th>
                        <label for="something"><?php _e( 'Subscription Plan', 'twitchpress'); ?></label>
                    </th>
                    <td>
                        <?php $this->display_users_subscription_plan( $user->ID ); ?>
                    </td>
                </tr>                    
                <tr>
                    <th>
                        <label for="something"><?php _e( 'Update Date', 'twitchpress'); ?></label>
                    </th>
                    <td>                
                        <?php $this->display_users_last_twitch_to_wp_sync_date( $user->ID ); ?>
                    </td>
                </tr>                    
                <tr>
                    <th>
                        <label for="something"><?php _e( 'Update Time', 'twitchpress'); ?></label>
                    </th>
                    <td>                
                        <?php $this->display_users_last_twitch_to_wp_sync_date( $user->ID, true ); ?>
                    </td>
                </tr>   
                <tr>
                    <th>
                        <label for="something"><?php _e( 'Twitch oAuth2 Status', 'twitchpress'); ?></label>
                    </th>
                    <td>                
                        <?php $this->display_users_twitch_authorisation_status( $user->ID ); ?>
                    </td>
                </tr>   
                <tr>
                    <th>
                        <label for="something"><?php _e( 'Code', 'twitchpress'); ?></label>
                    </th>
                    <td>                
                        <?php echo get_user_meta( $user->ID, 'twitchpress_code', true ); ?>
                    </td>
                </tr>   
                <tr>
                    <th>
                        <label for="something"><?php _e( 'Token', 'twitchpress'); ?></label>
                    </th>
                    <td>                
                        <?php if( get_user_meta( $user->ID, 'twitchpress_token', true ) ) { _e( 'Token Is Saved', 'twitchpress' ); }else{ _e( 'No User Token', 'twitchpress' ); } ?>
                    </td>
                </tr>
                
            </tbody>
        </table>
        <?php  
        do_action( 'twitchpress_sync_user_profile_section' );         
    }
    
    /**
    * Calls $this->sync_user() 
    * 
    * Hooked by personal_options_update() and edit_user_profile_update()
    * 
    * @uses sync_user
    * @param mixed $user_id
    */
    public function twitch_subscription_status_save( $user_id ) {
        $this->sync_user( $user_id, false, true );   
    }
            
    /**
    * Outputs giving users scription status for the main channel. 
    * 
    * @param mixed $user_id
    * 
    * @version 2.0
    */
    public function display_users_subscription_status( $user_id ) {
        $output = '';

        $status = get_user_meta( $user_id, 'twitchpress_substatus_mainchannel', true );
        
        if( $status == true )
        {
            $output = __( 'Subscribed', 'twitchpress' );                
        }
        else
        {
            $output = __( 'Not Subscribed', 'twitchpress' );
        }
        
        echo esc_html( $output );    
    }
    
    /**
    * Outputs giving users scription plan for the main channel. 
    * 
    * @param mixed $user_id
    * 
    * @version 1.0
    */
    public function display_users_subscription_plan( $user_id ) {
        $output = '';
        $channel_id = twitchpress_get_main_channels_twitchid();
   
        $plan = get_user_meta( $user_id, 'twitchpress_sub_plan_' . $channel_id, true );
        
        if( $plan !== '' && is_string( $plan ) )
        {
            $output = $plan;                
        }
        else
        {
            $output = __( 'None', 'twitchpress' );
        }
        
        echo esc_html( $output );    
    }  
          
    /**
    * Outputs giving users scription package name for the main channel. 
    * 
    * @param mixed $user_id
    * 
    * @version 1.0
    */
    public function display_users_subscription_plan_name( int $user_id ) {
        $output = '';
        $channel_id = twitchpress_get_main_channels_twitchid();
   
        $plan = get_user_meta( $user_id, 'twitchpress_sub_plan_name_' . $channel_id, true );
        
        if( $plan !== '' && is_string( $plan ) )
        {
            $output = $plan;                
        }
        else
        {
            $output = __( 'None', 'twitchpress' );
        }
        
        echo esc_html( $output );    
    }
    
    /**
    * Outputs the giving users last sync date and time. 
    * 
    * @param mixed $user_id
    * 
    * @version 1.0
    */
    public function display_users_last_twitch_to_wp_sync_date( int $user_id, $ago = false ) {
        $output = __( 'Waiting - Please Click Update', 'twitchpress' );
        
        $time = get_user_meta( $user_id, 'twitchpress_sync_time', true );
        
        if( !$time ) 
        { 
            $output = __( 'Never Updated - Please Click Update', 'twitchpress' ); 
        }
        else
        {   
            if( $ago ) 
            {   
                $output = human_time_diff( $time, time() );
            }
            else
            {
                $output = date( 'F j, Y g:i a', $time );
            }
        }
        
        echo $output;
    }                   
    
    /**
    * Outputs use friendly status of twitch authorisation. 
    *         
    * @param mixed $user_id
    * 
    * @version 1.2
    */
    public function display_users_twitch_authorisation_status( $user_id ) {

        $code = get_user_meta( $user_id, 'twitchpress_code', true );
        $token = get_user_meta( $user_id, 'twitchpress_token', true );
        
        if( !$code && !$token)
        {
            echo __( 'No Twitch Authorisation Setup', 'twitchpress' );
            return;
        }
        elseif( !$code )
        {
            echo __( 'No Code', 'twitchpress' );
            return;
        }
        else
        {   
            echo __( 'Ready', 'twitchpress' );
            return;
        }

    }     
    
    /**
    * Schedules all repetitive calls to run again but these jobs will be handled
    * by class.async-request.php
    * 
    * Called in footer to reduce affects on header and content loading. 
    * 
    * @version 1.0
    */
    public function repeat_calls_run() {

        /**
        // We will get registered calls
        $calls_list = $this->repeat_calls_get_calls();
        
        $args = array();
        
        wp_schedule_single_event( time() + 10, 'twitchpress_plugin_repeat_calls_twitchtv', $args );
        **/
    } 
}  

endif;
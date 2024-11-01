<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if( !class_exists( 'TwitchPress_Twitch_Subscription_Management' ) ) :

/**
 * TwitchPress Class for systematically syncing Twitch.tv data to WP.
 * 
 * @class    TwitchPress_Feeds
 * @author   Ryan Bayne
 * @category Admin
 * @package  TwitchPress/Core
 * @version  1.0.0
 */
#[AllowDynamicProperties]
class TwitchPress_Twitch_Subscription_Management {
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
        
        // Sync the current logged in visitors twitch sub data for the main channel. 
        add_action( 'shutdown', array( $this, 'sync_current_user' ), 10 );
        
        // WP User Profile integration...
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
    * @version 3.0
    */
    public function sync_current_user() {    
        // Hook should only be called within a method that involves a
        // logged in user but we will make sure. 
        if( !is_user_logged_in() ) { return; } 
                                     
        // Avoid processing the owner of the main channel (might not be admin with ID 1)
        if( twitchpress_is_current_user_main_channel_owner() ) { return; }
        
        twitchpress_user_sub_sync_single( get_current_user_id() );             
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
        twitchpress_user_sub_sync_single( $user->data->ID, false );    
    }

    /**
    * Hooked by profile_personal_options and syncs user data. 
    * 
    * @version 1.2
    */
    public function sync_user_on_viewing_profile( $user ) {    
        twitchpress_user_sub_sync_single( $user->ID, false );    
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
                        <?php $this->display_users_subscription_plan_name( $user->ID ); ?>
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
                        <?php $this->display_users_subscription_tier( $user->ID ); ?>
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
        twitchpress_user_sub_sync_single( $user_id, false );   
    }
            
    /**
    * Outputs giving users scription status for the main channel. 
    * 
    * @param mixed $user_id
    * 
    * @version 2.0
    */
    public function display_users_subscription_status( $wp_user_id ) {
        $sub = twitchpress_get_user_meta_twitch_sub( $wp_user_id );
        if( twitchpress_subscription_data_ready( $sub ) ) {
            _e( 'Subscribed', 'twitchpress' );                
        }
        _e( 'Not Subscribed', 'twitchpress' );   
    }
    
    /**
    * Outputs giving users scription plan for the main channel. 
    * 
    * @param mixed $user_id
    * 
    * @version 1.0
    */
    public function display_users_subscription_tier( $wp_user_id ) {
        if( !$tier = twitchpress_get_user_sub_tier( $wp_user_id ) ){
            _e( 'None', 'twitchpress' );               
        } else {
            echo esc_html( $tier );    
        }            
    }  
          
    /**
    * Outputs giving users scription package name for the main channel. 
    * 
    * @param mixed $user_id
    * 
    * @version 1.0
    */
    public function display_users_subscription_plan_name( int $wp_user_id ) {
        if( !$plan = twitchpress_get_user_sub_plan_name( $wp_user_id ) ){
            _e( 'None', 'twitchpress' );               
        } else {
            echo esc_html( $plan );    
        }    
    }
    
    /**
    * Outputs the giving users last sync date and time. 
    * 
    * @param mixed $user_id
    * 
    * @version 1.0
    */
    public function display_users_last_twitch_to_wp_sync_date( int $wp_user_id ) {
        $output = __( 'Waiting - Please Click Update', 'twitchpress' );
        $time = twitchpress_get_user_sub_last_checked( $wp_user_id );
        if( !$time ) { _e( 'Never Updated - Please Click Update', 'twitchpress' ); return; }
        echo human_time_diff( $time, time() ); //$output = date( 'F j, Y g:i a', $time ); 
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
}  

endif;
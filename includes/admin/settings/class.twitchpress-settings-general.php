<?php
/**
* TwitchPress General Settings
*
* @author Ryan Bayne
* @category settings
* @package TwitchPress/Settings/General
* @version 1.0
*/

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'Direct script access is not allowed!' ); 

if ( ! class_exists ( 'TwitchPress_Settings_General' ) ) :

class TwitchPress_Settings_General extends TwitchPress_Settings_Page {

    private $sections_array = array ();
 
    /**
    * Constructor
    * 
    * @version 1.0  
    */
    public function __construct()  {

        $this->id  = 'general'; 
        $this->label = __( 'General', 'twitchpress' );

        add_filter( 'twitchpress_settings_tabs_array',        array( $this, 'add_settings_page' ), 20 );
        add_action( 'twitchpress_settings_' . $this->id,      array( $this, 'output' ) );
        add_action( 'twitchpress_settings_save_' . $this->id, array( $this, 'save' ) );
        add_action( 'twitchpress_sections_' . $this->id,      array( $this, 'output_sections' ) );
    }
 
    /**
    * Get sections.
    * 
    * @return array
    * 
    * @version 1.0
    */
    public function get_sections() {
        
        // Add more sections to the settings tab.
        $this->sections_array = array(
            'default'   => __( 'General', 'twitchpress' ), 
            'removal'   => __( 'Plugin Removal', 'twitchpress' ),
            'advanced'  => __( 'Advanced', 'twitchpress' ),
            'systems'   => __( 'System Switches', 'twitchpress' ),
            //'livemenu'  => __( 'Live Menu', 'twitchpress' ),
            'team'      => __( 'Main Team', 'twitchpress' ),
        );
        
        return apply_filters( 'twitchpress_get_sections_' . $this->id, $this->sections_array );
    }
    
    /**
    * Output the settings.
    */
    public function output() {
        global $current_section;
        $settings = $this->get_settings( $current_section );
        TwitchPress_Admin_Settings::output_fields( $settings );
    }
       
    /**
     * Save settings method runs along with save() method in class.twitchpress-admin-settings.php
     * 
     * @version 3.0
     */
    public function save() {      
        
        // Handle all sections (tabs) first...
        global $current_section;
        $settings = $this->get_settings( $current_section );
        TwitchPress_Admin_Settings::save_fields( $settings ); // Use the saved values where possible...
        $notices = new TwitchPress_Admin_Notices();
        
        // Handle the $current_section only...
        switch ( $current_section ) {
            case 'default':

            break;
            case 'removal':

            break;
            case 'advanced':

            break;
            case 'systems':
                if( isset( $_POST['twitchpress_twitchsubscribers_switch'] ) ) {
                    TwitchPress_Admin_Settings::add_message( __( 'Subscription System Activated', 'twitchpress' ) );                
                    if( twitchpress_confirm_scope( 'user_read_subscriptions', 'channel' ) && twitchpress_confirm_scope( 'channel_read_subscriptions', 'channel' ) ) {
                        $notices->info( __( 'TwitchPress Instruction: ', 'twitchpress' ), __( 'You should now go to Twitch API Settings and select subscription related scopes that allow subscription features to work.', 'twitchpress' ), false );
                    }
                } else {
                    TwitchPress_Admin_Settings::add_message( __( 'Subscription System Disabled', 'twitchpress' ) );    
                }
                
                if( isset( $_POST['twitchpress_giveaways_switch'] ) ) {
                    TwitchPress_Admin_Settings::add_message( __( 'Giveaways System Activated', 'twitchpress' ) );                
                } else {
                    TwitchPress_Admin_Settings::add_message( __( 'Giveaways System Disabled', 'twitchpress' ) );    
                }                
                
                if( isset( $_POST['twitchpress_perks_switch'] ) ) {
                    TwitchPress_Admin_Settings::add_message( __( 'Perks System Activated', 'twitchpress' ) );                                
                } else {
                    TwitchPress_Admin_Settings::add_message( __( 'Perks System Disabled', 'twitchpress' ) );    
                }                
                
                if( isset( $_POST['twitchpress_webhooks_switch'] ) ) {
                    TwitchPress_Admin_Settings::add_message( __( 'Webhooks System Activated', 'twitchpress' ) );
                } else {
                    TwitchPress_Admin_Settings::add_message( __( 'Webhooks System Disabled', 'twitchpress' ) );    
                }                
                
                if( isset( $_POST['twitchpress_gate_switch'] ) ) {
                    TwitchPress_Admin_Settings::add_message( __( 'Content Gate System Activated', 'twitchpress' ) );
                } else {
                    TwitchPress_Admin_Settings::add_message( __( 'Content Gate System Disabled', 'twitchpress' ) );    
                }                

            break;
            case 'team':
            
                // React to team section being submitted...
                if( !isset( $_POST['twitchpress_team_name'] ) ) {
                    TwitchPress_Admin_Settings::add_error( __( 'Please enter your teams name as shown on Twitch.', 'twitchpress' ) );
                    return;
                }
                
                $twitch_api = new TwitchPress_Twitch_API();
                $twitch_api->get_team( get_option( 'twitchpress_team_name' ) );
                                
                if( $twitch_api->curl_object->response_code == 200 ) {
                    $team_id = $twitch_api->curl_object->curl_reply_body->data['0']->id;
                    twitchpress_update_main_channels_team_id( $team_id );    
                    TwitchPress_Admin_Settings::add_message( sprintf( __( 'Your main teams ID is %d and will be used to request team data from Twitch.', 'twitchpress' ), $team_id ) );
                } elseif( $twitch_api->curl_object->response_code == 404 ) {
                    TwitchPress_Admin_Settings::add_error( __( 'Your team could not be found. Ensure the name is entered correctly and try again.', 'twitchpress' ) );
                }
                
            break;
        }
  
        
    }  

    /**
     * Get settings array.
     *
     * @return array
     * 
     * @version 1.0
     */
    public function get_settings( $current_section = 'default' ) {
        $settings = array(); 
        
        if ( 'default' == $current_section ) {

            $settings = apply_filters( 'twitchpress_general_settings', array(

                array(
                    'title' => __( 'General Settings', 'twitchpress' ),
                    'type'     => 'title',
                    'desc'     => __( 'You can support development by opting into the improvement program. It does not send sensitive data.', 'twitchpress' ),
                    'id'     => 'generalsettings'
                ),

                array(
                    'desc'            => __( 'Send Usage Data', 'twitchpress' ),
                    'id'              => 'twitchpress_feedback_data',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),

                array(
                    'desc'            => __( 'Allow Feedback Prompts', 'twitchpress' ),
                    'id'              => 'twitchpress_feedback_prompt',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
               
                array(
                    'desc'            => __( 'Over-ride BuddyPress Avatars', 'twitchpress' ),
                    'id'              => 'twitchpress_buddypress_avatars_override',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                                    
                array(
                    'type'     => 'sectionend',
                    'id'     => 'generalsettings'
                )

            ));
            
        // Domain to Twitch API permission Options.
        } elseif( 'removal' == $current_section ) {
            
            $settings = apply_filters( 'twitchpress_general_removal_settings', array(
 
                array(
                    'title' => __( 'Plugin Removal Settings', 'twitchpress' ),
                    'type'     => 'title',
                    'desc'     => __( 'What should the TwitchPress core plugin remove when being deleted?', 'twitchpress' ),
                    'id'     => 'pluginremovalsettings',
                ),
            
                array(
                    'desc'            => __( 'Delete Options', 'twitchpress' ),
                    'id'              => 'twitchpress_remove_options',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),    

                array(
                    'desc'            => __( 'Delete Database Tables', 'twitchpress' ),
                    'id'              => 'twitchpress_remove_database_tables',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),        
            
                array(
                    'desc'            => __( 'Delete User Data', 'twitchpress' ),
                    'id'              => 'twitchpress_remove_user_data',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),    
            
                array(
                    'desc'            => __( 'Delete Media', 'twitchpress' ),
                    'id'              => 'twitchpress_remove_media',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),    
                
                array(
                    'type'     => 'sectionend',
                    'id'     => 'pluginremovalsettings'
                ),

            ));
        
         // Advanced settings for developers only...
        } elseif( 'advanced' == $current_section ) {
            
            $settings = apply_filters( 'twitchpress_general_advanced_settings', array(
 
                array(
                    'title' => __( 'Advanced Settings', 'twitchpress' ),
                    'type'     => 'title',
                    'desc'     => __( 'Use with care. Some settings are meant for development environments (not live sites).', 'twitchpress' ),
                    'id'     => 'advancedsettings',
                ),
            
                array(
                    'desc'            => __( 'Display Errors', 'twitchpress' ),
                    'id'              => 'twitchpress_displayerrors',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),  
                          
                array(
                    'desc'            => __( 'Activate Redirect Tracking', 'twitchpress' ),
                    'id'              => 'twitchpress_redirect_tracking_switch',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),
                          
                array(
                    'desc'            => __( 'Log API Activity', 'twitchpress' ),
                    'id'              => 'twitchpress_api_logging_switch',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),
                          
                array(
                    'desc'            => __( 'Log API Raw Response/Body', 'twitchpress' ),
                    'id'              => 'twitchpress_api_logging_body_switch',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),
                                        
                array(
                    'type'     => 'sectionend',
                    'id'     => 'advancedsettings'
                ),

            ));

        } elseif( 'systems' == $current_section ) {
            
            $settings = apply_filters( 'twitchpress_general_systems_settings', array(
 
                array(
                    'title' => __( 'System Switches', 'twitchpress' ),
                    'type'     => 'title',
                    'desc'     => __( 'Use these settings to quickly enable/disable systems.', 'twitchpress' ),
                    'id'     => 'systemsettings',
                ),
            
                array(
                    'desc'            => __( 'Twitch Subscription System', 'twitchpress' ),
                    'id'              => 'twitchpress_twitchsubscribers_switch',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),   
                         
                array(
                    'desc'            => __( 'Giveaways System (Alpha - test only)', 'twitchpress' ),
                    'id'              => 'twitchpress_giveaways_switch',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),  
                            
                array(
                    'desc'            => __( 'Perks System (Alpha - test only)', 'twitchpress' ),
                    'id'              => 'twitchpress_perks_switch',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),  
                                            
                array(
                    'desc'            => __( 'Webhooks System (Alpha - test only)', 'twitchpress' ),
                    'id'              => 'twitchpress_webhooks_switch',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),  
                                
                array(
                    'desc'            => __( 'Content Gate System', 'twitchpress' ),
                    'id'              => 'twitchpress_gate_switch',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),  
                                            
                array(
                    'type'     => 'sectionend',
                    'id'     => 'systemsettings'
                ),

            ));

        } elseif( 'livemenu' == $current_section ) {
            
            $settings = apply_filters( 'twitchpress_general_livemenu_settings', array(
 
                array(
                    'title' => __( 'Live Menu', 'twitchpress' ),
                    'type'     => 'title',
                    'desc'     => __( 'Add a vertical drop-down menu of your live streamers to your themes main menu.', 'twitchpress' ),
                    'id'     => 'livemenusettings',
                ),
            
                array(
                    'desc'            => __( 'Activate Live Menu', 'twitchpress' ),
                    'id'              => 'twitchpress_livemenu_switch',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),  
                            
                array(
                    'desc'            => __( 'Include Offline', 'twitchpress' ),
                    'id'              => 'twitchpress_livemenu_includeoffline',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,                 
                ),     
                                       
                array(
                    'desc'            => __( 'Data Source', 'twitchpress' ),
                    'id'              => 'twitchpress_livemenu_includeoffline',
                    'default'         => 'no',
                    'type'            => 'radio',
                    'options'         => array(
                        'Default Team Name',
                        'WordPress Team Members',
                    ),  
                    'show_if_checked' => 'yes',
                    'autoload'        => true,                 
                ),  
                                            
                array(
                    'type'     => 'sectionend',
                    'id'     => 'livemenusettings'
                ),

            ));

        } elseif( 'team' == $current_section ) {
            
            $settings = apply_filters( 'twitchpress_general_team_settings', array(
 
                array(
                    'title' => __( 'Main Team', 'twitchpress' ),
                    'type'     => 'title',
                    'desc'     => __( 'Manage the main team to act as a default for team related features.', 'twitchpress' ),
                    'id'     => 'teamsettings',
                ),
            
                array(
                    'title'           => __( 'Twitch Team Name', 'twitchpress' ),               
                    'desc'            => __( 'Enter it exactly as displayed on Twitch', 'twitchpress' ),
                    'id'              => 'twitchpress_team_name',
                    'default'         => '',
                    'type'            => 'text',
                    'validation'      => 'alphanumeric',
                    'autoload'        => true,
                ),  
                                    
                array(
                    'title'           => __( 'Twitch Team ID', 'twitchpress' ),
                    'desc'            => __( 'This will be found automatically.', 'twitchpress' ),
                    'id'              => 'twitchpress_main_channel_team_id',
                    'default'         => '',
                    'type'            => 'text',
                    'autoload'        => true,
                    'readonly'        => true
                ),  
                                            
                array(
                    'type'     => 'sectionend',
                    'id'     => 'teamsettings'
                ),

            ));

        }
  
        return apply_filters( 'twitchpress_get_settings_' . $this->id, $settings, $current_section );
    }
}
    
endif;

return new TwitchPress_Settings_General();
          
     

 
    
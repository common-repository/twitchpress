<?php
/**
 * TwitchPress Permissions Settings
 *
 * @author   Ryan Bayne
 * @category Admin
 * @package  TwitchPress/Admin/Settings
 * @version  1.0
 */

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'Direct script access is not allowed!' );

if ( ! class_exists( 'TwitchPress_Settings_Twitch' ) ) :

/**
 * TwitchPress_Settings_Sections.
 */
class TwitchPress_Settings_Twitch extends TwitchPress_Settings_Page {
    
    public $webhooks_ready = null;

    /**
     * Constructor.
     */
    public function __construct() {
        global $wpdb, $current_section;
        
        $this->id    = 'twitch';
        $this->label = __( 'Twitch API', 'twitchpress' );
     
        // Establish installation status...
        if( twitchpress_webhooks_ready() && $current_section == 'webhooks' ) {
            $this->webhooks_ready = false;  
            add_filter( 'twitchpress_settings_save_button_text', array( $this, 'custom_save_button_webhooks' ), 1 );          
        } else {
            $this->webhooks_ready = true;
        }
        
        add_filter( 'twitchpress_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
        add_action( 'twitchpress_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'twitchpress_settings_save_' . $this->id, array( $this, 'save' ) );
        add_action( 'twitchpress_sections_' . $this->id, array( $this, 'output_sections' ) ); 
    }
    
    /**
    * Filter the save button text when on the Webhooks tab and change it according
    * to Webhooks installation state...
    * 
    * @param mixed $text original button text i.e. "Save changes"
    * 
    * @version 1.0
    */
    public function custom_save_button_webhooks( $text ) {
        if( !$this->webhooks_ready )
        {
            return __( 'Install Webhooks System', 'twitchpress' );
        }
        return $text;
    }
    
    /**
     * Get sections.
     *
     * @return array
     */
    public function get_sections() {

        $sections = array(
            'default'               => __( 'Permissions Scope', 'twitchpress' ),
            'entermaincredentials'  => __( 'Enter Main Credentials', 'twitchpress' ),
            'general'               => __( 'General Options', 'twitchpress' ),
            'syncvalues'            => __( 'Sync Values', 'twitchpress' ),
            'webhooks'              => __( 'Webhooks', 'twitchpress' ),
        );

        return apply_filters( 'twitchpress_get_sections_' . $this->id, $sections );
    }

    /**
     * Output the settings.
     */
    public function output() {
        global $current_section, $wpdb;
        
        if( !$this->webhooks_ready && $current_section == 'webhooks' ) 
        {
            $settings = $this->get_settings( 'webhooksinstallation' );
            $message = __( 'Webhooks System has not been installed yet.', 'twitchpress' );
            echo '<div id="message" class="error inline"><p><strong>' . $message . '</strong></p></div>';
        }
        else
        {
            $settings = $this->get_settings( $current_section );
        }
        
        TwitchPress_Admin_Settings::output_fields( $settings );
    }

    /**
     * Save settings.
     */
    public function save() {
        
        // First time installation by admin is required...
        if( isset( $_POST['webhooks_first_installation_request'] ) ) 
        {
            $settings = $this->get_settings( 'installation' );
            
            twitchpress_webhooks_activate_service();
            
            TwitchPress_Admin_Settings::add_message( __( 'Webhooks system (alpha - test only) has been installed.', 'twitchpress' ) );                
        }
        else
        {        
            global $current_section;
            $settings = $this->get_settings( $current_section );
        }
        
        TwitchPress_Admin_Settings::save_fields( $settings );
    }

    /**
     * Get settings array.
     *
     * @return array
     * 
     * @version 3.0
     */
    public function get_settings( $current_section = '' ) {
        $settings = array();
                           
        if ( 'general' == $current_section ) {
            
            $settings = apply_filters( 'twitchpress_generalapi_settings', array(
            
                array(
                    'title' => __( 'Twitch API Options', 'twitchpress' ),
                    'type'     => 'title',
                    'desc'     => __( 'Mostly miscellanous options for the Twitch API.', 'twitchpress' ),
                    'id'     => 'twitchapigeneraloptions'
                ),

                array(
                    'title'   => __( 'Twitch API Version', 'twitchpress' ),
                    'desc'    => __( 'Switch with care and fully test used features.', 'twitchpress' ),
                    'id'      => 'twitchpress_apiversion',
                    'default' => '6',
                    'type'    => 'radio',
                    'options' => array(
                        '6' => __( 'Helix (v6)', 'twitchpress' ),
                    ),
                    'autoload'        => true,
                    'show_if_checked' => 'option',
                ),
                
                array(
                    'title'           => __( 'Log API Activty', 'twitchpress' ),
                    'desc'            => __( 'Check to activate the API-focused log service.', 'twitchpress' ),
                    'id'              => 'twitchpress_api_logging_switch',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => 'twitchpressapilog',
                    'show_if_checked' => 'yes',
                    'autoload'        => true,
                ),
                                                     
                array(
                    'type'     => 'sectionend',
                    'id'     => 'twitchapigeneraloptions'
                )

            ));
                
        } elseif ( 'entermaincredentials' == $current_section ) {

            $settings = apply_filters( 'twitchpress_entermaincredentials_settings', array(
            
                array(
                    'title' => __( 'Enter Main Twitch API Application', 'twitchpress' ),
                    'type'  => 'title',
                    'desc'  => __( 'This is the form for entering your main developer application. When you submit the form for the first time, you will go through the oAuth2 procedure. If a code already exists and it is still valid, the procedure will be shorter. When you arrive back on this screen, the token field should be populated and you should be able to make calls to the Twitch API.', 'twitchpress' ),
                    'id'    => 'mainapplicationcredentials'
                ),

                array(
                    'title'           => __( 'Main Channel', 'twitchpress' ),
                    'desc'            => __( 'Add the channel that the developer application has been created in.', 'twitchpress' ),
                    'id'              => 'twitchpress_main_channels_name',
                    'default'         => '',
                    'type'            => 'text',
                ),

                array(
                    'title'             => __( 'Main Channel ID', 'twitchpress' ),
                    'desc'              => __( 'Main channel ID is currently only set by TwitchPress to confirm oAuth2 credentials are correct.', 'twitchpress' ),
                    'id'                => 'twitchpress_main_channels_id',
                    'default'           => '',
                    'type'              => 'text',
                    'custom_attributes' => array( 'disabled' => 'disabled' ),
                ),
                
                array(
                    'title'           => __( 'Redirect URL', 'twitchpress' ),
                    'desc'            => __( 'Redirect URL', 'twitchpress' ),
                    'id'              => 'twitchpress_app_redirect',
                    'default'         => '',
                    'autoload'        => false,
                    'type'            => 'text',
                ),

                array(
                    'title'           => __( 'Client/App ID', 'twitchpress' ),
                    'desc'            => __( 'Your applications public ID.', 'twitchpress' ),
                    'id'              => 'twitchpress_app_id',
                    'default'         => '',
                    'type'            => 'text',
                    'autoload'        => false,
                ),

                array(
                    'title'           => __( 'Client/App Secret', 'twitchpress' ),
                    'desc'            => __( 'Keep this value hidden at all times.', 'twitchpress' ),
                    'id'              => 'twitchpress_app_secret',
                    'default'         => '',
                    'type'            => 'password',
                    'autoload'        => false,
                ),

                array(
                    'title'           => __( 'Code', 'twitchpress' ),
                    'desc'            => __( 'Created by Twitch.tv only.', 'twitchpress' ),
                    'id'              => 'twitchpress_main_channels_code',
                    'default'         => '',
                    'type'            => 'password',
                    'autoload'        => false,
                    'custom_attributes' => array( 'disabled' => 'disabled' ),
                ),

                array(
                    'title'           => __( 'Token', 'twitchpress' ),
                    'desc'            => __( 'Created by Twitch.tv only.', 'twitchpress' ),
                    'id'              => 'twitchpress_main_channels_token',
                    'default'         => '',
                    'type'            => 'password',
                    'autoload'        => false,
                    'custom_attributes' => array( 'disabled' => 'disabled' ),
                ),

                array(
                    'type'     => 'sectionend',
                    'id'     => 'mainapplicationcredentials'
                )

            ));
            
        // Domain to Twitch API permission Options
        } elseif ( 'default' == $current_section ) {
    
            $default = 'no';
            
            $scopes = array(); 
            $scopes[] = array(
                'title' => __( 'Global Scope', 'twitchpress' ),
                'type'     => 'title',
                'desc'     => __( 'A scope with <span class="dashicons dashicons-yes"></span> indicates that it is required and <span class="dashicons dashicons-no"></span> suggests it is not. Scopes are a type of permission. You set the scope of required permissions for a visitor to fully use your service. The visitor will see the list of scopes when they are sent to Twitch.tv (through oAuth2) to give your site permissions. Please learn and understand all scopes. You should only select the ones your service requires.', 'twitchpress' ),
                'id'     => 'global_scope_options',
            ); 
                
            $scopes[] = array(
                'title'           => __( 'Select Acceptable Scopes', 'twitchpress' ),
                'desc'            => __( 'user_read: Read access to non-public user information, such as email address.', 'twitchpress' ),
                'id'              => 'twitchpress_scope_user_read',
                'default'         => $default,
                'type'            => 'scopecheckbox',
                'checkboxgroup'   => 'start',
                'show_if_checked' => 'option',
                'scope'           => 'user_read',
            );
                                
            foreach( twitchpress_scopes() as $scope => $info ) {
                $scope_edited = str_replace( ':', '_', $scope );
                $scopes[] = array(
                    'desc'            => $scope . ': ' . $info['apidesc'],
                    'id'              => 'twitchpress_scope_' . $scope_edited,
                    'default'         => $default,
                    'type'            => 'scopecheckbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => $scope_edited,
                );
            }
                
            $scopes[] = array(
                'type'     => 'sectionend',
                'id'     => 'global_scope_options'
            );
              
            $scopes[] = array(
                'title' => __( 'Visitor Scopes', 'twitchpress' ),
                'type'     => 'title',
                'desc'     => __( 'These are the permissions users will be asked to accept when using Twitch to login and register.', 'twitchpress' ),
                'id'     => 'visitor_scope_options',
            );
            
            $scopes[] = array(
                'title'           => __( 'Select Acceptable Scopes', 'twitchpress' ),
                'desc'            => __( 'user_read: Read access to non-public user information, such as email address.', 'twitchpress' ),
                'id'              => 'twitchpress_visitor_scope_user_read',
                'default'         => $default,
                'type'            => 'scopecheckboxpublic',
                'checkboxgroup'   => 'start',
                'show_if_checked' => 'option',
                'scope'           => 'user_read',
            );
           
            foreach( twitchpress_scopes() as $scope => $info ) {
                $scope_edited = str_replace( ':', '_', $scope );
                $scopes[] = array(
                    'desc'            => $scope . ': ' . $info['apidesc'],
                    'id'              => 'twitchpress_visitor_scope_' . $scope_edited,
                    'default'         => $default,
                    'type'            => 'scopecheckboxpublic',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                    'scope'           => $scope_edited,
                );
            }
            
            $scopes[] = array(
                'type'     => 'sectionend',
                'id'     => 'visitor_scope_options'
            );
                                  
            $settings = apply_filters( 'twitchpress_permissions_scope_settings', $scopes );
            
        } elseif ( 'syncvalues' == $current_section ) {
            $settings = apply_filters( 'twitchpress_syncvalues_twitch_settings', array(
 
                array(
                    'title' => __( 'Activate Syncronizing: Grouped Data', 'twitchpress' ),
                    'type'  => 'title',
                    'desc'  => __( 'Select the data groups and purposes your site will need to operate the services you plan to offer. A group will store and update more than one value.', 'twitchpress' ),
                    'id'    => 'syncgroupedvaluesettings',
                ),

                array(
                    'desc'            => __( 'Subscribers - import all of your channels subscribers and use the data to improve subscriber experience. This will create a post for each subscriber which can be used by admin only or displayed to the public as part of your services.', 'twitchpress' ),
                    'id'              => 'twitchpress_sync_switch_channel_subscribers',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => 'start',
                    'show_if_checked' => 'option',
                ),
                
                /*
                array( 
                    'desc'            => __( 'Import all subscribers for building a subscriber aware website."', 'twitchpress' ),
                    'id'              => 'twitchpress_sync_switch_channel_subscribers',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),

                array(
                    'desc'            => __( 'partnered: used by services that monitor a visitors partner status."', 'twitchpress' ),
                    'id'              => 'twitchpress_sync_user_partnered',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => 'end',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                */
                                                  
                array(
                    'type'     => 'sectionend',
                    'id'     => 'syncgroupedvaluesettings'
                ),                    
                
                array(
                    'title' => __( 'Activate Syncronizing: Individual Values', 'twitchpress' ),
                    'type'  => 'title',
                    'desc'  => __( 'The Twitch API returns groups of data for many calls, that cannot be avoided. What can be avoided is storing all the data Twitch returns. If you need a single value from each channel/user to operate a feature and want to avoid storing large amounts of Twitch data that has no used to you. Use the options below to configure TwitchPress to extract the values you need and ignore the rest when making requests to the Twitch API.', 'twitchpress' ),
                    'id'    => 'syncvaluesettings',
                ),

                array(
                    'desc'            => __( 'name: the Twitch username can change.', 'twitchpress' ),
                    'id'              => 'twitchpress_sync_user_name',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => 'start',
                    'show_if_checked' => 'option',
                ),
                
                array(
                    'desc'            => __( 'sub_plan: keep the Twitch subscription plan updated and control WordPress membership levels.', 'twitchpress' ),
                    'id'              => 'twitchpress_sync_user_sub_plan',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => '',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                
                array(
                    'desc'            => __( 'partnered: used by services that monitor a visitors partner status."', 'twitchpress' ),
                    'id'              => 'twitchpress_sync_user_partnered',
                    'default'         => 'yes',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => 'end',
                    'show_if_checked' => 'yes',
                    'autoload'        => false,
                ),
                                                  
                array(
                    'type'     => 'sectionend',
                    'id'     => 'syncvaluesettings'
                ),

            ));   
        } elseif ( 'webhooksinstallation' == $current_section ) {
            $settings = apply_filters( 'twitchpress_webhook_installation_settings', array(
            
                array(
                    'title' => __( 'Webhooks Installation', 'twitchpress' ),
                    'type'  => 'title',
                    'desc'  => __( 'Webhook system requires an additional database table to be installed.', 'twitchpress' ),
                    'id'    => 'webhooksinstallation'
                ),
                
                array(
                    'id'              => 'webhooks_first_installation_request',
                    'default'         => true,
                    'type'            => 'hidden',
                ),
                
                array(
                    'id'              => 'twitchpress_webhooks_switch',
                    'default'         => 'yes',
                    'type'            => 'hidden',
                ),
                                              
                array(
                    'type'     => 'sectionend',
                    'id'     => 'webhooksinstallation'
                )

            ));   
        } elseif ( 'webhooks' == $current_section ) {
            $settings = apply_filters( 'twitchpress_webhook_settings', array(
            
                array(
                    'title' => __( 'Webhooks', 'twitchpress' ),
                    'type'  => 'title',
                    'desc'  => __( 'Webhooks are a method of allowing Twitch.tv to update your WordPress with changes/events on Twitch.', 'twitch' ),
                    'id'    => 'webhookssettings'
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
                    'type'     => 'sectionend',
                    'id'     => 'webhookssettings'
                )

            ));   
        }        

        return apply_filters( 'twitchpress_get_settings_' . $this->id, $settings, $current_section );
    }
}

endif;

return new TwitchPress_Settings_Twitch();
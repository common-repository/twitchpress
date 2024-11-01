<?php
/**
 * TwitchPress Shortcode for locking Subscriber Only Content
 * 
 * @author Ryan Bayne  
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists( 'TwitchPress_Shortcode_Subscriber_Only_Content' ) ) :

/**
* TwitchPress Shortcode Class
* 
* @version 1.1
*/
class TwitchPress_Shortcode_Subscriber_Only_Content {
    
    var $sub_only_content = null;
    var $output = null;
    var $atts = array( 'empty' );

    public function gate() {
        global $post;

        include_once( TWITCHPRESS_PLUGIN_DIR_PATH . '/includes/classes/class.twitchpress-twitch-button.php' );
        
        // An unlock-content button does not require login...
        if( $this->atts['require_login'] == 'no' ) 
        {
            // If user is logged in and a known Twitch subscriber we can display content...
            if( is_user_logged_in() ) 
            {
                if( twitchpress_is_user_subscribing( get_current_user_id() ) ) {
                    echo $this->sub_only_content;
                    return;
                }
            } 
            
            if( isset( $_COOKIE['twitchsubtier'] ) ) {
                echo $this->sub_only_content;
                return;
            }
            
            // Display content unlock via oAuth2 at twitch...
            $button = new TwitchPress_Twitch_Connect_Button();
            $this->atts['purpose'] = 'twitchsubcontent';
            echo $button->new_button( $post->ID, $this->atts['text'], $this->atts['scope'], $this->atts['id'], $this->atts );            
            return;                  
        }

        // Visitor must be logged into the blog...
        if( !is_user_logged_in() ) { 
            echo $this->atts['defaulttext'];
            return; 
        }
        
        // Visitor must be subscribing to the main channel...
        if( !twitchpress_is_user_subscribing( get_current_user_id() ) ) {
            echo $this->atts['nosubtext'];
            return;
        }   
        
        echo $this->sub_only_content;
    }

    public function output() {
        ob_start(); 
        $this->gate();
        return ob_get_clean();
    }  
}

endif;

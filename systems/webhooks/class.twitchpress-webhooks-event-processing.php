<?php
/**
* TwitchPress Webhooks Event Processing
* 
* Extends the class TwitchPress_Background_Processing() which runs which allows
* queing of tasks and asyncronus processing and WordPress CRON...
*
* @author Ryan Bayne
* @package TwitchPress/Webhooks
* @version 1.0
*/

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'Direct script access is not allowed!' ); 

if ( ! class_exists ( 'TwitchPress_Webhooks_Event_Processing' ) ) :

class TwitchPress_Webhooks_Event_Processing extends TwitchPress_Background_Processing {
    /**
    * The specific action that this class will perform...
    * 
    * @var mixed
    */
    protected $action = 'twitch_webhooks_processing'; 
    
    /**
    * Listens for the $_POST request performed by wp_remote_post() in class.async-request.php...
    * 
    * The $_GET is performed
    * @version 1.0
    */
    public function process_handler() {
        if ( ! isset( $_POST['twitchpress_webhooks_queue'] ) || ! isset( $_POST['_wpnonce'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'process') ) {
            return;
        }

        if ( 'single' === $_POST['process_size'] ) {
            $this->handle_single();
        }

        if ( 'all' === $_POST['process_size'] ) {
            $this->handle_all();
        }        
    }
    
    protected function task( $item ) {
        
        # actions to perform based on the webhook post
        
        # get the web hook post
        
        # determine what webhook type it is 
        
        # get all actions from the post (post meta)
        
        # call each applicable method/class to process the webhook notification 
        
        // Remove the item (event) because it is finished...
        return false; 
    }  
    
    /**
    * Run an arbitrary task on completion...
    * 
    * @version 1.0
    */
    protected function complete() {
        parent::complete(); 
    }

    protected function handle_single() {

    }

    protected function handle_all() {

    }
}

endif;
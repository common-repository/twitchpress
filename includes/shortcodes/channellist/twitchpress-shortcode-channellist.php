<?php
/**
 * TwitchPress Shortcode - Channel List
 * 
 * @author Ryan Bayne  
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists( 'TwitchPress_Shortcode_Channel_List' ) ) :

class TwitchPress_Shortcode_Channel_List {
    
    var $atts = array( 'empty' );
    var $response= null;
    public array $channels = array();
    public string $message = '';// Used in default output, set it to explain the lack of content 

    public function init() {
        add_action( 'wp_enqueue_scripts', array( $this, 'register_styles'), 4 );   
        $this->register_styles();
        $this->get_twitch_data();
        $this->prepare_data(); // orderby, blacklist, priority channel positioning etc 
    }
    
    /**
    * Get data from Twitch...
    * 
    * @version 1.0
    */
    public function get_twitch_data() {     
        $twitch_api = new TwitchPress_Twitch_API();
        switch ( $this->atts['type'] ) {
           case 'team':  
                if( !isset( $this->atts['team'] ) ) {
                    $this->message = __( 'Please add the team attribute to the shortcode used in this content.', 'twitchpress' );
                    return;
                }
                
                // Get the team
                $this->response = $twitch_api->get_team( $this->atts['team'] );
                
                // Get all user ID from team
                $user_id_array = array_column( $this->response->data[0]->users, 'user_id' ); 

                // Requests stream data
                $returned_channels = $twitch_api->get_streams( null, null, null, 100, null, null, $user_id_array, null ); 
               
                foreach( $returned_channels->data as $key => $user ) {

                    // Get broadcasters total views
                    $channel = $twitch_api->get_user_by_id( $user->user_id );

                    self::array_construct_next_channel( $key, 
                                                        $user->user_id,
                                                        $user->user_login,
                                                        $channel->data[0]->profile_image_url,
                                                        $user->user_name,
                                                        __( 'Live', 'twitchpress' ),
                                                        twitchpress_get_follower_count( $user->user_id ),
                                                        $user->game_name,
                                                        $user->thumbnail_url,
                                                        $user->viewer_count,
                                                        'online',
                                                        $channel->data[0]->view_count );                    
                }
                            
             break;          
           case 'followers':
                return false; // TODO - add function for retrieving followers of giving or default channel
             break;
           case 'specific':
                
             break;
           default:                          
                $returned_channels = $twitch_api->get_streams( null, null, null, 10, null, null, null, null ); 

                foreach( $returned_channels->data as $key => $user ) {  
                    // Get broadcasters followers...
                    //$followers = $twitch_api->get_users_follows_to_id( null, null, $user->user_id );
                    $followers_count = twitchpress_get_follower_count( $user->user_id );

                    // Get broadcasters total views...
                    $channel = $twitch_api->get_user_by_id( $user->user_id ); 
                    
                    // Pass values needed by the output...
                    self::array_construct_next_channel( $key, 
                                                        $user->user_id,
                                                        $user->user_login,
                                                        $channel->data[0]->profile_image_url,
                                                        $user->user_name,
                                                        __( 'Live', 'twitchpress' ),
                                                        $followers_count,
                                                        $user->game_name,
                                                        $user->thumbnail_url,
                                                        $user->viewer_count,
                                                        'online',
                                                        $channel->data[0]->view_count );
                }
             break;             
        }  

        unset($twitch_api);          
    }
    
    public function array_construct_next_channel( int $key, int $user_id, string $user_login, string $logo, string $display_name, string $status, int $followers, string $game, string $thumb, int $viewers, string $display, int $views ) {
        $this->channels[$key]['user_id'] = $user_id;      
        $this->channels[$key]['name'] = $user_login;      
        $this->channels[$key]['logo'] = $logo;   
        $this->channels[$key]['display_name'] = $display_name;   
        $this->channels[$key]['status'] = $status;   
        $this->channels[$key]['followers'] = $followers;   
        $this->channels[$key]['game'] = $game;   
        $this->channels[$key]['thumbnail_url'] = $thumb;   
        $this->channels[$key]['viewer_count'] = $viewers;     
        $this->channels[$key]['display'] = $display;   
        $this->channels[$key]['views'] = $views;        
    }

    /**
    * Make additional alterations to data that can be applied to
    * the results for all endpoints...
    * 
    * @version 1.0
    */
    public function prepare_data() {
        // Order
        if( $this->atts['orderby'] ) {
            $this->channels = wp_list_sort(
                $this->channels,
                $this->atts['orderby'],
                'DESC',
                true
            );
        }        
    }
    
    public function register_scripts() {
  
    }  
    
    /**
    * Register styles for channel list shortcode. 
    * Constants currently set in core pending proper integration using API. 
    *   
    * @version 1.2
    */
    public function register_styles() {
        wp_enqueue_style( 'dashicons' );                                             
        wp_register_style( 'twitchpress_shortcode_channellist', TWITCHPRESS_PLUGIN_URL . '/includes/shortcodes/channellist/twitchpress-shortcode-channellist.css' );   
        wp_enqueue_style( 'twitchpress_shortcode_channellist', TWITCHPRESS_PLUGIN_URL . '/includes/shortcodes/channellist/twitchpress-shortcode-channellist.css' );
    }
    
    public function output() {
        switch ( $this->atts['style'] ) {
           case 'error':
                return $this->atts['error'];
             break; 
           case 'shutters':
                return $this->style_shutters();
             break;
           default:
                return $this->style_shutters();
             break;
        }    
    }
    
    /**
     * Outputs channels in a shutters
     *
     * @return void
     * 
     * @version 2.0.0
     */
    public function style_shutters() {
        ob_start(); 
        
        $online = '';
        $offline = '';
        $closed = '';
        $articles = 0; /* number of html articles generated */

        // Get all the user ID's for adding to a single API call...
        $user_id_array = array();
        
        // If no channels...
        if( !$this->channels ) {?>
            <main>
                <section><?php echo $this->message; ?></section>   
                <section id="open"></section>
            </main>
            <?php 
            return ob_get_clean();
        }
        
        foreach( $this->channels as $key => $user ) {   
                 
            // Build article HTML based on the output requested i.e. online or offline only or all...
            if( $user['display'] !== 'offline' ) {
                $thumbnail_url = str_replace( array( '{width}', '{height}'), array( '640', '360' ), $user['thumbnail_url'] );
                $online .= $this->shutter_article( $user, 'online', $user['viewer_count'], $thumbnail_url );
            } elseif( $user['display'] == 'all' || $user['display'] == 'offline' ) {
                $offline .= $this->shutter_article( $user, 'offline', 0 );
            } 
               
            unset( $stream_obj );
        }         
        
        // Wrap articles in section html...
        $online_section = '<section id="online">' . $online . '</section>';
        $html_offline = '<section id="offline">' . $offline . '</section>'; 
        ?>
        
        <main>
            <?php 
            // All this is simply to avoid outputting empty section HTML...
            if( $user['display'] == 'all' || $user['display'] == 'online' ){ echo $online_section; } 
            if( $user['display'] == 'all' || $user['display'] == 'offline' ){ echo $html_offline; } 
            ?>
        </main>
           
        <?php  
        return ob_get_clean();
    }
    
    /**
    * HTML structure for a single channel (article)
    * 
    * @param mixed $user
    * @param mixed $status
    * @param mixed $viewer_count
    * @param mixed $preview
    * 
    * @version 2.0
    */
    static function shutter_article( $user, $status, $viewer_count = 0, $preview = '' ) {
        ob_start(); 
        ?>
            <article class="channel" id="<?php echo esc_attr( $user['name'] ); ?>">                                
            
                <a class="channel-link" href="https://www.twitch.tv/<?php echo esc_url( $user['name'] ); ?>" target="_blank">                                    
                
                    <header class="channel-primary row">                                        
                        <div class="channel-logo col-s">
                            <img src="<?php echo esc_url( $user['logo'] ); ?>">
                        </div>                                        
                        <div class="col-lg">                                            
                            <div class="row">                                                
                                <h3 class="channel-name"><?php echo esc_attr( $user['display_name'] ); ?></h3>                                                
                                <div class="channel-curr-status"><?php echo esc_attr( $status ); ?></div>                                            
                            </div>                                            
                            <div class="channel-status row"><?php echo esc_attr( $user['status'] ); ?></div>                                        
                        </div>                                    
                    </header>
                    
                    <div class="stream-preview row">
                        <img src="<?php echo esc_attr( $preview ); ?>">
                    </div>
                    <div class="channel-details row">                                    
                        <ul class="channel-stats">                                        
                            <li><i class="dashicons dashicons-heart"></i><?php echo esc_attr( $user['followers'] ); ?></li>  
                            <li><i class="dashicons dashicons-visibility"></i><?php echo esc_attr( $user['views'] ); ?></li>                                    
                        </ul>
                        <div class="stream-details">                                    
                            <span class="stream-game"><?php echo esc_attr( $user['game'] ); ?></span>
                            <span class="stream-stats">
                            <i class="dashicons dashicons-admin-users"></i><?php echo esc_attr( $viewer_count ); ?></span>                                
                        </div>
                        <div class="more-btn">
                            <i class="fa fa-chevron-down"></i> 
                        </div>
                    </div>
                </a>
            </article>
        <?php        
        return ob_get_clean();   
    }

}

endif;

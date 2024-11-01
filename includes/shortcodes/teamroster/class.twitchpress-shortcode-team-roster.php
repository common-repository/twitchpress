<?php
/**
 * TwitchPress Shortcode - Team Roster
 * 
 * @author Ryan Bayne  
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists( 'TwitchPress_Shortcode_Team_Roster' ) ) :

class TwitchPress_Shortcode_Team_Roster {
    
    var $atts = array( 'empty' );
    var $response= null;
    var $channels = array();

    public function init() {          
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_css'), 4 );   
        $this->enqueue_public_css();
        $this->get_twitch_data();
        $this->prepare_data(); // orderby, blacklist, priority channel positioning etc 
    }
    
    public function get_twitch_data() {
        $twitch_api = new TwitchPress_Twitch_API();
        
        $team = $twitch_api->get_team( 'test' );
           
        foreach( $team->data[0]->users as $key => $user ) {   
            
            // Get broadcasters followers...
            $followers = $twitch_api->get_users_follows_to_id( null, null, $user->user_id );
                
            // Get broadcasters total views...
            $channel = $twitch_api->get_user_by_id( $user->user_id );
               
            if( !$channel->data ) {
                continue;
            }
            
            $this->channels[$key]['user_id'] = $user->user_id;      
            $this->channels[$key]['name'] = $user->user_login;      
            $this->channels[$key]['logo'] = $channel->data[0]->profile_image_url;   
            $this->channels[$key]['display_name'] = $user->user_name;      
            $this->channels[$key]['followers'] = $followers->total;               
            $this->channels[$key]['display'] = 'offline';   
            $this->channels[$key]['views'] = $channel->data[0]->view_count;  
            $this->channels[$key]['description'] = $channel->data[0]->description;  
            $this->channels[$key]['broadcaster_type'] = $channel->data[0]->broadcaster_type;  
        }

        unset($twitch_api);          
    }
    
    /**
    * Make additional alterations to data that can be applied to
    * the results for all endpoints...
    * 
    * @version 1.0
    */
    public function prepare_data() {
        // Order
        if( isset( $this->atts['orderby'] ) && $this->atts['orderby'] ) {
            $this->channels = wp_list_sort(
                $this->channels,
                $this->atts['orderby'],
                'DESC',
                true
            );
        }        
    }

    /**
    * Register styles for channel list shortcode. 
    * Constants currently set in core pending proper integration using API. 
    *   
    * @version 1.0
    */
    public function enqueue_public_css() {
        if( !isset( $this->atts['style'] ) ) { $this->atts['style'] = 'table'; }
        switch ( $this->atts['style'] ) {
           case 'table':                                                          
                    wp_register_style( 'twitchpress_shortcode_teamroster', TWITCHPRESS_PLUGIN_URL . '/includes/shortcodes/teamroster/twitchpress-shortcode-teamroster-table.css' );   
                    wp_enqueue_style( 'twitchpress_shortcode_teamroster', TWITCHPRESS_PLUGIN_URL . '/includes/shortcodes/teamroster/twitchpress-shortcode-teamroster-table.css' );                
             break;
           case 'horizontal':                                                          
                    wp_register_style( 'twitchpress_shortcode_teamroster', TWITCHPRESS_PLUGIN_URL . '/includes/shortcodes/teamroster/twitchpress-shortcode-teamroster-table.css' );   
                    wp_enqueue_style( 'twitchpress_shortcode_teamroster', TWITCHPRESS_PLUGIN_URL . '/includes/shortcodes/teamroster/twitchpress-shortcode-teamroster-table.css' );                
             break;

        }        
    }
    
    public function output() {      
        switch ( $this->atts['style'] ) {
           case 'error':
                return $this->atts['error'];
             break; 
           case 'table':
                return $this->style_table();
             break;
           case 'horizontal':
                return $this->style_horizontal();
             break;
           default:
                return $this->style_table();
             break;
        }    
    }
    public function style_table() {
        ob_start(); 

        // Get all the user ID's for adding to a single API call...
        $user_id_array = array();

        // If no team members, the team name is probably incorrect...
        if( !$this->channels ) {
            ?>
            <p>No team members returned by Twitch.tv</p>
            <?php 
            return ob_get_clean();
        } 
        ?>
        
        <main>
            <div class="divTable">
                <div class="divTableBody">        
                    <?php 
                    foreach( $this->channels as $key => $user ) {   
                    ?>
                    <article class="channel" id="<?php echo esc_attr( $user['user_id'] ); ?>">
                        <div class="divTableRow">
                            <div class="divTableCell"><img width="150" height="150" src="<?php echo esc_url( $user['logo'] ); ?>"></div>
                            <div class="divTableCell"><?php echo esc_attr( $user['name'] ); ?></div>
                            <div class="divTableCell">Followers: <?php echo esc_attr( $user['followers'] ); ?> <br> Views: <?php echo esc_attr( $user['views'] ); ?></div>
                        </div>
                    </article>
                    <?php
                    } 
                    ?>
                </div>
            </div>            
        </main>
           
        <?php  
        return ob_get_clean();    
    }

    /**
    * Three column roster layout...
    * 
    * @link https://www.w3schools.com/howto/howto_css_team.asp
    * 
    * @version 1.0
    */
    public function style_horizontal() {
        ?>

        <?php 
        ob_start(); 

        // Get all the user ID's for adding to a single API call...
        $user_id_array = array();

        // If no team members, the team name is probably incorrect...
        if( !$this->channels ) {
            ?>
            <p>No team members returned by Twitch.tv</p>
            <?php 
            return ob_get_clean();
        } 

        $column_count = 0;
        foreach( $this->channels as $key => $user ) {   
            if( $column_count == 0 ) {
                echo '<div class="row">';    
            }?>        

          <div class="column">
            <div class="card">
              <img src="<?php echo esc_url( $user['logo'] ); ?>" alt="<?php echo esc_attr( $user['display_name'] ); ?>" style="width:100%">
              <div class="container">
                <h2><?php echo esc_attr( $user['display_name'] ); ?></h2>
                <p class="title"><?php echo $user['broadcaster_type']; ?></p>
                <p><?php echo $user['description']; ?></p>
                <p>Followers: <?php echo esc_attr( $user['followers'] ); ?></p>
                <p><button class="button">Views: <?php echo esc_attr( $user['views'] ); ?></button></p>
              </div>
            </div>
          </div>

            <?php
            if( $column_count == 3 ) {
                echo '</div>';   
                $column_count = 0; 
            } else {
                ++$column_count;
            }       
        } 
        ?>
                               
        <?php  
        return ob_get_clean();
    }
}

endif;

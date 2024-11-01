<?php
/** 
* TwitchPress Install Example Pages
* 
* This class will install many example pages including an index page that
* lists all other pages created for easier browsing. 
* 
* @package TwitchPress
* @author Ryan Bayne   
* @since 1.1
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! class_exists( 'TwitchPress_Install_Examples' ) ) :

class TwitchPress_Install_Examples {
    public static function everything() {
        $pages_array = self::pages_array();
        foreach( $pages_array as $key => $page ) {
            $page['post_type'] = 'page';
            $page['post_author'] = 1;
            $page['post_status'] = 'private';
            $pages_array[$key]['post_id'] = wp_insert_post( $page );
        }
  
        // Generate content for an index page...
        $index = '<ol>';
        foreach( $pages_array as $key => $page ) {
            $url = get_post_permalink( $page['post_id'], true );
            $index .= '<li><a href="' . $url . '">'. get_the_title( $page['post_id'] ) . '</a></li>';        
        }
        $index .= '</ol>';
        
        // Create the index page... 
        $id = wp_insert_post( array(
              'post_title'    => 'TwitchPress Examples Index',
              'post_content'  => $index,
              'post_status'   => 'private',
              'post_author'   => 1,
            )
        ); 
    }
    
    public static function pages_array() {
        return array(
            array(
              'post_title'    => 'TwitchPress Example: Login',
              'post_content'  => self::example_content_login(),
            ),            
            array(
              'post_title'    => 'TwitchPress Example: Video and Chat',
              'post_content'  => self::example_content_embedeverything(),
            ), 
            array(
              'post_title'    => 'TwitchPress Example: Random Channels List',
              'post_content'  => self::example_content_channel_list(),
            ),            
            array(
              'post_title'    => 'TwitchPress Example: Ambassadors Team Channel List',
              'post_content'  => self::example_content_channel_list_team(),
            ),               
            array(
              'post_title'    => 'TwitchPress Example: Display Game Information',
              'post_content'  => self::example_content_get_game(),
            ),
            array(
              'post_title'    => 'TwitchPress Example: Basic Clips List',
              'post_content'  => self::example_content_get_clips(),
            ),
            array(
              'post_title'    => 'TwitchPress Example: Display Videos',
              'post_content'  => self::example_content_videos(),
            ),
            array(
              'post_title'    => 'TwitchPress Example: Top Games List',
              'post_content'  => self::example_content_top_games_list(),
            ),
            array(
              'post_title'    => 'TwitchPress Example: Display Channel Status',
              'post_content'  => self::example_content_channel_status(),
            ),
            array(
              'post_title'    => 'TwitchPress Example: Display Status Line',
              'post_content'  => self::example_content_status_line(),
            ),
            array(
              'post_title'    => 'TwitchPress Example: Status Box',
              'post_content'  => self::example_content_status_box(),
            ),
            array(
              'post_title'    => 'TwitchPress Example: Twitch Connect Button',
              'post_content'  => self::example_content_twitch_connect_button(),
            ),
            array(
              'post_title'    => 'TwitchPress Example: Follower Only Content',
              'post_content'  => self::example_content_followers_only(),
            ),  
            array(
              'post_title'    => 'TwitchPress Example: Live Stream Default Content Multiple Videos',
              'post_content'  => self::example_content_live_stream_default_videos(),
            ),  
            array(
              'post_title'    => 'TwitchPress Example: Live Stream Default Content Single Video',
              'post_content'  => self::example_content_live_stream_default_video(),
            ),  
                               
        );
    }
    
    public static function example_content_login() {
        $shortcode = '[twitchpress_connect_button]';
        $prepend = '';
        $append = '';
        return $prepend . $shortcode . $append;   
    }    
    
    public static function example_content_embedeverything() {
        $shortcode = sprintf( '[twitchpress_embed_everything channel="%s"]', twitchpress_get_main_channels_name() );
        $prepend = '';
        $append = '';
        return $prepend . $shortcode . $append;   
    }
       
    public static function example_content_channel_list() {
        $shortcode = '[twitchpress_shortcodes type="team" team="test" shortcode="channel_list"]';
        $prepend = '';
        $append = '';
        return $prepend . $shortcode . $append;   
    }
       
    public static function example_content_channel_list_team() {
      $shortcode = '[twitchpress_shortcodes type="team" team="ambassadors" shortcode="channel_list"]';
      $prepend = '';
      $append = '';
      return $prepend . $shortcode . $append;   
    }

    public static function example_content_get_game() {
        $shortcode = '[twitchpress_shortcodes shortcode="get_game" refresh="500" game_name="Conan Exiles"]';
        $prepend = '';
        $append = '';
        return $prepend . $shortcode . $append;   
    }

    public static function example_content_get_clips() {
        $shortcode = '[twitchpress_shortcodes shortcode="get_clips" refresh="500" broadcaster_id="120841817"]';
        $prepend = '';
        $append = '';
        return $prepend . $shortcode . $append;   
    }

    public static function example_content_videos() {
        $shortcode = sprintf( '[twitchpress_videos user_id="%s"]', twitchpress_get_main_channels_twitchid() );
        $prepend = '';
        $append = '';
        return $prepend . $shortcode . $append;   
    }

    public static function example_content_top_games_list() {
        $shortcode = '[twitchpress_get_top_games_list total="5"]';
        $prepend = '';
        $append = '';
        return $prepend . $shortcode . $append;   
    }

    public static function example_content_channel_status() {
        $shortcode = sprintf( '[twitchpress_channel_status channel_name="%s"]', twitchpress_get_main_channels_name() );
        $prepend = '';
        $append = '';
        return $prepend . $shortcode . $append;   
    }

    public static function example_content_status_line() {
        $shortcode = sprintf( '[twitchpress_channel_status_line channel_id="%s"]', twitchpress_get_main_channels_twitchid() );
        $prepend = '';
        $append = '';
        return $prepend . $shortcode . $append;   
    }

    public static function example_content_status_box() {
        $shortcode = sprintf( '[twitchpress_shortcode_channel_status_box channel_id="%s"]', twitchpress_get_main_channels_twitchid() );
        $prepend = '';
        $append = '';
        return $prepend . $shortcode . $append;   
    }

    public static function example_content_twitch_connect_button() {
        $shortcode = '[twitchpress_connect_button]';
        $prepend = '';
        $append = '';
        return $prepend . $shortcode . $append;   
    }

    public static function example_content_followers_only() {
        $shortcode = '[twitchpress_followers_only]Some content for Twitch.tv followers only.[/twitchpress_followers_only]';
        $prepend = '';
        $append = '';
        return $prepend . $shortcode . $append;   
    }
    
    public static function example_content_live_stream_default_videos() {
        $shortcode = '[twitchpress_embed_everything channel="LOLinDark1" defaultcontent="videos"]';
        $prepend = '';
        $append = '';
        return $prepend . $shortcode . $append;   
    }
    
    public static function example_content_live_stream_default_video() {
        $shortcode = '[twitchpress_embed_everything channel="LOLinDark1" defaultcontent="video" videoid="1040648073"]';
        $prepend = '';
        $append = '';
        return $prepend . $shortcode . $append;   
    }
    
}

endif;


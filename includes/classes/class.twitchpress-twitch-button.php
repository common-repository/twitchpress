<?php
/**
* Returns the HTML for a Twitch oAuth button...
* 
* @version 5.0
*/
if ( ! class_exists( 'TwitchPress_Twitch_Connect_Button' ) ) :

class TwitchPress_Twitch_Connect_Button {
    
    public function new_button( $page_id, $text, $scope, $purpose, $atts ) {

        // Shortcode attributes.
        $atts = shortcode_atts( array(
            'loginpageid'        => $page_id,
            'text'               => $text,
            'purpose'            => 'twitchconnectbutton', 
            'style'              => 0,
            'random14'           => twitchpress_random14(),
            'successurl'         => null,// URL visitor is sent to on successful login. 
            'wpmlapplysubdomain' => false,
            'returntopost'       => true,
            'failureurl'         => null,
            'view'               => 'post',
            'scope'              => array( 'user:read:email' )
        ), $atts, 'twitchpress_connect_button' );
        
        // Prepare scopes array for adding to URL...
        if( is_array( $scope ) ) {
            $scope = twitchpress_prepare_scopes( $scope, true );
        }
        
        set_transient( 'twitchpress_oauth_' . $atts['random14'], $atts, 6000 );

        $url = self::generate_url( $scope, $atts['random14'] );
        
        return self::html( $url, $atts['text'], $atts['style'] );
    }
    
    public function generate_url( $scope_array, $random14 ) {
        return 'https://id.twitch.tv/oauth2/authorize?' .
            'response_type=code&' .
            'client_id=' . twitchpress_get_app_id() . '&' .
            'redirect_uri=' . get_option( 'twitchpress_app_redirect', __( 'Redirect Value Not Set In WordPress', 'twitchpress' ) ) . '&' .
            'scope=' . $scope_array . '&' .
            'state=' . $random14;       
    }
    
    public function html( $url, $text, $style = 0 ) {   
        ob_start();
        switch ( $style ) {
            case 0:
                ?>
                <div id="twitchpress_connect_button" class="ui text container">
                  <div class="ui inverted segment">
                    <a class="ui twitch button" href="<?php echo $url; ?>">
                      <i class="twitch icon"></i><?php echo $text; ?>
                    </a>
                  </div>
                </div> 
                <?php
            break;
            case 1:
                ?>
                <div class="twitchpress-connect-button-one">';
                <a href="<?php echo $url; ?>"><?php echo $text; ?></a>
                </div>
                <?php 
            break;
            default:
                ?>
                <div id="twitchpress_connect_button" class="ui text container">
                  <div class="ui inverted segment">
                    <a class="ui twitch button" href="<?php echo $url; ?>">
                      <i class="twitch icon"></i><?php echo $text; ?>
                    </a>
                  </div>
                </div> 
                <?php
            break;
        }
        return ob_get_clean();
    }
}

endif; 
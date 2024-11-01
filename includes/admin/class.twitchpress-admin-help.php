<?php
/**
 * Add the default content to the help tab.
 *
 * @author      Ryan Bayne
 * @category    Admin
 * @package     TwitchPress/Admin
 * @version     1.0.0
 */
          
if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

if ( ! class_exists( 'TwitchPress_Admin_Help', false ) ) :
           
class TwitchPress_Admin_Help {

    /**
     * Add Contextual help tabs.
     * 
     * @version 1.0
     */
    public function add_tabs() {
        $screen = get_current_screen();
                                       
        if ( ! $screen || ! in_array( $screen->id, twitchpress_get_screen_ids() ) ) {             
            return;
        }                                                                                                                                            
        
        $page = empty( $_GET['page'] ) ? '' : sanitize_title( $_GET['page'] );
        $tab  = empty( $_GET['tab'] )  ? '' : sanitize_title( $_GET['tab'] );
          
        /**
        * This is the right side sidebar, usually displaying a list of links. 
        * 
        * @var {WP_Screen|WP_Screen}
        * 
        * @version 2.0
        */
        $screen->set_help_sidebar(
            apply_filters( 'twitchpress_set_help_sidebar',
                '<p><strong>' . __( 'For more information:', 'twitchpress' ) . '</strong></p>' .
                '<p><a href="https://github.com/ryanbayne/twitchpress" target="_blank">' . __( 'GitHub', 'twitchpress' ) . '</a></p>' .
                '<p><a href="https://twitchpress.wordpress.com" target="_blank">' . __( 'Blog', 'twitchpress' ) . '</a></p>'.
                '<p><a href="https://discord.gg/ScrhXPE" target="_blank">' . __( 'Discord', 'twitchpress' ) . '</a></p>' .
                '<p><a href="https://twitch.tv/lolindark1" target="_blank">' . __( 'My Twitch', 'twitchpress' ) . '</a></p>' . 
                '<p><a href="https://dev.twitch.tv/dashboard/apps" target="_blank">' . __( 'Twitch Dev Apps', 'twitchpress' ) . '</a></p>' . 
                '<p><a href="https://dev.twitch.tv/docs/api/reference/" target="_blank">' . __( 'Twitch Dev Docs', 'twitchpress' ) . '</a></p>' .            
                '<p><a href="https://www.patreon.com/twitchpress" target="_blank">' . __( 'Patron Pledges', 'twitchpress' ) . '</a></p>'
            )
        );
                
        $screen->add_help_tab( 
            apply_filters( 'twitchpress_help_tab_support', 
                array(
                    'id'        => 'twitchpress_support_tab',
                    'title'     => __( 'Help &amp; Support', 'twitchpress' ),
                    'content'   => '<h2>' . __( 'Help &amp; Support', 'twitchpress' ) . '</h2>' . 
                    '<p><a href="https://github.com/RyanBayne/twitchpress/issues" class="button button-primary">' . __( 'Bugs', 'twitchpress' ) . '</a> </p>' . 
                    //'<h2>' . __( 'Pointers Tutorial', 'twitchpress' ) . '</h2>' .
                    //'<p>' . __( 'The plugin will explain some features using WordPress pointers.', 'twitchpress' ) . '</p>' .
                    //'<p><a href="' . admin_url( 'admin.php?page=twitchpress&amp;twitchpresstutorial=normal' ) . '" class="button button-primary">' . __( 'Start Tutorial', 'twitchpress' ) . '</a></p>' .
                    '<h2>' . __( 'Report A Bug', 'twitchpress' ) . '</h2>' .
                    '<p>You could save a lot of people a lot of time by reporting issues. Tell the developers and community what has gone wrong by creating a ticket. Please explain what you were doing, what you expected from your actions and what actually happened. Screenshots and short videos are often a big help as the evidence saves us time, we will give you cookies in return.</p>' .  
                    '<p><a href="' . TWITCHPRESS_GITHUB . '/issues?state=open' . '" class="button button-primary">' . __( 'Report a bug', 'twitchpress' ) . '</a></p>',
                )
            ) 
        );
        
        ########################################################################
        #                                                                      #
        #                          INSTALLATION TAB                            #
        #                                                                      #
        ########################################################################                          
        $nonce = wp_create_nonce( 'tool_action' );
        
        $screen->add_help_tab( 
            apply_filters( 'twitchpress_help_tab_installation',
                array(
                    'id'        => 'twitchpress_installation_tab',
                    'title'     => __( 'Installation', 'twitchpress' ),
                    'content'   =>
                        '<h2>' . __( 'Setup Wizard', 'twitchpress' ) . '</h2>' .
                        '<p>' . __( 'You can use the Setup Wizard more than once and not just on the first installation.', 'twitchpress' ) . '</p>' .
                        '<p><a href="' . admin_url( 'index.php?page=twitchpress-setup' ) . '" class="button button-primary">' . __( 'Twitch API Setup Wizard', 'twitchpress' ) . '</a></p>' .
                        '<h2>' . __( 'Authorize Main Channel', 'twitchpress' ) . '</h2>' .
                        '<p>' . __( 'This procedure is included in the Setup Wizard but you can run it here if you need to re-authorize your main Twitch channel. This procedure will take you through oAuth and generate an API user token.', 'twitchpress' ) . '</p>' .
                        '<p><a href="' . admin_url( 'admin.php?page=twitchpress_tools&_wpnonce=' . $nonce . '&toolname=tool_authorize_main_channel' ) . '" class="button button-primary">' . __( 'Connect to Twitch', 'twitchpress' ) . '</a></p>',
                    'callback'  => array( $this, 'installation' ),
                )
            ) 
        );   
        
        ########################################################################
        #                                                                      #
        #                          CONTRIBUTION TAB                            #
        #                                                                      #
        ########################################################################                                   
        $screen->add_help_tab( 
            apply_filters( 'twitchpress_help_tab_contribute',
                array(
                    'id'        => 'twitchpress_contribute_tab',
                    'title'     => __( 'Contribute', 'twitchpress' ),
                    'content'   => '<h2>' . __( 'Everyone Can Contribute', 'twitchpress' ) . '</h2>' .
                    '<p>' . __( 'You can contribute in many ways and by doing so you will help the project thrive.' ) . '</p>' .
                    '<p><a href="' . TWITCHPRESS_DONATE . '" class="button button-primary">' . __( 'Donate', 'twitchpress' ) . '</a> <a href="' . TWITCHPRESS_GITHUB . '/wiki" class="button button-primary">' . __( 'Update Wiki', 'twitchpress' ) . '</a> <a href="' . TWITCHPRESS_GITHUB . '/issues" class="button button-primary">' . __( 'Fix Bugs', 'twitchpress' ) . '</a></p>',
                ) 
            ) 
        );

        $screen->add_help_tab( array(
            'id'        => 'twitchpress_newsletter_tab',
            'title'     => __( 'News Mail', 'twitchpress' ),
            'content'   => '<h2>' . __( 'Annual News', 'twitchpress' ) . '</h2>' .
            '<p>' . __( 'Mailchip is used to manage the projects newsletter subscribers list.' ) . '</p>' .
            '<p>' . '<!-- Begin MailChimp Signup Form -->
                <link href="//cdn-images.mailchimp.com/embedcode/classic-10_7.css" rel="stylesheet" type="text/css">
                <style type="text/css">         
                    #mc_embed_signup{background:#f6fbfd; clear:left; font:14px Helvetica,Arial,sans-serif; }
                    /* Add your own MailChimp form style overrides in your site stylesheet or in this style block.
                       We recommend moving this block and the preceding CSS link to the HEAD of your HTML file. */
                </style>
                <div id="mc_embed_signup">
                <form action="//webtechglobal.us9.list-manage.com/subscribe/post?u=99272fe1772de14ff2be02fe6&amp;id=b9058458e5" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
                    <div id="mc_embed_signup_scroll">
                    <h2>TwitchPress News by Email</h2>
                <div class="indicates-required"><span class="asterisk">*</span> indicates required</div>
                <div class="mc-field-group">
                    <label for="mce-EMAIL">Email Address  <span class="asterisk">*</span>
                </label>
                    <input type="email" value="" name="EMAIL" class="required email" id="mce-EMAIL">
                </div>
                <div class="mc-field-group">
                    <label for="mce-FNAME">First Name </label>
                    <input type="text" value="" name="FNAME" class="" id="mce-FNAME">
                </div>
                <div class="mc-field-group">
                    <label for="mce-LNAME">Last Name </label>
                    <input type="text" value="" name="LNAME" class="" id="mce-LNAME">
                </div>
                <p>Powered by <a href="http://eepurl.com/2W_2n" title="MailChimp - email marketing made easy and fun">MailChimp</a></p>
                    <div id="mce-responses" class="clear">
                        <div class="response" id="mce-error-response" style="display:none"></div>
                        <div class="response" id="mce-success-response" style="display:none"></div>
                    </div>    <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
                    <div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_99272fe1772de14ff2be02fe6_b9058458e5" tabindex="-1" value=""></div>
                    <div class="clear"><input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="button"></div>
                    </div>
                </form>
                </div>
                <script type=\'text/javascript\' src=\'//s3.amazonaws.com/downloads.mailchimp.com/js/mc-validate.js\'></script><script type=\'text/javascript\'>(function($) {window.fnames = new Array(); window.ftypes = new Array();fnames[0]=\'EMAIL\';ftypes[0]=\'email\';fnames[1]=\'FNAME\';ftypes[1]=\'text\';fnames[2]=\'LNAME\';ftypes[2]=\'text\';}(jQuery));var $mcj = jQuery.noConflict(true);</script>
                <!--End mc_embed_signup-->' . '</p>',
        ) );
        
        $screen->add_help_tab( 
            apply_filters( 'twitchpress_help_tab_credits', 
                array(
                    'id'        => 'twitchpress_credits_tab',
                    'title'     => __( 'Credits', 'twitchpress' ),
                    'content'   => '<h2>' . __( 'Credits', 'twitchpress' ) . '</h2>' .
                    '<p>Please do not remove credits from the plugin. You may edit them or give credit somewhere else in your project.</p>' . 
                    '<h4>' . __( 'Automattic - This plugins core is largely based on their WooCommerce plugin.' ) . '</h4>' .
                    '<h4>' . __( 'Brian at WPMUDEV - our discussion led to this project and entirely new approach in my development.' ) . '</h4>' . 
                    '<h4>' . __( 'Ignacio Cruz at WPMUDEV - has giving us a good approach to handling shortcodes.' ) . '</h4>' .
                    '<h4>' . __( 'Ashley Rich (A5shleyRich) - author of a crucial piece of the puzzle, related to asynchronous background tasks.' ) . '</h4>' .
                    '<h4>' . __( 'Igor Vaynberg - thank you for an elegant solution to searching within a menu.' ) . '</h4>',
                    '<h4>' . __( 'Nookyyy - a constant supporter who is building Nookyyy.com using TwitchPress.' ) . '</h4>'
                )
            ) 
        );
                    
        $screen->add_help_tab( array(
            'id'        => 'twitchpress_faq_tab',
            'title'     => __( 'FAQ', 'twitchpress' ),
            'content'   => '',
            'callback'  => array( $this, 'faq' ),
        ) );     
                    
        $screen->add_help_tab( array(
            'id'        => 'twitchpress_app_status_tab',
            'title'     => __( 'Twitch App Status', 'twitchpress' ),
            'content'   => '',
            'callback'  => array( $this, 'app_status' ),
        ) );
                
        $screen->add_help_tab( array(
            'id'        => 'twitchpress_channel_status_tab',
            'title'     => __( 'Twitch Channel Status', 'twitchpress' ),
            'content'   => '',
            'callback'  => array( $this, 'channel_status' ),
        ) );
                
        $screen->add_help_tab( array(
            'id'        => 'twitchpress_testing_tab',
            'title'     => __( 'Testing', 'twitchpress' ),
            'content'   => '',
            'callback'  => array( $this, 'testing' ),
        ) );
                        
        $screen->add_help_tab( array(
            'id'        => 'twitchpress_development_tab',
            'title'     => __( 'Development', 'twitchpress' ),
            'content'   => '',
            'callback'  => array( $this, 'development' ),
        ) );
              
    }
    
    /**
    * Installation tab in help section.
    * 
    * @version 3.0
    */
    public function installation() {     
  
    }
    
    /**
    * FAQ menu uses script to display a selected answer.
    * 
    * @version 1.2
    */
    public function faq() {
        $questions = array(
            0 => __( '-- Select a question --', 'twitchpress' ),
            1 => __( 'Can I create my own extensions?', 'twitchpress' ),
            2 => __( 'How much would it cost for a custom extension?', 'twitchpress' ),
            3 => __( 'Does the plugin support Twitch API version 6?', 'twitchpress' ),
        );  
        
        $questions = apply_filters( 'twitchpress_faq', $questions );
        ?>

        <style>
            .faq-answers li {
                background:white;
                padding:10px 20px;
                border:1px solid #cacaca;
            }
        </style>

        <p>
            <ul id="faq-index">
                <?php foreach ( $questions as $question_index => $question ): ?>
                    <li data-answer="<?php echo $question_index; ?>"><a href="#q<?php echo $question_index; ?>"><?php echo $question; ?></a></li>
                <?php endforeach; ?>
            </ul>
        </p>
        
        <ul class="faq-answers">
            <li class="faq-answer" id='q1'>
                <?php _e('Yes, if you have experience with PHP and WordPress you can create an extension for TwitchPress. You can submit your extension to the WordPress.org repository for the community to use or keep it private or sell it as a premium extension. Please invite me to the projects GitHub for support.', 'twitchpress');?>
            </li>
            <li class="faq-answer" id='q2'>
                <p> <?php _e('You can hire me to create a new extension from as little as $30.00 and if you make the extension available to the WordPress community I will charge 50% less. I will also put from free hours into improving it which I cannot do if you request a private extension.', 'twitchpress');?> </p>
            </li>        
            <li class="faq-answer" id='q3'>
                <p> <?php _e('Twitch API version 6 is being added to TwitchPress but it will not be ready for testing until April 2018.', 'twitchpress');?> </p>
            </li>        
        </ul>                     
             
        <script>
            jQuery( document).ready( function( $ ) {
                var selectedQuestion = '';

                function selectQuestion() {
                    var q = $( '#' + $(this).val() );
                    if ( selectedQuestion.length ) {
                        selectedQuestion.hide();
                    }
                    q.show();
                    selectedQuestion = q;
                }

                var faqAnswers = $('.faq-answer');
                var faqIndex = $('#faq-index');
                faqAnswers.hide();
                faqIndex.hide();

                var indexSelector = $('<select/>')
                    .attr( 'id', 'question-selector' )
                    .addClass( 'widefat' );
                var questions = faqIndex.find( 'li' );
                var advancedGroup = false;
                questions.each( function () {
                    var self = $(this);
                    var answer = self.data('answer');
                    var text = self.text();
                    var option;

                    if ( answer === 39 ) {
                        advancedGroup = $( '<optgroup />' )
                            .attr( 'label', "<?php _e( 'Advanced: This part of FAQ requires some knowledge about HTML, PHP and/or WordPress coding.', 'twitchpress' ); ?>" );

                        indexSelector.append( advancedGroup );
                    }

                    if ( answer !== '' && text !== '' ) {
                        option = $( '<option/>' )
                            .val( 'q' + answer )
                            .text( text );
                        if ( advancedGroup ) {
                            advancedGroup.append( option );
                        }
                        else {
                            indexSelector.append( option );
                        }

                    }

                });

                faqIndex.after( indexSelector );
                indexSelector.before(
                    $('<label />')
                        .attr( 'for', 'question-selector' )
                        .text( "<?php _e( 'Select a question', 'twitchpress' ); ?>" )
                        .addClass( 'screen-reader-text' )
                );

                indexSelector.change( selectQuestion );
            });
        </script>        

        <?php 
    }
          
    /**
    * Displays Twitch application status. 
    * 
    * This focuses on the services main Twitch application credentials only.
    * 
    * @author Ryan Bayne
    * @version 3.1
    */
    public function app_status() {
        $set_app_status = twitchpress_get_app_status();
        
        // Ensure the Twitch API application has been setup...
        if( $set_app_status[0] !== 1 ) {
            echo '<h3>' . __( 'Welcome to TwitchPress', 'twitchpress' ) . '</h3>';
            echo $set_app_status[1];             
            echo '<p><a href="' . admin_url( 'index.php?page=twitchpress-setup' ) . '" class="button button-primary">' . __( 'Setup wizard', 'twitchpress' ) . '</a></p>';           
            return;    
        }                                  
                    
        // Check for existing cache.
        $cache = get_transient( 'twitchpresshelptabappstatus' );
        if( $cache )                                                                                          
        {
            _e( '<p>You are viewing cached data that is up to 120 seconds old. Refresh again soon to get the latest data.</p>', 'twitchpress' );
            print $cache;                                                                                              
            return;
        }                                                                                                          
        else
        {
            // No existing cache found, so test Twitch API, generate output, cache output, output output!
            _e( '<p>You are viewing real-time data on this request (not cached). The data will be cached for 120 seconds.</p>', 'twitchpress' );  
        }
        
        // Define variables. 
        $overall_result = true;
        $channel_display_name = __( 'Not Found', 'twitchpress' );
        $channel_status = __( 'Not Found', 'twitchpress' );
        $channel_game = __( 'Not Found', 'twitchpress' );
        $current_user_id = get_current_user_id();
                                          
        $output = '<h2>' . __( 'Application Credentials', 'twitchpress' ) . '</h2>';
        $output .= '<p>Old App ID Method: ' . twitchpress_get_main_client_id() . '</p>';
        $output .= '<p>New App ID Method: ' . twitchpress_get_app_id() . '</p>';
        $output .= '<p>App Redirect: ' . twitchpress_get_app_redirect() . '</p>';

        // Test Get Application Token
        $output .= '<h2>' . __( 'Test: Get Application Token', 'twitchpress' ) . '</h2>';

        if( twitchpress_get_app_token() )
        {
            $output .= __( 'Result: Token Exists!' ); 
        }
        else
        { 
            $output .= __( 'Result: No Application Token Found' ); 
            $overall_result = false; 
        }

        if( !$overall_result ) {
            $output .= '<h3>' . __( 'Overall Result: Not Ready!', 'twitchpress' ) . '</h3>';
        } else {
            $output .= '<h3>' . __( 'Overall Result: Ready!', 'twitchpress' ) . '</h3>';            
        }

        // Avoid making these requests for every admin page request. 
        set_transient( 'twitchpresshelptabappstatus', $output, 120 );

        print $output;    
        
        print sprintf( __( 'Please check Twitch.tv status %s before creating fault reports.' ), '<a target="_blank" href="https://twitchstatus.com/">here</a>' );   
    }
    
    /**
    * Displays Twitch application status. 
    * 
    * This focuses on the services main Twitch application credentials only.
    * 
    * @author Ryan Bayne
    * @version 2.4
    */
    public function channel_status() {
        $app_status = twitchpress_get_app_status();
        
        // Ensure the Twitch API application has been setup...
        if( $app_status[0] !== 1 ) {
            echo '<h3>' . __( 'Welcome to TwitchPress', 'twitchpress' ) . '</h3>';
            echo $app_status[1];             
            echo '<p><a href="' . admin_url( 'index.php?page=twitchpress-setup' ) . '" class="button button-primary">' . __( 'Setup wizard', 'twitchpress' ) . '</a></p>';           
            return;    
        }
                
        // Check for existing cache.
        $cache = get_transient( 'twitchpresshelptabchannelstatus' );
        if( $cache ) 
        {
            _e( '<p>You are viewing cached data that is up to 120 seconds old. Refresh again soon to get the latest data.</p>', 'twitchpress' );
            print $cache; 
            return;
        }
        else
        {
            // No existing cache found, so test Twitch API, generate output, cache output, output output!
            _e( '<p>You are viewing real-time data on this request (not cached). The data will be cached for 120 seconds.</p>', 'twitchpress' );  
        }
        
        // Define variables. 
        $overall_result = true;
        $channel_display_name = __( 'Not Found', 'twitchpress' );
        $channel_status = __( 'Not Found', 'twitchpress' );
        $channel_game = __( 'Not Found', 'twitchpress' );
        $current_user_id = get_current_user_id();
        $output = '';

        $helix = new TWITCHPRESS_Twitch_API();
        $twitch_user = $helix->get_user_without_email_by_login_name( twitchpress_get_main_channels_name() );

        $output .= '<h2>' . __( 'Main Channel Credentials', 'twitchpress' ) . '</h2>';
        $output .= '<p>Main Channel Name: ' .          twitchpress_get_main_channels_name() . '</p>';
        $output .= '<p>Main Channel Twitch ID: ' .     twitchpress_get_main_channels_twitchid() . '</p>';
        $output .= '<p>Main Channel WP Post ID: ' .    twitchpress_get_main_channels_postid() . '</p>';
        $output .= '<p>Main Channel Code: ' .          twitchpress_get_main_channels_code() . '</p>';
        $output .= '<p>Main Channel WP Owner ID: ' .   twitchpress_get_main_channels_wpowner_id() . '</p>';
        $output .= '<p>Main Channel Refresh Token: ' . twitchpress_get_main_channels_refresh() . '</p>';

        // Confirm Main Channel
        $output .= '<h2>' . __( 'Main Channel Submitted (Home of Application)', 'twitchpress' ) . '</h2>';
        $output .= '<p>' . twitchpress_get_main_channels_name() . '</p>';
        
        // Test Get Application Token
        $output .= '<h2>' . __( 'Test: Get Main Channel (Users) oAuth Token', 'twitchpress' ) . '</h2>';
        
        $token_result = twitchpress_get_main_channels_token();
        if( $token_result ){$output .= __( 'Result: Main channel token exists!' ); }
        else{ $output .= __( 'Result: No channel token found' ); $overall_result = false; }
        
        if( !$overall_result ) {
            $output .= '<h3>' . __( 'Overall Result: Not Ready!', 'twitchpress' ) . '</h3>';
        } else {
            $output .= '<h3>' . __( 'Overall Result: Ready!', 'twitchpress' ) . '</h3>';            
        }
        
        // Avoid making these requests for every admin page request. 
        set_transient( 'twitchpresshelptabchannelstatus', $output, 120 );

        print $output;    
    }
    
    public function testing() {
        $tool_action_nonce = wp_create_nonce( 'tool_action' );
        
        ob_start();
        echo '<h3>Test New Features</h3>';
        echo '<p>' . __( 'Do not test on live sites.', 'twitchpress' ) . '</p>';
        
        // New Test
        echo '<h2>' . __( 'Embed Everything Shortcode: Default Videos', 'twitchpress' ) . '</h2>';
        echo '<p>' . __( 'Test the ability to display a channels default videos when the stream is offline.', 'twitchpress' ) . '</p>';
        echo '<p>[twitchpress_embed_everything defaultcontent="videos"]</p>';
        
        // New Test
        echo '<h2>' . __( 'Embed Everything Shortcode: Default Video', 'twitchpress' ) . '</h2>';
        echo '<p>' . __( 'Test the ability to display a specific video when the stream is offline.', 'twitchpress' ) . '</p>';
        echo '<p>[twitchpress_embed_everything defaultcontent="video" videoid="1040648073"]</p>';
                
        // New Test
        echo '<h2>' . __( 'Authorize Bot Channel', 'twitchpress' ) . '</h2>';
        echo '<p>' . __( 'Logout of your main Twitch account on Twitch.tv before using this feature.', 'twitchpress' ) . '</p>';
        echo '<p><a href="' . admin_url( 'admin.php?page=twitchpress_tools&_wpnonce=' . $tool_action_nonce . '&toolname=tool_authorize_bot_channel' ) . '" class="button button-primary">' . __( 'Connect to Twitch', 'twitchpress' ) . '</a></p>';
        
        // New Test
        echo '<h2>' . __( 'YouTube (Google API) Setup Wizard', 'twitchpress' ) . '</h2>';
        echo '<p>' . __( 'Add a set of Google API credentials created for requesting YouTube data.', 'twitchpress' ) . '</p>';
        echo '<p><a href="' . admin_url( 'index.php?page=twitchpress-setup-youtube' ) . '" class="button button-primary">' . __( 'YouTube Setup Wizard', 'twitchpress' ) . '</a></p>';
        echo '<p><a href="' . admin_url( 'admin.php?page=twitchpress_tools&_wpnonce=' . $tool_action_nonce . '&toolname=tool_google_api_test' ) . '" class="button button-primary">' . __( 'Test Google API (YouTube)', 'twitchpress' ) . '</a></p>';
        
        // New Test
        echo '<h2>' . __( 'Follower Only Shortcode', 'twitchpress' ) . '</h2>';
        echo '<p>Hide content from visitors unless they follow your main channel on Twitch.tv - please test and monitor occasionally over 24 hours before live use.</p>';
        echo '<p>[twitchpress_followers_only]Gated content goes here.[/twitchpress_followers_only]</p>';
        
        // New Test
        echo '<h2>' . __( 'Team Roster Shortcode', 'twitchpress' ) . '</h2>';
        echo '<p>Hide content from .</p>';
        echo '<p>[twitchpress_shortcodes shortcode="team_roster" team_id="team_id" team_name="TEST" style="horizontal"]</p>';

        ob_end_flush();
    }    
    
    public function development() {
        ob_start();
        echo '<h3>Development Area</h3>';
        echo '<p>' . __( 'Do not use these features on live sites. Feel free to test features 
        but do not feedback faults.', 'twitchpress' ) . '</p>';
        
        // New Test
        echo '<h2>' . __( 'Raffle Entry Shortcode', 'twitchpress' ) . '</h2>';
        echo '<p>This is the first feature within the giveaways system. It will display a button for quick raffle entry.</p>';
        echo '<p>[twitchpress_raffle_entry_button]Gated content goes here.[/twitchpress_raffle_entry_button]</p>';
        
        ob_end_flush();                
    }
}

endif;

$class = new TwitchPress_Admin_Help();

add_action( 'current_screen', array( $class, 'add_tabs' ), 50 );

unset( $class );
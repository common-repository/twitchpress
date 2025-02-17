<?php
/**
 * View a single BugNet trace...
 *
 * @author      Ryan Bayne
 * @category    Admin
 * @package     TwitchPress/Admin
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists( 'TwitchPress_View_Trace' ) ) :

class TwitchPress_View_Trace {

    /** @var string Current Step */
    private $step = '';

    /** @var array Steps for the setup wizard */
    private $steps = array();

    /** @var boolean Is the wizard optional or required? */
    private $optional = false;

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'admin_menus' ) );
        add_action( 'admin_init', array( $this, 'trace_view' ) ); 
    }

    public function admin_menus() {
        add_dashboard_page( '', '', 'manage_options', 'twitchpress-traces', '' );
    }

    public function trace_view() {
        if ( empty( $_GET['page'] ) || 'twitchpress-traces' !== $_GET['page'] ) {
            return;
        }
        
        $this->steps = array(
            'introduction' => array(
                'name'    =>  __( 'Introduction', 'twitchpress' ),
                'view'    => array( $this, 'twitchpress_setup_in' ),
                'handler' => ''
            )
        );
        
        // Queue CSS for the entire setup process.
        wp_enqueue_style( 'twitchpress_admin_styles', TWITCHPRESS_PLUGIN_URL . '/assets/css/admin.css', array(), TWITCHPRESS_VERSION );
        wp_enqueue_style( 'twitchpress-setup', TWITCHPRESS_PLUGIN_URL . '/assets/css/twitchpress-setup.css', array( 'dashicons', 'install' ), TWITCHPRESS_VERSION );
        wp_register_script( 'twitchpress-setup', TWITCHPRESS_PLUGIN_URL . '/assets/js/admin/twitchpress-setup.min.js', array( 'jquery', 'twitchpress-enhanced-select', 'jquery-blockui' ), TWITCHPRESS_VERSION );

        if ( ! empty( $_POST['save_step'] ) && isset( $this->steps[ $this->step ]['handler'] ) ) {
            call_user_func( $this->steps[ $this->step ]['handler'] );
        }
    
        ob_start();
        $this->head();
        $this->content();
        $this->footer();
        exit;
    }

    public function head() {        
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta name="viewport" content="width=device-width" />
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title><?php _e( 'WordPress TwitchPress &rsaquo; Setup Wizard', 'twitchpress' ); ?></title>
            <?php wp_print_scripts( 'twitchpress-setup' ); ?>
            <?php do_action( 'admin_print_styles' ); ?>
            <?php do_action( 'admin_head' ); ?>
        </head>
        <body class="twitchpress-setup wp-core-ui">
            <h1 id="twitchpress-logo"><a href="<?php echo TWITCHPRESS_HOME;?>"><img src="<?php echo TWITCHPRESS_PLUGIN_URL; ?>/assets/images/twitchpress_logo.png" alt="TwitchPress" /></a></h1>
        <?php
    }

    public function footer() { 
        if ( 'next_steps' === $this->step ) : ?>
                <a class="twitchpress-return-to-dashboard" href="<?php echo esc_url( admin_url() ); ?>"><?php _e( 'Return to the WordPress Dashboard', 'twitchpress' ); ?></a>
            <?php endif; ?>
            </body>
        </html>
        <?php
    }

    public function content() {           
        echo '<div class="twitchpress-setup-content">'; 

        TwitchPress_Admin_Notices::output_custom_notices();
        $this->trace();

        echo '</div>';
    }

    public function trace() { ?>            

        <p><?php _e( 'You are viewing a trace generated by BugNet for the purpose of debugging background activity. 
        Each trace will be different due to the nature of the procedure being monitored.', 'twitchpress' ); ?></p>

        <?php 
        $t = bugnet_get_trace_by_trace_id( $_GET['trace_id'] );

        $time = sprintf( __( '%s ago', 'twitchpress' ), human_time_diff( strtotime( $t->timestamp ), time() ) );        
        ?>

        <h2><?php echo $t->name; ?></h2>
        <ul>
            <li><?php _e( 'ID: ', 'twitchpress' ); echo $t->id; ?></li>
            <li><?php _e( 'Code: ', 'twitchpress' ); echo $t->code; ?></li>
            <li><?php _e( 'Time: ', 'twitchpress' ); echo $time; ?></li>
            <li><?php _e( 'Line: ', 'twitchpress' ); echo $t->line; ?></li>
            <li><?php _e( 'Function: ', 'twitchpress' ); echo $t->function; ?></li>
        </ul>
        
        <h2><?php _e( 'Trace Steps' );?></h2>

        <form method="post">
            <table class="form-table">
            <?php echo $this->steps_table_rows( $t->code ); ?>
            </table>
        </form>  
              
        <h2><?php _e( 'Trace Meta' );?></h2>

        <form method="post">
            <table class="form-table">
            <?php echo $this->meta_table_rows( $t->code ); ?>
            </table>
        </form>
        
        <p class="twitchpress-setup-actions step">
            <a href="" class="button-primary button button-large button-next"><?php _e( 'Report', 'twitchpress' ); ?></a>
            <a href="" class="button button-large" target="_blank"><?php _e( 'Return', 'twitchpress' ); ?></a>
        </p>

        <?php 
    }
    
    static function meta_table_rows( $code ) {
        $m = bugnet_get_trace_meta( $code );

        $html = sprintf( '<tr><th>%s</th><th>%s</th><th>%s</th></tr>', __( 'Meta Key', 'twitchpress' ), __( 'Meta Value', 'twitchpress' ), __( 'Timer', 'twitchpress' ) );

        if( $m ) {
            foreach( $m as $k => $meta_array ) {
                
                unset( $meta_array->meta_id );
                unset( $meta_array->code );
                unset( $meta_array->request );
                
                $html .= '<tr>';       
                                                                         
                foreach( $meta_array as $key => $value ) {
                    $html .= '<td>' . $value . '</td>';
                }
                
                $html .= '</tr>';
            }
        }    
        
        return $html;    
    }    
    
    static function steps_table_rows( $code ) {
        $m = bugnet_get_trace_steps( $code );

        $html = sprintf( '<tr><th>%s</th><th>%s</th></tr>', __( 'Description', 'twitchpress' ), __( 'Timer', 'twitchpress' ) );
        
        $previous_microtime = 0;
        
        if( $m ) {
            foreach( $m as $k => $meta_array ) {
                
                unset( $meta_array->step_id );
                unset( $meta_array->code );
                unset( $meta_array->request );
                unset( $meta_array->line );
                unset( $meta_array->function );
                
                $html .= '<tr>';       
                                                                         
                // Convert microtime to date format for easier reading...
                $micro = sprintf("%06d",( $meta_array->microtime - floor( $meta_array->microtime ) ) * 1000000);
                $d = new DateTime( date('Y-m-d H:i:s.' . $micro, $meta_array->microtime ) );
                $meta_array->microtime = $d->format("Y-m-d H:i:s.u"); // note at point on "u"

                foreach( $meta_array as $key => $value ) {
                    $html .= '<td>' . $value . '</td>';
                }
                
                $html .= '</tr>';
                
                $previous_microtime = $meta_array->microtime;
            }
        }    
        
        return $html;    
    }
}

endif;

// This file is conditionally included...
new TwitchPress_View_Trace();
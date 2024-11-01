<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Requires core WP List Table class...
if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class.wp-list-table.php' );
}

/**
 * List table for viewing all API activity...
 *
 * @author      Ryan Bayne
 * @category    Admin
 * @package     TwitchPress/Views
 * @version     1.0
 */
class TwitchPress_ListTable_APIActivity extends WP_List_Table {

    /**
     * Max items.
     *
     * @var int
     */
    protected $max_items;

    public $items = array();
    
    /**
     * Constructor.
     */
    public function __construct() {

        parent::__construct( array(
            'singular'  => __( 'Entry', 'twitchpress' ),
            'plural'    => __( 'Entries', 'twitchpress' ),
            'ajax'      => false
        ) );
        
        // Apply default items to the $items object.
        $this->default_items();
    }   
    
    /**
    * Setup default items. 
    * 
    * This is not required and was only implemented for demonstration purposes. 
    * 
    * @version 1.3
    */
    public function default_items() {
        global $wpdb;
        $entry_counter = 0;// Acts as temporary ID for data that does not have one. 
        
        $activity = $wpdb->twitchpress_activity;
        $meta = $wpdb->twitchpress_meta;
        
        /*
        $records = $wpdb->get_results( "
            SELECT a.*,m.metavalue AS rawbody  
            FROM $activity a
            INNER JOIN $meta m    
            ON a.entryid = m.entryid
            WHERE m.metakey = 'rawbody' 
        ", 
        'OBJECT' );
        */
        
        //$records = twitchpress_db_selectorderby( $wpdb->twitchpress_activity, null, 'callid' );       

        /*
        $records = $wpdb->get_results( "
        SELECT a.*, m1.metavalue AS rawresponse, m2.metavalue AS rawbody, m3.metavalue as rawheader
        FROM $activity AS a
        LEFT JOIN $meta AS m1 ON a.entryid = m1.entryid
        LEFT JOIN $meta AS m2 ON a.entryid = m1.entryid
        LEFT JOIN $meta AS m3 ON a.entryid = m1.entryid
        WHERE m1.metakey = 'rawresponse'
        AND m2.metakey = 'rawbody'
        AND m3.metakey = 'rawheader'
        LIMIT 10
        ", 
        'OBJECT' );        
        */
        
        /*
        $records = $wpdb->get_results( "
        SELECT a.*, m1.metavalue AS rawresponse, m2.metavalue AS rawbody, m3.metavalue as rawheader
        FROM $activity AS a
        LEFT JOIN $meta AS m1 ON a.entryid = m1.entryid
        LEFT JOIN $meta AS m2 ON a.entryid = m1.entryid
        LEFT JOIN $meta AS m3 ON a.entryid = m1.entryid
        WHERE m1.metakey = 'rawresponse'
        AND m2.metakey = 'rawbody'
        AND m3.metakey = 'rawheader'
        LIMIT 10
        ", 
        'OBJECT' );        
        */
        
        $records = $wpdb->get_results( "
            SELECT a.*,m.metavalue AS rawresponse  
            FROM $activity a
            INNER JOIN $meta m    
            ON a.entryid = m.entryid
            WHERE m.metakey = 'rawresponse' 
        ", 
        'OBJECT' );
                
        if( !isset( $records ) || !is_array( $records ) ) { $records = array(); } 

        // Loop on individual trace entries. 
        foreach( $records as $key => $row ) {

            ++$entry_counter;
            
            // Create new array entry and get it's key...
            $this->items[]['entry_number'] = $entry_counter; 
            end( $this->items);
            $key = key( $this->items );
            
            $this->items[$key]['entryid']     = $row->entryid;
            $this->items[$key]['callid']      = $row->callid;
            $this->items[$key]['service']     = $row->service;
            $this->items[$key]['type']        = $row->type;
            $this->items[$key]['outcome']     = $row->outcome;
            $this->items[$key]['timestamp']   = $row->timestamp;
            $this->items[$key]['wpuserid']    = $row->wpuserid;     
            $this->items[$key]['rawresponse'] = $row->rawresponse;
        }   
        
        $this->items = array_reverse( $this->items );                                                   
    }
    
    /**
     * No items found text.
     */
    public function no_items() {
        _e( 'No items found.', 'twitchpress' );
    }

    public function display_tablenav( $position ) {
        if ( $position != 'top' ) { parent::display_tablenav( $position ); }
    }

    /**
     * Output the tabled report
     */
    public function output_result() {     
        $this->prepare_items();
        add_thickbox();
        echo '<div id="poststuff" class="twitchpress-tablelist-wide">';
        $this->display();
        echo '</div>';
    }

    /**
     * Get column value.
     *
     * @param mixed $item
     * @param string $column_name   
     * 
     * @version 2.0
     */
    public function column_default( $item, $column_name ) {
        
        $url = add_query_arg( array( 'view_record' => $item['entryid'] ) );
        switch( $column_name ) {
            case 'timestamp' :
                $time_passed = human_time_diff( strtotime( $item['timestamp'] ), current_time( 'timestamp' ) );
                echo sprintf( __( '%s ago', 'twitchpress' ), $time_passed );
                echo '<pre><a href="' . $url . '">' . $item['callid'] . '</a></pre>';
            break;
            case 'callid' :
                echo '<pre>'; print_r( $item['service'] ); echo '</pre>';
                echo '<pre>'; print_r( $item['type'] ); echo '</pre>';
                echo '<pre>'; print_r( $item['outcome'] ); echo '</pre>';                
            break;
            
            case 'user' :
                echo '<pre>'; _e( 'WP ID: ', 'twitchpress' ); print_r( $item['wpuserid'] ); echo '</pre>';
            break;            
        
            case 'type' :
                echo '<pre>'; print_r( $item['type'] ); echo '</pre>';
            break;            
            
            case 'outcome' :
                echo '<pre>'; print_r( $item['outcome'] ); echo '</pre>';
            break;                                    
            
            case 'wpuserid' :
                echo '<pre>'; print_r( $item['wpuserid'] ); echo '</pre>';
            break;  
            case 'rawresponse' :
                echo '<textarea rows="3" cols="25">' . print_r( $item['rawresponse'], true ) . '</textarea>';
            break;                                                                                                       
        }
    }

    /**
     * Get columns.
     *
     * @return array
     */
    public function get_columns() {
        $columns = array(
            'timestamp'   => __( 'Time/Call ID', 'twitchpress' ),
            'callid'      => __( 'General', 'twitchpress' ),
            'user'        => __( 'User', 'twitchpress' ),
            'rawresponse' => __( 'Response', 'twitchpress' ),
            'custom'      => __( 'Custom', 'twitchpress' ),
        );
        return $columns;
    }

    /**
     * Prepare customer list items.
     */
    public function prepare_items() {

        $this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
        $current_page          = absint( $this->get_pagenum() );
        $per_page              = apply_filters( 'twitchpress_listtable_apiactivity_items_per_page', 20 );

        $this->get_items( $current_page, $per_page );

        /**
         * Pagination.
         */
        $this->set_pagination_args( array(
            'total_items' => $this->max_items,
            'per_page'    => $per_page,
            'total_pages' => ceil( $this->max_items / $per_page )
        ) );
    } 
    
    public function display() {        
        if( isset( $_REQUEST['view_record'] ) && is_numeric( $_REQUEST['view_record']  ) ) {
            $this->record_listed( $_REQUEST['view_record']  );
            return;
        }
        
        $singular = $this->_args['singular'];

        $this->display_tablenav( 'top' );

        $this->screen->render_screen_reader_content( 'heading_list' );
        ?>
<table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
    <thead>
    <tr>
        <?php $this->print_column_headers(); ?>
    </tr>
    </thead>

    <tbody id="the-list"
        <?php
        if ( $singular ) {
            echo " data-wp-lists='list:$singular'";
        }
        ?>
        >
        <?php $this->display_rows_or_placeholder(); ?>
    </tbody>

    <tfoot>
    <tr>
        <?php $this->print_column_headers( false ); ?>
    </tr>
    </tfoot>

</table>
        <?php
        $this->display_tablenav( 'bottom' );
    }
    
    public function record_listed( $entry_id ) {
        global $wpdb;
        $entry_counter = 0;// Acts as temporary ID for data that does not have one. 
        
        $activity = $wpdb->twitchpress_activity;
        $meta = $wpdb->twitchpress_meta;
        $records = $wpdb->get_row( "
            SELECT a.*,m.metavalue AS rawbody  
            FROM $activity a
            INNER JOIN $meta m   
            ON a.entryid = m.entryid
            WHERE a.entryid = $entry_id 
        ", 
        'OBJECT' );
                          
        if( !isset( $records ) || !is_object( $records ) ) { 
            _e( 'Record not found', 'twitchpress' );
            return;
        } 
        
        echo '
        <table>
            <tr><td></td><td>' . __( '<strong>Information</strong>', 'twitchpress' ) . '</td></tr>
            <tr><td>' . __( 'Entry ID', 'twitchpress' ) . '</td><td>' . $records->entryid . '</td></tr>
            <tr><td>' . __( 'Call ID', 'twitchpress' ) . '</td><td>' . $records->callid . '</td></tr>
            <tr><td>' . __( 'Service', 'twitchpress' ) . '</td><td>' . $records->service . '</td></tr>
            <tr><td>' . __( 'Type', 'twitchpress' ) . '</td><td>' . $records->type . '</td></tr>
            <tr><td>' . __( 'Status', 'twitchpress' ) . '</td><td>' . $records->status . '</td></tr>
            <tr><td>' . __( 'File', 'twitchpress' ) . '</td><td><a href="' . $records->file . '">' . $records->file . '</a></td></tr>
            <tr><td>' . __( 'Function', 'twitchpress' ) . '</td><td>' . $records->function . '</td></tr>
            <tr><td>' . __( 'Line', 'twitchpress' ) . '</td><td>' . $records->line . '</td></tr>
            <tr><td>' . __( 'WP User ID', 'twitchpress' ) . '</td><td>' . $records->wpuserid . '</td></tr>
            <tr><td>' . __( 'Timestamp', 'twitchpress' ) . '</td><td>' . $records->timestamp . '</td></tr>
            <tr><td>' . __( 'Description', 'twitchpress' ) . '</td><td>' . $records->description . '</td></tr>
            <tr><td>' . __( 'Outcome', 'twitchpress' ) . '</td><td>' . $records->outcome . '</td></tr>
            <tr><td>' . __( 'Life', 'twitchpress' ) . '</td><td>' . $records->life . '</td></tr>
            <tr><td>' . __( 'Rawbody', 'twitchpress' ) . '</td><td>' . $records->rawbody . '</td></tr>
        </table>
        ';
  
    }
    
}
            

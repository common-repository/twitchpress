<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class.wp-list-table.php' );
}

/**
 * Table for viewing all API activity relating to Twitch subscriptions... 
 *
 * @author      Ryan Bayne
 * @category    Admin
 * @package     TwitchPress/Views
 * @version     1.0.0
 */
class TwitchPress_ListTable_TwitchSubs extends WP_List_Table {

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
            'singular'  => __( 'Outcome', 'twitchpress' ),
            'plural'    => __( 'Outcomes', 'twitchpress' ),
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
    * @version 1.2
    */
    public function default_items() {
        global $wpdb;
        
        $entry_counter = 0;// Acts as temporary ID for data that does not have one. 

        $table_activity = $wpdb->twitchpress_activity;
        $table_endpoints = $wpdb->twitchpress_endpoints;
        $meta = $wpdb->twitchpress_meta;
        
        /*
        $select = 
        "SELECT $table_activity.*,$table_endpoints.*"; 
                 
        $join = "
        JOIN $table_endpoints ON $table_activity.entryid = $table_endpoints.entryid";

        $where = "
        WHERE $table_endpoints.endpoint = 'https://api.twitch.tv/helix/subscriptions'";
        */
                
        $select = 
        "SELECT $table_activity.*,$table_endpoints.*,$meta.metavalue AS rawbody"; 
                 
        $join = "
        JOIN $table_endpoints ON $table_activity.entryid = $table_endpoints.entryid
        JOIN $meta ON $meta.entryid = $table_activity.entryid";

        $where = "
        WHERE $table_endpoints.endpoint = 'https://api.twitch.tv/helix/subscriptions'
        AND $meta.metakey = 'rawbody'";
        
        $records = $wpdb->get_results( "$select FROM $table_activity $join $where", OBJECT );        
        
        if( !isset( $records ) || !is_array( $records ) ) { $records = array(); } 

        // Loop on individual trace entries. 
        foreach( $records as $key => $row ) {
                                          
            ++$entry_counter;
            
            $this->items[]['entry_number'] = $entry_counter; 

            // Get the new array key we just created. 
            end( $this->items);
            $new_key = key( $this->items );

            $this->items[$new_key]['entryid']    = $row->entryid;
            $this->items[$new_key]['callid']     = $row->callid;
            $this->items[$new_key]['service']    = $row->service;
            //$this->items[$new_key]['api']        = $row->api; causing undefined notice
            $this->items[$new_key]['type']       = $row->type;
            $this->items[$new_key]['outcome']    = $row->outcome;
            $this->items[$new_key]['timestamp']  = $row->timestamp;
            $this->items[$new_key]['function']   = $row->function;
            $this->items[$new_key]['wpuserid']   = $row->wpuserid;
            $this->items[$new_key]['endpointid'] = $row->endpointid;
            $this->items[$new_key]['endpoint']   = $row->endpoint;
            $this->items[$new_key]['firstuse']   = $row->firstuse;
            $this->items[$new_key]['lastuse']    = $row->lastuse;
            $this->items[$new_key]['counter']    = $row->counter;
            $this->items[$new_key]['parameters'] = $row->parameters;
            $this->items[$new_key]['rawbody']    = $row->rawbody;
        }
        
        $this->items = array_reverse( $this->items );
    }
    
    /**
     * No items found text.
     */
    public function no_items() {
        _e( 'No items found.', 'twitchpress' );
    }

    /**
     * Don't need this.
     *
     * @param string $position
     */
    public function display_tablenav( $position ) {

        if ( $position != 'top' ) {
            parent::display_tablenav( $position );
        }
    }

    /**
     * Output the report.
     */
    public function output_result() {

        $this->prepare_items();
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
     * @version 1.0
     */
    public function column_default( $item, $column_name ) {
        
        switch( $column_name ) {
            case 'entryid' :
                 echo '<pre>'; print_r( $item['entryid'] ); echo '</pre>';
            break; 
            case 'callid' :
                 echo '<pre>'; print_r( $item['callid'] ); echo '</pre>';
            break; 
            case 'service' :
                 echo '<pre>'; print_r( $item['service'] ); echo '</pre>';
            break; 
            case 'api' :
                 echo '<pre>'; print_r( $item['api'] ); echo '</pre>';
            break; 
            case 'type' :
                 echo '<pre>'; print_r( $item['type'] ); echo '</pre>';
            break; 
            case 'outcome' :
                echo '<textarea rows="3" cols="25">' . print_r( $item['outcome'], true ) . '</textarea>';
            break;
            case 'timestamp' : 
                $time_passed = human_time_diff( strtotime( $item['timestamp'] ), time() );
                echo sprintf( __( '%s ago', 'twitchpress' ), $time_passed );         
            break; 
            case 'function' :
                 echo '<pre>'; print_r( $item['function'] ); echo '</pre>';
            break;  
            case 'wpuserid' :
                 echo '<pre>'; print_r( $item['wpuserid'] ); echo '</pre>';
            break; 
            case 'endpointid' :
                 echo '<pre>'; print_r( $item['endpointid'] ); echo '</pre>';
            break; 
            case 'endpoint' :
                 echo '<pre>'; print_r( $item['endpoint'] ); echo '</pre>';
            break; 
            case 'firstuse' :
                 echo '<pre>'; print_r( $item['firstuse'] ); echo '</pre>';
            break; 
            case 'lastuse' :
                 echo '<pre>'; print_r( $item['lastuse'] ); echo '</pre>';
            break; 
            case 'counter' :
                 echo '<pre>'; print_r( $item['counter'] ); echo '</pre>';
            break; 
            case 'parameters' :
                 echo '<textarea rows="4" cols="14">' . print_r( $item['parameters'], true ) . '</textarea>';                 
            break;                                       
            case 'rawbody' :
                 echo '<textarea rows="4" cols="14">' . print_r( $item['rawbody'], true ) . '</textarea>';                 
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
            'entryid'    => __( 'Entry ID', 'twitchpress' ),
            'callid'     => __( 'Call ID', 'twitchpress' ),
            'type'       => __( 'Type', 'twitchpress' ),
            'outcome'    => __( 'Outcome', 'twitchpress' ),
            'timestamp'  => __( 'Time', 'twitchpress' ),
            'lastuse'    => __( 'Last Use', 'twitchpress' ),
            'counter'    => __( 'Used', 'twitchpress' ),
            'parameters' => __( 'Parameters', 'twitchpress' ),
            'rawbody'    => __( 'API Raw Data', 'twitchpress' ),
        );
  
        return $columns;
    }

    /**
     * Prepare customer list items.
     */
    public function prepare_items() {

        $this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
        $current_page          = absint( $this->get_pagenum() );
        $per_page              = apply_filters( 'twitchpress_listtable_twitchsubs_items_per_page', 20 );

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
}

<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class.wp-list-table.php' );
}

/**
 * Table for viewing all API Responses in raw format. 
 *
 * @author      Ryan Bayne
 * @category    Admin
 * @package     TwitchPress/Views
 * @version     1.0.0
 */
class TwitchPress_ListTable_APIOutcomes extends WP_List_Table {

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
        
        $records = twitchpress_db_selectorderby( $wpdb->twitchpress_outcomes, null, 'resultid' );        
        if( !isset( $records ) || !is_array( $records ) ) { $records = array(); } 

        // Loop on individual trace entries. 
        foreach( $records as $key => $row ) {
                                          
            ++$entry_counter;
            
            $this->items[]['entry_number'] = $entry_counter; 

            // Get the new array key we just created. 
            end( $this->items);
            $new_key = key( $this->items );
                                
            // Time of entry example: int 1503562573
            $this->items[$new_key]['resultid']  = $row->resultid;
            $this->items[$new_key]['entryid']   = $row->entryid;
            $this->items[$new_key]['outcome']   = $row->outcome;
            $this->items[$new_key]['timestamp'] = $row->timestamp;
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

            case 'resultid' :
                echo '<pre>'; print_r( $item['resultid'] ); echo '</pre>';
            break;

            case 'entryid' :
                echo '<pre>'; print_r( $item['entryid'] ); echo '</pre>';
            break;          
            
            case 'outcome' :
                echo '<textarea rows="4" cols="30">' . print_r( $item['outcome'], true ) . '</textarea>';
            break;            
            
            case 'timestamp' :

                $time_passed = human_time_diff( strtotime( $item['timestamp'] ), time() );
                echo sprintf( __( '%s ago', 'twitchpress' ), $time_passed );         
              
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
            'resultid'  => __( 'Result ID', 'twitchpress' ),
            'entryid'   => __( 'Request ID', 'twitchpress' ),
            'outcome'   => __( 'Outcome', 'twitchpress' ),
            'timestamp' => __( 'Event Time', 'twitchpress' ),
        );
            
        return $columns;
    }

    /**
     * Prepare customer list items.
     */
    public function prepare_items() {

        $this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
        $current_page          = absint( $this->get_pagenum() );
        $per_page              = apply_filters( 'twitchpress_listtable_apioutcomes_items_per_page', 20 );

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

<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class.wp-list-table.php' );
}

/**
 * TwitchPress_ListTable_Endpoints.
 *
 * @author      Ryan Bayne
 * @category    Admin
 * @package     TwitchPress/Views
 * @version     1.0.0
 */
class TwitchPress_ListTable_Endpoints extends WP_List_Table {

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
            'singular'  => __( 'Endpoint', 'twitchpress' ),
            'plural'    => __( 'Endpoints', 'twitchpress' ),
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
        
        $records = twitchpress_db_selectorderby( $wpdb->twitchpress_endpoints, null, 'endpointid' );        
        if( !isset( $records ) || !is_array( $records ) ) { $records = array(); } 

        // Loop on individual trace entries. 
        foreach( $records as $key => $row ) {
            ++$entry_counter;
            
            // Create new array entry and get it's key...
            $this->items[]['entry_number'] = $entry_counter; 
            end( $this->items);
            $key = key( $this->items );
            
            $this->items[$key]['endpointid'] = $row->endpointid;
            $this->items[$key]['entryid']    = $row->entryid;
            $this->items[$key]['endpoint']   = $row->endpoint;
            $this->items[$key]['firstuse']   = $row->firstuse;
            $this->items[$key]['lastuse']    = $row->lastuse;
            $this->items[$key]['counter']    = $row->counter;
        }   
        
        $this->items = array_reverse( $this->items );
    }
    
    /**
     * No items found text.
     */
    public function no_items() {
        _e( 'No endpoints found.', 'twitchpress' );
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
            case 'endpointid' :
                echo '<pre>'; print_r( $item['endpointid'] ); echo '</pre>';
            break;

            case 'entryid' :
                echo '<pre>'; print_r( $item['entryid'] ); echo '</pre>';
            break;    
                    
            case 'endpoint' :
                echo '<textarea rows="3" cols="25">' . print_r( $item['endpoint'], true ) . '</textarea>';
            break;
                        
            case 'firstuse' :
                echo $item['firstuse'];         
            break;
            
            case 'lastuse' :
                echo $item['lastuse'];         
            break;                        
            
            case 'lastuse' :
                echo $item['lastuse'];         
            break;                        
                       
            case 'counter' :
                echo $item['counter'];         
            break;                                    
        }
    }

    /**
     * Get columns.
     *
     * @return array
     * 
     * @version 2.0
     */
    public function get_columns() {
        $columns = array(
            'endpointid' => __( 'Endpoint ID', 'twitchpress' ),
            'entryid'    => __( 'Entry ID', 'twitchpress' ),
            'endpoint'   => __( 'Endpoint', 'twitchpress' ),
            'firstuse'   => __( 'First Use', 'twitchpress' ),
            'lastuse'    => __( 'Last Use', 'twitchpress' ),
            'counter'     => __( 'Counter', 'twitchpress' ),
        );
            
        return $columns;
    }

    /**
     * Prepare customer list items.
     */
    public function prepare_items() {

        $this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
        $current_page          = absint( $this->get_pagenum() );
        $per_page              = apply_filters( 'twitchpress_listtable_twitchcalls_items_per_page', 20 );

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

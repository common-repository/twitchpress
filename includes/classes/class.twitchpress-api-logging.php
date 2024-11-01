<?php
/** 
* TwitchPress API activity logging...
* 
* @package TwitchPress
* @author Ryan Bayne   
* @since 1.0
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! class_exists( 'TwitchPress_API_Logging' ) ) :

/**
* Replies on logging ID being stored by the API object for updating record...
* 
* @version 1.0
*/
class TwitchPress_API_Logging {
    static function ready() {
        if( get_option( 'twitchpress_api_logging_switch' ) == 'yes' ) {      
            return true;    
        }
        return false;     
    }
    
    /**
    * Inserts a new log record...
    * 
    * @param mixed $call_id
    * @param mixed $service
    * @param mixed $type
    * @param mixed $life
    * @param mixed $meta
    * @param mixed $description
    * @param mixed $backtrace
    * 
    * @return WP_Error or int $entryid (incremental database ID)
    * 
    * @version 2.0
    */
    static function new( $call_id, $service = 'twitch', $type = 'GET', $life = 172800, $meta = array(), $description = 'Unknown', $backtrace_level = 0 ) {
        global $wpdb;
                            
        if( !is_int( $call_id ) || !is_string( $service ) || !is_string( $type ) || !is_int( $life ) || !is_array( $meta ) ) {
            new WP_Error( __CLASS__, __( 'Invalid value type passed to TwitchPress_API_Logging::new()', 'twitchpress') );
            error_log( __( 'TwitchPress API logging has not been setup properly somewhere in the plugin.    ', 'twitchpress' ) );
            return false; 
        }    
                              
        $backtrace = debug_backtrace();

        $values = array( 
            'callid'       => $call_id,
            'service'      => $service, 
            'type'         => $type,
            'file'         => $backtrace[$backtrace_level]['file'],
            'function'     => $backtrace[$backtrace_level]['function'],
            'line'         => $backtrace[$backtrace_level]['line'],
            'description' => $description             
        );

        $current_user_id = get_current_user_id();
        if( is_int( $current_user_id ) && $current_user_id > 0 ) {
            $values = array_merge( $values, array( 'wpuserid' => $current_user_id ) );    
        }
                                         
        $entryid = twitchpress_db_insert( 
            $wpdb->twitchpress_activity, 
            $values 
        );
                 
        self::process_meta( $entryid, $meta );
        
        return $entryid;
    }
    
    /**
    * Adds a new error to an existing record in the case of failure...
    * 
    * This does not include a rejection based on request parameters or a null
    * dataset where the query does not match records. 
    * 
    * @param mixed $entryid
    * @param mixed $code - HTTP code or something similar
    * @param mixed $error
    * @param mixed $server
    * @param mixed $meta
    * 
    * @returns WP_Error always
    * 
    * @version 1.0
    */
    static function error( $entryid, $code, $error, $meta = array() ) {
        global $wpdb;
        
        if( !self::ready() ) { return; }
        
        if( !is_int( $entryid ) || !is_int( $code ) || !is_string( $error ) || !is_array( $meta ) ) {
            return new WP_Error( __CLASS__, __( 'Invalid value type passed to TwitchPress_API_Logging::error()', 'twitchpress') );
        }    
        
        twitchpress_db_insert( 
            $wpdb->twitchpress_error, 
            array( 'entryid' => $entryid, 'code' => $code, 'error' => $error ) 
        );
        
        // Add backtrace values to meta...
        $backtrace = debug_backtrace();
        $meta = array_merge( $meta, array( 
            'file' => $backtrace[0]['file'],
            'line' => $backtrace[0]['line'],
            'function' => $backtrace[0]['function']
        ) );
                        
        self::process_meta( $entryid, $meta );  
        
        return new WP_Error( __CLASS__, $error, $code );
    }
    
    /**
    * Close an entry with a final outcome...
    * 
    * Stores admin readable context of what took place, sometimes using
    * data returned by Twitch to explain changes.   
    * 
    * @param int $entryid
    * @param string $outcome  
    * @param int $life - number of seconds the data should be kept for
    * @param array $meta
    * @return WP_Error - this is the only return done
    * 
    * @version 3.0
    */
    static function outcome( $entryid, $outcome, $life = 129600, $meta = array() ) {
        global $wpdb;
                           
        if( !self::ready() ) { return; }
        
        if( !is_int( $entryid ) || !is_string( $outcome ) || !is_int( $life ) || !is_array( $meta ) ) {
            return new WP_Error( __CLASS__, __( 'Invalid value type passed to TwitchPress_API_Logging::outcome()', 'twitchpress') );
        } 

        twitchpress_db_update( $wpdb->twitchpress_activity, 'entryid = ' . $entryid, array( 'outcome' => $outcome, 'life' => $life ) );
                
        self::process_meta( $entryid, $meta );       
    }
    
    /**
    * Stores the endpoint for an API call or updates the counter for an endpoint...
    * 
    * @param integer $entryid
    * @param string $api
    * @param string $full_endpoint Pass the complete endpoint with parameters
    * @param array $meta
    * @return WP_Error
    * 
    * @version 2.0
    */
    static function endpoint( $entryid, $full_endpoint, $api = 'helix', $meta = array() ) {
        global $wpdb;
        
        if( !self::ready() ) { return; }
        
        if( !is_int( $entryid ) || !is_string( $api ) || !is_string( $full_endpoint ) || !is_array( $meta ) ) {
            return new WP_Error( __CLASS__, __( 'Invalid value type passed to TwitchPress_API_Logging::endpoint()', 'twitchpress') );
        }

        // ...endpoint has parameters...
        if( strstr( $full_endpoint, '?' ) ) {           
            $endpoint = substr( $full_endpoint, 0, strpos( $full_endpoint, "?" ) );
            $parameters = substr( $full_endpoint, strpos( $full_endpoint, "?" ) );            
            $where_query = 'endpoint = "' . $endpoint . '" AND parameters = "' . $parameters . '"';
            $insert_values = array( 'entryid' => $entryid, 'endpoint' => $endpoint, 'parameters' => $parameters );
        } else {
            $where_query = 'endpoint = "' . $full_endpoint . '"';
            $insert_values = array( 'entryid' => $entryid, 'endpoint' => $full_endpoint );
        } 

        // Find matching entry and update it else enter the endpoint for the first time...
        $endpoint_row = twitchpress_db_selectrow( 
            $wpdb->twitchpress_endpoints, 
            $where_query, 
            $select = 'endpointid,counter' 
        );
        
        // ...existing row then increase counter...
        if( $endpoint_row ) {
            $counter = $endpoint_row->counter + 1;
            return twitchpress_db_update( $wpdb->twitchpress_endpoints, 'endpointid = ' . $endpoint_row->endpointid, array( 'counter' => $counter ) );            
        } else {
            return twitchpress_db_insert( $wpdb->twitchpress_endpoints, $insert_values );   
        }
                
        self::process_meta( $entryid, $meta );
    }
    
    /**
    * Used to breakdown every Curl calls standard values into meta...
    * 
    * @param mixed $entryid
    * @param mixed $raw_response
    * 
    * @version 2.1
    */
    static function breakdown( $entryid, $raw_response ) {  
    
        if( get_option( 'twitchpress_api_logging_body_switch' ) == 'yes' ) {      
            self::meta( $entryid, 'rawresponse', $raw_response['body'] );    
        }
                       
        self::meta( $entryid, 'response_code', $raw_response['response']['code'] ); 
        self::meta( $entryid, 'response_message', $raw_response['response']['message'] ); 
    }
    
    /**
    * Adds meta data to an existing record...
    * 
    * @param mixed $entryid
    * @param mixed $metakey
    * @param mixed $metavalue
    * @return WP_Error
    * 
    * @version 1.0
    */
    static function meta( $entryid, $metakey, $metavalue ) {
        global $wpdb;
        
        if( !self::ready() ) { return; }
        
        if( !is_int( $entryid ) || !is_string( $metakey ) ) {
            return new WP_Error( __CLASS__, __( 'Invalid value type passed to TwitchPress_API_Logging::meta()', 'twitchpress') );
        }   

        twitchpress_db_insert( 
            $wpdb->twitchpress_meta, 
            array( 'entryid' => $entryid, 'metakey' => $metakey, 'metavalue' => $metavalue ) 
        );        
    }
    
    /**
    * Processes arrays of meta for other methods in this class...
    * 
    * @param mixed $entryid
    * @param mixed $meta
    *  
    * @return WP_Error
    * 
    * @version 1.0
    */
    private static function process_meta( $entryid, $meta = array() ) {        
        if( empty( $meta ) ) { return false; }
        if( !self::ready() ) { return; }
        
        if( !is_array( $meta ) ) {
            return new WP_Error( __CLASS__, __( 'Invalid value type passed to TwitchPress_API_Logging::process_meta()', 'twitchpress') );    
        }
        
        foreach( $meta as $metakey => $metavalue ) {
            self::meta( $entryid, $metakey, $metavalue );
        }
    }
    
    /**
    * Closes a record by updating the original activity-table entry.
    * This is used within class.twitchpress-curl.php to signal the end of the Curl
    * call, outside of context. 
    * 
    * See other methods for updating a record with additional information within
    * the context of a plugin/wordpress procedure. 
    * 
    * @param mixed $entryid
    * @param mixed $outcome granted|rejected|failure (this is not an API standard but summary)
    * 
    * @version 1.0
    */
    static function end_logging( $entryid, $outcome = 'granted' ) {
        global $wpdb;        
        if( !self::ready() ) { return; }
        twitchpress_db_update( $wpdb->twitchpress_activity, 'entryid = "' . $entryid . '"', array( 'outcome' => $outcome ) );
        
        // Determine if the log entry should be added to a report...
        #TODO  
    } 
}

endif;
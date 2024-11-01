<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! class_exists( 'TwitchPress_Install_Tables' ) ) :

class TwitchPress_Install_Tables {
    
    var $installation_type = 'update';
    
    public $tables = array(
        'twitchpress_activity',  /* entryid, callid, service, function (__FUNCTION__), type (get,post,put,patch,delete), outcome (granted,rejected,failure), life */
        'twitchpress_errors',    /* errorid, entryid, code (usually HTTP code but string allowed), error (message) */
        'twitchpress_endpoints', /* endpointid, api, endpoint, firstuse, lastuse */
        'twitchpress_meta',      /* metaid, entryid, metakey, metavalue */   
    );
        
    public function __construct() {
        if ( ! defined( 'TWITCHPRESS_TABLES_INSTALLING' ) ) {
            define( 'TWITCHPRESS_TABLES_INSTALLING', true );
        }  
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );       
    }
    
    public function install() {
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        // Register primary tables...
        add_action( 'init', array( $this, 'primary_tables_registration' ) );
        add_action( 'switch_blog', array( $this, 'primary_tables_registration' ) );   

        // Install groups of tables or specific services tables...
        switch ( $this->installation_type ) {
           case 'activation':
                $this->activation();
             break;
           case 'update':
                 $this->update();
             break;
           case 'empty':
             break;
           case 'empty':
             break;
           default:
                $this->update();
             break;
        }
    }
    
    /**
    * Procedure ran when first-time or re-activation is happening. 
    * 
    * @param mixed $request_type
    */
    public function activation() { 
        $this->primary_tables(); 
    }  
    
    public function update() {
        $this->primary_tables();
    }
    
    function twitchpress_database_change_versions() {
        
        /*
        $current_db_version = get_option( 'twitchpress_db_version', null );
        
        if ( !is_null( $current_db_version ) && version_compare( $current_db_version, max( array_keys( twitchpress_database_change_versions() ) ), '<' ) ) {
            TwitchPress_Admin_Notices::add_notice( 'update' );
        } else {
            twitchpress_update_db_version();
        }
    
        $arr = array();
        
        // 0.0.0
        $arr['0.0.0'] = array(            
            'twitchpress_update_000_file_paths',
            'twitchpress_update_000_db_version',
        );
        
        return $arr;  
        */ 
    }
                                                    
    /**
    * Minimum tables for BugNet to operate in the recommended manner...
    * 
    * @version 1.0
    */
    static function primary_tables() {
        self::table_activity();
        self::table_errors();
        self::table_endpoints();
        self::table_meta();           
    }    

    static function table_activity() {
        global $charset_collate, $wpdb;  
        $table = "CREATE TABLE " . $wpdb->prefix . "twitchpress_activity (
entryid bigint(20) unsigned NOT NULL AUTO_INCREMENT,
callid bigint(20) unsigned DEFAULT '0',
service varchar(50) NOT NULL,
type varchar(50) NOT NULL,
status varchar(50) NOT NULL,
file varchar(500), 
function varchar(125) NOT NULL,
line bigint(20),
wpuserid bigint(20) unsigned DEFAULT '0',
timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
description longtext,
outcome longtext,
life bigint(20) NOT NULL DEFAULT '86400',
PRIMARY KEY (entryid)
) $charset_collate; ";
        dbDelta( $table );          
    }
    
    static function table_errors() {
        global $charset_collate, $wpdb;    
        $table = "CREATE TABLE " . $wpdb->prefix . "twitchpress_errors (
errorid bigint(20) unsigned NOT NULL AUTO_INCREMENT,
entryid bigint(20) unsigned,
code varchar(50),
error varchar(250),
line bigint(20),
function varchar(125),
file varchar(500),
timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
PRIMARY KEY (errorid)
) $charset_collate; ";
        dbDelta( $table );          
    }
    
    static function table_endpoints() {
        global $charset_collate, $wpdb;    
        $table = "CREATE TABLE " . $wpdb->prefix . "twitchpress_endpoints (
endpointid bigint(20) unsigned NOT NULL AUTO_INCREMENT,
entryid bigint(20) unsigned,
service varchar(50) NOT NULL,
endpoint varchar(500) NOT NULL,
parameters longtext NOT NULL, 
firstuse timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
lastuse timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
counter bigint(20) NOT NULL DEFAULT '1',
PRIMARY KEY (endpointid)
) $charset_collate; ";
        dbDelta( $table );          
    }
    
    static function table_meta() {
        global $charset_collate, $wpdb;    
        $table = "CREATE TABLE " . $wpdb->prefix . "twitchpress_meta (
metaid bigint(20) unsigned NOT NULL AUTO_INCREMENT,
entryid bigint(20) unsigned,
metakey varchar(50) NOT NULL,
metavalue longtext NOT NULL,
timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
expiry DATETIME,
PRIMARY KEY (metaid)
) $charset_collate; ";
        dbDelta( $table );          
    }  
}                   
    
endif;
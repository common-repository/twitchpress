<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function bugnet_insert_issue( $type, $outcome, $name, $title, $reason, $line, $function, $file ) {
    global $wpdb;
    return twitchpress_db_insert( $wpdb->bugnet_issues, 
        array( 
            'type'        => $type, 
            'outcome'     => $outcome, 
            'name'        => $name,
            'title'       => $title,
            'reason'      => $reason,
            'line'        => $line,
            'function'    => $function,
            'file'        => $file
        ) 
    );     
}
<?php
/**
* These are the subscription types from Twitch EventSub...
* 
* @version 1.0
*/
function twitchpress_eventsub_types() {
    return array(
        array( 'Channel Update', 'channel.update', 'A broadcaster updates their channel properties e.g., category, title, mature flag, broadcast, or language.' ),
        array( 'Channel Follow', 'channel.follow', 'A specified channel receives a follow.' ),
        array( 'Channel Subscribe', 'channel.subscribe', 'A notification when a specified channel receives a subscriber. This does not include resubscribes.' ),
        //array( 'Channel Cheer', 'channel.cheer', 'A user cheers on the specified channel.' ),
        //array( 'Channel Raid', 'channel.raid', 'A broadcaster raids another broadcaster�s channel.' ),
        //array( 'Channel Ban', 'channel.ban', 'A viewer is banned from the specified channel.' ),
        //array( 'Channel Unban', 'channel.unban', 'A viewer is unbanned from the specified channel.' ),
        //array( 'Channel Moderator Add', 'channel.moderator.add', 'Moderator privileges were added to a user on a specified channel.' ),
        //array( 'Channel Moderator Remove', 'channel.moderator.remove', 'Moderator privileges were removed from a user on a specified channel.' ),
        //array( 'Channel Points Custom Reward Add', 'channel.channel_points_custom_reward.add', 'A custom channel points reward has been created for the specified channel.' ),
        //array( 'Channel Points Custom Reward Update', 'channel.channel_points_custom_reward.update', 'A custom channel points reward has been updated for the specified channel.' ),
        //array( 'Channel Points Custom Reward Remove', 'channel.channel_points_custom_reward.remove', 'A custom channel points reward has been removed from the specified channel.' ),
        //array( 'Channel Points Custom Reward Redemption Add', 'channel.channel_points_custom_reward_redemption.add', 'A viewer has redeemed a custom channel points reward on the specified channel.' ),
        //array( 'Channel Points Custom Reward Redemption Update', 'channel.channel_points_custom_reward_redemption.update', 'A redemption of a channel points custom reward has been updated for the specified channel.' ),
        //array( 'Hype Train Begin', 'channel.hype_train.begin', 'A hype train begins on the specified channel.' ),
        //array( 'Hype Train Progress', 'channel.hype_train.progress', 'A hype train makes progress on the specified channel.' ),
        //array( 'Hype Train End', 'channel.hype_train.end', 'A hype train ends on the specified channel.' ),
        array( 'Stream Online', 'stream.online', 'The specified broadcaster starts a stream.' ),
        array( 'Stream Offline', 'stream.offline', 'The specified broadcaster stops a stream.' ),
        //array( 'User Authorization Revoke', 'user.authorization.revoke', 'A user�s authorization has been revoked for your client id.' ),
        //array( 'User Update', 'user.update', 'A user has updated their account.' ),
    );
}

/**
* Listens for $_POST activity from Twitch.tv and stores the event for
* processing later by the background processing class...
* 
* This is called as add_action() in loader.php currently...
* 
* @version 1.0
*/
function twitchpress_webhooks_eventsub_listener() {
    if ( $_SERVER['REQUEST_METHOD'] !== 'POST' || !isset( $_GET['webhook'] ) || $_GET['webhook'] == 'twitchpress_eventsub_notification' ){ return; }
        
    // Expecting valid json...
    if (json_last_error() !== JSON_ERROR_NONE) { return; }
  
    $data = file_get_contents( 'php://input' );
    $events = json_decode( $data, true );
    
    
    
    /*
    $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
    */
    
    
    /*
    $headers = getallheaders();
    var_dump($headers['Content-Name']);
    */                                                                                       


                    
    /*
    public function webhook(Request $request) {
    $json = file_get_contents('php://input');
    Storage::disk('local')->put('file.txt', $json);
    Storage::disk('local')->put('request.txt', Request::header('x-wc-webhook-source'));
    */

    
    foreach ( $events as $event ) {
        
  
        
        # if the event is a Twitch.tv webhook notification then store it 
    
    
        
        twitchpress_webhooks_eventsub_store_event($event);
    }
}

function twitchpress_webhooks_eventsub_store_event( $event ) {
    //$caching = new TwitchPress_Webhooks_Caching( file_location, file_name, 'txt' );
 
    # check event and if a Twitch.tv notification save to cache 
    //$caching->save( $event );     
    
    # then queue the event using background processing 
    
    # twitchpress_webhooks_eventsub_queue_event(); 
}

function twitchpress_webhooks_eventsub_queue_event() {
     # use aysnc-request.php and background-process.php to fully process notification 
}

function twitchpress_webhooks_ready() {
    global $wpdb;
    if( !isset( $wpdb->webhooksmeta ) || !twitchpress_db_does_table_exist( $wpdb->prefix . 'webhooksmeta' ) ) {
        return false;            
    } else {
        return true;
    }
}

/**
* Webhook services are not be ready until manual installation is run... 
* 
* @version 1.0
*/
function twitchpress_webhooks_activate_service() {              
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    twitchpress_create_table_webhooks_meta();   
}

/**
* Creates a meta data table for webhooks...
* 
* @version 1.0
*/
function twitchpress_create_table_webhooks_meta() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'webhooksmeta';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $max_index_length = 191;
    
    $install_query = "CREATE TABLE $table_name (
        meta_id bigint(20) unsigned NOT NULL auto_increment,
        webhook_id bigint(20) unsigned NOT NULL default '0',
        meta_key varchar(255) default NULL,
        meta_value longtext,
        PRIMARY KEY  (meta_id),
        KEY webhook_id (webhook_id),
        KEY meta_key (meta_key($max_index_length))
    ) $charset_collate;";
    
    dbDelta( $install_query );
}

/**
 * Adds meta data field to a webhook.
 *
 * @param int    $webhook_id  Webhook ID.
 * @param string $meta_key   Metadata name.
 * @param mixed  $meta_value Metadata value.
 * @param bool   $unique     Optional, default is false. Whether the same key should not be added.
 * @return int|false Meta ID on success, false on failure.
 */
function add_webhook_meta($webhook_id, $meta_key, $meta_value, $unique = false) {
    return add_metadata( 'webhook', $webhook_id, $meta_key, $meta_value, $unique );
}

/**
 * Removes metadata matching criteria from a webhook.
 *
 * @param int    $webhook_id    Webhook ID
 * @param string $meta_key   Metadata name.
 * @param mixed  $meta_value Optional. Metadata value.
 * @return bool True on success, false on failure.
 */
function delete_webhook_meta($webhook_id, $meta_key, $meta_value = '') {
    return delete_metadata( 'webhook', $webhook_id, $meta_key, $meta_value );
}

/**
 * Retrieve meta field for a webhook.
 *
 * @param int    $badge_id Webhook ID.
 * @param string $key     Optional. The meta key to retrieve. By default, returns data for all keys.
 * @param bool   $single  Whether to return a single value.
 * @return mixed Will be an array if $single is false. Will be value of meta data field if $single is true.
 */
function get_webhook_meta($webhook_id, $key = '', $single = false) {
    return get_metadata( 'webhook', $webhook_id, $key, $single );
}

/**
 * Update webhook meta field based on webhook ID.
 *
 * @param int    $webhook_id   Webhook ID.
 * @param string $meta_key   Metadata key.
 * @param mixed  $meta_value Metadata value.
 * @param mixed  $prev_value Optional. Previous value to check before removing.
 * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
 */
function update_webhook_meta($webhook_id, $meta_key, $meta_value, $prev_value = '') {
    return update_metadata( 'webhook', $webhook_id, $meta_key, $meta_value, $prev_value );
}

/**
 * Integrates webhooksmeta table with $wpdb...
 *
 * @version 1.0
 */
function twitchpress_integrate_wpdb_webhooksmeta() {
    global $wpdb;        
    $wpdb->webhooksmeta = $wpdb->prefix . 'webhooksmeta';
    $wpdb->tables[] = 'webhooksmeta';
    return;
}
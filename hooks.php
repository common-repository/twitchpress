<?php
/**
* TwitchPress Hooked Functions
*/
   
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
* Popup dialog for get user quick-tool...
* 
* @version 1.0
*/
function twitchpress_toolsform_get_user_by_id(){ 
    $tools_class = new TwitchPress_Tools();
    ?>
    <div id="twitchpress_toolsform_get_user_by_id" style="display:none;">    
         <p>
            <?php _e( 'Please enter a WordPress user ID to manually update their Twitch subscription data in your WordPress database.', 'twitchpress' ); ?>
            <form method="POST" action="<?php echo $tools_class->url( 'tool_get_user_by_id' ); ?>">
            
                <table class="form-table">    
                     <tr>
                        <th scope="row"><label for="wordpress_user_id2"><?php _e( 'Enter Twitch User ID', 'twitchpress' ); ?></label></th>
                        <td>
                            <input type="text" id="wordpress_user_id2" name="wordpress_user_id2" class="input-text" value="120841817" />
                            <label for="wordpress_user_id2"><?php _e( '', 'twitchpress' ); ?></label>
                        </td>
                    </tr>
                     <tr>
                        <th scope="row"><label for="twitchpress_var_dump2"><?php _e( 'Dump API Response', 'twitchpress' ); ?></label></th>
                        <td>
                            <input type="checkbox" id="twitchpress_var_dump2" name="twitchpress_var_dump2" class="input-checkbox" value="1" />
                            <label for="twitchpress_var_dump2"><?php _e( '', 'twitchpress' ); ?></label>
                        </td>
                    </tr>                                                    
                </table>
                
                <input type="submit" value="Submit" />                
            </form>
         </p>                         
    </div>
 <?php 
}     
add_action('admin_init', 'twitchpress_toolsform_get_user_by_id');

function twitchpress_toolsform_syncsubscriber(){ 
    $tools_class = new TwitchPress_Tools();
    ?>
    <div id="twitchpress_toolsform_syncsubscriber" style="display:none;">    
         <p>
            <?php _e( 'Please enter a WordPress user ID to manually update their Twitch subscription data in your WordPress database.', 'twitchpress' ); ?>
            <form method="POST" action="<?php echo $tools_class->url( 'tool_syncsubscriber' ); ?>">

                <table class="form-table">  
                     <tr>
                        <th scope="row"><label for="wordpress_user_id"><?php _e( 'Enter WordPress User ID', 'twitchpress' ); ?></label></th>
                        <td>
                            <input type="text" id="wordpress_user_id" name="wordpress_user_id" class="input-text" value="" />
                            <label for="wordpress_user_id"><?php _e( '', 'twitchpress' ); ?></label>
                        </td>
                    </tr>                
                     <tr>
                        <th scope="row"><label for="twitchpress_var_dump"><?php _e( 'Dump API Response', 'twitchpress' ); ?></label></th>
                        <td>
                            <input type="checkbox" id="twitchpress_var_dump" name="twitchpress_var_dump" class="input-checkbox" value="1" />
                            <label for="twitchpress_var_dump"><?php _e( '', 'twitchpress' ); ?></label>
                        </td>
                    </tr>                
                </table>
                <input type="submit" value="Submit" />                
            </form>
         </p>                         
    </div>
 <?php 
}
add_action('admin_init', 'twitchpress_toolsform_syncsubscriber');
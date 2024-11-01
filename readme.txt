=== TwitchPress ===
Contributors: Ryan Bayne
Donate link: https://www.patreon.com/twitchpress
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Tags: Twitch, Twitch.tv, Twitch Channel, Twitch Embed, Twitch Stream, Twitch API, TwitchPress
Requires at least: 5.4
Tested up to: 6.6.1
Stable tag: 3.18.0
Requires PHP: 5.6
                        
Unofficial Twitch.tv power-up for your WordPress! 
                       
== Description ==
Add the power of the Twitch API to your WordPress by installing this plugin.
Some features are similar to those found in premium plugins and as development
continues. More features will match those premium plugins. 

TwitchPress is unofficial and has not been endorsed by Twitch Interactive, Inc. Use of this plugin requires
the full understanding and acceptance of Twitch Interactive, Inc. terms of service.

= Links =                                                                
*   <a href="https://twitchpress.wordpress.com" title="">Blog</a>
*   <a href="https://github.com/RyanBayne/TwitchPress" title="">GitHub</a>       
*   <a href="https://twitter.com/ryan_r_bayne" title="Follow the projects Tweets.">Developers Twitter</a>
*   <a href="https://twitter.com/twitchpress" title="Follow the projects Tweets.">TwitchPress Twitter</a>
*   <a href="https://www.twitch.tv/lolindark1" title="Follow my Twitch channel.">Authors Twitch</a>   
*   <a href="https://discord.gg/ScrhXPE" title="Chat about TwitchPress on Discord.">Discord Chat</a>     
*   <a href="https://www.patreon.com/twitchpress" title="">Patreon Pledges</a>     
*   <a href="https://www.paypal.me/zypherevolved" title="">PayPal Donations</a>       

= Features List = 
* Free Pro Features
* Sign-In Via Twitch
* Registration Via Twitch
* Follower Only Content 
* Embed Live Streams
* Embed Live Chat
* Full-Width Split-Screen Layout
* Redirect Visitors Anywhere 
* Channel List Shortcode
* Twitch.tv Logo as Avatars

= Beta Features = 
* Ultimate Members Integration
* Subscribers Only Content
* Subscriber Only Posts 
* Subscriber Roles/Capabilities

= Support = 
The plugins development is fully supported. The community can support each
other on the projects <a href="https://discord.gg/ScrhXPE" title="Chat about TwitchPress on Discord.">Discord</a>
and you can unlock premium support by joining the projects <a href="https://www.patreon.com/twitchpress" title="">Patreon</a> 
or donating via <a href="https://www.paypal.me/zypherevolved" title="">PayPal</a> or other agreed methods.

== Installation ==
= Method One =
Move folder inside the .zip file into the "wp-content/plugins/" directory if your website is stored locally. Then upload the new plugin folder using your FTP program.

= Method Two = 
Use your hosting control panels file manager to upload the plugin folder (not the .zip, only the folder inside it) to the "wp-content/plugins/" directory.

= Method Three =
In your WordPress admin click on Plugins then click on Add New. You can search for your plugin there and perform the installation easily. This method does not apply to premium plugins.

= Example Shortcodes = 
These are examples of what the Twitch API can do. 
Some configurations might not work within your blog but
can be configured or improved by me to suit your needs. See the projects
<a href="https://twitchpress.wordpress.com/shortcodes/" title="">website</a> for
instructions.

[twitchpress_embed_everything channel="ZypheREvolved"]
[twitchpress_connect_button]
[twitchpress_shortcodes shortcode="channel_list"]
[twitchpress_shortcodes shortcode="get_game" refresh="5" game_name="Conan Exiles"]
[twitchpress_shortcodes shortcode="get_clips" refresh="5" broadcaster_id="120841817"]
[twitchpress_shortcode_stream_data channel_name="" value="game_id"]
[twitchpress_shortcodes shortcode="get_bits_leaderboard" channel_id="123"]
[twitchpress_videos user_id=""]
[twitchpress_get_top_games_list total="5"]
[twitchpress_channel_status channel_name="LOLinDark1"]
[twitchpress_channel_status_line channel_id=""]
[twitchpress_shortcode_channel_status_box]
[twitchpress_update_um_role_button]
[twitchpress_streamlabs_current_users_points]
[twitchpress_followers_only]Some content for Twitch.tv followers only.[/twitchpress_followers_only]
[twitchpress_shortcodes shortcode="sub_only_content"]Some subscriber only content goes here.[/twitchpress_shortcodes]

== Screenshots ==
1. Custom list of plugins for bulk installation and activation.
2. Example of how the WP admin is fully used. Help tab can be available on any page.
3. Security feature that helps to detect illegal entry of administrator accounts into the database.

== Languages ==
Translator needed to localize the Channel Solution for Twitch.

== Changelog == 
= 3.18.0 UPGRADE = 
* New Features
    - None - various fixes though! 
* Feature Changes
    - Example pages installation now includes a random list of channels and a team list example
* Faults Resolved
    - Function twitchpress_setup_database_save() - error on installation of debug databases
    - Function twitchpress_channel_status_box_shortcode_helix() Undefined variable $channel_id
    - Channel List CSS was not loading
    - Videos were failing to display due to "parent" value being missing
* Technical Notes
    - Function endpoint() - parameter order changed 
    - Function curl() - parameter order changed 
    - Resolved messages regarding Deprecated: Creation of dynamic property 
    - Function get_streams() can now accept an array of ID 
    - Removed unused function twitchpress_is_dashboard() which appeared incomplete also
    - Corrected incorrect variable in twitchpress_user_sub_sync_single() 
    - Removed unsed function twitchpress_install_action_do_update() 
* Configuration Advice
    - None
* Database Changes
    - None 


= 3.17.1 PATCH Released 17th October 2022 = 
* Missing files

= 3.17.0 UPGRADE Released 15th October 2022 = 
* New Features
    - Raw Twitch API response is displayed on the Activity and Subscription views of the data tables area
    - API body responses are no longer stored in the activity meta table by default, setting available to activate this
* Feature Changes
    - Subscriber sync tool now has option to dump the API response 
    - Example pages created during setup wizard now create a page that uses [twitch_connect_button]
    - Login text setting now applies to the shortcode button
    - Can now initiate BugNet from the Setup Wizard tables installation step
    - API activity table time is no longer one hour ahead
    - Status-box shortcode example now includes the channel ID
* Faults Resolved
    - Connect button style 1 output bad characters
    - Function register_styles() for channel list shortcode was using old constants. 
    - Channel Status shortcode fixed
* Technical Notes
    - New hooks.php file for holding hooked functions
    - New parameter added to function new() in logging for increasing backtrace level
    - Changed function logging_initiate() to use new logging backtrace level 
    - TwitchPress Curl class changed in method parse_arguments() - json_encode() no longer applied to body
* Configuration Advice
    - None
* Database Changes
    - None 
     
= 3.16.0 UPGRADE Released 20th March 2022 = 
* New Features
    - New tool for testing the subscription systems ready status
    - New tool with pop-up form for syncing a Twitch subscriber (intended as a diagnostic tool) 
* Feature Changes
    - Support added for scope: user:read:subscriptions
    - Support added for scope: channel:edit:commercial
    - Support added for scope: channel:manage:broadcast
    - Support added for scope: channel:manage:extensions
    - Support added for scope: channel:manage:polls
    - Support added for scope: channel:manage:predictions
    - Support added for scope: channel:manage:redemptions
    - Support added for scope: channel:manage:schedule
    - Support added for scope: channel:manage:videos
    - Support added for scope: channel:read:editors
    - Support added for scope: channel:read:goals
    - Support added for scope: channel:read:hype_train
    - Support added for scope: channel:read:polls
    - Support added for scope: channel:read:predictions
    - Support added for scope: channel:read:redemptions
    - Support added for scope: channel:read:stream_key
    - Support added for scope: moderation:read
    - Support added for scope: moderator:manage:banned_users
    - Support added for scope: moderator:read:blocked_terms
    - Support added for scope: moderator:manage:blocked_terms
    - Support added for scope: moderator:manage:automod
    - Support added for scope: moderator:manage:automod_settings
    - Support added for scope: moderator:read:automod_settings
    - Support added for scope: moderator:read:chat_settings
    - Support added for scope: moderator:manage:chat_settings
    - Support added for scope: user:manage:blocked_users
    - Support added for scope: user:read:blocked_users
    - Support added for scope: user:read:broadcast
    - Support added for scope: user:read:follows
    - Support added for scope: whispers:read
    - Support added for scope: whispers:edit
    - Activating/Disabling systems now displays messages
    - Activating subscription system now displays a reminder to setup subscription related scopes
* Faults Resolved
    - Avatar filtering causing notices in additional places
    - Subscription syncing procedure for logged in users improved (requires more testing)
* Technical Notes
    - Function twitchpress_get_user_sub_data() changed to update subscription meta instead of insert
    - ...I don't believe this caused any fault but in the case that the old Twitch API data was being stored
    - ...it would correct an issue it's just not likley to be the case
* Configuration Advice
    - Examine scope options and ensure they are setup correctly
* Database Changes
    - None 
    
= 3.15.0 UPGRADE Released 1st November 2021 = 
* New Features
    - None
* Feature Changes
    - None
* Faults Resolved
    - Radio buttons corrected on Login and Registration view
    - Chat edit scope for visitors can now be setup
    - Chat read scope for visitors can now be setup
    - Dashboard Activity errors resolved
* Technical Notes
    - Pro folder deleted and containing shortcodes moved to the main shortcode folder
    - Added function twitchpress_is_streaming( $channel_id ) returns boolean
    - Open ID scope removed as it is not currently in use
* Configuration Advice
    - None
* Database Changes
    - None
    
= 3.14.0 UPGRADE Released 11th July 2021 = 
* New Features
    - Option to install examples pages added to Setup Wizard
    - Quick tool for installing example pages
* Feature Changes
    - Twitch Login button will no longer be displayed as soon as the plugin is activated
* Faults Resolved
    - First-time installation notice will now appear admin and not just those with twitchpress capabilities
* Technical Notes
    - General settings save function uses a switch statement to handle individual tabs now
    - Function twitchpress_prepare_scopes() will now return empty string instead of empty array if no scopes setup
    - Admin user with ID 1 (reffered to as the keyholder) will not be subscription-synced anymore (was left active for testing)
* Configuration Advice
    - If using BuddyPress plugin please test the new General setting for avatar control 
* Database Changes
    - None

= 3.13.0 UPGRADE Released 6th May 2021 = 
* New Features
    - Can now enter the main team name and the teams Twitch ID is obtained automatically
* Feature Changes
    - Webhooks option added to System switches in General Settings
    - Added a Content Gate checkbox for switching content gating on/off as a system
    - New options added to Edit Webhooks view for webhook type 
* Faults Resolved
    - PHP object as array error in function validate_user_token() avoided but the cause still to be determined.
    - Uninstallation - fixed issue with removal of admin users meta data
* Technical Notes
    - Removed Quick-Tool for installing Pro edition (pro was merged into the free download)
    - Removed description values from hidden fields in installation setts for BugNet
    - Created option twitchpress_gate_switch which now defines content gating as a system
    - Now that Twitch API Helix (version 6) is in full use, error logging needs to be better integrated 
    - Removed API column from the Data view API Activity as the database table also had it removed
    - Further improvements made to API logging
    - File class.twitchpress-admin-notices.php is no longer loaded in class.twitchpress-admin.php because...
    - ...the class is being used in function twitchpress_user_sub_sync_single() which is used during login
    - ...it is now loaded in loader.php 
    - Removed twitchpress_is_sync_due() from UM functions because it's use is vague and potentially preventing UM roles to be set
    - Added json_encode() for body value in class.twitchpress-curl.php (json_encode( $this->curl_request_body ))
    - Contents of class.twitch-webhooks.php moved and the file deleted
* Configuration Advice
    - Manually setup webhooks for Twitch subs to test the new webhooks system which includes a custom post type 
* Database Changes
    - Added log entry "life" column to API activity table   
     
= 3.12.0 UPGRADE Released 15th March 2021 = 
* New Features
    - BuddyPress Avatar Over-ride
* Feature Changes
    - Uninstallation removes BugNet options data
* Faults Resolved
    - None
* Technical Notes
    - Function var_dump_twitchpress() now accepts an unlimited number of values
    - New option value twitchpress_buddypress_avatars_override
    - The image control for BuddyPress profiles (150x150) is achieved using a temporary hack
* Configuration Advice
    - If using BuddyPress plugin please test the new General setting for avatar control 
* Database Changes
    - None
    
= 3.11.1 PATCH Released 13th March 2021 = 
* Function twitchpress_save_twitch_logo() deprecated
* Plugin will no longer generate Twitch user logo attachment within WP 
    
= 3.11.0 UPGRADE Released 12th March 2021 = 
* New Features
    - None
* Feature Changes
    - Full-Width Split-Screen has very different (better) HTML and CSS causing very slight changes in appearance
    - Plugin table links: GitHub, Discord and my Twitch channel added
    - Channel List shortcode loads quicker
    - Setup Wizard link added to plugins information on the plugins admin view
* Faults Resolved
    - Footer based (happens end of page load) subscription syncing fixed
    - User meta data uninstallation
    - Login via wp-login.php got a fix
* Technical Notes
    - Changed the filter name in function twitchpress_shortcode_init() 
    - New team endpoint added to function get_team() 
    - New team roster shortcode has begun but not ready to declare a usable feature
    - $wpdb was not defined in the login procedure which caused error after new user registration, preventing proper redirection
* Configuration Advice
    - None
* Database Changes
    - None
    
= 3.10.0 UPGRADE Released 5th March 2021 = 
* New Features
    - None
* Feature Changes
    - Setup Wizard will now prompt on installation
    - Incorrectly setup shortcodes are handled better
    - Subscriber-only content improved
    - Subscription related features improved
    - Uninstallation improvements
    - See Technical Notes for more details...
    - Error output stopped on Edit Post views
    - Shortcode cache expiry reduced for admin
    - Teams Endpoint No Longer Supported
* Faults Resolved
    - Incorrect subscriber related message being displayed on login for the first time
* Technical Notes
    - Class twitchpress_systematic_syncing() remade into TwitchPress_Twitch_Subscription_Management() to focus on this one important aspect 
    - Removed function twitchpress_sync_user_on_registration() and will no longer sync data during registrations
    - Remove some updates to deprecated options that existed to aid transition to new options
    - Uninstallation now removes the plugins database tables - admin must indicate they want this to happen in Settings first
    - Deprecated options added to uninstallation procedure
    - Setup Wizard will now be offered to all administrators when Twitch API is not ready
    - Incorrectly setup shortcodes with invalid function name will not error, instead displaying text for webmasters/admin
    - Subscriber content gate shortcode triggers API call to Twitch.tv rather than relying on locally stored data
    - Subscriber content gate shortcode now has additional parameters for configuring output
    - Subscriber-only shortcode now relies more on immediate Twitch API calls and not only on local data
    - Subscription related features now rely on more immediate calls to the Twitch API and not only local data
    - Displaying errors prevented posts being updated so an error dump will no longer happen when creating/editing posts
    - The teams endpoint is not supported by Helix and so this will change how the Channel List shortcode works if "team" is the intended output
    - Function get_streams() parameter $first changed from null to 10 to prevent 100 channels being output when not expected
    - Shortcode class TwitchPress_Shortcode_Channel_List() now uses the get_streams endpoint only to replace the deprecated teams endpoint 
* Configuration Advice
    - Subscription related features should be well tested and I'll be eager to release fixes very quickly
    - Ultimate Member should be well tested 
    - If you have more than one administrator please take the security of your Twitch.tv account into consideration when using TwitchPress
* Database Changes
    - None
    
 = 3.9.0 Released 18th February 2021 = 
* New Features
    - Added a page template for displaying two streams side by side (Splitscreen)
* Feature Changes
    - None
* Faults Resolved
    - Installation error resolved
* Technical Notes
    - Kraken files/code have now been fully removed from the plugin
    - Removed a second use of establish_user_token() in twitchpress_init_main_channel_twitch_oauth()
* Configuration Advice
    - Create custom meta "twitchpress_channel_one" and "twitchpress_channel_two" to create the new Splitscreen page
* Database Changes
    - None
    
= 3.8.1 Released 14th February 2021 = 
* Faults Resolved
    - Valid user tokens are no longer replaced, reducing API calls.
    - Some users experienced login issues
* Feature Improvements
    - Commercials - added a test tool for starting commercials on your Twitch channel
    - Error display can now be seen when logged out if IP address added to whitelist
* Technical Notes
    - Function twitchpress_update_user_token() now uses the correct key to store user token in the object registry. 
    - Added parameters to all uses of wp_die() to make it easier to locate use.
    - Function validate_user_token() now passes Authorization OAuth token which replaces the default "Bearer".
    - Function add_headers() in the Curl class now correctly replaces existing headers (not determined if this fixes anything yet).
    - twitchpress_are_errors_allowed() now displays errors for whitelisted IP and skips user related checks - intended for localhost
* Configuration Advice
    - None
* Database Changes
    - None

= 3.8.0 Released 12th February 2021 = 
* Faults Resolved
    - A fix causing debugging output in a way that made it difficult to access admin.
* Feature Improvements
    - None
* Technical Notes
    - Function get_current_userobject_authd() now uses object registry to get user token rather than get_user_meta() 
* Configuration Advice
    - None
* Database Changes
    - None 
    
= 3.7.0 Released 12th February 2021 = 
* Faults Resolved
    - A fix relating to users API credentials not loading properly. 
* Feature Improvements
    - New Scope: moderation:read
* Technical Notes
    - None
* Configuration Advice
    - None
* Database Changes
    - None= 
        
= 3.6.1 Released 8th February 2021 = 
* Faults Resolved
    - Complex issues with storing and maintaining API credentials took hours to track but are fixed
    - Owners oAuth token refreshes better (related to accessing main channel data)
    - Improved token refresh solves sudden inability to get Twitch data
    - Fixed login by Twitch caused by the procedures inability to obtain app credentials
* Feature Improvements
    - Twitch subscription tier filters added to Users view
    - New helix scope added: channel:read:subscriptions 
* Technical Notes
    - Updated scopes array in class TwitchPress_Twitch_API()
    - Updated scopes function twitchpress_scopes()
    - Status column replaces outcome column in the activity table
    - New outcome column in activity table stores fully explained outcome now
    - Removed database table for logging "outcomes", the data will now be stored in the "activity" table
    - Main channel credentials now updated during main channel owners login
    - Function establish_user_token() now updates main channel credentials for keyholder only (WP ID 1)
    - Function establish_user_token() now defaults to an else argument instead of elseif as this function always returns a returned value
    - Some main channel update functions were not storing the sanitized value, also renamed the sanitized value
    - Added registry lines to user update functions as in main channel functions
    - Function twitchpress_get_user_token() now uses establish_user_token() and not raw database value
    - Function refresh_token_by_userid() no longer performs database updates as it is called by a method that does
    - Removed class.twitchpress-history.php as database logging is replacing the use of transients
    - BugNet install() no longer adds actions for primary_tables_registration() as this is done in configuration of BugNet
    - Object registry no longer stores entire classes but only set groups of values from those classes
    - Developer content dump is much neater and includes more backtrace.
* Configuration Advice
    - None
* Database Changes
    - Added "expiry" column to the twitchpress_meta table to help clean-up short-use data
    - Value for the metavalue column is now "longstring" to hold API responses 
    
= 3.6.0 Released 21st October 2020 = 
* Faults Resolved
    - Corrections made to BugNet database table installation script.
* Feature Improvements
    - API focused logging added - the existing BugNet logging service is still the global debugging tool
    - Beta Feature: Live Menu section added to General settings for a front-end live stream menu feature
    - Beta Feature: Main Team section added to General Settings for managing a default team
    - Option added for activating new API logging (sites should be monitored for permance decrease when using using this new feature)
    - Added option to General/Systems for activating Twitch subscription data syncing (twitchpress_twitchsubscribers_switch)
    - New sub syncing option also added to Setup Wizard
    - Option added to Twitch API General tab for activating API logging
* Technical Notes
    - Adapted the call_execute() method in the TwitchPress API class to loop and prepare data for pagination
    - Curl Change: function decode_body() now applies array_merge() to handle looped/paginated procedures
    - Curl Change: function do_call() no longer executes a call if a transient(cache) is allowed for the procedure
    - New "Display Subscribers" tool mainly for testing subscription queries to Twitch.tv right now (this requires testing please)
    - Database tables class TwitchPress_Install_Tables() created and will initially install tables for logging API activity 
    - Removed update related lines from function twitchpress_installation_prepare() due to new database tables class
    - Deleted file class.twitchpress-logger.php as this logging approach is incomplete, lines in background-updater.php commented-out  
    - Created class.twitchpress_api_logging.php which is uses to add logging data to the new tables
    - The new TwitchPress_API_Logging::error() has been added throughout WP_Http_Curl->request()
    - Removed quick-tool tool_user_sync_twitch_sub_data() as it only used the current users credentials which is an unsufficiant approach.
    - Removed function get_broadcasters_subscribers() as the Twitch API no longer includes the matching documentation
    - Changed function get_broadcaster_subscriptions() to include $user_id paramater and replaced uses of get_broadcasters_subscribers() with it
    - Global TWITCHPRESS_CALL_COUNTER renamed to TWITCHPRESS_RETRY_CALL_LIMIT because it is a limit on retrying failed calls
    - Login procedure has new "if" statement for the shortcode version changed, only calls login_success() (not a security related method) on Twitch authentication.  
    - Removed class.twitchpress-all-k raken5requests.php as the data view is being replaced
    - Time spent preparing clear logging messages for Twitch subscription syncing
* Configuration Advice
    - None
* Database Changes
    - The following tables are now installed by the core plugin by default...
    - [prefix]_tpapi_activity
    - [prefix]_tpapi_errors
    - [prefix]_tpapi_results
    - [prefix]_tpapi_endpoints
    - [prefix]_tpapi_meta
    - Naming pattern is to avoid conflict with other plugin tables while keeping names short
    
= 3.5.2 Released 4th July 2020 = 
* Faults Resolved
    - Changed path to Discord API file again to correct error seen on activation
* Feature Improvements
    - None
* Technical Notes
    - Replaced "require" with "include_once" in All API loader.php file
* Configuration Advice
    - None
* Database Changes
    - None= 
 
= When To Update = 

Browse the changes log and decide if an update is required. There is nothing wrong with skipping version if it does not
help you - look for security related changes or new features that could really benefit you. If you do not see any you may want
to avoid updating. If you decide to apply the new version - do so after you have backedup your entire WordPress installation 
(files and data). Files only or data only is not a suitable backup. Every WordPress installation is different and creates a different
environment for WTG Task Manager - possibly an environment that triggers faults with the new version of this software. This is common
in software development and it is why we need to make preparations that allow reversal of major changes to our website.

== Contributors ==
List of developers and people who have supported development in a technical way... 

* nookyyy      - A popular Twitch.tv streamer who done half of the testing.
* IBurn36360   - Author of the main Twitch API class on GitHub.
* Automattic   - The plugins initial design is massively based on their work.  
* Ashley Rich  - I used a great class by Ashley (Username A5shleyRich).

== Version Numbers and Updating ==

Explanation of versioning used by myself Ryan Bayne. The versioning scheme I use is called "Semantic Versioning 2.0.0" and more
information about it can be found at http://semver.org/ 

These are the rules followed to increase the TwitchPress plugin version number. Given a version number MAJOR.MINOR.PATCH, increment the:

MAJOR version when you make incompatible API changes,
MINOR version when you add functionality in a backwards-compatible manner, and
PATCH version when you make backwards-compatible bug fixes.

Additional labels for pre-release and build metadata are available as extensions to the MAJOR.MINOR.PATCH format.



                  
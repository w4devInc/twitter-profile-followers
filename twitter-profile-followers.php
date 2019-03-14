<?php
/*
 * Plugin Name: Twitter Profile Followers
 * Plugin URI: https://shazzad.me
 * Description: Fetch Twitter Profile Followers and update on post meta. Any post type can be used along with custom field name for page url.
 * Version: 1.2
 * Author: Shazzad Hossain Khan
 * Author URI: https://shazzad.me
*/


/* Define current file as plugin file */
if (! defined('TWPF_PLUGIN_FILE')) {
	define('TWPF_PLUGIN_FILE', __FILE__);
}


/* Plugin instance caller */
function twpf() {
	/* Require the main plug class */
	if (! class_exists('Twitter_Profile_Followers')) {
		require plugin_dir_path(__FILE__) . 'includes/class-twitter-profile-followers.php';
	}

	return Twitter_Profile_Followers::instance();
}


/* Initialize */
add_action('plugins_loaded', 'twpf_init');
function twpf_init() {
	twpf();
}

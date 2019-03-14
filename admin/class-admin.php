<?php
/**
 * Admin Environment
 * @package WordPress
 * @subpackage Twitter Profile Followers
 * @author Shazzad Hossain Khan
 * @url https://shazzad.me
**/


class TWPF_Admin
{
	function __construct()
	{
		add_action('admin_menu'								, [$this, 'admin_menu']);
		add_filter('plugin_action_links_' . TWPF_BASENAME	, [$this, 'plugin_action_links']);
	}

	public function admin_menu()
	{
		// access capability
		$access_cap = apply_filters('twpf/admin_page/access_cap', 'manage_options');

		// menu position
		$menu_position = 23.7;

		// register a parent menu
		$admin_page = add_menu_page(
			__('Twitter Profile Followers', 'twpf'),
			__('Twitter Profile Followers', 'twpf'),
			$access_cap,
			TWPF_SLUG,
			'__return_false',
			'dashicons-admin-home',
			$menu_position
		);
	}

	public function plugin_action_links($links)
	{
		$links['settings'] = '<a href="'. admin_url('admin.php?page=twpf') .'">' . __('Settings', 'twpf'). '</a>';
		return $links;
	}
}

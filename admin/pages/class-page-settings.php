<?php
/**
 * Settings Page
 * @package WordPress
 * @subpackage Twitter Profile Followers
 * @author Shazzad Hossain Khan
 * @url https://shazzad.me
**/

class TWPF_Admin_Page_Settings implements TWPF_Interface_Admin_Page
{
	public function __construct()
	{
		add_action( 'admin_menu'										, [$this, 'admin_menu']				, 120 );
	}

	public function handle_actions()
	{
		do_action( 'twpf/admin_page/settings/handle_actions' );
	}

	public function load_page()
	{
		do_action( 'twpf/admin_page/settings/load' );
	}
	public function render_notices()
	{
		do_action( 'twpf/admin_page/settings/notices' );
		do_action( 'twpf/admin_page_notices' );
	}
	public function render_page()
	{
		?><div class="wrap twpf_wrap">
			<h1><?php _e( 'Settings', 'twpf' ); ?></h1>
			<br /><?php

			do_action('twpf/admin_page_notices');
			$this->settings_notices();

			?><div class="twpf-admin-sidebar">
				<div class="twpf-box">
					<div class="twpf-box-content"><?php
						$jobRecurrence = twpf()->settings->get('job_recurrence');
						$processRecurrence = twpf()->settings->get('process_recurrence');

						include_once(TWPF_DIR . 'admin/views/settings-sidebar.php');
					?></div>
				</div>
			</div>
			<div class="twpf-admin-content">
            	<div class="twpf-box"><?php
					$settings = new TWPF_Plugin_Settings();
					include_once(TWPF_DIR . 'admin/views/form-settings.php');
				?></div>
			</div><?php

			do_action( 'twpf/admin_page/template_after/' );

		?></div><?php
	}

	public function settings_notices()
	{
		#$twApi = twpf()->twitter_api;
		# TWPF_Utils::p($fetch);
		#TWPF_Utils::d($twApi->getFollowers(['https://www.twitter.com/twitter', 'w4dev']));
		if ($message = get_option('twpf_settings_error')) {
			printf(
				'<div class="error settings-error notice is-dismissible">
					<p><strong>%s</strong> %s</p>
				</div>',
				__('Twitter Error:'),
				$message
			);
		}
	}

	public function admin_menu()
	{
		// access capability
		$access_cap = apply_filters( 'twpf/access_cap/settings', 'manage_options' );

		// register menu
		$admin_page = add_submenu_page(
			TWPF_SLUG,
			sprintf( '%s - %s', __('Settings', 'twpf'), TWPF_NAME ),
			__('Settings', 'twpf'),
			$access_cap,
			'twpf',
			[$this, 'render_page']
		);

		add_action( "admin_print_styles-{$admin_page}"	, [$this, 'print_scripts']);
		add_action( "load-{$admin_page}"				, [$this, 'load_page']);
		add_action( "load-{$admin_page}"				, [$this, 'handle_actions']);
	}

	public function print_scripts()
	{
		wp_localize_script('twpf_admin', 'twpf', [
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'settingsUrl' => admin_url('admin.php?page=twpf')
		]);

		wp_enqueue_style(['twpf_admin']);
		wp_enqueue_script(['twpf_admin']);

		do_action( 'twpf/admin_page/print_styles/settings' );
	}
}

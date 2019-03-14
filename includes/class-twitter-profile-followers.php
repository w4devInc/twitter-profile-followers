<?php
/**
 * Main Plugin Class
 * @package WordPress
 * @subpackage Twitter Profile Followers
 * @author Shazzad Hossain Khan
 * @url https://shazzad.me
**/


final class Twitter_Profile_Followers
{
	// plugin name
	public $name = 'Twitter Profile Followers';

	// plugin version
	public $version = '1.2';

	// class instance
	public $twitter_api = null;

	// class instance
	public $settings = null;

	// class instance
	protected static $_instance = null;

	// static instance
	public static function instance()
	{
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct()
	{
		$this->define_constants();
		$this->include_files();
		$this->initialize();
		$this->init_hooks();

		do_action('twpf/loaded');
	}

	private function define_constants()
	{
		define('TWPF_NAME'				, $this->name);
		define('TWPF_VERSION'			, $this->version);
		define('TWPF_DIR'				, plugin_dir_path(TWPF_PLUGIN_FILE));
		define('TWPF_URL'				, plugin_dir_url(TWPF_PLUGIN_FILE));
		define('TWPF_BASENAME'			, plugin_basename(TWPF_PLUGIN_FILE));
		define('TWPF_SLUG'				, 'twpf');
	}

	private function include_files()
	{
		// core
		include_once(TWPF_DIR . 'includes/libraries/class-logger.php');
		include_once(TWPF_DIR . 'includes/libraries/functions-form.php');
		include_once(TWPF_DIR . 'includes/class-config.php');
		include_once(TWPF_DIR . 'includes/class-utils.php');

		// abstract classes
		foreach(glob(TWPF_DIR . 'includes/abstracts/*.php') as $file) {
			include_once($file);
		}

		// models
		foreach(glob(TWPF_DIR . 'includes/models/*.php') as $file) {
			include_once($file);
		}

		// apis
		foreach (glob(TWPF_DIR . 'includes/apis/*.php') as $file) {
			include_once($file);
		}

		// rest api controllers
		foreach (glob(TWPF_DIR . 'includes/rest-api-controllers/*.php') as $file) {
			include_once($file);
		}

		include_once(TWPF_DIR . 'includes/class-twitter-profile-followers-updater.php');
		include_once(TWPF_DIR . 'includes/class-twitter-profile-followers-updater-crons.php');

		// admin
		if (is_admin()) {
			include_once(TWPF_DIR . 'admin/class-admin.php');
			foreach(glob(TWPF_DIR . 'admin/interfaces/*.php') as $file){
				include_once($file);
			}
			foreach(glob(TWPF_DIR . 'admin/pages/*.php') as $file){
				include_once($file);
			}
		}

		unset($file);
	}

	private function initialize()
	{
		$this->settings = new TWPF_Plugin_Settings();
		$this->twitter_api = new TWPF_Twitter_Api(
			$this->settings->get('twitter_api_key'),
			$this->settings->get('twitter_api_secret')
		);

		if (is_admin()) {
			new TWPF_Admin();
			new TWPF_Admin_Page_Settings();

			/* render admin logs page if debuggin is enabled */
			if ('yes' == $this->settings->get('enable_debugging')) {
				new TWPF_Admin_Page_Logs();
			}
		}
	}

	private function init_hooks()
	{
		add_action('rest_api_init'							, [$this, 'rest_api_init'] 			, 10);
		add_action('admin_enqueue_scripts'					, [$this, 'register_admin_scripts']	, 10);

		// cronjobs
		add_action('twpf_updater_cron'						, [$this, 'updater_cron']			, 10);
		add_action('twpf_processor_cron'					, [$this, 'processor_cron']			, 10);
		add_action('twpf_processor_second_cron'				, [$this, 'processor_cron']			, 10);
	}

	// fan count updater cronjob handler
	public function updater_cron()
	{
		$fbFansUpdater = new TWPF_Twitter_Profile_Followers_Updater();
		if ('processing' == $fbFansUpdater->get('status')) {
			TWPF_Utils::log(__('Unable to schedule new job. Error: Another job in progress, skipping.', 'twpf'));
			return false;
		}

		$schedule = $fbFansUpdater->schedule_job();
		if (is_wp_error($schedule)) {
			TWPF_Utils::log(sprintf(__('Unable to schedule job. %s', 'twpf'), $schedule->get_error_message()));
			return false;
		} else {
			TWPF_Utils::log(__('Job scheduled.', 'twpf'));

			wp_schedule_single_event(time(), 'twpf_processor_cron');
			TWPF_Utils::log(__('Job processor scheduled.', 'twpf'));

			return true;
		}
	}

	// fan count updater process cronjob handler
	public function processor_cron()
	{
		TWPF_Utils::log(__('Job processor started.', 'twpf'));

		$fbFansUpdater = new TWPF_Twitter_Profile_Followers_Updater();
		if (! in_array($fbFansUpdater->get('status'), ['scheduled', 'processing'])) {
			TWPF_Utils::log(__('No job scheduled to progress, skipping.', 'twpf'));
			return false;
		}

		$fbFansUpdater->process();
		if ('processing' === $fbFansUpdater->get('status')) {
			if ('twpf_processor_cron' == current_filter()) {
				wp_schedule_single_event(time() + 10, 'twpf_processor_second_cron');
			} elseif ('twpf_processor_second_cron' == current_filter()) {
				wp_schedule_single_event(time() + 10, 'twpf_processor_cron');
			}
		}

		TWPF_Utils::log(__('Job processor ended.'));

		if ('completed' === $fbFansUpdater->get('status')) {
			TWPF_Utils::log(sprintf(
				__('Job complete. Total updated %d, failed %d.', 'twpf'),
				$fbFansUpdater->get('updated_count'),
				$fbFansUpdater->get('failed_count')
			));
		}
		return true;
	}

	public function rest_api_init()
	{
		$rest_api_classes = [
			'TWPF_Settings_Rest_Api_Controller',
			'TWPF_Twitter_Profile_Followers_Updater_Rest_Api_Controller'
		];

		foreach ($rest_api_classes as $rest_api_class) {
			$controller = new $rest_api_class();
			$controller->register_routes();
		}
	}

	public function register_admin_scripts()
	{
		wp_register_style('twpf_form', 					TWPF_URL . 'assets/form.css'				, [], TWPF_VERSION);
		wp_register_script('twpf_form', 				TWPF_URL . 'assets/form.js'					, ['jquery'], TWPF_VERSION);
		wp_register_style('twpf_admin', 				TWPF_URL . 'assets/admin.css'				, ['twpf_form'], TWPF_VERSION);
		wp_register_script('twpf_admin', 				TWPF_URL . 'assets/admin.js'				, ['jquery', 'twpf_form'], TWPF_VERSION);
	}
}

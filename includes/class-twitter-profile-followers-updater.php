<?php
class TWPF_Twitter_Profile_Followers_Updater extends TWPF_Settings
{
	/* where we store the data */
	protected $option_name = 'twpf_updater';

	/* default settings */
	protected $settings = [
		'status' 					=> '',
		'time_started'				=> '',
		'time_last_run'				=> '',
		'time_completed'			=> '',
		'next_page'					=> '',
		'updated_count'				=> 0,
		'failed_count'				=> 0,
		'post_types'				=> [],
		'batch_size'				=> 50,
		'profile_url_key'			=> '',
		'page_fan_count_key'		=> '',
		'errors'					=> ''
	];

	protected $query;

	public function __construct()
	{
		parent::__construct();
		$this->settings = get_option($this->option_name, $this->settings);
	}

	/* store data to database */
	public function save()
	{
		update_option($this->option_name, $this->settings);
	}

	public function schedule_job()
	{
		if (! twpf()->settings->get('post_types')){
			return new WP_Error('scheduleError', __('Can\'t schedule twitter page fans updater, post type not selected.'));
		} elseif (! twpf()->settings->get('profile_url_key')){
			return new WP_Error('scheduleError', __('Can\'t schedule twitter page fans updater, profile_url_key not assigned.'));
		} elseif (! twpf()->settings->get('page_fan_count_key')){
			return new WP_Error('scheduleError', __('Can\'t schedule twitter page fans updater, page_fan_count_key not assigned.'));
		} elseif (! twpf()->twitter_api->isReady()) {
			return new WP_Error('scheduleError', __('Can\'t schedule twitter page fans updater, twitter app not configured.'));
		}

		$test = twpf()->twitter_api->getTestCount();
		if (is_wp_error($test)) {
			return new WP_Error('scheduleError', __('Can\'t schedule twitter page fans updater, twitter app error. '. $test->get_error_message()));
		}

		$this->set('errors', []);
		$this->set('status', 'scheduled');
		$this->set('time_started', '');
		$this->set('time_last_run', '');
		$this->set('time_completed', '');
		$this->set('next_page', 1);
		$this->set('updated_count', 0);
		$this->set('failed_count', 0);
		$this->set('batch_size', 50);
		$this->set('post_types', twpf()->settings->get('post_types'));
		$this->set('profile_url_key', twpf()->settings->get('profile_url_key'));
		$this->set('page_fan_count_key', twpf()->settings->get('page_fan_count_key'));
		$this->save();
	}

	public function cancel_job()
	{
		if ('completed' === $this->get('status')) {
			return new WP_Error('cancelError', __('Job already completed.'));
		} else if ('cancelled' === $this->get('status')) {
			return new WP_Error('cancelError', __('Job cancelled already.'));
		} else if ('processing' !== $this->get('status')) {
			return new WP_Error('cancelError', __('No job is running.'));
		} else {
			$this->set('status', 'cancelled');
			$this->set('time_cancelled', time());
			$this->save();

			return true;
		}
	}

	public function process()
	{
		if ('scheduled' === $this->get('status')) {
			$this->set('time_started', time());
			$this->set('status', 'processing');
			$this->save();

			$this->process_job();
		} else if ('processing' == $this->get('status')) {
			$this->process_job();
		}
	}

	public function process_job()
	{
		$this->set('time_last_run', time());

		$errors = $this->get('errors');

		$this->query = new WP_Query([
			'posts_per_page' 	=> $this->get('batch_size'),
			'post_type' 		=> $this->get('post_types'),
			'paged' 			=> $this->get('next_page'),
			'meta_key' 			=> $this->get('profile_url_key'),
			'meta_compare' 		=> 'EXISTS',
			'orderby' 			=> 'ID',
			'order' 			=> 'ASC',
			'suppress_filters' 	=> true
		]);

		$twProfileFollowers = [];
		$profileUrls = [];

		if ($this->query->get_posts()) {
			foreach ($this->query->get_posts() as $post) {
				$twProfileFollower = new TWPF_Twitter_Profile_Follower(
					$post->ID,
					$this->get('profile_url_key'),
					$this->get('page_fan_count_key')
				);

				$screenName = $this->sanitizeScreenName($twProfileFollower->get_profile_url());

				$profileUrls[] = $screenName;
				$twProfileFollowers[$screenName] = $twProfileFollower;
			}
		}

		$followeCounts = twpf()->twitter_api->getFollowers($profileUrls);
		if (is_wp_error($followeCounts)) {
			return $followeCounts;
		}

		foreach ($twProfileFollowers as $screenName => $twProfileFollower) {
			if (isset($followeCounts[$screenName])) {
				$twProfileFollower->set_fan_count($followeCounts[$screenName]);
				$twProfileFollower->update();
				$this->set('updated_count', $this->get('updated_count') + 1);
			} else {
				$this->set('failed_count', $this->get('failed_count') + 1);
			}
		}


		if ($this->query->max_num_pages > $this->get('next_page')) {
			$this->set('next_page', $this->get('next_page') + 1);
		} else {
			$this->set('status', 'completed');
			$this->set('time_completed', time());
		}

		$this->save();
	}

	public function sanitizeScreenName($screenName)
	{
		$screenName = strtolower(trim($screenName));
		if (false !== strpos($screenName, 'twitter.com')) {
			$parts = explode('/', $screenName);
			$screenName = array_pop($parts);
		}

		if (false !== strpos($screenName, '?')) {
			$parts = explode('?', $screenName);
			$screenName = array_shift($parts);
		}

		return $screenName;
	}

	public function get_data()
	{
		return $this->settings;
	}
}

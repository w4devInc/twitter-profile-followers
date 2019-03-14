<?php
/**
 * Settings API
 * @package WordPress
 * @subpackage Twitter Profile Followers
 * @author Shazzad Hossain Khan
 * @url https://w4dev.com
**/


class TWPF_Settings_Api
{
	public function update_settings($data)
	{
		$settings = new TWPF_Plugin_Settings();
		$old_job_recurrence = $settings->get('job_recurrence');
		foreach ($data as $key => $val) {
			$settings->set($key, $val);
		}
		$settings->save();

		flush_rewrite_rules();

		if ($old_job_recurrence != $settings->get('job_recurrence') || ! wp_next_scheduled('twpf_updater_cron')) {
			$updaterCrons = new TWPF_Twitter_Profile_Followers_Updater_Crons($settings->get('job_recurrence'));
			$updaterCrons->reschedule_crons();
		}

		$twitterApi = new TWPF_Twitter_Api(
			$settings->get('twitter_api_key'),
			$settings->get('twitter_api_secret')
		);

		delete_option('twpf_settings_error');
		if ($twitterApi->isReady()) {
			$fanCount = $twitterApi->getTestCount();
			if (is_wp_error($fanCount)) {
				$message = $fanCount->get_error_message();
				if (false !== strpos($message, 'Bad Authentication data')) {
					$message = __('Wrong API key / secret entered. Please check your keys.', 'twpf');
				}
				update_option('twpf_settings_error', $message);
			}
		}

		TWPF_Utils::log(sprintf(
			__( 'Settings updated by <a href="%s">%s</a>', 'impm' ),
			admin_url('user-edit.php?user_id='. get_current_user_id()),
			get_user_option('user_login')
		));

		return [
			'success' => true,
			'message' => __('Settings updated', 'impm')
		];
	}

	public function clear_cache()
	{
		if (! current_user_can('administrator')) {
			return [
			   'success' => false,
			   'message' => __('Sorry, you cant do this.', 'impm')
		   ];
		}

		global $wpdb;
		$options = $wpdb->get_col("SELECT option_name FROM $wpdb->options WHERE option_name LIKE '%\_twpf\_%'");
		if (! empty($options)) {
			foreach($options as $option) {
				delete_option($option);
			}
		}

		// clear orphan postmeta
		$wpdb->query("DELETE pm FROM wp_postmeta pm LEFT JOIN wp_posts wp ON wp.ID = pm.post_id WHERE wp.ID IS NULL");

		// clear opcache
		if (function_exists('opcache_reset')) {
			opcache_reset();
		}

		return  [
			'success' => true,
			'message' => __('Cache cleaned', 'impm')
		];
	}
}

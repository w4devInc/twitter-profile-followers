<?php
/**
 * Twitter API
 * @package WordPress
 * @subpackage Twitter Profile Followers
 * @author Shazzad Hossain Khan
 * @url https://shazzad.me
**/


class TWPF_Twitter_Profile_Followers_Updater_Api
{
	public function start_updater()
	{
		$fbFansUpdater = new TWPF_Twitter_Profile_Followers_Updater();
		if ('processing' == $fbFansUpdater->get('status')) {
			return [
				'success' => false,
				'message' => __('Unable to schedule updater. Error: Another job in progress.', 'twpf')
			];
		}

		$schedule = $fbFansUpdater->schedule_job();
		if (is_wp_error($schedule)) {
			return [
				'success' => false,
				'message' => sprintf(__('Unable to schedule job. %s', 'twpf'), $schedule->get_error_message())
			];
		} else {
			wp_schedule_single_event(time(), 'twpf_processor_cron');
			return [
				'success' => true,
				'urlReload' => true,
				'message' => __('Job scheduled.', 'twpf')
			];
		}
	}

	public function cancel_updater()
	{
		$fbFansUpdater = new TWPF_Twitter_Profile_Followers_Updater();
		$cancel = $fbFansUpdater->cancel_job();
		if (is_wp_error($cancel)) {
			return [
				'success' => false,
				'message' => sprintf(__('Unable to cancel job. %s', 'twpf'), $cancel->get_error_message())
			];
		} else {
			return [
				'success' => true,
				'urlReload' => true,
				'message' => __('Job cancelled.', 'twpf')
			];
		}
	}
}

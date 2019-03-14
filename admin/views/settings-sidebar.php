<h2><?php _e('Updater', 'twpf'); ?></h2>
<hr />
<p><?php
	$fbFansUpdater = new TWPF_Twitter_Profile_Followers_Updater();
	if ('processing' == $fbFansUpdater->get('status')) {
		printf(
			__('Updater is running since %s.', 'twpf'),
			human_time_diff($fbFansUpdater->get('time_started'))
		);

		?><br /><br /><button class="button button-primary wff_ajax_action_btn" data-alert="1" data-url="<?php echo rest_url('twpf/v2/twitter-profile-followers-updater/cancel'); ?>"><?php _e('Cancel Updater', 'twpf'); ?></button><?php
	} elseif ('cancelled' == $fbFansUpdater->get('status')) {
		printf(
			__('Last update were cancelled %s ago.<br />Total %d items were updated, %d failed.', 'twpf'),
			human_time_diff($fbFansUpdater->get('time_cancelled')),
			$fbFansUpdater->get('updated_count'),
			$fbFansUpdater->get('failed_count')
		);

		?><br /><br /><button class="button button-primary wff_ajax_action_btn" data-alert="1" data-url="<?php echo rest_url('twpf/v2/twitter-profile-followers-updater/start'); ?>"><?php _e('Update Now', 'twpf'); ?></button>
		<?php
	} elseif ('completed' == $fbFansUpdater->get('status')) {
		printf(
			__('Last update completed %s ago.<br />Total %d items were updated, %d failed.'),
			human_time_diff($fbFansUpdater->get('time_completed')),
			$fbFansUpdater->get('updated_count'),
			$fbFansUpdater->get('failed_count')
		);

		?><br /><br /><button class="button button-primary wff_ajax_action_btn" data-alert="1" data-url="<?php echo rest_url('twpf/v2/twitter-profile-followers-updater/start'); ?>"><?php _e('Update Now', 'twpf'); ?></button>
		<?php
	} elseif ('scheduled' == $fbFansUpdater->get('status')) {
		_e('Update scheduled, will start shortly. Reload this page to see updates.', 'twpf');
	} else {
		_e('No information about updater. Please update plugin settings and check back.', 'twpf');
	}
?><p>
<br />
<h2><?php _e('Cron Updater', 'twpf'); ?></h2>
<hr />
<p><?php
	if ($timestamp = wp_next_scheduled('twpf_updater_cron')) {
		printf(
			__('Next update is scheduled to run in %s', 'twpf'),
			human_time_diff($timestamp)
		);
	} else {
		_e('Update is not scheduled.', 'twpf');
	}

	if ($timestamp = wp_next_scheduled('twpf_processor_cron')) {
		echo '<br />';
		printf(__('Next process is scheduled to run in %s', 'twpf'), human_time_diff($timestamp));
	} elseif ($timestamp = wp_next_scheduled('twpf_processor_second_cron')) {
		echo '<br />';
		printf(__('Next process is scheduled to run in %s', 'twpf'), human_time_diff($timestamp));
	} else {
		echo '<br />';
		echo __('No process is running.', 'twpf');
	}
?></p>

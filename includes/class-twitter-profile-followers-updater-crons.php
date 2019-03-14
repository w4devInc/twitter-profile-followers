<?php
class TWPF_Twitter_Profile_Followers_Updater_Crons
{
	protected $updaterCronRecurrence;

	public function __construct($recurrence)
	{
		$this->updaterCronRecurrence = $recurrence;
	}

	public function reschedule_crons()
	{
		$this->remove_crons();
		$this->schedule_crons();
	}

	public function remove_crons()
	{
		$hooks = [
			'twpf_updater_cron',
			'twpf_processor_cron',
			'twpf_processor_second_cron'
		];

		foreach ($hooks as $hook) {
			$timestamp = wp_next_scheduled($hook);
			wp_unschedule_event($timestamp, $hook);
			wp_clear_scheduled_hook($hook);
		}
		# TWPF_Utils::d($hooks);
	}

	public function schedule_crons()
	{
		if ($this->updaterCronRecurrence) {
			wp_schedule_event(time(), $this->updaterCronRecurrence, 'twpf_updater_cron');
		}
	}
}

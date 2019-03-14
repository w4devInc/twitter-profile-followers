<?php

class TWPF_Plugin_Settings extends TWPF_Settings
{
	/* where we store the data */
	protected $option_name = 'twpf_settings';

	/* default settings */
	protected $settings = [
		'twitter_api_key' 				=> '',
		'twitter_api_secret'			=> '',
		'post_types'					=> '',
		'enable_debugging'				=> 'no'
	];

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
}

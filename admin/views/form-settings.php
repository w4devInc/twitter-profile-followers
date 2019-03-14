<?php
/**
 * Plugin Settings Form
**/

$options = $settings->get_settings();

$fields = [];
$fields['action'] = [
	'position'		=> 1,
	'name' 			=> 'action',
	'type' 			=> 'hidden',
	'value' 		=> 'twpf_settings_update'
];

$pos = 10;

++ $pos;
$fields[] = [
	'position'		=> $pos,
	'html'			=> '<div class="wf_field_group_title">'. __('Twitter API Credentials:', 'twpf') .'</div>
	<div class="wf_field_group_subtitle">'. sprintf(
		__('Api credentials can be found at your <a href="%s">twitter developers page</a>. <br />Find your app under the <code>Apps</code>, click on <code>Details</code> > <code>Keys and token</code>', 'twpf'),
		'https://developer.twitter.com/en/apps/'
	) .'</div>'
];

++ $pos;
$fields[] = [
	'position'		=> $pos,
	'label'    		=> __( 'Consumer API key', 'twpf' ),
	'name'	  		=> 'twitter_api_key',
	'type'    		=> 'text',
	'descs'			=> sprintf(
		__('Api credentials can be found at your <a href="%s">twitter developers page</a>.<br />Find your app under the "Apps",<br />click on <code>Details -> Keys and token</code>', 'twpf'),
		'https://developer.twitter.com/en/apps/'
	)
];
++ $pos;
$fields[] = [
	'position'		=> $pos,
	'label'    		=> __( 'Consumer API secret key', 'twpf' ),
	'name'	  		=> 'twitter_api_secret',
	'type'    		=> 'text'
];

++ $pos;
$fields[] = [
	'position'		=> $pos,
	'html'			=> '<div class="wf_field_group_title">'. __('General Settings:') .'</div>'
];

++ $pos;
$fields[] = [
	'position'		=> $pos,
	'label'    		=> __( 'Post Types', 'twpf' ),
	'name'	  		=> 'post_types',
	'type'    		=> 'checkbox',
	'option'		=> TWPF_Config::post_types(),
	'desc'			=> __('All of the selected post types will be scanned for page url data and updated with count', 'twpf')
];
++ $pos;
$fields[] = [
	'position'		=> $pos,
	'label'    		=> __( 'Meta key for Profile Url', 'twpf' ),
	'name'	  		=> 'profile_url_key',
	'type'    		=> 'text',
	'desc'			=> __('We will take the value of this field as the reference of twitter profile url.<br />The value can be full profile url, ie:<br /><code>https://www.twitter.com/google</code><br />or it can be the page slug ie <code>google</code>', 'twpf'),
	'default'		=> 'twitter_profile'
];
++ $pos;
$fields[] = [
	'position'		=> $pos,
	'label'    		=> __( 'Meta key for Page Fans Count', 'twpf' ),
	'name'	  		=> 'page_fan_count_key',
	'type'    		=> 'text',
	'desc'			=> __('This is the meta field where we will update the follower count.', 'twpf'),
	'default'		=> 'twitter_profile_follower_count'
];

++ $pos;
$fields[] = [
	'position'		=> $pos,
	'label'    		=> __( 'Cron Recurrence', 'twpf' ),
	'name'	  		=> 'job_recurrence',
	'type'    		=> 'select',
	'option'		=> TWPF_Config::cron_schedules(),
	'desc'			=> __('How frequently the script should run ?', 'twpf')
];


++ $pos;
$fields[] = [
	'position'		=> $pos,
	'html'			=> '<div class="wf_field_group_title">'. __('Advanced Settings:') .'</div>'
];

++ $pos;
$fields['enable_debugging'] = [
	'position'		=> $pos,
	'label'    		=> __('Enable debugging ?', 'pkbi'),
	'name'			=> 'enable_debugging',
	'type'    		=> 'radio',
	'option'		=> ['yes' => 'Yes', 'no' => 'No'],
	'desc'			=> __('By enabling debugging, you will be able to see process logs and trace errors', 'pkbi')
];

$form_args 	= [
	'id' 			=> 'twpf_settings_form',
	'name' 			=> 'twpf_settings_form',
	'ajax' 			=> true,
	'action' 		=> rest_url('twpf/v2/settings'),
	'loading_text'	=> __('Updating', 'twpf')
];

// allow filters
$fields = apply_filters( 'twpf/settings_page/form_fields', $fields, $options, $form_args );

// order by position
uasort( $fields, 'TWPF_Utils::order_by_position' ); // order by position

echo twpf_form_fields( $fields, $options, $form_args );

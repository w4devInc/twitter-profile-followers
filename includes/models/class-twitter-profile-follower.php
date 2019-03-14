<?php
/**
 * Handles twitter account communication
 * @package WordPress
 * @subpackage Twitter Profile Followers
 * @author Shazzad Hossain Khan
 * @url https://shazzad.me
**/


class TWPF_Twitter_Profile_Follower
{
	public $id = 0;
	public $data = [
		'profile_url'		=> '',
		'fan_count' 	=> '',
		'time_updated' 	=> ''
	];
	protected $profileUrlKey;
	protected $followerCountKey;
	protected $timeUpdatedKey = '_twpf_time_updated';

	function __construct($postId, $profileUrlKey, $followerCountKey)
	{
		$this->id = $postId;
		$this->profileUrlKey = $profileUrlKey;
		$this->followerCountKey = $followerCountKey;

		$this->read($postId);
	}

	public function get_profile_url()
	{
		return $this->data['profile_url'];
	}
	public function get_fan_count()
	{
		return $this->data['fan_count'];
	}
	public function get_time_updated()
	{
		return $this->data['time_updated'];
	}

	public function set_profile_url($val)
	{
		return $this->data['profile_url'] = (string) $val;
	}
	public function set_fan_count($val)
	{
		return $this->data['fan_count'] = (int) $val;
	}
	public function set_time_updated($val)
	{
		return $this->data['time_updated'] = (int) $val;
	}

	public function read($id)
	{
		$this->id = $id;
		$this->data = [
			'profile_url' 	=> get_post_meta($this->id, $this->profileUrlKey, true),
			'fan_count' 	=> get_post_meta($this->id, $this->followerCountKey, true),
			'time_updated' 	=> get_post_meta($this->id, $this->timeUpdatedKey, true)
		];
	}

	public function update()
	{
		$this->data['time_updated'] = time();

		update_post_meta($this->id, $this->profileUrlKey, $this->data['profile_url']);
		update_post_meta($this->id, $this->followerCountKey, $this->data['fan_count']);
		update_post_meta($this->id, $this->timeUpdatedKey, $this->data['time_updated']);
	}
}

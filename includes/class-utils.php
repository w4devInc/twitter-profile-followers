<?php
/**
 * The Plugin Class
 * @package WordPress
 * @subpackage  Twitter Profile Followers
 * @author Shazzad Hossain Khan
 * @url https://shazzad.me
**/


class TWPF_Utils
{
	// log created by addming action
	public static function log($str = '')
	{
		if ('yes' == twpf()->settings->get('enable_debugging')) {
			TWPF_Logger::log($str);
		}
	}
	public static function order_by_position($a, $b)
	{
		if (!isset($a['position']) || !isset($b['position'])) return -1;
		if ($a['position'] == $b['position']) return 0;
	    return ($a['position'] < $b['position']) ? -1 : 1;
	}
	public static function ajax_error($html, $args = array())
	{
		self::ajax_response(array_merge(array('status'=>'error','html' => $html), $args));
	}
	public static function ajax_ok($html, $args = array())
	{
		self::ajax_response(array_merge(array('status'=>'ok','html' => $html), $args));
	}
	public static function ajax_response($a)
	{
		@error_reporting(0);
		header('Content-type: application/json');
		echo json_encode($a);
		die('');
	}
	public static function d($a)
	{
		self::p($a);
		die();
	}
	public static function p($a)
	{
		echo '<pre>';
		print_r($a);
		echo '</pre>';
	}
	public static function is_localhost()
	{
		return in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1', '192.168.0.2'));
	}
	public static function validate_cookie_user()
	{
		if (isset($_COOKIE[LOGGED_IN_COOKIE]) && $user_id = wp_validate_auth_cookie($_COOKIE[LOGGED_IN_COOKIE], 'logged_in')) {
			wp_set_current_user($user_id);
		}
	}
}

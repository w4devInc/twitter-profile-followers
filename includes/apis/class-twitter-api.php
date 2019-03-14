<?php
/**
 * Twitter API
 * @package WordPress
 * @subpackage Twitter Profile Followers
 * @author Shazzad Hossain Khan
 * @url https://shazzad.me
**/


class TWPF_Twitter_Api
{
	protected $apiKey = null;
	protected $apiSecret = null;
	protected $apiEndpoint = 'https://api.twitter.com/';
	protected $accessToken;

	public function __construct($apiKey = '', $apiSecret = '')
	{
		if (! empty($apiKey)) {
			$this->apiKey = $apiKey;
		}
		if (! empty($apiSecret)) {
			$this->apiSecret = $apiSecret;
		}

		if (! $this->accessToken) {
			$accessToken = $this->getAccessToken();
			if (! is_wp_error($accessToken)) {
				$this->accessToken = $accessToken;
			}
		}
	}

	public function isReady()
	{
		return ! empty($this->apiKey) && ! empty($this->apiSecret);
	}

	public function getAccessToken()
	{
		$data = $this->post('/oauth2/token?grant_type=client_credentials', [
			'headers' => [
				'Authorization' => 'Basic '. base64_encode($this->apiKey .':'. $this->apiSecret)
			]
		], 3600);

		if (is_wp_error($data)) {
			return $data;
		}

		return $data['access_token'];
	}

	public function getFollowers($screenNames = [])
	{
		$data = $this->get('/1.1/users/lookup.json?include_entities=false&screen_name='. implode($screenNames, ','), [
			'headers' => [
				'Authorization' => 'Bearer '. $this->accessToken
			]
		], 0);

		if (is_wp_error($data)) {
			return $data;
		}

		$followersCount = [];
		foreach ($data as $item) {
			$followersCount[strtolower($item['screen_name'])] = $item['followers_count'];
		}

		return $followersCount;
	}

	public function getTestCount()
	{
		return $this->getFollowers(['twitter']);
	}

	public function post($path = '/', $args = [], $cached = 0)
	{
		return $this->request('POST', $path, $args, $cached);
	}
	public function get($path = '/', $args = [], $cached = 0)
	{
		return $this->request('GET', $path, $args, $cached);
	}
	private function request($method, $path = '/', $args = array(), $cached = 0)
	{
		$args = wp_parse_args($args, array(
			'method' => $method,
			'headers' => array('Content-type' => 'application/json')
		));
		if (! empty($args['body'])) {
			$args['body'] = json_encode($args['body']);
		}
		$url = $this->apiEndpoint . $path;

		$response = false;
		if ($cached > 0) {
			$cache_key = 'twpf_api_'. md5($method . $url . serialize($args));
			$response = get_transient($cache_key);
		}

		if (! $cached || false === $response) {
			// TWPF_Utils::log('Twitter Api - ' . $method . ' '. $path);
			$request = wp_remote_get($url, $args);

			if (is_wp_error($request)) {
				return $request;
			}

			$response = json_decode(wp_remote_retrieve_body($request), true);
			if ($cached > 0) {
				set_transient($cache_key, $response, $cached);
			}
		}

		if (isset($response['errors'])) {
			$errors = new WP_Error();
			if (! empty($response['errors'])) {
				foreach ($response['errors'] as $error) {
					$errors->add(isset($error['label']) ? $error['label'] : $error['code'], $error['message']);
				}
			} else {
				$errors->add('twpfApiError', __('Twitter api error'));
			}

			# TWPF_Utils::d($errors);
			return $errors;
		}

		return $response;
	}
}

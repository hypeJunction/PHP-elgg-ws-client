<?php

namespace hypeJunction\WebServices;

/**
 * PHP Client for interfacing with Elgg's web services
 */
class Client {

	/**
	 * URL of the Elgg installation
	 * @var string
	 */
	private $base_url;

	/**
	 * API Key
	 * @var string
	 */
	private $api_key;

	/**
	 * Default cURL options
	 * @var array 
	 */
	private $curl_options = [
		CURLOPT_SSL_VERIFYHOST => 0,
	];

	/**
	 * Constructor
	 *
	 * @param string $base_url     URL of the Elgg installation
	 * @param string $api_key      API Key
	 * @param array  $curl_options Additional cURL options
	 */
	public function __construct($base_url, $api_key = null, array $curl_options = null) {
		$this->base_url = $base_url;
		$this->api_key = $api_key;
		if (isset($curl_options)) {
			$this->curl_options = $curl_options;
		}
	}

	/**
	 * Build API call URL
	 *
	 * @param string $method     API method
	 * @param string $auth_token User auth token
	 * @return string
	 */
	public function buildUrl($method, $auth_token = null) {
		$query_data = array(
			'method' => $method,
			'api_key' => $this->api_key,
			'auth_token' => $auth_token,
		);
		ksort($query_data);
		$query = http_build_query(array_filter($query_data));
		$domain = rtrim($this->base_url, '/');
		return "$domain/services/api/rest/json/?$query";
	}

	/**
	 * Make a cURL request
	 *
	 * @param string $url    URL to fetch
	 * @param array  $fields Request data
	 * @param array  $method HTTP method
	 * @return mixed API call result
	 */
	public function fetch($url, array $fields = array(), $method = 'POST') {

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		foreach ($this->curl_options as $key => $value) {
			curl_setopt($ch, $key, $value);
		}

		switch (strtoupper($method)) {
			case 'GET' :
				curl_setopt($ch, CURLOPT_POST, false);
				break;

			default:
				$fields_string = http_build_query($fields);
				curl_setopt($ch, CURLOPT_POST, count($fields));
				curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
				break;
		}

		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			error_log(curl_error($ch));
		}


		curl_close($ch);
		return json_decode($result, true);
	}

	/**
	 * Get a token for performing API calls on behalf of the Elgg user
	 *
	 * @param string $username Username
	 * @param string $password Password
	 * @return string|false
	 */
	public function getAuthToken($username, $password) {
		$data = $this->post('auth.gettoken', [
			'username' => $username,
			'password' => $password,
		]);
		if (!isset($data['result'])) {
			return false;
		}
		return $data['result'];
	}

	/**
	 * Make POST API call
	 *
	 * @param string $method     Web services method to request
	 * @param array  $fields     Data to send with the request
	 * @param string $auth_token Auth token to use for the request
	 * @return mixed API call result
	 */
	public function post($method, array $fields = array(), $auth_token = null) {
		return $this->fetch($this->buildUrl($method, $auth_token), $fields, 'POST');
	}

	/**
	 * Make GET api call
	 *
	 * @param string $method     Web services method to request
	 * @param array  $fields     Data to send with the request
	 * @param string $auth_token Auth token to use for the request
	 * @return mixed API call result
	 */
	public function get($method, array $fields = array(), $auth_token = null) {

		$url = $this->buildUrl($method, $auth_token);

		$glue = parse_url($url, PHP_URL_QUERY) ? '&' : '?';
		$url .= $glue . http_build_query($fields);

		return $this->fetch($url, $fields, 'GET');
	}

}

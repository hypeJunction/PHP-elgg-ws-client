<?php

namespace hypeJunction\WebServices;

class ClientTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var string
	 */
	private $base_url = 'http://localhost/';

	/**
	 * @var string
	 */
	private $api_key = 'apikey_abcdef123456';

	/**
	 * @var string
	 */
	private $auth_token = 'token_abcdef123456';

	/**
	 *
	 * @var Client
	 */
	private $client;

	/**
	 * @var array
	 */
	private $success = [
		'status' => 0,
		'result' => [
			'foo' => 'bar',
		],
	];

	public function setUp() {

		$this->client = $this->getMockBuilder(Client::class)
				->setConstructorArgs([$this->base_url, $this->api_key])
				->setMethods(['fetch'])
				->getMock();

		$this->client->expects($this->any())
				->method('fetch')
				->will($this->returnCallback(array($this, 'fetch')));
	}

	public function fetch($url, array $fields = array(), $method = 'POST') {

		switch ($url) {
			case "http://localhost/services/api/rest/json/?api_key=$this->api_key&method=auth.gettoken" :
				return [
					'status' => 0,
					'result' => $this->auth_token,
				];

			case "http://localhost/services/api/rest/json/?api_key=$this->api_key&method=post.foo.bar" :
			case "http://localhost/services/api/rest/json/?api_key=$this->api_key&method=get.foo.bar&foo=bar" :
				return $this->success;
		}

		return [
			'status' => -1,
			'result' => [],
			'message' => 'Invalid URL',
		];
	}

	public function testCanBuildUrlWithoutAuthToken() {
		$method = 'foo.bar';
		$expected = "http://localhost/services/api/rest/json/?api_key=$this->api_key&method=$method";
		$this->assertEquals($expected, $this->client->buildUrl($method));
	}

	public function testCanBuildUrlWithAuthToken() {
		$method = 'foo.bar';
		$expected = "http://localhost/services/api/rest/json/?api_key=$this->api_key&auth_token=$this->auth_token&method=$method";
		$this->assertEquals($expected, $this->client->buildUrl($method, $this->auth_token));
	}

	public function testCanGetUserToken() {
		$this->assertEquals($this->auth_token, $this->client->getAuthToken('username', 'password'));
	}

	public function testCanRequestPostMethod() {
		$this->assertEquals($this->success, $this->client->post('post.foo.bar', ['foo' => 'bar']));
	}

	public function testCanRequestGetMethod() {
		$this->assertEquals($this->success, $this->client->get('get.foo.bar', ['foo' => 'bar']));
	}

}

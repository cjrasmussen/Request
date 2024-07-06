<?php

use cjrasmussen\Request\RequestData;
use PHPUnit\Framework\TestCase;

class RequestDataTest extends TestCase
{
	public function testConstructor(): void
	{
		$get = [
			'alpha' => 'foo',
			'beta' => 'bar',
			'gamma' => 'biz',
		];

		$requestData = new RequestData($get, [], [], [], [], []);

		$this->assertEquals(count($get), $requestData->query->count());
		$this->assertEquals($get['alpha'], $requestData->query->get('alpha'));
		$this->assertEquals($get['beta'], $requestData->query->get('beta'));
		$this->assertEquals($get['gamma'], $requestData->query->get('gamma'));
	}

	public function testIsWebRequest_true(): void
	{
		$server = [
			'REQUEST_METHOD' => 'GET',
			'REQUEST_URI' => '/some/fake/path',
		];

		$requestData = new RequestData([], [], [], [], $server, []);

		$this->assertTrue($requestData->isWebRequest());
	}

	public function testIsWebRequest_false(): void
	{
		$server = [
			'PHP_SELF' => '/some/fake/path',
		];

		$requestData = new RequestData([], [], [], [], $server, []);

		$this->assertFalse($requestData->isWebRequest());
	}

	public function testIsRequestHttps_null(): void {
		$server = [
			'PHP_SELF' => '/some/fake/path',
		];

		$requestData = new RequestData([], [], [], [], $server, []);

		$this->assertNull($requestData->isRequestHttps());
	}

	public function testIsRequestHttps_false(): void {
		$server = [
			'HTTPS' => 'off',
			'REQUEST_METHOD' => 'GET',
			'REQUEST_URI' => '/some/fake/path',
		];

		$requestData = new RequestData([], [], [], [], $server, []);

		$this->assertFalse($requestData->isRequestHttps());
	}

	public function testIsRequestHttps_true(): void {
		$server = [
			'HTTPS' => 'on',
			'REQUEST_METHOD' => 'GET',
			'REQUEST_URI' => '/some/fake/path',
		];

		$requestData = new RequestData([], [], [], [], $server, []);

		$this->assertTrue($requestData->isRequestHttps());

		$server = [
			'HTTPS' => true,
			'REQUEST_METHOD' => 'GET',
			'REQUEST_URI' => '/some/fake/path',
		];

		$requestData = new RequestData([], [], [], [], $server, []);

		$this->assertTrue($requestData->isRequestHttps());

		$server = [
			'HTTPS' => 'yep',
			'REQUEST_METHOD' => 'GET',
			'REQUEST_URI' => '/some/fake/path',
		];

		$requestData = new RequestData([], [], [], [], $server, []);

		$this->assertTrue($requestData->isRequestHttps());
	}

	public function testGetRequestedUrl_null(): void {
		$server = [
			'PHP_SELF' => '/some/fake/path',
		];

		$requestData = new RequestData([], [], [], [], $server, []);

		$this->assertNull($requestData->getRequestedUrl());
	}

	public function testGetRequestedUrl_http(): void {
		$server = [
			'HTTP_HOST' => 'somedomain.com',
			'HTTPS' => 'off',
			'REQUEST_METHOD' => 'GET',
			'REQUEST_URI' => '/some/fake/path',
		];

		$requestData = new RequestData([], [], [], [], $server, []);

		$expected = 'http://somedomain.com/some/fake/path';
		$this->assertEquals($expected, $requestData->getRequestedUrl());
	}

	public function testGetRequestedUrl_https(): void {
		$server = [
			'HTTP_HOST' => 'somedomain.com',
			'HTTPS' => 'on',
			'REQUEST_METHOD' => 'GET',
			'REQUEST_URI' => '/some/fake/path',
		];

		$requestData = new RequestData([], [], [], [], $server, []);

		$expected = 'https://somedomain.com/some/fake/path';
		$this->assertEquals($expected, $requestData->getRequestedUrl());
	}
}

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

	public function testSanitization(): void
	{
		$get = [
			'alpha' => 'foo',
			'beta' => 'bar',
			'gamma' => 'biz',
			'stringFloat' => '1.23456',
			'stringInt' => '123',
			'stringFloatLarge' => '9225372036854775807.23456',
			'stringIntLarge' => '9233372036854775807',
			'stringFloatSmall' => '-9225372036854775807.23456',
			'stringIntSmall' => '-9233372036854775807',
			'stringIntZeros' => '000000328765',
			'float' => 66.66667,
			'int' => 6789,
			'stringPadded' => '     lorem ipsum dolor sit    ',
			'stringSlashes' => '\"This is some quoted text\"',
		];

		$requestData = new RequestData($get, [], [], [], [], []);

		$this->assertSame(count($get), $requestData->query->count());
		$this->assertSame($get['alpha'], $requestData->query->get('alpha'));
		$this->assertSame($get['beta'], $requestData->query->get('beta'));
		$this->assertSame($get['gamma'], $requestData->query->get('gamma'));

		$this->assertSame($get['stringFloat'], $requestData->getRawData(RequestData::METHOD_GET)->get('stringFloat'));
		$this->assertSame($get['stringInt'], $requestData->getRawData(RequestData::METHOD_GET)->get('stringInt'));
		$this->assertSame($get['stringFloatLarge'], $requestData->getRawData(RequestData::METHOD_GET)->get('stringFloatLarge'));
		$this->assertSame($get['stringIntLarge'], $requestData->getRawData(RequestData::METHOD_GET)->get('stringIntLarge'));
		$this->assertSame($get['stringFloatSmall'], $requestData->getRawData(RequestData::METHOD_GET)->get('stringFloatSmall'));
		$this->assertSame($get['stringIntSmall'], $requestData->getRawData(RequestData::METHOD_GET)->get('stringIntSmall'));
		$this->assertSame($get['stringIntZeros'], $requestData->getRawData(RequestData::METHOD_GET)->get('stringIntZeros'));

		$this->assertEquals($get['stringFloat'], $requestData->query->get('stringFloat'));
		$this->assertEquals($get['stringInt'], $requestData->query->get('stringInt'));
		$this->assertEquals($get['stringFloatLarge'], $requestData->query->get('stringFloatLarge'));
		$this->assertEquals($get['stringIntLarge'], $requestData->query->get('stringIntLarge'));
		$this->assertEquals($get['stringFloatSmall'], $requestData->query->get('stringFloatSmall'));
		$this->assertEquals($get['stringIntSmall'], $requestData->query->get('stringIntSmall'));
		$this->assertEquals($get['stringIntZeros'], $requestData->query->get('stringIntZeros'));

		$this->assertSame((float)$get['stringFloat'], $requestData->query->get('stringFloat'));
		$this->assertSame((int)$get['stringInt'], $requestData->query->get('stringInt'));
		$this->assertSame((float)$get['stringFloatLarge'], $requestData->query->get('stringFloatLarge'));
		$this->assertNotSame((int)$get['stringIntLarge'], $requestData->query->get('stringIntLarge'));
		$this->assertSame($get['stringIntLarge'], $requestData->query->get('stringIntLarge'));
		$this->assertSame((float)$get['stringFloatSmall'], $requestData->query->get('stringFloatSmall'));
		$this->assertNotSame((int)$get['stringIntSmall'], $requestData->query->get('stringIntSmall'));
		$this->assertSame($get['stringIntSmall'], $requestData->query->get('stringIntSmall'));
		$this->assertNotSame((int)$get['stringIntZeros'], $requestData->query->get('stringIntZeros'));
		$this->assertSame($get['stringIntZeros'], $requestData->query->get('stringIntZeros'));
		$this->assertSame($get['float'], $requestData->query->get('float'));
		$this->assertSame($get['int'], $requestData->query->get('int'));

		$this->assertEquals($get['stringPadded'], $requestData->getRawData(RequestData::METHOD_GET)->get('stringPadded'));
		$this->assertEquals(trim($get['stringPadded']), $requestData->query->get('stringPadded'));

		$this->assertEquals($get['stringSlashes'], $requestData->getRawData(RequestData::METHOD_GET)->get('stringSlashes'));
		$this->assertEquals(stripslashes($get['stringSlashes']), $requestData->query->get('stringSlashes'));
	}

	public function testUploadedFileSanitization(): void
	{
		$files = [
			'file1' => [
				'name' => 'file1.png',
				'type' => 'image/png',
				'tmp_name' => '/tmp/guiafhv',
				'error' => UPLOAD_ERR_OK,
				'size' => 1024,
			],
			'fileArray' => [
				'name' => [
					'file2' => 'file2.jpg',
					'file3' => 'file3.gif',
				],
				'type' => [
					'file2' => 'image/jpg',
					'file3' => 'image/gif',
				],
				'tmp_name' => [
					'file2' => '/tmp/soufwf',
					'file3' => '/tmp/objsbe',
				],
				'error' => [
					'file2' => UPLOAD_ERR_NO_FILE,
					'file3' => UPLOAD_ERR_CANT_WRITE,
				],
				'size' => [
					'file2' => 2048,
					'file3' => 4096,
				],
			],
		];

		$requestData = new RequestData([], [], [], $files, [], []);

		$this->assertEquals($files['file1']['name'], $requestData->files->get('file1')->name);
		$this->assertEquals($files['file1']['type'], $requestData->files->get('file1')->type);
		$this->assertEquals($files['file1']['tmp_name'], $requestData->files->get('file1')->tmpName);
		$this->assertEquals($files['file1']['error'], $requestData->files->get('file1')->error);
		$this->assertEquals($files['file1']['size'], $requestData->files->get('file1')->size);

		$this->assertEquals($files['fileArray']['name']['file2'], $requestData->files->get('fileArray')['file2']->name);
		$this->assertEquals($files['fileArray']['type']['file2'], $requestData->files->get('fileArray')['file2']->type);
		$this->assertEquals($files['fileArray']['tmp_name']['file2'], $requestData->files->get('fileArray')['file2']->tmpName);
		$this->assertEquals($files['fileArray']['error']['file2'], $requestData->files->get('fileArray')['file2']->error);
		$this->assertEquals($files['fileArray']['size']['file2'], $requestData->files->get('fileArray')['file2']->size);

		$this->assertEquals($files['fileArray']['name']['file3'], $requestData->files->get('fileArray')['file3']->name);
		$this->assertEquals($files['fileArray']['type']['file3'], $requestData->files->get('fileArray')['file3']->type);
		$this->assertEquals($files['fileArray']['tmp_name']['file3'], $requestData->files->get('fileArray')['file3']->tmpName);
		$this->assertEquals($files['fileArray']['error']['file3'], $requestData->files->get('fileArray')['file3']->error);
		$this->assertEquals($files['fileArray']['size']['file3'], $requestData->files->get('fileArray')['file3']->size);
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
		$this->assertSame($expected, $requestData->getRequestedUrl());
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
		$this->assertSame($expected, $requestData->getRequestedUrl());
	}
}

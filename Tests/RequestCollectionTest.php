<?php

use cjrasmussen\Request\RequestCollection;
use PHPUnit\Framework\TestCase;

class RequestCollectionTest extends TestCase
{
	public function testConstructor(): void
	{
		$array = [
			'alpha' => 'foo',
			'beta' => 'bar',
			'gamma' => 'biz',
		];

		$collection = new RequestCollection($array);

		$this->assertEquals(count($array), $collection->count());
		$this->assertEquals($array['alpha'], $collection->get('alpha'));
		$this->assertEquals($array['beta'], $collection->get('beta'));
		$this->assertEquals($array['gamma'], $collection->get('gamma'));
	}
}

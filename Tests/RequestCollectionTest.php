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

		$this->assertSame(count($array), $collection->count());
		$this->assertSame($array['alpha'], $collection->get('alpha'));
		$this->assertSame($array['beta'], $collection->get('beta'));
		$this->assertSame($array['gamma'], $collection->get('gamma'));
	}
}

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
			'delta' => '',
			'epsilon' => 0,
			'zeta' => null,
		];

		$collection = new RequestCollection($array);

		$this->assertSame(count($array), $collection->count());
		$this->assertTrue($collection->has('alpha'));
		$this->assertSame($array['alpha'], $collection->get('alpha'));
		$this->assertSame($array['beta'], $collection->get('beta'));
		$this->assertSame($array['gamma'], $collection->get('gamma'));
		$this->assertTrue($collection->has('delta'));
		$this->assertSame($array['delta'], $collection->get('delta'));
		$this->assertTrue($collection->has('epsilon'));
		$this->assertSame($array['epsilon'], $collection->get('epsilon'));
		$this->assertFalse($collection->has('zeta'));
		$this->assertSame($array['zeta'], $collection->get('zeta'));
		$this->assertFalse($collection->has('eta'));
	}
}

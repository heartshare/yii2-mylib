<?php
namespace tests\components;

use app\components\ApcCache;

class ApcCacheTest extends \tests\AppTestCase
{
	public function test_buildKey()
	{
		$prefix = 'prefix';
		$key = 'z123';
		$apc = new ApcCache(['keyPrefix' => $prefix]);
		
		$this->assertEquals($prefix.$key, $apc->buildKey($key)); //string
		$this->assertEquals($prefix.$key.':'.md5($key), $apc->buildKey([$key])); //array
	}
	
}
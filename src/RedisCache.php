<?php
/**
 * raysoft\RollingCurl\RedisCache
 * RedisCache
 */
namespace raysoft\RollingCurl;

class RedisCache
{
	protected $redis = null;

	public function __construct($host='127.0.01', $port=6379)
	{
		static $redis = null;
		if( !$redis )
		{
			$redis = new \Redis;
			$redis->connect($host, $port);
		}

		$this->redis = $redis;
	}
}
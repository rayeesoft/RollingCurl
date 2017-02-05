<?php
/**
 * raysoft\RollingCurl\HashMap
 * HashMap
 */
namespace raysoft\RollingCurl;

class HashMap extends RedisCache
{
	private $name;

	public function __construct($name, $host='127.0.01', $port=6379)
	{
		$this->name = $name;
		parent::__construct($host, $port);
	}

	/**
	 * Set value
	 * @param $key
	 * @param $value
	 * @return int
	 */
	public function set($key, $value)
	{
		return $this->redis->hSet( $this->name, $key, $value );
	}

    /**
     * Get value by key
     * @param $key
     * @return string
     */
	public function get($key)
	{
		return $this->redis->hGet( $this->name, $key );
	}

    /**
     * Delete by key
     * @param $key
     * @return int
     */
	public function del($key)
	{
		return $this->redis->hDel( $this->name, $key );
	}

    /**
     * Get length
     * @return int
     */
	public function size()
	{
		return $this->redis->hLen($this->name);
	}

    /**
     * Clean the hashmap
     * @return bool
     */
	public function clean()
	{
		$keys = $this->redis->hKeys($this->name);
		foreach($keys as $key)
			$this->del($key);
		
		return true;
	}
}
<?php
/**
 * raysoft\RollingCurl\Queue
 * 队列
 */
namespace raysoft\RollingCurl;

class Queue extends RedisCache
{
    private $name;

    public function __construct($name, $host = '127.0.01', $port = 6379)
    {
        $this->name = $name;
        parent::__construct($host, $port);
    }

    /**
     * @param $data
     * @return int
     */
    public function push($data)
    {
        return $this->redis->lPush($this->name, $data);
    }

    /**
     * @return string
     */
    public function pop()
    {
        return $this->redis->rPop($this->name);
    }

    /**
     * @return int
     */
    public function size()
    {
        return $this->redis->lLen($this->name);
    }

    /**
     * 清空队列
     * @return bool
     */
    public function clean()
    {
        $this->redis->lTrim($this->name, 0, 0);
        $this->redis->lPop($this->name);
        return true;
    }
}
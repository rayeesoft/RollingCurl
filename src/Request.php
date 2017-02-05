<?php
/*
* raysoft\RollingCurl\Request
* 请求类  
*/
namespace raysoft\RollingCurl;

class Request
{
	/**
	 * 请求地址
	 * @type string
	 */
	public $url = '';

	/**
	 * 任务编号
	 * @type string
	 */
	public $key = '';

	/**
	 * 任务名称
	 * @type string
	 */
	public $name = '';

	/**
	 * 请求方式
	 * @type string
	 */
	public $method = 'GET';

	/**
	 * POST数据
	 * @type string
	 */
	public $post_data = '';


	/**
	 * 额外的HTTP头数据
	 * @type array
	 */
	public $header = array();

	/**
	 * 回调地址
	 * 如果请求完成后，将请求这个地址，以POST的方式传回数据
	 * @type string
	 */
	public $callback_url = '';

	/**
	 * 重试次数
	 * @type integer
	 */
	public $retry_times = 0;

	/**
	 * 出错次数
	 * @type integer
	 */
	public $error_times = 0;
	
	/**
	 * 正确的响应状态
	 * 用以判断请求是否正确
	 * 如果设定了请求数据，状态与此不同，将会再次执行
	 * @type integer
	 */
	public $correct_status = 200;

	/**
	 * 是否要求在请求队列中唯一
	 * @type boolean
	 */
	public $unique_in_queue = false;

	/**
	 * 是否要求在运行队列中唯一
	 * @type boolean
	 */
	public $unique_in_running = false;


	public function __construct($name, $url='', $method='GET', $post_data='', $correct_status=200)
	{
		$this->url = $url;
		$this->name = $name;
		$this->method = $method;
		$this->post_data = $post_data;
		$this->correct_status = $correct_status;
	}

	public function setUrl( $url )
	{
		$this->url = $url;
	}

	public function setKey( $key )
	{
		$this->key = $key;
	}

	public function setPostData( $data )
	{
		$this->method = 'POST';
		$this->post_data = $data;
	}

	public function setHeader( $header )
	{
		if( !$header )
			return false;

		if( !is_array($header) )
			$header = array($header);

		$this->header = $header;
		return true;
	}

	public function setRetryTimes( $times )
	{
		$this->retry_times = (int)$times;
	}

	public function setCallbackUrl( $url )
	{
		$this->callback_url = $url;
	}

	public function setUniqueInQueue( $bool )
	{
		$this->unique_in_queue = $bool;
	}

	public function setUniqueInRunning( $bool )
	{
		$this->unique_in_running = $bool;
	}

	public function getUID()
	{
		return $this->name.':'.$this->key;
	}
}
<?php
/**
 * raysoft\RollingCurl\Manager
 * 管理器
 */
namespace raysoft\RollingCurl;

class Manager
{
	/**
	 * 请求队列
	 * @type \raysoft\RollingCurl\Queue
	 */
	private $requests;
	/**
	 * 请求FLAG记录
	 * @type \raysoft\RollingCurl\Queue
	 */
	private $requests_flag;

	/**
	 * 正在运行的任务
	 * key为handler的标志
	 * @type \raysoft\RollingCurl\HashMap
	 */
	private static $runnings;
	/**
	 * 正在运行任务的FLAG记录
	 * key为任务的UID
	 * @type \raysoft\RollingCurl\HashMap
	 */
	private static $runnings_flag;


	public function __construct()
	{
		$this->requests = new Queue('REQUEST');
		$this->requests_flag = new HashMap('REQUEST_FLAG');

		if( !self::$runnings ) {
			self::$runnings = new HashMap('RUNNING');
		}

		if( !self::$runnings_flag ) {
			self::$runnings_flag = new HashMap('RUNNING_FLAG');
		}
	}

	/**
	 * 添加请求
	 * @param $request
	 * @return bool
	 */
	public function add($request)
	{
		// 检查是否是任务实例
		if( !$request instanceof Request )
			die('Request must be raysfot\RollingCurl\Request\'s instance');

		// 获取任务UID
		$request_uid = $request->getUID();

		// 如果是唯一任务，检查任务是否已存在
		if( $request->unique_in_queue && $this->requests_flag->get( $request_uid ) )
		{
			Log::warning( 'Request ['.$request_uid."] is already in queue. Queue length:".$this->requests->size() );
			return false;
		}

		// 如果是唯一运行，检查正在运行的队列
		if( $request->unique_in_running && self::$runnings_flag->get( $request_uid ) )
		{
			Log::warning('Request ['.$request_uid."] is running");
			return false;
		}

		// 把任务添加进队列
		$this->requests->push(serialize($request));

		// 设置任务标志为已存在
		$this->requests_flag->set( $request_uid, 1 );

		return true;
	}

	/**
	 * 开始执行请求
	 */
	public static function run()
	{
		Log::info("Notify new request...");

		// 通知守护进程有新任务加入
		Notifier::startRquest();

		return true;
	}

	public function queue()
	{
		return array(
			'request' => $this->requests->size(),
			'running' => self::$runnings->size()
		);
	}

	public function debug_clean()
	{
		self::$runnings->clean();
		self::$runnings_flag->clean();
		
		$this->requests->clean();
		$this->requests_flag->clean();
	}
}
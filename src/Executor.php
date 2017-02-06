<?php
/**
 * raysoft\RollingCurl\Excutor
 * 执行器
 * 运行于CLI模式，由Daemo调用
 */
namespace raysoft\RollingCurl;

class Executor
{
	/**
	 * 请求队列
	 * @type \raysoft\RollingCurl\Queue
	 */
	private $requests;
	/**
	 * 请求FLAG记录
	 * @type \raysoft\RollingCurl\HashMap
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

	/**
	 * 每秒并发数
	 * @var integer
	 */
	private $tunnels = 10;


	public function __construct()
	{
		$this->requests      = new Queue('REQUEST');
		$this->requests_flag = new HashMap('REQUEST_FLAG');

        if( !self::$runnings ) {
            self::$runnings = new HashMap('RUNNING');
        }

        if( !self::$runnings_flag ) {
            self::$runnings_flag = new HashMap('RUNNING_FLAG');
        }
	}

	public function cliRun()
	{
		$this->execute();
	}

	private function execute()
	{
		$ch = curl_multi_init();

		// 循环等待任务，30秒超时，0.01*3000
		for ($i = 0; $i < 3000; $i++) {
			// 执行rolling curl
			$this->rolling_curl($ch);

			// 等待10毫秒
			usleep(10000);

			// 检查队列是否还有请求
			if ($this->requests->size() == 0) {
				break;
			}
		}

		curl_multi_close($ch);
		Log::debug('All done');
	}

	/**
	 * @param resource $master
	 */
	private function rolling_curl($master)
	{
		do {
			// 添加请求
			$count = $this->add_curl($master);
			if ($count) {
				Log::debug('Add request count: '.$count);
			}

			// 执行所有的请求
			while (($execrun = curl_multi_exec($master, $active)) == CURLM_CALL_MULTI_PERFORM) {
				;
			}
			if ($execrun != CURLM_OK) {
				Log::error('curl_multi_exec error');
				break;
			}

			// 检查是否有完成的请求
			while ($done = curl_multi_info_read($master, $msgs_in_queue)) {
				// 获取请求信息
				$info   = curl_getinfo($done['handle']);
				$output = curl_multi_getcontent($done['handle']);

				// 获取任务信息
				$key     = (string)$done['handle'];

				/** @type \raysoft\RollingCurl\Request $request */
				$request = unserialize(self::$runnings->get($key));

				// 删除运行中的请求
				self::$runnings->del($key);
				self::$runnings_flag->del($request->getUID());

				// 处理结果
				$this->process_result($request, $info, $output, $done['handle']);

				// 移除已完成的任务
				curl_multi_remove_handle($master, $done['handle']);
				curl_close($done['handle']);
			}

			// 阻塞，等待输入输出
			if ($active) {
				curl_multi_select($master);
			}
		} while ($active);
	}

	/**
	 * 添加请求到master
	 * @param $master
	 * @return int
	 */
	private function add_curl($master)
	{
		static $last_time = 0;
		static $last_count = 0;

		$add_count = 0;

		$time = time();

		if ($time != $last_time) {
			$last_time  = $time;
			$last_count = 0;
		}

		for ($i = 0; $i < $this->tunnels; $i++) {
			// 检查这一秒有是否达到上限
			if ($last_count >= $this->tunnels) {
				break;
			}

			// 是否有任务
			if ( ($ch = $this->prepare_curl()) == false) {
				continue;
			}

			curl_multi_add_handle($master, $ch);
			$add_count++;
			$last_count++;
		}
		return $add_count;
	}

	/**
	 * 准备curl请求
	 * @return resource
	 */
	private function prepare_curl()
	{
		// 从请求队列中取出一个请求
		/** @type \raysoft\RollingCurl\Request $request */
		$request = $this->requests->pop();
		if (!$request) {
			return false;
		}

		// 还原请求实例
		$request = unserialize($request);

		// 从请求FLAG队列中删除请求标志
		$this->requests_flag->del($request->getUID());

		// 准备curl请求
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $request->url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if ($request->method == 'POST') {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $request->post_data);
		}
		if ($request->header) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $request->header);
		}

		// 添加到运行列表
		self::$runnings->set((string)$ch, serialize($request));

		// 添加到运行FLAG表
		self::$runnings_flag->set($request->getUID(), 1);

		return $ch;
	}

	/**
	 * 处理结果
	 * @param $request
	 * @param $info
	 * @param $result
	 * @param $handle
	 * @return bool
	 */
	private function process_result($request, $info, $result, $handle)
	{
		Log::info('process_result:'.$result->url);

		// 如果没有设置回调地址，直接返回
		if (!$request->callback_url) {
			return true;
		}

		// 推送到回调地址
		$data = [];
		$data['key']    = $request->key;
		$data['time']   = time();
		$data['result'] = $result;

		Notifier::sendCallback($request->callback_url, $data);
		return true;
	}
}
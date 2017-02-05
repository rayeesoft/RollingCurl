<?php
namespace raysoft\RollingCurl;

class Log
{
    const ERROR     = 'ERROR';
    const WARNING   = 'WARNING';
    const INFO      = 'INFO';
    const DEBUG     = 'DEBUG';

	private static $log_levels = array(
		self::ERROR,
		self::WARNING,
		self::INFO,
		self::DEBUG
	);

	/**
	 * @param $message
	 */
	public static function error($message)
	{
		self::write(self::ERROR, $message);
	}

	/**
	 * @param $message
	 */
	public static function warning($message)
	{
		self::write(self::WARNING, $message);
	}

	/**
	 * @param $message
	 */
	public static function info($message)
	{
		self::write(self::INFO, $message);
	}

    /**
     * @param $message
     */
	public static function debug($message)
	{
		self::write(self::DEBUG, $message);
	}


	public static function write($level, $message, $file='/tmp/new_rollcurl.log')
	{
		if( in_array($level, self::$log_levels) )
			file_put_contents($file, date('Y-m-d H:i:s')."\t".$level."\t".$message."\r\n", FILE_APPEND);
	}
}
<?php
/**
 * 通知器
 * 与外部进行通讯
 */
namespace raysoft\RollingCurl;

class Notifier
{
    private static $_daemon_port = 13928;

    /**
     * notify daemon
     */
    public static function startRquest()
    {
        $fp = fsockopen('127.0.0.1', self::$_daemon_port);
        if ($fp) {
            fwrite($fp, 'new');
        } else {
            Log::error('Connect to daemon failed!');
        }
        fclose($fp);
    }

    public static function sendCallback($url, $data, $wait_resp = false)
    {
        if (!$wait_resp) {
            return self::post_no_wait($url, $data);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $resp = curl_exec($ch);
        curl_close($ch);

        if ($resp !== '1') {
            Log::error('callback error:'.$resp);
        }
        return true;
    }

    private static function post_no_wait($url, $data)
    {
        if ($url[0] == '/') {
            $url = 'http://'.$_SERVER["SERVER_NAME"].($_SERVER["SERVER_PORT"] == 80 ? '' : ':'.$_SERVER["SERVER_PORT"]).$url;
        }

        $uri  = parse_url($url);
        $host = $uri['host'];
        $port = (key_exists('port', $uri) ? $uri['port'] : 80);
        $file = $uri['path'].(isset($uri['query']) ? '?'.$uri['query'] : '');
        unset($url, $uri);

        $fp = fsockopen($host, $port, $errno, $errstr, 10);
        if (!$fp) {
            return false;
        }

        $data = http_build_query($data);

        $out = "POST /".ltrim($file, '/')." HTTP/1.1\r\n";
        $out .= "Host: {$host}\r\n";
        $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $out .= "Content-length: ".strlen($data)."\r\n";
        $out .= "Connection: Close\r\n\r\n";
        $out .= $data;
        fwrite($fp, $out);
        fclose($fp);
        return true;
    }
}
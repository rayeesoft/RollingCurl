<?php
/**
 * Rolling Curl Daemon
 *
 * @author jesonray@163.com
 */

// 只能运行在CLI模式
if (substr(php_sapi_name(), 0, 3) !== 'cli') {
    echo("This program can only be run in CLI mode\n");
    exit(0);
}

// 只能运行一个实例
if (isRunning(__FILE__)) {
    echo("This program is already in running\n");
    exit(0);
}

// 配置
$php    = 'php';
$port   = 13928;
$addr   = '127.0.0.1';
$worker = dirname(__FILE__).'/excute.php';

// 检查脚本
if (!is_file($worker)) {
    echo("The worker is not exists: $worker\n");
    exit(0);
}

// 绑定监听端口
if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
    echo("Socket_create() failed :reason:".socket_strerror(socket_last_error())."\n");
    exit(0);
}
if (socket_bind($sock, $addr, $port) === false) {
    echo("Socket_bind() failed :reason:".socket_strerror(socket_last_error($sock))."\n");
    exit(0);
}
if (socket_listen($sock, 5) === false) {
    echo("Socket_bind() failed :reason:".socket_strerror(socket_last_error($sock))."\n");
    exit(0);
}

echo "start deamon, done\n";

do {
    if (($msgsock = socket_accept($sock)) === false) {
        continue;
    }

    $buf = socket_read($msgsock, 8192);
    echo $buf;
    switch ($buf) {
        case 'stop':
            socket_close($sock);
            exit(0);
            break;
        case 'new':
            // 检查是否已经运行
            if (isRunning($worker, 0)) {
                echo "Aready in running...";
                break;
            }

            // 如果没运行，建立子进程
            $pid = pcntl_fork();
            if (!$pid) {
                // 创建孙进程
                if (pcntl_fork()) {
                    socket_close($sock);
                    exit(0);
                } else {
                    // 运行rollcurl脚本
                    echo "Run new worker...\n";
                    exec('nohup '.$php.' '.$worker.' &');
                    socket_close($sock);
                    exit(0);
                }
            } else {
                pcntl_wait($status);
            }

            break;
    }
} while (true);
socket_close($sock);
exit(0);

function isRunning($file, $except = 1)
{
    $output = '';
    exec('ps ax | grep php', $output);
    if ($output && preg_match_all('#'.preg_quote($file).'#i', implode('', $output), $match) && count($match[0]) > $except) {
        return true;
    }

    return false;
}
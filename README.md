Rolling Curl - 快速http请求模块
========================

应用场景:
- 请求量大
- 需控制并发
- 实时性高
- 数据量小

依赖组件:
- Sockets
- PCNTL
- Redis
- Curl

技术方案:
- deamon监视
- redis高速队列
- Socket触发
- Curl并发查询
- http回调

安装
------------

建议通过[composer](http://getcomposer.org/download/)安装.

运行

```
php composer.phar require --dev --prefer-dist raysoft/rollingcurl
```

或将

```
"raysoft/rollingcurl": "*"
```

添加到 `composer.json` 文件中.


用法
-----

1. 修改rollcurld中的相关路径, 然后执行`./rollcurld start`运行deamon;
2. 如果需要添加到服务中设置自动启动, 请将rollcurld复制到/etc/init.d/中, 并修改相关路径;
3. 参考tests/run.php中的使用方法.
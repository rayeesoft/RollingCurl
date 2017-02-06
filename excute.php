<?php
require(__DIR__.'/../../autoload.php');

$executor = new raysoft\RollingCurl\Executor;
$s = microtime(1);
$executor->cliRun();
echo microtime(1) - $s;

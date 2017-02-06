<?php
require(__DIR__.'/../../../autoload.php');

$domain = '127.0.0.1';

$urls = [];
for ($i=0; $i<10; $i++) {
    $urls[] = 'http://'.$domain.'/vendor/raysoft/rollingcurl/tests/delay.php?'.$i;
}

$manager = new raysoft\RollingCurl\Manager;
$manager->debug_clean();

$request = new raysoft\RollingCurl\Request('test');
$request->setUniqueInQueue(true);
$request->setUniqueInRunning(true);
$request->setCallbackUrl('http://'.$domain.'/vendor/raysoft/rollingcurl/tests/response.php');
foreach($urls as $url)
{
	$request = clone $request;
	$request->setKey( md5($url));
	$request->setUrl( $url );

	$manager->add($request);
}
$manager->run();
var_dump($manager->queue());


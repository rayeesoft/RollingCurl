<?php
require(__DIR__ . '../../../vendor/autoload.php');

$urls = array(
	'http://127.0.0.1/delay.php?1',
	'http://127.0.0.1/delay.php?2',
	'http://127.0.0.1/delay.php?3',
	'http://127.0.0.1/delay.php?4',
	'http://127.0.0.1/delay.php?5',
	'http://127.0.0.1/delay.php?6',
	'http://127.0.0.1/delay.php?7',
	'http://127.0.0.1/delay.php?8',
	'http://127.0.0.1/delay.php?9',
	'http://127.0.0.1/delay.php?0'
);

$manager = new raysoft\RollingCurl\Manager;
$manager->debug_clean();

$request = new raysoft\RollingCurl\Request('test');
$request->setUniqueInQueue(true);
$request->setUniqueInRunning(true);
$request->setCallbackUrl('http://callbackurl/response.php');
foreach($urls as $url)
{
	$request = clone $request;
	$request->setKey( md5($url));
	$request->setUrl( $url );

	$manager->add($request);
}
$manager->run();
var_dump($manager->queue());


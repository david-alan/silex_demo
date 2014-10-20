<?php
/*
* I had everything in here, but I moved it to app.php.
* Some tests run better without running $app->run().
*
* I should have created an environment variable to see
* if it's running in a test or live. Probably something
* like this:
*
*	if($env == 'test'){
*		$app['debug'] = true;
*	} else {
*		$app->run();
*	}
*
*/

require_once 'app.php';

$app->run();
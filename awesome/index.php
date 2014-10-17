<?php

require_once 'vendor/autoload.php';

$app = new Silex\Application();

$app['debug'] = true;

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'dbname' => 'twitter_fake_db',
		'user' => 'twitter_user',
		'password' => 'X7Ev3x7YKR87wVkqEYsUhdr8f9xmzj',
		'host' => 'localhost',
		'driver' => 'pdo_mysql',
    ),
));

$app->get('/user/{user_id}', function($user_id) use ($app){
	$sql = "SELECT * FROM user WHERE ID = ?";
	$vars[] = (int)$user_id;
	$user_details = $app['db']->fetchAssoc($sql, $vars);

	return "{$user_details['password']}";

});

$app->get('/hello/{name}', function($name) use ($app) {
    return 'Hello ' . $app->escape($name);
});

$app->get('/', function() use ($app) {
    return 'test';
});

$app->run();
//echo 'end';

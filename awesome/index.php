<?php

require_once 'vendor/autoload.php';

$app = new Silex\Application();

$app['debug'] = true;

$app->get('/hello/{name}', function($name) use ($app) {
    return 'Hello ' . $app->escape($name);
});

$app->get('/', function() use ($app) {
    return 'test';
});

$app->run();
//echo 'end';

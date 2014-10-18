<?php

//use composer's autoloader
$loader = require_once 'vendor/autoload.php';
//$loader->add('models', __DIR__.'/../models/');

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


$app = new Silex\Application();

$app['debug'] = true;

//Use Twig for views
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

//connect with doctrine, but Silex doesn't really implement a full ORM 
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'dbname' => 'twitter_fake_db',
		'user' => 'twitter_user',
		'password' => 'X7Ev3x7YKR87wVkqEYsUhdr8f9xmzj',
		'host' => 'localhost',
		'driver' => 'pdo_mysql',
    ),
));


//login form
$app->get('/login/', function ($error = '') use ($app) {
    return $app['twig']->render('login.twig', array(
    	'error' => $error
    ));
});

//login form submitted
$app->post('/login/', function (Request $request) use ($app){
    $username = $request->get('username');
    $password = $request->get('password');

    $u = new FakeTwitter\User($app);
    $error = $u->validateUser($username, $password);

	if(count($error)){ //there was an error registering the new user
	 	return $app['twig']->render('login.twig', array(
	 		'error' => $error
			));
	}
	
	return  $app['twig']->render('home.twig', array(
			'user' => $u
			));
});

//sign-up form
$app->get('/sign-up/', function () use ($app) {
    return $app['twig']->render('sign-up.twig', array(
    ));
});

//sign-up form submitted
$app->post('/sign-up/', function(Request $request) use ($app){
	$username = $request->get('username');
    $password = $request->get('password');
     
    $u = new FakeTwitter\User($app);
    $error = $u->addUser($username, $password);
    
    if(count($error)){ //there was an error registering the new user
     	return $app['twig']->render('sign-up.twig', array(
     		'error' => $error
    		));
    }

    return  $app['twig']->render('home.twig', array(
    	'user' => $u
    		));
});

$app->get('/login/{name}', function ($name) use ($app) {
    return $app['twig']->render('login.twig', array(
        'name' => $name,
    ));
});


$app->get('/', function() use ($app) {
    return 'test';
});

$app->run();
//echo 'end';

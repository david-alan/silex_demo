<?php
//use composer's autoloader
$loader = require_once 'vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application();

//Use Twig for views
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/views'
));

//Use sessions
$app->register(new Silex\Provider\SessionServiceProvider());

//connect with doctrine, but Silex doesn't really implement a full ORM 
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'dbname'   => 'twitter_fake_db',
        'user'     => 'twitter_user',
        'password' => 'X7Ev3x7YKR87wVkqEYsUhdr8f9xmzj',
        'host'     => 'localhost',
        'driver'   => 'pdo_mysql'
    )
));

//login form
$app->get('/login/', function() use ($app) {
    return $app['twig']->render('login.twig', array());
});

//login form submitted
$app->post('/login/', function(Request $request) use ($app) {
    $username = $request->get('username');
    $password = $request->get('password');
    
    $u     = new FakeTwitter\User($app['db'], $app['session']); //inject $app so we can mock it
    $error = $u->validateUser($username, $password);
    
    if (count($error)) { //there was an error logging in
        return $app['twig']->render('login.twig', array(
            'error' => $error
        ));
    }
    
    return $app->redirect('/home/'); //successful login
});

//sign-up form
$app->get('/sign-up/', function() use ($app) {
    return $app['twig']->render('sign-up.twig', array());
});

//sign-up form submitted
$app->post('/sign-up/', function(Request $request) use ($app) {
    $username = $request->get('username');
    $password = $request->get('password');
    
    $u     = new FakeTwitter\User($app['db'], $app['session']);
    $error = $u->addUser($username, $password);
    
    if (count($error)) { //there was an error registering the new user
        return $app['twig']->render('sign-up.twig', array(
            'error' => $error
        ));
    }
    
    return $app->redirect('/home/'); //user registered and logged in
});

//post message
$app->post('/home/', function(Request $request) use ($app) {
    
    $u        = new FakeTwitter\User($app['db'], $app['session']);
    $user_id  = $u->getUserID();
    $username = $u->getName();
    
    if ($user_id == 0) { //user is not logged in
        return $app['twig']->render('login.twig', array());
    }
    
    $m             = new FakeTwitter\Message($app['db']);
    $error         = $m->addMessage($user_id, $request->get('message'));
    $user_messages = $m->getMessages($user_id);
    $all_messages  = $m->getMessages();
    
    return $app['twig']->render('home.twig', array(
        'user'          => $u,
        'error'         => $error,
        'user_messages' => $user_messages,
        'all_messages'  => $all_messages
    ));
});

//view messages posted for logged in users
$app->get('/home/', function(Request $request) use ($app) {
    
    $u        = new FakeTwitter\User($app['db'], $app['session']);
    $user_id  = $u->getUserID();
    $username = $u->getName();
    
    if ($user_id == 0) { //user is not logged in
        return $app['twig']->render('login.twig', array());
    }
    
    $m             = new FakeTwitter\Message($app['db']);
    $user_messages = $m->getMessages($user_id);
    $all_messages  = $m->getMessages();
    
    return $app['twig']->render('home.twig', array(
        'user'          => $u,
        'user_messages' => $user_messages,
        'all_messages'  => $all_messages
    ));
});

//just redirect the root users to /home/
$app->get('/', function() use ($app) {
    return $app->redirect('/home/');
});

return $app;
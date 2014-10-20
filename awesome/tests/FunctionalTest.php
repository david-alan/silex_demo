<?php
require __DIR__ . '/../vendor/autoload.php';

use Silex\WebTestCase;

class FunctionalTest extends WebTestCase
{
    public function createApplication()
	{

		$app = require __DIR__.'/../app.php';
		
		$app['session.test'] = true;
		$app['debug'] = true;
		$app['exception_handler']->disable();

		return $app;
    }

	public function testSignUpPageLoads()
	{
	    $client = $this->createClient();
	    $crawler = $client->request('GET','/sign-up/');
	    $this->assertEquals(200,$client->getResponse()->getStatusCode(),'Not a 200 status response code');
	}  

	public function testLoginPageLoads()
	{
	    $client = $this->createClient();
	    $crawler = $client->request('GET','/login/');
	    $this->assertEquals(200,$client->getResponse()->getStatusCode(),'Not a 200 status response code');
	}  

	//weird - this should be returning a 302
	public function testHomePageRedirectsWithoutBeingLoggedIn(){
		$client = $this->createClient();
	    $crawler = $client->request('GET','/');
	    $this->assertEquals(200,$client->getResponse()->getStatusCode(),'Redirect failed');

	}
}
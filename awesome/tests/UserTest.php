<?php
namespace FakeTwitterTest;

use Silex\Application;
use \FakeTwitter\User;

require __DIR__ . '/../vendor/autoload.php';

class UserTest extends \PHPUnit_Framework_TestCase
{

    protected $user;

    protected function setUp()
    {
        $this->mockedDbConnection = \Mockery::mock('\Doctrine\DBAL\Connection');
        
        $this->mockedRows = array(
                array('id' => 1,'username' => 'david', 'password' => '$2y$10$uTIqvpPL1txb/RZbupCpr.lgVwtFf6/Ba.C5HfxuEPurD1xi5I9YK'),
                array('id' => 2,'username' => 'fred', 'password' => '$2y$10$uTIqvpPL1txb/RZbupCpr.lgVwtFf6/Ba.C5HfxuEPurD1xi5I9YK'),
                array('id' => 3,'username' => 'john', 'password' => '$2y$10$uTIqvpPL1txb/RZbupCpr.lgVwtFf6/Ba.C5HfxuEPurD1xi5I9YK')
            );

        $user = new \FakeTwitter\User($this->mockedDbConnection);//mock the db connection
        $this->user = \Mockery::mock($user);//mock the user b/c we have nondeterministic function to override
    }

    protected function tearDown() {
        \Mockery::close();
    }

    public function testGetNameFromDB()
    {
        $test_id = 1; //the user's db id
        $test_id--; //arrays start at zero, db starts at one
        $params[]=$test_id;
        $this->mockedDbConnection
            ->shouldReceive('fetchAssoc')
            ->with('SELECT username FROM user WHERE id = ?',$params)
            ->andReturn($this->mockedRows[$test_id]);
        
        $expected_name = 'david';
        $result_name = $this->user->getNameFromDB($test_id);
        $this->assertEquals($result_name,$expected_name);
    }

    public function testNameSetGet()
    {    
        $set_name = 'david';
        $this->user->setName($set_name);
        $get_name = $this->user->getName();

        $this->assertEquals($set_name,$get_name);
    }

    public function testNameSetGetSqlInjection()
    {    
        $set_name = '\'; drop table user;--';
        $this->user->setName($set_name);
        $get_name = $this->user->getName();

        $this->assertEquals($set_name,$get_name);
    }
/*
    //User::userExists() is a private method - derp!
    public function testUserExists(){
        
        $test_id = 1; //the user's db id
        $test_id--; //arrays start at zero, db starts at one

        $username = $this->mockedRows[$test_id]['username'];

        $params[] = $username;
        $this->mockedDbConnection
            ->shouldReceive('fetchAssoc')
            ->with('SELECT id FROM user WHERE username = ?',$params)
            ->andReturn(isset($this->mockedRows[$test_id]));

        $does_user_exist = $this->user->userExists($username);
        $this->assertTrue($does_user_exist);
    }
*/
    public function testValidateUserSuccess()
    {
        $test_id = 1; //the user's db id
        $test_id--; //arrays start at zero, db starts at one

        $username = $this->mockedRows[$test_id]['username'];
        $plain_text_password = 'password';

        $params[] = $username;
        $this->mockedDbConnection
            ->shouldReceive('fetchAssoc')
            ->with('SELECT id, password FROM user WHERE username = ?',$params)
            ->andReturn($this->mockedRows[$test_id]);

        $this->mockedDbConnection
            ->shouldReceive('fetchAssoc')
            ->with('SELECT id FROM user WHERE username = ?',$params)
            ->andReturn(isset($this->mockedRows[$test_id]));
        
        $expected_errors = array();
        $result_errors = $this->user->validateUser($username,$plain_text_password);
        $this->assertEquals($result_errors,$expected_errors);
    }

    public function testValidateUserBadPassword()
    {
        $test_id = 1; //the user's db id
        $test_id--; //arrays start at zero, db starts at one

        $username = $this->mockedRows[$test_id]['username'];
        $plain_text_password = 'this is not the right password';

        $params[] = $username;
        $this->mockedDbConnection
            ->shouldReceive('fetchAssoc')
            ->with('SELECT id, password FROM user WHERE username = ?',$params)
            ->andReturn($this->mockedRows[$test_id]);

        
        $this->mockedDbConnection
            ->shouldReceive('fetchAssoc')
            ->with('SELECT id FROM user WHERE username = ?',$params)
            ->andReturn(isset($this->mockedRows[$test_id]));

        $expected_errors = array('invalid password');
        
        $result_errors = $this->user->validateUser($username,$plain_text_password);
        $this->assertEquals($result_errors,$expected_errors);
    }

    public function testValidateUserFailBadUsername()
    {
        $username = "he_who_will_not_be_staring_in_this_unit_test";
        $plain_text_password = 'password';

        $params[] = $username;
        $this->mockedDbConnection
            ->shouldReceive('fetchAssoc')
            ->with('SELECT id, password FROM user WHERE username = ?',$params)
            ->andReturn(array());

        $this->mockedDbConnection
            ->shouldReceive('fetchAssoc')
            ->with('SELECT id FROM user WHERE username = ?',$params)
            ->andReturn(array());

        $expected_errors = array('User does not exist');
        
        $result_errors = $this->user->validateUser($username,$plain_text_password);
        $this->assertEquals($result_errors,$expected_errors);

    }

    public function testValidateUserFailMissingUsername()
    {
        $username = "";
        $plain_text_password = 'password';

        $params[] = $username;
        $this->mockedDbConnection
            ->shouldReceive('fetchAssoc')
            ->with('SELECT id, password FROM user WHERE username = ?',$params)
            ->andReturn(array());

        $this->mockedDbConnection
            ->shouldReceive('fetchAssoc')
            ->with('SELECT id FROM user WHERE username = ?',$params)
            ->andReturn(array());

        $expected_errors = array('User does not exist', 'Username  and password must be entered');
        
        $result_errors = $this->user->validateUser($username,$plain_text_password);
        $this->assertEquals($result_errors,$expected_errors);
    }

    public function testAddUser()
    {
        $expected_count = count($this->mockedRows) + 1;

        $username = 'Harry';
        $password = 'Potter';

        $params[] = $username;
        $params[] = $password;

        //TODO: intercept call to User::hashPassword()
         $this->user
            ->shouldReceive('hashPassword')
            ->with($password)
            ->andReturn('some_predictable_encryption');

        $this->mockedDbConnection
            ->shouldReceive('executeUpdate')
            //->with('INSERT INTO user(username, password) VALUES (?,?)',$params)
            ->withAnyArgs()
            ->andReturn();
       
        unset($params); $params[] = $username;
        $this->mockedDbConnection
            ->shouldReceive('fetchAssoc')
            ->with('SELECT id FROM user WHERE username = ?',$params)
            ->andReturn(array());

        $this->mockedDbConnection
            ->shouldReceive('lastInsertId')            
            ->andReturn(count($this->mockedRows));

        $errors = $this->user->addUser($username,$password);        
        $this->mockedRows[] = array(count($this->mockedRows),$username,$password);

        $this->assertEquals($errors,array());
        $this->assertEquals($expected_count,count($this->mockedRows));
    }
}
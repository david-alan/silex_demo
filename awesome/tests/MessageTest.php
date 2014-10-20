<?php
namespace FakeTwitterTest;

use Silex\Application;
use \FakeTwitter\Message;

require __DIR__ . '/../vendor/autoload.php';

class MessageTest extends \PHPUnit_Framework_TestCase{

	protected $message;

    protected function setUp()
    {
        $this->mockedDbConnection = \Mockery::mock('\Doctrine\DBAL\Connection');
		
		$this->mockedRows = array(
		        array('id' => 1,'user_id' => 1, 'message' => 'this is message one', 'ts' => '2014-10-19 17:08:10'),
		        array('id' => 2,'user_id' => 1, 'message' => 'this is message two', 'ts' => '2014-10-19 17:08:10'),
		        array('id' => 3,'user_id' => 2, 'message' => 'this is message three', 'ts' => '2014-10-19 17:08:10'),
		        array('id' => 4,'user_id' => 2, 'message' => 'this is message four', 'ts' => '2014-10-19 17:08:10'),
		        array('id' => 5,'user_id' => 1, 'message' => 'this is message five', 'ts' => '2014-10-19 17:08:10')
		    );

		$this->message = new \FakeTwitter\Message($this->mockedDbConnection);//mock the db connection		
    }

	protected function tearDown() {
        \Mockery::close();
    }

    public function testAddMessageSuccess(){
    	$expected_count = count($this->mockedRows) + 1;
    	$expected_errors = array();

    	$user_id = 1;
    	$message = "140characters_afsdfjlasjdflaksdflaksjdflaksjflaksjdflaskfalskdfajsldfkjalsdfkjasl;dfkajs;ldfkjasldfkajsdflkjalsf;a;sdflkjasddaesvzxvvsasfass";
		$params[] = $user_id;
		$params[] = $message;

        $this->mockedDbConnection
            ->shouldReceive('executeUpdate')
            ->with('INSERT INTO message(user_id, message) VALUES (?,?)',$params)
            ->andReturn();
		
		$actual_errors = $this->message->addMessage($user_id, $message);

		$this->mockedRows[] = array(count($this->mockedRows),$user_id,$message,'2014-10-19 17:08:10');

        $this->assertEquals($actual_errors,$expected_errors);
        $this->assertEquals($expected_count,count($this->mockedRows));
    }

    public function testAddMessageFail(){
    	$expected_count = count($this->mockedRows) + 1;
    	$expected_errors = array('Message submitted is 141 chars long.  Limit is 140 long.');

    	$user_id = 1;
    	$message = "141characters_afsdfjlasjdflaksdeflaksjdflaksjflaksjdflaskfalskdfajsldfkjalsdfkjasl;dfkajs;ldfkjasldfkajsdflkjalsf;a;sdflkjasddaesvzxvvsasfass";
		$params[] = $user_id;
		$params[] = $message;

        $this->mockedDbConnection
            ->shouldReceive('executeUpdate')
            ->with('INSERT INTO message(user_id, message) VALUES (?,?)',$params)
            ->andReturn();
		
		$actual_errors = $this->message->addMessage($user_id, $message);

		$this->mockedRows[] = array(count($this->mockedRows),$user_id,$message,'2014-10-19 17:08:10');

        $this->assertEquals($actual_errors,$expected_errors);
        $this->assertEquals($expected_count,count($this->mockedRows));		
    }    

    public function testGetMessagesForAllUsers(){
    	$number_of_rows = count($this->mockedRows);

    	$params = array();
        $this->mockedDbConnection
            ->shouldReceive('fetchAll')             
            ->with("SELECT username, message, DATE_FORMAT(ts, '%M %d, %Y %h:%i%p') as ts FROM message m INNER JOIN user u ON m.user_id = u.id  ORDER BY ts DESC", $params)
            ->andReturn($this->mockedRows);

            $number_of_rows = count($this->message->getMessages());
            $this->assertEquals(count($this->mockedRows),$number_of_rows);
    }

    public function testGetMessagesForSingleUsers(){
    	$user_id = 1;
    	foreach($this->mockedRows as $row){ //get rows for this user from our mockrows
    		if($row['user_id'] == $user_id){
    			$user_rows[] = $row;
    		}
    	}

    	$number_of_rows = count($user_rows);

    	$params = array($user_id);
        $this->mockedDbConnection
            ->shouldReceive('fetchAll')             
            ->with("SELECT username, message, DATE_FORMAT(ts, '%M %d, %Y %h:%i%p') as ts FROM message m INNER JOIN user u ON m.user_id = u.id WHERE user_id = ? ORDER BY ts DESC", $params)
            ->andReturn($user_rows);

            $number_of_rows = count($this->message->getMessages(1));
            $this->assertEquals(count($user_rows),$number_of_rows);
    }
}
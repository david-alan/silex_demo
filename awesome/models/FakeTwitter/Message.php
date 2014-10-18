<?php

namespace FakeTwitter;

class Message{

	public function __construct($app){
		$this->app = $app;
	}

	public function addMessage($user_id,$message){
		
		$errors = array();
		$max_chars = 140;

		if(strlen($message) > $max_chars){
			$errors[] = 'Message submitted is ' . strlen($message) . ' chars long.  Limit is ' . $max_chars . ' long.';
		} 

		if(count($errors) == 0){						
			$sql = "INSERT INTO message(user_id, message) VALUES (?,?)";
			$vars[] = (int)$user_id;
			$vars[] = $message;
			$this->app['db']->executeUpdate($sql, $vars);
		}

		return $errors ;
	}

/*
pass in no params to get all messages; 
pass in user id to get a user's messages
*/
	public function getMessages($user_id = 0){
		
		$where_clause = $user_id ? 'WHERE user_id = ?' : '';
		$vars = $user_id ? array($user_id) : array();

		$sql = "SELECT username, message, DATE_FORMAT(ts, '%M %d, %Y %h:%i%p') as ts FROM message m INNER JOIN user u ON m.user_id = u.id $where_clause ORDER BY ts DESC";
		$result = $this->app['db']->fetchAll($sql, $vars);
		return $result;

	}

}
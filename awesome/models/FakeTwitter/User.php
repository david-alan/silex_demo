<?php

namespace FakeTwitter;

class User{

	public function __construct($app){
		$this->app = $app;
	}

	public function setName($name){
		$this->name = $name;
	}

	public function getName(){
		return $this->name;
	}

	public function validateUser($name,$password){

		$errors = array();

		if(!self::userExists($name)){
			$errors[] = 'User does not exist';			
		} 

		if(strlen($name) === 0 || strlen($password) === 0){
			$errors[] = 'Username  and password must be entered';
		}

		if(count($errors) == 0){
			$sql = "SELECT id, password FROM user WHERE username = ?";
			$vars[] = $name;

			$user_details = $this->app['db']->fetchAssoc($sql, $vars);
			$id = $user_details['id'];
			$hash = $user_details['password'];

			if(password_verify($password, $hash)){
				self::setUserID($id);
				self::setUserName($name);
			} else {
				$errors[] = "invalid password";
			}

		}

		return $errors;
	}

	public function addUser($name,$password){
		//check to see if username exists, if it does, return an array with error as string
		//else add user and return an empty array

		$errors = array();

		if(self::userExists($name)){
			$errors[] = 'User already exists';			
		} 

		if(strlen($name) === 0 || strlen($password) === 0){
			$errors[] = 'Username  and password must be entered';
		}

		//TODO: check password complexity, add error to $errors array

		if(count($errors) == 0){
			
			$hashed_password = password_hash($password, PASSWORD_DEFAULT);
			$sql = "INSERT INTO user(username, password) VALUES (?,?)";
			$vars[] = $name;
			$vars[] = $hashed_password;
			$this->app['db']->executeUpdate($sql, $vars);
			self::setUserID($this->app['db']->lastInsertId());
			self::setUserName($name);
		}

		return $errors ;
	}

	private function setUserID($id){
		$_SESSION['user_id'] = (int)$id;
		$this->id = (int)$id;
	}

	private function userExists($name){
		$sql = "SELECT id FROM user WHERE username = ?";
		$vars[] = $name;
		$user_exists = $this->app['db']->fetchAssoc($sql, $vars);
		
		if($user_exists){
			return true;
		} else {
			return false;
		}
	}

	private function setUserName($name){
		$this->name = $name;
	}

	public function test(){
		echo 'test';
	}
}
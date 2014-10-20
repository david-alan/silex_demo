<?php
namespace FakeTwitter;

/**
* Handle business logic of users.  Users may 
* Sign-up and Login.
*
*/
class User
{
    /**
    * Create a User.  Used dep. injection to
    * make unit testing easier by passing in a
    * mocked DB.
    *
    * @param object $app_db An instance of Doctrine's DBAL
    * @param object $app_session Silex's session instance
    * @return object User
    */
    public function __construct($app_db, $app_session = 0)
    {
        $this->app['db']      = $app_db;
        $this->app['session'] = $app_session;
    }
    
    /**
    * Declare a user's name
    *
    * @param string $name A username
    * @return void
    */
    public function setName($name)
    {
        $this->name = $name;
    }
    
    /**
    * Return a user's name.  If it hasn't
    * already been set, get it from the 
    * database.
    *
    * @return string User's username
    */
    public function getName()
    {
        if (isset($this->name)) {
            return $this->name;
        } else {
            return self::getNameFromDB($this->id);
        }
    }
    
      /**
    * Retrieve a user's username from
    * the database.
    *
    * @param int $id A user's surrogate key from table user
    * @return string User's username
    */
    public function getNameFromDB($id)
    {
        $sql    = "SELECT username FROM user WHERE id = ?";
        $vars[] = $id;
        
        $user_details = $this->app['db']->fetchAssoc($sql, $vars);
        return $user_details['username'];
    }

      /**
    * Check to see if a user's login credentials 
    * are valid
    *
    * @param string $name A user's submitted username
    * @param string $password A user's submitted password
    * @return array Errors the user encountered logging in
    */
    public function validateUser($name, $password)
    {
        $errors = array();
        
        if (!self::userExists($name)) {
            $errors[] = 'User does not exist';
        }
        
        if (strlen($name) === 0 || strlen($password) === 0) {
            $errors[] = 'Username  and password must be entered';
        }
        
        if (count($errors) == 0) {
            $sql    = "SELECT id, password FROM user WHERE username = ?";
            $vars[] = $name;
            
            $user_details = $this->app['db']->fetchAssoc($sql, $vars);
            $id           = $user_details['id'];
            $hash         = $user_details['password'];
            
            if (password_verify($password, $hash)) {
                self::setUserID($id);
                self::setName($name);
            } else {
                $errors[] = "invalid password";
            }
            
        }
        
        return $errors;
    }

      /**
    * When a user registers for the FakeTwitter
    * service, add him to the database. User must enter
    * a username and password, and the username must
    * be unique
    *
    * @param string $name A user's submitted username
    * @param string $password A user's submitted password
    * @return array Errors encountered registering
    */    
    public function addUser($name, $password)
    {
        $errors = array();
        
        if (self::userExists($name)) {
            $errors[] = 'User already exists';
        }
        
        if (strlen($name) === 0 || strlen($password) === 0) {
            $errors[] = 'Username  and password must be entered';
        }
        
        //TODO: check password complexity, add errors to $errors array
        
        if (count($errors) == 0) {
            $hashed_password = self::hashPassword($password);
            $sql             = "INSERT INTO user(username, password) VALUES (?,?)";
            $vars[]          = $name;
            $vars[]          = $hashed_password;
            $this->app['db']->executeUpdate($sql, $vars);
            
            self::setUserID($this->app['db']->lastInsertId());
            self::setName($name);
        }
        
        return $errors;
    }
    
    /**
    * Use PHP's native password hashing functionality
    * (I factored this out at the last minute in an attempt 
    * to make other methods deterministic for unit tests)
    *
    * @param string $password A user's plaintext password
    * @return string User's password hash
    */
    public function hashPassword($password)
    {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        return $hashed_password;
        
    }
    
    /**
    * A helper function for setting the user's id.
    * The session checking is to make unit tests easier.
    *
    * @param int $id A user's database id
    * @return void
    */
    private function setUserID($id)
    {
        if ($this->app['session']) {
            $this->app['session']->set('user_id', (int) $id);
        }
        $this->id = (int) $id;
    }

    /**
    * A helper function to determine if a username
    * is already in use.
    *
    * @param int $id A user's database id
    * @return void
    */
    private function userExists($name)
    {
        $sql         = "SELECT id FROM user WHERE username = ?";
        $vars[]      = $name;
        $user_exists = $this->app['db']->fetchAssoc($sql, $vars);
        
        if ($user_exists) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
    * Return a user's database id.  
    * If it hasn't already been set, 
    * get it from the session.
    *
    * @return int User's id
    */    
    public function getUserID()
    {
        if (isset($this->id)) {
            return (int) $this->id;
        } else {
            $this->id = (int) $this->app['session']->get('user_id');
            return $this->id;
        }
    }
}
<?php
namespace FakeTwitter;

/**
* Handle business logic of messages.  Users may 
* add and view messages.
*
*/
class Message
{

	/**
	* Create a message.  Used dep. injection to
	* make unit testing easier by passing in a
	* mocked DB.
	*
	* @param object $app_db An instance of Doctrine's DBAL
	* @return object Message
	*/
    public function __construct($app_db)
    {
        $this->app['db'] = $app_db;
    }
    
    /**
    * Add a message to the database limited
    * to $max_chars in length
    *
    * @param int $user_id The DB id of a user
    * @param string $message The user's posted message
    * @return void
    */
    public function addMessage($user_id, $message)
    {
        $errors    = array();
        $max_chars = 140;
        
        //check for errors
        if (strlen($message) > $max_chars) {
            $errors[] = 'Message submitted is ' . strlen($message) . ' chars long.'
                         . '  Limit is ' . $max_chars . ' long.';
        }
        
        if (count($errors) == 0) {
            $sql    = "INSERT INTO message(user_id, message) VALUES (?,?)";
            $vars[] = (int) $user_id;
            $vars[] = $message;
            $this->app['db']->executeUpdate($sql, $vars);
        }
        return $errors;
    }
    
    /**
    * Retrieve messages for a specific user
    * or all messages in the database.
    * Messages are ordered by most recent
    * entry date.
    *
    * @param int $user_id If zero, all messages; otherwise, a user's messages
    * @return array $result All messages selected
    */
    public function getMessages($user_id = 0)
    {
        $where_clause = $user_id ? 'WHERE user_id = ?' : '';
        $vars         = $user_id ? array($user_id) : array();
        
        $sql    = "SELECT username, message, DATE_FORMAT(ts, '%M %d, %Y %h:%i%p') as ts " .
                  "FROM message m INNER JOIN user u ON m.user_id = u.id $where_clause ORDER BY ts DESC";
        $result = $this->app['db']->fetchAll($sql, $vars);
        return $result;        
    }
}
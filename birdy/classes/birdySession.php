<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');

/**
 * Session class
 *
 * handles the session stuff. creates session when no one exists, sets and
 * gets values, and closes the session properly (=logout). Those methods
 * are STATIC, which means you can call them with Session::get(XXX);
 */
class birdySession
{
    /**
     * starts the session
     */
    public static function init()
    {
        // if no session exist, start the session
        if (session_id() == '') {
            session_start();
        }
    }

    /**
     * sets a specific value to a specific key of the session
     * @param mixed $key
     * @param mixed $value
     */
    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * gets/returns the value of a specific key of the session
     * @param mixed $key Usually a string, right ?
     * @return mixed
     */
    public static function get($key)
    {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }
    }

    /**
     * deletes the session (= logs the user out)
     */
    public static function destroy()
    {
        if (session_id() != '') {
			session_destroy();
		}
    }
    
    public static function writeCookie($user_id) {
                $db = birdyDB::getInstance();
                // generate 64 char random string
                $random_token_string = hash('sha256', mt_rand());

                // write that token into database
                $sql = "UPDATE birdy_users SET rememberme_token = :user_rememberme_token WHERE id = :user_id";
                $sth = $db->prepare($sql);
                $sth->execute(array(':user_rememberme_token' => $random_token_string, ':user_id' => $user_id));

                // generate cookie string that consists of user id, random string and combined hash of both
                $cookie_string_first_part = $user_id . ':' . $random_token_string;
                $cookie_string_hash = hash('sha256', $cookie_string_first_part);
                $cookie_string = $cookie_string_first_part . ':' . $cookie_string_hash;

                // set cookie
                setcookie('rememberme', $cookie_string, time() + COOKIE_RUNTIME, "/", COOKIE_DOMAIN);
    }
    
    public static function writeSocialCookie($user_id) {
                $db = birdyDB::getInstance();
                // generate 64 char random string
                $random_token_string = hash('sha256', mt_rand());

                // write that token into database
                $sql = "UPDATE birdy_social_users SET rememberme_token = :user_rememberme_token WHERE user_id = :user_id";
                $sth = $db->prepare($sql);
                $sth->execute(array(':user_rememberme_token' => $random_token_string, ':user_id' => $user_id));

                // generate cookie string that consists of user id, random string and combined hash of both
                $cookie_string_first_part = $user_id . ':' . $random_token_string;
                $cookie_string_hash = hash('sha256', $cookie_string_first_part);
                $cookie_string = $cookie_string_first_part . ':' . $cookie_string_hash;

                // set cookie
                setcookie('rememberme_social', $cookie_string, time() + COOKIE_RUNTIME, "/", COOKIE_DOMAIN);
    }
    
    public function checkCookie($cookie) {
         $db = birdyDB::getInstance();
        // do we have a cookie var ?
        if (!$cookie) {
            return false;
        }

        // check cookie's contents, check if cookie contents belong together
        list ($user_id, $token, $hash) = explode(':', $cookie);
        if ($hash !== hash('sha256', $user_id . ':' . $token)) {
            return false;
        }

        // do not log in when token is empty
        if (empty($token)) {
            return false;
        }

        // get real token from database (and all other data)
        $query = $db->prepare("SELECT *
                                     FROM birdy_users
                                     WHERE id = :user_id
                                       AND rememberme_token = :user_rememberme_token
                                       AND rememberme_token IS NOT NULL");
        $query->execute(array(':user_id' => $user_id, ':user_rememberme_token' => $token));
        
        if ($query->rowCount()>0) {
            // fetch one row (we only have one result)
            $result = $query->fetch();
            return $result;
        } 
        else return false;
    }
    
    public function checkSocialCookie($cookie) {
        $db = birdyDB::getInstance();
        // do we have a cookie var ?
        if (!$cookie) {
            return false;
        }

        // check cookie's contents, check if cookie contents belong together
        list ($user_id, $token, $hash) = explode(':', $cookie);
        if ($hash !== hash('sha256', $user_id . ':' . $token)) {
            return false;
        }

        // do not log in when token is empty
        if (empty($token)) {
            return false;
        }

        // get real token from database (and all other data)
        $query = $db->prepare("SELECT *
                                     FROM birdy_social_users
                                     WHERE user_id = :user_id
                                       AND rememberme_token = :user_rememberme_token
                                       AND rememberme_token IS NOT NULL");
        $query->execute(array(':user_id' => $user_id, ':user_rememberme_token' => $token));
        
        if ($query->rowCount()>0) return $user_id;
        else return 0;
    }
    
}

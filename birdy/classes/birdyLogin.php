<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
require_once(BIRDY_BASE.DS.'birdy'.DS.'helpers'.DS.'password.php');

/**
 * Login Controller
 * Controls the login processes
 */

class birdyLogin
{
	public static function loginOrRegister(/*login vars*/$login_username, $login_password, $rememberme=false, /*register vars*/$user_email, $password, $password_repeat=false, $user_name=false, $captcha=false) 
	{
		$birdy = birdyCMS::getInstance();
		/*login vars*/
		$login_username = isset($_POST[$login_username]) ? $_POST[$login_username] : false;
		$login_password = isset($_POST[$login_password]) ? $_POST[$login_password] : false;
		if ($rememberme) $rememberme = isset($_POST[$rememberme]) ? $_POST[$rememberme] : false;
		/*register vars*/
		$user_email = isset($_POST[$user_email]) ? $_POST[$user_email] : false;
		$password = isset($_POST[$password]) ? $_POST[$password] : false;
		if ($password_repeat) $password_repeat = isset($_POST[$password_repeat]) ? $_POST[$password_repeat] : false;
		if ($user_name) $user_name = isset($_POST[$user_name]) ? $_POST[$user_name] : false;
		if ($captcha) $captcha = isset($_POST[$captcha]) ? $_POST[$captcha] : false;
		
		//if (!empty($_POST)) {echo "<pre>";print_r($_POST);echo "</pre>";}
		if ($login_username!==false) {
				// we do negative-first checks here
				if (empty($login_username)) {
					$birdy->outputAlert("Please enter your email address!");
				}
				elseif (!isset($login_password) OR empty($login_password)) {
					$birdy->outputAlert("Please enter your password!");
				}
				else {
					if (birdyLogin::login($login_username,$login_password,$rememberme)) {
						$birdy = birdyCMS::getInstance();
						$page = ($_SERVER['HTTP_REFERER']=='http://www.klipsam.com/login') ? birdyConfig::$login_redirection : $_SERVER['HTTP_REFERER'];
						$birdy->loadPage($page);
					}
				}
		}
		//============================================================================
		// then check if we need to signup...
		elseif ($user_email!==false) {
				// we do negative-first checks here
				if (empty($user_email)) {
					$birdy->outputAlert("Please enter your email address!");
				}
				elseif (!isset($password) OR empty($password)) {
					$birdy->outputAlert("Please enter a password!");
				}
				else {
					birdyLogin::register($user_email, $password, $password_repeat, $user_name, $captcha);
				}
		}
	}

    /**
     * Login process (for DEFAULT user accounts).
     * Users who login with Facebook etc. are handled with loginWithFacebook()
     * @return bool success state
     */
    public static function login($username, $password, $rememberme=false)
    {
		$birdy = birdyCMS::getInstance();
		$db = birdyDB::getInstance();
        // get user's data
        // (we check if the password fits the password_hash via password_verify() some lines below)
        $sth = $db->prepare("SELECT *
                                   FROM   birdy_users
                                   WHERE  (username = :user_name OR email = :user_name)");
        $sth->execute(array(':user_name' => $username));
        $count =  $sth->rowCount();
        // if there's NOT one result
        if ($count != 1) {
            // was FEEDBACK_USER_DOES_NOT_EXIST before, but has changed to FEEDBACK_LOGIN_FAILED
            // to prevent potential attackers showing if the user exists
            $birdy->outputAlert(FEEDBACK_LOGIN_FAILED);
            return false;
        }

        // fetch one row (we only have one result)
        $result = $sth->fetch();

        // block login attempt if somebody has already failed 3 times and the last login attempt is less than 30sec ago
        if (($result->failed_logins >= 3) AND ($result->last_failed_login > (time()-30))) {
            $birdy->outputAlert(FEEDBACK_PASSWORD_WRONG_3_TIMES);
            return false;
        }

        // check if hash of provided password matches the hash in the database
        if (password_verify($password, $result->password_hash)) {
			return self::loginAction($result);
        } else {
            // increment the failed login counter for that user
            $sql = "UPDATE birdy_users
                    SET failed_logins = failed_logins+1, last_failed_login = :last_failed_login
                    WHERE username = :user_name OR email = :user_name";
            $sth = $db->prepare($sql);
            $sth->execute(array(':user_name' => $username, ':last_failed_login' => time() ));
            // feedback message
            $birdy->outputAlert(FEEDBACK_PASSWORD_WRONG);
            return false;
        }

        // default return
        return false;
    }
    
    /**
    * Do the actual login
    * This function should be endpoint of all user related creation/login methods
    *
    * @param object $result the user object
    * @returns boolean succes status
    */
    public function loginAction($result) {
 		$birdy = birdyCMS::getInstance();
		$db = birdyDB::getInstance();

           if ($result->active != 1) {
                $birdy->outputAlert(FEEDBACK_ACCOUNT_NOT_ACTIVATED_YET);
                return false;
            }

            if ($result->suspended == 1) {
                $birdy->outputAlert("Your account has been suspended because of dubious activity in Klipsam! If you feel this is unjust, contact us to resolve any issues.");
                return false;
            }

            // login process, write the user data into session
            birdySession::init();
            self::loginSuccess($result);
        
            // reset the failed login counter for that user (if necessary)
            if ($result->last_failed_login > 0) {
                $sql = "UPDATE birdy_users SET failed_logins = 0, last_failed_login = NULL
                        WHERE id = :user_id AND failed_logins != 0";
                $sth = $db->prepare($sql);
                $sth->execute(array(':user_id' => $result->id));
            }

            // generate integer-timestamp for saving of last-login date
            $last_login_timestamp = time();
            // write timestamp of this login into database (we only write "real" logins via login form into the
            // database, not the session-login on every page request
            $sql = "UPDATE birdy_users SET last_login_timestamp = :last_login_timestamp WHERE id = :user_id";
            $sth = $db->prepare($sql);
            $sth->execute(array(':user_id' => $result->id, ':last_login_timestamp' => $last_login_timestamp));

            // if user has checked the "remember me" checkbox, then write cookie
            if ($rememberme) {
				birdySession::writeCookie($result->id);
            }

            // return true to make clear the login was successful
            return true;
    }
    
    // createUserInstance because this is also called from inside birdyUser when a new user is created.
    // in that case, createUserInstance must be false!
    public static function loginSuccess($result, $createUserInstance=true) 
    {
            birdySession::set('user_logged_in', true);
            birdySession::set('user_id', $result->id);
        if ($createUserInstance) $user = new birdyUser($result->id);
        return $result->id;
    }

    /**
     * Log out process, deletes cookie, deletes session
     */
    public static function logout()
    {
        $birdy = birdyCMS::getInstance();
        $db = birdyDB::getInstance();
        $user = birdyUser::getInstance();
        
        // set the remember-me-cookie to ten years ago (3600sec * 365 days * 10).
        // that's obviously the best practice to kill a cookie via php
        // @see http://stackoverflow.com/a/686166/1114320
        setcookie('rememberme', false, time() - (3600 * 3650), '/', COOKIE_DOMAIN);
        setcookie('rememberme_social', false, time() - (3600 * 3650), '/', COOKIE_DOMAIN);
        
        // delete the session
        birdySession::init();
        birdySession::set('user_logged_in', false);
        birdySession::set('user_id', 0);
		unset($_COOKIE['rememberme']);
		unset($_COOKIE['rememberme_social']);
		
		// remove any authorized social connections
		$hybridconfig = BIRDY_BASE.DS.'birdy'.DS.'helpers'.DS.'hybridauth'.DS.'config.php';
		require_once( BIRDY_BASE.DS.'birdy'.DS.'helpers'.DS.'hybridauth'.DS.'Hybrid'.DS.'Auth.php' );
		$hybridauth = new Hybrid_Auth( $hybridconfig );
		$hybridauth->logoutAllProviders();
		
		// remove persistent connections from the database
		$set = 'session_token = ""';
		$where = 'user_id=:user_id';
		$args = array(":user_id"=>$user->id);
		$db->update("birdy_social_users",$set,$where,$args);
		
		// destroy all user instances
        $user->destroy();
		
		// redirect to the home page
        $birdy->outputNotice("You have signed out");
        $birdy->loadPage(birdyConfig::$logout_redirection);
    }

    /**
     * performs the login via cookie (for DEFAULT user account, FACEBOOK-accounts are handled differently)
     * @return bool success state
     */
    public static function loginWithCookie()
    {
		$birdy = birdyCMS::getInstance();
		$db = birdyDB::getInstance();
		
        $cookie = isset($_COOKIE['rememberme']) ? $_COOKIE['rememberme'] : '';

        $result =  birdySession::checkCookie($cookie);
        if ($result) {
			$user_id = $result->id;
			
            // write data into session
            if (self::loginAction($result)) {
				// generate integer-timestamp for saving of last-login date
				$last_login_timestamp = time();
				// write timestamp of this login into database (we only write "real" logins via login form into the
				// database, not the session-login on every page request
				$sql = "UPDATE birdy_users SET last_login_timestamp = :last_login_timestamp WHERE id = :user_id";
				$sth = $db->prepare($sql);
				$sth->execute(array(':user_id' => $user_id, ':last_login_timestamp' => $last_login_timestamp));

				birdySession::writeCookie($user_id);
				// NOTE: Instead we could have not set another rememberme-cookie here, and the current cookie would always
				// be invalid after a certain amount of time, so the user has to login with username/password
				// again from time to time. This is good and safe ! ;)
				$used_name = (birdyConfig::$use_real_name && (!empty($result->first_name)||!empty($result->last_name))) ? $result->first_name.' '.$result->last_name : $result->username;
				$birdy->outputNotice(str_replace("%user",$used_name,FEEDBACK_COOKIE_LOGIN_SUCCESSFUL));
				return true;
			}
			$birdy->outputAlert("We could not log you in automatically. Please sign-in manually");
			$birdy->loadPage(birdyConfig::$login_page);
			return false;
			
        } else {
            $birdy->outputAlert(FEEDBACK_COOKIE_INVALID);
            return false;
        }
    }

    public static function register($user_email, $password, $password_repeat=false, $user_name=false, $captcha=false) 
    {
		$birdy = birdyCMS::getInstance();
        // perform all necessary form checks
        if ($captcha && !self::checkCaptcha()) {
            $birdy->outputAlert(FEEDBACK_CAPTCHA_WRONG);
            return false;
        } elseif ($user_name==='') {
            $birdy->outputAlert(FEEDBACK_USERNAME_FIELD_EMPTY);
            return false;
        } elseif ($password==='' OR $password_repeat==='') {
            $birdy->outputAlert(FEEDBACK_PASSWORD_FIELD_EMPTY);
            return false;
        } elseif (!empty($password) && $password_repeat!==false && $password!==$password_repeat) {
            $birdy->outputAlert(FEEDBACK_PASSWORD_REPEAT_WRONG);
            return false;
        } elseif (strlen($password) < 6) {
            $birdy->outputAlert(FEEDBACK_PASSWORD_TOO_SHORT);
            return false;
        } elseif ($user_name!==false && (strlen($user_name) > 64 OR strlen($user_name) < 2)) {
            $birdy->outputAlert(FEEDBACK_USERNAME_TOO_SHORT_OR_TOO_LONG);
            return false;
        } elseif ($user_name!==false && !preg_match('/^[a-z0-9-\_\.\d]{2,64}$/i', $user_name)) {
            $birdy->outputAlert(FEEDBACK_USERNAME_DOES_NOT_FIT_PATTERN);
            return false;
        } elseif (empty($user_email)) {
            $birdy->outputAlert(FEEDBACK_EMAIL_FIELD_EMPTY);
            return false;
        } elseif (strlen($user_email) > 64) {
            $birdy->outputAlert(FEEDBACK_EMAIL_TOO_LONG);
            return false;
        } elseif (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
            $birdy->outputAlert(FEEDBACK_EMAIL_DOES_NOT_FIT_PATTERN);
            return false;
        }
        
            // clean the input
            $user_email = strip_tags($user_email);
            $user_name = $user_name===false ? str_replace("@","_",$user_email) : strip_tags($user_name);
            $password = strip_tags($password);
            return self::registerNewUser($user_name, $user_email, $password);
    }

    /**
     * handles the entire registration process for DEFAULT users (not for people who register with
     * 3rd party services, like facebook) and creates a new user in the database if everything is fine
     * Make sure you do the required tests in your registration model before registering!
     * @return boolean Gives back the success status of the registration
     */
    private static function registerNewUser($user_name, $user_email, $password)
    {
		$birdy = birdyCMS::getInstance();
		$db = birdyDB::getInstance();
		
		$user_name = strtolower($user_name);
            // crypt the user's password with the PHP 5.5's password_hash() function, results in a 60 character
            // hash string. the PASSWORD_DEFAULT constant is defined by the PHP 5.5, or if you are using PHP 5.3/5.4,
            // by the password hashing compatibility library. the third parameter looks a little bit shitty, but that's
            // how those PHP 5.5 functions want the parameter: as an array with, currently only used with 'cost' => XX
            $hash_cost_factor = (defined('HASH_COST_FACTOR') ? HASH_COST_FACTOR : null);
            $user_password_hash = password_hash($password, PASSWORD_DEFAULT, array('cost' => $hash_cost_factor));

            // check if username already exists
            $query = $db->prepare("SELECT * FROM birdy_users WHERE username = :user_name");
            $query->execute(array(':user_name' => $user_name));
            $count =  $query->rowCount();
            if ($count == 1) {
                $birdy->outputAlert(FEEDBACK_USERNAME_ALREADY_TAKEN);
                return false;
            }

            // check if email already exists
            $query = $db->prepare("SELECT id FROM birdy_users WHERE email = :user_email");
            $query->execute(array(':user_email' => $user_email));
            $count =  $query->rowCount();
            if ($count == 1) {
                $birdy->outputAlert(FEEDBACK_USER_EMAIL_ALREADY_TAKEN);
                return false;
            }

            // generate random hash for email verification (40 char string)
            $user_activation_hash = sha1(uniqid(mt_rand(), true));
            // generate integer-timestamp for saving of account-creating date
            $user_creation_timestamp = time();

            // write new users data into database
            $sql = "INSERT INTO birdy_users (username, password_hash, email, creation_timestamp, activation_hash)
                    VALUES (:user_name, :user_password_hash, :user_email, :user_creation_timestamp, :user_activation_hash)";
            $query = $db->prepare($sql);
            $query->execute(array(':user_name' => $user_name,
                                  ':user_password_hash' => $user_password_hash,
                                  ':user_email' => $user_email,
                                  ':user_creation_timestamp' => $user_creation_timestamp,
                                  ':user_activation_hash' => $user_activation_hash));
            $count =  $query->rowCount();
            if ($count != 1) {
                $birdy->outputAlert(FEEDBACK_ACCOUNT_CREATION_FAILED);
                return false;
            }

            // get user_id of the user that has been created, to keep things clean we DON'T use lastInsertId() here
            $query = $db->prepare("SELECT id FROM birdy_users WHERE username = :user_name");
            $query->execute(array(':user_name' => $user_name));
            if ($query->rowCount() != 1) {
                $birdy->outputAlert(FEEDBACK_UNKNOWN_ERROR);
                return false;
            }
            $result_user_row = $query->fetch();
            $user_id = $result_user_row->id;

            // send verification email, if verification email sending failed: instantly delete the user
            if (self::sendVerificationEmail($user_id, $user_email, $user_activation_hash)) {
                $birdy->outputNotice(FEEDBACK_ACCOUNT_SUCCESSFULLY_CREATED);
                return true;
            } else {
                $query = $db->prepare("DELETE FROM birdy_users WHERE id = :last_inserted_id");
                $query->execute(array(':last_inserted_id' => $user_id));
                $birdy->outputAlert(FEEDBACK_VERIFICATION_MAIL_SENDING_FAILED);
                return false;
            }

        // default return, returns only true of really successful (see above)
        $birdy->outputAlert(FEEDBACK_UNKNOWN_ERROR);
        return false;
    }

    /**
     * sends an email to the provided email address
     * @param int $user_id user's id
     * @param string $user_email user's email
     * @param string $user_activation_hash user's mail verification hash string
     * @return boolean gives back true if mail has been sent, gives back false if no mail could been sent
     */
    private static function sendVerificationEmail($user_id, $user_email, $user_activation_hash)
    {
		$birdy = birdyCMS::getInstance();
        // create PHPMailer object (this is easily possible as we auto-load the according class(es) via composer)
        $mail = new birdyMailer;

        // fill mail with data
        $mail->From = EMAIL_VERIFICATION_FROM_EMAIL;
        $mail->FromName = EMAIL_VERIFICATION_FROM_NAME;
        $mail->AddAddress($user_email);
        $mail->Subject = EMAIL_VERIFICATION_SUBJECT;
        $mail->Body = EMAIL_VERIFICATION_CONTENT . EMAIL_VERIFICATION_URL . '/' . urlencode($user_id) . '/' . urlencode($user_activation_hash);

        // final sending and check
        if($mail->Send()) {
            $birdy->outputNotice(FEEDBACK_VERIFICATION_MAIL_SENDING_SUCCESSFUL);
            return true;
        } else {
            $birdy->outputAlert(FEEDBACK_VERIFICATION_MAIL_SENDING_ERROR . $mail->ErrorInfo);
            return false;
        }
    }

	/**
	* verifies something that needs verification (account, password)
	* @param string $verify something to verify, most cases $_REQUEST['verify']
	* @return bool success status
	*/
    public static function verifySomething($verify=false) 
    {
		if (!empty($verify)) {
			switch ($verify) {
				case 'passwordreset':
					$uri = explode("/",substr(str_replace('/'.str_replace(URL,'',EMAIL_PASSWORD_RESET_URL),'',BIRDY_SEF_URI),1));
					if (birdyLogin::verifyPasswordReset($uri[0], $uri[1])) return array('resetPassword',$uri[0],$uri[1]);
					break;
				case 'accountactivation':
					$uri = explode("/",substr(str_replace('/'.str_replace(URL,'',EMAIL_VERIFICATION_URL),'',BIRDY_SEF_URI),1));
					if (isset($uri[0]) && isset($uri[1])) birdyLogin::verifyNewUser($uri[0], $uri[1]);
					break;
			}
		}
		return array(false);
    }
    
    /**
     * checks the email/verification code combination and set the user's activation status to true in the database
     * @param int $user_id user id
     * @param string $user_activation_verification_code verification token
     * @return bool success status
     */
    public function verifyNewUser($user_id, $user_activation_verification_code)
    {
		$birdy = birdyCMS::getInstance();
		$db = birdyDB::getInstance();
		
        $sth = $db->prepare("UPDATE birdy_users
                                   SET active = 1, activation_hash = NULL
                                   WHERE id = :user_id AND activation_hash = :user_activation_hash");
        $sth->execute(array(':user_id' => $user_id, ':user_activation_hash' => $user_activation_verification_code));

        if ($sth->rowCount() == 1) {
            $birdy->outputNotice(FEEDBACK_ACCOUNT_ACTIVATION_SUCCESSFUL);
            return true;
        } else {
            $birdy->outputAlert(FEEDBACK_ACCOUNT_ACTIVATION_FAILED);
            return false;
        }
    }

    /**
     * Perform the necessary actions to send a password reset mail
     * @return bool success status
     */
    public static function requestPasswordReset($user_input)
    {
		$birdy = birdyCMS::getInstance();
		$db = birdyDB::getInstance();
        if (empty($_POST[$user_input])) {
            $birdy->outputAlert(FEEDBACK_USERNAME_FIELD_EMPTY);
            return false;
        }

        // generate integer-timestamp (to see when exactly the user (or an attacker) requested the password reset mail)
        $temporary_timestamp = time();
        // generate random hash for email password reset verification (40 char string)
        $user_password_reset_hash = sha1(uniqid(mt_rand(), true));
        // clean user input
        $user_name = strip_tags($_POST[$user_input]);

        // check if that username exists
        $query = $db->prepare("SELECT id, username, email FROM birdy_users
                                     WHERE username = :user_name OR email = :user_name");
        $query->execute(array(':user_name' => $user_name));
        $count = $query->rowCount();
        if ($count != 1) {
            $birdy->outputAlert(FEEDBACK_USER_DOES_NOT_EXIST);
            return false;
        }

        // get result
        $result_user_row = $result = $query->fetch();
        $user_name = $result_user_row->username;
        $user_email = $result_user_row->email;

        // set token (= a random hash string and a timestamp) into database
        if (self::setPasswordResetDatabaseToken($user_name, $user_password_reset_hash, $temporary_timestamp) == true) {
            // send a mail to the user, containing a link with username and token hash string
            if (self::sendPasswordResetMail($user_name, $user_password_reset_hash, $user_email)) {
                return true;
            }
        }
        // default return
        return false;
    }

    /**
     * Set password reset token in database (for DEFAULT user accounts)
     * @param string $user_name username
     * @param string $user_password_reset_hash password reset hash
     * @param int $temporary_timestamp timestamp
     * @return bool success status
     */
    private static function setPasswordResetDatabaseToken($user_name, $user_password_reset_hash, $temporary_timestamp)
    {
		$birdy = birdyCMS::getInstance();
        $db = birdyDB::getInstance();
        $query_two = $db->prepare("UPDATE birdy_users
                                            SET password_reset_hash = :password_reset_hash,
                                                password_reset_timestamp = :password_reset_timestamp
                                          WHERE username = :user_name");
        $query_two->execute(array(':password_reset_hash' => $user_password_reset_hash,
                                  ':password_reset_timestamp' => $temporary_timestamp,
                                  ':user_name' => $user_name));

        // check if exactly one row was successfully changed
        $count =  $query_two->rowCount();
        if ($count == 1) {
            return true;
        } else {
            $birdy->outputAlert(FEEDBACK_PASSWORD_RESET_TOKEN_FAIL);
            return false;
        }
    }

    /**
     * send the password reset mail
     * @param string $user_name username
     * @param string $user_password_reset_hash password reset hash
     * @param string $user_email user email
     * @return bool success status
     */
    private static function sendPasswordResetMail($user_name, $user_password_reset_hash, $user_email)
    {
		$birdy = birdyCMS::getInstance();
        // create PHPMailer object here. This is easily possible as we auto-load the according class(es) via composer
        $mail = new birdyMailer;

        // please look into the config/config.php for much more info on how to use this!
        if (EMAIL_USE_SMTP) {
            // Set mailer to use SMTP
            $mail->IsSMTP();
            //useful for debugging, shows full SMTP errors, config this in config/config.php
            $mail->SMTPDebug = PHPMAILER_DEBUG_MODE;
            // Enable SMTP authentication
            $mail->SMTPAuth = EMAIL_SMTP_AUTH;
            // Enable encryption, usually SSL/TLS
            if (defined(EMAIL_SMTP_ENCRYPTION)) {
                $mail->SMTPSecure = EMAIL_SMTP_ENCRYPTION;
            }
            // Specify host server
            $mail->Host = EMAIL_SMTP_HOST;
            $mail->Username = EMAIL_SMTP_USERNAME;
            $mail->Password = EMAIL_SMTP_PASSWORD;
            $mail->Port = EMAIL_SMTP_PORT;
        } else {
            $mail->IsMail();
        }

        // build the email
        $mail->From = EMAIL_PASSWORD_RESET_FROM_EMAIL;
        $mail->FromName = EMAIL_PASSWORD_RESET_FROM_NAME;
        $mail->AddAddress($user_email);
        $mail->Subject = EMAIL_PASSWORD_RESET_SUBJECT;
        $link = EMAIL_PASSWORD_RESET_URL . '/' . urlencode($user_name) . '/' . urlencode($user_password_reset_hash);
        $mail->Body = EMAIL_PASSWORD_RESET_CONTENT . ' ' . $link;

        // send the mail
        if($mail->Send()) {
            $birdy->outputNotice(FEEDBACK_PASSWORD_RESET_MAIL_SENDING_SUCCESSFUL);
            return true;
        } else {
            $birdy->outputAlert(FEEDBACK_PASSWORD_RESET_MAIL_SENDING_ERROR . $mail->ErrorInfo);
            return false;
        }
    }

    /**
     * Verifies the password reset request via the verification hash token (that's only valid for one hour)
     * @param string $user_name Username
     * @param string $verification_code Hash token
     * @return bool Success status
     */
    public function verifyPasswordReset($user_name, $verification_code)
    {
		$birdy = birdyCMS::getInstance();
		$db = birdyDB::getInstance();
        // check if user-provided username + verification code combination exists
        $query = $db->prepare("SELECT id, password_reset_timestamp
                                       FROM birdy_users
                                      WHERE username = :user_name
                                        AND password_reset_hash = :user_password_reset_hash");
        $query->execute(array(':user_password_reset_hash' => $verification_code,
                              ':user_name' => $user_name,));

        // if this user with exactly this verification hash code exists
        if ($query->rowCount() != 1) {
            $birdy->outputAlert(FEEDBACK_PASSWORD_RESET_COMBINATION_DOES_NOT_EXIST);
            return false;
        }

        // get result row (as an object)
        $result_user_row = $query->fetch();
        // 3600 seconds are 1 hour
        $timestamp_one_hour_ago = time() - 3600;
        // if password reset request was sent within the last hour (this timeout is for security reasons)
        if ($result_user_row->password_reset_timestamp > $timestamp_one_hour_ago) {
            // verification was successful
            $birdy->outputNotice(FEEDBACK_PASSWORD_RESET_LINK_VALID);
            return true;
        } else {
            $birdy->outputAlert(FEEDBACK_PASSWORD_RESET_LINK_EXPIRED);
            return false;
        }
    }

    /**
     * Set the new password (for DEFAULT user, FACEBOOK-users don't have a password)
     * Please note: At this point the user has already pre-verified via verifyPasswordReset() (within one hour),
     * so we don't need to check again for the 60min-limit here. In this method we authenticate
     * via username & password-reset-hash from (hidden) form fields.
     * @return bool success state of the password reset
     */
    public function setNewPassword($user_name, $user_password_reset_hash, $user_password_new, $user_password_repeat)
    {
		$birdy = birdyCMS::getInstance();
		$db = birdyDB::getInstance();
        // basic checks
        if (!isset($_POST[$user_name]) OR empty($_POST[$user_name])) {
            $birdy->outputAlert(FEEDBACK_USERNAME_FIELD_EMPTY);
            return false;
        }
        if (!isset($_POST[$user_password_reset_hash]) OR empty($_POST[$user_password_reset_hash])) {
            $birdy->outputAlert(FEEDBACK_PASSWORD_RESET_TOKEN_MISSING);
            return false;
        }
        if (!isset($_POST[$user_password_new]) OR empty($_POST[$user_password_new])) {
            $birdy->outputAlert(FEEDBACK_PASSWORD_FIELD_EMPTY);
            return false;
        }
        if (!isset($_POST[$user_password_repeat]) OR empty($_POST[$user_password_repeat])) {
            $birdy->outputAlert(FEEDBACK_PASSWORD_FIELD_EMPTY);
            return false;
        }
        // password does not match password repeat
        if ($_POST[$user_password_new] !== $_POST[$user_password_repeat]) {
            $birdy->outputAlert(FEEDBACK_PASSWORD_REPEAT_WRONG);
            return false;
        }
        // password too short
        if (strlen($_POST[$user_password_new]) < 6) {
            $birdy->outputAlert(FEEDBACK_PASSWORD_TOO_SHORT);
            return false;
        }

        // check if we have a constant HASH_COST_FACTOR defined
        // if so: put the value into $hash_cost_factor, if not, make $hash_cost_factor = null
        $hash_cost_factor = (defined('HASH_COST_FACTOR') ? HASH_COST_FACTOR : null);

        // crypt the user's password with the PHP 5.5's password_hash() function, results in a 60 character hash string
        // the PASSWORD_DEFAULT constant is defined by the PHP 5.5, or if you are using PHP 5.3/5.4, by the password hashing
        // compatibility library. the third parameter looks a little bit shitty, but that's how those PHP 5.5 functions
        // want the parameter: as an array with, currently only used with 'cost' => XX.
        $user_password_hash = password_hash($_POST[$user_password_new], PASSWORD_DEFAULT, array('cost' => $hash_cost_factor));

        // write users new password hash into database, reset user_password_reset_hash
        $query = $db->prepare("UPDATE birdy_users
                                        SET password_hash = :user_password_hash,
                                            password_reset_hash = NULL,
                                            password_reset_timestamp = NULL
                                      WHERE username = :user_name
                                        AND password_reset_hash = :user_password_reset_hash");

        $query->execute(array(':user_password_hash' => $user_password_hash,
                              ':user_name' => $_POST[$user_name],
                              ':user_password_reset_hash' => $_POST[$user_password_reset_hash]));

        // check if exactly one row was successfully changed:
        if ($query->rowCount() == 1) {
            // successful password change!
            $birdy->outputNotice(FEEDBACK_PASSWORD_CHANGE_SUCCESSFUL);
            return true;
        }

        // default return
        $birdy->outputAlert(FEEDBACK_PASSWORD_CHANGE_FAILED);
        return false;
    }

    /**
     * Upgrades/downgrades the user's account (for DEFAULT and FACEBOOK users)
     * Currently it's just the field user_account_type in the database that
     * can be 1 or 2 (maybe "basic" or "premium"). In this basic method we
     * simply increase or decrease this value to emulate an account upgrade/downgrade.
     * Put some more complex stuff in here, maybe a pay-process or whatever you like.
     */
    public function changeAccountType()
    {
		$birdy = birdyCMS::getInstance();
		$db = birdyDB::getInstance();
        if (isset($_POST["user_account_upgrade"]) AND !empty($_POST["user_account_upgrade"])) {

            // do whatever you want to upgrade the account here (pay-process etc)
            // ...
            // ... myPayProcess();
            // ...

            // upgrade account type
            $query = $db->prepare("UPDATE birdy_users SET account_type = 2 WHERE id = :user_id");
            $query->execute(array(':user_id' => $_SESSION["user_id"]));

            if ($query->rowCount() == 1) {
                // set account type in session to 2
                birdySession::set('user_account_type', 2);
                $birdy->outputNotice(FEEDBACK_ACCOUNT_UPGRADE_SUCCESSFUL);
            } else {
                $birdy->outputAlert(FEEDBACK_ACCOUNT_UPGRADE_FAILED);
            }
        } elseif (isset($_POST["user_account_downgrade"]) AND !empty($_POST["user_account_downgrade"])) {

            // do whatever you want to downgrade the account here (pay-process etc)
            // ...
            // ... myWhateverProcess();
            // ...

            $query = $db->prepare("UPDATE birdy_users SET account_type = 1 WHERE id = :user_id");
            $query->execute(array(':user_id' => $_SESSION["user_id"]));

            if ($query->rowCount() == 1) {
                // set account type in session to 1
                birdySession::set('user_account_type', 1);
                $birdy->outputNotice(FEEDBACK_ACCOUNT_DOWNGRADE_SUCCESSFUL);
            } else {
                $birdy->outputAlert(FEEDBACK_ACCOUNT_DOWNGRADE_FAILED);
            }
        }
    }

    /**
     * Generates the captcha, "returns" a real image,
     * this is why there is header('Content-type: image/jpeg')
     * Note: This is a very special method, as this is echoes out binary data.
     * Eventually this is something to refactor
     */
    public function generateCaptcha()
    {
        // create a captcha with the CaptchaBuilder lib
        $builder = new CaptchaBuilder;
        $builder->build();

        // write the captcha character into session
        $_SESSION['captcha'] = $builder->getPhrase();

        // render an image showing the characters (=the captcha)
        header('Content-type: image/jpeg');
        $builder->output();
    }

    /**
     * Checks if the entered captcha is the same like the one from the rendered image which has been saved in session
     * @return bool success of captcha check
     */
    private static function checkCaptcha()
    {
        if (isset($_POST["captcha"]) AND ($_POST["captcha"] == $_SESSION['captcha'])) {
            return true;
        }
        // default return
        return false;
    }

    /**
     * Create an avatar picture (and checks all necessary things too)
     * @return bool success status
     */
    public function createAvatar()
    {
		$birdy = birdyCMS::getInstance();
        if (!is_dir(AVATAR_PATH) OR !is_writable(AVATAR_PATH)) {
            $birdy->outputAlert(FEEDBACK_AVATAR_FOLDER_DOES_NOT_EXIST_OR_NOT_WRITABLE);
            return false;
        }

        if (!isset($_FILES['avatar_file']) OR empty ($_FILES['avatar_file']['tmp_name'])) {
            $birdy->outputAlert(FEEDBACK_AVATAR_IMAGE_UPLOAD_FAILED);
            return false;
        }

        // get the image width, height and mime type
        $image_proportions = getimagesize($_FILES['avatar_file']['tmp_name']);

        // if input file too big (>5MB)
        if ($_FILES['avatar_file']['size'] > 5000000 ) {
            $birdy->outputAlert(FEEDBACK_AVATAR_UPLOAD_TOO_BIG);
            return false;
        }
        // if input file too small
        if ($image_proportions[0] < AVATAR_SIZE OR $image_proportions[1] < AVATAR_SIZE) {
            $birdy->outputAlert(FEEDBACK_AVATAR_UPLOAD_TOO_SMALL);
            return false;
        }

        if ($image_proportions['mime'] == 'image/jpeg' || $image_proportions['mime'] == 'image/png') {
            // create a jpg file in the avatar folder
            $target_file_path = AVATAR_PATH . $_SESSION['user_id'] . ".jpg";
            self::resizeAvatarImage($_FILES['avatar_file']['tmp_name'], $target_file_path, AVATAR_SIZE, AVATAR_SIZE, AVATAR_JPEG_QUALITY, true);
            $birdy->outputNotice(FEEDBACK_AVATAR_UPLOAD_SUCCESSFUL);
            return true;
        } else {
            $birdy->outputAlert(FEEDBACK_AVATAR_UPLOAD_WRONG_TYPE);
            return false;
        }
    }

    /**
     * Resize avatar image (while keeping aspect ratio and cropping it off sexy)
     * Originally written by:
     * @author Jay Zawrotny <jayzawrotny@gmail.com>
     * @license Do whatever you want with it.
     *
     * @param string $source_image The location to the original raw image.
     * @param string $destination_filename The location to save the new image.
     * @param int $width The desired width of the new image
     * @param int $height The desired height of the new image.
     * @param int $quality The quality of the JPG to produce 1 - 100
     * @param bool $crop Whether to crop the image or not. It always crops from the center.
     * @return bool success state
     */
    public static function resizeAvatarImage(
        $source_image, $destination_filename, $width = 44, $height = 44, $quality = 85, $crop = true)
    {
        $image_data = getimagesize($source_image);
        if (!$image_data) {
            return false;
        }

        // set to-be-used function according to filetype
        switch ($image_data['mime']) {
            case 'image/gif':
                $get_func = 'imagecreatefromgif';
                $suffix = ".gif";
            break;
            case 'image/jpeg';
                $get_func = 'imagecreatefromjpeg';
                $suffix = ".jpg";
            break;
            case 'image/png':
                $get_func = 'imagecreatefrompng';
                $suffix = ".png";
            break;
        }

        $img_original = call_user_func($get_func, $source_image );
        $old_width = $image_data[0];
        $old_height = $image_data[1];
        $new_width = $width;
        $new_height = $height;
        $src_x = 0;
        $src_y = 0;
        $current_ratio = round($old_width / $old_height, 2);
        $desired_ratio_after = round($width / $height, 2);
        $desired_ratio_before = round($height / $width, 2);

        if ($old_width < $width OR $old_height < $height) {
             // the desired image size is bigger than the original image. Best not to do anything at all really.
            return false;
        }

        // if crop is on: it will take an image and best fit it so it will always come out the exact specified size.
        if ($crop) {
            // create empty image of the specified size
            $new_image = imagecreatetruecolor($width, $height);

            // landscape image
            if ($current_ratio > $desired_ratio_after) {
                $new_width = $old_width * $height / $old_height;
            }

            // nearly square ratio image
            if ($current_ratio > $desired_ratio_before AND $current_ratio < $desired_ratio_after) {

                if ($old_width > $old_height) {
                    $new_height = max($width, $height);
                    $new_width = $old_width * $new_height / $old_height;
                } else {
                    $new_height = $old_height * $width / $old_width;
                }
            }

            // portrait sized image
            if ($current_ratio < $desired_ratio_before) {
                $new_height = $old_height * $width / $old_width;
            }

            // find ratio of original image to find where to crop
            $width_ratio = $old_width / $new_width;
            $height_ratio = $old_height / $new_height;

            // calculate where to crop based on the center of the image
            $src_x = floor((($new_width - $width) / 2) * $width_ratio);
            $src_y = round((($new_height - $height) / 2) * $height_ratio);
        }
        // don't crop the image, just resize it proportionally
        else {
            if ($old_width > $old_height) {
                $ratio = max($old_width, $old_height) / max($width, $height);
            } else {
                $ratio = max($old_width, $old_height) / min($width, $height);
            }

            $new_width = $old_width / $ratio;
            $new_height = $old_height / $ratio;
            $new_image = imagecreatetruecolor($new_width, $new_height);
        }

        // create avatar thumbnail
        imagecopyresampled($new_image, $img_original, 0, 0, $src_x, $src_y, $new_width, $new_height, $old_width, $old_height);

        // save it as a .jpg file with our $destination_filename parameter
        imagejpeg($new_image, $destination_filename, $quality);

        // delete "working copy" and original file, keep the thumbnail
        imagedestroy($new_image);
        imagedestroy($img_original);

        if (file_exists($destination_filename)) {
            return true;
        }
        // default return
        return false;
    }
    
    public function connectWithProvider($provider=false) {
		$birdy = birdyCMS::getInstance();
		$hybridconfig = BIRDY_BASE.DS.'birdy'.DS.'helpers'.DS.'hybridauth'.DS.'config.php';
		require_once( BIRDY_BASE.DS.'birdy'.DS.'helpers'.DS.'hybridauth'.DS.'Hybrid'.DS.'Auth.php' );
		
		// check for errors and whatnot
		$error = "";
		if( isset( $_GET["error"] ) ){
			$birdy->outputAlert(trim( strip_tags(  $_GET["error"] ) ));
		}

		// if user select a provider to login with
			// then inlcude hybridauth config and main class
			// then try to authenticate te current user
			// finally redirect him to his profile page
		if( $provider ):
			try{
				// create an instance for Hybridauth with the configuration file path as parameter
				$hybridauth = new Hybrid_Auth( $hybridconfig );
	
				// set selected provider name 
				$provider = @ trim( strip_tags( $provider ) );

				// try to authenticate the selected $provider
				$adapter = $hybridauth->authenticate( $provider );

				// Success!
				// unset the connecting session
				unset($_SESSION['connectWithProvider']);
				// and grab the user profile
				$user_data = $adapter->getUserProfile();
				$user_data->provider = $provider;
				
// 				echo "<pre>";print_r($user_data);echo "</pre>";
// 						if ($provider!='google') {
// 							$user_contacts = $adapter->getUserContacts();
// 						} else {
// 							$provider_username = str_replace('https://profiles.google.com/','',$user_data->profileURL);
// 							//https://www.googleapis.com/plus/v1/people/102659880853930627111/people/visible?key=
// 							$response = $adapter->api()->api('https://www.googleapis.com/plus/v1/people/'.$provider_username.'/people/visible?key=AIzaSyB84DZKonqRx__86K2U0f8hbpvKcDufb9E');
// 							$user_contacts = $response->items;
// 						}
// 				echo "<pre>CONTACTS:";print_r($user_contacts);echo "</pre>";
// 				exit();

				# 1 - check if user already have authenticated using this provider before
				$user_already_authenticated = self::providerUserIdExists( $user_data );

				# 2 - if authentication exists in the database...
				if( $user_already_authenticated ){
				
					// if non-logged in yet, we set the user as connected and redirect him to the home page
					if (empty($_SESSION['user_id']) && empty($_SESSION['user_logged_in'])) {
					
						// 2.1 - login the user
						self::loginAction($user_already_authenticated);
						
						// 2.2 - redirect to page
						$birdy->loadPage(birdyConfig::$login_redirection);
						
						return;
						
					} else {
					
						// a logged in user has requested to connect his account with social network, but he already did in the past.
						$birdy->outputWarning("This ".$provider." account is already connected with another profile!");
						$birdy->loadPage(birdyConfig::$login_redirection);
						// do nothing.
						return;
					}
				}
				
				# 3 - else, here lets check if the user email we got from the provider already exists in our database ( for this example the email is UNIQUE for each user )
				// if authentication does not exist, but the email address returned  by the provider does exist in database, 
				// we associate the authentication with the user having the adress email in the database.
				if( $user_data->email ){
					$user_exists = self::providerUserEmailExists( $user_data );
					if ($user_exists) {
						if (empty($_SESSION['user_id']) && empty($_SESSION['user_logged_in'])) {
						
							// non-logged in user. we continue to associate this account with social account
							$user_id = $user_exists->id;
							
						} else {
						
							// logged in account. check if current user id is the same as the id of the account with the same email address
							if ($user_exists->id!=$_SESSION['user_id']) {
							
								// this email address associated with other account. this is WRONG!
								$birdy->outputAlert("This ".$provider." account cannot be connected with your account. 
								Another user has registered with this account's email address");
								
								// do nothing.
								return;
							}
						}
					}
				}
				
				# 4 - check the username
				if ($provider=='facebook') $provider_username = str_replace('https://www.facebook.com/','',$user_data->profileURL);
				if ($provider=='twitter') $provider_username = str_replace('http://twitter.com/','',$user_data->profileURL);
				if ($provider=='google') $provider_username = str_replace('https://profiles.google.com/','',$user_data->profileURL);
				
				$user_exists = self::providerUserNameExists( $provider_username );
					if ($user_exists) {
						if (empty($_SESSION['user_id']) && empty($_SESSION['user_logged_in'])) {
						
							// non-logged in user. we continue to associate this account with social account
							$user_id = $user_exists->id;
							
						} else {
						
							// logged in account. check if current user id is the same as the id of the account with the same email address
							if ($user_exists->id!=$_SESSION['user_id']) {
							
								// this email address associated with other account. we must generate another username
								$provider_username = self::generateUniqueUserNameFromExistingUserName($provider_username);
							}
						}
					}

				# 5 - if authentication does not exist and email is not in use, then we create a new social user
					
					// make sure we associate with the currently logged in user, if any
					if (empty($user_id)) $user_id = empty($_SESSION['user_id']) ? false : $_SESSION['user_id'];
					
				$user_data->password      	= 'pass'.(string)rand() ; # for the password we generate something random
				$user_data->username		= $provider_username;
				$user_data->user_id			= isset($user_id) ? $user_id : false;
				if (empty($user_data->lastName)) {
					$lastname = explode(" ",$user_data->firstName);
					if (isset($lastname[1])) {
						$user_data->firstName = $lastname[0];
						$lastname = $lastname[1];
					} else $lastname = '';
					$user_data->lastName = $lastname;
				}
				if (empty($user_data->email)) {
					$birdy->outputAlert("There is no email address associated with your ".$provider." account. 
					You will need to edit your profile and add your real email address in order for your profile to function properly.");
					$user_data->email = $user_data->username.'@'.$user_data->provider.'.com';
				}
				
				if (!$user_data->user_id) {
					// 5.1 - user doesn't exist, create a new one
					$user_data->user_id = self::registerProvider($user_data);
				}
				
				if ($user_data->user_id) {
					// 5.2 - user exists or has been created, connect his account with social provider.
					$success = self::connectProvider($user_data);

					if ($success) {
						// 5.3 - store user's contacts
						if ($provider!='google') {
							$user_contacts = $adapter->getUserContacts();
						} else {
							$response = $adapter->api()->get('/plus/v1/people/'.$user_data->username.'/people/visible?key='.birdyConfig::$googleAPIkey);
							$user_contacts = $response->items;
						}
						self::storeUserContacts($user_data, $user_contacts);
						
						// 5.4 - call Hybrid_Auth::getSessionData() to get stored data, and save it to database
						self::updateSocialData($hybridauth,$user_data->user_id,$provider);
						
						// 5.5 - set a cookie with user_id to check social connection to klipsam on this computer
						birdySession::writeSocialCookie($user_data->user_id);
						
						// 5.6 - login the user
						self::loginAction($success);
						
						// 5.7 - redirect to profile page
						$birdy->loadPage("/profile");
						
						return;
					}
				
				}
				
				// if we got this far, something went wrong. Inform the user to inform us! :)
				$birdy->outputAlert("Something went wrong during the social connection. Please contact our administrators");
				return false;
				
				
			}
			catch( Exception $e ){
				// In case we have errors 6 or 7, then we have to use Hybrid_Provider_Adapter::logout() to 
				// let hybridauth forget all about the user so we can try to authenticate again.

				// Display the recived error, 
				// to know more please refer to Exceptions handling section on the userguide
				switch( $e->getCode() ){ 
					case 0 : $error = "Unspecified error."; break;
					case 1 : $error = "Hybriauth configuration error."; break;
					case 2 : $error = "Provider not properly configured."; break;
					case 3 : $error = "Unknown or disabled provider."; break;
					case 4 : $error = "Missing provider application credentials."; break;
					case 5 : $error = "Authentication failed. The user has canceled the authentication or the provider refused the connection."; 
						$_SESSION['hybrid_must_logout']=$provider;break;
					case 6 : $error = "User profile request failed. Most likely the user is not connected to the provider and he should authenticate again."; 
						$_SESSION['hybrid_must_logout']=$provider;break;
					case 7 : $error = "User not connected to the provider."; 
						$_SESSION['hybrid_must_logout']=$provider;break;
				} 

				// well, basically your should not display this to the end user, just give him a hint and move on..
				//$error .= "<br /><br /><b>Original error message:</b> " . $e->getMessage(); 
				//$error .= "<hr /><pre>Trace:<br />" . $e->getTraceAsString() . "</pre>";
				$birdy->outputAlert($error);
			}
		endif;
	}

    /**
     * Register user with data from the "facebook object"
     * @param array $facebook_user_data stuff from the facebook class
     * @return bool success state
     */
    private function registerProvider($user_data)
    {
		$db = birdyDB::getInstance();
        // generate integer-timestamp for saving of account-creating date
        $user_creation_timestamp = time();
        $hash_cost_factor = (defined('HASH_COST_FACTOR') ? HASH_COST_FACTOR : null);
        $user_password_hash = password_hash($user_data->password, PASSWORD_DEFAULT, array('cost' => $hash_cost_factor));
        
        if($db->insert("birdy_users", array(
			"username"			=> $user_data->username,
			"first_name" 		=> $user_data->firstName,
			"last_name"			=> $user_data->lastName,
			"about"				=> $user_data->description,
			"website"			=> $user_data->webSiteURL,
			"gender"			=> $user_data->gender,
			"language"			=> $user_data->language,
			"birthday"			=> $user_data->birthYear.'-'.$user_data->birthMonth.'-'.$user_data->birthDay,
			"email"				=> $user_data->email,
			"active"			=> 1,
			"creation_timestamp"=> $user_creation_timestamp))) {
			
			$user_id = $db->lastInsertId();
			
			if ($db->insert("birdy_social_users", array(
				"user_id"			=> $user_id,
				"provider_uid"		=> $user_data->identifier,
				"provider_type" 	=> $user_data->provider,
				"provider_username" => $user_data->username,
				"provider_email"	=> $user_data->email,
				"profileURL"		=> $user_data->profileURL,
				"photoURL"			=> $user_data->photoURL))) {
				
				return $user_id;
				
			}
			
			return false;
		}
	}
        
	private static function connectProviderHelper($user,$what,$user_data,$what_in_user_data) {
		$db = birdyDB::getInstance();
		if (empty($user->$what)) {
			$set 	= $what.'=:'.$what;
			$where 	= 'id=:user_id';
			$args 	= array(":".$what=>$user_data->$what_in_user_data, ":user_id"=>$user_data->user_id);
			$db->update("birdy_users",$set,$where,$args);
		}
	}
        
    /**
     * Connect existing user with data from the "facebook object"
     * @param array $facebook_user_data stuff from the facebook class
     * @return bool success state
     */
    private function connectProvider($user_data)
    {
		$db = birdyDB::getInstance();
		$user = new birdyUser($user_data->user_id);

		self::connectProviderHelper($user,'username',$user_data,'username');
		self::connectProviderHelper($user,'first_name',$user_data,'firstName');
		self::connectProviderHelper($user,'last_name',$user_data,'lastName');
		self::connectProviderHelper($user,'about',$user_data,'description');
		self::connectProviderHelper($user,'website',$user_data,'webSiteURL');
		self::connectProviderHelper($user,'gender',$user_data,'gender');
		self::connectProviderHelper($user,'language',$user_data,'language');
		self::connectProviderHelper($user,'email',$user_data,'email');
		
		if (empty($user->active)) 		$db->update("birdy_users","active=:active","id=:user_id",array(":active"=>1, ":user_id"=>$user_data->user_id));
		
		//use the social avatar if none exists yet
		/*
		this doesn't work. we will save the profile pic in database and take it from there if none other is provided.
		if (!file_exists(BIRDY_BASE.DS.'images'.DS.'avatars'.DS.$user_data->user_id.'.jpg')) {
			birdyUser::saveAvatar($user_data->photoURL, $user_data->user_id, false);
		}
		*/
		
		if (!$db->loadResult("SELECT id FROM birdy_social_users WHERE user_id=:user_id AND provider_uid=:provider_uid AND provider_type=:provider",
			array(":user_id"=>$user_data->user_id, ":provider_uid"=>$user_data->identifier, ":provider"=>$user_data->provider))) {

			if (!$db->insert("birdy_social_users", array(
				"user_id"			=> $user_data->user_id,
				"provider_uid"		=> $user_data->identifier,
				"provider_type" 	=> $user_data->provider,
				"provider_username" => $user_data->username,
				"provider_email"	=> $user_data->email,
				"profileURL"		=> $user_data->profileURL,
				"photoURL"			=> $user_data->photoURL))) {
				
				return false;
				
			}
			
			
		}

		// save geo data
		birdyUser::saveGeo($user_data);
				
		// re-instantiate the user, to get new data added.. ;)
		$user = new birdyUser($user_data->user_id);
		return $user;
	}
	
	private function storeUserContacts($user_data, $user_contacts) {
		$db = birdyDB::getInstance();
		//delete previous contacts from this provider, if any
		$db->delete("birdy_social_contacts", array("user_id"=>$user_data->user_id, "provider_type"=>$user_data->provider));
		$rows = array();
		foreach ($user_contacts as $user_contact) {
			$user_contact->identifier = isset($user_contact->identifier) ? $user_contact->identifier : $user_contact->id;
			$user_contact->photoURL   = isset($user_contact->photoURL) ? $user_contact->photoURL : $user_contact->image->url;
			$rows[] = array(
			$user_data->user_id,
			$user_contact->identifier,
			$user_contact->photoURL,
			$user_contact->displayName,
			$user_data->provider);
		}
		$db->insertMultiple('INSERT INTO birdy_social_contacts 
			(user_id, contact_uid, photoURL, displayName, provider_type)',
			$rows);
	}
        
    /**
     * Check if the facebook-user's UID (unique facebook ID) already exists in our database
     * @param array $facebook_user_data stuff from the facebook class
     * @return bool success state
     */
    private function providerUserIdExists($user_data)
    {
		$db = birdyDB::getInstance();
        $user_id = $db->loadResult("SELECT user_id AS id FROM birdy_social_users WHERE provider_uid = :user_provider_uid AND provider_type = :provider",
					array(':user_provider_uid' => $user_data->identifier, ':provider' => $user_data->provider));
		if (!empty($user_id)) {
			return $db->loadObject("SELECT * FROM birdy_users WHERE id= :user_id",
					array(':user_id'=>$user_id));
		}
		return false;
    }

    /**
     * Checks if the facebook-user's username is already in our database
     * Note: facebook's user-names have dots, so we remove all dots.
     * @param array $facebook_user_data stuff from the facebook class
     * @return bool success state
     */
    private function providerUserNameExists($username)
    {
		$db = birdyDB::getInstance();
        return $db->loadObject("SELECT * FROM birdy_users WHERE username = :provider_username",
			array(':provider_username' => $username));
    }

    /**
     * Checks if the facebook-user's email address is already in our database
     * @param array $facebook_user_data stuff from the facebook class
     * @return bool success state
     */
    private function providerUserEmailExists($user_data)
    {
		$db = birdyDB::getInstance();
        return $db->loadObject("SELECT * FROM birdy_users WHERE email = :provider_email",
			array(':provider_email' => $user_data->email));
    }

    /**
     * Generate unique user_name from facebook-user's username appended with a number
     * @param string $existing_name $facebook_user_data stuff from the facebook class
     * @return string unique user_name not in database yet
     */
     //@TODO: WE NEED TO CHECK IF THE PROVIDER USERNAME DOES NOT EXIST IN PROVIDER DATABASE BUT ALREADY EXISTS IN THE BIRDY_USERS DATABASE
     //IF THIS IS TRUE, THEN WE TELL THIS TO THE USER, WE SHOW THE PROFILE AVATAR AND ASK "IS THAT YOU? ENTER YOUR PASSWORD
     //TO CONNECT YOUR PROFILE WITH PROVIDER. THEN WE CONNECT THE PROFILES.
     //@NOTE: THIS NEEDS TO BE DONE FOR ALL EMAILS IN THE SOCIAL PROVIDER.
     //IF USER IS NOT HIM, HE ANSWERS NO.
     //THIS MEANS THE USER DOESN'T EXIST YET IN OUR APP, AND WE NEED TO CREATE A NEW USERNAME FOR HIM, BECAUSE THE ONE HE HAS WITH THE PROVIDER IS ALREADY TAKEN HERE
    private function generateUniqueUserNameFromExistingUserName($existing_name)
    {
		$db = birdyDB::getInstance();
    	//strip any dots, trailing numbers and white spaces
        $existing_name = str_replace(".", "", $existing_name);
        $existing_name = preg_replace('/\s*\d+$/', '', $existing_name);

        // loop until we have a new username, adding an increasing number to the given string every time
    	$n = 0;
    	do {
            $n = $n+1;
            $new_username = $existing_name . $n;
            $query = $db->prepare("SELECT id FROM birdy_users WHERE username = :name_with_number");
            $query->execute(array(':name_with_number' => $new_username));
    	 	 
    	 } while ($query->rowCount() == 1);

    	return $new_username;
    }
    
    public function updateSocialData($hybridauth,$user_id,$provider) {
		$db = birdyDB::getInstance();
		$hybridconfig = BIRDY_BASE.DS.'birdy'.DS.'helpers'.DS.'hybridauth'.DS.'config.php';
		require_once( BIRDY_BASE.DS.'birdy'.DS.'helpers'.DS.'hybridauth'.DS.'Hybrid'.DS.'Auth.php' );
		$session_token = $hybridauth->getSessionData();
		$set = 'session_token=:session_token';
		$where = 'user_id=:user_id AND provider_type=:provider_type';
		$args = array(":session_token"=>$session_token, ":user_id"=>$user_id, ":provider_type"=>$provider);
		$db->update("birdy_social_users",$set,$where,$args);
    }

}

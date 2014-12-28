<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');

class birdyUser
{
	protected static $_instance;

    function __construct($user_id=false)
    {
		$this->db = birdyDB::getInstance();
		$this->id = 0; // if no user logged in, then id is always 0
		
		// ONLY CURRENT USER SHOULD BE INSTANTIATED WITHOUT USER ID! All other users should be instantiated with their id!
		if (!$user_id) {
		
			// user has remember-me-cookie ? then try to login with cookie ("remember me" feature)
			if (empty($_SESSION['user_logged_in']) && isset($_COOKIE['rememberme'])) {
				$cookieLogin = birdyLogin::loginWithCookie();
			}
			
			// check if we should connect the user with his social approval
			$hybridconfig = BIRDY_BASE.DS.'birdy'.DS.'helpers'.DS.'hybridauth'.DS.'config.php';
			require_once( BIRDY_BASE.DS.'birdy'.DS.'helpers'.DS.'hybridauth'.DS.'Hybrid'.DS.'Auth.php' );
			
			// let's check if we got an error on last social login. If yes, we must logout
			if (isset($_SESSION['hybrid_must_logout'])) {
				try {
					Hybrid_Auth::initialize( $hybridconfig );
					$adapter = Hybrid_Auth::getAdapter($_SESSION['hybrid_must_logout']);
					$adapter->logout();
					// social profile doesn't need to logout anymore
					unset($_SESSION['hybrid_must_logout']);
					// user is not connecting to a provider anymore
					unset($_SESSION['connectWithProvider']);
				} catch( Exception $e ) {
					$birdy = birdyCMS::getInstance();
					$birdy->outputAlert($e->getMessage());
				}
			}
			
			// if the user has finished connecting his social account, try to re-initialize the social connection
			// this time for login purposes. We need this check here, so we don't loop into connecting with the same
			// social provider again and again
			try {
				if (empty($_SESSION['connectWithProvider']))
					$hybridauth = new Hybrid_Auth( $hybridconfig );
			} catch( Exception $e ) {
				$birdy = birdyCMS::getInstance();
				$birdy->outputAlert($e->getMessage());
			}
			
			// this 3 IFs check connection on social networks on ALL devices!
			if (empty($_SESSION['user_logged_in'])) {
				// if no connection is found, check for a cookie on this computer, maybe we can restore session from there
				if (isset($_COOKIE['rememberme_social'])) {
					$cookie = $_COOKIE['rememberme_social'];
					$user_id =  birdySession::checkSocialCookie($cookie);
					if ($user_id) {
						$session_tokens = $this->db->loadObjeclist("SELECT session_token, provider_type FROM birdy_social_users WHERE user_id=:user_id",
							array(":user_id"=>intval($user_id)));
							
						// check for each social session token (serialized social data) of this user,
						if (!empty($session_tokens)) {
							foreach($session_tokens as $session_token) {
								//try to re-connect with this provider
								$provider = $session_token->provider_type;
								$hybridauth->restoreSessionData($session_token->session_token);
								$adapter = $hybridauth->authenticate( $provider );
								// connected with one social account, no need to re-connect with anymore social accounts
								if (!empty($adapter)) break;
							}
						}
						
					}
				}
				
				// if we have connection of the user with one social account
				if (!empty($adapter)) {
					// try to login this user with this account
					try {
						$user_data = $adapter->getUserProfile();
						if (!empty($user_data)) {
							// we need this db call for loginSuccess method...
							$user_data = $this->db->loadObject("SELECT * FROM birdy_users WHERE id=:user_id",
								array(":user_id"=>$user_id));
							// set this user as successfully logged in
							if (birdyLogin::loginAction($user_data)) {
								// update his social data for future re-logins
								birdyLogin::updateSocialData($hybridauth,$user_id,$provider);
							// login failed. set the user ID to 0
							} else {
								$user_id = 0;
							}
						}
					// something went wrong. Disconnect this social account to let the user re-connect manually if he wants
					} catch( Exception $e ) {
						$adapter->logout();
						// these messages only for moderators
						//$birdy = birdyCMS::getInstance();
						//$birdy->outputAlert($e->getMessage());
					}
				}
			}
			
			// if user is still not logged in, then destroy all user instances, handle user as "not logged in"
			if (empty($_SESSION['user_logged_in']) && empty($_SESSION['user_id'])) {
				$this->destroy();
				$this->id = 0;
			} else {
				$user_id = $_SESSION['user_id'];
			}
			
		}
		
		if ($user_id) {
			$user = $this->getUser($user_id);
			foreach ($user as $key => $value) {
				$this->$key = stripslashes($value);
			}
		}
		
        self::$_instance[$this->id] = $this; // to keep instances of multiple users
        if (isset($_SESSION['user_id']) && $user_id == $_SESSION['user_id']) self::$_instance['current'] = $this; // to keep an instance of the current instance
    }
    
    /**
    * get current Instance, or an Instance of any user ID
    */
	public static function getInstance($user_id=false) {
		if (empty($user_id)) $user_id = 'current'; // if no user_id given check for the current instance
		if (empty(self::$_instance[$user_id])) {
			if ($user_id=='current') $user_id = false;
			$_instance = new BirdyUser($user_id); // if this instance does not exist, create it
			$user_id = $_instance->id; // and get the user id from the newly created instance
		}
		return isset(self::$_instance[$user_id]) ? self::$_instance[$user_id] : self::$_instance[0];
	}
	
	public function destroy() {
		if (!empty(self::$_instance)) {
			foreach (self::$_instance as $key => $instance) {
				foreach (self::$_instance[$key] as $instakey => $value) {
					unset(self::$_instance[$key]->$instakey);
				}
			}
		}
		if (!empty($this)) {
			foreach ($this as $key => $value) {
				unset($this->$key);
			}
		}
	}
	
	public function isLoggedIn() {
		return !empty($this->id) ? $this->id : false;
	}
	
	public function set($key,$value) {
		$this->$key = $value;
	}
	
	private function getUser($user) {
        // get user's data
        // (we check if the password fits the password_hash via password_verify() some lines below)
        $sth = $this->db->prepare("SELECT *
                                   FROM   birdy_users
                                   WHERE  id = :user_id");
        $sth->execute(array(':user_id' => $user));
        $count =  $sth->rowCount();
        // if there's NOT one result
        if ($count != 1) {
            $_SESSION["feedback_negative"][] = FEEDBACK_USER_DOES_NOT_EXIST;
            return false;
        }
        // fetch one row (we only have one result)
        $user = $sth->fetch();
		$user->avatar = $this->getAvatar($user->id);
        if (empty($user->avatar)) {
			$user->avatar = $this->getGravatar($user->email);
        }
        $user->used_name = (birdyConfig::$use_real_name && (!empty($user->first_name)||!empty($user->last_name))) ? $user->first_name.' '.$user->last_name : $user->username;
        return $user;
	}
	
    /**
     * Get either a Gravatar URL or complete image tag for a specified email address.
     * Gravatar is the #1 (free) provider for email address based global avatar hosting.
     * The image url (on gravatar servers), will return in something like (note that there's no .jpg)
     * http://www.gravatar.com/avatar/205e460b479e2e5b48aec07710c08d50?s=80&d=mm&r=g
     *
     * For deeper info on the different parameter possibilities:
     * @see http://gravatar.com/site/implement/images/
     * @source http://gravatar.com/site/implement/images/php/
     *
     * @param string $email The email address
     * @param int $s Size in pixels [ 1 - 2048 ]
     * @param string $d Default imageset to use [ 404 | mm | identicon | monsterid | wavatar ]
     * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
     * @param array $attributes Optional, additional key/value attributes to include in the IMG tag
     */
    public function getGravatar($email, $s=AVATAR_SIZE, $d='mm', $r='pg')
    {
        // create image URL, write it into session
        return 'http://www.gravatar.com/avatar/' . md5(strtolower(trim($email))) .  "?s=$s&d=$d&r=$r";
    }

    /**
     * Gets the user's avatar file path
     * @return string avatar picture path
     */
    public function getAvatar($user_id)
    {
        if (file_exists(BIRDY_BASE. DS . AVATAR_PATH . $user_id . '.jpg')) {
            return URL . AVATAR_PATH . $user_id . '.jpg?time=' . filemtime(BIRDY_BASE. DS . AVATAR_PATH . $user_id . '.jpg');
        } else {
			$db = birdyDB::getInstance();
			$avatar = $db->loadResult("SELECT photoURL FROM birdy_social_users WHERE user_id=:user_id ORDER BY id DESC LIMIT 1",
				array(":user_id"=>$user_id));
			if ($avatar) {
				$success = self::saveAvatar($avatar,$user_id);
				if ($success) return URL . AVATAR_PATH . $user_id . '.jpg?time=' . filemtime(BIRDY_BASE. DS . AVATAR_PATH . $user_id . '.jpg');
			}
            return URL . AVATAR_PATH . AVATAR_DEFAULT_IMAGE;
        }
    }

    /**
     * Edit the user's name, provided in the editing form
     * @return bool success status
     */
    public function editUserName()
    {
		birdySession::init();
        // new username provided ?
        if (!isset($_POST['user_name']) OR empty($_POST['user_name'])) {
            $_SESSION["feedback_negative"][] = FEEDBACK_USERNAME_FIELD_EMPTY;
            return false;
        }

        // new username same as old one ?
        if ($_POST['user_name'] == $_SESSION["user_name"]) {
            //$_SESSION["feedback_negative"][] = FEEDBACK_USERNAME_SAME_AS_OLD_ONE;
            return true;
        }

        // username cannot be empty and must be azAZ09 and 2-64 characters
        if (!preg_match("/^[a-z0-9-\_\.\d]{2,64}$/i", $_POST['user_name'])) {
            $_SESSION["feedback_negative"][] = FEEDBACK_USERNAME_DOES_NOT_FIT_PATTERN;
            return false;
        }

        // clean the input
        $user_name = substr(strip_tags($_POST['user_name']), 0, 64);
        
        $db = birdyDB::getInstance();

        // check if new username already exists
        $query = $db->prepare("SELECT id FROM birdy_users WHERE id<>:user_id AND username = :user_name");
        $query->execute(array(':user_id'=>$_SESSION['user_id'],':user_name' => $user_name));
        $count =  $query->rowCount();
        if ($count == 1) {
            $_SESSION["feedback_negative"][] = FEEDBACK_USERNAME_ALREADY_TAKEN;
            return false;
        }

        $query = $db->prepare("UPDATE birdy_users SET username = :user_name WHERE id = :user_id");
        $query->execute(array(':user_name' => $user_name, ':user_id' => $_SESSION['user_id']));
        $count =  $query->rowCount();
        if ($count == 1) {
            birdySession::set('user_name', $user_name);
            $_SESSION["feedback_positive"][] = FEEDBACK_USERNAME_CHANGE_SUCCESSFUL;
            return true;
        } else {
            $_SESSION["feedback_negative"][] = FEEDBACK_UNKNOWN_ERROR;
            return false;
        }
    }

    /**
     * Edit the user's name, provided in the editing form
     * @return bool success status
     */
    public function editUserFirstName()
    {
		birdySession::init();
        // new username provided ?
        if (!isset($_POST['first_name']) OR empty($_POST['first_name'])) {
            //$_SESSION["feedback_negative"][] = FEEDBACK_USERNAME_FIELD_EMPTY;
            return false;
        }

        // clean the input
        $first_name = substr(strip_tags($_POST['first_name']), 0, 128);
        
        $db = birdyDB::getInstance();

        $query = $db->prepare("UPDATE birdy_users SET first_name = :first_name WHERE id = :user_id");
        $query->execute(array(':first_name' => $first_name, ':user_id' => $_SESSION['user_id']));
        return true;
    }

    /**
     * Edit the user's name, provided in the editing form
     * @return bool success status
     */
    public function editUserLastName()
    {
		birdySession::init();
        // new username provided ?
        if (!isset($_POST['last_name']) OR empty($_POST['last_name'])) {
            //$_SESSION["feedback_negative"][] = FEEDBACK_USERNAME_FIELD_EMPTY;
            return false;
        }

        // clean the input
        $last_name = substr(strip_tags($_POST['last_name']), 0, 128);
        
        $db = birdyDB::getInstance();

        $query = $db->prepare("UPDATE birdy_users SET last_name = :last_name WHERE id = :user_id");
        $query->execute(array(':last_name' => $last_name, ':user_id' => $_SESSION['user_id']));
        return true;
    }

    /**
     * Edit the user's name, provided in the editing form
     * @return bool success status
     */
    public function editUserHeader()
    {
		birdySession::init();
        // new username provided ?
        if (!isset($_POST['user_header']) OR empty($_POST['user_header'])) {
            //$_SESSION["feedback_negative"][] = FEEDBACK_USERNAME_FIELD_EMPTY;
            return false;
        }

        // clean the input
        $user_header = substr(strip_tags($_POST['user_header']), 0, 128);
        
        $db = birdyDB::getInstance();

        $query = $db->prepare("UPDATE birdy_users SET header = :header WHERE id = :user_id");
        $query->execute(array(':header' => $user_header, ':user_id' => $_SESSION['user_id']));
        return true;
    }

    /**
     * Edit the user's name, provided in the editing form
     * @return bool success status
     */
    public function editUserBio()
    {
		birdySession::init();
        // new username provided ?
        if (!isset($_POST['about']) OR empty($_POST['about'])) {
            //$_SESSION["feedback_negative"][] = FEEDBACK_USERNAME_FIELD_EMPTY;
            return false;
        }

        // clean the input
        $about = strip_tags($_POST['about']);
        
        $db = birdyDB::getInstance();

        $query = $db->prepare("UPDATE birdy_users SET about = :about WHERE id = :user_id");
        $query->execute(array(':about' => $about, ':user_id' => $_SESSION['user_id']));
        return true;
    }

    /**
     * Edit the user's email, provided in the editing form
     * @return bool success status
     */
    public function editUserEmail()
    {
		$db = birdyDB::getInstance();
 		birdySession::init();
       // email provided ?
        if (!isset($_POST['user_email']) OR empty($_POST['user_email'])) {
            //$_SESSION["feedback_negative"][] = FEEDBACK_PASSWORD_FIELD_EMPTY;
            return false;
        }

        // check if new email is same like the old one
        $current_email = $db->loadResult("SELECT email FROM birdy_users WHERE id = :user_id",
			array(":user_id"=>$_SESSION['user_id']));
        if ($_POST['user_email'] == $current_email) {
            return true;
            // no change of email
        }

        // user's email must be in valid email format
        if (!filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION["feedback_negative"][] = FEEDBACK_EMAIL_DOES_NOT_FIT_PATTERN;
            return false;
        }
        
        // check if user's email already exists
        $query = $db->prepare("SELECT * FROM birdy_users WHERE id<>:user_id AND email = :user_email");
        $query->execute(array(':user_id'=>$_SESSION['user_id'],':user_email' => $_POST['user_email']));
        $count =  $query->rowCount();
        if ($count == 1) {
            $_SESSION["feedback_negative"][] = FEEDBACK_USER_EMAIL_ALREADY_TAKEN;
            return false;
        }

        if (self::sendVerificationEmail($user_id, $user_email, $user_activation_hash)) {
                $birdy->outputNotice("You will need to verify your new email address. 
                We have sent you a new account activation link for this purpose to your new address. 
                Please notice that you will not be able to login again out of security reasons, until you re-activate your account.
                This is for your own good.");
                return true;
				// cleaning and write new email to database
				$user_email = substr(strip_tags($_POST['user_email']), 0, 64);
				$query = $db->prepare("UPDATE birdy_users SET email = :user_email, active=0 WHERE id = :user_id");
				$query->execute(array(':user_email' => $user_email, ':user_id' => $_SESSION['user_id']));
				birdySession::set('user_email', $user_email);
				// call the setGravatarImageUrl() method which writes gravatar URLs into the session
				//$this->setGravatarImageUrl($user_email, AVATAR_SIZE);
				$_SESSION["feedback_positive"][] = FEEDBACK_EMAIL_CHANGE_SUCCESSFUL;
				return true;
        } else {
                $birdy->outputAlert("We were unable to send you a verification link to your new email address.
                Because of this, your email address has not been updated");
                return false;
        }

    }

    /**
     * Create an avatar picture (and checks all necessary things too)
     * @return bool success status
     */
    public function createAvatar()
    {
		birdySession::init();
        if (!is_dir(AVATAR_PATH) OR !is_writable(AVATAR_PATH)) {
            $_SESSION["feedback_negative"]['user'] = FEEDBACK_AVATAR_FOLDER_DOES_NOT_EXIST_OR_NOT_WRITABLE;
            return false;
        }

        if (!isset($_FILES['avatar_file']) OR empty ($_FILES['avatar_file']['tmp_name'])) {
            $_SESSION["feedback_negative"]['user'] = FEEDBACK_AVATAR_IMAGE_UPLOAD_FAILED;
            return false;
        }

        // if input file too big (>5MB)
        if ($_FILES['avatar_file']['size'] > 5000000 ) {
            $_SESSION["feedback_negative"]['user'] = FEEDBACK_AVATAR_UPLOAD_TOO_BIG;
            return false;
        }
        return self::saveAvatar($_FILES['avatar_file']['tmp_name'], $_SESSION['user_id']);
    }
    
    public static function saveAvatar($avatar_file, $user_id, $suppress_notifications=false) {
        include_once(BIRDY_BASE.'/birdy/helpers/simpleimage.php');
        $target_file_path = AVATAR_PATH . $user_id . ".jpg";
        // get the image width, height and mime type
        $image_proportions = getimagesize($avatar_file);
        // if input file too small
        if ($image_proportions[0] < AVATAR_SIZE OR $image_proportions[1] < AVATAR_SIZE) {
			copy($avatar_file,$target_file_path);
			$image = new SimpleImage();
			$image->load($target_file_path);
			$image->setQuality(100);
			$image->resizeToWidth(200);
			$image->resizeToHeight(200);
			$image->save($target_file_path);
			$image->close();
            //if (!$suppress_notifications && $user_id==$_SESSION['user_id']) $_SESSION["feedback_negative"]['user'] = FEEDBACK_AVATAR_UPLOAD_TOO_SMALL;
            $avatar_file = $target_file_path;
            $image_proportions = getimagesize($avatar_file);
        }

        if ($image_proportions['mime'] == 'image/jpeg' || $image_proportions['mime'] == 'image/png') {
            // create a jpg file in the avatar folder
            $destination_filename = self::resizeAvatarImage($avatar_file, $target_file_path, AVATAR_SIZE, AVATAR_SIZE, AVATAR_JPEG_QUALITY, true);
            if (!$suppress_notifications) $_SESSION["feedback_positive"]['user'] = FEEDBACK_AVATAR_UPLOAD_SUCCESSFUL;
            $thumbnail = str_replace("/avatars/","/avatars/thumbnails/",$destination_filename);
			$image = new SimpleImage();
			$image->load($destination_filename);
			$image->setQuality(98);
			$image->resizeToWidth(64);
			$image->save($thumbnail);
			$image->close();
            return true;
        } else {
            if (!$suppress_notifications && $user_id==$_SESSION['user_id']) $_SESSION["feedback_negative"]['user'] = FEEDBACK_AVATAR_UPLOAD_WRONG_TYPE;
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
            return $destination_filename;
        }
        // default return
        return false;
    }

    /**
     *
     */
    function changeAccountType()
    {
        birdyLogin::changeAccountType();
    }
    
    public function isConnectedWith($provider) {
		$hybridconfig = BIRDY_BASE.DS.'birdy'.DS.'helpers'.DS.'hybridauth'.DS.'config.php';
		require_once( BIRDY_BASE.DS.'birdy'.DS.'helpers'.DS.'hybridauth'.DS.'Hybrid'.DS.'Auth.php' );
		$hybridauth = new Hybrid_Auth( $hybridconfig );
		if ($hybridauth->isConnectedWith($provider)) return true; 
		else {
			//check also in the database
			$connected = $this->db->loadResult("SELECT user_id FROM birdy_social_users WHERE user_id=:user_id AND provider_type=:provider",
				array(":user_id"=>$this->id,":provider"=>$provider));
			if ($connected==$this->id) return true;
		}
		return false;
    }
    
    private static function saveGeoHelper($user,$what,$user_data) {
		$db = birdyDB::getInstance();
		if (empty($user->$what)) {
			$set 	= $what.'=:'.$what;
			$where 	= 'id=:user_id';
			$args 	= array(":".$what=>$user_data->$what, ":user_id"=>$user_data->user_id);
			$db->update("birdy_geo_users",$set,$where,$args);
		}
    }
    
    public static function saveGeo($user_data) {
		$db = birdyDB::getInstance();
		$user_geo = $db->loadObject("SELECT * FROM birdy_geo_users WHERE user_id=:user_id",array(":user_id"=>$user_data->user_id));
		if (!empty($user_geo)) {
			self::saveGeoHelper($user_geo,'phone',$user_data);
			self::saveGeoHelper($user_geo,'address',$user_data);
			self::saveGeoHelper($user_geo,'country',$user_data);
			self::saveGeoHelper($user_geo,'region',$user_data);
			self::saveGeoHelper($user_geo,'city',$user_data);
			self::saveGeoHelper($user_geo,'zip',$user_data);
		} else {
			$db->insert("birdy_geo_users", array(
				"user_id" 		=> $user_data->user_id,
				"phone"			=> $user_data->phone,
				"address"		=> $user_data->address,
				"country"		=> $user_data->country,
				"region"		=> $user_data->region,
				"city"			=> $user_data->city,
				"zip"			=> $user_data->zip
			));
		}
    }

}
?>
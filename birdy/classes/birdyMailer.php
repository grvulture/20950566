<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
require_once(BIRDY_BASE.DS.'birdy'.DS.'helpers'.DS.'phpmailer'.DS.'PHPMailerAutoload.php');

Class birdyMailer extends PHPMailer {

	function __construct() {
		parent::__construct();
	}
	
	public function Send($html=false) {
		// please look into the config/config.php for much more info on how to use this!
		if (EMAIL_USE_SMTP) {
		    // set PHPMailer to use SMTP
		    $this->IsSMTP();
		    // useful for debugging, shows full SMTP errors, config this in config/config.php
		    $this->SMTPDebug = PHPMAILER_DEBUG_MODE;
		    // enable SMTP authentication
		    $this->SMTPAuth = EMAIL_SMTP_AUTH;
		    // enable encryption, usually SSL/TLS
		    if (defined(EMAIL_SMTP_ENCRYPTION)) {
			$this->SMTPSecure = EMAIL_SMTP_ENCRYPTION;
		    }
		    // set SMTP provider's credentials
		    $this->Host = EMAIL_SMTP_HOST;
		    $this->Username = EMAIL_SMTP_USERNAME;
		    $this->Password = EMAIL_SMTP_PASSWORD;
		    $this->Port = EMAIL_SMTP_PORT;
		}
		elseif (EMAIL_USE_SENDMAIL) {
		    $this->IsSendmail();
		
		} elseif (EMAIL_USE_QMAIL) {
		    $this->IsQmail();
		
		} else {
		    $this->IsMail();
		}

		if ($html) $this->Body = $this->msgHTML($this->Body);
		return parent::send();
	}
	
	public function getUnreadMessages($user_id) {
		$db = birdyDB::getInstance();
		$unread = $db->loadResult("SELECT count(id) FROM birdy_messages WHERE sender=:user_id AND read_sender<>1",
			array(":user_id"=>$user_id));
		$unread += $db->loadResult("SELECT count(id) FROM birdy_messages WHERE receiver=:user_id AND read_receiver<>1",
			array(":user_id"=>$user_id));
		return $unread;
	}
	
}
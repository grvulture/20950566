<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//=============================================================================
	?>
		<a href="/profile"><img src="<?php echo $user->avatar; ?>" style="border:2px solid #DDDDDD;border-radius:150px;width:80px;float:left;" alt="<?php echo $user_credentials; ?>"></a>
		<h4 class="style top1" style="word-wrap: break-word;"><a href="/profile"><?php echo $user_credentials; ?></a></h4>
		<div style="text-align:center;padding:2px;">
		<button onclick="window.location='/friend_requests'" class="klip-submit klip-button"><?php echo $db->loadResult("SELECT count(id) FROM friends WHERE requested=:user_id AND accepted=0",array(":user_id"=>$user->id)); ?> friend requests</button>
		<button class="klip-submit klip-button"><?php echo birdyMailer::getUnreadMessages($user->id); ?> new messages</button>
		<button class="klip-submit klip-button"><?php echo $db->loadResult("SELECT count(id) FROM notifications WHERE user_id=:user_id",array(":user_id"=>$user->id)); ?> notifications</button>
		</div>

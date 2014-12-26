<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
?>
<div class="header_bg">
<div class="wrap">
<div class="wrapper">
	<div class="header">
		<div class="logo">
			<a href="/" style="color: rgb(255, 255, 255); font-family: nexa_boldregular; font-size: 36px;">
				<img src="/images/logo-inverse-small.jpg" style="height: 46px; position: relative; top: -5px; float: left;" alt=""/> 
				Klipsam
			</a>
		</div>
		<div class="cssmenu">
			<ul>
				<?php
				if ($user->isLoggedIn()) {
					$unread = birdyMailer::getUnreadMessages($user->id);
					if ($unread) $unread = '('.$unread.')'; else $unread = '';
					$notifications = $db->loadResult("SELECT count(id) FROM notifications WHERE user_id=:user_id",array(":user_id"=>$user->id));
					if ($notifications) $notifications = '('.$notifications.')'; else $notifications = '';
					$friend_requests = $db->loadResult("SELECT count(id) FROM friends WHERE requested=:user_id AND accepted=0",array(":user_id"=>$user->id));
					if ($friend_requests) $friend_requests= '('.$friend_requests.')'; else $friend_requests = '';
					
					$active = (BIRDY_SEF_URI==''||BIRDY_SEF_URI=='/'||BIRDY_SEF_URI=='/index.php') ? 'class="active"' : '';
					echo '<li '.$active.'><a title="Your Newsfeed" class="home" href="/"></a></li>';

					$active = (strstr(BIRDY_SEF_URI,'friend_requests')) ? 'class="active"' : '';
					if ($birdy->browser=='web') echo '<li '.$active.'><a style="font-size:11px;font-weight:bold" href="/friend_requests">Friend Requests'.$friend_requests.'</a></li>';
					
					$active = (strstr(BIRDY_SEF_URI,'inbox')) ? 'class="active"' : '';
					if ($birdy->browser=='web') echo '<li '.$active.'><a style="font-size:11px;font-weight:bold" href="/inbox">Inbox'.$unread.'</a></li>';
					
					$active = (strstr(BIRDY_SEF_URI,'notifications')) ? 'class="active"' : '';
					if ($birdy->browser=='web') echo '<li '.$active.'><a rel="lightbox" style="font-size:11px;font-weight:bold" href="/notifications">Notifications'.$notifications.'</a></li>';
					
					$active = (strstr(BIRDY_SEF_URI,'klipit')) ? 'class="active"' : '';
					$popup = ($birdy->browser=='web') ? "?popup=1" : "";
					$lightbox = $birdy->browser=='web' ? 'rel="lightbox"' : '';
					echo '<li '.$active.'><a title="Klip something" style="font-weight:bold;font-size:14px;padding-right:10px;" '.$lightbox.' href="'.BIRDY_URL.'klipit'.$popup.'">Klip it!</a></li>';
					
					$user_credentials = birdyConfig::$use_real_name ? trim($user->first_name.' '.$user->last_name) : $user->username;
					if (empty($user_credentials)) $user_credentials = $user->email;
					$active= (strstr(BIRDY_SEF_URI,'profile')) ? 'class="active"' : '';
					echo '<li '.$active.'><a style="font-weight:bold;font-size:17px;padding-right:10px;" href="/profile">'.$user_credentials.'</a></li>';
					
					echo '<li><a title="Logout" href="/logout"><img class="logout_image" alt="Logout" src="'.BIRDY_URL.'images/icons/logout-2-16.png" /></a></li>';
					
				} else {
					$active = (strstr(BIRDY_SEF_URI,'login')) ? 'class="active"' : '';
					if ($birdy->browser=='web') echo '<li '.$active.'><a rel="lightbox" href="/login?popup=1">login/sign up</a></li>';
					else echo '<li '.$active.'><a href="/login">login/sign up</a></li>'; 
				}
				?>
			 </ul>
			<div class="clear"></div>
		</div>
		<div class="clear"></div>
	</div>
</div>
</div>
</div>

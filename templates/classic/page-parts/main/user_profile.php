<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
include_once("klips_sql.php");

/**
* function to show the user profile page
* user: the current logged in user
* klipper: the profile page's user
*/
function userProfilePage($user,$klipper=false) {
	$birdy= birdyCMS::getInstance();
	$db = birdyDB::getInstance();
	//==================================================
	if (!$klipper) {
		echo '<h2 class="style top1">Invalid user!</a>';
		return;
	}
	$user_credentials = birdyConfig::$use_real_name ? trim($klipper->first_name.' '.$klipper->last_name) : $klipper->username;
	if (empty($user_credentials)) $user_credentials = $klipper->email;
	
	$birdy->pageTitle($user_credentials." | Klipsam");
	$birdy->pageDescription($klipper->header.' | Klipsam');
	$birdy->pageImage($klipper->avatar);
	
	$klips = userLeftColumn($user,$klipper);
	
	?>
	<div class="span2_of_3 profile-right">
	<?php
	//if (!empty($_POST['birdyCommentSession'])) birdyComments::submitComment(); // this is already done on "displayComments"
	if ($user->id==$klipper->id && (!empty($_POST['klip-submit']) || (!empty($_POST['klip-done'])&&empty($_REQUEST['done-klipping'])))) {
		include_once(BIRDY_TEMPLATE_BASE.DS.'forms'.DS.'klipit.php'); 
	}
	if (empty($_POST['klip-submit'])) {
		if ($klips) {
			foreach ($klips as $klip) {
				include(BIRDY_TEMPLATE_BASE.DS.'page-parts'.DS.'klip-block.php');
				echo "<div class='clear'></div>";
			}
			//include(BIRDY_TEMPLATE_BASE.DS.'forms'.DS.'klipit.php');
		} elseif ($user->id==$klipper->id) {
		?>
			<h2 class="style list"><a href="javascript:scrollToAnchor('firstjam')">Klip your first jam</a></h2>
			<p class="para top1">In Kipsam you can klip everything you like while you surf. Images, videos, your favorite articles, or even your personal notes.</p>
			<p class="para top">Furthermore, you can upload your own pictures, <!--MP3s and videos, -->and personalize them with titles and descriptions.</p>
			<p class="para top">Keep it organized in nested tags (tags, sub-tags, etc.), so you won't get lost in your own jam.</p>
			<p>&nbsp;</p>
			<p id="firstjam">Start by klipping your first jam now:</p>
			<form action="" method="POST" enctype='multipart/form-data'>
			<label class="tooltip"><input type="text" class="textbox klip-from-web" name="klip-url" placeholder="Klip it from the web" /><?php echo $birdy->tooltip("Enter URL to klip"); ?></label>
			<div class="ui divider">
			<div class="ui or theme-main-bg theme-bright-fg theme-bright-border">or</div>
			</div>
			<p class="info">upload a photo</p>
			<input type="file" class="klip-from-disk" name="klip-file" placeholder="Klip it from my disk" />
			<div class="ui divider">
			<div class="ui or theme-main-bg theme-bright-fg theme-bright-border">or</div>
			</div>
			<label class="tooltip">
				<textarea class="textbox klip-textarea" name="klip-note" placeholder="Klip a personal note" /></textarea>
				<?php echo $birdy->tooltip("BBCode allowed!"); ?>
			</label>
			<p class="para top" style="font-size: 15px; font-weight: bold;">Happy Klipping! <img src="<?php echo BIRDY_URL.'images/loading.gif'; ?>" id="loading_image" style="display:none;float:right;padding-left:5px;" /><input type="submit" class="klip-submit" name="klip-submit" value="Klip it!" /></p>
			<input type="hidden" name="edit" value="0" />
			</form>
		<?php
		}
	}
	?>
	</div>
	</div>
	<div class="clear"></div>		<p>&nbsp;</p>
	<?php
}

function userLeftColumn($user,$klipper) {
	$birdy= birdyCMS::getInstance();
	$db = birdyDB::getInstance();
	//==================================================
	$user_credentials = birdyConfig::$use_real_name ? trim($klipper->first_name.' '.$klipper->last_name) : $klipper->username;
	if (empty($user_credentials)) $user_credentials = $klipper->email;

	$popup = ($birdy->browser=='web') ? "?popup=1" : "";
	$lightbox = ($birdy->browser=='web') ? 'rel="lightbox"' : '';
	
	$user_heading = empty($klipper->header)&&($user->id==$klipper->id) ? '<a '.$lightbox.' title="Update your profile info" href="'.BIRDY_URL.'editProfile'.$popup.'">Enter a catching header for your profile</a>' : $klipper->header;
	$user_about   = empty($klipper->about)&&($user->id==$klipper->id) ? '<a '.$lightbox.' title="Update your profile info" href="'.BIRDY_URL.'editProfile'.$popup.'">Enter a short bio of you</a>' : $klipper->about;
	$aboutmestyle = (empty($klipper->about)&&($user->id==$klipper->id))||strlen($user_about)<150 ? 'text-align:center;' : 'text-align:justify;';

	$addfriend = $db->loadResult("SELECT id FROM friends WHERE 
	(requester=:user AND requested=:requested) OR (requester=:requested AND requested=:user)",
	array(":user"=>$user->id, ":requested"=>$klipper->id));
	$addfriend = empty($addfriend) ? '+ Add friend' : 'Un-friend';

	$follow = $db->loadResult("SELECT id FROM following WHERE user_id=:user AND following=:requested",array(":user"=>$user->id, ":requested"=>$klipper->id));
	$follow = empty($follow) ? 'Follow' : 'Un-follow';
	?>
	<div class="span3">
	<div class="span1_of_3 profile-left">
		<?php if ($user->id==$klipper->id) { ?>
			<a rel="lightbox" title="Change your Profile Picture" href="<?php echo BIRDY_URL; ?>uploadAvatar">
			<h2 class="style top1" style="word-wrap: break-word;"><a <?php echo $lightbox; ?> title="Update your profile info" href="<?php echo BIRDY_URL; ?>editProfile<?php echo $popup; ?>"><?php echo $user_credentials; ?></a></h2>
		<?php } ?>
			<img src="<?php echo $klipper->avatar; ?>" style="border:2px solid #DDDDDD;border-radius:150px" alt="<?php echo $user_credentials; ?>">
			<h2 class="style top1" style="word-wrap: break-word;"><a><?php echo $user_credentials; ?></a></h2>
		<?php if ($user->id==$klipper->id) { ?>
			</a>
		<?php } ?>
		<h5 class="style"><?php echo $user_heading; ?></h5>
		<p class="para info" style="font-size:11px;padding:10px;<?php echo $aboutmestyle; ?>"><?php echo $user_about; ?></p>
		<div class="ui divider" style="margin:10px 0">
		</div>
		<?php if ($user->id==$klipper->id) {
					if (!$user->isConnectedWith('facebook')) { ?>
							<button id="facebookLogin" class="button facebook ui">
							<i class="social-facebook"></i>
							<img src="<?php echo BIRDY_URL; ?>images/facebook-white.png" style="float:left;" />
							<span style="float:right;">Connect with
							Facebook</span>
							</button>
					<?php }
					if (!$user->isConnectedWith('twitter')) { ?>
							<button class="button facebook twitter ui" id="twitterLogin">
							<i class="social-twitter"></i>
							<img src="<?php echo BIRDY_URL; ?>images/twitter-white.png" style="float:left;" />
							<span style="float:right;">Connect with
							Twitter</span>
							</button>
					<?php }
					if (!$user->isConnectedWith('google')) { ?>
							<button class="button google_oauth2 ui" id="googleLogin">
							<i class="social-google"></i>
							<img src="<?php echo BIRDY_URL; ?>images/googleplus.png" style="float:left;" />
							<span style="float:right;">Connect with
							Google</span>
							</button>
					<?php } ?>
		<div class="ui divider" style="margin:10px 0">
		</div>
		<?php } ?>
		<div style="text-align:center;">
		<button onclick="window.location='/followers/klipper/<?php echo $klipper->id; ?>'" class="klip-submit klip-button"><?php echo $db->loadResult("SELECT count(id) FROM following WHERE following=:user_id",array(":user_id"=>$klipper->id)); ?> followers</button>
		<button onclick="window.location='/friends/klipper/<?php echo $klipper->id; ?>'" class="klip-submit klip-button"><?php echo $db->loadResult("SELECT count(id) FROM friends WHERE (requester=:user_id OR requested=:user_id) AND accepted=1",array(":user_id"=>$klipper->id)); ?> friends</button>
		<button onclick="window.location='/following/klipper/<?php echo $klipper->id; ?>'" class="klip-submit klip-button"><?php echo $db->loadResult("SELECT count(id) FROM following WHERE user_id=:user_id",array(":user_id"=>$klipper->id)); ?> following</button>
		</div>
		<div class="ui divider" style="margin:10px 0">
		</div>
		<div style="text-align:center;">
		<?php if ($user->id!=$klipper->id) { ?>
		<a rel="lightbox" href="/add/friend/<?php echo $klipper->id; ?>" id="addFriend<?php echo $klipper->id; ?>" onclick="changeFriend(<?php echo $klipper->id; ?>)" class="klip-submit klip-button" style="font-size:12px;background:#EFEFEF;"><?php echo $addfriend; ?></a>
		<a rel="lightbox" href="/add/follow/<?php echo $klipper->id; ?>" id="addFollow<?php echo $klipper->id; ?>" onclick="changeFollow(<?php echo $klipper->id; ?>)" class="klip-submit klip-button" style="font-size:12px;background:#EFEFEF;"><?php echo $follow; ?></a>
		<a <?php echo $lightbox; ?> href="/message/new/<?php echo $klipper->id.$popup; ?>" class="klip-submit klip-button" style="font-size:12px;background:#EFEFEF;">Message</a>
		<p style="line-height:15px;">&nbsp;</p>
		<!--
		<button class="klip-submit klip-button" style="font-size:12px;background:#EFEFEF;">Add/Remove from lists...</button>
		<button class="klip-submit klip-button" style="font-size:12px;background:#EFEFEF;">Klip on this Klipper</button>
		-->
		<a rel="lightbox" href="/block/klipper/<?php echo $klipper->id; ?>" id="block<?php echo $klipper->id; ?>" class="klip-submit klip-button" onclick="changeBlock(<?php echo $klipper->id; ?>)" style="font-size:12px;background:#EFEFEF;">Block this Klipper</button>
		<a rel="lightbox" href="/report/type/klipper/id/<?php echo $klipper->id; ?>" class="klip-submit klip-button" style="font-size:12px;background:#EFEFEF;">Report this Klipper</a>
		<?php } else { ?>
		<button onclick="window.location='/friend_requests'" class="klip-submit klip-button" style="font-size:12px;background:#EFEFEF;"><?php echo $db->loadResult("SELECT count(id) FROM friends WHERE requested=:user_id AND accepted=0",array(":user_id"=>$user->id)); ?> friend requests</button>
		<button class="klip-submit klip-button" style="font-size:12px;background:#EFEFEF;"><?php echo birdyMailer::getUnreadMessages($user->id); ?> new messages</button>
		<button class="klip-submit klip-button" style="font-size:12px;background:#EFEFEF;"><?php echo $db->loadResult("SELECT count(id) FROM notifications WHERE user_id=:user_id",array(":user_id"=>$user->id)); ?> notifications</button>
		<?php } ?>
		</div>
		<div class="ui divider" style="margin:10px 0">
		</div>
		<?php 
		$klips_sql = klips_sql($klipper);
		$klips = $db->loadObjectlist("SELECT * FROM klips WHERE ".$klips_sql[0]." ORDER BY id DESC",$klips_sql[1]);
		echo left_column('profile',$klips); 
		?>
	</div>
	<?php
	return $klips;
}

function left_column($page,$klips) {
	$birdy = birdyCMS::getInstance();
	$db = birdyDB::getInstance();
	$user = birdyUser::getInstance();
	$total_klips = count($klips);
	$type = array();
	foreach ($klips as $klip) {
		$type[$klip->type][] = 1;
	}
	ksort($type);
	$parent_tags = array();
	foreach ($klips as $klip) {
		if ($klip->parent_tag>0) {
			$parent = $db->loadResult("SELECT tag FROM tags WHERE id=:id",array(":id"=>$klip->parent_tag));
			if (!empty($parent)) $parent_tags[$parent][] = $klip->parent_tag;
		}
	}
	ksort($parent_tags);
	$tags = array();
	foreach ($klips as $klip) {
		if ($klip->tags!='') {
			if (strstr($klip->tags,",")) $klip_tags = explode(",",$klip->tags);
			else $klip_tags = array($klip->tags);
			foreach($klip_tags as $tag) {
				$tag_id = $db->loadResult("SELECT id FROM tags WHERE tag=:tag",array(":tag"=>ucfirst($tag)));
				if (!empty($tag_id)) $tags[$tag][] = $tag_id;
			}
		}
	}
	ksort($tags);
	?>
		<div style="text-align:center;">
		<button class="klip-submit"><?php echo $total_klips; ?> <?php echo $total_klips>1 ? "total" : ""; ?> klip<?php echo $total_klips>1 ? "s" : ""; ?></button>
		<?php
		foreach ($type as $klip_type => $value) {
			echo '<button onclick="window.location=\'?type='.(string)$klip_type.'\'" class="klip-submit klip-button" style="font-size:12px;">'.count($type[$klip_type]).' '.(string)$klip_type;
			if (count($type[$klip_type])>1) echo "s".'</button>';
		}
		?>
		</div>
		<div class="clear"></div>
		<div class="ui divider">
		</div>
		<div class="hidden">
			<div style="text-align:center;">
			<button class="klip-submit"><?php echo count($parent_tags); ?> parent tag<?php echo count($parent_tags)>1 ? "s" : ""; ?></button>
			<?php
			foreach ($parent_tags as $klip_type => $value) {
				//if (!isset($_SESSION['request_parent_tag'][$parent_tags[$klip_type][0]]) 
				//|| $_SESSION['request_parent_tag'][$parent_tags[$klip_type][0]]!=(string)$klip_type) 
					echo '<button onclick="window.location=\'?parent_tag='.$parent_tags[$klip_type][0].'\'" class="klip-submit klip-button" style="font-size:12px;">'.ucfirst((string)$klip_type).'</button>';
			}
			?>
			</div>
			<div class="clear"></div>
			<div class="ui divider">
			</div>
			<div style="text-align:center;">
			<button class="klip-submit"><?php echo count($tags); ?> tag<?php echo count($tags)>1 ? "s" : ""; ?></button>
			<?php
			foreach ($tags as $klip_type => $value) {
				//if (!isset($_SESSION['request_tag'][$tags[$klip_type][0]]) 
				//|| $_SESSION['request_tag'][$tags[$klip_type][0]]!=(string)$klip_type) 
					echo '<button onclick="window.location=\'?tag='.$tags[$klip_type][0].'\'" class="klip-submit klip-button" style="font-size:12px;">'.ucfirst((string)$klip_type).'</button>';
			}
			?>
			</div>
			<div class="clear"></div>
		</div>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
	<?php
}
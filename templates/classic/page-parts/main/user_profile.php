<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
include_once("klips_sql.php");

//echo "<pre>";print_r($klipper);echo "</pre>";
//============================================================================
//HERE IS A GOOD PLACE TO SHOW MODERATOR MESSAGES
$suspendbutton = "Suspend Klipper for now";
$banbutton = "Ban Klipper forever";
$confirmbutton = "Unconfirm registration of Klipper";
$warn = 'reward';
$reportbutton = '';
// if we are not on our own profile...
if ($birdy->current_page=="klipper") {
	// first make sure we do not allow views from others if suspended/banned/unconfirmed...
	if ($klipper->account_type < 1 || $klipper->suspended==1) {
		//suspended?
		if ($klipper->suspended== 1) {
			$birdy->outputWarning("This Klipper has been suspended until further notice.");
			$suspendbutton = "Unsuspend Klipper";
			$warn = 'warn';
		}
		//banned?
		if ($klipper->account_type == -1) {
			$birdy->outputWarning("This Klipper has been banned!");
			$banbutton = "Unban Klipper";
			$warn = false;
		//unconfirmed?
		} elseif ($klipper->account_type==0) {
			$birdy->outputWarning("This Klipper is unconfirmed and is not allowed to interact with other klippers.");
			$confirmbutton = "Confirm registration of Klipper";
			$warn = 'warn';
		}
		//redirect non-moderators
		if ($user->account_type < 2) {
			if (!($_SERVER['HTTP_REFERER']=='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'])) {
				$birdy->loadPage('/');
			} else {
				$birdy->loadPage('/profile');
			}
		}
	}
	// now the moderators...
	if ($user->account_type>1) {
		if ($klipper->reports >= _MAX_REPORTS) {
			$birdy->outputWarning("This Klipper has been reported: ".$klipper->reports.' time(s)! Please take action');
			$reportbutton = '<button style="font-size:17px;" onclick="window.location=\'/showreports/report_id/'.$klipper->id.'\'" class="klip-submit klip-button">Show reports</button>';
			$warn = 'warn';
		}
		?>
		<h2>
			<p style="background:#E9E9E9; text-align: center;">
			<button style="font-size:17px;" onclick="window.location='/suspend/klipper/<?php echo $klipper->id; ?>'" class="klip-submit klip-button"><?php echo $suspendbutton; ?></button>
			<button style="font-size:17px;" onclick="window.location='/ban/klipper/<?php echo $klipper->id; ?>'" class="klip-submit klip-button"><?php echo $banbutton; ?></button>
			<button style="font-size:17px;" onclick="window.location='/confirm/klipper/<?php echo $klipper->id; ?>'" class="klip-submit klip-button"><?php echo $confirmbutton; ?></button>
			<?php
			if ($warn) echo '<button style="font-size:17px;" onclick="window.location=\'/'.$warn.'/klipper/'.$klipper->id.'\'" class="klip-submit klip-button">'.ucfirst($warn).' Klipper</button>';
			echo $reportbutton;
			?>
			</p>
			<p style="background:#E9E9E9; padding-top: 5px;"></p>
		</h2>
		<p><br /></p>
		<?php
	}
}

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
	<div id="result" class="span2_of_3 profile-right">
	<?php
	//if (!empty($_POST['birdyCommentSession'])) birdyComments::submitComment(); // this is already done on "displayComments"
	if ($user->id==$klipper->id && (!empty($_POST['klip-submit']) || (!empty($_POST['klip-done'])&&empty($_REQUEST['done-klipping'])))) {
		include_once(BIRDY_TEMPLATE_BASE.DS.'forms'.DS.'klipit.php'); 
	}
	if (empty($_POST['klip-submit'])) {
		if ($klips) {
			$total = count($klips);
			$actual_row_count = $total<$birdy->total_users ? $total : $birdy->total_users;
			for ($i=0; $i<$actual_row_count; $i++) {
				if (isset($klips[$i])) {
					$klip = $klips[$i];
					include(BIRDY_TEMPLATE_BASE.DS.'page-parts'.DS.'klip-block.php');
					echo "<div class='clear'></div>";
				}
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

	$block = $db->loadResult("SELECT id FROM blocks WHERE blocker=:user AND blocked=:requested",array(":user"=>$user->id, ":requested"=>$klipper->id));
	$block = empty($block) ? 'Block this Klipper' : 'Un-block';
	?>
	<div class="span3">
	<div class="span1_of_3 profile-left">

		<?php if ($user->id==$klipper->id) { ?>
			<a <?php echo $lightbox; ?> title="Change your Profile Picture" href="<?php echo BIRDY_URL; ?>uploadAvatar">
				<img src="<?php echo $klipper->avatar; ?>" style="border:2px solid #DDDDDD;border-radius:150px" alt="<?php echo $user_credentials; ?>">
			</a>
			<h2 class="style top1" style="word-wrap: break-word;"><a <?php echo $lightbox; ?> title="Update your profile info" href="<?php echo BIRDY_URL; ?>editProfile<?php echo $popup; ?>"><?php echo $user_credentials; ?></a></h2>

		<?php } else { ?>

			<img src="<?php echo $klipper->avatar; ?>" style="border:2px solid #DDDDDD;border-radius:150px" alt="<?php echo $user_credentials; ?>">
			<h2 class="style top1" style="word-wrap: break-word;"><a><?php echo $user_credentials; ?></a></h2>
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
		<a rel="lightbox" href="/block/klipper/<?php echo $klipper->id; ?>" id="block<?php echo $klipper->id; ?>" onclick="changeBlock(<?php echo $klipper->id.',\''.$block.'\''; ?>)" class="klip-submit klip-button" style="font-size:12px;background:#EFEFEF;"><?php echo $block; ?></button>
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
		$klips = left_column('profile',$klipper); 
		?>
	</div>
	<?php
	return $klips;
}

function left_column($page,$klipper=false) {
	$birdy = birdyCMS::getInstance();
	$db = birdyDB::getInstance();
	$user = birdyUser::getInstance();
	$type = array();
	$klips_sql = klips_sql($klipper);
	//$klips = $db->loadObjectlist("SELECT * FROM klips WHERE privacy=:privacy ORDER BY id DESC",array(":privacy"=>0));
	// @TODO: fix privacy of klips!
	$klips = $db->loadObjectlist("SELECT * FROM klips WHERE ".$klips_sql[0]." ORDER BY id DESC",$klips_sql[1]);
	$total_klips = count($klips);

	if (isset($_SESSION['klipper_search'])) {
		echo "<h2 class='style' style='font-size:8px'><a>Search results: <small style='font-size:small'>(klippers)</small><br />".$_SESSION['klipper_search']['query']."</a>
			<a class='klip-submit klip-button remove-filter' style='float:none;display:block !important;' href='?remove_filter=klippers'>remove this search</a></h2>
			</h2>";
	} else {
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
		if (isset($_SESSION['klipper_search']) && $page!='profile') {
			$total = count($_SESSION['klipper_search']);
			$actual_row_count = $total<$birdy->total_users ? $total : $birdy->total_users;
		} elseif ($klips) {
			$total = count($klips);
			$actual_row_count = $total<$birdy->total_users ? $total : $birdy->total_users;
			//echo "TOTAL: $total <br /> ACTUAL_ROW_COUNT: $actual_row_count <br /> BIRDY USERS: ".$birdy->total_users." <br /> ";
		}

		//make sure SESSION has escaped doube quotes
		$SESSION = $birdy->recur_replace('"','dipla-eisag',$_SESSION);

		$birdy->addScriptToBottom('
            var page = 1;


            $(window).scroll(function () {
                if($(window).scrollTop() + $(window).height() == $(document).height()) {
                    $("html, body").css("cursor", "wait");
                    page++;
                    var php_page = "'.$page.'";
                    var actual_count = "'.$actual_row_count.'";
                    var total = "'.$total.'";
                    var ajax_session = \''.json_encode($SESSION).'\';
                    var klips_sql0 = \''.$klips_sql[0].'\';
                    var klips_sql1 = \''.json_encode($klips_sql[1]).'\';
                    var data = {
                    	php_page: php_page,
                        page_num: page,
                        actual_count: actual_count,
                        ajax_session: ajax_session,
                        klips_sql0: klips_sql0,
                        klips_sql1: klips_sql1,
                        birdy_ajax: "ajax_klip_blocks.php" //THIS IS NEEDED TO TRIGGER AJAX IN BIRDY AND TO KNOW WHICH PAGE TO LOAD IN /plugins/_ajax_responses/
                    };

                    if((page-1)* actual_count > total || total==actual_count){
                        $("html, body").css("cursor", "auto");
                    }else{
                        $.ajax({
                            type: "POST",
                            url: "'.BIRDY_URL.'/index.php",
                            data:data,
                            success: function(res) {
		                    	$("html, body").css("cursor", "auto");
                                $("#result").append(res);
								$("img.lazy").lazyload({
								    //effect : "fadeIn",
								    threshold : 200,
								    skip_invisible : false
								});
                            }
                        });
                    }

                }


            });
		');
	return $klips;
}
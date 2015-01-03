<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
$klip = $db->loadObject("SELECT * FROM klips WHERE id=:id",array(":id"=>$_REQUEST['klip_id']));
if (empty($klip) || ($klip->reports >= _MAX_REPORTS && ($user->account_type < 2 || !$user->isLoggedIn()))) {
	$birdy->outputWarning("klip->reports:".$klip->reports." _MAX_REPORTS:"._MAX_REPORTS." user->account_type:".$user->account_type.' user->isLoggedIn:'.$user->isLoggedIn);
	$birdy->outputWarning("Klip not found, or you don't have enough privileges to access it!");
	if (!($_SERVER['HTTP_REFERER']=='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'])) {
		$birdy->loadPage('/');
	} else {
		$birdy->loadPage('/profile');
	}
}
if ($klip->reports >= _MAX_REPORTS && ($user->account_type > 1 || $user->id==$klip->user_id)) {
	$birdy->outputWarning("This klip has been reported: ".$klip->reports.' time(s)! It is now offline and waits moderation');
}
if ($user->account_type>1) {
	
}

	$klip_user = birdyUser::getInstance($klip->user_id);
	
	$klip_parent_tag = ($klip->parent_tag) ? $db->loadResult("SELECT tag FROM tags WHERE id=:parent_tag",array(":parent_tag"=>$klip->parent_tag)) : "";
	$parent_tag = (empty($klip_parent_tag)) ? "" : 'Parent Tag: <a class="tag_link" href="'.$klip->parent_tag.'">'.stripslashes($klip_parent_tag).'</a>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;';
	$title = stripslashes($klip->title);
	$descr = stripslashes($klip->description);
	$what  = $klip->type;

$birdy->pageTitle($title." | Klipsam");
$birdy->pageDescription($descr.' | Klipsam');
$birdy->pageImage($klip->thumbnail);

	$klip_url = '/klip/'.$klip->id.'/'.str_replace(array("/"," "),"_",$title);
	//
	$tags  = $klip->tags;
	if (!empty($tags)) {
		$klip->tags = explode(",",$tags);
		//print_r($tags);
		if (!empty($tags)) {
			$tag_array = array();
			foreach ($klip->tags as $tag) {
				$tag = str_replace("\\","",$tag);
				$tag_id = $db->loadResult("SELECT id FROM tags WHERE tag=:tag",array(":tag"=>$tag));
				$tag_array[] = '<a class="tag_link" href="'.$tag_id.'">'.stripslashes($tag).'</a>';
			}
			$tags = " tags: <span class='tags'>".implode(", ",$tag_array)."</span>";
		}
	}
	//echo "USER: ".$user->id." LOG:".$user->isLoggedIn();
	$comments = birdyComments::getComments($klip->id);
	$comment_count = count($comments);
	if ($comment_count>0) {
		$comment_info = $comment_count.' comment';
		if ($comment_count>1) $comment_info.='s';
	} else $comment_info = "Comment on this";
	//
	$lightbox = ($birdy->browser=='web') ? 'rel="lightbox"' : '';
	//if (!$birdy->url_exists($klip->bignail)) $klip->bignail = $klip->thumbnail;
	$klip_image_link = ($birdy->browser=='web') ? $klip->bignail : $klip_url;

	$favorite = $db->loadResult("SELECT id FROM favorites WHERE user_id=:current_user AND klip_id=:klip_id",
					array(":current_user"=>$user->id, ":klip_id"=>$klip->id));
	$favorites = $db->loadResult("SELECT count(id) FROM favorites WHERE klip_id=:klip_id",
					array(":klip_id"=>$klip->id));
	$favoritecount 	= ($favorites) ? " <span id='favoritecount".$klip->id."'>(".$favorites.")</span>" : "";
	$favoritecount 	= ($favorite) ? str_replace("<span","<span style='color:#FFAC33'",$favoritecount) : $favoritecount;
	$favoriteicon 	= ($favorite) ? "<img id='favoriteicon".$klip->id."' src='/images/favorite.png' style='width:10px' />" : "<img id='favoriteicon".$klip->id."' src='/images/favorite.png' style='width:10px;display:none' />";
	$favoritestyle 	= ($favorite) ? "style='font-size:12px;color:#FFAC33'" : 'style="font-size:12px;background:#EFEFEF;"';
	$favoritetext 	= ($favorite) ? "Approved" : "Approve";
	$favorite 		= '<a id="favorite'.$klip->id.'" href="javascript:change_favorite('.$klip->id.')" class="klip-submit klip-button klip-page-actions" '.$favoritestyle.'>'.$favoriteicon.' '.$favoritetext.'</a>';
	
	$reklips = $db->loadResult("SELECT count(id) FROM klips WHERE reklip=:id",array(":id"=>$klip->id));
	$klip_bignail = ($what=='video') ? str_replace("&feature=youtube_gdata_player","",str_replace("https://","http://",$klip->url)) : $klip->bignail;
	$klip_height  = ($what=='video') ? 'width:100%;height:55vh' : 'width:75%;/*height:75vh*/';
	$klip_relative= ($what=='video') ? 'position:relative;' : 'text-align:center;';
	$klip_frame   = ($what=='video') ? '<iframe scrollbars="no" style="'.$klip_height.'" src="'.$klip->bignail.'"></iframe>' : '<img style="'.$klip_height.'" src="'.$klip->bignail.'" />';

		$gotoTitle = ($klip->type=="klip-note" || $klip->type=="klip-photo") ? "Go to Klipper's page" : "Go to original page"; 
		$gotoTarget= ($klip->type=="klip-note" || $klip->type=="klip-photo") ? "" : 'target="_blank"';

		if (!$klip->author) {
			$site_author = parse_url($klip->url);
			$klip->author = $site_author['host'];
		}
		if ($klip->author) {
			$klip_author='
			<div style="width:100%;text-align:center;">
			<img src="http://getfavicon.appspot.com/'.rawurlencode($klip->url).'" style="width:12px;position:relative;top:2px;padding-left:3px;" /> 
			<a href="'.$klip->url.'" class="info author-link" style="text-align:center;width:25%;float:none;" '.$gotoTarget.'>'
			.$klip->author.'</a>
			</div>
			<div class="clear"></div>';
		} else {
			$klip_author='';
		}

		if ($klip->reklip) {
			$reklip = $db->loadObject("SELECT title,user_id FROM klips WHERE id=:reklip",array(":reklip"=>$klip->reklip));
			if (isset($reklip->user_id)) $reklipper = birdyUser::getInstance($reklip->user_id);
			if (isset($reklip->title)) $reklip_url = '/klip/'.$klip->reklip.'/'.str_replace(array("/"," "),"_",stripslashes($reklip->title));
			if (isset($reklipper)) $reklip_info ='
			<a href="'.$reklip_url.'" style="font-size:11px;color:#0066FF;">original klip by '.$reklipper->used_name.'</a>
			'; else $reklip_info = '';
		} else {
			$reklip_info = '';
		}

	if ($klip->type=="klip-photo" || $klip->type=="klip-note") {
		$klip_image_style = 'border-radius:100px;'; 
		$lightbox = '<a href="/klipper/'.$klip_user->username.'">';
		$klip->thumbnail = $klip_user->avatar;
	} else {
		$klip_image_style = 'border-radius:15px;';
		$lightbox = '<a rel="lightbox" href="'.$klip_bignail.'" '.$lightbox.'>';
	}
?>
<div class="span3">
	<div class="span1_of_3 profile-left">
		<?php echo $lightbox; ?><img src="<?php echo $klip->thumbnail; ?>" style="border:2px solid #DDDDDD;<?php echo $klip_image_style; ?>" alt="<?php echo $title; ?>"></a>
		<div class="ui divider" style="margin:10px 0">
		</div>
		<div style="text-align:center;">
		<button class="klip-submit klip-button"><?php echo $favorites; ?> approvals</button>
		<a href="javascript:scrollToAnchor('comments<?php echo $klip->id; ?>')" class="klip-submit klip-button klip-page-actions"><?php echo $comment_count; ?> comments</a>
		<button class="klip-submit klip-button"><?php echo $reklips; ?> reklips</button>
		</div>
		<div class="ui divider" style="margin:10px 0">
		</div>
		<div style="text-align:center;">
		<a href="/reklip/klip/<?php echo $klip->id; ?>" rel="lightbox" class="klip-submit klip-button klip-page-actions" style="font-size:12px;background:#EFEFEF;">Reklip</a>
		<a href="javascript:scrollToAnchor('commentform<?php echo $klip->id; ?>')" class="klip-submit klip-button klip-page-actions" style="font-size:12px;background:#EFEFEF;">Comment</a>
		<a href="/share/klip/<?php echo $klip->id; ?>" rel="lightbox" class="klip-submit klip-button klip-page-actions" style="font-size:12px;background:#EFEFEF;">Share</a>
		<p>&nbsp;</p>
		<?php echo $favorite; ?>
		<?php if ($user->id!=$klip->user_id) { ?><a href="/report/klip/<?php echo $klip->id; ?>" rel="lightbox" class="klip-submit klip-button klip-page-actions" style="font-size:12px;background:#EFEFEF;">Report</a><?php } ?>
		<?php if ($user->id==$klip->user_id) { ?>
		<p>&nbsp;</p>
		<a href="/delete/klip/<?php echo $klip->id; ?>" rel="lightbox" class="klip-submit klip-button klip-page-actions" style="font-size:12px;background:#EFEFEF;">Delete</a>
		<a href="/klipit/klip/<?php echo $klip->id; ?>" class="klip-submit klip-button klip-page-actions" style="font-size:12px;background:#EFEFEF;">Edit</a>
		<?php } ?>
		</div>
		<div class="ui divider" style="margin:10px 0">
		</div>
		<div style="text-align:center;">
		<?php if (!empty($klip_parent_tag)) { ?>
		<div class="info">parent tag</div>
		<p>&nbsp;</p>
		<p>
		<?php echo '<a class="klip-submit klip-button klip-page-actions" href="/?tag='.$klip->parent_tag.'" style="font-size:12px;">'.$klip_parent_tag.'</a>'; ?>
		</p>
		<div class="ui divider">
		</div>
		<?php } ?>
		<?php if (!empty($klip->tags)) { ?>
		<div class="info"><?php echo  count($klip->tags); ?> tag
		<?php if (count($klip->tags)>1) echo "s"; ?>
		</div>
		<?php } ?>
		<p>&nbsp;</p>
		<p>
		<?php
		if (!empty($klip->tags)) {
			foreach($klip->tags as $tag) {
				$tag = str_replace("\\","",$tag);
				$tag_id = $db->loadResult("SELECT id FROM tags WHERE tag=:tag",array(":tag"=>$tag));
				$tag = stripslashes($tag);
				echo '<a class="klip-submit klip-button klip-page-actions" style="font-size:11px;" href="/?tag='.$tag_id.'">'.$tag.'</a>';
			}
		}
		?>
		</p>
		<p>&nbsp;</p>
		</div>
		<div class="ui divider" style="margin:10px 0">
		</div>
	</div>
	<div class="span2_of_3 profile-right">
		<h2 class="style top1" style="word-wrap: break-word;"><a title="<?php echo $gotoTitle; ?>" <?php echo $gotoTarget; ?> href="<?php echo $klip->url; ?>"><?php echo $title; ?></a></h2>
		<?php echo $klip_author; ?>
		<h5 class="style" style="line-height:1;text-align:center">
		<?php echo $reklip_info; ?><br />
			<?php //echo $parent_tag; ?>
			<span class="para info" style="font-size:11px;">
			<?php //echo $tags; ?>
			</span>
		<span class="para info" style="font-size:11px;">
		<?php
		echo "<span style='font-size:12px;'>";
		if (($klip->type!="klip-note" && $klip->type!="klip-photo") || ($user->isLoggedIn() && !strstr($title,$user->used_name))) echo "by <a href='".BIRDY_URL."klipper/".$klip_user->username."'>".stripslashes($klip_user->used_name)."</a>";
		/*if ($birdy->browser=='web')*/ echo " on <a href='/?date=".strtotime($klip->creation_date)."'>".date("F d, Y",strtotime($klip->creation_date))."</a>";
		echo "</span>";
		?>
		</span>
		</h5>
		<div style="margin-top:10px;width:100%;">
		<div style="<?php echo $klip_relative; ?>">
		<?php //if ($klip->type!="klip-note" && $klip->type!="klip-photo") echo rawurldecode(str_replace("http://www.klipsam.com/loadinFrame.php?url=","",$klip_frame)); ?>
		</div>
		</div>
		<div class="clear"></div>
		<div style="margin-top:10px;width:100%;">
		<?php 
		if ($klip->type=="klip-photo") {
			$src = (string) reset(simplexml_import_dom(DOMDocument::loadHTML($descr))->xpath("//img/@src"));
			$descr = '<a rel="lightbox" style="color:#333;" href="'.$src.'">'.$descr.'</a>';
			$info_class = 'center';
		} else {
			$info_class = 'info';
		}
		$content = '<div style="text-align:justify;">';
		$content.='<div class="'.$info_class.' klip-big-description">'.$descr.'</div>';
		$content.='</div>';
		echo $content;
		?>
		<br />
		Comments
		<?php if ($klip->type!='klip-photo' && $klip->type!='klip-note') { ?>
			<a target="_blank" class="klip-submit klip-page-actions" style="font-size:13px;text-align:center;" href="<?php echo $klip->url; ?>">go to original page 
			<!--<img src="/images/arrow_right.png" style="width:12px;"/>-->
			</a>
		<?php } ?>
		</div>
		<div class="clear"></div><br />
		<?php
		$comments_style_div = 'block';
		include "comments.php";
		?>
	</div>
</div>
<div class="clear"></div>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>

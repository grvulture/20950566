<?php
defined('_BIRDY') or die(__FILE__.': Restricted access');
//============================================================================
if (!$user->isLoggedIn()) $user->id=0;

	$klip_user = birdyUser::getInstance($klip->user_id);
	
	$parent_tag = ($klip->parent_tag) ? $db->loadResult("SELECT tag FROM tags WHERE id=:parent_tag",array(":parent_tag"=>$klip->parent_tag)) : "";
	$parent_tag = (empty($parent_tag)) ? "" : '<div style="float:left;">Parent Tag: <a class="tag_link" href="/?parent_tag='.$klip->parent_tag.'">'.$parent_tag.'</a></div>';
	$title = stripslashes($klip->title);
	$descr = stripslashes($klip->description);
	$what  = $klip->type;
	if ($what=='klip-note' || $what=='klip-photo') $klip->thumbnail = $klip_user->avatar;
	if ($what=='video') $klip->bignail = str_replace("&feature=youtube_gdata_player","",str_replace("https://","http://",$klip->url));
	$klip_url = '/klip/'.$klip->id.'/'.str_replace(array("/"," "),"_",$title);
	//
	$tags  = $klip->tags;
	if (!empty($tags)) {
		$tags = explode(",",$tags);
		//print_r($tags);
		if (!empty($tags)) {
			$tag_array = array();
			foreach ($tags as $tag) {
				$tag = str_replace("\\","",$tag);
				$tag_id = $db->loadResult("SELECT id FROM tags WHERE tag=:tag",array(":tag"=>$tag));
				$tag_array[] = '<a class="tag_link" href="/?tag='.$tag_id.'">'.stripslashes($tag).'</a>';
			}
			//if (!empty($parent_tag)) $tags = "|&nbsp;&nbsp;&nbsp;"; else $tags = '';
			$tags= "<div style='float:right;'>tags: <span class='tags'>".implode(", ",$tag_array)."</span></div>";
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
	//
	$repost 		= '<a href="/reklip/klip/'.$klip->id.'" class="add1-actions" rel="lightbox">ReKlipIt</a>';
	$share  		= '<a href="/share/klip/'.$klip->id.'" class="add1-actions" rel="lightbox">Share</a>';
	$favorite = $db->loadResult("SELECT id FROM favorites WHERE user_id=:current_user AND klip_id=:klip_id",
					array(":current_user"=>$user->id, ":klip_id"=>$klip->id));
	$favorites = $db->loadResult("SELECT count(id) FROM favorites WHERE klip_id=:klip_id",
					array(":klip_id"=>$klip->id));
	$favoritecount 	= ($favorites) ? " <span id='favoritecount".$klip->id."'>(".$favorites.")</span>" : "";
	$favoritecount 	= ($favorite) ? str_replace("<span","<span style='color:#FFAC33'",$favoritecount) : $favoritecount;
	$favoriteicon 	= ($favorite) ? "<img id='favoriteicon".$klip->id."' src='/images/favorite.png' style='width:10px' />" : "<img id='favoriteicon".$klip->id."' src='/images/favorite.png' style='width:10px;display:none' />";
	$favoritestyle 	= ($favorite) ? "style='color:#FFAC33'" : "style=''";
	$favoritetext 	= ($favorite) ? "Approved" : "Approve";
	if ($user->isLoggedIn()) $favorite = $favoriteicon; else $favorite = '';
	if ($user->isLoggedIn()) $favorite.= '<a id="favorite'.$klip->id.'" href="javascript:change_favorite('.$klip->id.')" class="add1-actions" '.$favoritestyle.'>';
	else $favorite.= '<a id="favorite'.$klip->id.'" href="/login" class="add1-actions" '.$favoritestyle.'>';
	$favorite.= $favoritetext;
	$favorite.= '</a>';
	//
	$delete = ($klip->user_id==$user->id) ? '<a class="klip-actions-links" href="/delete/klip/'.$klip->id.'" rel="lightbox" style="padding-right:25px;">Delete</a>' : '';
	$edit 	= ($klip->user_id==$user->id) ? '<a class="klip-actions-links" href="/klipit/klip/'.$klip->id.'" style="padding-right:25px;">Edit</a>' : '';
	$report = ($user->isLoggedIn() && $klip->user_id!=$user->id) ? '<a class="klip-actions-links" href="/report/klip/'.$klip->id.'" rel="lightbox" style="padding-right:25px;">Report</a>' : '';
	//
	if ($klip->type=="klip-photo" || $klip->type=="klip-note") {
		$klip_image_style = $birdy->browser=='web' ? 'style="border-radius:100px;width:150px;"' : 'style="border-radius:50px;width:50px;height:50px;"'; 
		$lightbox = '<a href="/klipper/'.$klip_user->username.'">';
	} else {
		$klip_image_style = $birdy->browser=='web' ? '' : 'style="width:28%;"';
		$lightbox = '<a href="'.$klip_image_link.'" '.$lightbox.'>';
	}
	$content = '
	<div class="klip-image" '.$klip_image_style.'>'
	.$lightbox.'<img src="'.$klip->thumbnail.'" class="klip-thumbnail" /></a>
	</div>
	<div style="float:right;width:66%">';
	if ($birdy->browser=='web') {
		$comment_info 	= '<a href="" id="comment_show'.$klip->id.'">'.$comment_info.'</a>';
		$actions_wrapper_width = ($klip->user_id!=$user->id) ? 'width:85px;' : 'width:129px;';
		if ($user->isLoggedIn()) {
			$content.= '<div id="actions-'.$klip->id.'" class="klip-actions-wrapper" style="display:none;'.$actions_wrapper_width.'"><div class="klip-actions">';
			$content.= $delete.$edit.$report;
			$content.= '</div></div>';
		}
	} else {
		$comment_info 	= '<a class="klip-actions-links" style="padding-right:15px" href="'.$klip_url.'" >'.str_replace("Comment on this","0 Comments",$comment_info).'</a>';
		$repost 	= str_replace('add1-actions"','klip-actions-links" style="padding-right:15px"',$repost);
		$share		= str_replace('add1-actions"','klip-actions-links" style="padding-right:15px"',$share);
		$favorite 	= str_replace('add1-actions"','klip-actions-links"',$favorite);
		$favorite 	= str_replace($favoriteicon,"",$favorite);
		$favorite 	= str_replace("style='","style='padding-right:15px;",$favorite);
		$actions_wrapper_width = ($klip->user_id!=$user->id) ? 'width:304px;' : 'width:304px;';
	}
	$content.= '
		<h2 class="klip-heading"><a class="klip-header-link" href="'.$klip_url.'">'.$title.'</a></h2>';
	if (!$klip->author) {
		$site_author = parse_url($klip->url);
		$klip->author = $site_author['host'];
	}
	if ($klip->reklip) {
		$reklip = $db->loadObject("SELECT title,user_id FROM klips WHERE id=:reklip",array(":reklip"=>$klip->reklip));
		if (isset($reklip->user_id)) $reklipper = birdyUser::getInstance($reklip->user_id);
		if (isset($reklip->title)) $reklip_url = '/klip/'.$klip->reklip.'/'.str_replace(array("/"," "),"_",stripslashes($reklip->title));
		if (isset($reklipper)) $content.='
		<a href="'.$reklip_url.'" style="float:left;font-size:11px;color:#0066FF;">original klip by '.$reklipper->used_name.'</a>
		';
	}
	if ($klip->author) {
		$content.='
		<!--<img src="http://getfavicon.appspot.com/'.rawurlencode($klip->url).'" style="float:right;width:12px;position:relative;top:2px;padding-left:3px;" />--> 
		<a href="'.$klip->url.'" class="hide info author-link" target="_blank">'.$klip->author.'</a>
		<div class="clear"></div>';
	}
	if ($klip->type=="klip-photo") {
		$src = (string) reset(simplexml_import_dom(DOMDocument::loadHTML($descr))->xpath("//img/@src"));
		$file = explode("/",$src);
		$file = end($file);
		$descr = '<a rel="lightbox" style="color:#333" href="'.$src.'">'.str_replace($file,'thumb-'.$file,$descr).'</a>';
	}
	$content.='<p class="klip-description">'.$descr.'</p>';
	$content.='<div class="info tags">'.$parent_tag.$tags.'</div>';
	$content.='</div>';
	
// 	if (($klip->type=='klip-note' || $klip->type=='klip-photo') && $birdy->browser!='web') {
// 		$klip_content_style = 'style="height:100%"';
// 	} else {
// 		$klip_content_style = '';
// 	}
	?>
	<form action="" method="POST">
		<div class="klip-input klip-content" <?php //echo $klip_content_style; ?> onmouseover="showOptions(<?php echo $klip->id; ?>,'inline')" onmouseout="showOptions(<?php echo $klip->id; ?>,'none')">
			<?php echo $content; ?>
		</div>
	</form>
	<p class="info">
	<?php
		if ($klip->privacy==0)
		$privacy= '<img id="privacy0" title="Public Klip" src="'.BIRDY_URL.'images/icons/public.png" style="width:12px;position: relative; top:4px; padding:2px;" />';
		if ($klip->privacy==1)
		$privacy= '<img id="privacy1" title="Followers and Friends" src="'.BIRDY_URL.'images/icons/friends.png" style="width:12px;position: relative; top:4px; padding:2px;" />';
		if ($klip->privacy==2)
		$privacy= '<img id="privacy2" title="Only Friends" src="'.BIRDY_URL.'images/icons/onlyfriends.png" style="width:12px;position: relative; top:4px; padding:2px;" />';
		if ($klip->privacy==3)
		$privacy= '<img id="privacy3" title="Only Me" src="'.BIRDY_URL.'images/icons/lock.png" style="width:12px;position: relative; top:4px; padding:2px;" />';
	//if (!strstr(BIRDY_SEF_URI,'profile') && !strstr(BIRDY_SEF_URI,'klipper')) {
		echo "<span style='font-size:12px;'>".$privacy." by <a href='".BIRDY_URL."klipper/".$klip_user->username."'>".stripslashes($klip_user->used_name)."</a>";
		/*if ($birdy->browser=='web')*/ echo " on <a href='?date=".strtotime($klip->creation_date)."'>".date("F d, Y",strtotime($klip->creation_date))."</a>";
		echo "</span>";
	//}
	if ($birdy->browser=="web") {
		echo "<span style='font-size:12px;float:right;padding-right:4%;'>";
		echo "<span class='hide' style='padding-right:25px;'>$comment_info</span>";
		if ($user->isLoggedIn()) echo "<span style='padding-right:25px;'>$repost</span>";
		echo "<span style='padding-right:25px;'>$share</span>";
		echo "<span style='padding-right:25px;'>".$favorite.$favoritecount."</span>";
		echo "</span>";
	} else {
		$report = str_replace('klip-actions-links"','add1-actions"',$report);
		$report = str_replace("Report",'<img src="/images/icons/report.png" style="width:16px;" />',$report);
		//$report = str_replace('rel="lightbox"','',$report);
		$report = str_replace('padding-right:25px','padding-right:5px',$report);
		
		if ($user->isLoggedIn()) $options = '<a class="add1-actions" href="javascript:showOptions('.$klip->id.')" style="padding-right:5px;"><img src="/images/icons/options.png" style="width:16px;" /></a>';
		else $options = '';
		
		echo "<span style='font-size:12px;float:right;padding-right:4%;'>";
		echo "<span>".$report.$options."</span>";
		echo "</span>";
		echo '</p><div style="float:left;width:100%"><div id="actions-'.$klip->id.'" class="klip-actions-wrapper" style="float: right; width: 318px; position: relative; top: -49px;display:none;'.$actions_wrapper_width.'"><div class="klip-actions" style="opacity:0.8">';
		echo $comment_info.$repost.$share.$favorite;
		echo '</div></div></div>';
	}
	?>
	</p>
	<?php
		$comments_style_div = 'none';
		include "main".DS."comments.php";
	?>
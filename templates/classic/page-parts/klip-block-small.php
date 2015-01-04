<?php
defined('_BIRDY') or die(__FILE__.': Restricted access');
//============================================================================
// THIS IS USED FOR REKLIPING and MOBILE...
if (!$user->isLoggedIn()) $user->id=0;

	$klip_user = birdyUser::getInstance($klip->user_id);
	
	$parent_tag = ($klip->parent_tag) ? $db->loadResult("SELECT tag FROM tags WHERE id=:parent_tag",array(":parent_tag"=>$klip->parent_tag)) : "";
	$parent_tag = (empty($parent_tag)) ? "" : 'Parent Tag: <a class="tag_link" href="'.$klip->parent_tag.'">'.$parent_tag.'</a>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;';
	$title = stripslashes($klip->title);
	$descr = stripslashes($klip->description);
	$what  = $klip->type;
	if ($what=='klip-note' || $what=='klip-photo') $klip->thumbnail = isset($_REQUEST['klip']) ? $user->avatar : $klip_user->avatar;
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
				$tag_array[] = '<a class="tag_link" href="'.$tag_id.'">'.stripslashes($tag).'</a>';
			}
			$tags = " tags: <span class='tags'>".implode(", ",$tag_array)."</span>";
		}
	}
	if (!$birdy->url_exists($klip->thumbnail)) $klip->thumbnail = "http://www.klipsam.com/images/URLs/default.jpg";
	//if (!$birdy->url_exists($klip->bignail)) $klip->bignail = $klip->thumbnail;
	$klip_image_link = ($birdy->browser=='web') ? $klip->bignail : $klip_url;
	//
	if ($klip->type=="klip-photo" || $klip->type=="klip-note") {
		$klip_image_style = $birdy->browser=='web' ? 'style="border-radius:100px;width:28%; max-height:68px;"' : 'style="border-radius:50px;width:50px;max-height:50px;"'; 
		$lightbox = '<a href="/klipper/'.$klip_user->username.'">';
	} else {
		$klip_image_style = $birdy->browser=='web' ? 'style="width:28%; max-height:68px;"': 'style="width:28%; max-height:68px;"';
		$lightbox = '<a href="'.$klip_image_link.'">';
	}
	if ($birdy->current_page=="embed") $klip_image_style = str_replace("max-height","nothing",$klip_image_style);
	$content = '<div class="klip-image" '.$klip_image_style.'>';
	if ($birdy->current_page!='reklip') $content.= $lightbox;
	$content.= '<img src="'.$klip->thumbnail.'" style="width:100%" class="klip-thumbnail" />';
	if ($birdy->current_page!='reklip') $content.= '</a>';
	$content.= '</div>
	<div style="float:right;width:66%">';
	$content.= '
		<h2 class="klip-heading"><a class="klip-header-link" style="font-size:13px;" href="'.$klip_url.'">'.$title.'</a></h2>';
	if ($klip->author) {
		$content.='
		<!--<img src="http://getfavicon.appspot.com/'.rawurlencode($klip->url).'" style="float:right;width:12px;position:relative;top:2px;padding-left:3px;" />--> 
		<a href="'.$klip->url.'" class="hide info author-link" style="font-size:10px;" target="_blank">'.$klip->author.'</a>
		<div class="clear"></div>';
	}
	$content.='<p class="klip-description" style="font-size:11px;">'.substr($descr,0,180).'</p>';
	$content.='</div>';
	?>
		<div class="klip-input klip-content">
			<?php echo $content; ?>
		</div>
	<p class="info">
	<?php
	//if (!strstr(BIRDY_SEF_URI,'profile') && !strstr(BIRDY_SEF_URI,'klipper')) {
		//echo "<span style='font-size:12px;'>by <a href='".BIRDY_URL."klipper/".$klip_user->username."'>".stripslashes($klip_user->used_name)."</a>";
		/*if ($birdy->browser=='web')*/ //echo " on <a href='?date=".strtotime($klip->creation_date)."'>".date("F d, Y",strtotime($klip->creation_date))."</a>";
		//echo "</span>";
	//}
	?>
	</p>

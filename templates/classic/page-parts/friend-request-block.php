<?php
defined('_BIRDY') or die(__FILE__.': Restricted access');
//============================================================================
if (isset($klipper->id) && $klipper->id!=$user->id) {
	$klipper = birdyUser::getInstance($klipper->id);
	$klip_url = "/klipper/".$klipper->username;
	$title = stripslashes($klipper->used_name);
	$descr = stripslashes($klipper->header).'<p class="info" style="font-size:11px;opacity:0.9">'.stripslashes($klipper->about).'</p><br /><br />';
	$image = $klipper->avatar;
	$klips = $db->loadResult("SELECT count(id) FROM klips WHERE reports<"._MAX_REPORTS." AND user_id=:user_id",array(":user_id"=>$klipper->id));
	
	$lightbox = ($birdy->browser=='web') ? 'rel="lightbox"' : '';
	
	$addfriend = 'Accept';
	$addfriend= '<a href="/add/accept/'.$klipper->id.'" onclick="document.getElementById(\'requester'.$klipper->id.'\').style.display = \'none\'" class="add1-actions" rel="lightbox">'.$addfriend.'</a>';
	$denyfriend = 'Deny';
	$denyfriend= '<a href="/add/deny/'.$klipper->id.'" onclick="document.getElementById(\'requester'.$klipper->id.'\').style.display = \'none\'" class="add1-actions" rel="lightbox">'.$denyfriend.'</a>';
	$follow = $db->loadResult("SELECT id FROM following WHERE user_id=:user AND following=:requested",array(":user"=>$user->id, ":requested"=>$klipper->id));
	$follow = empty($follow) ? 'Follow' : 'Un-follow';
	$follow   = '<a id="addFollow'.$klipper->id.'" onclick="changeFollow('.$klipper->id.')" href="/add/follow/'.$klipper->id.'" class="add1-actions" rel="lightbox">'.$follow.'</a>';
	$message  = '<a href="/message/new/'.$klipper->id.'" class="add1-actions" rel="lightbox">Message</a>';
	//
	$klip_image_style = $birdy->browser=='web' ? 'style="border-radius:100px;width:150px;"' : 'style="border-radius:50px;width:50px;height:50px;"'; 
	$lightbox = '<a>';

	$content = '
	<div class="klip-image" '.$klip_image_style.'>'.
	'<a href="'.$klip_url.'"><img src="'.$image.'" style="width:100%" class="klip-thumbnail" /></a>
	</div>
	
	<div style="float:right;width:66%">';
	if ($birdy->browser=='web') {
		$addfriend = str_replace('add1-actions"','klip-actions-links" style="padding-right:20px"',$addfriend);
		$denyfriend = str_replace('add1-actions"','klip-actions-links" style="padding-right:20px"',$denyfriend);
		$follow    = str_replace('add1-actions"','klip-actions-links" style="padding-right:20px"',$follow);
		$message   = str_replace('add1-actions"','klip-actions-links" style="padding-right:20px"',$message);
		$actions_wrapper_width = 'width:262px;';
		$content.= '<div id="actions-'.$klipper->id.'" class="klip-actions-wrapper" style="display:none;'.$actions_wrapper_width.'"><div class="klip-actions">';
		$content.= $addfriend.$denyfriend.$follow.$message;
		$content.= '</div></div>';
	} else {
		$addfriend = str_replace('add1-actions"','klip-actions-links" style="padding-right:15px"',$addfriend);
		$denyfriend = str_replace('add1-actions"','klip-actions-links" style="padding-right:15px"',$denyfriend);
		$follow    = str_replace('add1-actions"','klip-actions-links" style="padding-right:15px"',$follow);
		$message   = str_replace('add1-actions"','klip-actions-links" style="padding-right:15px"',$message);
		$actions_wrapper_width = 'width:304px;';
	}
	
	$content.= '
		<h2 class="klip-heading"><a class="klip-header-link" href="'.$klip_url.'">'.$title.'</a></h2>';
		
	$content.='<p class="klip-description">'.$descr.'</p>';
	$content.='<div class="info tags">'.$klips.' Klips</div>';
	$content.='</div>';
	
	?>
	<form id="requester<?php echo $klipper->id; ?>" action="" method="POST">
		<div class="klip-input klip-content" <?php //echo $klip_content_style; ?> onmouseover="showOptions(<?php echo $klipper->id; ?>,'inline')" onmouseout="showOptions(<?php echo $klipper->id; ?>,'none')">
			<?php echo $content; ?>
		</div>
	<p class="info">
	<?php
	if ($birdy->browser!="web") {
		$options = '<a class="add1-actions" href="javascript:showOptions('.$klipper->id.')" style="padding-right:5px;"><img src="/images/icons/options.png" style="width:16px;" /></a>';
		
		echo "<span style='font-size:12px;float:right;padding-right:4%;'>";
		echo "<span>".$options."</span>";
		echo "</span>";
		echo '</p><div style="float:left;width:100%"><div id="actions-'.$klipper->id.'" class="klip-actions-wrapper" style="float: right; width: 318px; position: relative; top: -49px;display:none;'.$actions_wrapper_width.'"><div class="klip-actions" style="opacity:0.8">';
		echo $addfriend.$denyfriend.$follow.$message;
		echo '</div></div></div>';
	}
	?>
	</p>
	</form>
<?php
}
?>
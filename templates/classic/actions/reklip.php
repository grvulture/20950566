<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
$birdy = birdyCMS::getInstance();
$db = birdyDB::getInstance();
$user = birdyUser::getInstance();
//============================================================================
// THESE ARE NOT NEEDED AS THIS IS A POPUP-ONLY ACTION
//include "..".DS."page-parts".DS."doctype.php";
//$birdy->pageTitle("Reklip | Klipsam");
//$birdy->pageDescription("Klip your thoughts. Klip your jam. Klipsam!");
//$birdy->pageImage(BIRDY_URL.'images/logo.jpg');
//============================================================================
// save the first referrer page where the klipit function was called from. When [DONE], we will redirect back there.
if (!isset($_POST['reklip'])) $_SESSION['loadPage'] = $_SERVER['HTTP_REFERER'];
//============================================================================
// done rekliping?
$klip = isset($_REQUEST['reklip']) ? $_REQUEST['reklip'] : 0;
if ($klip) {
	include_once(BIRDY_TEMPLATE_BASE.DS.'forms'.DS.'klipit.php');
	if (strstr($_SESSION['loadPage'],BIRDY_URL)) {
		$loadPage = $_SESSION['loadPage'];
		unset($_SESSION['loadPage']);
		$birdy->loadPage($loadPage);
	}
}
//============================================================================
// Initial page
$klip = isset($_REQUEST['klip']) ? $_REQUEST['klip'] : 0;
if ($klip) {
	$klipDB = $db->loadAssoc("SELECT * FROM klips WHERE id=:reklip",array(":reklip"=>$klip));
	$type = trim($klipDB['type']);
	$url = trim($klipDB['url']);
	$title = stripslashes($klipDB['title']);
	$description = stripslashes($klipDB['description']);
	$author = stripslashes($klipDB['author']);
	$thumbnail = trim($klipDB['thumbnail']);
	$bignail = trim($klipDB['bignail']);
	$user_id = $user->id;
	$parent_tag = stripslashes($klipDB['parent_tag']);
	$tags = stripslashes($klipDB['tags']);

	$lastprivacy = $db->loadResult("SELECT privacy FROM klips WHERE user_id=:current_user ORDER BY id DESC LIMIT 1",
	array(":current_user"=>$user->id));
	if (!empty($lastprivacy)) $privacy = $lastprivacy; else $privacy = $klipDB['privacy'];
	
	$padding = $birdy->browser=='web' ? 10 : 0;
?>
<!--<small style="font-size:9px;color:grey;">Please press [Enter] if the popup fails to load correctly</small>-->
     <div style="text-align:center;" class="main">
		<!--<p style="width:20%">-->
                          <h2 class="style" style="font-size:22px;color:#3289c8;background: #e9e9e9;padding:5px;">Do you want to reklip this?</h2>
                          <div class="big_social">
		<form action="/profile" method="POST">
			<span class=""></span>
			<div class="clear"></div>
			<input type="hidden" name="reklip" value="<?php echo $klip; ?>" />
			<input type="hidden" name="url" value="<?php echo $url; ?>" />
			<input type="hidden" name="klip_title" value="<?php echo $title; ?>" />
			<input type="hidden" name="klip_description" value="<?php echo htmlentities($description); ?>" />
			<input type="hidden" name="author" value="<?php echo $author; ?>" />
			<input type="hidden" name="thumbnail" value="<?php echo $thumbnail; ?>" />
			<input type="hidden" name="bignail" value="<?php echo $bignail; ?>" />
			<input type="hidden" name="what" value="<?php echo $type; ?>" />
			<input type="hidden" name="edit" value="0" />
			<input type="hidden" name="privacy" value="<?php echo $pivacy; ?>" />
			<input type="hidden" name="parent_tag" value="<?php echo $parent_tag; ?>" />
			<input type="hidden" name="klip_tags" value="<?php echo $tags; ?>" />
			<?php 
			$klip = $db->loadObject("SELECT * FROM klips WHERE id=:id AND reports<:reports",array(":id"=>$klip, ":reports"=>1));
			$klip_user = birdyUser::getInstance($klip->user_id);
			$klip_url = BIRDY_URL.'klip/'.$klip->id.'/'.str_replace(array("/"," "),"_",$title);
			$title = stripslashes($klip->title);
			// @TODO: maybe the display on phones is not enough wide for the iframe. This must be tested. We must also check display on tablets in the future!
			if ($birdy->browser!='web') include(BIRDY_TEMPLATE_BASE.DS.'page-parts'.DS.'klip-block-small.php');
			else
			{
			// show the (embed) iframe...
			  echo '<iframe src="'.BIRDY_URL.'embed/klip_id/'.$klip->id.'" frameborder="0" height="180" width="660"></iframe>
			  <div style="clear: both; height: 3px; width:652px;"></div>
			  <p style="display: block; font-size: 11px; font-family: &quot;OpenSans&quot;,Helvetica,Arial,sans-serif; margin: 0px; padding: 3px 4px; color:rgb(153, 153, 153); width: 652px;">
			      <a href="'.$klip_url.'" target="_blank" style="color:#808080; font-weight:bold;">'.$title.'</a>
			      <span> by </span>
			      <a href="'.BIRDY_URL."klipper/".$klip_user->username.'" target="_blank" style="color:#808080; font-weight:bold;">'.stripslashes($klip_user->used_name).'</a>
			      <span> on </span>
			      <a href="'.BIRDY_URL.'" target="_blank" style="color:#808080; font-weight:bold;"> Klipsam</a>
			  </p>
			  <div style="clear: both; height: 3px; width: 652px;"></div>
			  ';
			}
			?>
			<input type="submit" class="klip-submit" style="float:left;margin-left:5px" name="klip-done" value="Yes" />
			<input type="button" onclick="Lightbox.hide()" class="klip-submit" style="text-align:center;float:right;margin-right:5px" name="delete-cancel" value="No" />
		</form>
			  </div>
     </div>
</div>
<?php
}
?>
<br />
<br />
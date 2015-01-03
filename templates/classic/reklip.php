<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
$birdy = birdyCMS::getInstance();
$db = birdyDB::getInstance();
$user = birdyUser::getInstance();
//============================================================================
include "page-parts".DS."doctype.php";
$birdy->pageTitle("Reklip | Klipsam");
$birdy->pageDescription("Klip your thoughts. Klip your jam. Klipsam!");
$birdy->pageImage(BIRDY_URL.'images/logo.jpg');
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
<div class="main" style="text-align:center;padding-left:<?php echo $padding; ?>%;width:270px;">
		<form action="/profile" method="POST">
			<span class="">Do you want to reklip this?</span>
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
			include(BIRDY_TEMPLATE_BASE.DS.'page-parts'.DS.'klip-block-small.php');
			?>
			<input type="submit" class="klip-submit" style="float:left;margin-left:5px" name="klip-done" value="Yes" />
			<input type="button" onclick="Lightbox.hide()" class="klip-submit" style="text-align:center;float:right;margin-right:5px" name="delete-cancel" value="No" />
		</form>
</div>
<?php
}
?>
</body>
</html>
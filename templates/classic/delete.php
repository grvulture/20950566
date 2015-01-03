<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
$birdy = birdyCMS::getInstance();
$db = birdyDB::getInstance();
$user = birdyUser::getInstance();
//============================================================================
include "page-parts".DS."doctype.php";
$birdy->pageTitle("Delete! | Klipsam");
$birdy->pageDescription("Klip your thoughts. Klip your jam. Klipsam!");
$birdy->pageImage(BIRDY_URL.'images/logo.jpg');
//============================================================================
// Keep the page where we came from to redirect upon deletion
if (!isset($_POST['delete-klip'])) $_SESSION['loadPage'] = $_SERVER['HTTP_REFERER'];
//============================================================================
// Confirmed deletion? Proceed here
$klip = isset($_REQUEST['delete-klip']) ? $_REQUEST['delete-klip'] : 0;
if ($klip) {
	$query = $db->delete("klips",array("id"=>$klip));
	$birdy->outputNotice("Klip deleted");
	if (strstr($_SESSION['loadPage'],BIRDY_URL)) {
		$loadPage = $_SESSION['loadPage'];
		unset($_SESSION['loadPage']);
		$birdy->loadPage($loadPage);
	}
}
//============================================================================
// Initial page
$klip = isset($_REQUEST['klip']) ? $_REQUEST['klip'] : 0;
?>
<body>
<?php
if ($klip) {
?>
<div class="main" style="text-align:center;padding-left:10%;width:270px;">
		<form action="<?php echo BIRDY_URL; ?>delete" method="POST">
			<span class="">Are you sure?</span>
			<div class="clear"></div>
			<div class="divider"></div>
			<input type="hidden" name="delete-klip" value="<?php echo $klip; ?>" />
			<input type="submit" class="klip-submit" style="float:left;margin-left:5px" name="delete-done" value="Yes" />
			<input type="button" onclick="Lightbox.hide()" class="klip-submit" style="text-align:center;float:right;margin-right:5px" name="delete-cancel" value="No" />
		</form>
</div>
<?php
}
?>
</body>
</html>
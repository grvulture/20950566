<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
$birdy= birdyCMS::getInstance();
$db = birdyDB::getInstance();
$user = birdyUser::getInstance();
//============================================================================
include "page-parts".DS."doctype.php";
$birdy->pageTitle("Block this Klipper | Klipsam");
$birdy->pageDescription("Klip everything you find while you surf. Klip your thoughts. Klip your jam!");
$birdy->pageImage(BIRDY_URL.'images/logo.jpg');
//============================================================================
$result='';
if (isset($_REQUEST['klipper'])) {
	$blocked = intval($_REQUEST['klipper']);
	$klipper = birdyUser::getInstance($blocked);
	$already = $db->loadResult("SELECT id FROM blocks WHERE blocker=:user AND blocked=:blocked",
		array(":user"=>$user->id, ":blocked"=>$blocked));
	if (empty($already)) {
		$result = $db->insert('blocks',array("blocker"=>$user->id, "blocked"=>$blocked));
		if ($result) $echo = "You have blocked ".$klipper->used_name;
	}
	else {
		$result = $db->delete('blocks',array("id"=>$already));
		$echo = "You have unblocked ".$klipper->used_name;
	}
}
//============================================================================
?>
<body>
<p class="info" style="height: 50px; width: 100%; text-align: center; padding-top: 65px; padding-left: 15px;">
<?php echo $echo; ?>
</p>
</body>
</html>

<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
$birdy= birdyCMS::getInstance();
$db = birdyDB::getInstance();
$user = birdyUser::getInstance();
//============================================================================
include "page-parts".DS."doctype.php";
$birdy->pageTitle("Add Friend or Follow | Klipsam");
$birdy->pageDescription("Klip everything you find while you surf. Klip your thoughts. Klip your jam!");
$birdy->pageImage(BIRDY_URL.'images/logo.jpg');
//============================================================================
$result='';
if (isset($_REQUEST['accept'])) {
	$requester = intval($_REQUEST['accept']);
	$klipper = birdyUser::getInstance($requester);
	$db->update("friends","accepted=1","requester=:requester",array(":requester"=>$requester));
	$echo = "You are now friends with ".$klipper->used_name;
}
elseif (isset($_REQUEST['deny'])) {
	$requester = intval($_REQUEST['deny']);
	$klipper = birdyUser::getInstance($requester);
	$db->delete("friends",array("requester"=>$requester));
	$echo = "You denied friendship with ".$klipper->used_name;
}
elseif (isset($_REQUEST['friend'])) {
	$requested = intval($_REQUEST['friend']);
	$klipper = birdyUser::getInstance($requested);
	$already = $db->loadResult("SELECT id FROM friends WHERE 
	(requester=:user AND requested=:requested) OR (requester=:requested AND requested=:user)",
	array(":user"=>$user->id, ":requested"=>$requested));
	if (empty($already)) {
		$result = $db->insert('friends',array("requester"=>$user->id, "requested"=>$requested, "accepted"=>0));
		if ($result) $echo = "Friend request sent to ".$klipper->used_name;
	}
	else {
		$result = $db->delete('friends',array("id"=>$already));
		$echo = "You removed ".$klipper->used_name." from your friends";
	}
}
elseif (isset($_REQUEST['follow'])) {
	$requested = intval($_REQUEST['follow']);
	$klipper = birdyUser::getInstance($requested);
	$already = $db->loadResult("SELECT id FROM following WHERE user_id=:user AND following=:requested",
	array(":user"=>$user->id, ":requested"=>$requested));
	if (empty($already)) {
		$result = $db->insert('following',array("user_id"=>$user->id, "following"=>$requested));
		if ($result) $echo = "You are now following ".$klipper->used_name;
	}
	else {
		$result = $db->delete('following',array("id"=>$already));
		$echo = "You are no longer following ".$klipper->used_name;
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

<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
$birdy = birdyCMS::getInstance();
$db = birdyDB::getInstance();
$user = birdyUser::getInstance();
//============================================================================
$klip = isset($_REQUEST['klip']) ? $_REQUEST['klip'] : 0;
if ($klip) {
	$exists= $db->loadResult("SELECT id FROM favorites WHERE user_id=:user_id AND klip_id=:klip_id",array("user_id"=>$user->id,"klip_id"=>$klip));
	if (!$exists) {
		$query = $db->insert("favorites",array("user_id"=>$user->id,"klip_id"=>$klip));
		echo $query->lastInsertId();
	} else {
		$query = $db->delete("favorites",array("id"=>$exists));
		echo 0;
	}
}
?>

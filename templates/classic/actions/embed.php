<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
$birdy= birdyCMS::getInstance();
$db = birdyDB::getInstance();
$user = birdyUser::getInstance();
//============================================================================
include "..".DS."page-parts".DS."doctype.php";
// page title and description will be generated on next step...
//============================================================================
?>
<body>
<!-- start main -->
<?php
$klip = $db->loadObject("SELECT * FROM klips WHERE id=:id",array(":id"=>$_REQUEST['klip_id']));
if (empty($klip) || ($klip->reports >= _MAX_REPORTS && ($user->account_type < 2 || !$user->isLoggedIn()))) {
	//$birdy->outputWarning("klip->reports:".$klip->reports." _MAX_REPORTS:"._MAX_REPORTS." user->account_type:".$user->account_type.' user->isLoggedIn:'.$user->isLoggedIn);
	$birdy->outputWarning("Klip not found, or you don't have enough privileges to access it!");
} else include "..".DS."page-parts".DS."klip-block-small.php"; ?>
</body>
</html>

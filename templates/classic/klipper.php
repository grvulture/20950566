<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
$birdy= birdyCMS::getInstance();
$db = birdyDB::getInstance();
$user = birdyUser::getInstance();
//============================================================================
if (!$user->isLoggedIn()) {
	$birdy->loadPage(BIRDY_URL.'login');
}
// get the Klipper
$user_name = $_REQUEST['user_name'];
$user_id = $db->loadResult("SELECT id FROM birdy_users WHERE username=:username",array(":username"=>$user_name));
$klipper = birdyUser::getInstance($user_id);

$birdy->addScriptToBottom("function scrollToAnchor(aid){
		$('html,body').animate({scrollTop: $('#'+aid).offset().top},'slow');
	}
");
include "page-parts".DS."doctype.php";
?>
<body>
<!-- start header -->
<?php include "page-parts".DS."header.php"; ?>
<!-- start sub-header -->
<?php include "page-parts".DS."sub-header.php"; ?>
<!-- start main -->
<?php include "page-parts".DS."main.php"; ?>
<!-- start footer -->
<?php include "page-parts".DS."footer.php"; ?>
</body>
</html>

<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
$birdy = birdyCMS::getInstance();
$db = birdyDB::getInstance();
$user = birdyUser::getInstance();
//============================================================================
include "..".DS."page-parts".DS."doctype.php";
$birdy->pageTitle("Upload a new Avatar! | Klipsam");
$birdy->pageDescription("Klip your thoughts. Klip your jam. Klipsam!");
$birdy->pageImage(BIRDY_URL.'images/logo.jpg');
//============================================================================
if (isset($_FILES['avatar_file'])) {
	$success = birdyUser::createAvatar();
	$birdy->loadPage($_SERVER['HTTP_REFERER']);
}
?>
<body>
<?php include_once(BIRDY_TEMPLATE_BASE.DS.'forms'.DS.'uploadAvatar.php'); ?>
</body>
</html>
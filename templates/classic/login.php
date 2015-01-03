<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
$birdy= birdyCMS::getInstance();
$db = birdyDB::getInstance();
$user = birdyUser::getInstance();
//============================================================================
if ($user->isLoggedIn()) $birdy->loadPage(BIRDY_URL."profile");
//============================================================================
include "page-parts".DS."doctype.php";
$birdy->pageTitle("Login | Klipsam");
$birdy->pageDescription("Login to Klipsam. Klip your thoughts. Klip your jam!");
$birdy->pageImage(BIRDY_URL.'images/logo.jpg');
//============================================================================
?>
<body>
<?php
if (empty($_REQUEST['popup'])) {
	include "page-parts".DS."header.php";
	include "page-parts".DS."sub-header.php";
}
?>
<!-- start main -->
<div style="background:#FFF;padding-right:18px;">
<?php include "page-parts".DS."main.php"; ?>
</div>
<!-- start footer -->
<?php if (empty($_REQUEST['popup'])) include "page-parts".DS."footer.php"; ?>
</body>
</html>


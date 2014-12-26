<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
$birdy = birdyCMS::getInstance();
$db = birdyDB::getInstance();
$user = birdyUser::getInstance();
//============================================================================
$birdy->pageTitle("Edit your Profile! | Klipsam");
$birdy->pageDescription("Klip your thoughts. Klip your jam. Klipsam!");
$birdy->pageImage(BIRDY_URL.'images/logo.jpg');
if (!$user->isLoggedIn()) {
	$birdy->loadPage(BIRDY_URL.'login');
}
if (isset($_POST['editProfile'])) {
	$success = 0;
	$success += (birdyUser::editUserName()) ? 1 : 0;
	$success += (birdyUser::editUserEmail()) ? 1 : 0;
	$success += (birdyUser::editUserFirstName()) ? 1 : 0;
	$success += (birdyUser::editUserLastName()) ? 1 : 0;
	$success += (birdyUser::editUserHeader()) ? 1 : 0;
	$success += (birdyUser::editUserBio()) ? 1 : 0;
	//echo "<h2 style='font-size:24px;background:#00BBCC;padding:20px'>Please wait, you will be redirected back to your profile...</h2>";
	if ($success>4) {
		unset($_SESSION['feedback_negative']);
		unset($_SESSION['feedback_positive']);
		$_SESSION['feedback_positive'][] = "Your profile information has been updated successfully!";
	}
	$birdy->loadPage(BIRDY_URL."profile");
}
include "page-parts".DS."doctype.php";
?>
<body>
<?php
if (empty($_REQUEST['popup'])) {
	include "page-parts".DS."header.php";
	include "page-parts".DS."sub-header.php";
	?>
	<div class="wrap">
	<div class="wrapper">
	<div class="main">
	<?php
}
?>
<?php include_once(BIRDY_TEMPLATE_BASE.DS.'forms'.DS.'editProfile.php'); ?>
<?php
if (empty($_REQUEST['popup'])) {
	?>
	<div class="clear"></div>
	</div>
	</div>
	</div>
	<?php
	include "page-parts".DS."footer.php";
}
?>
</body>
</html>
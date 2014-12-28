<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
$birdy = birdyCMS::getInstance();
$db = birdyDB::getInstance();
$user = birdyUser::getInstance();
//============================================================================
$birdy->pageTitle("Write a new message | Klipsam");
$birdy->pageDescription("Klip your thoughts. Klip your jam. Klipsam!");
$birdy->pageImage(BIRDY_URL.'images/logo.jpg');
if (!$user->isLoggedIn()) {
	$birdy->loadPage(BIRDY_URL.'login');
}
// save the first referrer page where the klipit function was called from. When [DONE], we will redirect back there.
if (!isset($_POST['message-done'])) $_SESSION['loadPage'] = $_SERVER['HTTP_REFERER'];
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
<?php
if (empty($_REQUEST['popup']) && $birdy->browser=='web') {
$user_heading = empty($user->header) ? '<a rel="lightbox" title="Update your profile info" href="'.BIRDY_URL.'editProfile'.$popup.'">Enter a catching header for your profile</a>' : $user->header;
$user_about   = empty($user->about) ? '<a rel="lightbox" title="Update your profile info" href="'.BIRDY_URL.'editProfile'.$popup.'">Enter a short bio of you</a>' : $user->about;
?>
<div class="span3">
	<div class="span1_of_3 profile-left">
		<a rel="lightbox" title="Change your Profile Picture" href="<?php echo BIRDY_URL; ?>uploadAvatar"><img src="<?php echo $user->avatar; ?>" style="border-radius:150px" alt="<?php echo $user_credentials; ?>"></a>
		<h2 class="style top1" style="word-wrap: break-word;"><a rel="lightbox" title="Update your profile info" href="<?php echo BIRDY_URL; ?>editProfile<?php echo $popup; ?>"><?php echo stripslashes($user_credentials); ?></a></h2>
		<h5 class="style"><?php echo stripslashes($user_heading); ?></h5>
		<p class="para info" style="font-size:11px;"><?php echo stripslashes($user_about); ?></p>
	</div>
	<div class="span2_of_3 profile-right">
<?php
}
?>
<?php include_once(BIRDY_TEMPLATE_BASE.DS.'forms'.DS.'messageit.php'); ?>
<?php
if (empty($_REQUEST['popup']) && $birdy->browser=='web') echo "</div>";
//
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
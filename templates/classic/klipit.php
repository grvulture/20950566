<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
$birdy = birdyCMS::getInstance();
$db = birdyDB::getInstance();
$user = birdyUser::getInstance();
//============================================================================
$birdy->pageTitle("Klip It! | Klipsam");
$birdy->pageDescription("Klip your thoughts. Klip your jam. Klipsam!");
$birdy->pageImage(BIRDY_URL.'images/logo.jpg');
if (!$user->isLoggedIn()) {
	if (empty($_REQUEST['popup'])) $popupredirect = "?popup=1"; else $popupredirect = '';
	$birdy->loadPage(BIRDY_URL.'login'.$popupredirect);
}
// save the first referrer page where the klipit function was called from. When [DONE], we will redirect back there.
if (!isset($_POST['klip-done'])) $_SESSION['loadPage'] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/profile';
include "page-parts".DS."doctype.php";
?>
<body>
<?php
if (empty($_REQUEST['popup'])) {
	if ($birdy->browser=="web") include "page-parts".DS."header.php";
	else {
	?>
	<div class="header_bg">
	<div class="wrap">
	<div class="wrapper">
		<div class="header">
			<div class="logo">
				<a href="/" style="color: rgb(255, 255, 255); font-family: nexa_boldregular; font-size: 36px;">
					<img src="/images/logo-inverse-small.jpg" style="height: 46px; position: relative; top: -5px; float: left;" alt=""/> 
					Klipsam
				</a>
			</div>
		</div>
	</div>
	</div>
	</div>
	<?php
	}
	if ($birdy->browser=="web") include "page-parts".DS."sub-header.php";
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
$aboutmestyle = strlen($user_about)<150 ? 'text-align:center;' : 'text-align:justify;';
?>
<div class="span3">
	<div class="span1_of_3 profile-left">
		<a rel="lightbox" title="Change your Profile Picture" href="<?php echo BIRDY_URL; ?>uploadAvatar"><img src="<?php echo $user->avatar; ?>" style="border-radius:150px" alt="<?php echo $user_credentials; ?>"></a>
		<h2 class="style top1" style="word-wrap: break-word;"><a rel="lightbox" title="Update your profile info" href="<?php echo BIRDY_URL; ?>editProfile<?php echo $popup; ?>"><?php echo stripslashes($user_credentials); ?></a></h2>
		<h5 class="style"><?php echo stripslashes($user_heading); ?></h5>
		<p class="para info" style="font-size:11px;padding:10px;<?php echo $aboutmestyle; ?>"><?php echo stripslashes($user_about); ?></p>
	</div>
	<div class="span2_of_3 profile-right">
<?php
}
?>
<?php include_once(BIRDY_TEMPLATE_BASE.DS.'forms'.DS.'klipit.php'); ?>
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
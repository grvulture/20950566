<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
$birdy = birdyCMS::getInstance();
$db = birdyDB::getInstance();
$user = birdyUser::getInstance();
//============================================================================
$birdy->pageTitle("Send a new Message | Klipsam");
$birdy->pageDescription("Klip your thoughts. Klip your jam. Klipsam!");
$birdy->pageImage(BIRDY_URL.'images/logo.jpg');
if (!$user->isLoggedIn()) {
	if (empty($_REQUEST['popup'])) $popupredirect = "?popup=1"; else $popupredirect = '';
	$birdy->loadPage(BIRDY_URL.'login'.$popupredirect);
}
// save the first referrer page where the klipit function was called from. When [DONE], we will redirect back there.
if (!isset($_POST['klip-done'])) $_SESSION['loadPage'] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/inbox';
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
$klipper = birdyUser::getInstance(intval($_REQUEST['new']));
if (empty($_REQUEST['popup']) && $birdy->browser=='web') {
	include_once(BIRDY_TEMPLATE_BASE.DS.'page-parts'.DS.'main'.DS.'user_profile.php');
	userLeftColumn($user,$klipper);
	echo '<div class="span2_of_3 profile-right">';
} else {
	echo "<h2 class='top style'>Send a message to ".$klipper->used_name."</h2>";
}

include_once(BIRDY_TEMPLATE_BASE.DS.'forms'.DS.'messageit.php');

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
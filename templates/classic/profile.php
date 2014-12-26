<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
$birdy= birdyCMS::getInstance();
$db = birdyDB::getInstance();
$user = birdyUser::getInstance();
//============================================================================
if (!$user->isLoggedIn()) {
	$birdy->outputAlert("You do not have access to this page. Please login");
	$birdy->loadPage(BIRDY_URL.'login');
}
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
<?php
$birdy->addScriptToBottom("
$(document).ready(function() {
	$('#facebookLogin').click(function(){
		windowOpen('".BIRDY_URL.'login?provider=facebook'."');
	});
	$('#twitterLogin').click(function(){
		windowOpen('".BIRDY_URL.'login?provider=twitter'."');
	});
	$('#googleLogin').click(function(){
		windowOpen('".BIRDY_URL.'login?provider=google'."');
	});
});
function windowOpen(what) {
	window.location = what;
}
");
?>
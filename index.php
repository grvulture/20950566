<?php
//=================================================================================
// ONLY IN INDEX.PHP
define( '_BIRDY', 1 );
define('BIRDY_BASE', dirname(__FILE__) );
define( 'DS', DIRECTORY_SEPARATOR );
//=================================================================================
// INITIALIZE THE FRAMEWORK
include_once('birdy'.DS.'birdy_main.php');
$birdy = new birdyCMS();
$db = birdyDB::getInstance();
$user = birdyUser::getInstance();
//=================================================================================
// PROCESS ANY AJAX REQUESTS. THIS TAKES FOR GRANTED THAT FILES WHICH PROCESS AJAX ARE IN BIRDY'S PLUGINS '_ajax_responses' FOLDER
if (!empty($_POST['birdy_ajax'])) {
	// @TODO: make sure this is a valid file! For now we will just take the $_POST as it is
	include(BIRDY_BASE.DS.'plugins'.DS.'_ajax_responses'.DS.$_POST['birdy_ajax']);
} else {
//=================================================================================
	// PROCESS THE ACTUAL PAGE REQUESTED
	$page = $birdy->parseRoute();
//=================================================================================	
	// CHECK PAGE ACL
	$publicpages = array("index","login","share","terms","contact","getinvolved","privacy","rules","search");
	// the above public pages will be pages on the birdy database with different permissions, one of the permissions is "public", which will be allowed to non-logged in users
	if (!$user->isLoggedIn() && !in_array($birdy->current_page,$publicpages)) {
		if (strstr($page['args'],"popup=1")) {
			$popupredirect = "?popup=1"; 
		} else {
			$popupredirect = '';
			$birdy->outputAlert("You don't have access to this page. Please login or register.");
		}
		$birdy->loadPage(BIRDY_URL.'login'.$popupredirect);
	}
//=================================================================================	
	// REDIRECT TO THE CORRECT PAGE, WITH ITS ARGS PARSED
	if ($page) {
		$birdy->redirect($page['file'].$page['args'].$page['anchor']);
	}
}
//=================================================================================
?>

<?php
// ONLY IN INDEX.PHP
define( '_BIRDY', 1 );
define('BIRDY_BASE', dirname(__FILE__) );
define( 'DS', DIRECTORY_SEPARATOR );
//=================================================================================
// INITIALIZE THE FRAMEWORK
include_once('birdy'.DS.'birdy_main.php');
$birdy = new birdyCMS();
//=================================================================================
// PROCESS ANY AJAX REQUESTS. THIS TAKES FOR GRANTED THAT FILES WHICH PROCESS AJAX ARE IN BIRDY'S PLUGINS 'AJAX' FOLDER
if (!empty($_POST['birdy_ajax'])) {
	// @TODO: make sure this is a valid file! For now we will just take the $_POST as it is
	include(BIRDY_BASE.DS.'plugins'.DS.'_ajax_responses'.DS.$_POST['birdy_ajax']);
} else {
	// PROCESS THE ACTUAL PAGE REQUESTED
	$page = $birdy->parseRoute();
	// REDIRECT TO THE CORRECT PAGE, WITH ITS ARGS PARSED
	if ($page) {
		$birdy->redirect($page['file'].$page['args'].$page['anchor']);
	}
}
?>

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
$page = $birdy->parseRoute();
// REDIRECT TO THE CORRECT PAGE, WITH ITS ARGS PARSED
if ($page) {
	$birdy->redirect($page['file'].$page['args'].$page['anchor']);
}
?>

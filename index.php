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
echo "<h2 class='top style'><a>SITE UNDER CONSTRUCTION</a></h2></body></html>";
return;
/*
$allow = array("77.7.125.23","77.190.23.24","77.191.43.118","77.190.32.221","77.7.7.154","77.7.12.253","77.7.1.157","77.7.110.168","77.190.38.27","77.7.110.122","77.7.5.176","77.7.3.121","77.190.22.125"); //allowed IPs
if ($_SERVER['REQUEST_URI']!='/roulette') {
	if(!in_array($_SERVER['REMOTE_ADDR'], $allow)) {
		header("Location: http://www.klipsam.com/index.html"); //redirect
		exit();
	}
}
*/
//=================================================================================
$page = $birdy->parseRoute();
// REDIRECT TO THE CORRECT PAGE, WITH ITS ARGS PARSED
if ($page) {
	$birdy->redirect($page['file'].$page['args'].$page['anchor']);
}
?>

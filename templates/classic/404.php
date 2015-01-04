<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
$birdy= birdyCMS::getInstance();
//============================================================================
include "page-parts".DS."doctype.php";
$birdy->pageTitle("404 | Klipsam");
$birdy->pageDescription("Klip Missing! What we actually want to say is 404 error :/");
$birdy->pageImage(BIRDY_URL.'images/logo.jpg');
//============================================================================
?>
<div class="header_bg">
<div class="wrap">
<div class="wrapper">
	<div class="header">
		<div class="logo" style="width:33%">
			<a href="/" style="color: rgb(255, 255, 255); font-family: nexa_boldregular; font-size: 36px;">
				<img src="/images/logo-inverse-small.jpg" style="height: 46px; position: relative; top: -5px; float: left;" alt=""/> 
				Klipsam
			</a>
			<h2 style="color: rgb(255, 255, 255); font-size: 60px; padding-top: 65px; width: 50%;">Klip Missing</h2>
		</div>
		<div class="cssmenu" style="width:50%">
			<img style="width:100%" src="/images/paper-clip-attachment.jpg" />
			<div class="clear"></div>
		</div>
		<div class="clear"></div>
		<h2 style="color: rgb(255, 255, 255); font-size: 30px; padding-top: 65px;">What we actually want to say is 404 error</h2>
		<h2 style="color: rgb(255, 255, 255); font-size: 15px; padding-top: 50px;">But we are too embarassed to say it! :/</h2>
		<p>&nbsp;</p>
		<p style="color: rgb(255, 255, 255); font-size: 13px; padding-top: 35px;">
			We are deeply sorry for this inconvenience, but we believe this klip has been lost forever. If it was even here in the first place.
		</p>
		<p style="color: rgb(255, 255, 255); font-size: 14px; padding-top: 20px;">
			Klipsam suggests you go to the <a href="/" style="color:#0066FF">Newsfeed</a> for a list of the latest klips, or even better <a href="/klipit" style="color:#0066FF">add</a> your own klip to our awesome collection!
		</p>
	</div>
</div>
</div>
</div>
</body>
</html>

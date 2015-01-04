<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
$birdy = birdyCMS::getInstance();
$db = birdyDB::getInstance();
$user = birdyUser::getInstance();
//============================================================================
include "..".DS."page-parts".DS."doctype.php";
$birdy->pageTitle("Search | Klipsam");
$birdy->pageDescription("Klip your thoughts. Klip your jam. Klipsam!");
$birdy->pageImage(BIRDY_URL.'images/logo.jpg');
//============================================================================
if (isset($_POST['search_query'])) {
	include("..".DS."forms".DS."search.php");
	return;
}
?>
<div class="main" style="padding-left:10%;width:270px;height:220px;">
	<div id="signin">
		<div class="artical-commentbox">
		<form action="/search" method="POST">
			<input name="search_query" autocomplete="off" type="text" class="textbox klip-submit klip-input" style="cursor:text;float:left;" placeholder="Search" value="" /><br /><br /><br />
			<label style="cursor:pointer;"><input type="radio" class="textbox klip-select" name="search_option" value="parent_tags" /> Parent Tags</label><br />
			<label style="cursor:pointer;"><input type="radio" class="textbox klip-select" name="search_option" value="tags" /> Tags</label><br />
			<label style="cursor:pointer;"><input type="radio" class="textbox klip-select" name="search_option" value="klips" /> Klips</label><br />
			<label style="cursor:pointer;"><input type="radio" checked="checked" class="textbox klip-select" name="search_option" value="klippers" /> Klippers</label><br /><br />
			<input type="submit" class="klip-submit klip-input" style="float:left;width:95% !important;" name="klip-done" value="Search" />
		</form>
		</div>
	</div>
</div>
</body>
</html>
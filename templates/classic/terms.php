<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
$birdy= birdyCMS::getInstance();
$db = birdyDB::getInstance();
$user = birdyUser::getInstance();
//============================================================================
include "page-parts".DS."doctype.php";
$birdy->pageTitle("Terms | Klipsam");
$birdy->pageDescription("Klip everything you find while you surf. Klip your thoughts. Klip your jam!");
$birdy->pageImage(BIRDY_URL.'images/logo.jpg');
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

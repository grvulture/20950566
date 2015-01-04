<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
$birdy= birdyCMS::getInstance();
$db = birdyDB::getInstance();
$user = birdyUser::getInstance();
//============================================================================
include "page-parts".DS."doctype.php";
$birdy->pageTitle("Contact Us | Klipsam");
$birdy->pageDescription("Klip everything you find while you surf. Klip your thoughts. Klip your jam!");
$birdy->pageImage(BIRDY_URL.'images/logo.jpg');
?>
<body>
<!-- start header -->
<?php include "page-parts".DS."header.php"; ?>
<!-- start sub-header -->
<?php include "page-parts".DS."sub-header.php"; ?>
<!-- start main -->
<div class="wrap">
<div class="wrapper">
	<div class="main">	
	{(birdy_feedback)}
	 	 <div class="contact">				
				  <div class="contact-form">
					<h2 class="style"><a href="#">Contact Us</a></h2>
					    <form method="post" action="/sendcontactemail">
					    	<div>
						    	<span><label>NAME</label></span>
						    	<span><input name="userName" type="text" class="textbox"></span>
						    </div>
						    <div>
						    	<span><label>E-MAIL</label></span>
						    	<span><input name="userEmail" type="text" class="textbox"></span>
						    </div>
						    <div>
						    	<span><label>SUBJECT</label></span>
						    	<span><textarea name="userMsg"> </textarea></span>
						    </div>
						   <div>
						   		<span><input type="submit" class="" name="action" value="Submit"></span>
						  </div>
					    </form>
				    </div>
  				<div class="clear"> </div>		
			  </div>
		</div>
	<div class="clear" style="padding:0 0 4% 0"></div>
	</div>
</div>
<!-- start footer -->
<!-- start footer -->
<?php include "page-parts".DS."footer.php"; ?>
</body>
</html>

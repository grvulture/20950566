<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
$birdy= birdyCMS::getInstance();
$db = birdyDB::getInstance();
//==================================================
$birdy->pageTitle("Inbox | Klipsam");
$birdy->pageDescription("Klip your thoughts. Klip your jam. Klipsam!");
$birdy->pageImage(BIRDY_URL.'images/logo.jpg');
	
	$klips = false;
	
	?>
	<div class="span3">
	<div class="span1_of_3 profile-left">
	</div>
	<div class="span2_of_3 profile-right">
	<?php
	//if (!empty($_POST['birdyCommentSession'])) birdyComments::submitComment(); // this is already done on "displayComments"
	if (!empty($_POST['message-submit'])) {
		include_once(BIRDY_TEMPLATE_BASE.DS.'forms'.DS.'message-submit.php'); 
	}
	else {
		if ($klips) {
			foreach ($klips as $klip) {
				include(BIRDY_TEMPLATE_BASE.DS.'page-parts'.DS.'klip-block.php');
				echo "<div class='clear'></div>";
			}
			//include(BIRDY_TEMPLATE_BASE.DS.'forms'.DS.'klipit.php');
		} else {
		?>
			<h2 class="style list"><a href="javascript:scrollToAnchor('firstjam')">Klip your first jam</a></h2>
			<p class="para top1">In Kipsam you can klip everything you like while you surf. Images, videos, your favorite articles, or even your personal notes.</p>
			<p class="para top">Furthermore, you can upload your own pictures, <!--MP3s and videos, -->and personalize them with titles and descriptions.</p>
			<p class="para top">Keep it organized in nested tags (tags, sub-tags, etc.), so you won't get lost in your own jam.</p>
			<p>&nbsp;</p>
			<p id="firstjam">Start by klipping your first jam now:</p>
			<form action="" method="POST" enctype='multipart/form-data'>
			<label class="tooltip"><input type="text" class="textbox klip-from-web" name="klip-url" placeholder="Klip it from the web" /><?php echo $birdy->tooltip("Enter URL to klip"); ?></label>
			<div class="ui divider">
			<div class="ui or theme-main-bg theme-bright-fg theme-bright-border">or</div>
			</div>
			<p class="info">upload a photo</p>
			<input type="file" class="klip-from-disk" name="klip-file" placeholder="Klip it from my disk" />
			<div class="ui divider">
			<div class="ui or theme-main-bg theme-bright-fg theme-bright-border">or</div>
			</div>
			<label class="tooltip">
				<textarea class="textbox klip-textarea" name="klip-note" placeholder="Klip a personal note" /></textarea>
				<?php echo $birdy->tooltip("BBCode allowed!"); ?>
			</label>
			<p class="para top" style="font-size: 15px; font-weight: bold;">Happy Klipping! <img src="<?php echo BIRDY_URL.'images/loading.gif'; ?>" id="loading_image" style="display:none;float:right;padding-left:5px;" /><input type="submit" class="klip-submit" name="klip-submit" value="Klip it!" /></p>
			<input type="hidden" name="edit" value="0" />
			</form>
		<?php
		}
	}
	?>
	</div>
	</div>
	<div class="clear"></div>		<p>&nbsp;</p>
	<?php

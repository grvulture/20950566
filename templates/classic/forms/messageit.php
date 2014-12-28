<div id="klipit" style="display:block;">
<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
// type, url, title, description, author, thumbnail, bignail, user_id, parent_tag, tags, creation_timestamp, creation_date
if (!empty($_POST['message-submit'])) {
	include("message-submit.php");
} else {
?>
		<form action="/inbox" method="POST" enctype='multipart/form-data'>
		<label class="tooltip">
			<textarea class="textbox klip-textarea" name="klip-note" placeholder="Write your message" /></textarea>
			<?php echo $birdy->tooltip("BBCode allowed!"); ?>
		</label>
		<div class="ui divider">
		</div>
		<label class="tooltip"><input autocomplete="off" type="text" class="textbox klip-from-web" name="klip-url" placeholder="Add a klip" /><?php echo $birdy->tooltip("Enter URL or select one from your klips"); ?></label>
		<div class="ui divider">
		<div class="ui or theme-main-bg theme-bright-fg theme-bright-border">or</div>
		</div>
		<p class="info">upload a photo</p>
		<input type="file" class="klip-from-disk" style="overflow:hidden;max-width:325px;" name="klip-file" placeholder="Klip it from my disk" />
		<p class="para top">
			<input type="submit" class="klip-submit" name="message-submit" onclick="document.getElementById('loading_image').style.display='inline';" value="Send it!" />
			<span id="loading_image" class="info" style="display:none;float:right;padding-left:5px;">Please wait ... <img src="<?php echo BIRDY_URL.'images/icons/loading.gif'; ?>" />&nbsp;&nbsp;<span>
		</p>
		<input type="hidden" name="edit" value="0" />
		<input type="hidden" name="receiver" value="<?php echo intval($_REQUEST['new']); ?>" />
		</form>
		<br />
		<br />
<?php
}
?>
</div>
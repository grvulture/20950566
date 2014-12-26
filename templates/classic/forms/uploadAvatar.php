<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
?>
		<form action="/uploadAvatar" method="POST" enctype='multipart/form-data'>
		<p class="info">Upload a new photo of you!</p>
		<input type="file" class="klip-from-disk" name="avatar_file" placeholder="Upload a photo from my disk" />
		<p class="info" style="float:right;font-size:11px;">max. file size allowed: 5MB</p>
		<div class="ui divider">
		</div>
		<p class="info" style="padding-left:3px">Your photo will be resized and<br />cropped to a circle with a 200<br />pixels diameter.</p>
		<p class="para top"><img src="<?php echo BIRDY_URL.'images/loading.gif'; ?>" id="loading_image" style="display:none;float:right;padding-left:5px;" /><input type="submit" class="klip-submit" name="klip-submit" value="Upload it!" /></p>
		</form>
<?php

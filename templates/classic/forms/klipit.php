<div id="klipit" style="display:block;">
<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
// type, url, title, description, author, thumbnail, bignail, user_id, parent_tag, tags, creation_timestamp, creation_date
if (!empty($_POST['klip-done'])) {
	include("klip-done.php");
}
elseif (!empty($_POST['klip-submit']) || isset($_REQUEST['klip']) || isset($_REQUEST['reklip']) || isset($_REQUEST['external'])) {
	include("klip-submit.php");
} else {
	$privacy= 0;
	if ($privacy==0) {
		$lastprivacy = $db->loadResult("SELECT privacy FROM klips WHERE user_id=:current_user ORDER BY id DESC LIMIT 1",
			array(":current_user"=>$user->id));
	if (!empty($lastprivacy)) $privacy = $lastprivacy;
	}
	?>
		<form action="/profile" method="POST" enctype='multipart/form-data'>
		<label class="tooltip"><input autocomplete="off" type="text" class="textbox klip-from-web" name="klip-url" placeholder="Klip from the web" /><?php echo $birdy->tooltip("Enter URL to klip"); ?></label>
		<div class="ui divider">
		<div class="ui or theme-main-bg theme-bright-fg theme-bright-border">or</div>
		</div>
		<p class="info">upload a photo</p>
		<input type="file" class="klip-from-disk" style="width:104px;" onchange="document.getElementById('thisfile').innerHTML = this.value" name="klip-file" placeholder="Klip it from my disk" />
		<span id="thisfile" style="float:right;">Nothing to upload</span>
		<div class="ui divider">
		<div class="ui or theme-main-bg theme-bright-fg theme-bright-border">or</div>
		</div>
		<label class="tooltip">
			<textarea class="textbox klip-textarea" name="klip-note" placeholder="Klip a personal note" /></textarea>
			<?php echo $birdy->tooltip("BBCode allowed!"); ?>
		</label>
		<div class="ui divider">
		</div>
		<span>
		Privacy: 
		<?php
		$opacity = ($privacy==0) ? 'opacity:1;' : 'opacity:0.2;';
		echo '<img id="klipprivacy0" onclick="change_privacy(0)" src="'.BIRDY_URL.'images/icons/public.png" style="position: relative; top:4px; padding:2px;cursor:pointer;'.$opacity.'" />';
		$opacity = ($privacy==1) ? 'opacity:1;' : 'opacity:0.2;';
		echo '<img id="klipprivacy1" onclick="change_privacy(1)" src="'.BIRDY_URL.'images/icons/friends.png" style="position: relative; top:4px; padding:2px;cursor:pointer;'.$opacity.'" />';
		$opacity = ($privacy==2) ? 'opacity:1;' : 'opacity:0.2;';
		echo '<img id="klipprivacy2" onclick="change_privacy(2)" src="'.BIRDY_URL.'images/icons/onlyfriends.png" style="width:16px;position: relative; top:4px; padding:2px;cursor:pointer;'.$opacity.'" />';
		$opacity = ($privacy==3) ? 'opacity:1;' : 'opacity:0.2;';
		echo '<img id="klipprivacy3" onclick="change_privacy(3)" src="'.BIRDY_URL.'images/icons/lock.png" style="position: relative; top:4px; padding:2px;cursor:pointer;'.$opacity.'" />';
		?>
		<select name="klipprivacy" id="klipprivacy" class="klip-select" style="width: 190px ! important;">
			<?php 
			$selected = ($privacy==0) ? 'selected="selected"' : ''; 
			echo '<option value="0" '.$selected.'>Public</option>';
			$selected = ($privacy==1) ? 'selected="selected"' : ''; 
			echo '<option value="1" '.$selected.'>My followers &amp; friends</option>';
			$selected = ($privacy==2) ? 'selected="selected"' : '';
			echo '<option value="2" '.$selected.'>Only my friends</option>';
			$selected = ($privacy==3) ? 'selected="selected"' : ''; 
			echo '<option value="3" '.$selected.'>Only me</option>';
			?>
		</select>
		</span>
		<div class="clear"><p>&nbsp;</p></div>
		<span style="float:right;">
			<?php if ($birdy->browser=='web') { ?>
			<label style="cursor:pointer;"><input type="checkbox" class="textbox klip-select" name="advanced_edit" id="advanced-edit" value="1" /> Advanced Edit</label>
			<?php } ?>
		</span>
		<div class="clear"></div>
		<p class="para top">
			<input type="submit" class="klip-submit" name="klip-submit" onclick="document.getElementById('loading_image').style.display='inline';" value="Klip it!" />
			<span id="loading_image" class="info" style="position:relative;top:12px;display:none;float:right;padding-left:5px;">Please wait ... <img src="<?php echo BIRDY_URL.'images/icons/loading.gif'; ?>" />&nbsp;&nbsp;<span>
		</p>
		<input type="hidden" name="edit" value="0" />
		</form>
		<p><br /><br /><br /></p>
<?php
	$birdy->addScriptToBottom('
	$(document).ready(function() {
		$("#privacy").change(function(){
			var value = document.getElementById("privacy").value;
			change_privacy(value);
		});
	} );
	function change_privacy(value) {
		document.getElementById("klipprivacy0").style.opacity = "0.2";
		document.getElementById("klipprivacy1").style.opacity = "0.2";
		document.getElementById("klipprivacy2").style.opacity = "0.2";
		document.getElementById("klipprivacy3").style.opacity = "0.2";
		document.getElementById("klipprivacy"+value).style.opacity = "1";
		document.getElementById("klipprivacy").value = value;
	}
	');
}
?>
</div>
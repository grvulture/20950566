<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
//============================================================================
if (empty($_REQUEST['popup']) && $birdy->browser=='web') {
$user_heading = empty($user->header) ? '<a rel="lightbox" title="Update your profile info" href="'.BIRDY_URL.'editProfile'.$popup.'">Enter a catching header for your profile</a>' : $user->header;
$user_about   = empty($user->about) ? '<a rel="lightbox" title="Update your profile info" href="'.BIRDY_URL.'editProfile'.$popup.'">Enter a short bio of you</a>' : $user->about;
?>
<div class="span3">
	<div class="span1_of_3 profile-left">
		<a rel="lightbox" title="Change your Profile Picture" href="<?php echo BIRDY_URL; ?>uploadAvatar"><img src="<?php echo $user->avatar; ?>" style="border-radius:150px" alt="<?php echo $user_credentials; ?>"></a>
		<h2 class="style top1" style="word-wrap: break-word;"><a rel="lightbox" title="Update your profile info" href="<?php echo BIRDY_URL; ?>editProfile<?php echo $popup; ?>"><?php echo $user_credentials; ?></a></h2>
		<h5 class="style"><?php echo $user_heading; ?></h5>
		<p class="para info" style="font-size:11px;"><?php echo $user_about; ?></p>
	</div>
	<div class="span2_of_3 profile-right">
<?php
}
?>
		<form action="/editProfile" method="POST">
		<div style="margin-bottom:25px;">
			<span class="">Email Address:</span>
			<label class="tooltip"><input autocomplete="off" type="text" class="textbox klip-from-web" name="user_email" placeholder="Your Email Address" value="<?php echo $user->email; ?>" /><?php echo $birdy->tooltip("It is required to login"); ?></label>
			<div style="padding-bottom:8px;"><p class="info" style="float:right;font-size:11px;">not displayed on site</p></div>
			<span class="">Username:</span>
			<label class="tooltip"><input autocomplete="off" type="text" class="textbox klip-from-web" name="user_name" placeholder="Your Klipper Name" value="<?php echo $user->username; ?>" /><?php echo $birdy->tooltip("It apppears on your profile URL"); ?></label>
			<p class="info" style="float:right;font-size:11px;">no spaces and special characters allowed</p>
		</div>

		<div style="margin-bottom:25px;">
			<span class="">Real Name:</span>
			<label class="tooltip"><input autocomplete="off" type="text" class="textbox klip-from-web" name="first_name" placeholder="Your First Name" value="<?php echo $user->first_name; ?>" /><?php echo $birdy->tooltip("It appears on your profile & activity"); ?></label>
			<label class="tooltip"><input autocomplete="off" type="text" class="textbox klip-from-web" name="last_name" placeholder="Your Last Name" value="<?php echo $user->last_name; ?>" /><?php echo $birdy->tooltip("It appears on your profile & activity"); ?></label>
		</div>

		<div style="margin-bottom:25px;">
			<span class="">Profile Header:</span>
			<label class="tooltip">
				<textarea class="textbox klip-textarea" name="user_header" style="height:22px;" placeholder="Enter a personal header"><?php echo $user->header; ?></textarea>
				<?php echo $birdy->tooltip("This is the line that identifies you"); ?>
			</label>
			<p class="info" style="float:right;font-size:11px;">max. length: 128 characters</p>
		</div>

		<div style="margin-bottom:25px;">
		<span class="">Short Bio:</span><br />
		<label class="tooltip">
			<textarea id="klip_description" class="textbox klip-textarea" style="height:50px;font-size:13px" name="about" placeholder="Enter a short bio of you"><?php echo $user->about; ?></textarea>
			<?php echo $birdy->tooltip('This is your "About me" paragraph'); ?>
		</label>
		</div>

		<input type="hidden" name="editProfile" value="1" />
		<p class="para top"><img src="<?php echo BIRDY_URL.'images/loading.gif'; ?>" id="loading_image" style="display:none;float:right;padding-left:5px;" /><input type="submit" class="klip-submit" name="klip-submit" value="Update!" /></p>
		<div class="clear">&nbsp;</div>
		</form>
<?php
if (empty($_REQUEST['popup']) && $birdy->browser=='web') echo "</div>";
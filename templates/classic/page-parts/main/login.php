<?php
defined('_BIRDY') or die(dirname(__FILE__).DS.__FILE__.': Restricted access');
birdySession::init();

// first check if we need to verify anything...
// if we have something to verify and it succeeded, redirect and return
$redirect_verification = isset($_REQUEST['verify']) ? birdyLogin::verifySomething($_REQUEST['verify']) : false;
//============================================================================
// now check if we need to login with provider...
if (isset($_GET['provider'])) {
	//start the connecting session
	$_SESSION['connectWithProvider'] = $_GET['provider'];
	//reload the page without the $_GET argument
	$birdy->loadPage("/login");
	return;
}
// if we are in a social connecting session
if (isset($_SESSION['connectWithProvider'])) {
	$provider = $_SESSION['connectWithProvider'];
	$login_with_provider = birdyLogin::connectWithProvider($provider);
	return;
}
//============================================================================
if ($user->isLoggedIn()) {
	echo '<div id="main" class="popup-wrapper">';
	echo '<div class="artical-commentbox">';
	echo '<div class="table-form">';
	echo "<h2 class='style'>You are already logged in!</h2>";
	echo '<a class="logoutButton" href="/logout">Logout</a>';
	echo "</div>";
	echo "</div>";
	echo "</div>";
	echo '<div class="clear"></div>';
	return;
}
//============================================================================
// then check if we need to login...
//if (!empty($_POST)) {
	//echo "<pre>";print_r($_POST);echo "</pre>";exit;
//}
birdyLogin::loginOrRegister(
	/*login vars*/
	'emailLogin',
	'passwordLogin',
	'rememberme',
	/*register vars*/
	'emailSignup',
	'passwordSignup'
);
if (isset($_REQUEST['emailResetPassword'])) birdyLogin::requestPasswordReset('emailResetPassword');
$OK = true;
if (isset($_REQUEST['user_password_reset_hash'])) {
	$OK = birdyLogin::setNewPassword('user_name', 'user_password_reset_hash', 'user_password_new', 'user_password_repeat');
	if ($OK) $redirect_verification[0]=false;
}
//============================================================================
// display
?>
<?php if (!isset($_REQUEST['popup']) && $birdy->browser=='web') { ?>
<div class="info basic-terms">By signing up, or logging in with either Facebook, Twitter, or Google+, you are automatically accepting our <a href="/terms">Terms of Service</a>.
<?php include("basic_terms.php"); ?>
</div>
<?php }
$forgot_password_question = '';
if (!isset($_SESSION["feedback_negative"])) $_SESSION["feedback_negative"] = array();
if ($redirect_verification[0]=='resetPassword' || !$OK) { ?>
	<div class="popup-wrapper" style="padding-left:15px;">
	<div class="artical-commentbox">
	<div class="table-form">
		<h2>Please reset your password:</h2>
		<form action="" method="POST" >
			<input name="user_password_new" type="password" class="textbox klip-input" placeholder="New Password:" value="">
			<input name="user_password_repeat" type="password" class="textbox klip-input" placeholder="Confirm New Password:" value="">
			<input type="submit" value="Set my new password" />
			<input name="user_name" type="hidden" value="<?php echo $redirect_verification[1]; ?>">
			<input name="user_password_reset_hash" type="hidden" value="<?php echo $redirect_verification[2]; ?>">
		</form>
	</div>
	</div>
	</div>
<?php
return;
}
elseif (in_array(FEEDBACK_LOGIN_FAILED,$_SESSION["feedback_negative"]) || in_array(FEEDBACK_PASSWORD_WRONG,$_SESSION["feedback_negative"]) || strstr(BIRDY_SEF_URI,'/reset/Password')) { 
	$forgot_password_question = '<a href="javascript:forgot_password_question()" id="forgot_password_question">Forgot password?</a>';
	?>
	<div class="popup-wrapper" style="height:100%;">
	<div class="artical-commentbox">
	<div id="forgot_password" class="table-form">
		<h2>Did you forget your password?</h2>
		<p class="info">Give us your email and we will send you a password reset link!</p>
		<form action="/login/reset/Password" method="POST" >
			<input name="emailResetPassword" id="emailResetPassword" type="email" class="textbox klip-input" placeholder="Email:" value="">
			<input type="submit" value="Reset my password" />
		</form>
	</div>
	</div>
	</div>
<br /><br /><br /><br /><div style="margin-bottom:20px"><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p></div>
<?php
}
//============================================================================
// DISPLAY
?>
<div id="main" class="popup-wrapper" style="width:360px;">
<h2>Welcome Klipper!</h2>
</div>
<div class="clear"></div>
			<div id="signin" style="display:none;">
				<div class="artical-commentbox">
					<h2>Welcome Back Klipper!</h2>
		  			<div class="table-form">
						<form action="/login" method="POST" name="login" id="loginForm">
							<input name="emailLogin" type="email" class="textbox klip-input" placeholder="Email:" value="" />
							<input name="passwordLogin" type="password" class="textbox klip-input" placeholder="Password:" value="" />
							<p><label class="rememberme_label"><input name="rememberme" type="checkbox" checked="checked" value="1" /> remember me</label></p>
						</form>
							<?php echo $forgot_password_question; ?>
							<a class="loginButton" id="signinButton" href="javascript:$('#loginForm').submit();">Login</a>
							<div class="clear"></div>
							<div class="ui divider">
							<div class="ui or theme-main-bg theme-bright-fg theme-bright-border">or</div>
							</div>
							<button onclick="windowOpen('<?php echo BIRDY_URL.'login?provider=facebook'; ?>');" id="facebookLogin" class="button facebook ui">
							<i class="social-facebook"></i>
							<img src="<?php echo BIRDY_URL; ?>images/facebook-white.png" style="float:left;" />
							<span style="float:right;">Login with
							Facebook</span>
							</button>
							<button onclick="windowOpen('<?php echo BIRDY_URL.'login?provider=twitter'; ?>');" class="button facebook twitter ui" id="twitterLogin">
							<i class="social-twitter"></i>
							<img src="<?php echo BIRDY_URL; ?>images/twitter-white.png" style="float:left;" />
							<span style="float:right;">Login with
							Twitter</span>
							</button>
							<button onclick="windowOpen('<?php echo BIRDY_URL.'login?provider=google'; ?>');" class="button google_oauth2 ui" id="googleLogin">
							<i class="social-google"></i>
							<img src="<?php echo BIRDY_URL; ?>images/googleplus.png" style="float:left;" />
							<span style="float:right;">Login with
							Google</span>
							</button>
							<p class="info">
								Don't have an account?
							</p>
							<a href="javascript:navigate('signup')">Sign up</a>
					</div>
					<div class="clear"></div>
		  		</div>
<?php if (isset($_REQUEST['popup']) || $birdy->browser!='web') { ?>
<div class="info basic-terms-small" style="position:relative;top:-47px;">By signing up, or logging in with either Facebook, Twitter, or Google+, you are automatically accepting our <a href="/terms">Terms of Service</a>.
</div>
<?php } ?>
			</div>
			<div id="signup" style="display:none;">
				<div class="artical-commentbox">
					<h2>Welcome New Klipper!</h2>
		  			<div class="table-form">
						<form action="/login" method="POST" name="signup" id="signupForm">
							<input name="emailSignup" type="email" class="textbox klip-input" placeholder="Email:" value="">
							<input name="passwordSignup" type="password" class="textbox klip-input" placeholder="Password:" value="">
						</form>
							<a class="loginButton" id="signupButton" href="javascript:$('#signupForm').submit();">Sign up</a>
							<div class="clear"></div>
							<div class="ui divider">
							<div class="ui or theme-main-bg theme-bright-fg theme-bright-border">or</div>
							</div>
							<button onclick="windowOpen('<?php echo BIRDY_URL.'login?provider=facebook'; ?>');" id="facebookSignup" class="button facebook ui">
							<i class="social-facebook"></i>
							<img src="<?php echo BIRDY_URL; ?>images/facebook-white.png" style="float:left;" />
							<span style="float:right;">Sign up with
							Facebook</span>
							</button>
							<button onclick="windowOpen('<?php echo BIRDY_URL.'login?provider=twitter'; ?>');" class="button facebook twitter ui" id="twitterSignup">
							<i class="social-twitter"></i>
							<img src="<?php echo BIRDY_URL; ?>images/twitter-white.png" style="float:left;" />
							<span style="float:right;">Sign up with
							Twitter</span>
							</button>
							<button onclick="windowOpen('<?php echo BIRDY_URL.'login?provider=google'; ?>');" class="button google_oauth2 ui" id="googleSignup">
							<i class="social-google"></i>
							<img src="<?php echo BIRDY_URL; ?>images/googleplus.png" style="float:left;" />
							<span style="float:right;">Sign up with
							Google</span>
							</button>
							<p class="info">
								Already have an account?
							</p>
							<a href="javascript:navigate('signin')">Login</a>
					</div>
					<div class="clear"></div>
		  		</div>
<?php if (isset($_REQUEST['popup']) || $birdy->browser!='web') { ?>
<div class="info basic-terms-small" style="position:relative;top:-47px;">By signing up, or logging in with either Facebook, Twitter, or Google+, you are automatically accepting our <a href="/terms">Terms of Service</a>.
</div>
<?php } ?>
			</div>
<?php 
$birdy->addScriptToBottom("
$(document).ready(function() {
	$('#main').html($('#signin').html());
});
function forgot_password_question() {
	scrollToAnchor('forgot_password');
	$('#emailResetPassword').focus();
}
function navigate(what) { 
	$('#main').fadeOut('slow',function(){
			$('#main').html($('#'+what).html());
			$('#main').fadeIn('slow');
		}
	);
}
function windowOpen(what) {
	window.location = what;
// 	window.open(what,
// 		'_blank',
// 		'toolbar=no, scrollbars=no, resizable=no, top=0, left=0, width=480, height=480'
// 	);
}
"); ?>
